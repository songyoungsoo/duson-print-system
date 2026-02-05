<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$no = intval($_GET['no'] ?? 0);

if ($no <= 0) {
    header('Location: /dashboard/orders/');
    exit;
}

$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: /dashboard/orders/');
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">주문 상세 #<?php echo $order['no']; ?></h1>
                <p class="mt-2 text-sm text-gray-600">주문 정보 및 상태 관리</p>
            </div>
            <a href="/dashboard/orders/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                목록으로
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">주문 정보</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">주문번호</dt>
                            <dd class="mt-1 text-sm text-gray-900">#<?php echo $order['no']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">주문일시</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo $order['date']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">품목</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['Type']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">수량</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['mesu']); ?></dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">금액</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo number_format($order['money_5']); ?>원</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">고객 정보</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">이름</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['name']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">이메일</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">전화번호</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['phone']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">휴대폰</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['Hendphone']); ?></dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">배송지</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                (<?php echo htmlspecialchars($order['zip']); ?>) 
                                <?php echo htmlspecialchars($order['zip1']); ?> 
                                <?php echo htmlspecialchars($order['zip2']); ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <?php if (!empty($order['cont'])): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">요청사항</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($order['cont']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">상태 관리</h3>
                    <form id="statusForm">
                        <input type="hidden" name="no" value="<?php echo $order['no']; ?>">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">주문 상태</label>
                            <select name="order_style" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="1" <?php echo $order['OrderStyle'] == '1' ? 'selected' : ''; ?>>접수</option>
                                <option value="2" <?php echo $order['OrderStyle'] == '2' ? 'selected' : ''; ?>>진행중</option>
                                <option value="3" <?php echo $order['OrderStyle'] == '3' ? 'selected' : ''; ?>>완료</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            상태 변경
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">결제 정보</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">결제방법</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['bank'] ?: '미입금'); ?></dd>
                        </div>
                        <?php if (!empty($order['bankname'])): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">입금자명</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($order['bankname']); ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update');
    
    try {
        const response = await fetch('/dashboard/api/orders.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('상태가 변경되었습니다.');
            location.reload();
        } else {
            alert('상태 변경 실패: ' + result.message);
        }
    } catch (error) {
        alert('상태 변경 중 오류가 발생했습니다.');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
