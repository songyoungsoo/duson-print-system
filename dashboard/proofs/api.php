<?php
require_once __DIR__ . '/../../admin/includes/admin_auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '로그인 필요']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'files':
        $order_no = intval($_GET['order_no'] ?? 0);
        if ($order_no <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order']);
            exit;
        }
        $upload_dir = realpath(__DIR__ . '/../../mlangorder_printauto/upload/' . $order_no);
        $files = [];
        if ($upload_dir && is_dir($upload_dir)) {
            foreach (array_diff(scandir($upload_dir), ['.', '..']) as $fname) {
                $files[] = [
                    'name' => $fname,
                    'url' => '/mlangorder_printauto/upload/' . $order_no . '/' . rawurlencode($fname),
                    'size' => filesize($upload_dir . '/' . $fname),
                ];
            }
        }
        echo json_encode(['success' => true, 'files' => $files]);
        break;

    case 'upload':
        $order_no = intval($_POST['order_no'] ?? 0);
        if ($order_no <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order']);
            exit;
        }

        $upload_base = __DIR__ . '/../../mlangorder_printauto/upload/' . $order_no;
        if (!is_dir($upload_base)) {
            mkdir($upload_base, 0777, true);
        }

        $uploaded = 0;
        $custom_names = $_POST['names'] ?? [];
        if (!is_array($custom_names)) $custom_names = [$custom_names];

        if (!empty($_FILES['files'])) {
            $file_count = is_array($_FILES['files']['name']) ? count($_FILES['files']['name']) : 1;
            for ($i = 0; $i < $file_count; $i++) {
                $name = is_array($_FILES['files']['name']) ? $_FILES['files']['name'][$i] : $_FILES['files']['name'];
                $tmp = is_array($_FILES['files']['tmp_name']) ? $_FILES['files']['tmp_name'][$i] : $_FILES['files']['tmp_name'];
                $error = is_array($_FILES['files']['error']) ? $_FILES['files']['error'][$i] : $_FILES['files']['error'];

                if ($error !== UPLOAD_ERR_OK) continue;

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'ai', 'psd', 'zip'];
                if (!in_array($ext, $allowed)) continue;

                // 커스텀 이름 사용 (없으면 원본 파일명)
                $custom = isset($custom_names[$i]) ? trim($custom_names[$i]) : '';
                if ($custom !== '') {
                    // 파일명에 안전하지 않은 문자 제거 (한글, 영문, 숫자, 공백, -, _ 허용)
                    $custom = preg_replace('/[\/\\\\:*?"<>|]/', '', $custom);
                    $custom = mb_substr($custom, 0, 100, 'UTF-8'); // 100자 제한
                    $save_name = date('Ymd_His') . '_' . $custom . '.' . $ext;
                } else {
                    $save_name = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                }

                // 동일 이름 존재 시 숫자 추가
                $final_path = $upload_base . '/' . $save_name;
                if (file_exists($final_path)) {
                    $base = pathinfo($save_name, PATHINFO_FILENAME);
                    $n = 1;
                    while (file_exists($upload_base . '/' . $base . '_' . $n . '.' . $ext)) $n++;
                    $save_name = $base . '_' . $n . '.' . $ext;
                    $final_path = $upload_base . '/' . $save_name;
                }

                if (move_uploaded_file($tmp, $final_path)) {
                    $uploaded++;
                }
            }
        }

        echo json_encode(['success' => $uploaded > 0, 'message' => $uploaded . '개 파일 업로드 완료', 'count' => $uploaded]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
