<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$page = max(1, intval($_GET['page'] ?? 1));
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];
$types = '';

if ($status_filter === 'proof') {
    $where .= " AND o.OrderStyle IN ('7', '10')";
} elseif ($status_filter === 'design') {
    $where .= " AND o.OrderStyle IN ('5', '6')";
} elseif ($status_filter === 'complete') {
    $where .= " AND o.OrderStyle = '8'";
}

if ($search !== '') {
    $where .= " AND (o.no LIKE ? OR o.name LIKE ? OR o.Type LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$count_query = "SELECT COUNT(*) as cnt FROM mlangorder_printauto o WHERE {$where}";
if (!empty($params)) {
    $stmt = mysqli_prepare($db, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt'];
} else {
    $total = mysqli_fetch_assoc(mysqli_query($db, $count_query))['cnt'];
}
$total_pages = max(1, ceil($total / $per_page));

$query = "SELECT o.no, o.Type, o.name, o.OrderStyle, o.date, o.uploaded_files,
          (SELECT COUNT(*) FROM mlangorder_printauto AS sub WHERE sub.no = o.no AND sub.uploaded_files IS NOT NULL AND sub.uploaded_files != '') as has_files
          FROM mlangorder_printauto o
          WHERE {$where}
          ORDER BY o.no DESC LIMIT {$per_page} OFFSET {$offset}";

if (!empty($params)) {
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($db, $query);
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['files'] = [];
    $upload_dir = realpath(__DIR__ . '/../../mlangorder_printauto/upload/' . $row['no']);
    if ($upload_dir && is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        $row['files'] = array_values($files);
    }
    $orders[] = $row;
}

$status_labels = [
    '0' => '미선택', '1' => '견적접수', '2' => '주문접수', '3' => '접수완료',
    '4' => '입금대기', '5' => '시안제작중', '6' => '시안', '7' => '교정',
    '8' => '작업완료', '9' => '작업중', '10' => '교정작업중'
];

$status_colors = [
    '7' => 'bg-blue-100 text-blue-700', '10' => 'bg-blue-100 text-blue-700',
    '5' => 'bg-yellow-100 text-yellow-700', '6' => 'bg-yellow-100 text-yellow-700',
    '8' => 'bg-green-100 text-green-700', '9' => 'bg-purple-100 text-purple-700',
];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🔍 교정 관리</h1>
                <p class="text-sm text-gray-600">교정보기 · 교정파일 올리기 · 진행 상태 확인</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-3 mb-4">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <div class="flex gap-1">
                    <a href="?<?php echo $search ? 'q='.urlencode($search) : ''; ?>" 
                       class="px-3 py-1.5 text-xs rounded-full <?php echo !$status_filter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">전체</a>
                    <a href="?status=proof<?php echo $search ? '&q='.urlencode($search) : ''; ?>" 
                       class="px-3 py-1.5 text-xs rounded-full <?php echo $status_filter === 'proof' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">교정대기</a>
                    <a href="?status=design<?php echo $search ? '&q='.urlencode($search) : ''; ?>" 
                       class="px-3 py-1.5 text-xs rounded-full <?php echo $status_filter === 'design' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">시안진행</a>
                    <a href="?status=complete<?php echo $search ? '&q='.urlencode($search) : ''; ?>" 
                       class="px-3 py-1.5 text-xs rounded-full <?php echo $status_filter === 'complete' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">작업완료</a>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="주문번호, 이름, 품목 검색..."
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo $status_filter; ?>"><?php endif; ?>
                <button type="submit" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">검색</button>
            </form>
        </div>

        <!-- Order/Proof List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">주문번호</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">품목</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">주문자</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">상태</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">교정파일</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">일시</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500">작업</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-400">데이터가 없습니다.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50" id="row-<?php echo $order['no']; ?>">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">#<?php echo $order['no']; ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($order['Type']); ?></td>
                            <td class="px-3 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($order['name']); ?></td>
                            <td class="px-3 py-2 text-center">
                                <?php 
                                    $style = $order['OrderStyle'] ?? '0';
                                    $color = $status_colors[$style] ?? 'bg-gray-100 text-gray-700';
                                    $label = $status_labels[$style] ?? '미정';
                                ?>
                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full <?php echo $color; ?>"><?php echo $label; ?></span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <?php if (!empty($order['files'])): ?>
                                    <span class="text-blue-600 text-xs font-medium"><?php echo count($order['files']); ?>개 파일</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">없음</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-400 text-center"><?php echo date('m/d H:i', strtotime($order['date'])); ?></td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if (!empty($order['files'])): ?>
                                    <button onclick="viewFiles(<?php echo $order['no']; ?>)" class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100" title="교정파일 보기">🔍보기</button>
                                    <?php endif; ?>
                                    <button onclick="openUpload(<?php echo $order['no']; ?>)" class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100" title="파일 올리기">📤올리기</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="px-3 py-2 border-t border-gray-200 flex items-center justify-between text-sm">
                <span class="text-gray-600">총 <?php echo number_format($total); ?>건 (<?php echo $page; ?>/<?php echo $total_pages; ?>)</span>
                <div class="flex gap-1">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&q=<?php echo urlencode($search); ?>" class="px-3 py-1 border rounded hover:bg-gray-50">이전</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&q=<?php echo urlencode($search); ?>" class="px-3 py-1 border rounded hover:bg-gray-50">다음</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- File Viewer Modal -->
<div id="fileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 id="fileModalTitle" class="font-semibold text-gray-900">교정파일</h3>
            <button onclick="closeFileModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div id="fileModalContent" class="p-4"></div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 id="uploadModalTitle" class="font-semibold text-gray-900">교정파일 올리기</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form id="uploadForm" enctype="multipart/form-data" class="p-4">
            <input type="hidden" id="upload_order_no" name="order_no">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors" id="dropZone">
                <div class="text-3xl mb-2">📁</div>
                <p class="text-sm text-gray-600 mb-2">파일을 끌어다 놓거나 클릭하여 선택</p>
                <input type="file" id="fileInput" name="files[]" multiple class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.ai,.psd,.zip">
                <button type="button" onclick="document.getElementById('fileInput').click()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">파일 선택</button>
                <p class="text-xs text-gray-400 mt-2">JPG, PNG, PDF, AI, PSD, ZIP (최대 20MB)</p>
            </div>
            <div id="fileList" class="mt-3 space-y-1"></div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">취소</button>
                <button type="submit" id="uploadBtn" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>업로드</button>
            </div>
        </form>
    </div>
</div>

<script>
function viewFiles(orderNo) {
    const modal = document.getElementById('fileModal');
    const content = document.getElementById('fileModalContent');
    document.getElementById('fileModalTitle').textContent = '#' + orderNo + ' 교정파일';
    content.innerHTML = '<div class="text-center py-4 text-gray-400">로딩 중...</div>';
    modal.classList.remove('hidden');

    fetch('/dashboard/proofs/api.php?action=files&order_no=' + orderNo)
        .then(r => r.json())
        .then(data => {
            if (!data.files || data.files.length === 0) {
                content.innerHTML = '<p class="text-center text-gray-400 py-4">파일이 없습니다.</p>';
                return;
            }
            let html = '<div class="grid grid-cols-2 gap-3">';
            data.files.forEach(f => {
                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(f.name);
                if (isImage) {
                    html += '<a href="' + f.url + '" target="_blank" class="block border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">' +
                        '<img src="' + f.url + '" alt="' + f.name + '" class="w-full h-32 object-cover">' +
                        '<div class="p-2 text-xs text-gray-600 truncate">' + f.name + '</div></a>';
                } else {
                    html += '<a href="' + f.url + '" target="_blank" class="flex items-center p-3 border rounded-lg hover:bg-gray-50">' +
                        '<span class="text-2xl mr-2">📄</span><div class="text-xs text-gray-700 truncate">' + f.name + '</div></a>';
                }
            });
            html += '</div>';
            content.innerHTML = html;
        })
        .catch(() => { content.innerHTML = '<p class="text-center text-red-500 py-4">로딩 실패</p>'; });
}

function closeFileModal() { document.getElementById('fileModal').classList.add('hidden'); }

function openUpload(orderNo) {
    document.getElementById('upload_order_no').value = orderNo;
    document.getElementById('uploadModalTitle').textContent = '#' + orderNo + ' 교정파일 올리기';
    document.getElementById('fileList').innerHTML = '';
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() { document.getElementById('uploadModal').classList.add('hidden'); }

document.getElementById('fileInput').addEventListener('change', function() {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    Array.from(this.files).forEach(f => {
        const size = (f.size / 1024 / 1024).toFixed(1);
        fileList.innerHTML += '<div class="flex items-center justify-between p-2 bg-gray-50 rounded text-xs"><span class="truncate">' + f.name + '</span><span class="text-gray-400 ml-2">' + size + 'MB</span></div>';
    });
    document.getElementById('uploadBtn').disabled = this.files.length === 0;
});

const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-400', 'bg-blue-50'); });
dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-blue-400', 'bg-blue-50'); });
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    document.getElementById('fileInput').files = e.dataTransfer.files;
    document.getElementById('fileInput').dispatchEvent(new Event('change'));
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.textContent = '업로드 중...';

    fetch('/dashboard/proofs/api.php?action=upload', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('파일이 업로드되었습니다.', 'success');
                closeUploadModal();
                location.reload();
            } else {
                showToast(data.message || '업로드 실패', 'error');
            }
        })
        .catch(() => showToast('업로드 실패', 'error'))
        .finally(() => { btn.disabled = false; btn.textContent = '업로드'; });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
