<?php
/**
 * Envelope 데이터베이스 데이터 확인
 */

require_once 'bootstrap.php';

echo "<h2>Envelope 데이터베이스 데이터 확인</h2>";

try {
    $dbManager = new DatabaseManager($db);
    
    // 카테고리 테이블의 envelope 관련 데이터 확인
    echo "<h3>카테고리 테이블 (envelope 관련)</h3>";
    $categoryQuery = "SELECT * FROM " . CATEGORY_TABLE . " WHERE Ttable = 'envelope' ORDER BY BigNo, no";
    $categories = $dbManager->executeQuery($categoryQuery);
    
    if (!empty($categories)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($categories[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($categories as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>카테고리 테이블에 envelope 관련 데이터가 없습니다.</p>";
        
        // 전체 카테고리 테이블 확인
        echo "<h4>전체 카테고리 테이블 샘플 (처음 10개)</h4>";
        $allCategories = $dbManager->executeQuery("SELECT * FROM " . CATEGORY_TABLE . " LIMIT 10");
        
        if (!empty($allCategories)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach (array_keys($allCategories[0]) as $column) {
                echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
            }
            echo "</tr>";
            
            foreach ($allCategories as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // envelope 테이블 확인
    echo "<h3>Envelope 테이블</h3>";
    $envelopeQuery = "SELECT * FROM " . ENVELOPE_TABLE . " LIMIT 10";
    $envelopes = $dbManager->executeQuery($envelopeQuery);
    
    if (!empty($envelopes)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($envelopes[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($envelopes as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Envelope 테이블에 데이터가 없습니다.</p>";
    }
    
    // 테이블 존재 여부 확인
    echo "<h3>테이블 존재 여부 확인</h3>";
    $tables = [CATEGORY_TABLE, ENVELOPE_TABLE];
    
    foreach ($tables as $table) {
        $checkQuery = "SHOW TABLES LIKE '{$table}'";
        $result = $dbManager->executeQuery($checkQuery);
        
        if (!empty($result)) {
            echo "<p style='color: green;'>✓ {$table} 테이블이 존재합니다.</p>";
        } else {
            echo "<p style='color: red;'>✗ {$table} 테이블이 존재하지 않습니다.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<strong>에러:</strong> " . $e->getMessage();
    echo "</div>";
}
?>