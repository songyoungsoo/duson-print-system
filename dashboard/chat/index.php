<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

// === 페이지 로드 시 30일 이상 된 미읽은 메시지 자동 읽음 처리 ===
$auto_expire_count = 0;
$autoExpireQuery = "UPDATE chatmessages SET isread = 1 WHERE isread = 0 AND createdat < DATE_SUB(NOW(), INTERVAL 30 DAY)";
if (mysqli_query($db, $autoExpireQuery)) {
    $auto_expire_count = mysqli_affected_rows($db);
}

// === 통계 조회 ===
$unread_count = 0;
$total_rooms = 0;
$empty_room_count = 0;
$recent_rooms = [];

try {
    // 미읽은 메시지 수 (staff, system, ai_bot 제외, 활성 채팅방만)
    $r = mysqli_query($db, "SELECT COUNT(*) as cnt FROM chatmessages cm
                            INNER JOIN chatrooms cr ON cr.id = cm.roomid AND cr.isactive = 1
                            WHERE cm.isread = 0 AND cm.senderid NOT LIKE 'staff%' AND cm.senderid != 'system' AND cm.senderid != 'ai_bot'");
    if ($r) $unread_count = intval(mysqli_fetch_assoc($r)['cnt']);

    // 활성 채팅방 수
    $r = mysqli_query($db, "SELECT COUNT(*) as cnt FROM chatrooms WHERE isactive = 1");
    if ($r) $total_rooms = intval(mysqli_fetch_assoc($r)['cnt']);

    // 빈 채팅방 수 (메시지 0건)
    $r = mysqli_query($db, "SELECT COUNT(*) as cnt FROM chatrooms cr
                            LEFT JOIN chatmessages cm ON cm.roomid = cr.id
                            WHERE cr.isactive = 1 AND cm.id IS NULL");
    if ($r) $empty_room_count = intval(mysqli_fetch_assoc($r)['cnt']);

    // 채팅방 목록 (메시지 수 포함)
    $r = mysqli_query($db, "
        SELECT cr.id, cr.roomname, cr.createdat, cr.updatedat,
               (SELECT COUNT(*) FROM chatmessages cm WHERE cm.roomid = cr.id AND cm.isread = 0 AND cm.senderid NOT LIKE 'staff%' AND cm.senderid != 'system' AND cm.senderid != 'ai_bot') as unread,
               (SELECT COUNT(*) FROM chatmessages cm WHERE cm.roomid = cr.id) as total_messages,
               (SELECT cm2.message FROM chatmessages cm2 WHERE cm2.roomid = cr.id ORDER BY cm2.id DESC LIMIT 1) as last_message,
               (SELECT cm3.createdat FROM chatmessages cm3 WHERE cm3.roomid = cr.id ORDER BY cm3.id DESC LIMIT 1) as last_message_at,
               (SELECT p.username FROM chatparticipants p WHERE p.roomid = cr.id AND p.isadmin = 0 LIMIT 1) as customer_name
        FROM chatrooms cr
        WHERE cr.isactive = 1
        ORDER BY last_message_at DESC
        LIMIT 24
    ");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) {
            $recent_rooms[] = $row;
        }
    }
} catch (Throwable $e) {}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">&#x1F4AC; 채팅 관리</h1>
                <p class="text-sm text-gray-600">고객 채팅 응대 · 미읽은 메시지: <span class="font-bold text-purple-600"><?php echo $unread_count; ?>건</span>
                    <?php if ($auto_expire_count > 0): ?>
                    <span class="text-xs text-orange-500 ml-2">(30일 경과 <?php echo $auto_expire_count; ?>건 자동 읽음 처리됨)</span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="#" onclick="openStaffChat(); return false;" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                채팅창 열기
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-purple-600"><?php echo $unread_count; ?></div>
                <div class="text-xs text-gray-500">미읽은 메시지</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-900"><?php echo $total_rooms; ?></div>
                <div class="text-xs text-gray-500">활성 채팅방</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold <?php echo $empty_room_count > 0 ? 'text-orange-500' : 'text-gray-400'; ?>"><?php echo $empty_room_count; ?></div>
                <div class="text-xs text-gray-500">빈 채팅방</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <a href="#" onclick="openStaffChat(); return false;" class="text-2xl">&#x1F5A5;&#xFE0F;</a>
                <div class="text-xs text-gray-500 mt-1">채팅창 열기</div>
            </div>
        </div>

        <!-- Management Buttons -->
        <div class="flex flex-wrap gap-2 mb-4">
            <?php if ($unread_count > 0): ?>
            <button onclick="adminAction('mark_all_read', '미읽은 메시지 <?php echo $unread_count; ?>건을 모두 읽음 처리하시겠습니까?')"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                모두 읽음 (<?php echo $unread_count; ?>건)
            </button>
            <?php endif; ?>
            <?php if ($empty_room_count > 0): ?>
            <button onclick="adminAction('cleanup_empty', '메시지가 없는 빈 채팅방 <?php echo $empty_room_count; ?>개를 삭제하시겠습니까?')"
                    class="inline-flex items-center px-3 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                빈 채팅방 정리 (<?php echo $empty_room_count; ?>개)
            </button>
            <?php endif; ?>
        </div>

        <!-- Chat Room List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">채팅방 목록</h3>
                <span class="text-xs text-gray-400"><?php echo count($recent_rooms); ?>개</span>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (empty($recent_rooms)): ?>
                <div class="px-4 py-8 text-center text-sm text-gray-400">채팅방이 없습니다.</div>
                <?php endif; ?>
                <?php foreach ($recent_rooms as $room): ?>
                <div class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors" id="room-row-<?php echo $room['id']; ?>">
                    <!-- Chat icon + room info -->
                    <a href="#" onclick="openStaffChat(); return false;" class="flex items-center flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full <?php echo $room['unread'] > 0 ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-400'; ?> flex items-center justify-center text-lg mr-3 flex-shrink-0">&#x1F4AC;</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($room['customer_name'] ?: $room['roomname'] ?: '채팅방 #' . $room['id']); ?></span>
                                <?php if ($room['unread'] > 0): ?>
                                <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0"><?php echo $room['unread']; ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars(mb_substr($room['last_message'] ?? '(메시지 없음)', 0, 50)); ?></p>
                        </div>
                    </a>
                    <!-- Meta + Close Button -->
                    <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                        <div class="text-right">
                            <div class="text-xs text-gray-400 whitespace-nowrap">
                                <?php echo $room['last_message_at'] ? date('m/d H:i', strtotime($room['last_message_at'])) : ''; ?>
                            </div>
                            <div class="text-xs text-gray-300"><?php echo intval($room['total_messages']); ?>건</div>
                        </div>
                        <button onclick="closeRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars(addslashes($room['customer_name'] ?: $room['roomname'] ?: '채팅방 #' . $room['id']), ENT_QUOTES); ?>')"
                                class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="채팅방 닫기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
var staffChatWin = null;
function openStaffChat() {
    if (staffChatWin && !staffChatWin.closed) {
        staffChatWin.focus();
        return;
    }
    staffChatWin = window.open(
        '/chat/admin.php',
        'staff_chat',
        'width=600,height=550,left=0,top=0,resizable=yes,scrollbars=yes'
    );
}

function adminAction(action, confirmMsg) {
    if (!confirm(confirmMsg)) return;

    var formData = new FormData();
    formData.append('action', 'admin_' + action);

    fetch('/chat/api.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert('오류: ' + (res.message || '처리 실패'));
            }
        })
        .catch(function(err) {
            alert('네트워크 오류: ' + err.message);
        });
}

function closeRoom(roomId, roomName) {
    if (!confirm('"' + roomName + '" 채팅방을 닫으시겠습니까?\n(목록에서 제거되며, 메시지는 읽음 처리됩니다)')) return;

    var formData = new FormData();
    formData.append('action', 'admin_close_room');
    formData.append('room_id', roomId);

    fetch('/chat/api.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                var row = document.getElementById('room-row-' + roomId);
                if (row) {
                    row.style.transition = 'opacity 0.3s, max-height 0.3s';
                    row.style.opacity = '0';
                    row.style.maxHeight = '0';
                    row.style.overflow = 'hidden';
                    setTimeout(function() { row.remove(); }, 300);
                }
                // Update sidebar badge
                if (typeof pollUnreadRooms === 'function') pollUnreadRooms();
            } else {
                alert('오류: ' + (res.message || '처리 실패'));
            }
        })
        .catch(function(err) {
            alert('네트워크 오류: ' + err.message);
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
