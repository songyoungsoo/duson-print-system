<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결 설정
$host = "localhost";
$user = "duson1830";
$dataname = "duson1830";
$password = "du1830";

$connect = mysqli_connect($host, $user, $password, $dataname);
if (!$connect) {
    die("데이터베이스 연결에 실패했습니다: " . mysqli_connect_error());
}

mysqli_set_charset($connect, "utf8"); 

// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 드롭다운 옵션을 가져오는 함수들
function getOptionsByBigNo($connect, $GGTABLE, $page, $bigNo) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$bigNo' ORDER BY no ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'no' => $row['no'],
                'title' => $row['title']
            ];
        }
    }
    return $options;
}

// 초기 옵션 데이터 가져오기
$sizeOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '0');
$pageOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '1'); 
$coverOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '2');
$innerOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '3');
$bindingOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '4');
$quantityOptions = getOptionsByBigNo($connect, $GGTABLE, $page, '5');


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
            } else {
                $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($connect);
            }
        }
    } elseif (isset($_POST['logout_action'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📖 두손기획인쇄 - 프리미엄 카다록 주문</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }
        
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content-wrapper {
            flex: 1;
            padding-bottom: 2rem;
        }
        
        html, body {
            scroll-behavior: smooth;
        }
        
        input, select, textarea, button {
            position: relative;
            z-index: 1;
        }
        
        .top-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }  
      
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .company-info h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .company-info p {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .contact-info {
            display: flex;
            gap: 30px;
        }
        
        .contact-card {
            text-align: right;
            padding: 15px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .contact-card .label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .contact-card .value {
            font-weight: 700;
            font-size: 1.2rem;
            color: #3498db;
        }
        
        .nav-menu {
            background: white;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 0;
            overflow-x: auto;
        }
        
        .nav-link {
            padding: 18px 25px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link:hover {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.05);
        }
        
        .nav-link.active {
            color: #3498db;
            border-bottom-color: #3498db;
            background: rgba(52, 152, 219, 0.1);
            font-weight: 700;
        } 
       
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px 40px 20px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.2rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }
        
        .card-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .order-form-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-form-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }
        
        .order-form-table tr:last-child td {
            border-bottom: none;
        }
        
        .label-cell {
            width: 220px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 700;
            font-size: 1rem;
            color: #2c3e50;
            border-right: 4px solid #3498db;
        }
        
        .input-cell {
            background: white;
        }
        
        .icon-label {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .icon-label .icon {
            font-size: 1.5rem;
        } 
       
        .form-control-modern {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }
        
        .help-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.4rem;
            display: block;
            font-weight: 500;
        }
        
        .btn-calculate {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-calculate:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
        }
        
        .price-result {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            margin: 1rem 0;
            display: none;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
        }
        
        .price-amount {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0.8rem 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
        }
        
        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }       
 
        .login-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3);
        }
        
        .user-info .value {
            color: #2ecc71 !important;
            font-weight: 700;
        }
        
        .login-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .login-modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .login-modal-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }
        
        .login-modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            transform: scale(1.1);
            opacity: 0.8;
        }   
     
        .login-modal-body {
            padding: 30px;
        }
        
        .login-tabs {
            display: flex;
            margin-bottom: 25px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8f9fa;
        }
        
        .login-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-tab.active {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .login-form {
            display: none;
        }
        
        .login-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }
        
        .form-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .form-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .login-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
        }
        
        .login-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .login-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .modern-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            margin-top: 2rem;
            border-top: 4px solid #3498db;
            position: relative;
            z-index: 1;
        }   
     
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-title {
                font-size: 2.2rem;
            }
            
            .label-cell {
                width: 200px;
                font-size: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .login-modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .login-modal-body {
                padding: 20px;
            }
            
            .login-btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
            
            .user-info .value {
                font-size: 1rem;
            }
            
            body {
                padding-bottom: 20px;
            }
            
            .container {
                padding: 10px 15px 30px 15px;
            }
            
            .modern-footer {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <div class="logo-icon">🖨️</div>
                        <div class="company-info">
                            <h1>두손기획인쇄</h1>
                            <p>기획에서 인쇄까지 원스톱으로 해결해 드립니다</p>
                        </div>
                    </div>
                    <div class="contact-info">
                        <div class="contact-card">
                            <div class="label">📞 고객센터</div>
                            <div class="value">1688-2384</div>
                        </div>
                        <div class="contact-card">
                            <div class="label">⏰ 운영시간</div>
                            <div class="value">평일 09:00-18:00</div>
                        </div>
                        <?php if ($is_logged_in): ?>
                        <div class="contact-card user-info">
                            <div class="label">👤 환영합니다</div>
                            <div class="value"><?php echo htmlspecialchars($user_name); ?>님</div>
                            <form method="post" style="margin-top: 10px;">
                                <button type="submit" name="logout_action" class="logout-btn">로그아웃</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="contact-card login-card">
                            <button onclick="showLoginModal()" class="login-btn">🔐 로그인</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> 
           <!-- 네비게이션 메뉴 -->
            <div class="nav-menu">
                <div class="nav-content">
                    <div class="nav-links">
                        <a href="/MlangPrintAuto/inserted/index.php" class="nav-link">📄 전단지</a>
                        <a href="/shop/view_modern.php" class="nav-link">🏷️ 스티커</a>
                        <a href="/MlangPrintAuto/cadarok/index.php" class="nav-link active">📖 카다록</a>
                        <a href="/MlangPrintAuto/NameCard/index.php" class="nav-link">📇 명함</a>
                        <a href="/MlangPrintAuto/MerchandiseBond/index.php" class="nav-link">🎫 상품권</a>
                        <a href="/MlangPrintAuto/envelope/index.php" class="nav-link">✉️ 봉투</a>
                        <a href="/MlangPrintAuto/LittlePrint/index.php" class="nav-link">🎨 포스터</a>
                        <a href="/shop/cart.php" class="nav-link cart">🛒 장바구니</a>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📝 카다록 주문 옵션 선택</h2>
                        <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                    </div>
                    
                    <form id="orderForm" method="post">
                        <input type="hidden" name="action" value="calculate">
                        
                        <table class="order-form-table">
                            <tbody>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📏</span><span>1. 규격</span></div></td>
                                    <td class="input-cell">
                                        <select name="size" class="form-control-modern" onchange="calculatePrice()">
                                            <?php foreach ($sizeOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📖</span><span>2. 페이지 수</span></div></td>
                                    <td class="input-cell">
                                        <select name="pages" class="form-control-modern" onchange="calculatePrice()">
                                             <?php foreach ($pageOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📄</span><span>3. 표지 용지</span></div></td>
                                    <td class="input-cell">
                                        <select name="cover_paper" class="form-control-modern" onchange="calculatePrice()">
                                            <?php foreach ($coverOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📑</span><span>4. 내지 용지</span></div></td>
                                    <td class="input-cell">
                                        <select name="inner_paper" class="form-control-modern" onchange="calculatePrice()">
                                            <?php foreach ($innerOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📚</span><span>5. 제본 방식</span></div></td>
                                    <td class="input-cell">
                                        <select name="binding" class="form-control-modern" onchange="calculatePrice()">
                                            <?php foreach ($bindingOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">📦</span><span>6. 수량</span></div></td>
                                    <td class="input-cell">
                                        <select name="quantity" class="form-control-modern" onchange="calculatePrice()">
                                            <?php foreach ($quantityOptions as $option): ?>
                                            <option value="<?php echo htmlspecialchars($option['no']); ?>"><?php echo htmlspecialchars($option['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><div class="icon-label"><span class="icon">✏️</span><span>7. 디자인(편집)</span></div></td>
                                    <td class="input-cell">
                                        <select name="design" class="form-control-modern" onchange="calculatePrice()">
                                            <option value="total">디자인+인쇄</option>
                                            <option value="print">인쇄만 의뢰</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="text-align: center; margin: 1.5rem 0;">
                            <button type="button" onclick="calculatePrice()" class="btn-calculate">
                                💰 실시간 가격 계산하기
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="priceSection" class="price-result">
                    <h3>💎 견적 결과</h3>
                    <div class="price-amount" id="priceAmount">0원</div>
                    <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
                    
                    <div class="action-buttons">
                        <button onclick="addToBasket()" class="btn-action btn-primary">
                            🛒 장바구니에 담기
                        </button>
                        <a href="/shop/cart.php" class="btn-action btn-secondary">
                            👀 장바구니 보기
                        </a>
                    </div>
                </div>
            </div>
        </div> 
     
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
                </div>
            </div>
        </div>

        <footer class="modern-footer">
            <div style="max-width: 1200px; margin: 0 auto; padding: 3rem 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem;">
                <div>
                    <h3 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">🖨️ 두손기획인쇄</h3>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📍 주소: 서울 영등포구 영등포로 36길9 송호빌딩 1층</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📞 전화: 1688-2384</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📠 팩스: 02-2632-1829</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📧 이메일: dsp1830@naver.com</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">🎯 주요 서비스</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📄 전단지 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🏷️ 스티커 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📇 명함 인쇄</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📖 카다록 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🎨 포스터 인쇄</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; margin-bottom: 1.5rem; font-weight: 700;">⏰ 운영 안내</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">평일: 09:00 - 18:00</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">토요일: 09:00 - 15:00</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">일요일/공휴일: 휴무</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">점심시간: 12:00 - 13:00</p>
                </div>
            </div>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding: 2rem 20px; text-align: center; background: rgba(0,0,0,0.2);">
                <p style="color: #bdc3c7; font-size: 0.95rem;">© 2024 두손기획인쇄. All rights reserved. | 제작: Mlang (010-8946-7038)</p>
            </div>
        </footer>
    </div> 

    <script>
    function showLoginModal() {
        document.getElementById('loginModal').style.display = 'block';
    }
    
    function hideLoginModal() {
        document.getElementById('loginModal').style.display = 'none';
    }
    
    function showLoginTab() {
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('registerForm').style.display = 'none';
        document.querySelector('.login-tab.active').classList.remove('active');
        event.target.classList.add('active');
    }
    
    function showRegisterTab() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
        document.querySelector('.login-tab.active').classList.remove('active');
        event.target.classList.add('active');
    }

    function calculatePrice() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        const params = new URLSearchParams();
        for (const pair of formData.entries()) {
            params.append(pair[0], pair[1]);
        }

        fetch('calculate_cadarok_price.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const priceData = data.data;
                document.getElementById('priceAmount').textContent = priceData.Order_Price + '원';
                document.getElementById('priceVat').textContent = Math.round(priceData.Total_PriceForm).toLocaleString() + '원';
                document.getElementById('priceSection').style.display = 'block';
                window.currentPriceData = priceData;
            } else {
                alert(data.error.message || '가격 계산에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('가격 계산 오류:', error);
            alert('가격 계산 중 오류가 발생했습니다.');
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        calculatePrice();
    });
    </script>

</body>
</html>
<?php
if ($connect) {
    mysqli_close($connect);
}
?>