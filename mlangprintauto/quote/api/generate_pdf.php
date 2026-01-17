<?php
/**
 * 견적서 PDF 생성 / 인쇄용 페이지
 * cart.php 견적서 스타일 적용
 */

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/QuoteManager.php';
require_once __DIR__ . '/../includes/QuoteTableRenderer.php';
require_once __DIR__ . '/../../../includes/QuantityFormatter.php';  // ✅ 2026-01-16: SSOT 연동

// 파라미터 확인
$id = intval($_GET['id'] ?? 0);
$token = trim($_GET['token'] ?? '');

if (!$id || !$token) {
    die('<h1>잘못된 요청입니다.</h1>');
}

$manager = new QuoteManager($db);

// 토큰 검증
$quote = $manager->getByToken($token);

if (!$quote || $quote['id'] != $id) {
    die('<h1>견적서를 찾을 수 없습니다.</h1>');
}

$company = $manager->getCompanySettings();
$items = $quote['items'];

// ✅ 2026-01-17: QuoteTableRenderer SSOT 사용
$renderer = new QuoteTableRenderer($db);

// 금액을 한글로 변환
function numberToKorean($number) {
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

$koreanAmount = numberToKorean($quote['grand_total']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서_<?php echo htmlspecialchars($quote['customer_name']); ?>_<?php echo htmlspecialchars($quote['quote_no']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            background: #f5f5f5;
        }

        .quote-paper {
            max-width: 210mm;
            margin: 20px auto;
            background: #fff;
            padding: 10mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .quote-no {
            font-size: 10px;
            color: #666;
        }

        .header {
            text-align: center;
            margin: 15px 0 10px 0;
        }

        .header h1 {
            font-size: 28px;
            margin: 0;
            letter-spacing: 12px;
            font-weight: bold;
        }

        /* 테이블 스타일 */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .supplier-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .supplier-table td {
            padding: 3px;
        }

        .total-amount {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .korean-amount {
            font-size: 14px;
            text-align: center;
        }

        /* 품목 테이블 */
        .items-table th {
            background: #f0f0f0;
            padding: 5px;
            text-align: center;
        }

        .items-table td {
            padding: 4px;
            vertical-align: top;
        }

        .items-table .center {
            text-align: center;
        }

        .items-table .right {
            text-align: right;
        }

        .items-table .spec {
            font-size: 11px;
        }

        /* 하단 정보 */
        .footer-info {
            margin-top: 12px;
            font-size: 13px;
            line-height: 1.8;
        }

        .footer-info p {
            margin: 4px 0;
        }

        /* 인쇄 버튼 */
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .print-controls button {
            padding: 12px 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 3px 10px rgba(52,152,219,0.3);
        }

        .btn-close {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            box-shadow: 0 3px 10px rgba(149,165,166,0.3);
        }

        @media print {
            body {
                background: #fff;
            }
            .quote-paper {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .print-controls {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button class="btn-print" onclick="window.print();">인쇄/PDF</button>
        <button class="btn-close" onclick="window.close();">닫기</button>
    </div>

    <div class="quote-paper">
        <div class="quote-no">No. <?php echo htmlspecialchars($quote['quote_no']); ?></div>

        <!-- 견적서 헤더 -->
        <div class="header">
            <h1>견 적 서</h1>
        </div>

        <!-- 상단 정보 테이블 -->
        <table style="margin-bottom: 8px;">
            <tr>
                <td style="width: 12%; font-weight: bold; background: #f0f0f0;">견적일</td>
                <td style="width: 28%;"><?php echo date('Y년 m월 d일', strtotime($quote['created_at'])); ?></td>
                <td rowspan="5" colspan="4" style="width: 45%; vertical-align: top; padding: 0;">
                    <table class="supplier-table">
                        <tr>
                            <td colspan="4" style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; background: #f0f0f0;">공 급 자</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; width: 22%;">등록번호</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; width: 28%; font-weight: bold;"><?php echo htmlspecialchars($company['business_number'] ?? '107-06-45106'); ?></td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; width: 22%;">대표자</td>
                            <td style="border-bottom: 1px solid #000; width: 28%;"><?php echo htmlspecialchars($company['representative'] ?? '차경선(직인생략)'); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000;">상 호</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; font-weight: bold;"><?php echo htmlspecialchars($company['company_name'] ?? '두손기획인쇄'); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000;">주 소</td>
                            <td colspan="3" style="border-bottom: 1px solid #000;"><?php echo htmlspecialchars($company['address'] ?? '서울 영등포구 영등포로36길9 송호빌딩 1층'); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000;">연락처</td>
                            <td colspan="3" style="border-bottom: 1px solid #000;"><?php echo htmlspecialchars($company['phone'] ?? '02-2632-1830'); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000;">업 태</td>
                            <td style="border-right: 1px solid #000;">제조</td>
                            <td style="border-right: 1px solid #000;">종 목</td>
                            <td>인쇄업외</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php if (!empty($quote['customer_company'])): ?>
            <tr>
                <td style="font-weight: bold; background: #f0f0f0;">회사명</td>
                <td><?php echo htmlspecialchars($quote['customer_company']); ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td style="font-weight: bold; background: #f0f0f0;">연락처</td>
                <td><?php echo htmlspecialchars($quote['customer_phone']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="font-weight: bold; background: #f0f0f0;">담당자</td>
                <td><?php echo htmlspecialchars($quote['customer_name']); ?> 귀하</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background: #f0f0f0;">유효기간</td>
                <td><?php echo date('Y년 m월 d일', strtotime($quote['valid_until'])); ?>까지</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2" style="text-align: center; font-weight: bold; font-size: 14px; vertical-align: middle; background: #f8f8f8;">
                    합계금액(부가세포함)
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td colspan="2" class="korean-amount">
                    일금 <?php echo $koreanAmount; ?>원정<br>
                    ( ₩<?php echo number_format($quote['grand_total']); ?> )
                </td>
                <td colspan="2" class="total-amount">
                    <?php echo number_format($quote['grand_total']); ?> 원
                </td>
            </tr>
        </table>

        <!-- 품목 테이블 -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">NO</th>
                    <th style="width: 15%;">품 목</th>
                    <th style="width: 25%;">규격 및 사양</th>
                    <th style="width: 12%;">수량</th>
                    <th style="width: 5%;">단위</th>
                    <th style="width: 8%;">단가</th>
                    <th style="width: 18%;">공급가액</th>
                    <th style="width: 12%;">비 고</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ✅ 2026-01-17: QuoteTableRenderer SSOT 사용
                foreach ($items as $index => $item): ?>
                <tr>
                    <td class="center"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="spec"><?php echo nl2br(htmlspecialchars($item['specification'])); ?></td>
                    <td class="center"><?php echo $renderer->formatQuantityCell($item); ?></td>
                    <td class="center"><?php echo $renderer->formatUnitCell($item); ?></td>
                    <td class="right"><?php echo $renderer->formatUnitPriceCell($item); ?></td>
                    <td class="right"><?php echo $renderer->formatSupplyPriceCell($item); ?></td>
                    <td class="spec"><?php echo htmlspecialchars($item['notes'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>

                <?php
                // 빈 줄 추가 (최소 5개 행 보장)
                $emptyRows = max(0, 5 - count($items));
                for ($i = 0; $i < $emptyRows; $i++):
                ?>
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
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">공급가액 합계</td>
                    <td colspan="2" class="right" style="font-weight: bold;"><?php echo number_format($quote['supply_total']); ?></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">부가세</td>
                    <td colspan="2" class="right" style="font-weight: bold;"><?php echo number_format($quote['vat_total']); ?></td>
                </tr>
                <?php if ($quote['delivery_price'] > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">배송비</td>
                    <td colspan="2" class="right" style="font-weight: bold;"><?php echo number_format($quote['delivery_price']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($quote['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">할인</td>
                    <td colspan="2" class="right" style="font-weight: bold; color: #c00;">-<?php echo number_format($quote['discount_amount']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0; font-size: 14px;">합 계 (VAT포함)</th>
                    <td colspan="2" class="right" style="font-weight: bold; font-size: 14px;"><?php echo number_format($quote['grand_total']); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- 하단 정보 -->
        <div class="footer-info">
            <p><strong>입금 계좌번호 :</strong> 국민 999-1688-2384 / 신한 110-342-543507 / 농협 301-2632-1830-11 예금주: 두손기획인쇄 차경선</p>
            <p><strong>담당자 :</strong> <?php echo htmlspecialchars($quote['customer_name']); ?></p>
            <?php if (!empty($quote['notes'])): ?>
            <p><strong>비 고 :</strong> <?php echo nl2br(htmlspecialchars($quote['notes'])); ?></p>
            <?php else: ?>
            <p><strong>비 고 :</strong> 택배는 착불기준입니다</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // 자동 인쇄 (선택적)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
