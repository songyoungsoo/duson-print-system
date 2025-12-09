<?php
/**
 * 고객용 견적서 공개 페이지
 * ?token=xxx 로 접근
 * generate_pdf.php와 동일한 견적서 양식 적용
 */

session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/QuoteManager.php';
require_once __DIR__ . '/../includes/PriceHelper.php';

$token = $_GET['token'] ?? '';
$manager = new QuoteManager($db);

$quote = $manager->getByToken($token);
if (!$quote) {
    http_response_code(404);
    die('<h1>견적서를 찾을 수 없습니다.</h1><p>링크가 잘못되었거나 만료되었습니다.</p>');
}

// 조회 시 상태 업데이트 (draft → viewed)
if ($quote['status'] === 'draft' || $quote['status'] === 'sent') {
    $manager->updateStatus($quote['id'], 'viewed');
}

$company = $manager->getCompanySettings();
$items = $quote['items'];

// 상태 라벨
$statusLabels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송완료', 'color' => '#0d6efd'],
    'viewed' => ['label' => '확인됨', 'color' => '#17a2b8'],
    'accepted' => ['label' => '승인', 'color' => '#28a745'],
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d'],
    'converted' => ['label' => '주문전환', 'color' => '#198754']
];

$statusInfo = $statusLabels[$quote['status']] ?? ['label' => $quote['status'], 'color' => '#6c757d'];
$isExpired = strtotime($quote['valid_until']) < time();

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
    <title>견적서 - <?php echo htmlspecialchars($quote['quote_no']); ?></title>
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

        /* 상태 배너 */
        .status-banner {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
        }
        .status-banner.expired { background: #f8d7da; color: #842029; }
        .status-banner.accepted { background: #d1e7dd; color: #0f5132; }
        .status-banner.rejected { background: #f8d7da; color: #842029; }

        /* 버튼 및 액션 */
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-accept { background: #28a745; color: #fff; }
        .btn-accept:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: #fff; }
        .btn-reject:hover { background: #c82333; }
        .btn-pdf { background: #6c757d; color: #fff; }
        .btn-pdf:hover { background: #5a6268; }
        .btn-print { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: #fff; }
        .btn-print:hover { background: #2980b9; }

        .response-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .response-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .response-form .btns {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .response-form h3 {
            margin-bottom: 15px;
            font-size: 16px;
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
            .actions, .status-banner, .response-form {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="quote-paper">
        <?php if ($isExpired && $quote['status'] !== 'accepted'): ?>
            <div class="status-banner expired">
                <?php
                $validUntil = $quote['valid_until'];
                if ($validUntil && strtotime($validUntil) > 0) {
                    echo '이 견적서는 ' . date('Y년 m월 d일', strtotime($validUntil)) . '에 만료되었습니다.';
                } else {
                    echo '이 견적서는 만료되었습니다.';
                }
                ?>
            </div>
        <?php elseif ($quote['status'] === 'accepted'): ?>
            <div class="status-banner accepted">
                ✅ 이 견적서는 승인되었습니다.
            </div>
        <?php elseif ($quote['status'] === 'rejected'): ?>
            <div class="status-banner rejected">
                ❌ 이 견적서는 거절되었습니다.
            </div>
        <?php endif; ?>

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
                    <th style="width: 10%;">수량</th>
                    <th style="width: 7%;">단위</th>
                    <th style="width: 13%;">단가</th>
                    <th style="width: 15%;">공급가액</th>
                    <th style="width: 10%;">비 고</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td class="center"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="spec"><?php echo nl2br(htmlspecialchars($item['specification'])); ?></td>
                    <td class="center"><?php
                        $qty = $item['quantity'];
                        $qtyDisplay = ($qty == intval($qty)) ? number_format($qty) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
                        echo $qtyDisplay;

                        // 전단지(inserted)인 경우 매수 표시 추가
                        if ($item['product_type'] == 'inserted' && !empty($item['source_data'])) {
                            $sourceData = json_decode($item['source_data'], true);
                            if (!empty($sourceData['mesu'])) {
                                echo '<br><span style="font-size: 10px; color: #666;">(' . number_format($sourceData['mesu']) . '매)</span>';
                            }
                        }
                    ?></td>
                    <td class="center"><?php echo htmlspecialchars($item['unit']); ?></td>
                    <td class="right"><?php
                        // 단가 계산: 역계산 검증으로 무한소수는 생략
                        echo formatUnitPrice($item['supply_price'], $qty, '-');
                    ?></td>
                    <td class="right"><?php echo number_format($item['supply_price']); ?></td>
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
                    <td class="right" style="font-weight: bold;"><?php echo number_format($quote['supply_total']); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">부가세</td>
                    <td class="right" style="font-weight: bold;"><?php echo number_format($quote['vat_total']); ?></td>
                    <td></td>
                </tr>
                <?php if ($quote['delivery_price'] > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">배송비 (공급가)</td>
                    <td class="right" style="font-weight: bold;"><?php echo number_format($quote['delivery_price']); ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">배송비 VAT</td>
                    <td class="right" style="font-weight: bold;"><?php echo number_format($quote['delivery_vat'] ?? round($quote['delivery_price'] * 0.1)); ?></td>
                    <td></td>
                </tr>
                <?php endif; ?>
                <?php if ($quote['discount_amount'] > 0): ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0;">할인</td>
                    <td class="right" style="font-weight: bold; color: #c00;">-<?php echo number_format($quote['discount_amount']); ?></td>
                    <td></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold; background: #f0f0f0; font-size: 14px;">합 계 (VAT포함)</td>
                    <td class="right" style="font-weight: bold; font-size: 14px;"><?php echo number_format($quote['grand_total']); ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- 하단 정보 -->
        <div class="footer-info">
            <p><strong>입금 계좌번호 :</strong> 국민 999-1688-2384 / 신한 110-342-543507 / 농협 301-2632-1829 예금주: 두손기획인쇄 차경선</p>
            <p><strong>담당자 :</strong> <?php echo htmlspecialchars($quote['customer_name']); ?></p>
            <?php if (!empty($quote['notes'])): ?>
            <p><strong>비 고 :</strong> <?php echo nl2br(htmlspecialchars($quote['notes'])); ?></p>
            <?php else: ?>
            <p><strong>비 고 :</strong> 택배는 착불기준입니다</p>
            <?php endif; ?>
        </div>

        <!-- 액션 버튼 -->
        <?php if (!$isExpired && !in_array($quote['status'], ['accepted', 'rejected', 'converted'])): ?>
        <div class="actions">
            <button class="btn btn-accept" onclick="showResponseForm('accept')">✓ 승인하기</button>
            <button class="btn btn-reject" onclick="showResponseForm('reject')">✕ 보류하기</button>
            <a href="../api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $token; ?>" class="btn btn-pdf" target="_blank">📄 인쇄/PDF</a>
        </div>

        <div class="response-form" id="responseForm">
            <h3 id="responseTitle"></h3>
            <textarea id="responseNotes" rows="3" placeholder="메모를 입력하세요 (선택사항)"></textarea>
            <div class="btns">
                <button class="btn" style="background:#ddd; color:#333;" onclick="hideResponseForm()">취소</button>
                <button class="btn btn-accept" id="confirmBtn" onclick="submitResponse()">확인</button>
            </div>
        </div>
        <?php else: ?>
        <div class="actions">
            <a href="../api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $token; ?>" class="btn btn-pdf" target="_blank">📄 인쇄/PDF</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
    let currentAction = '';

    function showResponseForm(action) {
        currentAction = action;
        document.getElementById('responseTitle').textContent = action === 'accept' ? '견적서를 승인하시겠습니까?' : '견적서를 보류하시겠습니까?';
        document.getElementById('confirmBtn').className = action === 'accept' ? 'btn btn-accept' : 'btn btn-reject';
        document.getElementById('responseForm').style.display = 'block';
    }

    function hideResponseForm() {
        document.getElementById('responseForm').style.display = 'none';
        currentAction = '';
    }

    function submitResponse() {
        const notes = document.getElementById('responseNotes').value;
        const formData = new FormData();
        formData.append('token', '<?php echo $token; ?>');
        formData.append('action', currentAction);
        formData.append('notes', notes);

        fetch('../api/respond.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            alert('오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
