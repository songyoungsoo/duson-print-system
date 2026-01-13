# 교정 승인 시스템

## 개요

인쇄 전 시안(교정본)을 고객에게 확인받는 시스템.
오탈자, 디자인 오류를 사전에 방지.

## 교정 프로세스

```
[주문완료] → [시안작업] → [교정요청] → [고객확인] → [승인/수정요청] → [인쇄]
```

## 교정 상태 코드

```php
const PROOF_STATUS = [
    'pending'   => '시안대기',     // 아직 시안 미등록
    'uploaded'  => '교정요청',     // 관리자가 시안 업로드
    'approved'  => '승인완료',     // 고객이 승인
    'rejected'  => '수정요청',     // 고객이 수정 요청
    'revised'   => '수정완료',     // 수정본 업로드
];
```

## 관리자: 시안 업로드

### 업로드 폼
```html
<form action="upload_proof.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="order_no" value="<?= $order_no ?>">
    <input type="hidden" name="item_idx" value="<?= $item['idx'] ?>">
    
    <div class="upload-area">
        <input type="file" name="proof_file" accept=".pdf,.jpg,.png" required>
        <p>PDF, JPG, PNG (최대 20MB)</p>
    </div>
    
    <textarea name="admin_comment" placeholder="고객에게 전달할 메모"></textarea>
    
    <button type="submit">교정 요청 보내기</button>
</form>
```

### 업로드 처리
```php
// admin/upload_proof.php
$order_no = $_POST['order_no'];
$item_idx = $_POST['item_idx'];
$admin_comment = $_POST['admin_comment'];

// 파일 업로드
$file = $_FILES['proof_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['pdf', 'jpg', 'jpeg', 'png'];

if (!in_array($ext, $allowed)) {
    die('허용되지 않는 파일 형식입니다.');
}

$new_filename = "proof_{$order_no}_{$item_idx}_" . date('YmdHis') . ".$ext";
$upload_path = "/uploads/proofs/$new_filename";

if (!move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $upload_path)) {
    die('파일 업로드 실패');
}

// proofs 테이블에 저장
$sql = "INSERT INTO proofs (order_no, orderformtree_idx, file_path, file_name, admin_comment, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'uploaded', NOW())";
$pdo->prepare($sql)->execute([$order_no, $item_idx, $upload_path, $file['name'], $admin_comment]);

// orderformtree 상태 업데이트
$sql = "UPDATE orderformtree SET proof_status = 'uploaded', proof_file = ? WHERE idx = ?";
$pdo->prepare($sql)->execute([$upload_path, $item_idx]);

// 고객에게 이메일 발송
sendProofRequestEmail($order_no, $item_idx);
```

## 고객: 교정 확인 페이지

### checkboard.php (교정보기)
```php
// 로그인 확인 또는 주문번호+이메일로 접근
$order_no = $_GET['order_no'];
$token = $_GET['token'];  // 이메일 링크용 토큰

// 토큰 검증
$order = $pdo->prepare("SELECT * FROM orderform WHERE order_no = ? AND proof_token = ?")
             ->execute([$order_no, $token])->fetch();

if (!$order) {
    die('잘못된 접근입니다.');
}

// 교정 대기중인 항목 조회
$sql = "SELECT t.*, p.file_path as proof_file, p.admin_comment, p.idx as proof_idx
        FROM orderformtree t
        LEFT JOIN proofs p ON t.idx = p.orderformtree_idx AND p.status = 'uploaded'
        WHERE t.order_no = ?";
$items = $pdo->prepare($sql)->execute([$order_no])->fetchAll();
```

### 교정 확인 UI
```html
<div class="proof-viewer">
    <h2>교정 확인</h2>
    <p>주문번호: <?= $order_no ?></p>
    
    <?php foreach ($items as $item): ?>
    <div class="proof-item" data-idx="<?= $item['idx'] ?>">
        <h3><?= $item['product_name'] ?></h3>
        
        <?php if ($item['proof_file']): ?>
        <!-- 시안 미리보기 -->
        <div class="proof-preview">
            <?php if (pathinfo($item['proof_file'], PATHINFO_EXTENSION) === 'pdf'): ?>
            <iframe src="<?= $item['proof_file'] ?>" width="100%" height="600"></iframe>
            <?php else: ?>
            <img src="<?= $item['proof_file'] ?>" alt="시안">
            <?php endif; ?>
            
            <a href="<?= $item['proof_file'] ?>" download class="btn-download">다운로드</a>
        </div>
        
        <!-- 관리자 메모 -->
        <?php if ($item['admin_comment']): ?>
        <div class="admin-comment">
            <strong>담당자 메모:</strong>
            <?= nl2br(htmlspecialchars($item['admin_comment'])) ?>
        </div>
        <?php endif; ?>
        
        <!-- 승인/수정요청 버튼 -->
        <div class="proof-actions">
            <button class="btn-approve" onclick="approveProof(<?= $item['proof_idx'] ?>)">
                ✓ 승인 (인쇄 진행)
            </button>
            <button class="btn-reject" onclick="showRejectForm(<?= $item['proof_idx'] ?>)">
                ✗ 수정 요청
            </button>
        </div>
        
        <!-- 수정 요청 폼 (숨김) -->
        <div class="reject-form" id="rejectForm_<?= $item['proof_idx'] ?>" style="display:none;">
            <textarea name="customer_comment" placeholder="수정 요청 사항을 입력해주세요"></textarea>
            <button onclick="rejectProof(<?= $item['proof_idx'] ?>)">수정 요청 보내기</button>
        </div>
        
        <?php else: ?>
        <p class="pending">시안 준비 중입니다.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
```

### JavaScript 처리
```javascript
// 승인
async function approveProof(proofIdx) {
    if (!confirm('승인 후에는 수정이 어렵습니다. 승인하시겠습니까?')) return;
    
    const res = await fetch('/api/proof_action.php', {
        method: 'POST',
        body: JSON.stringify({ action: 'approve', proof_idx: proofIdx })
    });
    
    if (res.ok) {
        alert('승인되었습니다. 인쇄를 진행합니다.');
        location.reload();
    }
}

// 수정 요청
async function rejectProof(proofIdx) {
    const comment = document.querySelector(`#rejectForm_${proofIdx} textarea`).value;
    
    if (!comment.trim()) {
        alert('수정 요청 사항을 입력해주세요.');
        return;
    }
    
    const res = await fetch('/api/proof_action.php', {
        method: 'POST',
        body: JSON.stringify({ 
            action: 'reject', 
            proof_idx: proofIdx,
            customer_comment: comment
        })
    });
    
    if (res.ok) {
        alert('수정 요청이 전달되었습니다.');
        location.reload();
    }
}
```

## 교정 액션 처리 (API)

```php
// api/proof_action.php
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'];
$proof_idx = $data['proof_idx'];

if ($action === 'approve') {
    // 승인 처리
    $sql = "UPDATE proofs SET status = 'approved', reviewed_at = NOW() WHERE idx = ?";
    $pdo->prepare($sql)->execute([$proof_idx]);
    
    // orderformtree 상태 업데이트
    $sql = "UPDATE orderformtree SET proof_status = 'approved' 
            WHERE idx = (SELECT orderformtree_idx FROM proofs WHERE idx = ?)";
    $pdo->prepare($sql)->execute([$proof_idx]);
    
    // 모든 항목 승인 완료 시 주문 상태 변경
    checkAllProofsApproved($proof_idx);
    
} elseif ($action === 'reject') {
    $customer_comment = $data['customer_comment'];
    
    // 수정 요청 처리
    $sql = "UPDATE proofs SET status = 'rejected', customer_comment = ?, reviewed_at = NOW() WHERE idx = ?";
    $pdo->prepare($sql)->execute([$customer_comment, $proof_idx]);
    
    // 관리자에게 알림
    notifyAdminProofRejected($proof_idx, $customer_comment);
}

echo json_encode(['success' => true]);
```

## 이메일 알림

### 교정 요청 이메일
```php
function sendProofRequestEmail($order_no, $item_idx) {
    $order = getOrderInfo($order_no);
    
    // 토큰 생성 (이메일 링크용)
    $token = bin2hex(random_bytes(16));
    $pdo->prepare("UPDATE orderform SET proof_token = ? WHERE order_no = ?")
        ->execute([$token, $order_no]);
    
    $proof_url = "https://dsp1830.shop/sub/checkboard.php?order_no={$order_no}&token={$token}";
    
    $subject = "[두손기획인쇄] 인쇄 시안을 확인해주세요";
    $body = "
    <h2>교정 확인 요청</h2>
    <p>주문번호: {$order_no}</p>
    <p>인쇄 전 시안을 확인하시고 승인 또는 수정 요청을 해주세요.</p>
    <p><a href='{$proof_url}' style='...'>시안 확인하기</a></p>
    <p>※ 승인 후에는 수정이 어려우니 꼼꼼히 확인해주세요.</p>
    ";
    
    sendEmail($order['orderer_email'], $subject, $body);
}
```

## 교정 이력 관리

```php
// 한 항목에 대한 교정 이력 조회
$sql = "SELECT * FROM proofs WHERE orderformtree_idx = ? ORDER BY created_at ASC";
$proofHistory = $pdo->prepare($sql)->execute([$item_idx])->fetchAll();

// 이력 표시
foreach ($proofHistory as $proof) {
    echo "버전 {$proof['idx']}: {$proof['status']} - {$proof['created_at']}";
}
```
