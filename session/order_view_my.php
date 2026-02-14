<?php
/**
 * 주문 상세보기 페이지
 * 수정: mysqli 함수 파라미터 순서 수정, 로그인 체크 개선
 */

session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

// 로그인 확인
$userid = '';
$userEmail = '';

if (isset($_SESSION['user_id'])) {
    // 신규 시스템
    $userid = $_SESSION['username'];
    $query = "SELECT email FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $userEmail = $user['email'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    // 기존 시스템
    $userid = $_SESSION['id_login_ok']['id'];
    // users 테이블 확인
    $query = "SELECT email FROM users WHERE username = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $userEmail = $user['email'];
    }
}

if (!$userid) {
    echo "<script>
            alert('로그인이 필요한 페이지입니다.');
            location.href='/member/login.php';
          </script>";
    exit;
}

// 주문번호 받기
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

if (!$no) {
    echo "<script>
            alert('잘못된 접근입니다.');
            history.back();
          </script>";
    exit;
}

// 주문 정보 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "<script>
            alert('주문 정보를 찾을 수 없습니다.');
            history.back();
          </script>";
    exit;
}

// 본인 주문인지 확인
if ($order['email'] != $userEmail) {
    echo "<script>
            alert('본인의 주문만 확인할 수 있습니다.');
            location.href='/session/orderhistory.php';
          </script>";
    exit;
}

// 주문 상태
$status_labels = [
    0 => "주문취소",
    1 => "주문접수",
    2 => "입금확인",
    3 => "작업중",
    4 => "배송중",
    5 => "배송완료"
];

// Type_1 JSON 파싱
$type1_display = $order['Type_1'] ?? '';
$json_data = json_decode($type1_display, true);

if ($json_data && isset($json_data['formatted_display'])) {
    // v1: formatted_display 키가 있는 경우
    $type1_display = $json_data['formatted_display'];
} elseif ($json_data && isset($json_data['spec_type'])) {
    // v2: spec_* 키가 있는 경우 (data_version=2)
    $display_parts = [];
    if (!empty($json_data['spec_type'])) $display_parts[] = "종류: " . $json_data['spec_type'];
    if (!empty($json_data['spec_material'])) $display_parts[] = "재질: " . $json_data['spec_material'];
    if (!empty($json_data['spec_size'])) $display_parts[] = "규격: " . $json_data['spec_size'];
    if (!empty($json_data['spec_sides'])) $display_parts[] = "인쇄: " . $json_data['spec_sides'];
    if (!empty($json_data['quantity_display'])) $display_parts[] = "수량: " . $json_data['quantity_display'];
    if (!empty($json_data['spec_design'])) $display_parts[] = "디자인: " . $json_data['spec_design'];

    // 추가옵션 파싱 (additional_options 또는 premium_options)
    $opts_json = $json_data['additional_options'] ?? $json_data['premium_options'] ?? '';
    if (is_string($opts_json)) {
        $opts = json_decode($opts_json, true);
    } else {
        $opts = $opts_json;
    }
    if ($opts && is_array($opts)) {
        $opt_labels = [];
        if (!empty($opts['coating_enabled']) && $opts['coating_enabled'] != '0') {
            $coating_types = ['single' => '단면코팅', 'double' => '양면코팅'];
            $opt_labels[] = $coating_types[$opts['coating_type'] ?? ''] ?? '코팅';
        }
        if (!empty($opts['folding_enabled']) && $opts['folding_enabled'] != '0') {
            $opt_labels[] = '접지(' . ($opts['folding_type'] ?? '') . ')';
        }
        if (!empty($opts['foil_enabled']) && $opts['foil_enabled'] != '0') {
            $foil_types = ['gold_matte' => '무광금박', 'gold_gloss' => '유광금박', 'silver_matte' => '무광은박', 'silver_gloss' => '유광은박'];
            $opt_labels[] = $foil_types[$opts['foil_type'] ?? ''] ?? '박가공';
        }
        if (!empty($opts['numbering_enabled']) && $opts['numbering_enabled'] != '0') {
            $opt_labels[] = '넘버링';
        }
        if (!empty($opts['perforation_enabled']) && $opts['perforation_enabled'] != '0') {
            $opt_labels[] = '미싱';
        }
        if (!empty($opts['rounding_enabled']) && $opts['rounding_enabled'] != '0') {
            $opt_labels[] = '귀돌이';
        }
        if (!empty($opts['creasing_enabled']) && $opts['creasing_enabled'] != '0') {
            $opt_labels[] = '오시';
        }
        if (!empty($opt_labels)) {
            $display_parts[] = "추가옵션: " . implode(', ', $opt_labels);
        }
    }

    $type1_display = implode("\n", $display_parts);
} elseif ($json_data && isset($json_data['order_details'])) {
    // 레거시: order_details 키가 있는 경우
    $details = $json_data['order_details'];
    $display_parts = [];

    if (isset($details['jong'])) $display_parts[] = "재질: " . $details['jong'];
    if (isset($details['garo']) && isset($details['sero'])) {
        $display_parts[] = "크기: " . $details['garo'] . 'mm × ' . $details['sero'] . 'mm';
    }
    if (isset($details['mesu'])) $display_parts[] = "수량: " . number_format($details['mesu']) . '매';
    if (isset($details['domusong'])) $display_parts[] = "모양: " . $details['domusong'];

    $type1_display = implode("\n", $display_parts);
}

$status = $order['level'] ?? 1;
$status_text = $status_labels[$status] ?? '주문접수';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 상세보기 #<?php echo $order['no']; ?> - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/style250801.css">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 10px;
            font-size: 13px;
        }
        .order-detail-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1466BA;
            font-size: 16px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
        }
        .status-1 { background: #e3f2fd; color: #1976d2; }
        .status-2 { background: #fff3e0; color: #f57c00; }
        .status-3 { background: #fce4ec; color: #c2185b; }
        .status-4 { background: #e8f5e9; color: #388e3c; }
        .status-5 { background: #e0f2f1; color: #00796b; }
        .status-0 { background: #ffebee; color: #c62828; }

        .info-section {
            margin-bottom: 12px;
        }
        .info-section h3 {
            background: #f8f9fa;
            padding: 6px 10px;
            margin: 0 0 8px 0;
            border-left: 3px solid #1466BA;
            font-size: 13px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .info-table th {
            text-align: left;
            padding: 6px 8px;
            background: #f8f9fa;
            width: 100px;
            font-weight: 500;
            border-bottom: 1px solid #e0e0e0;
            font-size: 12px;
        }
        .info-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        .order-content {
            white-space: pre-line;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            line-height: 1.5;
            font-size: 12px;
        }
        .button-group {
            text-align: center;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
        }
        .btn {
            display: inline-block;
            padding: 6px 20px;
            margin: 0 4px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 13px;
        }
        .btn-primary {
            background: #1466BA;
            color: white;
        }
        .btn-primary:hover {
            background: #0d4d8a;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="order-detail-container">
        <h2>
            📦 주문 상세보기 #<?php echo htmlspecialchars($order['no']); ?>
            <span class="status-badge status-<?php echo $status; ?>">
                <?php echo $status_text; ?>
            </span>
        </h2>

        <!-- 주문 기본 정보 -->
        <div class="info-section">
            <h3>📋 주문 정보</h3>
            <table class="info-table">
                <tr>
                    <th>주문번호</th>
                    <td>#<?php echo htmlspecialchars($order['no']); ?></td>
                </tr>
                <tr>
                    <th>주문일시</th>
                    <td><?php echo htmlspecialchars($order['date'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>주문자명</th>
                    <td><?php echo htmlspecialchars($order['name'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>이메일</th>
                    <td><?php echo htmlspecialchars($order['email'] ?? ''); ?></td>
                </tr>
            </table>
        </div>

        <!-- 주문 상품 정보 -->
        <div class="info-section">
            <h3>🛍️ 주문 상품</h3>
            <div class="order-content">
                <?php echo htmlspecialchars($type1_display); ?>
            </div>
            <table class="info-table" style="margin-top: 15px;">
                <tr>
                    <th>주문금액</th>
                    <td><strong style="color: #1466BA; font-size: 18px;">
                        <?php echo number_format($order['money_4'] ?? 0); ?>원
                    </strong></td>
                </tr>
            </table>
        </div>

        <!-- 연락처 정보 -->
        <div class="info-section">
            <h3>📞 연락처 정보</h3>
            <table class="info-table">
                <tr>
                    <th>전화번호</th>
                    <td><?php echo htmlspecialchars($order['phone'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th>휴대폰</th>
                    <td><?php echo htmlspecialchars($order['Hendphone'] ?? ''); ?></td>
                </tr>
            </table>
        </div>

        <!-- 배송 정보 -->
        <?php if (!empty($order['delivery']) || !empty($order['zip1'])): ?>
        <div class="info-section">
            <h3>🚚 배송 정보</h3>
            <table class="info-table">
                <?php if (!empty($order['delivery'])): ?>
                <tr>
                    <th>배송방법</th>
                    <td><?php echo htmlspecialchars($order['delivery']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['zip1']) || !empty($order['zip2'])): ?>
                <tr>
                    <th>배송주소</th>
                    <td>
                        <?php
                        echo htmlspecialchars($order['zip1'] ?? '');
                        if (!empty($order['zip2'])) {
                            echo '<br>' . htmlspecialchars($order['zip2']);
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- 입금 정보 -->
        <?php if (!empty($order['bank']) || !empty($order['bankname'])): ?>
        <div class="info-section">
            <h3>💳 입금 정보</h3>
            <table class="info-table">
                <?php if (!empty($order['bank'])): ?>
                <tr>
                    <th>입금은행</th>
                    <td><?php echo htmlspecialchars($order['bank']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['bankname'])): ?>
                <tr>
                    <th>입금자명</th>
                    <td><?php echo htmlspecialchars($order['bankname']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- 요청사항 -->
        <?php if (!empty($order['cont'])): ?>
        <div class="info-section">
            <h3>📝 요청사항</h3>
            <div class="order-content">
                <?php echo nl2br(htmlspecialchars($order['cont'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 버튼 -->
        <div class="button-group">
            <a href="/session/orderhistory.php" class="btn btn-secondary">← 주문 목록</a>
            <a href="/" class="btn btn-primary">메인으로</a>
        </div>
    </div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
