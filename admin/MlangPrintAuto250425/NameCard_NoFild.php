<?php
include "../../db.php";
$db = new mysqli($host, $user, $password, $dataname);
// 데이터베이스 연결 오류 처리
if (!$db) {
    die("<script language='javascript'>
            window.alert('▒ ERROR - 데이터베이스에 연결할 수 없습니다: " . mysqli_connect_error() . "');
            window.self.close();
         </script>");
}

// $no 변수 초기화
$no = isset($_GET['no']) ? htmlspecialchars($_GET['no']) : '';

if (!$no) {
    echo ("<script language='javascript'>
            window.alert('▒ ERROR - 등록번호가 유효하지 않습니다.');
            window.self.close();
           </script>");
    exit;
}

// 준비된 문을 사용한 쿼리
$stmt = mysqli_prepare($db, "SELECT * FROM MlangPrintAuto_NameCard WHERE no = ?");
mysqli_stmt_bind_param($stmt, "s", $no);

// 쿼리 실행 및 결과 처리
if (mysqli_stmt_execute($stmt)) {
    $MlangPrintAutoFild_result = mysqli_stmt_get_result($stmt);
    $TreeSelect = $_GET['TreeSelect'] ?? $_POST['TreeSelect'] ?? ''; // 기본값 '' 또는 null

    if ($MlangPrintAutoFild_result) {
        $MlangPrintAutoFild_row = mysqli_fetch_array($MlangPrintAutoFild_result);

        if ($MlangPrintAutoFild_row) {
            $MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'];
            $MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'];
            $MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'];
            $MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'];
            $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'] ?? '';
            $MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'];
            $MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'];
            $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'] ?? 0;
        } else {
            echo ("<script language='javascript'>
                    window.alert('▒ ERROR - 등록번호: $no 번에 관련된 자료가 없거나 DB 에러일 수 있습니다.');
                    window.self.close();
                   </script>");
            exit;
        }
    } else {
        echo ("<script language='javascript'>
                window.alert('▒ ERROR - 쿼리 실행에 실패했습니다.');
                window.self.close();
               </script>");
        exit;
    }
} else {
    echo ("<script language='javascript'>
            window.alert('▒ ERROR - 쿼리 준비에 실패했습니다.');
            window.self.close();
           </script>");
    exit;
}

// 준비된 문 종료 및 데이터베이스 연결 종료
mysqli_stmt_close($stmt);
mysqli_close($db);
?>
