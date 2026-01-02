<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $config['name'] ?> 관리 - 두손기획인쇄</title>
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
            max-width: 1400px;
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

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .actions {
            white-space: nowrap;
        }

        .actions a {
            margin-right: 8px;
            font-size: 12px;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            padding: 8px 12px;
            margin: 0 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
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
            <h1><?= $config['name'] ?> 관리</h1>
            <div>
                <a href="product_manager.php?product=<?= $product ?>&action=new" class="btn btn-primary">+ 새로 추가</a>
                <a href="product_manager.php" class="btn btn-secondary">← 제품 선택</a>
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

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="검색..." onkeyup="filterTable()">
        </div>

        <?php
        // 데이터 조회
        $query = "SELECT * FROM {$table} ORDER BY no DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // 전체 개수
        $count_query = "SELECT COUNT(*) as total FROM {$table}";
        $count_result = mysqli_query($db, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total = $count_row['total'];
        $total_pages = ceil($total / $limit);
        ?>

        <p>전체 <strong><?= number_format($total) ?></strong>개</p>

        <table id="dataTable">
            <thead>
                <tr>
                    <?php
                    $display_columns = $config['display_columns'];
                    foreach ($display_columns as $col) {
                        $label = ProductConfig::getColumnLabel($col);
                        echo "<th>{$label}</th>";
                    }
                    ?>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <?php foreach ($display_columns as $col): ?>
                            <td>
                                <?php
                                $value = $row[$col] ?? '';
                                if ($col === 'money' || $col === 'DesignMoney') {
                                    echo number_format($value) . '원';
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="actions">
                            <a href="product_manager.php?product=<?= $product ?>&action=view&id=<?= $row['no'] ?>" class="btn btn-secondary">보기</a>
                            <a href="product_manager.php?product=<?= $product ?>&action=edit&id=<?= $row['no'] ?>" class="btn btn-primary">수정</a>
                            <a href="product_manager.php?product=<?= $product ?>&action=delete&id=<?= $row['no'] ?>" class="btn btn-danger" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="product_manager.php?product=<?= $product ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('dataTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let found = false;
                const td = tr[i].getElementsByTagName('td');

                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>