<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>채팅 관리 - 직원용</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            height: 100vh;
            overflow: hidden;
        }

        .admin-container {
            display: flex;
            height: 100vh;
        }

        /* 사이드바 - 채팅방 목록 */
        .sidebar {
            width: 210px;
            background: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sidebar-header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.9;
        }

        .staff-login {
            padding: 15px;
            background: #f9f9f9;
            border-bottom: 1px solid #e0e0e0;
        }

        .staff-login select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box {
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
            background: #f9f9f9;
        }

        .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #4A5FBF;
        }

        .room-list {
            flex: 1;
            overflow-y: auto;
        }

        .room-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .room-item:hover {
            background: #f9f9f9;
        }

        .room-item.active {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
        }

        .room-item-name {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 15px;
        }

        .room-item-header {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
            font-size: 15px;
        }

        .room-item-preview {
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .room-item-message {
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .room-item-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }

        .room-item-unread {
            background: #ff4757;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            float: right;
        }

        /* 메인 채팅 영역 */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header-admin {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: white;
        }

        .chat-header-admin h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }

        .chat-header-admin .participants {
            font-size: 12px;
            color: #666;
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .admin-message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .admin-message.sent {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4A5FBF 0%, #3A4D99 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .message-content {
            max-width: 60%;
        }

        .message-sender {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .admin-message.sent .message-sender {
            text-align: right;
        }

        .message-bubble {
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .admin-message.sent .message-bubble {
            background: linear-gradient(135deg, #4A5FBF 0%, #3A4D99 100%);
            color: white;
        }

        .admin-message.received .message-bubble {
            background: #e9ecef;
            color: #333;
        }

        .admin-message.system .message-bubble {
            background: #e3f2fd;
            color: #1976d2;
            text-align: center;
            font-size: 13px;
        }

        .message-image {
            max-width: 300px;
            border-radius: 12px;
            cursor: pointer;
        }

        .message-time {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }

        .input-area-admin {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-wrapper-admin {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .input-admin {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
        }

        .input-admin:focus {
            border-color: #4A5FBF;
        }

        .btn-admin {
            background: #4A5FBF;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background: #3A4D99;
        }

        .btn-image-admin {
            background: #e3f2fd;
            color: #1976d2;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #90caf9;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-image-admin:hover {
            background: #1976d2;
            color: white;
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
        }

        .btn-image-admin:active {
            transform: scale(1.05);
        }

        /* 툴팁 스타일 */
        .btn-image-admin::before {
            content: '이미지 첨부 (최대 5MB)';
            position: absolute;
            bottom: 120%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            z-index: 1000;
        }

        .btn-image-admin::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.8);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .btn-image-admin:hover::before,
        .btn-image-admin:hover::after {
            opacity: 1;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- 사이드바 - 채팅방 목록 -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>채팅 관리</h1>
                <p>두손기획인쇄 - 고객 지원</p>
            </div>

            <div class="staff-login">
                <select id="staff-select" onchange="staffLogin()">
                    <option value="">직원 선택</option>
                    <option value="staff1">두손1</option>
                    <option value="staff2">두손2</option>
                    <option value="staff3">두손3</option>
                </select>
            </div>

            <div class="search-box" id="search-box" style="display: none;">
                <input type="text" id="room-search" placeholder="고객명, 메시지 검색..." oninput="filterRooms(this.value)">
            </div>

            <div class="room-list" id="room-list">
                <div class="empty-state">
                    <p>로그인 후 채팅방 목록이 표시됩니다.</p>
                </div>
            </div>
        </div>

        <!-- 메인 채팅 영역 -->
        <div class="main-chat">
            <div id="empty-chat" class="empty-state">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                </svg>
                <p>채팅방을 선택하세요</p>
            </div>

            <div id="chat-area" style="display: none; height: 100%; display: flex; flex-direction: column;">
                <div class="chat-header-admin">
                    <h2 id="chat-room-name">채팅방</h2>
                    <div class="participants" id="chat-participants"></div>
                </div>

                <div class="messages-area" id="messages-area"></div>

                <div class="input-area-admin">
                    <div class="input-wrapper-admin">
                        <button class="btn-image-admin" onclick="document.getElementById('admin-image-input').click()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                            </svg>
                        </button>
                        <input type="file" id="admin-image-input" accept="image/*" style="display:none" onchange="uploadAdminImage(event)">
                        <input type="text" class="input-admin" id="admin-message-input" placeholder="메시지를 입력하세요..." onkeypress="if(event.key==='Enter') sendAdminMessage()">
                        <button class="btn-admin" onclick="sendAdminMessage()">전송</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStaffId = null;
        let currentStaffName = null;
        let currentRoomId = null;
        let lastMessageId = 0;
        let pollInterval = null;
        let allRooms = []; // 전체 채팅방 목록 저장

        // 직원 로그인
        function staffLogin() {
            const select = document.getElementById('staff-select');
            currentStaffId = select.value;
            currentStaffName = select.options[select.selectedIndex].text;

            if (currentStaffId) {
                sessionStorage.setItem('user_id', currentStaffId);
                sessionStorage.setItem('user_name', currentStaffName);

                // 검색창 표시
                document.getElementById('search-box').style.display = 'block';

                loadRoomList();
                startRoomPolling();
            }
        }

        // 채팅방 필터링 (검색)
        function filterRooms(searchText) {
            searchText = searchText.toLowerCase().trim();

            if (!searchText) {
                displayRoomList(allRooms);
                return;
            }

            const filtered = allRooms.filter(room => {
                const customerName = (room.customer_name || '').toLowerCase();
                const lastMessage = (room.last_message || '').toLowerCase();

                return customerName.includes(searchText) || lastMessage.includes(searchText);
            });

            displayRoomList(filtered);
        }

        // 채팅방 목록 로드
        async function loadRoomList() {
            if (!currentStaffId) return;

            try {
                const response = await fetch('api.php?action=get_staff_rooms&staff_id=' + currentStaffId);

                // 응답 텍스트 먼저 확인
                const text = await response.text();
                console.log('API 응답 텍스트:', text);

                if (!text.trim()) {
                    console.error('API가 빈 응답을 반환했습니다');
                    return;
                }

                // JSON 파싱 시도
                const data = JSON.parse(text);
                console.log('채팅방 목록 응답:', data);

                if (data.success) {
                    allRooms = data.data; // 전체 목록 저장
                    displayRoomList(allRooms);
                } else {
                    console.error('채팅방 목록 로드 실패:', data.message);
                }
            } catch (error) {
                console.error('채팅방 목록 로드 오류:', error);
            }
        }

        // 채팅방 목록 표시
        function displayRoomList(rooms) {
            const roomList = document.getElementById('room-list');

            if (rooms.length === 0) {
                roomList.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">채팅방이 생성되면 여기에 표시됩니다.</div>';
                return;
            }

            roomList.innerHTML = '';
            rooms.forEach(room => {
                const roomDiv = document.createElement('div');
                roomDiv.className = 'room-item' + (room.id === currentRoomId ? ' active' : '');
                roomDiv.onclick = () => selectRoom(room.id);

                let lastMessageText = '';
                if (room.last_message_type === 'text') {
                    lastMessageText = room.last_message || '메시지 없음';
                    if (lastMessageText.length > 30) {
                        lastMessageText = lastMessageText.substring(0, 30) + '...';
                    }
                } else if (room.last_message_type === 'image') {
                    lastMessageText = '📷 이미지';
                } else {
                    lastMessageText = '메시지 없음';
                }

                let timeText = '방금';
                if (room.last_message_time) {
                    const messageTime = new Date(room.last_message_time);
                    const now = new Date();
                    const diff = Math.floor((now - messageTime) / 1000); // seconds

                    if (diff < 60) {
                        timeText = '방금';
                    } else if (diff < 3600) {
                        timeText = Math.floor(diff / 60) + '분 전';
                    } else if (diff < 86400) {
                        timeText = Math.floor(diff / 3600) + '시간 전';
                    } else if (diff < 604800) {
                        timeText = Math.floor(diff / 86400) + '일 전';
                    } else {
                        timeText = messageTime.toLocaleDateString('ko-KR', { month: 'short', day: 'numeric' });
                    }
                }

                const customerName = room.customer_name || '손님';
                const unreadBadge = room.unread_count > 0
                    ? `<span class="room-item-unread">${room.unread_count}</span>`
                    : '';

                roomDiv.innerHTML = `
                    <div class="room-item-header">
                        <strong>${escapeHtml(customerName)}</strong>
                        ${unreadBadge}
                    </div>
                    <div class="room-item-message">${escapeHtml(lastMessageText)}</div>
                    <div class="room-item-time">${timeText}</div>
                `;

                roomList.appendChild(roomDiv);
            });
        }

        // 채팅방 선택
        async function selectRoom(roomId) {
            currentRoomId = roomId;
            lastMessageId = 0;
            document.getElementById('empty-chat').style.display = 'none';
            document.getElementById('chat-area').style.display = 'flex';

            // 메시지 영역 초기화
            document.getElementById('messages-area').innerHTML = '';

            // 채팅방 정보 로드
            await loadRoomInfo(roomId);

            // 전체 메시지 히스토리 로드 (lastMessageId = 0이면 모든 메시지 가져옴)
            await loadMessages();

            startMessagePolling();

            // 채팅방 목록 새로고침 (활성 상태 표시)
            loadRoomList();
        }

        // 채팅방 정보 로드 (고객 이름 등)
        async function loadRoomInfo(roomId) {
            try {
                const response = await fetch('api.php?action=get_staff_rooms&staff_id=' + currentStaffId);
                const data = await response.json();

                if (data.success) {
                    const room = data.data.find(r => r.id == roomId);
                    if (room) {
                        const customerName = room.customer_name || '손님';
                        const createDate = new Date(room.createdat).toLocaleDateString('ko-KR');

                        document.getElementById('chat-room-name').textContent = customerName + '님과의 대화';
                        document.getElementById('chat-participants').textContent = '시작일: ' + createDate;
                    }
                }
            } catch (error) {
                console.error('채팅방 정보 로드 오류:', error);
            }
        }

        // 메시지 로드
        async function loadMessages() {
            if (!currentRoomId) return;

            try {
                const response = await fetch(`api.php?action=get_messages&room_id=${currentRoomId}&last_id=${lastMessageId}`);
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    data.data.forEach(msg => {
                        appendAdminMessage(msg);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    scrollToBottom();
                }
            } catch (error) {
                console.error('메시지 로드 오류:', error);
            }
        }

        // 메시지 추가
        function appendAdminMessage(msg) {
            const messagesArea = document.getElementById('messages-area');
            const isSent = msg.senderid === currentStaffId;
            const isSystem = msg.senderid === 'system';

            // 디버그: 메시지 표시 로직 확인
            console.log('💬 메시지 표시:', {
                senderid: msg.senderid,
                sendername: msg.sendername,
                currentStaffId: currentStaffId,
                currentStaffName: currentStaffName,
                isSent: isSent,
                isSystem: isSystem
            });

            const messageDiv = document.createElement('div');
            messageDiv.className = `admin-message ${isSent ? 'sent' : 'received'} ${isSystem ? 'system' : ''}`;

            let avatarHtml = '';
            if (!isSent && !isSystem) {
                const initial = msg.sendername.charAt(0);
                avatarHtml = `<div class="message-avatar">${initial}</div>`;
            }

            let contentHtml = '';
            if (msg.messagetype === 'text') {
                contentHtml = `<div class="message-bubble">${escapeHtml(msg.message)}</div>`;
            } else if (msg.messagetype === 'image') {
                contentHtml = `
                    <div class="message-bubble">
                        <img src="/${msg.filepath}" alt="${msg.filename}" class="message-image" onclick="window.open('/${msg.filepath}', '_blank')">
                    </div>
                `;
            }

            const time = new Date(msg.createdat).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });

            messageDiv.innerHTML = `
                ${avatarHtml}
                <div class="message-content">
                    ${!isSystem ? `<div class="message-sender">${msg.sendername}</div>` : ''}
                    ${contentHtml}
                    <div class="message-time">${time}</div>
                </div>
            `;

            messagesArea.appendChild(messageDiv);
        }

        // 메시지 전송
        async function sendAdminMessage() {
            const input = document.getElementById('admin-message-input');
            const message = input.value.trim();

            if (!message || !currentRoomId) return;

            // 디버그: 현재 직원 정보 확인
            console.log('📤 메시지 전송:', {
                staffId: currentStaffId,
                staffName: currentStaffName,
                message: message
            });

            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('room_id', currentRoomId);
                formData.append('message', message);
                formData.append('sender_id', currentStaffId); // 직원 ID 전송
                formData.append('sender_name', currentStaffName); // 직원 이름 전송

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    input.value = '';
                    loadMessages();
                }
            } catch (error) {
                console.error('메시지 전송 오류:', error);
            }
        }

        // 이미지 업로드
        async function uploadAdminImage(event) {
            const file = event.target.files[0];
            if (!file || !currentRoomId) return;

            try {
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('room_id', currentRoomId);
                formData.append('image', file);
                formData.append('sender_id', currentStaffId); // 직원 ID 전송
                formData.append('sender_name', currentStaffName); // 직원 이름 전송

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    loadMessages();
                    event.target.value = '';
                } else {
                    alert('이미지 업로드 실패: ' + data.message);
                }
            } catch (error) {
                console.error('이미지 업로드 오류:', error);
            }
        }

        function scrollToBottom() {
            const messagesArea = document.getElementById('messages-area');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function startMessagePolling() {
            stopMessagePolling();
            pollInterval = setInterval(() => {
                loadMessages();
            }, 2000);
        }

        function stopMessagePolling() {
            if (pollInterval) {
                clearInterval(pollInterval);
            }
        }

        function startRoomPolling() {
            setInterval(() => {
                loadRoomList();
            }, 5000);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // 테스트용: 임시 채팅방 생성
        window.createTestRoom = async function() {
            const response = await fetch('api.php?action=get_or_create_room');
            const data = await response.json();
            if (data.success) {
                alert('채팅방 생성됨: ' + data.data.id);
                selectRoom(data.data.id);
            }
        };

        // 페이지 로드 시 직원 정보 복원
        window.addEventListener('DOMContentLoaded', function() {
            const savedStaffId = sessionStorage.getItem('user_id');
            const savedStaffName = sessionStorage.getItem('user_name');

            if (savedStaffId && savedStaffName) {
                // sessionStorage에서 복원
                currentStaffId = savedStaffId;
                currentStaffName = savedStaffName;

                const select = document.getElementById('staff-select');
                select.value = savedStaffId;

                console.log('✅ 직원 정보 복원:', {
                    staffId: currentStaffId,
                    staffName: currentStaffName
                });

                // 검색창 표시
                document.getElementById('search-box').style.display = 'block';

                loadRoomList();
                startRoomPolling();
            } else {
                // sessionStorage 없으면 기본값으로 자동 로그인
                const select = document.getElementById('staff-select');
                select.value = 'staff1';
                staffLogin();
            }
        });
    </script>
</body>
</html>
