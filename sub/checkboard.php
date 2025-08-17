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

// 공통 인증 처리는 필요시에만 포함 (비회원 접근 허용)
// if (file_exists("../includes/auth.php")) {
//     include "../includes/auth.php";
// }

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

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// 전체 주문 수 조회
$count_query = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto {$where_clause}";
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

// 주문 목록 조회
$query = "SELECT * FROM MlangOrder_PrintAuto {$where_clause} ORDER BY no DESC LIMIT ? OFFSET ?";
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

// 로그인 모달 포함
if (file_exists("../includes/login_modal.php")) {
    include "../includes/login_modal.php";
}

// 세션 ID를 JavaScript에서 사용할 수 있도록 메타 태그 추가
echo '<meta name="session-id" content="' . htmlspecialchars($session_id) . '">';
?>

<style>
/* 교정사항 확인 페이지 전용 스타일 */
.checkboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    width: calc(100% - 30px);
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

/* 검색 섹션 스타일 */
.search-section {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    border: 1px solid #e9ecef;
}

.search-form {
    margin-bottom: 15px;
}

.search-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 12px;
    align-items: end;
}

.search-field {
    display: flex;
    flex-direction: column;
}

.search-field label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.search-field input,
.search-field select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.search-buttons {
    display: flex;
    gap: 10px;
}

.search-btn,
.reset-btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.search-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.reset-btn {
    background: #f8f9fa;
    color: #666;
    border: 1px solid #ddd;
}

.reset-btn:hover {
    background: #e9ecef;
}

.result-info {
    color: #666;
    font-size: 0.85rem;
    text-align: center;
    padding: 8px;
    background: #e9ecef;
    border-radius: 4px;
    margin-top: 10px;
}

/* 페이지네이션 스타일 */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 3px;
    margin: 20px 0 15px;
    flex-wrap: wrap;
}

.page-btn {
    padding: 6px 10px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    text-decoration: none;
    border-radius: 3px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.page-btn:hover {
    background: #f8f9ff;
    border-color: #667eea;
    color: #667eea;
}

.page-btn.current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.page-btn.prev-btn,
.page-btn.next-btn {
    font-weight: 600;
}

.page-dots {
    padding: 8px 4px;
    color: #999;
}

/* 리스트 형식 스타일 */
.orders-list-container {
    background: white;
    border-radius: 6px;
    width: 100%;
    border: 1px solid #e9ecef;
}


.orders-table {
    background: white;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
    margin-bottom: 15px;
    width: 100%;
    border: 1px solid #dee2e6;
    border-top: none;
}

.table-header {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr 1.2fr 1fr 1fr;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0;
    width: 100%;
}

.table-header > div {
    padding: 0 15px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    color: white !important;
    border-right: 1px solid rgba(255,255,255,0.2);
}

.table-header > div:last-child {
    border-right: none;
}

.table-body {
    background: white;
}

.table-row {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr 1.2fr 1fr 1fr;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
    padding: 0;
    align-items: center;
    width: 100%;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row.clickable {
    cursor: pointer;
}

.table-row.clickable:hover {
    background: #f8f9ff;
    transform: none;
    box-shadow: none;
}

.table-row.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.table-row > div {
    padding: 0 15px;
    text-align: center;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 45px;
    border-right: 1px solid #f0f0f0;
}

.table-row > div:last-child {
    border-right: none;
}

.col-order .order-number {
    font-weight: 700;
    color: #2c3e50;
    font-size: 0.9rem;
}

.col-type {
    font-size: 0.8rem;
}

.col-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.85rem;
}

.col-status {
    position: relative;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    margin-bottom: 2px;
}

.status-badge.status-6, .status-badge.status-7 { background: #3498db; }
.status-badge.status-8 { background: #27ae60; }
.status-badge.status-5 { background: #f39c12; }
.status-badge.status-9, .status-badge.status-10 { background: #e74c3c; }
.status-badge.status-2, .status-badge.status-3, .status-badge.status-4 { background: #95a5a6; }

.clickable-icon {
    font-size: 1.2rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-5px); }
    60% { transform: translateY(-3px); }
}

.col-date {
    color: #666;
    font-size: 0.8rem;
}

.col-designer {
    color: #666;
    font-size: 0.8rem;
}

.list-info {
    margin-top: 15px;
    padding: 15px;
}

.info-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 3px solid #667eea;
}

.info-box h4 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.info-box ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-box li {
    padding: 8px 0;
    color: #666;
    font-size: 0.9rem;
    border-bottom: 1px solid #e9ecef;
}

.info-box li:last-child {
    border-bottom: none;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}


/* 반응형 디자인 */
@media (max-width: 768px) {
    .search-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .search-buttons {
        justify-content: center;
    }
    
    .table-header,
    .table-row {
        grid-template-columns: 1fr;
        text-align: left;
    }
    
    .table-header {
        display: none;
    }
    
    .table-row {
        display: block;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }
    
    .table-row > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table-row > div:last-child {
        border-bottom: none;
    }
    
    .table-row > div:before {
        content: attr(data-label);
        font-weight: 600;
        color: #666;
        font-size: 0.8rem;
    }
    
    .col-order:before { content: '주문번호'; }
    .col-type:before { content: '상품유형'; }
    .col-name:before { content: '주문자'; }
    .col-status:before { content: '진행상태'; }
    .col-date:before { content: '주문일시'; }
    .col-designer:before { content: '담당자'; }
    
    .pagination {
        justify-content: center;
        gap: 3px;
    }
    
    .page-btn {
        padding: 6px 8px;
        font-size: 0.8rem;
    }
}
</style>

<!-- 메인 컨테이너 -->
<div class="checkboard-container">
    <!-- 검색 및 필터 섹션 -->
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
                </div>
                
                <div class="table-body">
                    <?php foreach ($all_orders as $order): ?>
                        <div class="table-row clickable" 
                             onclick="showPasswordModal(<?php echo $order['no']; ?>, '<?php echo htmlspecialchars($order['name']); ?>', '<?php echo htmlspecialchars($order['phone']); ?>')">
                            
                            <div class="col-order">
                                <span class="order-number">#<?php echo $order['no']; ?></span>
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
                                <?php echo htmlspecialchars($order['name']); ?>
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
                                <span class="clickable-icon">👆</span>
                            </div>
                            
                            <div class="col-date">
                                <?php echo date('Y/m/d H:i', strtotime($order['date'])); ?>
                            </div>
                            
                            <div class="col-designer">
                                <?php echo htmlspecialchars($order['Designer'] ?: '미배정'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            
            <!-- 페이지네이션 -->
            <?php if ($total_pages > 1): ?>
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
                    // 페이지 번호 표시 로직
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    // 첫 페이지
                    if ($start_page > 1):
                        $query_params['page'] = 1;
                        $first_url = $current_url . '?' . http_build_query($query_params);
                    ?>
                        <a href="<?php echo $first_url; ?>" class="page-btn">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="page-btn current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <?php
                            $query_params['page'] = $i;
                            $page_url = $current_url . '?' . http_build_query($query_params);
                            ?>
                            <a href="<?php echo $page_url; ?>" class="page-btn"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php
                    // 마지막 페이지
                    if ($end_page < $total_pages):
                        if ($end_page < $total_pages - 1): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                        <?php
                        $query_params['page'] = $total_pages;
                        $last_url = $current_url . '?' . http_build_query($query_params);
                        ?>
                        <a href="<?php echo $last_url; ?>" class="page-btn"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                    
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
                        <li>교정사항 확인 시 <strong>이름 + 전화번호 뒷자리 4자리</strong> 인증이 필요합니다</li>
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

<!-- 비밀번호 인증 모달 -->
<div id="passwordModal" class="password-modal" style="display: none;">
    <div class="modal-overlay" onclick="closePasswordModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>🔐 교정사항 확인 인증</h3>
            <button class="modal-close" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modalMessage">교정사항을 확인하시려면 <strong>전화번호 뒷자리 4자리</strong>를 입력해주세요.</p>
            <div id="modalHint" style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 0.9rem;">
                <!-- 동적으로 주문자명이 표시됩니다 -->
            </div>
            <input type="text" id="passwordInput" placeholder="전화번호 뒷자리 4자리" maxlength="4" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin: 10px 0; font-size: 16px;">
            <div id="passwordError" style="color: #e74c3c; margin-top: 10px; display: none;"></div>
        </div>
        <div class="modal-footer">
            <button onclick="closePasswordModal()" class="btn-cancel">취소</button>
            <button onclick="verifyPassword()" class="btn-verify">확인</button>
        </div>
    </div>
</div>

<style>
.password-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 0 20px 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-cancel, .btn-verify {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.btn-cancel {
    background: #f8f9fa;
    color: #666;
}

.btn-verify {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-verify:hover {
    opacity: 0.9;
}

/* 로그인 모달 스타일 */
.login-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.login-modal-content {
    position: relative;
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    width: 90%;
    max-width: 400px;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-modal-header {
    padding: 20px 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.login-modal-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.3rem;
}

.close-modal {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
    line-height: 1;
}

.close-modal:hover {
    color: #000;
}

.login-modal-body {
    padding: 0 20px 20px;
}

.login-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.login-tab {
    flex: 1;
    padding: 10px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    border-bottom: 2px solid transparent;
}

.login-tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.login-form {
    display: none;
}

.login-form.active {
    display: block;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

.form-submit {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.form-submit:hover {
    opacity: 0.9;
}

.login-message {
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
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
</style>

<script>
let currentOrderNo = null;
let currentOrderName = '';
let currentOrderPhone = '';

function showPasswordModal(orderNo, orderName, orderPhone) {
    currentOrderNo = orderNo;
    currentOrderName = orderName;
    currentOrderPhone = orderPhone;
    document.getElementById('passwordModal').style.display = 'flex';
    document.getElementById('passwordInput').focus();
    document.getElementById('passwordError').style.display = 'none';
    document.getElementById('passwordInput').value = '';
    
    // 주문자명 표시 및 힌트 업데이트 (전화번호는 마스킹)
    const hintDiv = document.getElementById('modalHint');
    hintDiv.innerHTML = `<strong>${orderName}</strong>님의 주문 → 전화번호 뒷자리 <strong>****</strong>를 입력하세요`;
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    currentOrderNo = null;
    currentOrderName = '';
    currentOrderPhone = '';
}

function verifyPassword() {
    const password = document.getElementById('passwordInput').value.trim();
    const errorDiv = document.getElementById('passwordError');
    
    if (!password) {
        errorDiv.textContent = '비밀번호를 입력해주세요.';
        errorDiv.style.display = 'block';
        return;
    }
    
    // AJAX로 비밀번호 확인
    fetch('/sub/verify_popup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_no=' + currentOrderNo + '&password=' + encodeURIComponent(password)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closePasswordModal();
            // 새 창으로 교정사항 보기
            const popup = window.open(
                data.redirect_url,
                'OrderDetails',
                'width=1000,height=600,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'
            );
            popup.focus();
        } else {
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        errorDiv.textContent = '확인 중 오류가 발생했습니다.';
        errorDiv.style.display = 'block';
    });
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