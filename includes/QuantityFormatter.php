<?php
/**
 * QuantityFormatter - 수량/단위의 단일 진실 공급원 (SSOT)
 *
 * Grand Design 원칙:
 * - 저장: qty_value (DECIMAL) + qty_unit_code (CHAR)
 * - 표시: format() 함수로 동적 생성
 * - 절대로 quantity_display를 직접 저장하지 않음
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

class QuantityFormatter {

    /**
     * 단위 코드 ↔ 한글 매핑
     * DB unit_codes 테이블과 동기화 유지
     */
    const UNIT_CODES = [
        // 표준 인쇄 단위
        'R' => '연',  // Ream - 전단지/리플렛
        'S' => '매',  // Sheet - 스티커/명함/봉투/포스터
        'B' => '부',  // Bundle - 카다록
        'V' => '권',  // Volume - NCR양식지
        'P' => '장',  // Piece - 개별 인쇄물
        'E' => '개',  // Each - 기타/커스텀

        // 비규격/수동 견적용 단위
        'H' => '헤베',  // Square Meter - 대형 출력물 (현수막, 배너)
        'X' => '박스',  // Box - 박스 단위
        'T' => '세트',  // Set - 세트 단위
        'M' => '미터'   // Meter - 길이 단위 (현수막)
    ];

    /**
     * 제품별 기본 단위 코드
     *
     * ⚠️ 중요 참고사항 (이전 개발자 레거시 주의):
     * - sticker: 폴더만 있고 사용 안 함 (옛날 방식)
     * - sticker_new: 실제 사용하는 스티커 (수학계산 기반)
     * - inserted: 전단지 + 리플렛 포괄 (90g/120g 이상은 리플렛)
     * - leaflet: 이미지 경로용만, 실제 주문은 inserted로 처리
     * - littleprint = poster: 같은 것 (이전 개발자 명칭 오류)
     * - msticker = msticker_01: 자석스티커
     */
    const PRODUCT_UNITS = [
        'inserted'       => 'R',  // 전단지+리플렛 - 연 (90g/120g 이상은 리플렛)
        'leaflet'        => 'R',  // (미사용) 이미지 경로용만
        'sticker'        => 'S',  // (미사용) 옛날 방식, 폴더만 존재
        'sticker_new'    => 'S',  // ✅ 실제 스티커 - 매 (수학계산 기반)
        'msticker'       => 'S',  // 자석스티커 - 매
        'msticker_01'    => 'S',  // 자석스티커 - 매 (msticker와 동일)
        'namecard'       => 'S',  // 명함 - 매
        'envelope'       => 'S',  // 봉투 - 매
        'cadarok'        => 'B',  // 카다록 - 부
        'ncrflambeau'    => 'V',  // NCR양식지 - 권
        'littleprint'    => 'P',  // 포스터 - 장 (=poster)
        'poster'         => 'P',  // 포스터 - 장 (=littleprint)
        'merchandisebond'=> 'S'   // 상품권 - 매
    ];

    /**
     * 전단지 1연당 매수 (용지별)
     */
    const SHEETS_PER_REAM = [
        'default' => 500,   // 기본
        '80g'     => 500,
        '100g'    => 500,
        '120g'    => 500,
        '150g'    => 250,
        '200g'    => 250
    ];

    /**
     * NCR양식지 1권당 조(Set) 수
     * 1권 = 50조 (고정)
     */
    const NCR_SETS_PER_VOLUME = 50;

    /**
     * 수량 표시 문자열 생성 (SSOT - 단일 진실 공급원)
     *
     * 이 함수만이 수량 표시 문자열을 생성합니다.
     * 모든 화면(장바구니, 주문서, 완료, 관리자)에서 이 함수를 사용해야 합니다.
     *
     * @param float $value 수량 값 (0.5, 1, 1000 등)
     * @param string $unitCode 단위 코드 (R/S/B/V/P/E)
     * @param int|null $sheets 실제 매수 (연/권 단위 제품용)
     * @param string $separator 연수/매수 구분자 (기본: ' ', 테이블용: '<br>')
     * @return string "1,000매", "0.5연 (2,000매)", "10권 (2,000매)" 형식
     */
    public static function format(float $value, string $unitCode, ?int $sheets = null, string $separator = ' '): string {
        // 단위 이름 조회
        $unitName = self::UNIT_CODES[$unitCode] ?? '개';

        // 숫자 포맷팅: 정수면 소수점 없이, 소수면 필요한 만큼만
        if (floor($value) == $value) {
            $formatted = number_format($value);
        } else {
            // 소수점 이하 불필요한 0 제거
            $formatted = rtrim(rtrim(number_format($value, 2), '0'), '.');
        }

        $display = $formatted . $unitName;

        // ✅ 2026-01-15: 연(R) 또는 권(V) 단위이고 매수 정보가 있으면 "(X매)" 추가
        // NCR양식지: "10권 (2,000매)" / 전단지: "0.5연 (2,000매)"
        if (($unitCode === 'R' || $unitCode === 'V') && $sheets !== null && $sheets > 0) {
            $display .= $separator . '(' . number_format($sheets) . '매)';
        }

        return $display;
    }

    /**
     * 레거시 데이터에서 표준 수량 정보 추출
     *
     * @param array $data 레거시 데이터 (MY_amount, mesu, quantity 등 포함)
     * @param string $productType 제품 타입
     * @return array [qty_value, qty_unit_code, qty_sheets]
     */
    public static function extractFromLegacy(array $data, string $productType): array {
        $unitCode = self::PRODUCT_UNITS[$productType] ?? 'E';
        $value = 0;
        $sheets = null;

        switch ($productType) {
            // 스티커류: mesu 필드 최우선, 없으면 quantity 또는 MY_amount
            case 'sticker':
            case 'sticker_new':
            case 'msticker':
            case 'msticker_01':
                $value = intval($data['mesu'] ?? 0);
                if ($value === 0) {
                    $value = intval($data['MY_amount'] ?? $data['quantity'] ?? 0);
                }
                break;

            // 전단지/리플렛: 연 단위 + 매수
            case 'inserted':
            case 'leaflet':
                $value = floatval($data['MY_amount'] ?? $data['quantity'] ?? 0);
                $sheets = intval($data['mesu'] ?? $data['quantityTwo'] ?? $data['quantity_sheets'] ?? 0);
                // ✅ 2026-01-13: 매수가 없으면 0 유지 (계산 금지, DB에서만 조회)
                // 호출하는 쪽(ProductSpecFormatter)에서 mlangprintauto_inserted 테이블 조회
                break;

            // 명함/봉투: mesu 우선, 없으면 quantity 또는 MY_amount
            case 'namecard':
            case 'envelope':
                // mesu가 있으면 그대로 사용
                if (!empty($data['mesu']) && $data['mesu'] != '0') {
                    $value = intval($data['mesu']);
                } else {
                    // quantity 또는 MY_amount 사용, 10 미만이면 천 단위로 해석
                    $amount = floatval($data['MY_amount'] ?? $data['quantity'] ?? 0);
                    if ($amount > 0 && $amount < 10) {
                        $value = intval($amount * 1000);
                    } else {
                        $value = intval($amount);
                    }
                }
                break;

            // 카다록: 부 단위
            case 'cadarok':
                $value = intval($data['MY_amount'] ?? $data['mesu'] ?? $data['quantity'] ?? 0);
                break;

            // NCR양식지: 권 단위 + 매수 계산
            // ✅ 2026-01-15: quantity_sheets 계산 추가 (권 × 50 × multiplier)
            case 'ncrflambeau':
                $value = intval($data['MY_amount'] ?? $data['mesu'] ?? $data['quantity'] ?? 0);
                // qty_sheets가 이미 있으면 사용, 없으면 계산
                if (!empty($data['quantity_sheets']) || !empty($data['qty_sheets'])) {
                    $sheets = intval($data['quantity_sheets'] ?? $data['qty_sheets'] ?? 0);
                } else {
                    // spec_material 또는 MY_Fsd_name에서 복사 매수(2매/3매/4매) 추출
                    $multiplier = self::extractNcrMultiplier($data);
                    $sheets = intval($value * self::NCR_SETS_PER_VOLUME * $multiplier);
                }
                break;

            // 포스터: 매/장 단위
            case 'littleprint':
            case 'poster':
                $value = intval($data['MY_amount'] ?? $data['mesu'] ?? $data['quantity'] ?? 0);
                break;

            // 상품권: 매 단위
            case 'merchandisebond':
                $value = intval($data['MY_amount'] ?? $data['mesu'] ?? $data['quantity'] ?? 0);
                break;

            // 기타: MY_amount 또는 quantity 사용
            default:
                $value = floatval($data['MY_amount'] ?? $data['mesu'] ?? $data['quantity'] ?? 0);
        }

        return [
            'qty_value' => $value,
            'qty_unit_code' => $unitCode,
            'qty_sheets' => $sheets
        ];
    }

    /**
     * NCR양식지 복사 매수(multiplier) 추출
     *
     * ✅ 2026-01-15: 신규 추가
     *
     * spec_material 또는 MY_Fsd_name에서 "2매", "3매", "4매" 키워드 추출
     * 예: "NCR 2매(100매철)" → 2, "NCR 3매(150매철)" → 3
     *
     * @param array $data 주문 데이터
     * @return int multiplier (2, 3, 4) - 기본값 2
     */
    public static function extractNcrMultiplier(array $data): int {
        // spec_material 또는 MY_Fsd_name에서 추출
        $materialText = $data['spec_material'] ?? $data['MY_Fsd_name'] ?? $data['MY_Fsd'] ?? '';

        // "4매", "3매", "2매" 순으로 검색 (더 큰 숫자 우선)
        if (preg_match('/(\d)매/u', $materialText, $matches)) {
            $multiplier = intval($matches[1]);
            // 유효 범위: 2~4
            if ($multiplier >= 2 && $multiplier <= 4) {
                return $multiplier;
            }
        }

        // 기본값: 2매 복사
        return 2;
    }

    /**
     * NCR양식지 매수 계산
     *
     * ✅ 2026-01-15: 신규 추가
     *
     * 공식: 총 매수 = 주문 권수 × 50 × 복사 매수
     *
     * @param int $volumes 주문 권수
     * @param int $multiplier 복사 매수 (2/3/4)
     * @return int 총 매수
     */
    public static function calculateNcrSheets(int $volumes, int $multiplier = 2): int {
        return $volumes * self::NCR_SETS_PER_VOLUME * $multiplier;
    }

    /**
     * 연수에서 매수 계산 (전단지/리플렛용)
     *
     * @param float $reams 연수
     * @param array $data 용지 정보 포함 데이터
     * @return int 매수
     */
    public static function calculateSheets(float $reams, array $data = []): int {
        // 용지 종류에서 연당 매수 결정
        $paperType = $data['MY_Fsd'] ?? $data['spec_material'] ?? 'default';
        $sheetsPerReam = self::SHEETS_PER_REAM['default'];

        foreach (self::SHEETS_PER_REAM as $key => $value) {
            if (stripos($paperType, $key) !== false) {
                $sheetsPerReam = $value;
                break;
            }
        }

        return intval($reams * $sheetsPerReam);
    }

    /**
     * 단위 코드에서 한글명 반환
     *
     * @param string $code 단위 코드 (R/S/B/V/P/E)
     * @return string 한글 단위명
     */
    public static function getUnitName(string $code): string {
        return self::UNIT_CODES[$code] ?? '개';
    }

    /**
     * 한글 단위에서 코드 반환
     *
     * @param string $name 한글 단위명
     * @return string 단위 코드
     */
    public static function getUnitCode(string $name): string {
        $reversed = array_flip(self::UNIT_CODES);
        return $reversed[$name] ?? 'E';
    }

    /**
     * 제품 타입에서 기본 단위 코드 반환
     *
     * @param string $productType 제품 타입
     * @param mysqli|null $db DB 연결 (null이면 상수 사용)
     * @return string 단위 코드
     */
    public static function getProductUnitCode(string $productType, $db = null): string {
        // DB 연결이 있으면 product_unit_config 테이블 조회
        if ($db !== null) {
            $config = self::getUnitConfigFromDB($db, $productType);
            if ($config !== null) {
                return $config['unit_code'];
            }
        }
        // Fallback: 상수 사용
        return self::PRODUCT_UNITS[$productType] ?? 'E';
    }

    /**
     * DB에서 품목별 단위 설정 조회
     *
     * @param mysqli $db DB 연결
     * @param string $productType 제품 타입
     * @return array|null [unit_code, unit_name, has_sub_quantity, sub_quantity_source]
     */
    public static function getUnitConfigFromDB($db, string $productType): ?array {
        static $cache = [];

        // 캐시에 있으면 반환
        if (isset($cache[$productType])) {
            return $cache[$productType];
        }

        $stmt = mysqli_prepare($db,
            "SELECT unit_code, unit_name, has_sub_quantity, sub_quantity_source
             FROM product_unit_config WHERE product_type = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 's', $productType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 캐시에 저장 (null도 저장하여 반복 쿼리 방지)
        $cache[$productType] = $row;

        return $row;
    }

    /**
     * 품목이 보조수량(매수) 표시가 필요한지 확인
     *
     * @param mysqli $db DB 연결
     * @param string $productType 제품 타입
     * @return bool
     */
    public static function needsSubQuantity($db, string $productType): bool {
        $config = self::getUnitConfigFromDB($db, $productType);
        return $config !== null && !empty($config['has_sub_quantity']);
    }

    /**
     * 품목의 매수 조회 테이블명 반환
     *
     * @param mysqli $db DB 연결
     * @param string $productType 제품 타입
     * @return string|null 테이블명 또는 null
     */
    public static function getSubQuantitySource($db, string $productType): ?string {
        $config = self::getUnitConfigFromDB($db, $productType);
        return $config['sub_quantity_source'] ?? null;
    }

    /**
     * 제품 타입에서 기본 단위명 반환
     *
     * @param string $productType 제품 타입
     * @return string 한글 단위명
     */
    public static function getProductUnitName(string $productType): string {
        $code = self::getProductUnitCode($productType);
        return self::getUnitName($code);
    }

    /**
     * 기존 quantity_display 문자열 파싱
     *
     * @param string $display "1,000매", "0.5연 (2,000매)" 형식
     * @return array [qty_value, qty_unit_code, qty_sheets]
     */
    public static function parseDisplay(string $display): array {
        $result = [
            'qty_value' => 0,
            'qty_unit_code' => 'E',
            'qty_sheets' => null
        ];

        // "0.5연 (2,000매)" 형식 파싱
        if (preg_match('/^([\d,\.]+)\s*(연|매|부|권|장|개)\s*(?:\(([\d,]+)매\))?$/u', $display, $matches)) {
            $result['qty_value'] = floatval(str_replace(',', '', $matches[1]));
            $result['qty_unit_code'] = self::getUnitCode($matches[2]);

            if (!empty($matches[3])) {
                $result['qty_sheets'] = intval(str_replace(',', '', $matches[3]));
            }
        }

        return $result;
    }

    /**
     * 수량 값 검증
     *
     * @param float $value 수량 값
     * @param string $unitCode 단위 코드
     * @return bool 유효 여부
     */
    public static function validate(float $value, string $unitCode): bool {
        // 수량은 0보다 커야 함
        if ($value <= 0) {
            return false;
        }

        // 단위 코드 유효성
        if (!isset(self::UNIT_CODES[$unitCode])) {
            return false;
        }

        // 연 단위가 아닌 경우 정수여야 함 (선택적 검증)
        if ($unitCode !== 'R' && floor($value) != $value) {
            // 경고만 로그, 실패로 처리하지 않음
            error_log("QuantityFormatter::validate WARNING: Non-integer value {$value} for unit {$unitCode}");
        }

        return true;
    }
}

/**
 * formatPrintQuantity - 공통 수량 출력 함수 (SSOT Wrapper)
 *
 * Standard Architecture Directive 준수:
 * - 견적서, 장바구니, 주문서, 관리자 모두 이 함수만 사용
 * - 새 단위 추가 시 이 함수(QuantityFormatter) 한 곳만 수정
 *
 * @param float $qty_val 수량 값 (0.5, 1000 등)
 * @param string $qty_unit 단위 코드 (R/S/B/V/P/E/H/X/T/M) 또는 한글 (연/매/부 등)
 * @param int|null $qty_sheets 매수 (연 단위 제품용, 선택)
 * @param string $separator 연수/매수 구분자 (기본: ' ', HTML용: '<br>')
 * @return string "1,000매", "0.5연 (2,000매)", "2개" 등
 *
 * @example
 * formatPrintQuantity(0.5, 'R', 2000);      // "0.5연 (2,000매)"
 * formatPrintQuantity(1000, 'S');            // "1,000매"
 * formatPrintQuantity(2, 'E');               // "2개"
 * formatPrintQuantity(5.5, 'H');             // "5.5헤베"
 * formatPrintQuantity(3, 'X');               // "3박스"
 * formatPrintQuantity(2, '개');              // "2개" (한글 단위도 지원)
 */
function formatPrintQuantity(float $qty_val, string $qty_unit, ?int $qty_sheets = null, string $separator = ' '): string {
    // 한글 단위가 입력된 경우 코드로 변환
    if (mb_strlen($qty_unit) > 1 || preg_match('/[가-힣]/u', $qty_unit)) {
        $qty_unit = QuantityFormatter::getUnitCode($qty_unit);
    }

    return QuantityFormatter::format($qty_val, $qty_unit, $qty_sheets, $separator);
}
