<?php
declare(strict_types=1);

// 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
$inquiry_id = intval($_GET['inquiry_id'] ?? 0);

include "top.php";

// 데이터베이스 연결
$admin_dir = dirname(__FILE__);
include $admin_dir . "/../db.php";

// 답변 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $admin_reply = $_POST['admin_reply'] ?? '';
    $reply_status = $_POST['reply_status'] ?? '답변완료';

    if (!empty($admin_reply) && $inquiry_id > 0) {
        $update_query = "UPDATE customer_inquiries SET
                        admin_reply = ?,
                        admin_reply_at = NOW(),
                        status = ?,
                        updated_at = NOW()
                        WHERE inquiry_id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $admin_reply, $reply_status, $inquiry_id);
            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('답변이 등록되었습니다.'); location.href='customer_inquiries.php';</script>";
                exit;
            } else {
                $error_message = "답변 등록에 실패했습니다.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// 문의 상세 조회
$inquiry = null;
if ($inquiry_id > 0) {
    $query = "SELECT
        inquiry_id,
        user_id,
        inquiry_name as name,
        inquiry_email as email,
        inquiry_phone as phone,
        inquiry_category as inquiry_type,
        inquiry_subject as title,
        inquiry_message as content,
        status,
        admin_reply,
        admin_reply_at as reply_date,
        created_at
    FROM customer_inquiries
    WHERE inquiry_id = ?";
    $stmt = mysqli_prepare($db, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $inquiry_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $inquiry = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 상태를 처리중으로 변경 (최초 열람시)
        if ($inquiry && ($inquiry['status'] === '대기중' || empty($inquiry['status']))) {
            $update_status = "UPDATE customer_inquiries SET status = '처리중', updated_at = NOW()
                            WHERE inquiry_id = ? AND (status = '대기중' OR status IS NULL OR status = '')";
            $stmt2 = mysqli_prepare($db, $update_status);
            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "i", $inquiry_id);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                $inquiry['status'] = '처리중';
            }
        }
    }
}

if (!$inquiry) {
    echo "<script>alert('문의를 찾을 수 없습니다.'); location.href='customer_inquiries.php';</script>";
    exit;
}
?>

<style>
.inquiry-detail {
    max-width: 900px;
    margin: 20px auto;
}
.detail-header {
    background: #4A90E2;
    color: white;
    padding: 15px;
    border-radius: 5px 5px 0 0;
}
.detail-content {
    background: white;
    border: 1px solid #ddd;
    padding: 20px;
}
.info-row {
    display: flex;
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}
.info-label {
    width: 120px;
    font-weight: bold;
    color: #666;
}
.info-value {
    flex: 1;
}
.content-box {
    background: #f9f9f9;
    padding: 20px;
    margin: 20px 0;
    border-left: 3px solid #4A90E2;
    min-height: 150px;
}
.reply-form {
    margin-top: 30px;
    padding: 20px;
    background: #f0f7ff;
    border-radius: 5px;
}
.reply-form textarea {
    width: 100%;
    min-height: 200px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 11pt;
    box-sizing: border-box;
}
.button-group {
    margin-top: 20px;
    text-align: center;
}
.btn-primary {
    background: #4A90E2;
    color: white;
    padding: 10px 30px;
    border: none;
    border-radius: 3px;
    font-size: 11pt;
    cursor: pointer;
    margin: 0 5px;
}
.btn-secondary {
    background: #666;
    color: white;
    padding: 10px 30px;
    border: none;
    border-radius: 3px;
    font-size: 11pt;
    cursor: pointer;
    margin: 0 5px;
    text-decoration: none;
    display: inline-block;
}
.status-badge {
    padding: 3px 10px;
    border-radius: 3px;
    font-weight: bold;
    font-size: 9pt;
}
.status-pending { background: #FF6B00; color: white; }
.status-processing { background: #2196F3; color: white; }
.status-completed { background: #4CAF50; color: white; }
.existing-reply {
    background: #e8f5e9;
    padding: 20px;
    margin: 20px 0;
    border-left: 3px solid #4CAF50;
}
</style>

<div class="inquiry-detail">
    <div class="detail-header">
        <h2 style="margin: 0;">💬 문의 상세보기</h2>
    </div>

    <div class="detail-content">
        <div class="info-row">
            <div class="info-label">문의번호</div>
            <div class="info-value">#<?php echo str_pad((string)$inquiry['inquiry_id'], 6, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">문의유형</div>
            <div class="info-value"><?php echo htmlspecialchars($inquiry['inquiry_type']); ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">제목</div>
            <div class="info-value"><strong><?php echo htmlspecialchars($inquiry['title']); ?></strong></div>
        </div>

        <div class="info-row">
            <div class="info-label">작성자</div>
            <div class="info-value"><?php echo htmlspecialchars($inquiry['name']); ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">이메일</div>
            <div class="info-value">
                <?php echo htmlspecialchars($inquiry['email']); ?>
                <?php if (!empty($inquiry['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>"
                       style="margin-left: 10px; color: #4A90E2;">[메일 보내기]</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-row">
            <div class="info-label">연락처</div>
            <div class="info-value">
                <?php echo htmlspecialchars($inquiry['phone']); ?>
                <?php if (!empty($inquiry['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>"
                       style="margin-left: 10px; color: #4A90E2;">[전화 걸기]</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-row">
            <div class="info-label">작성일시</div>
            <div class="info-value"><?php echo date('Y년 m월 d일 H:i', strtotime($inquiry['created_at'])); ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">상태</div>
            <div class="info-value">
                <?php
                $status_class = 'status-pending';
                if ($inquiry['status'] === '답변완료') {
                    $status_class = 'status-completed';
                } elseif ($inquiry['status'] === '처리중') {
                    $status_class = 'status-processing';
                }
                ?>
                <span class="status-badge <?php echo $status_class; ?>"><?php echo $inquiry['status']; ?></span>
                <?php if (!empty($inquiry['reply_date'])): ?>
                    <span style="margin-left: 10px; color: #666;">
                        (답변일: <?php echo date('Y-m-d H:i', strtotime($inquiry['reply_date'])); ?>)
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin: 30px 0;">
            <h3>📝 문의내용</h3>
            <div class="content-box">
                <?php echo nl2br(htmlspecialchars($inquiry['content'])); ?>
            </div>
        </div>

        <?php if (!empty($inquiry['admin_reply'])): ?>
        <div style="margin: 30px 0;">
            <h3>✅ 답변내용</h3>
            <div class="existing-reply">
                <?php echo nl2br(htmlspecialchars($inquiry['admin_reply'])); ?>
                <div style="margin-top: 10px; color: #666; font-size: 9pt;">
                    답변일시: <?php echo date('Y-m-d H:i', strtotime($inquiry['reply_date'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="reply-form">
            <h3>📮 <?php echo !empty($inquiry['admin_reply']) ? '답변 수정' : '답변 작성'; ?></h3>
            <form method="post" action="">
                <div style="margin-bottom: 15px;">
                    <label for="reply_status" style="margin-right: 10px;">상태 변경:</label>
                    <select name="reply_status" id="reply_status" style="padding: 5px;">
                        <option value="처리중" <?php echo ($inquiry['status'] === '처리중') ? 'selected' : ''; ?>>처리중</option>
                        <option value="답변완료" <?php echo ($inquiry['status'] === '답변완료') ? 'selected' : ''; ?>>답변완료</option>
                    </select>
                </div>
                <textarea name="admin_reply" placeholder="답변 내용을 입력하세요..." required><?php echo htmlspecialchars($inquiry['admin_reply'] ?? ''); ?></textarea>

                <div class="button-group">
                    <button type="submit" name="submit_reply" class="btn-primary">
                        <?php echo !empty($inquiry['admin_reply']) ? '답변 수정' : '답변 등록'; ?>
                    </button>
                    <a href="customer_inquiries.php" class="btn-secondary">목록으로</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "down.php"; ?>