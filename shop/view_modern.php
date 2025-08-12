<?php 
session_start(); 
$session_id = session_id();
$HomeDir="../../";
include "../lib/func.php";
$connect = dbconn(); 

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
            // 데이터베이스 연결 확인
            if (!$connect) {
                $login_message = '데이터베이스 연결에 실패했습니다.';
            } else {
                // 로그인용 users 테이블 설정
                $setup_success = false;
                
                // 기존 users 테이블 구조 확인
                $table_exists = mysqli_query($connect, "SHOW TABLES LIKE 'users'");
                
                if (mysqli_num_rows($table_exists) > 0) {
                    // 테이블이 존재하면 필요한 컬럼들이 있는지 확인
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
                        // 기존 테이블을 백업하고 새로 생성
                        $backup_table = "users_backup_" . date('YmdHis');
                        mysqli_query($connect, "CREATE TABLE $backup_table AS SELECT * FROM users");
                        mysqli_query($connect, "DROP TABLE users");
                        
                        // 새 테이블 생성
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
                        // 필요한 컬럼들이 모두 있으면 추가 컬럼만 확인
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
                    // 테이블이 없으면 새로 생성
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
                
                // 테이블 설정이 성공한 경우에만 관리자 계정 생성
                if ($setup_success && empty($login_message)) {
                    // 테이블 구조 재확인
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
            
            // 로그인 확인 (테이블 구조가 올바른 경우에만)
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
            // 중복 확인
            $check_query = "SELECT id FROM users WHERE username = ?";
            $stmt = mysqli_prepare($connect, $check_query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $login_message = '이미 존재하는 아이디입니다.';
                } else {
                    // 회원가입
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
    <title>🏷️ 두손기획인쇄 - 프리미엄 스티커 주문</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/style250801.css">
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
                <a href="/shop/view_modern.php" class="nav-link active">🏷️ 스티커</a>
                <a href="/MlangPrintAuto/cadarok/index.php" class="nav-link">📖 카다록</a>
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
                <h2 class="card-title">📝 스티커 주문 옵션 선택</h2>
                <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
            </div>
            
            <form id="orderForm" method="post">
                <input type="hidden" name="no" value="<?php echo htmlspecialchars($no ?? '', ENT_QUOTES, 'UTF-8')?>">
                <input type="hidden" name="action" value="calculate">
                
                <table class="order-form-table">
                    <tbody>
                        <tr>
                            <td class="label-cell">
                                <div class="icon-label">
                                    <span class="icon">📄</span>
                                    <span>1. 재질 선택</span>
                                </div>
                            </td>
                            <td class="input-cell">
                                <select name="jong" class="form-control-modern">
                                    <option value="jil 아트유광">✨ 아트지유광 (90g)</option>
                                    <option value="jil 아트무광코팅">🌟 아트지무광코팅 (90g)</option>
                                    <option value="jil 아트비코팅">💫 아트지비코팅 (90g)</option>
                                    <option value="cka 초강접아트유광">⚡ 초강접아트유광 (90g)</option>
                                    <option value="cka 초강접아트비코팅">⚡ 초강접아트비코팅 (90g)</option>
                                    <option value="jsp 유포지">📄 유포지 (80g)</option>
                                    <option value="jsp 투명스티커">🔍 투명스티커</option>
                                    <option value="jsp 홀로그램">🌈 홀로그램</option>
                                    <option value="jsp 크라프트">🌿 크라프트지</option>
                                </select>
                                <small class="help-text">재질에 따라 스티커의 느낌과 내구성이 달라집니다</small>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label-cell">
                                <div class="icon-label">
                                    <span class="icon">📏</span>
                                    <span>2. 크기 설정</span>
                                </div>
                            </td>
                            <td class="input-cell">
                                <div class="size-inputs">
                                    <div class="size-input-inline">
                                        <label class="size-label">가로 (mm):</label>
                                        <input type="number" name="garo" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required>
                                    </div>
                                    <span class="size-multiply">×</span>
                                    <div class="size-input-inline">
                                        <label class="size-label">세로 (mm):</label>
                                        <input type="number" name="sero" class="form-control-inline" placeholder="예: 100" min="10" max="1000" required>
                                    </div>
                                </div>
                                <small class="help-text">최소 10mm, 최대 1000mm까지 제작 가능합니다</small>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label-cell">
                                <div class="icon-label">
                                    <span class="icon">📦</span>
                                    <span>3. 수량 선택</span>
                                </div>
                            </td>
                            <td class="input-cell">
                                <select name="mesu" class="form-control-modern">
                                    <option value="500">500매</option>
                                    <option value="1000" selected>1,000매 (추천)</option>
                                    <option value="2000">2,000매</option>
                                    <option value="3000">3,000매</option>
                                    <option value="5000">5,000매</option>
                                    <option value="10000">10,000매</option>
                                    <option value="20000">20,000매</option>
                                    <option value="30000">30,000매 (대량할인)</option>
                                </select>
                                <small class="help-text">수량이 많을수록 단가가 저렴해집니다</small>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label-cell">
                                <div class="icon-label">
                                    <span class="icon">✏️</span>
                                    <span>4. 편집비</span>
                                </div>
                            </td>
                            <td class="input-cell">
                                <select name="uhyung" class="form-control-modern">
                                    <option value="0">인쇄만 (파일 준비완료)</option>
                                    <option value="10000">기본 편집 (+10,000원)</option>
                                    <option value="30000">고급 편집 (+30,000원)</option>
                                </select>
                                <small class="help-text">디자인 파일이 없으시면 편집 서비스를 이용해주세요</small>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="label-cell">
                                <div class="icon-label">
                                    <span class="icon">🔲</span>
                                    <span>5. 모양 선택</span>
                                </div>
                            </td>
                            <td class="input-cell">
                                <select name="domusong" class="form-control-modern">
                                    <option value="00000 사각">⬜ 사각형 (기본)</option>
                                    <option value="00001 원형">⭕ 원형</option>
                                    <option value="00002 타원">🥚 타원형</option>
                                    <option value="00003 별모양">⭐ 별모양</option>
                                    <option value="00004 하트">❤️ 하트</option>
                                    <option value="00005 다각형">🔷 다각형</option>
                                </select>
                                <small class="help-text">모양에 따라 추가 작업비가 발생할 수 있습니다</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align: center; margin: 3rem 0;">
                    <button type="button" onclick="calculatePrice()" class="btn-calculate">
                        💰 실시간 가격 계산하기
                    </button>
                </div>
            </form>
        </div>
        
        <!-- 가격 계산 결과 -->
        <div id="priceSection" class="price-result">
            <h3>💎 견적 결과</h3>
            <div class="price-amount" id="priceAmount">0원</div>
            <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
            
            <div class="action-buttons">
                <button onclick="addToBasket()" class="btn-action btn-primary">
                    🛒 장바구니에 담기
                </button>
                <a href="cart.php" class="btn-action btn-secondary">
                    👀 장바구니 보기
                </a>
            </div>
        </div>
        
        <!-- 최근 주문 내역 -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📋 최근 스티커 주문 내역</h3>
                <p class="card-subtitle">현재 세션의 주문 내역입니다</p>
            </div>
            
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>재질</th>
                        <th>크기</th>
                        <th>수량</th>
                        <th>도무송</th>
                        <th>편집비</th>
                        <th>금액</th>
                        <th>VAT포함</th>
                        <th>삭제</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 스티커 주문 내역 조회
                    $query = "SELECT * FROM shop_temp WHERE session_id='$session_id' ORDER BY no DESC LIMIT 5";  
                    $result = mysqli_query($connect, $query);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($data = mysqli_fetch_array($result)) {
                            // 도무송 이름 파싱
                            $domusong_parts = explode(' ', $data['domusong'], 2);
                            $domusong_name = isset($domusong_parts[1]) ? $domusong_parts[1] : $data['domusong'];
                            ?>
                            <tr>
                                <td><?php echo $data['no'] ?></td>
                                <td><?php echo substr($data['jong'], 4, 12); ?></td>
                                <td><?php echo $data['garo'] ?>×<?php echo $data['sero'] ?>mm</td>
                                <td><?php echo number_format($data['mesu']) ?>매</td>
                                <td><?php echo htmlspecialchars($domusong_name) ?></td>
                                <td><?php echo number_format($data['uhyung']) ?>원</td>
                                <td><strong><?php echo number_format($data['st_price']) ?>원</strong></td>
                                <td><strong><?php echo number_format($data['st_price_vat']) ?>원</strong></td>
                                <td><a href="del.php?no=<?php echo $data['no'] ?>" onclick="return confirm('정말 삭제할까요?');" class="btn-action btn-secondary" style="padding: 8px 15px; font-size: 0.9rem;">삭제</a></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <div>
                                    <h4>📭 주문 내역이 없습니다</h4>
                                    <p>첫 번째 스티커 주문을 시작해보세요!</p>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

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
        </div> <!-- main-content-wrapper 끝 -->

        <!-- 푸터 -->
    <footer class="modern-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>🖨️ 두송기획인쇄</h3>
                <p>📍 주소: 서울시 영등포구 영등포로 36길 9 송호빌딩 1층</p>
                <p>📞 전화: 1688-2384</p>
                <p>📠 팩스: 02-2632-1829</p>
                <p>✉️ 이메일: dsp1830@naver.com</p>
            </div>

            <div class="footer-section">
                <h4>🎯 주요 서비스</h4>
                <p>🏷️ 스티커 제작</p>
                <p>📇 명함 인쇄</p>
                <p>📖 카다록 제작</p>
                <p>🎨 포스터 인쇄</p>
                <p>📄 각종 인쇄물</p>
            </div>

            <div class="footer-section">
                <h4>⏰ 운영 안내</h4>
                <p><strong>평일:</strong> 09:00 - 18:00</p>
                <p><strong>토요일:</strong> 09:00 - 15:00</p>
                <p><strong>일요일:</strong> 휴무</p>
                <p><strong>점심시간:</strong> 12:00 - 13:00</p>
            </div>

            <div class="footer-section">
                <h4>📋 주문 안내</h4>
                <p>💰 입금 확인 후 작업 진행</p>
                <p>📦 택배비 착불 (3만원 이상 무료)</p>
                <p>📁 주문 후 파일 업로드 필수</p>
                <p>🔄 디자인 수정 3회까지 무료</p>
                <p>⚡ 당일 주문 시 익일 출고</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2024 두송기획인쇄. All rights reserved.</p>
            <p>고품질 인쇄물을 합리적인 가격으로 제작해드립니다. | 사업자등록번호: 123-45-67890</p>
        </div>
    </footer>

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
    
    // 가격 계산 함수
    function calculatePrice() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 필수 입력값 체크
        if (!formData.get('garo') || !formData.get('sero')) {
            alert('가로, 세로 크기를 입력해주세요.');
            return;
        }
        
        // action 파라미터 추가
        formData.set('action', 'calculate');
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 계산중...';
        button.disabled = true;
        
        // AJAX로 가격 계산 요청
        fetch('calculate_price.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                // 계산 결과 표시
                document.getElementById('priceAmount').textContent = data.price + '원';
                document.getElementById('priceVat').textContent = data.price_vat + '원';
                
                // 가격 섹션 표시
                const priceSection = document.getElementById('priceSection');
                priceSection.style.display = 'block';
                priceSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('가격 계산 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('Error:', error);
            alert('가격 계산 중 오류가 발생했습니다.');
        });
    }

    // 장바구니에 추가하는 함수
    function addToBasket() {
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // 필수 입력값 체크
        if (!formData.get('garo') || !formData.get('sero')) {
            alert('가로, 세로 크기를 입력해주세요.');
            return;
        }
        
        // action 파라미터 추가
        formData.set('action', 'add_to_basket');
        
        // 로딩 표시
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ 추가중...';
        button.disabled = true;
        
        // AJAX로 장바구니에 추가
        fetch('add_to_basket_safe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            if (data.success) {
                alert('장바구니에 추가되었습니다! 🛒');
                
                // 장바구니 확인 여부 묻기
                if (confirm('장바구니를 확인하시겠습니까?')) {
                    window.location.href = 'cart.php';
                } else {
                    // 폼 초기화하고 계속 쇼핑
                    document.getElementById('orderForm').reset();
                    document.getElementById('priceSection').style.display = 'none';
                    location.reload(); // 최근 주문 내역 새로고침
                }
            } else {
                alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            button.innerHTML = originalText;
            button.disabled = false;
            console.error('Error:', error);
            alert('장바구니 추가 중 오류가 발생했습니다.');
        });
    }
    
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

    // 페이지 상태 저장 및 복원 기능
    function savePageState() {
        const pageState = {
            scrollPosition: window.pageYOffset || document.documentElement.scrollTop,
            formData: {},
            timestamp: Date.now()
        };
        
        // 폼 데이터 저장
        const form = document.getElementById('orderForm');
        if (form) {
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                pageState.formData[key] = value;
            }
        }
        
        // 가격 결과 표시 상태 저장
        const priceSection = document.getElementById('priceSection');
        if (priceSection) {
            pageState.priceVisible = priceSection.style.display !== 'none';
            pageState.priceAmount = document.getElementById('priceAmount')?.textContent || '';
            pageState.priceVat = document.getElementById('priceVat')?.textContent || '';
        }
        
        // localStorage에 저장 (24시간 유효)
        localStorage.setItem('stickerPageState', JSON.stringify(pageState));
    }
    
    function restorePageState() {
        try {
            const savedState = localStorage.getItem('stickerPageState');
            if (!savedState) return;
            
            const pageState = JSON.parse(savedState);
            
            // 24시간이 지났으면 삭제
            if (Date.now() - pageState.timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem('stickerPageState');
                return;
            }
            
            // 폼 데이터 복원
            if (pageState.formData) {
                Object.keys(pageState.formData).forEach(key => {
                    const element = document.querySelector(`[name="${key}"]`);
                    if (element) {
                        element.value = pageState.formData[key];
                        // 선택된 옵션에 스타일 적용
                        if (element.checkValidity()) {
                            element.style.borderColor = '#27ae60';
                        }
                    }
                });
            }
            
            // 가격 결과 복원
            if (pageState.priceVisible && pageState.priceAmount) {
                const priceSection = document.getElementById('priceSection');
                const priceAmount = document.getElementById('priceAmount');
                const priceVat = document.getElementById('priceVat');
                
                if (priceSection && priceAmount) {
                    priceAmount.textContent = pageState.priceAmount;
                    if (priceVat) priceVat.textContent = pageState.priceVat;
                    priceSection.style.display = 'block';
                }
            }
            
            // 스크롤 위치 복원 (약간의 지연을 두어 페이지 로딩 완료 후 실행)
            setTimeout(() => {
                if (pageState.scrollPosition > 0) {
                    window.scrollTo({
                        top: pageState.scrollPosition,
                        behavior: 'smooth'
                    });
                }
            }, 100);
            
        } catch (error) {
            console.error('페이지 상태 복원 중 오류:', error);
            localStorage.removeItem('stickerPageState');
        }
    }
    
    // 페이지 로드 시 상태 복원
    document.addEventListener('DOMContentLoaded', restorePageState);
    
    // 페이지 언로드 시 상태 저장
    window.addEventListener('beforeunload', savePageState);
    
    // 스크롤 시 주기적으로 위치 저장 (성능을 위해 throttling 적용)
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(savePageState, 150);
    });
    
    // 폼 입력 시 상태 저장
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('change', savePageState);
    });
    
    // 가격 계산 후 상태 저장
    const originalCalculatePrice = window.calculatePrice;
    if (typeof originalCalculatePrice === 'function') {
        window.calculatePrice = function() {
            originalCalculatePrice();
            setTimeout(savePageState, 500); // 가격 계산 완료 후 저장
        };
    }
    
    // 장바구니 추가 성공 시 상태 초기화
    const originalAddToBasket = window.addToBasket;
    if (typeof originalAddToBasket === 'function') {
        window.addToBasket = function() {
            const result = originalAddToBasket();
            // 장바구니 추가 성공 시 저장된 상태 삭제
            setTimeout(() => {
                localStorage.removeItem('stickerPageState');
            }, 1000);
            return result;
        };
    }
    
    // 페이지 새로고침 감지 및 상태 유지 알림
    if (performance.navigation.type === performance.navigation.TYPE_RELOAD) {
        const notification = document.createElement('div');
        notification.innerHTML = '📍 이전 작업 상태가 복원되었습니다';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            z-index: 10000;
            font-weight: 600;
            animation: slideIn 0.5s ease-out;
        `;
        
        // 애니메이션 CSS 추가
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(notification);
        
        // 3초 후 알림 제거
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.5s ease-in';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }
    </script>

    </div> <!-- page-wrapper 끝 -->
</body>
</html>

<?php
if ($connect) {
    mysqli_close($connect);
}
?>