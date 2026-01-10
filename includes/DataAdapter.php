<?php
/**
 * Data Adapter - 레거시 필드 ↔ 표준 필드 변환
 * Phase 2: Dual-Write 전략의 핵심 컴포넌트
 *
 * 11개 제품의 서로 다른 필드명을 표준화된 필드로 변환
 * 예: namecard MY_type → spec_type, sticker jong → spec_material
 */

class DataAdapter {

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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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
        // 가격이 문자열로 저장된 경우 정수로 변환
        $price_supply = is_numeric($data['price'] ?? 0) ? intval($data['price']) : 0;
        $price_vat = is_numeric($data['price_vat'] ?? 0) ? intval($data['price_vat']) : 0;

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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['Order_PriceForm'] ?? $data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['Total_PriceForm'] ?? $data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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
     * MY_type=종류, Section=규격, POtype=인쇄면, MY_amount=수량
     */
    private static function convertCadarok($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // ✅ Phase 3: Frontend에서 보낸 quantity_display 우선 사용 (inserted 방식과 동일)
        $quantity_display = !empty($data['quantity_display'])
            ? $data['quantity_display']
            : number_format($amount) . '부';

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

        return [
            'spec_type' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),
            'spec_material' => '',
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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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
     */
    private static function convertNcrflambeau($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

        return [
            'spec_type' => $data['PN_type_name'] ?: ($data['PN_type'] ?? ''),  // 타입
            'spec_material' => $data['MY_Fsd_name'] ?: ($data['MY_Fsd'] ?? ''),  // 용지
            'spec_size' => '',
            'spec_sides' => $data['MY_type_name'] ?: ($data['MY_type'] ?? ''),  // 도수
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '권',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '권',
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

        // 가격 필드 fallback: shop_temp는 st_price/st_price_vat 사용
        $price_supply = intval($data['price'] ?? $data['st_price'] ?? 0);
        $price_vat = intval($data['vat_price'] ?? $data['st_price_vat'] ?? 0);

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
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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
}
?>
