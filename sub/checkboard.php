<?php 
session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 페이지 설정
$page_title = '🔍 두손기획인쇄 - 교정사항 확인';
$current_page = 'checkboard';
$additional_css = ['/css/checkboard.css'];

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

// 공통 인증 처리 포함 (통합 로그인 시스템)
if (file_exists("../includes/auth.php")) {
    include "../includes/auth.php";
}

// 관리자 로그인 체크
$is_admin = false;
if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == '1') {
    $is_admin = true;
} elseif (isset($_SESSION['username']) && $_SESSION['username'] == 'admin') {
    $is_admin = true;
} elseif (isset($_SESSION['level']) && $_SESSION['level'] == '1') {
    $is_admin = true;
}

// 일반 사용자 인증 처리
$auth_error = '';
$authenticated_order_no = null;

// 로그아웃 처리
if (isset($_GET['logout'])) {
    unset($_SESSION['checkboard_order_no']);
    header("Location: checkboard.php");
    exit;
}

// GET 파라미터로 특정 주문 인증 해제
if (isset($_GET['clear_auth'])) {
    unset($_SESSION['checkboard_order_no']);
}

// 세션에서 인증된 주문번호 가져오기
if (isset($_SESSION['checkboard_order_no'])) {
    $authenticated_order_no = $_SESSION['checkboard_order_no'];
}

// POST로 인증 시도 (AJAX)
if (isset($_POST['auth_action']) && $_POST['auth_action'] == 'verify') {
    $input_phone_last4 = isset($_POST['phone_last4']) ? trim($_POST['phone_last4']) : '';
    $order_no = isset($_POST['order_no']) ? intval($_POST['order_no']) : 0;

    header('Content-Type: application/json');

    if (empty($input_phone_last4) || $order_no <= 0) {
        echo json_encode(['success' => false, 'message' => '입력 정보가 올바르지 않습니다.']);
        exit;
    }

    // 해당 주문의 전화번호 확인
    $auth_query = "SELECT no, name FROM mlangorder_printauto
                   WHERE no = ?
                   AND (RIGHT(phone, 4) = ? OR RIGHT(Hendphone, 4) = ?)
                   LIMIT 1";
    $auth_stmt = mysqli_prepare($connect, $auth_query);
    mysqli_stmt_bind_param($auth_stmt, "iss", $order_no, $input_phone_last4, $input_phone_last4);
    mysqli_stmt_execute($auth_stmt);
    $auth_result = mysqli_stmt_get_result($auth_stmt);

    if ($auth_row = mysqli_fetch_assoc($auth_result)) {
        // 인증 성공
        $_SESSION['checkboard_order_no'] = $auth_row['no'];
        $redirect_url = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' . $auth_row['no'];
        echo json_encode([
            'success' => true,
            'message' => '인증되었습니다.',
            'redirect_url' => $redirect_url
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => '전화번호가 일치하지 않습니다.']);
        exit;
    }
}

// 캐시 방지 헤더
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// 페이지네이션 설정
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20; // 한 페이지당 주문 수
$offset = ($page - 1) * $limit;

// 검색 필터 처리
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_type = isset($_GET['search_type']) ? trim($_GET['search_type']) : '';
$search_status = isset($_GET['search_status']) ? trim($_GET['search_status']) : '';

// WHERE 조건 구성
$where_conditions = [];
$params = [];
$param_types = '';

// 기본 조건: 모든 주문 표시 (주소 필터 제거 - 2025-01-02)
$where_conditions[] = "1=1";

// 검색 필터는 관리자만 사용 가능
if ($is_admin) {
    if (!empty($search_name)) {
        $where_conditions[] = "name LIKE ?";
        $params[] = "%{$search_name}%";
        $param_types .= 's';
    }

    if (!empty($search_type)) {
        $where_conditions[] = "Type = ?";
        $params[] = $search_type;
        $param_types .= 's';
    }

    if (!empty($search_status)) {
        $where_conditions[] = "OrderStyle = ?";
        $params[] = $search_status;
        $param_types .= 's';
    }
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// 전체 주문 수 조회
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto {$where_clause}";
if (!empty($params)) {
    $count_stmt = mysqli_prepare($connect, $count_query);
    if (!empty($param_types)) {
        mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
} else {
    $count_result = mysqli_query($connect, $count_query);
}
$total_orders = mysqli_fetch_array($count_result)['total'];
$total_pages = ceil($total_orders / $limit);

// 주문 목록 조회 (교정확정 정보 포함)
$query = "SELECT *, IFNULL(proofreading_confirmed, 0) as proofreading_confirmed FROM mlangorder_printauto {$where_clause} ORDER BY no DESC LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$limit, $offset]);
$final_param_types = $param_types . 'ii';

$stmt = mysqli_prepare($connect, $query);
if (!empty($final_param_types)) {
    mysqli_stmt_bind_param($stmt, $final_param_types, ...$final_params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$all_orders = [];
while ($row = mysqli_fetch_array($result)) {
    $all_orders[] = $row;
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="session-id" content="<?php echo htmlspecialchars($session_id); ?>">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/css/common-styles.css?v=<?php echo time(); ?>">
    <?php foreach ($additional_css as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo time(); ?>">
    <?php endforeach; ?>
</head>
<body>
    <?php include "../includes/header-ui.php"; ?>
    <?php include "../includes/nav.php"; ?>

    <?php
    // 로그인 모달 포함
    if (file_exists("../includes/login_modal.php")) {
        include "../includes/login_modal.php";
    }
    ?>

<!-- 콘텐츠 영역 시작 -->
<div class="content-area">
<!-- 메인 컨테이너 -->
<div class="checkboard-container">

    <?php if ($is_admin): ?>
    <!-- 관리자 표시 -->
    <div style="text-align:right; padding:10px; color:#2563eb; font-weight:600;">
        👤 관리자 모드 | <a href="?logout=1" style="color:#dc2626;">로그아웃</a>
    </div>

    <!-- 검색 및 필터 섹션 (관리자 전용) -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <div class="search-row">
                <div class="search-field">
                    <label>주문자명</label>
                    <input type="text" name="search_name" placeholder="주문자명 검색" 
                           value="<?php echo htmlspecialchars($search_name); ?>">
                </div>
                
                <div class="search-field">
                    <label>상품유형</label>
                    <select name="search_type">
                        <option value="">전체</option>
                        <option value="inserted" <?php echo $search_type === 'inserted' ? 'selected' : ''; ?>>전단지</option>
                        <option value="sticker" <?php echo $search_type === 'sticker' ? 'selected' : ''; ?>>스티커</option>
                        <option value="NameCard" <?php echo $search_type === 'NameCard' ? 'selected' : ''; ?>>명함</option>
                        <option value="MerchandiseBond" <?php echo $search_type === 'MerchandiseBond' ? 'selected' : ''; ?>>상품권</option>
                        <option value="envelope" <?php echo $search_type === 'envelope' ? 'selected' : ''; ?>>봉투</option>
                        <option value="cadarok" <?php echo $search_type === 'cadarok' ? 'selected' : ''; ?>>카탈로그</option>
                        <option value="LittlePrint" <?php echo $search_type === 'LittlePrint' ? 'selected' : ''; ?>>포스터</option>
                        <option value="NcrFlambeau" <?php echo $search_type === 'NcrFlambeau' ? 'selected' : ''; ?>>양식지</option>
                    </select>
                </div>
                
                <div class="search-field">
                    <label>진행상태</label>
                    <select name="search_status">
                        <option value="">전체</option>
                        <option value="2" <?php echo $search_status === '2' ? 'selected' : ''; ?>>접수중</option>
                        <option value="3" <?php echo $search_status === '3' ? 'selected' : ''; ?>>접수완료</option>
                        <option value="4" <?php echo $search_status === '4' ? 'selected' : ''; ?>>입금대기</option>
                        <option value="5" <?php echo $search_status === '5' ? 'selected' : ''; ?>>시안제작중</option>
                        <option value="6" <?php echo $search_status === '6' ? 'selected' : ''; ?>>시안완료</option>
                        <option value="7" <?php echo $search_status === '7' ? 'selected' : ''; ?>>교정중</option>
                        <option value="8" <?php echo $search_status === '8' ? 'selected' : ''; ?>>작업완료</option>
                        <option value="9" <?php echo $search_status === '9' ? 'selected' : ''; ?>>작업중</option>
                        <option value="10" <?php echo $search_status === '10' ? 'selected' : ''; ?>>교정작업중</option>
                    </select>
                </div>
                
                <div class="search-buttons">
                    <button type="submit" class="search-btn">🔍 검색</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="reset-btn">🔄 초기화</a>
                </div>
            </div>
        </form>
        
        <!--
        <div class="result-info">
            <span>총 <strong><?php echo number_format($total_orders); ?></strong>개 주문 | 
            <?php echo $page; ?>페이지 / <?php echo $total_pages; ?>페이지</span>
        </div>
        -->
    </div>
    <?php else: ?>
    <?php endif; ?>

    <!-- 주문 내역 섹션 -->
    <?php if (!empty($all_orders)): ?>
        <div class="orders-list-container">
            <div class="orders-table">
                <div class="table-header">
                    <div class="col-order">주문번호</div>
                    <div class="col-type">상품유형</div>
                    <div class="col-name">주문자</div>
                    <div class="col-status">진행상태</div>
                    <div class="col-date">주문일시</div>
                    <div class="col-designer">담당자</div>
                    <div class="col-proofreading">교정확정</div>
                    <div class="col-waybill">운송장번호</div>
                </div>
                
                <div class="table-body">
                    <?php foreach ($all_orders as $order):
                        // 일반 사용자이고, 인증되지 않은 주문인 경우
                        $is_authenticated_order = ($is_admin || $order['no'] == $authenticated_order_no);

                        // display_name 정의 (onclick에서 사용하기 전에 정의)
                        $display_name = $order['name'];
                        if (empty($display_name) || $display_name === '0' || $display_name === 0) {
                            if (!empty($order['email'])) {
                                $email_parts = explode('@', $order['email']);
                                $display_name = $email_parts[0];
                            } else {
                                $display_name = '주문자';
                            }
                        }
                    ?>
                        <div class="table-row clickable"
                             onclick="showPasswordModal(<?php echo $order['no']; ?>, '<?php echo htmlspecialchars($display_name); ?>', '')"
                             style="cursor: pointer;">

                            <div class="col-order">
                                <span class="order-number">#<?php echo $order['no']; ?></span>
                                <?php if ($is_authenticated_order): ?>
                                <span style="color:#059669; font-size:10px; display:block;">✓ 인증됨</span>
                                <?php endif; ?>
                            </div>

                            <div class="col-type">
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
                            
                            <div class="col-name">
                                <?php
                                // name이 0이거나 비어있으면 email의 @ 앞부분 사용
                                $display_name = $order['name'];
                                if (empty($display_name) || $display_name === '0' || $display_name === 0) {
                                    if (!empty($order['email'])) {
                                        $email_parts = explode('@', $order['email']);
                                        $display_name = $email_parts[0];
                                    } else {
                                        $display_name = '주문자';
                                    }
                                }
                                echo htmlspecialchars($display_name);
                                ?>
                            </div>
                            
                            <div class="col-status">
                                <span class="status-badge status-<?php echo $order['OrderStyle']; ?>">
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
                                </span>
                            </div>

                            <div class="col-date">
                                <?php echo date('Y/m/d H:i', strtotime($order['date'])); ?>
                            </div>

                            <div class="col-designer">
                                <?php echo htmlspecialchars($order['Designer'] ?: '관리자'); ?>
                            </div>

                            <div class="col-proofreading">
                                <?php if ($order['proofreading_confirmed'] == 1): ?>
                                    <span class="proofreading-status confirmed">인쇄진행</span>
                                <?php else: ?>
                                    <span class="proofreading-status pending">-</span>
                                <?php endif; ?>
                            </div>

                            <div class="col-waybill" onclick="event.stopPropagation();">
                                <?php if (!empty($order['waybill_no'])): ?>
                                    <a href="https://www.ilogen.com/web/personal/trace/<?php echo htmlspecialchars($order['waybill_no']); ?>"
                                       target="_blank"
                                       class="waybill-link"
                                       title="택배사: <?php echo htmlspecialchars($order['delivery_company'] ?? '로젠'); ?> - 클릭하면 배송조회">
                                        📦 <?php echo htmlspecialchars($order['waybill_no']); ?>
                                    </a>
                                    <?php if (!empty($order['waybill_date'])): ?>
                                        <small style="display:block; color:#666; font-size:0.85em;">
                                            <?php echo date('m/d H:i', strtotime($order['waybill_date'])); ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#999;">-</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            
            <!-- 페이지네이션 (테스트용으로 항상 표시) -->
            <?php if (true): ?>
                <!-- DEBUG: 총 주문수: <?php echo $total_orders; ?>, 총 페이지수: <?php echo $total_pages; ?>, 현재 페이지: <?php echo $page; ?> -->
                <div class="pagination">
                    <?php
                    $current_url = $_SERVER['PHP_SELF'];
                    $query_params = $_GET;
                    
                    // 이전 페이지
                    if ($page > 1):
                        $query_params['page'] = $page - 1;
                        $prev_url = $current_url . '?' . http_build_query($query_params);
                    ?>
                        <a href="<?php echo $prev_url; ?>" class="page-btn prev-btn">◀ 이전</a>
                    <?php endif; ?>
                    
                    <?php
                    // 페이지 번호 표시 로직 - 좌우 5개씩 (총 11개)
                    $start_page = max(1, $page - 5);
                    $end_page = min($total_pages, $page + 5);

                    // 페이지 번호 표시
                    for ($i = $start_page; $i <= $end_page; $i++):
                        if ($i == $page): ?>
                            <span class="page-btn current"><?php echo $i; ?></span>
                        <?php else:
                            $query_params['page'] = $i;
                            $page_url = $current_url . '?' . http_build_query($query_params);
                        ?>
                            <a href="<?php echo $page_url; ?>" class="page-btn"><?php echo $i; ?></a>
                        <?php endif;
                    endfor; ?>
                    
                    <?php
                    // 다음 페이지
                    if ($page < $total_pages):
                        $query_params['page'] = $page + 1;
                        $next_url = $current_url . '?' . http_build_query($query_params);
                    ?>
                        <a href="<?php echo $next_url; ?>" class="page-btn next-btn">다음 ▶</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="list-info">
                <div class="info-box">
                    <h4>💡 이용 안내</h4>
                    <ul>
                        <li>모든 주문 목록을 확인할 수 있습니다</li>
                        <li>주문을 클릭하면 본인 인증 후 교정사항을 확인할 수 있습니다</li>
                        <li>교정사항 확인 시 <strong>전화번호 뒷자리 4자리</strong> 인증이 필요합니다</li>
                        <li>검색 기능을 이용하여 원하는 주문을 빠르게 찾을 수 있습니다</li>
                    </ul>
                </div>
            </div>
        </div>
    
    <?php else: ?>
        <div class="no-orders">
            <h3>📋 주문 내역이 없습니다</h3>
            <p>검색 조건에 맞는 주문이 없습니다.</p>
        </div>
    <?php endif; ?>
</div>

<!-- 전화번호 인증 모달 -->
<div id="passwordModal" class="password-modal" onclick="if(event.target===this) closePasswordModal()">
    <div class="modal-content">
        <button class="modal-close" onclick="closePasswordModal()">&times;</button>

        <h3>📱 주문 확인</h3>
        <p>주문번호 <strong>#<span id="modalOrderNo"></span></strong></p>

        <div class="modal-hint">
            전화번호 뒤 4자리를 입력하세요
        </div>

        <input type="text"
               id="passwordInput"
               placeholder="0000"
               maxlength="4"
               pattern="[0-9]{4}"
               class="modal-input"
               autocomplete="off">

        <div id="passwordError" class="password-error" style="display:none;"></div>

        <div class="modal-buttons">
            <button onclick="closePasswordModal()" class="modal-btn btn-cancel">취소</button>
            <button onclick="verifyPassword()" class="modal-btn btn-verify">확인</button>
        </div>
    </div>
</div>


<script>
// PHP에서 관리자 상태 전달
const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

// PHP에서 인증된 주문 번호 전달
const authenticatedOrderNo = <?php echo isset($_SESSION['checkboard_order_no']) ? intval($_SESSION['checkboard_order_no']) : 'null'; ?>;

let currentOrderNo = null;
let currentOrderName = '';
let currentOrderPhone = '';

function showPasswordModal(orderNo, orderName, orderPhone) {
    currentOrderNo = orderNo;
    currentOrderName = orderName;
    currentOrderPhone = orderPhone;

    // 관리자는 인증 없이 바로 팝업 열기
    if (isAdmin) {
        openProofreadingPopup(orderNo);
        return;
    }

    // 이미 인증된 주문이면 바로 팝업 열기
    if (authenticatedOrderNo === orderNo) {
        openProofreadingPopup(orderNo);
        return;
    }

    // 인증되지 않은 주문 - 비밀번호 모달 표시
    document.getElementById('modalOrderNo').textContent = orderNo;
    document.getElementById('passwordModal').style.display = 'flex';
    document.getElementById('passwordInput').focus();
    document.getElementById('passwordError').style.display = 'none';
    document.getElementById('passwordInput').value = '';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    currentOrderNo = null;
    currentOrderName = '';
    currentOrderPhone = '';
}

function verifyPassword() {
    const phone = document.getElementById('passwordInput').value.trim();
    const errorDiv = document.getElementById('passwordError');

    if (phone.length !== 4) {
        errorDiv.textContent = '전화번호 뒤 4자리를 입력해주세요.';
        errorDiv.style.display = 'block';
        return;
    }

    // AJAX로 전화번호 확인
    fetch('checkboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'auth_action=verify&order_no=' + currentOrderNo + '&phone_last4=' + encodeURIComponent(phone)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 인증 성공 - 모달 닫고 팝업 열기
            document.getElementById('passwordModal').style.display = 'none';
            if (data.redirect_url) {
                // 팝업 열기
                const width = 1000;
                const height = 600;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                const features = `width=${width},height=${height},left=${left},top=${top},` +
                                 `resizable=yes,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=no`;
                const popup = window.open(data.redirect_url, 'ProofreadingDetail_' + currentOrderNo, features);
                if (popup && !popup.closed) {
                    popup.focus();
                } else {
                    alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
                }
            } else {
                // 폴백: redirect_url이 없으면 페이지 새로고침
                location.reload();
            }
        } else {
            errorDiv.textContent = data.message || '전화번호가 일치하지 않습니다.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        errorDiv.textContent = '확인 중 오류가 발생했습니다.';
        errorDiv.style.display = 'block';
    });
}

/**
 * 교정사항 팝업 열기
 * @param {number} orderNo - 주문 번호
 */
function openProofreadingPopup(orderNo) {
    const url = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' + orderNo;

    // 팝업 창 크기 및 위치 계산
    const width = 1000;
    const height = 600;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    const features = `width=${width},height=${height},left=${left},top=${top},` +
                     `resizable=yes,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=no`;

    // 팝업 창 열기
    const popup = window.open(url, 'ProofreadingDetail_' + orderNo, features);

    // 팝업 포커스 (차단되지 않은 경우)
    if (popup && !popup.closed) {
        popup.focus();
    } else {
        alert('팝업이 차단되었습니다. 팝업 차단을 해제해주세요.');
    }
}

// Enter 키로 확인
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && document.getElementById('passwordModal').style.display === 'flex') {
        verifyPassword();
    }
    if (event.key === 'Escape') {
        closePasswordModal();
    }
});

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

</div> <!-- content-area 끝 -->

<?php
// 공통 푸터 포함
include "../includes/footer.php";

// 데이터베이스 연결 종료
if (isset($connect) && $connect) {
    mysqli_close($connect);
}
?>