<?php
function ERROR(){
    echo("<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>");
}

if ($TRYCobe == "ok") {
    if ($TypeOne == "========") { 
        ERROR(); 
    } else if ($TypeTwo == "========") { 
        ERROR(); 
    } else if ($TypeTree == "========") { 
        ERROR(); 
    }

    $Ttable = "NcrFlambeau";
    include "../../db.php";
    
    // 쿼리 실행
    $result = mysqli_query($db, "SELECT * FROM mlangprintauto_$Ttable WHERE style='$TypeOne' AND Section='$TypeTwo' AND TreeSelect='$TypeTree' ORDER BY quantity ASC");
    $rows = mysqli_num_rows($result);
?>

<script>
    var obj = parent.document.forms["choiceForm"].MY_amount;
    var i;

    // 기존 옵션 제거
    for (i = parent.document.forms["choiceForm"].MY_amount.options.length - 1; i >= 0; i--) {
        parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
    }

    <?php     
    if ($rows) {
        $g = 0;
        while ($row = mysqli_fetch_array($result)) { 
    ?>
    obj.options[<?= $g ?>] = new Option('<?= $row['quantity'] ?>매', '<?= $row['quantity'] ?>');
    <?php
            $g++;
        } 
    }
    ?> 

</script>

<?php
    mysqli_close($db); 
}
?>
