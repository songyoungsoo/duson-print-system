<?php
/**
 * 견적서 회사명 수정 도구
 * 사용법: fix_company_name.php?id=63&company=한하니&key=fix2025
 */

// 보안키 확인
$key = $_GET['key'] ?? '';
if ($key !== 'fix2025') {
    die('❌ Unauthorized. Use ?key=fix2025');
}

require_once __DIR__ . '/../db.php';

$id = intval($_GET['id'] ?? 0);
$newCompany = trim($_GET['company'] ?? '');

if (!$id) {
    die('❌ 사용법: fix_company_name.php?id=63&company=한하니&key=fix2025');
}

if (!$newCompany) {
    die('❌ 회사명이 필요합니다. 사용법: ?id=63&company=한하니&key=fix2025');
}

header('Content-Type: text/html; charset=utf-8');

// 기존 데이터 조회
$query = "SELECT id, quote_no, customer_company FROM quotes WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quote = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$quote) {
    die("❌ 견적서를 찾을 수 없습니다. (ID: $id)");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회사명 수정</title>
    <style>
        body { font-family: 'Malgun Gothic', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .success { background: #d4edda; border-left-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-left-color: #dc3545; color: #721c24; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 회사명 수정</h1>

        <div class="info">
            <strong>현재 상태:</strong><br>
            견적번호: <code><?php echo htmlspecialchars($quote['quote_no']); ?></code><br>
            ID: <code><?php echo $quote['id']; ?></code><br>
            기존 회사명: <code><?php echo htmlspecialchars($quote['customer_company']); ?></code><br>
            새 회사명: <code><?php echo htmlspecialchars($newCompany); ?></code>
        </div>

        <?php
        // 회사명 업데이트
        $updateQuery = "UPDATE quotes SET customer_company = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "si", $newCompany, $id);

        if (mysqli_stmt_execute($updateStmt)) {
            echo '<div class="info success">';
            echo '<strong>✅ 회사명 수정 완료!</strong><br>';
            echo '이전: <code>' . htmlspecialchars($quote['customer_company']) . '</code><br>';
            echo '변경: <code>' . htmlspecialchars($newCompany) . '</code>';
            echo '</div>';

            echo '<p><strong>다음 단계:</strong></p>';
            echo '<ol>';
            echo '<li>브라우저에서 <strong>Ctrl+F5</strong>로 강력 새로고침</li>';
            echo '<li>견적서 상세 페이지에서 회사명 확인</li>';
            echo '</ol>';

            echo '<a href="detail.php?id=' . $id . '" class="btn">견적서 상세 페이지로 이동</a>';
            echo '<a href="check_quote.php?id=' . $id . '" class="btn">데이터 확인</a>';
        } else {
            echo '<div class="info error">';
            echo '<strong>❌ 회사명 수정 실패</strong><br>';
            echo '오류: ' . mysqli_error($db);
            echo '</div>';
        }

        mysqli_stmt_close($updateStmt);
        mysqli_close($db);
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <p><strong>사용 예시:</strong></p>
            <ul>
                <li><code>fix_company_name.php?id=63&company=한하니&key=fix2025</code></li>
                <li><code>fix_company_name.php?id=63&company=(주)한하니&key=fix2025</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
