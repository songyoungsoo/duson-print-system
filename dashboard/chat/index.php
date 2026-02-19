<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$unread_count = 0;
$recent_rooms = [];
try {
    $r = mysqli_query($db, "SELECT COUNT(*) as cnt FROM chatmessages WHERE isread = 0 AND senderid != 'admin'");
    if ($r) $unread_count = intval(mysqli_fetch_assoc($r)['cnt']);

    $r = mysqli_query($db, "
        SELECT cr.id, cr.roomname, cr.createdat,
               (SELECT COUNT(*) FROM chatmessages cm WHERE cm.roomid = cr.id AND cm.isread = 0 AND cm.senderid != 'admin') as unread,
               (SELECT cm2.message FROM chatmessages cm2 WHERE cm2.roomid = cr.id ORDER BY cm2.id DESC LIMIT 1) as last_message,
               (SELECT cm3.createdat FROM chatmessages cm3 WHERE cm3.roomid = cr.id ORDER BY cm3.id DESC LIMIT 1) as last_message_at
        FROM chatrooms cr
        WHERE cr.isactive = 1
        ORDER BY last_message_at DESC
        LIMIT 20
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
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">💬 채팅 관리</h1>
                <p class="text-sm text-gray-600">고객 채팅 응대 · 미읽은 메시지: <span class="font-bold text-purple-600"><?php echo $unread_count; ?>건</span></p>
            </div>
            <a href="#" onclick="openStaffChat(); return false;" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                채팅창 열기
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-purple-600"><?php echo $unread_count; ?></div>
                <div class="text-xs text-gray-500">미읽은 메시지</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <div class="text-2xl font-bold text-gray-900"><?php echo count($recent_rooms); ?></div>
                <div class="text-xs text-gray-500">활성 채팅방</div>
            </div>
            <div class="bg-white rounded-lg shadow p-3 text-center">
                <a href="#" onclick="openStaffChat(); return false;" class="text-2xl">🖥️</a>
                <div class="text-xs text-gray-500 mt-1">채팅창 열기</div>
            </div>
        </div>

        <!-- Recent Chat Rooms -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">최근 채팅방</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (empty($recent_rooms)): ?>
                <div class="px-4 py-8 text-center text-sm text-gray-400">채팅방이 없습니다.</div>
                <?php endif; ?>
                <?php foreach ($recent_rooms as $room): ?>
                <a href="#" onclick="openStaffChat(); return false;" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-lg mr-3">💬</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($room['roomname'] ?: '채팅방 #' . $room['id']); ?></span>
                            <?php if ($room['unread'] > 0): ?>
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?php echo $room['unread']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars(mb_substr($room['last_message'] ?? '', 0, 50)); ?></p>
                    </div>
                    <div class="text-xs text-gray-400 ml-2 whitespace-nowrap">
                        <?php echo $room['last_message_at'] ? date('m/d H:i', strtotime($room['last_message_at'])) : ''; ?>
                    </div>
                </a>
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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
