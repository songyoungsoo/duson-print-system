<?php
/**
 * 견적서 상태 변경 도구
 * 사용법: change_status.php?id=24&status=sent&key=update2025
 */

// 보안키 확인
$key = $_GET['key'] ?? '';
if ($key !== 'update2025') {
    die('❌ Unauthorized. Use ?key=update2025');
}

require_once __DIR__ . '/../db.php';

$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if (!$id) {
    die('❌ 견적서 ID가 필요합니다. 사용법: ?id=24&status=sent&key=update2025');
}

$validStatuses = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired', 'converted'];
if (!in_array($status, $validStatuses)) {
    die('❌ 유효하지 않은 상태입니다. 사용 가능: ' . implode(', ', $validStatuses));
}

// 기존 상태 확인
$query = "SELECT id, quote_no, status FROM quotes WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quote = mysqli_fetch_assoc($result);

if (!$quote) {
    die("❌ 견적서를 찾을 수 없습니다. (ID: $id)");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적서 상태 변경</title>
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
        <h1>🔧 견적서 상태 변경</h1>

        <div class="info">
            <strong>현재 상태:</strong><br>
            견적번호: <code><?php echo htmlspecialchars($quote['quote_no']); ?></code><br>
            ID: <code><?php echo $quote['id']; ?></code><br>
            상태: <code><?php echo $quote['status']; ?></code>
        </div>

        <?php
        // 상태 변경 실행
        $updateQuery = "UPDATE quotes SET status = ? WHERE id = ?";
        $updateStmt = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "si", $status, $id);

        if (mysqli_stmt_execute($updateStmt)) {
            echo '<div class="info success">';
            echo '<strong>✅ 상태 변경 완료!</strong><br>';
            echo '이전 상태: <code>' . htmlspecialchars($quote['status']) . '</code><br>';
            echo '새 상태: <code>' . htmlspecialchars($status) . '</code>';
            echo '</div>';

            echo '<p><strong>다음 단계:</strong></p>';
            echo '<ol>';
            echo '<li>브라우저에서 <strong>Ctrl+F5</strong>로 강력 새로고침</li>';
            echo '<li>견적서 상세 페이지에서 버튼 확인</li>';
            echo '</ol>';

            echo '<a href="detail.php?id=' . $id . '" class="btn">견적서 상세 페이지로 이동</a>';
            echo '<a href="check_status.php?id=' . $id . '" class="btn">상태 확인</a>';
        } else {
            echo '<div class="info error">';
            echo '<strong>❌ 상태 변경 실패</strong><br>';
            echo '오류: ' . mysqli_error($db);
            echo '</div>';
        }

        mysqli_stmt_close($updateStmt);
        mysqli_close($db);
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <p><strong>사용 가능한 상태:</strong></p>
            <ul>
                <li><code>draft</code> - 초안 (✏️ 수정 버튼 표시)</li>
                <li><code>sent</code> - 발송됨 (📝 개정판 작성 버튼 표시)</li>
                <li><code>viewed</code> - 조회됨 (수정 불가)</li>
                <li><code>accepted</code> - 승인됨 (수정 불가)</li>
                <li><code>rejected</code> - 거절됨 (수정 불가)</li>
                <li><code>expired</code> - 만료됨 (수정 불가)</li>
            </ul>
        </div>
    </div>
</body>
</html>
