<div id="ai-chatbot-widget" style="display:none; position:fixed; bottom:20px; right:80px; z-index:99998; font-family:'Pretendard Variable','Noto Sans KR',sans-serif;">
    <button id="ai-chatbot-toggle" onclick="aiChatToggle()" style="width:79px;height:79px;border-radius:50%;background:linear-gradient(145deg,#6366f1,#4f46e5);border:none;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.45);display:flex;align-items:center;justify-content:center;position:relative;transition:transform .2s;">
        <span style="color:#fff;font-weight:900;font-size:20px;letter-spacing:-0.5px;line-height:.95;text-align:center;">야간<br>당번</span>
        <span style="position:absolute;top:0;right:0;width:16px;height:16px;background:#22c55e;border-radius:50%;border:2px solid #fff;"></span>
    </button>
    <span id="ai-chatbot-label" style="position:absolute;top:-24px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:11px;font-weight:600;color:#fff;background:#6366f1;padding:3px 10px;border-radius:10px;pointer-events:none;">AI 상담</span>

    <div id="ai-chatbot-window" style="display:none;position:fixed;width:310px;height:420px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,.18);overflow:hidden;flex-direction:column;animation:aiChatSlideUp .25s ease;z-index:99999;">
        <div data-drag-handle style="background:linear-gradient(135deg,#6366f1,#4f46e5);padding:14px 16px;display:flex;align-items:center;gap:12px;cursor:move;user-select:none;">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-weight:900;font-size:11px;letter-spacing:-0.5px;line-height:.95;text-align:center;">야간<br>당번</span>
            </div>
            <div style="flex:1;">
                <div style="color:#fff;font-weight:700;font-size:14px;">두손 AI 상담봇</div>
                <div style="color:rgba(255,255,255,.7);font-size:10px;">영업시간 외 AI가 안내해드립니다</div>
            </div>
            <button onclick="aiChatToggle()" style="background:none;border:none;color:rgba(255,255,255,.7);font-size:22px;cursor:pointer;padding:4px;">&times;</button>
        </div>

        <div id="ai-chat-messages" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;">
            <div style="display:flex;gap:8px;">
                <div style="width:30px;height:30px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#6366f1;font-weight:900;font-size:8px;letter-spacing:-0.3px;line-height:.95;text-align:center;">야간<br>당번</span>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px 14px 14px 4px;padding:10px 14px;max-width:75%;font-size:12px;color:#334155;line-height:1.6;">
                    안녕하세요! 두손기획인쇄 AI 상담봇입니다. 😊<br><br>현재 영업시간 외입니다. 인쇄물 가격이 궁금하시면 편하게 물어봐주세요!<br>직접 주문하시면 접수가능합니다.
                </div>
            </div>
        </div>

        <div style="padding:10px 12px;border-top:1px solid #e2e8f0;background:#fff;">
            <div id="ai-chat-quickbtns" style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px;">
                <button onclick="aiChatQuick('스티커')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">스티커/라벨</button>
                <button onclick="aiChatQuick('전단지')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">전단지/리플렛</button>
                <button onclick="aiChatQuick('명함')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">명함/쿠폰</button>
                <button onclick="aiChatQuick('자석스티커')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">자석스티커</button>
                <button onclick="aiChatQuick('봉투')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">봉투</button>
                <button onclick="aiChatQuick('카다록')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">카다록</button>
                <button onclick="aiChatQuick('포스터')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">포스터</button>
                <button onclick="aiChatQuick('양식지')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">양식지</button>
                <button onclick="aiChatQuick('상품권')" style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;font-size:10px;color:#475569;cursor:pointer;">상품권</button>
            </div>
            <form id="ai-chat-form" onsubmit="aiChatSend(event)" style="display:flex;gap:8px;">
                <input id="ai-chat-input" type="text" placeholder="궁금한 상품을 선택 또는 입력하세요" style="flex:1;padding:10px 14px;border:1px solid #d1d5db;border-radius:22px;font-size:12px;font-family:inherit;outline:none;" autocomplete="off">
                <button type="submit" id="ai-chat-sendbtn" style="width:40px;height:40px;border-radius:50%;background:#6366f1;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes aiChatSlideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@keyframes aiBounce { to { transform: translateY(-4px); opacity: .5; } }
#ai-chatbot-toggle:hover { transform:scale(1.08); }
#ai-chatbot-window { overscroll-behavior: contain; }
#ai-chat-messages { overscroll-behavior: contain; }
.floating-menu.fm-chat-active .fm-item[data-panel] { pointer-events: none; }
.ai-opt-btn {
    text-align:left; padding:8px 12px; background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:8px; font-size:11.5px; color:#334155; cursor:pointer;
    transition:all .15s; font-family:inherit; width:100%; line-height:1.4;
}
.ai-opt-btn:hover:not(:disabled) { background:#e0e7ff; border-color:#c7d2fe; }
.ai-opt-btn:disabled { cursor:default; }
.ai-opt-btn.selected { background:#c7d2fe; border-color:#a5b4fc; font-weight:600; opacity:1; }
@media(max-width:768px){
    #ai-chatbot-widget { right:10px; bottom:80px; }
    #ai-chatbot-toggle { width:63px; height:63px; }
    #ai-chatbot-toggle > span:first-child { font-size:16px; }
    #ai-chatbot-window { width:calc(100vw - 20px); height:70vh; }
}
</style>

<script>
(function(){
    var widget = document.getElementById('ai-chatbot-widget');
    if (!widget) return;

    var messages = [];
    var loading = false;
    var avatarHtml = '<div style="width:30px;height:30px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><span style="color:#6366f1;font-weight:900;font-size:8px;letter-spacing:-0.3px;line-height:.95;text-align:center;">야간<br>당번</span></div>';

    function escHtml(t) { return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function fmtMsg(text) {
        var s = escHtml(text);
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong style="color:#6366f1;">$1</strong>');
        return s.replace(/\n/g, '<br>');
    }

    function buildOpts(options) {
        if (!options || !options.length) return '';
        var h = '<div style="margin-top:8px;display:flex;flex-direction:column;gap:4px;">';
        for (var i = 0; i < options.length; i++) {
            var o = options[i];
            h += '<button class="ai-opt-btn" data-num="' + o.num + '" onclick="aiChatSelectOption(this)">' + o.num + '. ' + escHtml(o.label) + '</button>';
        }
        return h + '</div>';
    }

    function disableOpts() {
        var btns = document.querySelectorAll('#ai-chat-messages .ai-opt-btn');
        for (var i = 0; i < btns.length; i++) { btns[i].disabled = true; btns[i].style.opacity = '0.5'; }
    }

    function appendMessage(role, content, options) {
        var container = document.getElementById('ai-chat-messages');
        if (!container) return;
        var div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:8px;' + (role === 'user' ? 'justify-content:flex-end;' : '');
        var formatted = fmtMsg(content);

        if (role === 'user') {
            div.innerHTML = '<div style="background:#6366f1;color:#fff;border-radius:14px 14px 4px 14px;padding:10px 14px;max-width:75%;font-size:12px;line-height:1.6;">' + formatted + '</div>';
        } else {
            var optsHtml = buildOpts(options);
            div.innerHTML = avatarHtml + '<div style="max-width:82%;"><div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px 14px 14px 4px;padding:10px 14px;font-size:12px;color:#334155;line-height:1.6;">' + formatted + '</div>' + optsHtml + '</div>';
        }
        container.appendChild(div);
        if (role === 'assistant' && options && options.length) {
            setTimeout(function() {
                var userMsg = div.previousElementSibling;
                var target = userMsg || div;
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        } else {
            container.scrollTop = container.scrollHeight;
        }
    }

    function appendSizeInput(message) {
        var container = document.getElementById('ai-chat-messages');
        if (!container) return;
        var div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:8px;';
        div.innerHTML = avatarHtml
            + '<div style="max-width:82%;">'
            + '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px 14px 14px 4px;padding:10px 14px;font-size:12px;color:#334155;line-height:1.6;">' + fmtMsg(message) + '</div>'
            + '<div id="ai-size-widget" style="margin-top:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">'
            + '<div style="display:flex;align-items:center;gap:6px;justify-content:center;">'
            + '<input type="number" class="ai-size-garo" placeholder="가로" min="1" max="590" style="width:72px;padding:8px;border:2px solid #d1d5db;border-radius:8px;font-size:14px;text-align:center;font-family:inherit;outline:none;transition:border-color .15s;" autocomplete="off">'
            + '<span style="font-size:16px;color:#64748b;font-weight:700;">×</span>'
            + '<input type="number" class="ai-size-sero" placeholder="세로" min="1" max="590" style="width:72px;padding:8px;border:2px solid #d1d5db;border-radius:8px;font-size:14px;text-align:center;font-family:inherit;outline:none;transition:border-color .15s;" autocomplete="off">'
            + '<span style="font-size:11px;color:#94a3b8;margin-left:2px;">mm</span>'
            + '</div>'
            + '<button class="ai-size-btn" onclick="aiChatSubmitSize()" style="margin-top:8px;width:100%;padding:8px;background:#6366f1;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">확인</button>'
            + '</div></div>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        var w = div.querySelector('#ai-size-widget');
        if (w) {
            var g = w.querySelector('.ai-size-garo');
            var s = w.querySelector('.ai-size-sero');
            if (g) {
                setTimeout(function() { g.focus(); }, 100);
                g.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); if (s) s.focus(); } });
                g.addEventListener('focus', function() { this.style.borderColor = '#6366f1'; });
                g.addEventListener('blur', function() { this.style.borderColor = '#d1d5db'; });
            }
            if (s) {
                s.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); aiChatSubmitSize(); } });
                s.addEventListener('focus', function() { this.style.borderColor = '#6366f1'; });
                s.addEventListener('blur', function() { this.style.borderColor = '#d1d5db'; });
            }
        }
    }

    function showTyping() {
        var container = document.getElementById('ai-chat-messages');
        var div = document.createElement('div');
        div.id = 'ai-chat-typing';
        div.style.cssText = 'display:flex;gap:8px;';
        div.innerHTML = avatarHtml + '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:10px 14px;display:flex;gap:4px;"><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s infinite alternate;"></span><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .15s infinite alternate;"></span><span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .3s infinite alternate;"></span></div>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function removeTyping() {
        var el = document.getElementById('ai-chat-typing');
        if (el) el.remove();
    }

    function sendToBackend(msgToSend, displayText) {
        if (loading) return;
        loading = true;
        disableOpts();
        messages.push({ role: 'user', content: msgToSend });
        appendMessage('user', displayText || msgToSend);
        showTyping();

        var fd = new FormData();
        fd.append('action', 'chat');
        fd.append('message', msgToSend);
        fd.append('history', JSON.stringify(messages.slice(0, -1)));

        fetch('/api/ai_chat.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                removeTyping();
                if (data.error) {
                    appendMessage('assistant', '죄송합니다. 오류가 발생했습니다: ' + data.error);
                } else if (data.message) {
                    messages.push({ role: 'assistant', content: data.message });
                    if (data.input_type === 'sticker_size') {
                        appendSizeInput(data.message);
                    } else {
                        appendMessage('assistant', data.message, data.options || null);
                    }
                }
            })
            .catch(function() {
                removeTyping();
                appendMessage('assistant', '네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
            })
            .finally(function() {
                loading = false;
            });
    }

    window.aiChatToggle = function() {
        var win = document.getElementById('ai-chatbot-window');
        if (!win) return;
        var isOpen = win.style.display === 'flex';
        var fm = document.getElementById('floating-menu');
        if (isOpen) {
            win.style.display = 'none';
            if (fm) fm.classList.remove('fm-chat-active');
        } else {
            var winW = 310, winH = 420;
            if (window.innerWidth <= 768) { winW = window.innerWidth - 20; winH = window.innerHeight * 0.7; }
            // Right edge aligned with floating sidebar cards (right: 12px)
            var left = window.innerWidth - 12 - winW;
            var toggle = document.getElementById('ai-chatbot-toggle');
            var tRect = toggle.getBoundingClientRect();
            var top = tRect.top - winH - 10;
            if (top < 10) top = 10;
            if (left < 10) left = 10;
            win.style.left = left + 'px';
            win.style.top = top + 'px';
            win.style.right = 'auto';
            win.style.bottom = 'auto';
            win.style.display = 'flex';
            // Pause sidebar card hover while chat window overlaps
            if (fm) {
                fm.classList.add('fm-chat-active');
                fm.querySelectorAll('.fm-item.active, .fm-item.pinned').forEach(function(item) {
                    item.classList.remove('active', 'pinned');
                });
            }
            var inp = document.getElementById('ai-chat-input');
            if (inp) inp.focus();
        }
    };

    window.aiChatSend = function(e) {
        if (e) e.preventDefault();
        var input = document.getElementById('ai-chat-input');
        var msg = input.value.trim();
        if (!msg || loading) return;
        input.value = '';
        sendToBackend(msg, msg);
    };

    window.aiChatQuick = function(text) {
        if (loading) return;
        sendToBackend(text, text);
    };

    window.aiChatSelectOption = function(btn) {
        if (loading || btn.disabled) return;
        var num = btn.getAttribute('data-num');
        var label = btn.textContent.replace(/^\d+\.\s*/, '');

        // Highlight selected
        btn.classList.add('selected');

        sendToBackend(num, label);
    };

    window.aiChatSubmitSize = function() {
        var widget = document.getElementById('ai-size-widget');
        if (!widget || loading) return;
        var garo = widget.querySelector('.ai-size-garo');
        var sero = widget.querySelector('.ai-size-sero');
        if (!garo || !sero) return;
        var g = parseInt(garo.value) || 0;
        var s = parseInt(sero.value) || 0;
        if (g <= 0 || g > 590) { garo.style.borderColor = '#ef4444'; garo.focus(); return; }
        if (s <= 0 || s > 590) { sero.style.borderColor = '#ef4444'; sero.focus(); return; }
        garo.disabled = true; sero.disabled = true;
        var btn = widget.querySelector('.ai-size-btn');
        if (btn) { btn.disabled = true; btn.style.opacity = '0.5'; }
        widget.removeAttribute('id');
        sendToBackend(g + '\u00d7' + s, g + '\u00d7' + s + 'mm');
    };

    // Drag functionality for chat window
    (function initDrag() {
        var chatWin = document.getElementById('ai-chatbot-window');
        var handle = chatWin ? chatWin.querySelector('[data-drag-handle]') : null;
        if (!handle) return;
        var dragging = false, startX, startY, winX, winY;

        function onStart(cx, cy) {
            dragging = true;
            startX = cx; startY = cy;
            var r = chatWin.getBoundingClientRect();
            winX = r.left; winY = r.top;
        }
        function onMove(cx, cy) {
            if (!dragging) return;
            var nx = winX + (cx - startX), ny = winY + (cy - startY);
            if (nx < 0) nx = 0;
            if (ny < 0) ny = 0;
            if (nx + chatWin.offsetWidth > window.innerWidth) nx = window.innerWidth - chatWin.offsetWidth;
            if (ny + chatWin.offsetHeight > window.innerHeight) ny = window.innerHeight - chatWin.offsetHeight;
            chatWin.style.left = nx + 'px';
            chatWin.style.top = ny + 'px';
            chatWin.style.right = 'auto';
            chatWin.style.bottom = 'auto';
        }
        function onEnd() { dragging = false; }

        handle.addEventListener('mousedown', function(e) {
            if (e.target.tagName === 'BUTTON') return;
            onStart(e.clientX, e.clientY); e.preventDefault();
        });
        document.addEventListener('mousemove', function(e) { onMove(e.clientX, e.clientY); });
        document.addEventListener('mouseup', onEnd);

        handle.addEventListener('touchstart', function(e) {
            if (e.target.tagName === 'BUTTON') return;
            var t = e.touches[0]; onStart(t.clientX, t.clientY);
        }, { passive: true });
        document.addEventListener('touchmove', function(e) {
            if (!dragging) return;
            var t = e.touches[0]; onMove(t.clientX, t.clientY);
        }, { passive: false });
        document.addEventListener('touchend', onEnd);
    })();
})();
</script>
