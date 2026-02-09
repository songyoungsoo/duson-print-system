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

    // 이미지 경로 찾기 (폴백 순서: 교정이미지 → ImgFolder → uploaded_files)
    // 모든 이미지 URL은 view_proof.php 프록시를 통해 서빙 (nginx 이미지 가로채기 우회)
    $found_image_path = '';
    $found_image_url = '';
    $image_source = '';

    // 1. 교정 이미지 경로 확인 (__DIR__ 기반 절대경로)
    if (!empty($ImgFile)) {
        $proof_image_path = __DIR__ . "/upload/$no/$ImgFile";
        if (file_exists($proof_image_path)) {
            $found_image_path = $proof_image_path;
            $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($ImgFile);
            $image_source = 'proof';
        }
    }

    // 2. ImgFolder 경로 확인 (폴백)
    if (empty($found_image_path) && !empty($ImgFolder)) {
        // 2-1. 레거시 경로 처리 (../shop/data/파일명)
        if (strpos($ImgFolder, '../shop/data/') === 0) {
            $legacy_filename = basename($ImgFolder);
            $legacy_path = $_SERVER['DOCUMENT_ROOT'] . '/shop/data/' . $legacy_filename;
            if (file_exists($legacy_path)) {
                $ext = strtolower(pathinfo($legacy_path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'ai', 'psd', 'hwp', 'hwpx'])) {
                    $found_image_path = $legacy_path;
                    $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($legacy_filename) . '&src=legacy';
                    $image_source = 'legacy';
                }
            }
        }
        // 2-2. uploads/orders/ 경로 (최신 업로드 시스템)
        elseif (strpos($ImgFolder, 'uploads/orders/') === 0) {
            $uploads_folder = $_SERVER['DOCUMENT_ROOT'] . '/' . $ImgFolder;
            if (is_dir($uploads_folder)) {
                $files = scandir($uploads_folder);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'ai', 'psd'])) {
                            $found_image_path = $uploads_folder . '/' . $file;
                            $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($file) . '&src=uploads';
                            $image_source = 'uploads_orders';
                            break;
                        }
                    }
                }
            }
            if (empty($found_image_path) && !empty($ImgFile)) {
                $thingcate_path = $uploads_folder . '/' . $ImgFile;
                if (file_exists($thingcate_path)) {
                    $found_image_path = $thingcate_path;
                    $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($ImgFile) . '&src=uploads';
                    $image_source = 'uploads_orders';
                }
            }
        }
        // 2-3. _MlangPrintAuto_ 경로 (신규 업로드 시스템)
        elseif (strpos($ImgFolder, '_MlangPrintAuto_') !== false) {
            $img_folder_base = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $ImgFolder;
            if (is_dir($img_folder_base)) {
                $files = scandir($img_folder_base);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                            $found_image_path = $img_folder_base . '/' . $file;
                            $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($file) . '&src=imgfolder&folder=' . urlencode($ImgFolder);
                            $image_source = 'imgfolder';
                            break;
                        }
                    }
                }
            }
        }
        // 2-4. 기타 ImgFolder 경로
        else {
            $img_folder_base = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $ImgFolder;
            if (is_dir($img_folder_base)) {
                $files = scandir($img_folder_base);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                            $found_image_path = $img_folder_base . '/' . $file;
                            $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode($file) . '&src=imgfolder&folder=' . urlencode($ImgFolder);
                            $image_source = 'imgfolder';
                            break;
                        }
                    }
                }
            }
        }
    }

    // 3. uploaded_files JSON에서 이미지 찾기 (폴백)
    if (empty($found_image_path) && !empty($uploaded_files_json) && $uploaded_files_json !== '0') {
        $uploaded_files = json_decode($uploaded_files_json, true);
        if (is_array($uploaded_files) && count($uploaded_files) > 0) {
            foreach ($uploaded_files as $file_info) {
                $file_path = $file_info['path'] ?? '';
                $web_url = $file_info['web_url'] ?? '';
                if (!empty($file_path) && file_exists($file_path)) {
                    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                        $found_image_path = $file_path;
                        $found_image_url = '/mlangorder_printauto/view_proof.php?no=' . $no . '&file=' . urlencode(basename($file_path)) . '&src=uploaded';
                        $image_source = 'uploaded_files';
                        break;
                    }
                }
            }
        }
    }

    // 이미지 파일이 없는 경우에도 주문 정보는 표시
    if (empty($found_image_path)) {
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
<title>주문 상세 보기 - <?= $View_OrderName ?></title>
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
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    color: #334155;
    line-height: 1.6;
    font-size: 14px;
    min-height: 100vh;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 8px;
    min-height: 100vh;
}

/* Header Section */
.header-section {
    background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.header-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.company-logo {
    text-align: center;
    position: relative;
    z-index: 1;
}

.company-logo h1 {
    color: white;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.company-logo .subtitle {
    color: #cbd5e1;
    font-size: 16px;
    font-weight: 400;
}

.notice-banner {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #f59e0b;
    border-radius: 12px;
    padding: 20px;
    margin: 24px 0;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
}

.notice-banner p {
    color: #92400e;
    font-size: 14px;
    font-weight: 500;
    margin: 4px 0;
    line-height: 1.5;
}

/* Order Info Card */
.order-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}

.order-header {
    text-align: center;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 2px solid #f1f5f9;
}

.order-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.order-subtitle {
    color: #64748b;
    font-size: 16px;
    font-weight: 500;
}

.order-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.detail-group {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #3b82f6;
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    padding: 8px 0;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    min-width: 120px;
    font-size: 14px;
    display: flex;
    align-items: center;
}

.detail-label::after {
    content: ':';
    margin: 0 12px 0 8px;
    color: #6b7280;
}

.detail-value {
    color: #1f2937;
    font-weight: 500;
    flex: 1;
    font-size: 14px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-processing { background: #dbeafe; color: #1d4ed8; }
.status-design { background: #e0e7ff; color: #5b21b6; }
.status-printing { background: #fef3c7; color: #d97706; }
.status-shipping { background: #d1fae5; color: #059669; }
.status-completed { background: #dcfce7; color: #16a34a; }
.status-cancelled { background: #fee2e2; color: #dc2626; }
.status-working { background: #fef2f2; color: #991b1b; }

.contact-info {
    color: #dc2626;
    font-weight: 600;
    background: #fef2f2;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 13px;
}

/* Image Section */
.image-section {
    background: white;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    text-align: center;
}

.image-header {
    margin-bottom: 24px;
}

.image-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.image-subtitle {
    color: #64748b;
    font-size: 14px;
}

.image-container {
    position: relative;
    display: inline-block;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    max-width: 100%;
}

.image-container:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 48px rgba(0,0,0,0.16);
}

.image-container img {
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
}

.no-image-message {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 48px 32px;
    color: #64748b;
    font-size: 16px;
    font-weight: 500;
}

.close-button {
    position: fixed;
    top: 24px;
    right: 24px;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(220, 38, 38, 0.3);
    transition: all 0.3s ease;
    z-index: 1000;
}

.close-button:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 16px;
    }
    
    .header-section,
    .order-card,
    .image-section {
        padding: 20px;
    }
    
    .order-details {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .detail-label {
        min-width: auto;
        margin-bottom: 4px;
    }
    
    .detail-label::after {
        content: '';
        margin: 0;
    }
    
    .company-logo h1 {
        font-size: 24px;
    }
    
    .order-title {
        font-size: 20px;
    }
}

/* Print Styles */
@media print {
    body {
        background: white;
    }
    
    .close-button {
        display: none;
    }
    
    .container {
        max-width: none;
        padding: 0;
    }
    
    .header-section,
    .order-card,
    .image-section {
        box-shadow: none;
        border: 1px solid #e2e8f0;
        page-break-inside: avoid;
    }
}

/* Loading Animation */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* 교정확정 버튼 스타일 */
.proofreading-confirm-btn {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(22, 163, 74, 0.3);
    transition: all 0.3s ease;
    min-width: 120px;
}

.proofreading-confirm-btn:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.4);
}

.proofreading-confirm-btn:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.order-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    min-width: 80px;
}

.detail-label::after {
    content: ':';
    margin-left: 4px;
}

.detail-value {
    color: #1e293b;
    font-weight: 500;
}
</style>
</head>

<body>
<div class="container">
    <!-- Close Button -->
    <button class="close-button" onclick="window.close();">
        ✕ 창 닫기
    </button>
    
    <?php if ($View_SignMMk == "yes" && $is_admin) { ?>
    <!-- Admin Badge -->
    <div style="position: fixed; top: 24px; left: 24px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); 
                color: white; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; 
                box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3); z-index: 1000;">
        👑 관리자 로그인
    </div>
    <?php } ?>

    <!-- Minimal Header -->
    <div style="background: #1e293b; padding: 8px 0; margin-bottom: 8px; border-radius: 6px;">
        <div style="text-align: center; color: white; font-size: 16px; font-weight: 600;">
            두손기획인쇄
        </div>
        <div style="text-align: center; color: #cbd5e1; font-size: 10px; margin-top: 2px; line-height: 1.3;">
            이미지는 RGB 표시 / 인쇄 시 CMYK 출력으로 색상차이 있음<br>
            오탈자 및 전체 상태를 확인하여 전반적인 수정사항을 요청하셔야 합니다<br>
            <span style="color: #fbbf24;">수정은 2회 가능합니다</span>
        </div>
    </div>

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
                echo "<div class='container'>
                        <div class='order-card' style='text-align: center; margin-top: 100px;'>
                            <div style='padding: 40px;'>
                                <h2 style='color: #dc2626; margin-bottom: 20px;'>❌ 인증 실패</h2>
                                <p style='margin-bottom: 30px; color: #64748b;'>비밀번호가 올바르지 않습니다.</p>
                                <button onclick='history.go(-1);' class='close-button' style='position: relative; top: 0; right: 0; margin: 0;'>
                                    ← 다시 시도
                                </button>
                            </div>
                        </div>
                      </div>";
                exit;
            }
        } else {
            // 비밀번호 입력 폼 (관리자가 아닌 경우만)
            echo "<div class='container'>
                    <div class='header-section'>
                        <div class='company-logo'>
                            <h1>두손기획인쇄</h1>
                            <div class='subtitle'>DUSON PLANNING PRINT</div>
                        </div>
                    </div>
                    
                    <div class='order-card' style='text-align: center; margin-top: 50px;'>
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

    <!-- Image Section -->
    <div class="image-section">
        <div style="margin-bottom: 16px; text-align: center;">
            <div class="order-details" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; text-align: left;">
                <div class="detail-item">
                    <span class="detail-label">주문번호</span>
                    <span class="detail-value"><?= htmlspecialchars($no) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">담당자</span>
                    <span class="detail-value"><?= htmlspecialchars($view_designer ?: '미배정') ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">주문자</span>
                    <span class="detail-value"><?= htmlspecialchars($View_OrderName) ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($found_image_url)) { ?>
            <div class="image-container">
                <img src="<?= htmlspecialchars($found_image_url) ?>" alt="주문 이미지" onclick="window.close();" style="cursor: pointer;">
            </div>
            <p style="margin-top: 16px; color: #64748b; font-size: 13px;">
                💡 이미지를 클릭하면 창이 닫힙니다
                <?php if ($image_source === 'imgfolder'): ?>
                <br><small style="color: #94a3b8;">(고객 원고 파일)</small>
                <?php elseif ($image_source === 'uploaded_files'): ?>
                <br><small style="color: #94a3b8;">(업로드 파일)</small>
                <?php endif; ?>
            </p>
        <?php } else { ?>
            <div class="no-image-message">
                📁 업로드된 이미지 파일이 없습니다
                <br>
                <small style="color: #9ca3af; margin-top: 8px; display: block;">
                    No uploaded image file found
                </small>
            </div>
        <?php } ?>
    </div>

    <!-- 교정확정 섹션 (이미지 하단) -->
    <div class="proofreading-section" style="margin-top: 12px; padding: 12px; background: white; border-radius: 6px; box-shadow: 0 1px 6px rgba(0,0,0,0.06);">
        <div id="proofreadingStatus" style="text-align: center;">
            <div id="proofreadingButton">
                <button onclick="confirmProofreading()" class="proofreading-confirm-btn" id="confirmBtn">
                    📝 교정확정
                </button>
                <p style="font-size: 11px; color: #64748b; margin: 6px 0 0 0; line-height: 1.3;">
                    오탈자 및 전체를 잘 확인 후 클릭해주세요
                </p>
            </div>
            <div id="proofreadingCompleted" style="display: none; color: #dc2626; font-weight: 600; font-size: 14px;">
                ✅ 인쇄진행
            </div>
        </div>
    </div>

</div> <!-- Close container -->

<script>
// 페이지 로드 시 교정확정 상태 확인
document.addEventListener('DOMContentLoaded', function() {
    checkProofreadingStatus();
});

// 교정확정 상태 확인 함수
function checkProofreadingStatus() {
    const orderNo = '<?= $no ?>';
    
    fetch('/mlangorder_printauto/check_proofreading_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_no=' + encodeURIComponent(orderNo)
    })
    .then(response => response.json())
    .then(data => {
        if (data.confirmed) {
            showProofreadingCompleted();
        }
    })
    .catch(error => {
        console.log('교정확정 상태 확인 중 오류:', error);
    });
}

// 교정확정 함수
function confirmProofreading() {
    if (!confirm('오탈자 및 전체를 잘 확인 했습니다.\n인쇄진행해주세요.\n\n인쇄 진행 후에는 더이상 수정할 수 없습니다.\n\n교정확정 하시겠습니까?')) {
        return;
    }
    
    const orderNo = '<?= $no ?>';
    const button = document.getElementById('confirmBtn');
    
    // 버튼 비활성화 및 로딩 표시
    button.disabled = true;
    button.innerHTML = '<div class="loading"></div> 처리중...';
    
    fetch('/mlangorder_printauto/confirm_proofreading.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_no=' + encodeURIComponent(orderNo)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('교정확정이 완료되었습니다.\n인쇄 진행됩니다.');
            showProofreadingCompleted();
        } else {
            alert('교정확정 처리 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            // 버튼 복원
            button.disabled = false;
            button.innerHTML = '📝 교정확정';
        }
    })
    .catch(error => {
        console.error('교정확정 처리 중 오류:', error);
        alert('교정확정 처리 중 오류가 발생했습니다.');
        // 버튼 복원
        button.disabled = false;
        button.innerHTML = '📝 교정확정';
    });
}

// 교정확정 완료 상태 표시
function showProofreadingCompleted() {
    document.getElementById('proofreadingButton').style.display = 'none';
    document.getElementById('proofreadingCompleted').style.display = 'block';
}
</script>

</body>
</html>
