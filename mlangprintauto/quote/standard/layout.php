<?php
/**
 * 표준 견적서 레이아웃 (단일 소스)
 * quotation.html 스타일 기준
 *
 * @param array $quote 견적서 데이터
 * @param array $items 품목 목록
 * @param array $supplier 공급자 정보
 * @param string $baseUrl 이미지/리소스 기본 URL
 * @return string 완성된 HTML
 */

function renderQuoteLayout(array $quote, array $items, array $supplier, string $baseUrl = ''): string {
    // === 금액 계산 ===
    $supplyTotal = 0;
    foreach ($items as $item) {
        $supplyTotal += intval($item['supply_price'] ?? 0);
    }
    $vatAmount = round($supplyTotal * 0.1);
    $grandTotal = $supplyTotal + $vatAmount;

    // === 포맷팅 함수 ===
    $formatMoney = function($val) {
        return number_format(intval($val));
    };

    $formatDate = function($date) {
        if (empty($date)) return '-';
        return date('Y년 m월 d일', strtotime($date));
    };

    // === HTML 이스케이프 ===
    $e = function($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    };

    // === HTML 생성 시작 ===
    ob_start();
?>
<!-- 표준 견적서 레이아웃 v2.0 - quotation.html 스타일 (A4) -->
<div class="a4-page" style="width:210mm;min-height:297mm;margin:20px auto;padding:15mm;background:white;box-shadow:0 2px 10px rgba(0,0,0,0.1);box-sizing:border-box;font-family:'Noto Sans KR',sans-serif;font-size:13px;color:#000;line-height:1.4;">

    <!-- 견적번호 -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:5px;">
        <tr>
            <td style="border:none;font-size:13px;">No. <?php echo $e($quote['quote_no'] ?? ''); ?></td>
        </tr>
    </table>

    <!-- 제목 -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
        <tr>
            <td style="border:none;font-size:24px;font-weight:bold;text-align:center;padding:15px 0;">견 적 서</td>
        </tr>
    </table>

    <!-- 기본정보 테이블 (공급받는자 50% / 공급자 50%) -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;border:2px solid #000;">
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:12%;">견적일</th>
            <td style="border:1px solid #000;padding:6px;width:38%;"><?php echo $formatDate($quote['quote_date'] ?? date('Y-m-d')); ?></td>
            <th colspan="4" style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:50%;">공급자</th>
        </tr>
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">회사명</th>
            <td style="border:1px solid #000;padding:6px;"><?php echo $e($quote['customer_company'] ?? ''); ?></td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:10%;">등록번호</th>
            <td style="border:1px solid #000;padding:6px;font-weight:bold;width:15%;"><?php echo $e($supplier['business_no'] ?? ''); ?></td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:10%;">대표자</th>
            <td style="border:1px solid #000;padding:6px;width:15%;"><?php echo $e($supplier['ceo_name'] ?? ''); ?> <span style="font-size:0.7em;color:#666;">(직인생략)</span></td>
        </tr>
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">담당자</th>
            <td style="border:1px solid #000;padding:6px;"><?php echo $e($quote['customer_name'] ?? ''); ?> 귀하</td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">상호</th>
            <td colspan="3" style="border:1px solid #000;padding:6px;font-weight:bold;"><?php echo $e($supplier['company_name'] ?? ''); ?></td>
        </tr>
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">유효기간</th>
            <td style="border:1px solid #000;padding:6px;">발행일로부터 <?php echo intval($quote['validity_days'] ?? 7); ?>일간 유효</td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">주소</th>
            <td colspan="3" style="border:1px solid #000;padding:6px;"><?php echo $e($supplier['address'] ?? ''); ?></td>
        </tr>
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">전화번호</th>
            <td style="border:1px solid #000;padding:6px;"><?php echo $e($supplier['phone'] ?? ''); ?></td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">업태</th>
            <td style="border:1px solid #000;padding:6px;">제조</td>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">종목</th>
            <td style="border:1px solid #000;padding:6px;">인쇄업외</td>
        </tr>
    </table>

    <!-- 합계금액 테이블 -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;border:2px solid #000;">
        <tr>
            <th colspan="2" style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:50%;">합계금액 (부가세포함)</th>
            <td rowspan="2" style="border:1px solid #000;padding:10px;text-align:center;font-weight:bold;font-size:20px;width:50%;">
                <?php echo $formatMoney($grandTotal); ?> 원
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border:1px solid #000;padding:10px;text-align:center;font-weight:bold;">
                ( ₩<?php echo $formatMoney($grandTotal); ?> )
            </td>
        </tr>
    </table>

    <!-- 품목 테이블 -->
    <table style="width:100%;border-collapse:collapse;margin-bottom:10px;border:2px solid #000;">
        <tr>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:40px;">NO</th>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:70px;">품목</th>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;">규격 및 사양</th>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:70px;">수량</th>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:70px;">단가</th>
            <th style="background:#e8e8e8;border:1px solid #000;padding:6px;text-align:center;width:90px;">공급가액</th>
        </tr>

        <?php if (empty($items)): ?>
        <tr>
            <td colspan="6" style="border:1px solid #000;padding:20px;text-align:center;color:#888;">등록된 품목이 없습니다.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($items as $idx => $item): ?>
        <tr>
            <td style="border:1px solid #000;padding:6px;text-align:center;"><?php echo ($idx + 1); ?></td>
            <td style="border:1px solid #000;padding:6px;text-align:center;"><?php echo $e($item['product_name'] ?? ''); ?></td>
            <td style="border:1px solid #000;padding:6px;"><?php echo nl2br($e($item['specification'] ?? '')); ?></td>
            <td style="border:1px solid #000;padding:6px;text-align:center;"><?php echo $e($item['quantity_display'] ?? ''); ?></td>
            <td style="border:1px solid #000;padding:6px;text-align:right;"><?php echo $formatMoney($item['unit_price'] ?? 0); ?></td>
            <td style="border:1px solid #000;padding:6px;text-align:right;"><?php echo $formatMoney($item['supply_price'] ?? 0); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- 빈 행 (최소 3행 유지) -->
        <?php for ($i = count($items); $i < 3; $i++): ?>
        <tr>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
            <td style="border:1px solid #000;padding:6px;">&nbsp;</td>
        </tr>
        <?php endfor; ?>

        <!-- 합계 -->
        <tr>
            <td colspan="5" style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;">공급가액 합계</td>
            <td style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;"><?php echo $formatMoney($supplyTotal); ?></td>
        </tr>
        <tr>
            <td colspan="5" style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;">부가세</td>
            <td style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;"><?php echo $formatMoney($vatAmount); ?></td>
        </tr>
        <tr>
            <td colspan="5" style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;">합 계 (VAT포함)</td>
            <td style="border:1px solid #000;padding:6px;text-align:right;font-weight:bold;font-size:16px;"><?php echo $formatMoney($grandTotal); ?></td>
        </tr>
    </table>

    <!-- 하단 정보 -->
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="border:none;padding:6px;line-height:1.8;">
                입금 계좌번호 :<br>
                <?php if (!empty($supplier['bank_accounts'])): ?>
                <?php foreach ($supplier['bank_accounts'] as $bank): ?>
                <?php echo $e($bank['bank_name']); ?> <?php echo $e($bank['account_no']); ?> /
                <?php endforeach; ?>
                <?php endif; ?><br>
                예금주 : <?php echo $e($supplier['account_holder'] ?? $supplier['company_name'] ?? ''); ?>
            </td>
        </tr>
        <tr>
            <td style="border:none;padding:6px;">담당자 : <?php echo $e($quote['customer_name'] ?? ''); ?></td>
        </tr>
        <tr>
            <td style="border:none;padding:6px;">비고 : 택배는 착불기준입니다</td>
        </tr>
    </table>

</div>
<!-- /표준 견적서 레이아웃 -->
<?php
    return ob_get_clean();
}

/**
 * CSS 스타일만 반환 (웹 출력용)
 */
function getQuoteStyles(): string {
    return <<<CSS
/* 표준 견적서 CSS - A4 설정 */
@page {
    size: A4;
    margin: 15mm;
}

body {
    font-family: "Noto Sans KR", sans-serif;
    font-size: 13px;
    color: #000;
    margin: 0;
    padding: 0;
    background: #f0f0f0;
}

.a4-page {
    width: 210mm;
    min-height: 297mm;
    margin: 20px auto;
    padding: 15mm;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

@media print {
    body {
        background: white;
        margin: 0;
        padding: 0;
    }
    .a4-page {
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
    .no-print { display: none !important; }
}
CSS;
}
