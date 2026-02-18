// 채팅 위젯 JavaScript
class ChatWidget {
    constructor() {
        this.roomId = null;
        this.lastMessageId = 0;
        this.isOpen = false;
        this.pollInterval = null;
        this.unreadCount = 0;
        this.isAdmin = false;
        this.isDashboard = false;

        this.init();
    }

    init() {
        this.checkContext();
        this.createWidget();
        this.attachEvents();
        this.loadChatState();
        this.startBlinkAnimation();
    }

    checkContext() {
        this.isDashboard = window.location.pathname.startsWith('/dashboard/');
    }

    startBlinkAnimation() {
        const img = document.querySelector('.chat-toggle-img');
        if (!img) return;

        const openEye = '/ImgFolder/infolady.png';
        const closedEye = '/ImgFolder/infolady2.png';

        setInterval(() => {
            img.src = closedEye;
            setTimeout(() => {
                img.src = openEye;
            }, 150);
        }, 2000);
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.className = 'chat-widget';
        widget.innerHTML = `
            <!-- 이름 입력 모달 -->
            <div class="chat-name-modal" id="chat-name-modal">
                <div class="chat-name-modal-content">
                    <div class="chat-name-modal-header">
                        <div class="chat-name-modal-icon">👋</div>
                        <div class="chat-name-modal-title">안녕하세요!</div>
                        <div class="chat-name-modal-subtitle">더 나은 상담을 위해<br>상호명이나 성함을 알려주세요</div>
                    </div>
                    <div class="chat-name-modal-body">
                        <label class="chat-name-modal-label">상호명 또는 성함 (선택사항)</label>
                        <input type="text" class="chat-name-modal-input" id="chat-name-input" placeholder="예: 홍길동 or 두손기획" maxlength="30">
                    </div>
                    <div class="chat-name-modal-footer">
                        <button class="chat-name-modal-btn chat-name-modal-btn-secondary" id="chat-name-skip-btn">건너뛰기</button>
                        <button class="chat-name-modal-btn chat-name-modal-btn-primary" id="chat-name-submit-btn">채팅 시작</button>
                    </div>
                </div>
            </div>

            <button class="chat-toggle-btn chat-toggle-btn-image" id="chat-toggle-btn">
                <span class="chat-forehead-label">상담연결</span>
                <img src="/ImgFolder/infolady.png" alt="상담" class="chat-toggle-img">
                <span class="chat-unread-badge" id="chat-unread-badge" style="display:none;">0</span>
            </button>

            <div class="chat-window" id="chat-window">
                <div class="chat-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg class="chat-drag-handle" width="12" height="18" viewBox="0 0 12 18" fill="rgba(255,255,255,0.5)" style="cursor: move; flex-shrink: 0;">
                            <circle cx="3" cy="3" r="1.5"/>
                            <circle cx="9" cy="3" r="1.5"/>
                            <circle cx="3" cy="9" r="1.5"/>
                            <circle cx="9" cy="9" r="1.5"/>
                            <circle cx="3" cy="15" r="1.5"/>
                            <circle cx="9" cy="15" r="1.5"/>
                        </svg>
                        <div>
                            <div class="chat-header-title">고객 지원</div>
                            <div class="chat-header-subtitle">두손기획인쇄</div>
                        </div>
                    </div>
                    <div class="chat-drag-hint" id="chat-drag-hint">이동가능</div>
                    <div class="chat-header-actions">
                        <button id="chat-export-btn" title="대화 내용 저장">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                                <path d="M19 12v7H5v-7H3v7c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-7h-2zm-6 .67l2.59-2.58L17 11.5l-5 5-5-5 1.41-1.41L11 12.67V3h2z"/>
                            </svg>
                        </button>
                        <button id="chat-minimize-btn" title="닫기">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <div class="chat-loading">
                        <div class="chat-loading-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <div class="chat-input-wrapper">
                        <button class="chat-image-btn" id="chat-image-btn" title="파일 첨부">+</button>
                        <input type="file" id="chat-image-input" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.hwp,.hwpx,.ai,.psd,.zip,.txt">
                        <input type="text" class="chat-input" id="chat-input" placeholder="메시지를 입력하세요...">
                        <button class="chat-send-btn" id="chat-send-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(widget);
    }

    attachEvents() {
        // 이름 입력 모달 이벤트
        document.getElementById('chat-name-submit-btn').addEventListener('click', () => {
            this.submitName();
        });

        document.getElementById('chat-name-skip-btn').addEventListener('click', () => {
            this.skipName();
        });

        document.getElementById('chat-name-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.submitName();
            }
        });

        // 채팅 열기/닫기
        const toggleBtn = document.getElementById('chat-toggle-btn');
        let lastTap = 0;

        const handleToggle = (e) => {
            const now = Date.now();
            if (now - lastTap < 500) return; // 중복 방지
            lastTap = now;
            e.preventDefault();
            e.stopPropagation();
            
            // 관리자 전용 동작
            if (this.isAdmin) {
                if (this.isDashboard) {
                    // 대시보드 내부: 같은 탭에서 /dashboard/chat/ 이동
                    window.location.href = '/dashboard/chat/';
                } else {
                    // 일반 페이지: 새 탭으로 /chat/admin.php 열기
                    window.open('/chat/admin.php', '_blank');
                }
                return;
            }
            
            // 고객 동작: 기존 채팅창 열기
            this.toggleChat();
        };

        // 클릭 이벤트 (데스크톱)
        toggleBtn.addEventListener('click', handleToggle, { passive: false });

        // 터치 이벤트 (모바일) - touchend 사용
        toggleBtn.addEventListener('touchend', handleToggle, { passive: false });

        // 채팅 닫기 버튼 (모바일 터치 지원)
        const minimizeBtn = document.getElementById('chat-minimize-btn');
        minimizeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.closeChat();
        });
        minimizeBtn.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.closeChat();
        }, { passive: false });

        // 메시지 전송
        document.getElementById('chat-send-btn').addEventListener('click', () => {
            this.sendMessage();
        });

        document.getElementById('chat-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // 모바일 키보드 대응
        const chatInput = document.getElementById('chat-input');
        const chatWindow = document.getElementById('chat-window');

        // 모바일 키보드 대응 - visualViewport API
        if (window.visualViewport && window.innerWidth <= 480) {
            let initialHeight = window.innerHeight;

            const adjustForKeyboard = () => {
                if (chatWindow.classList.contains('active')) {
                    const currentHeight = window.visualViewport.height;
                    const keyboardOpen = initialHeight - currentHeight > 100;

                    if (keyboardOpen) {
                        // 키보드 열림 - 뷰포트 크기로 조정
                        chatWindow.style.height = currentHeight + 'px';
                        chatWindow.style.bottom = 'auto';
                    } else {
                        // 키보드 닫힘 - 전체화면 복귀
                        chatWindow.style.height = '100%';
                        chatWindow.style.bottom = '0';
                    }
                }
            };

            window.visualViewport.addEventListener('resize', adjustForKeyboard);
        }

        chatInput.addEventListener('focus', () => {
            setTimeout(() => {
                chatInput.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }, 350);
        });

        // 이미지 업로드
        document.getElementById('chat-image-btn').addEventListener('click', () => {
            document.getElementById('chat-image-input').click();
        });

        document.getElementById('chat-image-input').addEventListener('change', (e) => {
            this.uploadImage(e.target.files[0]);
        });

        // 대화 내용 저장
        document.getElementById('chat-export-btn').addEventListener('click', () => {
            this.exportChat();
        });

        // 드래그 기능
        this.makeDraggable();
    }

    makeDraggable() {
        const chatWindow = document.getElementById('chat-window');
        const header = chatWindow.querySelector('.chat-header');
        const dragHint = document.getElementById('chat-drag-hint');
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;

        header.style.cursor = 'move';

        header.addEventListener('mousedown', dragStart);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', dragEnd);

        function dragStart(e) {
            if (e.target.closest('.chat-header-actions')) return;

            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;

            if (e.target === header || header.contains(e.target)) {
                isDragging = true;
                if (dragHint) {
                    dragHint.classList.add('dragging');
                }
            }
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();

                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;

                xOffset = currentX;
                yOffset = currentY;

                setTranslate(currentX, currentY, chatWindow);
            }
        }

        function dragEnd(e) {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
            if (dragHint) {
                dragHint.classList.remove('dragging');
            }
        }

        function setTranslate(xPos, yPos, el) {
            el.style.transform = `translate(${xPos}px, ${yPos}px)`;
        }
    }

    async toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            // 이름이 설정되지 않았으면 모달 표시
            if (!sessionStorage.getItem('user_name_set')) {
                this.showNameModal();
            } else {
                this.openChat();
            }
        }
    }

    showNameModal() {
        const modal = document.getElementById('chat-name-modal');
        if (modal) {
            modal.classList.add('active');
        }

        // 입력창에 포커스
        setTimeout(() => {
            document.getElementById('chat-name-input').focus();
        }, 300);
    }

    hideNameModal() {
        const modal = document.getElementById('chat-name-modal');
        modal.classList.remove('active');
    }

    submitName() {
        const input = document.getElementById('chat-name-input');
        const name = input.value.trim();

        if (name) {
            // 이름 저장
            sessionStorage.setItem('user_name', name);
            sessionStorage.setItem('user_name_set', 'true');
        } else {
            // 빈 값이면 자동 생성
            this.skipName();
            return;
        }

        // 모달 닫고 채팅 열기
        this.hideNameModal();
        this.openChat();
    }

    skipName() {
        // 자동으로 "손님_xxxx" 생성
        const guestName = '손님_' + Math.random().toString(36).substring(2, 6).toUpperCase();
        sessionStorage.setItem('user_name', guestName);
        sessionStorage.setItem('user_name_set', 'true');

        // 모달 닫고 채팅 열기
        this.hideNameModal();
        this.openChat();
    }

    async openChat() {
        this.isOpen = true;
        document.getElementById('chat-window').classList.add('active');

        // 모바일에서 채팅창 열면 토글 버튼 숨김
        document.getElementById('chat-toggle-btn').classList.add('chat-open');

        // 채팅방 가져오기 또는 생성
        if (!this.roomId) {
            await this.getOrCreateRoom();
        }

        // 메시지 로드
        await this.loadMessages();

        // 실시간 업데이트 시작
        this.startPolling();

        // 읽음 처리
        this.markAsRead();

        // 채팅 상태 저장
        this.saveChatState();
    }

    closeChat() {
        this.isOpen = false;
        const chatWindow = document.getElementById('chat-window');
        chatWindow.classList.remove('active');

        // 인라인 스타일 초기화 (키보드 조정으로 인한 스타일)
        chatWindow.style.height = '';
        chatWindow.style.bottom = '';
        chatWindow.style.top = '';

        // 모바일에서 채팅창 닫으면 토글 버튼 다시 표시
        document.getElementById('chat-toggle-btn').classList.remove('chat-open');

        // 폴링 중지
        this.stopPolling();

        // 채팅 상태 저장
        this.saveChatState();
    }

    async getOrCreateRoom() {
        try {
            const response = await fetch('/chat/api.php?action=get_or_create_room');
            const data = await response.json();

            if (data.success) {
                this.roomId = data.data.id;
                localStorage.setItem('chat_room_id', this.roomId);
            } else {
                console.error('채팅방 생성 실패:', data.message);
            }
        } catch (error) {
            console.error('API 오류:', error);
        }
    }

    async loadMessages() {
        if (!this.roomId) return;

        try {
            const response = await fetch(`/chat/api.php?action=get_messages&room_id=${this.roomId}&last_id=${this.lastMessageId}`);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                const messagesContainer = document.getElementById('chat-messages');

                // 로딩 제거
                const loading = messagesContainer.querySelector('.chat-loading');
                if (loading) {
                    loading.remove();
                }

                data.data.forEach(msg => {
                    this.appendMessage(msg);
                    this.lastMessageId = Math.max(this.lastMessageId, msg.id);
                });

                this.scrollToBottom();
            }
        } catch (error) {
            console.error('메시지 로드 오류:', error);
        }
    }

    appendMessage(msg) {
        const messagesContainer = document.getElementById('chat-messages');
        const user = this.getCurrentUser();

        // 고객 메시지는 오른쪽, 직원/시스템 메시지는 왼쪽
        const isCustomer = msg.senderid && (msg.senderid.startsWith('guest_') || msg.senderid == user.id);
        const isSystem = msg.senderid === 'system';
        const isSent = isCustomer && !isSystem;

        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isSent ? 'sent' : 'received'} ${isSystem ? 'system' : ''}`;

        let avatarHtml = '';
        if (!isSent && !isSystem) {
            const initial = msg.sendername.charAt(0);
            avatarHtml = `<div class="chat-message-avatar">${initial}</div>`;
        }

        let contentHtml = '';
        if (msg.messagetype === 'text') {
            contentHtml = `<div class="chat-message-bubble">${this.escapeHtml(msg.message)}</div>`;
        } else if (msg.messagetype === 'image') {
            contentHtml = `
                <div class="chat-message-bubble">
                    <img src="/${msg.filepath}" alt="${msg.filename}" class="chat-message-image" onclick="window.open('/${msg.filepath}', '_blank')">
                </div>
            `;
        } else if (msg.messagetype === 'file') {
            // 파일 첨부
            const fileSize = msg.filesize ? this.formatFileSize(msg.filesize) : '';
            const fileIcon = this.getFileIcon(msg.filename);
            contentHtml = `
                <div class="chat-message-bubble chat-file-message">
                    <a href="/${msg.filepath}" target="_blank" class="chat-file-link">
                        <span class="chat-file-icon">${fileIcon}</span>
                        <span class="chat-file-info">
                            <span class="chat-file-name">${this.escapeHtml(msg.filename)}</span>
                            <span class="chat-file-size">${fileSize}</span>
                        </span>
                    </a>
                </div>
            `;
        }

        const time = new Date(msg.createdat).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });

        messageDiv.innerHTML = `
            ${avatarHtml}
            <div class="chat-message-content">
                ${!isSystem ? `<div class="chat-message-sender">${msg.sendername}</div>` : ''}
                ${contentHtml}
                <div class="chat-message-time">${time}</div>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
    }

    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();

        if (!message || !this.roomId) return;

        try {
            const user = this.getCurrentUser();
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('room_id', this.roomId);
            formData.append('message', message);
            formData.append('sender_id', user.id); // 사용자 ID 전송
            formData.append('sender_name', user.name); // 사용자 이름 전송

            const response = await fetch('/chat/api.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                input.value = '';
                await this.loadMessages();
            }
        } catch (error) {
            console.error('메시지 전송 오류:', error);
        }
    }

    async uploadImage(file) {
        if (!file || !this.roomId) return;

        // 파일 크기 체크 (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('파일 크기 초과 (최대 10MB)\n\n대용량 파일은 dsp1830@naver.com 으로 보내주세요.');
            return;
        }

        // 허용된 파일 확장자 확인
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp', 'hwpx', 'ai', 'psd', 'zip', 'txt'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(ext)) {
            alert('허용되지 않는 파일 형식입니다.');
            return;
        }

        try {
            const user = this.getCurrentUser();
            const formData = new FormData();
            formData.append('action', 'upload_file');
            formData.append('room_id', this.roomId);
            formData.append('file', file);
            formData.append('sender_id', user.id);
            formData.append('sender_name', user.name);

            const response = await fetch('/chat/api.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                await this.loadMessages();
                document.getElementById('chat-image-input').value = '';
            } else {
                alert('파일 업로드 실패: ' + data.message);
            }
        } catch (error) {
            console.error('파일 업로드 오류:', error);
            alert('파일 업로드 중 오류가 발생했습니다.');
        }
    }

    // 파일 크기 포맷팅
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // 파일 타입별 아이콘
    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': '📕',
            'doc': '📘', 'docx': '📘',
            'xls': '📗', 'xlsx': '📗',
            'ppt': '📙', 'pptx': '📙',
            'hwp': '📝', 'hwpx': '📝',
            'ai': '🎨',
            'psd': '🎨',
            'zip': '📦',
            'txt': '📄'
        };
        return icons[ext] || '📎';
    }

    async markAsRead() {
        if (!this.roomId) return;

        try {
            const formData = new FormData();
            formData.append('action', 'mark_as_read');
            formData.append('room_id', this.roomId);

            await fetch('/chat/api.php', {
                method: 'POST',
                body: formData
            });

            this.unreadCount = 0;
            this.updateUnreadBadge();
        } catch (error) {
            console.error('읽음 처리 오류:', error);
        }
    }

    async updateUnreadCount() {
        if (this.isOpen) return;

        try {
            // 관리자 전용: 전체 읽지 않은 메시지 수 조회
            const response = await fetch('/chat/api.php?action=get_admin_unread_count');
            const data = await response.json();

            if (data.success) {
                this.isAdmin = data.data.is_admin || false;
                
                if (this.isAdmin) {
                    this.unreadCount = data.data.count;
                    this.updateUnreadBadge();
                    return;
                }
            }
            
            // 고객: 자기 채팅방만 조회
            if (!this.roomId) return;
            
            const customerResponse = await fetch(`/chat/api.php?action=get_unread_count&room_id=${this.roomId}`);
            const customerData = await customerResponse.json();

            if (customerData.success) {
                this.unreadCount = customerData.data.count;
                this.updateUnreadBadge();
            }
        } catch (error) {
            console.error('읽지 않은 메시지 수 조회 오류:', error);
        }
    }

    updateUnreadBadge() {
        const badge = document.getElementById('chat-unread-badge');
        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }

    startPolling() {
        this.stopPolling();
        this.pollInterval = setInterval(() => {
            this.loadMessages();
        }, 2000); // 2초마다 새 메시지 확인
    }

    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chat-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    exportChat() {
        if (!this.roomId) return;
        window.open(`/chat/api.php?action=export_chat&room_id=${this.roomId}`, '_blank');
    }

    saveChatState() {
        localStorage.setItem('chat_is_open', this.isOpen);
    }

    loadChatState() {
        const savedRoomId = localStorage.getItem('chat_room_id');
        if (savedRoomId) {
            this.roomId = parseInt(savedRoomId);
        }

        const isOpen = localStorage.getItem('chat_is_open') === 'true';
        if (isOpen && this.roomId) {
            this.openChat();
        }

        // 주기적으로 읽지 않은 메시지 확인
        setInterval(() => {
            this.updateUnreadCount();
        }, 5000);
    }

    getCurrentUser() {
        // localStorage에서 사용자 정보 가져오기 (영구 저장)
        let userId = localStorage.getItem('chat_user_id');
        let userName = sessionStorage.getItem('user_name') || localStorage.getItem('chat_user_name');

        // 사용자 ID가 없으면 생성 (영구 저장)
        if (!userId) {
            userId = 'guest_' + Date.now();
            localStorage.setItem('chat_user_id', userId);
        }

        // 사용자 이름이 없으면 기본값
        if (!userName) {
            userName = '손님';
        } else {
            // 이름도 localStorage에 백업
            localStorage.setItem('chat_user_name', userName);
        }

        return {
            id: userId,
            name: userName
        };
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// 초기화는 chat_widget.php에서 처리 (중복 방지 로직 포함)
