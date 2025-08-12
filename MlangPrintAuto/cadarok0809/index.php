<?php
// 공통 함수 및 설정
include "../../includes/functions.php";
include "../../db.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 세션 및 기본 설정
check_session();
check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 로그 정보 생성
$log_info = generateLogInfo();

// 로그인 처리
$login_message = '';
if ($_POST['login_action'] ?? '' === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $login_message = '로그인 성공! 환영합니다.';
    } else {
        $login_message = '아이디와 비밀번호를 입력해주세요.';
    }
}

// 페이지 제목 설정
$page_title = generate_page_title("카다록/리플렛 자동견적");

// 데이터베이스 연결 변수 호환성
$connect = $db;

// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";
$MultyUploadDir = "../../PHPClass/MultyUpload";

// 로그 정보에서 필요한 변수들 추출
$log_url = $log_info['url'];
$log_y = $log_info['y'];
$log_md = $log_info['md'];
$log_ip = $log_info['ip'];
$log_time = $log_info['time'];

// 드롭다운 옵션을 가져오는 함수들
function getOptions($connect, $GGTABLE, $page, $BigNo) {
    $options = [];
    $res = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$BigNo' ORDER BY no ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $options[] = $row;
    }
    return $options;
}

// 초기 구분값 가져오기
$initial_type = "";
$type_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $initial_type = $type_row['no'];
}

// 초기 규격 옵션 가져오기
$size_options = getOptions($connect, $GGTABLE, $page, $initial_type);

// 초기 규격의 첫 번째 값 가져오기
$initial_size = "";
if (!empty($size_options)) {
    $initial_size = $size_options[0]['no'];
}

// 초기 종이종류 옵션 가져오기
$paper_options = [];
if (!empty($initial_size)) {
    $paper_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND TreeNo='$initial_type' ORDER BY no ASC");
    while ($paper_row = mysqli_fetch_assoc($paper_result)) {
        $paper_options[] = $paper_row;
    }
} 

// 카다록 관련 설정
$page = "cadarok"; // 페이지를 cadarok으로 설정
$GGTABLE = "MlangPrintAuto_transactionCate";

// 초기 구분값 가져오기
$initial_type = "";
$type_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($type_row = mysqli_fetch_assoc($type_result)) {
  $initial_type = $type_row['no'];
  error_log("초기 구분: " . $initial_type . " - " . $type_row['title']);
}

// 초기 규격 옵션 가져오기 (BigNo = 초기 구분값)
$size_options = getOptions($connect, $GGTABLE, $page, $initial_type);
error_log("규격 옵션 개수: " . count($size_options));

// 초기 규격의 첫 번째 값 가져오기
$initial_size = "";
if (!empty($size_options)) {
  $initial_size = $size_options[0]['no'];
  error_log("초기 규격: " . $initial_size . " - " . $size_options[0]['title']);
}

// 종이종류 옵션 가져오기 (TreeNo = 초기 구분값, 데이터 구조상 종이종류는 구분에 직접 연결됨)
$paper_options = [];
if ($initial_type) {
  $paper_query = "SELECT no, title FROM $GGTABLE WHERE TreeNo='$initial_type' ORDER BY no ASC";
  $paper_result = mysqli_query($connect, $paper_query);
  
  // 디버깅: 쿼리와 결과 확인
  error_log("종이종류 쿼리: " . $paper_query);
  error_log("종이종류 결과 개수: " . mysqli_num_rows($paper_result));
  
  while ($row = mysqli_fetch_assoc($paper_result)) {
    $paper_options[] = $row;
    error_log("종이종류 옵션: " . $row['no'] . " - " . $row['title']);
  }
} else {
  error_log("초기 구분이 없습니다. initial_type: " . $initial_type);
}

// 로그 세부 정보
$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = $_SERVER['REMOTE_ADDR'];
$log_time = time();

// 로그인 처리
$login_message = '';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_action'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if (empty($username) || empty($password)) {
            $login_message = '아이디와 비밀번호를 입력해주세요.';
        } else {
            if (!$connect) {
                $login_message = '데이터베이스 연결에 실패했습니다.';
            } else {
                $setup_success = false;
                $table_exists = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
                
                if (mysqli_num_rows($table_exists) > 0) {
                    $required_columns = ['id', 'username', 'password', 'name'];
                    $all_columns_exist = true;
                    foreach ($required_columns as $column) {
                        $check_column = mysqli_query($connect, "SHOW COLUMNS FROM users LIKE '$column'");
                        if (mysqli_num_rows($check_column) == 0) {
                            $all_columns_exist = false;
                            break;
                        }
                    }
                    
                    if (!$all_columns_exist) {
                        $backup_table = "users_backup_" . date('YmdHis');
                        mysqli_query($connect, "CREATE TABLE $backup_table AS SELECT * FROM users");
                        mysqli_query($connect, "DROP TABLE users");
                        $create_table_query = "CREATE TABLE users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            username VARCHAR(50) UNIQUE NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            name VARCHAR(100) NOT NULL,
                            email VARCHAR(100) DEFAULT NULL,
                            phone VARCHAR(20) DEFAULT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )";  
                        if (mysqli_query($connect, $create_table_query)) {
                            $setup_success = true;
                        } else {
                            $login_message = '테이블 재생성 중 오류: ' . mysqli_error($connect);
                        }
                    } else {
                        $optional_columns = ['email', 'phone'];
                        foreach ($optional_columns as $column) {
                            $check_column = mysqli_query($connect, "SHOW COLUMNS FROM users LIKE '$column'");
                            if (mysqli_num_rows($check_column) == 0) {
                                if ($column == 'email') {
                                    mysqli_query($connect, "ALTER TABLE users ADD COLUMN email VARCHAR(100) DEFAULT NULL");
                                } elseif ($column == 'phone') {
                                    mysqli_query($connect, "ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
                                }
                            }
                        }
                        $setup_success = true;
                    }
                } else {
                    $create_table_query = "CREATE TABLE users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(100) DEFAULT NULL,
                        phone VARCHAR(20) DEFAULT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";
                    if (mysqli_query($connect, $create_table_query)) {
                        $setup_success = true;
                    } else {
                        $login_message = '테이블 생성 중 오류: ' . mysqli_error($connect);
                    }
                }
                
                if ($setup_success && empty($login_message)) {
                    $verify_columns = mysqli_query($connect, "SHOW COLUMNS FROM users");
                    $columns = [];
                    while ($row = mysqli_fetch_assoc($verify_columns)) {
                        $columns[] = $row['Field'];
                    }
                    
                    if (in_array('password', $columns) && in_array('name', $columns)) {
                        $admin_check = mysqli_query($connect, "SELECT id FROM users WHERE username = 'admin'");
                        if ($admin_check && mysqli_num_rows($admin_check) == 0) {
                            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                            $admin_insert = mysqli_query($connect, "INSERT INTO users (username, password, name, email) VALUES ('admin', '$admin_password', '관리자', 'admin@dusong.co.kr')");
                            if (!$admin_insert) {
                                $login_message = '관리자 계정 생성 중 오류: ' . mysqli_error($connect);
                            }
                        }
                    } else {
                        $login_message = '테이블 구조 확인 실패: 필수 컬럼이 없습니다.';
                    }
                }
            }
            
            if (empty($login_message)) {
                $query = "SELECT id, username, password, name FROM users WHERE username = ?";
                $stmt = mysqli_prepare($connect, $query);  
              
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $username);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($user = mysqli_fetch_assoc($result)) {
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['user_name'] = $user['name'];
                            $is_logged_in = true;
                            $user_name = $user['name'];
                            $login_message = '로그인 성공!';
                        } else {
                            $login_message = '비밀번호가 올바르지 않습니다.';
                        }
                    } else {
                        $login_message = '존재하지 않는 사용자입니다.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
                }
            }
        }
    } elseif (isset($_POST['register_action'])) {
        $username = trim($_POST['reg_username']);
        $password = trim($_POST['reg_password']);
        $confirm_password = trim($_POST['reg_confirm_password']);
        $name = trim($_POST['reg_name']);
        $email = trim($_POST['reg_email']);
        $phone = trim($_POST['reg_phone']);
        
        if (empty($username) || empty($password) || empty($name)) {
            $login_message = '필수 항목을 모두 입력해주세요.';
        } elseif ($password !== $confirm_password) {
            $login_message = '비밀번호가 일치하지 않습니다.';
        } elseif (strlen($password) < 6) {
            $login_message = '비밀번호는 6자 이상이어야 합니다.';
        } elseif (!$connect) {
            $login_message = '데이터베이스 연결에 실패했습니다.';
        } else {
            $check_query = "SELECT id FROM users WHERE username = ?";
            $stmt = mysqli_prepare($connect, $check_query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $login_message = '이미 존재하는 아이디입니다.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($connect, $insert_query);
                    
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $name, $email, $phone);
                        
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $login_message = '회원가입이 완료되었습니다. 로그인해주세요.';
                        } else {
                            $login_message = '회원가입 중 오류가 발생했습니다: ' . mysqli_stmt_error($insert_stmt);
                        }
                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['logout_action'])) {
        // 세션 변수 모두 제거
        $_SESSION = array();
        
        // 세션 쿠키 삭제
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // 세션 파괴
        session_destroy();
        
        // 새 세션 시작 (깨끗한 상태로)
        session_start();
        
        // 리다이렉트
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<?php
// 공통 헤더 포함
include "../../includes/header.php";
?>

<?php
// 공통 네비게이션 포함
include "../../includes/nav.php";
?>

    <div class="main-content-wrapper">
        <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 5px;
            max-height: 780px;
            overflow-y: auto;
        }
        
        .card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 5px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 0.3rem;
            border-bottom: 1px solid #dee2e6;
            text-align: center;
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .card-subtitle {
            color: #6c757d;
            font-size: 0.8rem;
            margin: 0;
        }
        
        .order-form-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-form-table td {
            padding: 5px 8px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .label-cell {
            width: 200px;
            font-weight: 600;
            color: #495057;
        }
        
        .icon-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .icon-label .icon {
            font-size: 1.3rem;
        }
        
        .form-control-modern {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .help-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .calculate-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 12px;
            margin: 20px 0;
        }
        
        .price-result {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 10px;
            border-radius: 8px;
            margin: 5px 0;
            text-align: center;
            color: white;
        }
        
        .price-result h3 {
            margin: 0 0 20px 0;
            color: white;
            font-size: 1.5rem;
        }
        
        .selected-options {
            background: white;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 8px;
            text-align: left;
        }
        
        .selected-options h4 {
            color: #495057;
            margin-bottom: 15px;
        }
        
        .option-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .option-label {
            color: #6c757d;
            font-weight: 600;
        }
        
        .option-value {
            color: #495057;
            font-weight: 600;
        }
        
        .price-display {
            background: white;
            border-radius: 6px;
            padding: 8px;
            margin: 5px 0;
        }
        
        .price-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #495057;
            margin: 5px 0;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-action {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        /* 업로드 컴포넌트 높이 조정 */
        .file-upload-container {
            margin: 3px 0;
        }
        
        .file-upload-drop-zone {
            min-height: 40px !important;
            padding: 8px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }
        
        .file-upload-title {
            font-size: 0.8rem !important;
            margin: 0 !important;
            display: none !important;
        }
        
        .file-upload-description {
            font-size: 0.7rem !important;
            margin: 0 !important;
            flex: 1 !important;
        }
        
        .file-upload-format-text {
            font-size: 0.65rem !important;
            margin: 0 !important;
        }
        
        .file-upload-icon {
            display: none !important;
        }
        </style>
        
        <div class="container">
            <!-- 주문 폼 -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📖 카다록/리플렛 주문 옵션 선택</h2>
                    <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                </div>

            <div class="container">
        <!-- 주문 폼 -->
        <form name="choiceForm" method="post" action="order_process.php">
            <div class="form-section">
                <input type="hidden" name="action" value="calculate">
                
                <!-- 가격 계산 결과를 저장할 hidden 필드들 -->
                <input type="hidden" name="Price" value="">
                <input type="hidden" name="DS_Price" value="">
                <input type="hidden" name="Order_Price" value="">
                <input type="hidden" name="PriceForm" value="">
                <input type="hidden" name="DS_PriceForm" value="">
                <input type="hidden" name="Order_PriceForm" value="">
                <input type="hidden" name="VAT_PriceForm" value="">
                <input type="hidden" name="Total_PriceForm" value="">
                <input type="hidden" name="StyleForm" value="">
                <input type="hidden" name="SectionForm" value="">
                <input type="hidden" name="QuantityForm" value="">
                <input type="hidden" name="DesignForm" value="">
                <input type="hidden" name="OnunloadChick" value="off">
                        
                        <table class="order-form-table">
                            <tbody>
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">🎨</span>
                                            <span>1. 구분</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_type" id="MY_type" class="form-control-modern" onchange="change_Field(this.value)">
                                            <?php
                                            $res = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                                            while ($row = mysqli_fetch_assoc($res)) {
                                              $selected = ($row['no'] == $initial_type) ? "selected" : "";
                                              echo "<option value='{$row['no']}' $selected>" . htmlspecialchars($row['title']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📏</span>
                                            <span>2. 규격</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_Fsd" id="MY_Fsd" class="form-control-modern" onchange="updatePaperType(this.value);">
                                            <?php foreach ($size_options as $opt) echo "<option value='{$opt['no']}'>{$opt['title']}</option>"; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📄</span>
                                            <span>3. 종이종류</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="PN_type" id="PN_type" class="form-control-modern">
                                            <?php 
                                            if (empty($paper_options)) {
                                                echo "<option value=''>종이종류를 선택하세요</option>";
                                            } else {
                                                foreach ($paper_options as $opt) {
                                                    echo "<option value='{$opt['no']}'>{$opt['title']}</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>  
                              
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">📦</span>
                                            <span>4. 수량</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="MY_amount" id="MY_amount" class="form-control-modern">
                                            <option value="1000">1000부</option>
                                            <option value="2000">2000부</option>
                                            <option value="3000">3000부</option>
                                            <option value="4000">4000부</option>
                                            <option value="5000">5000부</option>
                                            <option value="기타">기타</option>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label-cell">
                                        <div class="icon-label">
                                            <span class="icon">✏️</span>
                                            <span>5. 주문방법</span>
                                        </div>
                                    </td>
                                    <td class="input-cell">
                                        <select name="ordertype" id="ordertype" class="form-control-modern">
                                            <!-- <option value="total">디자인+인쇄</option> -->
                                            <option value="print">인쇄만 의뢰</option>
                                            <!-- <option value="design">디자인만 의뢰</option> -->
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="text-align: center; margin: 1.5rem 0;">
                            <button type="button" onclick="calc_ok()" class="btn-calculate">
                                💰 실시간 가격 계산하기
                            </button>
                        </div>

                        <!-- Hidden Fields for price_cal.php -->
                        <input type="hidden" name="Price">
                        <input type="hidden" name="DS_Price">
                        <input type="hidden" name="Order_Price">
                        <input type="hidden" name="PriceForm">
                        <input type="hidden" name="DS_PriceForm">
                        <input type="hidden" name="Order_PriceForm">
                        <input type="hidden" name="VAT_PriceForm">
                        <input type="hidden" name="Total_PriceForm">
                        <input type="hidden" name="StyleForm">
                        <input type="hidden" name="SectionForm">
                        <input type="hidden" name="QuantityForm">
                        <input type="hidden" name="DesignForm">
                        <input type="hidden" name="POtype" value="1">
                        <input type="hidden" name="OnunloadChick" value="on">
                        <input type='hidden' name='Turi' value='<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>'>
                        <input type='hidden' name='Ty' value='<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>'>
                        <input type='hidden' name='Tmd' value='<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>'>
                        <input type='hidden' name='Tip' value='<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>'>
                        <input type='hidden' name='Ttime' value='<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>'>
                        <input type="hidden" name="ImgFolder" value="<?php echo htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type='hidden' name='OrderSytle' value='카다록'>
                        <input type='hidden' name='page' value='<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>'>

                    </form>
                </div>
                
                <!-- 가격 계산 결과 -->
                <div id="priceSection" class="price-result" style="display: none;">
                    <h3>💎 견적 결과</h3>
                    
                    <!-- 선택한 옵션 요약 -->
                    <div id="selectedOptions" class="selected-options">
                        <h4>📋 선택한 옵션</h4>
                        <div class="option-summary">
                            <div class="option-item">
                                <span class="option-label">🎨 구분:</span>
                                <span id="selectedColor" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📏 규격:</span>
                                <span id="selectedPaperType" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📄 종이종류:</span>
                                <span id="selectedPaperSize" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">📦 수량:</span>
                                <span id="selectedQuantity" class="option-value">-</span>
                            </div>
                            <div class="option-item">
                                <span class="option-label">✏️ 주문방법:</span>
                                <span id="selectedDesign" class="option-value">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="price-display">
                        <div class="price-amount" id="priceAmount">0원</div>
                        <div style="color: #495057;">부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700; color: #495057;">0원</span></div>
                    </div>
                    
                    <?php
                    // 카다록용 업로드 컴포넌트 설정
                    $uploadComponent = new FileUploadComponent([
                        'product_type' => 'cadarok',
                        'max_file_size' => 25 * 1024 * 1024, // 25MB
                        'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/zip'],
                        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip'],
                        'multiple' => true,
                        'drag_drop' => true,
                        'show_progress' => true,
                        'auto_upload' => true,
                        'delete_enabled' => true,
                        'custom_messages' => [
                            'title' => '카다록 디자인 파일 업로드',
                            'drop_text' => '카다록 디자인 완성파일이나 제작 관련 파일을 여기로 드래그하거나 클릭하여 선택하세요',
                            'format_text' => '지원 형식: JPG, PNG, PDF, ZIP (최대 25MB)'
                        ]
                    ]);
                    
                    // 컴포넌트 렌더링
                    echo $uploadComponent->render();
                    ?>
                    
                    <div class="action-buttons">
                        <button onclick="CheckTotal('OrderOne');" class="btn-action btn-primary">
                            🛒 주문하기
                        </button>
                        <a href="/MlangPrintAuto/shop/cart.php" class="btn-action btn-secondary">
                            🛒 장바구니
                        </a>
                    </div>
                </div>
                        <select size="3" style="width:100%; height:80px;" name="parentList" multiple class="form-control-modern"></select>
                        <div style="margin-top: .5rem;">
                            <input type="button" onClick="javascript:small_window('<?php echo $MultyUploadDir; ?>/FileUp.php?Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>&Mode=tt');" value="파일올리기" class="btn-action btn-primary" style="width: auto; padding: 8px 15px; font-size: 0.9rem;">
                            <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value="삭제" class="btn-action btn-secondary" style="width: auto; padding: 8px 15px; font-size: 0.9rem;">
                        </div>
                    </div>
                </div>

                <!-- 기타 요청 사항 섹션 -->
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <h2 class="card-title">📝 기타 요청 사항</h2>
                        <p class="card-subtitle">특별히 요청할 사항이 있다면 기재해주세요</p>
                    </div>
                    <div style="padding: 1.5rem;">
                        <textarea name="textarea" rows="5" class="form-control-modern"></textarea>
                    </div>
                </div>

            </div>
        </div> <!-- main-content-wrapper 끝 -->   
     
        <!-- 로그인 모달 -->
        <div id="loginModal" class="login-modal">
            <div class="login-modal-content">
                <div class="login-modal-header">
                    <h2>🔐 로그인 / 회원가입</h2>
                    <span class="close-modal" onclick="hideLoginModal()">&times;</span>
                </div>
                <div class="login-modal-body">
                    <?php if (!empty($login_message)): ?>
                    <div class="login-message <?php echo (strpos($login_message, '성공') !== false || strpos($login_message, '완료') !== false) ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($login_message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="login-tabs">
                        <button class="login-tab active" onclick="showLoginTab()">로그인</button>
                        <button class="login-tab" onclick="showRegisterTab()">회원가입</button>
                    </div>
                    
                    <!-- 로그인 폼 -->
                    <form id="loginForm" class="login-form active" method="post">
                        <div class="form-group">
                            <label for="username">아이디</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">비밀번호</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login_action" class="form-submit">로그인</button>
                    </form>
                    
                    <!-- 회원가입 폼 -->
                    <form id="registerForm" class="login-form" method="post">
                        <div class="form-group">
                            <label for="reg_username">아이디 *</label>
                            <input type="text" id="reg_username" name="reg_username" required>
                        </div>
                        <div class="form-group">
                            <label for="reg_password">비밀번호 * (6자 이상)</label>
                            <input type="password" id="reg_password" name="reg_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="reg_confirm_password">비밀번호 확인 *</label>
                            <input type="password" id="reg_confirm_password" name="reg_confirm_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="reg_name">이름 *</label>
                            <input type="text" id="reg_name" name="reg_name" required>
                        </div>
                        <div class="form-group">
                            <label for="reg_email">이메일</label>
                            <input type="email" id="reg_email" name="reg_email">
                        </div>
                        <div class="form-group">
                            <label for="reg_phone">전화번호</label>
                            <input type="tel" id="reg_phone" name="reg_phone">
                        </div>
                        <button type="submit" name="register_action" class="form-submit">회원가입</button>
                    </form>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; color: #6c757d;">
                        <strong>테스트 계정:</strong><br>
                        아이디: admin<br>
                        비밀번호: admin123
                    </div>
                </div>
            </div>
        </div>

        <?php
        // 공통 푸터 포함
        include "../../includes/footer.php";
        ?>
    </div> <!-- page-wrapper 끝 -->    

    <script>
    // 로그인 모달 관련 함수들
    function showLoginModal() {
        document.getElementById('loginModal').style.display = 'block';
        document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
    }
    
    function hideLoginModal() {
        document.getElementById('loginModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // 배경 스크롤 복원
    }
    
    function showLoginTab() {
        document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
        
        event.target.classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    }
    
    function showRegisterTab() {
        document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
        
        event.target.classList.add('active');
        document.getElementById('registerForm').classList.add('active');
    }
    
    // 모달 외부 클릭 시 닫기
    window.onclick = function(event) {
        const modal = document.getElementById('loginModal');
        if (event.target == modal) {
            hideLoginModal();
        }
    }
    
    // ESC 키로 모달 닫기
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            hideLoginModal();
        }
    });
    
    // 회원가입 폼 비밀번호 확인 검증
    document.getElementById('reg_confirm_password').addEventListener('input', function() {
        const password = document.getElementById('reg_password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.style.borderColor = '#e74c3c';
            this.setCustomValidity('비밀번호가 일치하지 않습니다.');
        } else {
            this.style.borderColor = '#27ae60';
            this.setCustomValidity('');
        }
    });
    
    // 로그인 메시지가 있으면 모달 자동 표시
    <?php if (!empty($login_message)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showLoginModal();
        <?php if (strpos($login_message, '성공') !== false): ?>
        setTimeout(hideLoginModal, 2000); // 로그인 성공 시 2초 후 자동 닫기
        <?php endif; ?>
    });
    <?php endif; ?>
    
    // 선택한 옵션 요약을 초기화하는 함수
    function resetSelectedOptions() {
        document.getElementById('selectedColor').textContent = '-';
        document.getElementById('selectedPaperType').textContent = '-';
        document.getElementById('selectedPaperSize').textContent = '-';
        document.getElementById('selectedQuantity').textContent = '-';
        document.getElementById('selectedDesign').textContent = '-';
        
        // 가격 섹션 숨기기
        document.getElementById('priceSection').style.display = 'none';
    }
    
    // 선택한 옵션들을 업데이트하는 함수
    function updateSelectedOptions(formData) {
        const form = document.forms['choiceForm']; // Use choiceForm
        
        // 각 select 요소에서 선택된 옵션의 텍스트 가져오기
        const colorSelect = form.querySelector('select[name="MY_type"]');
        const paperTypeSelect = form.querySelector('select[name="MY_Fsd"]');
        const paperSizeSelect = form.querySelector('select[name="PN_type"]');
        const quantitySelect = form.querySelector('select[name="MY_amount"]');
        const ordertypeSelect = form.querySelector('select[name="ordertype"]');
        
        // 선택된 옵션의 텍스트 업데이트
        document.getElementById('selectedColor').textContent = 
            colorSelect.options[colorSelect.selectedIndex].text;
        document.getElementById('selectedPaperType').textContent = 
            paperTypeSelect.options[paperTypeSelect.selectedIndex].text;
        document.getElementById('selectedPaperSize').textContent = 
            paperSizeSelect.options[paperSizeSelect.selectedIndex].text;
        document.getElementById('selectedQuantity').textContent = 
            quantitySelect.options[quantitySelect.selectedIndex].text;
        document.getElementById('selectedDesign').textContent = 
            ordertypeSelect.options[ordertypeSelect.selectedIndex].text;
    }
    
    // 카다록 기존 계산 로직 (iframe 방식 유지)
    function CheckTotal(mode) {
      var f = document.forms['choiceForm'];
      
      if (f.Total_PriceForm.value == "" || f.Total_PriceForm.value == "0") {
        alert("가격 정보가 없습니다. 옵션을 다시 선택하여 가격을 계산해주세요.");
        return false;
      }
      
      f.action = "/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=" + mode;
      f.submit();
    }

    function calc() {
      var asd = document.forms["choiceForm"];
      cal.document.location.href = 'price_cal.php?MY_type=' + asd.MY_type.value + '&PN_type=' + asd.PN_type.value + '&MY_Fsd=' + asd.MY_Fsd.value + '&MY_amount=' + asd.MY_amount.value + '&ordertype=' + asd.ordertype.value;
    }

    function calc_ok() {
      var form = document.forms["choiceForm"];
      
      // AJAX로 가격 계산 요청
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          try {
            var response = JSON.parse(xhr.responseText);
            
            // 폼의 hidden 필드들 업데이트
            form.Price.value = response.PriceForm;
            form.DS_Price.value = response.DS_PriceForm;
            form.Order_Price.value = response.Order_PriceForm;
            form.PriceForm.value = response.PriceForm;
            form.DS_PriceForm.value = response.DS_PriceForm;
            form.Order_PriceForm.value = response.Order_PriceForm;
            form.VAT_PriceForm.value = response.VAT_PriceForm;
            form.Total_PriceForm.value = response.Total_PriceForm;
            form.StyleForm.value = response.StyleForm;
            form.SectionForm.value = response.SectionForm;
            form.QuantityForm.value = response.QuantityForm;
            form.DesignForm.value = response.DesignForm;
            
            // 화면에 가격 표시
            document.getElementById('priceAmount').textContent = 
              response.PriceForm ? parseInt(response.PriceForm).toLocaleString() + '원' : '0원';
            document.getElementById('priceVat').textContent = 
              response.Total_PriceForm ? parseInt(response.Total_PriceForm).toLocaleString() + '원' : '0원';
            
            // 선택된 옵션 요약 업데이트
            updateSelectedOptions();
            
            // 가격 섹션 표시
            document.getElementById('priceSection').style.display = 'block';
            document.getElementById('priceSection').scrollIntoView({ behavior: 'smooth' });
            
          } catch (e) {
            console.error("가격 계산 응답 파싱 오류:", e);
            console.log("서버 응답:", xhr.responseText);
          }
        }
      };
      
      // POST 방식으로 데이터 전송
      var formData = new FormData();
      formData.append('MY_type', form.MY_type.value);
      formData.append('PN_type', form.PN_type.value);
      formData.append('MY_Fsd', form.MY_Fsd.value);
      formData.append('MY_amount', form.MY_amount.value);
      formData.append('ordertype', form.ordertype.value);
      
      xhr.open("POST", "price_cal.php", true);
      xhr.send(formData);
    }

    function calc_re() {
      setTimeout(function () {
        calc_ok();
      }, 100);
    }

    // 구분 선택 시 하위 항목들 업데이트 및 가격 계산 (cadarok 기존 로직)
    function change_Field(val) {
      console.log("change_Field 호출됨, val:", val);
      var f = document.forms['choiceForm'];

      // 규격 옵션 업데이트
      var MY_Fsd = document.getElementById('MY_Fsd');
      MY_Fsd.options.length = 0;

      var xhr1 = new XMLHttpRequest();
      xhr1.onreadystatechange = function () {
        if (xhr1.readyState === 4 && xhr1.status === 200) {
          console.log("규격 서버 응답:", xhr1.responseText);
          try {
            var options = JSON.parse(xhr1.responseText);
            console.log("규격 옵션 개수:", options.length);
            for (var i = 0; i < options.length; i++) {
              MY_Fsd.options[MY_Fsd.options.length] = new Option(options[i].title, options[i].no);
            }
            // 첫 번째 규격을 자동 선택하고 종이종류 업데이트
            if (options.length > 0) {
              MY_Fsd.selectedIndex = 0;
              console.log("첫 번째 규격 선택됨:", options[0].title, "no:", options[0].no);
              updatePaperType(options[0].no);
            }
          } catch (e) {
            console.error("규격 옵션 파싱 오류:", e);
            console.log("서버 응답:", xhr1.responseText);
          }
        }
      };
      var url = "get_sizes.php?CV_no=" + val;
      console.log("규격 요청 URL:", url);
      xhr1.open("GET", url, true);
      xhr1.send();
    }

    // 종이종류 옵션 업데이트 (cadarok 기존 로직)
    function updatePaperType(val) {
      console.log("updatePaperType 호출됨, val:", val);
      var f = document.forms['choiceForm'];
      var PN_type = document.getElementById('PN_type');
      PN_type.options.length = 0;

      var xhr2 = new XMLHttpRequest();
      xhr2.onreadystatechange = function () {
        if (xhr2.readyState === 4 && xhr2.status === 200) {
          console.log("종이종류 서버 응답:", xhr2.responseText);
          try {
            var options = JSON.parse(xhr2.responseText);
            console.log("종이종류 옵션 개수:", options.length);
            for (var i = 0; i < options.length; i++) {
              PN_type.options[PN_type.options.length] = new Option(options[i].title, options[i].no);
            }
            // 첫 번째 종이종류를 자동 선택
            if (options.length > 0) {
              PN_type.selectedIndex = 0;
              console.log("첫 번째 종이종류 선택됨:", options[0].title);
            } else {
              console.log("종이종류 옵션이 없습니다.");
            }
          } catch (e) {
            console.error("종이종류 옵션 파싱 오류:", e);
            console.log("서버 응답:", xhr2.responseText);
          }
        }
      };
      var url = "get_paper_types.php?CV_no=" + val;
      console.log("종이종류 요청 URL:", url);
      xhr2.open("GET", url, true);
      xhr2.send();
    }

    // 파일첨부 관련 함수들 (cadarok 기존 로직)
    function small_window(myurl) {
      var newWindow;
      var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
      newWindow = window.open("<?php echo $MultyUploadDir; ?>/" + myurl, "Add_from_Src_to_Dest", props);
    }

    function addToParentList(sourceList) {
      destinationList = window.document.forms[0].parentList;
      for (var count = destinationList.options.length - 1; count >= 0; count--) {
        destinationList.options[count] = null;
      }
      for (var i = 0; i < sourceList.options.length; i++) {
        if (sourceList.options[i] != null)
          destinationList.options[i] = new Option(sourceList.options[i].text, sourceList.options[i].value);
      }
    }

    function deleteSelectedItemsFromList(sourceList) {
      var maxCnt = sourceList.options.length;
      for (var i = maxCnt - 1; i >= 0; i--) {
        if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
          window.open('<?php echo $MultyUploadDir; ?>/FileDelete.php?FileDelete=ok&Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>&FileName=' + sourceList.options[i].text, '', 'scrollbars=no,resizable=no,width=100,height=100,top=2000,left=2000');
          sourceList.options[i] = null;
        }
      }
    }

    function MlangWinExit() {
      if (document.forms['choiceForm'].OnunloadChick.value == "on") {
        window.open("<?php echo $MultyUploadDir; ?>/FileDelete.php?DirDelete=ok&Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>", "MlangWinExitsdf", "width=100,height=100,top=2000,left=2000,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes");
      }
    }
    window.onunload = MlangWinExit;

    // 페이지 로드 시 초기화 및 이벤트 리스너 설정
    document.addEventListener('DOMContentLoaded', function() {
        // 초기 옵션 로드 (가격 계산은 버튼 클릭 시에만)
        var initialType = document.getElementById('MY_type').value;
        change_Field(initialType);

        // 로그인 모달 관련 함수들
        function showLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        window.showLoginModal = showLoginModal; // 전역으로 노출
        
        function hideLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        window.hideLoginModal = hideLoginModal; // 전역으로 노출
        
        function showLoginTab() {
            document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById('loginForm').classList.add('active');
        }
        window.showLoginTab = showLoginTab; // 전역으로 노출
        
        function showRegisterTab() {
            document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById('registerForm').classList.add('active');
        }
        window.showRegisterTab = showRegisterTab; // 전역으로 노출
        
        window.onclick = function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target == modal) {
                hideLoginModal();
            }
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLoginModal();
            }
        });
        
        document.getElementById('reg_confirm_password').addEventListener('input', function() {
            const password = document.getElementById('reg_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.style.borderColor = '#e74c3c';
                this.setCustomValidity('비밀번호가 일치하지 않습니다.');
            } else {
                this.style.borderColor = '#27ae60';
                this.setCustomValidity('');
            }
        });
        
        <?php if (!empty($login_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showLoginModal();
            <?php if (strpos($login_message, '성공') !== false): ?>
            setTimeout(hideLoginModal, 2000);
            <?php endif; ?>
        });
        <?php endif; ?>

        // 입력값 변경 시 실시간 유효성 검사
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('change', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '#27ae60';
                } else {
                    this.style.borderColor = '#e74c3c';
                }
            });
        });

    });
    </script>



</body>
</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>