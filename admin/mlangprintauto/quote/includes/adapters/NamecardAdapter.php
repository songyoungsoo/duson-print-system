<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class NamecardAdapter implements QuoteAdapterInterface
{
    private static $premiumOptionNames = [
        'foil' => '박',
        'numbering' => '넘버링',
        'perforation' => '타공',
        'rounding' => '귀돌이',
        'creasing' => '오시',
    ];

    public function getProductType()
    {
        return 'namecard';
    }

    public function getProductName()
    {
        return '명함';
    }

    public function getDefaultUnit()
    {
        return '매';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/namecard/calculate_price_ajax.php';
    }

    public function getPriceMethod()
    {
        return 'GET';
    }

    public function mapToPriceParams(array $formParams)
    {
        $params = [
            'MY_type' => $formParams['MY_type'] ?? '',
            'Section' => $formParams['Section'] ?? '',
            'POtype' => $formParams['POtype'] ?? '',
            'MY_amount' => $formParams['MY_amount'] ?? '',
            'ordertype' => $formParams['ordertype'] ?? '',
        ];

        if (!empty($formParams['premium_options_total'])) {
            $params['premium_options_total'] = $formParams['premium_options_total'];
        }
        if (!empty($formParams['premium_options'])) {
            $params['premium_options'] = $formParams['premium_options'];
        }

        return $params;
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

        $supply = $priceResponse['total_supply_price']
            ?? $priceResponse['total_price']
            ?? $priceResponse['order_price']
            ?? 0;
        $payload->supply_price = intval($this->extractNumeric($supply));

        $total = $priceResponse['final_total_with_vat']
            ?? $priceResponse['total_with_vat']
            ?? $priceResponse['vat_price']
            ?? 0;
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
        $premiumTotal = intval($calcParams['premium_options_total'] ?? $priceResponse['premium_total'] ?? 0);
        if ($premiumTotal > 0) {
            $options['premium_options_total'] = $premiumTotal;
        }
        $premiumDetails = $priceResponse['premium_details'] ?? [];
        if (!empty($premiumDetails)) {
            $options['premium_details'] = $premiumDetails;
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

        $amount = intval($calcParams['MY_amount'] ?? 0);
        if ($amount > 0) {
            $line2Parts[] = number_format($amount) . '매';
        }

        $ordertype = $calcParams['ordertype'] ?? '';
        $designMap = ['print' => '인쇄만', 'design' => '디자인+인쇄'];
        $designName = $calcParams['spec_design'] ?? ($designMap[$ordertype] ?? '');
        if (!empty($designName)) {
            $line2Parts[] = $designName;
        }

        $premiumNames = $this->buildPremiumOptionNames($calcParams);
        if (!empty($premiumNames)) {
            $line2Parts[] = $premiumNames;
        }

        $line1 = implode(' / ', array_filter($line1Parts));
        $line2 = implode(' / ', array_filter($line2Parts));

        if (!empty($line1) && !empty($line2)) {
            return $line1 . "\n" . $line2;
        }
        return $line1 . $line2;
    }

    private function buildPremiumOptionNames(array $calcParams)
    {
        $premiumJson = $calcParams['premium_options'] ?? '';
        if (empty($premiumJson)) {
            return '';
        }

        $decoded = is_array($premiumJson) ? $premiumJson : json_decode($premiumJson, true);
        if (!is_array($decoded) || empty($decoded)) {
            return '';
        }

        $names = [];
        foreach ($decoded as $key => $info) {
            $name = self::$premiumOptionNames[$key] ?? $key;
            $names[] = $name;
        }

        return !empty($names) ? implode('+', $names) : '';
    }

    private function extractNumeric($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return preg_replace('/[^0-9.]/', '', strval($value));
    }
}
