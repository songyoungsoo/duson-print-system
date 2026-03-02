<!-- Mini-Chat Widget for Admin Dashboard -->
<!-- Floating quick-reply chat widget on all dashboard pages -->
<style>
/* ===== Mini-Chat Widget ===== */
.mc-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9990;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1E4E79 0%, #2D6FA8 100%);
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(30,78,121,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s, box-shadow .2s;
}
.mc-fab:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(30,78,121,0.45); }
.mc-fab.has-unread { animation: mc-pulse 2s infinite; }
@keyframes mc-pulse {
    0%,100% { box-shadow: 0 4px 16px rgba(30,78,121,0.35); }
    50% { box-shadow: 0 4px 16px rgba(30,78,121,0.35), 0 0 0 8px rgba(30,78,121,0.12); }
}
.mc-fab-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 10px;
    padding: 0 4px;
    display: none;
}

/* Panel */
.mc-panel {
    position: fixed;
    bottom: 88px;
    right: 24px;
    z-index: 9991;
    width: 340px;
    height: 480px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    display: none;
    flex-direction: column;
    overflow: hidden;
    font-family: 'Noto Sans KR', sans-serif;
}
.mc-panel.open {
    display: flex;
    animation: mc-slideUp .25s ease-out;
}
@keyframes mc-slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Header */
.mc-hdr {
    background: #1E4E79;
    color: #fff;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.mc-hdr-title {
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mc-hdr-badge {
    background: #ef4444;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 8px;
    display: none;
}
.mc-hdr-close {
    background: rgba(255,255,255,0.15);
    border: none;
    color: #fff;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.mc-hdr-close:hover { background: rgba(255,255,255,0.25); }

/* Back button (chat view) */
.mc-back {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    padding: 0 4px;
    margin-right: 6px;
    display: none;
}

/* Room List */
.mc-rooms {
    flex: 1;
    overflow-y: auto;
}
.mc-rooms::-webkit-scrollbar { width: 3px; }
.mc-rooms::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }

.mc-room {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background .12s;
    gap: 10px;
}
.mc-room:hover { background: #f8fafc; }
.mc-room-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #e8f0fe;
    color: #1E4E79;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}
.mc-room-body {
    flex: 1;
    min-width: 0;
}
.mc-room-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mc-room-msg {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}
.mc-room-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}
.mc-room-time {
    font-size: 10px;
    color: #94a3b8;
}
.mc-room-unread {
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 10px;
    padding: 0 4px;
    display: none;
}
.mc-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #94a3b8;
    font-size: 13px;
    gap: 8px;
}
.mc-empty-icon { font-size: 32px; opacity: 0.5; }

/* Chat View */
.mc-chat {
    flex: 1;
    display: none;
    flex-direction: column;
    overflow: hidden;
}
.mc-chat.active { display: flex; }

.mc-msgs {
    flex: 1;
    overflow-y: auto;
    padding: 10px 12px;
    background: #f8fafc;
}
.mc-msgs::-webkit-scrollbar { width: 3px; }
.mc-msgs::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }

.mc-msg {
    margin-bottom: 8px;
    display: flex;
    align-items: flex-end;
    gap: 6px;
}
.mc-msg.sent { flex-direction: row-reverse; }
.mc-msg-ava {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
}
.mc-msg.sent .mc-msg-ava {
    background: #1E4E79;
    color: #fff;
}
.mc-msg.ai-bot .mc-msg-ava {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 14px;
}
.mc-msg-body { max-width: 72%; }
.mc-msg-sender {
    font-size: 9px;
    color: #94a3b8;
    margin-bottom: 2px;
    padding: 0 8px;
}
.mc-msg.ai-bot .mc-msg-sender { color: #667eea; font-weight: 600; }
.mc-msg-bub {
    background: #fff;
    padding: 7px 11px;
    border-radius: 12px;
    font-size: 12.5px;
    line-height: 1.45;
    word-break: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    color: #1e293b;
}
.mc-msg.sent .mc-msg-bub {
    background: #1E4E79;
    color: #fff;
}
.mc-msg.ai-bot .mc-msg-bub {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}
.mc-msg.system .mc-msg-bub {
    background: #e0f2fe;
    color: #0369a1;
    text-align: center;
    font-size: 11px;
}
.mc-msg-time {
    font-size: 9px;
    color: #94a3b8;
    margin-top: 2px;
    padding: 0 8px;
}
.mc-msg.sent .mc-msg-time { text-align: right; }

/* Input */
.mc-input {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
    flex-shrink: 0;
}
.mc-input-field {
    flex: 1;
    padding: 7px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    font-size: 12.5px;
    outline: none;
    font-family: inherit;
    transition: border-color .15s;
}
.mc-input-field:focus { border-color: #1E4E79; }
.mc-send-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #1E4E79;
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background .15s;
}
.mc-send-btn:hover { background: #153A5A; }
.mc-send-btn svg { width: 14px; height: 14px; }

/* Loading */
.mc-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    color: #94a3b8;
    font-size: 12px;
}

/* New message flash */
.mc-room.flash {
    animation: mc-flash .6s ease;
}
@keyframes mc-flash {
    0%,100% { background: transparent; }
    50% { background: #dbeafe; }
}

/* /dashboard/chat/ 페이지에서는 미니챗 숨김 */
</style>

<!-- FAB Button -->
<button class="mc-fab" id="mcFab" title="채팅 관리">
    💬
    <span class="mc-fab-badge" id="mcFabBadge">0</span>
</button>

<!-- Panel -->
<div class="mc-panel" id="mcPanel">
    <div class="mc-hdr">
        <div class="mc-hdr-title">
            <button class="mc-back" id="mcBack" title="목록으로">←</button>
            <span id="mcTitle">💬 채팅</span>
            <span class="mc-hdr-badge" id="mcHdrBadge">0</span>
        </div>
        <button class="mc-hdr-close" id="mcClose" title="닫기">×</button>
    </div>

    <!-- Room List View -->
    <div class="mc-rooms" id="mcRooms">
        <div class="mc-empty" id="mcEmpty">
            <span class="mc-empty-icon">💬</span>
            <span>채팅방이 없습니다</span>
        </div>
    </div>

    <!-- Chat View -->
    <div class="mc-chat" id="mcChat">
        <div class="mc-msgs" id="mcMsgs"></div>
        <div class="mc-input">
            <input type="text" class="mc-input-field" id="mcMsgInput" placeholder="메시지 입력..." autocomplete="off">
            <button class="mc-send-btn" id="mcSendBtn" title="전송">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    // /dashboard/chat/ 페이지에서는 미니챗 비활성화
    if (location.pathname.indexOf('/dashboard/chat') === 0) {
        var fab = document.getElementById('mcFab');
        if (fab) fab.style.display = 'none';
        return;
    }

    // ─── State ───
    var state = {
        open: false,
        view: 'rooms',      // 'rooms' | 'chat'
        rooms: [],
        currentRoomId: null,
        currentRoomName: '',
        lastMsgId: 0,
        prevTotalUnread: -1,
        staffId: 'staff1',
        staffName: '관리자',
        msgPollTimer: null,
        prevRoomUnreads: {},
        originalTitle: document.title,
        titleFlashInterval: null
    };

    // ─── DOM refs ───
    var fab       = document.getElementById('mcFab');
    var fabBadge  = document.getElementById('mcFabBadge');
    var panel     = document.getElementById('mcPanel');
    var backBtn   = document.getElementById('mcBack');
    var titleEl   = document.getElementById('mcTitle');
    var hdrBadge  = document.getElementById('mcHdrBadge');
    var closeBtn  = document.getElementById('mcClose');
    var roomsDiv  = document.getElementById('mcRooms');
    var emptyDiv  = document.getElementById('mcEmpty');
    var chatDiv   = document.getElementById('mcChat');
    var msgsDiv   = document.getElementById('mcMsgs');
    var msgInput  = document.getElementById('mcMsgInput');
    var sendBtn   = document.getElementById('mcSendBtn');

    // ─── Audio (reuse from footer if available) ───
    var audioCtx = null;
    function playSound() {
        // Use footer's playNotificationSound if available
        if (typeof window.playNotificationSound === 'function') {
            window.playNotificationSound();
            return;
        }
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime);
            osc.frequency.setValueAtTime(660, audioCtx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.3);
        } catch(e) {}
    }

    // ─── Browser Notification ───
    function requestNotifPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    function showBrowserNotif(name, message, roomId) {
        if (!('Notification' in window)) return;
        if (Notification.permission !== 'granted') return;
        // 패널 열려있고 해당 방을 보고 있으면 알림 불필요
        if (!document.hidden && state.open && state.currentRoomId == roomId) return;

        var body = name + ': ' + (message.length > 50 ? message.substring(0, 50) + '...' : message);
        try {
            var notif = new Notification('\uD83D\uDCAC 두손기획 채팅', {
                body: body,
                icon: '/ImgFolder/infolady.png',
                tag: 'chat-room-' + roomId,
                requireInteraction: false
            });
            notif.onclick = function() {
                window.focus();
                if (!state.open) togglePanel();
                if (roomId) selectRoom(roomId);
                notif.close();
            };
            setTimeout(function() { notif.close(); }, 8000);
        } catch(e) {}
    }

    // ─── Title Flash ───
    function startTitleFlash(count) {
        stopTitleFlash();
        var original = state.originalTitle;
        var alertTitle = '(' + count + ') \uD83D\uDCAC \uC0C8 \uBA54\uC2DC\uC9C0!';
        var flip = false;
        state.titleFlashInterval = setInterval(function() {
            document.title = flip ? original : alertTitle;
            flip = !flip;
        }, 1000);
    }

    function stopTitleFlash() {
        if (state.titleFlashInterval) {
            clearInterval(state.titleFlashInterval);
            state.titleFlashInterval = null;
        }
        document.title = state.originalTitle;
    }

    // ─── Helpers ───
    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 60) return '방금';
        if (diff < 3600) return Math.floor(diff/60) + '분 전';
        if (diff < 86400) return Math.floor(diff/3600) + '시간 전';
        return (d.getMonth()+1) + '/' + d.getDate();
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
    }

    // ─── Badge Update ───
    function updateBadge(total) {
        var text = total > 99 ? '99+' : String(total);
        // FAB badge
        if (total > 0) {
            fabBadge.textContent = text;
            fabBadge.style.display = '';
            fab.classList.add('has-unread');
        } else {
            fabBadge.style.display = 'none';
            fab.classList.remove('has-unread');
        }
        // Header badge
        if (total > 0) {
            hdrBadge.textContent = text;
            hdrBadge.style.display = '';
        } else {
            hdrBadge.style.display = 'none';
        }
        // Also update sidebar badge (if exists)
        var sbBadge = document.getElementById('chat-badge-count');
        if (sbBadge) {
            if (total > 0) {
                sbBadge.textContent = text;
                sbBadge.style.display = '';
            } else {
                sbBadge.style.display = 'none';
            }
        }
    }

    // ─── Fetch Rooms ───
    function fetchRooms() {
        fetch('/chat/api.php?action=get_staff_rooms&staff_id=' + state.staffId)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) return;
                state.rooms = res.data || [];
                var totalUnread = 0;
                var newRoomUnreads = {};
                state.rooms.forEach(function(r) {
                    var unread = parseInt(r.unread_count) || 0;
                    totalUnread += unread;
                    newRoomUnreads[r.id] = unread;
                });

                updateBadge(totalUnread);

                // New message detected → sound + browser notification
                if (state.prevTotalUnread >= 0 && totalUnread > state.prevTotalUnread) {
                    playSound();
                    // If panel is closed, pulse the FAB
                    if (!state.open) {
                        fab.classList.add('has-unread');
                    }
                    // Per-room browser notifications
                    state.rooms.forEach(function(r) {
                        var prev = state.prevRoomUnreads[r.id] || 0;
                        var cur = parseInt(r.unread_count) || 0;
                        if (cur > prev) {
                            showBrowserNotif(
                                r.customer_name || '손님',
                                r.last_message || '새 메시지가 도착했습니다',
                                r.id
                            );
                        }
                    });
                    // Title flash when tab is hidden
                    if (document.hidden) {
                        startTitleFlash(totalUnread);
                    }
                }
                state.prevTotalUnread = totalUnread;
                state.prevRoomUnreads = newRoomUnreads;

                if (state.view === 'rooms') renderRooms();
            })
            .catch(function() {});
    }

    // ─── Render Rooms ───
    function renderRooms() {
        if (state.rooms.length === 0) {
            emptyDiv.style.display = 'flex';
            // Clear any existing room elements but keep empty div
            var existingRooms = roomsDiv.querySelectorAll('.mc-room');
            existingRooms.forEach(function(el) { el.remove(); });
            return;
        }
        emptyDiv.style.display = 'none';

        // Build HTML
        var html = '';
        state.rooms.forEach(function(room) {
            var name = esc(room.customer_name || '손님');
            var initial = (room.customer_name || '손님').charAt(0);
            var msg = esc(room.last_message || '');
            if (msg.length > 30) msg = msg.substring(0, 30) + '...';
            var time = timeAgo(room.last_message_time);
            var unread = parseInt(room.unread_count) || 0;

            html += '<div class="mc-room" data-room-id="' + room.id + '">'
                + '<div class="mc-room-avatar">' + esc(initial) + '</div>'
                + '<div class="mc-room-body">'
                +   '<div class="mc-room-name">' + name + '</div>'
                +   '<div class="mc-room-msg">' + (msg || '메시지 없음') + '</div>'
                + '</div>'
                + '<div class="mc-room-meta">'
                +   '<span class="mc-room-time">' + time + '</span>'
                +   '<span class="mc-room-unread" style="' + (unread > 0 ? '' : 'display:none') + '">' + unread + '</span>'
                + '</div>'
                + '</div>';
        });

        // Replace all room items (keep empty div)
        var existingRooms = roomsDiv.querySelectorAll('.mc-room');
        existingRooms.forEach(function(el) { el.remove(); });
        roomsDiv.insertAdjacentHTML('beforeend', html);

        // Attach click handlers
        roomsDiv.querySelectorAll('.mc-room').forEach(function(el) {
            el.addEventListener('click', function() {
                var rid = parseInt(this.dataset.roomId);
                selectRoom(rid);
            });
        });
    }

    // ─── Select Room ───
    function selectRoom(roomId) {
        state.currentRoomId = roomId;
        state.lastMsgId = 0;
        msgsDiv.innerHTML = '<div class="mc-loading">로딩 중...</div>';

        // Find room name
        var room = state.rooms.find(function(r) { return r.id == roomId; });
        state.currentRoomName = room ? (room.customer_name || '손님') : '채팅';

        showChatView();
        fetchMessages();
        markAsRead(roomId);

        // Start message polling for this room
        if (state.msgPollTimer) clearInterval(state.msgPollTimer);
        state.msgPollTimer = setInterval(function() {
            fetchMessages();
        }, 3000);
    }

    // ─── Show/Hide Views ───
    function showChatView() {
        state.view = 'chat';
        roomsDiv.style.display = 'none';
        chatDiv.classList.add('active');
        backBtn.style.display = 'inline';
        titleEl.textContent = state.currentRoomName;
        hdrBadge.style.display = 'none';
        setTimeout(function() { msgInput.focus(); }, 100);
    }

    function showRoomsView() {
        state.view = 'rooms';
        state.currentRoomId = null;
        state.lastMsgId = 0;
        if (state.msgPollTimer) { clearInterval(state.msgPollTimer); state.msgPollTimer = null; }
        chatDiv.classList.remove('active');
        roomsDiv.style.display = '';
        backBtn.style.display = 'none';
        titleEl.textContent = '💬 채팅';
        fetchRooms(); // Refresh room list
    }

    // ─── Fetch Messages ───
    function fetchMessages() {
        if (!state.currentRoomId) return;
        fetch('/chat/api.php?action=get_messages&room_id=' + state.currentRoomId + '&last_id=' + state.lastMsgId)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) return;
                var msgs = res.data || [];
                if (msgs.length === 0 && state.lastMsgId === 0) {
                    msgsDiv.innerHTML = '<div class="mc-loading" style="color:#94a3b8;">메시지가 없습니다</div>';
                    return;
                }
                if (state.lastMsgId === 0 && msgs.length > 0) {
                    msgsDiv.innerHTML = '';
                }
                msgs.forEach(function(msg) {
                    var msgId = parseInt(msg.id);
                    // Skip duplicates (race condition: poll + post-send fetch)
                    if (msgsDiv.querySelector('[data-msg-id="' + msgId + '"]')) return;
                    appendMessage(msg);
                    state.lastMsgId = Math.max(state.lastMsgId, msgId);
                });
                if (msgs.length > 0) {
                    scrollToBottom();
                    markAsRead(state.currentRoomId);
                }
            })
            .catch(function() {});
    }

    // ─── Append Message ───
    function appendMessage(msg) {
        var isStaff = (msg.senderid || '').indexOf('staff') === 0;
        var isAiBot = msg.senderid === 'ai_bot';
        var isSystem = msg.senderid === 'system';
        var isSent = isStaff;

        var cls = 'mc-msg';
        if (isSent) cls += ' sent';
        if (isAiBot) cls += ' ai-bot';
        if (isSystem) cls += ' system';

        var initial = isAiBot ? '🤖' : (msg.sendername || '?').charAt(0);
        var avaCls = 'mc-msg-ava' + (isAiBot ? ' ai-bot-avatar' : '');

        var content = '';
        if (msg.messagetype === 'image' && msg.filepath) {
            content = '<img src="/chat/' + esc(msg.filepath) + '" style="max-width:160px;border-radius:8px;" alt="image">';
        } else if (msg.messagetype === 'file' && msg.filepath) {
            content = '📎 ' + esc(msg.filename || '파일');
        } else {
            content = esc(msg.message || '');
        }

        var time = formatTime(msg.createdat);
        var senderLabel = isSystem ? '' : (!isSent ? '<div class="mc-msg-sender">' + esc(msg.sendername || '') + '</div>' : '');

        var html = '<div class="' + cls + '" data-msg-id="' + (msg.id || '') + '">'
            + (isSystem ? '' : '<div class="' + avaCls + '">' + esc(initial) + '</div>')
            + '<div class="mc-msg-body">'
            + senderLabel
            + '<div class="mc-msg-bub">' + content + '</div>'
            + '<div class="mc-msg-time">' + time + '</div>'
            + '</div>'
            + '</div>';

        msgsDiv.insertAdjacentHTML('beforeend', html);
    }

    function scrollToBottom() {
        msgsDiv.scrollTop = msgsDiv.scrollHeight;
    }

    // ─── Send Message ───
    function sendMessage() {
        var text = msgInput.value.trim();
        if (!text || !state.currentRoomId) return;

        msgInput.value = '';

        var fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('room_id', state.currentRoomId);
        fd.append('sender_id', state.staffId);
        fd.append('sender_name', state.staffName);
        fd.append('message', text);
        fd.append('message_type', 'text');

        fetch('/chat/api.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    fetchMessages();
                }
            })
            .catch(function() {});
    }

    // ─── Mark as Read ───
    function markAsRead(roomId) {
        var fd = new FormData();
        fd.append('action', 'mark_as_read');
        fd.append('room_id', roomId);
        fd.append('sender_id', state.staffId);
        fetch('/chat/api.php', { method: 'POST', body: fd }).catch(function() {});
    }

    // ─── Panel Toggle ───
    function togglePanel() {
        state.open = !state.open;
        if (state.open) {
            panel.classList.add('open');
            fab.classList.remove('has-unread');
            stopTitleFlash();
            requestNotifPermission();
            fetchRooms();
        } else {
            panel.classList.remove('open');
            if (state.view === 'chat') showRoomsView();
        }
    }

    // ─── Event Bindings ───
    fab.addEventListener('click', togglePanel);
    closeBtn.addEventListener('click', function() {
        state.open = false;
        panel.classList.remove('open');
        if (state.view === 'chat') showRoomsView();
    });
    backBtn.addEventListener('click', showRoomsView);
    sendBtn.addEventListener('click', sendMessage);
    msgInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    // ─── Polling ───
    // Room list polling (every 5s)
    fetchRooms();
    setInterval(fetchRooms, 5000);

    // ─── Visibility: stop title flash when tab regains focus ───
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) stopTitleFlash();
    });

    // ─── Expose for footer.php integration ───
    window._miniChat = {
        toggle: togglePanel,
        fetchRooms: fetchRooms,
        isOpen: function() { return state.open; }
    };
})();
</script>
