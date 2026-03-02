<?php
session_start();

// Check authentication based on access type
$is_customer_access = isset($_GET['customer']) && $_GET['customer'] === '1';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

if ($is_customer_access) {
    // 고객용 접근 - 본인 주문만 확인 가능
    if (!isset($_SESSION['customer_authenticated']) || $_SESSION['customer_authenticated'] !== true) {
        echo "<script>
                alert('로그인이 필요합니다.');
                window.close();
              </script>";
        exit;
    }
} elseif (strpos($referrer, 'checkboard.php') !== false) {
    // checkboard.php에서 접근 - 비밀번호 인증 후 접근이므로 허용
    // 개별 주문 비밀번호 인증이 이미 완료된 상태
}

header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Cache-control: private"); // <= it's magical!!

include "../db.php";

$no = $_GET['no'] ?? '';

$result = $db->query("SELECT * FROM mlangorder_printauto WHERE no='$no'");
$row = $result->fetch_assoc();

if ($row) {
    $ImgFile = $row['ThingCate'] ?? '';
    $View_Type = $row['Type'] ?? '';
    $View_PMmember = $row['PMmember'] ?? '';
    $View_ThingNo = $row['ThingNo'] ?? '';
    $View_OrderStyle = $row['OrderStyle'] ?? '';
    $View_OrderName = $row['name'] ?? '';
    $View_standard = $row['standard'] ?? '';
    $View_pass = $row['pass'] ?? '';
    $view_designer = $row['Designer'] ?? '';
    $View_Phone = $row['phone'] ?? '';

    // 추가: ImgFolder 및 uploaded_files 가져오기
    $ImgFolder = $row['ImgFolder'] ?? '';
    $uploaded_files_json = $row['uploaded_files'] ?? '';

    // 고객 접근 시 본인 주문인지 확인
    if ($is_customer_access) {
        $session_name = $_SESSION['customer_name'] ?? '';
        $session_phone = $_SESSION['customer_phone'] ?? '';
        $session_phone_normalized = preg_replace('/[^0-9]/', '', $session_phone);
        $db_phone_normalized = preg_replace('/[^0-9]/', '', $View_Phone);

        if ($View_OrderName !== $session_name ||
            ($session_phone_normalized !== $db_phone_normalized &&
             strpos($db_phone_normalized, $session_phone_normalized) === false)) {
            echo "<script>
                    alert('본인의 주문만 조회하실 수 있습니다.');
                    window.close();
                  </script>
                  <meta charset='UTF-8'>";
            exit;
        }
    }

    // ============================================================
    // 이미지 수집 — 모든 소스에서 ALL 이미지를 배열로 수집
    // ============================================================
    $all_images = []; // [{url, name, source}]
    $viewable_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // 1. upload/{no}/ 폴더 — 교정 관리에서 업로드한 모든 파일 (최우선)
    $upload_dir = __DIR__ . '/upload/' . $no;
    if (is_dir($upload_dir)) {
        $files = scandir($upload_dir);
        $upload_files = [];
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $viewable_extensions)) {
                    $upload_files[] = [
                        'file' => $file,
                        'mtime' => filemtime($upload_dir . '/' . $file)
                    ];
                }
            }
        }
        // 최신 파일 우선 정렬
        usort($upload_files, function($a, $b) {
            return $b['mtime'] - $a['mtime'];
        });
        foreach ($upload_files as $uf) {
            $all_images[] = [
                'url' => '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($uf['file']) . '&src=upload',
                'name' => $uf['file'],
                'date' => date('Y-m-d H:i', $uf['mtime']),
                'source' => 'upload'
            ];
        }
    }

    // 2. ImgFolder 경로들
    if (!empty($ImgFolder)) {
        $imgfolder_images = [];

        // 2-1. 레거시 경로 처리 (../shop/data/파일명)
        if (strpos($ImgFolder, '../shop/data/') === 0) {
            $legacy_filename = basename($ImgFolder);
            $legacy_path = $_SERVER['DOCUMENT_ROOT'] . '/shop/data/' . $legacy_filename;
            if (file_exists($legacy_path)) {
                $ext = strtolower(pathinfo($legacy_path, PATHINFO_EXTENSION));
                if (in_array($ext, $viewable_extensions)) {
                    $imgfolder_images[] = [
                        'url' => '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($legacy_filename) . '&src=legacy',
                        'name' => $legacy_filename,
                        'date' => date('Y-m-d H:i', filemtime($legacy_path)),
                        'source' => 'legacy'
                    ];
                }
            }
        }
        // 2-2. uploads/orders/ 경로
        elseif (strpos($ImgFolder, 'uploads/orders/') === 0) {
            $uploads_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $ImgFolder;
            if (is_dir($uploads_folder)) {
                $files = scandir($uploads_folder);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, $viewable_extensions)) {
                            $imgfolder_images[] = [
                                'url' => '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($file) . '&src=uploads',
                                'name' => $file,
                                'date' => date('Y-m-d H:i', filemtime($uploads_folder . '/' . $file)),
                                'source' => 'uploads_orders'
                            ];
                        }
                    }
                }
            }
        }
        // 2-3. _MlangPrintAuto_ 경로 또는 기타 ImgFolder 경로
        else {
            $img_folder_base = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $ImgFolder;
            if (is_dir($img_folder_base)) {
                $files = scandir($img_folder_base);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, $viewable_extensions)) {
                            $imgfolder_images[] = [
                                'url' => '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($file) . '&src=imgfolder&folder=' . urlencode($ImgFolder),
                                'name' => $file,
                                'date' => date('Y-m-d H:i', filemtime($img_folder_base . '/' . $file)),
                                'source' => 'imgfolder'
                            ];
                        }
                    }
                }
            }
        }

        // ImgFolder 이미지 중 upload에 이미 있는 파일명은 제외 (중복 방지)
        $existing_names = array_column($all_images, 'name');
        foreach ($imgfolder_images as $img) {
            if (!in_array($img['name'], $existing_names)) {
                $all_images[] = $img;
            }
        }
    }

    // 3. uploaded_files JSON에서 이미지 찾기
    if (!empty($uploaded_files_json) && $uploaded_files_json !== '0') {
        $uploaded_files = json_decode($uploaded_files_json, true);
        if (is_array($uploaded_files)) {
            $existing_names = array_column($all_images, 'name');
            foreach ($uploaded_files as $file_info) {
                $file_path = $file_info['path'] ?? '';
                if (!empty($file_path) && file_exists($file_path)) {
                    $fname = basename($file_path);
                    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                    if (in_array($ext, $viewable_extensions) && !in_array($fname, $existing_names)) {
                        $all_images[] = [
                            'url' => '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($fname) . '&src=uploaded',
                            'name' => $fname,
                            'date' => date('Y-m-d H:i', filemtime($file_path)),
                            'source' => 'uploaded_files'
                        ];
                    }
                }
            }
        }
    }

    // 하위 호환: 기존 변수들 유지 (auth 섹션에서 사용)
    $found_image_path = !empty($all_images) ? 'exists' : '';
    $found_image_url = !empty($all_images) ? $all_images[0]['url'] : '';
    $image_source = !empty($all_images) ? $all_images[0]['source'] : '';

    if (empty($all_images)) {
        $no_image_message = "업로드된 이미지 파일이 없습니다.";
    }
} else {
    echo "<script>
            window.alert('데이터가 없습니다.');
            window.self.close();
          </script>
          <meta charset='UTF-8'>";
    exit;
}

$db->close();

include "../admin/mlangprintauto/int/info.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>교정보기 - <?= htmlspecialchars($View_OrderName) ?> #<?= htmlspecialchars($no) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Noto Sans KR', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #000;
    color: #e2e8f0;
    line-height: 1.6;
    font-size: 14px;
    min-height: 100vh;
    overflow: hidden;
}

/* ============================================ */
/* Password / Auth Form Styles                  */
/* ============================================ */
.auth-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 8px;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.auth-header-section {
    background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.auth-company-logo {
    text-align: center;
    position: relative;
    z-index: 1;
}

.auth-company-logo h1 {
    color: white;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
}

.auth-company-logo .subtitle {
    color: #cbd5e1;
    font-size: 16px;
}

.auth-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    color: #334155;
}

/* ============================================ */
/* No Image State                               */
/* ============================================ */
.no-image-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: #334155;
    padding: 32px;
}

.no-image-card {
    background: white;
    border-radius: 16px;
    padding: 48px 32px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    text-align: center;
    max-width: 500px;
    width: 100%;
}

.no-image-card h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
}

.no-image-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.no-image-text {
    color: #64748b;
    font-size: 15px;
    margin-bottom: 24px;
}

.no-image-order-info {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #475569;
}

.btn-close-window {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(220, 38, 38, 0.3);
    transition: all 0.2s ease;
}

.btn-close-window:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* ============================================ */
/* Image Viewer — Full Screen Overlay           */
/* ============================================ */
.viewer-wrap {
    position: fixed;
    inset: 0;
    z-index: 60;
    background: #000;
}

/* Top Header Bar */
.viewer-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 72px;
    background: #1e293b;
    padding: 6px 0;
    z-index: 85;
    text-align: center;
}

.header-title {
    color: white;
    font-size: 15px;
    font-weight: 600;
}

.header-order-info {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 2px;
    font-size: 11px;
    color: #94a3b8;
}

.header-order-info span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.header-order-info .info-value {
    color: #e2e8f0;
    font-weight: 500;
}

.header-notice {
    margin-top: 3px;
}

.notice-item {
    color: #cbd5e1;
    font-size: 11px;
    display: block;
    line-height: 1.3;
}

.notice-item.highlight {
    color: #fbbf24;
}

/* Image Container (Zoom/Pan area) */
.image-container {
    position: absolute;
    top: 88px;
    left: 0;
    right: 72px;
    bottom: 104px;
    overflow: hidden;
    background: #1a1a1a;
}

.overlay-image {
    max-width: none;
    max-height: none;
    object-fit: contain;
    transition: transform 0.1s ease-out;
    cursor: default;
    user-select: none;
    -webkit-user-drag: none;
    transform: translate(0, 0) scale(1);
    transform-origin: 0 0;
}

.overlay-image.dragging {
    cursor: grabbing;
}

/* Right Control Panel */
.control-panel {
    position: fixed;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: rgba(30, 30, 30, 0.9);
    padding: 10px 6px;
    border-radius: 12px;
    z-index: 80;
    backdrop-filter: blur(10px);
}

.control-btn {
    width: 44px;
    height: 44px;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.85);
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

.control-btn:active {
    background: rgba(255, 255, 255, 0.25);
}

.control-btn svg {
    pointer-events: none;
}

.control-btn.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.control-btn.nav-btn-hidden {
    display: none;
}

.control-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.15);
    margin: 4px 0;
}

/* Status Bar */
.status-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 44px;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    z-index: 80;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.status-left,
.status-center,
.status-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.zoom-level {
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    font-weight: 500;
    min-width: 80px;
}

.img-counter {
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
}

.img-filename {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12px;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.img-date {
    color: rgba(255, 255, 255, 0.5);
    font-size: 11px;
}

/* Thumbnail Bar (left side) */
.thumbnail-bar {
    position: fixed;
    top: 88px;
    left: 0;
    bottom: 44px;
    width: 72px;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 4px;
    z-index: 75;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.3) transparent;
}

.thumbnail-bar.hidden {
    display: none;
}

.thumbnail-bar::-webkit-scrollbar {
    width: 4px;
}
.thumbnail-bar::-webkit-scrollbar-track {
    background: transparent;
}
.thumbnail-bar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.thumbnails-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 4px;
}

.thumb-item {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 6px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.15s ease;
    opacity: 0.5;
    flex-shrink: 0;
}

.thumb-item:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.thumb-item.active {
    border-color: white;
    opacity: 1;
    box-shadow: 0 0 0 2px rgba(255,255,255,0.3);
}

/* Proof Confirmation Area */
.proof-confirm-area {
    position: fixed;
    bottom: 44px;
    left: 0;
    right: 72px;
    background: #1e293b;
    padding: 10px 16px;
    z-index: 85;
    text-align: center;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

.proof-confirm-btn {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(22, 163, 74, 0.3);
    transition: all 0.3s ease;
}

.proof-confirm-btn:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.4);
}

.proof-confirm-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.proof-confirm-notice {
    font-size: 11px;
    color: #fbbf24;
    margin: 0;
    line-height: 1.3;
    white-space: nowrap;
}

.proof-confirmed-msg {
    color: #dc2626;
    font-weight: 600;
    font-size: 14px;
}

/* Loading spinner */
.loading {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .viewer-header {
        right: 0;
        padding: 4px 0;
    }
    .header-title {
        font-size: 13px;
    }
    .header-order-info {
        gap: 8px;
        font-size: 10px;
    }
    .notice-item {
        font-size: 10px;
    }
    .image-container {
        right: 0;
        top: 80px;
        bottom: 96px;
    }
    .control-panel {
        right: 4px;
        padding: 6px 4px;
        gap: 4px;
        border-radius: 8px;
        background: rgba(30, 30, 30, 0.7);
    }
    .control-btn {
        width: 36px;
        height: 36px;
    }
    .proof-confirm-area {
        right: 0;
        padding: 8px 12px;
    }
    .thumbnail-bar {
        width: 56px;
    }
    .thumb-item {
        width: 44px;
        height: 44px;
    }
}
</style>
</head>

<body>

<?php
$mode = $_REQUEST['mode'] ?? '';
$FormPass = $_POST['FormPass'] ?? '';

// 관리자 인증 확인
$is_admin = false;
$View_SignMMk = isset($View_SignMMk) ? $View_SignMMk : "";

if ($View_SignMMk == "yes") {
    include "../db.php";
    $AdminChickTYyj = $db->query("SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1");
    $row_AdminChickTYyj = $AdminChickTYyj->fetch_assoc();
    $BBSAdminloginKPass = $row_AdminChickTYyj['pass'];
    $BBSAdminloginKK = $row_AdminChickTYyj['id'];

    // 관리자 쿠키 확인
    if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
        $is_admin = true;
    }
    
    // 관리자 세션 확인 (추가 보안)
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $is_admin = true;
    }

    // 관리자인 경우 비밀번호 확인 건너뛰기
    if ($is_admin) {
        // 관리자는 바로 접근 허용
    } else {
        // 일반 사용자인 경우 기존 비밀번호 확인 로직 실행
        if (isset($FormPass)) {
            if ($FormPass == $View_pass || $FormPass == $BBSAdminloginKPass) {
                // authorized
            } else {
                echo "<div class='auth-container'>
                        <div class='auth-card' style='text-align: center; margin-top: 100px;'>
                            <div style='padding: 40px;'>
                                <h2 style='color: #dc2626; margin-bottom: 20px;'>❌ 인증 실패</h2>
                                <p style='margin-bottom: 30px; color: #64748b;'>비밀번호가 올바르지 않습니다.</p>
                                <button onclick='history.go(-1);' class='btn-close-window' style='background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);'>
                                    ← 다시 시도
                                </button>
                            </div>
                        </div>
                      </div>";
                exit;
            }
        } else {
            // 비밀번호 입력 폼 (관리자가 아닌 경우만)
            echo "<div class='auth-container'>
                    <div class='auth-header-section'>
                        <div class='auth-company-logo'>
                            <h1>두손기획인쇄</h1>
                            <div class='subtitle'>DUSON PLANNING PRINT</div>
                        </div>
                    </div>
                    
                    <div class='auth-card' style='text-align: center; margin-top: 50px;'>
                        <form method='post' action='{$_SERVER['PHP_SELF']}' style='padding: 40px;'>
                            <input type='hidden' name='mode' value='$mode'>
                            <input type='hidden' name='no' value='$no'>
                            
                            <h2 style='color: #1e293b; margin-bottom: 20px;'>🔐 주문 확인</h2>
                            <p style='color: #64748b; margin-bottom: 30px; line-height: 1.6;'>
                                이미지 파일 확인을 위해<br>
                                <strong>전화번호 뒷자리 4자리</strong>를 입력해주세요.
                            </p>
                            
                            <div style='margin: 30px 0;'>
                                <input type='text' name='FormPass' size='20' 
                                       style='padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 8px; 
                                              font-size: 16px; text-align: center; width: 200px;'
                                       placeholder='예: 1830' maxlength='4'>
                            </div>
                            
                            <div style='margin: 30px 0;'>
                                <input type='submit' value='확인' 
                                       style='background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
                                              color: white; border: none; padding: 12px 30px; border-radius: 8px; 
                                              font-size: 16px; font-weight: 600; cursor: pointer;
                                              box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);'>
                            </div>
                            
                            <p style='color: #9ca3af; font-size: 14px; margin-top: 30px;'>
                                📞 문의: 02-2632-1830
                            </p>
                        </form>
                    </div>
                  </div>";
            exit;
        }
    }
}
?>

<?php if (empty($all_images)): ?>
<!-- No Images State -->
<div class="no-image-container">
    <div class="no-image-card">
        <div class="no-image-icon">📁</div>
        <h2>교정 이미지가 없습니다</h2>
        <div class="no-image-text">아직 업로드된 교정 파일이 없습니다.</div>
        <div class="no-image-order-info">
            주문번호: <strong>#<?= htmlspecialchars($no) ?></strong> &nbsp;|&nbsp;
            주문자: <strong><?= htmlspecialchars($View_OrderName) ?></strong> &nbsp;|&nbsp;
            담당자: <strong><?= htmlspecialchars($view_designer ?: '미배정') ?></strong>
        </div>
        <button class="btn-close-window" onclick="window.close();">✕ 창 닫기</button>
    </div>
</div>

<?php else: ?>
<!-- Full-Screen Image Viewer -->
<div class="viewer-wrap" id="viewerWrap">
    <!-- Top Header Bar -->
    <div class="viewer-header" id="viewerHeader">
        <div class="header-title">두손기획인쇄 교정 페이지</div>
        <div class="header-order-info">
            <span>주문번호 <span class="info-value">#<?= htmlspecialchars($no) ?></span></span>
            <span>주문자 <span class="info-value"><?= htmlspecialchars($View_OrderName) ?></span></span>
            <span>담당 <span class="info-value"><?= htmlspecialchars($view_designer ?: '미배정') ?></span></span>
        </div>
        <div class="header-notice">
            <span class="notice-item">이미지는 RGB 표시 / 인쇄 시 CMYK 출력으로 색상차이 있음 · 오탈자 및 전체 상태를 확인하여 전반적인 수정사항을 요청해주세요</span>
            <span class="notice-item highlight">수정은 2회 가능합니다</span>
        </div>
    </div>

    <!-- Image Container (Zoom/Pan area) -->
    <div id="imageContainer" class="image-container">
        <img id="overlayImg" src="" alt="" class="overlay-image">
    </div>

    <!-- Right Control Panel -->
    <div id="controlPanel" class="control-panel">
        <button id="zoomInBtn" type="button" title="확대 (+)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="11" y1="8" x2="11" y2="14"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button id="zoomOutBtn" type="button" title="축소 (-)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
        </button>
        <button id="fitBtn" type="button" title="화면 맞춤 (0)" class="control-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                <path d="M9 3v18M15 3v18M3 9h18M3 15h18"></path>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="fullscreenBtn" type="button" title="전체화면 (F)" class="control-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="prevBtn" type="button" title="이전 (←)" class="control-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        <button id="nextBtn" type="button" title="다음 (→)" class="control-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        <div class="control-divider"></div>
        <button id="closeBtn" type="button" title="닫기 (Esc)" class="control-btn close-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Proof Confirmation Area -->
    <div id="proofConfirmArea" class="proof-confirm-area">
        <div id="proofConfirmContent" style="display:flex;align-items:center;justify-content:center;gap:12px;">
            <button id="proofConfirmBtn" type="button" class="proof-confirm-btn">
                📝 교정확정
            </button>
            <p class="proof-confirm-notice">오탈자 및 전체를 잘 확인 후 클릭해주세요</p>
        </div>
        <div id="proofConfirmedMsg" class="proof-confirmed-msg" style="display: none;">
            ✅ 인쇄진행
        </div>
    </div>

    <!-- Bottom Status Bar -->
    <div id="statusBar" class="status-bar">
        <div class="status-left">
            <span id="zoomLevel" class="zoom-level">화면 맞춤</span>
        </div>
        <div class="status-center">
            <span id="imgCounter" class="img-counter"></span>
            <span id="imgFileName" class="img-filename"></span>
        </div>
        <div class="status-right">
            <span id="imgFileDate" class="img-date"></span>
        </div>
    </div>

    <!-- Thumbnail Bar (left side) -->
    <div id="thumbnailBar" class="thumbnail-bar hidden">
        <div id="imgThumbnails" class="thumbnails-container"></div>
    </div>
</div>
<?php endif; ?>

<script>
<?php if (!empty($all_images)): ?>
// === Image Data (from PHP) ===
var viewerImages = <?= json_encode($all_images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
var viewerIndex = 0;
var orderNo = '<?= htmlspecialchars($no, ENT_QUOTES) ?>';

// === Zoom State ===
var zoomState = {
    level: 'fit',
    scale: 1,
    offsetX: 0,
    offsetY: 0,
    isDragging: false,
    startX: 0,
    startY: 0
};

var ZOOM_LEVELS = [25, 50, 75, 100, 125, 150, 200, 300, 400];

// === Keyboard Shortcuts ===
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { e.preventDefault(); window.close(); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); navImage(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); navImage(1); }
    if (e.key === '+' || e.key === '=') { e.preventDefault(); zoomIn(); }
    if (e.key === '-' || e.key === '_') { e.preventDefault(); zoomOut(); }
    if (e.key === '0') { e.preventDefault(); setZoom('fit'); }
    if (e.key === 'f' || e.key === 'F') { e.preventDefault(); toggleFullscreen(); }
});

// === Zoom Functions ===
function fitToScreen() {
    var img = document.getElementById('overlayImg');
    var container = document.getElementById('imageContainer');

    if (!img.naturalWidth) return;

    var containerWidth = container.clientWidth;
    var containerHeight = container.clientHeight;

    var imgRatio = img.naturalWidth / img.naturalHeight;
    var containerRatio = containerWidth / containerHeight;

    var scale;
    if (imgRatio > containerRatio) {
        scale = containerWidth / img.naturalWidth;
    } else {
        scale = containerHeight / img.naturalHeight;
    }

    var scaledWidth = img.naturalWidth * scale;
    var scaledHeight = img.naturalHeight * scale;

    zoomState.level = 'fit';
    zoomState.scale = scale;
    zoomState.offsetX = (containerWidth - scaledWidth) / 2;
    zoomState.offsetY = (containerHeight - scaledHeight) / 2;

    applyTransform();
}

function setZoom(level, centerX, centerY) {
    var img = document.getElementById('overlayImg');
    var container = document.getElementById('imageContainer');
    var oldScale = zoomState.scale;

    if (level === 'fit') {
        fitToScreen();
        return;
    }

    var newScale = level / 100;
    var imgWidth = img.naturalWidth;
    var imgHeight = img.naturalHeight;

    var currentCenterX = zoomState.offsetX + (imgWidth * oldScale) / 2;
    var currentCenterY = zoomState.offsetY + (imgHeight * oldScale) / 2;

    zoomState.level = level;
    zoomState.scale = newScale;
    zoomState.offsetX = currentCenterX - (imgWidth * newScale) / 2;
    zoomState.offsetY = currentCenterY - (imgHeight * newScale) / 2;

    applyTransform();
}

function applyTransform() {
    var img = document.getElementById('overlayImg');

    var zoomText = zoomState.level === 'fit' ? '화면 맞춤' : zoomState.level + '%';
    document.getElementById('zoomLevel').textContent = zoomText;

    img.style.transform = 'translate(' + zoomState.offsetX + 'px, ' + zoomState.offsetY + 'px) scale(' + zoomState.scale + ')';
    img.style.transformOrigin = '0 0';
    img.style.maxWidth = 'none';
    img.style.maxHeight = 'none';

    if (zoomState.level === 'fit') {
        img.style.cursor = 'default';
    } else {
        img.style.cursor = zoomState.isDragging ? 'grabbing' : 'grab';
    }
}

function zoomIn() {
    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);
    var nextIndex = Math.min(currentIndex + 1, ZOOM_LEVELS.length - 1);
    setZoom(ZOOM_LEVELS[nextIndex]);
}

function zoomOut() {
    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);
    if (currentIndex > 0) {
        setZoom(ZOOM_LEVELS[currentIndex - 1]);
    } else {
        setZoom('fit');
    }
}

function toggleFullscreen() {
    var viewer = document.getElementById('viewerWrap');
    if (!document.fullscreenElement) {
        viewer.requestFullscreen().catch(function(err) {
            console.log('Fullscreen error:', err);
        });
    } else {
        document.exitFullscreen();
    }
}

// === Mouse Wheel Zoom ===
document.getElementById('imageContainer').addEventListener('wheel', function(e) {
    e.preventDefault();
    var currentIndex = ZOOM_LEVELS.indexOf(zoomState.level);
    if (zoomState.level === 'fit') currentIndex = ZOOM_LEVELS.indexOf(100);

    if (e.deltaY < 0) {
        var nextIndex = Math.min(currentIndex + 1, ZOOM_LEVELS.length - 1);
        setZoom(ZOOM_LEVELS[nextIndex]);
    } else {
        var prevIndex = Math.max(currentIndex - 1, 0);
        if (prevIndex === 0 && currentIndex === 0 && zoomState.level !== 'fit') {
            setZoom('fit');
        } else {
            setZoom(ZOOM_LEVELS[prevIndex]);
        }
    }
}, { passive: false });

// === Drag/Pan ===
var overlayImg = document.getElementById('overlayImg');
var imageContainer = document.getElementById('imageContainer');
var clickStartPos = { x: 0, y: 0 };

overlayImg.addEventListener('mousedown', function(e) {
    clickStartPos.x = e.clientX;
    clickStartPos.y = e.clientY;
    if (zoomState.level === 'fit') return;
    zoomState.isDragging = true;
    zoomState.startX = e.clientX - zoomState.offsetX;
    zoomState.startY = e.clientY - zoomState.offsetY;
    this.classList.add('dragging');
    e.preventDefault();
});

document.addEventListener('mousemove', function(e) {
    if (!zoomState.isDragging) return;
    zoomState.offsetX = e.clientX - zoomState.startX;
    zoomState.offsetY = e.clientY - zoomState.startY;
    applyTransform();
});

document.addEventListener('mouseup', function() {
    if (zoomState.isDragging) {
        zoomState.isDragging = false;
        var img = document.getElementById('overlayImg');
        if (img) img.classList.remove('dragging');
    }
});

// === Click on image → close window (drag 5px+ = ignore) ===
overlayImg.addEventListener('click', function(e) {
    var dx = Math.abs(e.clientX - clickStartPos.x);
    var dy = Math.abs(e.clientY - clickStartPos.y);
    if (dx < 5 && dy < 5) {
        window.close();
    }
});

// Click on background (imageContainer) → close window
imageContainer.addEventListener('click', function(e) {
    if (e.target === imageContainer) {
        window.close();
    }
});

// === Control Panel Buttons ===
document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
document.getElementById('fitBtn').addEventListener('click', function() { setZoom('fit'); });
document.getElementById('fullscreenBtn').addEventListener('click', toggleFullscreen);
document.getElementById('closeBtn').addEventListener('click', function() { window.close(); });
document.getElementById('prevBtn').addEventListener('click', function(e) { e.stopPropagation(); navImage(-1); });
document.getElementById('nextBtn').addEventListener('click', function(e) { e.stopPropagation(); navImage(1); });

// === Window Resize ===
window.addEventListener('resize', function() {
    if (zoomState.level === 'fit') {
        fitToScreen();
    }
});

// === Thumbnail Builder ===
function buildThumbnails() {
    var container = document.getElementById('imgThumbnails');
    var thumbnailBar = document.getElementById('thumbnailBar');
    container.textContent = '';

    var ic = document.getElementById('imageContainer');
    if (viewerImages.length <= 1) {
        thumbnailBar.classList.add('hidden');
        ic.style.left = '0';
        return;
    }

    thumbnailBar.classList.remove('hidden');
    ic.style.left = '72px';

    viewerImages.forEach(function(img, i) {
        var thumb = document.createElement('img');
        thumb.src = img.url;
        thumb.dataset.idx = i;
        thumb.className = 'thumb-item' + (i === 0 ? ' active' : '');
        thumb.addEventListener('click', function(e) {
            e.stopPropagation();
            viewerIndex = i;
            showImage();
        });
        container.appendChild(thumb);
    });
}

// === Show Image ===
function showImage() {
    var img = viewerImages[viewerIndex];
    var overlayImg = document.getElementById('overlayImg');

    // Reset zoom state
    zoomState.level = 'fit';
    zoomState.scale = 1;
    zoomState.offsetX = 0;
    zoomState.offsetY = 0;

    overlayImg.src = img.url + (img.url.indexOf('?') >= 0 ? '&' : '?') + 't=' + Date.now();
    overlayImg.style.transform = '';
    overlayImg.style.maxWidth = '';
    overlayImg.style.maxHeight = '';

    var total = viewerImages.length;
    document.getElementById('imgCounter').textContent = (viewerIndex + 1) + ' / ' + total;
    document.getElementById('imgFileName').textContent = img.name;
    document.getElementById('imgFileDate').textContent = img.date || '';

    // Image load handler
    overlayImg.onload = function() {
        fitToScreen();
    };

    overlayImg.onerror = function() {
        document.getElementById('imgFileName').textContent += ' (이미지 로드 실패)';
    };

    // Update navigation buttons
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    if (total > 1) {
        prevBtn.classList.toggle('nav-btn-hidden', viewerIndex === 0);
        nextBtn.classList.toggle('nav-btn-hidden', viewerIndex === total - 1);
        document.querySelectorAll('.thumb-item').forEach(function(el, i) {
            if (i === viewerIndex) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    } else {
        prevBtn.classList.add('nav-btn-hidden');
        nextBtn.classList.add('nav-btn-hidden');
    }

    // Check proof status
    checkProofreadingStatus();
}

function navImage(dir) {
    var next = viewerIndex + dir;
    if (next < 0 || next >= viewerImages.length) return;
    viewerIndex = next;
    showImage();
}

// === 교정확정 Functions ===
function checkProofreadingStatus() {
    fetch('/mlangorder_printauto/check_proofreading_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_no=' + encodeURIComponent(orderNo)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.confirmed) {
            showProofreadingCompleted();
        } else {
            showProofreadingButton();
        }
    })
    .catch(function() {
        showProofreadingButton();
    });
}

function showProofreadingButton() {
    document.getElementById('proofConfirmContent').style.display = 'flex';
    document.getElementById('proofConfirmedMsg').style.display = 'none';
    var btn = document.getElementById('proofConfirmBtn');
    btn.disabled = false;
    btn.innerHTML = '📝 교정확정';
}

function showProofreadingCompleted() {
    document.getElementById('proofConfirmContent').style.display = 'none';
    document.getElementById('proofConfirmedMsg').style.display = 'block';
}

function confirmProofreading() {
    showStyledConfirm(
        '오탈자 및 전체를 잘 확인 했습니다.<br>인쇄진행해주세요.<br><br>인쇄 진행 후에는 더이상 수정할 수 없습니다.<br><br><span style="color:#e53e3e;font-weight:bold;font-size:15px;">교정확정 하시겠습니까?</span>',
        function() {
            showStyledConfirm(
                '⚠️ 최종 확인<br><br>교정확정 후에는 취소할 수 없습니다.<br><span style="color:#e53e3e;font-weight:bold;font-size:15px;">정말 인쇄를 진행하시겠습니까?</span>',
                function() { doConfirmProofreading(); }
            );
        }
    );
}

function doConfirmProofreading() {
    var btn = document.getElementById('proofConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> 처리중...';

    fetch('/mlangorder_printauto/confirm_proofreading.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_no=' + encodeURIComponent(orderNo)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert('교정확정이 완료되었습니다.\n인쇄 진행됩니다.');
            showProofreadingCompleted();
        } else {
            alert('교정확정 처리 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            btn.disabled = false;
            btn.innerHTML = '📝 교정확정';
        }
    })
    .catch(function() {
        alert('교정확정 처리 중 오류가 발생했습니다.');
        btn.disabled = false;
        btn.innerHTML = '📝 교정확정';
    });
}

// Styled Confirm Dialog (replaces native confirm)
function showStyledConfirm(htmlMessage, onConfirm) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;';
    var box = document.createElement('div');
    box.style.cssText = 'background:#fff;border-radius:12px;padding:28px 32px 20px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;';
    box.innerHTML = '<div style="font-size:14px;line-height:1.7;margin-bottom:24px;color:#333;">' + htmlMessage + '</div>'
        + '<div style="display:flex;gap:12px;justify-content:center;">'
        + '<button id="scCancel" style="padding:8px 24px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;cursor:pointer;font-size:14px;">취소</button>'
        + '<button id="scConfirm" style="padding:8px 24px;border:none;border-radius:8px;background:#e53e3e;color:#fff;cursor:pointer;font-size:14px;font-weight:600;">확인</button>'
        + '</div>';
    overlay.appendChild(box);
    document.body.appendChild(overlay);
    box.querySelector('#scCancel').onclick = function() { document.body.removeChild(overlay); };
    box.querySelector('#scConfirm').onclick = function() { document.body.removeChild(overlay); onConfirm(); };
    overlay.addEventListener('click', function(e) { if (e.target === overlay) document.body.removeChild(overlay); });
}

// === Event Listener: Proof Confirm Button ===
document.getElementById('proofConfirmBtn').addEventListener('click', confirmProofreading);

// === Page Load: Auto-open viewer + maximize window ===
document.addEventListener('DOMContentLoaded', function() {
    // 창 크기를 화면 전체로 최대화
    try {
        window.moveTo(0, 0);
        window.resizeTo(screen.availWidth, screen.availHeight);
    } catch(e) {
        // 팝업이 아닌 탭에서 열린 경우 resizeTo 불가 — 무시
    }
    if (viewerImages.length > 0) {
        buildThumbnails();
        showImage();
    }
});

<?php endif; ?>
</script>

</body>
</html>
