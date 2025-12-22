<?php
/**
 * 롤스티커 견적서 리스트
 * 경로: /shop/quote_list.php
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// db.php에서 $db 변수를 사용하므로 $conn으로 별칭 설정
$conn = $db;

// 페이징 설정
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 검색 조건
$search = $_GET['search'] ?? '';
$where = "";
if ($search) {
    $search_term = $conn->real_escape_string($search);
    $where = "WHERE company_name LIKE '%$search_term%' OR quote_number LIKE '%$search_term%'";
}

// 전체 개수
$total = 0;
$total_pages = 0;
$result = null;

try {
    $count_sql = "SELECT COUNT(*) as total FROM roll_sticker_quotes $where";
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $total = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total / $per_page);
        
        // 견적 리스트 조회
        $sql = "SELECT * FROM roll_sticker_quotes $where ORDER BY created_at DESC LIMIT $offset, $per_page";
        $result = $conn->query($sql);
    }
} catch (Exception $e) {
    // 테이블이 없는 경우
    $result = null;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>롤스티커 견적서 리스트 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Malgun Gothic', 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 5px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border: 1px solid #d0d0d0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .header {
            background: #4472C4;
            color: white;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2E5090;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: 600;
        }
        
        .header a {
            background: white;
            color: #4472C4;
            padding: 4px 12px;
            border-radius: 2px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }
        
        .search-box {
            padding: 8px 15px;
            background: #F2F2F2;
            border-bottom: 1px solid #d0d0d0;
        }
        
        .search-box form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .search-box input {
            flex: 1;
            padding: 4px 8px;
            border: 1px solid #d0d0d0;
            font-size: 12px;
        }
        
        .search-box button {
            padding: 4px 15px;
            background: #4472C4;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }
        
        .content {
            padding: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d0d0d0;
            font-size: 12px;
        }
        
        th, td {
            padding: 4px 8px;
            text-align: left;
            border: 1px solid #d0d0d0;
        }
        
        th {
            background: #5B9BD5;
            font-weight: 600;
            color: white;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background: #F2F2F2;
        }
        
        tr:hover {
            background: #E7F3FF;
        }
        
        .quote-number {
            color: #0066CC;
            font-weight: 600;
        }
        
        .company-name {
            font-weight: 600;
            color: #000;
        }
        
        .price {
            color: #C00000;
            font-weight: 600;
            text-align: right;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin-top: 10px;
            padding: 10px;
        }
        
        .pagination a {
            padding: 3px 8px;
            border: 1px solid #d0d0d0;
            text-decoration: none;
            color: #000;
            font-size: 11px;
            background: white;
        }
        
        .pagination a.active {
            background: #4472C4;
            color: white;
            border-color: #4472C4;
        }
        
        .pagination a:hover {
            background: #E7F3FF;
        }
        
        .view-btn {
            padding: 2px 8px;
            background: #4472C4;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 11px;
            font-size: 13px;
        }
        
        .view-btn:hover {
            background: #0d4a8a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏷️ 롤스티커 견적서 리스트</h1>
            <a href="roll_sticker_calculator.php">+ 새 견적서 작성</a>
        </div>
        
        <div class="search-box">
            <form method="get">
                <input type="text" name="search" placeholder="회사명 또는 견적번호로 검색" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">🔍 검색</button>
            </form>
        </div>
        
        <div class="content">
            <p style="margin-bottom: 20px; color: #666;">총 <?php echo number_format($total); ?>개의 견적서</p>
            
            <table>
                <thead>
                    <tr>
                        <th>견적번호</th>
                        <th>회사명</th>
                        <th>규격</th>
                        <th>매수</th>
                        <th>재질</th>
                        <th>공급가</th>
                        <th>총 금액</th>
                        <th>작성일</th>
                        <th>보기</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="quote-number"><?php echo htmlspecialchars($row['quote_number']); ?></td>
                            <td class="company-name"><?php echo htmlspecialchars($row['company_name'] ?: '-'); ?></td>
                            <td><?php echo number_format($row['width']); ?>×<?php echo number_format($row['height']); ?>mm</td>
                            <td><?php echo number_format($row['quantity']); ?>매</td>
                            <td><?php 
                                $materials = [
                                    'art' => '아트지',
                                    'yupo' => '유포지',
                                    'silver_deadlong' => '은데드롱',
                                    'clear_deadlong' => '투명데드롱',
                                    'gold_paper' => '금지',
                                    'silver_paper' => '은지',
                                    'kraft' => '크라프트',
                                    'hologram' => '홀로그램'
                                ];
                                echo $materials[$row['material']] ?? $row['material'];
                            ?></td>
                            <td><?php echo number_format($row['supply_price']); ?>원</td>
                            <td class="price"><?php echo number_format($row['total_price']); ?>원</td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="quote_view.php?id=<?php echo $row['id']; ?>" class="view-btn">상세보기</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #999;">
                                견적서가 없습니다.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
