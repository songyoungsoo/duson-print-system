<?php
/**
 * 채팅 위젯 - 모든 페이지에 포함
 * 채팅 위젯 + AI 챗봇 듀얼 인스턴스
 */
?>
<link rel="stylesheet" href="/chat/chat.css?v=20260303a">
<script src="/chat/chat.js?v=20260303a"></script>
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
