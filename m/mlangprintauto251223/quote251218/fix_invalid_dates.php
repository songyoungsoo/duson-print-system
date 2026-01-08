<?php
/**
 * 잘못된 유효기간 자동 수정 스크립트
 *
 * 용도: valid_until이 잘못 저장된 견적서를 일괄 수정
 * 실행: http://dsp1830.shop/mlangprintauto/quote/fix_invalid_dates.php
 *
 * 안전 모드:
 * - ?dry_run=1 : 수정하지 않고 확인만
 * - ?execute=1 : 실제 수정 실행
 */

session_start();
require_once __DIR__ . '/../db.php';

// 관리자 권한 확인 (선택사항)
// if (!isset($_SESSION['admin_logged_in'])) {
//     die('관리자 권한이 필요합니다.');
// }

$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] === '1';
$execute = isset($_GET['execute']) && $_GET['execute'] === '1';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 유효기간 수정</title>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #03C75A;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: #03C75A;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #03C75A;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #03C75A;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        .old-value {
            color: #dc3545;
            text-decoration: line-through;
        }
        .new-value {
            color: #28a745;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📅 견적서 유효기간 자동 수정</h1>

        <?php
        if (!$dryRun && !$execute) {
            ?>
            <div class="warning">
                <strong>⚠️ 주의사항</strong>
                <ul>
                    <li>이 스크립트는 잘못된 유효기간을 가진 견적서를 자동으로 수정합니다.</li>
                    <li>실행 전 반드시 <strong>데이터베이스 백업</strong>을 권장합니다.</li>
                    <li>먼저 "미리보기 모드"로 확인 후 "실제 수정" 버튼을 클릭하세요.</li>
                </ul>
            </div>
            <a href="?dry_run=1" class="btn btn-warning">🔍 미리보기 (수정하지 않음)</a>
            <a href="index.php" class="btn btn-secondary">← 견적서 목록으로</a>
            <?php
            exit;
        }

        // 잘못된 유효기간을 가진 견적서 찾기
        $query = "SELECT id, quote_no, valid_until, valid_days, created_at, status
                  FROM quotes
                  WHERE valid_until IS NULL
                     OR valid_until = ''
                     OR valid_until = '0000-00-00'
                     OR valid_until < '1900-01-01'
                  ORDER BY created_at DESC";

        $result = mysqli_query($db, $query);

        if (!$result) {
            echo '<div class="error">쿼리 실패: ' . htmlspecialchars(mysqli_error($db)) . '</div>';
            exit;
        }

        $invalidQuotes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $invalidQuotes[] = $row;
        }

        $totalInvalid = count($invalidQuotes);

        ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalInvalid; ?></div>
                <div class="stat-label">잘못된 견적서 수</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $dryRun ? '미리보기' : '실제 수정'; ?></div>
                <div class="stat-label">실행 모드</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo date('Y-m-d H:i:s'); ?></div>
                <div class="stat-label">실행 시간</div>
            </div>
        </div>

        <?php
        if ($totalInvalid === 0) {
            echo '<div class="success"><strong>✅ 모든 견적서의 유효기간이 정상입니다!</strong></div>';
            echo '<a href="index.php" class="btn btn-primary">← 견적서 목록으로</a>';
            exit;
        }

        if ($dryRun) {
            echo '<div class="warning">';
            echo '<strong>🔍 미리보기 모드</strong><br>';
            echo '아래 견적서들이 수정됩니다. 데이터는 변경되지 않습니다.';
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '<strong>✅ 수정 실행 중...</strong>';
            echo '</div>';
        }

        $fixedCount = 0;
        $errorCount = 0;

        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>견적번호</th>
                    <th>작성일시</th>
                    <th>유효기간 (일)</th>
                    <th>이전 유효기간</th>
                    <th>→ 수정 후</th>
                    <th>상태</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($invalidQuotes as $quote) {
                    $id = intval($quote['id']);
                    $quoteNo = htmlspecialchars($quote['quote_no']);
                    $validDays = intval($quote['valid_days'] ?? 7);
                    $createdAt = $quote['created_at'];
                    $oldValue = htmlspecialchars($quote['valid_until'] ?? 'NULL');
                    $status = htmlspecialchars($quote['status']);

                    // 새 유효기간 계산: created_at + valid_days
                    $newValidUntil = date('Y-m-d', strtotime($createdAt . ' +' . $validDays . ' days'));

                    echo '<tr>';
                    echo '<td>' . $id . '</td>';
                    echo '<td>' . $quoteNo . '</td>';
                    echo '<td>' . date('Y-m-d H:i', strtotime($createdAt)) . '</td>';
                    echo '<td>' . $validDays . '</td>';
                    echo '<td class="old-value">' . $oldValue . '</td>';
                    echo '<td class="new-value">' . $newValidUntil . '</td>';

                    // 실제 수정 실행
                    if ($execute) {
                        $updateQuery = "UPDATE quotes SET valid_until = ? WHERE id = ?";
                        $stmt = mysqli_prepare($db, $updateQuery);
                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, "si", $newValidUntil, $id);
                            if (mysqli_stmt_execute($stmt)) {
                                echo '<td style="color: #28a745;">✅ 수정 완료</td>';
                                $fixedCount++;
                            } else {
                                echo '<td style="color: #dc3545;">❌ 실패</td>';
                                $errorCount++;
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            echo '<td style="color: #dc3545;">❌ 쿼리 준비 실패</td>';
                            $errorCount++;
                        }
                    } else {
                        echo '<td style="color: #ffc107;">⏸️ 미리보기</td>';
                    }

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <?php
        if ($execute) {
            echo '<div class="success">';
            echo '<h3>✅ 수정 완료</h3>';
            echo '<ul>';
            echo '<li>총 대상: ' . $totalInvalid . '개</li>';
            echo '<li>성공: ' . $fixedCount . '개</li>';
            echo '<li>실패: ' . $errorCount . '개</li>';
            echo '</ul>';
            echo '</div>';
            echo '<a href="index.php" class="btn btn-primary">← 견적서 목록으로</a>';
            echo '<a href="?dry_run=1" class="btn btn-secondary">🔄 다시 확인</a>';
        } else {
            echo '<div style="margin-top: 20px;">';
            echo '<a href="?execute=1" class="btn btn-primary">✅ 실제 수정 실행 (' . $totalInvalid . '개)</a>';
            echo '<a href="index.php" class="btn btn-secondary">← 취소</a>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
