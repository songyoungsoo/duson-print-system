<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 페이지 설정
$page_title = '📚 두손기획인쇄 - 프리미엄 카다록/리플렛 주문';
$current_page = 'cadarok';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 드롭다운 옵션을 가져오는 함수들
function getCategoryOptions($connect, $GGTABLE, $page) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC";
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

function getSizeOptions($connect, $GGTABLE, $category_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE BigNo='$category_no' ORDER BY no ASC";
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

function getPaperTypeOptions($connect, $GGTABLE, $category_no) {
    $options = [];
    $query = "SELECT * FROM $GGTABLE WHERE TreeNo='$category_no' ORDER BY no ASC";
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

function getQuantityOptionsCadarok($connect) {
    $options = [];
    $TABLE = "MlangPrintAuto_cadarok";
    
    // 고유한 수량 옵션들을 가져오기
    $query = "SELECT DISTINCT quantity FROM $TABLE WHERE quantity IS NOT NULL ORDER BY CAST(quantity AS UNSIGNED) ASC";
    $result = mysqli_query($connect, $query);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $options[] = [
                'quantity' => $row['quantity']
            ];
        }
    }
    return $options;
}

// 초기 옵션 데이터 가져오기
$categoryOptions = getCategoryOptions($connect, $GGTABLE, $page);
$firstCategoryNo = !empty($categoryOptions) ? $categoryOptions[0]['no'] : '1';
$sizeOptions = getSizeOptions($connect, $GGTABLE, $firstCategoryNo);
$paperTypeOptions = getPaperTypeOptions($connect, $GGTABLE, $firstCategoryNo);
$quantityOptions = getQuantityOptionsCadarok($connect);

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
// 공통 인증 처리 포함
include "../../includes/auth.php";

// 파일 업로드 컴포넌트 포함
include "../../includes/FileUploadComponent.php";

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 공통 헤더 포함
include "../../includes/header.php";
include "../../includes/nav.php";

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';

// 업로드 컴포넌트 JavaScript 라이브러리 포함
echo '<script src="../../includes/js/UniversalFileUpload.js"></script>';
?>

            <div class="container">
                <!-- 주문 폼 -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📚 카다록/리플렛 주문 옵션 선택</h2>
                        <p class="card-subtitle">아래 옵션들을 선택하신 후 가격을 확인해보세요</p>
                    </div>
                    
                    <form name="choiceForm" method="post" action="order_process.php">
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
                    
                    <div class="price-amount" id="priceAmount">0원</div>
                    <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
                    
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
                            'drop_text' => '카다록 디자인 파일을 여기로 드래그하거나 클릭하여 선택하세요',
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
                        <!-- <select size="3" style="width:100%; height:80px;" name="parentList" multiple class="form-control-modern"></select>
                        <div style="margin-top: .5rem;">
                            <input type="button" onClick="javascript:small_window('<?php echo $MultyUploadDir; ?>/FileUp.php?Turi=<?php echo htmlspecialchars($log_url, ENT_QUOTES, 'UTF-8'); ?>&Ty=<?php echo htmlspecialchars($log_y, ENT_QUOTES, 'UTF-8'); ?>&Tmd=<?php echo htmlspecialchars($log_md, ENT_QUOTES, 'UTF-8'); ?>&Tip=<?php echo htmlspecialchars($log_ip, ENT_QUOTES, 'UTF-8'); ?>&Ttime=<?php echo htmlspecialchars($log_time, ENT_QUOTES, 'UTF-8'); ?>&Mode=tt');" value="파일올리기" class="btn-action btn-primary" style="width: auto; padding: 8px 15px; font-size: 0.9rem;">
                            <input type="button" onclick="javascript:deleteSelectedItemsFromList(parentList);" value="삭제" class="btn-action btn-secondary" style="width: auto; padding: 8px 15px; font-size: 0.9rem;">
                        </div> -->
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
        </div> <!-- container 끝 -->

<?php
// 공통 로그인 모달 포함
include "../../includes/login_modal.php";
?>

    

    <script>
    
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
      console.log('가격 계산 시작');
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

<?php
// 공통 푸터 포함
include "../../includes/footer.php";

if ($connect) {
    mysqli_close($connect);
}
?>