<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class EnvelopeAdapter implements QuoteAdapterInterface
{
    public function getProductType()
    {
        return 'envelope';
    }

    public function getProductName()
    {
        return '봉투';
    }

    public function getDefaultUnit()
    {
        return '매';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/envelope/calculate_price_ajax.php';
    }

    public function getPriceMethod()
    {
        return 'GET';
    }

    public function mapToPriceParams(array $formParams)
    {
        return [
            'MY_type' => $formParams['MY_type'] ?? '',
            'Section' => $formParams['Section'] ?? '',
            'POtype' => $formParams['POtype'] ?? '',
            'MY_amount' => $formParams['MY_amount'] ?? '',
            'ordertype' => $formParams['ordertype'] ?? '',
        ];
    }

    public function normalize(array $calcParams, array $priceResponse)
    {
        $payload = new QuoteItemPayload();

        $payload->product_type = $this->getProductType();
        $payload->product_name = $this->getProductName();
        $payload->unit = $this->getDefaultUnit();

        $amount = floatval($calcParams['MY_amount'] ?? $calcParams['quantity'] ?? 0);
        $payload->quantity = $amount;
        $payload->quantity_display = number_format($amount) . '매';

        $data = $priceResponse['data'] ?? $priceResponse;

        $supply = $data['order_price'] ?? $data['total_price'] ?? $data['Order_PriceForm'] ?? 0;
        $payload->supply_price = intval($this->extractNumeric($supply));

        $total = $data['total_with_vat'] ?? $data['Total_PriceForm'] ?? 0;
        $totalParsed = intval($this->extractNumeric($total));
        if ($totalParsed > 0) {
            $payload->total_price = $totalParsed;
            $payload->vat_price = $totalParsed - $payload->supply_price;
        } else {
            $payload->calculateVat();
        }

        $payload->calculateUnitPrice();

        $payload->specification = $this->buildSpecification($calcParams);

        $options = [];
        $tapeEnabled = !empty($calcParams['envelope_tape_enabled']);
        if ($tapeEnabled) {
            $options['envelope_tape_enabled'] = 1;
            $options['envelope_tape_quantity'] = intval($calcParams['envelope_tape_quantity'] ?? 0);
            $options['envelope_tape_price'] = intval($calcParams['envelope_tape_price'] ?? 0);
        }
        $additionalTotal = intval($data['additional_options_total'] ?? $priceResponse['additional_options_price'] ?? 0);
        if ($additionalTotal > 0) {
            $options['additional_options_total'] = $additionalTotal;
        }
        $payload->options = $options;

        $payload->raw_params = [
            'MY_type' => $calcParams['MY_type'] ?? '',
            'Section' => $calcParams['Section'] ?? '',
            'POtype' => $calcParams['POtype'] ?? '',
            'MY_amount' => $calcParams['MY_amount'] ?? '',
            'ordertype' => $calcParams['ordertype'] ?? '',
        ];

        return $payload;
    }

    public function buildSpecification(array $calcParams)
    {
        $line1Parts = [];

        $typeName = $calcParams['spec_type'] ?? $calcParams['MY_type_name'] ?? '';
        if (!empty($typeName)) {
            $line1Parts[] = $typeName;
        }

        $materialName = $calcParams['spec_material'] ?? $calcParams['Section_name'] ?? '';
        if (!empty($materialName)) {
            $line1Parts[] = $materialName;
        }

        $line2Parts = [];

        $sidesName = $calcParams['spec_sides'] ?? $calcParams['POtype_name'] ?? '';
        if (!empty($sidesName)) {
            $line2Parts[] = $sidesName;
        }

        $ordertype = $calcParams['ordertype'] ?? '';
        $designMap = ['print' => '인쇄만', 'total' => '디자인+인쇄'];
        $designName = $designMap[$ordertype] ?? '';
        if (!empty($designName)) {
            $line2Parts[] = $designName;
        }

        $tapeEnabled = !empty($calcParams['envelope_tape_enabled']);
        if ($tapeEnabled) {
            $line2Parts[] = '양면테이프';
        }

        $line1 = implode(' / ', array_filter($line1Parts));
        $line2 = implode(' / ', array_filter($line2Parts));

        if (!empty($line1) && !empty($line2)) {
            return $line1 . "\n" . $line2;
        }
        return $line1 . $line2;
    }

    private function extractNumeric($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return preg_replace('/[^0-9.]/', '', strval($value));
    }
}
