<?php
$_aibot_hour = (int)date('H');
$_aibot_min = (int)date('i');
$_aibot_is_after_hours = ($_aibot_hour >= 18 && $_aibot_min >= 30) || $_aibot_hour >= 19 || $_aibot_hour < 9;
?>
<div id="ai-chatbot-widget" style="display:none; position:fixed; bottom:20px; right:80px; z-index:99998; font-family:'Pretendard Variable','Noto Sans KR',sans-serif;">
    <button id="ai-chatbot-toggle" onclick="aiChatToggle()" style="width:60px;height:60px;border-radius:50%;background:linear-gradient(145deg,#6366f1,#4f46e5);border:none;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.45);display:flex;align-items:center;justify-content:center;position:relative;transition:transform .2s;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/><line x1="9" y1="22" x2="15" y2="22"/><line x1="10" y1="2" x2="10" y2="7"/><line x1="14" y1="2" x2="14" y2="7"/></svg>
        <span style="position:absolute;top:-2px;right:-2px;width:14px;height:14px;background:#22c55e;border-radius:50%;border:2px solid #fff;"></span>
    </button>
    <span id="ai-chatbot-label" style="position:absolute;top:-22px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:10px;font-weight:600;color:#fff;background:#6366f1;padding:3px 8px;border-radius:10px;pointer-events:none;">AI 상담</span>

    <div id="ai-chatbot-window" style="display:none;position:absolute;bottom:70px;right:0;width:370px;height:520px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.18);overflow:hidden;flex-direction:column;animation:aiChatSlideUp .25s ease;">
        <div style="background:linear-gradient(135deg,#6366f1,#4f46e5);padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:#fff;font-weight:700;font-size:15px;">두손 AI 상담봇</div>
                <div style="color:rgba(255,255,255,.7);font-size:11px;">영업시간 외 AI가 안내해드립니다</div>
            </div>
            <button onclick="aiChatToggle()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:22px;cursor:pointer;padding:4px;">&times;</button>
        </div>

        <div id="ai-chat-messages" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;">
            <div style="display:flex;gap:8px;">
                <div style="width:30px;height:30px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/></svg>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px 14px 14px 4px;padding:10px 14px;max-width:75%;font-size:13px;color:#334155;line-height:1.6;">
                    안녕하세요! 두손기획인쇄 AI 상담봇입니다. 😊<br><br>현재 영업시간 외입니다. 인쇄물 가격이 궁금하시면 편하게 물어봐주세요!
                </div>
            </div>
        </div>

        <div style="padding:10px 12px;border-top:1px solid #e2e8f0;background:#fff;">
            <div id="ai-chat-quickbtns" style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px;">
                <button onclick="aiChatQuick('명함')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">명함</button>
                <button onclick="aiChatQuick('전단지')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">전단지</button>
                <button onclick="aiChatQuick('스티커')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">스티커</button>
                <button onclick="aiChatQuick('봉투')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">봉투</button>
                <button onclick="aiChatQuick('카다록')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">카다록</button>
                <button onclick="aiChatQuick('포스터')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">포스터</button>
                <button onclick="aiChatQuick('상품권')" style="padding:4px 10px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:11px;color:#475569;cursor:pointer;">상품권</button>
            </div>
            <form id="ai-chat-form" onsubmit="aiChatSend(event)" style="display:flex;gap:8px;">
                <input id="ai-chat-input" type="text" placeholder="궁금한 상품을 입력하세요..." style="flex:1;padding:10px 14px;border:1px solid #d1d5db;border-radius:22px;font-size:13px;font-family:inherit;outline:none;" autocomplete="off">
                <button type="submit" id="ai-chat-sendbtn" style="width:40px;height:40px;border-radius:50%;background:#6366f1;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes aiChatSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
#ai-chatbot-toggle:hover { transform:scale(1.08); }
@media(max-width:768px){
    #ai-chatbot-widget { right:10px; bottom:80px; }
    #ai-chatbot-window { width:calc(100vw - 20px); right:-10px; bottom:65px; height:70vh; }
}
</style>

<script>
(function(){
    var widget = document.getElementById('ai-chatbot-widget');
    if (!widget) return;

    var serverAfterHours = <?php echo $_aibot_is_after_hours ? 'true' : 'false'; ?>;
    var messages = [];
    var loading = false;

    function isAfterHours() {
        var now = new Date();
        var h = now.getHours(), m = now.getMinutes();
        return (h >= 18 && m >= 30) || h >= 19 || h < 9;
    }

    function checkAndShow() {
        if (isAfterHours() || serverAfterHours) {
            widget.style.display = 'block';
        } else {
            widget.style.display = 'none';
            var win = document.getElementById('ai-chatbot-window');
            if (win) win.style.display = 'none';
        }
    }

    checkAndShow();
    setInterval(checkAndShow, 60000);

    window.aiChatToggle = function() {
        var win = document.getElementById('ai-chatbot-window');
        if (!win) return;
        var isOpen = win.style.display === 'flex';
        win.style.display = isOpen ? 'none' : 'flex';
        if (!isOpen) {
            var input = document.getElementById('ai-chat-input');
            if (input) input.focus();
        }
    };

    function appendMessage(role, content) {
        var container = document.getElementById('ai-chat-messages');
        if (!container) return;
        var div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:8px;' + (role === 'user' ? 'justify-content:flex-end;' : '');

        var formatted = content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        formatted = formatted.replace(/\*\*(.+?)\*\*/g, '<strong style="color:#6366f1;">$1</strong>');
        formatted = formatted.replace(/\n/g, '<br>');

        if (role === 'user') {
            div.innerHTML = '<div style="background:#6366f1;color:#fff;border-radius:14px 14px 4px 14px;padding:10px 14px;max-width:75%;font-size:13px;line-height:1.6;">' + formatted + '</div>';
        } else {
            div.innerHTML = '<div style="width:30px;height:30px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/></svg></div><div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px 14px 14px 4px;padding:10px 14px;max-width:75%;font-size:13px;color:#334155;line-height:1.6;">' + formatted + '</div>';
        }
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function showTyping() {
        var container = document.getElementById('ai-chat-messages');
        var div = document.createElement('div');
        div.id = 'ai-chat-typing';
        div.style.cssText = 'display:flex;gap:8px;';
        div.innerHTML = '<div style="width:30px;height:30px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.5"><path d="M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7z"/></svg></div><div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:10px 14px;display:flex;gap:4px;"><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s infinite alternate;"></span><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .15s infinite alternate;"></span><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .3s infinite alternate;"></span></div>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function removeTyping() {
        var el = document.getElementById('ai-chat-typing');
        if (el) el.remove();
    }

    window.aiChatSend = function(e) {
        if (e) e.preventDefault();
        var input = document.getElementById('ai-chat-input');
        var msg = input.value.trim();
        if (!msg || loading) return;

        input.value = '';
        loading = true;
        messages.push({ role: 'user', content: msg });
        appendMessage('user', msg);
        showTyping();

        var fd = new FormData();
        fd.append('action', 'chat');
        fd.append('message', msg);
        fd.append('history', JSON.stringify(messages.slice(0, -1)));

        fetch('/api/ai_chat.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                removeTyping();
                if (data.error) {
                    appendMessage('assistant', '죄송합니다. 오류가 발생했습니다: ' + data.error);
                } else if (data.message) {
                    messages.push({ role: 'assistant', content: data.message });
                    appendMessage('assistant', data.message);
                }
            })
            .catch(function() {
                removeTyping();
                appendMessage('assistant', '네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
            })
            .finally(function() {
                loading = false;
            });
    };

    window.aiChatQuick = function(text) {
        var input = document.getElementById('ai-chat-input');
        input.value = text;
        aiChatSend();
    };
})();
</script>
<style>
@keyframes aiBounce { to { transform: translateY(-4px); opacity: .5; } }
</style>
