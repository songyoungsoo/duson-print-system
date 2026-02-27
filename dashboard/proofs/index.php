<?php
date_default_timezone_set('Asia/Seoul');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$page = max(1, intval($_GET['page'] ?? 1));
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$per_page = 24;
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
          o.Designer, o.proofreading_confirmed, o.proofreading_date,
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

<main class="flex-1 bg-gray-50">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-1">
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
                    <thead style="background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-semibold text-white">주문번호</th>
                            <th class="px-2 py-1.5 text-left text-xs font-semibold text-white">품목</th>
                            <th class="px-2 py-1.5 text-left text-xs font-semibold text-white">주문자</th>
                            <th class="px-2 py-1.5 text-left text-xs font-semibold text-white">전화번호</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">상태</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">담당자</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">교정확정</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">운송장번호</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">교정파일</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">일시</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">보기</th>
                            <th class="px-2 py-1.5 text-center text-xs font-semibold text-white">올리기</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="12" class="px-2 py-4 text-center text-xs text-gray-400">데이터가 없습니다.</td></tr>
                        <?php endif; ?>
                        <?php $loop_idx = 0; foreach ($orders as $order): ?>
                        <tr class="<?php echo $loop_idx % 2 === 1 ? 'hover:bg-gray-100' : 'hover:bg-gray-50'; ?>" <?php if ($loop_idx % 2 === 1) echo 'style="background-color:#e6f7ff"'; ?> id="row-<?php echo $order['no']; ?>">
                            <td class="px-1.5 py-0.5 text-xs font-medium text-gray-900">#<?php echo $order['no']; ?></td>
                            <td class="px-1.5 py-0.5 text-xs text-gray-600"><?php
                                $typeMap = [
                                    'sticker' => '스티커', 'sticker_new' => '스티커',
                                    'namecard' => '명함', 'inserted' => '전단지',
                                    'envelope' => '봉투', 'cadarok' => '카다록',
                                    'littleprint' => '포스터', 'merchandisebond' => '상품권',
                                    'ncrflambeau' => 'NCR양식지', 'msticker' => '자석스티커'
                                ];
                                $rawType = trim($order['Type'] ?? '');
                                $displayType = $typeMap[strtolower($rawType)] ?? $rawType;
                                echo htmlspecialchars($displayType);
                            ?></td>
                            <td class="px-1.5 py-0.5 text-xs text-gray-600"><?php echo htmlspecialchars($order['name']); ?></td>
                            <td class="px-1.5 py-0.5 text-xs">
                                <?php
                                    $phone = trim($order['phone'] ?? '');
                                    $hendphone = trim($order['Hendphone'] ?? '');
                                    $displayPhone = $phone ?: $hendphone;
                                ?>
                                <?php if ($displayPhone): ?>
                                    <span class="text-gray-700" id="phone-display-<?php echo $order['no']; ?>"><?php echo htmlspecialchars($displayPhone); ?></span>
                                <?php else: ?>
                                    <div class="flex items-center gap-1" id="phone-edit-<?php echo $order['no']; ?>">
                                        <input type="tel"
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
                            <td class="px-1.5 py-0.5 text-center whitespace-nowrap">
                                <?php $curStyle = $order['OrderStyle'] ?? '0'; ?>
                                <select onchange="changeOrderStatus(<?php echo $order['no']; ?>, this.value, this)"
                                        class="text-xs border border-gray-300 rounded px-1 py-0.5 focus:ring-1 focus:ring-blue-500 cursor-pointer"
                                        style="color: <?php
                                            $sc = ['1'=>'#64748b','2'=>'#d97706','3'=>'#d97706','4'=>'#ea580c','5'=>'#4f46e5','6'=>'#7c3aed','7'=>'#2563eb','8'=>'#16a34a','9'=>'#9333ea','10'=>'#0891b2'];
                                            echo $sc[$curStyle] ?? '#333';
                                        ?>; font-weight: 600;">
                                    <?php foreach ($status_labels as $k => $v): if ($k === '0') continue; ?>
                                    <option value="<?php echo $k; ?>" <?php echo $curStyle === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-1.5 py-0.5 text-center text-xs text-gray-600"><?php echo htmlspecialchars($order['Designer'] ?: '-'); ?></td>
                            <td class="px-1.5 py-0.5 text-center">
                                <?php if (!empty($order['proofreading_confirmed']) && $order['proofreading_confirmed'] == 1): ?>
                                    <span class="inline-block px-1.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">인쇄진행</span>
                                    <?php if (!empty($order['proofreading_date'])): ?>
                                    <small class="block text-[10px] text-gray-400"><?php echo date('m/d H:i', strtotime($order['proofreading_date'])); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-1.5 py-0.5 text-center text-xs text-gray-400">-</td>
                            <td class="px-1.5 py-0.5 text-center">
                                <?php
                                    $file_count = count($order['files']);
                                    if ($file_count === 0):
                                ?>
                                    <span class="text-gray-400 text-xs">없음</span>
                                <?php elseif ($file_count === 1): ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                        1개
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                        <?php echo $file_count; ?>개
                                        <span class="text-[10px] text-amber-500"><?php echo $order['latest_date']; ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-1.5 py-0.5 text-xs text-gray-400 text-center"><?php echo date('m/d H:i', strtotime($order['date'])); ?></td>
                            <td class="px-1.5 py-0.5 text-center">
                                <?php if (!empty($order['files'])): ?>
                                <button onclick="viewFiles(<?php echo $order['no']; ?>)" class="relative px-1.5 py-0.5 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100" title="교정파일 보기">
                                    보기
                                    <?php if (count($order['files']) > 1): ?>
                                    <span class="absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 flex items-center justify-center px-0.5 text-[9px] font-bold text-white bg-amber-500 rounded-full leading-none"><?php echo count($order['files']); ?></span>
                                    <?php endif; ?>
                                </button>
                                <?php else: ?>
                                <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-1.5 py-0.5 text-center">
                                <button onclick="openUpload(<?php echo $order['no']; ?>)" class="px-1.5 py-0.5 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100" title="파일 올리기">올리기</button>
                            </td>
                        </tr>
                        <?php $loop_idx++; endforeach; ?>
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
                    <a href="?page=1&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-gray-50 text-gray-500" title="처음">«</a>
                    <a href="?page=<?php echo $page-1; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-gray-50 text-gray-500" title="이전">‹</a>
                    <?php else: ?>
                    <span class="px-2 py-1 border rounded text-gray-300">«</span>
                    <span class="px-2 py-1 border rounded text-gray-300">‹</span>
                    <?php endif; ?>

                    <?php if ($start > 1): ?>
                    <a href="?page=1&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-gray-50">1</a>
                    <?php if ($start > 2): ?><span class="px-1 text-gray-400">…</span><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i === $page): ?>
                    <span class="px-2.5 py-1 bg-blue-600 text-white rounded font-medium"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-gray-50"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="px-1 text-gray-400">…</span><?php endif; ?>
                    <a href="?page=<?php echo $total_pages; ?>&<?php echo $qs; ?>" class="px-2.5 py-1 border rounded hover:bg-gray-50"><?php echo $total_pages; ?></a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-gray-50 text-gray-500" title="다음">›</a>
                    <a href="?page=<?php echo $total_pages; ?>&<?php echo $qs; ?>" class="px-2 py-1 border rounded hover:bg-gray-50 text-gray-500" title="마지막">»</a>
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
    <div class="bg-white rounded-lg shadow-xl max-w-7xl w-full max-h-[90vh] overflow-y-auto">
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
            <div id="uploadPhoneArea" class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
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
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">취소</button>
                    <button type="button" id="uploadBtn" onclick="doUpload()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>업로드</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Overlay (Windows Photo Viewer Style) -->
<div id="imgOverlay" class="fixed inset-0 z-[60] hidden bg-black">
    <!-- Top Header Bar -->
    <div id="viewerHeader" class="viewer-header">
        <div class="header-title">두손기획인쇄</div>
        <div class="header-notice">
            <span class="notice-item">이미지는 RGB 표시 / 인쇄 시 CMYK 출력으로 색상차이 있음</span>
            <span class="notice-item">오탈자 및 전체 상태를 확인하여 전반적인 수정사항을 요청하셔야 합니다</span>
            <span class="notice-item highlight">수정은 2회 가능합니다</span>
        </div>
    </div>

    <!-- Image Container (Zoom/Pan area) -->
    <div id="imageContainer" class="image-container">
        <img id="overlayImg" src="" alt="" class="overlay-image">
    </div>

    <!-- Right Control Panel -->
    <div id="controlPanel" class="control-panel">
        <button id="zoomInBtn" type="button" title="확대 (Ctrl+Roll Up)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button id="zoomOutBtn" type="button" title="축소 (Ctrl+Roll Down)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button id="fitBtn" type="button" title="화면 맞춤 (0)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <path d="M9 3v18M15 3v18M3 9h18M3 15h18"></path>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="fullscreenBtn" type="button" title="전체화면 (F)" class="control-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="prevBtn" type="button" title="이전 (&larr;)" class="control-btn nav-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button id="nextBtn" type="button" title="다음 (&rarr;)" class="control-btn nav-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="deleteImgBtn" type="button" title="삭제 (Delete)" class="control-btn delete-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
        </button>
        <button id="closeBtn" type="button" title="닫기 (Esc)" class="control-btn close-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Bottom Status Bar -->
    <div id="statusBar" class="status-bar">
        <div class="status-left">
            <span id="zoomLevel" class="zoom-level">화면 맞춤</span>
        </div>
        <div class="status-center">
            <span id="imgCounter" class="img-counter"></span>
            <span id="imgFileName" class="img-filename"></span>
        </div>
        <div class="status-right">
            <span id="imgFileDate" class="img-date"></span>
        </div>
    </div>

    <!-- Proof Confirmation Area -->
    <div id="proofConfirmArea" class="proof-confirm-area">
        <div id="proofConfirmContent">
            <button id="proofConfirmBtn" type="button" class="proof-confirm-btn">
                교정확정
            </button>
            <p class="proof-confirm-notice">오탈자 및 전체를 잘 확인 후 클릭해주세요</p>
        </div>
        <div id="proofConfirmedMsg" class="proof-confirmed-msg" style="display: none;">
            인쇄진행
        </div>
    </div>

    <!-- Thumbnail Bar (below status bar) -->
    <div id="thumbnailBar" class="thumbnail-bar">
        <div id="imgThumbnails" class="thumbnails-container"></div>
    </div>
</div>

<style>
/* Image Viewer Styles */
.image-container {
    position: absolute;
    top: 62px;  /* 상단 헤더 높이 */
    left: 0;
    right: 72px;
    bottom: 160px;  /* 상태바(48px) + 썸네일바(56px) + 교정확정영역(48px) + 여유(8px) */
    overflow: hidden;
    background: #1a1a1a;
}

.overlay-image {
    /* transform으로만 위치 제어 */
    max-width: none;
    max-height: none;
    object-fit: contain;
    transition: transform 0.1s ease-out;
    cursor: default;
    user-select: none;
    -webkit-user-drag: none;
    /* transform 초기값 명시 */
    transform: translate(0, 0) scale(1);
    transform-origin: 0 0;
    /* flex 정렬 제거 - transform만 사용 */
}

.overlay-image.dragging {
    cursor: grabbing;
}

/* Viewer Header */
.viewer-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 72px;
    background: #1e293b;
    padding: 8px 0;
    z-index: 85;
    text-align: center;
}

.header-title {
    color: white;
    font-size: 16px;
    font-weight: 600;
}

.header-notice {
    margin-top: 4px;
}

.notice-item {
    color: #cbd5e1;
    font-size: 10px;
    display: block;
    line-height: 1.3;
}

.notice-item.highlight {
    color: #fbbf24;
}

/* Control Panel */
.control-panel {
    position: fixed;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: rgba(30, 30, 30, 0.9);
    padding: 10px 6px;
    border-radius: 12px;
    z-index: 80;
    backdrop-filter: blur(10px);
}

.control-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.85);
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.control-btn:active {
    background: rgba(255, 255, 255, 0.25);
}

.control-btn svg {
    pointer-events: none;
}

.control-btn.delete-btn:hover {
    background: rgba(239, 68, 68, 0.8);
}

.control-btn.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.control-btn.hidden {
    display: none;
}

.control-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.15);
    margin: 4px 0;
}

/* Status Bar */
.status-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 48px;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 80;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.status-left,
.status-center,
.status-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.zoom-level {
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    font-weight: 500;
    min-width: 80px;
}

.img-counter {
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
}

.img-filename {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.img-date {
    color: rgba(255, 255, 255, 0.5);
    font-size: 11px;
}

/* Thumbnail Bar */
.thumbnail-bar {
    position: fixed;
    bottom: 48px;
    left: 0;
    right: 72px;
    height: 56px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    z-index: 75;
    pointer-events: none;
}

.thumbnail-bar.hidden {
    display: none;
}

.thumbnails-container {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    max-width: 100%;
    padding: 4px;
    pointer-events: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}

.thumbnails-container::-webkit-scrollbar {
    height: 4px;
}

.thumbnails-container::-webkit-scrollbar-track {
    background: transparent;
}

.thumbnails-container::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
}

.thumb-item {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.15s ease;
    opacity: 0.5;
    flex-shrink: 0;
}

.thumb-item:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.thumb-item.active {
    border-color: white;
    opacity: 1;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
}

/* Proof Confirmation Area */
.proof-confirm-area {
    position: fixed;
    bottom: 110px;
    left: 0;
    right: 72px;
    background: rgba(255, 255, 255, 0.95);
    padding: 12px 16px;
    z-index: 85;
    text-align: center;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

.proof-confirm-btn {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(22, 163, 74, 0.3);
    transition: all 0.3s ease;
}

.proof-confirm-btn:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.4);
}

.proof-confirm-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.proof-confirm-notice {
    font-size: 11px;
    color: #64748b;
    margin: 6px 0 0 0;
    line-height: 1.3;
}

.proof-confirmed-msg {
    color: #dc2626;
    font-weight: 600;
    font-size: 14px;
}
</style>

<script>
// === 상태 변경 ===
var statusColors = {'1':'#64748b','2':'#d97706','3':'#d97706','4':'#ea580c','5':'#4f46e5','6':'#7c3aed','7':'#2563eb','8':'#16a34a','9':'#9333ea','10':'#0891b2'};
async function changeOrderStatus(no, newStatus, selectEl) {
    try {
        var formData = new FormData();
        formData.append('action', 'update');
        formData.append('no', no);
        formData.append('order_style', newStatus);
        var response = await fetch('/dashboard/api/orders.php', { method: 'POST', body: formData });
        var result = await response.json();
        if (result.success) {
            selectEl.style.color = statusColors[newStatus] || '#333';
        } else {
            alert('상태 변경 실패: ' + result.message);
            location.reload();
        }
    } catch (error) {
        alert('상태 변경 중 오류가 발생했습니다.');
        location.reload();
    }
}

// === Order Phone Data (from PHP) ===
var orderPhoneData = {};
<?php foreach ($orders as $o): ?>
orderPhoneData[<?php echo $o['no']; ?>] = {
    name: <?php echo json_encode($o['name'] ?: '주문자', JSON_UNESCAPED_UNICODE); ?>,
    phone: <?php echo json_encode(trim($o['phone'] ?? ''), JSON_UNESCAPED_UNICODE); ?>,
    hendphone: <?php echo json_encode(trim($o['Hendphone'] ?? ''), JSON_UNESCAPED_UNICODE); ?>
};
<?php endforeach; ?>

// === Image Viewer Zoom State ===
var zoomState = {
    level: 'fit',
    scale: 1,
    offsetX: 0,
    offsetY: 0,
    isDragging: false,
    startX: 0,
    startY: 0
};

var ZOOM_LEVELS = [25, 50, 75, 100, 125, 150, 200, 300, 400];

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
    if (e.key === 'Escape') { e.preventDefault(); closeImageViewer(); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); navImage(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); navImage(1); }
    if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); deleteCurrentImage(); }
    if (e.key === '+' || e.key === '=') { e.preventDefault(); zoomIn(); }
    if (e.key === '-' || e.key === '_') { e.preventDefault(); zoomOut(); }
    if (e.key === '0') { e.preventDefault(); setZoom('fit'); }
    if (e.key === 'f' || e.key === 'F') { e.preventDefault(); toggleFullscreen(); }
});

// === Zoom Functions ===
function fitToScreen() {
    var img = document.getElementById('overlayImg');
    var container = document.getElementById('imageContainer');

    if (!img.naturalWidth) return;

    var containerWidth = container.clientWidth;
    var containerHeight = container.clientHeight;

    var imgRatio = img.naturalWidth / img.naturalHeight;
    var containerRatio = containerWidth / containerHeight;

    var scale;
    if (imgRatio > containerRatio) {
        scale = containerWidth / img.naturalWidth;
    } else {
        scale = containerHeight / img.naturalHeight;
    }

    // fit 상태에서도 실제 중앙 offset 계산 (첫 줌을 위해서)
    var scaledWidth = img.naturalWidth * scale;
    var scaledHeight = img.naturalHeight * scale;

    zoomState.level = 'fit';
    zoomState.scale = scale;
    zoomState.offsetX = (containerWidth - scaledWidth) / 2;
    zoomState.offsetY = (containerHeight - scaledHeight) / 2;

    // CSS flex 정렬 사용 (transform은 적용하지 않음)
    applyTransform();
}

function setZoom(level, centerX, centerY) {
    var img = document.getElementById('overlayImg');
    var container = document.getElementById('imageContainer');
    var oldScale = zoomState.scale;

    if (level === 'fit') {
        fitToScreen();
        return;
    }

    var newScale = level / 100;
    var containerWidth = container.clientWidth;
    var containerHeight = container.clientHeight;
    var imgWidth = img.naturalWidth;
    var imgHeight = img.naturalHeight;

    // 중심 기준 확대: 현재 이미지 중심이 유지되도록 계산
    // 현재 이미지 중심 위치 (컨테이너 좌표계)
    var currentCenterX = zoomState.offsetX + (imgWidth * oldScale) / 2;
    var currentCenterY = zoomState.offsetY + (imgHeight * oldScale) / 2;

    // 새로운 스케일에서 중심 위치가 같도록 offset 계산
    zoomState.level = level;
    zoomState.scale = newScale;
    zoomState.offsetX = currentCenterX - (imgWidth * newScale) / 2;
    zoomState.offsetY = currentCenterY - (imgHeight * newScale) / 2;

    applyTransform();
}

function applyTransform() {
    var img = document.getElementById('overlayImg');

    // Update zoom level display
    var zoomText = zoomState.level === 'fit' ? '화면 맞춤' : zoomState.level + '%';
    document.getElementById('zoomLevel').textContent = zoomText;

    // 항상 transform 사용 (fit 상태에서도 중앙 위치 유지)
    img.style.transform = 'translate(' + zoomState.offsetX + 'px, ' + zoomState.offsetY + 'px) scale(' + zoomState.scale + ')';
    img.style.transformOrigin = '0 0';
    img.style.maxWidth = 'none';
    img.style.maxHeight = 'none';

    // 커서 설정
    if (zoomState.level === 'fit') {
        img.style.cursor = 'default';
    } else {
        img.style.cursor = zoomState.isDragging ? 'grabbing' : 'grab';
    }
}

function zoomIn() {
    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);
    var nextIndex = Math.min(currentIndex + 1, ZOOM_LEVELS.length - 1);
    setZoom(ZOOM_LEVELS[nextIndex]);
}

function zoomOut() {
    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);
    if (currentIndex > 0) {
        setZoom(ZOOM_LEVELS[currentIndex - 1]);
    } else {
        setZoom('fit');
    }
}

function toggleFullscreen() {
    var overlay = document.getElementById('imgOverlay');
    if (!document.fullscreenElement) {
        overlay.requestFullscreen().catch(function(err) {
            console.log('Fullscreen error:', err);
        });
    } else {
        document.exitFullscreen();
    }
}

// === Mouse Wheel Zoom (Center-based) ===
document.getElementById('imageContainer').addEventListener('wheel', function(e) {
    e.preventDefault();
    var rect = this.getBoundingClientRect();
    // 화면 중앙을 기준으로 줌
    var centerX = rect.width / 2;
    var centerY = rect.height / 2;

    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);

    if (e.deltaY < 0) {
        // Zoom in
        var nextIndex = Math.min(currentIndex + 1, ZOOM_LEVELS.length - 1);
        setZoom(ZOOM_LEVELS[nextIndex], centerX, centerY);
    } else {
        // Zoom out
        var prevIndex = Math.max(currentIndex - 1, 0);
        if (prevIndex === 0 && currentIndex === 0 && zoomState.level !== 'fit') {
            setZoom('fit');
        } else {
            setZoom(ZOOM_LEVELS[prevIndex], centerX, centerY);
        }
    }
}, { passive: false });

// === Drag/Pan + Click-to-Close ===
var overlayImg = document.getElementById('overlayImg');
var imageContainer = document.getElementById('imageContainer');
var clickStartPos = { x: 0, y: 0 };
overlayImg.addEventListener('mousedown', function(e) {
    clickStartPos.x = e.clientX;
    clickStartPos.y = e.clientY;
    if (zoomState.level === 'fit') return;
    zoomState.isDragging = true;
    zoomState.startX = e.clientX - zoomState.offsetX;
    zoomState.startY = e.clientY - zoomState.offsetY;
    this.classList.add('dragging');
    e.preventDefault();
});
document.addEventListener('mousemove', function(e) {
    if (!zoomState.isDragging) return;
    zoomState.offsetX = e.clientX - zoomState.startX;
    zoomState.offsetY = e.clientY - zoomState.startY;
    applyTransform();
});
document.addEventListener('mouseup', function() {
    if (zoomState.isDragging) {
        zoomState.isDragging = false;
        var img = document.getElementById('overlayImg');
        if (img) img.classList.remove('dragging');
    }
});

// Click on image = close viewer (drag 5px+ moved = ignore)
overlayImg.addEventListener('click', function(e) {
    var dx = Math.abs(e.clientX - clickStartPos.x);
    var dy = Math.abs(e.clientY - clickStartPos.y);
    if (dx < 5 && dy < 5) {
        closeImageViewer();
    }
});

// Click on background (imageContainer) = close viewer
imageContainer.addEventListener('click', function(e) {
    if (e.target === imageContainer) {
        closeImageViewer();
    }
});

// === Control Panel Buttons ===
document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
document.getElementById('fitBtn').addEventListener('click', function() { setZoom('fit'); });
document.getElementById('fullscreenBtn').addEventListener('click', toggleFullscreen);
document.getElementById('closeBtn').addEventListener('click', closeImageViewer);
document.getElementById('prevBtn').addEventListener('click', function(e) { e.stopPropagation(); navImage(-1); });
document.getElementById('nextBtn').addEventListener('click', function(e) { e.stopPropagation(); navImage(1); });
document.getElementById('deleteImgBtn').addEventListener('click', function(e) { e.stopPropagation(); deleteCurrentImage(); });

// === Window resize handler ===
window.addEventListener('resize', function() {
    if (!document.getElementById('imgOverlay').classList.contains('hidden')) {
        if (zoomState.level === 'fit') {
            fitToScreen();
        }
    }
});

function viewFiles(orderNo) {
    viewerOrderNo = orderNo;
    fetch('/dashboard/proofs/api.php?action=files&order_no=' + orderNo)
        .then(r => r.json())
        .then(data => {
            if (!data.files || data.files.length === 0) return;
            var images = data.files.filter(f => /\.(jpg|jpeg|png|gif)$/i.test(f.name));
            if (images.length > 0) {
                viewerImages = images.map(function(f) {
                    return {
                        url: f.url + '?t=' + Date.now(),
                        name: f.name,
                        date: f.date || '',
                        orderNo: orderNo,
                        filePath: f.path
                    };
                });
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
    var thumbnailBar = document.getElementById('thumbnailBar');
    container.textContent = '';

    if (viewerImages.length <= 1) {
        thumbnailBar.classList.add('hidden');
        return;
    }

    thumbnailBar.classList.remove('hidden');

    viewerImages.forEach(function(img, i) {
        var thumb = document.createElement('img');
        thumb.src = img.url;
        thumb.dataset.idx = i;
        thumb.className = 'thumb-item' + (i === 0 ? ' active' : '');
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
    var overlayImg = document.getElementById('overlayImg');
    var statusBar = document.getElementById('statusBar');

    // Reset zoom state
    zoomState.level = 'fit';
    zoomState.scale = 1;
    zoomState.offsetX = 0;
    zoomState.offsetY = 0;

    overlayImg.src = img.url;
    overlayImg.style.transform = '';
    overlayImg.style.maxWidth = '';
    overlayImg.style.maxHeight = '';

    var total = viewerImages.length;
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var counter = document.getElementById('imgCounter');
    var fileName = document.getElementById('imgFileName');
    var fileDate = document.getElementById('imgFileDate');

    statusBar.classList.remove('hidden');
    counter.textContent = (viewerIndex + 1) + ' / ' + total;
    fileName.textContent = img.name;
    fileDate.textContent = img.date;

    // Image load handler - fit to screen after load
    overlayImg.onload = function() {
        fitToScreen();
    };

    // Image error handler
    overlayImg.onerror = function() {
        fileName.textContent += ' (이미지 로드 실패)';
    };

    // Update navigation buttons
    if (total > 1) {
        prevBtn.classList.toggle('hidden', viewerIndex === 0);
        nextBtn.classList.toggle('hidden', viewerIndex === total - 1);
        document.querySelectorAll('.thumb-item').forEach(function(el, i) {
            if (i === viewerIndex) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    } else {
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
    }

    // 교정확정 상태 확인
    checkProofreadingStatus();
}

function navImage(dir) {
    var next = viewerIndex + dir;
    if (next < 0 || next >= viewerImages.length) return;
    viewerIndex = next;
    showImage();
}

function closeImageViewer() {
    document.getElementById('imgOverlay').classList.add('hidden');
    document.getElementById('statusBar').classList.add('hidden');
    document.getElementById('thumbnailBar').classList.add('hidden');

    var overlayImg = document.getElementById('overlayImg');
    overlayImg.src = '';
    overlayImg.style.transform = '';
    overlayImg.style.maxWidth = '';
    overlayImg.style.maxHeight = '';

    // Reset zoom state
    zoomState = {
        level: 'fit',
        scale: 1,
        offsetX: 0,
        offsetY: 0,
        isDragging: false,
        startX: 0,
        startY: 0
    };

    viewerImages = [];
}

// === 교정확정 관련 함수 ===
function checkProofreadingStatus() {
    if (!viewerOrderNo) return;

    fetch('/dashboard/proofs/api.php?action=check_proof_status&order_no=' + viewerOrderNo)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.confirmed) {
                showProofreadingCompleted();
            } else {
                showProofreadingButton();
            }
        })
        .catch(function() {
            showProofreadingButton();
        });
}

function showProofreadingButton() {
    document.getElementById('proofConfirmContent').style.display = 'block';
    document.getElementById('proofConfirmedMsg').style.display = 'none';
}

function showProofreadingCompleted() {
    document.getElementById('proofConfirmContent').style.display = 'none';
    document.getElementById('proofConfirmedMsg').style.display = 'block';
}

function confirmProofreading() {
    if (!viewerOrderNo) return;

    if (!confirm('오탈자 및 전체를 잘 확인 했습니다.\n인쇄진행해주세요.\n\n인쇄 진행 후에는 더이상 수정할 수 없습니다.\n\n교정확정 하시겠습니까?')) {
        return;
    }

    if (!confirm('⚠️ 최종 확인\n\n교정확정 후에는 취소할 수 없습니다.\n정말 인쇄를 진행하시겠습니까?')) {
        return;
    }

    var btn = document.getElementById('proofConfirmBtn');
    btn.disabled = true;
    btn.textContent = '처리중...';

    fetch('/dashboard/proofs/api.php?action=confirm_proofreading', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_no=' + viewerOrderNo
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('교정확정이 완료되었습니다.', 'success');
            showProofreadingCompleted();
        } else {
            showToast(data.message || '처리 중 오류가 발생했습니다.', 'error');
            btn.disabled = false;
            btn.textContent = '교정확정';
        }
    })
    .catch(function() {
        showToast('네트워크 오류', 'error');
        btn.disabled = false;
        btn.textContent = '교정확정';
    });
}

// 교정확정 버튼 이벤트 리스너
document.getElementById('proofConfirmBtn').addEventListener('click', confirmProofreading);

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
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('삭제 완료', 'success');
            viewerImages.splice(viewerIndex, 1);

            if (viewerImages.length === 0) {
                closeImageViewer();
                updateRowFileCount(img.orderNo);
            } else {
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
    .catch(function() { showToast('네트워크 오류', 'error'); });
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
            + '<input type="tel" id="modalPhoneInput" placeholder="010-0000-0000" '
            + 'class="w-28 px-1.5 py-0.5 text-xs border border-orange-300 rounded focus:ring-1 focus:ring-blue-400 outline-none bg-orange-50" maxlength="20" '
            + 'onkeydown="if(event.key===\'Enter\'){saveModalPhone(' + orderNo + ')}">'
            + '<button onclick="saveModalPhone(' + orderNo + ')" class="px-2 py-0.5 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">저장</button>';
        var modalInput = document.getElementById('modalPhoneInput');
        if (modalInput && window.applyPhoneFormat) window.applyPhoneFormat(modalInput);
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
            var icons = {'.pdf':'PDF','.ai':'AI','.psd':'PSD','.zip':'ZIP'};
            thumb = '<span class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded text-base flex-shrink-0">' + (icons[ext]||'📎') + '</span>';
        }
        html += '<div class="flex items-center gap-2 p-2 bg-gray-50 rounded text-xs group">'
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
                    var existingBtn = actionTd.querySelector('[data-view-btn]');
                    if (!existingBtn) {
                        var viewBtn = document.createElement('button');
                        viewBtn.className = 'relative px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100';
                        viewBtn.title = '교정파일 보기';
                        viewBtn.textContent = '보기';
                        viewBtn.setAttribute('data-view-btn', '1');
                        viewBtn.onclick = function() { viewFiles(orderNo); };
                        actionTd.insertBefore(viewBtn, actionTd.firstChild);
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
