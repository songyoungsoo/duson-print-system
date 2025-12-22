<?php
include "db.php";

echo "<h2>Cadarok 카테고리 구조 확인</h2>";

// 1단계: 구분 (BigNo='0')
$result1 = mysqli_query($db, "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE Ttable='cadarok' AND BigNo='0' ORDER BY no");
echo "<h3>1단계 - 구분 (BigNo='0')</h3>";
echo "<table border='1'><tr><th>no</th><th>title</th><th>BigNo</th><th>TreeNo</th></tr>";
while($row = mysqli_fetch_assoc($result1)) {
    echo "<tr><td>{$row['no']}</td><td>{$row['title']}</td><td>{$row['BigNo']}</td><td>{$row['TreeNo']}</td></tr>";
    
    // 2단계: 규격 (BigNo = 1단계 no)
    $result2 = mysqli_query($db, "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE BigNo='{$row['no']}' ORDER BY no LIMIT 5");
    if(mysqli_num_rows($result2) > 0) {
        echo "<tr><td colspan='4' style='background:#eee;'><b>└─ 2단계 규격 (BigNo={$row['no']})</b></td></tr>";
        while($row2 = mysqli_fetch_assoc($result2)) {
            echo "<tr><td>&nbsp;&nbsp;{$row2['no']}</td><td>&nbsp;&nbsp;{$row2['title']}</td><td>{$row2['BigNo']}</td><td>{$row2['TreeNo']}</td></tr>";
            
            // 3단계: 종이종류 (TreeNo = 1단계 no)
            $result3 = mysqli_query($db, "SELECT no, title, BigNo, TreeNo FROM mlangprintauto_transactioncate WHERE TreeNo='{$row['no']}' ORDER BY no LIMIT 3");
            if(mysqli_num_rows($result3) > 0) {
                echo "<tr><td colspan='4' style='background:#ddd;'><b>&nbsp;&nbsp;&nbsp;&nbsp;└─ 3단계 종이종류 (TreeNo={$row['no']})</b></td></tr>";
                while($row3 = mysqli_fetch_assoc($result3)) {
                    echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;{$row3['no']}</td><td>&nbsp;&nbsp;&nbsp;&nbsp;{$row3['title']}</td><td>{$row3['BigNo']}</td><td>{$row3['TreeNo']}</td></tr>";
                }
            }
        }
    }
}
echo "</table>";

mysqli_close($db);
?>
