<?php
/**
 * 주문 정보 디버그 파일
 * 특정 주문의 상세 정보를 확인하기 위한 디버그 도구
 */

include "../../db.php";

$no = $_GET['no'] ?? 83223; // 기본값으로 문제가 된 주문 번호 사용

echo "<h2>🔍 주문 정보 디버그 (주문번호: $no)</h2>";

// 주문 정보 조회
$stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
$stmt->bind_param("i", $no);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<h3>📋 주문 기본 정보</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>필드명</th><th>값</th></tr>";
    
    foreach ($row as $key => $value) {
        $display_value = htmlspecialchars($value ?? '');
        if (empty($display_value)) {
            $display_value = '<span style="color: red;">[비어있음]</span>';
        }
        echo "<tr><td><strong>$key</strong></td><td>$display_value</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>📁 파일 정보</h3>";
    $thing_cate = $row['ThingCate'];
    
    if (!empty($thing_cate)) {
        echo "<p><strong>DB에 저장된 파일명:</strong> $thing_cate</p>";
        
        // 파일 경로들 확인
        $file_paths = [
            "주문 폴더" => "../../MlangOrder_PrintAuto/upload/$no/$thing_cate",
            "구형 ImgFolder" => "../../ImgFolder/" . ($row['ImgFolder'] ?? '') . "/$thing_cate"
        ];
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>경로 유형</th><th>파일 경로</th><th>존재 여부</th><th>파일 크기</th></tr>";
        
        foreach ($file_paths as $type => $path) {
            $exists = file_exists($path);
            $size = $exists ? filesize($path) : 0;
            $size_mb = $size > 0 ? round($size / 1024 / 1024, 2) . 'MB' : '-';
            
            $status = $exists ? 
                '<span style="color: green;">✅ 존재함</span>' : 
                '<span style="color: red;">❌ 없음</span>';
                
            echo "<tr>";
            echo "<td><strong>$type</strong></td>";
            echo "<td><code>$path</code></td>";
            echo "<td>$status</td>";
            echo "<td>$size_mb</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 주문 폴더 내 모든 파일 확인
        $order_dir = "../../MlangOrder_PrintAuto/upload/$no";
        echo "<h4>📂 주문 폴더 내 모든 파일</h4>";
        
        if (is_dir($order_dir)) {
            $files = scandir($order_dir);
            $file_count = 0;
            
            echo "<ul>";
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && is_file("$order_dir/$file")) {
                    $file_count++;
                    $file_size = filesize("$order_dir/$file");
                    $file_size_mb = round($file_size / 1024 / 1024, 2);
                    
                    echo "<li>";
                    echo "<strong>$file</strong> ({$file_size_mb}MB)";
                    echo " - <a href='download.php?no=$no&downfile=" . urlencode($file) . "' target='_blank'>다운로드 테스트</a>";
                    echo "</li>";
                }
            }
            echo "</ul>";
            
            if ($file_count == 0) {
                echo "<p style='color: red;'>❌ 주문 폴더에 파일이 없습니다.</p>";
            } else {
                echo "<p style='color: green;'>✅ 총 $file_count 개의 파일이 있습니다.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ 주문 폴더가 존재하지 않습니다: <code>$order_dir</code></p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ DB에 파일명이 저장되지 않았습니다.</p>";
    }
    
    echo "<h3>🔗 다운로드 링크 테스트</h3>";
    if (!empty($thing_cate)) {
        $download_url = "download.php?no=$no&downfile=" . urlencode($thing_cate);
        echo "<p><a href='$download_url' target='_blank' style='color: blue; font-weight: bold;'>📥 파일 다운로드 테스트</a></p>";
    } else {
        echo "<p style='color: red;'>다운로드할 파일이 없습니다.</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ 주문 번호 $no 를 찾을 수 없습니다.</p>";
}

$stmt->close();
$db->close();

echo "<hr>";
echo "<p><a href='admin.php?mode=OrderView&no=$no'>🔙 관리자 페이지로 돌아가기</a></p>";
?>