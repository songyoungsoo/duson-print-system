<?php
// 채팅 API - 메시지 전송, 조회, 이미지 업로드
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_or_create_room':
        getOrCreateRoom();
        break;
    case 'get_messages':
        getMessages();
        break;
    case 'send_message':
        sendMessage();
        break;
    case 'upload_image':
    case 'upload_file':
        uploadFile();
        break;
    case 'mark_as_read':
        markAsRead();
        break;
    case 'get_unread_count':
        getUnreadCount();
        break;
    case 'export_chat':
        exportChat();
        break;
    case 'get_staff_rooms':
        getStaffRooms();
        break;
    case 'get_admin_unread_count':
        getAdminUnreadCount();
        break;
    default:
        jsonResponse(false, null, '잘못된 요청입니다.');
}

// 채팅방 가져오기 또는 생성
function getOrCreateRoom() {
    global $db;
    $user = getCurrentUser();

    // 기존 채팅방 찾기
    $query = "SELECT r.* FROM chatrooms r
              INNER JOIN chatparticipants p ON r.id = p.roomid
              WHERE p.userid = ? AND r.isactive = 1
              ORDER BY r.updatedat DESC LIMIT 1";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 's', $user['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        jsonResponse(true, $row);
    }

    // 새 채팅방 생성
    $roomName = '고객 지원 채팅 - ' . $user['name'];
    $query = "INSERT INTO chatrooms (roomname, roomtype, createdby) VALUES (?, 'group', ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $roomName, $user['id']);

    if (mysqli_stmt_execute($stmt)) {
        $roomId = mysqli_insert_id($db);

        // 고객 참여자 추가
        $query = "INSERT INTO chatparticipants (roomid, userid, username, isadmin) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        $isAdmin = 0;
        mysqli_stmt_bind_param($stmt, 'issi', $roomId, $user['id'], $user['name'], $isAdmin);
        mysqli_stmt_execute($stmt);

        // 모든 직원을 채팅방에 자동 참여
        $staffQuery = "SELECT staffid, staffname FROM chatstaff WHERE isonline = 1";
        $staffResult = mysqli_query($db, $staffQuery);

        while ($staff = mysqli_fetch_assoc($staffResult)) {
            $staffInsertQuery = "INSERT INTO chatparticipants (roomid, userid, username, isadmin)
                                VALUES (?, ?, ?, 1)";
            $staffStmt = mysqli_prepare($db, $staffInsertQuery);
            mysqli_stmt_bind_param($staffStmt, 'iss', $roomId, $staff['staffid'], $staff['staffname']);
            mysqli_stmt_execute($staffStmt);
        }

        // 시스템 메시지 추가
        $systemMsg = "채팅방이 시작되었습니다. 직원이 곧 응답할 예정입니다.";
        $systemQuery = "INSERT INTO chatmessages (roomid, senderid, sendername, messagetype, message)
                       VALUES (?, 'system', '시스템', 'text', ?)";
        $systemStmt = mysqli_prepare($db, $systemQuery);
        mysqli_stmt_bind_param($systemStmt, 'is', $roomId, $systemMsg);
        mysqli_stmt_execute($systemStmt);

        jsonResponse(true, ['id' => $roomId, 'roomname' => $roomName]);
    } else {
        jsonResponse(false, null, '채팅방 생성 실패');
    }
}

// 메시지 조회
function getMessages() {
    global $db;
    $roomId = $_GET['room_id'] ?? 0;
    $lastId = $_GET['last_id'] ?? 0;

    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }

    $query = "SELECT * FROM chatmessages
              WHERE roomid = ? AND id > ?
              ORDER BY createdat ASC";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $roomId, $lastId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }

    jsonResponse(true, $messages);
}

// 메시지 전송
function sendMessage() {
    global $db;
    $user = getCurrentUser();

    $roomId = $_POST['room_id'] ?? 0;
    $message = $_POST['message'] ?? '';
    $senderId = $_POST['sender_id'] ?? $user['id']; // 전달된 ID 우선 사용
    $senderName = $_POST['sender_name'] ?? $user['name']; // 전달된 이름 우선 사용

    if (!$roomId || !$message) {
        jsonResponse(false, null, '채팅방 ID와 메시지가 필요합니다.');
    }

    $query = "INSERT INTO chatmessages (roomid, senderid, sendername, messagetype, message)
              VALUES (?, ?, ?, 'text', ?)";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'isss', $roomId, $senderId, $senderName, $message);

    if (mysqli_stmt_execute($stmt)) {
        $messageId = mysqli_insert_id($db);

        // 채팅방 업데이트 시간 갱신
        $updateQuery = "UPDATE chatrooms SET updatedat = NOW() WHERE id = ?";
        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
        mysqli_stmt_execute($updateStmt);

        jsonResponse(true, ['id' => $messageId, 'message' => $message]);
    } else {
        jsonResponse(false, null, '메시지 전송 실패');
    }
}

// 파일 업로드 (이미지 + PDF)
function uploadFile() {
    global $db;
    $user = getCurrentUser();

    $roomId = $_POST['room_id'] ?? 0;
    $senderId = $_POST['sender_id'] ?? $user['id'];
    $senderName = $_POST['sender_name'] ?? $user['name'];

    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }

    // 'image' 또는 'file' 키로 파일 받기 (하위 호환성)
    $file = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
    }

    if (!$file) {
        jsonResponse(false, null, '파일 업로드 오류');
    }

    // 파일 크기 체크
    if ($file['size'] > CHAT_UPLOAD_MAX_SIZE) {
        jsonResponse(false, null, '파일 크기 초과 (최대 10MB)\n대용량 파일은 dsp1830@naver.com 으로 보내주세요.');
    }

    // 허용된 파일 타입
    $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $documentTypes = [
        'application/pdf',                                                      // PDF
        'application/msword',                                                   // DOC
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
        'application/vnd.ms-excel',                                             // XLS
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',    // XLSX
        'application/vnd.ms-powerpoint',                                        // PPT
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX
        'application/haansofthwp',                                              // HWP
        'application/x-hwp',                                                    // HWP (alt)
        'application/vnd.hancom.hwp',                                           // HWP (alt2)
        'application/postscript',                                               // AI
        'application/illustrator',                                              // AI (alt)
        'image/vnd.adobe.photoshop',                                            // PSD
        'image/x-photoshop',                                                    // PSD (alt)
        'application/zip',                                                      // ZIP
        'application/x-zip-compressed',                                         // ZIP (alt)
        'text/plain',                                                           // TXT
        'text/x-c',                                                             // TXT (code)
        'text/x-java',                                                          // TXT (java)
        'text/x-python',                                                        // TXT (python)
        'text/html',                                                            // HTML
        'text/css',                                                             // CSS
        'text/javascript',                                                      // JS
        'application/octet-stream',                                             // 일반 바이너리 (확장자로 2차 확인)
    ];
    $allowedTypes = array_merge($imageTypes, $documentTypes);

    // 확장자로도 추가 확인 (MIME 타입 감지 실패 대비)
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp', 'hwpx', 'ai', 'psd', 'zip', 'txt'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // 확장자 확인
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // MIME 타입 또는 확장자로 허용 여부 확인
    if (!in_array($mimeType, $allowedTypes) && !in_array($extension, $allowedExtensions)) {
        jsonResponse(false, null, '허용되지 않는 파일 형식입니다. (이미지, PDF, 문서, 한글, 엑셀, PPT, AI, PSD, ZIP, TXT 파일만 가능)');
    }

    // 메시지 타입 결정
    $messageType = in_array($mimeType, $imageTypes) ? 'image' : 'file';
    $filePrefix = $messageType === 'image' ? 'chat_img_' : 'chat_file_';

    // 파일명 생성
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = uniqid($filePrefix) . '.' . $extension;
    $filePath = CHAT_UPLOAD_DIR . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // DB에 저장
        $query = "INSERT INTO chatmessages (roomid, senderid, sendername, messagetype, message, filepath, filename, filesize)
                  VALUES (?, ?, ?, ?, '', ?, ?, ?)";

        $stmt = mysqli_prepare($db, $query);
        $relativeFilePath = 'chat_uploads/' . $fileName;
        mysqli_stmt_bind_param($stmt, 'isssssi', $roomId, $senderId, $senderName, $messageType,
                               $relativeFilePath, $file['name'], $file['size']);

        if (mysqli_stmt_execute($stmt)) {
            $messageId = mysqli_insert_id($db);

            // 채팅방 업데이트 시간 갱신
            $updateQuery = "UPDATE chatrooms SET updatedat = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($db, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
            mysqli_stmt_execute($updateStmt);

            jsonResponse(true, [
                'id' => $messageId,
                'filepath' => $relativeFilePath,
                'filename' => $file['name'],
                'messagetype' => $messageType
            ]);
        } else {
            unlink($filePath);
            jsonResponse(false, null, 'DB 저장 실패');
        }
    } else {
        jsonResponse(false, null, '파일 업로드 실패');
    }
}

// 읽음 처리
function markAsRead() {
    global $db;
    $user = getCurrentUser();
    $roomId = $_POST['room_id'] ?? 0;

    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }

    $query = "UPDATE chatmessages SET isread = 1
              WHERE roomid = ? AND senderid != ?";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'is', $roomId, $user['id']);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, ['affected_rows' => mysqli_affected_rows($db)]);
    } else {
        jsonResponse(false, null, '읽음 처리 실패');
    }
}

// 읽지 않은 메시지 수
function getUnreadCount() {
    global $db;
    $user = getCurrentUser();
    $roomId = $_GET['room_id'] ?? 0;

    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }

    $query = "SELECT COUNT(*) as count FROM chatmessages
              WHERE roomid = ? AND senderid != ? AND isread = 0";

    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'is', $roomId, $user['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    jsonResponse(true, ['count' => $row['count']]);
}

// 채팅 내용 내보내기
function exportChat() {
    global $db;
    $roomId = $_GET['room_id'] ?? 0;

    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }

    $query = "SELECT * FROM chatmessages WHERE roomid = ? ORDER BY createdat ASC";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $roomId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }

    // 텍스트 파일로 내보내기
    $content = "=== 채팅 내역 ===\n\n";
    foreach ($messages as $msg) {
        $date = date('Y-m-d H:i:s', strtotime($msg['createdat']));
        $content .= "[{$date}] {$msg['sendername']}: ";

        if ($msg['messagetype'] == 'text') {
            $content .= $msg['message'];
        } elseif ($msg['messagetype'] == 'image') {
            $content .= "[이미지: {$msg['filename']}]";
        }
        $content .= "\n";
    }

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="chat_export_' . date('Ymd_His') . '.txt"');
    echo $content;
    exit;
}

// 직원용 채팅방 목록 (모든 채팅방 + 마지막 메시지 + 읽지 않은 수)
function getStaffRooms() {
    global $db;
    $staffId = $_GET['staff_id'] ?? '';

    if (!$staffId) {
        jsonResponse(false, null, '직원 ID가 필요합니다.');
        return;
    }

    // 모든 채팅방 조회 (직원이 참여한 방 + 최근 활동 순)
    $query = "SELECT
                r.id,
                r.roomname,
                r.createdat,
                r.updatedat,
                (SELECT COUNT(*)
                 FROM chatmessages m
                 WHERE m.roomid = r.id
                 AND m.senderid != ?
                 AND m.isread = 0) as unread_count,
                (SELECT m2.message
                 FROM chatmessages m2
                 WHERE m2.roomid = r.id
                 ORDER BY m2.createdat DESC
                 LIMIT 1) as last_message,
                (SELECT m3.messagetype
                 FROM chatmessages m3
                 WHERE m3.roomid = r.id
                 ORDER BY m3.createdat DESC
                 LIMIT 1) as last_message_type,
                (SELECT m4.createdat
                 FROM chatmessages m4
                 WHERE m4.roomid = r.id
                 ORDER BY m4.createdat DESC
                 LIMIT 1) as last_message_time,
                (SELECT p.username
                 FROM chatparticipants p
                 WHERE p.roomid = r.id
                 AND p.isadmin = 0
                 LIMIT 1) as customer_name
              FROM chatrooms r
              INNER JOIN chatparticipants cp ON r.id = cp.roomid
              WHERE cp.userid = ? AND r.isactive = 1
              ORDER BY r.updatedat DESC";

    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        jsonResponse(false, null, 'SQL 준비 실패: ' . mysqli_error($db));
        return;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $staffId, $staffId);

    if (!mysqli_stmt_execute($stmt)) {
        jsonResponse(false, null, 'SQL 실행 실패: ' . mysqli_stmt_error($stmt));
        return;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        jsonResponse(false, null, '결과 가져오기 실패: ' . mysqli_stmt_error($stmt));
        return;
    }

    $rooms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }

    jsonResponse(true, $rooms);
}

// 관리자 전용: 전체 읽지 않은 고객 메시지 수
function getAdminUnreadCount() {
    global $db;
    
    // 관리자 체크
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        jsonResponse(true, ['count' => 0, 'is_admin' => false]);
        return;
    }
    
    // 읽지 않은 고객 메시지 수 (staff, system 제외)
    $query = "SELECT COUNT(*) as count FROM chatmessages 
              WHERE isread = 0 
                AND senderid NOT LIKE 'staff%' 
                AND senderid != 'system'";
    
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);
    
    jsonResponse(true, ['count' => (int)$row['count'], 'is_admin' => true]);
}
?>
