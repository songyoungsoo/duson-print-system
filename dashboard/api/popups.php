<?php
/**
 * 팝업 관리 API
 * POST /dashboard/api/popups.php
 *
 * Actions: list, create, update, delete, toggle, preview
 * Supports: image upload + template-based popups
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/popup_templates.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// === DB 테이블 자동 생성 + 스키마 마이그레이션 ===
$tableCheck = mysqli_query($db, "SHOW TABLES LIKE 'site_popups'");
if (mysqli_num_rows($tableCheck) === 0) {
    $createSql = "CREATE TABLE site_popups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL DEFAULT '',
        content_type VARCHAR(20) DEFAULT 'image',
        template_type VARCHAR(30) DEFAULT NULL,
        html_content TEXT DEFAULT NULL,
        template_data TEXT DEFAULT NULL,
        image_path VARCHAR(500) NOT NULL DEFAULT '',
        link_url VARCHAR(500) DEFAULT '',
        link_target VARCHAR(10) DEFAULT '_blank',
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        hide_option VARCHAR(20) DEFAULT 'today',
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($db, $createSql)) {
        echo json_encode(['success' => false, 'error' => 'DB 테이블 생성 실패: ' . mysqli_error($db)]);
        exit;
    }
} else {
    // 기존 테이블에 새 컬럼 추가 (마이그레이션)
    $cols = [];
    $colResult = mysqli_query($db, "SHOW COLUMNS FROM site_popups");
    while ($col = mysqli_fetch_assoc($colResult)) {
        $cols[] = $col['Field'];
    }
    $alters = [];
    if (!in_array('content_type', $cols))  $alters[] = "ADD COLUMN content_type VARCHAR(20) DEFAULT 'image' AFTER title";
    if (!in_array('template_type', $cols)) $alters[] = "ADD COLUMN template_type VARCHAR(30) DEFAULT NULL AFTER content_type";
    if (!in_array('html_content', $cols))  $alters[] = "ADD COLUMN html_content TEXT DEFAULT NULL AFTER template_type";
    if (!in_array('template_data', $cols)) $alters[] = "ADD COLUMN template_data TEXT DEFAULT NULL AFTER html_content";
    if (!empty($alters)) {
        mysqli_query($db, "ALTER TABLE site_popups " . implode(', ', $alters));
    }
}

$action = $_POST['action'] ?? '';
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/popups/';
$uploadUrlBase = '/ImgFolder/popups/';

switch ($action) {

    // === 목록 조회 ===
    case 'list':
        $result = mysqli_query($db, "SELECT * FROM site_popups ORDER BY sort_order ASC, id DESC");
        $popups = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['is_active'] = (int)$row['is_active'];
            $row['sort_order'] = (int)$row['sort_order'];
            $today = date('Y-m-d');
            $row['is_showing'] = ($row['is_active'] && $row['start_date'] <= $today && $row['end_date'] >= $today) ? 1 : 0;
            $popups[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $popups], JSON_UNESCAPED_UNICODE);
        break;

    // === 템플릿 목록 ===
    case 'templates':
        $templates = getTemplateList();
        echo json_encode(['success' => true, 'data' => $templates], JSON_UNESCAPED_UNICODE);
        break;

    // === 템플릿 미리보기 ===
    case 'preview':
        $templateType = $_POST['template_type'] ?? '';
        $templates = getTemplateList();
        if (!isset($templates[$templateType])) {
            echo json_encode(['success' => false, 'error' => '알 수 없는 템플릿: ' . $templateType]);
            exit;
        }

        $tplData = [
            'year'        => intval($_POST['year'] ?? date('Y')),
            'start_date'  => $_POST['start_date'] ?? '',
            'end_date'    => $_POST['end_date'] ?? '',
            'resume_date' => $_POST['resume_date'] ?? '',
            'phone'       => $_POST['phone'] ?? '02-2632-1830',
            'company'     => $_POST['company'] ?? '두손기획인쇄',
            'greeting'    => $_POST['greeting'] ?? '',
        ];

        $html = renderPopupTemplate($templateType, $tplData);
        $ganji = getGanji($tplData['year']);

        echo json_encode([
            'success' => true,
            'html'    => $html,
            'ganji'   => $ganji
        ], JSON_UNESCAPED_UNICODE);
        break;

    // === 생성 ===
    case 'create':
        $contentType = $_POST['content_type'] ?? 'image';
        $title = trim($_POST['title'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $linkTarget = ($_POST['link_target'] ?? '_blank') === '_self' ? '_self' : '_blank';
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $hideOption = $_POST['hide_option'] ?? 'today';
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $imagePath = '';
        $templateType = null;
        $htmlContent = null;
        $templateData = null;

        if ($contentType === 'template') {
            // === 템플릿 기반 팝업 ===
            $templateType = $_POST['template_type'] ?? '';
            $templates = getTemplateList();
            if (!isset($templates[$templateType])) {
                echo json_encode(['success' => false, 'error' => '알 수 없는 템플릿: ' . $templateType]);
                exit;
            }

            $tplData = [
                'year'        => intval($_POST['year'] ?? date('Y')),
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'resume_date' => $_POST['resume_date'] ?? '',
                'phone'       => $_POST['phone'] ?? '02-2632-1830',
                'company'     => $_POST['company'] ?? '두손기획인쇄',
                'greeting'    => $_POST['greeting'] ?? '',
            ];

            $htmlContent = renderPopupTemplate($templateType, $tplData);
            $templateData = json_encode($tplData, JSON_UNESCAPED_UNICODE);

            if (empty($title)) {
                $title = $templates[$templateType]['name'] . ' (' . $tplData['year'] . ')';
            }
        } else {
            // === 이미지 기반 팝업 (기존) ===
            if (!empty($_FILES['image']['name'])) {
                $uploadResult = handleImageUpload($_FILES['image'], $uploadDir, $uploadUrlBase);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    exit;
                }
                $imagePath = $uploadResult['path'];
            }

            if (empty($imagePath) && empty($title)) {
                echo json_encode(['success' => false, 'error' => '이미지 또는 제목을 입력하세요.']);
                exit;
            }
        }

        $stmt = mysqli_prepare($db,
            "INSERT INTO site_popups (title, content_type, template_type, html_content, template_data, image_path, link_url, link_target, start_date, end_date, hide_option, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "sssssssssssi",
            $title, $contentType, $templateType, $htmlContent, $templateData,
            $imagePath, $linkUrl, $linkTarget, $startDate, $endDate, $hideOption, $sortOrder
        );

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($db);
            echo json_encode(['success' => true, 'id' => $newId, 'message' => '팝업이 등록되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => '저장 실패: ' . mysqli_error($db)]);
        }
        break;

    // === 수정 ===
    case 'update':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => '잘못된 ID']);
            exit;
        }

        $contentType = $_POST['content_type'] ?? 'image';
        $title = trim($_POST['title'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $linkTarget = ($_POST['link_target'] ?? '_blank') === '_self' ? '_self' : '_blank';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $hideOption = $_POST['hide_option'] ?? 'today';
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $templateType = null;
        $htmlContent = null;
        $templateData = null;
        $imagePath = '';

        if ($contentType === 'template') {
            $templateType = $_POST['template_type'] ?? '';
            $templates = getTemplateList();
            if (!isset($templates[$templateType])) {
                echo json_encode(['success' => false, 'error' => '알 수 없는 템플릿: ' . $templateType]);
                exit;
            }

            $tplData = [
                'year'        => intval($_POST['year'] ?? date('Y')),
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'resume_date' => $_POST['resume_date'] ?? '',
                'phone'       => $_POST['phone'] ?? '02-2632-1830',
                'company'     => $_POST['company'] ?? '두손기획인쇄',
                'greeting'    => $_POST['greeting'] ?? '',
            ];

            $htmlContent = renderPopupTemplate($templateType, $tplData);
            $templateData = json_encode($tplData, JSON_UNESCAPED_UNICODE);

            if (empty($title)) {
                $title = $templates[$templateType]['name'] . ' (' . $tplData['year'] . ')';
            }

            // 템플릿으로 전환 시 이미지 경로 초기화
            $imagePath = '';
            $stmt = mysqli_prepare($db,
                "UPDATE site_popups SET title=?, content_type=?, template_type=?, html_content=?, template_data=?,
                 image_path=?, link_url=?, link_target=?, start_date=?, end_date=?, hide_option=?, sort_order=? WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "sssssssssssii",
                $title, $contentType, $templateType, $htmlContent, $templateData,
                $imagePath, $linkUrl, $linkTarget, $startDate, $endDate, $hideOption, $sortOrder, $id
            );
        } else {
            // 이미지 기반 수정
            $imageUpdate = '';
            if (!empty($_FILES['image']['name'])) {
                $uploadResult = handleImageUpload($_FILES['image'], $uploadDir, $uploadUrlBase);
                if (!$uploadResult['success']) {
                    echo json_encode($uploadResult);
                    exit;
                }
                $imagePath = $uploadResult['path'];
            }

            if (!empty($imagePath)) {
                $stmt = mysqli_prepare($db,
                    "UPDATE site_popups SET title=?, content_type='image', template_type=NULL, html_content=NULL, template_data=NULL,
                     image_path=?, link_url=?, link_target=?, start_date=?, end_date=?, hide_option=?, sort_order=? WHERE id=?"
                );
                mysqli_stmt_bind_param($stmt, "sssssssii",
                    $title, $imagePath, $linkUrl, $linkTarget, $startDate, $endDate, $hideOption, $sortOrder, $id
                );
            } else {
                $stmt = mysqli_prepare($db,
                    "UPDATE site_popups SET title=?, content_type='image', template_type=NULL, html_content=NULL, template_data=NULL,
                     link_url=?, link_target=?, start_date=?, end_date=?, hide_option=?, sort_order=? WHERE id=?"
                );
                mysqli_stmt_bind_param($stmt, "ssssssii",
                    $title, $linkUrl, $linkTarget, $startDate, $endDate, $hideOption, $sortOrder, $id
                );
            }
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => '수정되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => '수정 실패: ' . mysqli_error($db)]);
        }
        break;

    // === 삭제 ===
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => '잘못된 ID']);
            exit;
        }

        // 이미지 파일 삭제 (이미지 타입일 때만)
        $result = mysqli_query($db, "SELECT image_path FROM site_popups WHERE id = " . $id);
        if ($row = mysqli_fetch_assoc($result)) {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $row['image_path'];
            if (!empty($row['image_path']) && file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        if (mysqli_query($db, "DELETE FROM site_popups WHERE id = " . $id)) {
            echo json_encode(['success' => true, 'message' => '삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => '삭제 실패']);
        }
        break;

    // === 활성/비활성 토글 ===
    case 'toggle':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => '잘못된 ID']);
            exit;
        }
        if (mysqli_query($db, "UPDATE site_popups SET is_active = NOT is_active WHERE id = " . $id)) {
            $result = mysqli_query($db, "SELECT is_active FROM site_popups WHERE id = " . $id);
            $row = mysqli_fetch_assoc($result);
            $statusText = $row['is_active'] ? '활성화' : '비활성화';
            echo json_encode(['success' => true, 'is_active' => (int)$row['is_active'], 'message' => $statusText . '되었습니다.'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => '토글 실패']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => '알 수 없는 액션: ' . $action]);
        break;
}

// === 이미지 업로드 함수 ===
function handleImageUpload($file, $uploadDir, $urlBase) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => '파일 업로드 오류 (code: ' . $file['error'] . ')'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => '파일 크기가 5MB를 초과합니다.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'error' => '허용되지 않는 파일 형식입니다. (jpg, png, gif, webp만 가능)'];
    }

    $newName = 'popup_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => true, 'path' => $urlBase . $newName];
    }

    return ['success' => false, 'error' => '파일 저장에 실패했습니다.'];
}
