class ChatWidget {
    constructor(options) {
        options = options || {};
        this.mode = options.mode || 'chat';
        this.pfx = this.mode === 'ai' ? 'ai' : 'chat';
        this.roomId = null;
        this.lastMessageId = 0;
        this.isOpen = false;
        this.pollInterval = null;
        this.unreadCount = 0;
        this.isAdmin = false;
        this.isDashboard = false;
        this.config = {};
        this.aiMessages = [];
        this.aiLoading = false;
        this.widgetState = 'mini'; // 3-state: 'mini' or 'full'
        this.init();
    }

    async init() {
        this.checkContext();
        await this.loadConfig();
        if (this.mode === 'chat' && !this.configVal('widget_enabled', true)) return;
        if (this.mode === 'ai' && !this.configVal('ai_enabled', true)) return;
        var hStart, hEnd;
        if (this.mode === 'ai') {
            hStart = this.configVal('ai_hour_start', '00:00');
            hEnd = this.configVal('ai_hour_end', '23:59');
        } else {
            hStart = this.configVal('widget_hour_start', '00:00');
            hEnd = this.configVal('widget_hour_end', '23:59');
        }
        if (!this.isWithinSchedule(hStart, hEnd)) this._outsideHours = true;
        // Admin check removed: widget always works as customer-facing
        this.createWidget();
        this.applyConfigToWidget();
        this.attachEvents();
        if (this.mode === 'chat') {
            this.loadChatState();
            // First visit bounce animation
            if (!sessionStorage.getItem(this.pfx + '_visited')) {
                sessionStorage.setItem(this.pfx + '_visited', '1');
                var mini = document.getElementById(this.pfx + '-mini-widget');
                if (mini) mini.classList.add('chat-mini-bounce');
            }
        }
    }

    async loadConfig() {
        try {
            var r = await fetch('/chat/api.php?action=get_chat_config');
            var res = await r.json();
            if (res.success && res.data) {
                var cfg = {};
                for (var k in res.data) cfg[k] = res.data[k].value;
                this.config = cfg;
            }
        } catch (e) {}
    }

    configVal(key, fallback) {
        var v = this.config[key];
        if (v === undefined || v === null) return fallback;
        return v;
    }

    isWithinSchedule(start, end) {
        if (!start || !end) return true;
        var now = new Date();
        var cur = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        if (start <= end) return cur >= start && cur <= end;
        return cur >= start || cur <= end;
    }

    applyConfigToWidget() {
        var p = this.pfx;
        if (this.mode === 'chat') {
            // Update mini widget titlebar text with button label
            var btnLabel = this.configVal('widget_button_label', '');
            if (btnLabel) {
                var titleText = document.getElementById(p + '-mini-titlebar-text');
                if (titleText) titleText.textContent = btnLabel;
            }
            // Update mini widget titlebar color
            var headerColor = this.configVal('widget_header_color', '');
            if (headerColor) {
                var titlebar = document.getElementById(p + '-mini-titlebar');
                if (titlebar) titlebar.style.background = headerColor;
            }
            // Update mini widget greeting with welcome message
            var welcomeMsg = this.configVal('widget_welcome_msg', '');
            if (welcomeMsg) {
                var greet = document.getElementById(p + '-mini-greeting');
                if (greet) greet.textContent = welcomeMsg;
            }
            var notice = this.configVal('notice_message', '');
            if (notice) {
                var bar = document.createElement('div');
                bar.style.cssText = 'padding:6px 12px;background:#fef3c7;color:#92400e;font-size:12px;text-align:center;border-bottom:1px solid #fde68a;';
                bar.textContent = notice;
                var mc = document.getElementById(p + '-messages');
                if (mc) mc.parentNode.insertBefore(bar, mc);
            }
        } else {
            var label = document.getElementById(p + '-forehead-label');
            if (label) label.textContent = this.configVal('ai_button_label', 'AI 상담');
            var btn = document.getElementById(p + '-toggle-btn');
            var color = this.configVal('ai_button_color', '#667eea');
            if (btn) btn.style.background = 'linear-gradient(135deg, ' + color + ' 0%, #764ba2 100%)';
        }
    }

    // checkAdminStatus removed: widget always operates in customer mode

    checkContext() { this.isDashboard = window.location.pathname.startsWith('/dashboard/'); }

    createWidget() {
        var p = this.pfx;
        var w = document.createElement('div');
        w.id = p + '-widget';
        w.className = 'chat-widget' + (this.mode === 'ai' ? ' ai-widget' : '');
        var btnHtml, hTitle, hSub, inputAreaHtml;

        if (this.mode === 'ai') {
            btnHtml = '<button class="chat-toggle-btn ai-toggle-btn" id="' + p + '-toggle-btn">'
                + '<span class="chat-forehead-label ai-forehead-label" id="' + p + '-forehead-label">AI 상담</span>'
                + '<span class="ai-toggle-icon">AI</span>'
                + '<span class="chat-unread-badge" id="' + p + '-unread-badge" style="display:none;">0</span></button>';
            hTitle = '두손 AI 상담봇';
            hSub = '영업시간 외 AI가 안내해드립니다';
            inputAreaHtml = '<div class="chat-input-area" id="' + p + '-input-area">'
                + '<div id="' + p + '-quickbtns" class="ai-quickbtns">'
                + '<button class="ai-quick-btn" data-q="스티커">스티커/라벨</button>'
                + '<button class="ai-quick-btn" data-q="전단지">전단지/리플렛</button>'
                + '<button class="ai-quick-btn" data-q="명함">명함/쿠폰</button>'
                + '<button class="ai-quick-btn" data-q="자석스티커">자석스티커</button>'
                + '<button class="ai-quick-btn" data-q="봉투">봉투</button>'
                + '<button class="ai-quick-btn" data-q="카다록">카다록</button>'
                + '<button class="ai-quick-btn" data-q="포스터">포스터</button>'
                + '<button class="ai-quick-btn" data-q="양식지">양식지</button>'
                + '<button class="ai-quick-btn" data-q="상품권">상품권</button>'
                + '</div>'
                + '<div class="chat-input-wrapper">'
                + '<input type="text" class="chat-input" id="' + p + '-input" placeholder="궁금한 상품을 선택 또는 입력하세요" autocomplete="off">'
                + '<button class="chat-send-btn" id="' + p + '-send-btn"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></button>'
                + '</div></div>';
        } else {
            // 3-State Mini Chat Widget (replaces infolady toggle button)
            btnHtml = '<div class="chat-mini-widget" id="' + p + '-mini-widget">'
                + '<div class="chat-mini-titlebar" id="' + p + '-mini-titlebar">'
                + '<span class="chat-mini-titlebar-icon">💬</span>'
                + '<span class="chat-mini-titlebar-text" id="' + p + '-mini-titlebar-text">상담연결</span>'
                + '</div>'
                + '<div class="chat-mini-body">'
                + '<div class="chat-mini-greeting" id="' + p + '-mini-greeting">궁금하신 점 있으신가요?</div>'
                + '<div class="chat-mini-input-row">'
                + '<input type="text" class="chat-mini-input" id="' + p + '-mini-input" placeholder="메시지를 입력하세요..." autocomplete="off">'
                + '<button class="chat-mini-send" id="' + p + '-mini-send"><svg viewBox="0 0 24 24" fill="white" width="14" height="14"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>'
                + '</div>'
                + '</div>'
                + '<span class="chat-unread-badge" id="' + p + '-unread-badge" style="display:none;">0</span>'
                + '</div>';
            hTitle = '고객 지원';
            hSub = '두손기획인쇄';
            inputAreaHtml = '<div class="chat-input-area">'
                + '<div class="chat-input-wrapper">'
                + '<button class="chat-image-btn" id="' + p + '-image-btn" title="파일 첨부">+</button>'
                + '<input type="file" id="' + p + '-image-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.hwp,.hwpx,.ai,.psd,.zip,.txt" style="display:none;">'
                + '<input type="text" class="chat-input" id="' + p + '-input" placeholder="메시지를 입력하세요..." autocomplete="off">'
                + '<button class="chat-send-btn" id="' + p + '-send-btn"><svg viewBox="0 0 24 24" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>'
                + '</div></div>';
        }

        var headerClass = 'chat-header' + (this.mode === 'ai' ? ' ai-chat-header' : '');
        var windowClass = 'chat-window' + (this.mode === 'ai' ? ' ai-chat-window' : '');

        // Minimize button: chevron-down for chat mode, X for AI mode
        var minimizeSvg = this.mode === 'chat'
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
        var minimizeTitle = this.mode === 'chat' ? '최소화' : '닫기';

        w.innerHTML = btnHtml
            + '<div class="' + windowClass + '" id="' + p + '-window">'
            + '<div class="' + headerClass + '">'
            + '<div style="display:flex;align-items:center;gap:10px;">'
            + '<svg class="chat-drag-handle" width="18" height="27" viewBox="0 0 12 18" fill="rgba(255,255,255,0.55)" style="cursor:move;flex-shrink:0;"><circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/><circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/><circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/></svg>'
            + '<div><div class="chat-header-title">' + hTitle + '</div><div class="chat-header-subtitle">' + hSub + '</div></div>'
            + '</div>'
            + '<div class="chat-header-actions">'
            + (this.mode === 'chat' ? '<button id="' + p + '-export-btn" title="대화 내용 저장"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2z"/></svg></button>' : '')
            + '<button id="' + p + '-minimize-btn" title="' + minimizeTitle + '">' + minimizeSvg + '</button>'
            + '</div></div>'
            + '<div class="chat-messages" id="' + p + '-messages">'
            + '<div class="chat-loading"><div class="chat-loading-dots"><span></span><span></span><span></span></div></div>'
            + '</div>'
            + inputAreaHtml
            + '</div>';

        document.body.appendChild(w);
    }

    attachEvents() {
        var self = this, p = this.pfx;

        if (this.mode === 'chat') {
            // Mini widget events
            var miniInput = document.getElementById(p + '-mini-input');
            var miniSend = document.getElementById(p + '-mini-send');

            if (miniInput) {
                miniInput.addEventListener('focus', function() {
                    self.expandToFull();
                });
            }

            if (miniSend) {
                miniSend.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.expandToFull();
                });
            }
        }

        if (this.mode === 'ai') {
            // AI mode still uses toggle button
            var toggleBtn = document.getElementById(p + '-toggle-btn');
            var lastTap = 0;
            var handleToggle = async function(e) {
                var now = Date.now();
                if (now - lastTap < 500) return;
                lastTap = now; e.preventDefault(); e.stopPropagation();
                self.toggleChat();
            };
            toggleBtn.addEventListener('click', handleToggle, { passive: false });
            toggleBtn.addEventListener('touchend', handleToggle, { passive: false });
        }

        var minBtn = document.getElementById(p + '-minimize-btn');
        minBtn.addEventListener('click', function(e) { e.preventDefault(); self.mode === 'chat' ? self.minimizeToMini() : self.closeChat(); });
        minBtn.addEventListener('touchstart', function(e) { e.preventDefault(); self.mode === 'chat' ? self.minimizeToMini() : self.closeChat(); }, { passive: false });

        document.getElementById(p + '-send-btn').addEventListener('click', function() { self.sendMessage(); });
        document.getElementById(p + '-input').addEventListener('keypress', function(e) { if (e.key === 'Enter') self.sendMessage(); });

        if (this.mode === 'ai') {
            var quickBtns = document.querySelectorAll('#' + p + '-quickbtns .ai-quick-btn');
            quickBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var q = btn.getAttribute('data-q');
                    if (q && !self.aiLoading) self.aiSendToBackend(q, q);
                });
            });
        }

        if (this.mode === 'chat') {
            var chatInput = document.getElementById(p + '-input');
            var chatWindow = document.getElementById(p + '-window');
            if (window.visualViewport && window.innerWidth <= 480) {
                var initH = window.innerHeight;
                window.visualViewport.addEventListener('resize', function() {
                    if (chatWindow.classList.contains('active')) {
                        var curH = window.visualViewport.height;
                        if (initH - curH > 100) { chatWindow.style.height = curH + 'px'; chatWindow.style.bottom = 'auto'; }
                        else { chatWindow.style.height = '100%'; chatWindow.style.bottom = '0'; }
                    }
                });
            }
            chatInput.addEventListener('focus', function() { setTimeout(function() { chatInput.scrollIntoView({ behavior: 'smooth', block: 'end' }); }, 350); });
            document.getElementById(p + '-image-btn').addEventListener('click', function() { document.getElementById(p + '-image-input').click(); });
            document.getElementById(p + '-image-input').addEventListener('change', function(e) { self.uploadImage(e.target.files[0]); });
            document.getElementById(p + '-export-btn').addEventListener('click', function() { self.exportChat(); });
        }

        this.makeDraggable();
    }

    makeDraggable() {
        var cw = document.getElementById(this.pfx + '-window');
        var hdr = cw.querySelector('.chat-header');
        var isDrag = false, curX, curY, iniX, iniY, xOff = 0, yOff = 0;
        hdr.style.cursor = 'move';
        hdr.addEventListener('mousedown', function(e) {
            if (e.target.closest('.chat-header-actions')) return;
            iniX = e.clientX - xOff; iniY = e.clientY - yOff;
            if (e.target === hdr || hdr.contains(e.target)) isDrag = true;
        });
        document.addEventListener('mousemove', function(e) {
            if (isDrag) { e.preventDefault(); curX = e.clientX - iniX; curY = e.clientY - iniY; xOff = curX; yOff = curY; cw.style.transform = 'translate(' + curX + 'px,' + curY + 'px)'; }
        });
        document.addEventListener('mouseup', function() { iniX = curX; iniY = curY; isDrag = false; });
    }

    async toggleChat() {
        if (this.isOpen) { this.mode === 'chat' ? this.minimizeToMini() : this.closeChat(); return; }
        if (this._outsideHours) { alert(this.configVal('offline_message', '현재 업무시간 외입니다.')); return; }
        if (this.mode === 'ai') {
            this.openAiChat();
        } else {
            this.expandToFull();
        }
    }

    async expandToFull() {
        if (this._outsideHours) { alert(this.configVal('offline_message', '현재 업무시간 외입니다.')); return; }
        if (this.widgetState === 'full' && this.isOpen) return;

        var p = this.pfx;
        // Transfer text from mini input to full input
        var miniInput = document.getElementById(p + '-mini-input');
        var fullInput = document.getElementById(p + '-input');
        if (miniInput && fullInput && miniInput.value.trim()) {
            fullInput.value = miniInput.value;
            miniInput.value = '';
        }

        this.widgetState = 'full';
        var miniWidget = document.getElementById(p + '-mini-widget');
        if (miniWidget) miniWidget.classList.add('chat-mini-hidden');

        // Set username automatically if not set
        if (!sessionStorage.getItem('user_name_set')) {
            var autoName = window._chatUserName || '손님';
            sessionStorage.setItem('user_name', autoName);
            sessionStorage.setItem('user_name_set', 'true');
        }

        await this.openChat();
        if (fullInput) setTimeout(function() { fullInput.focus(); }, 300);
    }

    minimizeToMini() {
        this.widgetState = 'mini';
        this.isOpen = false;
        var p = this.pfx;
        var cw = document.getElementById(p + '-window');
        cw.classList.remove('active');
        cw.style.height = ''; cw.style.bottom = ''; cw.style.top = '';

        var miniWidget = document.getElementById(p + '-mini-widget');
        if (miniWidget) miniWidget.classList.remove('chat-mini-hidden');

        if (this.mode === 'chat') this.stopPolling();
        this.saveChatState();

        // Update mini greeting with last message preview
        this.updateMiniPreview();
    }

    updateMiniPreview() {
        var mc = document.getElementById(this.pfx + '-messages');
        var greeting = document.getElementById(this.pfx + '-mini-greeting');
        if (!mc || !greeting) return;
        var msgs = mc.querySelectorAll('.chat-message.received .chat-message-bubble');
        if (msgs.length > 0) {
            var last = msgs[msgs.length - 1].textContent.trim();
            if (last.length > 30) last = last.substring(0, 30) + '...';
            greeting.textContent = last;
        }
    }

    async openChat() {
        var p = this.pfx; this.isOpen = true;
        document.getElementById(p + '-window').classList.add('active');
        if (window.innerWidth > 480 && !sessionStorage.getItem(p + '_drag_hint_shown')) {
            sessionStorage.setItem(p + '_drag_hint_shown', '1');
            setTimeout(function() {
                var t = document.createElement('div');
                t.style.cssText = 'position:fixed;top:14px;right:16px;background:#364052;color:#fff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;z-index:999999;box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:opacity .3s;font-family:"Noto Sans KR",sans-serif;';
                t.textContent = '채팅창은 드래그하여 이동 가능합니다';
                document.body.appendChild(t);
                setTimeout(function() { t.style.opacity = '0'; }, 3000);
                setTimeout(function() { t.remove(); }, 3300);
            }, 500);
        }
        if (!this.roomId) await this.getOrCreateRoom();
        await this.loadMessages(); this.startPolling(); this.markAsRead(); this.saveChatState();
    }

    closeChat() {
        var p = this.pfx; this.isOpen = false;
        var cw = document.getElementById(p + '-window');
        cw.classList.remove('active'); cw.style.height = ''; cw.style.bottom = ''; cw.style.top = '';
        if (this.mode === 'ai') {
            document.getElementById(p + '-toggle-btn').classList.remove('chat-open');
        }
        if (this.mode === 'chat') this.stopPolling();
        this.saveChatState();
    }

    async getOrCreateRoom() {
        try {
            var r = await fetch('/chat/api.php?action=get_or_create_room'), d = await r.json();
            if (d.success) { this.roomId = d.data.id; localStorage.setItem(this.pfx + '_room_id', this.roomId); }
        } catch (e) { console.error('채팅방 생성 실패:', e); }
    }

    async loadMessages() {
        if (!this.roomId) return;
        try {
            var r = await fetch('/chat/api.php?action=get_messages&room_id=' + this.roomId + '&last_id=' + this.lastMessageId);
            var d = await r.json();
            if (d.success && d.data.length > 0) {
                var mc = document.getElementById(this.pfx + '-messages');
                var ld = mc.querySelector('.chat-loading'); if (ld) ld.remove();
                var self = this;
                d.data.forEach(function(msg) { self.appendMessage(msg); self.lastMessageId = Math.max(self.lastMessageId, msg.id); });
                this.scrollToBottom();
                // Update mini preview if in mini state
                if (this.widgetState === 'mini') this.updateMiniPreview();
            }
        } catch (e) {}
    }

    appendMessage(msg) {
        var mc = document.getElementById(this.pfx + '-messages'), user = this.getCurrentUser();
        var isCust = msg.senderid && (msg.senderid.startsWith('guest_') || msg.senderid == user.id);
        var isSys = msg.senderid === 'system', isAi = msg.senderid === 'ai_bot', isSent = isCust && !isSys;
        var div = document.createElement('div');
        div.className = ('chat-message ' + (isSent ? 'sent' : 'received') + (isSys ? ' system' : '') + (isAi ? ' ai-bot' : '')).replace(/\s+/g, ' ').trim();
        var avatar = '';
        if (isAi) avatar = '<div class="chat-message-avatar ai-bot-avatar">AI</div>';
        else if (!isSent && !isSys) avatar = '<div class="chat-message-avatar">' + msg.sendername.charAt(0) + '</div>';
        var content = '';
        if (msg.messagetype === 'text') content = '<div class="chat-message-bubble">' + this.linkify(this.escapeHtml(msg.message)) + '</div>';
        else if (msg.messagetype === 'image') content = '<div class="chat-message-bubble"><img src="/' + msg.filepath + '" alt="' + msg.filename + '" class="chat-message-image" onclick="window.open(\'/' + msg.filepath + '\',\'_blank\')"></div>';
        else if (msg.messagetype === 'file') { var fs = msg.filesize ? this.formatFileSize(msg.filesize) : '', fi = this.getFileIcon(msg.filename); content = '<div class="chat-message-bubble chat-file-message"><a href="/' + msg.filepath + '" target="_blank" class="chat-file-link"><span class="chat-file-icon">' + fi + '</span><span class="chat-file-info"><span class="chat-file-name">' + this.escapeHtml(msg.filename) + '</span><span class="chat-file-size">' + fs + '</span></span></a></div>'; }
        var time = new Date(msg.createdat).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
        div.innerHTML = avatar + '<div class="chat-message-content">' + (!isSys ? '<div class="chat-message-sender">' + msg.sendername + '</div>' : '') + content + '<div class="chat-message-time">' + time + '</div></div>';
        mc.appendChild(div);
    }

    async sendMessage() {
        if (this.mode === 'ai') { this.aiSendFromInput(); return; }
        var inp = document.getElementById(this.pfx + '-input'), msg = inp.value.trim();
        if (!msg || !this.roomId) return;
        try {
            var u = this.getCurrentUser(), fd = new FormData();
            fd.append('action', 'send_message'); fd.append('room_id', this.roomId);
            fd.append('message', msg); fd.append('sender_id', u.id); fd.append('sender_name', u.name);
            var r = await fetch('/chat/api.php', { method: 'POST', body: fd }), d = await r.json();
            if (d.success) { inp.value = ''; await this.loadMessages(); }
        } catch (e) {}
    }

    async uploadImage(file) {
        if (!file || !this.roomId) return;
        if (file.size > 10 * 1024 * 1024) { alert('파일 크기 초과 (최대 10MB)'); return; }
        var exts = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','hwp','hwpx','ai','psd','zip','txt'];
        var ext = file.name.split('.').pop().toLowerCase();
        if (!exts.includes(ext)) { alert('허용되지 않는 파일 형식입니다.'); return; }
        var pv = this.showUploadPreview(file, ext);
        try {
            var u = this.getCurrentUser(), fd = new FormData();
            fd.append('action', 'upload_file'); fd.append('room_id', this.roomId);
            fd.append('file', file); fd.append('sender_id', u.id); fd.append('sender_name', u.name);
            var r = await fetch('/chat/api.php', { method: 'POST', body: fd }), d = await r.json();
            if (pv) pv.remove();
            if (d.success) { await this.loadMessages(); document.getElementById(this.pfx + '-image-input').value = ''; }
            else alert('파일 업로드 실패: ' + d.message);
        } catch (e) { if (pv) pv.remove(); alert('파일 업로드 중 오류가 발생했습니다.'); }
    }

    showUploadPreview(file, ext) {
        var mc = document.getElementById(this.pfx + '-messages'), div = document.createElement('div');
        div.className = 'chat-message sent';
        var isImg = ['jpg','jpeg','png','gif','webp'].includes(ext), bc = '';
        if (isImg) { var ou = URL.createObjectURL(file); bc = '<div class="chat-upload-preview"><img src="' + ou + '" class="chat-message-image" onload="URL.revokeObjectURL(this.src)"><div class="chat-upload-overlay"><div class="chat-upload-spinner"></div><span>전송 중...</span></div></div>'; }
        else { bc = '<div class="chat-upload-preview chat-upload-preview-file"><span class="chat-file-icon">' + this.getFileIcon(file.name) + '</span><div class="chat-file-info"><span class="chat-file-name">' + this.escapeHtml(file.name) + '</span><span class="chat-file-size">' + this.formatFileSize(file.size) + '</span></div><div class="chat-upload-spinner-small"></div></div>'; }
        div.innerHTML = '<div class="chat-message-content"><div class="chat-message-bubble">' + bc + '</div></div>';
        mc.appendChild(div); this.scrollToBottom(); return div;
    }

    formatFileSize(b) { if (b === 0) return '0 B'; var k = 1024, s = ['B','KB','MB','GB'], i = Math.floor(Math.log(b) / Math.log(k)); return parseFloat((b / Math.pow(k, i)).toFixed(1)) + ' ' + s[i]; }
    getFileIcon(fn) { var ext = fn.split('.').pop().toLowerCase(), m = {'pdf':'📕','doc':'📘','docx':'📘','xls':'📗','xlsx':'📗','ppt':'📙','pptx':'📙','hwp':'📝','hwpx':'📝','ai':'🎨','psd':'🎨','zip':'📦','txt':'📄'}; return m[ext] || '📎'; }

    async markAsRead() {
        if (!this.roomId) return;
        try { var fd = new FormData(); fd.append('action', 'mark_as_read'); fd.append('room_id', this.roomId); await fetch('/chat/api.php', { method: 'POST', body: fd }); this.unreadCount = 0; this.updateUnreadBadge(); } catch (e) {}
    }

    async updateUnreadCount() {
        if (this.isOpen || this.mode === 'ai') return;
        try {
            if (!this.roomId) return; var r = await fetch('/chat/api.php?action=get_unread_count&room_id=' + this.roomId), d = await r.json(); if (d.success) { this.unreadCount = d.data.count; this.updateUnreadBadge(); }
        } catch (e) {}
    }

    updateUnreadBadge() {
        var b = document.getElementById(this.pfx + '-unread-badge'); if (!b) return;
        if (this.unreadCount > 0) { b.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount; b.style.display = 'block'; } else b.style.display = 'none';
        // Mini widget notification glow
        var mini = document.getElementById(this.pfx + '-mini-widget');
        if (mini) {
            if (this.unreadCount > 0) mini.classList.add('chat-mini-has-unread');
            else mini.classList.remove('chat-mini-has-unread');
        }
    }

    startPolling() { this.stopPolling(); var iv = parseInt(this.configVal('widget_poll_interval', 2000)) || 2000, self = this; this.pollInterval = setInterval(function() { self.loadMessages(); }, iv); }
    stopPolling() { if (this.pollInterval) { clearInterval(this.pollInterval); this.pollInterval = null; } }
    scrollToBottom() { var mc = document.getElementById(this.pfx + '-messages'); if (mc) mc.scrollTop = mc.scrollHeight; }
    exportChat() { if (this.roomId) window.open('/chat/api.php?action=export_chat&room_id=' + this.roomId, '_blank'); }

    saveChatState() {
        localStorage.setItem(this.pfx + '_is_open', this.isOpen);
        localStorage.setItem(this.pfx + '_widget_state', this.widgetState);
    }

    loadChatState() {
        var rid = localStorage.getItem(this.pfx + '_room_id'); if (rid) this.roomId = parseInt(rid);
        // Always start in mini state — don't auto-open full chat on page load
        var self = this; setInterval(function() { self.updateUnreadCount(); }, 5000);
    }

    openAiChat() {
        var p = this.pfx; this.isOpen = true;
        document.getElementById(p + '-window').classList.add('active');
        document.getElementById(p + '-toggle-btn').classList.add('chat-open');
        var mc = document.getElementById(p + '-messages');
        var ld = mc.querySelector('.chat-loading'); if (ld) ld.remove();
        if (this.aiMessages.length === 0) {
            this.aiAppendBotMessage('안녕하세요! 두손기획인쇄 AI 상담봇입니다. 😊\n\n현재 영업시간 외입니다. 인쇄물 가격이 궁금하시면 편하게 물어봐주세요!\n직접 주문하시면 접수가능합니다.', null);
        }
        var inp = document.getElementById(p + '-input');
        if (inp) setTimeout(function() { inp.focus(); }, 300);
    }

    aiSendFromInput() {
        var inp = document.getElementById(this.pfx + '-input');
        var msg = inp.value.trim();
        if (!msg || this.aiLoading) return;
        inp.value = '';
        this.aiSendToBackend(msg, msg);
    }

    aiSendToBackend(msgToSend, displayText) {
        if (this.aiLoading) return;
        this.aiLoading = true;
        this.aiDisableOpts();
        this.aiMessages.push({ role: 'user', content: msgToSend });
        this.aiAppendUserMessage(displayText || msgToSend);
        this.aiShowTyping();
        var self = this;
        var fd = new FormData();
        fd.append('action', 'chat');
        fd.append('message', msgToSend);
        fd.append('history', JSON.stringify(this.aiMessages.slice(0, -1)));
        fetch('/api/ai_chat.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                self.aiRemoveTyping();
                if (data.error) {
                    self.aiAppendBotMessage('죄송합니다. 오류가 발생했습니다: ' + data.error, null);
                } else if (data.message) {
                    self.aiMessages.push({ role: 'assistant', content: data.message });
                    if (data.input_type === 'sticker_size') {
                        self.aiAppendSizeInput(data.message);
                    } else {
                        self.aiAppendBotMessage(data.message, data.options || null);
                    }
                }
            })
            .catch(function() {
                self.aiRemoveTyping();
                self.aiAppendBotMessage('네트워크 오류가 발생했습니다. 잠시 후 다시 시도해주세요.', null);
            })
            .finally(function() { self.aiLoading = false; });
    }

    aiAppendUserMessage(text) {
        var mc = document.getElementById(this.pfx + '-messages');
        if (!mc) return;
        var div = document.createElement('div');
        div.className = 'chat-message sent';
        div.innerHTML = '<div class="chat-message-content"><div class="chat-message-bubble">' + this.escapeHtml(text) + '</div></div>';
        mc.appendChild(div);
        mc.scrollTop = mc.scrollHeight;
    }

    aiAppendBotMessage(text, options) {
        var mc = document.getElementById(this.pfx + '-messages');
        if (!mc) return;
        var div = document.createElement('div');
        div.className = 'chat-message received ai-bot';
        var formatted = this.aiFmtMsg(text);
        var optsHtml = this.aiBuildOpts(options);
        div.innerHTML = '<div class="chat-message-avatar ai-bot-avatar">AI</div>'
            + '<div class="chat-message-content"><div class="chat-message-sender" style="color:#667eea;font-weight:600;">AI 상담봇</div>'
            + '<div class="chat-message-bubble">' + formatted + '</div>'
            + optsHtml + '</div>';
        mc.appendChild(div);
        if (options && options.length) {
            setTimeout(function() {
                var userMsg = div.previousElementSibling;
                (userMsg || div).scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        } else {
            mc.scrollTop = mc.scrollHeight;
        }
    }

    aiAppendSizeInput(message) {
        var mc = document.getElementById(this.pfx + '-messages');
        if (!mc) return;
        var self = this;
        var div = document.createElement('div');
        div.className = 'chat-message received ai-bot';
        div.innerHTML = '<div class="chat-message-avatar ai-bot-avatar">AI</div>'
            + '<div class="chat-message-content"><div class="chat-message-sender" style="color:#667eea;font-weight:600;">AI 상담봇</div>'
            + '<div class="chat-message-bubble">' + this.aiFmtMsg(message) + '</div>'
            + '<div class="ai-size-widget" style="margin-top:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;">'
            + '<div style="display:flex;align-items:center;gap:6px;justify-content:center;">'
            + '<input type="number" class="ai-size-garo" placeholder="가로" min="1" max="590" style="width:72px;padding:8px;border:2px solid #d1d5db;border-radius:8px;font-size:14px;text-align:center;font-family:inherit;outline:none;" autocomplete="off">'
            + '<span style="font-size:16px;color:#64748b;font-weight:700;">\u00d7</span>'
            + '<input type="number" class="ai-size-sero" placeholder="세로" min="1" max="590" style="width:72px;padding:8px;border:2px solid #d1d5db;border-radius:8px;font-size:14px;text-align:center;font-family:inherit;outline:none;" autocomplete="off">'
            + '<span style="font-size:11px;color:#94a3b8;margin-left:2px;">mm</span></div>'
            + '<button class="ai-size-confirm" style="margin-top:8px;width:100%;padding:8px;background:#667eea;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">확인</button>'
            + '</div></div>';
        mc.appendChild(div);
        mc.scrollTop = mc.scrollHeight;
        var widget = div.querySelector('.ai-size-widget');
        var garo = widget.querySelector('.ai-size-garo');
        var sero = widget.querySelector('.ai-size-sero');
        var confirmBtn = widget.querySelector('.ai-size-confirm');
        if (garo) {
            setTimeout(function() { garo.focus(); }, 100);
            garo.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); if (sero) sero.focus(); } });
            garo.addEventListener('focus', function() { this.style.borderColor = '#667eea'; });
            garo.addEventListener('blur', function() { this.style.borderColor = '#d1d5db'; });
        }
        if (sero) {
            sero.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); doSubmit(); } });
            sero.addEventListener('focus', function() { this.style.borderColor = '#667eea'; });
            sero.addEventListener('blur', function() { this.style.borderColor = '#d1d5db'; });
        }
        if (confirmBtn) confirmBtn.addEventListener('click', doSubmit);
        function doSubmit() {
            if (self.aiLoading) return;
            var g = parseInt(garo.value) || 0, s = parseInt(sero.value) || 0;
            if (g <= 0 || g > 590) { garo.style.borderColor = '#ef4444'; garo.focus(); return; }
            if (s <= 0 || s > 590) { sero.style.borderColor = '#ef4444'; sero.focus(); return; }
            garo.disabled = true; sero.disabled = true;
            if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.style.opacity = '0.5'; }
            self.aiSendToBackend(g + '\u00d7' + s, g + '\u00d7' + s + 'mm');
        }
    }

    aiShowTyping() {
        var mc = document.getElementById(this.pfx + '-messages');
        var div = document.createElement('div');
        div.id = this.pfx + '-typing';
        div.className = 'chat-message received ai-bot';
        div.innerHTML = '<div class="chat-message-avatar ai-bot-avatar">AI</div>'
            + '<div class="chat-message-content"><div class="chat-message-bubble" style="padding:10px 14px;display:flex;gap:4px;">'
            + '<span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s infinite alternate;"></span>'
            + '<span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .15s infinite alternate;"></span>'
            + '<span style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:aiBounce .6s .3s infinite alternate;"></span>'
            + '</div></div>';
        mc.appendChild(div);
        mc.scrollTop = mc.scrollHeight;
    }

    aiRemoveTyping() {
        var el = document.getElementById(this.pfx + '-typing');
        if (el) el.remove();
    }

    aiDisableOpts() {
        var mc = document.getElementById(this.pfx + '-messages');
        var btns = mc.querySelectorAll('.ai-opt-btn');
        for (var i = 0; i < btns.length; i++) { btns[i].disabled = true; btns[i].style.opacity = '0.5'; }
    }

    aiBuildOpts(options) {
        if (!options || !options.length) return '';
        var self = this;
        var h = '<div class="ai-opts-container" style="margin-top:8px;display:flex;flex-direction:column;gap:4px;">';
        for (var i = 0; i < options.length; i++) {
            h += '<button class="ai-opt-btn" data-num="' + options[i].num + '">' + options[i].num + '. ' + this.escapeHtml(options[i].label) + '</button>';
        }
        h += '</div>';
        setTimeout(function() {
            var mc = document.getElementById(self.pfx + '-messages');
            mc.querySelectorAll('.ai-opt-btn:not([data-bound])').forEach(function(btn) {
                btn.setAttribute('data-bound', '1');
                btn.addEventListener('click', function() {
                    if (self.aiLoading || btn.disabled) return;
                    btn.classList.add('selected');
                    self.aiSendToBackend(btn.getAttribute('data-num'), btn.textContent.replace(/^\d+\.\s*/, ''));
                });
            });
        }, 50);
        return h;
    }

    aiFmtMsg(text) {
        var s = this.escapeHtml(text);
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong style="color:#667eea;">$1</strong>');
        return s.replace(/\n/g, '<br>');
    }

    getCurrentUser() {
        var uid = localStorage.getItem('chat_user_id');
        var un = window._chatUserName || sessionStorage.getItem('user_name') || localStorage.getItem('chat_user_name');
        if (!uid) { uid = 'guest_' + Date.now(); localStorage.setItem('chat_user_id', uid); }
        if (!un) un = '손님'; else localStorage.setItem('chat_user_name', un);
        return { id: uid, name: un };
    }

    escapeHtml(t) { var m = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}; return t.replace(/[&<>"']/g, function(c) { return m[c]; }); }
    linkify(t) {
        var p = /(https?:\/\/[^\s<>&"']+(?:\.[^\s<>&"']+)+[^\s<>&"'.,;:!?)]*|www\.[^\s<>&"']+(?:\.[^\s<>&"']+)+[^\s<>&"'.,;:!?)]*|[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9\-]*[a-zA-Z0-9])?)*\.(?:com|net|org|co\.kr|go\.kr|or\.kr|ne\.kr|re\.kr|pe\.kr|kr|io|me|info|biz|shop|xyz|dev|app|site|online|store|tech)(?:\/[^\s<>&"']*)?)/gi;
        return t.replace(p, function(u) { var h = /^https?:\/\//i.test(u) ? u : 'https://' + u; return '<a href="' + h + '" target="_blank" rel="noopener noreferrer" style="color:#4a9eff;text-decoration:underline;word-break:break-all;">' + u + '</a>'; });
    }
}
