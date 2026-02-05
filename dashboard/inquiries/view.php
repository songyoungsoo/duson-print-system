<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /dashboard/inquiries/');
    exit;
}

$query = "SELECT * FROM customer_inquiries WHERE inquiry_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inquiry = mysqli_fetch_assoc($result);

if (!$inquiry) {
    header('Location: /dashboard/inquiries/');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">문의 상세 #<?php echo $inquiry['inquiry_id']; ?></h1>
                <p class="mt-2 text-sm text-gray-600">고객 문의 내용 및 답변</p>
            </div>
            <a href="/dashboard/inquiries/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                목록으로
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">문의 내용</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">제목</dt>
                            <dd class="mt-1 text-base text-gray-900"><?php echo htmlspecialchars($inquiry['inquiry_subject']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">내용</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($inquiry['inquiry_message']); ?></dd>
                        </div>
                    </dl>
                </div>

                <?php if (!empty($inquiry['admin_reply'])): ?>
                <div class="bg-blue-50 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">관리자 답변</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($inquiry['admin_reply']); ?></p>
                    <p class="mt-4 text-xs text-gray-500">답변일: <?php echo $inquiry['admin_reply_at']; ?></p>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">답변 작성</h3>
                    <form id="replyForm">
                        <input type="hidden" name="id" value="<?php echo $inquiry['inquiry_id']; ?>">
                        <textarea name="reply" rows="6" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="답변 내용을 입력하세요..." required></textarea>
                        <button type="submit" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            답변 등록
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">작성자 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">이름</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($inquiry['inquiry_name']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">이메일</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($inquiry['inquiry_email']); ?></dd>
                        </div>
                        <?php if (!empty($inquiry['inquiry_phone'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">전화번호</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($inquiry['inquiry_phone']); ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">문의 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">분류</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($inquiry['inquiry_category']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">상태</dt>
                            <dd class="mt-1">
                                <?php if ($inquiry['status'] === 'pending'): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">미답변</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">답변완료</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">작성일</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo $inquiry['created_at']; ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
<?php if (empty($inquiry['admin_reply'])): ?>
document.getElementById('replyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'reply');
    
    try {
        const response = await fetch('/dashboard/api/inquiries.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('답변이 등록되었습니다.');
            location.reload();
        } else {
            alert('답변 등록 실패: ' + result.message);
        }
    } catch (error) {
        alert('답변 등록 중 오류가 발생했습니다.');
    }
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
