<?php
/**
 * DB 연결 상세 확인
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DB 연결 확인 ===\n\n";

// 1. db.php 경로 확인
$db_file = "/var/www/html/db.php";
echo "1. db.php 파일 존재: " . (file_exists($db_file) ? "예" : "아니오") . "\n";

if (file_exists($db_file)) {
    include $db_file;

    echo "\n2. \$db 변수 존재: " . (isset($db) ? "예" : "아니오") . "\n";

    if (isset($db)) {
        echo "3. \$db 타입: " . gettype($db) . "\n";

        if (is_object($db) && $db instanceof mysqli) {
            echo "4. mysqli 객체: 예\n";

            // 연결 테스트
            if ($db->ping()) {
                echo "5. DB 연결 상태: 연결됨\n";

                // 현재 DB 이름
                $result = $db->query("SELECT DATABASE() as db_name");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "6. 현재 DB: " . ($row['db_name'] ?? 'NULL') . "\n";

                    // 테이블 목록
                    echo "\n7. 테이블 목록:\n";
                    $result = $db->query("SHOW TABLES");
                    if ($result) {
                        $count = 0;
                        while ($row = $result->fetch_row()) {
                            if ($count < 10) {
                                echo "   - " . $row[0] . "\n";
                            }
                            $count++;
                        }
                        echo "   총 $count 개 테이블\n";
                    }

                    // mlangorder_printauto 테이블 확인
                    echo "\n8. mlangorder_printauto 테이블 확인:\n";
                    $result = $db->query("SELECT COUNT(*) as cnt FROM mlangorder_printauto");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo "   - 총 주문 개수: " . $row['cnt'] . "\n";

                        // 최근 주문 5개
                        $result = $db->query("SELECT no, Type, date FROM mlangorder_printauto ORDER BY no DESC LIMIT 5");
                        echo "   - 최근 주문 5개:\n";
                        while ($r = $result->fetch_assoc()) {
                            echo "     * 주문 #{$r['no']}: {$r['Type']} ({$r['date']})\n";
                        }
                    } else {
                        echo "   - 오류: " . $db->error . "\n";
                    }

                } else {
                    echo "6. DB 이름 조회 실패: " . $db->error . "\n";
                }

            } else {
                echo "5. DB 연결 상태: 연결 안됨\n";
                echo "   오류: " . $db->connect_error . "\n";
            }
        } else {
            echo "4. mysqli 객체: 아니오 (타입: " . gettype($db) . ")\n";
        }
    } else {
        echo "   \$db 변수가 설정되지 않았습니다\n";
        echo "   정의된 변수들: " . implode(", ", array_keys(get_defined_vars())) . "\n";
    }
} else {
    echo "   파일을 찾을 수 없습니다: $db_file\n";
}

echo "\n=== 확인 완료 ===\n";
?>
