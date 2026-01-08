<?php
function ERROR(){
    echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

if($TRYCobe == "ok"){
    if($TypeOne == "========"){  
        ERROR(); 
    } else if($TypeTwo == "========"){  
        ERROR(); 
    } else if($TypeTree == "========"){  
        ERROR(); 
    }

    $Ttable = "cadarok";
    include "../../db.php";

    if (!$db) {
        die("데이터베이스 연결 오류: " . mysqli_connect_error());
    }

    // mysqli 방식으로 쿼리 실행
    $query = "SELECT * FROM mlangprintauto_$Ttable WHERE style='$TypeOne' AND Section='$TypeTwo' AND TreeSelect='$TypeTree' ORDER BY quantity ASC";
    $result = mysqli_query($db, $query);

    if (!$result) {
        die("쿼리 실행 오류: " . mysqli_error($db));
    }

    $rows = mysqli_num_rows($result);
?>

<script>
    var obj = parent.document.forms["choiceForm"].MY_amount;
    var i;

    // 기존 옵션 제거
    for (i = parent.document.forms["choiceForm"].MY_amount.options.length; i >= 0; i--) {
        parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
    }

<?php
    if($rows > 0) {
        $g = 0;
        while ($row = mysqli_fetch_assoc($result)) {
?>

<?php if($row['quantity'] == "9999") { ?>
    obj.options[<?=$g?>] = new Option('기타', '<?=$row['quantity']?>');
<?php } else { ?>
    obj.options[<?=$g?>] = new Option('<?=$row['quantity']?>부', '<?=$row['quantity']?>');
<?php } ?>

<?php
$g++;  } 
}else{
}?> 

</script>

<?php
    // 연결 종료
    mysqli_close($db);
}
?>