<?php
require_once __DIR__ . '/auth_required.php';

$user_id = $current_user['id'];

// 업로드된 파일이 있는 주문 조회
$query = "
    SELECT no, Type, name, date, uploaded_files, ImgFolder
    FROM mlangorder_printauto
    WHERE user_id = ? AND uploaded_files IS NOT NULL AND uploaded_files != ''
    ORDER BY date DESC
";

$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>파일 다운로드 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/mlangprintauto/css/common-styles.css">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Malgun Gothic', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .nav-link { margin: 20px 0; }
        .nav-link a { color: #667eea; text-decoration: none; }
        .file-list { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .order-files { margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .order-files h3 { color: #333; margin-bottom: 15px; }
        .file-item { padding: 10px; margin: 5px 0; background: white; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .file-item a { color: #667eea; text-decoration: none; }
        .no-files { text-align: center; padding: 40px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📁 파일 다운로드</h1>
            <p style="color: #666; margin-top: 5px;">업로드한 파일을 다운로드할 수 있습니다</p>
        </div>
        <div class="nav-link">
            <a href="index.php">← 마이페이지로 돌아가기</a>
        </div>
        <div class="file-list">
            <?php if (mysqli_num_rows($orders) > 0): ?>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <?php
                    $files = json_decode($order['uploaded_files'], true);
                    if (!empty($files)):
                    ?>
                        <div class="order-files">
                            <h3>주문 #<?php echo htmlspecialchars($order['no']); ?> - <?php echo htmlspecialchars($order['Type']); ?></h3>
                            <small style="color: #666;">주문일: <?php echo date('Y-m-d', strtotime($order['date'])); ?></small>
                            <?php foreach ($files as $file): ?>
                                <div class="file-item">
                                    <span>📄 <?php echo htmlspecialchars($file['original_name'] ?? 'file'); ?></span>
                                    <a href="<?php echo htmlspecialchars($file['web_url'] ?? '#'); ?>" download>다운로드</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-files">
                    <p>업로드된 파일이 없습니다.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
