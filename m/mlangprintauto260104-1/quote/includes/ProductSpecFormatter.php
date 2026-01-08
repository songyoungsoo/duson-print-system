<?php
/**
 * 상품 규격 포맷터
 * 스티커, 명함 등 각 상품의 규격 정보를 사람이 읽기 쉬운 형태로 변환
 */

class ProductSpecFormatter {
    private $db;
    private $nameCache = [];  // 코드→이름 캐시

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 상품 유형별 규격 포맷팅
     */
    public function format($item) {
        $productType = $item['product_type'] ?? '';

        // 레거시 스티커 감지: product_type이 없지만 jong, garo, sero가 있으면 스티커
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
     * 스티커 규격 포맷팅
     */
    private function formatSticker($item) {
        $parts = [];

        // 재질 (jong) - "jil " 접두어 제거
        $jong = $item['jong'] ?? '';
        $jong = preg_replace('/^jil\s*/i', '', $jong);
        if (!empty($jong)) {
            $parts[] = '재질: ' . $jong;
        }

        // 크기
        if (!empty($item['garo']) && !empty($item['sero'])) {
            $parts[] = '크기: ' . $item['garo'] . 'mm × ' . $item['sero'] . 'mm';
        }

        // 모양 (domusong) - "00000 " 접두어 제거
        $domusong = $item['domusong'] ?? '';
        $domusong = preg_replace('/^[0\s]+/', '', $domusong);
        if (!empty($domusong) && $domusong !== '0') {
            $parts[] = '모양: ' . $domusong;
        }

        return implode(' / ', $parts);
    }

    /**
     * 명함 규격 포맷팅
     */
    private function formatNamecard($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }
        if (!empty($item['Section'])) {
            $name = $this->getKoreanName($item['Section']);
            if ($name) $parts[] = '재질: ' . $name;
        }
        if (!empty($item['POtype'])) {
            $parts[] = $item['POtype'] == '1' ? '단면' : '양면';
        }

        // 추가 옵션 (박, 넘버링, 미싱, 귀돌이, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 봉투 규격 포맷팅
     */
    private function formatEnvelope($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }
        if (!empty($item['Section'])) {
            $name = $this->getKoreanName($item['Section']);
            if ($name) $parts[] = '재질: ' . $name;
        }

        // 추가 옵션 (박, 넘버링, 미싱, 귀돌이, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 전단지/리플렛 규격 포맷팅
     */
    private function formatLeaflet($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $this->getKoreanName($item['PN_type']);
            if ($name) $parts[] = $name;
        }
        if (!empty($item['MY_Fsd'])) {
            $name = $this->getKoreanName($item['MY_Fsd']);
            if ($name) $parts[] = $name;
        }
        if (!empty($item['POtype'])) {
            $parts[] = $item['POtype'] == '1' ? '단면' : '양면';
        }
        // 수량 정보는 별도 컬럼에 표시되므로 규격에서 제외
        // if (!empty($item['MY_amount'])) {
        //     $qtyPart = '수량: ' . $item['MY_amount'] . '연';
        //     if (!empty($item['mesu'])) {
        //         $qtyPart .= ' (' . number_format($item['mesu']) . '매)';
        //     }
        //     $parts[] = $qtyPart;
        // }

        // 추가 옵션 (코팅, 접지, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 추가 옵션 파싱 (코팅, 접지, 오시, 박, 넘버링, 미싱, 귀돌이)
     * additional_options (전단지/리플렛/카다록/포스터) + premium_options (명함/봉투/양식지/상품권) 모두 지원
     */
    private function parseAdditionalOptions($item) {
        $options = [];

        // 코팅 타입 한글 매핑
        $coatingNames = [
            'single' => '단면유광코팅',
            'double' => '양면유광코팅',
            'single_matte' => '단면무광코팅',
            'double_matte' => '양면무광코팅'
        ];

        // 접지 타입 한글 매핑
        $foldingNames = [
            '2fold' => '2단접지',
            '3fold' => '3단접지',
            '4fold' => '4단접지',
            'zigzag' => '병풍접지',
            'gate' => '대문접지',
            'zfold' => 'Z접지'
        ];

        // 박 타입 한글 매핑
        $foilNames = [
            'gold' => '금박',
            'silver' => '은박',
            'hologram' => '홀로그램',
            'red' => '적박',
            'blue' => '청박'
        ];

        // additional_options JSON 파싱 (전단지/리플렛/카다록/포스터)
        if (!empty($item['additional_options'])) {
            $addOpts = json_decode($item['additional_options'], true);
            if ($addOpts && is_array($addOpts)) {
                // 코팅
                if (!empty($addOpts['coating_enabled']) && !empty($addOpts['coating_type'])) {
                    $coatingType = $addOpts['coating_type'];
                    $coatingName = $coatingNames[$coatingType] ?? $coatingType;
                    $options[] = '코팅: ' . $coatingName;
                }
                // 접지
                if (!empty($addOpts['folding_enabled']) && !empty($addOpts['folding_type'])) {
                    $foldingType = $addOpts['folding_type'];
                    $foldingName = $foldingNames[$foldingType] ?? $foldingType;
                    $options[] = '접지: ' . $foldingName;
                }
                // 오시
                if (!empty($addOpts['creasing_enabled']) && !empty($addOpts['creasing_lines'])) {
                    $options[] = '오시: ' . $addOpts['creasing_lines'] . '줄';
                }
            }
        }

        // premium_options JSON 파싱 (명함/봉투/양식지/상품권)
        if (!empty($item['premium_options'])) {
            $premOpts = json_decode($item['premium_options'], true);
            if ($premOpts && is_array($premOpts)) {
                // 박 (foil)
                if (!empty($premOpts['foil_enabled'])) {
                    $foilType = $premOpts['foil_type'] ?? '';
                    $foilName = $foilNames[$foilType] ?? ($premOpts['foil_type_name'] ?? $foilType);
                    if (!empty($foilName)) {
                        $options[] = '박: ' . $foilName;
                    }
                }
                // 넘버링
                if (!empty($premOpts['numbering_enabled'])) {
                    $numCount = $premOpts['numbering_count'] ?? '';
                    if (!empty($numCount)) {
                        $options[] = '넘버링: ' . $numCount;
                    } else {
                        $options[] = '넘버링';
                    }
                }
                // 미싱 (perforation)
                if (!empty($premOpts['perforation_enabled'])) {
                    $perfCount = $premOpts['perforation_count'] ?? '';
                    if (!empty($perfCount)) {
                        $options[] = '미싱: ' . $perfCount;
                    } else {
                        $options[] = '미싱';
                    }
                }
                // 귀돌이 (rounding)
                if (!empty($premOpts['rounding_enabled'])) {
                    $options[] = '귀돌이';
                }
                // 오시 (premium_options에서도 처리)
                if (!empty($premOpts['creasing_enabled'])) {
                    $creasType = $premOpts['creasing_type'] ?? '';
                    if (!empty($creasType)) {
                        $options[] = '오시: ' . $creasType;
                    } else {
                        $options[] = '오시';
                    }
                }
            }
        }

        // 양면테이프 (봉투 전용 - 개별 컬럼에 저장됨)
        if (!empty($item['envelope_tape_enabled']) && $item['envelope_tape_enabled'] == 1) {
            $tapeQty = intval($item['envelope_tape_quantity'] ?? 0);
            $tapePrice = intval($item['envelope_tape_price'] ?? 0);
            if ($tapeQty > 0) {
                $options[] = '양면테이프: ' . number_format($tapeQty) . '개';
            } elseif ($tapePrice > 0) {
                $options[] = '양면테이프';
            }
        }

        return $options;
    }

    /**
     * 카다록 규격 포맷팅
     */
    private function formatCatalog($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $this->getKoreanName($item['PN_type']);
            if ($name) $parts[] = '크기: ' . $name;
        }

        // 추가 옵션 (코팅, 접지, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 포스터 규격 포맷팅
     */
    private function formatPoster($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '용지: ' . $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $this->getKoreanName($item['PN_type']);
            if ($name) $parts[] = '크기: ' . $name;
        }

        // 추가 옵션 (코팅, 접지, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 자석스티커 규격 포맷팅
     */
    private function formatMagnetSticker($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }
        if (!empty($item['Section'])) {
            $name = $this->getKoreanName($item['Section']);
            if ($name) $parts[] = '구분: ' . $name;
        }

        // 추가 옵션 (코팅, 접지, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * NCR 양식지 규격 포맷팅
     */
    private function formatNCR($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }
        if (!empty($item['Section'])) {
            $name = $this->getKoreanName($item['Section']);
            if ($name) $parts[] = '매수: ' . $name;
        }

        // 추가 옵션 (박, 넘버링, 미싱, 귀돌이, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 상품권 규격 포맷팅
     */
    private function formatVoucher($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '종류: ' . $name;
        }

        // 추가 옵션 (박, 넘버링, 미싱, 귀돌이, 오시)
        $options = $this->parseAdditionalOptions($item);
        if (!empty($options)) {
            $parts = array_merge($parts, $options);
        }

        return implode(' / ', $parts);
    }

    /**
     * 일반 상품 규격 포맷팅
     */
    private function formatGeneric($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            $parts[] = $name ?: $item['MY_type'];
        }
        if (!empty($item['Section'])) {
            $name = $this->getKoreanName($item['Section']);
            if ($name) $parts[] = $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $this->getKoreanName($item['PN_type']);
            if ($name) $parts[] = $name;
        }

        return implode(' / ', $parts);
    }

    /**
     * 코드번호로 한글 이름 조회 (캐싱)
     */
    public function getKoreanName($code) {
        if (empty($code)) return '';

        // 숫자가 아니면 그대로 반환
        if (!is_numeric($code)) return $code;

        // 캐시 확인
        if (isset($this->nameCache[$code])) {
            return $this->nameCache[$code];
        }

        // DB 조회
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

        // 스티커 계열은 "매"
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01'])) {
            return '매';
        }
        // 명함은 "매"
        if ($productType === 'namecard') {
            return '매';
        }
        // 전단지/리플렛은 "연"
        if (in_array($productType, ['inserted', 'leaflet'])) {
            return '연';
        }
        // 기타 인쇄물은 "부"
        return '부';
    }

    /**
     * 수량 추출
     * 전단지/리플렛: MY_amount를 "연" 단위 그대로 반환 (0.5연)
     * 스티커: mesu 우선, 없으면 MY_amount 사용
     * 기타 상품: MY_amount → 10 미만이면 * 1000
     */
    public static function getQuantity($item) {
        $productType = $item['product_type'] ?? '';

        // ✅ 전단지/리플렛은 MY_amount("연" 단위) 우선 사용 (mesu보다 먼저 체크)
        if (in_array($productType, ['inserted', 'leaflet'])) {
            if (!empty($item['MY_amount'])) {
                return floatval($item['MY_amount']);  // 0.5, 1, 2 등 그대로
            }
        }

        // ✅ 스티커 계열: mesu 우선, 없으면 MY_amount 사용
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01'])) {
            // mesu가 있으면 mesu 사용 (계산기에서 전송한 값)
            if (!empty($item['mesu'])) {
                return intval($item['mesu']);
            }
            // mesu가 없으면 MY_amount 사용 (수동 입력 또는 레거시 데이터)
            if (!empty($item['MY_amount'])) {
                $amount = floatval($item['MY_amount']);
                // 10 미만이면 천 단위로 해석 (예: 1 → 1000매)
                if ($amount > 0 && $amount < 10) {
                    return intval($amount * 1000);
                }
                return intval($amount);
            }
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

        // quantity 필드 (quote_items에서만 사용)
        if (!empty($item['quantity'])) {
            return intval($item['quantity']);
        }
        return 1;
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
?>
