<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['name'] ?> 상세 - 두손기획인쇄</title>
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
            margin-left: 8px;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-secondary {
            background: #757575;
            color: white;
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .detail-table th,
        .detail-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
            width: 30%;
        }

        .detail-table td {
            color: #666;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
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
            <h1><?= $config['name'] ?> 상세</h1>
            <div>
                <a href="product_manager.php?product=<?= $product ?>&action=edit&id=<?= $id ?>" class="btn btn-primary">수정</a>
                <a href="product_manager.php?product=<?= $product ?>&action=delete&id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                <a href="product_manager.php?product=<?= $product ?>" class="btn btn-secondary">← 목록</a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

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

        <table class="detail-table">
            <tbody>
                <?php
                // ProductConfig의 columns 정의를 사용하여 모든 필드 표시
                $columns = $config['columns'];

                foreach ($columns as $key => $db_column) {
                    $label = ProductConfig::getColumnLabel($db_column);
                    $value = $row[$db_column] ?? '';

                    // 가격 필드는 포맷팅
                    if ($db_column === 'money' || $db_column === 'DesignMoney') {
                        $value = number_format($value) . '원';
                    } else {
                        $value = htmlspecialchars($value);
                    }

                    echo "<tr>";
                    echo "  <th>{$label}</th>";
                    echo "  <td>{$value}</td>";
                    echo "</tr>";
                }

                // 추가 필드들 (ProductConfig에 없는 것들)
                $additional_fields = array_diff(array_keys($row), array_values($columns));
                foreach ($additional_fields as $field) {
                    if ($field === 'no') continue; // 이미 표시됨

                    $label = ProductConfig::getColumnLabel($field);
                    $value = htmlspecialchars($row[$field] ?? '');

                    echo "<tr>";
                    echo "  <th>{$label}</th>";
                    echo "  <td>{$value}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
