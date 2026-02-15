<?php
/**
 * 전자세금계산서 HTML 템플릿 생성 클래스
 * 경로: /includes/TaxInvoiceTemplate.php
 * 
 * 사용처: /mypage/view_invoice.php
 * 디자인: 홈페이지 헤더색(#1E4E79) 기준 견적서 테마 통일
 * SSOT: /mlangprintauto/includes/company_info.php
 */

require_once __DIR__ . '/../mlangprintauto/includes/company_info.php';

class TaxInvoiceTemplate {
    private $data;
    private $company;

    public function __construct($invoice_data) {
        $this->data = $invoice_data;
        $this->company = getCompanyInfo();
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
            border: 2px solid #2a6496;
        }

        .invoice-header {
            text-align: center;
            padding: 15px;
            border-bottom: 2px solid #2a6496;
            background: #1E4E79;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #ffffff;
            letter-spacing: 6px;
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
            border-bottom: 1px solid #2a6496;
            background: #e8eff7;
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
            border-bottom: 1px solid #2a6496;
        }

        .section {
            flex: 1;
            padding: 12px;
        }

        .section:first-child {
            border-right: 1px solid #2a6496;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #94a3b8;
            color: #1E4E79;
        }

        .company-info {
            margin-bottom: 6px;
        }

        .company-label {
            display: inline-block;
            width: 90px;
            font-weight: 600;
            color: #1E4E79;
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
            border: 1px solid #94a3b8;
        }

        .items-table th {
            background: #1E4E79;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            border-color: #3a7ab5;
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
            background: #1E4E79;
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
            background: #163d5f;
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
            <h1 class="invoice-title">전자세금계산서 (공급받는자 보관용)</h1>
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
            <!-- 공급자 (SSOT: company_info.php) -->
            <div class="section">
                <div class="section-title">공급자</div>
                <div class="company-info">
                    <span class="company-label">등록번호</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['business_number']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">상호 (법인명)</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['name']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">성명 (대표자)</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['owner']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">사업장 주소</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['address']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">업태</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['business_type']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">종목</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['business_item']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">전화번호</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['phone']); ?></span>
                </div>
                <div class="company-info">
                    <span class="company-label">이메일</span>
                    <span class="company-value"><?php echo htmlspecialchars($this->company['email']); ?></span>
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
                    <th width="35">월일</th>
                    <th>품목</th>
                    <th width="70">규격</th>
                    <th width="45">수량</th>
                    <th width="90">단가</th>
                    <th width="100">공급가액</th>
                    <th width="90">세액</th>
                    <th width="60">비고</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $items = $this->data['items'] ?? [];
                if (empty($items)) {
                    $items = [[
                        'name' => $this->data['item_name'] ?? '인쇄물',
                        'spec' => $this->data['item_type'] ?? '',
                        'quantity' => 1,
                        'unit_price' => $this->data['supply_amount'],
                        'supply_amount' => $this->data['supply_amount'],
                        'tax_amount' => $this->data['tax_amount'],
                        'memo' => ''
                    ]];
                }

                $issue_md = date('m/d', strtotime($this->data['issue_date']));
                foreach ($items as $idx => $item):
                ?>
                <tr>
                    <td><?php echo $issue_md; ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['spec'] ?? ''); ?></td>
                    <td><?php echo number_format($item['quantity']); ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo number_format($item['supply_amount']); ?></td>
                    <td class="text-right"><?php echo number_format($item['tax_amount']); ?></td>
                    <td><?php echo htmlspecialchars($item['memo'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>

                <?php for ($i = count($items); $i < 4; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
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

        <!-- 합계금액 -->
        <table class="items-table" style="margin-bottom:0;">
            <tr>
                <th style="width:80px;">합계금액</th>
                <th>공급가액</th>
                <th>세액</th>
            </tr>
            <tr>
                <td style="font-weight:700; font-size:14px; background:#e8eff7;"><?php echo number_format($this->data['total_amount']); ?></td>
                <td class="text-right"><?php echo number_format($this->data['supply_amount']); ?></td>
                <td class="text-right"><?php echo number_format($this->data['tax_amount']); ?></td>
            </tr>
        </table>

        <!-- 현금/수표/어음/외상미수금 -->
        <table class="items-table" style="margin-top:-1px; margin-bottom:0;">
            <tr>
                <th style="width:80px;">현금</th>
                <th>수표</th>
                <th>어음</th>
                <th>외상미수금</th>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="text-right"><?php echo number_format($this->data['total_amount']); ?></td>
            </tr>
        </table>

        <!-- 영수/청구 구분 -->
        <div style="text-align:center; padding:15px; border:1px solid #94a3b8; border-top:none; margin-bottom:20px; font-size:14px;">
            위 금액을 &nbsp;
            <span style="font-size:16px;">○</span> 영수 &nbsp;&nbsp;
            <span style="font-size:16px; color:#1E4E79; font-weight:700;">●</span> <strong>청구</strong>
            &nbsp; 함
        </div>

        <!-- 하단 정보 (SSOT: company_info.php) -->
        <div class="footer-section">
            <p>본 세금계산서는 전자세금계산서로 발급되었습니다.</p>
            <p><?php echo htmlspecialchars($this->company['name']); ?> | <?php echo htmlspecialchars($this->company['address']); ?> | TEL: <?php echo htmlspecialchars($this->company['phone']); ?></p>
            <p>사업자등록번호: <?php echo htmlspecialchars($this->company['business_number']); ?> | 대표자: <?php echo htmlspecialchars($this->company['owner']); ?></p>
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
