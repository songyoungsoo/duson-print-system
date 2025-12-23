<?php
/**
 * 주문 내역 페이지
 * 수정: mysqli 함수 파라미터 순서 수정, 로그인 체크 추가
 */

session_start();
$session_id = session_id();
include $_SERVER['DOCUMENT_ROOT'] . "/db.php";

// 로그인 확인
$userid = '';
if (isset($_SESSION['user_id'])) {
    // 신규 시스템
    $userid = $_SESSION['username'];
} elseif (isset($_SESSION['id_login_ok'])) {
    // 기존 시스템
    $userid = $_SESSION['id_login_ok']['id'];
}

if (!$userid) {
    echo "<script>
            alert('로그인이 필요한 페이지입니다.');
            location.href='/member/login.php';
          </script>";
    exit;
}

// 사용자 정보 조회 (users 테이블 우선)
$query = "SELECT email, name FROM users WHERE username = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    // users 테이블에 없으면 member 테이블 확인
    $query = "SELECT email, name FROM member WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result);
}

if (!$user_data) {
    echo "<script>
            alert('사용자 정보를 찾을 수 없습니다.');
            location.href='/';
          </script>";
    exit;
}

$userEmail = $user_data['email'];
$userName = $user_data['name'];

// 주문 상태
$status_labels = [
    0 => "주문취소",
    1 => "주문접수",
    2 => "입금확인",
    3 => "작업중",
    4 => "배송중"
];

// 페이징 처리
$start = isset($_GET['page']) ? intval($_GET['page']) : 1;
if (!$start) $start = 1;

// 전체 주문 수
$count_query = "SELECT COUNT(*) as total FROM mlangorder_printauto WHERE email = ?";
$count_stmt = mysqli_prepare($db, $count_query);
mysqli_stmt_bind_param($count_stmt, "s", $userEmail);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_data = mysqli_fetch_assoc($count_result);
$total = $count_data['total'];

// 페이지 설정
$pagenum = 10;
$pages = ceil($total / $pagenum);
$offset = $pagenum * ($start - 1);

// 주문 내역 조회
$order_query = "SELECT * FROM mlangorder_printauto WHERE email = ? ORDER BY no DESC LIMIT ?, ?";
$order_stmt = mysqli_prepare($db, $order_query);
mysqli_stmt_bind_param($order_stmt, "sii", $userEmail, $offset, $pagenum);
mysqli_stmt_execute($order_stmt);
$orders = mysqli_stmt_get_result($order_stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 내역 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/style250801.css">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 15px;
            font-size: 13px;
        }
        .order-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 8px;
            font-size: 18px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        .total-count {
            font-size: 12px;
            color: #666;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 13px;
        }
        th {
            background: #1466BA;
            color: white;
            padding: 8px;
            text-align: center;
            font-weight: 500;
            font-size: 13px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: center;
            font-size: 13px;
        }
        tr:hover td {
            background: #f8f9fa;
        }
        a {
            color: #1466BA;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background: #1466BA;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover {
            background: #0d4d8a;
            text-decoration: none;
        }
        .pagination {
            text-align: center;
            margin-top: 15px;
        }
        .pagination a {
            display: inline-block;
            padding: 6px 10px;
            margin: 0 3px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        .pagination a.active {
            background: #1466BA;
            color: white;
            border-color: #1466BA;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <h2>📦 주문 내역</h2>

        <div class="user-info">
            <strong><?php echo htmlspecialchars($userName); ?></strong>님의 주문 내역
            (<?php echo htmlspecialchars($userEmail); ?>)
        </div>

        <div class="total-count">
            총 <strong><?php echo $total; ?></strong>건의 주문
        </div>

        <?php if ($total > 0): ?>
        <table>
            <thead>
                <tr>
                    <th width="8%">주문번호</th>
                    <th width="12%">이름</th>
                    <th width="*">주문내용</th>
                    <th width="12%">총금액</th>
                    <th width="15%">주문일자</th>
                    <th width="10%">상태</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)):
                    // Type_1 JSON 파싱
                    $type1_display = $order['Type_1'] ?? '';
                    $json_data = json_decode($type1_display, true);

                    if ($json_data && isset($json_data['formatted_display'])) {
                        // formatted_display가 있으면 사용
                        $type1_display = $json_data['formatted_display'];
                    } elseif ($json_data && isset($json_data['order_details'])) {
                        // order_details에서 직접 포맷팅
                        $details = $json_data['order_details'];
                        $display_parts = [];

                        if (isset($details['jong'])) $display_parts[] = $details['jong'];
                        if (isset($details['garo']) && isset($details['sero'])) {
                            $display_parts[] = $details['garo'] . 'mm × ' . $details['sero'] . 'mm';
                        }
                        if (isset($details['mesu'])) $display_parts[] = number_format($details['mesu']) . '매';
                        if (isset($details['domusong'])) $display_parts[] = $details['domusong'];

                        $type1_display = implode(' / ', $display_parts);
                    }
                    // JSON이 아니면 원본 텍스트 그대로 사용
                ?>
                <tr>
                    <td>
                        <a href="order_view_my.php?no=<?php echo $order['no']; ?>">
                            #<?php echo $order['no']; ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($order['name'] ?? ''); ?></td>
                    <td style="text-align: left; padding-left: 20px;">
                        <?php echo nl2br(htmlspecialchars($type1_display)); ?>
                    </td>
                    <td><?php echo number_format($order['money_4'] ?? 0); ?>원</td>
                    <td><?php echo htmlspecialchars($order['date'] ?? ''); ?></td>
                    <td>
                        <?php
                        $status = $order['level'] ?? 1;
                        echo $status_labels[$status] ?? '주문접수';
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php
            $page_links_to_show = 5;
            $current_page = $start;

            $start_page = max(1, $current_page - floor($page_links_to_show / 2));
            $end_page = $start_page + $page_links_to_show - 1;

            if ($end_page > $pages) {
                $end_page = $pages;
                $start_page = max(1, $end_page - $page_links_to_show + 1);
            }

            if ($current_page > 1) {
                echo '<a href="?page=1">처음</a>';
                echo '<a href="?page=' . ($current_page - 1) . '">이전</a>';
            }

            for ($i = $start_page; $i <= $end_page; $i++) {
                echo '<a href="?page=' . $i . '" class="' . ($i == $current_page ? 'active' : '') . '">' . $i . '</a>';
            }

            if ($current_page < $pages) {
                echo '<a href="?page=' . ($current_page + 1) . '">다음</a>';
                echo '<a href="?page=' . $pages . '">끝</a>';
            }
            ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="no-orders">
            <p style="font-size: 18px; margin-bottom: 10px;">📭</p>
            <p>주문 내역이 없습니다.</p>
            <p style="font-size: 14px; color: #999; margin-top: 10px;">
                첫 주문을 시작해보세요!
            </p>
        </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="/" class="back-link">← 메인으로 돌아가기</a>
        </div>
    </div>
</body>
</html>
<?php
mysqli_stmt_close($count_stmt);
mysqli_stmt_close($order_stmt);
mysqli_close($db);
?>
