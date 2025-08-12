<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경

function showError($message) {
    echo ("<script language='javascript'>
        window.alert('$message');
        window.history.back();
        </script>");
    exit;
}

$no = isset($_GET['no']) ? intval($_GET['no']) : 0;

if ($no > 0) {
    $stmt = $conn->prepare("SELECT * FROM MlangPrintAuto_cadarok WHERE no = ?");
    $stmt->bind_param("i", $no);
    
    if (!$stmt->execute()) {
        showError('DB 쿼리 실행 오류입니다.');
    }

    $result = $stmt->get_result();
    $MlangPrintAutoFild_row = $result->fetch_assoc();

    if ($MlangPrintAutoFild_row) {
        $MlangPrintAutoFildView_style = $MlangPrintAutoFild_row['style'];
        $MlangPrintAutoFildView_Section = $MlangPrintAutoFild_row['Section'];
        $MlangPrintAutoFildView_quantity = $MlangPrintAutoFild_row['quantity'];
        $MlangPrintAutoFildView_money = $MlangPrintAutoFild_row['money'];
        $MlangPrintAutoFildView_TreeSelect = $MlangPrintAutoFild_row['TreeSelect'];
        $MlangPrintAutoFildView_DesignMoney = $MlangPrintAutoFild_row['DesignMoney'];
        $MlangPrintAutoFildView_POtype = $MlangPrintAutoFild_row['POtype'];
        $MlangPrintAutoFildView_quantityTwo = $MlangPrintAutoFild_row['quantityTwo'];
    } else {
        showError("입력번호: $no 에 해당하는 데이터가 없거나 DB 오류입니다.");
    }

    $stmt->close();
} else {
    showError('입력번호가 유효하지 않습니다.');
}

$conn->close();
?>
