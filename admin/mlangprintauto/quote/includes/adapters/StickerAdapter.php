<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class StickerAdapter implements QuoteAdapterInterface
{
    private static $uhyungMap = [
        0 => '기본편집',
        1 => '인쇄만',
        2 => '편집디자인',
    ];

    public function getProductType()
    {
        return 'sticker';
    }

    public function getProductName()
    {
        return '스티커';
    }

    public function getDefaultUnit()
    {
        return '매';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/sticker_new/calculate_price.php';
    }

    public function getPriceMethod()
    {
        return 'POST';
    }

    public function mapToPriceParams(array $formParams)
    {
        return [
            'action' => 'calculate',
            'jong' => $formParams['jong'] ?? '',
            'garo' => $formParams['garo'] ?? '',
            'sero' => $formParams['sero'] ?? '',
            'mesu' => $formParams['mesu'] ?? '',
            'uhyung' => $formParams['uhyung'] ?? '0',
            'domusong' => $formParams['domusong'] ?? '',
        ];
    }

    public function normalize(array $calcParams, array $priceResponse)
    {
        $payload = new QuoteItemPayload();

        $payload->product_type = $this->getProductType();
        $payload->product_name = $this->getProductName();
        $payload->unit = $this->getDefaultUnit();

        $mesu = intval($calcParams['mesu'] ?? 0);
        $payload->quantity = floatval($mesu);
        $payload->quantity_display = number_format($mesu) . '매';

        $data = $priceResponse['data'] ?? $priceResponse;

        $priceRaw = $data['st_price'] ?? $data['base_price'] ?? $data['price'] ?? $priceResponse['price'] ?? 0;
        $payload->supply_price = intval($this->extractNumeric($priceRaw));

        $vatRaw = $data['st_price_vat'] ?? $data['total_with_vat'] ?? $data['price_vat'] ?? $priceResponse['price_vat'] ?? 0;
        $vatParsed = intval($this->extractNumeric($vatRaw));
        if ($vatParsed > 0) {
            $payload->total_price = $vatParsed;
            $payload->vat_price = $vatParsed - $payload->supply_price;
        } else {
            $payload->calculateVat();
        }

        $payload->calculateUnitPrice();

        $payload->specification = $this->buildSpecification($calcParams);

        $payload->options = [];

        $payload->raw_params = [
            'jong' => $calcParams['jong'] ?? '',
            'garo' => $calcParams['garo'] ?? '',
            'sero' => $calcParams['sero'] ?? '',
            'mesu' => $mesu,
            'uhyung' => intval($calcParams['uhyung'] ?? 0),
            'domusong' => $calcParams['domusong'] ?? '',
        ];

        return $payload;
    }

    public function buildSpecification(array $calcParams)
    {
        $jong = $calcParams['jong'] ?? '';
        $garo = $calcParams['garo'] ?? '';
        $sero = $calcParams['sero'] ?? '';
        $domusong = $calcParams['domusong'] ?? '';
        $mesu = intval($calcParams['mesu'] ?? 0);
        $uhyung = intval($calcParams['uhyung'] ?? 0);

        $line1Parts = [];
        if (!empty($jong)) {
            $line1Parts[] = $jong;
        }
        if (!empty($garo) && !empty($sero)) {
            $line1Parts[] = $garo . 'x' . $sero . 'mm';
        }
        if (!empty($domusong)) {
            $shape = preg_replace('/^\d+\s*/', '', $domusong);
            if (!empty($shape)) {
                $line1Parts[] = $shape;
            }
        }

        $line2Parts = [];
        if ($mesu > 0) {
            $line2Parts[] = number_format($mesu) . '매';
        }
        $uhyungName = self::$uhyungMap[$uhyung] ?? '';
        if (!empty($uhyungName)) {
            $line2Parts[] = $uhyungName;
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
