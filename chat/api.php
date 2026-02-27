<?php
// 채팅 API - 메시지 전송, 조회, 이미지 업로드, AI 긴급대응
error_reporting(E_ALL);
ini_set('display_errors', 0); // 프로덕션 안전
require_once 'config.php';

// 액션 읽기: GET, POST, JSON body 순서로 확인
$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $json = json_decode(file_get_contents('php://input'), true);
    $action = $json['action'] ?? '';
}

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
    case 'get_unread_rooms':
        getUnreadRooms();
        break;
    case 'get_chat_config':
        getChatConfig();
        break;
    case 'update_chat_config':
        updateChatConfig();
        break;
    // === 관리자 채팅 관리 액션 ===
    case 'admin_mark_all_read':
        adminMarkAllRead();
        break;
    case 'admin_close_room':
        adminCloseRoom();
        break;
    case 'admin_cleanup_empty':
        adminCleanupEmpty();
        break;
    case 'admin_auto_expire':
        adminAutoExpire();
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

// 메시지 조회 + AI 긴급대응 체크 (고객 폴링에 피기백)
function getMessages() {
    global $db;
    $roomId = intval($_GET['room_id'] ?? 0);
    $lastId = intval($_GET['last_id'] ?? 0);
    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
    }
    // AI 긴급대응 체크 (메시지 조회 전에 실행)
    checkAndTriggerAI($roomId);
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

// 메시지 전송 + 타임스탬프 추적 + AI 퇴장 처리
function sendMessage() {
    global $db;
    $user = getCurrentUser();
    $roomId = intval($_POST['room_id'] ?? 0);
    $message = $_POST['message'] ?? '';
    $senderId = $_POST['sender_id'] ?? $user['id'];
    $senderName = $_POST['sender_name'] ?? $user['name'];
    if (!$roomId || !$message) {
        jsonResponse(false, null, '채팅방 ID와 메시지가 필요합니다.');
    }
    // 관리자(staff) 메시지 시: AI가 활성 상태이면 퇴장 처리
    $isStaff = (strpos($senderId, 'staff') === 0);
    $isCustomer = (strpos($senderId, 'guest_') === 0);
    if ($isStaff) {
        deactivateAIIfActive($roomId);
    }

    // 메시지 INSERT
    $query = "INSERT INTO chatmessages (roomid, senderid, sendername, messagetype, message)
              VALUES (?, ?, ?, 'text', ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'isss', $roomId, $senderId, $senderName, $message);
    if (mysqli_stmt_execute($stmt)) {
        $messageId = mysqli_insert_id($db);
        // 채팅방 업데이트 시간 갱신 + 발신자별 타임스탬프 업데이트
        if ($isCustomer) {
            $updateQuery = "UPDATE chatrooms SET updatedat = NOW(), last_customer_msg_at = NOW() WHERE id = ?";
        } elseif ($isStaff) {
            $updateQuery = "UPDATE chatrooms SET updatedat = NOW(), last_staff_msg_at = NOW() WHERE id = ?";
        } else {
            $updateQuery = "UPDATE chatrooms SET updatedat = NOW() WHERE id = ?";
        }
        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
        mysqli_stmt_execute($updateStmt);
        jsonResponse(true, ['id' => $messageId]);
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
    
    $query = "SELECT COUNT(*) as count FROM chatmessages cm
              INNER JOIN chatrooms cr ON cr.id = cm.roomid AND cr.isactive = 1
              WHERE cm.isread = 0 
                AND cm.senderid NOT LIKE 'staff%' 
                AND cm.senderid != 'system'";
    
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);
    
    jsonResponse(true, ['count' => (int)$row['count'], 'is_admin' => true]);
}

// 관리자 전용: 미읽은 메시지가 있는 채팅방 목록
function getUnreadRooms() {
    global $db;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        jsonResponse(true, ['rooms' => [], 'total_unread' => 0]);
        return;
    }

    $query = "SELECT
                r.id as room_id,
                (SELECT p.username FROM chatparticipants p
                 WHERE p.roomid = r.id AND p.isadmin = 0 LIMIT 1) as customer_name,
                COUNT(m.id) as unread_count,
                (SELECT m2.message FROM chatmessages m2
                 WHERE m2.roomid = r.id ORDER BY m2.createdat DESC LIMIT 1) as last_message,
                (SELECT m3.createdat FROM chatmessages m3
                 WHERE m3.roomid = r.id ORDER BY m3.createdat DESC LIMIT 1) as last_message_time
              FROM chatrooms r
              INNER JOIN chatmessages m ON m.roomid = r.id
                AND m.isread = 0
                AND m.senderid NOT LIKE 'staff%'
                AND m.senderid != 'system'
              WHERE r.isactive = 1
              GROUP BY r.id
              ORDER BY last_message_time DESC
              LIMIT 10";

    $result = mysqli_query($db, $query);
    if (!$result) {
        jsonResponse(false, null, 'SQL 오류: ' . mysqli_error($db));
        return;
    }

    $rooms = [];
    $totalUnread = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row['unread_count'] = (int)$row['unread_count'];
        $totalUnread += $row['unread_count'];
        $rooms[] = $row;
    }

    jsonResponse(true, ['rooms' => $rooms, 'total_unread' => $totalUnread]);
}
// ============================================================
// AI 긴급대응 함수들
// ============================================================

/**
 * 60초 무응답 감지 → AI 자동 응답 트리거
 * getMessages() 폴링에 피기백으로 실행
 */
function checkAndTriggerAI($roomId) {
    global $db;

    // 1. 채팅방 AI 상태 확인
    $roomQuery = "SELECT ai_active, last_customer_msg_at, last_staff_msg_at FROM chatrooms WHERE id = ? AND isactive = 1";
    $roomStmt = mysqli_prepare($db, $roomQuery);
    mysqli_stmt_bind_param($roomStmt, 'i', $roomId);
    mysqli_stmt_execute($roomStmt);
    $roomResult = mysqli_stmt_get_result($roomStmt);
    $room = mysqli_fetch_assoc($roomResult);

    if (!$room) return;

    // DB 설정 로드
    $cfg = loadChatConfig($db);
    $aiEnabled = $cfg['ai_enabled'] ?? true;
    $aiWaitSec = $cfg['ai_wait_seconds'] ?? 60;
    $aiHourStart = $cfg['ai_hour_start'] ?? '00:00';
    $aiHourEnd = $cfg['ai_hour_end'] ?? '23:59';

    // AI 비활성화 체크
    if (!$aiEnabled) return;

    // AI 운영 시간대 체크
    $now = date('H:i');
    if ($aiHourStart <= $aiHourEnd) {
        if ($now < $aiHourStart || $now > $aiHourEnd) return;
    } else {
        if ($now < $aiHourStart && $now > $aiHourEnd) return;
    }

    // AI가 이미 활성 상태이면 → 고객의 새 메시지에 AI가 응답
    if ($room['ai_active'] == 1) {
        handleAIConversation($roomId);
        return;
    }

    // 2. 마지막 고객 메시지 시간 확인 (DB에서 직접 조회 — 정확성)
    $lastCustQuery = "SELECT id, message, createdat FROM chatmessages 
                      WHERE roomid = ? AND senderid LIKE 'guest_%' 
                      ORDER BY createdat DESC LIMIT 1";
    $lastCustStmt = mysqli_prepare($db, $lastCustQuery);
    mysqli_stmt_bind_param($lastCustStmt, 'i', $roomId);
    mysqli_stmt_execute($lastCustStmt);
    $lastCustResult = mysqli_stmt_get_result($lastCustStmt);
    $lastCustMsg = mysqli_fetch_assoc($lastCustResult);

    if (!$lastCustMsg) return;

    // 3. 마지막 응답 시간 확인 (staff 또는 ai_bot)
    $lastReplyQuery = "SELECT createdat FROM chatmessages 
                       WHERE roomid = ? AND (senderid LIKE 'staff%' OR senderid = 'ai_bot') 
                       ORDER BY createdat DESC LIMIT 1";
    $lastReplyStmt = mysqli_prepare($db, $lastReplyQuery);
    mysqli_stmt_bind_param($lastReplyStmt, 'i', $roomId);
    mysqli_stmt_execute($lastReplyStmt);
    $lastReplyResult = mysqli_stmt_get_result($lastReplyStmt);
    $lastReply = mysqli_fetch_assoc($lastReplyResult);

    if ($lastReply && strtotime($lastReply['createdat']) >= strtotime($lastCustMsg['createdat'])) {
        return;
    }

    // 4. N초 경과 확인 (DB 설정)
    $elapsed = time() - strtotime($lastCustMsg['createdat']);
    if ($elapsed < $aiWaitSec) return;

    // 5. AI 진입!
    activateAI($roomId, $lastCustMsg['message']);
}

/**
 * AI 활성 상태에서 고객의 새 메시지에 AI가 응답
 */
function handleAIConversation($roomId) {
    global $db;

    // 마지막 AI 메시지 시간 확인
    $lastAiQuery = "SELECT createdat FROM chatmessages 
                    WHERE roomid = ? AND senderid = 'ai_bot' 
                    ORDER BY createdat DESC LIMIT 1";
    $lastAiStmt = mysqli_prepare($db, $lastAiQuery);
    mysqli_stmt_bind_param($lastAiStmt, 'i', $roomId);
    mysqli_stmt_execute($lastAiStmt);
    $lastAiResult = mysqli_stmt_get_result($lastAiStmt);
    $lastAiMsg = mysqli_fetch_assoc($lastAiResult);

    if (!$lastAiMsg) return;

    // AI 마지막 응답 이후 고객 메시지가 있는지 확인
    $newCustQuery = "SELECT id, message FROM chatmessages 
                     WHERE roomid = ? AND senderid LIKE 'guest_%' AND createdat > ? 
                     ORDER BY createdat ASC LIMIT 1";
    $newCustStmt = mysqli_prepare($db, $newCustQuery);
    $aiTime = $lastAiMsg['createdat'];
    mysqli_stmt_bind_param($newCustStmt, 'is', $roomId, $aiTime);
    mysqli_stmt_execute($newCustStmt);
    $newCustResult = mysqli_stmt_get_result($newCustStmt);
    $newCustMsg = mysqli_fetch_assoc($newCustResult);

    if (!$newCustMsg) return; // 새 고객 메시지 없음

    // ChatbotService로 AI 응답 생성
    $aiResponse = callChatbotService($newCustMsg['message']);
    if ($aiResponse) {
        insertAIMessage($roomId, $aiResponse);
    }
}

/**
 * AI 긴급대응 진입 — 인사 메시지 + 첫 응답
 */
function activateAI($roomId, $customerMessage) {
    global $db;

    $cfg = loadChatConfig($db);

    // AI 활성화 플래그 설정
    $updateQuery = "UPDATE chatrooms SET ai_active = 1 WHERE id = ?";
    $updateStmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
    mysqli_stmt_execute($updateStmt);

    // 인사 메시지 (DB 설정)
    $greeting = $cfg['ai_greeting_msg'] ?? '안녕하세요, 긴급대응입니다. 담당자 연결 전까지 제가 도와드리겠습니다.';
    insertAIMessage($roomId, $greeting);

    // ChatbotService로 고객 질문에 대한 AI 응답 생성
    $aiResponse = callChatbotService($customerMessage);
    if ($aiResponse) {
        insertAIMessage($roomId, $aiResponse);
    }
}

/**
 * 관리자 응답 시 AI 퇴장
 */
function deactivateAIIfActive($roomId) {
    global $db;

    // AI 활성 상태인지 확인
    $checkQuery = "SELECT ai_active FROM chatrooms WHERE id = ?";
    $checkStmt = mysqli_prepare($db, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, 'i', $roomId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $room = mysqli_fetch_assoc($checkResult);

    if (!$room || $room['ai_active'] != 1) return;

    $cfg = loadChatConfig($db);
    $farewell = $cfg['ai_farewell_msg'] ?? '담당자가 연결되었습니다. 이어서 상담 도와드릴 거예요. 감사합니다!';
    insertAIMessage($roomId, $farewell);

    // AI 비활성화
    $updateQuery = "UPDATE chatrooms SET ai_active = 0 WHERE id = ?";
    $updateStmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
    mysqli_stmt_execute($updateStmt);
}

/**
 * AI 메시지 INSERT 헬퍼
 */
function insertAIMessage($roomId, $message) {
    global $db;

    $cfg = loadChatConfig($db);
    $senderid = 'ai_bot';
    $sendername = $cfg['ai_display_name'] ?? '긴급대응';
    $query = "INSERT INTO chatmessages (roomid, senderid, sendername, messagetype, message)
              VALUES (?, ?, ?, 'text', ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'isss', $roomId, $senderid, $sendername, $message);
    mysqli_stmt_execute($stmt);

    // 채팅방 업데이트 시간 갱신
    $updateQuery = "UPDATE chatrooms SET updatedat = NOW() WHERE id = ?";
    $updateStmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'i', $roomId);
    mysqli_stmt_execute($updateStmt);
}

/**
 * ChatbotService 호출 — 야간당번 챗봇과 동일 엔진
 * 세션 키를 분리하여 야간당번과 긴급대응이 서로 간섭하지 않도록 함
 */
function callChatbotService($message) {
    // 긴급대응용 세션 키 분리 (야간당번 세션과 충돌 방지)
    $originalChatbot = $_SESSION['chatbot'] ?? null;
    if (isset($_SESSION['ai_emergency_chatbot'])) {
        $_SESSION['chatbot'] = $_SESSION['ai_emergency_chatbot'];
    } else {
        unset($_SESSION['chatbot']); // 새 세션 시작
    }

    try {
        // V2_ROOT 정의
        $v2Root = dirname(__DIR__) . '/v2';
        if (!defined('V2_ROOT')) {
            define('V2_ROOT', $v2Root);
        }

        // .env 로드 (Gemini API 키)
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                [$key, $value] = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                putenv(trim($key) . '=' . trim($value));
            }
        }

        // ChatbotService 로드
        require_once $v2Root . '/src/Services/AI/ChatbotService.php';
        $chatbot = new \App\Services\AI\ChatbotService();

        if (!$chatbot->isConfigured()) {
            return '죄송합니다. 잠시 후 담당자가 연결될 예정입니다. 전화(02-2632-1830)로도 문의 가능합니다.';
        }

        $result = $chatbot->chat($message);

        // 긴급대응 세션 저장
        $_SESSION['ai_emergency_chatbot'] = $_SESSION['chatbot'];

        // 원래 야간당번 세션 복원
        if ($originalChatbot !== null) {
            $_SESSION['chatbot'] = $originalChatbot;
        }

        if (!empty($result['success']) && !empty($result['message'])) {
            $response = $result['message'];

            // 옵션이 있으면 번호 목록으로 추가
            if (!empty($result['options'])) {
                $response .= "\n";
                foreach ($result['options'] as $opt) {
                    $num = $opt['num'] ?? '';
                    $label = $opt['label'] ?? '';
                    $response .= "\n{$num}. {$label}";
                }
            }

            return $response;
        }

        return null;

    } catch (\Throwable $e) {
        error_log('AI Emergency chatbot error: ' . $e->getMessage());

        // 원래 세션 복원
        if ($originalChatbot !== null) {
            $_SESSION['chatbot'] = $originalChatbot;
        }

        return '죄송합니다. 잠시 후 담당자가 연결될 예정입니다. 전화(02-2632-1830)로도 문의 가능합니다.';
    }
}

// ============================================================
// 채팅 설정 관리
// ============================================================

function getChatConfig() {
    global $db;

    $group = $_GET['group'] ?? '';
    $where = '';
    $params = [];
    $types = '';

    if ($group && in_array($group, ['widget', 'ai', 'extra'])) {
        $where = 'WHERE config_group = ?';
        $params[] = $group;
        $types = 's';
    }

    $query = "SELECT config_key, config_value, config_type, config_group, description FROM chat_config $where ORDER BY config_group, config_key";

    if ($types) {
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($db, $query);
    }

    if (!$result) {
        jsonResponse(false, null, 'DB 오류: ' . mysqli_error($db));
        return;
    }

    $config = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $val = $row['config_value'];
        if ($row['config_type'] === 'boolean') $val = ($val === '1' || $val === 'true');
        elseif ($row['config_type'] === 'number') $val = is_numeric($val) ? (strpos($val, '.') !== false ? floatval($val) : intval($val)) : $val;

        $config[$row['config_key']] = [
            'value' => $val,
            'type' => $row['config_type'],
            'group' => $row['config_group'],
            'description' => $row['description']
        ];
    }

    jsonResponse(true, $config);
}

function updateChatConfig() {
    global $db;
    requireAdmin();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !is_array($input)) {
        $input = $_POST;
    }

    if (empty($input) || !is_array($input)) {
        jsonResponse(false, null, '설정 데이터가 필요합니다.');
        return;
    }

    $allowedKeys = [
        'widget_enabled', 'widget_position', 'widget_hour_start', 'widget_hour_end',
        'widget_button_label', 'widget_welcome_msg', 'widget_poll_interval',
        'ai_enabled', 'ai_wait_seconds', 'ai_greeting_msg', 'ai_farewell_msg',
        'ai_hour_start', 'ai_hour_end', 'ai_display_name',
        'offline_message', 'notice_message', 'upload_max_mb'
    ];

    $updated = 0;
    $errors = [];

    foreach ($input as $key => $value) {
        if ($key === 'action') continue;
        if (!in_array($key, $allowedKeys)) {
            $errors[] = "허용되지 않는 키: $key";
            continue;
        }

        if (is_bool($value)) $value = $value ? '1' : '0';
        $value = (string)$value;

        $query = "UPDATE chat_config SET config_value = ? WHERE config_key = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $value, $key);

        if (mysqli_stmt_execute($stmt)) {
            $updated++;
        } else {
            $errors[] = "$key: " . mysqli_stmt_error($stmt);
        }
    }

    jsonResponse(true, [
        'updated' => $updated,
        'errors' => $errors,
        'message' => $updated . '개 설정 저장 완료'
    ]);
}

function loadChatConfig($db) {
    $result = mysqli_query($db, "SELECT config_key, config_value, config_type FROM chat_config");
    $config = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $val = $row['config_value'];
            if ($row['config_type'] === 'boolean') $val = ($val === '1');
            elseif ($row['config_type'] === 'number') $val = intval($val);
            $config[$row['config_key']] = $val;
        }
    }
    return $config;
}

// ============================================================
// 관리자 채팅 관리 함수들
// ============================================================

/**
 * 관리자 인증 체크 헬퍼
 */
function requireAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        jsonResponse(false, null, '관리자 권한이 필요합니다.');
        exit;
    }
}

/**
 * 모두 읽음 처리 — 미읽은 고객 메시지 전체를 읽음으로 변경
 * POST: action=admin_mark_all_read
 */
function adminMarkAllRead() {
    global $db;
    requireAdmin();

    $query = "UPDATE chatmessages SET isread = 1 
              WHERE isread = 0 
                AND senderid NOT LIKE 'staff%' 
                AND senderid != 'system' 
                AND senderid != 'ai_bot'";

    if (mysqli_query($db, $query)) {
        $affected = mysqli_affected_rows($db);
        jsonResponse(true, ['affected_rows' => $affected, 'message' => $affected . '건 읽음 처리 완료']);
    } else {
        jsonResponse(false, null, '읽음 처리 실패: ' . mysqli_error($db));
    }
}

/**
 * 채팅방 닫기(비활성화) — isactive = 0으로 변경
 * POST: action=admin_close_room, room_id={id}
 */
function adminCloseRoom() {
    global $db;
    requireAdmin();

    $roomId = intval($_POST['room_id'] ?? 0);
    if (!$roomId) {
        jsonResponse(false, null, '채팅방 ID가 필요합니다.');
        return;
    }

    // 해당 방의 미읽은 메시지도 읽음 처리
    $markQuery = "UPDATE chatmessages SET isread = 1 WHERE roomid = ? AND isread = 0";
    $markStmt = mysqli_prepare($db, $markQuery);
    mysqli_stmt_bind_param($markStmt, 'i', $roomId);
    mysqli_stmt_execute($markStmt);

    // 채팅방 비활성화
    $query = "UPDATE chatrooms SET isactive = 0 WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $roomId);

    if (mysqli_stmt_execute($stmt)) {
        jsonResponse(true, ['message' => '채팅방 #' . $roomId . ' 닫기 완료']);
    } else {
        jsonResponse(false, null, '채팅방 닫기 실패: ' . mysqli_stmt_error($stmt));
    }
}

/**
 * 빈 채팅방 정리 — 메시지 0건인 채팅방 삭제
 * POST: action=admin_cleanup_empty
 */
function adminCleanupEmpty() {
    global $db;
    requireAdmin();

    // 메시지가 없는 채팅방 찾기
    $findQuery = "SELECT r.id FROM chatrooms r 
                  LEFT JOIN chatmessages m ON m.roomid = r.id 
                  WHERE m.id IS NULL AND r.isactive = 1";
    $findResult = mysqli_query($db, $findQuery);

    if (!$findResult) {
        jsonResponse(false, null, '조회 실패: ' . mysqli_error($db));
        return;
    }

    $emptyIds = [];
    while ($row = mysqli_fetch_assoc($findResult)) {
        $emptyIds[] = intval($row['id']);
    }

    if (empty($emptyIds)) {
        jsonResponse(true, ['deleted_count' => 0, 'message' => '정리할 빈 채팅방이 없습니다.']);
        return;
    }

    // 참여자 먼저 삭제 → 채팅방 삭제
    $idList = implode(',', $emptyIds);
    mysqli_query($db, "DELETE FROM chatparticipants WHERE roomid IN ($idList)");
    $deleteResult = mysqli_query($db, "DELETE FROM chatrooms WHERE id IN ($idList)");

    if ($deleteResult) {
        $deleted = mysqli_affected_rows($db);
        jsonResponse(true, ['deleted_count' => $deleted, 'deleted_ids' => $emptyIds, 'message' => $deleted . '개 빈 채팅방 삭제 완료']);
    } else {
        jsonResponse(false, null, '삭제 실패: ' . mysqli_error($db));
    }
}

/**
 * N일 경과 자동 읽음 처리 — 오래된 미읽은 메시지 일괄 읽음
 * POST: action=admin_auto_expire, days={30}
 */
function adminAutoExpire() {
    global $db;
    requireAdmin();

    $days = intval($_POST['days'] ?? 30);
    if ($days < 1) $days = 30;

    $query = "UPDATE chatmessages SET isread = 1 
              WHERE isread = 0 
                AND createdat < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $days);

    if (mysqli_stmt_execute($stmt)) {
        $affected = mysqli_affected_rows($db);
        jsonResponse(true, ['affected_rows' => $affected, 'days' => $days, 'message' => $days . '일 이상 경과된 ' . $affected . '건 읽음 처리 완료']);
    } else {
        jsonResponse(false, null, '자동 읽음 처리 실패: ' . mysqli_stmt_error($stmt));
    }
}

?>
