<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class MstickerAdapter implements QuoteAdapterInterface
{
    public function getProductType()
    {
        return 'msticker';
    }

    public function getProductName()
    {
        return '자석스티커';
    }

    public function getDefaultUnit()
    {
        return '매';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/msticker/calculate_price_ajax.php';
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

        $amount = floatval($calcParams['MY_amount'] ?? $calcParams['mesu'] ?? 0);
        $payload->quantity = $amount;
        $payload->quantity_display = number_format($amount) . '매';

        $supply = $priceResponse['total_price'] ?? $priceResponse['order_price'] ?? 0;
        $payload->supply_price = intval($this->extractNumeric($supply));

        $total = $priceResponse['total_with_vat'] ?? 0;
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
        $jong = $calcParams['jong'] ?? '';
        $garo = $calcParams['garo'] ?? '';
        $sero = $calcParams['sero'] ?? '';
        $mesu = intval($calcParams['mesu'] ?? $calcParams['MY_amount'] ?? 0);
        $domusong = $calcParams['domusong'] ?? '';

        $line1Parts = [];
        if (!empty($jong)) {
            $line1Parts[] = $jong;
        }
        if (!empty($garo) && !empty($sero)) {
            $line1Parts[] = $garo . 'x' . $sero . 'mm';
        }

        $line2Parts = [];
        if ($mesu > 0) {
            $line2Parts[] = number_format($mesu) . '매';
        }
        if (!empty($domusong)) {
            $shape = preg_replace('/^\d+\s*/', '', $domusong);
            if (!empty($shape)) {
                $line2Parts[] = $shape;
            }
        }

        $typeName = $calcParams['spec_type'] ?? $calcParams['MY_type_name'] ?? '';
        if (!empty($typeName) && empty($jong)) {
            $line1Parts[] = $typeName;
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
