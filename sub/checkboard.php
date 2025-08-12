<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 페이지 설정
$page_title = '🔍 두손기획인쇄 - 교정사항 확인';
$current_page = 'checkboard';

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
} 

// 공통 함수 및 설정
if (file_exists("../includes/functions.php")) {
    include "../includes/functions.php";
}

// 세션 및 기본 설정
if (function_exists('check_session')) {
    check_session();
}
if (function_exists('check_db_connection')) {
    check_db_connection($db);
}

// 로그 정보 생성
if (function_exists('generateLogInfo')) {
    $log_info = generateLogInfo();
}

// 공통 인증 처리 포함
if (file_exists("../includes/auth.php")) {
    include "../includes/auth.php";
}

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 전화번호 인증 처리
$phone_auth_success = false;
$auth_error = '';
$user_orders = [];

if ($_POST && isset($_POST['phone_last4'])) {
    $phone_last4 = preg_replace('/[^0-9]/', '', $_POST['phone_last4']);
    
    if (strlen($phone_last4) === 4) {
        // 전화번호 뒷자리로 주문 내역 검색
        $query = "SELECT * FROM MlangOrder_PrintAuto WHERE phone LIKE '%{$phone_last4}' ORDER BY NO DESC";
        $result = mysqli_query($connect, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $phone_auth_success = true;
            while ($row = mysqli_fetch_array($result)) {
                $user_orders[] = $row;
            }
        } else {
            $auth_error = '해당 전화번호로 등록된 주문 내역이 없습니다.';
        }
    } else {
        $auth_error = '전화번호 끝 4자리를 정확히 입력해주세요.';
    }
}

// 공통 헤더 포함
if (file_exists("../includes/header.php")) {
    include "../includes/header.php";
} else {
    // 기본 HTML 헤더
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>' . $page_title . '</title></head><body>';
}

if (file_exists("../includes/nav.php")) {
    include "../includes/nav.php";
}

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';
?>

<style>
/* 교정사항 확인 페이지 전용 스타일 */
.checkboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.auth-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    color: white;
    text-align: center;
}

.auth-form {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.auth-input {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    width: 200px;
    text-align: center;
}

.auth-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.auth-btn:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
}

.error-message {
    background: #ff6b6b;
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: center;
}

.orders-grid {
    display: grid;
    gap: 20px;
}

.order-card {
    background: white;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.order-number {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

.order-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    color: white;
}

.status-6, .status-7 { background: #3498db; } /* 시안, 교정 */
.status-8 { background: #27ae60; } /* 작업완료 */
.status-5 { background: #f39c12; } /* 시안제작중 */
.status-9, .status-10 { background: #e74c3c; } /* 작업중, 교정작업중 */

.order-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
}

.info-label {
    font-size: 12px;
    color: #666;
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
}

.info-value {
    font-size: 16px;
    color: #2c3e50;
    font-weight: 500;
}

.view-details-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.view-details-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.page-title {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
}

@media (max-width: 768px) {
    .auth-form {
        flex-direction: column;
    }
    
    .auth-input {
        width: 100%;
        max-width: 300px;
    }
    
    .order-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .order-info {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- 메인 컨테이너 -->
<div class="checkboard-container">
    <!-- 페이지 타이틀 -->
    <h1 class="page-title">🔍 교정사항 확인</h1>
    
    <!-- 전화번호 인증 섹션 -->
    <div class="auth-section">
        <h2 style="margin: 0 0 10px 0; font-size: 28px;">📱 본인 확인</h2>
        <p style="margin: 0 0 20px 0; opacity: 0.9; font-size: 16px;">주문 시 입력하신 전화번호의 끝 4자리를 입력하세요</p>
        
        <form method="POST" class="auth-form">
            <input type="text" 
                   name="phone_last4" 
                   class="auth-input" 
                   placeholder="전화번호 끝 4자리" 
                   maxlength="4" 
                   pattern="[0-9]{4}" 
                   required 
                   value="<?php echo isset($_POST['phone_last4']) ? htmlspecialchars($_POST['phone_last4']) : ''; ?>">
            <button type="submit" class="auth-btn">🔍 주문내역 확인</button>
        </form>
        
        <?php if ($auth_error): ?>
            <div class="error-message">
                ❌ <?php echo htmlspecialchars($auth_error); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 주문 내역 섹션 -->
    <?php if ($phone_auth_success && !empty($user_orders)): ?>
        <div class="orders-grid">
            <?php foreach ($user_orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">주문번호 #<?php echo $order['no']; ?></div>
                        <div class="order-status status-<?php echo $order['OrderStyle']; ?>">
                            <?php 
                            $status_map = [
                                '2' => '접수중',
                                '3' => '접수완료', 
                                '4' => '입금대기',
                                '5' => '시안제작중',
                                '6' => '시안완료',
                                '7' => '교정중',
                                '8' => '작업완료',
                                '9' => '작업중',
                                '10' => '교정작업중'
                            ];
                            echo isset($status_map[$order['OrderStyle']]) ? $status_map[$order['OrderStyle']] : '상태미정';
                            ?>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-item">
                            <span class="info-label">주문 분류</span>
                            <div class="info-value">
                                <?php 
                                $type_map = [
                                    'inserted' => '📄 전단지',
                                    'sticker' => '🏷️ 스티커', 
                                    'NameCard' => '💼 명함',
                                    'MerchandiseBond' => '🎫 상품권',
                                    'envelope' => '✉️ 봉투',
                                    'NcrFlambeau' => '📋 양식지',
                                    'cadarok' => '📖 카탈로그',
                                    'LittlePrint' => '🖨️ 소량인쇄'
                                ];
                                echo isset($type_map[$order['Type']]) ? $type_map[$order['Type']] : $order['Type'];
                                ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">주문자명</span>
                            <div class="info-value"><?php echo htmlspecialchars($order['name']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">담당자</span>
                            <div class="info-value"><?php echo htmlspecialchars($order['Designer'] ?: '미배정'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">주문일시</span>
                            <div class="info-value"><?php echo date('Y-m-d H:i', strtotime($order['date'])); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">연락처</span>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">이메일</span>
                            <div class="info-value"><?php echo htmlspecialchars($order['email'] ?: '미입력'); ?></div>
                        </div>
                    </div>
                    
                    <?php if (in_array($order['OrderStyle'], ['6', '7', '8'])): ?>
                        <button class="view-details-btn" 
                                onclick="viewOrderDetails(<?php echo $order['no']; ?>)">
                            📋 교정사항 및 상세내용 보기
                        </button>
                    <?php else: ?>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; color: #666;">
                            📝 아직 교정사항이 준비되지 않았습니다.<br>
                            <small>시안 완료 후 확인 가능합니다.</small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    
    <?php elseif ($phone_auth_success && empty($user_orders)): ?>
        <div class="no-orders">
            <h3>📋 주문 내역이 없습니다</h3>
            <p>입력하신 전화번호로 등록된 주문이 없습니다.</p>
        </div>
    
    <?php elseif (!$phone_auth_success && $_POST): ?>
        <!-- 인증 실패 메시지는 위에 표시됨 -->
    
    <?php else: ?>
        <div style="background: #f8f9fa; padding: 30px; border-radius: 12px; text-align: center; color: #666;">
            <h3>👆 위에서 전화번호 끝 4자리를 입력해주세요</h3>
            <p>주문하실 때 입력하신 전화번호의 마지막 4자리 숫자를 입력하시면<br>해당 번호로 주문하신 내역과 교정사항을 확인하실 수 있습니다.</p>
            
            <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #667eea;">
                <strong>💡 이용 안내</strong><br>
                <small>• 전화번호 끝 4자리만 입력하시면 됩니다 (예: 1234)<br>
                • 시안이 완료된 주문만 교정사항을 확인할 수 있습니다<br>
                • 문의사항이 있으시면 1688-2384로 연락해주세요</small>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function viewOrderDetails(orderNo) {
    // 주문 상세 정보를 팝업으로 표시
    const popup = window.open(
        '/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=' + orderNo, 
        'OrderDetails',
        'width=1000,height=600,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'
    );
    popup.focus();
}

// 전화번호 입력 필드에 숫자만 입력되도록 제한
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector('input[name="phone_last4"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>

<?php
// 공통 푸터 포함
if (file_exists("../includes/footer.php")) {
    include "../includes/footer.php";
} else {
    echo '</body></html>';
}

// 데이터베이스 연결 종료
if (isset($connect) && $connect) {
    mysqli_close($connect);
}
?>