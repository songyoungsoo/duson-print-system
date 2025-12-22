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

        return implode(' / ', $parts);
    }

    /**
     * 전단지/리플렛 규격 포맷팅
     */
    private function formatLeaflet($item) {
        $parts = [];

        if (!empty($item['MY_type'])) {
            $name = $this->getKoreanName($item['MY_type']);
            if ($name) $parts[] = '용지: ' . $name;
        }
        if (!empty($item['PN_type'])) {
            $name = $this->getKoreanName($item['PN_type']);
            if ($name) $parts[] = '크기: ' . $name;
        }
        if (!empty($item['MY_Fsd'])) {
            $name = $this->getKoreanName($item['MY_Fsd']);
            if ($name) $parts[] = '평량: ' . $name;
        }
        if (!empty($item['POtype'])) {
            $parts[] = $item['POtype'] == '1' ? '단면' : '양면';
        }

        return implode(' / ', $parts);
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
     * 기타 상품: 정수로 변환
     */
    public static function getQuantity($item) {
        $productType = $item['product_type'] ?? '';

        // 스티커는 mesu 사용
        if (!empty($item['mesu'])) {
            return intval($item['mesu']);
        }

        // 다른 상품은 MY_amount 사용
        if (!empty($item['MY_amount'])) {
            $amount = floatval($item['MY_amount']);

            // 전단지/리플렛은 "연" 단위 소수점 그대로 반환
            if (in_array($productType, ['inserted', 'leaflet'])) {
                return $amount;  // 0.5, 1, 2 등 그대로
            }

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
