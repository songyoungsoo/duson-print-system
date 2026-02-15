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

    // === 숫자를 한글로 변환 ===
    $numberToKorean = function($number) {
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
    };
    $koreanAmount = $numberToKorean($grandTotal);

    // === HTML 이스케이프 ===
    $e = function($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    };

    // === HTML 생성 시작 ===
    ob_start();
?>
<!-- 표준 견적서 레이아웃 - quotation.html 스타일 -->
<style>
/* A4 용지 설정 */
@page {
    size: A4;
    margin: 15mm;
}
.quote-body {
    font-family: "Noto Sans KR", sans-serif;
    font-size: 13px;
    color: #000;
    margin: 0;
    padding: 0;
    background: white;
}
/* A4 컨테이너 */
.a4-page {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 15mm;
    background: white;
    box-sizing: border-box;
}
@media print {
    .quote-body {
        background: white;
    }
    .a4-page {
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
}
.a4-page table {
    width: 100%;
    border-collapse: collapse;
}
.a4-page th, .a4-page td {
    border: 1px solid #94a3b8;
    padding: 6px;
    vertical-align: middle;
}
.a4-page th {
    background: #1e293b;
    color: #fff;
    text-align: center;
}
.a4-page .title {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    border: none;
    color: #1e293b;
}
.a4-page .no-border {
    border: none;
}
.a4-page .right {
    text-align: right;
}
.a4-page .center {
    text-align: center;
}
.a4-page .bold {
    font-weight: bold;
}
.a4-page .table-bordered {
    border: 2px solid #334155;
}
</style>

<div class="quote-body">
<div class="a4-page">

<table>
    <tr>
        <td class="no-border">No. <?php echo $e($quote['quote_no'] ?? ''); ?></td>
    </tr>
    <tr>
        <td class="title" colspan="4">견 적 서</td>
        <td class="no-border"></td>
    </tr>
</table>

<br>

<table class="table-bordered">
    <colgroup>
        <!-- 공급받는자 50% -->
        <col style="width:12%;">
        <col style="width:38%;">
        <!-- 공급자 50% -->
        <col style="width:10%;">
        <col style="width:15%;">
        <col style="width:10%;">
        <col style="width:15%;">
    </colgroup>
    <tr>
        <th>견적일</th>
        <td><?php echo $formatDate($quote['quote_date'] ?? date('Y-m-d')); ?></td>
        <th colspan="4">공급자</th>
    </tr>
    <tr>
        <th>회사명</th>
        <td><?php echo $e($quote['customer_company'] ?? ''); ?></td>
        <th>등록번호</th>
        <td class="bold"><?php echo $e($supplier['business_no'] ?? ''); ?></td>
        <th>대표자</th>
        <td><?php echo $e($supplier['ceo_name'] ?? ''); ?> <span style="font-size:0.7em;color:#666;">(직인생략)</span></td>
    </tr>
    <tr>
        <th>담당자</th>
        <td><?php echo $e($quote['customer_name'] ?? ''); ?> 귀하</td>
        <th>상호</th>
        <td colspan="3" class="bold"><?php echo $e($supplier['company_name'] ?? ''); ?></td>
    </tr>
    <tr>
        <th>유효기간</th>
        <td>발행일로부터 <?php echo intval($quote['validity_days'] ?? 7); ?>일간 유효</td>
        <th>주소</th>
        <td colspan="3"><?php echo $e($supplier['address'] ?? ''); ?></td>
    </tr>
    <tr>
        <th>전화번호</th>
        <td><?php echo $e($supplier['phone'] ?? ''); ?></td>
        <th>업태</th>
        <td>제조</td>
        <th>종목</th>
        <td>인쇄업외</td>
    </tr>
</table>

<br>

<table class="table-bordered">
    <colgroup>
        <col style="width:5%;">
        <col style="width:8%;">
        <col style="width:32%;">
        <col style="width:8%;">
        <col style="width:6%;">
        <col style="width:10%;">
        <col style="width:14%;">
        <col style="width:17%;">
    </colgroup>
    <tr>
        <th colspan="2" class="center bold">합계금액(VAT포함)</th>
        <td colspan="2" class="center bold">
            일금 <?php echo $koreanAmount; ?>원정<br>
            ( ₩<?php echo $formatMoney($grandTotal); ?> )
        </td>
        <td colspan="4" class="center bold" style="font-size:20px;color:#2563eb;">
            <?php echo $formatMoney($grandTotal); ?> 원
        </td>
    </tr>
    <tr>
        <th>NO</th>
        <th>품목</th>
        <th>규격 및 사양</th>
        <th>수량</th>
        <th>단위</th>
        <th>단가</th>
        <th>공급가액</th>
        <th>비고</th>
    </tr>

    <?php if (empty($items)): ?>
    <tr>
        <td colspan="8" class="center" style="padding:20px;color:#888;">등록된 품목이 없습니다.</td>
    </tr>
    <?php else: ?>
    <?php foreach ($items as $idx => $item): ?>
    <tr>
        <td class="center"><?php echo ($idx + 1); ?></td>
        <td class="center"><?php echo $e($item['product_name'] ?? ''); ?></td>
        <td><?php echo nl2br($e($item['specification'] ?? '')); ?></td>
        <td class="center"><?php echo $e($item['quantity_display'] ?? ''); ?></td>
        <td class="center"><?php echo $e($item['unit'] ?? '개'); ?></td>
        <td class="right"><?php echo $formatMoney($item['unit_price'] ?? 0); ?></td>
        <td class="right"><?php echo $formatMoney($item['supply_price'] ?? 0); ?></td>
        <td class="center"><?php echo $e($item['notes'] ?? ''); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- 빈 행 (최소 3행 유지) -->
    <?php for ($i = count($items); $i < 3; $i++): ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <?php endfor; ?>

    <tr>
        <td colspan="6" class="right bold">공급가액 합계</td>
        <td colspan="2" class="right bold"><?php echo $formatMoney($supplyTotal); ?></td>
    </tr>
    <tr>
        <td colspan="6" class="right bold">부가세</td>
        <td colspan="2" class="right bold"><?php echo $formatMoney($vatAmount); ?></td>
    </tr>
    <tr style="background:#f1f5f9;">
        <th colspan="6" class="right bold">합 계 (VAT포함)</th>
        <td colspan="2" class="right bold" style="color:#2563eb;"><?php echo $formatMoney($grandTotal); ?></td>
    </tr>
</table>

<br>

<table>
    <tr>
        <td class="no-border">
            입금 계좌번호 :<br>
            <?php if (!empty($supplier['bank_accounts'])): ?>
            <?php foreach ($supplier['bank_accounts'] as $bank): ?>
            <?php echo $e($bank['bank_name']); ?> <?php echo $e($bank['account_no']); ?> /
            <?php endforeach; ?>
            <?php endif; ?>
            예금주 : <?php echo $e($supplier['account_holder'] ?? $supplier['company_name'] ?? ''); ?>
        </td>
    </tr>
    <tr>
        <td class="no-border">담당자 : <?php echo $e($quote['customer_name'] ?? ''); ?></td>
    </tr>
    <tr>
        <td class="no-border">비고 : 택배는 착불기준입니다</td>
    </tr>
</table>

</div>
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
