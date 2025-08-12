<?php
session_start();
$HomeDir = "../../";
include "../lib/func.php";
$connect = dbconn();

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// GET 파라미터에서 no 값 받기
$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

if ($no > 0) {
    // Prepared Statement를 사용한 안전한 삭제
    $query = "DELETE FROM shop_temp WHERE no = ?";
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $no);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            mysqli_stmt_close($stmt);
            mysqli_close($connect);
            
            // 성공 메시지와 함께 리다이렉트
            echo "<script>
                alert('주문 항목이 삭제되었습니다.');
                location.href = 'view_modern.php';
            </script>";
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($connect);
            
            // 오류 메시지
            echo "<script>
                alert('삭제 중 오류가 발생했습니다.');
                history.back();
            </script>";
        }
    } else {
        mysqli_close($connect);
        
        echo "<script>
            alert('데이터베이스 오류가 발생했습니다.');
            history.back();
        </script>";
    }
} else {
    if ($connect) {
        mysqli_close($connect);
    }
    
    echo "<script>
        alert('잘못된 요청입니다.');
        history.back();
    </script>";
}
?>