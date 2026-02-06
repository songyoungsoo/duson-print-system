<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class MerchandisebondAdapter implements QuoteAdapterInterface
{
    public function getProductType()
    {
        return 'merchandisebond';
    }

    public function getProductName()
    {
        return '상품권';
    }

    public function getDefaultUnit()
    {
        return '매';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/merchandisebond/calculate_price_ajax.php';
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

        $amount = floatval($calcParams['MY_amount'] ?? 0);
        $payload->quantity = $amount;
        $payload->quantity_display = number_format($amount) . '매';

        $data = $priceResponse['data'] ?? $priceResponse;

        $supply = $data['total_price'] ?? $data['order_price']
            ?? $data['Order_PriceForm'] ?? $data['PriceForm'] ?? 0;
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

        $payload->options = [];

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

        $amount = intval($calcParams['MY_amount'] ?? 0);
        if ($amount > 0) {
            $line2Parts[] = number_format($amount) . '매';
        }

        $ordertype = $calcParams['ordertype'] ?? '';
        $designMap = ['print' => '인쇄만', 'design' => '디자인+인쇄'];
        $designName = $designMap[$ordertype] ?? '';
        if (!empty($designName)) {
            $line2Parts[] = $designName;
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
