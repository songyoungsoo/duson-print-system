<?php
session_start(); 
$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

// 데이터베이스 연결
include "$HomeDir/db.php";

// 기본 페이지 설정
$page = $_GET['page'] ?? "cadarok";

$Ttable = $page;
include "../ConDb.php";
include "inc.php";
$GGTABLE = "MlangPrintAuto_transactionCate";

$log_url = str_replace("/", "_", $_SERVER['PHP_SELF']);
$log_y = date("Y");
$log_md = date("md");
$log_ip = $_SERVER['REMOTE_ADDR'];
$log_time = time();

// 전역 $db 변수 확인
global $db;
if (!$db) {
  die("Database connection error: " . mysqli_connect_error());
}

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
            if (!$db) {
                $login_message = '데이터베이스 연결에 실패했습니다.';
            } else {
                $query = "SELECT id, username, password, name FROM users WHERE username = ?";
                $stmt = mysqli_prepare($db, $query);  
              
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
                    $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
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
        } elseif (!$db) {
            $login_message = '데이터베이스 연결에 실패했습니다.';
        } else {
            $check_query = "SELECT id FROM users WHERE username = ?";
            $stmt = mysqli_prepare($db, $check_query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $login_message = '이미 존재하는 아이디입니다.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($db, $insert_query);
                    
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $name, $email, $phone);
                        
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $login_message = '회원가입이 완료되었습니다. 로그인해주세요.';
                        } else {
                            $login_message = '회원가입 중 오류가 발생했습니다: ' . mysqli_stmt_error($insert_stmt);
                        }
                        mysqli_stmt_close($insert_stmt);
                    } else {
                        $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                $login_message = '데이터베이스 오류가 발생했습니다: ' . mysqli_error($db);
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
            padding: 1.5rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1.5rem;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .form-column .form-group:not(:last-child) {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: .5rem;
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
        
        .price-display {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }

        .price-display .price-item {
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .price-display .price-item .label {
            font-weight: 600;
            color: #495057;
        }

        .price-display .price-item .value {
            font-weight: 700;
            color: #2c3e50;
        }

        .price-display .total {
            border-top: 2px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.5rem;
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
            .header-content, .contact-info, .nav-links {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script type="text/javascript">
        // PHP 변수를 JavaScript로 전달
        var phpVars = {
            MultyUploadDir: "<?php echo $MultyUploadDir; ?>",
            log_url: "<?php echo $log_url; ?>",
            log_y: "<?php echo $log_y; ?>",
            log_md: "<?php echo $log_md; ?>",
            log_ip: "<?php echo $log_ip; ?>",
            log_time: "<?php echo $log_time; ?>",
            page: "<?php echo $page; ?>"
        };

        // 파일첨부 관련 함수들
        function small_window(url) {
          window.open(url, 'FileUpload', 'width=500,height=400,scrollbars=yes,resizable=yes');
        }

        function deleteSelectedItemsFromList(selectObj) {
          var i;
          for (i = selectObj.options.length - 1; i >= 0; i--) {
            if (selectObj.options[i].selected) {
              selectObj.options[i] = null;
            }
          }
        }

        function addToParentList(srcList) {
          var parentList = document.choiceForm.parentList;
          for (var i = 0; i < srcList.options.length; i++) {
            if (srcList.options[i] != null) {
              parentList.options[parentList.options.length] = new Option(srcList.options[i].text, srcList.options[i].value);
            }
          }
        }

        function CheckTotal(mode) {
          var f = document.choiceForm;
          
          if (!f.MY_type.value || !f.PN_type.value || !f.MY_Fsd.value || !f.MY_amount.value) {
            alert("모든 옵션을 선택해주세요.");
            return false;
          }
          
          if (f.Order_PriceForm.value == "" || f.Order_PriceForm.value == "0") {
            alert("가격 정보가 없습니다. 옵션을 다시 선택하여 가격을 계산해주세요.");
            return false;
          }
          
          f.action = "/MlangOrder_PrintAuto/OnlineOrder.php?SubmitMode=" + mode;
          f.submit();
        }

        // 카다록 가격 계산 함수
        function calc_ok() {
          var form = document.forms["choiceForm"];
          
          if (!form.MY_type.value || !form.PN_type.value || !form.MY_Fsd.value || !form.MY_amount.value) {
            console.log("가격 계산에 필요한 값들이 비어있음");
            return;
          }
          
          var params = {
            MY_type: form.MY_type.value,
            PN_type: form.PN_type.options[form.PN_type.selectedIndex].text,
            MY_Fsd: form.MY_Fsd.options[form.MY_Fsd.selectedIndex].text,
            MY_amount: form.MY_amount.value,
            ordertype: form.ordertype.value
          };
          
          var xhr = new XMLHttpRequest();
          xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                  var response = JSON.parse(xhr.responseText);
                  if (response.success && response.data) {
                    document.getElementById('print_price').textContent = response.data.Price + "원";
                    document.getElementById('design_price').textContent = response.data.DS_Price + "원";
                    document.getElementById('total_price').textContent = response.data.Order_Price + "원";

                    // Update hidden fields
                    form.PriceForm.value = response.data.PriceForm;
                    form.DS_PriceForm.value = response.data.DS_PriceForm;
                    form.Order_PriceForm.value = response.data.Order_PriceForm;
                    form.VAT_PriceForm.value = response.data.VAT_PriceForm;
                    form.Total_PriceForm.value = response.data.Total_PriceForm;
                    form.StyleForm.value = response.data.StyleForm;
                    form.SectionForm.value = response.data.SectionForm;
                    form.QuantityForm.value = response.data.QuantityForm;
                    form.DesignForm.value = response.data.DesignForm;

                  } else {
                    console.error("가격 계산 실패:", response.message || "알 수 없는 오류");
                    alert("가격 계산 실패: " + (response.message || "알 수 없는 오류"));
                  }
                } catch (e) {
                  console.error("가격 계산 응답 파싱 오류:", e, xhr.responseText);
                }
            } else if (xhr.readyState === 4) {
                console.error("가격 계산 요청 실패:", xhr.status, xhr.statusText);
            }
          };
          xhr.open("POST", "price_cal_ajax.php", true);
          xhr.setRequestHeader("Content-Type", "application/json");
          xhr.send(JSON.stringify(params));
        }

        function calc_re() {
          setTimeout(calc_ok, 100);
        }

        function change_Field(val) {
            var f = document.choiceForm;
            var MY_Fsd = f.MY_Fsd;
            var PN_type = f.PN_type;

            // 규격 옵션 업데이트
            MY_Fsd.options.length = 0;
            MY_Fsd.options[0] = new Option("로딩중...", "");

            var xhr1 = new XMLHttpRequest();
            xhr1.onreadystatechange = function () {
                if (xhr1.readyState === 4 && xhr1.status === 200) {
                    try {
                        var options = JSON.parse(xhr1.responseText);
                        MY_Fsd.options.length = 0; // Clear loading option
                        for (var i = 0; i < options.length; i++) {
                            MY_Fsd.options[i] = new Option(options[i].title, options[i].no);
                        }
                        // 종이종류 옵션 업데이트
                        PN_type.options.length = 0;
                        PN_type.options[0] = new Option("로딩중...", "");

                        var xhr2 = new XMLHttpRequest();
                        xhr2.onreadystatechange = function () {
                            if (xhr2.readyState === 4 && xhr2.status === 200) {
                                try {
                                    var options2 = JSON.parse(xhr2.responseText);
                                    PN_type.options.length = 0; // Clear loading option
                                    for (var i = 0; i < options2.length; i++) {
                                        PN_type.options[i] = new Option(options2[i].title, options2[i].no);
                                    }
                                    calc_ok();
                                } catch (e) {
                                    console.error("카다록 종이종류 옵션 파싱 오류:", e, xhr2.responseText);
                                    PN_type.options.length = 0;
                                    PN_type.options[0] = new Option("선택하세요", "");
                                }
                            } else if (xhr2.readyState === 4) {
                                console.error("카다록 종이종류 AJAX 요청 실패:", xhr2.status, xhr2.statusText);
                                PN_type.options.length = 0;
                                PN_type.options[0] = new Option("선택하세요", "");
                            }
                        };
                        xhr2.open("GET", "get_cadarok_papers.php?category_type=" + val, true);
                        xhr2.send();

                    } catch (e) {
                        console.error("카다록 규격 옵션 파싱 오류:", e, xhr1.responseText);
                        MY_Fsd.options.length = 0;
                        MY_Fsd.options[0] = new Option("선택하세요", "");
                    }
                }
            };
            xhr1.open("GET", "get_cadarok_sizes.php?category_type=" + val, true);
            xhr1.send();
        }

        window.onload = function() {
            var form = document.forms["choiceForm"];
            if (form && form.MY_type && form.MY_type.value) {
                change_Field(form.MY_type.value);
            }
        };
    </script>
</head>

<body>
    <div class="page-wrapper">
        <div class="main-content-wrapper">
            <!-- 상단 헤더 -->
            <div class="top-header">
                <div class="header-content">
                    <div class="logo-section">
                        <div class="logo-icon">📖</div>
                        <div class="company-info">
                            <h1>두손기획인쇄</h1>
                            <p>프리미엄 카다록 주문</p>
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
                <form name='choiceForm' method='post'>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">📝 카다록 주문 옵션 선택</h2>
                            <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                        </div>
                        <div class="form-grid">
                            <div class="form-column">
                                <div class="form-group">
                                    <label for="MY_type">구분</label>
                                    <select id="MY_type" class="form-control-modern" name='MY_type'>
                                      <?php
                                      $Cate_result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC");
                                      while ($Cate_row = mysqli_fetch_array($Cate_result)) {
                                          echo "<option value='" . htmlspecialchars($Cate_row['no'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($Cate_row['title'], ENT_QUOTES, 'UTF-8') . "</option>";
                                      }
                                      ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="MY_Fsd">규격</label>
                                    <select id="MY_Fsd" class="form-control-modern" name="MY_Fsd" onchange="calc_re();">
                                      <!-- 옵션은 JS로 채워집니다 -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="PN_type">종이종류</label>
                                    <select id="PN_type" class="form-control-modern" name="PN_type" onchange="calc_re();">
                                      <!-- 옵션은 JS로 채워집니다 -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="MY_amount">수량</label>
                                    <select id="MY_amount" class="form-control-modern" name="MY_amount" onchange="calc_ok();">
                                      <option value='1000'>1000부</option>
                                      <option value='2000'>2000부</option>
                                      <option value='3000'>3000부</option>
                                      <option value='4000'>4000부</option>
                                      <option value='5000'>5000부</option>
                                      <option value='기타'>기타</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ordertype">주문방법</label>
                                    <select id="ordertype" class="form-control-modern" name="ordertype" onchange="calc_ok();">
                                      <option value='print'>인쇄만 의뢰</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="price-display">
                                    <div class="price-item">
                                        <span class="label">인쇄비</span>
                                        <span class="value" id="print_price">0원</span>
                                    </div>
                                    <div class="price-item">
                                        <span class="label">디자인비</span>
                                        <span class="value" id="design_price">0원</span>
                                    </div>
                                    <div class="price-item total">
                                        <span class="label">총 금액 (VAT 별도)</span>
                                        <span class="value" id="total_price">0원</span>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 1.5rem;">
                                    <label>파일첨부</label>
                                    <select size="3" style="width:100%; height:80px;" name="parentList" multiple></select>
                                    <div style="margin-top: .5rem;">
                                        <input type="button" onClick="javascript:small_window('../../PHPClass/MultyUpload/FileUp.php?Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>&Mode=tt');" value="파일올리기">
                                        <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value="삭제">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="textarea">기타사항</label>
                                    <textarea id="textarea" name="textarea" class="form-control-modern" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                     <input type="button" onClick="CheckTotal('OrderOne');" value="주문하기" class="form-submit">
                                </div>
                                <div style="text-align: center; margin-top: 1.5rem;">
                                    <button type="button" onclick="calc_ok()" class="btn-calculate">
                                        💰 실시간 가격 계산하기
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden Fields -->
                    <input type="hidden" name="OnunloadChick" value="on">
                    <input type='hidden' name='Turi' value='<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ty' value='<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tmd' value='<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tip' value='<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ttime' value='<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type="hidden" name="ImgFolder" value="<?php echo htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type='hidden' name='OrderSytle' value='카다록'>
                    <input type='hidden' name='StyleForm'>
                    <input type='hidden' name='SectionForm'>
                    <input type='hidden' name='QuantityForm'>
                    <input type='hidden' name='DesignForm'>
                    <input type='hidden' name='PriceForm'>
                    <input type='hidden' name='DS_PriceForm'>
                    <input type='hidden' name='Order_PriceForm'>
                    <input type='hidden' name='VAT_PriceForm'>
                    <input type='hidden' name='Total_PriceForm'>
                    <input type='hidden' name='page' value='<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>'>  
                </form>
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
                    <h3 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🖨️ 두손기획인쇄</h3>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📍 주소: 서울 영등포구 영등포로 36길9 송호빌딩 1층</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📞 전화: 1688-2384</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📠 팩스: 02-2632-1829</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📧 이메일: dsp1830@naver.com</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🎯 주요 서비스</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📄 전단지 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🏷️ 스티커 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📇 명함 인쇄</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📖 카다록 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🎨 포스터 인쇄</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">⏰ 운영 안내</h4>
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
    </script>
</body>
</html>
<?php
if ($db) {
    mysqli_close($db);
}
?>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden Fields -->
                    <input type="hidden" name="OnunloadChick" value="on">
                    <input type='hidden' name='Turi' value='<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ty' value='<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tmd' value='<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Tip' value='<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type='hidden' name='Ttime' value='<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>'>
                    <input type="hidden" name="ImgFolder" value="<?php echo htmlspecialchars($log_url . "/" . $log_y . "/" . $log_md . "/" . $log_ip . "/" . $log_time, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type='hidden' name='OrderSytle' value='카다록'>
                    <input type='hidden' name='StyleForm'>
                    <input type='hidden' name='SectionForm'>
                    <input type='hidden' name='QuantityForm'>
                    <input type='hidden' name='DesignForm'>
                    <input type='hidden' name='PriceForm'>
                    <input type='hidden' name='DS_PriceForm'>
                    <input type='hidden' name='Order_PriceForm'>
                    <input type='hidden' name='VAT_PriceForm'>
                    <input type='hidden' name='Total_PriceForm'>
                    <input type='hidden' name='page' value='<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>'>  
                </form>
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
                    <h3 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🖨️ 두손기획인쇄</h3>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📍 주소: 서울 영등포구 영등포로 36길9 송호빌딩 1층</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📞 전화: 1688-2384</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📠 팩스: 02-2632-1829</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📧 이메일: dsp1830@naver.com</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">🎯 주요 서비스</h4>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📄 전단지 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🏷️ 스티커 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📇 명함 인쇄</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">📖 카다록 제작</p>
                    <p style="line-height: 1.8; color: #bdc3c7; font-size: 1rem; margin: 0.5rem 0;">🎨 포스터 인쇄</p>
                </div>

                <div>
                    <h4 style="color: #3498db; font-size: 1.3rem; font-weight: 700;">⏰ 운영 안내</h4>
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
    </script>
</body>
</html>
<?php
if ($db) {
    mysqli_close($db);
}
?>