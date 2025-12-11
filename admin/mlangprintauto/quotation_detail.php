<?php
/**
 * 견적서 상세 조회 API
 * AJAX로 호출되어 HTML 반환
 */

include "../../db.php";
include "../../includes/auth.php";

if (!isset($_SESSION['user_id'])) {
    echo '<p>권한이 없습니다.</p>';
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo '<p>잘못된 요청입니다.</p>';
    exit;
}

mysqli_set_charset($db, 'utf8mb4');

// 견적서 조회
$stmt = mysqli_prepare($db, "SELECT * FROM quotations WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$quotation) {
    echo '<p>견적서를 찾을 수 없습니다.</p>';
    exit;
}

// 이메일 발송 내역 조회
$email_stmt = mysqli_prepare($db, "SELECT * FROM quotation_emails WHERE quotation_id = ? ORDER BY sent_at DESC");
mysqli_stmt_bind_param($email_stmt, "i", $id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$emails = [];
while ($row = mysqli_fetch_assoc($email_result)) {
    $emails[] = $row;
}
mysqli_stmt_close($email_stmt);

// JSON 데이터 파싱
$cart_items = json_decode($quotation['cart_items_json'], true) ?? [];
$custom_items = json_decode($quotation['custom_items_json'], true) ?? [];

$statusLabels = [
    'draft' => '임시저장',
    'sent' => '발송완료',
    'accepted' => '수락',
    'rejected' => '거절',
    'expired' => '만료'
];
?>

<div class="detail-section">
    <h3>기본 정보</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">견적번호:</span>
            <span class="value"><?php echo htmlspecialchars($quotation['quotation_no']); ?></span>
        </div>
        <div class="detail-item">
            <span class="label">상태:</span>
            <span class="value"><?php echo $statusLabels[$quotation['status']] ?? $quotation['status']; ?></span>
        </div>
        <div class="detail-item">
            <span class="label">담당자:</span>
            <span class="value"><?php echo htmlspecialchars($quotation['customer_name']); ?></span>
        </div>
        <div class="detail-item">
            <span class="label">이메일:</span>
            <span class="value"><?php echo htmlspecialchars($quotation['customer_email'] ?? '-'); ?></span>
        </div>
        <div class="detail-item">
            <span class="label">작성일:</span>
            <span class="value"><?php echo date('Y-m-d H:i:s', strtotime($quotation['created_at'])); ?></span>
        </div>
        <div class="detail-item">
            <span class="label">유효기간:</span>
            <span class="value"><?php echo $quotation['expires_at'] ? date('Y-m-d', strtotime($quotation['expires_at'])) : '-'; ?></span>
        </div>
    </div>
</div>

<div class="detail-section">
    <h3>금액 정보</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">공급가액:</span>
            <span class="value"><?php echo number_format($quotation['total_supply']); ?>원</span>
        </div>
        <div class="detail-item">
            <span class="label">부가세:</span>
            <span class="value"><?php echo number_format($quotation['total_vat']); ?>원</span>
        </div>
        <div class="detail-item">
            <span class="label">합계금액:</span>
            <span class="value" style="color: #c00; font-size: 18px;"><?php echo number_format($quotation['total_price']); ?>원</span>
        </div>
    </div>
</div>

<?php if (!empty($cart_items)): ?>
<div class="detail-section">
    <h3>장바구니 상품 (<?php echo count($cart_items); ?>건)</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>품목</th>
                <th>규격</th>
                <th>수량</th>
                <th>금액</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $idx => $item): ?>
            <tr>
                <td><?php echo $idx + 1; ?></td>
                <td><?php echo htmlspecialchars($item['product_type'] ?? '상품'); ?></td>
                <td><?php echo htmlspecialchars(($item['MY_type'] ?? '') . ' / ' . ($item['Section'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($item['MY_amount'] ?? 1); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['st_price'] ?? 0); ?>원</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($quotation['delivery_type']) && $quotation['delivery_price'] > 0): ?>
<div class="detail-section">
    <h3>배송 정보</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="label">배송방식:</span>
            <span class="value"><?php echo htmlspecialchars($quotation['delivery_type']); ?></span>
        </div>
        <div class="detail-item">
            <span class="label">배송비:</span>
            <span class="value"><?php echo number_format($quotation['delivery_price']); ?>원</span>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($custom_items)): ?>
<div class="detail-section">
    <h3>추가 항목</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>품목</th>
                <th>규격</th>
                <th>수량</th>
                <th>단위</th>
                <th>단가</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($custom_items as $item): ?>
            <?php if (!empty($item['item'])): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item']); ?></td>
                <td><?php echo htmlspecialchars($item['spec'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($item['qty'] ?? 1); ?></td>
                <td><?php echo htmlspecialchars($item['unit'] ?? '개'); ?></td>
                <td style="text-align: right;"><?php echo number_format($item['price'] ?? 0); ?>원</td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($emails)): ?>
<div class="detail-section">
    <h3>이메일 발송 내역 (<?php echo count($emails); ?>건)</h3>
    <div class="email-history">
        <?php foreach ($emails as $email): ?>
        <div class="email-item">
            <div><strong><?php echo htmlspecialchars($email['recipient_email']); ?></strong> (<?php echo htmlspecialchars($email['recipient_name'] ?? ''); ?>)</div>
            <div style="font-size: 12px; color: #666;">
                <?php echo date('Y-m-d H:i:s', strtotime($email['sent_at'])); ?> -
                <?php if ($email['status'] === 'sent'): ?>
                <span style="color: #27ae60;">발송 성공</span>
                <?php else: ?>
                <span style="color: #e74c3c;">발송 실패: <?php echo htmlspecialchars($email['error_message'] ?? ''); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="detail-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
    <div style="display: flex; gap: 10px;">
        <select id="statusSelect" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="draft" <?php echo $quotation['status'] === 'draft' ? 'selected' : ''; ?>>임시저장</option>
            <option value="sent" <?php echo $quotation['status'] === 'sent' ? 'selected' : ''; ?>>발송완료</option>
            <option value="accepted" <?php echo $quotation['status'] === 'accepted' ? 'selected' : ''; ?>>수락</option>
            <option value="rejected" <?php echo $quotation['status'] === 'rejected' ? 'selected' : ''; ?>>거절</option>
            <option value="expired" <?php echo $quotation['status'] === 'expired' ? 'selected' : ''; ?>>만료</option>
        </select>
        <button onclick="updateStatus(<?php echo $id; ?>)" style="padding: 8px 16px; background: #27ae60; color: #fff; border: none; border-radius: 4px; cursor: pointer;">상태 변경</button>
    </div>
</div>

<script>
function updateStatus(id) {
    const status = document.getElementById('statusSelect').value;

    fetch('quotation_update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: status })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('상태가 변경되었습니다.');
            location.reload();
        } else {
            alert('변경 실패: ' + result.message);
        }
    });
}
</script>
