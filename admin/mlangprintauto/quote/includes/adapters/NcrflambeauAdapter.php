<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class NcrflambeauAdapter implements QuoteAdapterInterface
{
    public function getProductType()
    {
        return 'ncrflambeau';
    }

    public function getProductName()
    {
        return 'NCR양식';
    }

    public function getDefaultUnit()
    {
        return '권';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/ncrflambeau/calculate_price_ajax.php';
    }

    public function getPriceMethod()
    {
        return 'POST';
    }

    public function mapToPriceParams(array $formParams)
    {
        return [
            'MY_type' => $formParams['MY_type'] ?? '',
            'MY_Fsd' => $formParams['MY_Fsd'] ?? '',
            'PN_type' => $formParams['PN_type'] ?? '',
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
        $payload->quantity_display = number_format($amount) . '권';

        $data = $priceResponse['data'] ?? $priceResponse;

        $supply = $data['total_price'] ?? $data['order_price'] ?? 0;
        $payload->supply_price = intval($this->extractNumeric($supply));

        $vatRaw = $data['vat_price'] ?? $data['total_with_vat'] ?? 0;
        $vatParsed = intval($this->extractNumeric($vatRaw));

        // NCR returns vat_price as VAT-inclusive total in legacy format
        if ($vatParsed > $payload->supply_price) {
            $payload->total_price = $vatParsed;
            $payload->vat_price = $vatParsed - $payload->supply_price;
        } elseif ($vatParsed > 0) {
            $payload->vat_price = $vatParsed;
            $payload->total_price = $payload->supply_price + $vatParsed;
        } else {
            $payload->calculateVat();
        }

        $payload->calculateUnitPrice();

        $payload->specification = $this->buildSpecification($calcParams);

        $options = [];
        $additionalTotal = intval($calcParams['additional_options_total'] ?? 0);
        if ($additionalTotal > 0) {
            $options['additional_options_total'] = $additionalTotal;
        }
        $payload->options = $options;

        $payload->raw_params = [
            'MY_type' => $calcParams['MY_type'] ?? '',
            'MY_Fsd' => $calcParams['MY_Fsd'] ?? '',
            'PN_type' => $calcParams['PN_type'] ?? '',
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

        $paperName = $calcParams['spec_material'] ?? $calcParams['MY_Fsd_name'] ?? '';
        if (!empty($paperName)) {
            $line1Parts[] = $paperName;
        }

        $line2Parts = [];

        $sidesName = $calcParams['spec_sides'] ?? $calcParams['PN_type_name'] ?? '';
        if (!empty($sidesName)) {
            $line2Parts[] = $sidesName;
        }

        $amount = intval($calcParams['MY_amount'] ?? 0);
        if ($amount > 0) {
            $line2Parts[] = number_format($amount) . '권';
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
