<?php
include "../../db.php";
include "../config.php";

// 변수 초기화
$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "sticker";

// ${} 대신 {$} 사용
include "{$T_DirUrl}/ConDb.php";  
$T_DirFole = "{$T_DirUrl}/{$T_TABLE}/inc.php";  
$TABLE = "MlangPrintAuto_{$T_TABLE}";  

if (isset($mode) && $mode == "form") {
    include "../title.php";
    include "{$T_DirFole}";  
    $Bgcolor1 = "408080";

    if (isset($code) && $code == "Modify") {
        include "./{$T_TABLE}_NoFild.php";  
    }
    ?>
    <head>
    <style>
    .Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
    </style>
    <script language=javascript>
    var NUM = "0123456789."; 
    var SALPHA = "abcdefghijklmnopqrstuvwxyz";
    var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

    function TypeCheck (s, spc) {
        for (var i = 0; i < s.length; i++) {
            if (spc.indexOf(s.substring(i, i+1)) < 0) {
                return false;
            }
        }        
        return true;
    }

    function MemberXCheckField() {
        var f = document.myForm;

        if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
            alert("스타일을 선택해주세요!!");
            f.RadOne.focus();
            return false;
        }

        if (f.myList.value == "#" || f.myList.value == "==================") {
            alert("섹션을 선택해주세요!!");
            f.myList.focus();
            return false;
        }

        if (f.quantity.value == "") {
            alert("수량을 입력해주세요!!");
            f.quantity.focus();
            return false;
        }
        if (!TypeCheck(f.quantity.value, NUM)) {
            alert("수량은 숫자로 입력해 주세요.");
            f.quantity.focus();
            return false;
        }

        if (f.money.value == "") {
            alert("금액을 입력해주세요!!");
            f.money.focus();
            return false;
        }
        if (!TypeCheck(f.money.value, NUM)) {
            alert("금액은 숫자로 입력해 주세요.");
            f.money.focus();
            return false;
        }

        if (f.TDesignMoney.value == "") {
            alert("디자인비용을 입력해주세요!!");
            f.TDesignMoney.focus();
            return false;
        }
        if (!TypeCheck(f.TDesignMoney.value, NUM)) {
            alert("디자인비용은 숫자로 입력해 주세요.");
            f.TDesignMoney.focus();
            return false;
        }
    }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>

    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

    <?php if ($code == "Modify") { ?>
    <b>&nbsp;&nbsp;기 데이터 수정 아자아자</b><BR>
    <?php } else { ?>
    <b>&nbsp;&nbsp;기 신규 데이터 입력 아자아자</b><BR>
    <?php } ?>

    <table border=0 align=center width=100% cellpadding=0 cellspacing=5>

    <?php include "{$T_TABLE}_Script.php"; ?> 

    <tr>
    <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
    <td><INPUT TYPE="text" NAME="quantity" size=20 maxLength='20' <?php if ($code == "Modify") {echo("value='$MlangPrintAutoFildView_quantity'");}?>>개</td>
    </tr>

    <tr>
    <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>금액&nbsp;&nbsp;</td>
    <td><INPUT TYPE="text" NAME="money" size=20 maxLength='20' <?php if ($code == "Modify") {echo("value='$MlangPrintAutoFildView_money'");}?>></td>
    </tr>

    <tr>
    <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>디자인비용&nbsp;&nbsp;</td>
    <td><INPUT TYPE="text" NAME="TDesignMoney" size=20 maxLength='20' <?php if ($code == "Modify") {echo("value='$MlangPrintAutoFildView_DesignMoney'");} else {echo("value='$DesignMoney'");}?>></td>
    </tr>

    <tr>
    <td>&nbsp;&nbsp;</td>
    <td>
    <?php if ($code == "Modify") { ?>
    <input type='submit' value=' 수정 합니다.'>
    <?php } else { ?>
    <input type='submit' value=' 입력 합니다.'>
    <?php } ?>
    </td>
    </tr>
    </FORM>
    </table>

    <?php
} elseif (isset($mode) && $mode == "form_ok") {

    $stmt = $db->prepare("INSERT INTO {$TABLE} (style, Section, quantity, money, DesignMoney) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $RadOne, $myList, $quantity, $money, $TDesignMoney);
    $stmt->execute();

    echo ("
        <script language=javascript>
        alert('\\n데이터를 정상적으로 저장했습니다.\\n');
        opener.parent.location.reload();
        </script>
    <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=form&Ttable={$Ttable}'>
    ");
    exit;

} elseif (isset($mode) && $mode == "Modify_ok") {

    $stmt = $db->prepare("UPDATE {$TABLE} SET style = ?, Section = ?, quantity = ?, money = ?, DesignMoney = ? WHERE no = ?");
    $stmt->bind_param("sssssi", $RadOne, $myList, $quantity, $money, $TDesignMoney, $no);
    $stmt->execute();

    echo ("
        <script language=javascript>
        alert('\\n데이터를 정상적으로 수정했습니다.\\n');
        opener.parent.location.reload();
        </script>
    <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=form&code=Modify&no={$no}&Ttable={$Ttable}'>
    ");
    exit;

} elseif (isset($mode) && $mode == "delete") {

    $stmt = $db->prepare("DELETE FROM {$TABLE} WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $db->close();

    echo ("
    <html>
    <script language=javascript>
    window.alert('{$no} 번 데이터를 삭제했습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;
} elseif (isset($mode) && $mode == "IncForm") {
    include "{$T_DirFole}";  
    include "../title.php";
}
?>
