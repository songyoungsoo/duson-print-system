<?php
/**
 * Corporate Design System - Order Management Admin Panel
 * Professional financial-style interface preserving all PHP logic
 */

// Set page configuration
$page_title = "주문 관리 시스템";
$breadcrumb = [
    ['title' => '주문 관리']
];

// Include original PHP logic (PRESERVE ALL CALCULATIONS)
include "../../db.php";
include "../../includes/auth.php";

// Debug: Database connection check
if (!isset($db) || !$db) {
    die("ERROR: Database connection not established from db.php");
}

include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";
$mode = isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : "");
$ModifyCode = isset($_POST['ModifyCode']) ? $_POST['ModifyCode'] : (isset($_GET['ModifyCode']) ? $_GET['ModifyCode'] : "");
$no = isset($_POST['no']) ? intval($_POST['no']) : (isset($_GET['no']) ? intval($_GET['no']) : 0);
$Type = isset($_POST['Type']) ? $_POST['Type'] : "기본값";
$ImgFolder = isset($_POST['ImgFolder']) ? $_POST['ImgFolder'] : "default_folder";
$Type_1 = isset($_POST['Type_1']) ? $_POST['Type_1'] : "default_type";
$money_1 = isset($_POST['money_1']) ? $_POST['money_1'] : 0;
$money_2 = isset($_POST['money_2']) ? $_POST['money_2'] : 0;
$money_3 = isset($_POST['money_3']) ? $_POST['money_3'] : 0;
$money_4 = isset($_POST['money_4']) ? $_POST['money_4'] : 0;
$money_5 = isset($_POST['money_5']) ? $_POST['money_5'] : 0;
$OrderName = isset($_POST['name']) ? $_POST['name'] : "미입력";
$email = isset($_POST['email']) ? $_POST['email'] : "noemail@example.com";
$zip = isset($_POST['zip']) ? $_POST['zip'] : "";
$zip1 = isset($_POST['zip1']) ? $_POST['zip1'] : "";
$zip2 = isset($_POST['zip2']) ? $_POST['zip2'] : "";
$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
$Hendphone = isset($_POST['Hendphone']) ? $_POST['Hendphone'] : "";
$bizname = isset($_POST['bizname']) ? $_POST['bizname'] : "기본 회사명";
$bank = isset($_POST['bank']) ? $_POST['bank'] : "기본 은행";
$bankname = isset($_POST['bankname']) ? $_POST['bankname'] : "";
$cont = isset($_POST['cont']) ? $_POST['cont'] : "내용 없음";
$date = isset($_POST['date']) ? $_POST['date'] : date("Y-m-d H:i:s");
$OrderStyle = isset($_POST['OrderStyle']) ? $_POST['OrderStyle'] : "기본 스타일";
$ThingCate = isset($_POST['ThingCate']) ? $_POST['ThingCate'] : "";
$pass = isset($_POST['pass']) ? $_POST['pass'] : "";
$Designer = isset($_POST['Designer']) ? $_POST['Designer'] : "미정";
$Gensu = isset($_POST['Gensu']) ? $_POST['Gensu'] : 0;
$ThingNo = isset($_POST['ThingNo']) ? $_POST['ThingNo'] : 0;

// ===== ORIGINAL PHP PROCESSING LOGIC (PRESERVED) =====
if ($mode == "ModifyOk") {
    // Database connection (preserved original logic)
    if ($db->connect_error) {
        die("Database connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8");

    // POST data processing (preserved)
    $TypeOne = isset($_POST['TypeOne']) ? $_POST['TypeOne'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $zip = isset($_POST['zip']) ? $_POST['zip'] : '';
    $zip1 = isset($_POST['zip1']) ? $_POST['zip1'] : '';
    $zip2 = isset($_POST['zip2']) ? $_POST['zip2'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $Hendphone = isset($_POST['Hendphone']) ? $_POST['Hendphone'] : '';
    $bizname = isset($_POST['bizname']) ? $_POST['bizname'] : '';
    $bank = isset($_POST['bank']) ? $_POST['bank'] : '';
    $bankname = isset($_POST['bankname']) ? $_POST['bankname'] : '';
    $cont = isset($_POST['cont']) ? $_POST['cont'] : '';
    $Gensu = isset($_POST['Gensu']) ? $_POST['Gensu'] : 0;
    $delivery = isset($_POST['delivery']) ? $_POST['delivery'] : '';

    // SQL UPDATE (preserved)
    $stmt = $db->prepare("UPDATE mlangorder_printauto 
        SET name = ?, email = ?, zip = ?, zip1 = ?, zip2 = ?, phone = ?, Hendphone = ?, bizname = ?, 
            bank = ?, bankname = ?, cont = ?, Gensu = ?, delivery = ?
        WHERE no = ?");

    $stmt->bind_param(
        "sssssssssssssi", 
        $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, 
        $bank, $bankname, $cont, $Gensu, $delivery, $no
    );

    if (!$stmt->execute()) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload();</script>";
    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=$no");
    exit;
}

// Get order statistics for dashboard
$order_stats = [];
try {
    $today_result = $db->query("SELECT COUNT(*) as count FROM mlangorder_printauto WHERE DATE(date) = CURDATE()");
    $order_stats['today'] = $today_result ? $today_result->fetch_assoc()['count'] : 0;
    
    $pending_result = $db->query("SELECT COUNT(*) as count FROM mlangorder_printauto WHERE status IS NULL OR status = ''");
    $order_stats['pending'] = $pending_result ? $pending_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $order_stats = ['today' => 0, 'pending' => 0];
}

// Include corporate header
include "../templates/corporate-header.php";
?>

<!-- Order Management Dashboard -->
<div class="grid grid-3 mb-lg">
    <div class="card">
        <div class="card-body text-center">
            <div class="text-2xl font-bold text-primary">
                <?php echo number_format($order_stats['today']); ?>
            </div>
            <div class="text-sm text-secondary">오늘 주문</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <div class="text-2xl font-bold text-warning">
                <?php echo number_format($order_stats['pending']); ?>
            </div>
            <div class="text-sm text-secondary">처리 대기</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <div class="text-2xl font-bold text-info">
                <?php echo date('H:i'); ?>
            </div>
            <div class="text-sm text-secondary">현재 시간</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-lg">
    <div class="card-header">
        <h3 class="card-title">📋 주문 관리 메뉴</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-3">
            <a href="cadarok_List.php" class="btn btn-outline">
                📄 카다록 주문
            </a>
            <a href="NameCard_List.php" class="btn btn-outline">
                💳 명함 주문
            </a>
            <a href="envelope_List.php" class="btn btn-outline">
                ✉️ 봉투 주문
            </a>
            <a href="sticker_List.php" class="btn btn-outline">
                🏷️ 스티커 주문
            </a>
            <a href="MerchandiseBond_List.php" class="btn btn-outline">
                📜 상품권 주문
            </a>
            <a href="NcrFlambeau_List.php" class="btn btn-outline">
                📋 NCR 주문
            </a>
        </div>
    </div>
</div>

<!-- Order Search and Filter -->
<div class="card mb-lg">
    <div class="card-header">
        <h3 class="card-title">🔍 주문 검색</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">검색 조건</label>
                    <select name="search_type" class="form-select">
                        <option value="name">고객명</option>
                        <option value="email">이메일</option>
                        <option value="bizname">회사명</option>
                        <option value="no">주문번호</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">검색어</label>
                    <input type="text" name="search_keyword" class="form-input" placeholder="검색어를 입력하세요">
                </div>
                <div class="form-group">
                    <label class="form-label">기간 설정</label>
                    <select name="date_range" class="form-select">
                        <option value="today">오늘</option>
                        <option value="week">이번 주</option>
                        <option value="month">이번 달</option>
                        <option value="all">전체</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        검색
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// ===== ORDER LIST PROCESSING (PRESERVE ORIGINAL LOGIC) =====
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';
$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'all';

// Build query based on search parameters (preserved logic)
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search_keyword) && !empty($search_type)) {
    $allowed_fields = ['name', 'email', 'bizname', 'no'];
    if (in_array($search_type, $allowed_fields)) {
        if ($search_type === 'no') {
            $where_conditions[] = "no = ?";
            $params[] = intval($search_keyword);
            $types .= 'i';
        } else {
            $where_conditions[] = "$search_type LIKE ?";
            $params[] = "%$search_keyword%";
            $types .= 's';
        }
    }
}

// Date range filtering (preserved logic)
switch ($date_range) {
    case 'today':
        $where_conditions[] = "DATE(date) = CURDATE()";
        break;
    case 'week':
        $where_conditions[] = "WEEK(date) = WEEK(NOW())";
        break;
    case 'month':
        $where_conditions[] = "MONTH(date) = MONTH(NOW())";
        break;
}

// Build final query
$where_clause = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";
$sql = "SELECT * FROM mlangorder_printauto $where_clause ORDER BY date DESC LIMIT 50";

try {
    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $orders = [];
    echo "<div class='alert alert-danger'>데이터 조회 중 오류가 발생했습니다.</div>";
}
?>

<!-- Order List Results -->
<?php if (!empty($orders)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📋 주문 목록</h3>
        <p class="card-subtitle">총 <?php echo count($orders); ?>건의 주문이 검색되었습니다</p>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>주문번호</th>
                        <th>고객정보</th>
                        <th>상품정보</th>
                        <th>주문일시</th>
                        <th>금액 정보</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <div class="font-bold text-primary">
                                #<?php echo str_pad($order['no'], 6, '0', STR_PAD_LEFT); ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm">
                                <div class="font-bold"><?php echo htmlspecialchars($order['name'] ?? '미입력'); ?></div>
                                <?php if (!empty($order['bizname'])): ?>
                                <div class="text-tertiary"><?php echo htmlspecialchars($order['bizname']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($order['email'])): ?>
                                <div class="text-tertiary"><?php echo htmlspecialchars($order['email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm">
                                <div class="badge badge-info">
                                    <?php echo htmlspecialchars($order['Type'] ?? '기본'); ?>
                                </div>
                                <?php if (!empty($order['Gensu']) && $order['Gensu'] > 0): ?>
                                <div class="text-tertiary mt-xs">
                                    수량: <?php echo number_format($order['Gensu']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-sm">
                            <?php 
                            $orderDate = new DateTime($order['date']);
                            echo $orderDate->format('m/d H:i');
                            ?>
                        </td>
                        <td class="text-sm text-right">
                            <?php
                            $total_amount = ($order['money_1'] ?? 0) + ($order['money_2'] ?? 0) + 
                                          ($order['money_3'] ?? 0) + ($order['money_4'] ?? 0) + ($order['money_5'] ?? 0);
                            if ($total_amount > 0):
                            ?>
                            <div class="font-bold">
                                <?php echo number_format($total_amount); ?>원
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status_class = 'badge-primary';
                            $status_text = '접수';
                            if (isset($order['status'])) {
                                switch ($order['status']) {
                                    case '처리중': 
                                        $status_class = 'badge-warning';
                                        $status_text = '처리중';
                                        break;
                                    case '완료': 
                                        $status_class = 'badge-success';
                                        $status_text = '완료';
                                        break;
                                    case '취소': 
                                        $status_class = 'badge-danger';
                                        $status_text = '취소';
                                        break;
                                }
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="?mode=OrderView&no=<?php echo $order['no']; ?>" 
                                   class="btn btn-sm btn-outline">
                                    보기
                                </a>
                                <a href="?mode=OrderEdit&no=<?php echo $order['no']; ?>" 
                                   class="btn btn-sm btn-primary">
                                   수정
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="flex justify-between items-center">
            <div class="text-sm text-secondary">
                표시된 주문: <?php echo count($orders); ?>건
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-sm btn-outline">
                    🖨️ 인쇄
                </button>
                <a href="?export=excel" class="btn btn-sm btn-success">
                    📊 Excel 다운로드
                </a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-body text-center p-3xl">
        <div class="text-6xl mb-lg">📭</div>
        <h3 class="text-lg font-bold mb-md">검색 결과가 없습니다</h3>
        <p class="text-secondary mb-lg">
            검색 조건을 확인하고 다시 시도해주세요.
        </p>
        <a href="?" class="btn btn-primary">
            전체 주문 보기
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// Enhanced order management functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh for real-time updates
    let autoRefresh = setInterval(function() {
        const lastUpdate = new Date();
        console.log('주문 목록 자동 업데이트 확인:', lastUpdate.toLocaleTimeString());
        
        // Visual indicator of last update
        const indicators = document.querySelectorAll('.card-title');
        indicators.forEach(indicator => {
            const original = indicator.textContent;
            if (original.includes('주문 목록')) {
                indicator.innerHTML = original + ' <span class="text-xs text-success">(최근 업데이트: ' + 
                    lastUpdate.toLocaleTimeString() + ')</span>';
            }
        });
    }, 60000); // Every 1 minute
    
    // Stop auto-refresh when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(autoRefresh);
        } else {
            // Restart when page becomes visible
            autoRefresh = setInterval(function() {
                location.reload();
            }, 60000);
        }
    });
    
    // Enhanced search functionality
    const searchForm = document.querySelector('form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const keyword = document.querySelector('[name="search_keyword"]');
            if (keyword && keyword.value.trim().length === 0) {
                if (!confirm('검색어가 비어있습니다. 전체 주문을 조회하시겠습니까?')) {
                    e.preventDefault();
                }
            }
        });
    }
    
    // Keyboard shortcuts for order management
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'f':
                    e.preventDefault();
                    document.querySelector('[name="search_keyword"]').focus();
                    break;
                case 'r':
                    e.preventDefault();
                    location.reload();
                    break;
                case 'p':
                    e.preventDefault();
                    window.print();
                    break;
            }
        }
    });
    
    // Order amount calculation display enhancement
    const amountCells = document.querySelectorAll('td:nth-child(5)');
    amountCells.forEach(cell => {
        const amount = cell.textContent.replace(/[^\d]/g, '');
        if (amount && parseInt(amount) > 0) {
            cell.title = '총 금액: ' + parseInt(amount).toLocaleString() + '원';
        }
    });
});

// Utility functions for order management
window.OrderManager = {
    refreshOrders: function() {
        location.reload();
    },
    
    exportToExcel: function() {
        window.location.href = '?export=excel&' + new URLSearchParams(location.search);
    },
    
    printOrderList: function() {
        window.print();
    },
    
    quickSearch: function(term) {
        document.querySelector('[name="search_keyword"]').value = term;
        document.querySelector('form').submit();
    }
};
</script>

<?php
// Include corporate footer
include "../templates/corporate-footer.php";
?>