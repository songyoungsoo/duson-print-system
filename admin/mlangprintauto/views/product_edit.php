<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['name'] ?> 수정 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .header h1 {
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-secondary {
            background: #757575;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: right;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $config['name'] ?> 수정</h1>
            <a href="product_manager.php?product=<?= $product ?>" class="btn btn-secondary">← 목록</a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php
        // 데이터 조회
        $query = "SELECT * FROM {$table} WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if (!$row) {
            echo '<p>데이터를 찾을 수 없습니다.</p>';
            echo '<a href="product_manager.php?product=' . $product . '" class="btn btn-secondary">← 목록으로</a>';
            exit;
        }
        ?>

        <form method="POST" action="product_manager.php?product=<?= $product ?>&action=save">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="product" value="<?= $product ?>">

            <?php
            // ProductConfig의 columns 정의를 사용하여 폼 필드 생성
            $columns = $config['columns'];

            foreach ($columns as $key => $db_column) {
                if ($db_column === 'no') continue; // ID는 수정 불가

                $label = ProductConfig::getColumnLabel($db_column);
                $value = htmlspecialchars($row[$db_column] ?? '');

                echo '<div class="form-group">';
                echo "  <label for=\"{$db_column}\">{$label}</label>";

                // 필드 타입에 따라 다른 입력 폼 표시
                if ($db_column === 'money' || $db_column === 'DesignMoney' ||
                    $db_column === 'quantity' || $db_column === 'mesu' ||
                    $db_column === 'garo' || $db_column === 'sero') {
                    // 숫자 필드
                    echo "  <input type=\"number\" id=\"{$db_column}\" name=\"{$db_column}\" value=\"{$value}\" step=\"1\">";
                } else {
                    // 텍스트 필드
                    echo "  <input type=\"text\" id=\"{$db_column}\" name=\"{$db_column}\" value=\"{$value}\">";
                }

                echo '</div>';
            }
            ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">저장</button>
                <a href="product_manager.php?product=<?= $product ?>&action=view&id=<?= $id ?>" class="btn btn-secondary">취소</a>
            </div>
        </form>
    </div>
</body>
</html>
