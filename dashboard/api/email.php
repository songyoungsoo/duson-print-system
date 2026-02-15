<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ==================== GET RECIPIENTS ====================
    case 'get_recipients':
        $type = $_GET['type'] ?? 'all';

        $where = "is_admin = 0 AND username NOT LIKE 'test%' AND email IS NOT NULL AND email != '' AND phone != '--'";
        $params = [];
        $types_str = '';

        if ($type === 'filtered') {
            $months = intval($_GET['login_months'] ?? 0);
            $domain = $_GET['domain'] ?? '';
            if ($months > 0) {
                $where .= " AND last_login >= DATE_SUB(NOW(), INTERVAL ? MONTH)";
                $params[] = $months;
                $types_str .= 'i';
            }
            if ($domain !== '') {
                $where .= " AND email LIKE ?";
                $params[] = '%@' . $domain;
                $types_str .= 's';
            }
        }

        $query = "SELECT DISTINCT email, name FROM users WHERE {$where} ORDER BY id";

        if (count($params) > 0) {
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, $types_str, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($db, $query);
        }

        $recipients = [];
        $gmail_count = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $recipients[] = $row;
            if (stripos($row['email'], '@gmail.com') !== false) $gmail_count++;
        }

        jsonResponse(true, 'Recipients counted', [
            'count' => count($recipients),
            'gmail_count' => $gmail_count,
            'recipients' => $recipients
        ]);
        break;

    // ==================== SEND (create campaign + return info) ====================
    case 'send':
        $subject = $_POST['subject'] ?? '';
        $body_html = $_POST['body_html'] ?? '';
        $recipient_type = $_POST['recipient_type'] ?? 'all';

        if (empty($subject) || empty($body_html)) {
            jsonResponse(false, '제목과 본문을 입력해주세요');
        }

        $recipients = resolveRecipients($db, $recipient_type, $_POST);
        if (empty($recipients)) {
            jsonResponse(false, '수신자가 없습니다');
        }

        $total = count($recipients);
        $recipient_filter = null;
        $recipient_emails_str = null;

        if ($recipient_type === 'filtered') {
            $filter = [];
            if (!empty($_POST['login_months'])) $filter['login_months'] = intval($_POST['login_months']);
            if (!empty($_POST['domain'])) $filter['domain'] = $_POST['domain'];
            $recipient_filter = json_encode($filter);
        } elseif ($recipient_type === 'manual') {
            $recipient_emails_str = $_POST['recipient_emails'] ?? '';
        }

        $created_by = $_SESSION['admin_username'] ?? 'admin';

        $q = "INSERT INTO email_campaigns (subject, body_html, recipient_type, recipient_filter, recipient_emails, total_recipients, status, started_at, created_by) VALUES (?, ?, ?, ?, ?, ?, 'sending', NOW(), ?)";
        $stmt = mysqli_prepare($db, $q);
        // 7 placeholders: s,s,s,s,s,i,s
        $placeholder_count = substr_count($q, '?'); // 7
        $type_string = 'sssssis';
        $type_count = strlen($type_string); // 7
        $var_count = 7;
        mysqli_stmt_bind_param($stmt, $type_string, $subject, $body_html, $recipient_type, $recipient_filter, $recipient_emails_str, $total, $created_by);
        mysqli_stmt_execute($stmt);
        $campaign_id = mysqli_insert_id($db);

        if (!$campaign_id) {
            jsonResponse(false, '캠페인 생성 실패');
        }

        $insert_q = "INSERT INTO email_send_log (campaign_id, recipient_email, recipient_name, status) VALUES (?, ?, ?, 'pending')";
        $insert_stmt = mysqli_prepare($db, $insert_q);
        foreach ($recipients as $r) {
            $email = $r['email'];
            $name = $r['name'] ?? '';
            mysqli_stmt_bind_param($insert_stmt, 'iss', $campaign_id, $email, $name);
            mysqli_stmt_execute($insert_stmt);
        }

        jsonResponse(true, '캠페인이 생성되었습니다', [
            'campaign_id' => $campaign_id,
            'total_recipients' => $total
        ]);
        break;

    // ==================== SEND BATCH ====================
    case 'send_batch':
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        $offset = intval($_POST['offset'] ?? 0);
        $batch_size = 100;

        if ($campaign_id <= 0) {
            jsonResponse(false, '잘못된 캠페인 ID');
        }

        $q = "SELECT id, subject, body_html, total_recipients, status FROM email_campaigns WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $campaign_id);
        mysqli_stmt_execute($stmt);
        $campaign = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$campaign) {
            jsonResponse(false, '캠페인을 찾을 수 없습니다');
        }

        $q = "SELECT id, recipient_email, recipient_name FROM email_send_log WHERE campaign_id = ? AND status = 'pending' ORDER BY id LIMIT ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'ii', $campaign_id, $batch_size);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $batch = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $batch[] = $row;
        }

        if (empty($batch)) {
            finalizeCampaign($db, $campaign_id);
            jsonResponse(true, '모든 발송 완료', [
                'sent_count' => 0,
                'fail_count' => 0,
                'batch_size' => $batch_size,
                'is_last_batch' => true
            ]);
            break;
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/mailer.lib.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/config.env.php';

        $sent = 0;
        $failed = 0;
        $sender_name = '두손기획인쇄';
        $sender_email = 'dsp1830@naver.com';

        // Gmail SMTP 사용 가능 여부 (한 번만 체크)
        $gmail_enabled = EnvironmentDetector::isGmailSmtpEnabled();
        // Gmail SMTP 사용 도메인 목록
        $gmail_domains = ['gmail.com'];

        ob_start();

        foreach ($batch as $entry) {
            $personalized_body = str_replace('{{name}}', ($entry['recipient_name'] ?: '고객'), $campaign['body_html']);

            $mail_result = false;
            $error_msg = '';

            // 수신자 도메인 추출
            $recipient_domain = strtolower(substr(strrchr($entry['recipient_email'], '@'), 1));
            $use_gmail = $gmail_enabled && in_array($recipient_domain, $gmail_domains);

            try {
                if ($use_gmail) {
                    // Gmail 수신자 → Gmail SMTP
                    $mail_result = mailer_gmail($sender_name, $entry['recipient_email'], $campaign['subject'], $personalized_body, 1, "");
                } else {
                    // 기타 수신자 → 네이버 SMTP (기존)
                    $mail_result = mailer($sender_name, $sender_email, $entry['recipient_email'], $campaign['subject'], $personalized_body, 1, "", "", "");
                }
            } catch (Exception $e) {
                $error_msg = $e->getMessage();
            }

            if ($mail_result) {
                $uq = "UPDATE email_send_log SET status = 'sent', sent_at = NOW() WHERE id = ?";
                $us = mysqli_prepare($db, $uq);
                mysqli_stmt_bind_param($us, 'i', $entry['id']);
                mysqli_stmt_execute($us);
                $sent++;
            } else {
                if (empty($error_msg)) $error_msg = 'SMTP 발송 실패';
                $uq = "UPDATE email_send_log SET status = 'failed', error_message = ?, sent_at = NOW() WHERE id = ?";
                $us = mysqli_prepare($db, $uq);
                mysqli_stmt_bind_param($us, 'si', $error_msg, $entry['id']);
                mysqli_stmt_execute($us);
                $failed++;
            }
        }

        ob_end_clean();

        $uq = "UPDATE email_campaigns SET sent_count = sent_count + ?, fail_count = fail_count + ?, updated_at = NOW() WHERE id = ?";
        $us = mysqli_prepare($db, $uq);
        mysqli_stmt_bind_param($us, 'iii', $sent, $failed, $campaign_id);
        mysqli_stmt_execute($us);

        $cq = "SELECT COUNT(*) as remaining FROM email_send_log WHERE campaign_id = ? AND status = 'pending'";
        $cs = mysqli_prepare($db, $cq);
        mysqli_stmt_bind_param($cs, 'i', $campaign_id);
        mysqli_stmt_execute($cs);
        $remaining = mysqli_fetch_assoc(mysqli_stmt_get_result($cs))['remaining'];

        $is_last = ($remaining == 0);
        if ($is_last) {
            finalizeCampaign($db, $campaign_id);
        }

        jsonResponse(true, '배치 발송 완료', [
            'sent_count' => $sent,
            'fail_count' => $failed,
            'batch_size' => $batch_size,
            'is_last_batch' => $is_last,
            'remaining' => intval($remaining)
        ]);
        break;

    // ==================== RESUME CAMPAIGN ====================
    case 'resume_campaign':
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        if ($campaign_id <= 0) jsonResponse(false, '잘못된 캠페인 ID');

        $q = "SELECT id, total_recipients, sent_count, fail_count, status FROM email_campaigns WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $campaign_id);
        mysqli_stmt_execute($stmt);
        $campaign = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$campaign) jsonResponse(false, '캠페인을 찾을 수 없습니다');

        $pq = "SELECT COUNT(*) as pending FROM email_send_log WHERE campaign_id = ? AND status = 'pending'";
        $ps = mysqli_prepare($db, $pq);
        mysqli_stmt_bind_param($ps, 'i', $campaign_id);
        mysqli_stmt_execute($ps);
        $pending = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($ps))['pending']);

        if ($pending == 0) jsonResponse(false, '발송 대기 중인 이메일이 없습니다');

        $uq = "UPDATE email_campaigns SET status = 'sending', updated_at = NOW() WHERE id = ?";
        $us = mysqli_prepare($db, $uq);
        mysqli_stmt_bind_param($us, 'i', $campaign_id);
        mysqli_stmt_execute($us);

        jsonResponse(true, '캠페인 재개 준비 완료', [
            'campaign_id' => $campaign_id,
            'total_recipients' => intval($campaign['total_recipients']),
            'sent_count' => intval($campaign['sent_count']),
            'fail_count' => intval($campaign['fail_count']),
            'pending_count' => $pending
        ]);
        break;

    // ==================== RETRY FAILED ====================
    case 'retry_failed':
        $campaign_id = intval($_POST['campaign_id'] ?? 0);
        if ($campaign_id <= 0) jsonResponse(false, '잘못된 캠페인 ID');

        // 제외할 도메인 목록 (e.g. "gmail.com,yahoo.com")
        $exclude_raw = trim($_POST['exclude_domains'] ?? '');
        $exclude_domains = [];
        if (!empty($exclude_raw)) {
            $exclude_domains = array_filter(array_map('trim', explode(',', $exclude_raw)));
        }

        // 캠페인 존재 확인
        $q = "SELECT id, total_recipients, sent_count, fail_count, status FROM email_campaigns WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $campaign_id);
        mysqli_stmt_execute($stmt);
        $campaign = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$campaign) jsonResponse(false, '캠페인을 찾을 수 없습니다');

        // 실패 건수 확인
        $fq = "SELECT COUNT(*) as cnt FROM email_send_log WHERE campaign_id = ? AND status = 'failed'";
        $fs = mysqli_prepare($db, $fq);
        mysqli_stmt_bind_param($fs, 'i', $campaign_id);
        mysqli_stmt_execute($fs);
        $total_failed = intval(mysqli_fetch_assoc(mysqli_stmt_get_result($fs))['cnt']);
        if ($total_failed == 0) jsonResponse(false, '실패한 발송 건이 없습니다');

        // 제외 도메인 빌드 (WHERE NOT)
        $exclude_where = '';
        $bind_types = 'i';
        $bind_params = [$campaign_id];
        if (count($exclude_domains) > 0) {
            $placeholders = implode(',', array_fill(0, count($exclude_domains), '?'));
            $exclude_where = " AND SUBSTRING_INDEX(recipient_email, '@', -1) NOT IN ({$placeholders})";
            foreach ($exclude_domains as $d) {
                $bind_types .= 's';
                $bind_params[] = $d;
            }
        }

        // failed → pending 업데이트
        $uq = "UPDATE email_send_log SET status = 'pending', error_message = NULL, sent_at = NULL WHERE campaign_id = ? AND status = 'failed'" . $exclude_where;
        $us = mysqli_prepare($db, $uq);
        // bind_param 검증: placeholder_count = 1 + count(exclude_domains)
        $placeholder_count = substr_count($uq, '?');
        $type_count = strlen($bind_types);
        $var_count = count($bind_params);
        // 3단계 검증 통과 확인
        if ($placeholder_count !== $type_count || $type_count !== $var_count) {
            jsonResponse(false, 'Internal bind_param mismatch');
        }
        mysqli_stmt_bind_param($us, $bind_types, ...$bind_params);
        mysqli_stmt_execute($us);
        $retried_count = mysqli_affected_rows($db);

        if ($retried_count == 0) jsonResponse(false, '재시도 가능한 실패 건이 없습니다 (모두 제외 도메인)');

        // 캠페인 fail_count 차감, status → sending
        $new_fail = intval($campaign['fail_count']) - $retried_count;
        if ($new_fail < 0) $new_fail = 0;
        $cq = "UPDATE email_campaigns SET fail_count = ?, status = 'sending', updated_at = NOW() WHERE id = ?";
        $cs = mysqli_prepare($db, $cq);
        // 2 placeholders: i, i
        mysqli_stmt_bind_param($cs, 'ii', $new_fail, $campaign_id);
        mysqli_stmt_execute($cs);

        // 제외된 건수
        $excluded_count = $total_failed - $retried_count;

        jsonResponse(true, '실패 ' . $retried_count . '건 재시도 준비 완료', [
            'campaign_id' => $campaign_id,
            'total_recipients' => intval($campaign['total_recipients']),
            'sent_count' => intval($campaign['sent_count']),
            'fail_count' => $new_fail,
            'retried_count' => $retried_count,
            'excluded_count' => $excluded_count,
            'pending_count' => $retried_count
        ]);
        break;

    // ==================== SEND TEST ====================
    case 'send_test':
        $subject = $_POST['subject'] ?? '';
        $body_html = $_POST['body_html'] ?? '';

        if (empty($subject) || empty($body_html)) {
            jsonResponse(false, '제목과 본문을 입력해주세요');
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/mailer.lib.php';

        $test_body = str_replace('{{name}}', '관리자(테스트)', $body_html);
        $test_subject = '[테스트] ' . $subject;

        ob_start();
        $result = mailer('두손기획인쇄', 'dsp1830@naver.com', 'dsp1830@naver.com', $test_subject, $test_body, 1, "", "", "");
        ob_end_clean();

        if ($result) {
            jsonResponse(true, '테스트 메일이 발송되었습니다 (dsp1830@naver.com)');
        } else {
            jsonResponse(false, '테스트 메일 발송에 실패했습니다');
        }
        break;

    // ==================== CAMPAIGNS LIST ====================
    case 'campaigns':
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset_val = ($page - 1) * $limit;

        $count_q = "SELECT COUNT(*) as total FROM email_campaigns";
        $count_r = mysqli_query($db, $count_q);
        $total_items = mysqli_fetch_assoc($count_r)['total'];
        $total_pages = ceil($total_items / $limit);

        $q = "SELECT id, subject, total_recipients, sent_count, fail_count, status, started_at, created_at FROM email_campaigns ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset_val);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $campaigns = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $campaigns[] = $row;
        }

        jsonResponse(true, 'Campaigns loaded', [
            'data' => $campaigns,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => intval($total_pages),
                'total_items' => intval($total_items),
                'per_page' => $limit
            ]
        ]);
        break;

    // ==================== CAMPAIGN DETAIL ====================
    case 'campaign_detail':
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) jsonResponse(false, '잘못된 ID');

        $q = "SELECT * FROM email_campaigns WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $campaign = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$campaign) jsonResponse(false, '캠페인을 찾을 수 없습니다');

        $lq = "SELECT recipient_email, recipient_name, status, error_message, sent_at FROM email_send_log WHERE campaign_id = ? ORDER BY id";
        $ls = mysqli_prepare($db, $lq);
        mysqli_stmt_bind_param($ls, 'i', $id);
        mysqli_stmt_execute($ls);
        $lr = mysqli_stmt_get_result($ls);

        $logs = [];
        while ($row = mysqli_fetch_assoc($lr)) {
            $logs[] = $row;
        }

        jsonResponse(true, 'Campaign detail', ['campaign' => $campaign, 'logs' => $logs]);
        break;

    // ==================== SAVE DRAFT ====================
    case 'save_draft':
        $subject = $_POST['subject'] ?? '';
        $body_html = $_POST['body_html'] ?? '';
        $recipient_type = $_POST['recipient_type'] ?? 'all';

        if (empty($subject)) jsonResponse(false, '제목을 입력해주세요');

        $recipient_filter = null;
        $recipient_emails_str = null;

        if ($recipient_type === 'filtered') {
            $filter = [];
            if (!empty($_POST['login_months'])) $filter['login_months'] = intval($_POST['login_months']);
            if (!empty($_POST['domain'])) $filter['domain'] = $_POST['domain'];
            $recipient_filter = json_encode($filter);
        } elseif ($recipient_type === 'manual') {
            $recipient_emails_str = $_POST['recipient_emails'] ?? '';
        }

        $created_by = $_SESSION['admin_username'] ?? 'admin';

        $q = "INSERT INTO email_campaigns (subject, body_html, recipient_type, recipient_filter, recipient_emails, status, created_by) VALUES (?, ?, ?, ?, ?, 'draft', ?)";
        $stmt = mysqli_prepare($db, $q);
        // 6 placeholders: s,s,s,s,s,s
        $placeholder_count = substr_count($q, '?'); // 6
        $type_string = 'ssssss';
        $type_count = strlen($type_string); // 6
        $var_count = 6;
        mysqli_stmt_bind_param($stmt, $type_string, $subject, $body_html, $recipient_type, $recipient_filter, $recipient_emails_str, $created_by);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, '임시저장 완료');
        } else {
            jsonResponse(false, '저장 실패: ' . mysqli_error($db));
        }
        break;

    // ==================== TEMPLATES LIST ====================
    case 'templates':
        $q = "SELECT id, name, subject, created_at, updated_at FROM email_templates ORDER BY id DESC";
        $result = mysqli_query($db, $q);
        $templates = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $templates[] = $row;
        }
        jsonResponse(true, 'Templates loaded', $templates);
        break;

    // ==================== LOAD TEMPLATE ====================
    case 'load_template':
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) jsonResponse(false, '잘못된 ID');

        $q = "SELECT id, name, subject, body_html FROM email_templates WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $tpl = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$tpl) jsonResponse(false, '템플릿을 찾을 수 없습니다');
        jsonResponse(true, 'Template loaded', $tpl);
        break;

    // ==================== SAVE TEMPLATE ====================
    case 'save_template':
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $body_html = $_POST['body_html'] ?? '';

        if (empty($name) || empty($subject)) jsonResponse(false, '이름과 제목을 입력해주세요');

        if ($id > 0) {
            $q = "UPDATE email_templates SET name = ?, subject = ?, body_html = ? WHERE id = ?";
            $stmt = mysqli_prepare($db, $q);
            // 4 placeholders: s,s,s,i
            $placeholder_count = substr_count($q, '?'); // 4
            $type_string = 'sssi';
            $type_count = strlen($type_string); // 4
            $var_count = 4;
            mysqli_stmt_bind_param($stmt, $type_string, $name, $subject, $body_html, $id);
        } else {
            $q = "INSERT INTO email_templates (name, subject, body_html) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db, $q);
            // 3 placeholders: s,s,s
            $placeholder_count = substr_count($q, '?'); // 3
            $type_string = 'sss';
            $type_count = strlen($type_string); // 3
            $var_count = 3;
            mysqli_stmt_bind_param($stmt, $type_string, $name, $subject, $body_html);
        }

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, $id > 0 ? '템플릿이 수정되었습니다' : '템플릿이 저장되었습니다');
        } else {
            jsonResponse(false, '저장 실패: ' . mysqli_error($db));
        }
        break;

    // ==================== DELETE TEMPLATE ====================
    case 'delete_template':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) jsonResponse(false, '잘못된 ID');

        $q = "DELETE FROM email_templates WHERE id = ?";
        $stmt = mysqli_prepare($db, $q);
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, '템플릿이 삭제되었습니다');
        } else {
            jsonResponse(false, '삭제 실패');
        }
        break;

    // ==================== UPLOAD IMAGE ====================
    case 'upload_image':
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            jsonResponse(false, '파일 업로드에 실패했습니다');
        }

        $file = $_FILES['image'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            jsonResponse(false, '파일 크기는 5MB 이하만 가능합니다');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            jsonResponse(false, '허용된 이미지 형식: JPG, PNG, GIF, WebP');
        }

        $ext_map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $ext = $ext_map[$mime] ?? 'jpg';
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/email/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $dest = $upload_dir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            jsonResponse(false, '파일 저장에 실패했습니다');
        }

        $is_production = (strpos($_SERVER['HTTP_HOST'] ?? '', 'dsp114.co.kr') !== false);
        $base_url = $is_production ? 'https://dsp114.co.kr' : 'http://localhost';
        $url = $base_url . '/dashboard/email/uploads/' . $filename;

        jsonResponse(true, '이미지가 업로드되었습니다', ['url' => $url, 'filename' => $filename]);
        break;

    default:
        jsonResponse(false, 'Invalid action');
}

// ==================== HELPER FUNCTIONS ====================

function resolveRecipients($db, $type, $post_data) {
    if ($type === 'manual') {
        $raw = $post_data['recipient_emails'] ?? '';
        $emails = array_filter(array_map('trim', preg_split('/[,\n]+/', $raw)));
        $recipients = [];
        foreach ($emails as $e) {
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = ['email' => $e, 'name' => ''];
            }
        }
        return $recipients;
    }

    $where = "is_admin = 0 AND username NOT LIKE 'test%' AND email IS NOT NULL AND email != '' AND phone != '--'";
    $params = [];
    $types_str = '';

    if ($type === 'filtered') {
        $months = intval($post_data['login_months'] ?? 0);
        $domain = $post_data['domain'] ?? '';
        if ($months > 0) {
            $where .= " AND last_login >= DATE_SUB(NOW(), INTERVAL ? MONTH)";
            $params[] = $months;
            $types_str .= 'i';
        }
        if ($domain !== '') {
            $where .= " AND email LIKE ?";
            $params[] = '%@' . $domain;
            $types_str .= 's';
        }
    }

    $query = "SELECT DISTINCT email, name FROM users WHERE {$where} ORDER BY id";

    if (count($params) > 0) {
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, $types_str, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($db, $query);
    }

    $recipients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $recipients[] = $row;
    }
    return $recipients;
}

function finalizeCampaign($db, $campaign_id) {
    $q = "UPDATE email_campaigns SET status = 'completed', completed_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($db, $q);
    mysqli_stmt_bind_param($stmt, 'i', $campaign_id);
    mysqli_stmt_execute($stmt);
}
