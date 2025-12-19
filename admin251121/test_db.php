<?php
// DB 연결 테스트
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>DB 연결 테스트</h1>";

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
    echo "<p style='color: green;'>✅ db.php 로드 성공</p>";
    
    if (isset($conn)) {
        echo "<p style='color: green;'>✅ DB 연결 객체 존재</p>";
        
        // 테이블 확인
        $result = $conn->query("SHOW TABLES LIKE 'roll_sticker_settings'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ roll_sticker_settings 테이블 존재</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ roll_sticker_settings 테이블 없음</p>";
            echo "<p><a href='create_settings_table.php'>테이블 생성하기</a></p>";
        }
        
        $conn->close();
    } else {
        echo "<p style='color: red;'>❌ DB 연결 객체 없음</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류: " . $e->getMessage() . "</p>";
}
?>
