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
 */

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
        $productType = $item['product_type'] ?? '';

        // Type_1 JSON 파싱 (mlangorder_printauto에서 가져온 경우)
        if (!empty($item['Type_1']) && is_string($item['Type_1'])) {
            $type1Data = json_decode($item['Type_1'], true);
            if ($type1Data && is_array($type1Data)) {
                $item = array_merge($item, $type1Data);
                $productType = $type1Data['product_type'] ?? $productType;
            }
        }

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
     * 1줄: 종류 / 용지
     * 2줄: 인쇄 / 수량 / 디자인
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
            if ($name) $line2Parts[] = $name;
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
     * 1줄: 재질 / 크기 / 모양
     * 2줄: 수량 / 디자인
     */
    private function formatSticker($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        $jong = $item['jong'] ?? '';
        $jong = preg_replace('/^jil\s*/i', '', $jong);
        if (!empty($jong)) $line1Parts[] = $jong;

        if (!empty($item['garo']) && !empty($item['sero'])) {
            $line1Parts[] = $item['garo'] . 'mm x ' . $item['sero'] . 'mm';
        }

        $domusong = $item['domusong'] ?? '';
        $domusong = preg_replace('/^[0\s]+/', '', $domusong);
        if (!empty($domusong) && $domusong !== '0') {
            $line1Parts[] = $domusong;
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
     * 1줄: 규격 / 용도
     * 2줄: 인쇄도수 / 수량 / 디자인
     */
    private function formatNCR($item) {
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
        if (!empty($item['PN_type'])) {
            $name = $item['PN_type_name'] ?? $this->getKoreanName($item['PN_type']);
            if ($name) $line2Parts[] = $name;
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
     * 1줄: 종류 / 인쇄면
     * 2줄: 수량 / 디자인
     */
    private function formatVoucher($item) {
        $line1Parts = [];
        $line2Parts = [];

        // 1줄: 규격
        if (!empty($item['MY_type'])) {
            $name = $item['MY_type_name'] ?? $this->getKoreanName($item['MY_type']);
            if ($name) $line1Parts[] = $name;
        }
        if (!empty($item['POtype'])) {
            $name = $item['POtype_name'] ?? ($item['POtype'] == '1' ? '단면' : '양면');
            $line1Parts[] = $name;
        }

        // 2줄: 옵션
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
     * 수량 포맷팅
     */
    private function formatQuantity($item) {
        $productType = $item['product_type'] ?? '';

        // 전단지/리플렛: 연 단위
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $reams = floatval($item['MY_amount'] ?? 0);
            $sheets = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);

            if ($reams > 0) {
                // 연수가 있으면 "X연 (Y매)" 형식
                $qty = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
                if ($sheets > 0) {
                    $qty .= ' (' . number_format($sheets) . '매)';
                }
                return $qty;
            } elseif ($sheets > 0) {
                // 연수 없이 매수만 있으면 "X매" 형식
                return number_format($sheets) . '매';
            }
        }

        // 스티커: mesu 사용
        if (!empty($item['mesu'])) {
            return number_format(intval($item['mesu'])) . '매';
        }

        // 기타: MY_amount 사용
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);
            $unit = $item['unit'] ?? '매';
            return number_format(intval($amount)) . $unit;
        }

        return '';
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
            'gold' => '금박', 'silver' => '은박', 'hologram' => '홀로그램',
            'red' => '적박', 'blue' => '청박'
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
     */
    public static function getUnit($item) {
        $productType = $item['product_type'] ?? '';

        if (in_array($productType, ['inserted', 'leaflet'])) {
            return '연';
        }
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01', 'namecard'])) {
            return '매';
        }
        return $item['unit'] ?? '부';
    }

    /**
     * 수량 추출 (견적서/장바구니용)
     * 전단지/리플렛: MY_amount를 "연" 단위 그대로 반환 (0.5연)
     * 기타 상품: 정수로 변환
     */
    public static function getQuantity($item) {
        $productType = $item['product_type'] ?? '';

        // 전단지/리플렛은 MY_amount("연" 단위) 우선 사용
        if (in_array($productType, ['inserted', 'leaflet'])) {
            if (!empty($item['MY_amount'])) {
                return floatval($item['MY_amount']);  // 0.5, 1, 2 등 그대로
            }
        }

        // 스티커는 mesu 사용
        if (!empty($item['mesu'])) {
            return intval($item['mesu']);
        }

        // 다른 상품은 MY_amount 사용
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);

            // 기타 상품: 10 미만이면 천 단위로 해석
            if ($amount > 0 && $amount < 10) {
                return intval($amount * 1000);
            }
            return intval($amount);
        }

        // quantity 필드
        if (!empty($item['quantity'])) {
            return intval($item['quantity']);
        }
        return 1;
    }

    /**
     * 수량 표시용 (장바구니 형식)
     * 전단지/리플렛: "0.5연 (250매)" 형식
     * 기타: "1,000매" 형식
     */
    public static function getQuantityDisplay($item) {
        $productType = $item['product_type'] ?? '';
        $unit = self::getUnit($item);

        // 전단지/리플렛: 연 + 매수 표시
        if (in_array($productType, ['inserted', 'leaflet'])) {
            $reams = floatval($item['MY_amount'] ?? 0);
            $sheets = intval($item['mesu'] ?? $item['quantityTwo'] ?? 0);

            if ($reams > 0) {
                // 두손기획 비즈니스 규칙: 0.5연만 소수점, 나머지 정수
                if ($reams == 0.5) {
                    $display = '0.5연';
                } else {
                    $display = number_format(intval($reams), 0) . '연';
                }

                if ($sheets > 0) {
                    $display .= ' (' . number_format(intval($sheets), 0) . '매)';
                }
                return $display;
            } elseif ($sheets > 0) {
                // 연수 없이 매수만 있으면 "X매" 형식
                return number_format(intval($sheets), 0) . '매';
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
