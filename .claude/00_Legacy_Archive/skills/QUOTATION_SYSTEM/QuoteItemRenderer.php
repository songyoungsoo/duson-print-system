<?php
/**
 * QuoteItemRenderer.php
 * 견적서 품목 공통 렌더러
 * 
 * PDF, 이메일, 웹 미리보기에서 동일하게 사용하여
 * 모든 출력물의 일관성을 보장합니다.
 * 
 * @version 1.0
 * @date 2025-12-27
 */

class QuoteItemRenderer {
    
    private $db;
    
    /**
     * 9개 취급품목 한글명
     * 
     * ★ 품목 타입 매핑 규칙:
     * - 스티커: sticker_new (정식), sticker (레거시)
     * - 전단지: inserted (정식)
     * - 명함: namecard (정식)
     * - 봉투: envelope (정식)
     * - 카다로그: cadarok (정식), leaflet (이미지 가져올 때만)
     * - 포스터: littleprint (정식), poster (매핑용)
     * - 상품권: merchandisebond (정식)
     * - 자석스티커: msticker (정식, 별도)
     * - NCR양식: ncrflambeau (정식)
     */
    private static $productTypeNames = [
        // 스티커
        'sticker_new' => '스티커',
        'sticker' => '스티커',           // 레거시 호환
        
        // 전단지
        'inserted' => '전단지',
        
        // 명함
        'namecard' => '명함',
        
        // 봉투
        'envelope' => '봉투',
        
        // 카다로그
        'cadarok' => '카다로그',
        'leaflet' => '카다로그',         // 이미지 매핑용
        
        // 포스터
        'littleprint' => '포스터',       // 정식 명칭
        'poster' => '포스터',            // 매핑용
        
        // 상품권
        'merchandisebond' => '상품권',
        
        // 자석스티커 (별도)
        'msticker' => '자석스티커',
        
        // NCR양식
        'ncrflambeau' => 'NCR양식',
        
        // 임의 입력
        'manual' => '품목'
    ];
    
    /**
     * 품목 타입 정규화 (레거시 → 정식 명칭)
     */
    private static $typeNormalization = [
        'sticker' => 'sticker_new',      // 레거시 스티커 → 정식
        'poster' => 'littleprint',       // poster → 정식
        'leaflet' => 'cadarok'           // leaflet → 정식 (이미지용)
    ];
    
    /**
     * 품목별 기본 단위
     */
    private static $defaultUnits = [
        // 스티커
        'sticker_new' => '매',
        'sticker' => '매',
        
        // 전단지
        'inserted' => '연',
        
        // 명함
        'namecard' => '매',
        
        // 봉투
        'envelope' => '매',
        
        // 카다로그
        'cadarok' => '부',
        'leaflet' => '부',
        
        // 포스터
        'littleprint' => '매',
        'poster' => '매',
        
        // 상품권
        'merchandisebond' => '매',
        
        // 자석스티커
        'msticker' => '매',
        
        // NCR양식
        'ncrflambeau' => '권',
        
        // 임의 입력
        'manual' => '개'
    ];
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    /**
     * 품목 데이터를 표시용 배열로 변환
     * 
     * @param array $item quote_items 또는 quotation_temp 데이터
     * @return array 표시용 데이터
     */
    public function formatItem($item) {
        return [
            'product_name' => $this->getProductName($item),
            'specification' => $this->getSpecification($item),
            'quantity_display' => $this->getQuantityDisplay($item),
            'unit' => $this->getUnit($item),
            'unit_price' => $this->getUnitPriceDisplay($item),
            'supply_price' => $this->getSupplyPrice($item),
            'supply_price_formatted' => number_format($this->getSupplyPrice($item)),
            'vat_amount' => $this->getVatAmount($item),
            'vat_amount_formatted' => number_format($this->getVatAmount($item)),
            'total_price' => $this->getTotalPrice($item),
            'total_price_formatted' => number_format($this->getTotalPrice($item)),
            'notes' => $item['notes'] ?? ''
        ];
    }
    
    /**
     * 품명 반환
     */
    public function getProductName($item) {
        $productType = $item['product_type'] ?? 'manual';
        
        // 임의 입력인 경우 직접 입력한 품명 사용
        if ($productType === 'manual' && !empty($item['product_name'])) {
            return $item['product_name'];
        }
        
        // 9개 품목 계산기 - 한글명 반환
        return self::$productTypeNames[$productType] ?? ($item['product_name'] ?? '품목');
    }
    
    /**
     * 규격/사양 반환 (품목별 형식)
     */
    public function getSpecification($item) {
        $productType = $item['product_type'] ?? 'manual';
        
        // 품목 타입 정규화
        $normalizedType = $this->normalizeProductType($productType);
        
        switch ($normalizedType) {
            case 'sticker_new':
            case 'sticker':
                return $this->formatStickerSpec($item);
            case 'inserted':
                return $this->formatInsertedSpec($item);
            case 'namecard':
                return $this->formatNamecardSpec($item);
            case 'envelope':
                return $this->formatEnvelopeSpec($item);
            case 'cadarok':
            case 'leaflet':
                return $this->formatCadarokSpec($item);
            case 'littleprint':
            case 'poster':
                return $this->formatPosterSpec($item);
            case 'merchandisebond':
                return $this->formatMerchandisebondSpec($item);
            case 'msticker':
                return $this->formatMstickerSpec($item);
            case 'ncrflambeau':
                return $this->formatNcrSpec($item);
            default:
                return $item['specification'] ?? '';
        }
    }
    
    /**
     * 품목 타입 정규화
     * 레거시/매핑 타입을 정식 타입으로 변환
     * 
     * @param string $productType
     * @return string 정규화된 타입
     */
    public function normalizeProductType($productType) {
        // 이미 정식 타입이면 그대로 반환
        if (in_array($productType, ['sticker_new', 'inserted', 'namecard', 'envelope', 
            'cadarok', 'littleprint', 'merchandisebond', 'msticker', 'ncrflambeau', 'manual'])) {
            return $productType;
        }
        
        // 매핑 테이블 확인
        if (isset(self::$typeNormalization[$productType])) {
            return self::$typeNormalization[$productType];
        }
        
        // 레거시 스티커 감지 (jong, garo 컬럼이 있으면 스티커)
        if ($productType === 'sticker') {
            return 'sticker_new';
        }
        
        return $productType;
    }
    
    /**
     * 스티커 규격 형식
     * 예: "재질: 아트유광코팅 / 크기: 60×90mm / 모양: 사각"
     */
    private function formatStickerSpec($item) {
        $parts = [];
        
        if (!empty($item['jong'])) {
            $parts[] = "재질: {$item['jong']}";
        }
        
        if (!empty($item['garo']) && !empty($item['sero'])) {
            $parts[] = "크기: {$item['garo']}×{$item['sero']}mm";
        }
        
        if (!empty($item['domusong'])) {
            // "00000 사각" → "사각", "08000 사각도무송" → "사각도무송"
            $shape = preg_replace('/^\d+\s*/', '', $item['domusong']);
            $parts[] = "모양: {$shape}";
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 전단지 규격 형식 (★ 연/매수 포함)
     * 예: "칼라(CMYK) / A4 / 90g아트지 / 단면 / 인쇄만
     *      0.5연 (2,000매)"
     */
    private function formatInsertedSpec($item) {
        $parts = [];
        
        // 기본 사양 (MY_type_name, Section_name 등 사용)
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        // 인쇄방식
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        $spec = implode(' / ', $parts);
        
        // ★ 핵심: 연/매수 표시 추가
        $yeonMaesu = $this->getYeonMaesuDisplay($item);
        if ($yeonMaesu) {
            $spec .= ($spec ? "\n" : '') . $yeonMaesu;
        }
        
        return $spec;
    }
    
    /**
     * 명함 규격 형식
     * 예: "일반명함(쿠폰) / 칼라코팅 / 단면 / 500매 / 인쇄만 의뢰"
     */
    private function formatNamecardSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        // 수량
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '매';
        }
        
        // 인쇄방식
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 봉투 규격 형식
     */
    private function formatEnvelopeSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '매';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 카다로그 규격 형식
     */
    private function formatCadarokSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '부';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 포스터 규격 형식
     */
    private function formatPosterSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '매';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 상품권 규격 형식
     */
    private function formatMerchandisebondSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '매';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * 자석스티커 규격 형식
     */
    private function formatMstickerSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '매';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * NCR양식 규격 형식
     */
    private function formatNcrSpec($item) {
        $parts = [];
        
        if (!empty($item['MY_type_name'])) {
            $parts[] = $item['MY_type_name'];
        }
        if (!empty($item['Section_name'])) {
            $parts[] = $item['Section_name'];
        }
        if (!empty($item['POtype_name'])) {
            $parts[] = $item['POtype_name'];
        }
        
        $qty = floatval($item['MY_amount'] ?? 0);
        if ($qty > 0) {
            $parts[] = number_format($qty) . '권';
        }
        
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') {
            $parts[] = '인쇄만 의뢰';
        } elseif ($ordertype === 'design' || $ordertype === 'total') {
            $parts[] = '디자인+인쇄';
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * ★ 연/매수 표시 문자열 반환 (전단지용)
     * 
     * @param array $item
     * @return string 예: "0.5연 (2,000매)" 또는 ""
     */
    public function getYeonMaesuDisplay($item) {
        $myAmount = floatval($item['MY_amount'] ?? 0);
        $mesu = intval($item['mesu'] ?? 0);
        
        if ($myAmount <= 0 || $mesu <= 0) {
            return '';
        }
        
        // 연수 표시: 정수면 정수로, 소수면 소수점 1자리
        $yeonDisplay = (floor($myAmount) == $myAmount) 
            ? number_format($myAmount) 
            : number_format($myAmount, 1);
        
        return $yeonDisplay . '연 (' . number_format($mesu) . '매)';
    }
    
    /**
     * 수량 표시 반환
     * 소수점이 있으면 표시, 없으면 정수로
     */
    public function getQuantityDisplay($item) {
        // quote_items는 quantity, quotation_temp/shop_temp는 MY_amount 사용
        $qty = floatval($item['quantity'] ?? $item['MY_amount'] ?? 0);
        
        if ($qty == intval($qty)) {
            return number_format($qty);
        } else {
            return rtrim(rtrim(number_format($qty, 2), '0'), '.');
        }
    }
    
    /**
     * 단위 반환
     */
    public function getUnit($item) {
        if (!empty($item['unit'])) {
            return $item['unit'];
        }
        
        $productType = $item['product_type'] ?? 'manual';
        return self::$defaultUnits[$productType] ?? '개';
    }
    
    /**
     * 공급가액 반환
     */
    public function getSupplyPrice($item) {
        return intval($item['supply_price'] ?? $item['st_price'] ?? 0);
    }
    
    /**
     * VAT 반환
     */
    public function getVatAmount($item) {
        $supply = $this->getSupplyPrice($item);
        $total = $this->getTotalPrice($item);
        return $total - $supply;
    }
    
    /**
     * 합계(VAT포함) 반환
     */
    public function getTotalPrice($item) {
        return intval($item['total_price'] ?? $item['st_price_vat'] ?? 0);
    }
    
    /**
     * 단가 표시 반환 (역계산 검증)
     * 무한소수가 나오는 경우 '-' 반환
     */
    public function getUnitPriceDisplay($item) {
        $supply = $this->getSupplyPrice($item);
        $qty = floatval($item['quantity'] ?? $item['MY_amount'] ?? 0);
        
        if ($qty <= 0) {
            return '-';
        }
        
        $unitPrice = $supply / $qty;
        $calculated = round($unitPrice * $qty);
        
        // 역계산 검증: 단가 × 수량 = 공급가액이면 표시
        if ($calculated == $supply) {
            return number_format($unitPrice, 0);
        }
        
        return '-'; // 무한소수는 생략
    }
    
    /**
     * 단가 숫자 반환 (역계산 검증)
     * 무한소수가 나오는 경우 0 반환
     */
    public function getUnitPrice($item) {
        $supply = $this->getSupplyPrice($item);
        $qty = floatval($item['quantity'] ?? $item['MY_amount'] ?? 0);
        
        if ($qty <= 0) {
            return 0;
        }
        
        $unitPrice = $supply / $qty;
        $calculated = round($unitPrice * $qty);
        
        if ($calculated == $supply) {
            return round($unitPrice, 2);
        }
        
        return 0;
    }
    
    /**
     * 품목 타입의 한글명 반환 (static)
     */
    public static function getProductTypeName($productType) {
        return self::$productTypeNames[$productType] ?? '품목';
    }
    
    /**
     * 품목 타입의 기본 단위 반환 (static)
     */
    public static function getDefaultUnit($productType) {
        return self::$defaultUnits[$productType] ?? '개';
    }
}
