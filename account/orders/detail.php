<?php
/**
 * 주문 상세 보기 페이지
 * 경로: /account/orders/{order_no} (URL 라우팅) 또는 /account/orders/detail.php?order_no={order_no}
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";

// 로그인 체크
if (!isset($_SESSION['user_id'])) {
    header('Location: /member/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// 주문번호 가져오기
$order_no = $_GET['order_no'] ?? '';

if (empty($order_no)) {
    header('Location: /account/orders.php');
    exit;
}

// 페이지 설정
$page_title = '주문 상세 - ' . $order_no . ' - 두손기획인쇄';

try {
    // 주문 상세 정보 조회 (본인 주문인지 확인)
    $query = "SELECT * FROM mlangorder_printauto WHERE order_no = ? AND customer_name = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ss", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        header('Location: /account/orders.php?error=order_not_found');
        exit;
    }
    
    // JSON 데이터 파싱 (상품 옵션 상세 정보)
    $detail_options = [];
    if (!empty($order['Type_1'])) {
        $decoded = json_decode($order['Type_1'], true);
        if ($decoded) {
            $detail_options = $decoded;
        }
    }
    
} catch (Exception $e) {
    error_log("주문 상세 조회 오류: " . $e->getMessage());
    header('Location: /account/orders.php?error=database_error');
    exit;
}

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

// 상품타입별 한글명
$product_names = [
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

function getProductTypeIcon($product_code) {
    $icons = [
        'inserted' => '📄',
        'sticker' => '🏷️',
        'namecard' => '💳',
        'envelope' => '✉️',
        'cadarok' => '📖',
        'poster' => '🖼️',
        'merchandisebond' => '🎫',
        'ncrflambeau' => '📝',
        'msticker' => '🧲'
    ];
    return $icons[$product_code] ?? '📦';
}

// 상품 옵션 상세 정보를 표시하는 함수
function displayProductOptions($product_code, $options) {
    if (empty($options) || !is_array($options)) {
        return '<p class="text-slate-500">상세 옵션 정보가 없습니다.</p>';
    }
    
    $html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    switch ($product_code) {
        case 'sticker':
            $option_labels = [
                'jong' => '재질',
                'garo' => '가로 (mm)',
                'sero' => '세로 (mm)',
                'mesu' => '수량 (매)',
                'uhyung' => '편집비',
                'domusong' => '모양'
            ];
            break;
            
        case 'inserted':
        case 'leaflet':
            $option_labels = [
                'MY_type' => '인쇄색상',
                'MY_Fsd' => '종이종류', 
                'PN_type' => '종이규격',
                'POtype' => '인쇄면',
                'MY_amount' => '수량',
                'ordertype' => '주문타입'
            ];
            break;
            
        case 'namecard':
            $option_labels = [
                'card_type' => '명함 타입',
                'card_size' => '명함 크기',
                'card_paper' => '용지'
            ];
            break;
            
        case 'envelope':
            $option_labels = [
                'envelope_type' => '봉투 타입',
                'envelope_paper' => '용지'
            ];
            break;
            
        default:
            $option_labels = [];
    }
    
    foreach ($options as $key => $value) {
        if (empty($value)) continue;
        
        $label = $option_labels[$key] ?? $key;
        $html .= '<div class="bg-slate-50 p-3 rounded-lg">';
        $html .= '<div class="text-sm font-medium text-slate-600">' . htmlspecialchars($label) . '</div>';
        $html .= '<div class="text-base text-slate-900">' . htmlspecialchars($value) . '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}
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
        .status-timeline {
            position: relative;
        }
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        .status-step {
            position: relative;
            padding-left: 40px;
            padding-bottom: 20px;
        }
        .status-step::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
        }
        .status-step.active::before {
            background: #3b82f6;
        }
        .status-step.completed::before {
            background: #10b981;
        }
    </style>
</head>
<body class="bg-slate-50">
    <?php include "../../includes/header.php"; ?>

    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 상단 네비게이션 -->
            <div class="mb-6">
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="/account/orders.php" class="text-indigo-600 hover:text-indigo-900">내 주문 내역</a>
                    <span class="text-slate-500">/</span>
                    <span class="text-slate-900">주문 상세</span>
                </nav>
            </div>

            <!-- 페이지 헤더 -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">주문 상세</h1>
                        <p class="mt-1 text-lg text-slate-600"><?php echo htmlspecialchars($order_no); ?></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $status_badges[$order['status']] ?? 'bg-slate-100 text-slate-800'; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                        <button onclick="window.print()" class="px-4 py-2 text-sm bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:ring-2 focus:ring-sky-500">
                            🖨️ 인쇄
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- 왼쪽: 주문 정보 -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- 기본 주문 정보 -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 mb-4">
                            <?php echo getProductTypeIcon($order['product_code']); ?> 상품 정보
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">상품명</label>
                                        <p class="text-lg text-slate-900"><?php echo htmlspecialchars($order['product_name'] ?: $product_names[$order['product_code']] ?? $order['product_code']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">주문 수량</label>
                                        <p class="text-lg text-slate-900"><?php echo number_format($order['qty']); ?>개</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">단가</label>
                                        <p class="text-lg text-slate-900"><?php echo number_format($order['unit_price']); ?>원</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">총 결제금액</label>
                                        <p class="text-2xl font-bold text-indigo-600"><?php echo number_format($order['total_price']); ?>원</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">주문일시</label>
                                        <p class="text-lg text-slate-900"><?php echo date('Y년 m월 d일 H:i', strtotime($order['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($order['options_summary'])): ?>
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <label class="text-sm font-medium text-slate-600">주문 옵션 요약</label>
                            <p class="mt-1 text-slate-900"><?php echo nl2br(htmlspecialchars($order['options_summary'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 상세 옵션 정보 -->
                    <?php if (!empty($detail_options)): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 mb-4">상세 옵션</h2>
                        <?php echo displayProductOptions($order['product_code'], $detail_options); ?>
                    </div>
                    <?php endif; ?>

                    <!-- 배송/수취 정보 -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 mb-4">📦 배송/수취 정보</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">수취인</label>
                                        <p class="text-lg text-slate-900"><?php echo htmlspecialchars($order['recv_name'] ?: $order['customer_name']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">연락처</label>
                                        <p class="text-lg text-slate-900"><?php echo htmlspecialchars($order['recv_phone'] ?: $order['customer_phone']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-slate-600">배송 주소</label>
                                        <p class="text-lg text-slate-900"><?php echo htmlspecialchars($order['recv_addr'] ?: '방문 수령'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($order['memo'])): ?>
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <label class="text-sm font-medium text-slate-600">요청사항</label>
                            <p class="mt-1 text-slate-900 bg-slate-50 p-3 rounded-lg"><?php echo nl2br(htmlspecialchars($order['memo'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 액션 버튼들 -->
                    <div class="flex flex-wrap gap-3">
                        <a href="/proof/view.php?order_no=<?php echo $order['order_no']; ?>" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                            📋 교정 확인
                        </a>
                        <a href="/shop/generate_quote_pdf.php?order_no=<?php echo $order['order_no']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500" target="_blank">
                            📄 PDF 다운로드
                        </a>
                        <button onclick="reorderItem('<?php echo $order['order_no']; ?>')" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-purple-500">
                            🔄 재주문
                        </button>
                        <?php if (in_array($order['status'], ['결제대기', '접수'])): ?>
                        <button onclick="cancelOrder('<?php echo $order['order_no']; ?>')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500">
                            ❌ 주문 취소
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 오른쪽: 주문 진행 상황 -->
                <div class="space-y-6">
                    <!-- 진행 상황 타임라인 -->
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
                        <h2 class="text-xl font-semibold text-slate-900 mb-4">📈 진행 상황</h2>
                        
                        <div class="status-timeline">
                            <?php 
                            $status_steps = ['결제대기', '접수', '교정중', '제작중', '출고', '완료'];
                            $current_status = $order['status'];
                            $current_index = array_search($current_status, $status_steps);
                            
                            foreach ($status_steps as $index => $step):
                                $is_completed = $index < $current_index || ($current_status === '완료' && $step === '완료');
                                $is_active = $step === $current_status;
                                $class = $is_completed ? 'completed' : ($is_active ? 'active' : '');
                            ?>
                            <div class="status-step <?php echo $class; ?>">
                                <div class="font-medium <?php echo $is_active ? 'text-blue-600' : ($is_completed ? 'text-green-600' : 'text-slate-600'); ?>">
                                    <?php echo $step; ?>
                                </div>
                                <?php if ($is_active): ?>
                                <div class="text-sm text-slate-500">현재 단계</div>
                                <?php elseif ($is_completed): ?>
                                <div class="text-sm text-green-600">완료됨</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 고객센터 연락처 -->
                    <div class="bg-indigo-50 rounded-lg border border-indigo-200 p-6">
                        <h2 class="text-lg font-semibold text-indigo-900 mb-3">📞 문의하기</h2>
                        <p class="text-indigo-700 mb-3">주문 관련 문의사항이 있으시면 언제든 연락 주세요.</p>
                        
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-indigo-600">📞</span>
                                <span class="font-medium text-indigo-900">1688-2384</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-indigo-600">📞</span>
                                <span class="font-medium text-indigo-900">02-2632-1830</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-indigo-600">⏰</span>
                                <span class="text-indigo-700">평일 09:00 - 18:00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../includes/footer.php"; ?>

    <script>
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
                        if (confirm('장바구니로 이동하시겠습니까?')) {
                            window.location.href = '/mlangprintauto/shop/cart.php';
                        }
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
            if (confirm('정말로 주문을 취소하시겠습니까?\n\n취소된 주문은 되돌릴 수 없습니다.')) {
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

<?php
// 데이터베이스 연결 종료
if (isset($stmt)) mysqli_stmt_close($stmt);
mysqli_close($db);
?>