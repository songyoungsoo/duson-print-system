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

$query = "SELECT o.no, o.Type, o.name, o.phone, o.Hendphone, o.OrderStyle, o.date, o.uploaded_files,
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
    $row['latest_date'] = '';
    $upload_dir = realpath(__DIR__ . '/../../mlangorder_printauto/upload/' . $row['no']);
    if ($upload_dir && is_dir($upload_dir)) {
        $files = array_diff(scandir($upload_dir), ['.', '..']);
        $row['files'] = array_values($files);
        // 최신 파일 날짜 구하기
        $latest_mtime = 0;
        foreach ($files as $f) {
            $mt = filemtime($upload_dir . '/' . $f);
            if ($mt > $latest_mtime) $latest_mtime = $mt;
        }
        if ($latest_mtime > 0) {
            $row['latest_date'] = date('m/d', $latest_mtime);
        }
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

<main class="flex-1 bg-cyan-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- 헤더 + 필터 한 줄 -->
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-2">
            <h1 class="text-lg font-bold text-gray-900 mr-2">교정 관리</h1>
            <div class="flex gap-1">
                <a href="?<?php echo $search ? 'q='.urlencode($search) : ''; ?>"
                   class="px-2 py-0.5 text-xs rounded-full <?php echo !$status_filter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">전체</a>
                <a href="?status=proof<?php echo $search ? '&q='.urlencode($search) : ''; ?>"
                   class="px-2 py-0.5 text-xs rounded-full <?php echo $status_filter === 'proof' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">교정대기</a>
                <a href="?status=design<?php echo $search ? '&q='.urlencode($search) : ''; ?>"
                   class="px-2 py-0.5 text-xs rounded-full <?php echo $status_filter === 'design' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">시안진행</a>
                <a href="?status=complete<?php echo $search ? '&q='.urlencode($search) : ''; ?>"
                   class="px-2 py-0.5 text-xs rounded-full <?php echo $status_filter === 'complete' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">작업완료</a>
            </div>
            <div class="flex-1 min-w-[180px]">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="주문번호, 이름, 품목 검색..."
                       class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <?php if ($status_filter): ?><input type="hidden" name="status" value="<?php echo $status_filter; ?>"><?php endif; ?>
            <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">검색</button>
        </form>

        <!-- Order/Proof List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-cyan-50">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">품목</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">주문자</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-gray-500">전화번호</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">상태</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">교정파일</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">일시</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">보기</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-gray-500">올리기</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="9" class="px-2 py-4 text-center text-xs text-gray-400">데이터가 없습니다.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-cyan-50" id="row-<?php echo $order['no']; ?>">
                            <td class="px-2 py-1 text-xs font-medium text-gray-900">#<?php echo $order['no']; ?></td>
                            <td class="px-2 py-1 text-xs text-gray-600"><?php echo htmlspecialchars($order['Type']); ?></td>
                            <td class="px-2 py-1 text-xs text-gray-600"><?php echo htmlspecialchars($order['name']); ?></td>
                            <td class="px-2 py-1 text-xs">
                                <?php
                                    $phone = trim($order['phone'] ?? '');
                                    $hendphone = trim($order['Hendphone'] ?? '');
                                    $displayPhone = $phone ?: $hendphone;
                                ?>
                                <?php if ($displayPhone): ?>
                                    <span class="text-gray-700" id="phone-display-<?php echo $order['no']; ?>"><?php echo htmlspecialchars($displayPhone); ?></span>
                                <?php else: ?>
                                    <div class="flex items-center gap-1" id="phone-edit-<?php echo $order['no']; ?>">
                                        <input type="text"
                                               id="phone-input-<?php echo $order['no']; ?>"
                                               placeholder="010-0000-0000"
                                               class="w-24 px-1 py-0.5 text-xs border border-orange-300 rounded focus:ring-1 focus:ring-blue-400 focus:border-blue-400 outline-none bg-orange-50"
                                               maxlength="20"
                                               onkeydown="if(event.key==='Enter'){savePhone(<?php echo $order['no']; ?>)}">
                                        <button onclick="savePhone(<?php echo $order['no']; ?>)"
                                                class="px-1 py-0.5 text-[10px] bg-blue-500 text-white rounded hover:bg-blue-600 flex-shrink-0">저장</button>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-1 text-center">
                                <?php
                                    $style = $order['OrderStyle'] ?? '0';
                                    $color = $status_colors[$style] ?? 'bg-gray-100 text-gray-700';
                                    $label = $status_labels[$style] ?? '미정';
                                ?>
                                <span class="inline-block px-1.5 py-0.5 text-xs font-medium rounded-full <?php echo $color; ?>"><?php echo $label; ?></span>
                            </td>
                            <td class="px-2 py-1 text-center">
                                <?php
                                    $file_count = count($order['files']);
                                    if ($file_count === 0):
                                ?>
                                    <span class="text-gray-400 text-xs">없음</span>
                                <?php elseif ($file_count === 1): ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                        📄 1개
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                        📑 <?php echo $file_count; ?>개
                                        <span class="text-[10px] text-amber-500"><?php echo $order['latest_date']; ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-1 text-xs text-gray-400 text-center"><?php echo date('Y/m/d H:i', strtotime($order['date'])); ?></td>
                            <td class="px-2 py-1 text-center">
                                <?php if (!empty($order['files'])): ?>
                                <button onclick="viewFiles(<?php echo $order['no']; ?>)" class="relative px-1.5 py-0.5 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100" title="교정파일 보기">
                                    🔍보기
                                    <?php if (count($order['files']) > 1): ?>
                                    <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 flex items-center justify-center px-0.5 text-[9px] font-bold text-white bg-amber-500 rounded-full leading-none"><?php echo count($order['files']); ?></span>
                                    <?php endif; ?>
                                </button>
                                <?php else: ?>
                                <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-2 py-1 text-center">
                                <button onclick="openUpload(<?php echo $order['no']; ?>)" class="px-1.5 py-0.5 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100" title="파일 올리기">📤올리기</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): 
                $qs = 'status=' . urlencode($status_filter) . '&q=' . urlencode($search);
                $range = 2;
                $start = max(1, $page - $range);
                $end = min($total_pages, $page + $range);
            ?>
            <div class="px-3 py-1.5 border-t border-gray-200 flex items-center justify-between text-xs">
                <span class="text-gray-500">총 <?php echo number_format($total); ?>건</span>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                    <a href="?page=1&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-cyan-50 text-gray-500" title="처음">«</a>
                    <a href="?page=<?php echo $page-1; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-cyan-50 text-gray-500" title="이전">‹</a>
                    <?php else: ?>
                    <span class="px-2 py-1 border rounded text-gray-300">«</span>
                    <span class="px-2 py-1 border rounded text-gray-300">‹</span>
                    <?php endif; ?>

                    <?php if ($start > 1): ?>
                    <a href="?page=1&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-cyan-50">1</a>
                    <?php if ($start > 2): ?><span class="px-1 text-gray-400">…</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i === $page): ?>
                    <span class="px-2.5 py-1 bg-blue-600 text-white rounded font-medium"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-cyan-50"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="px-1 text-gray-400">…</span><?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?>&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-cyan-50"><?php echo $total_pages; ?></a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-cyan-50 text-gray-500" title="다음">›</a>
                    <a href="?page=<?php echo $total_pages; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-cyan-50 text-gray-500" title="마지막">»</a>
                    <?php else: ?>
                    <span class="px-2 py-1 border rounded text-gray-300">›</span>
                    <span class="px-2 py-1 border rounded text-gray-300">»</span>
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
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 id="uploadModalTitle" class="font-semibold text-gray-900">교정파일 올리기</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div class="p-4">
            <input type="hidden" id="upload_order_no">
            <!-- 주문자 정보 (전화번호) -->
            <div id="uploadPhoneArea" class="mb-3 p-3 bg-cyan-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">주문자:</span>
                        <span id="uploadOrderName" class="font-medium text-gray-800"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm" id="uploadPhoneDisplay">
                        <!-- JS로 동적 렌더링 -->
                    </div>
                </div>
            </div>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-5 text-center hover:border-blue-400 transition-colors cursor-pointer" id="dropZone" onclick="document.getElementById('fileInput').click()">
                <div class="text-3xl mb-1">📁</div>
                <p class="text-sm text-gray-600">파일을 끌어다 놓거나 클릭하여 선택</p>
                <input type="file" id="fileInput" multiple class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.ai,.psd,.zip">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF, AI, PSD, ZIP (파일당 최대 20MB)</p>
            </div>
            <div id="fileList" class="mt-3 space-y-1 max-h-[240px] overflow-y-auto"></div>
            <div id="uploadProgress" class="mt-3 hidden">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span id="progressText">업로드 중...</span>
                    <span id="progressPct">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all" style="width:0%"></div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between">
                <span id="fileSummary" class="text-xs text-gray-400"></span>
                <div class="flex gap-2">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm border rounded-lg hover:bg-cyan-50">취소</button>
                    <button type="button" id="uploadBtn" onclick="doUpload()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>업로드</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Overlay -->
<div id="imgOverlay" class="fixed inset-0 z-[60] hidden bg-black bg-opacity-90 overflow-auto cursor-pointer" onclick="closeImageViewer()">
    <!-- Close button -->
    <button onclick="closeImageViewer()" class="fixed top-3 right-4 z-[70] text-white bg-black bg-opacity-60 rounded-full w-9 h-9 flex items-center justify-center hover:bg-opacity-90 text-lg">✕</button>
    <!-- Delete button -->
    <button id="deleteImgBtn" onclick="event.stopPropagation(); deleteCurrentImage()" class="fixed top-3 right-16 z-[70] text-white bg-red-600 bg-opacity-80 rounded-full w-9 h-9 flex items-center justify-center hover:bg-opacity-100 text-sm" title="이미지 삭제">🗑</button>
    <!-- Prev button -->
    <button id="prevBtn" onclick="event.stopPropagation(); navImage(-1)" class="fixed left-3 top-1/2 -translate-y-1/2 z-[70] text-white bg-black bg-opacity-60 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-90 text-xl hidden">&lsaquo;</button>
    <!-- Next button -->
    <button id="nextBtn" onclick="event.stopPropagation(); navImage(1)" class="fixed right-3 top-1/2 -translate-y-1/2 z-[70] text-white bg-black bg-opacity-60 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-90 text-xl hidden">&rsaquo;</button>
    <!-- Image (100% original size, scrollable) -->
    <div class="min-h-full flex items-start justify-center p-4">
        <img id="overlayImg" src="" class="cursor-pointer" onclick="closeImageViewer()" style="max-width:none;">
    </div>
    <!-- Info bar: counter + filename + date + thumbnails -->
    <div id="imgInfoBar" class="fixed bottom-0 left-0 right-0 z-[70] bg-gradient-to-t from-black/80 to-transparent pt-8 pb-3 px-4 hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-center gap-3 mb-2">
            <span id="imgCounter" class="text-white text-sm bg-white/20 px-3 py-1 rounded-full"></span>
            <span id="imgFileName" class="text-white/70 text-xs truncate max-w-[300px]"></span>
            <span id="imgFileDate" class="text-white/50 text-xs"></span>
        </div>
        <div id="imgThumbnails" class="flex items-center justify-center gap-1.5 overflow-x-auto max-w-2xl mx-auto py-1"></div>
    </div>
</div>

<script>
// === Order Phone Data (from PHP) ===
var orderPhoneData = {};
<?php foreach ($orders as $o): ?>
orderPhoneData[<?php echo $o['no']; ?>] = {
    name: <?php echo json_encode($o['name'] ?: '주문자', JSON_UNESCAPED_UNICODE); ?>,
    phone: <?php echo json_encode(trim($o['phone'] ?? ''), JSON_UNESCAPED_UNICODE); ?>,
    hendphone: <?php echo json_encode(trim($o['Hendphone'] ?? ''), JSON_UNESCAPED_UNICODE); ?>
};
<?php endforeach; ?>

// === Phone Save ===
function saveModalPhone(orderNo) {
    var input = document.getElementById('modalPhoneInput');
    var phone = input.value.trim();
    if (!phone) { input.focus(); return; }

    fetch('/dashboard/proofs/api.php?action=save_phone', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_no=' + orderNo + '&phone=' + encodeURIComponent(phone)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var phoneArea = document.getElementById('uploadPhoneDisplay');
            phoneArea.innerHTML = '<span class="text-gray-500">📞</span><span class="text-gray-800">' + phone.replace(/</g,'&lt;') + '</span>';
            if (orderPhoneData[orderNo]) orderPhoneData[orderNo].phone = phone;
            var tableEdit = document.getElementById('phone-edit-' + orderNo);
            if (tableEdit) {
                tableEdit.innerHTML = '<span class="text-gray-700">' + phone.replace(/</g,'&lt;') + '</span>';
            }
            showToast('전화번호 저장 완료', 'success');
        } else {
            showToast(data.message || '저장 실패', 'error');
        }
    })
    .catch(function() { showToast('네트워크 오류', 'error'); });
}

function savePhone(orderNo) {
    var input = document.getElementById('phone-input-' + orderNo);
    var phone = input.value.trim();
    if (!phone) { input.focus(); return; }

    fetch('/dashboard/proofs/api.php?action=save_phone', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_no=' + orderNo + '&phone=' + encodeURIComponent(phone)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var editDiv = document.getElementById('phone-edit-' + orderNo);
            editDiv.innerHTML = '<span class="text-gray-700">' + phone.replace(/</g,'&lt;') + '</span>';
            showToast('전화번호 저장 완료', 'success');
        } else {
            showToast(data.message || '저장 실패', 'error');
        }
    })
    .catch(function() { showToast('네트워크 오류', 'error'); });
}

var viewerImages = [];  // [{url, name, date, orderNo, filePath}]
var viewerIndex = 0;
var viewerOrderNo = 0;

document.addEventListener('keydown', function(e) {
    if (document.getElementById('imgOverlay').classList.contains('hidden')) return;
    if (e.key === 'Escape') closeImageViewer();
    if (e.key === 'ArrowLeft') navImage(-1);
    if (e.key === 'ArrowRight') navImage(1);
    if (e.key === 'Delete' || e.key === 'Backspace') deleteCurrentImage();
});

function viewFiles(orderNo) {
    viewerOrderNo = orderNo;
    fetch('/dashboard/proofs/api.php?action=files&order_no=' + orderNo)
        .then(r => r.json())
        .then(data => {
            if (!data.files || data.files.length === 0) return;
            var images = data.files.filter(f => /\.(jpg|jpeg|png|gif)$/i.test(f.name));
            if (images.length > 0) {
                viewerImages = images.map(f => ({
                    url: f.url + '?raw',
                    name: f.name,
                    date: f.date || '',
                    orderNo: orderNo,
                    filePath: f.path
                }));
                viewerIndex = 0;
                buildThumbnails();
                showImage();
                document.getElementById('imgOverlay').classList.remove('hidden');
            } else {
                window.open(data.files[0].url, '_blank');
            }
        });
}

function buildThumbnails() {
    var container = document.getElementById('imgThumbnails');
    container.textContent = '';
    if (viewerImages.length <= 1) return;

    viewerImages.forEach(function(img, i) {
        var thumb = document.createElement('img');
        thumb.src = img.url;
        thumb.dataset.idx = i;
        thumb.className = 'thumb-item w-12 h-12 object-cover rounded cursor-pointer border-2 transition-all hover:opacity-100 '
            + (i === 0 ? 'border-white opacity-100' : 'border-transparent opacity-50');
        thumb.addEventListener('click', function(e) {
            e.stopPropagation();
            viewerIndex = i;
            showImage();
        });
        container.appendChild(thumb);
    });
}

function showImage() {
    var img = viewerImages[viewerIndex];
    document.getElementById('overlayImg').src = img.url;

    var total = viewerImages.length;
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var infoBar = document.getElementById('imgInfoBar');
    var counter = document.getElementById('imgCounter');
    var fileName = document.getElementById('imgFileName');
    var fileDate = document.getElementById('imgFileDate');

    infoBar.classList.remove('hidden');
    counter.textContent = (viewerIndex + 1) + ' / ' + total;
    fileName.textContent = img.name;
    fileDate.textContent = img.date;

    if (total > 1) {
        prevBtn.classList.toggle('hidden', viewerIndex === 0);
        nextBtn.classList.toggle('hidden', viewerIndex === total - 1);
        document.querySelectorAll('.thumb-item').forEach(function(el, i) {
            if (i === viewerIndex) {
                el.classList.add('border-white', 'opacity-100');
                el.classList.remove('border-transparent', 'opacity-50');
            } else {
                el.classList.remove('border-white', 'opacity-100');
                el.classList.add('border-transparent', 'opacity-50');
            }
        });
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
    }
}

function navImage(dir) {
    var next = viewerIndex + dir;
    if (next < 0 || next >= viewerImages.length) return;
    viewerIndex = next;
    showImage();
}

function onOverlayClick(e) {
    if (e.target === document.getElementById('imgOverlay')) closeImageViewer();
}

function closeImageViewer() {
    document.getElementById('imgOverlay').classList.add('hidden');
    document.getElementById('imgInfoBar').classList.add('hidden');
    document.getElementById('overlayImg').src = '';
    viewerImages = [];
}

function deleteCurrentImage() {
    if (viewerImages.length === 0) return;
    var img = viewerImages[viewerIndex];
    if (!img.filePath) return;

    if (!confirm('정말 이 이미지를 삭제하시겠습니까?\n\n' + img.name)) return;

    fetch('/dashboard/proofs/api.php?action=delete_file', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_no=' + img.orderNo + '&file=' + encodeURIComponent(img.filePath)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('삭제 완료', 'success');
            // 현재 이미지 제거
            viewerImages.splice(viewerIndex, 0);

            if (viewerImages.length === 0) {
                // 더 이상 이미지가 없으면 닫기
                closeImageViewer();
                // 테이블 행 파일 수 갱신
                updateRowFileCount(img.orderNo);
            } else {
                // 인덱스 조정
                if (viewerIndex >= viewerImages.length) {
                    viewerIndex = viewerImages.length - 1;
                }
                buildThumbnails();
                showImage();
            }
        } else {
            showToast(data.message || '삭제 실패', 'error');
        }
    })
    .catch(() => showToast('네트워크 오류', 'error'));
}

function closeFileModal() { document.getElementById('fileModal').classList.add('hidden'); }

// === Upload System ===
var pendingFiles = [];  // [{file: File, customName: string}]
var currentUploadOrderNo = 0;
var MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
var ALLOWED_EXT = ['jpg','jpeg','png','gif','pdf','ai','psd','zip'];

function openUpload(orderNo) {
    currentUploadOrderNo = orderNo;
    pendingFiles = [];
    document.getElementById('upload_order_no').value = orderNo;
    document.getElementById('uploadModalTitle').textContent = '#' + orderNo + ' 교정파일 올리기';
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadProgress').classList.add('hidden');
    renderFileList();

    var data = orderPhoneData[orderNo] || {};
    var displayPhone = data.phone || data.hendphone || '';
    document.getElementById('uploadOrderName').textContent = data.name || '주문자';

    var phoneArea = document.getElementById('uploadPhoneDisplay');
    if (displayPhone) {
        phoneArea.innerHTML = '<span class="text-gray-500">📞</span><span class="text-gray-800">' + displayPhone.replace(/</g,'&lt;') + '</span>';
    } else {
        phoneArea.innerHTML = '<span class="text-gray-500">📞</span>'
            + '<input type="text" id="modalPhoneInput" placeholder="010-0000-0000" '
            + 'class="w-28 px-1.5 py-0.5 text-xs border border-orange-300 rounded focus:ring-1 focus:ring-blue-400 outline-none bg-orange-50" maxlength="20" '
            + 'onkeydown="if(event.key===\'Enter\'){saveModalPhone(' + orderNo + ')}">'
            + '<button onclick="saveModalPhone(' + orderNo + ')" class="px-2 py-0.5 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">저장</button>';
    }

    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    pendingFiles = [];
}

function getBaseName(filename) {
    var dot = filename.lastIndexOf('.');
    return dot > 0 ? filename.substring(0, dot) : filename;
}

function getExt(filename) {
    var dot = filename.lastIndexOf('.');
    return dot > 0 ? filename.substring(dot) : '';
}

function addFiles(fileList) {
    Array.from(fileList).forEach(function(f) {
        var ext = f.name.split('.').pop().toLowerCase();
        if (ALLOWED_EXT.indexOf(ext) === -1) {
            showToast(f.name + ' - 지원하지 않는 형식', 'error');
            return;
        }
        if (f.size > MAX_FILE_SIZE) {
            showToast(f.name + ' - 20MB 초과', 'error');
            return;
        }
        var exists = pendingFiles.some(function(p) { return p.file.name === f.name && p.file.size === f.size; });
        if (!exists) {
            pendingFiles.push({ file: f, customName: getBaseName(f.name) });
        }
    });
    renderFileList();
}

function removeFile(idx) {
    pendingFiles.splice(idx, 1);
    renderFileList();
}

function onNameChange(idx, val) {
    if (idx >= 0 && idx < pendingFiles.length) {
        pendingFiles[idx].customName = val;
    }
}

function renderFileList() {
    var list = document.getElementById('fileList');
    var summary = document.getElementById('fileSummary');
    var btn = document.getElementById('uploadBtn');

    if (pendingFiles.length === 0) {
        list.innerHTML = '';
        summary.textContent = '';
        btn.disabled = true;
        return;
    }

    var totalSize = 0;
    var html = '';
    pendingFiles.forEach(function(item, i) {
        var f = item.file;
        var size = f.size / 1024 / 1024;
        totalSize += size;
        var ext = getExt(f.name).toLowerCase();
        var isImg = /\.(jpg|jpeg|png|gif)$/i.test(f.name);
        var thumb = '';
        if (isImg) {
            thumb = '<img src="' + URL.createObjectURL(f) + '" class="w-10 h-10 object-cover rounded flex-shrink-0">';
        } else {
            var icons = {'.pdf':'📄','.ai':'🎨','.psd':'🎨','.zip':'📦'};
            thumb = '<span class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded text-base flex-shrink-0">' + (icons[ext]||'📎') + '</span>';
        }
        html += '<div class="flex items-center gap-2 p-2 bg-cyan-50 rounded text-xs group">'
            + thumb
            + '<div class="flex-1 min-w-0">'
            +   '<div class="flex items-center gap-1">'
            +     '<input type="text" value="' + item.customName.replace(/"/g, '&quot;') + '" '
            +       'onchange="onNameChange(' + i + ', this.value)" '
            +       'onfocus="this.select()" '
            +       'class="flex-1 min-w-0 px-1.5 py-0.5 border border-gray-200 rounded text-xs focus:border-blue-400 focus:ring-1 focus:ring-blue-200 outline-none bg-white" '
            +       'placeholder="파일 이름 입력">'
            +     '<span class="text-gray-400 flex-shrink-0">' + ext + '</span>'
            +   '</div>'
            +   '<div class="text-[10px] text-gray-400 mt-0.5">' + size.toFixed(1) + 'MB</div>'
            + '</div>'
            + '<button type="button" onclick="removeFile(' + i + ')" class="text-red-400 hover:text-red-600 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-base" title="제거">&times;</button>'
            + '</div>';
    });
    list.innerHTML = html;
    summary.textContent = pendingFiles.length + '개 파일 · ' + totalSize.toFixed(1) + 'MB';
    btn.disabled = false;
}

function doUpload() {
    if (pendingFiles.length === 0) return;
    var btn = document.getElementById('uploadBtn');
    var prog = document.getElementById('uploadProgress');
    var bar = document.getElementById('progressBar');
    var pct = document.getElementById('progressPct');
    var txt = document.getElementById('progressText');

    btn.disabled = true;
    btn.textContent = '업로드 중...';
    prog.classList.remove('hidden');

    // 입력란의 최신 값 수집
    var nameInputs = document.querySelectorAll('#fileList input[type="text"]');
    nameInputs.forEach(function(inp, i) {
        if (i < pendingFiles.length) pendingFiles[i].customName = inp.value;
    });

    var formData = new FormData();
    formData.append('order_no', currentUploadOrderNo);
    pendingFiles.forEach(function(item) {
        formData.append('files[]', item.file);
        formData.append('names[]', item.customName.trim());
    });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/dashboard/proofs/api.php?action=upload');

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var percent = Math.round(e.loaded / e.total * 100);
            bar.style.width = percent + '%';
            pct.textContent = percent + '%';
            txt.textContent = '업로드 중... (' + (e.loaded/1024/1024).toFixed(1) + '/' + (e.total/1024/1024).toFixed(1) + 'MB)';
        }
    });

    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = '업로드';
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
                showToast(data.message || '업로드 완료', 'success');
                closeUploadModal();
                // 해당 행 파일 수 갱신
                updateRowFileCount(currentUploadOrderNo);
            } else {
                showToast(data.message || '업로드 실패', 'error');
                prog.classList.add('hidden');
            }
        } catch(e) {
            showToast('업로드 실패', 'error');
            prog.classList.add('hidden');
        }
    };

    xhr.onerror = function() {
        btn.disabled = false;
        btn.textContent = '업로드';
        showToast('네트워크 오류', 'error');
        prog.classList.add('hidden');
    };

    xhr.send(formData);
}

function updateRowFileCount(orderNo) {
    fetch('/dashboard/proofs/api.php?action=files&order_no=' + orderNo)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var row = document.getElementById('row-' + orderNo);
            if (!row) return;
            var files = data.files || [];
            var cnt = files.length;
            var latestDate = cnt > 0 && files[0].date ? files[0].date.split(' ')[0] : '';

            // 교정파일 열 업데이트 (5번째 td)
            var fileTd = row.children[5];
            if (fileTd) {
                fileTd.textContent = '';
                var span = document.createElement('span');
                if (cnt === 0) {
                    span.className = 'text-gray-400 text-xs';
                    span.textContent = '없음';
                } else if (cnt === 1) {
                    span.className = 'inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-600';
                    span.textContent = '📄 1개';
                } else {
                    span.className = 'inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200';
                    span.textContent = '📑 ' + cnt + '개';
                    if (latestDate) {
                        var dateSpan = document.createElement('span');
                        dateSpan.className = 'text-[10px] text-amber-500';
                        dateSpan.textContent = latestDate;
                        span.appendChild(dateSpan);
                    }
                }
                fileTd.appendChild(span);
            }

            // 보기 열에 보기 버튼 추가/업데이트
            var actionTd = row.children[7];
            if (actionTd && cnt > 0) {
                if (actionTd.querySelector('span')?.textContent === '-') {
                    var existingBtn = btnDiv.querySelector('[data-view-btn]');
                    if (!existingBtn) {
                        var viewBtn = document.createElement('button');
                        viewBtn.className = 'relative px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100';
                        viewBtn.title = '교정파일 보기';
                        viewBtn.textContent = '🔍보기';
                        viewBtn.setAttribute('data-view-btn', '1');
                        viewBtn.onclick = function() { viewFiles(orderNo); };
                        btnDiv.insertBefore(viewBtn, btnDiv.firstChild);
                        existingBtn = viewBtn;
                    }
                    // 뱃지 업데이트
                    var badge = existingBtn.querySelector('.count-badge');
                    if (cnt > 1) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'count-badge absolute -top-1.5 -right-1.5 min-w-[16px] h-4 flex items-center justify-center px-1 text-[10px] font-bold text-white bg-amber-500 rounded-full leading-none';
                            existingBtn.appendChild(badge);
                        }
                        badge.textContent = cnt;
                    } else if (badge) {
                        badge.remove();
                    }
                }
            }
        });
}

// File input change → 파일 추가 (기존 목록에 append)
document.getElementById('fileInput').addEventListener('change', function() {
    addFiles(this.files);
    this.value = ''; // 리셋하여 같은 파일 재선택 가능
});

// Drag & Drop
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('border-blue-400', 'bg-blue-50'); });
dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('border-blue-400', 'bg-blue-50'); });
dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    addFiles(e.dataTransfer.files);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
