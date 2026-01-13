<?php
/**
 * Core Print Logic - 두손기획인쇄 중앙 로직 모듈 (SSOT)
 *
 * Grand Design 아키텍처의 단일 진입점:
 * - 모든 수량/단위 포맷팅
 * - 제품 사양 표시
 * - 데이터 어댑터 변환
 *
 * @package DusonPrint
 * @version 1.0.0
 * @since 2026-01-13
 *
 * 사용법:
 *   require_once __DIR__ . '/../lib/core_print_logic.php';
 *   $qty = PrintCore::formatQuantity(1000, 'S'); // "1,000매"
 */

// ============================================================
// 1. 경로 상수 정의
// ============================================================

defined('DUSON_ROOT') or define('DUSON_ROOT', dirname(__DIR__));
defined('DUSON_LIB') or define('DUSON_LIB', __DIR__);
defined('DUSON_INCLUDES') or define('DUSON_INCLUDES', DUSON_ROOT . '/includes');

// ============================================================
// 2. SSOT 클래스 자동 로드
// ============================================================

// QuantityFormatter - 수량/단위 SSOT
if (!class_exists('QuantityFormatter')) {
    require_once DUSON_INCLUDES . '/QuantityFormatter.php';
}

// ProductSpecFormatter - 제품 사양 표시
if (!class_exists('ProductSpecFormatter')) {
    require_once DUSON_INCLUDES . '/ProductSpecFormatter.php';
}

// SpecDisplayService - 사양 표시 서비스
if (!class_exists('SpecDisplayService')) {
    require_once DUSON_INCLUDES . '/SpecDisplayService.php';
}

// DataAdapter - 레거시 ↔ 신규 스키마 변환
if (!class_exists('DataAdapter') && file_exists(DUSON_INCLUDES . '/DataAdapter.php')) {
    require_once DUSON_INCLUDES . '/DataAdapter.php';
}

// ============================================================
// 3. 전역 상수 (단위 코드)
// ============================================================

// 단위 코드 → 한글
defined('UNIT_CODE_REAM') or define('UNIT_CODE_REAM', 'R');    // 연
defined('UNIT_CODE_SHEET') or define('UNIT_CODE_SHEET', 'S');   // 매
defined('UNIT_CODE_BUNDLE') or define('UNIT_CODE_BUNDLE', 'B'); // 부
defined('UNIT_CODE_VOLUME') or define('UNIT_CODE_VOLUME', 'V'); // 권
defined('UNIT_CODE_PIECE') or define('UNIT_CODE_PIECE', 'P');   // 장
defined('UNIT_CODE_EACH') or define('UNIT_CODE_EACH', 'E');     // 개

// ============================================================
// 4. 제품 타입 상수
// ============================================================

defined('PRODUCT_INSERTED') or define('PRODUCT_INSERTED', 'inserted');       // 전단지
defined('PRODUCT_LEAFLET') or define('PRODUCT_LEAFLET', 'leaflet');          // 리플렛
defined('PRODUCT_STICKER_NEW') or define('PRODUCT_STICKER_NEW', 'sticker_new'); // 스티커
defined('PRODUCT_MSTICKER') or define('PRODUCT_MSTICKER', 'msticker');       // 자석스티커
defined('PRODUCT_NAMECARD') or define('PRODUCT_NAMECARD', 'namecard');       // 명함
defined('PRODUCT_ENVELOPE') or define('PRODUCT_ENVELOPE', 'envelope');       // 봉투
defined('PRODUCT_CADAROK') or define('PRODUCT_CADAROK', 'cadarok');          // 카다록
defined('PRODUCT_NCRFLAMBEAU') or define('PRODUCT_NCRFLAMBEAU', 'ncrflambeau'); // NCR양식지
defined('PRODUCT_POSTER') or define('PRODUCT_POSTER', 'littleprint');        // 포스터 (littleprint = poster)
defined('PRODUCT_MERCHANDISEBOND') or define('PRODUCT_MERCHANDISEBOND', 'merchandisebond'); // 상품권

// ============================================================
// 5. PrintCore 파사드 클래스
// ============================================================

/**
 * PrintCore - 중앙 로직 파사드
 *
 * SSOT 클래스들에 대한 단순화된 접근 제공
 */
class PrintCore {

    /** @var mysqli|null DB 연결 (지연 초기화) */
    private static $db = null;

    /** @var ProductSpecFormatter|null 캐시된 인스턴스 */
    private static $specFormatter = null;

    /** @var SpecDisplayService|null 캐시된 인스턴스 */
    private static $specService = null;

    // --------------------------------------------------------
    // DB 연결 관리
    // --------------------------------------------------------

    /**
     * DB 연결 설정 (선택적)
     *
     * @param mysqli $db 활성화된 DB 연결
     */
    public static function setDb(mysqli $db): void {
        self::$db = $db;
    }

    /**
     * DB 연결 가져오기 (지연 초기화)
     *
     * @return mysqli|null
     */
    public static function getDb(): ?mysqli {
        if (self::$db === null && file_exists(DUSON_ROOT . '/db.php')) {
            require_once DUSON_ROOT . '/db.php';
            global $db;
            self::$db = $db;
        }
        return self::$db;
    }

    // --------------------------------------------------------
    // 수량 포맷팅 (QuantityFormatter 위임)
    // --------------------------------------------------------

    /**
     * 수량 표시 문자열 생성
     *
     * @param float $value 수량 값
     * @param string $unitCode 단위 코드 (R/S/B/V/P/E)
     * @param int|null $sheets 매수 (연 단위용)
     * @param string $separator 구분자 (' ' 또는 '<br>')
     * @return string 포맷된 문자열
     */
    public static function formatQuantity(float $value, string $unitCode, ?int $sheets = null, string $separator = ' '): string {
        return QuantityFormatter::format($value, $unitCode, $sheets, $separator);
    }

    /**
     * 레거시 데이터에서 수량 정보 추출
     *
     * @param array $data 레거시 주문 데이터
     * @param string $productType 제품 타입
     * @return array [qty_value, qty_unit_code, qty_sheets]
     */
    public static function extractQuantity(array $data, string $productType): array {
        return QuantityFormatter::extractFromLegacy($data, $productType);
    }

    /**
     * 제품별 기본 단위 코드 조회
     *
     * @param string $productType 제품 타입
     * @return string 단위 코드
     */
    public static function getUnitCode(string $productType): string {
        return QuantityFormatter::getProductUnitCode($productType);
    }

    /**
     * 제품별 기본 단위명 조회
     *
     * @param string $productType 제품 타입
     * @return string 한글 단위명
     */
    public static function getUnitName(string $productType): string {
        return QuantityFormatter::getProductUnitName($productType);
    }

    // --------------------------------------------------------
    // 전단지 매수 DB 조회 (SSOT - 계산 금지)
    // --------------------------------------------------------

    /**
     * 전단지 매수를 mlangprintauto_inserted 테이블에서 조회
     *
     * ⚠️ 중요: 절대 계산하지 않음, DB값만 사용
     *
     * @param float $reams 연수 (quantity 필드)
     * @return int 매수 (quantityTwo 필드) 또는 0
     */
    public static function lookupInsertedSheets(float $reams): int {
        if ($reams <= 0) {
            return 0;
        }

        $db = self::getDb();
        if (!$db) {
            return 0;
        }

        $stmt = mysqli_prepare($db,
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

        // 조회 실패 시 0 반환 (계산하지 않음)
        return 0;
    }

    // --------------------------------------------------------
    // 제품 사양 포맷팅 (ProductSpecFormatter 위임)
    // --------------------------------------------------------

    /**
     * ProductSpecFormatter 인스턴스 가져오기 (싱글톤)
     *
     * @return ProductSpecFormatter
     */
    public static function getSpecFormatter(): ProductSpecFormatter {
        if (self::$specFormatter === null) {
            self::$specFormatter = new ProductSpecFormatter(self::getDb());
        }
        return self::$specFormatter;
    }

    /**
     * 제품 사양 포맷팅
     *
     * @param array $item 주문 항목 데이터
     * @param string $format 포맷 유형 (standardized/table/compact)
     * @return string|array 포맷된 사양
     */
    public static function formatSpec(array $item, string $format = 'standardized') {
        $formatter = self::getSpecFormatter();

        switch ($format) {
            case 'standardized':
                return $formatter->formatStandardized($item);
            case 'table':
                return $formatter->formatForTable($item);
            case 'compact':
                return $formatter->formatCompact($item);
            default:
                return $formatter->formatStandardized($item);
        }
    }

    // --------------------------------------------------------
    // 사양 표시 서비스 (SpecDisplayService 위임)
    // --------------------------------------------------------

    /**
     * SpecDisplayService 인스턴스 가져오기 (싱글톤)
     *
     * @return SpecDisplayService
     */
    public static function getSpecService(): SpecDisplayService {
        if (self::$specService === null) {
            self::$specService = new SpecDisplayService(self::getDb());
        }
        return self::$specService;
    }

    /**
     * 표시용 사양 정보 조회
     *
     * @param array $item 주문 항목
     * @return array 표시용 사양 정보
     */
    public static function getDisplaySpec(array $item): array {
        return self::getSpecService()->getDisplaySpec($item);
    }

    // --------------------------------------------------------
    // 유틸리티 함수
    // --------------------------------------------------------

    /**
     * 제품 타입 정규화
     *
     * @param string $type 입력된 제품 타입
     * @return string 정규화된 제품 타입
     */
    public static function normalizeProductType(string $type): string {
        $aliases = [
            'poster' => 'littleprint',
            'sticker' => 'sticker_new',
            'msticker_01' => 'msticker',
        ];

        $type = strtolower(trim($type));
        return $aliases[$type] ?? $type;
    }

    /**
     * 수량이 유효한지 확인
     *
     * @param float $value 수량 값
     * @param string $unitCode 단위 코드
     * @return bool 유효 여부
     */
    public static function isValidQuantity(float $value, string $unitCode): bool {
        return QuantityFormatter::validate($value, $unitCode);
    }

    /**
     * 버전 정보
     *
     * @return array 버전 및 구성 정보
     */
    public static function version(): array {
        return [
            'version' => '1.0.0',
            'date' => '2026-01-13',
            'components' => [
                'QuantityFormatter' => class_exists('QuantityFormatter'),
                'ProductSpecFormatter' => class_exists('ProductSpecFormatter'),
                'SpecDisplayService' => class_exists('SpecDisplayService'),
                'DataAdapter' => class_exists('DataAdapter'),
            ],
        ];
    }
}

// ============================================================
// 6. 전역 헬퍼 함수 (편의성)
// ============================================================

if (!function_exists('duson_format_qty')) {
    /**
     * 수량 포맷팅 헬퍼
     *
     * @param float $value 수량 값
     * @param string $unitCode 단위 코드
     * @param int|null $sheets 매수
     * @return string 포맷된 문자열
     */
    function duson_format_qty(float $value, string $unitCode, ?int $sheets = null): string {
        return PrintCore::formatQuantity($value, $unitCode, $sheets);
    }
}

if (!function_exists('duson_lookup_sheets')) {
    /**
     * 전단지 매수 조회 헬퍼
     *
     * @param float $reams 연수
     * @return int 매수
     */
    function duson_lookup_sheets(float $reams): int {
        return PrintCore::lookupInsertedSheets($reams);
    }
}

if (!function_exists('duson_get_unit')) {
    /**
     * 제품별 단위 조회 헬퍼
     *
     * @param string $productType 제품 타입
     * @return string 한글 단위명
     */
    function duson_get_unit(string $productType): string {
        return PrintCore::getUnitName($productType);
    }
}
