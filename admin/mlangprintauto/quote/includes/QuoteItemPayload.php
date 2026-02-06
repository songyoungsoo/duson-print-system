<?php
/**
 * QuoteItemPayload - 견적서 품목 표준 페이로드 데이터 클래스
 *
 * 모든 9개 품목 계산기가 생성하는 데이터를 이 형식으로 정규화합니다.
 * PDF/이메일/웹 출력, quotation_temp 저장, postMessage 통신에 모두 사용됩니다.
 *
 * PHP 7.4 호환 (typed properties 사용 가능, union types 불가)
 *
 * @since Phase 1 - Standard Interface Layer
 */
class QuoteItemPayload
{
    // ===== Required fields =====

    /** @var string 제품 유형 식별자 ('sticker', 'inserted', 'namecard', etc.) */
    public $product_type = '';

    /** @var string 제품 한글명 ('스티커', '전단지', '명함', etc.) */
    public $product_name = '';

    /** @var string 규격/사양 (2줄 형식: "Line1\nLine2") */
    public $specification = '';

    /** @var float 수량 (숫자, 예: 0.5, 1000) */
    public $quantity = 0.0;

    /** @var string 단위 ('매', '연', '부', '권') */
    public $unit = '개';

    /** @var string 수량 표시 문자열 ("0.5연 (250매)", "1,000매") */
    public $quantity_display = '';

    /** @var float 단가 (개당 가격) */
    public $unit_price = 0.0;

    /** @var int 공급가액 (VAT 제외) */
    public $supply_price = 0;

    /** @var int VAT 금액 (supply_price * 0.1) */
    public $vat_price = 0;

    /** @var int 합계 (supply_price + vat_price) */
    public $total_price = 0;

    /** @var array 제품별 옵션 (프리미엄 옵션, 테이프, 코팅 등) */
    public $options = [];

    /** @var array 원본 계산기 파라미터 (DB 저장용: MY_type, Section, etc.) */
    public $raw_params = [];

    // ===== Optional fields =====

    /** @var int 전단지 전용: 매수 (sheets count) */
    public $qty_sheets = 0;

    /**
     * 배열로 변환 (JSON/postMessage용)
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            'product_type' => $this->product_type,
            'product_name' => $this->product_name,
            'specification' => $this->specification,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'quantity_display' => $this->quantity_display,
            'unit_price' => $this->unit_price,
            'supply_price' => $this->supply_price,
            'vat_price' => $this->vat_price,
            'total_price' => $this->total_price,
            'options' => $this->options,
            'raw_params' => $this->raw_params,
        ];

        // 전단지 매수가 있으면 포함
        if ($this->qty_sheets > 0) {
            $data['qty_sheets'] = $this->qty_sheets;
        }

        return $data;
    }

    /**
     * postMessage 페이로드에서 인스턴스 생성
     *
     * @param array $data postMessage로 받은 데이터
     * @return self
     */
    public static function fromPostMessagePayload(array $data)
    {
        $payload = new self();

        $payload->product_type = strval($data['product_type'] ?? '');
        $payload->product_name = strval($data['product_name'] ?? '');
        $payload->specification = strval($data['specification'] ?? '');
        $payload->quantity = floatval($data['quantity'] ?? 0);
        $payload->unit = strval($data['unit'] ?? '개');
        $payload->quantity_display = strval($data['quantity_display'] ?? '');
        $payload->unit_price = floatval($data['unit_price'] ?? 0);
        $payload->supply_price = intval($data['supply_price'] ?? 0);
        $payload->vat_price = intval($data['vat_price'] ?? 0);
        $payload->total_price = intval($data['total_price'] ?? 0);
        $payload->options = is_array($data['options'] ?? null) ? $data['options'] : [];
        $payload->raw_params = is_array($data['raw_params'] ?? null) ? $data['raw_params'] : [];
        $payload->qty_sheets = intval($data['qty_sheets'] ?? 0);

        return $payload;
    }

    /**
     * 배열에서 인스턴스 생성 (DB 레코드 등)
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        return self::fromPostMessagePayload($data);
    }

    /**
     * VAT 자동 계산 (supply_price 기반)
     * 저장 직전에 호출하여 vat_price, total_price를 일관되게 설정
     *
     * @return self
     */
    public function calculateVat()
    {
        $this->vat_price = intval(round($this->supply_price * 0.1));
        $this->total_price = $this->supply_price + $this->vat_price;
        return $this;
    }

    /**
     * 단가 역산 (supply_price / quantity)
     * 역산 불가능하면 0 반환
     *
     * @return self
     */
    public function calculateUnitPrice()
    {
        if ($this->quantity > 0) {
            $calculated = $this->supply_price / $this->quantity;
            // 역산 검증: 단가 × 수량 = 공급가액
            if (intval(round($calculated * $this->quantity)) === $this->supply_price) {
                $this->unit_price = round($calculated, 2);
            } else {
                $this->unit_price = 0;
            }
        } else {
            $this->unit_price = 0;
        }
        return $this;
    }

    /**
     * 유효성 검증
     *
     * @return array 오류 메시지 배열 (비어있으면 유효)
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->product_type)) {
            $errors[] = 'product_type은 필수입니다.';
        }
        if (empty($this->product_name)) {
            $errors[] = 'product_name은 필수입니다.';
        }
        if ($this->supply_price < 0) {
            $errors[] = 'supply_price는 0 이상이어야 합니다.';
        }
        if ($this->quantity < 0) {
            $errors[] = 'quantity는 0 이상이어야 합니다.';
        }

        return $errors;
    }
}
