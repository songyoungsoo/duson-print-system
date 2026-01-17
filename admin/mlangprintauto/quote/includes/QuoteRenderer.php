<?php
/**
 * 견적서 PDF/HTML 렌더러
 * 표준 레이아웃 연동 버전 (standard/layout.php 사용)
 */

$companyInfoPath = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '/var/www/html';
require_once $companyInfoPath . '/mlangprintauto/includes/company_info.php';

// Standard 레이아웃 로드
$standardLayoutPath = $companyInfoPath . '/mlangprintauto/quote/standard/layout.php';
if (file_exists($standardLayoutPath)) {
    require_once $standardLayoutPath;
}

class QuoteRenderer
{
    private $quote;
    private $items;
    private $companyInfo;
    private $useStandardLayout = true; // standard 레이아웃 사용 여부

    public function __construct(array $quote, array $items)
    {
        $this->quote = $quote;
        $this->items = $items;
        $this->companyInfo = getCompanyInfo();
    }

    /**
     * Standard 레이아웃 사용 여부 설정
     */
    public function setUseStandardLayout(bool $use): void
    {
        $this->useStandardLayout = $use;
    }

    /**
     * 데이터를 Standard 형식으로 변환
     */
    private function convertToStandardFormat(): array
    {
        $quote = $this->quote;
        $company = $this->companyInfo;

        // Quote 데이터 변환
        $standardQuote = [
            'quote_no' => $quote['quote_no'] ?? '',
            'quote_date' => $quote['created_at'] ?? date('Y-m-d'),
            'customer_company' => $quote['customer_company'] ?? '',
            'customer_name' => $quote['customer_name'] ?? '',
            'customer_email' => $quote['customer_email'] ?? '',
            'validity_days' => 7, // 기본 7일
        ];

        // Items 데이터 변환
        $standardItems = [];
        foreach ($this->items as $item) {
            $standardItems[] = [
                'product_name' => $item['product_name'] ?? '',
                'specification' => $item['specification'] ?? '',
                'quantity_display' => $item['quantity_display'] ?? number_format($item['quantity'] ?? 0),
                'unit_price' => $item['unit_price'] ?? 0,
                'supply_price' => $item['supply_price'] ?? 0,
            ];
        }

        // Supplier 데이터 변환
        $standardSupplier = [
            'company_name' => $company['name'] ?? '',
            'business_no' => $company['business_number'] ?? '',
            'ceo_name' => $company['owner'] ?? '',
            'address' => $company['address'] ?? '',
            'phone' => $company['phone'] ?? '',
            'email' => $company['email'] ?? 'dsp1830@naver.com',
            'account_holder' => $company['account_holder'] ?? '',
            'bank_accounts' => [
                ['bank_name' => '국민', 'account_no' => preg_replace('/^국민(은행)?\s*/', '', $company['bank_kookmin'] ?? '')],
                ['bank_name' => '신한', 'account_no' => preg_replace('/^신한(은행)?\s*/', '', $company['bank_shinhan'] ?? '')],
                ['bank_name' => '농협', 'account_no' => preg_replace('/^농협\s*/', '', $company['bank_nonghyup'] ?? '')],
            ],
        ];

        return [
            'quote' => $standardQuote,
            'items' => $standardItems,
            'supplier' => $standardSupplier,
        ];
    }

    private function numberToKorean($number): string
    {
        $number = intval($number);
        if ($number == 0) return '영';

        $units = ['', '만', '억', '조'];
        $digits = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
        $positions = ['', '십', '백', '천'];

        $result = '';
        $unitIndex = 0;

        while ($number > 0) {
            $part = $number % 10000;
            if ($part > 0) {
                $partStr = '';
                $posIndex = 0;
                while ($part > 0) {
                    $digit = $part % 10;
                    if ($digit > 0) {
                        $digitStr = ($digit == 1 && $posIndex > 0) ? '' : $digits[$digit];
                        $partStr = $digitStr . $positions[$posIndex] . $partStr;
                    }
                    $part = intdiv($part, 10);
                    $posIndex++;
                }
                $result = $partStr . $units[$unitIndex] . $result;
            }
            $number = intdiv($number, 10000);
            $unitIndex++;
        }

        return $result;
    }

    public function renderHTML(): string
    {
        // Standard 레이아웃 사용 시
        if ($this->useStandardLayout && function_exists('renderQuoteLayout')) {
            return $this->renderStandardHTML();
        }

        // 레거시 렌더링 (fallback)
        return $this->renderLegacyHTML();
    }

    /**
     * Standard 레이아웃을 사용한 HTML 렌더링
     */
    private function renderStandardHTML(): string
    {
        $data = $this->convertToStandardFormat();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $quoteHtml = renderQuoteLayout($data['quote'], $data['items'], $data['supplier'], $baseUrl);

        // 완전한 HTML 문서로 래핑 (여백 없음)
        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 - {$this->escape($data['quote']['quote_no'])}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            margin: 0;
            padding: 0;
        }
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .print-controls button {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            color: white;
        }
        .btn-print { background: #2980b9; }
        .btn-close { background: #7f8c8d; }
        @media print {
            body { margin: 0; padding: 0; }
            .print-controls { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button class="btn-print" onclick="window.print();">인쇄 / PDF</button>
        <button class="btn-close" onclick="window.close();">닫기</button>
    </div>
    {$quoteHtml}
</body>
</html>
HTML;
    }

    /**
     * 레거시 HTML 렌더링 (기존 방식)
     */
    private function renderLegacyHTML(): string
    {
        $quote = $this->quote;
        $items = $this->items;
        $company = $this->companyInfo;

        $supplyTotal = intval($quote['supply_total']);
        $vatTotal = intval($quote['vat_total']);
        $grandTotal = intval($quote['grand_total']);
        $koreanAmount = $this->numberToKorean($grandTotal);

        $validUntil = !empty($quote['valid_until'])
            ? date('Y년 m월 d일', strtotime($quote['valid_until']))
            : date('Y년 m월 d일', strtotime('+7 days'));

        $createdAt = !empty($quote['created_at'])
            ? date('Y년 m월 d일', strtotime($quote['created_at']))
            : date('Y년 m월 d일');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서_{$this->escape($quote['customer_name'])}_{$this->escape($quote['quote_no'])}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Noto Sans KR 강제 적용 */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap');

        /* Reset - 다른 CSS 충돌 방지 */
        #aq-container, #aq-container * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif !important;
            line-height: 1.5;
        }

        @page { size: A4; margin: 15mm; }

        body.aq-body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif !important;
            font-size: 12px !important;
            color: #000 !important;
            background: #e8e8e8 !important;
            font-weight: 400 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #aq-container .aq-paper {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 12mm 15mm;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        #aq-container .aq-quote-no {
            font-size: 11px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 400;
        }

        #aq-container .aq-header {
            text-align: center;
            margin: 10px 0 20px 0;
        }

        #aq-container .aq-header h1 {
            display: inline-block;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 25px;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
            margin-left: 25px;
            background: none;
            color: #000;
        }

        #aq-container table {
            width: 100%;
            border-collapse: collapse;
        }

        /* 상단 정보 섹션 */
        #aq-container .aq-info-section {
            display: flex;
            gap: 0;
            margin-bottom: 0;
        }

        #aq-container .aq-info-left { width: 38%; }
        #aq-container .aq-info-right { width: 62%; }

        /* ========================================
           편집 디자인 원칙:
           - 라벨(Label): Regular (400)
           - 값(Value): Semi-Bold (600) ~ Bold (700)
           - 최종 합계: 가장 강조 Bold (700)
           - 외곽선: 2px, 내부선: 1px
           ======================================== */

        /* 공급받는자 테이블 */
        #aq-container .aq-info-table {
            border: 2px solid #000;
        }

        #aq-container .aq-info-table th,
        #aq-container .aq-info-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12px;
        }

        /* 라벨 셀 - Regular */
        #aq-container .aq-info-table th {
            background: #f5f5f5;
            font-weight: 400;
            text-align: center;
            width: 80px;
        }

        /* 값 셀 - Semi-Bold */
        #aq-container .aq-info-table td {
            font-weight: 600;
            text-align: left;
        }

        /* 공급자 테이블 */
        #aq-container .aq-supplier-table {
            border: 2px solid #000;
            border-left: 1px solid #000;
        }

        #aq-container .aq-supplier-table th,
        #aq-container .aq-supplier-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 11px;
        }

        /* 공급자 헤더 - 섹션 제목이므로 Regular */
        #aq-container .aq-supplier-table td.aq-header-row {
            background: #f5f5f5;
            font-weight: 400;
            text-align: center;
            font-size: 12px;
        }

        /* 라벨 셀 - Regular */
        #aq-container .aq-supplier-table td.aq-label {
            background: #fafafa;
            text-align: center;
            font-weight: 400;
        }

        /* 값 셀 - Semi-Bold */
        #aq-container .aq-supplier-table td.aq-value {
            font-weight: 600;
        }

        /* 중요 값 (사업자번호, 상호) - Bold */
        #aq-container .aq-supplier-table td.aq-value-strong {
            font-weight: 700;
        }

        /* 합계금액 테이블 */
        #aq-container .aq-total-table {
            border: 2px solid #000;
            margin-top: -1px;
        }

        #aq-container .aq-total-table td {
            border: 1px solid #000;
            padding: 10px;
        }

        /* 라벨 - Regular */
        #aq-container .aq-total-table td.aq-total-label {
            background: #f5f5f5;
            text-align: center;
            width: 150px;
            font-size: 13px;
            font-weight: 400;
        }

        /* 한글 금액 - Semi-Bold */
        #aq-container .aq-total-table td.aq-korean {
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
        }

        /* 최종 합계 금액 - 가장 강조 Bold */
        #aq-container .aq-total-table td.aq-amount {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            width: 180px;
        }

        /* 품목 테이블 */
        #aq-container .aq-items-table {
            margin-top: 15px;
            border: 2px solid #000;
        }

        /* 테이블 헤더 - 라벨이지만 구분을 위해 배경색 유지, Regular */
        #aq-container .aq-items-table thead th {
            border: 1px solid #000;
            padding: 8px 4px;
            font-size: 12px;
            font-weight: 400;
            background: #f5f5f5;
            text-align: center;
        }

        /* 본문 값 - Regular (데이터가 많아 가독성 우선) */
        #aq-container .aq-items-table tbody td {
            border: 1px solid #000;
            padding: 6px 4px;
            font-size: 11px;
            font-weight: 400;
        }

        #aq-container .aq-items-table .aq-center { text-align: center; }
        #aq-container .aq-items-table .aq-right { text-align: right; padding-right: 8px; }
        #aq-container .aq-items-table .aq-left { text-align: left; padding-left: 6px; }

        #aq-container .aq-items-table td.aq-spec-cell {
            font-size: 11px;
            line-height: 1.4;
            padding: 6px 8px;
        }

        /* 합계 행 - tfoot */
        #aq-container .aq-items-table tfoot td {
            background: #fff;
            border: 1px solid #000;
            padding: 6px 4px;
        }

        /* 합계 라벨 - Regular */
        #aq-container .aq-items-table tfoot td.aq-sum-label {
            text-align: right;
            padding-right: 12px;
            font-weight: 400;
            font-size: 11px;
        }

        /* 합계 값 - Bold */
        #aq-container .aq-items-table tfoot td.aq-sum-value {
            text-align: right;
            padding-right: 12px;
            font-weight: 700;
            font-size: 12px;
        }

        /* 최종 합계 행 강조 */
        #aq-container .aq-items-table tfoot tr.aq-grand-total td.aq-sum-label {
            font-weight: 600;
            font-size: 12px;
        }

        #aq-container .aq-items-table tfoot tr.aq-grand-total td.aq-sum-value {
            font-weight: 700;
            font-size: 13px;
        }

        /* 하단 정보 */
        #aq-container .aq-footer-info {
            margin-top: 20px;
            font-size: 12px;
            line-height: 2;
        }

        #aq-container .aq-footer-info p {
            margin: 3px 0;
        }

        /* 라벨 - Regular */
        #aq-container .aq-footer-info .aq-label {
            font-weight: 400;
        }

        /* 값 - Semi-Bold */
        #aq-container .aq-footer-info .aq-value {
            font-weight: 600;
        }

        #aq-container .aq-footer-info .aq-note-text {
            color: #0066cc;
            text-decoration: underline;
            font-weight: 600;
        }

        /* 인쇄 버튼 */
        .aq-print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .aq-print-controls button {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            color: white;
        }

        .aq-print-controls .aq-btn-print { background: #2980b9; }
        .aq-print-controls .aq-btn-close { background: #7f8c8d; }

        @media print {
            body.aq-body { background: #fff !important; }
            #aq-container .aq-paper {
                box-shadow: none;
                margin: 0;
                padding: 8mm;
                min-height: auto;
            }
            .aq-print-controls { display: none !important; }
        }
    </style>
</head>
<body class="aq-body">
    <div class="aq-print-controls">
        <button class="aq-btn-print" onclick="window.print();">인쇄 / PDF</button>
        <button class="aq-btn-close" onclick="window.close();">닫기</button>
    </div>

    <div id="aq-container">
        <div class="aq-paper">
            <div class="aq-quote-no">No. {$this->escape($quote['quote_no'])}</div>

            <div class="aq-header">
                <h1>견 적 서</h1>
            </div>

            <div class="aq-info-section">
                <div class="aq-info-left">
                    <table class="aq-info-table">
                        <tr><th>견적일</th><td>{$createdAt}</td></tr>
                        <tr><th>회사명</th><td>{$this->escape($quote['customer_company'] ?? '')}</td></tr>
                        <tr><th>담당자</th><td>{$this->escape($quote['customer_name'])} 귀하</td></tr>
                        <tr><th>유효기간</th><td>{$validUntil}까지</td></tr>
                    </table>
                </div>
                <div class="aq-info-right">
                    <table class="aq-supplier-table">
                        <tr><td colspan="4" class="aq-header-row">공 급 자</td></tr>
                        <tr>
                            <td class="aq-label">등록번호</td>
                            <td class="aq-value-strong">{$this->escape($company['business_number'])}</td>
                            <td class="aq-label">대표자</td>
                            <td class="aq-value">{$this->escape($company['owner'])} (직인생략)</td>
                        </tr>
                        <tr>
                            <td class="aq-label">상 호</td>
                            <td class="aq-value-strong" colspan="3">{$this->escape($company['name'])}</td>
                        </tr>
                        <tr>
                            <td class="aq-label">주 소</td>
                            <td class="aq-value" colspan="3">{$this->escape($company['address'])}</td>
                        </tr>
                        <tr>
                            <td class="aq-label">연락처</td>
                            <td class="aq-value" colspan="3">{$this->escape($company['phone'])}</td>
                        </tr>
                        <tr>
                            <td class="aq-label">업 태</td>
                            <td class="aq-value">{$this->escape($company['business_type'])}</td>
                            <td class="aq-label">종 목</td>
                            <td class="aq-value">{$this->escape($company['business_item'])}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="aq-total-table">
                <tr>
                    <td class="aq-total-label"><strong>합계금액</strong>(부가세포함)</td>
                    <td class="aq-korean">
                        일금 {$koreanAmount}원정<br>
                        ( ₩{$this->formatPrice($grandTotal)} )
                    </td>
                    <td class="aq-amount">{$this->formatPrice($grandTotal)} 원</td>
                </tr>
            </table>

            <table class="aq-items-table">
                <colgroup>
                    <col style="width: 5%;"><col style="width: 10%;"><col style="width: 31%;">
                    <col style="width: 8%;"><col style="width: 6%;"><col style="width: 10%;">
                    <col style="width: 14%;"><col style="width: 16%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>NO</th><th>품 목</th><th>규격/옵션</th>
                        <th>수량</th><th>단위</th><th>단가</th><th>공급가액</th><th>비 고</th>
                    </tr>
                </thead>
                <tbody>
HTML;

        foreach ($items as $index => $item) {
            $no = $index + 1;
            $productName = $this->escape($item['product_name']);
            $specRaw = $item['specification'] ?? '';
            $specParts = preg_split('/[\|\n]+/', $specRaw);
            $specParts = array_map('trim', $specParts);
            $specParts = array_filter($specParts, function($p) { return !empty($p); });
            $specHtml = $this->escape(implode(' / ', $specParts));

            $qty = floatval($item['quantity']);
            $qtyDisplay = $item['quantity_display'] ?? '';
            if (empty($qtyDisplay)) {
                $qtyDisplay = ($qty == intval($qty)) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
            }
            $unit = $this->escape($item['unit'] ?? '개');
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $unitPriceDisplay = ($unitPrice > 0) ? $this->formatPrice($unitPrice) : '-';
            $supplyPrice = intval($item['supply_price']);
            $notes = $this->escape($item['notes'] ?? '');

            $html .= "<tr>
                <td class=\"aq-center\">{$no}</td>
                <td class=\"aq-left\">{$productName}</td>
                <td class=\"aq-spec-cell\">{$specHtml}</td>
                <td class=\"aq-center\">{$qtyDisplay}</td>
                <td class=\"aq-center\">{$unit}</td>
                <td class=\"aq-right\">{$unitPriceDisplay}</td>
                <td class=\"aq-right\">{$this->formatPrice($supplyPrice)}</td>
                <td class=\"aq-center\">{$notes}</td>
            </tr>";
        }

        $emptyRows = max(0, 5 - count($items));
        for ($i = 0; $i < $emptyRows; $i++) {
            $html .= '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        }

        $html .= <<<HTML
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="border-right: none;"></td>
                        <td class="aq-sum-label" style="border-left: none;">공급가액 합계</td>
                        <td class="aq-sum-value">{$this->formatPrice($supplyTotal)}</td>
                    </tr>
                    <tr>
                        <td colspan="6" style="border-right: none;"></td>
                        <td class="aq-sum-label" style="border-left: none;">부가세</td>
                        <td class="aq-sum-value">{$this->formatPrice($vatTotal)}</td>
                    </tr>
                    <tr class="aq-grand-total">
                        <td colspan="6" style="border-right: none;"></td>
                        <td class="aq-sum-label" style="border-left: none;">합 계 (VAT포함)</td>
                        <td class="aq-sum-value">{$this->formatPrice($grandTotal)}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="aq-footer-info">
                <p><span class="aq-label">입금 계좌번호 :</span> <span class="aq-value">{$company['bank_kookmin']} / {$company['bank_shinhan']} / {$company['bank_nonghyup']} (예금주: {$company['account_holder']})</span></p>
                <p><span class="aq-label">담당자 :</span> <span class="aq-value">{$this->escape($quote['customer_name'])}</span></p>
HTML;

        if (!empty($quote['admin_memo'])) {
            $html .= '<p><span class="aq-label">비 고 :</span> <span class="aq-value">' . nl2br($this->escape($quote['admin_memo'])) . '</span></p>';
        } else {
            $html .= '<p><span class="aq-label">비 고 :</span> <span class="aq-note-text">택배는 착불기준입니다</span></p>';
        }

        $html .= '</div></div></div></body></html>';
        return $html;
    }

    public function renderPDF(?string $outputPath = null, string $mode = 'D')
    {
        // Standard 레이아웃 사용 시 mPDF 우선
        if ($this->useStandardLayout && function_exists('renderQuoteLayout')) {
            return $this->renderStandardPDF($outputPath, $mode);
        }

        // 레거시 렌더링 (TCPDF fallback)
        return $this->renderLegacyPDF($outputPath, $mode);
    }

    /**
     * Standard 레이아웃 + mPDF를 사용한 PDF 렌더링
     */
    private function renderStandardPDF(?string $outputPath, string $mode)
    {
        $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '/var/www/html';

        // Composer autoload
        $autoloadPaths = [
            $docRoot . '/vendor/autoload.php',
            '/var/www/html/vendor/autoload.php',
        ];
        foreach ($autoloadPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                break;
            }
        }

        // mPDF 필요
        if (!class_exists('Mpdf\\Mpdf')) {
            // mPDF 없으면 TCPDF fallback
            return $this->renderLegacyPDF($outputPath, $mode);
        }

        $data = $this->convertToStandardFormat();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $quoteHtml = renderQuoteLayout($data['quote'], $data['items'], $data['supplier'], $baseUrl);

        // PDF용: 불필요한 wrapper와 CSS 제거
        $quoteHtml = preg_replace('/<style>.*?<\/style>/s', '', $quoteHtml);
        $quoteHtml = preg_replace('/<div class="quote-body">\s*<div class="a4-page">/s', '<div>', $quoteHtml);
        $quoteHtml = preg_replace('/<\/div>\s*<\/div>\s*<!--\s*\/표준 견적서 레이아웃\s*-->/s', '</div>', $quoteHtml);

        // 전체 HTML 문서 구성
        $fullHtml = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            font-family: nanumgothic, sans-serif !important;
        }
        body {
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }
        th {
            background: #e8e8e8;
            text-align: center;
        }
        .no-border { border: none; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .table-bordered { border: 2px solid #000; }
        .title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            border: none;
        }
    </style>
</head>
<body>
{$quoteHtml}
</body>
</html>
HTML;

        try {
            // Noto Sans CJK 폰트 설정
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'default_font' => 'nanumgothic',
                'tempDir' => '/tmp/mpdf',
                'fontDir' => array_merge($fontDirs, ['/usr/share/fonts/truetype/nanum']),
                'fontdata' => $fontData + [
                    'nanumgothic' => [
                        'R' => 'NanumGothic.ttf',
                        'B' => 'NanumGothicBold.ttf',
                    ],
                ],
            ]);

            $mpdf->WriteHTML($fullHtml);

            $filename = '견적서_' . ($data['quote']['quote_no'] ?? date('Ymd')) . '.pdf';

            if ($mode === 'F' && $outputPath) {
                return $mpdf->Output($outputPath, \Mpdf\Output\Destination::FILE);
            } elseif ($mode === 'I') {
                return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
            } else {
                return $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
            }

        } catch (\Exception $e) {
            error_log('mPDF Error: ' . $e->getMessage());
            // mPDF 실패 시 TCPDF fallback
            return $this->renderLegacyPDF($outputPath, $mode);
        }
    }

    /**
     * 레거시 PDF 렌더링 (TCPDF)
     */
    private function renderLegacyPDF(?string $outputPath, string $mode)
    {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php')) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/lib/tcpdf/tcpdf.php')) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/tcpdf/tcpdf.php');
        } else {
            throw new Exception('PDF 라이브러리를 찾을 수 없습니다.');
        }

        $quote = $this->quote;
        $items = $this->items;
        $company = $this->companyInfo;

        $supplyTotal = intval($quote['supply_total']);
        $vatTotal = intval($quote['vat_total']);
        $grandTotal = intval($quote['grand_total']);
        $koreanAmount = $this->numberToKorean($grandTotal);

        $validUntil = !empty($quote['valid_until'])
            ? date('Y년 m월 d일', strtotime($quote['valid_until']))
            : date('Y년 m월 d일', strtotime('+7 days'));
        $createdAt = !empty($quote['created_at'])
            ? date('Y년 m월 d일', strtotime($quote['created_at']))
            : date('Y년 m월 d일');

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator($company['name']);
        $pdf->SetAuthor($company['name']);
        $pdf->SetTitle('견적서 - ' . $quote['quote_no']);
        $pdf->SetMargins(15, 12, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // 견적번호
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 5, 'No. ' . $quote['quote_no'], 0, 1, 'L');
        $pdf->Ln(2);

        // 제목
        $pdf->SetFont('cid0kr', 'B', 22);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.5);
        $titleW = $pdf->GetStringWidth('견  적  서') + 10;
        $titleX = (210 - $titleW) / 2;
        $pdf->SetX($titleX);
        $pdf->Cell($titleW, 10, '견  적  서', 'B', 1, 'C');
        $pdf->Ln(6);

        $startY = $pdf->GetY();
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFillColor(245, 245, 245);

        // === 왼쪽: 공급받는자 ===
        // 편집 디자인 원칙: 라벨=Regular, 값=Bold
        $leftW = 68; $labelW = 22; $valueW = 46; $rowH = 7;

        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, $startY, $leftW, $rowH * 4, 'D');

        $pdf->SetLineWidth(0.2);

        // 라벨: Regular, 값: Bold
        $pdf->SetXY(15, $startY);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($labelW, $rowH, '견적일', 1, 0, 'C', true);
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell($valueW, $rowH, $createdAt, 1, 1, 'L');

        $pdf->SetX(15);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($labelW, $rowH, '회사명', 1, 0, 'C', true);
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell($valueW, $rowH, $quote['customer_company'] ?? '', 1, 1, 'L');

        $pdf->SetX(15);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($labelW, $rowH, '담당자', 1, 0, 'C', true);
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell($valueW, $rowH, $quote['customer_name'] . ' 귀하', 1, 1, 'L');

        $pdf->SetX(15);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($labelW, $rowH, '유효기간', 1, 0, 'C', true);
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell($valueW, $rowH, $validUntil . '까지', 1, 1, 'L');

        // === 오른쪽: 공급자 ===
        $rightX = 85; $rightW = 110; $sRowH = 6;

        $pdf->SetLineWidth(0.5);
        $pdf->Rect($rightX, $startY, $rightW, $sRowH * 6, 'D');

        $pdf->SetLineWidth(0.2);

        // 섹션 제목: Regular
        $pdf->SetXY($rightX, $startY);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($rightW, $sRowH, '공 급 자', 1, 1, 'C', true);

        // 라벨: Regular, 중요값(사업자번호): Bold, 일반값: Bold
        $pdf->SetX($rightX);
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '등록번호', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(30, $sRowH, $company['business_number'], 1, 0, 'C');
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '대표자', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(44, $sRowH, $company['owner'] . ' (직인생략)', 1, 1, 'C');

        // 상호: 중요값 Bold
        $pdf->SetX($rightX);
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '상 호', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell(92, $sRowH, $company['name'], 1, 1, 'L');

        // 주소, 연락처: 값 Bold
        $pdf->SetX($rightX);
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '주 소', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(92, $sRowH, $company['address'], 1, 1, 'L');

        $pdf->SetX($rightX);
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '연락처', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(92, $sRowH, $company['phone'], 1, 1, 'L');

        // 업태, 종목: 값 Bold
        $pdf->SetX($rightX);
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '업 태', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(30, $sRowH, $company['business_type'], 1, 0, 'C');
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell(18, $sRowH, '종 목', 1, 0, 'C');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell(44, $sRowH, $company['business_item'], 1, 1, 'C');

        // 합계금액
        $pdf->Ln(2);
        $totalY = $pdf->GetY();
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, $totalY, 180, 12, 'D');

        $pdf->SetLineWidth(0.2);
        $pdf->SetXY(15, $totalY);
        // 라벨: Regular
        $pdf->SetFont('cid0kr', '', 11);
        $pdf->Cell(18, 12, '합계금액', 1, 0, 'R', true);
        $pdf->Cell(32, 12, '(부가세포함)', 1, 0, 'L', true);
        // 한글금액: Bold
        $koreanText = '일금 ' . $koreanAmount . '원정  ( ₩' . number_format($grandTotal) . ' )';
        $pdf->SetFont('cid0kr', 'B', 11);
        $pdf->Cell(75, 12, $koreanText, 1, 0, 'C');
        // 최종합계: 가장 강조 Bold
        $pdf->SetFont('cid0kr', 'B', 18);
        $pdf->Cell(55, 12, number_format($grandTotal) . ' 원', 1, 1, 'C');

        $pdf->Ln(4);

        // 품목 테이블
        $itemsY = $pdf->GetY();
        $colW = [10, 18, 56, 14, 11, 18, 25, 28];
        $tableW = array_sum($colW);
        $itemRowH = 7;
        $headerH = 7;
        $minBodyRows = max(5, count($items));
        $tableH = $headerH + ($minBodyRows * $itemRowH) + (3 * $itemRowH);

        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, $itemsY, $tableW, $tableH, 'D');

        $pdf->SetLineWidth(0.2);
        // 테이블 헤더: 라벨이므로 Regular (배경색으로 구분)
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetXY(15, $itemsY);
        $pdf->Cell($colW[0], $headerH, 'NO', 1, 0, 'C', true);
        $pdf->Cell($colW[1], $headerH, '품 목', 1, 0, 'C', true);
        $pdf->Cell($colW[2], $headerH, '규격/옵션', 1, 0, 'C', true);
        $pdf->Cell($colW[3], $headerH, '수량', 1, 0, 'C', true);
        $pdf->Cell($colW[4], $headerH, '단위', 1, 0, 'C', true);
        $pdf->Cell($colW[5], $headerH, '단가', 1, 0, 'C', true);
        $pdf->Cell($colW[6], $headerH, '공급가액', 1, 0, 'C', true);
        $pdf->Cell($colW[7], $headerH, '비 고', 1, 1, 'C', true);

        $pdf->SetFont('cid0kr', '', 9);
        foreach ($items as $index => $item) {
            $no = $index + 1;
            $spec = str_replace(['|', "\n"], ' / ', $item['specification'] ?? '-');
            if (mb_strlen($spec) > 35) $spec = mb_substr($spec, 0, 33) . '..';
            $qtyDisplay = $item['quantity_display'] ?? number_format($item['quantity']);
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $unitPriceStr = ($unitPrice > 0) ? number_format($unitPrice) : '-';

            $pdf->Cell($colW[0], $itemRowH, $no, 1, 0, 'C');
            $pdf->Cell($colW[1], $itemRowH, $item['product_name'], 1, 0, 'L');
            $pdf->Cell($colW[2], $itemRowH, $spec, 1, 0, 'L');
            $pdf->Cell($colW[3], $itemRowH, $qtyDisplay, 1, 0, 'C');
            $pdf->Cell($colW[4], $itemRowH, $item['unit'] ?? '개', 1, 0, 'C');
            $pdf->Cell($colW[5], $itemRowH, $unitPriceStr, 1, 0, 'R');
            $pdf->Cell($colW[6], $itemRowH, number_format($item['supply_price']), 1, 0, 'R');
            $pdf->Cell($colW[7], $itemRowH, $item['notes'] ?? '', 1, 1, 'C');
        }

        $emptyRows = max(0, 5 - count($items));
        for ($i = 0; $i < $emptyRows; $i++) {
            $pdf->Cell($colW[0], $itemRowH, '', 1, 0, 'C');
            $pdf->Cell($colW[1], $itemRowH, '', 1, 0, 'L');
            $pdf->Cell($colW[2], $itemRowH, '', 1, 0, 'L');
            $pdf->Cell($colW[3], $itemRowH, '', 1, 0, 'C');
            $pdf->Cell($colW[4], $itemRowH, '', 1, 0, 'C');
            $pdf->Cell($colW[5], $itemRowH, '', 1, 0, 'R');
            $pdf->Cell($colW[6], $itemRowH, '', 1, 0, 'R');
            $pdf->Cell($colW[7], $itemRowH, '', 1, 1, 'C');
        }

        // colspan 6: NO, 품목, 규격, 수량, 단위, 단가
        $leftColsW = $colW[0] + $colW[1] + $colW[2] + $colW[3] + $colW[4] + $colW[5];

        // 합계 행: 라벨=Regular, 값=Bold
        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell($leftColsW, $itemRowH, '', 1, 0, 'C');
        $pdf->Cell($colW[6], $itemRowH, '공급가액 합계', 1, 0, 'R');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell($colW[7], $itemRowH, number_format($supplyTotal), 1, 1, 'R');

        $pdf->SetFont('cid0kr', '', 9);
        $pdf->Cell($leftColsW, $itemRowH, '', 1, 0, 'C');
        $pdf->Cell($colW[6], $itemRowH, '부가세', 1, 0, 'R');
        $pdf->SetFont('cid0kr', 'B', 9);
        $pdf->Cell($colW[7], $itemRowH, number_format($vatTotal), 1, 1, 'R');

        // 최종 합계: 라벨=Regular, 값=Bold 강조
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell($leftColsW, $itemRowH, '', 1, 0, 'C');
        $pdf->Cell($colW[6], $itemRowH, '합 계 (VAT포함)', 1, 0, 'R');
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell($colW[7], $itemRowH, number_format($grandTotal), 1, 1, 'R');

        // 하단 정보: 라벨=Regular, 값=Bold
        $pdf->Ln(6);
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell(28, 6, '입금 계좌번호 :', 0, 0, 'L');
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell(0, 6, $company['bank_kookmin'] . ' / ' . $company['bank_shinhan'] . ' / ' . $company['bank_nonghyup'] . ' (예금주: ' . $company['account_holder'] . ')', 0, 1, 'L');

        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell(18, 6, '담당자 :', 0, 0, 'L');
        $pdf->SetFont('cid0kr', 'B', 10);
        $pdf->Cell(0, 6, $quote['customer_name'], 0, 1, 'L');

        $memo = $quote['admin_memo'] ?? '';
        $pdf->SetFont('cid0kr', '', 10);
        $pdf->Cell(14, 6, '비 고 :', 0, 0, 'L');
        if (empty($memo)) {
            $pdf->SetTextColor(0, 102, 204);
            $pdf->SetFont('cid0kr', 'B', 10);
            $pdf->Cell(0, 6, '택배는 착불기준입니다', 0, 1, 'L');
        } else {
            $pdf->SetFont('cid0kr', 'B', 10);
            $pdf->Cell(0, 6, $memo, 0, 1, 'L');
        }

        $filename = '견적서_' . $quote['quote_no'] . '.pdf';
        if ($mode === 'F' && $outputPath) {
            return $pdf->Output($outputPath, 'F');
        }
        return $pdf->Output($filename, $mode);
    }

    public function renderEmailBody(): string
    {
        $quote = $this->quote;
        $items = $this->items;
        $company = $this->companyInfo;

        $supplyTotal = intval($quote['supply_total']);
        $vatTotal = intval($quote['vat_total']);
        $grandTotal = intval($quote['grand_total']);
        $koreanAmount = $this->numberToKorean($grandTotal);

        $validUntil = !empty($quote['valid_until'])
            ? date('Y년 m월 d일', strtotime($quote['valid_until']))
            : date('Y년 m월 d일', strtotime('+7 days'));
        $createdAt = !empty($quote['created_at'])
            ? date('Y년 m월 d일', strtotime($quote['created_at']))
            : date('Y년 m월 d일');

        // 인라인 스타일 정의
        $tableStyle = 'width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:13px;';
        $thStyle = 'background:#e8e8e8;border:1px solid #000;padding:8px;text-align:center;font-weight:normal;';
        $tdStyle = 'border:1px solid #000;padding:8px;';
        $boldStyle = 'font-weight:bold;';
        $centerStyle = 'text-align:center;';
        $rightStyle = 'text-align:right;';
        $noBorderStyle = 'border:none;';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 - {$this->escape($quote['quote_no'])}</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,'Malgun Gothic',sans-serif;">
<div style="max-width:700px;margin:0 auto;background:#fff;padding:30px;border:1px solid #ddd;">

<!-- 헤더 -->
<table style="{$tableStyle}">
    <tr>
        <td style="{$noBorderStyle}padding:0 0 5px 0;font-size:12px;color:#666;">No. {$this->escape($quote['quote_no'])}</td>
    </tr>
    <tr>
        <td style="{$noBorderStyle}padding:15px 0;font-size:26px;font-weight:bold;text-align:center;border-bottom:3px solid #000;">견 적 서</td>
    </tr>
</table>

<div style="height:15px;"></div>

<!-- 기본정보 테이블 -->
<table style="{$tableStyle}border:2px solid #000;">
    <tr>
        <th style="{$thStyle}width:12%;">견적일</th>
        <td style="{$tdStyle}width:38%;">{$createdAt}</td>
        <th colspan="4" style="{$thStyle}width:50%;">공급자</th>
    </tr>
    <tr>
        <th style="{$thStyle}">회사명</th>
        <td style="{$tdStyle}">{$this->escape($quote['customer_company'] ?? '')}</td>
        <th style="{$thStyle}width:10%;">등록번호</th>
        <td style="{$tdStyle}{$boldStyle}width:15%;">{$this->escape($company['business_number'])}</td>
        <th style="{$thStyle}width:10%;">대표자</th>
        <td style="{$tdStyle}width:15%;">{$this->escape($company['owner'])} <span style="font-size:10px;color:#888;">(직인생략)</span></td>
    </tr>
    <tr>
        <th style="{$thStyle}">담당자</th>
        <td style="{$tdStyle}">{$this->escape($quote['customer_name'])} 귀하</td>
        <th style="{$thStyle}">상호</th>
        <td colspan="3" style="{$tdStyle}{$boldStyle}">{$this->escape($company['name'])}</td>
    </tr>
    <tr>
        <th style="{$thStyle}">유효기간</th>
        <td style="{$tdStyle}">{$validUntil}까지</td>
        <th style="{$thStyle}">주소</th>
        <td colspan="3" style="{$tdStyle}">{$this->escape($company['address'])}</td>
    </tr>
    <tr>
        <th style="{$thStyle}">전화번호</th>
        <td style="{$tdStyle}">{$this->escape($company['phone'])}</td>
        <th style="{$thStyle}">업태</th>
        <td style="{$tdStyle}">{$this->escape($company['business_type'])}</td>
        <th style="{$thStyle}">종목</th>
        <td style="{$tdStyle}">{$this->escape($company['business_item'])}</td>
    </tr>
</table>

<div style="height:15px;"></div>

<!-- 합계금액 + 품목 테이블 -->
<table style="{$tableStyle}border:2px solid #000;">
    <tr>
        <th colspan="2" style="{$thStyle}{$boldStyle}">합계금액(VAT포함)</th>
        <td colspan="2" style="{$tdStyle}{$centerStyle}{$boldStyle}">
            일금 {$koreanAmount}원정<br>
            ( ₩{$this->formatPrice($grandTotal)} )
        </td>
        <td colspan="4" style="{$tdStyle}{$centerStyle}{$boldStyle}font-size:20px;">
            {$this->formatPrice($grandTotal)} 원
        </td>
    </tr>
    <tr>
        <th style="{$thStyle}width:5%;">NO</th>
        <th style="{$thStyle}width:10%;">품목</th>
        <th style="{$thStyle}width:30%;">규격/옵션</th>
        <th style="{$thStyle}width:8%;">수량</th>
        <th style="{$thStyle}width:7%;">단위</th>
        <th style="{$thStyle}width:12%;">단가</th>
        <th style="{$thStyle}width:13%;">공급가액</th>
        <th style="{$thStyle}width:15%;">비고</th>
    </tr>
HTML;

        // 품목 행
        foreach ($items as $index => $item) {
            $no = $index + 1;
            $spec = str_replace(['|', "\n"], ' / ', $item['specification'] ?? '');
            $qtyDisplay = $item['quantity_display'] ?? number_format($item['quantity'] ?? 0);
            $unit = $item['unit'] ?? '개';
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $unitPriceStr = ($unitPrice > 0) ? $this->formatPrice($unitPrice) : '-';
            $supplyPrice = intval($item['supply_price'] ?? 0);
            $notes = $item['notes'] ?? '';

            $html .= <<<HTML
    <tr>
        <td style="{$tdStyle}{$centerStyle}">{$no}</td>
        <td style="{$tdStyle}{$centerStyle}">{$this->escape($item['product_name'])}</td>
        <td style="{$tdStyle}">{$this->escape($spec)}</td>
        <td style="{$tdStyle}{$centerStyle}">{$this->escape($qtyDisplay)}</td>
        <td style="{$tdStyle}{$centerStyle}">{$this->escape($unit)}</td>
        <td style="{$tdStyle}{$rightStyle}">{$unitPriceStr}</td>
        <td style="{$tdStyle}{$rightStyle}">{$this->formatPrice($supplyPrice)}</td>
        <td style="{$tdStyle}{$centerStyle}">{$this->escape($notes)}</td>
    </tr>
HTML;
        }

        // 빈 행 추가 (최소 3행)
        $emptyRows = max(0, 3 - count($items));
        for ($i = 0; $i < $emptyRows; $i++) {
            $html .= '<tr><td style="'.$tdStyle.'">&nbsp;</td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td><td style="'.$tdStyle.'"></td></tr>';
        }

        // 합계 행
        $html .= <<<HTML
    <tr>
        <td colspan="6" style="{$tdStyle}{$rightStyle}{$boldStyle}">공급가액 합계</td>
        <td colspan="2" style="{$tdStyle}{$rightStyle}{$boldStyle}">{$this->formatPrice($supplyTotal)}</td>
    </tr>
    <tr>
        <td colspan="6" style="{$tdStyle}{$rightStyle}{$boldStyle}">부가세</td>
        <td colspan="2" style="{$tdStyle}{$rightStyle}{$boldStyle}">{$this->formatPrice($vatTotal)}</td>
    </tr>
    <tr>
        <th colspan="6" style="{$thStyle}{$rightStyle}{$boldStyle}">합 계 (VAT포함)</th>
        <td colspan="2" style="{$tdStyle}{$rightStyle}{$boldStyle}font-size:15px;">{$this->formatPrice($grandTotal)}</td>
    </tr>
</table>

<div style="height:15px;"></div>

<!-- 하단 정보 -->
<table style="{$tableStyle}">
    <tr>
        <td style="{$noBorderStyle}padding:8px 0;line-height:1.8;">
            <strong>입금 계좌번호 :</strong><br>
            {$this->escape($company['bank_kookmin'])} / {$this->escape($company['bank_shinhan'])} / {$this->escape($company['bank_nonghyup'])}<br>
            예금주 : {$this->escape($company['account_holder'])}
        </td>
    </tr>
    <tr>
        <td style="{$noBorderStyle}padding:5px 0;">담당자 : {$this->escape($quote['customer_name'])}</td>
    </tr>
    <tr>
        <td style="{$noBorderStyle}padding:5px 0;color:#0066cc;">비고 : 택배는 착불기준입니다</td>
    </tr>
</table>

<!-- 푸터 -->
<div style="margin-top:30px;padding-top:20px;border-top:1px solid #ddd;text-align:center;color:#888;font-size:12px;">
    본 견적서는 두손기획인쇄에서 발송되었습니다.<br>
    문의: {$this->escape($company['phone'])} | {$this->escape($company['email'] ?? 'dsp1830@naver.com')}
</div>

</div>
</body>
</html>
HTML;

        return $html;
    }

    private function escape(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    private function formatPrice($price): string
    {
        return number_format(intval($price));
    }
}
