<?php
/**
 * Envelope 테이블 구조 확인
 */

require_once 'bootstrap.php';

echo "<h2>Envelope 테이블 구조 확인</h2>";

try {
    $dbManager = new DatabaseManager($db);
    
    // 테이블 구조 확인
    echo "<h3>테이블 구조</h3>";
    $structure = $dbManager->executeQuery("DESCRIBE " . ENVELOPE_TABLE);
    
    if (!empty($structure)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($structure[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($structure as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // quantityTwo 필드 존재 여부 확인
    $hasQuantityTwo = false;
    foreach ($structure as $field) {
        if ($field['Field'] === 'quantityTwo') {
            $hasQuantityTwo = true;
            break;
        }
    }
    
    echo "<h3>quantityTwo 필드 상태</h3>";
    if ($hasQuantityTwo) {
        echo "<p style='color: green;'>✓ quantityTwo 필드가 존재합니다.</p>";
        
        // quantityTwo 데이터 샘플 확인
        $sampleData = $dbManager->executeQuery("SELECT style, Section, quantity, quantityTwo FROM " . ENVELOPE_TABLE . " WHERE quantityTwo IS NOT NULL AND quantityTwo != '' LIMIT 5");
        
        if (!empty($sampleData)) {
            echo "<h4>quantityTwo 데이터 샘플</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th style='padding: 5px; background-color: #f0f0f0;'>style</th><th style='padding: 5px; background-color: #f0f0f0;'>Section</th><th style='padding: 5px; background-color: #f0f0f0;'>quantity</th><th style='padding: 5px; background-color: #f0f0f0;'>quantityTwo</th></tr>";
            
            foreach ($sampleData as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>quantityTwo 필드에 데이터가 없습니다.</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ quantityTwo 필드가 존재하지 않습니다.</p>";
        echo "<p>기존 price_cal.php에서 사용하던 quantityTwo 필드가 없어서 quantity_display가 비어있습니다.</p>";
    }
    
    // 전체 데이터 샘플
    echo "<h3>전체 데이터 샘플 (처음 5개)</h3>";
    $allData = $dbManager->executeQuery("SELECT * FROM " . ENVELOPE_TABLE . " LIMIT 5");
    
    if (!empty($allData)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($allData[0]) as $column) {
            echo "<th style='padding: 5px; background-color: #f0f0f0;'>{$column}</th>";
        }
        echo "</tr>";
        
        foreach ($allData as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #ffe8e8; padding: 15px; border: 1px solid #ff0000;'>";
    echo "<strong>에러:</strong> " . $e->getMessage();
    echo "</div>";
}
?>