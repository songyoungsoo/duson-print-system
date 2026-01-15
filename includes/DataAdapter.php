<?php
/**
 * Data Adapter - 레거시 필드 ↔ 표준 필드 변환
 * Phase 2: Dual-Write 전략의 핵심 컴포넌트
 *
 * 11개 제품의 서로 다른 필드명을 표준화된 필드로 변환
 * 예: namecard MY_type → spec_type, sticker jong → spec_material
 *
 * ✅ 2026-01-13 Grand Design: legacyToNormalized() 추가
 * - 새 스키마 (orders, order_items) 형식으로 변환
 * - QuantityFormatter SSOT 적용
 */

require_once __DIR__ . '/QuantityFormatter.php';

class DataAdapter {

    /**
     * 가격 문자열을 정수로 안전하게 변환 (방어 로직)
     *
     * ✅ 2026-01-15: 신규 추가
     *
     * 콤마(,)가 포함된 문자열도 자동으로 숫자로 변환
     * 예: "1,000,000" → 1000000, "50000" → 50000, null → 0
     *
     * @param mixed $value 가격 값 (string, int, float, null)
     * @return int 정수 가격
     */
    public static function sanitizePrice($value): int {
        if ($value === null || $value === '') {
            return 0;
        }

        // 문자열인 경우 콤마 제거
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
            $value = str_replace(' ', '', $value);
        }

        return intval($value);
    }

    /**
     * 레거시 데이터를 표준 필드로 변환
     *
     * @param array $legacy_data 레거시 필드 배열
     * @param string $product_type 제품 타입 (namecard, sticker, inserted 등)
     * @return array 표준 필드 배열 (spec_type, spec_material, price_supply 등)
     */
    public static function legacyToStandard($legacy_data, $product_type) {
        $method = 'convert' . ucfirst(str_replace('_', '', $product_type));

        if (method_exists(__CLASS__, $method)) {
            $result = self::$method($legacy_data);
        } else {
            // 알 수 없는 제품 타입: 기본 변환
            $result = self::convertGeneric($legacy_data);
        }

        // ✅ Phase 3 FIX: quantity_display 단위 검증 자동 적용
        // 모든 변환 결과에서 단위가 포함되도록 보장
        if (isset($result['quantity_display'])) {
            $result['quantity_display'] = self::ensureQuantityDisplayUnit(
                $result['quantity_display'],
                $product_type,
                $legacy_data
            );
        }

        return $result;
    }

    /**
     * 표준 데이터 검증
     *
     * Phase 3 추가: 필수 필드 검증 메소드
     *
     * @param array $standardData 표준 필드 배열
     * @param string $productType 제품 타입
     * @return bool 검증 성공 여부
     */
    public static function validateStandardData($standardData, $productType) {
        // 필수 필드 정의 (spec_type은 레거시 데이터의 경우 숫자 ID도 허용)
        $required = ['spec_type', 'quantity_value', 'quantity_unit'];

        // 각 필수 필드 검증
        foreach ($required as $field) {
            if (!isset($standardData[$field]) || $standardData[$field] === '') {
                error_log("DataAdapter Validation Error: Missing field '{$field}' for product type '{$productType}'");
                return false;
            }
        }

        // 수량 값 유효성 검증
        if ($standardData['quantity_value'] <= 0) {
            error_log("DataAdapter Validation Error: Invalid quantity_value for product type '{$productType}'");
            return false;
        }

        // 가격 유효성 검증 (price_supply는 필수 아님, 있으면 >= 0 확인)
        if (isset($standardData['price_supply']) && $standardData['price_supply'] < 0) {
            error_log("DataAdapter Validation Error: Invalid price_supply for product type '{$productType}'");
            return false;
        }

        return true;
    }

    /**
     * quantity_display 자동 생성
     *
     * Phase 3 추가: 표준 필드에서 quantity_display 문자열 자동 생성
     *
     * @param array $standardData 표준 필드 배열
     * @return string 수량 표시 문자열 (예: "1,000매", "1.5연 (750매)")
     */
    public static function generateQuantityDisplay($standardData) {
        $value = floatval($standardData['quantity_value'] ?? 0);
        $unit = $standardData['quantity_unit'] ?? '매';
        $sheets = intval($standardData['quantity_sheets'] ?? 0);

        // 기본 수량 표시: 숫자 + 단위
        // 정수는 소수점 없이, 소수는 소수점 1자리까지 표시
        $display = number_format($value, ($value == intval($value)) ? 0 : 1) . $unit;

        // 전단지(연 단위)인 경우: 매수 추가 표시
        if ($unit === '연' && $sheets > 0) {
            $display .= ' (' . number_format($sheets) . '매)';
        }

        return $display;
    }

    /**
     * quantity_display 단위 검증 및 보정
     *
     * @param string $display 입력된 quantity_display
     * @param string $productType 제품 타입
     * @param array $data 원본 데이터 (폴백용)
     * @return string 단위가 포함된 quantity_display
     */
    public static function ensureQuantityDisplayUnit($display, $productType, $data = []) {
        // 단위 패턴: 매, 연, 부, 권, 개, 장
        $unitPattern = '/[매연부권개장]/u';

        // 이미 단위가 있으면 그대로 반환
        if (!empty($display) && preg_match($unitPattern, $display)) {
            return $display;
        }

        // 제품별 기본 단위
        $defaultUnits = [
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

        $unit = $defaultUnits[$productType] ?? '개';

        // 숫자만 있으면 단위 추가
        if (!empty($display) && is_numeric(str_replace(',', '', $display))) {
            return $display . $unit;
        }

        // ✅ FIX (2026-01-09): 원본 데이터에서 수량 직접 추출 (재귀 호출 제거)
        // 스티커: mesu, 전단지: MY_amount, 기타: MY_amount 또는 quantity
        $qty = 0;
        if (in_array($productType, ['sticker', 'msticker', 'msticker_01', 'sticker_new'])) {
            $qty = intval($data['mesu'] ?? 0);
        } elseif (in_array($productType, ['inserted', 'leaflet'])) {
            $qty = floatval($data['MY_amount'] ?? 0);
        } else {
            $qty = floatval($data['MY_amount'] ?? $data['quantity'] ?? 0);
        }

        // 수량이 0이면 경고 로그 및 기본값 사용
        if ($qty <= 0) {
            error_log("DataAdapter::ensureQuantityDisplayUnit WARNING: qty=0 for $productType, data=" . json_encode(array_slice($data, 0, 5)));
            $qty = 1;
        }

        // 단위 포함 수량 표시 반환
        $formattedQty = ($qty == intval($qty)) ? number_format($qty) : number_format($qty, 1);
        return $formattedQty . $unit;
    }

    /**
     * 레거시 데이터를 표준 필드로 변환 후 quantity_display 단위 검증
     *
     * @param array $legacyData 레거시 필드 배열
     * @param string $productType 제품 타입
     * @return array 표준 필드 배열 (quantity_display 단위 보장)
     */
    public static function legacyToStandardWithUnitCheck($legacyData, $productType) {
        $standardData = self::legacyToStandard($legacyData, $productType);

        // quantity_display 단위 최종 검증
        $standardData['quantity_display'] = self::ensureQuantityDisplayUnit(
            $standardData['quantity_display'] ?? '',
            $productType,
            $legacyData
        );

        return $standardData;
    }

    /**
     * 명함 (namecard) 변환
     * MY_type=종류, Section=용지, POtype=인쇄면, MY_amount=매수(천단위)
     */
    private static function convertNamecard($data) {
        $amount = floatval($data['MY_amount'] ?? 0);
        $qty_value = $amount > 0 && $amount < 10 ? $amount * 1000 : intval($amount);

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_size' => '90x50mm',  // 명함 고정 규격
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $qty_value,
            'quantity_unit' => '매',
            'quantity_sheets' => $qty_value,
            'quantity_display' => number_format($qty_value) . '매',
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'premium_options' => $data['premium_options'] ?? '',
            'product_type' => 'namecard'
        ];
    }

    /**
     * 스티커 (sticker) 변환
     * jong=재질, garo/sero=크기, domusong=모양, mesu=매수
     */
    private static function convertSticker($data) {
        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? 0);
        $price_vat = self::sanitizePrice($data['price_vat'] ?? 0);

        // jong 필드에서 "jil" 제거
        $jong = $data['jong'] ?? '';
        $jong = preg_replace('/^jil\s*/i', '', $jong);

        // domusong에서 앞의 0 제거
        $domusong = $data['domusong'] ?? '';
        $domusong = preg_replace('/^[0\s]+/', '', $domusong);

        $garo = intval($data['garo'] ?? 0);
        $sero = intval($data['sero'] ?? 0);
        $size = ($garo > 0 && $sero > 0) ? "{$garo}mm x {$sero}mm" : '';

        $mesu = intval($data['mesu'] ?? 0);

        // ✅ DEBUG: quantity_display 생성 확인
        $quantity_display_value = number_format($mesu) . '매';
        error_log("DataAdapter convertSticker DEBUG: mesu={$mesu}, quantity_display={$quantity_display_value}");

        return [
            'spec_type' => $domusong && $domusong !== '0' ? $domusong : '사각',
            'spec_material' => $jong,
            'spec_size' => $size,
            'spec_sides' => '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $mesu,
            'quantity_unit' => '매',
            'quantity_sheets' => $mesu,
            'quantity_display' => $quantity_display_value,  // 스티커 수량 단위 추가
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'product_type' => 'sticker'
        ];
    }

    /**
     * 전단지 (inserted/leaflet) 변환
     * MY_type=도수, MY_Fsd=용지, PN_type=규격, POtype=인쇄면, MY_amount=연수, mesu=매수
     */
    private static function convertInserted($data) {
        $reams = floatval($data['MY_amount'] ?? 0);
        $sheets = intval($data['mesu'] ?? 0);

        // ★ PRIORITY: Use stored quantity_display from dropdown if available
        $qty_display = $data['quantity_display'] ?? '';

        // Fallback: Calculate quantity_display if not provided
        if (empty($qty_display)) {
            if ($reams > 0) {
                $qty_display = number_format($reams, $reams == intval($reams) ? 0 : 1) . '연';
                if ($sheets > 0) {
                    $qty_display .= ' (' . number_format($sheets) . '매)';
                }
            } elseif ($sheets > 0) {
                $qty_display = number_format($sheets) . '매';
            }
        }

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['Order_PriceForm'] ?? $data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = self::sanitizePrice($data['Total_PriceForm'] ?? $data['vat_price'] ?? $data['st_price_vat'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['MY_Fsd_name'] ?: ($data['MY_Fsd'] ?? ''),
            'spec_size' => $data['PN_type_name'] ?: ($data['PN_type'] ?? ''),
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $reams,
            'quantity_unit' => '연',
            'quantity_sheets' => $sheets,
            'quantity_display' => $qty_display,
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'additional_options' => $data['additional_options'] ?? '',
            'product_type' => 'inserted'
        ];
    }

    /**
     * 리플렛 (leaflet) - 전단지와 동일
     */
    private static function convertLeaflet($data) {
        $result = self::convertInserted($data);
        $result['product_type'] = 'leaflet';
        return $result;
    }

    /**
     * 봉투 (envelope) 변환
     * MY_type=종류, Section=재질, POtype=인쇄색상(마스터1도/마스터2도/칼라4도), MY_amount=매수
     */
    private static function convertEnvelope($data) {
        $amount = floatval($data['MY_amount'] ?? 0);
        $qty_value = $amount > 0 && $amount < 10 ? $amount * 1000 : intval($amount);

        // ✅ 수정: 항상 계산된 qty_value 사용 (프론트엔드 값 무시)
        // 이유: 프론트엔드에서 "1"만 보내면 "1,000매"로 변환되지 않는 문제 해결
        $quantity_display = number_format($qty_value) . '매';

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_size' => '',
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] ?? ''),  // 봉투: 인쇄 색상(마스터1도/마스터2도/칼라4도)
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $qty_value,
            'quantity_unit' => '매',
            'quantity_sheets' => $qty_value,
            'quantity_display' => $quantity_display,
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'envelope_tape_enabled' => $data['envelope_tape_enabled'] ?? 0,
            'envelope_tape_quantity' => $data['envelope_tape_quantity'] ?? 0,
            'product_type' => 'envelope'
        ];
    }

    /**
     * 카다록 (cadarok) 변환
     * MY_type=종류, MY_Fsd=용지, Section=규격, POtype=인쇄면, MY_amount=수량
     *
     * ✅ 2026-01-15: spec_material 추가 (MY_Fsd_name 사용)
     */
    private static function convertCadarok($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ Phase 3: Frontend에서 보낸 quantity_display 우선 사용 (inserted 방식과 동일)
        $quantity_display = !empty($data['quantity_display'])
            ? $data['quantity_display']
            : number_format($amount) . '부';

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['MY_Fsd_name'] ?: ($data['MY_Fsd'] ?? ''),  // ✅ 2026-01-15: 용지 정보 추가
            'spec_size' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] ?? ''),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '부',
            'quantity_sheets' => $amount,
            'quantity_display' => $quantity_display,
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'additional_options' => $data['additional_options'] ?? '',
            'product_type' => 'cadarok'
        ];
    }

    /**
     * 포스터 (littleprint/poster) 변환
     * MY_type=구분, Section=용지, PN_type=규격, MY_amount=수량
     */
    private static function convertLittleprint($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_size' => $data['PN_type_name'] ?: ($data['PN_type'] ?? ''),
            'spec_sides' => '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '장',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '장',
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'additional_options' => $data['additional_options'] ?? '',
            'product_type' => 'littleprint'
        ];
    }

    /**
     * 포스터 (poster alias)
     */
    private static function convertPoster($data) {
        $result = self::convertLittleprint($data);
        $result['product_type'] = 'poster';
        return $result;
    }

    /**
     * 자석스티커 (msticker) 변환
     * MY_type=종류, Section=규격, POtype=인쇄면, MY_amount=매수
     */
    private static function convertMsticker($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => '',
            'spec_size' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '매',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '매',
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'product_type' => 'msticker'
        ];
    }

    /**
     * NCR양식지 (ncrflambeau) 변환
     * MY_type=도수, MY_Fsd=용지, PN_type=타입, MY_amount=수량
     *
     * ✅ 2026-01-15: quantity_sheets 계산 추가 (권 × 50 × multiplier)
     * - 복사 매수(2매/3매/4매)를 MY_Fsd_name에서 자동 추출
     * - 공식: 총 매수 = 주문 권수 × 50 × 복사 매수
     */
    private static function convertNcrflambeau($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ 용지(MY_Fsd_name)에서 복사 매수 추출 (2매/3매/4매)
        $spec_material = $data['MY_Fsd_name'] ?: ($data['MY_Fsd'] ?? '');
        $multiplier = QuantityFormatter::extractNcrMultiplier(['spec_material' => $spec_material]);

        // ✅ quantity_sheets 계산: 권 × 50 × multiplier
        $quantity_sheets = QuantityFormatter::calculateNcrSheets($amount, $multiplier);

        // ✅ quantity_display 생성: "10권 (2,000매)" 형식
        $quantity_display = QuantityFormatter::format($amount, 'V', $quantity_sheets);

        // 가격 필드 fallback: shop_temp는 st_price, mlangorder_printauto는 money_4/money_5
        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['PN_type_name'] ?: ($data['PN_type'] ?? ''),  // 타입
            'spec_material' => $spec_material,  // 용지 (복사 매수 포함)
            'spec_size' => '',
            'spec_sides' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),  // 도수
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '권',
            'quantity_sheets' => $quantity_sheets,  // ✅ 계산된 총 매수
            'quantity_display' => $quantity_display,  // ✅ "10권 (2,000매)" 형식
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'premium_options' => $data['premium_options'] ?? '',
            'product_type' => 'ncrflambeau'
        ];
    }

    /**
     * 상품권 (merchandisebond) 변환
     * MY_type=종류, Section=재질, POtype=인쇄면, MY_amount=매수
     */
    private static function convertMerchandisebond($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? $data['st_price'] ?? $data['money_4'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? $data['st_price_vat'] ?? $data['money_5'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => $data['Section_name'] ?: ($data['Section'] ?? ''),
            'spec_size' => '',
            'spec_sides' => $data['POtype_name'] ?: ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '매',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '매',
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'premium_options' => $data['premium_options'] ?? '',
            'product_type' => 'merchandisebond'
        ];
    }

    /**
     * 기본 변환 (알 수 없는 제품 타입)
     */
    private static function convertGeneric($data) {
        $amount = intval($data['MY_amount'] ?? $data['quantity'] ?? 1);

        // ✅ 방어 로직: 콤마 포함 문자열도 자동 변환
        $price_supply = self::sanitizePrice($data['price'] ?? 0);
        $price_vat = self::sanitizePrice($data['vat_price'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['Section_name'] ?? $data['MY_Fsd_name'] ?? '',
            'spec_size' => $data['PN_type_name'] ?? '',
            'spec_sides' => $data['POtype_name'] ?? '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '개',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '개',
            'price_supply' => $price_supply,
            'price_vat' => $price_vat,
            'price_vat_amount' => $price_vat - $price_supply,
            'product_type' => $data['product_type'] ?? 'unknown'
        ];
    }

    /**
     * 표준 데이터를 레거시 필드로 역변환 (선택적 구현)
     * Phase 4에서 필요시 구현
     */
    public static function standardToLegacy($standard_data, $product_type) {
        // 현재는 미구현 (Dual-Write 전략에서는 불필요)
        throw new Exception('standardToLegacy not implemented yet');
    }

    // =========================================================================
    // 용지 규격 마스터 연동 (paper_standard_master 테이블)
    // ✅ 2026-01-15: A/B 시리즈 혼란 방지를 위한 DB 기반 계산
    // =========================================================================

    /**
     * 규격명으로 매수 자동 계산 (SSOT Wrapper)
     *
     * paper_standard_master 테이블에서 1연당 매수를 조회하여 계산합니다.
     * A4/B4 혼동 없이 정확한 매수를 계산합니다.
     *
     * @param mysqli $db DB 연결
     * @param string $specName 규격명 (A4, B4 등)
     * @param float $reams 주문 연수
     * @return int 계산된 매수
     *
     * @example
     * // add_to_basket.php에서 사용
     * $sheets = DataAdapter::calculateSheetsBySpec($db, 'A4', 0.5);  // 2000
     */
    public static function calculateSheetsBySpec($db, string $specName, float $reams): int {
        return QuantityFormatter::calculateSheetsBySpec($db, $specName, $reams);
    }

    /**
     * PN_type_name에서 규격 코드 추출
     *
     * 전단지/포스터 주문 시 PN_type_name 필드에서 규격을 추출합니다.
     * 예: "A4 (210×297)" → "A4", "국4절" → "B3"
     *
     * @param string $pnTypeName PN_type_name 필드 값
     * @return string|null 규격 코드 (A4, B4 등) 또는 null
     */
    public static function extractSpecFromPnType(string $pnTypeName): ?string {
        return QuantityFormatter::extractSpecName($pnTypeName);
    }

    /**
     * 전단지/리플렛 매수 자동 계산 (규격 기반)
     *
     * PN_type_name에서 규격을 추출하고, paper_standard_master에서 조회하여
     * 정확한 매수를 계산합니다. DB 조회 실패 시 레거시 방식으로 폴백합니다.
     *
     * @param mysqli $db DB 연결
     * @param array $data 주문 데이터 (PN_type_name, MY_amount 포함)
     * @return int 계산된 매수
     *
     * @example
     * // inserted/add_to_basket.php에서 사용
     * $data = ['PN_type_name' => 'A4 (210×297)', 'MY_amount' => 0.5];
     * $sheets = DataAdapter::calculateInsertedSheets($db, $data);  // 2000
     */
    public static function calculateInsertedSheets($db, array $data): int {
        $reams = floatval($data['MY_amount'] ?? 0);
        if ($reams <= 0) {
            return 0;
        }

        // 1. PN_type_name에서 규격 추출 시도
        $pnTypeName = $data['PN_type_name'] ?? $data['spec_size'] ?? '';
        $specName = self::extractSpecFromPnType($pnTypeName);

        // 2. 규격이 있으면 paper_standard_master에서 조회
        if ($specName) {
            $sheets = self::calculateSheetsBySpec($db, $specName, $reams);
            if ($sheets > 0) {
                return $sheets;
            }
        }

        // 3. Fallback: mesu 필드 또는 레거시 계산
        if (!empty($data['mesu'])) {
            return intval($data['mesu']);
        }

        // 4. Fallback: mlangprintauto_inserted 테이블 조회 (기존 방식)
        // 이 부분은 OrderDataService에서 처리
        return 0;
    }

    /**
     * 규격 정보 조회 (SSOT Wrapper)
     *
     * @param mysqli $db DB 연결
     * @param string $specName 규격명
     * @return array|null 규격 정보
     */
    public static function getPaperStandard($db, string $specName): ?array {
        return QuantityFormatter::getPaperStandard($db, $specName);
    }

    /**
     * 모든 규격 목록 조회 (SSOT Wrapper)
     *
     * @param mysqli $db DB 연결
     * @param string|null $series A 또는 B (null이면 전체)
     * @return array 규격 목록
     */
    public static function getAllPaperStandards($db, ?string $series = null): array {
        return QuantityFormatter::getAllPaperStandards($db, $series);
    }

    // ========== Grand Design Methods (2026-01-13) ==========

    /**
     * ✅ Grand Design: 레거시 데이터를 정규화된 스키마로 변환
     *
     * 새 order_items 테이블 형식으로 변환:
     * - qty_value (DECIMAL): 수량 값
     * - qty_unit_code (CHAR): R/S/B/V/P/E
     * - qty_sheets (INT): 전단지 등 실제 매수
     *
     * @param array $legacyData 레거시 필드 배열 (mlangorder_printauto 또는 shop_temp)
     * @param string $productType 제품 타입
     * @return array order_items 테이블 형식의 정규화된 데이터
     */
    public static function legacyToNormalized(array $legacyData, string $productType): array {
        // ✅ Type_1 파싱 (JSON 또는 파이프 구분 텍스트)
        if (!empty($legacyData['Type_1']) && is_string($legacyData['Type_1'])) {
            $type1Str = trim($legacyData['Type_1']);

            // 1. JSON 형식 시도
            $type1Data = json_decode($type1Str, true);
            if ($type1Data && is_array($type1Data)) {
                // order_details 중첩 구조 처리
                if (isset($type1Data['order_details']) && is_array($type1Data['order_details'])) {
                    $type1Data = array_merge($type1Data, $type1Data['order_details']);
                }
                // DB 컬럼 값이 없는 경우에만 Type_1 값 사용
                foreach ($type1Data as $key => $value) {
                    if ($value !== '' && $value !== null && (!isset($legacyData[$key]) || $legacyData[$key] === '' || $legacyData[$key] === null)) {
                        $legacyData[$key] = $value;
                    }
                }
                // product_type이 없으면 Type_1에서 가져옴
                if (empty($productType) && !empty($type1Data['product_type'])) {
                    $productType = $type1Data['product_type'];
                }
            }
            // 2. 파이프(|) 구분 레거시 형식 시도
            // 예: "아트코팅 스티카|크기: 90 x 50mm |매수: 1000 매|사각"
            elseif (strpos($type1Str, '|') !== false) {
                $parts = explode('|', $type1Str);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;

                    // 매수 파싱: "매수: 1000 매" 또는 "1000 매"
                    if (preg_match('/매수[:\s]*(\d+)/u', $part, $m)) {
                        $legacyData['mesu'] = intval($m[1]);
                    } elseif (preg_match('/^(\d+)\s*매$/u', $part, $m)) {
                        $legacyData['mesu'] = intval($m[1]);
                    }
                    // 크기 파싱: "크기: 90 x 50mm"
                    elseif (preg_match('/크기[:\s]*(.+)/u', $part, $m)) {
                        $legacyData['spec_size'] = trim($m[1]);
                    }
                    // 수량 파싱: "수량: 1연"
                    elseif (preg_match('/수량[:\s]*(.+)/u', $part, $m)) {
                        $legacyData['MY_amount'] = trim($m[1]);
                    }
                }
            }
        }

        // 기존 표준 변환 (spec_type, spec_material 등)
        $standard = self::legacyToStandard($legacyData, $productType);

        // QuantityFormatter로 수량 정보 추출 (SSOT)
        $qtyInfo = QuantityFormatter::extractFromLegacy($legacyData, $productType);

        // 가격 정보 추출
        $priceSupply = intval($standard['price_supply'] ?? 0);
        $priceVat = intval($standard['price_vat'] ?? 0);

        // 정규화된 order_items 형식으로 반환
        return [
            // 제품 정보
            'product_type' => $productType,
            'product_type_display' => ProductSpecFormatter::getProductTypeName($productType),

            // 규격 정보 (표준 필드 그대로 사용)
            'spec_type' => $standard['spec_type'] ?? null,
            'spec_material' => $standard['spec_material'] ?? null,
            'spec_size' => $standard['spec_size'] ?? null,
            'spec_sides' => $standard['spec_sides'] ?? null,
            'spec_design' => $standard['spec_design'] ?? null,

            // 수량 정보 (QuantityFormatter SSOT)
            'qty_value' => $qtyInfo['qty_value'],
            'qty_unit_code' => $qtyInfo['qty_unit_code'],
            'qty_sheets' => $qtyInfo['qty_sheets'],

            // 가격 정보
            'price_supply' => $priceSupply,
            'price_vat' => $priceVat,
            'price_unit' => $priceSupply > 0 && $qtyInfo['qty_value'] > 0
                ? intval($priceSupply / $qtyInfo['qty_value'])
                : null,

            // 파일 정보 (DB 컬럼은 대문자: ImgFolder, ThingCate)
            'img_folder' => $legacyData['ImgFolder'] ?? $legacyData['img_folder'] ?? null,
            'thing_cate' => $legacyData['ThingCate'] ?? $legacyData['thing_cate'] ?? null,

            // 메타 정보 (DB 컬럼은 OrderStyle)
            'ordertype' => $legacyData['OrderStyle'] ?? $legacyData['ordertype'] ?? null,
            'work_memo' => $legacyData['memo'] ?? $legacyData['work_memo'] ?? null,

            // 레거시 데이터 보존 (롤백용)
            'legacy_data' => json_encode($legacyData, JSON_UNESCAPED_UNICODE),

            // 추가 옵션 (제품별로 다름)
            'additional_options' => self::extractOptions($standard, $productType)
        ];
    }

    /**
     * ✅ Grand Design: 옵션 정보 추출
     *
     * @param array $standard 표준 데이터
     * @param string $productType 제품 타입
     * @return array|null order_options 테이블용 데이터
     */
    private static function extractOptions(array $standard, string $productType): ?array {
        $options = [];

        // 코팅/접지/오시 옵션 (전단지, 카다록, 포스터)
        if (!empty($standard['additional_options'])) {
            $addOpts = is_string($standard['additional_options'])
                ? json_decode($standard['additional_options'], true)
                : $standard['additional_options'];

            if (!empty($addOpts['coating_enabled'])) {
                $options[] = [
                    'category' => 'coating',
                    'type' => $addOpts['coating_type'] ?? null,
                    'price' => intval($addOpts['coating_price'] ?? 0)
                ];
            }
            if (!empty($addOpts['folding_enabled'])) {
                $options[] = [
                    'category' => 'folding',
                    'type' => $addOpts['folding_type'] ?? null,
                    'price' => intval($addOpts['folding_price'] ?? 0)
                ];
            }
            if (!empty($addOpts['creasing_enabled'])) {
                $options[] = [
                    'category' => 'creasing',
                    'value' => $addOpts['creasing_lines'] ?? null,
                    'price' => intval($addOpts['creasing_price'] ?? 0)
                ];
            }
        }

        // 프리미엄 옵션 (명함, 상품권, NCR)
        if (!empty($standard['premium_options'])) {
            $premOpts = is_string($standard['premium_options'])
                ? json_decode($standard['premium_options'], true)
                : $standard['premium_options'];

            if (!empty($premOpts['foil_enabled'])) {
                $options[] = [
                    'category' => 'premium',
                    'type' => 'foil_' . ($premOpts['foil_type'] ?? 'gold'),
                    'price' => intval($premOpts['foil_price'] ?? 0)
                ];
            }
            if (!empty($premOpts['numbering_enabled'])) {
                $options[] = [
                    'category' => 'premium',
                    'type' => 'numbering',
                    'value' => $premOpts['numbering_count'] ?? null,
                    'price' => intval($premOpts['numbering_price'] ?? 0)
                ];
            }
            if (!empty($premOpts['perforation_enabled'])) {
                $options[] = [
                    'category' => 'premium',
                    'type' => 'perforation',
                    'value' => $premOpts['perforation_count'] ?? null,
                    'price' => intval($premOpts['perforation_price'] ?? 0)
                ];
            }
        }

        // 봉투 양면테이프
        if ($productType === 'envelope' && !empty($standard['envelope_tape_enabled'])) {
            $options[] = [
                'category' => 'envelope_tape',
                'value' => $standard['envelope_tape_quantity'] ?? null,
                'price' => intval($standard['envelope_tape_price'] ?? 0)
            ];
        }

        return !empty($options) ? $options : null;
    }

    /**
     * ✅ Grand Design: 정규화된 데이터를 표시용으로 변환
     *
     * @param array $normalizedData order_items 형식의 데이터
     * @return array 표시용 데이터 (quantity_display 등 포함)
     */
    public static function normalizedToDisplay(array $normalizedData): array {
        $display = $normalizedData;

        // QuantityFormatter로 표시 문자열 생성 (SSOT)
        if (!empty($normalizedData['qty_value']) && !empty($normalizedData['qty_unit_code'])) {
            $display['quantity_display'] = QuantityFormatter::format(
                floatval($normalizedData['qty_value']),
                $normalizedData['qty_unit_code'],
                $normalizedData['qty_sheets'] ?? null
            );
        }

        return $display;
    }
}
?>
