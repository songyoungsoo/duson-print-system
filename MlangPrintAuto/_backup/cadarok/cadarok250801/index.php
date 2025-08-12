<?php
session_start();
$HomeDir = "../../";
$PageCode = "PrintAuto";
$MultyUploadDir = "../../PHPClass/MultyUpload";

include "$HomeDir/db.php";
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
    <title>📖 두손기획인쇄 - 프리미엄 카다록 주문 (리팩토링)</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cadarok.css">
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
                            <button class="login-btn">🔐 로그인</button>
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
                                    <select id="MY_Fsd" class="form-control-modern" name="MY_Fsd">
                                      <option value="">구분을 먼저 선택하세요</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="PN_type">종이종류</label>
                                    <select id="PN_type" class="form-control-modern" name="PN_type">
                                      <option value="">구분을 먼저 선택하세요</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="MY_amount">수량</label>
                                    <select id="MY_amount" class="form-control-modern" name="MY_amount">
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
                                    <select id="ordertype" class="form-control-modern" name="ordertype">
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
                                        <input type="button" value="파일올리기">
                                        <input type="button" value="삭제">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="textarea">기타사항</label>
                                    <textarea id="textarea" name="textarea" class="form-control-modern" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                     <input type="button" value="주문하기" class="form-submit">
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
                    <span class="close-modal">&times;</span>
                </div>
                <div class="login-modal-body">
                    <?php if (!empty($login_message)): ?>
                    <div class="login-message <?php echo (strpos($login_message, '성공') !== false || strpos($login_message, '완료') !== false) ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($login_message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="login-tabs">
                        <button class="login-tab active">로그인</button>
                        <button class="login-tab">회원가입</button>
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
        // Pass PHP variables to JavaScript
        window.phpVars = {
            MultyUploadDir: "<?php echo htmlspecialchars($MultyUploadDir, ENT_QUOTES, 'UTF-8'); ?>",
            log_url: "<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>",
            log_y: "<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>",
            log_md: "<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>",
            log_ip: "<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>",
            log_time: "<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>",
            page: "<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <script src="cadarok.js" defer></script>
</body>
</html>
<?php
if ($db) {
    mysqli_close($db);
}
?>