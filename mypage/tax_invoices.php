<?php
/**
 * 전자세금계산서 목록 및 발급 요청
 * 경로: /mypage/tax_invoices.php
 */

// 세션 및 인증 처리 (8시간 유지, 자동 로그인 30일)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// 트러스빌 API 연동
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/TrusbillAPI.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 발급 요청 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_invoice'])) {
    $order_no = intval($_POST['order_no']);

    // 주문 정보 확인
    $order_query = "SELECT o.*, u.business_number, u.business_name, u.name, u.phone, u.zipcode, u.address, u.detail_address
                    FROM mlangorder_printauto o
                    LEFT JOIN users u ON o.email = u.email
                    WHERE o.no = ? AND u.id = ?";
    $stmt = mysqli_prepare($db, $order_query);
    mysqli_stmt_bind_param($stmt, "ii", $order_no, $user_id);
    mysqli_stmt_execute($stmt);
    $order_result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($order_result);
    mysqli_stmt_close($stmt);

    if (!$order) {
        $error = "주문 정보를 찾을 수 없습니다.";
    } else {
        // 이미 발급된 세금계산서가 있는지 확인
        $check_query = "SELECT id FROM tax_invoices WHERE order_no = ?";
        $stmt = mysqli_prepare($db, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $order_no);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        $existing = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($stmt);

        if ($existing) {
            $error = "이미 세금계산서가 발급된 주문입니다.";
        } else {
            // 사업자등록번호 확인
            if (empty($order['business_number'])) {
                $error = "사업자등록번호가 등록되지 않았습니다. 마이페이지에서 사업자 정보를 등록해주세요.";
            } else {
                // 세금계산서 발급 요청 생성
                $invoice_number = 'TAX' . date('Ymd') . sprintf('%06d', $order_no);
                $total_price = intval($order['money_5']);
                $supply_amount = round($total_price / 1.1);
                $tax_amount = $total_price - $supply_amount;

                // 트러스빌 API를 통한 전자세금계산서 발급
                $trusbill = new TrusbillAPI();
                
                // 품목 데이터 생성
                $items = [];
                $items[] = TrusbillAPI::createItem(
                    $order['name'], // 품목명 (주문 상품명)
                    1, // 수량
                    $supply_amount, // 단가
                    $supply_amount, // 공급가액
                    $tax_amount // 세액
                );
                
                // 세금계산서 데이터 준비
                $invoice_data = [
                    'issue_date' => date('Ymd'),
                    'customer_business_number' => preg_replace('/[^0-9]/', '', $order['business_number']),
                    'customer_company_name' => $order['business_name'] ?? $order['name'],
                    'customer_ceo_name' => $order['business_owner'] ?? $order['name'],
                    'customer_address' => ($order['zipcode'] ?? '') . ' ' . ($order['address'] ?? '') . ' ' . ($order['detail_address'] ?? ''),
                    'customer_business_type' => $order['business_type'] ?? '',
                    'customer_business_item' => $order['business_item'] ?? '',
                    'customer_contact_name' => $order['name'],
                    'customer_email' => $order['email'],
                    'customer_phone' => $order['phone'],
                    'supply_amount' => $supply_amount,
                    'tax_amount' => $tax_amount,
                    'total_amount' => $total_price,
                    'items' => $items
                ];
                
                // 트러스빌 API 호출
                $api_result = $trusbill->issueInvoice($invoice_data);
                
                if ($api_result['success']) {
                    // 국세청 승인번호 저장
                    $nts_confirm_num = $api_result['data']['ntsConfirmNum'] ?? '';
                    
                    $insert_query = "INSERT INTO tax_invoices
                                    (user_id, order_no, invoice_number, nts_confirm_num, issue_date, supply_amount, tax_amount, total_amount, status, api_response)
                                    VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, 'issued', ?)";
                    // bind_param 3단계 검증: ? = 8개, 타입 = 8자, 변수 = 8개
                    $stmt = mysqli_prepare($db, $insert_query);
                    $api_response_json = json_encode($api_result['data'], JSON_UNESCAPED_UNICODE);
                    mysqli_stmt_bind_param($stmt, "iissiiis", $user_id, $order_no, $invoice_number, $nts_confirm_num, $supply_amount, $tax_amount, $total_price, $api_response_json);

                    if (mysqli_stmt_execute($stmt)) {
                        $success = "전자세금계산서가 발급되었습니다. 국세청 승인번호: " . $nts_confirm_num;
                    } else {
                        $error = "세금계산서 저장 중 오류가 발생했습니다.";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    // API 오류 처리
                    $error = "전자세금계산서 발급 실패: " . ($api_result['message'] ?? '알 수 없는 오류');
                    
                    // 실패 로그 저장
                    $insert_query = "INSERT INTO tax_invoices
                                    (user_id, order_no, invoice_number, issue_date, supply_amount, tax_amount, total_amount, status, api_response)
                                    VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'failed', ?)";
                    // bind_param 3단계 검증: ? = 7개, 타입 = 7자, 변수 = 7개
                    $stmt = mysqli_prepare($db, $insert_query);
                    $api_response_json = json_encode($api_result, JSON_UNESCAPED_UNICODE);
                    mysqli_stmt_bind_param($stmt, "iisiiis", $user_id, $order_no, $invoice_number, $supply_amount, $tax_amount, $total_price, $api_response_json);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// 페이지네이션 설정
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 전체 세금계산서 수
$count_query = "SELECT COUNT(*) as total FROM tax_invoices WHERE user_id = ?";
$stmt = mysqli_prepare($db, $count_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_count = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_count / $per_page);
mysqli_stmt_close($stmt);

// 세금계산서 목록 조회
$list_query = "SELECT * FROM tax_invoices WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($db, $list_query);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $per_page, $offset);
mysqli_stmt_execute($stmt);
$invoices_result = mysqli_stmt_get_result($stmt);
$invoices = [];
while ($row = mysqli_fetch_assoc($invoices_result)) {
    $invoices[] = $row;
}
mysqli_stmt_close($stmt);

// 발급 가능한 주문 목록 조회 (세금계산서가 없는 주문)
// 먼저 사용자 이메일 조회
$email_query = "SELECT email FROM users WHERE id = ?";
$email_stmt = mysqli_prepare($db, $email_query);
mysqli_stmt_bind_param($email_stmt, "i", $user_id);
mysqli_stmt_execute($email_stmt);
$email_result = mysqli_stmt_get_result($email_stmt);
$user_email_data = mysqli_fetch_assoc($email_result);
mysqli_stmt_close($email_stmt);

$available_orders = [];
if ($user_email_data && $user_email_data['email']) {
    $user_email = $user_email_data['email'];

    $available_query = "SELECT o.no, o.date, o.name, CAST(o.money_5 AS UNSIGNED) as total_price
                        FROM mlangorder_printauto o
                        WHERE o.email = ?
                        AND o.no NOT IN (SELECT COALESCE(order_no, 0) FROM tax_invoices)
                        AND o.OrderStyle = '입금완료'
                        ORDER BY o.date DESC
                        LIMIT 10";
    $stmt = mysqli_prepare($db, $available_query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        $available_result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($available_result)) {
            $available_orders[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        // 에러 로깅 (개발 환경에서만)
        if (isset($_GET['debug'])) {
            echo "<!-- SQL Error: " . mysqli_error($db) . " -->";
        }
    }
}

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전자세금계산서 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
        body {
            background: #f5f5f5;
            font-size: 13px;
        }

        .mypage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .mypage-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 900px;
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ffffff;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .section {
            margin-bottom: 35px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1466BA;
            color: #333;
        }

        .request-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .form-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4a8a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            font-size: 12px;
            padding: 6px 14px;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th,
        .data-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        .data-table td {
            color: #666;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-issued {
            background: #28a745;
            color: white;
        }

        .status-pending {
            background: #ffc107;
            color: #000;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .status-failed {
            background: #6c757d;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }

        .page-link:hover {
            background: #f8f9fa;
        }

        .page-link.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 12px;
            }

            .data-table th,
            .data-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="mypage-container">
        <!-- 사이드바 -->
        <?php include 'sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <div class="mypage-content">
            <h1 class="page-title">전자세금계산서</h1>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- 발급 요청 섹션 -->
            <?php if (!empty($available_orders)): ?>
            <div class="section">
                <h2 class="section-title">세금계산서 발급 요청</h2>
                <div class="request-form">
                    <form method="post">
                        <div class="form-group">
                            <label class="form-label">발급 요청할 주문 선택</label>
                            <select name="order_no" class="form-select" required>
                                <option value="">주문을 선택하세요</option>
                                <?php foreach ($available_orders as $order): ?>
                                <option value="<?php echo $order['no']; ?>">
                                    주문번호 #<?php echo $order['no']; ?> -
                                    <?php echo htmlspecialchars($order['name']); ?> -
                                    <?php echo number_format($order['total_price']); ?>원 -
                                    <?php echo htmlspecialchars($order['date']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">입금완료된 주문만 세금계산서 발급이 가능합니다.</div>
                        </div>
                        <button type="submit" name="request_invoice" class="btn btn-primary">발급 요청</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- 세금계산서 목록 -->
            <div class="section">
                <h2 class="section-title">발급된 세금계산서</h2>

                <?php if (empty($invoices)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📄</div>
                    <p>발급된 세금계산서가 없습니다.</p>
                    <p style="font-size: 12px; color: #999;">입금완료된 주문에 대해 세금계산서를 발급 요청할 수 있습니다.</p>
                </div>
                <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>승인번호</th>
                                <th>주문번호</th>
                                <th>발급일자</th>
                                <th>공급가액</th>
                                <th>세액</th>
                                <th>합계금액</th>
                                <th>상태</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                <td>#<?php echo $invoice['order_no']; ?></td>
                                <td><?php echo htmlspecialchars($invoice['issue_date']); ?></td>
                                <td><?php echo number_format($invoice['supply_amount']); ?>원</td>
                                <td><?php echo number_format($invoice['tax_amount']); ?>원</td>
                                <td><?php echo number_format($invoice['total_amount']); ?>원</td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $invoice['status'];
                                    $status_text = [
                                        'issued' => '발급완료',
                                        'pending' => '발급대기',
                                        'cancelled' => '취소됨',
                                        'failed' => '발급실패'
                                    ][$invoice['status']] ?? '알 수 없음';
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <?php if ($invoice['status'] == 'issued'): ?>
                                    <a href="/mypage/view_invoice.php?id=<?php echo $invoice['id']; ?>"
                                       target="_blank"
                                       class="btn btn-secondary">
                                        📄 보기
                                    </a>
                                    <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">대기중</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">이전</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>"
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">다음</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
