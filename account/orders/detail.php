<?php
/**
 * 주문 상세 페이지
 * 경로: /account/orders/detail.php
 */

session_start();
include "../../db.php";

// 로그인 체크
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (!$is_logged_in) {
    header('Location: /member/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// 사용자 정보 설정
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
    $user_id = $user_name;
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
    $user_id = $user_name;
}

// 주문번호 파라미터
$order_no = $_GET['order_no'] ?? '';

if (empty($order_no)) {
    header('Location: /account/orders.php?error=order_not_found');
    exit;
}

// 주문 정보 조회
$query = "SELECT 
    no as order_no,
    Type as product_code,
    Type as product_name,
    ThingCate as product_category,
    Gensu as quantity,
    money_1 as print_price,
    money_2 as design_price,
    money_3 as additional_price,
    money_4 as shipping_price,
    money_5 as total_price,
    name as customer_name,
    email,
    zip,
    zip1 as address1,
    zip2 as address2,
    phone,
    Hendphone as mobile,
    delivery as delivery_method,
    bizname as company_name,
    bank as payment_method,
    bankname as depositor_name,
    cont as memo,
    date as order_date,
    OrderStyle as status,
    Designer as designer,
    coating_enabled,
    coating_type,
    coating_price,
    folding_enabled,
    folding_type,
    folding_price,
    creasing_enabled,
    creasing_lines,
    creasing_price,
    additional_options_total
FROM mlangorder_printauto 
WHERE no = ? AND name = ?";

$stmt = mysqli_prepare($db, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "is", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        header('Location: /account/orders.php?error=access_denied');
        exit;
    }
} else {
    header('Location: /account/orders.php?error=database_error');
    exit;
}

$page_title = '주문 상세 - 두손기획인쇄';

// 상태별 색상
$status_colors = [
    '결제대기' => 'bg-gray-100 text-gray-800',
    '접수' => 'bg-blue-100 text-blue-800',
    '교정중' => 'bg-indigo-100 text-indigo-800',
    '제작중' => 'bg-purple-100 text-purple-800',
    '출고' => 'bg-green-100 text-green-800',
    '완료' => 'bg-green-100 text-green-800',
    '취소' => 'bg-red-100 text-red-800'
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
        }
        .print-area {
            background: white;
            padding: 40px;
            margin: 20px auto;
            max-width: 800px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-area {
                margin: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 헤더 -->
            <div class="mb-6 no-print">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">주문 상세</h1>
                        <p class="mt-1 text-sm text-gray-600">주문번호: <?php echo htmlspecialchars($order['order_no']); ?></p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            🖨️ 인쇄
                        </button>
                        <a href="/account/orders.php" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                            목록으로
                        </a>
                    </div>
                </div>
            </div>

            <!-- 주문 정보 카드 -->
            <div class="print-area bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- 주문 상태 -->
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">주문번호: <?php echo htmlspecialchars($order['order_no']); ?></h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_colors[$order['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo htmlspecialchars($order['status'] ?? '확인중'); ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">주문일: <?php echo date('Y년 m월 d일 H:i', strtotime($order['order_date'])); ?></p>
                </div>

                <!-- 상품 정보 -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold mb-3">📦 상품 정보</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">상품명</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['product_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">카테고리</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['product_category'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">수량</p>
                                <p class="font-medium"><?php echo number_format($order['quantity']); ?>부</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">디자이너</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['designer'] ?? '미정'); ?></p>
                            </div>
                        </div>

                        <?php if ($order['coating_enabled'] || $order['folding_enabled'] || $order['creasing_enabled']): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm font-semibold mb-2">추가 옵션</p>
                            <ul class="text-sm space-y-1">
                                <?php if ($order['coating_enabled']): ?>
                                <li>• 코팅: <?php echo htmlspecialchars($order['coating_type']); ?> (<?php echo number_format($order['coating_price']); ?>원)</li>
                                <?php endif; ?>
                                <?php if ($order['folding_enabled']): ?>
                                <li>• 접지: <?php echo htmlspecialchars($order['folding_type']); ?> (<?php echo number_format($order['folding_price']); ?>원)</li>
                                <?php endif; ?>
                                <?php if ($order['creasing_enabled']): ?>
                                <li>• 오시: <?php echo $order['creasing_lines']; ?>줄 (<?php echo number_format($order['creasing_price']); ?>원)</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 주문자 정보 -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold mb-3">👤 주문자 정보</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">주문자명</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">회사명</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['company_name'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">이메일</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['email'] ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">연락처</p>
                                <p class="font-medium"><?php echo htmlspecialchars($order['phone'] ?? $order['mobile'] ?? '-'); ?></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-600">배송지</p>
                                <p class="font-medium">
                                    <?php echo htmlspecialchars($order['zip'] ?? ''); ?>
                                    <?php echo htmlspecialchars($order['address1'] ?? ''); ?>
                                    <?php echo htmlspecialchars($order['address2'] ?? ''); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 결제 정보 -->
                <div class="mb-6">
                    <h3 class="text-md font-semibold mb-3">💳 결제 정보</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">인쇄비</span>
                                <span class="font-medium"><?php echo number_format($order['print_price'] ?? 0); ?>원</span>
                            </div>
                            <?php if ($order['design_price'] > 0): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">디자인비</span>
                                <span class="font-medium"><?php echo number_format($order['design_price']); ?>원</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($order['additional_options_total'] > 0): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">추가옵션</span>
                                <span class="font-medium"><?php echo number_format($order['additional_options_total']); ?>원</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($order['shipping_price'] > 0): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">배송비</span>
                                <span class="font-medium"><?php echo number_format($order['shipping_price']); ?>원</span>
                            </div>
                            <?php endif; ?>
                            <div class="pt-2 border-t border-gray-300">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold">총 결제금액</span>
                                    <span class="text-lg font-bold text-blue-600"><?php echo number_format($order['total_price'] ?? 0); ?>원</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">결제방법</p>
                            <p class="font-medium"><?php echo htmlspecialchars($order['payment_method'] ?? '무통장입금'); ?></p>
                            <?php if ($order['depositor_name']): ?>
                            <p class="text-sm text-gray-600 mt-1">입금자명: <?php echo htmlspecialchars($order['depositor_name']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 메모 -->
                <?php if ($order['memo']): ?>
                <div class="mb-6">
                    <h3 class="text-md font-semibold mb-3">📝 메모</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm"><?php echo nl2br(htmlspecialchars($order['memo'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 액션 버튼 -->
                <div class="mt-6 pt-6 border-t border-gray-200 no-print">
                    <div class="flex justify-center gap-3">
                        <?php if (in_array($order['status'], ['교정중', '접수', '제작중'])): ?>
                        <a href="/proof/view.php?order_no=<?php echo $order['order_no']; ?>" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            📋 교정 보기
                        </a>
                        <?php endif; ?>
                        
                        <a href="/shop/generate_quote_pdf.php?order_no=<?php echo $order['order_no']; ?>" 
                           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            📄 PDF 다운로드
                        </a>
                        
                        <button onclick="reorderItem('<?php echo $order['order_no']; ?>')" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            🔄 재주문
                        </button>
                        
                        <?php if (in_array($order['status'], ['결제대기', '접수'])): ?>
                        <button onclick="cancelOrder('<?php echo $order['order_no']; ?>')" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            ❌ 주문 취소
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 재주문 함수
        function reorderItem(orderNo) {
            if (confirm('이 주문과 동일한 사양으로 재주문하시겠습니까?')) {
                window.location.href = '/api/orders/reorder.php?order_no=' + orderNo;
            }
        }

        // 주문 취소 함수
        function cancelOrder(orderNo) {
            if (confirm('정말로 주문을 취소하시겠습니까?\n취소 후에는 복구할 수 없습니다.')) {
                window.location.href = '/api/orders/cancel.php?order_no=' + orderNo;
            }
        }
    </script>
</body>
</html>