<?php
/**
 * 견적서 시스템 진단 도구
 * 데이터베이스 상태를 확인하고 필요한 조치를 안내합니다.
 *
 * 실행: http://dsp1830.shop/mlangprintauto/quote/diagnostic.php
 */

session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 시스템 진단</title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .status.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .action-box {
            background: #e7f3ff;
            border: 2px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .action-box h3 {
            margin-top: 0;
            color: #007bff;
        }
        .action-box a {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 5px 5px 0;
        }
        .action-box a:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 견적서 시스템 진단</h1>
        <p>데이터베이스 연결: <code><?php echo $dataname; ?>@<?php echo $host; ?></code></p>

        <?php
        // 1. 기본 테이블 존재 확인
        echo "<h2>1️⃣ 데이터베이스 테이블 확인</h2>";

        $required_tables = ['company_settings', 'quotes', 'quote_items', 'quote_emails'];
        $tables_status = [];

        foreach ($required_tables as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $result = mysqli_query($db, $query);
            $exists = mysqli_num_rows($result) > 0;
            $tables_status[$table] = $exists;

            if ($exists) {
                echo "<div class='status success'><span class='icon'>✅</span> <strong>$table</strong> 테이블이 존재합니다.</div>";
            } else {
                echo "<div class='status error'><span class='icon'>❌</span> <strong>$table</strong> 테이블이 존재하지 않습니다.</div>";
            }
        }

        // 2. quotes 테이블이 있는 경우 컬럼 구조 확인
        if ($tables_status['quotes']) {
            echo "<h2>2️⃣ quotes 테이블 컬럼 구조</h2>";

            $columns_query = "SHOW COLUMNS FROM quotes";
            $columns_result = mysqli_query($db, $columns_query);

            $version_columns = ['original_quote_id', 'version', 'is_latest'];
            $existing_columns = [];

            echo "<table>";
            echo "<tr><th>컬럼명</th><th>타입</th><th>Null</th><th>기본값</th><th>설명</th></tr>";

            while ($col = mysqli_fetch_assoc($columns_result)) {
                $existing_columns[] = $col['Field'];
                echo "<tr>";
                echo "<td><code>{$col['Field']}</code></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";

                if (in_array($col['Field'], $version_columns)) {
                    echo "<td><strong style='color: green;'>✅ 버전 관리 컬럼</strong></td>";
                } else {
                    echo "<td>-</td>";
                }
                echo "</tr>";
            }
            echo "</table>";

            // 버전 관리 컬럼 확인
            echo "<h3>버전 관리 컬럼 상태</h3>";
            $all_version_cols_exist = true;
            foreach ($version_columns as $vcol) {
                if (in_array($vcol, $existing_columns)) {
                    echo "<div class='status success'><span class='icon'>✅</span> <strong>$vcol</strong> 컬럼이 존재합니다.</div>";
                } else {
                    echo "<div class='status error'><span class='icon'>❌</span> <strong>$vcol</strong> 컬럼이 존재하지 않습니다.</div>";
                    $all_version_cols_exist = false;
                }
            }

            // 3. 견적서 데이터 확인
            echo "<h2>3️⃣ 견적서 데이터 확인</h2>";

            $count_query = "SELECT COUNT(*) as total FROM quotes";
            $count_result = mysqli_query($db, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $total_quotes = $count_row['total'];

            if ($total_quotes > 0) {
                echo "<div class='status success'><span class='icon'>✅</span> 총 <strong>{$total_quotes}개</strong>의 견적서가 있습니다.</div>";

                // 최근 견적서 목록
                $recent_query = "SELECT id, quote_no, quote_type, customer_name, status, created_at FROM quotes ORDER BY id DESC LIMIT 5";
                $recent_result = mysqli_query($db, $recent_query);

                echo "<h3>최근 견적서 목록</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>견적번호</th><th>유형</th><th>고객명</th><th>상태</th><th>작성일</th><th>조회</th></tr>";

                while ($quote = mysqli_fetch_assoc($recent_result)) {
                    $status_badge = [
                        'draft' => '📝 초안',
                        'sent' => '📧 발송됨',
                        'viewed' => '👀 조회됨',
                        'accepted' => '✅ 승인됨',
                        'rejected' => '❌ 거절됨',
                        'expired' => '⏰ 만료됨',
                        'converted' => '🔄 전환됨'
                    ];

                    echo "<tr>";
                    echo "<td>{$quote['id']}</td>";
                    echo "<td><code>{$quote['quote_no']}</code></td>";
                    echo "<td>{$quote['quote_type']}</td>";
                    echo "<td>{$quote['customer_name']}</td>";
                    echo "<td>{$status_badge[$quote['status']]}</td>";
                    echo "<td>" . date('Y-m-d H:i', strtotime($quote['created_at'])) . "</td>";
                    echo "<td><a href='check_status.php?id={$quote['id']}' target='_blank' style='color: #007bff;'>상태 확인</a></td>";
                    echo "</tr>";
                }
                echo "</table>";

            } else {
                echo "<div class='status warning'><span class='icon'>⚠️</span> 생성된 견적서가 없습니다.</div>";
            }

        } else {
            echo "<div class='status warning'><span class='icon'>⚠️</span> quotes 테이블이 없어 컬럼 구조를 확인할 수 없습니다.</div>";
        }

        // 4. 필요한 조치 안내
        echo "<h2>4️⃣ 필요한 조치</h2>";

        if (!$tables_status['quotes'] || !$tables_status['quote_items']) {
            echo "<div class='action-box'>";
            echo "<h3>🚀 초기 설정이 필요합니다</h3>";
            echo "<p>기본 테이블이 생성되지 않았습니다. 다음 스크립트를 실행하세요:</p>";
            echo "<a href='setup_database.php?key=setup2025' target='_blank'>1. 기본 테이블 생성 (setup_database.php)</a>";
            echo "</div>";
        } elseif ($tables_status['quotes'] && !$all_version_cols_exist) {
            echo "<div class='action-box'>";
            echo "<h3>⬆️ 버전 관리 기능 추가가 필요합니다</h3>";
            echo "<p>기본 테이블은 있지만 버전 관리 컬럼이 없습니다. 다음 스크립트를 실행하세요:</p>";
            echo "<a href='add_version_columns.php' target='_blank'>2. 버전 관리 컬럼 추가 (add_version_columns.php)</a>";
            echo "</div>";
        } else {
            echo "<div class='action-box' style='border-color: #28a745; background: #d4edda;'>";
            echo "<h3 style='color: #28a745;'>✅ 시스템이 정상적으로 설정되었습니다!</h3>";
            echo "<p>모든 테이블과 컬럼이 올바르게 생성되었습니다.</p>";
            echo "<a href='create.php'>견적서 작성하기</a>";
            echo "<a href='index.php'>견적서 목록</a>";
            echo "</div>";
        }

        // 5. 파일 업로드 확인
        echo "<h2>5️⃣ 파일 업로드 확인</h2>";

        $files_to_check = [
            'edit.php' => '견적서 수정 페이지',
            'revise.php' => '견적서 개정판 작성 페이지',
            'api/update.php' => '견적서 수정 API',
            'api/create_revision.php' => '개정판 생성 API',
            'check_status.php' => '상태 확인 디버그 페이지'
        ];

        echo "<table>";
        echo "<tr><th>파일명</th><th>설명</th><th>상태</th></tr>";

        foreach ($files_to_check as $file => $desc) {
            $file_path = __DIR__ . '/' . $file;
            $exists = file_exists($file_path);

            echo "<tr>";
            echo "<td><code>$file</code></td>";
            echo "<td>$desc</td>";
            if ($exists) {
                echo "<td><span style='color: green; font-weight: bold;'>✅ 존재</span></td>";
            } else {
                echo "<td><span style='color: red; font-weight: bold;'>❌ 없음</span></td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        // 6. 시스템 정보
        echo "<h2>6️⃣ 시스템 정보</h2>";
        echo "<div class='status info'>";
        echo "<strong>PHP 버전:</strong> " . phpversion() . "<br>";
        echo "<strong>MySQL 버전:</strong> " . mysqli_get_server_info($db) . "<br>";
        echo "<strong>데이터베이스:</strong> $dataname<br>";
        echo "<strong>현재 시각:</strong> " . date('Y-m-d H:i:s') . "<br>";
        echo "<strong>서버:</strong> " . $_SERVER['SERVER_NAME'] . "<br>";
        echo "</div>";

        mysqli_close($db);
        ?>

        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center; color: #999;">
            <p>견적서 시스템 진단 도구 v1.0</p>
            <p><a href="index.php" style="color: #007bff;">← 견적서 목록으로 돌아가기</a></p>
        </div>
    </div>
</body>
</html>
