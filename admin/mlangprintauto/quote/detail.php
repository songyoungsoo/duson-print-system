<?php
/**
 * 관리자 견적서 상세 - Excel Style
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: /admin/mlangprintauto/login.php");
    exit;
}

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/includes/AdminQuoteManager.php';

if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

$quoteManager = new AdminQuoteManager($db);
$quoteId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($quoteId <= 0) { header("Location: index.php"); exit; }

$quote = $quoteManager->getQuote($quoteId);
if (!$quote) { header("Location: index.php?error=not_found"); exit; }

$items = $quoteManager->getQuoteItems($quoteId);

$statusText = ['draft'=>'임시저장','sent'=>'발송','viewed'=>'열람','accepted'=>'승인','rejected'=>'거절','expired'=>'만료','converted'=>'주문전환'];
$statusClass = ['draft'=>'status-draft','sent'=>'status-sent','viewed'=>'status-viewed','accepted'=>'status-accepted','rejected'=>'status-rejected','expired'=>'status-expired','converted'=>'status-converted'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 상세 - <?php echo htmlspecialchars($quote['quote_no']); ?></title>
    <link rel="stylesheet" href="assets/excel-style.css">
</head>
<body>
<div class="container">
    <div class="page-header">
        <div>
            <h1>견적서 상세 <span class="quote-no"><?php echo htmlspecialchars($quote['quote_no']); ?></span>
            <span class="status-badge <?php echo $statusClass[$quote['status']] ?? ''; ?>" style="margin-left:10px;">
                <?php echo $statusText[$quote['status']] ?? $quote['status']; ?>
            </span></h1>
        </div>
        <div class="action-bar">
            <a href="index.php" class="back-link">← 목록</a>
            <a href="edit.php?id=<?php echo $quoteId; ?>" class="btn btn-primary btn-sm">수정</a>
            <a href="api/generate_pdf.php?id=<?php echo $quoteId; ?>" class="btn btn-sm" target="_blank">PDF</a>
            <button onclick="sendEmail()" class="btn btn-primary btn-sm"><?php echo $quote['status']==='draft'?'발송':'재발송'; ?></button>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
        <div class="card">
            <div class="card-header">견적 정보</div>
            <div class="card-body" style="padding:0;">
                <div class="info-grid" style="gap:0;">
                    <div class="info-row"><div class="info-label">견적번호</div><div class="info-value text-primary fw-bold"><?php echo htmlspecialchars($quote['quote_no']); ?></div></div>
                    <div class="info-row"><div class="info-label">작성일</div><div class="info-value"><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></div></div>
                    <div class="info-row"><div class="info-label">유효기간</div><div class="info-value"><?php echo $quote['valid_until'] ? date('Y-m-d', strtotime($quote['valid_until'])) : '-'; ?></div></div>
                    <div class="info-row"><div class="info-label">작성자</div><div class="info-value"><?php echo htmlspecialchars($quote['created_by'] ?: '관리자'); ?></div></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">고객 정보</div>
            <div class="card-body" style="padding:0;">
                <div class="info-grid" style="gap:0;">
                    <?php if (!empty($quote['customer_company'])): ?>
                    <div class="info-row"><div class="info-label">회사명</div><div class="info-value"><?php echo htmlspecialchars($quote['customer_company']); ?></div></div>
                    <?php endif; ?>
                    <div class="info-row"><div class="info-label">담당자</div><div class="info-value"><?php echo htmlspecialchars($quote['customer_name']); ?></div></div>
                    <?php if (!empty($quote['customer_phone'])): ?>
                    <div class="info-row"><div class="info-label">연락처</div><div class="info-value"><?php echo htmlspecialchars($quote['customer_phone']); ?></div></div>
                    <?php endif; ?>
                    <?php if (!empty($quote['customer_email'])): ?>
                    <div class="info-row"><div class="info-label">이메일</div><div class="info-value"><?php echo htmlspecialchars($quote['customer_email']); ?></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">견적 품목 (<?php echo count($items); ?>개)</div>
        <table class="excel-table">
            <thead>
                <tr>
                    <th style="width:40px">NO</th>
                    <th style="width:100px">품목</th>
                    <th>규격 및 사양</th>
                    <th style="width:80px">수량</th>
                    <th style="width:80px">단가</th>
                    <th style="width:100px">공급가액</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="6" class="text-center" style="padding:30px;color:#888;">품목 없음</td></tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?php echo $item['item_no']; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item['specification'])); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['quantity_display'] ?: number_format($item['quantity']).$item['unit']); ?></td>
                    <td class="text-right"><?php echo number_format($item['unit_price']); ?></td>
                    <td class="text-right"><?php echo number_format($item['supply_price']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="totals-section" style="padding:10px;">
            <div class="totals-row"><span class="totals-label">공급가액</span><span class="totals-value"><?php echo number_format($quote['supply_total']); ?></span></div>
            <div class="totals-row"><span class="totals-label">부가세</span><span class="totals-value"><?php echo number_format($quote['vat_total']); ?></span></div>
            <div class="totals-row grand"><span class="totals-label">총액</span><span class="totals-value"><?php echo number_format($quote['grand_total']); ?></span></div>
        </div>
    </div>

    <?php if (!empty($quote['customer_memo']) || !empty($quote['admin_memo'])): ?>
    <div class="card">
        <div class="card-header">메모</div>
        <div class="card-body">
            <?php if (!empty($quote['customer_memo'])): ?>
            <div class="form-label">고객 요청사항</div>
            <div style="background:#f9f9f9;border:1px solid #d0d0d0;padding:8px;margin-bottom:10px;white-space:pre-wrap;"><?php echo htmlspecialchars($quote['customer_memo']); ?></div>
            <?php endif; ?>
            <?php if (!empty($quote['admin_memo'])): ?>
            <div class="form-label">관리자 메모</div>
            <div style="background:#f9f9f9;border:1px solid #d0d0d0;padding:8px;white-space:pre-wrap;"><?php echo htmlspecialchars($quote['admin_memo']); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function sendEmail() {
    <?php if (empty($quote['customer_email'])): ?>
    alert('고객 이메일이 없습니다.');
    <?php else: ?>
    if(confirm('<?php echo addslashes($quote['customer_email']); ?>로 발송하시겠습니까?')) {
        fetch('api/send_email.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({quote_id:<?php echo $quoteId; ?>, recipient_email:'<?php echo addslashes($quote['customer_email']); ?>'})})
        .then(r=>r.json()).then(d=>{ if(d.success){alert('발송됨');location.reload();}else alert('실패: '+d.message); })
        .catch(e=>alert('오류: '+e.message));
    }
    <?php endif; ?>
}
</script>
</body>
</html>
