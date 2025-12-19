<?php
/**
 * 전자세금계산서 HTML 템플릿 생성 클래스
 */

class TaxInvoiceTemplate {
    private $data;

    public function __construct($invoice_data) {
        $this->data = $invoice_data;
    }

    public function generate() {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>전자세금계산서 - <?php echo htmlspecialchars($this->data['invoice_number']); ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
        }

        body {
            font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            border: 2px solid #000;
        }

        .invoice-header {
            text-align: center;
            padding: 15px;
            border-bottom: 2px solid #000;
            background: #f8f9fa;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }

        .invoice-subtitle {
            font-size: 11px;
            color: #666;
            margin: 0;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #000;
            background: #f8f9fa;
        }

        .info-item {
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            color: #000;
        }

        .section-row {
            display: flex;
            border-bottom: 1px solid #000;
        }

        .section {
            flex: 1;
            padding: 12px;
        }

        .section:first-child {
            border-right: 1px solid #000;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        .company-info {
            margin-bottom: 6px;
        }

        .company-label {
            display: inline-block;
            width: 90px;
            font-weight: 600;
            color: #333;
        }

        .company-value {
            color: #000;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
        }

        .items-table th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 12px;
        }

        .items-table td {
            font-size: 11px;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .text-right {
            text-align: right;
        }

        .amount-section {
            padding: 15px;
            border-bottom: 1px solid #000;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .amount-row.total {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        .amount-label {
            font-weight: 600;
        }

        .amount-value {
            font-weight: bold;
            min-width: 150px;
            text-align: right;
        }

        .footer-section {
            padding: 15px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #1466BA;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background: #0d4a8a;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-issued {
            background: #28a745;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ 인쇄하기</button>

    <div class="invoice-container">
        <!-- 헤더 -->
        <div class="invoice-header">
            <h1 class="invoice-title">전자세금계산서 (공급자 보관용)</h1>
            <p class="invoice-subtitle">
                <?php
                $status_class = 'status-' . $this->data['status'];
                $status_text = [
                    'issued' => '발급완료',
                    'pending' => '발급대기',
                    'cancelled' => '취소됨'
                ][$this->data['status']] ?? '알 수 없음';
                ?>
                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
            </p>
        </div>

        <!-- 계산서 정보 -->
        <div class="invoice-info">
            <div class="info-item">
                <span class="info-label">승인번호:</span>
                <span><?php echo htmlspecialchars($this->data['invoice_number']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">작성일자:</span>
                <span><?php echo htmlspecialchars($this->data['issue_date']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">주문번호:</span>
                <span><?php echo htmlspecialchars($this->data['order_no']); ?></span>
            </div>
        </div>

        <!-- 공급자/공급받는자 정보 -->
        <div class="section-row">
            <!-- 공급자 (두손기획인쇄) -->
            <div class="section">
                <div class="section-title">공급자</div>
                <div class="company-info">
                    <span class="company-label">등록번호</span>
                    <span class="company-value">123-45-67890</span>
                </div>
                <div class="company-info">
                    <span class="company-label">상호 (법인명)</span>
                    <span class="company-value">두손기획인쇄</span>
                </div>
                <div class="company-info">
                    <span class="company-label">성명 (대표자)</span>
                    <span class="company-value">홍길동</span>
                </div>
                <div class="company-info">
                    <span class="company-label">사업장 주소</span>
                    <span class="company-value">서울특별시 강남구 테헤란로 123</span>
                </div>
                <div class="company-info">
                    <span class="company-label">업태</span>
                    <span class="company-value">제조업</span>
                </div>
                <div class="company-info">
                    <span class="company-label">종목</span>
                    <span class="company-value">인쇄업</span>
                </div>
                <div class="company-info">
                    <span class="company-label">전화번호</span>
                    <span class="company-value">02-1234-5678</span>
                </div>
                <div class="company-info">
                    <span class="company-label">이메일</span>
                    <span class="company-value">info@dsp114.com</span>
                </div>
            </div>

            <!-- 공급받는자 (고객) -->
            <div class="section">
                <div class="section-title">공급받는자</div>
                <div class="company-info">
                    <span class="company-label">등록번호</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_business_number'] ?? '-'); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">상호 (법인명)</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_business_name'] ?? '-'); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">성명 (대표자)</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_name']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">사업장 주소</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_address'] ?? '-'); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">업태</span>
                    <span class="company-value">-</span>
                </div>
                <div class="company-info">
                    <span class="company-label">종목</span>
                    <span class="company-value">-</span>
                </div>
                <div class="company-info">
                    <span class="company-label">전화번호</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_phone'] ?? '-'); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">이메일</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->data['buyer_email']); ?></span>
                </div>
            </div>
        </div>

        <!-- 품목 내역 -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="100">작성일자</th>
                    <th>품목</th>
                    <th width="60">수량</th>
                    <th width="100">단가</th>
                    <th width="120">공급가액</th>
                    <th width="100">세액</th>
                    <th>비고</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = $this->data['items'] ?? [];
                if (empty($items)) {
                    $items = [[
                        'name' => $this->data['item_name'] ?? '인쇄물',
                        'quantity' => 1,
                        'unit_price' => $this->data['supply_amount'],
                        'supply_amount' => $this->data['supply_amount'],
                        'tax_amount' => $this->data['tax_amount'],
                        'memo' => ''
                    ]];
                }

                foreach ($items as $idx => $item):
                ?>
                <tr>
                    <td><?php echo $idx + 1; ?></td>
                    <td><?php echo htmlspecialchars($this->data['issue_date']); ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['quantity']); ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo number_format($item['supply_amount']); ?></td>
                    <td class="text-right"><?php echo number_format($item['tax_amount']); ?></td>
                    <td><?php echo htmlspecialchars($item['memo'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>

                <!-- 빈 행 추가 (최소 5행 유지) -->
                <?php for ($i = count($items); $i < 5; $i++): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- 금액 합계 -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">공급가액</span>
                <span class="amount-value"><?php echo number_format($this->data['supply_amount']); ?>원</span>
            </div>
            <div class="amount-row">
                <span class="amount-label">세액 (10%)</span>
                <span class="amount-value"><?php echo number_format($this->data['tax_amount']); ?>원</span>
            </div>
            <div class="amount-row total">
                <span class="amount-label">합계금액</span>
                <span class="amount-value"><?php echo number_format($this->data['total_amount']); ?>원</span>
            </div>
        </div>

        <!-- 하단 정보 -->
        <div class="footer-section">
            <p>본 세금계산서는 전자세금계산서로 발급되었습니다.</p>
            <p>두손기획인쇄 | www.dsp114.com | TEL: 02-1234-5678</p>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">
                본 문서는 <?php echo date('Y-m-d H:i:s'); ?>에 출력되었습니다.
            </p>
        </div>
    </div>

    <script>
        // 인쇄 후 창 닫기 여부 확인
        window.onafterprint = function() {
            if (confirm('인쇄가 완료되었습니다. 창을 닫으시겠습니까?')) {
                window.close();
            }
        };
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>
