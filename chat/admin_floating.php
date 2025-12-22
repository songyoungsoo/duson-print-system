<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>채팅 관리</title>
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
            font-family: 'Noto Sans KR', sans-serif;
            background: white;
            height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        /* 최소화 바 */
        .minimized-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 12px 20px;
            border-radius: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 10000;
        }

        .minimized-bar:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .minimized-bar .icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .minimized-bar .text {
            font-weight: 500;
            color: #333;
        }

        /* 플로팅 윈도우 */
        .admin-window {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .admin-window.hidden {
            display: none;
        }

        /* 윈도우 헤더 (드래그 영역) */
        .window-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: move;
            user-select: none;
        }

        .window-title {
            font-size: 15px;
            font-weight: 500;
        }

        .window-controls {
            display: flex;
            gap: 8px;
        }

        .window-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .window-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* 윈도우 컨텐츠 */
        .window-content {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        /* 사이드바 */
        .sidebar {
            width: 280px;
            background: #f9f9f9;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .staff-login {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .staff-login select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .room-list {
            flex: 1;
            overflow-y: auto;
        }

        .room-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .room-item:hover {
            background: #f0f0f0;
        }

        .room-item.active {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
        }

        .room-item-header {
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
            font-size: 14px;
        }

        .room-item-message {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .room-item-time {
            font-size: 10px;
            color: #999;
            margin-top: 4px;
        }

        /* 채팅 영역 */
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header-admin {
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .chat-header-admin h2 {
            font-size: 15px;
            color: #333;
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f5f5f5;
        }

        .admin-message {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .admin-message.sent {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4A5FBF 0%, #3A4D99 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .message-content {
            max-width: 60%;
        }

        .message-sender {
            font-size: 10px;
            color: #666;
            margin-bottom: 4px;
        }

        .message-bubble {
            background: white;
            padding: 10px 14px;
            border-radius: 16px;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            font-size: 13px;
        }

        .admin-message.sent .message-bubble {
            background: linear-gradient(135deg, #4A5FBF 0%, #3A4D99 100%);
            color: white;
        }

        .message-time {
            font-size: 9px;
            color: #999;
            margin-top: 4px;
        }

        .input-area-admin {
            padding: 15px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-wrapper-admin {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .input-admin {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 13px;
            outline: none;
        }

        .btn-admin {
            background: #4A5FBF;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-admin:hover {
            background: #3A4D99;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
        }
    </style>
</head>
<body>
    <!-- 최소화 바 -->
    <div class="minimized-bar" id="minimized-bar" onclick="restoreWindow()">
        <div class="icon">💬</div>
        <div class="text">채팅 관리</div>
    </div>

    <!-- 플로팅 윈도우 -->
    <div class="admin-window" id="admin-window">
        <div class="window-header" id="window-header">
            <div class="window-title">채팅 관리 - 두손기획인쇄</div>
            <div class="window-controls">
                <button class="window-btn" onclick="minimizeWindow()" title="최소화">−</button>
                <button class="window-btn" onclick="closeWindow()" title="닫기">×</button>
            </div>
        </div>

        <div class="window-content">
            <!-- 사이드바 -->
            <div class="sidebar">
                <div class="staff-login">
                    <select id="staff-select" onchange="staffLogin()">
                        <option value="">직원 선택</option>
                        <option value="staff1">직원1</option>
                        <option value="staff2">직원2</option>
                        <option value="staff3">직원3</option>
                    </select>
                </div>

                <div class="room-list" id="room-list">
                    <div class="empty-state">
                        <p>로그인 후 채팅방 표시</p>
                    </div>
                </div>
            </div>

            <!-- 메인 채팅 영역 -->
            <div class="main-chat">
                <div id="empty-chat" class="empty-state">
                    <p>채팅방을 선택하세요</p>
                </div>

                <div id="chat-area" style="display: none; height: 100%; display: flex; flex-direction: column;">
                    <div class="chat-header-admin">
                        <h2 id="chat-room-name">채팅방</h2>
                    </div>

                    <div class="messages-area" id="messages-area"></div>

                    <div class="input-area-admin">
                        <div class="input-wrapper-admin">
                            <input type="text" class="input-admin" id="admin-message-input" placeholder="메시지 입력..." onkeypress="if(event.key==='Enter') sendAdminMessage()">
                            <button class="btn-admin" onclick="sendAdminMessage()">전송</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="admin_floating.js"></script>
</body>
</html>
