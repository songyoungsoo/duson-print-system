<?php
/**
 * 데이터베이스 최적화 도구
 * PHP를 통한 데이터베이스 성능 개선
 */

require_once 'db.php';

// 실행 시간 제한 해제
set_time_limit(0);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>DB 최적화 도구</title>
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .warning { background: #fff3cd; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>duson1830 데이터베이스 최적화 도구</h1>
    
    <div class="warning">
        ⚠️ <strong>주의:</strong> 최적화 작업 전 반드시 데이터베이스 백업을 수행하세요!
    </div>

    <?php
    // 1. 데이터베이스 연결 개선
    function improveConnection($db) {
        // 연결 타임아웃 설정
        mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        
        // 영구 연결 사용 (필요시)
        // $db = mysqli_connect('p:localhost', 'user', 'pass', 'db');
        
        // UTF-8 설정 최적화
        mysqli_set_charset($db, "utf8mb4");
        
        return true;
    }

    // 2. 테이블 통계 확인
    function getTableStats($db) {
        $query = "
            SELECT 
                table_name AS 'Table',
                table_rows AS 'Rows',
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size_MB',
                ROUND((data_length / 1024 / 1024), 2) AS 'Data_MB',
                ROUND((index_length / 1024 / 1024), 2) AS 'Index_MB',
                engine AS 'Engine'
            FROM information_schema.tables
            WHERE table_schema = 'duson1830'
            ORDER BY (data_length + index_length) DESC
            LIMIT 20
        ";
        
        $result = mysqli_query($db, $query);
        echo "<div class='section'>";
        echo "<h2>주요 테이블 통계</h2>";
        echo "<table>";
        echo "<tr><th>테이블</th><th>행 수</th><th>전체 크기(MB)</th><th>데이터(MB)</th><th>인덱스(MB)</th><th>엔진</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>{$row['Table']}</td>";
            echo "<td>" . number_format($row['Rows']) . "</td>";
            echo "<td>{$row['Size_MB']}</td>";
            echo "<td>{$row['Data_MB']}</td>";
            echo "<td>{$row['Index_MB']}</td>";
            echo "<td>{$row['Engine']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }

    // 3. 느린 쿼리 감지
    function detectSlowQueries($db) {
        // 최근 실행된 쿼리 중 실행 시간이 긴 것들 확인
        $queries = [
            "SELECT * FROM mlangorder_printauto WHERE date > DATE_SUB(NOW(), INTERVAL 30 DAY)",
            "SELECT * FROM shop_temp WHERE session_id IN (SELECT session_id FROM shop_temp GROUP BY session_id)",
            "SELECT COUNT(*) FROM mlangprintauto_namecard"
        ];
        
        echo "<div class='section'>";
        echo "<h2>쿼리 성능 테스트</h2>";
        
        foreach ($queries as $query) {
            $start = microtime(true);
            $result = mysqli_query($db, "EXPLAIN " . $query);
            $end = microtime(true);
            $time = round(($end - $start) * 1000, 2);
            
            echo "<div>";
            echo "<strong>쿼리:</strong> " . substr($query, 0, 100) . "...<br>";
            echo "<span class='info'>실행 시간: {$time}ms</span><br>";
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['Extra'] && strpos($row['Extra'], 'Using filesort') !== false) {
                        echo "<span class='error'>⚠️ 파일 정렬 사용 - 인덱스 필요</span><br>";
                    }
                    if ($row['type'] == 'ALL') {
                        echo "<span class='error'>⚠️ 전체 테이블 스캔 - 인덱스 필요</span><br>";
                    }
                }
            }
            echo "</div><hr>";
        }
        echo "</div>";
    }

    // 4. 인덱스 분석
    function analyzeIndexes($db) {
        $tables = ['users', 'mlangorder_printauto', 'shop_temp', 'mlangprintauto_namecard'];
        
        echo "<div class='section'>";
        echo "<h2>인덱스 분석</h2>";
        
        foreach ($tables as $table) {
            echo "<h3>테이블: $table</h3>";
            
            $result = mysqli_query($db, "SHOW INDEX FROM `$table`");
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<tr><th>인덱스명</th><th>컬럼</th><th>고유</th><th>카디널리티</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>{$row['Key_name']}</td>";
                    echo "<td>{$row['Column_name']}</td>";
                    echo "<td>" . ($row['Non_unique'] == 0 ? 'Yes' : 'No') . "</td>";
                    echo "<td>{$row['Cardinality']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<span class='error'>인덱스 없음 - 추가 필요!</span>";
            }
        }
        echo "</div>";
    }

    // 5. 자동 최적화 실행
    function runOptimization($db) {
        if (isset($_POST['optimize'])) {
            echo "<div class='section'>";
            echo "<h2>최적화 실행 결과</h2>";
            
            // 주요 테이블 최적화
            $tables = [
                'users', 'mlangorder_printauto', 'shop_temp',
                'mlangprintauto_namecard', 'mlangprintauto_sticker',
                'mlangprintauto_envelope', 'mlangprintauto_littleprint'
            ];
            
            foreach ($tables as $table) {
                $result = mysqli_query($db, "OPTIMIZE TABLE `$table`");
                if ($result) {
                    echo "<span class='success'>✓ $table 테이블 최적화 완료</span><br>";
                } else {
                    echo "<span class='error'>✗ $table 테이블 최적화 실패</span><br>";
                }
                
                // ANALYZE 실행
                $result = mysqli_query($db, "ANALYZE TABLE `$table`");
                if ($result) {
                    echo "<span class='success'>✓ $table 테이블 분석 완료</span><br>";
                }
            }
            
            // 오래된 장바구니 데이터 정리
            $result = mysqli_query($db, "DELETE FROM shop_temp WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $deleted = mysqli_affected_rows($db);
            echo "<br><span class='info'>오래된 장바구니 데이터 {$deleted}개 삭제</span><br>";
            
            echo "</div>";
        }
    }

    // 6. 추천 인덱스 생성
    function suggestIndexes($db) {
        echo "<div class='section'>";
        echo "<h2>추천 인덱스</h2>";
        
        $suggestions = [
            "ALTER TABLE `users` ADD INDEX `idx_userid` (`userid`)" => "사용자 로그인 속도 개선",
            "ALTER TABLE `mlangorder_printauto` ADD INDEX `idx_date` (`date`)" => "주문 날짜 검색 속도 개선",
            "ALTER TABLE `mlangorder_printauto` ADD INDEX `idx_name_phone` (`name`, `phone`)" => "주문자 검색 속도 개선",
            "ALTER TABLE `shop_temp` ADD INDEX `idx_session_id` (`session_id`)" => "장바구니 조회 속도 개선",
            "ALTER TABLE `mlangprintauto_namecard` ADD INDEX `idx_type_jong` (`MY_type`, `jong`)" => "명함 가격 조회 속도 개선"
        ];
        
        echo "<ul>";
        foreach ($suggestions as $query => $benefit) {
            echo "<li>";
            echo "<strong>효과:</strong> $benefit<br>";
            echo "<code>$query</code>";
            echo "</li><br>";
        }
        echo "</ul>";
        echo "</div>";
    }

    // 실행
    improveConnection($db);
    getTableStats($db);
    detectSlowQueries($db);
    analyzeIndexes($db);
    suggestIndexes($db);
    runOptimization($db);
    ?>

    <div class="section">
        <h2>최적화 실행</h2>
        <form method="post">
            <button type="submit" name="optimize" onclick="return confirm('최적화를 실행하시겠습니까? 백업을 먼저 하세요!')">
                🚀 최적화 실행
            </button>
        </form>
    </div>

    <div class="section">
        <h2>추가 최적화 팁</h2>
        <ol>
            <li><strong>정기적인 최적화:</strong> 매주 또는 매월 OPTIMIZE TABLE 실행</li>
            <li><strong>쿼리 캐시 활용:</strong> my.ini에서 query_cache_size 설정</li>
            <li><strong>인덱스 관리:</strong> 자주 검색되는 컬럼에 인덱스 추가</li>
            <li><strong>불필요한 데이터 정리:</strong> 오래된 로그, 임시 데이터 삭제</li>
            <li><strong>테이블 엔진 최적화:</strong> MyISAM → InnoDB 전환 고려</li>
            <li><strong>연결 풀링:</strong> 영구 연결 사용으로 연결 오버헤드 감소</li>
            <li><strong>쿼리 최적화:</strong> SELECT * 대신 필요한 컬럼만 선택</li>
            <li><strong>정규화:</strong> 중복 데이터 제거로 저장 공간 절약</li>
        </ol>
    </div>

    <div class="section">
        <h2>MySQL 설정 최적화 (my.ini)</h2>
        <pre>
[mysqld]
# 기본 성능 설정
key_buffer_size = 256M
max_allowed_packet = 64M
table_open_cache = 2000
sort_buffer_size = 2M
read_buffer_size = 2M

# 쿼리 캐시
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# InnoDB 설정 (InnoDB 사용 시)
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_method = O_DIRECT

# 느린 쿼리 로그
slow_query_log = 1
slow_query_log_file = slow_query.log
long_query_time = 2
        </pre>
    </div>

</body>
</html>