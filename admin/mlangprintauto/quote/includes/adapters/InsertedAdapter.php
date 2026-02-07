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

        $reams = floatval($calcParams['MY_amount'] ?? $calcParams['quantity'] ?? 0);
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

        // Pass spec_* fields from widget for human-readable specification
        $specParams = $calcParams;
        if (!empty($calcParams['spec_type'])) {
            $specParams['spec_type'] = $calcParams['spec_type'];
        }
        if (!empty($calcParams['spec_size'])) {
            $specParams['spec_material'] = $calcParams['spec_size'];
        }
        if (!empty($calcParams['spec_paper'])) {
            $specParams['spec_size'] = $calcParams['spec_paper'];
        }

        $payload->specification = $this->buildSpecification($specParams);

        // Build options including additional options (coating/folding/creasing)
        $options = [];
        $premiumTotal = intval($calcParams['premium_options_total'] ?? 0);
        if ($premiumTotal > 0) {
            $options['premium_options_total'] = $premiumTotal;
        }

        // Pass through coating/folding/creasing options from widget
        $passthrough = ['coating_enabled', 'coating_type', 'coating_price',
                        'folding_enabled', 'folding_type', 'folding_price',
                        'creasing_enabled', 'creasing_lines', 'creasing_price',
                        'additional_options_total'];
        foreach ($passthrough as $key) {
            if (isset($calcParams[$key])) {
                $options[$key] = $calcParams[$key];
            }
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
            'spec_type' => $calcParams['spec_type'] ?? '',
            'spec_size' => $calcParams['spec_size'] ?? '',
            'spec_paper' => $calcParams['spec_paper'] ?? '',
        ];

        return $payload;
    }

    public function buildSpecification(array $calcParams)
    {
        $line1Parts = [];

        // Use human-readable text from widget (spec_type), fall back to StyleForm (numeric ID)
        $typeName = $calcParams['spec_type'] ?? $calcParams['StyleForm'] ?? '';
        if (!empty($typeName)) {
            $line1Parts[] = $typeName;
        }

        // spec_material = size name (규격), spec_size = paper name (용지)
        $sizeName = $calcParams['spec_material'] ?? $calcParams['SectionForm'] ?? '';
        if (!empty($sizeName)) {
            $line1Parts[] = $sizeName;
        }

        $paperName = $calcParams['spec_size'] ?? $calcParams['QuantityForm'] ?? '';
        if (!empty($paperName)) {
            $line1Parts[] = $paperName;
        }

        $line2Parts = [];

        $potype = $calcParams['POtype'] ?? '';
        $sidesMap = ['1' => '단면칼라', '2' => '양면칼라'];
        $sidesName = $calcParams['spec_sides'] ?? ($sidesMap[$potype] ?? '');
        if (!empty($sidesName)) {
            $line2Parts[] = $sidesName;
        }

        $reams = floatval($calcParams['MY_amount'] ?? $calcParams['quantity'] ?? 0);
        $sheets = intval($calcParams['mesu'] ?? 0);
        $qtyDisplay = !empty($calcParams['spec_quantity_display'])
            ? $calcParams['spec_quantity_display']
            : $this->formatQuantityDisplay($reams, $sheets);
        if (!empty($qtyDisplay)) {
            $line2Parts[] = $qtyDisplay;
        }

        $ordertype = $calcParams['ordertype'] ?? '';
        $designMap = ['print' => '인쇄만', 'total' => '디자인+인쇄'];
        $designName = $calcParams['spec_design'] ?? $calcParams['DesignForm'] ?? ($designMap[$ordertype] ?? '');
        if (!empty($designName)) {
            $line2Parts[] = $designName;
        }

        // Additional options text
        $optionNames = $this->buildAdditionalOptionNames($calcParams);
        if (!empty($optionNames)) {
            $line2Parts[] = $optionNames;
        }

        $line1 = implode(' / ', array_filter($line1Parts));
        $line2 = implode(' / ', array_filter($line2Parts));

        if (!empty($line1) && !empty($line2)) {
            return $line1 . "\n" . $line2;
        }
        return $line1 . $line2;
    }

    private function buildAdditionalOptionNames(array $calcParams)
    {
        $names = [];

        $coatingNames = [
            'single' => '단면유광코팅', 'double' => '양면유광코팅',
            'single_matte' => '단면무광코팅', 'double_matte' => '양면무광코팅'
        ];
        $foldingNames = [
            '2fold' => '2단접지', '3fold' => '3단접지',
            'accordion' => '병풍접지', 'gate' => '대문접지'
        ];

        if (!empty($calcParams['coating_enabled'])) {
            $type = $calcParams['coating_type'] ?? '';
            $names[] = $coatingNames[$type] ?? $type ?: '코팅';
        }
        if (!empty($calcParams['folding_enabled'])) {
            $type = $calcParams['folding_type'] ?? '';
            $names[] = $foldingNames[$type] ?? $type ?: '접지';
        }
        if (!empty($calcParams['creasing_enabled'])) {
            $lines = intval($calcParams['creasing_lines'] ?? 1);
            $names[] = '오시' . $lines . '줄';
        }

        return !empty($names) ? implode('+', $names) : '';
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
