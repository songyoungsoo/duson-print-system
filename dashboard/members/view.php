<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: /dashboard/members/');
    exit;
}

$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

if (!$member) {
    header('Location: /dashboard/members/');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">회원 상세 #<?php echo $member['id']; ?></h1>
                <p class="mt-2 text-sm text-gray-600">회원 정보 조회 및 수정</p>
            </div>
            <a href="/dashboard/members/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                목록으로
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">기본 정보</h3>
                <form id="memberForm">
                    <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">아이디</label>
                            <input type="text" value="<?php echo htmlspecialchars($member['username']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" disabled>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">이름</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            정보 수정
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">주소 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">우편번호</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['postcode'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">주소</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['address'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">상세주소</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['detail_address'] ?: '-'); ?></dd>
                        </div>
                    </dl>
                </div>

                <?php if (!empty($member['business_number'])): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">사업자 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">사업자등록번호</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['business_number']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">상호</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['business_name'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">대표자</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($member['business_owner'] ?: '-'); ?></dd>
                        </div>
                    </dl>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">가입 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">가입일</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo $member['created_at'] ?: '-'; ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('memberForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update');
    
    try {
        const response = await fetch('/dashboard/api/members.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('회원 정보가 수정되었습니다.');
            location.reload();
        } else {
            alert('수정 실패: ' + result.message);
        }
    } catch (error) {
        alert('수정 중 오류가 발생했습니다.');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
