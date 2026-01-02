<?php
/**
 * 견적서 상세 페이지 (관리자용) - 엑셀 스타일
 */

session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/includes/QuoteManager.php';

if (!$db) {
    die('데이터베이스 연결 실패');
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$manager = new QuoteManager($db);
$quote = $manager->getById($id);

if (!$quote) {
    header('Location: index.php');
    exit;
}

$company = $manager->getCompanySettings();
$items = $quote['items'];

// 이메일 발송 이력
$emailLogs = [];
$emailQuery = "SELECT * FROM quote_emails WHERE quote_id = ? ORDER BY sent_at DESC";
$stmt = mysqli_prepare($db, $emailQuery);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$emailResult = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($emailResult)) {
    $emailLogs[] = $row;
}

// 상태 라벨
$statusLabels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송', 'color' => '#0d6efd'],
    'viewed' => ['label' => '확인', 'color' => '#17a2b8'],
    'accepted' => ['label' => '승인', 'color' => '#28a745'],
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d'],
    'converted' => ['label' => '주문', 'color' => '#198754']
];

$statusInfo = $statusLabels[$quote['status']] ?? ['label' => $quote['status'], 'color' => '#6c757d'];
$isExpired = strtotime($quote['valid_until']) < time() && !in_array($quote['status'], ['accepted', 'rejected', 'converted']);

// 공개 URL
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$publicUrl = $baseUrl . '/mlangprintauto/quote/public/view.php?token=' . $quote['public_token'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 상세 - <?php echo htmlspecialchars($quote['quote_no']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; background: #f0f0f0; font-size: 13px; }

        .container { max-width: 1400px; margin: 0 auto; padding: 12px; }

        /* 헤더 */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            background: #fff;
            padding: 10px 18px;
            border: 1px solid #ccc;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 16px;
            margin-right: 8px;
        }
        .back-link:hover { color: #333; }

        .header-actions { display: flex; gap: 4px; }
        .btn {
            padding: 5px 12px;
            border: 1px solid #ccc;
            background: #f8f8f8;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }
        .btn:hover { background: #e0e0e0; }
        .btn-primary { background: #217346; color: #fff; border-color: #217346; }
        .btn-primary:hover { background: #1a5c38; }

        .status-tag {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #fff;
        }
        .expired-tag { background: #dc3545; margin-left: 5px; }

        /* 알림 박스 */
        .alert {
            padding: 10px 15px;
            margin-bottom: 12px;
            border: 1px solid;
            font-size: 13px;
        }
        .alert-warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .alert-success { background: #d4edda; border-color: #28a745; color: #155724; }
        .alert-danger { background: #f8d7da; border-color: #dc3545; color: #721c24; }

        /* 그리드 레이아웃 */
        .grid { display: grid; grid-template-columns: 1fr 350px; gap: 12px; }

        /* 섹션 박스 */
        .section {
            background: #fff;
            border: 1px solid #8c8c8c;
            margin-bottom: 12px;
        }
        .section-header {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #8c8c8c;
        }
        .section-body { padding: 12px; }

        /* 정보 테이블 (라벨-값) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            padding: 6px 10px;
            border: 1px solid #c0c0c0;
            font-size: 13px;
            vertical-align: middle;
        }
        .info-table th {
            background: #f5f5f5;
            font-weight: normal;
            width: 90px;
            text-align: left;
            color: #555;
        }
        .info-table td {
            background: #fff;
        }

        /* 품목 테이블 (엑셀 스타일) */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
        }
        .excel-table th {
            background: linear-gradient(180deg, #f8f8f8 0%, #e8e8e8 100%);
            border: 1px solid #8c8c8c;
            padding: 7px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            color: #333;
        }
        .excel-table td {
            border: 1px solid #c0c0c0;
            padding: 6px 10px;
            font-size: 13px;
            vertical-align: middle;
        }
        .excel-table tbody tr:hover { background: #e8f4fc; }
        .excel-table tbody tr:nth-child(even) { background: #fafafa; }
        .excel-table tbody tr:nth-child(even):hover { background: #e8f4fc; }

        .col-no { width: 40px; text-align: center; }
        .col-name { }
        .col-spec { }
        .col-qty { width: 60px; text-align: center; }
        .col-unit { width: 50px; text-align: center; }
        .col-price { width: 90px; text-align: right; font-family: 'Consolas', monospace; }
        .col-amount { width: 100px; text-align: right; font-family: 'Consolas', monospace; }

        /* 합계 테이블 */
        .summary-table {
            width: 250px;
            margin-left: auto;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 6px 10px;
            border: 1px solid #c0c0c0;
            font-size: 13px;
        }
        .summary-table th {
            background: #f5f5f5;
            font-weight: normal;
            text-align: left;
            width: 80px;
        }
        .summary-table td {
            text-align: right;
            font-family: 'Consolas', monospace;
        }
        .summary-table tr.total-row th,
        .summary-table tr.total-row td {
            background: #217346;
            color: #fff;
            font-weight: bold;
            border-color: #217346;
        }

        /* URL 박스 */
        .url-box {
            display: flex;
            gap: 4px;
        }
        .url-box input {
            flex: 1;
            padding: 5px 8px;
            border: 1px solid #ccc;
            font-size: 12px;
        }
        .url-box button {
            padding: 5px 12px;
            background: #5a5a5a;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .url-box button:hover { background: #444; }

        /* 이메일 이력 */
        .email-list { }
        .email-item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        .email-item:last-child { border-bottom: none; }
        .email-status { margin-right: 6px; }
        .email-addr { font-weight: 500; }
        .email-date { color: #666; font-size: 12px; margin-left: 8px; }
        .email-error { color: #dc3545; font-size: 12px; display: block; margin-top: 3px; }

        /* 비고 박스 */
        .notes-box {
            background: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 10px;
            font-size: 13px;
            white-space: pre-wrap;
        }

        /* 빈 상태 */
        .empty-text {
            color: #999;
            text-align: center;
            padding: 15px;
            font-size: 13px;
        }

        @media (max-width: 1024px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <div class="header">
            <h1>
                <a href="index.php" class="back-link">←</a>
                <?php echo htmlspecialchars($quote['quote_no']); ?>
                <span class="status-tag" style="background:<?php echo $statusInfo['color']; ?>;">
                    <?php echo $statusInfo['label']; ?>
                </span>
                <?php if ($isExpired): ?>
                <span class="status-tag expired-tag">만료</span>
                <?php endif; ?>
            </h1>
            <div class="header-actions">
                <a href="api/generate_pdf.php?id=<?php echo $quote['id']; ?>&token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">PDF</a>
                <a href="public/view.php?token=<?php echo $quote['public_token']; ?>" class="btn" target="_blank">미리보기</a>
                <?php if ($quote['status'] === 'draft'): ?>
                <button class="btn btn-primary" onclick="sendEmail()">이메일 발송</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- 알림 메시지 -->
        <?php if ($quote['status'] === 'accepted'): ?>
        <div class="alert alert-success">
            이 견적서는 고객이 승인하였습니다.
            <?php if ($quote['responded_at']): ?>
            (<?php echo date('Y-m-d H:i', strtotime($quote['responded_at'])); ?>)
            <?php endif; ?>
        </div>
        <?php elseif ($quote['status'] === 'rejected'): ?>
        <div class="alert alert-danger">
            이 견적서는 고객이 거절하였습니다.
            <?php if ($quote['responded_at']): ?>
            (<?php echo date('Y-m-d H:i', strtotime($quote['responded_at'])); ?>)
            <?php endif; ?>
        </div>
        <?php elseif ($isExpired): ?>
        <div class="alert alert-warning">
            이 견적서는 <?php echo date('Y년 m월 d일', strtotime($quote['valid_until'])); ?>에 만료되었습니다.
        </div>
        <?php endif; ?>

        <div class="grid">
            <!-- 메인 영역 -->
            <div class="main-content">
                <!-- 견적 정보 -->
                <div class="section">
                    <div class="section-header">견적 정보</div>
                    <div class="section-body">
                        <table class="info-table">
                            <tr>
                                <th>고객명</th>
                                <td><?php echo htmlspecialchars($quote['customer_name']); ?></td>
                                <th>회사명</th>
                                <td><?php echo htmlspecialchars($quote['customer_company'] ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>이메일</th>
                                <td><?php echo htmlspecialchars($quote['customer_email'] ?: '-'); ?></td>
                                <th>연락처</th>
                                <td><?php echo htmlspecialchars($quote['customer_phone'] ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>작성일</th>
                                <td><?php echo date('Y-m-d H:i', strtotime($quote['created_at'])); ?></td>
                                <th>유효기간</th>
                                <td><?php echo date('Y-m-d', strtotime($quote['valid_until'])); ?>까지</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- 품목 목록 -->
                <div class="section">
                    <div class="section-header">품목 목록 (<?php echo count($items); ?>건)</div>
                    <div class="section-body" style="padding: 0;">
                        <table class="excel-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th class="col-name">품명</th>
                                    <th class="col-spec">규격/사양</th>
                                    <th class="col-qty">수량</th>
                                    <th class="col-unit">단위</th>
                                    <th class="col-price">단가</th>
                                    <th class="col-amount">금액</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td class="col-no"><?php echo $no++; ?></td>
                                    <td class="col-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="col-spec"><?php echo htmlspecialchars($item['specification']); ?></td>
                                    <td class="col-qty"><?php echo number_format($item['quantity']); ?></td>
                                    <td class="col-unit"><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td class="col-price"><?php echo number_format($item['unit_price']); ?></td>
                                    <td class="col-amount"><?php echo number_format($item['total_price']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div style="padding: 12px;">
                            <table class="summary-table">
                                <tr>
                                    <th>공급가액</th>
                                    <td><?php echo number_format($quote['supply_total']); ?> 원</td>
                                </tr>
                                <tr>
                                    <th>부가세</th>
                                    <td><?php echo number_format($quote['vat_total']); ?> 원</td>
                                </tr>
                                <?php if ($quote['delivery_price'] > 0): ?>
                                <tr>
                                    <th>배송비</th>
                                    <td><?php echo number_format($quote['delivery_price']); ?> 원</td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quote['discount_amount'] > 0): ?>
                                <tr>
                                    <th>할인</th>
                                    <td>-<?php echo number_format($quote['discount_amount']); ?> 원</td>
                                </tr>
                                <?php endif; ?>
                                <tr class="total-row">
                                    <th>합계</th>
                                    <td><?php echo number_format($quote['grand_total']); ?> 원</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 비고 -->
                <?php if ($quote['notes']): ?>
                <div class="section">
                    <div class="section-header">비고</div>
                    <div class="section-body">
                        <div class="notes-box"><?php echo htmlspecialchars($quote['notes']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 고객 응답 -->
                <?php if ($quote['customer_response']): ?>
                <div class="section">
                    <div class="section-header">고객 응답</div>
                    <div class="section-body">
                        <div class="notes-box"><?php echo htmlspecialchars($quote['customer_response']); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 사이드바 -->
            <div class="sidebar">
                <!-- 공개 링크 -->
                <div class="section">
                    <div class="section-header">공개 링크</div>
                    <div class="section-body">
                        <div class="url-box">
                            <input type="text" id="publicUrl" value="<?php echo htmlspecialchars($publicUrl); ?>" readonly>
                            <button onclick="copyUrl()">복사</button>
                        </div>
                    </div>
                </div>

                <!-- 이메일 발송 이력 -->
                <div class="section">
                    <div class="section-header">이메일 발송 이력</div>
                    <div class="section-body" style="padding: <?php echo count($emailLogs) > 0 ? '8px 12px' : '0'; ?>;">
                        <?php if (count($emailLogs) > 0): ?>
                        <div class="email-list">
                            <?php foreach ($emailLogs as $log): ?>
                            <div class="email-item">
                                <span class="email-status"><?php echo $log['status'] === 'sent' ? '✓' : '✗'; ?></span>
                                <span class="email-addr"><?php echo htmlspecialchars($log['recipient_email']); ?></span>
                                <span class="email-date"><?php echo date('Y-m-d H:i', strtotime($log['sent_at'])); ?></span>
                                <?php if ($log['status'] !== 'sent'): ?>
                                <span class="email-error"><?php echo htmlspecialchars($log['error_message']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-text">발송 이력이 없습니다.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyUrl() {
        const input = document.getElementById('publicUrl');
        input.select();
        document.execCommand('copy');
        alert('링크가 복사되었습니다.');
    }

    function sendEmail() {
        const email = '<?php echo addslashes($quote['customer_email'] ?? ''); ?>';
        if (!email) {
            alert('고객 이메일이 등록되어 있지 않습니다.');
            return;
        }

        if (!confirm('견적서를 ' + email + '(으)로 발송하시겠습니까?')) {
            return;
        }

        fetch('api/send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'quote_id=<?php echo $quote['id']; ?>&recipient_email=' + encodeURIComponent(email)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => {
            alert('발송 중 오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
