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

$result = $db->query("SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'");
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

    // 이미지 파일이 없는 경우에도 주문 정보는 표시
    if (!$ImgFile) {
        $ImgFile = ''; // 빈 값으로 처리하여 계속 진행
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

include "../admin/MlangPrintAuto/int/info.php";
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
    padding: 12px;
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
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
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
    padding: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
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
    <div style="background: #1e293b; padding: 12px 0; margin-bottom: 16px; border-radius: 8px;">
        <div style="text-align: center; color: white; font-size: 18px; font-weight: 600;">
            두손기획인쇄
        </div>
        <div style="text-align: center; color: #cbd5e1; font-size: 11px; margin-top: 4px; line-height: 1.4;">
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
    $AdminChickTYyj = $db->query("SELECT * FROM member WHERE no='1'");
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
            <h3 style="font-size: 16px; color: #374151; margin: 0;">업로드된 파일</h3>
        </div>
        
        <?php if (!empty($ImgFile) && file_exists("./upload/$no/$ImgFile")) { ?>
            <div class="image-container">
                <img src="./upload/<?= $no ?>/<?= $ImgFile ?>" alt="주문 이미지" onclick="window.close();" style="cursor: pointer;">
            </div>
            <p style="margin-top: 16px; color: #64748b; font-size: 13px;">
                💡 이미지를 클릭하면 창이 닫힙니다
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

</div> <!-- Close container -->

</body>
</html>
