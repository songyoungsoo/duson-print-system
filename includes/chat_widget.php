<?php
/**
 * 채팅 위젯 - 모든 페이지에 포함
 * 우측 하단에 플로팅 채팅 버튼 표시
 *
 * 사용법: 페이지 하단 </body> 태그 직전에 포함
 * <?php include __DIR__ . '/../includes/chat_widget.php'; ?>
 */
?>
<!-- 채팅 시스템 -->
<link rel="stylesheet" href="/chat/chat.css?v=20260218b">
<script src="/chat/chat.js?v=20260218b"></script>
<script>
// DOMContentLoaded가 이미 발생했다면 즉시 초기화
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.chatWidgetInitialized) {
            window.chatWidget = new ChatWidget();
            window.chatWidgetInitialized = true;
        }
    });
} else {
    if (!window.chatWidgetInitialized) {
        window.chatWidget = new ChatWidget();
        window.chatWidgetInitialized = true;
    }
}
</script>
