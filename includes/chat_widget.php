<?php
/**
 * 채팅 위젯 - 모든 페이지에 포함
 * 채팅 위젯 + AI 챗봇 듀얼 인스턴스
 * 3-State 미니 채팅 위젯 (2026-03-07)
 */

// 로그인 유저명 가져오기 (quote_gauge.php 패턴)
require_once __DIR__ . '/../db.php';
$_chat_user_name = '';
if (!empty($_SESSION['user_id'])) {
    $_chat_ustmt = mysqli_prepare($db, "SELECT name FROM users WHERE id = ?");
    $_chat_uid = intval($_SESSION['user_id']);
    mysqli_stmt_bind_param($_chat_ustmt, "i", $_chat_uid);
    mysqli_stmt_execute($_chat_ustmt);
    $_chat_ures = mysqli_stmt_get_result($_chat_ustmt);
    $_chat_urow = mysqli_fetch_assoc($_chat_ures);
    if ($_chat_urow) $_chat_user_name = $_chat_urow['name'] ?? '';
    mysqli_stmt_close($_chat_ustmt);
}
?>
<script>window._chatUserName=<?php echo json_encode($_chat_user_name, JSON_UNESCAPED_UNICODE); ?>;</script>
<link rel="stylesheet" href="/chat/chat.css?v=20260307a">
<script src="/chat/chat.js?v=20260307a"></script>
<script>
(function() {
    function initWidgets() {
        if (window.chatWidgetInitialized) return;
        window.chatWidgetInitialized = true;
        window.chatWidget = new ChatWidget({ mode: 'chat' });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidgets);
    } else {
        initWidgets();
    }
})();
</script>
