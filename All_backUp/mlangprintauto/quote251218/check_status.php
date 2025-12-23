<?php
/**
 * 견적서 상태 확인 디버그 페이지
 */
session_start();
require_once __DIR__ . '/../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die('견적서 ID를 입력하세요. 예: check_status.php?id=1');
}

$query = "SELECT id, quote_no, status, version, is_latest, original_quote_id FROM quotes WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quote = mysqli_fetch_assoc($result);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적서 상태 확인</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 15px; border: 1px solid #ccc; margin-bottom: 10px; }
        .label { font-weight: bold; color: #333; }
        .value { color: #0066cc; }
        .status { font-size: 18px; padding: 5px 10px; border-radius: 3px; display: inline-block; }
        .draft { background: #6c757d; color: white; }
        .sent { background: #0d6efd; color: white; }
    </style>
</head>
<body>
    <h1>견적서 상태 확인</h1>

    <?php if ($quote): ?>
    <div class="box">
        <div><span class="label">ID:</span> <span class="value"><?php echo $quote['id']; ?></span></div>
        <div><span class="label">견적번호:</span> <span class="value"><?php echo htmlspecialchars($quote['quote_no']); ?></span></div>
        <div>
            <span class="label">상태:</span>
            <span class="status <?php echo $quote['status']; ?>"><?php echo strtoupper($quote['status']); ?></span>
        </div>
        <div><span class="label">버전:</span> <span class="value"><?php echo $quote['version'] ?? '1'; ?></span></div>
        <div><span class="label">최신 버전:</span> <span class="value"><?php echo $quote['is_latest'] ? 'YES' : 'NO'; ?></span></div>
        <div><span class="label">원본 ID:</span> <span class="value"><?php echo $quote['original_quote_id'] ?? 'NULL'; ?></span></div>
    </div>

    <div class="box">
        <h3>버튼 표시 조건:</h3>
        <?php if ($quote['status'] === 'draft'): ?>
            <div style="color: green;">✅ <strong>draft 상태</strong> → "✏️ 수정" 버튼이 표시되어야 합니다.</div>
            <div style="margin-top: 10px;">
                <a href="edit.php?id=<?php echo $quote['id']; ?>" style="background: #ffc107; padding: 8px 15px; text-decoration: none; color: #000; border-radius: 3px;">✏️ 수정</a>
            </div>
        <?php elseif ($quote['status'] === 'sent'): ?>
            <div style="color: blue;">✅ <strong>sent 상태</strong> → "📝 개정판 작성" 버튼이 표시되어야 합니다.</div>
            <div style="margin-top: 10px;">
                <a href="revise.php?id=<?php echo $quote['id']; ?>" style="background: #ffc107; padding: 8px 15px; text-decoration: none; color: #000; border-radius: 3px;">📝 개정판 작성</a>
            </div>
        <?php else: ?>
            <div style="color: red;">❌ <strong><?php echo $quote['status']; ?> 상태</strong> → 수정/개정판 버튼이 표시되지 않습니다.</div>
        <?php endif; ?>
    </div>

    <div class="box">
        <h3>문제 해결:</h3>
        <ol>
            <li>웹 서버에 <strong>detail.php</strong>를 FTP로 업로드했는지 확인</li>
            <li>브라우저 캐시: <strong>Ctrl+F5</strong>로 새로고침</li>
            <li>견적서 상태가 <strong>draft</strong> 또는 <strong>sent</strong>인지 확인</li>
        </ol>
    </div>

    <div class="box">
        <a href="detail.php?id=<?php echo $quote['id']; ?>">&larr; 견적서 상세로 돌아가기</a>
    </div>

    <?php else: ?>
    <div class="box" style="color: red;">
        ❌ 견적서를 찾을 수 없습니다. (ID: <?php echo $id; ?>)
    </div>
    <?php endif; ?>
</body>
</html>
