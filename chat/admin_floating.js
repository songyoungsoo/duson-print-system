// 전역 변수
let currentStaffId = null;
let currentRoomId = null;
let lastMessageId = 0;
let pollInterval = null;
let allRooms = [];

// 드래그 관련 변수
let isDragging = false;
let currentX;
let currentY;
let initialX;
let initialY;
let xOffset = 0;
let yOffset = 0;

// 윈도우 드래그 기능
const adminWindow = document.getElementById('admin-window');
const windowHeader = document.getElementById('window-header');

windowHeader.addEventListener('mousedown', dragStart);
document.addEventListener('mousemove', drag);
document.addEventListener('mouseup', dragEnd);

function dragStart(e) {
    if (e.target.closest('.window-btn')) return; // 버튼 클릭 시 드래그 방지

    initialX = e.clientX - xOffset;
    initialY = e.clientY - yOffset;
    isDragging = true;
    windowHeader.style.cursor = 'grabbing';
}

function drag(e) {
    if (isDragging) {
        e.preventDefault();
        currentX = e.clientX - initialX;
        currentY = e.clientY - initialY;
        xOffset = currentX;
        yOffset = currentY;

        setTranslate(currentX, currentY, adminWindow);
    }
}

function dragEnd() {
    initialX = currentX;
    initialY = currentY;
    isDragging = false;
    windowHeader.style.cursor = 'move';
}

function setTranslate(xPos, yPos, el) {
    el.style.transform = `translate(${xPos}px, ${yPos}px)`;
}

// 최소화/복원
function minimizeWindow() {
    document.getElementById('admin-window').classList.add('hidden');
    document.getElementById('minimized-bar').style.display = 'flex';
}

function restoreWindow() {
    document.getElementById('admin-window').classList.remove('hidden');
    document.getElementById('minimized-bar').style.display = 'none';
}

function closeWindow() {
    if (confirm('채팅 관리 창을 닫으시겠습니까?')) {
        window.close();
    }
}

// 직원 로그인
function staffLogin() {
    const selectElement = document.getElementById('staff-select');
    currentStaffId = selectElement.value;

    if (currentStaffId) {
        loadStaffRooms();
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(loadStaffRooms, 3000);
    } else {
        if (pollInterval) clearInterval(pollInterval);
        document.getElementById('room-list').innerHTML = '<div class="empty-state"><p>로그인 후 채팅방 표시</p></div>';
    }
}

// 채팅방 목록 로드
async function loadStaffRooms() {
    if (!currentStaffId) return;

    try {
        const response = await fetch(`api.php?action=get_staff_rooms&staff_id=${currentStaffId}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            allRooms = data.data;
            renderRooms(allRooms);
        } else {
            document.getElementById('room-list').innerHTML = '<div class="empty-state"><p>채팅방이 없습니다</p></div>';
        }
    } catch (error) {
        console.error('채팅방 로드 오류:', error);
    }
}

// 채팅방 목록 렌더링
function renderRooms(rooms) {
    const roomList = document.getElementById('room-list');
    roomList.innerHTML = '';

    rooms.forEach(room => {
        const roomDiv = document.createElement('div');
        roomDiv.className = 'room-item';
        roomDiv.dataset.roomId = room.id;
        if (room.id == currentRoomId) roomDiv.classList.add('active');
        roomDiv.onclick = () => selectRoom(room.id);

        const customerName = room.customer_name || '손님';
        const lastMessageText = room.last_message || '메시지 없음';
        const timeText = room.last_message_time ? new Date(room.last_message_time).toLocaleTimeString('ko-KR', {hour: '2-digit', minute: '2-digit'}) : '';

        roomDiv.innerHTML = `
            <div class="room-item-header">${escapeHtml(customerName)}</div>
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
    document.getElementById('messages-area').innerHTML = '';

    await loadRoomInfo(roomId);
    await loadMessages();

    // 활성 채팅방 표시 업데이트
    document.querySelectorAll('.room-item').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.room-item').forEach(item => {
        if (item.dataset.roomId == roomId) {
            item.classList.add('active');
        }
    });
}

// 채팅방 정보 로드
async function loadRoomInfo(roomId) {
    try {
        const response = await fetch(`api.php?action=get_room_info&room_id=${roomId}`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('chat-room-name').textContent = data.data.customername || '손님';
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

    const messageDiv = document.createElement('div');
    messageDiv.className = `admin-message ${isSent ? 'sent' : 'received'}`;

    let avatarHtml = '';
    if (!isSent) {
        const initial = msg.sendername.charAt(0);
        avatarHtml = `<div class="message-avatar">${initial}</div>`;
    }

    const time = new Date(msg.createdat).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });

    messageDiv.innerHTML = `
        ${avatarHtml}
        <div class="message-content">
            ${!isSent ? `<div class="message-sender">${msg.sendername}</div>` : ''}
            <div class="message-bubble">${escapeHtml(msg.message)}</div>
            <div class="message-time">${time}</div>
        </div>
    `;

    messagesArea.appendChild(messageDiv);
}

// 메시지 전송
async function sendAdminMessage() {
    const input = document.getElementById('admin-message-input');
    const message = input.value.trim();

    if (!message || !currentRoomId || !currentStaffId) return;

    try {
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('room_id', currentRoomId);
        formData.append('sender_id', currentStaffId);
        formData.append('sender_name', `직원${currentStaffId.replace('staff', '')}`);
        formData.append('message', message);
        formData.append('message_type', 'text');

        const response = await fetch('api.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            input.value = '';
            await loadMessages();
        } else {
            alert('메시지 전송 실패: ' + data.message);
        }
    } catch (error) {
        console.error('메시지 전송 오류:', error);
        alert('메시지 전송 중 오류가 발생했습니다.');
    }
}

// 스크롤 하단으로
function scrollToBottom() {
    const messagesArea = document.getElementById('messages-area');
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

// HTML 이스케이프
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

// 페이지 로드 시 staff1 자동 로그인
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('staff-select').value = 'staff1';
    staffLogin();
});
