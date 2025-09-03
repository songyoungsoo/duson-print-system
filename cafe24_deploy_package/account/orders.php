<?php
/**
 * 내 주문 내역 대시보드
 * 경로: /account/orders.php
 */

session_start();
include "../db.php";
include "../includes/functions.php";

// 통합 로그인 체크 (세션 + 쿠키 호환)
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (!$is_logged_in) {
    header('Location: /member/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// 사용자 정보 설정
if (isset($_SESSION['user_id'])) {
    // 신규 시스템
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    // 기존 시스템 세션
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
    $user_id = $user_name; // 기존 시스템에서는 ID를 사용
} elseif (isset($_COOKIE['id_login_ok'])) {
    // 기존 시스템 쿠키
    $user_name = $_COOKIE['id_login_ok'];
    $user_id = $user_name; // 기존 시스템에서는 ID를 사용
}

// 페이지 설정
$page_title = '내 주문 내역 - 두손기획인쇄';

// 필터 파라미터
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$product = $_GET['product'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$page_size = intval($_GET['pageSize'] ?? 10);
$offset = ($page - 1) * $page_size;

// 기본 쿼리
$where_conditions = ["customer_name = ?"];
$params = [$user_name];
$param_types = "s";

// 검색 조건 추가
if (!empty($search)) {
    $where_conditions[] = "(order_no LIKE ? OR product_name LIKE ? OR recv_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $param_types .= "sss";
}

if (!empty($status)) {
    $where_conditions[] = "status = ?";
    $params[] = $status;
    $param_types .= "s";
}

if (!empty($product)) {
    $where_conditions[] = "product_code LIKE ?";
    $params[] = "%$product%";
    $param_types .= "s";
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $param_types .= "s";
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $param_types .= "s";
}

$where_clause = implode(' AND ', $where_conditions);

// 총 레코드 수 조회
$count_query = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto WHERE $where_clause";
$count_stmt = mysqli_prepare($db, $count_query);
if ($count_stmt && !empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
} else {
    $total_records = 0;
}

// 주문 데이터 조회
$query = "SELECT 
    order_no,
    product_code,
    product_name,
    options_summary,
    qty as amount,
    total_price,
    status,
    created_at,
    recv_name
FROM MlangOrder_PrintAuto 
WHERE $where_clause 
ORDER BY created_at DESC 
LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    $params[] = $page_size;
    $params[] = $offset;
    $param_types .= "ii";
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $orders = [];
}

$total_pages = ceil($total_records / $page_size);

// 상태 옵션
$status_options = [
    '' => '전체 상태',
    '결제대기' => '결제대기',
    '접수' => '접수',
    '교정중' => '교정중',
    '제작중' => '제작중',
    '출고' => '출고',
    '완료' => '완료',
    '취소' => '취소'
];

// 제품 옵션
$product_options = [
    '' => '전체 상품',
    'inserted' => '전단지',
    'sticker' => '스티커', 
    'namecard' => '명함',
    'envelope' => '봉투',
    'cadarok' => '카다록',
    'poster' => '포스터',
    'merchandisebond' => '쿠폰',
    'ncrflambeau' => '양식지',
    'msticker' => '종이자석'
];

// 상태별 배지 스타일
$status_badges = [
    '결제대기' => 'bg-slate-100 text-slate-800',
    '접수' => 'bg-sky-100 text-sky-800',
    '교정중' => 'bg-indigo-100 text-indigo-800',
    '제작중' => 'bg-violet-100 text-violet-800',
    '출고' => 'bg-emerald-100 text-emerald-800',
    '완료' => 'bg-emerald-100 text-emerald-800',
    '취소' => 'bg-rose-100 text-rose-800'
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cafe': {
                            50: '#f8fafc',
                            100: '#f1f5f9', 
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Noto Sans KR', system-ui, -apple-system, sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #4f46e5 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7 0%, #3730a3 100%);
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            .table-stack {
                display: block;
            }
            .table-stack thead {
                display: none;
            }
            .table-stack tbody,
            .table-stack tr,
            .table-stack td {
                display: block;
                width: 100%;
            }
            .table-stack tr {
                border: 1px solid #e2e8f0;
                margin-bottom: 1rem;
                padding: 1rem;
                border-radius: 0.5rem;
                background: white;
            }
            .table-stack td {
                border: none;
                padding: 0.25rem 0;
            }
            .table-stack td:before {
                content: attr(data-label) ": ";
                font-weight: 600;
                color: #475569;
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    <?php
    // 인증된 사용자용 헤더 업데이트 필요
    include "../includes/header.php";
    ?>

    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 페이지 헤더 -->
            <div class="mb-8">
                <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-red-600">⚠️</span>
                        <span class="ml-2 text-red-800">
                            <?php 
                            $error_messages = [
                                'order_not_found' => '요청하신 주문을 찾을 수 없습니다.',
                                'database_error' => '데이터베이스 오류가 발생했습니다. 잠시 후 다시 시도해주세요.',
                                'access_denied' => '해당 주문에 접근할 권한이 없습니다.'
                            ];
                            echo $error_messages[$_GET['error']] ?? '알 수 없는 오류가 발생했습니다.';
                            ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['logout_message'])): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <span class="text-green-600">✅</span>
                        <span class="ml-2 text-green-800"><?php echo htmlspecialchars($_SESSION['logout_message']); ?></span>
                    </div>
                </div>
                <?php 
                unset($_SESSION['logout_message']); 
                endif; 
                ?>

                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">내 주문 내역</h1>
                        <p class="mt-1 text-sm text-slate-600">주문하신 인쇄물의 상태를 확인하고 관리하세요</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button onclick="exportCsv()" class="px-4 py-2 text-sm bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-sky-500" aria-label="CSV로 내보내기">
                            📊 CSV 내보내기
                        </button>
                        <button onclick="refreshPage()" class="px-4 py-2 text-sm bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-sky-500" aria-label="새로고침">
                            🔄 새로고침
                        </button>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="주문번호/상품명/수취인 검색"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                aria-label="주문 검색"
                            >
                        </div>
                        <div>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <?php foreach ($status_options as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $status === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <select name="product" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <?php foreach ($product_options as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $product === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input 
                                type="date" 
                                name="from" 
                                value="<?php echo htmlspecialchars($date_from); ?>"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>
                        <div>
                            <input 
                                type="date" 
                                name="to" 
                                value="<?php echo htmlspecialchars($date_to); ?>"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary px-4 py-2 text-white rounded-md hover:opacity-90 focus:ring-2 focus:ring-sky-500">
                            🔍 검색
                        </button>
                        <a href="/account/orders.php" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-md hover:bg-slate-200 focus:ring-2 focus:ring-slate-500">
                            🗑️ 초기화
                        </a>
                    </div>
                </form>
            </div>

            <?php if (empty($orders)): ?>
            <!-- 빈 상태 -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-12 text-center">
                <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <span class="text-4xl">📦</span>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-2">아직 주문이 없습니다</h3>
                <p class="text-slate-600 mb-6">원하시는 상품을 선택해 견적안내으로 빠르게 주문해 보세요.</p>
                <a href="/mlangprintauto/cadarok/index.php" class="btn-primary px-6 py-3 text-white rounded-md hover:opacity-90 focus:ring-2 focus:ring-sky-500">
                    견적안내 시작
                </a>
            </div>
            <?php else: ?>
            <!-- 주문 테이블 -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="table-responsive">
                    <table class="min-w-full table-stack" aria-label="내 주문 내역 표">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider sticky left-0 bg-white" style="width: 140px;">주문번호</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">상품</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">옵션(규격/용지/수량)</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">주문일</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">상태</th>
                                <th class="px-6 py-4 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">결제금액</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-slate-500 uppercase tracking-wider" style="width: 220px;">관리</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 sticky left-0 bg-white" data-label="주문번호">
                                    <?php echo htmlspecialchars($order['order_no']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-900" data-label="상품">
                                    <?php echo htmlspecialchars($order['product_name'] ?: $order['product_code']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600" data-label="옵션">
                                    <div class="max-w-xs">
                                        <?php 
                                        $options = $order['options_summary'];
                                        if (strlen($options) > 50) {
                                            echo htmlspecialchars(substr($options, 0, 47)) . '...';
                                        } else {
                                            echo htmlspecialchars($options);
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600" data-label="주문일">
                                    <?php echo date('Y.m.d', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-label="상태">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_badges[$order['status']] ?? 'bg-slate-100 text-slate-800'; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 text-right font-medium" data-label="결제금액">
                                    <?php echo number_format($order['total_price']); ?>원
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm" data-label="관리">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="/account/orders/detail.php?order_no=<?php echo $order['order_no']; ?>" class="text-indigo-600 hover:text-indigo-900 text-xs px-2 py-1 rounded">상세</a>
                                        
                                        <?php if (in_array($order['status'], ['교정중', '접수', '제작중', '출고', '완료'])): ?>
                                        <a href="/proof/view.php?order_no=<?php echo $order['order_no']; ?>" class="text-blue-600 hover:text-blue-900 text-xs px-2 py-1 rounded">교정</a>
                                        <?php endif; ?>
                                        
                                        <a href="/shop/generate_quote_pdf.php?order_no=<?php echo $order['order_no']; ?>" class="text-green-600 hover:text-green-900 text-xs px-2 py-1 rounded">PDF</a>
                                        
                                        <button onclick="reorderItem('<?php echo $order['order_no']; ?>')" class="text-purple-600 hover:text-purple-900 text-xs px-2 py-1 rounded">재주문</button>
                                        
                                        <?php if (in_array($order['status'], ['결제대기', '접수'])): ?>
                                        <button onclick="cancelOrder('<?php echo $order['order_no']; ?>')" class="text-red-600 hover:text-red-900 text-xs px-2 py-1 rounded">취소</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        총 <?php echo number_format($total_records); ?>건 중 <?php echo (($page-1) * $page_size) + 1; ?>-<?php echo min($page * $page_size, $total_records); ?>건
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="px-3 py-2 text-sm bg-white border border-slate-300 rounded-md hover:bg-slate-50">이전</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="px-3 py-2 text-sm rounded-md <?php echo $i === $page ? 'btn-primary text-white' : 'bg-white border border-slate-300 hover:bg-slate-50'; ?>">
                           <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="px-3 py-2 text-sm bg-white border border-slate-300 rounded-md hover:bg-slate-50">다음</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>

    <script>
        // 새로고침 함수
        function refreshPage() {
            window.location.reload();
        }

        // CSV 내보내기 함수
        function exportCsv() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.location.href = '/api/orders/export?' + params.toString();
        }

        // 재주문 함수
        function reorderItem(orderNo) {
            if (confirm('이 주문을 다시 주문하시겠습니까?')) {
                fetch('/api/orders/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_no: orderNo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('재주문이 장바구니에 추가되었습니다.');
                        window.location.href = '/mlangprintauto/shop/cart.php';
                    } else {
                        alert('재주문 중 오류가 발생했습니다: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('재주문 중 오류가 발생했습니다.');
                });
            }
        }

        // 주문 취소 함수
        function cancelOrder(orderNo) {
            if (confirm('결제대기/접수 상태에서만 취소가 가능합니다. 진행할까요?')) {
                fetch('/api/orders/cancel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_no: orderNo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('주문이 취소되었습니다.');
                        location.reload();
                    } else {
                        alert('주문 취소 중 오류가 발생했습니다: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('주문 취소 중 오류가 발생했습니다.');
                });
            }
        }
    </script>
</body>
</html>