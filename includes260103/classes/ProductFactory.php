<?php
/**
 * ProductFactory - 제품 인스턴스 생성 팩토리 클래스
 *
 * 제품 타입에 따라 적절한 제품 클래스 인스턴스를 생성합니다.
 *
 * 사용법:
 * ```php
 * $product = ProductFactory::create($db, 'namecard');
 * $product->setFromPost($_POST);
 * $product->uploadFiles($_FILES);
 * $product->addToCart();
 * ```
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

class ProductFactory {
    /**
     * 제품 인스턴스 생성
     *
     * @param mysqli $db 데이터베이스 연결
     * @param string $product_type 제품 타입
     * @return BaseProduct 제품 인스턴스
     * @throws Exception 알 수 없는 제품 타입
     */
    public static function create($db, $product_type) {
        // 클래스 자동 로드
        self::loadClasses();

        switch ($product_type) {
            // 전단지 (복잡한 수량 - mesu)
            case 'inserted':
                return new FlierProduct($db, $product_type);

            // 스티커 (개별 계산 - jong/garo/sero)
            case 'sticker':
                return new StickerProduct($db, $product_type);

            // NCR양식 (특수 구조)
            case 'ncrflambeau':
                return new NCRProduct($db, $product_type);

            // 표준형 6개 제품
            case 'namecard':      // 명함
            case 'envelope':      // 봉투
            case 'cadarok':       // 카다록
            case 'littleprint':   // 포스터
            case 'merchandisebond': // 상품권
            case 'msticker':      // 자석스티커
                return new CategoryBasedProduct($db, $product_type);

            default:
                throw new Exception("Unknown product type: {$product_type}");
        }
    }

    /**
     * 클래스 파일 로드
     */
    private static function loadClasses() {
        $class_dir = __DIR__;

        require_once $class_dir . '/BaseProduct.php';
        require_once $class_dir . '/CategoryBasedProduct.php';
        require_once $class_dir . '/FlierProduct.php';
        require_once $class_dir . '/StickerProduct.php';
        require_once $class_dir . '/NCRProduct.php';
    }

    /**
     * 지원 제품 목록 반환
     *
     * @return array 제품 코드 배열
     */
    public static function getSupportedProducts() {
        return [
            'inserted',        // 전단지
            'namecard',        // 명함
            'envelope',        // 봉투
            'sticker',         // 스티커
            'msticker',        // 자석스티커
            'cadarok',         // 카다록
            'littleprint',     // 포스터
            'merchandisebond', // 상품권
            'ncrflambeau'      // NCR양식
        ];
    }

    /**
     * 제품 한글명 반환
     *
     * @param string $product_type 제품 코드
     * @return string 제품 한글명
     */
    public static function getProductName($product_type) {
        $names = [
            'inserted' => '전단지',
            'namecard' => '명함',
            'envelope' => '봉투',
            'sticker' => '스티커',
            'msticker' => '자석스티커',
            'cadarok' => '카다록',
            'littleprint' => '포스터',
            'merchandisebond' => '상품권',
            'ncrflambeau' => 'NCR양식'
        ];

        return $names[$product_type] ?? $product_type;
    }

    /**
     * 제품 카테고리 반환
     *
     * @param string $product_type 제품 코드
     * @return string 카테고리 (standard|complex|individual|special)
     */
    public static function getProductCategory($product_type) {
        $categories = [
            'inserted' => 'complex',        // 복잡한 수량 (mesu)
            'sticker' => 'individual',      // 개별 계산 (jong/garo/sero)
            'ncrflambeau' => 'special',     // 특수 구조
            'namecard' => 'standard',       // 표준형
            'envelope' => 'standard',
            'cadarok' => 'standard',
            'littleprint' => 'standard',
            'merchandisebond' => 'standard',
            'msticker' => 'standard'
        ];

        return $categories[$product_type] ?? 'unknown';
    }

    /**
     * 제품이 지원되는지 확인
     *
     * @param string $product_type 제품 코드
     * @return bool 지원 여부
     */
    public static function isSupported($product_type) {
        return in_array($product_type, self::getSupportedProducts());
    }
}
