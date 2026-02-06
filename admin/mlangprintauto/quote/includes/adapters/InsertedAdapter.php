<?php
require_once __DIR__ . '/../QuoteAdapterInterface.php';
require_once __DIR__ . '/../QuoteItemPayload.php';

class InsertedAdapter implements QuoteAdapterInterface
{
    public function getProductType()
    {
        return 'inserted';
    }

    public function getProductName()
    {
        return '전단지';
    }

    public function getDefaultUnit()
    {
        return '연';
    }

    public function getPriceEndpoint()
    {
        return '/mlangprintauto/inserted/calculate_price_ajax.php';
    }

    public function getPriceMethod()
    {
        return 'GET';
    }

    public function mapToPriceParams(array $formParams)
    {
        return [
            'MY_type' => $formParams['MY_type'] ?? '',
            'MY_Fsd' => $formParams['MY_Fsd'] ?? '',
            'PN_type' => $formParams['PN_type'] ?? '',
            'POtype' => $formParams['POtype'] ?? '',
            'MY_amount' => $formParams['MY_amount'] ?? '',
            'ordertype' => $formParams['ordertype'] ?? '',
            'premium_options_total' => $formParams['premium_options_total'] ?? 0,
        ];
    }

    public function normalize(array $calcParams, array $priceResponse)
    {
        $payload = new QuoteItemPayload();

        $payload->product_type = $this->getProductType();
        $payload->product_name = $this->getProductName();
        $payload->unit = $this->getDefaultUnit();

        $reams = floatval($calcParams['MY_amount'] ?? 0);
        $payload->quantity = $reams;

        $data = $priceResponse['data'] ?? $priceResponse;

        $sheets = intval($this->extractNumeric($data['MY_amountRight'] ?? '0'));
        $payload->qty_sheets = $sheets;

        $payload->quantity_display = $this->formatQuantityDisplay($reams, $sheets);

        $supplyRaw = $data['Order_PriceForm'] ?? $data['Order_Price'] ?? $data['total_price'] ?? 0;
        $payload->supply_price = intval($this->extractNumeric($supplyRaw));

        $totalRaw = $data['Total_PriceForm'] ?? $data['total_with_vat'] ?? 0;
        $totalParsed = intval($this->extractNumeric($totalRaw));
        if ($totalParsed > 0) {
            $payload->total_price = $totalParsed;
            $payload->vat_price = $totalParsed - $payload->supply_price;
        } else {
            $payload->calculateVat();
        }

        $payload->calculateUnitPrice();

        $payload->specification = $this->buildSpecification($calcParams);

        $options = [];
        $premiumTotal = intval($calcParams['premium_options_total'] ?? 0);
        if ($premiumTotal > 0) {
            $options['premium_options_total'] = $premiumTotal;
        }
        $payload->options = $options;

        $payload->raw_params = [
            'MY_type' => $calcParams['MY_type'] ?? '',
            'MY_Fsd' => $calcParams['MY_Fsd'] ?? '',
            'PN_type' => $calcParams['PN_type'] ?? '',
            'POtype' => $calcParams['POtype'] ?? '',
            'MY_amount' => $calcParams['MY_amount'] ?? '',
            'mesu' => $sheets,
            'ordertype' => $calcParams['ordertype'] ?? '',
        ];

        return $payload;
    }

    public function buildSpecification(array $calcParams)
    {
        $line1Parts = [];

        $styleForm = $calcParams['StyleForm'] ?? $calcParams['spec_type'] ?? '';
        if (!empty($styleForm)) {
            $line1Parts[] = $styleForm;
        }

        $sectionForm = $calcParams['SectionForm'] ?? $calcParams['spec_material'] ?? '';
        if (!empty($sectionForm)) {
            $line1Parts[] = $sectionForm;
        }

        $quantityForm = $calcParams['QuantityForm'] ?? $calcParams['spec_size'] ?? '';
        if (!empty($quantityForm)) {
            $line1Parts[] = $quantityForm;
        }

        $line2Parts = [];

        $potype = $calcParams['POtype'] ?? '';
        $sidesMap = ['1' => '단면칼라', '2' => '양면칼라'];
        $sidesName = $calcParams['spec_sides'] ?? ($sidesMap[$potype] ?? '');
        if (!empty($sidesName)) {
            $line2Parts[] = $sidesName;
        }

        $reams = floatval($calcParams['MY_amount'] ?? 0);
        $sheets = intval($calcParams['mesu'] ?? 0);
        $qtyDisplay = $this->formatQuantityDisplay($reams, $sheets);
        if (!empty($qtyDisplay)) {
            $line2Parts[] = $qtyDisplay;
        }

        $ordertype = $calcParams['ordertype'] ?? '';
        $designMap = ['print' => '인쇄만', 'design' => '디자인+인쇄'];
        $designForm = $calcParams['DesignForm'] ?? ($designMap[$ordertype] ?? '');
        if (!empty($designForm)) {
            $line2Parts[] = $designForm;
        }

        $line1 = implode(' / ', array_filter($line1Parts));
        $line2 = implode(' / ', array_filter($line2Parts));

        if (!empty($line1) && !empty($line2)) {
            return $line1 . "\n" . $line2;
        }
        return $line1 . $line2;
    }

    private function formatQuantityDisplay($reams, $sheets)
    {
        if ($reams <= 0) {
            return '';
        }

        $reamStr = (floor($reams) == $reams)
            ? number_format($reams)
            : number_format($reams, 1);

        if ($sheets > 0) {
            return $reamStr . '연 (' . number_format($sheets) . '매)';
        }
        return $reamStr . '연';
    }

    private function extractNumeric($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return preg_replace('/[^0-9.]/', '', strval($value));
    }
}
