<?php
/**
 * SpecDisplayService - 통합 출력 서비스
 *
 * 모든 화면(장바구니, 주문, 완료, 관리자, PDF, 이메일)에서
 * 동일한 출력 데이터를 생성하는 단일 권한 클래스
 *
 * 핵심 원칙:
 * 1. Phase 3 표준 필드 우선 사용
 * 2. 레거시 필드는 폴백으로만 사용
 * 3. quantity_display는 항상 단위 포함 상태로 출력
 * 4. 가격은 DB 값 그대로 사용 (역계산 금지)
 *
 * @version 1.0.0
 * @since 2026-01-08
 */

require_once __DIR__ . '/ProductSpecFormatter.php';

class SpecDisplayService {
    private $db;
    private $formatter;

    // 단위 정규식 패턴 (한글 단위)
    const UNIT_PATTERN = '/[매연부권개장]/u';

    // 제품별 기본 단위
    const PRODUCT_UNITS = [
        'inserted' => '연',
        'leaflet' => '연',
        'sticker' => '매',
        'msticker' => '매',
        'msticker_01' => '매',
        'namecard' => '매',
        'envelope' => '매',
        'cadarok' => '부',
        'littleprint' => '장',
        'poster' => '장',
        'ncrflambeau' => '권',
        'merchandisebond' => '매'
    ];

    // 제품별 한글명
    const PRODUCT_NAMES = [
        'sticker' => '스티커',
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
        'merchandisebond' => '상품권'
    ];

    public function __construct($db) {
        $this->db = $db;
        $this->formatter = new ProductSpecFormatter($db);
    }

    /**
     * 통합 출력 데이터 생성 (모든 화면 공통)
     *
     * @param array $item 상품 데이터 (shop_temp, quotation_temp, quote_items, mlangorder_printauto)
     * @return array 통합 출력 데이터
     */
    public function getDisplayData(array $item): array {
        // 1. 데이터 정규화 (Type_1 JSON 파싱 등)
        $normalized = $this->normalizeItem($item);

        // 2. 제품 타입 및 이름
        $productType = $normalized['product_type'] ?? '';
        $productName = $this->getProductName($normalized);

        // 3. 사양 라인 생성 (ProductSpecFormatter 활용)
        $specs = $this->formatter->format($normalized);

        // 4. quantity_display 보정 (단위 필수)
        $quantityDisplay = $this->ensureQuantityUnit($normalized);

        // 5. 단위 추출
        $unit = $this->getUnit($normalized);

        // 6. 가격 정보 (DB 값 그대로, 역계산 금지)
        $priceData = $this->getPriceData($normalized);

        return [
            // 기본 정보
            'product_type' => $productType,
            'product_name' => $productName,

            // 사양 라인 (2줄 형식)
            'line1' => $specs['line1'] ?? '',
            'line2' => $specs['line2'] ?? '',
            'additional' => $specs['additional'] ?? '',

            // 통합 포맷 (4줄 형식, 견적서/주문서용)
            'unified' => $this->formatter->formatUnified($normalized),

            // 수량 정보
            'quantity_display' => $quantityDisplay,
            'quantity_value' => $this->getQuantityValue($normalized),
            'quantity_sheets' => intval($normalized['mesu'] ?? $normalized['quantity_sheets'] ?? 0),
            'unit' => $unit,

            // 가격 정보 (DB값 그대로)
            'price_supply' => $priceData['supply'],
            'price_vat_amount' => $priceData['vat_amount'],
            'price_total' => $priceData['total'],

            // 수기 입력 여부
            'is_manual_entry' => !empty($normalized['is_manual_entry']),

            // 디자인 여부
            'design_type' => $this->getDesignType($normalized),

            // 원본 데이터 (필요시 참조)
            '_raw' => $normalized
        ];
    }

    /**
     * HTML 출력용 (장바구니, 주문 페이지)
     *
     * @param array $item 상품 데이터
     * @return string HTML 문자열
     */
    public function getHtml(array $item): string {
        $data = $this->getDisplayData($item);

        $html = '';
        if (!empty($data['line1'])) {
            $html .= htmlspecialchars($data['line1']);
        }
        if (!empty($data['line2'])) {
            $html .= '<br>' . htmlspecialchars($data['line2']);
        }
        if (!empty($data['additional'])) {
            $html .= '<br><span style="color:#666;font-size:12px;">' .
                     htmlspecialchars($data['additional']) . '</span>';
        }

        return $html;
    }

    /**
     * 텍스트 출력용 (이메일, PDF)
     *
     * @param array $item 상품 데이터
     * @return string 텍스트 문자열 (줄바꿈)
     */
    public function getText(array $item): string {
        $data = $this->getDisplayData($item);

        $lines = [];
        if (!empty($data['line1'])) $lines[] = $data['line1'];
        if (!empty($data['line2'])) $lines[] = $data['line2'];
        if (!empty($data['additional'])) $lines[] = $data['additional'];

        return implode("\n", $lines);
    }

    /**
     * 단일 행 출력용 (관리자 목록, 엑셀)
     *
     * @param array $item 상품 데이터
     * @return string 단일 행 문자열
     */
    public function getSingleLine(array $item): string {
        $data = $this->getDisplayData($item);

        $parts = [];
        if (!empty($data['line1'])) $parts[] = $data['line1'];
        if (!empty($data['line2'])) $parts[] = $data['line2'];

        return implode(' | ', $parts);
    }

    /**
     * 수기 입력 품목 데이터 생성
     *
     * @param array $input 사용자 입력 (product_name, specification, quantity_display, price_supply)
     * @return array 정규화된 수기 입력 데이터
     */
    public function createManualEntryData(array $input): array {
        $productName = trim($input['product_name'] ?? '');
        $specification = trim($input['specification'] ?? '');
        $quantityDisplay = trim($input['quantity_display'] ?? '');
        $priceSupply = intval($input['price_supply'] ?? 0);

        // quantity_display에 단위 없으면 '개' 추가
        if (!empty($quantityDisplay) && !preg_match(self::UNIT_PATTERN, $quantityDisplay)) {
            $quantityDisplay .= '개';
        }

        // VAT 계산 (출력용)
        $vatAmount = intval(round($priceSupply * 0.1));
        $total = $priceSupply + $vatAmount;

        return [
            'product_type' => 'manual',
            'product_name' => $productName,
            'specification' => $specification,
            'quantity_display' => $quantityDisplay,
            'quantity_value' => $this->extractQuantityValue($quantityDisplay),
            'unit' => $this->extractUnit($quantityDisplay),
            'price_supply' => $priceSupply,
            'price_vat_amount' => $vatAmount,
            'price_total' => $total,
            'is_manual_entry' => 1,
            'data_version' => 2
        ];
    }

    /**
     * 데이터 정규화 (Type_1 JSON 파싱 등)
     */
    private function normalizeItem(array $item): array {
        // Type_1 JSON 파싱 (mlangorder_printauto에서 가져온 경우)
        if (!empty($item['Type_1']) && is_string($item['Type_1'])) {
            $type1Data = json_decode($item['Type_1'], true);
            if ($type1Data && is_array($type1Data)) {
                // nested order_details 처리 (레거시 스티커)
                if (isset($type1Data['order_details']) && is_array($type1Data['order_details'])) {
                    $item = array_merge($item, $type1Data['order_details'], $type1Data);
                    unset($item['order_details']);
                } else {
                    $item = array_merge($item, $type1Data);
                }
            } else {
                // JSON 실패 시 레거시 텍스트 파싱
                $legacyData = $this->parseLegacyType1($item['Type_1'], $item);
                if (!empty($legacyData)) {
                    $item = array_merge($item, $legacyData);
                }
            }
        }

        // product_data JSON 파싱 (quote_items)
        if (!empty($item['product_data']) && is_string($item['product_data'])) {
            $productData = json_decode($item['product_data'], true);
            if ($productData && is_array($productData)) {
                $item = array_merge($item, $productData);
            }
        }

        // 레거시 스티커 감지
        if (empty($item['product_type']) && !empty($item['jong']) && !empty($item['garo']) && !empty($item['sero'])) {
            $item['product_type'] = 'sticker';
        }

        return $item;
    }

    /**
     * 레거시 Type_1 텍스트 파싱 (프로덕션 호환)
     *
     * 프로덕션 DB에서 Type_1이 JSON이 아닌 텍스트로 저장된 경우 처리
     * 예: "명함종류: 일반명함(쿠폰)\n명함재질: \n인쇄면: 단면\n수량: 500.00매"
     *
     * @param string $text Type_1 텍스트
     * @param array $item 원본 아이템 데이터
     * @return array 파싱된 데이터
     */
    private function parseLegacyType1(string $text, array $item): array {
        $result = [];

        // 줄바꿈으로 분리
        $lines = preg_split('/[\r\n]+/', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // "키: 값" 형식 파싱
            if (preg_match('/^(.+?):\s*(.*)$/', $line, $matches)) {
                $key = trim($matches[1]);
                $value = trim($matches[2]);

                // 명함
                if ($key === '명함종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'namecard';
                    }
                }
                elseif ($key === '명함재질') {
                    $result['spec_material'] = $value;
                }
                // 봉투
                elseif ($key === '봉투종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'envelope';
                    }
                }
                elseif ($key === '봉투재질') {
                    $result['spec_material'] = $value;
                }
                // 스티커
                elseif ($key === '스티커종류' || $key === '종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'sticker';
                    }
                }
                elseif ($key === '스티커재질' || $key === '재질') {
                    $result['spec_material'] = $value;
                }
                // 카다록
                elseif ($key === '카다록종류' || $key === '리플렛종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'cadarok';
                    }
                }
                // 전단지
                elseif ($key === '전단지종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'inserted';
                    }
                }
                // 포스터
                elseif ($key === '포스터종류') {
                    $result['spec_type'] = $value;
                    if (empty($item['product_type'])) {
                        $result['product_type'] = 'littleprint';
                    }
                }
                // ✅ 2026-01-12: 양식지/NCR, 카다록 레거시 필드 추가
                // 구분 (양식지/NCR, 카다록 공통)
                elseif ($key === '구분') {
                    $result['spec_type'] = $value;
                    // product_type 감지
                    if (empty($item['product_type'])) {
                        if (strpos($value, '양식') !== false || strpos($value, 'NCR') !== false) {
                            $result['product_type'] = 'ncrflambeau';
                        } elseif (strpos($value, '카다록') !== false || strpos($value, '리플렛') !== false) {
                            $result['product_type'] = 'cadarok';
                        }
                    }
                }
                // 규격 (양식지/NCR: 계약서(A4).기타서식(A4) 등)
                elseif ($key === '규격') {
                    if (!empty($value)) {
                        $result['spec_size'] = $value;
                    }
                }
                // 색상 (양식지/NCR: 1도, 2도 등)
                elseif ($key === '색상') {
                    if (!empty($value)) {
                        $result['spec_sides'] = $value;
                    }
                }
                // 종이종류 (카다록)
                elseif ($key === '종이종류') {
                    if (!empty($value)) {
                        $result['spec_material'] = $value;
                    }
                }
                // 주문방법 (카다록: 인쇄만/디자인+인쇄)
                elseif ($key === '주문방법') {
                    $result['spec_design'] = $value;
                }
                // 공통
                elseif ($key === '인쇄면') {
                    $result['spec_sides'] = $value;
                }
                elseif ($key === '편집디자인' || $key === '디자인') {
                    $result['spec_design'] = $value;
                }
                elseif ($key === '용지' || $key === '용지종류') {
                    $result['spec_material'] = $value;
                }
                elseif ($key === '크기' || $key === '사이즈') {
                    $result['spec_size'] = $value;
                }
                // 수량 파싱 (예: "500.00매", "1,000부")
                elseif ($key === '수량') {
                    // ✅ 2026-01-12: 소수점 정리 후 quantity_display 설정
                    // 예: "10.00권" → "10권", "500.00매" → "500매", "0.50연" → "0.5연"
                    $cleanedValue = $value;
                    if (preg_match('/([0-9,\.]+)\s*([매연부권개장])/u', $value, $matches)) {
                        $num = floatval(str_replace(',', '', $matches[1]));
                        $unit = $matches[2];
                        // 정수면 소수점 없이, 소수면 불필요한 0 제거
                        if (floor($num) == $num) {
                            $cleanedValue = number_format($num) . $unit;
                        } else {
                            $cleanedValue = rtrim(rtrim(number_format($num, 2), '0'), '.') . $unit;
                        }
                    }
                    $result['quantity_display'] = $cleanedValue;

                    // 숫자 추출
                    if (preg_match('/([0-9,\.]+)/', $value, $qtyMatch)) {
                        $result['quantity_value'] = floatval(str_replace(',', '', $qtyMatch[1]));
                    }
                    // 단위 추출
                    if (preg_match('/([매연부권개장])/u', $value, $unitMatch)) {
                        $result['quantity_unit'] = $unitMatch[1];
                    }
                }
            }
        }

        // Type에서 product_type 감지 (폴백)
        if (empty($result['product_type']) && !empty($item['Type'])) {
            $type = $item['Type'];
            if (strpos($type, '명함') !== false) {
                $result['product_type'] = 'namecard';
            } elseif (strpos($type, '봉투') !== false) {
                $result['product_type'] = 'envelope';
            } elseif (strpos($type, '스티커') !== false) {
                $result['product_type'] = 'sticker';
            } elseif (strpos($type, '자석') !== false) {
                $result['product_type'] = 'msticker';
            } elseif (strpos($type, '전단') !== false) {
                $result['product_type'] = 'inserted';
            } elseif (strpos($type, '카다록') !== false || strpos($type, '리플렛') !== false) {
                $result['product_type'] = 'cadarok';
            } elseif (strpos($type, '포스터') !== false) {
                $result['product_type'] = 'littleprint';
            } elseif (strpos($type, 'NCR') !== false || strpos($type, '양식') !== false) {
                $result['product_type'] = 'ncrflambeau';
            } elseif (strpos($type, '상품권') !== false) {
                $result['product_type'] = 'merchandisebond';
            }
        }

        return $result;
    }

    /**
     * quantity_display에 단위가 없으면 추가, 소수점 정리
     *
     * ✅ 2026-01-13: 전단지 "X연 (Y매)" 패턴 보존
     * ✅ 2026-01-13: 전단지 매수가 없으면 mlangprintauto_inserted에서 조회
     */
    private function ensureQuantityUnit(array $item): string {
        // Phase 3 필드 우선
        $display = $item['quantity_display'] ?? '';
        $productType = $item['product_type'] ?? '';

        // 이미 단위가 있는 경우
        if (!empty($display) && preg_match(self::UNIT_PATTERN, $display)) {
            // ✅ 전단지 패턴: "0.5연 (2,000매)" - 전체 패턴 보존
            if (preg_match('/([0-9,\.]+)\s*연\s*\(([0-9,]+)매\)/u', $display, $matches)) {
                $yeon = floatval(str_replace(',', '', $matches[1]));
                $maesoo = intval(str_replace(',', '', $matches[2]));
                // 연수 소수점 정리
                $yeonFormatted = (floor($yeon) == $yeon)
                    ? number_format($yeon)
                    : rtrim(rtrim(number_format($yeon, 2), '0'), '.');
                return $yeonFormatted . '연 (' . number_format($maesoo) . '매)';
            }

            // ✅ 2026-01-14: DB 기반 보조수량 필요 여부 확인 (product_unit_config 테이블 사용)
            if (QuantityFormatter::needsSubQuantity($this->db, $productType) &&
                preg_match('/^([0-9,\.]+)\s*연$/u', trim($display), $matches)) {
                $yeon = floatval(str_replace(',', '', $matches[1]));
                $maesoo = $this->lookupInsertedSheets($yeon);
                $yeonFormatted = (floor($yeon) == $yeon)
                    ? number_format($yeon)
                    : rtrim(rtrim(number_format($yeon, 2), '0'), '.');
                if ($maesoo > 0) {
                    return $yeonFormatted . '연 (' . number_format($maesoo) . '매)';
                }
                return $yeonFormatted . '연';
            }

            // 일반 패턴: 숫자와 단위 분리 후 재포맷
            if (preg_match('/([0-9,\.]+)\s*([매연부권개장])/u', $display, $matches)) {
                $num = floatval(str_replace(',', '', $matches[1]));
                $unit = $matches[2];
                // 정수면 소수점 없이, 소수면 불필요한 0 제거
                if (floor($num) == $num) {
                    return number_format($num) . $unit;
                }
                return rtrim(rtrim(number_format($num, 2), '0'), '.') . $unit;
            }
            return $display;
        }

        // 단위가 없으면 ProductSpecFormatter의 getQuantityDisplay 사용
        return ProductSpecFormatter::getQuantityDisplay($item);
    }

    /**
     * ✅ 2026-01-13: 전단지 매수를 mlangprintauto_inserted 테이블에서 조회
     * (절대 계산하지 않음, DB값만 사용)
     */
    private function lookupInsertedSheets(float $reams): int {
        if (!$this->db || $reams <= 0) {
            return 0;
        }

        $stmt = mysqli_prepare($this->db,
            "SELECT quantityTwo FROM mlangprintauto_inserted WHERE quantity = ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "d", $reams);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $sheets = intval($row['quantityTwo']);
                mysqli_stmt_close($stmt);
                return $sheets;
            }
            mysqli_stmt_close($stmt);
        }

        return 0;  // 조회 실패 시 0 반환 (계산하지 않음)
    }

    /**
     * 수량 값 추출 (숫자만)
     */
    private function getQuantityValue(array $item): float {
        // Phase 3 필드 우선
        if (!empty($item['quantity_value'])) {
            return floatval($item['quantity_value']);
        }

        return ProductSpecFormatter::getQuantity($item);
    }

    /**
     * 단위 추출
     * 우선순위: quantity_unit_phase3 > quantity_unit > PRODUCT_UNITS > unit > 기본값
     */
    private function getUnit(array $item): string {
        // 1. Phase 3 필드 우선
        if (!empty($item['quantity_unit_phase3'])) {
            return $item['quantity_unit_phase3'];
        }

        // 2. quantity_unit (파싱된 데이터)
        if (!empty($item['quantity_unit'])) {
            return $item['quantity_unit'];
        }

        // 3. 제품 타입 기반 단위 (DB unit 컬럼보다 우선)
        // ✅ 2026-01-12: DB unit 컬럼에 잘못된 기본값('매')이 있을 수 있음
        $productType = $item['product_type'] ?? '';
        if (!empty($productType) && isset(self::PRODUCT_UNITS[$productType])) {
            return self::PRODUCT_UNITS[$productType];
        }

        // 4. DB unit 컬럼 (제품 타입 없을 때만)
        if (!empty($item['unit'])) {
            return $item['unit'];
        }

        return '개';
    }

    /**
     * 가격 데이터 추출 (DB값 그대로, 역계산 금지)
     */
    private function getPriceData(array $item): array {
        // Phase 3 필드 우선
        $supply = 0;
        $total = 0;

        // 공급가액
        if (!empty($item['price_supply'])) {
            $supply = intval($item['price_supply']);
        } elseif (!empty($item['price_supply_phase3'])) {
            $supply = intval($item['price_supply_phase3']);
        } elseif (!empty($item['supply_price'])) {
            $supply = intval($item['supply_price']);
        } elseif (!empty($item['st_price'])) {
            $supply = intval($item['st_price']);
        } elseif (!empty($item['money_4'])) {
            $supply = intval($item['money_4']);
        }

        // 합계금액 (VAT 포함)
        if (!empty($item['price_vat'])) {
            $total = intval($item['price_vat']);
        } elseif (!empty($item['total_price'])) {
            $total = intval($item['total_price']);
        } elseif (!empty($item['st_price_vat'])) {
            $total = intval($item['st_price_vat']);
        } elseif (!empty($item['money_5'])) {
            $total = intval($item['money_5']);
        }

        // VAT 금액
        $vatAmount = 0;
        if (!empty($item['price_vat_amount'])) {
            $vatAmount = intval($item['price_vat_amount']);
        } elseif (!empty($item['price_vat_amount_phase3'])) {
            $vatAmount = intval($item['price_vat_amount_phase3']);
        } elseif (!empty($item['vat_amount'])) {
            $vatAmount = intval($item['vat_amount']);
        } elseif ($total > 0 && $supply > 0) {
            // 합계와 공급가가 있으면 차이로 계산
            $vatAmount = $total - $supply;
        } elseif ($supply > 0) {
            // 공급가만 있으면 10%로 계산 (출력용)
            $vatAmount = intval(round($supply * 0.1));
            $total = $supply + $vatAmount;
        }

        return [
            'supply' => $supply,
            'vat_amount' => $vatAmount,
            'total' => $total
        ];
    }

    /**
     * 제품명 추출
     */
    private function getProductName(array $item): string {
        // 명시적 제품명
        if (!empty($item['product_name'])) {
            return $item['product_name'];
        }

        // Type 필드 (mlangorder_printauto)
        if (!empty($item['Type'])) {
            return $item['Type'];
        }

        // product_type으로 한글명 변환
        $productType = $item['product_type'] ?? '';
        return self::PRODUCT_NAMES[$productType] ?? '기타';
    }

    /**
     * 디자인 타입 추출
     */
    private function getDesignType(array $item): string {
        // spec_design 필드 우선
        if (!empty($item['spec_design'])) {
            return $item['spec_design'];
        }

        // ordertype 필드
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'total' || $ordertype === 'design') {
            return '디자인+인쇄';
        }

        return '인쇄만';
    }

    /**
     * 문자열에서 숫자만 추출
     */
    private function extractQuantityValue(string $display): float {
        // 쉼표 제거 후 숫자 추출
        $cleaned = str_replace(',', '', $display);
        preg_match('/[\d.]+/', $cleaned, $matches);
        return floatval($matches[0] ?? 0);
    }

    /**
     * 문자열에서 단위 추출
     */
    private function extractUnit(string $display): string {
        preg_match(self::UNIT_PATTERN, $display, $matches);
        return $matches[0] ?? '개';
    }

    /**
     * 장바구니 항목 목록 변환
     *
     * @param array $items 장바구니 항목 배열
     * @return array 변환된 항목 배열
     */
    public function transformCartItems(array $items): array {
        $result = [];
        foreach ($items as $item) {
            $result[] = $this->getDisplayData($item);
        }
        return $result;
    }

    /**
     * 견적서 항목 변환 (견적서 테이블용)
     *
     * @param array $item 원본 데이터
     * @return array 견적서 저장용 데이터
     */
    public function prepareForQuote(array $item): array {
        $display = $this->getDisplayData($item);
        $unified = $display['unified'];

        return [
            'product_type' => $display['product_type'],
            'product_name' => $display['product_name'],
            'specification' => $display['line1'] . ($display['line2'] ? ' / ' . $display['line2'] : ''),
            'quantity' => $display['quantity_value'],
            'unit' => $display['unit'],
            'quantity_display' => $display['quantity_display'],
            'supply_price' => $display['price_supply'],
            'vat_amount' => $display['price_vat_amount'],
            'total_price' => $display['price_total'],
            'is_manual_entry' => $display['is_manual_entry'] ? 1 : 0,

            // 통합 4줄 포맷
            'formatted_display' => implode("\n", array_filter([
                $unified['line1'],
                $unified['line2'],
                $unified['line3'],
                $unified['line4']
            ])),

            // Phase 3 표준 필드
            'spec_type' => $item['spec_type'] ?? '',
            'spec_material' => $item['spec_material'] ?? '',
            'spec_size' => $item['spec_size'] ?? '',
            'spec_sides' => $item['spec_sides'] ?? '',
            'spec_design' => $display['design_type'],
            'quantity_value' => $display['quantity_value'],
            'quantity_unit_phase3' => $display['unit'],
            'quantity_sheets' => $item['quantity_sheets'] ?? $item['mesu'] ?? 0,
            'price_supply_phase3' => $display['price_supply'],
            'price_vat' => $display['price_total'],
            'price_vat_amount_phase3' => $display['price_vat_amount'],
            'data_version' => 2
        ];
    }

    /**
     * 가격 요약 계산 (장바구니/견적서 합계용)
     *
     * @param array $items 항목 배열
     * @return array ['supply_total', 'vat_total', 'grand_total']
     */
    public function calculatePriceSummary(array $items): array {
        $supplyTotal = 0;
        $vatTotal = 0;

        foreach ($items as $item) {
            $data = is_array($item) && isset($item['price_supply'])
                ? $item
                : $this->getDisplayData($item);

            $supplyTotal += intval($data['price_supply'] ?? 0);
            $vatTotal += intval($data['price_vat_amount'] ?? 0);
        }

        return [
            'supply_total' => $supplyTotal,
            'vat_total' => $vatTotal,
            'grand_total' => $supplyTotal + $vatTotal
        ];
    }
}
