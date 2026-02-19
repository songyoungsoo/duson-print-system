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

        require_once __DIR__ . '/../../includes/ImagePathResolver.php';

        // DB에서 주문 정보 조회 (ImgFolder, ThingCate, uploaded_files 포함)
        $stmt = mysqli_prepare($db, "SELECT no, date, ImgFolder, ThingCate, uploaded_files FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_no);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $files = [];
        if ($row) {
            // ImagePathResolver로 5가지 저장 패턴 통합 조회
            $result = ImagePathResolver::getFilesFromRow($row, false);
            $doc_root = $_SERVER['DOCUMENT_ROOT'];

            foreach ($result['files'] as $f) {
                $filepath = $f['path'] ?? '';
                $web_url = $f['web_url'] ?? '';

                // web_url이 없으면 path에서 생성
                if (empty($web_url) && !empty($filepath) && file_exists($filepath)) {
                    $web_url = str_replace($doc_root, '', $filepath);
                }

                if (empty($web_url)) continue;

                $fname = $f['name'] ?? basename($filepath);
                $fsize = $f['size'] ?? (file_exists($filepath) ? filesize($filepath) : 0);
                $mtime = file_exists($filepath) ? filemtime($filepath) : 0;

                $files[] = [
                    'name' => $fname,
                    'url' => $web_url,
                    'path' => $filepath,
                    'size' => $fsize,
                    'mtime' => $mtime,
                    'date' => $mtime ? date('m/d H:i', $mtime) : '',
                ];
            }

            // 최신 파일 순 정렬
            usort($files, function($a, $b) { return $b['mtime'] - $a['mtime']; });
        }
        echo json_encode(['success' => true, 'files' => $files]);
        break;

    case 'save_phone':
        $order_no = intval($_POST['order_no'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');
        if ($order_no <= 0 || $phone === '') {
            echo json_encode(['success' => false, 'message' => '주문번호와 전화번호를 입력해주세요']);
            exit;
        }
        // phone 컬럼에 저장 (기존 값이 비어있을 때만)
        $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET phone = ? WHERE no = ? AND (phone IS NULL OR phone = '')");
        mysqli_stmt_bind_param($stmt, "si", $phone, $order_no);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected > 0) {
            echo json_encode(['success' => true, 'message' => '전화번호 저장 완료']);
        } else {
            // 이미 전화번호가 있는 경우에도 Hendphone으로 저장 시도
            $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET Hendphone = ? WHERE no = ?");
            mysqli_stmt_bind_param($stmt, "si", $phone, $order_no);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo json_encode(['success' => true, 'message' => '전화번호 저장 완료']);
        }
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

    case 'delete_file':
        $order_no = intval($_POST['order_no'] ?? 0);
        $file_path = trim($_POST['file'] ?? '');

        if ($order_no <= 0 || $file_path === '') {
            echo json_encode(['success' => false, 'message' => '잘못된 요청']);
            exit;
        }

        // 경로 조회 확인 (보안)
        $upload_base = realpath(__DIR__ . '/../../mlangorder_printauto/upload/' . $order_no);
        if (!$upload_base || strpos($file_path, $upload_base) !== 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 파일 경로']);
            exit;
        }

        $full_path = realpath($file_path);
        if (!$full_path || strpos($full_path, $upload_base) !== 0) {
            echo json_encode(['success' => false, 'message' => '파일을 찾을 수 없습니다']);
            exit;
        }

        if (unlink($full_path)) {
            echo json_encode(['success' => true, 'message' => '파일 삭제 완료']);
        } else {
            echo json_encode(['success' => false, 'message' => '삭제 실패']);
        }
        break;

    case 'check_proof_status':
        $order_no = intval($_GET['order_no'] ?? 0);
        if ($order_no <= 0) {
            echo json_encode(['success' => false, 'confirmed' => false]);
            exit;
        }

        $stmt = mysqli_prepare($db, "SELECT OrderStyle FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_no);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $confirmed = ($row && $row['OrderStyle'] == '8'); // 작업완료 상태 확인
        echo json_encode(['success' => true, 'confirmed' => $confirmed]);
        break;

    case 'confirm_proofreading':
        $order_no = intval($_POST['order_no'] ?? 0);
        if ($order_no <= 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청']);
            exit;
        }

        // 주문 상태를 작업완료(8)로 변경
        $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto SET OrderStyle = '8' WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $order_no);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($affected > 0) {
            echo json_encode(['success' => true, 'message' => '교정확정 완료']);
        } else {
            echo json_encode(['success' => false, 'message' => '처리 실패']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
