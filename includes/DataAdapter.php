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
            return self::$method($legacy_data);
        }

        // 알 수 없는 제품 타입: 기본 변환
        return self::convertGeneric($legacy_data);
    }

    /**
     * 명함 (namecard) 변환
     * MY_type=종류, Section=용지, POtype=인쇄면, MY_amount=매수(천단위)
     */
    private static function convertNamecard($data) {
        $amount = floatval($data['MY_amount'] ?? 0);
        $qty_value = $amount > 0 && $amount < 10 ? $amount * 1000 : intval($amount);

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['Section_name'] ?? '',
            'spec_size' => '90x50mm',  // 명함 고정 규격
            'spec_sides' => $data['POtype_name'] ?? ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $qty_value,
            'quantity_unit' => '매',
            'quantity_sheets' => $qty_value,
            'quantity_display' => number_format($qty_value) . '매',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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

        return [
            'spec_type' => $domusong && $domusong !== '0' ? $domusong : '사각',
            'spec_material' => $jong,
            'spec_size' => $size,
            'spec_sides' => '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $mesu,
            'quantity_unit' => '매',
            'quantity_sheets' => $mesu,
            'quantity_display' => number_format($mesu),  // 단위 없이 숫자만
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

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['MY_Fsd_name'] ?? '',
            'spec_size' => $data['PN_type_name'] ?? '',
            'spec_sides' => $data['POtype_name'] ?? ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $reams,
            'quantity_unit' => '연',
            'quantity_sheets' => $sheets,
            'quantity_display' => $qty_display,
            'price_supply' => intval($data['Order_PriceForm'] ?? $data['price'] ?? 0),
            'price_vat' => intval($data['Total_PriceForm'] ?? $data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['Total_PriceForm'] ?? 0) - intval($data['Order_PriceForm'] ?? 0),
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

        // ★ Phase 2: 드롭다운 텍스트 우선 사용 (quantity_display)
        $quantity_display = !empty($data['quantity_display'])
            ? $data['quantity_display']
            : number_format($qty_value) . '매';

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['Section_name'] ?? '',
            'spec_size' => '',
            'spec_sides' => $data['POtype_name'] ?? '',  // 봉투: 인쇄 색상(마스터1도/마스터2도/칼라4도)
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $qty_value,
            'quantity_unit' => '매',
            'quantity_sheets' => $qty_value,
            'quantity_display' => $quantity_display,
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => '',
            'spec_size' => $data['Section_name'] ?? '',
            'spec_sides' => $data['POtype_name'] ?? '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '부',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '부',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['Section_name'] ?? '',
            'spec_size' => $data['PN_type_name'] ?? '',
            'spec_sides' => '',
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '장',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '장',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => '',
            'spec_size' => $data['Section_name'] ?? '',
            'spec_sides' => $data['POtype_name'] ?? ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '매',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '매',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
            'product_type' => 'msticker'
        ];
    }

    /**
     * NCR양식지 (ncrflambeau) 변환
     * MY_type=도수, MY_Fsd=용지, PN_type=타입, MY_amount=수량
     */
    private static function convertNcrflambeau($data) {
        $amount = intval($data['MY_amount'] ?? 0);

        return [
            'spec_type' => $data['PN_type_name'] ?? '',  // 타입
            'spec_material' => $data['MY_Fsd_name'] ?? '',  // 용지
            'spec_size' => '',
            'spec_sides' => $data['MY_type_name'] ?? '',  // 도수
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '권',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '권',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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

        return [
            'spec_type' => $data['MY_type_name'] ?? '',
            'spec_material' => $data['Section_name'] ?? '',
            'spec_size' => '',
            'spec_sides' => $data['POtype_name'] ?? ($data['POtype'] == '1' ? '단면' : '양면'),
            'spec_design' => ($data['ordertype'] ?? '') === 'total' ? '디자인+인쇄' : '인쇄만',
            'quantity_value' => $amount,
            'quantity_unit' => '매',
            'quantity_sheets' => $amount,
            'quantity_display' => number_format($amount) . '매',
            'price_supply' => intval($data['price'] ?? 0),
            'price_vat' => intval($data['vat_price'] ?? 0),
            'price_vat_amount' => intval($data['vat_price'] ?? 0) - intval($data['price'] ?? 0),
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
