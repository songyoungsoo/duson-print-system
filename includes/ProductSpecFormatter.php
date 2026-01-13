<?php
/**
 * 상품 규격 포맷터 (2줄 슬래시 형식)
 *
 * 스킬 규칙 준수: duson-print-rules
 * - 1줄: 규격 (종류 / 용지 / 규격 등)
 * - 2줄: 옵션 (인쇄면 / 수량 / 디자인 등)
 * - 추가옵션(코팅, 접지 등)이 있으면 별도 행으로 표시
 *
 * 사용 위치: 장바구니, 주문, 주문완료, 관리자, 주문서출력, 견적서, 마이페이지
 *
 * ✅ 2026-01-13 Grand Design: QuantityFormatter SSOT 통합
 */

require_once __DIR__ . '/QuantityFormatter.php';

class ProductSpecFormatter {
    private $db;
    private $nameCache = [];

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 2줄 형식으로 규격/옵션 반환
     * @param array $item 상품 데이터 (shop_temp 또는 mlangorder_printauto)
     * @return array ['line1' => '규격', 'line2' => '옵션', 'additional' => '추가옵션']
     */
    public function format($item) {
        // ✅ 2026-01-13 FIX: DB 컬럼 값 보존, Type_1 JSON은 누락된 필드만 보충
        // Type_1 JSON 파싱 (mlangorder_printauto에서 가져온 경우)
        if (!empty($item['Type_1']) && is_string($item['Type_1'])) {
            $type1Data = json_decode($item['Type_1'], true);
            if ($type1Data && is_array($type1Data)) {
                // ✅ 2026-01-13 FIX: order_details 중첩 구조 처리 (레거시 데이터 호환)
                // 예: {"order_details": {"jong": "...", "garo": 90, ...}}
                if (isset($type1Data['order_details']) && is_array($type1Data['order_details'])) {
                    $type1Data = array_merge($type1Data, $type1Data['order_details']);
                    unset($type1Data['order_details']);
                }

                // DB 컬럼 값이 없는 경우에만 Type_1 JSON 값 사용
                foreach ($type1Data as $key => $value) {
                    // 빈 문자열이나 NULL은 건너뛰기, DB 값이 이미 있으면 덮어쓰지 않음
                    if ($value !== '' && $value !== null && (!isset($item[$key]) || $item[$key] === '' || $item[$key] === null)) {
                        $item[$key] = $value;
                    }
                }
            }
        }

        // ✅ Phase 3-3: 버전 체크 - 신규 vs 레거시 (강화된 폴백)
        // data_version=2이거나, data_version이 없어도 표준 필드가 있으면 표준 포맷 시도
        $hasStandardFields = !empty($item['spec_type']) || !empty($item['spec_material']) ||
                             !empty($item['spec_size']) || !empty($item['quantity_display']);

        $shouldTryStandard = (isset($item['data_version']) && $item['data_version'] == 2) || $hasStandardFields;

        if ($shouldTryStandard) {
            $standardResult = $this->formatStandardized($item);  // 신규 표준 포맷

            // ✅ Phase 3-3: 표준 필드가 모두 비어있으면 레거시로 폴백
            if (empty($standardResult['line1']) && empty($standardResult['line2'])) {
                error_log("Phase 3-3: 표준 필드 비어있음, 레거시로 폴백 - product_type: " . ($item['product_type'] ?? 'unknown'));
                return $this->formatLegacy($item);
            }

            error_log("Phase 3-3: 표준 포맷 사용 - product_type: " . ($item['product_type'] ?? 'unknown') . ", has_data_version: " . (isset($item['data_version']) ? 'yes' : 'no'));
            return $standardResult;
        }

        return $this->formatLegacy($item);  // 레거시 포맷 (10만+ 기존 주문)
    }

    /**
     * ✅ Phase 3: 표준화된 데이터 포맷 (모든 제품 동일 로직)
     * @param array $item 표준 필드가 있는 데이터
     * @return array ['line1' => '규격', 'line2' => '옵션', 'additional' => '추가옵션']
     */
    private function formatStandardized($item) {
        // 1줄: 규격 정보 (spec_type / spec_material / spec_size)
        $line1_parts = array_filter([
            $item['spec_type'] ?? '',
            $item['spec_material'] ?? '',
            $item['spec_size'] ?? ''
        ]);

        // 2줄: 옵션 정보 (spec_sides / quantity_display / spec_design)
        $quantity_display = $item['quantity_display'] ?? '';

        // ✅ 수정: quantity_display가 비어있거나 단위가 없는 경우 formatQuantity() 호출
        if (empty($quantity_display) || !preg_match('/[매연부권개장]/u', $quantity_display)) {
            $quantity_display = $this->formatQuantity($item);
        } else {
            // ✅ 2026-01-12: 단위가 있어도 소수점 정리 (500.00매 → 500매)
            $quantity_display = $this->formatDecimalQuantity($quantity_display);
        }

        $line2_parts = array_filter([
            $item['spec_sides'] ?? '',
            $quantity_display,
            $item['spec_design'] ?? ''
        ]);

        // 추가 옵션 처리 (제품 타입별로 적절한 포맷터 호출)
        $additional = $this->formatStandardOptions($item);

        return [
            'line1' => implode(' / ', $line1_parts),
            'line2' => implode(' / ', $line2_parts),
            'additional' => $additional
        ];
    }

    /**
     * ✅ Phase 3: 표준 데이터의 추가 옵션 포맷 (제품별 분기)
     */
    private function formatStandardOptions($item) {
        $productType = $item['product_type'] ?? '';

        // 프리미엄 옵션 제품: 명함, 상품권, NCR
        if (in_array($productType, ['namecard', 'merchandisebond', 'ncrflambeau'])) {
            return $this->formatPremiumOptions($item);
        }

        // 추가 옵션 제품: 전단지, 카다록, 포스터
        if (in_array($productType, ['inserted', 'leaflet', 'cadarok', 'littleprint', 'poster'])) {
            return $this->formatAdditionalOptions($item);
        }

        // 봉투: 양면테이프 처리
        if ($productType === 'envelope') {
            if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
                $tapeQty = intval($item['envelope_tape_quantity'] ?? 0);
                return $tapeQty > 0 ? "양면테이프: " . number_format($tapeQty) . "개" : "양면테이프";
            }
        }

        return '';
    }

    /**
     * ✅ Phase 3: 레거시 데이터 포맷 (기존 10만+ 주문 호환)
     * @param array $item 레거시 필드가 있는 데이터
     * @return array ['line1' => '규격', 'line2' => '옵션', 'additional' => '추가옵션']
     */
    private function formatLegacy($item) {
        $productType = $item['product_type'] ?? '';

        // 레거시 스티커 감지
        if (empty($productType) && !empty($item['jong']) && !empty($item['garo']) && !empty($item['sero'])) {
            $productType = 'sticker';
        }

        switch ($productType) {
            case 'sticker':
                return $this->formatSticker($item);
            case 'namecard':
                return $this->formatNamecard($item);
            case 'envelope':
                return $this->formatEnvelope($item);
            case 'inserted':
            case 'leaflet':
                return $this->formatLeaflet($item);
            case 'cadarok':
                return $this->formatCatalog($item);
            case 'littleprint':
            case 'poster':
                return $this->formatPoster($item);
            case 'msticker':
            case 'msticker_01':
                return $this->formatMagnetSticker($item);
            case 'ncrflambeau':
                return $this->formatNCR($item);
            case 'merchandisebond':
                return $this->formatVoucher($item);
            default:
                return $this->formatGeneric($item);
        }
    }

    /**
     * HTML 출력용 (2줄 + 추가옵션)
     */
    public function formatHtml($item) {
        $result = $this->format($item);
        $html = '';

        if (!empty($result['line1'])) {
            $html .= htmlspecialchars($result['line1']);
        }
        if (!empty($result['line2'])) {
            $html .= '<br>' . htmlspecialchars($result['line2']);
        }
        if (!empty($result['additional'])) {
            $html .= '<br><span style="color:#666;font-size:12px;">' . htmlspecialchars($result['additional']) . '</span>';
        }

        return $html;
    }

    /**
     * 텍스트 출력용 (줄바꿈)
     */
    public function formatText($item) {
        $result = $this->format($item);
        $lines = [];

        if (!empty($result['line1'])) $lines[] = $result['line1'];
        if (!empty($result['line2'])) $lines[] = $result['line2'];
        if (!empty($result['additional'])) $lines[] = $result['additional'];

        return implode("\n", $lines);
    }

    /**
     * ✅ 통합 포맷: 고정 양식에 맞춰 4줄로 표시
     * 모든 제품을 동일한 구조로 표시 (일관성 확보)
     *
     * @param array $item 상품 데이터
     * @return array ['line1' => '', 'line2' => '', 'line3' => '', 'line4' => '']
     */
    public function formatUnified($item) {
        // Type_1 JSON 파싱
        if (!empty($item['Type_1']) && is_string($item['Type_1'])) {
            $type1Data = json_decode($item['Type_1'], true);
            if ($type1Data && is_array($type1Data)) {
                $item = array_merge($item, $type1Data);
            }
        }

        $productType = $item['product_type'] ?? '';

        // 1줄: 제품종류 / 재질 / 규격
        $line1 = $this->buildLine1($item, $productType);

        // 2줄: 인쇄옵션 / 수량+단위 / 디자인
        $line2 = $this->buildLine2($item, $productType);

        // 3줄: 추가옵션 (코팅, 접지, 오시)
        $line3 = $this->buildLine3($item, $productType);

        // 4줄: 특수옵션 (프리미엄 옵션, 양면테이프)
        $line4 = $this->buildLine4($item, $productType);

        return [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'line4' => $line4
        ];
    }

    /**
     * 1줄 구성: spec_type / spec_material / spec_size
     */
    private function buildLine1($item, $productType) {
        // 표준 필드 우선 사용
        $slot1 = $item['spec_type'] ?? '';
        $slot2 = $item['spec_material'] ?? '';
        $slot3 = $item['spec_size'] ?? '';

        // 레거시 필드 폴백
        if (empty($slot1)) {
            if ($productType === 'sticker') {
                // 스티커: domusong 필드 (앞의 0과 공백 제거)
                $domusong = $item['domusong'] ?? '';
                $slot1 = preg_replace('/^[0\s]+/', '', $domusong);
            } elseif ($productType === 'ncrflambeau') {
                // NCR은 PN_type이 spec_type에 매핑
                $slot1 = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type'] ?? '');
            } else {
                $slot1 = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type'] ?? '');
            }
        }

        if (empty($slot2)) {
            if ($productType === 'sticker') {
                // 스티커: jong 필드 사용 (jil 제거)
                $jong = $item['jong'] ?? '';
                $slot2 = preg_replace('/^jil\s*/i', '', $jong);
            } elseif ($productType === 'cadarok') {
                // 카다록: slot2는 비어있음 (종류 / 규격 형식)
                $slot2 = '';
            } elseif (in_array($productType, ['namecard', 'envelope', 'merchandisebond'])) {
                $slot2 = $item['Section_name'] ?? $this->getKoreanName($item['Section'] ?? '');
            } elseif (in_array($productType, ['littleprint', 'poster'])) {
                // 포스터: Section 또는 MY_Fsd가 재질
                $slot2 = $item['Section_name'] ?? $item['MY_Fsd_name'] ??
                         $this->getKoreanName($item['Section'] ?? $item['MY_Fsd'] ?? '');
            } else {
                $slot2 = $item['MY_Fsd_name'] ?? $this->getKoreanName($item['MY_Fsd'] ?? '');
            }
        }

        if (empty($slot3)) {
            if ($productType === 'sticker') {
                // 스티커: garo x sero 형식
                if (!empty($item['garo']) && !empty($item['sero'])) {
                    $slot3 = $item['garo'] . 'mm x ' . $item['sero'] . 'mm';
                }
            } elseif ($productType === 'msticker' || $productType === 'msticker_01') {
                $slot3 = $item['Section_name'] ?? $this->getKoreanName($item['Section'] ?? '');
            } elseif (in_array($productType, ['namecard', 'merchandisebond'])) {
                $slot3 = '90mm x 50mm';  // 고정값
            } elseif ($productType === 'cadarok') {
                // 카다록: Section이 규격
                $slot3 = $item['Section_name'] ?? $this->getKoreanName($item['Section'] ?? '');
            } elseif ($productType === 'ncrflambeau') {
                // NCR: slot3는 비어있음 (타입 / 용지 형식)
                $slot3 = '';
            } else {
                $slot3 = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type'] ?? '');
            }
        }

        return implode(' / ', array_filter([$slot1, $slot2, $slot3]));
    }

    /**
     * 2줄 구성: spec_sides / quantity_display / spec_design
     */
    private function buildLine2($item, $productType) {
        // 인쇄옵션 (spec_sides)
        $slot1 = $item['spec_sides'] ?? '';
        if (empty($slot1)) {
            if ($productType === 'envelope') {
                // 봉투: POtype_name이 인쇄 색상 (마스터1도, 칼라4도 등)
                $slot1 = $item['POtype_name'] ?? $this->getKoreanName($item['POtype'] ?? '');
            } elseif ($productType === 'ncrflambeau') {
                // NCR: MY_type_name이 도수 (spec_sides에 매핑)
                $slot1 = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type'] ?? '');
            } elseif (in_array($productType, ['namecard', 'inserted', 'leaflet', 'merchandisebond'])) {
                // 명함, 전단지, 상품권: POtype이 단면/양면
                if (!empty($item['POtype'])) {
                    $slot1 = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');
                }
            } elseif (in_array($productType, ['msticker', 'msticker_01'])) {
                // 자석스티커: POtype이 단면/양면
                if (!empty($item['POtype'])) {
                    $slot1 = $item['POtype'] == '1' ? '단면' : '양면';
                }
            } elseif ($productType === 'cadarok') {
                // 카다록: POtype_name (4도4도 등)
                $slot1 = $item['POtype_name'] ?? $this->getKoreanName($item['POtype'] ?? '');
            }
            // 스티커, 포스터는 빈값
        }

        // 수량+단위 (quantity_display)
        $rawSlot2 = $item['quantity_display'] ?? '';

        // ✅ 2026-01-12: 모든 경우에 소수점 정리 적용
        // 단위: 매, 연, 부, 권, 개, 장 등
        if (empty($rawSlot2) || !preg_match('/[매연부권개장]/u', $rawSlot2)) {
            // formatQuantity() 호출 (레거시 로직 + 천 단위 변환)
            $slot2 = $this->formatQuantity($item);
        } else {
            // 단위가 있는 경우에도 소수점 정리 (500.00매 → 500매)
            $slot2 = $this->formatDecimalQuantity($rawSlot2);
        }

        // 디자인 (spec_design)
        $slot3 = $item['spec_design'] ?? '';
        if (empty($slot3)) {
            $slot3 = $this->formatDesign($item);
        }

        return implode(' / ', array_filter([$slot1, $slot2, $slot3]));
    }

    /**
     * 3줄 구성: 추가옵션 (코팅, 접지, 오시)
     * 해당 제품만 표시: inserted, leaflet, cadarok, littleprint, poster
     */
    private function buildLine3($item, $productType) {
        // 추가옵션 제품만
        if (!in_array($productType, ['inserted', 'leaflet', 'cadarok', 'littleprint', 'poster'])) {
            return '';
        }

        return $this->formatAdditionalOptions($item);
    }

    /**
     * 4줄 구성: 특수옵션 (프리미엄 옵션, 양면테이프)
     */
    private function buildLine4($item, $productType) {
        // 프리미엄 옵션 제품: 명함, 상품권, NCR
        if (in_array($productType, ['namecard', 'merchandisebond', 'ncrflambeau'])) {
            return $this->formatPremiumOptions($item);
        }

        // 봉투: 양면테이프
        if ($productType === 'envelope') {
            if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
                $tapeQty = intval($item['envelope_tape_quantity'] ?? 0);
                return $tapeQty > 0 ? "양면테이프: " . number_format($tapeQty) . "개" : "양면테이프";
            }
        }

        return '';
    }

    /**
     * 단일 행 출력용 (관리자 주문서, 엑셀용)
     * 규격 | 옵션 형식으로 반환
     */
    public function formatSingleLine($item) {
        $result = $this->format($item);
        $parts = [];

        if (!empty($result['line1'])) $parts[] = $result['line1'];
        if (!empty($result['line2'])) $parts[] = $result['line2'];

        return implode(' | ', $parts);
    }

    // ========== 품목별 포맷터 ==========

    /**
     * 전단지/리플렛
     * 1줄: 용지 / 규격
     * 2줄: 인쇄면 / 수량 / 디자인
     */
    private function formatLeaflet($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_Fsd'])) {
            $name = $item['MY_Fsd_name'] ?? $this->getKoreanName($item['MY_Fsd']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type']);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        if (!empty($item['POtype'])) {
            $line2Parts[] = $item['POtype'] == '1' ? '단면' : '양면';
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatAdditionalOptions($item)
        ];
    }

    /**
     * 명함
     * 1줄: 종류 / 용지
     * 2줄: 인쇄면 / 수량 / 디자인
     */
    private function formatNamecard($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['MY_Fsd']) || !empty($item['Section'])) {
            $code = $item['MY_Fsd'] ?? $item['Section'];
            $name = $item['Section_name'] ?? $this->getKoreanName($code);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        if (!empty($item['POtype'])) {
            $name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');
            $line2Parts[] = $name;
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatPremiumOptions($item)
        ];
    }

    /**
     * 봉투
     * 1줄: 종류 / 재질
     * 2줄: 인쇄 색상 / 수량 / 디자인
     */
    private function formatEnvelope($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section']) || !empty($item['MY_Fsd'])) {
            $code = $item['Section'] ?? $item['MY_Fsd'];
            $name = $item['Section_name'] ?? $this->getKoreanName($code);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        if (!empty($item['POtype'])) {
            $name = $item['POtype_name'] ?? $this->getKoreanName($item['POtype']);
            if ($name) $line2Parts[] = $name;  // 인쇄 색상 (마스터1도/마스터2도/칼라4도)
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        // 양면테이프 추가옵션
        $additional = '';
        if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
            $tapeQty = intval($item['envelope_tape_quantity'] ?? 0);
            $additional = $tapeQty > 0 ? "양면테이프: " . number_format($tapeQty) . "개" : "양면테이프";
        }

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $additional
        ];
    }

    /**
     * 스티커
     * 1줄: 모양 / 재질 / 크기
     * 2줄: 수량 / 디자인
     *
     * ✅ 2026-01-13 FIX: 표준 필드 우선 사용, 레거시 필드 폴백
     * 표준 필드: spec_type(모양), spec_material(재질), spec_size(크기)
     * 레거시 필드: domusong(모양), jong(재질), garo/sero(크기)
     */
    private function formatSticker($item) {
        $line1Parts = [];
        $line2Parts = [];

        // ✅ 1줄: 규격 - 표준 필드 우선, 레거시 폴백
        // 모양 (spec_type 우선 → domusong 폴백)
        $shape = $item['spec_type'] ?? '';
        if (empty($shape)) {
            $domusong = $item['domusong'] ?? '';
            $domusong = preg_replace('/^[0\s]+/', '', $domusong);
            if (!empty($domusong) && $domusong !== '0') {
                $shape = $domusong;
            }
        }
        if (!empty($shape)) $line1Parts[] = $shape;

        // 재질 (spec_material 우선 → jong 폴백)
        $material = $item['spec_material'] ?? '';
        if (empty($material)) {
            $jong = $item['jong'] ?? '';
            $jong = preg_replace('/^jil\s*/i', '', $jong);
            if (!empty($jong)) {
                $material = $jong;
            }
        }
        if (!empty($material)) $line1Parts[] = $material;

        // 크기 (spec_size 우선 → garo/sero 폴백)
        $size = $item['spec_size'] ?? '';
        if (empty($size) && !empty($item['garo']) && !empty($item['sero'])) {
            $size = $item['garo'] . 'mm x ' . $item['sero'] . 'mm';
        }
        if (!empty($size)) $line1Parts[] = $size;

        // 2줄: 옵션
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => ''
        ];
    }

    /**
     * 카다록
     * 1줄: 종류 / 규격
     * 2줄: 인쇄면 / 수량 / 디자인
     */
    private function formatCatalog($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section'])) {
            $name = $item['Section_name'] ?? $this->getKoreanName($item['Section']);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        if (!empty($item['POtype'])) {
            $name = $this->getKoreanName($item['POtype']);
            if ($name) $line2Parts[] = $name;
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatAdditionalOptions($item)
        ];
    }

    /**
     * 포스터
     * 1줄: 구분 / 용지 / 규격
     * 2줄: 수량 / 디자인
     */
    private function formatPoster($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section']) || !empty($item['MY_Fsd'])) {
            $code = $item['Section'] ?? $item['MY_Fsd'];
            $name = $item['Section_name'] ?? $this->getKoreanName($code);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type']);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatAdditionalOptions($item)
        ];
    }

    /**
     * 자석스티커
     * 1줄: 종류 / 규격
     * 2줄: 수량 / 디자인
     */
    private function formatMagnetSticker($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section'])) {
            $name = $item['Section_name'] ?? $this->getKoreanName($item['Section']);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 옵션
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => ''
        ];
    }

    /**
     * NCR양식지
     * 1줄: 타입 / 용지
     * 2줄: 도수 / 수량 / 디자인
     * 필드 매핑: MY_type=도수, PN_type=타입, MY_Fsd=용지
     */
    private function formatNCR($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 타입 / 용지
        if (!empty($item['PN_type'])) {
            $name = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type']);
            if ($name) $line1Parts[] = $name;  // 타입
        }
        if (!empty($item['MY_Fsd'])) {
            $name = $item['MY_Fsd_name'] ?? $this->getKoreanName($item['MY_Fsd']);
            if ($name) $line1Parts[] = $name;  // 용지
        }

        // 2줄: 도수 / 수량 / 디자인
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line2Parts[] = $name;  // 도수
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatPremiumOptions($item)
        ];
    }

    /**
     * 상품권
     * 1줄: 타입 / 용지
     * 2줄: 인쇄면 / 수량 / 디자인
     * 필드: MY_type=종류, Section=재질, POtype=인쇄면
     */
    private function formatVoucher($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 타입 / 용지 (Section 사용!)
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section'])) {
            $name = $item['Section_name'] ?? $this->getKoreanName($item['Section']);
            if ($name) $line1Parts[] = $name;
        }

        // 2줄: 인쇄면 / 수량 / 디자인
        if (!empty($item['POtype'])) {
            $name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');
            $line2Parts[] = $name;
        }
        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => $this->formatPremiumOptions($item)
        ];
    }

    /**
     * 일반 상품
     */
    private function formatGeneric($item) {
        $line1Parts = [];
        $line2Parts = [];

        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['Section'])) {
            $name = $item['Section_name'] ?? $this->getKoreanName($item['Section']);
            if ($name) $line1Parts[] = $name;
        }

        $line2Parts[] = $this->formatQuantity($item);
        $line2Parts[] = $this->formatDesign($item);

        return [
            'line1' => implode(' / ', array_filter($line1Parts)),
            'line2' => implode(' / ', array_filter($line2Parts)),
            'additional' => ''
        ];
    }

    // ========== 공통 헬퍼 ==========

    /**
     * 수량 포맷팅 (규격/사양 텍스트용)
     *
     * ✅ 2026-01-13 Grand Design: QuantityFormatter SSOT 적용
     * 1. 새 스키마 필드 (qty_value, qty_unit_code) 우선 사용
     * 2. 레거시 데이터는 extractFromLegacy()로 변환 후 format() 사용
     */
    private function formatQuantity($item) {
        // ✅ Grand Design: 새 스키마 필드 우선 사용 (SSOT)
        if (!empty($item['qty_value']) && !empty($item['qty_unit_code'])) {
            return QuantityFormatter::format(
                floatval($item['qty_value']),
                $item['qty_unit_code'],
                $item['qty_sheets'] ?? null
            );
        }

        // 레거시 스티커 감지
        $productType = $item['product_type'] ?? '';
        if (empty($productType) && !empty($item['jong']) && !empty($item['garo']) && !empty($item['sero'])) {
            $productType = 'sticker';
        }

        // ✅ Grand Design: 레거시 데이터 → QuantityFormatter 위임
        $extracted = QuantityFormatter::extractFromLegacy($item, $productType);

        // ✅ 2026-01-13: 전단지 매수가 없거나 계산값인 경우 DB에서 조회
        if (in_array($productType, ['inserted', 'leaflet']) && $extracted['qty_value'] > 0) {
            $mesu = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);
            // mesu가 없으면 mlangprintauto_inserted에서 조회 (샛밥 방식)
            if ($mesu === 0 && $this->db) {
                $mesu = $this->lookupInsertedSheets($extracted['qty_value']);
            }
            if ($mesu > 0) {
                $extracted['qty_sheets'] = $mesu;
            }
        }

        // extractFromLegacy가 유효한 값을 반환하면 format() 사용
        if ($extracted['qty_value'] > 0) {
            return QuantityFormatter::format(
                $extracted['qty_value'],
                $extracted['qty_unit_code'],
                $extracted['qty_sheets']
            );
        }

        // 폴백: 기존 레거시 로직 (extractFromLegacy에서 처리하지 못한 경우)
        // 1. 스티커: mesu 최우선 사용
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01'])) {
            if (!empty($item['mesu'])) {
                return number_format(intval($item['mesu'])) . '매';
            }
        }

        // 2. 전단지/리플렛: 연 단위
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $reams = floatval($item['MY_amount'] ?? 0);
            $sheets = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);

            // ✅ 2026-01-13: 매수가 없으면 mlangprintauto_inserted 테이블에서 조회
            if ($sheets === 0 && $reams > 0 && $this->db) {
                $sheets = $this->lookupInsertedSheets($reams);
            }

            if ($reams > 0) {
                $qty = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
                if ($sheets > 0) {
                    $qty .= ' (' . number_format($sheets) . '매)';
                }
                return $qty;
            } elseif ($sheets > 0) {
                return number_format($sheets) . '매';
            }
        }

        // 3. 봉투/명함: 10 미만이면 천 단위로 변환
        if (in_array($productType, ['envelope', 'namecard'])) {
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                $qty_value = $amount > 0 && $amount < 10 ? $amount * 1000 : intval($amount);
                return number_format($qty_value) . '매';
            }
        }

        // 4. 카다록: 부 단위
        if ($productType === 'cadarok') {
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                if (floor($amount) == $amount) {
                    return number_format($amount) . '부';
                }
                return rtrim(rtrim(number_format($amount, 2), '0'), '.') . '부';
            }
        }

        // 5. NCR양식지: 권 단위
        if ($productType === 'ncrflambeau') {
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                if (floor($amount) == $amount) {
                    return number_format($amount) . '권';
                }
                return rtrim(rtrim(number_format($amount, 2), '0'), '.') . '권';
            }
        }

        // 6. 기타: MY_amount 사용
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);
            $unit = $item['unit'] ?? '매';
            return number_format(intval($amount)) . $unit;
        }

        return '';
    }

    /**
     * 소수점 정리된 수량 반환 (2026-01-12)
     * 500.00매 → 500매, 0.50연 → 0.5연
     *
     * @param string $quantity 수량 문자열 (단위 포함)
     * @return string 소수점 정리된 수량
     */
    private function formatDecimalQuantity(string $quantity): string {
        // ✅ 2026-01-13: 전단지 "X연 (Y매)" 패턴 우선 처리 - 전체 패턴 보존
        if (preg_match('/([0-9,\.]+)\s*연\s*\(([0-9,]+)매\)/u', $quantity, $matches)) {
            $yeon = floatval(str_replace(',', '', $matches[1]));
            $maesoo = intval(str_replace(',', '', $matches[2]));
            // 연수 포맷팅: 정수면 소수점 없이, 소수면 불필요한 0 제거
            $yeonFormatted = (floor($yeon) == $yeon)
                ? number_format($yeon)
                : rtrim(rtrim(number_format($yeon, 2), '0'), '.');
            return $yeonFormatted . '연 (' . number_format($maesoo) . '매)';
        }

        // 일반 패턴: "500매", "100부" 등
        if (preg_match('/([0-9,\.]+)\s*([매연부권개장])/u', $quantity, $matches)) {
            $num = floatval(str_replace(',', '', $matches[1]));
            $unit = $matches[2];
            // 정수면 소수점 없이, 소수면 불필요한 0 제거
            if (floor($num) == $num) {
                return number_format($num) . $unit;
            }
            return rtrim(rtrim(number_format($num, 2), '0'), '.') . $unit;
        }
        // 매칭 실패 시 원본 반환
        return $quantity;
    }

    /**
     * ✅ 2026-01-13: 전단지 매수를 mlangprintauto_inserted 테이블에서 조회
     * 기존 방식: 연수로 가격표에서 매수 조회 (샛밥 먹듯이 별도 조회)
     *
     * @param float $reams 연수 (0.5, 1, 2, ...)
     * @return int 매수 (2000, 4000, 8000, ...)
     */
    private function lookupInsertedSheets(float $reams): int {
        if (!$this->db || $reams <= 0) {
            return 0;
        }

        // 캐시 키
        $cacheKey = "inserted_sheets_{$reams}";
        if (isset($this->nameCache[$cacheKey])) {
            return $this->nameCache[$cacheKey];
        }

        // mlangprintauto_inserted 테이블에서 조회
        $stmt = mysqli_prepare($this->db,
            "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "d", $reams);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $sheets = intval($row['quantityTwo']);
                $this->nameCache[$cacheKey] = $sheets;
                mysqli_stmt_close($stmt);
                return $sheets;
            }
            mysqli_stmt_close($stmt);
        }

        // ✅ 2026-01-13: 조회 실패 시 0 반환 (절대 계산하지 않음, DB값만 사용)
        $this->nameCache[$cacheKey] = 0;
        return 0;
    }

    /**
     * 디자인 포맷팅
     */
    private function formatDesign($item) {
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'total' || $ordertype === 'design') {
            return '디자인+인쇄';
        }
        return '인쇄만';
    }

    /**
     * 추가옵션 (전단지/카다록/포스터용: 코팅, 접지, 오시)
     */
    private function formatAdditionalOptions($item) {
        $options = [];

        $coatingNames = [
            'single' => '단면유광', 'double' => '양면유광',
            'single_matte' => '단면무광', 'double_matte' => '양면무광'
        ];
        $foldingNames = [
            '2fold' => '2단접지', '3fold' => '3단접지', '4fold' => '4단접지',
            'zigzag' => '병풍접지', 'gate' => '대문접지', 'zfold' => 'Z접지'
        ];

        // JSON 파싱
        $addOpts = [];
        if (!empty($item['additional_options'])) {
            $addOpts = is_string($item['additional_options'])
                ? json_decode($item['additional_options'], true)
                : $item['additional_options'];
        }

        // 개별 필드에서도 확인
        if ((!empty($item['coating_enabled']) && $item['coating_enabled'] == 1) ||
            (!empty($addOpts['coating_enabled']))) {
            $type = $item['coating_type'] ?? $addOpts['coating_type'] ?? '';
            $name = $coatingNames[$type] ?? $type;
            if ($name) $options[] = '코팅:' . $name;
        }

        if ((!empty($item['folding_enabled']) && $item['folding_enabled'] == 1) ||
            (!empty($addOpts['folding_enabled']))) {
            $type = $item['folding_type'] ?? $addOpts['folding_type'] ?? '';
            $name = $foldingNames[$type] ?? $type;
            if ($name) $options[] = '접지:' . $name;
        }

        if ((!empty($item['creasing_enabled']) && $item['creasing_enabled'] == 1) ||
            (!empty($addOpts['creasing_enabled']))) {
            $lines = $item['creasing_lines'] ?? $addOpts['creasing_lines'] ?? '';
            if ($lines) $options[] = '오시:' . $lines . '줄';
        }

        return implode(' / ', $options);
    }

    /**
     * 프리미엄옵션 (명함/상품권/NCR용: 박, 넘버링, 미싱 등)
     */
    private function formatPremiumOptions($item) {
        $options = [];

        $foilNames = [
            'gold' => '금박', 'gold_matte' => '금무광', 'gold_glossy' => '금유광',
            'silver' => '은박', 'silver_matte' => '은무광', 'silver_glossy' => '은유광',
            'hologram' => '홀로그램', 'red' => '적박', 'blue' => '청박',
            'rose_gold' => '로즈골드', 'copper' => '동박'
        ];

        // JSON 파싱
        $premOpts = [];
        if (!empty($item['premium_options'])) {
            $premOpts = is_string($item['premium_options'])
                ? json_decode($item['premium_options'], true)
                : $item['premium_options'];
        }

        if (!empty($premOpts['foil_enabled'])) {
            $type = $premOpts['foil_type'] ?? '';
            $name = $foilNames[$type] ?? $premOpts['foil_type_name'] ?? $type;
            if ($name) $options[] = '박:' . $name;
        }

        if (!empty($premOpts['numbering_enabled'])) {
            $count = $premOpts['numbering_count'] ?? '';
            $options[] = $count ? '넘버링:' . $count : '넘버링';
        }

        if (!empty($premOpts['perforation_enabled'])) {
            $count = $premOpts['perforation_count'] ?? '';
            $options[] = $count ? '미싱:' . $count : '미싱';
        }

        if (!empty($premOpts['rounding_enabled'])) {
            $options[] = '귀돌이';
        }

        if (!empty($premOpts['creasing_enabled'])) {
            $type = $premOpts['creasing_type'] ?? '';
            $options[] = $type ? '오시:' . $type : '오시';
        }

        return implode(' / ', $options);
    }

    /**
     * 코드번호로 한글 이름 조회 (캐싱)
     */
    public function getKoreanName($code) {
        if (empty($code)) return '';
        if (!is_numeric($code)) return $code;

        if (isset($this->nameCache[$code])) {
            return $this->nameCache[$code];
        }

        $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($this->db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $code);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            $name = $row['title'] ?? '';
            $this->nameCache[$code] = $name;
            return $name;
        }

        return '';
    }

    /**
     * 상품 유형 한글명
     */
    public static function getProductTypeName($type) {
        $types = [
            'sticker' => '스티커',
            'sticker_new' => '스티커',  // ✅ FIX (2026-01-09): 레거시 호환
            'namecard' => '명함',
            'envelope' => '봉투',
            'inserted' => '전단지',
            'leaflet' => '리플렛',
            'cadarok' => '카다록',
            'littleprint' => '포스터',
            'poster' => '포스터',
            'msticker' => '자석스티커',
            'msticker_01' => '자석스티커',
            'ncrflambeau' => 'NCR양식지',
            'merchandisebond' => '상품권',
        ];
        return $types[$type] ?? '기타';
    }

    /**
     * 수량 단위 추출
     *
     * ✅ 2026-01-13 Grand Design: QuantityFormatter SSOT 적용
     */
    public static function getUnit($item) {
        // ✅ Grand Design: 새 스키마 필드 우선 사용
        if (!empty($item['qty_unit_code'])) {
            return QuantityFormatter::getUnitName($item['qty_unit_code']);
        }

        // 레거시: 제품 타입에서 단위 추출
        $productType = $item['product_type'] ?? '';
        return QuantityFormatter::getProductUnitName($productType) ?: ($item['unit'] ?? '부');
    }

    /**
     * 수량 추출 (견적서/장바구니용)
     * 전단지/리플렛: MY_amount를 "연" 단위 그대로 반환 (0.5연)
     * 스티커: mesu를 "매" 단위로 반환 (1000)
     * 기타 상품: MY_amount를 정수로 변환
     */
    public static function getQuantity($item) {
        $productType = $item['product_type'] ?? '';

        // 레거시 스티커 감지
        if (empty($productType) && !empty($item['jong']) && !empty($item['garo']) && !empty($item['sero'])) {
            $productType = 'sticker';
        }

        // 1. 스티커: mesu("매" 단위) 최우선 사용 - productType 명시적 체크
        // ✅ FIX (2026-01-09): sticker_new 추가 (레거시 호환)
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01', 'sticker_new'])) {
            if (!empty($item['mesu'])) {
                return intval($item['mesu']);  // 500, 1000 등 매수
            }
        }

        // 2. 전단지/리플렛: MY_amount("연" 단위) 사용
        if (in_array($productType, ['inserted', 'leaflet'])) {
            if (!empty($item['MY_amount'])) {
                return floatval($item['MY_amount']);  // 0.5, 1, 2 등 그대로
            }
        }

        // 3. 카다록/NCR: 천 단위 변환 없이 그대로 사용
        if (in_array($productType, ['cadarok', 'ncrflambeau'])) {
            if (!empty($item['MY_amount'])) {
                return floatval($item['MY_amount']);  // 1000부, 10권 등 그대로
            }
        }

        // 4. 다른 상품: MY_amount 사용
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);

            // 명함, 봉투 등: 10 미만이면 천 단위로 해석 (1 → 1000)
            if ($amount > 0 && $amount < 10) {
                return intval($amount * 1000);
            }
            return intval($amount);
        }

        // 4. quantity 필드 (폴백)
        if (!empty($item['quantity'])) {
            return intval($item['quantity']);
        }

        return 1;
    }

    /**
     * 수량 표시용 (장바구니 형식)
     * 전단지/리플렛: "0.5연 (250매)" 형식
     * 스티커: "1,000매" 형식
     * 기타: "1,000매" 형식
     *
     * ✅ 2026-01-13 Grand Design: QuantityFormatter SSOT 적용
     */
    public static function getQuantityDisplay($item) {
        // ✅ Grand Design: 새 스키마 필드 우선 사용 (SSOT)
        if (!empty($item['qty_value']) && !empty($item['qty_unit_code'])) {
            return QuantityFormatter::format(
                floatval($item['qty_value']),
                $item['qty_unit_code'],
                $item['qty_sheets'] ?? null
            );
        }

        // 레거시 스티커 감지
        $productType = $item['product_type'] ?? '';
        if (empty($productType) && !empty($item['jong']) && !empty($item['garo']) && !empty($item['sero'])) {
            $productType = 'sticker';
        }

        // ✅ Grand Design: 레거시 데이터 → QuantityFormatter 위임
        $extracted = QuantityFormatter::extractFromLegacy($item, $productType);
        if ($extracted['qty_value'] > 0) {
            return QuantityFormatter::format(
                $extracted['qty_value'],
                $extracted['qty_unit_code'],
                $extracted['qty_sheets']
            );
        }

        // 폴백: 기존 로직 (extractFromLegacy에서 처리하지 못한 경우)
        $unit = self::getUnit($item);

        // ✅ FIX (2026-01-09): 스티커는 quantity_display 무시하고 mesu에서 항상 추출
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01', 'sticker_new'])) {
            if (!empty($item['mesu'])) {
                return number_format(intval($item['mesu'])) . '매';
            }
        }

        // quantity_display 우선 체크 (스티커 제외, 단위가 있는 경우만)
        if (!empty($item['quantity_display']) && preg_match('/[매연부권개장]/u', $item['quantity_display'])) {
            return $item['quantity_display'];
        }

        // 전단지/리플렛: 연 + 매수 표시
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $reams = floatval($item['MY_amount'] ?? 0);
            $sheets = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);

            if ($reams > 0) {
                $display = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
                if ($sheets > 0) {
                    $display .= ' (' . number_format($sheets) . '매)';
                }
                return $display;
            } elseif ($sheets > 0) {
                return number_format($sheets) . '매';
            }
        }

        // 카다록: 부 단위
        if ($productType === 'cadarok') {
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                if (floor($amount) == $amount) {
                    return number_format($amount) . '부';
                }
                return rtrim(rtrim(number_format($amount, 2), '0'), '.') . '부';
            }
        }

        // NCR양식지: 권 단위
        if ($productType === 'ncrflambeau') {
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                if (floor($amount) == $amount) {
                    return number_format($amount) . '권';
                }
                return rtrim(rtrim(number_format($amount, 2), '0'), '.') . '권';
            }
        }

        // 기타: 수량 + 단위
        $qty = self::getQuantity($item);
        return number_format($qty) . $unit;
    }

    /**
     * 가격 추출 (VAT 포함)
     */
    public static function getPrice($item) {
        // VAT 포함 가격 우선
        if (!empty($item['st_price_vat'])) {
            return intval($item['st_price_vat']);
        }
        if (!empty($item['st_price'])) {
            return intval($item['st_price']);
        }
        return 0;
    }


    /**
     * 공급가액 추출 (VAT 제외)
     */
    public static function getSupplyPrice($item) {
        if (!empty($item['st_price'])) {
            return intval($item['st_price']);
        }
        // VAT 포함 가격에서 역산
        if (!empty($item['st_price_vat'])) {
            return intval(round($item['st_price_vat'] / 1.1));
        }
        return 0;
    }
}
