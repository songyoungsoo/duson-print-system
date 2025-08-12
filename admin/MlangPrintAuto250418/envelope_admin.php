<?php
// =====[ 전처리: Request 변수 초기화 + DB 연결 ]=====
include "../../db.php";
include "../config.php";

$mode           = $_REQUEST['mode'] ?? '';
$code           = $_REQUEST['code'] ?? '';
$no             = $_REQUEST['no'] ?? '';
$RadOne         = $_REQUEST['RadOne'] ?? '';
$myList         = $_REQUEST['myList'] ?? '';
$quantity       = $_REQUEST['quantity'] ?? '';
$money          = $_REQUEST['money'] ?? '';
$TDesignMoney   = $_REQUEST['TDesignMoney'] ?? '';
$POtype         = $_REQUEST['POtype'] ?? '';
$Ttable         = $_REQUEST['Ttable'] ?? '';
$T_DirUrl       = "../../MlangPrintAuto";
$T_TABLE        = "envelope";
$TABLE          = "MlangPrintAuto_{$T_TABLE}";
$T_DirFole      = "{$T_DirUrl}/{$T_TABLE}/inc.php";

include "{$T_DirUrl}/ConDb.php";

////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode === "form") {
    include "../title.php";
    include $T_DirFole;
    $Bgcolor1 = "408080";

    if ($code === "Modify") {
        include "./{$T_TABLE}_NoFild.php";
    }
?>
<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script>
var NUM = "0123456789."; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) return false;
    }
    return true;
}

function MemberXCheckField() {
    var f = document.myForm;

    if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
        alert("<?php echo  $View_TtableC ?> [구분] 을 선택하여주세요!!");
        f.RadOne.focus();
        return false;
    }

    if (f.myList.value == "#" || f.myList.value == "==================") {
        alert("<?php echo  $View_TtableC ?>[종류] 을 선택하여주세요!!");
        f.myList.focus();
        return false;
    }

    if (f.quantity.value === "" || !TypeCheck(f.quantity.value, NUM)) {
        alert("수량은 숫자로 입력해 주세요.");
        f.quantity.focus();
        return false;
    }

    if (f.money.value === "" || !TypeCheck(f.money.value, NUM)) {
        alert("가격은 숫자로 입력해 주세요.");
        f.money.focus();
        return false;
    }

    if (f.TDesignMoney.value === "" || !TypeCheck(f.TDesignMoney.value, NUM)) {
        alert("디자인비는 숫자로 입력해 주세요.");
        f.TDesignMoney.focus();
        return false;
    }

    return true;
}
</script>
</head>

<body class='coolBar' marginwidth='0' marginheight='0'>

<?php if ($code === "Modify"): ?>
<b>&nbsp;&nbsp;▒ <?php echo  $View_TtableC ?> 자료 수정 ▒▒▒▒▒</b><br>
<?php else: ?>
<b>&nbsp;&nbsp;▒ <?php echo  $View_TtableC ?> 신 자료 입력 ▒▒▒▒▒</b><br>
<?php endif; ?>

<form name="myForm" method="post" onsubmit="return MemberXCheckField();" action="<?php echo  $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="mode" value="<?php echo  $code === 'Modify' ? 'Modify_ok' : 'form_ok' ?>">
<input type="hidden" name="no" value="<?php echo  $no ?>">

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
<?php include "{$T_TABLE}_Script.php"; ?>

<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">인쇄색상&nbsp;&nbsp;</td>
<td>
<select name="POtype">
    <option value='2' <?php echo  $MlangPrintAutoFildView_POtype == "2" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "" ?>>마스터2도</option>
    <option value='1' <?php echo  $MlangPrintAutoFildView_POtype == "1" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "" ?>>마스터1도</option>
    <option value='3' <?php echo  $MlangPrintAutoFildView_POtype == "3" ? "selected style='background-color:#429EB2; color:#FFFFFF;'" : "" ?>>칼라4도(옵셋)</option>
</select>
</td>
</tr>

<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" class="Left1" align="right">수량&nbsp;&nbsp;</td>
<td><input type="text" name="quantity" size="20" maxlength="20" value="<?php echo  $code === "Modify" ? $MlangPrintAutoFildView_quantity : '' ?>">매</td>
</tr>

<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" class="Left1" align="right">가격&nbsp;&nbsp;</td>
<td><input type="text" name="money" size="20" maxlength="20" value="<?php echo  $code === "Modify" ? $MlangPrintAutoFildView_money : '' ?>"></td>
</tr>

<tr>
<td bgcolor="#<?php echo  $Bgcolor1 ?>" class="Left1" align="right">디자인비&nbsp;&nbsp;</td>
<td><input type="text" name="TDesignMoney" size="20" maxlength="20" value="<?php echo  $code === "Modify" ? $MlangPrintAutoFildView_DesignMoney : $DesignMoney ?>"></td>
</tr>

<tr>
<td>&nbsp;</td>
<td><input type="submit" value="<?php echo  $code === "Modify" ? "수정 합니다." : "저장 합니다." ?>"></td>
</tr>
</table>
</form>
</body>
<?php
exit;
}

// ========== INSERT 처리 ==========
if ($mode === "form_ok") {
    $sql = "INSERT INTO {$TABLE} VALUES (
        '',
        '{$RadOne}',
        '{$myList}',
        '{$quantity}',
        '{$money}',
        '{$TDesignMoney}',
        '{$POtype}'
    )";

    $result = mysqli_query($db, $sql);

    echo "<script>alert('자료를 정상적으로 저장하였습니다.'); opener.parent.location.reload();</script>";
    echo "<meta http-equiv='refresh' content='0; url={$_SERVER['PHP_SELF']}?mode=form&Ttable={$Ttable}'>";
    exit;
}

// ========== UPDATE 처리 ==========
if ($mode === "Modify_ok") {
    $sql = "UPDATE {$TABLE} SET 
        style='{$RadOne}',
        Section='{$myList}',
        quantity='{$quantity}',
        money='{$money}',
        DesignMoney='{$TDesignMoney}',
        POtype='{$POtype}'
        WHERE no='{$no}'";

    $result = mysqli_query($db, $sql);

    if (!$result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    echo "<script>alert('정보를 정상적으로 수정하였습니다.'); opener.parent.location.reload();</script>";
    echo "<meta http-equiv='refresh' content='0; url={$_SERVER['PHP_SELF']}?mode=form&code=Modify&no={$no}&Ttable={$Ttable}'>";
    exit;
}

// ========== DELETE 처리 ==========
if ($mode === "delete") {
    mysqli_query($db, "DELETE FROM {$TABLE} WHERE no='{$no}'");
    mysqli_close($db);

    echo "<script>alert('{$no}번 자료를 삭제 처리 하였습니다.'); opener.parent.location.reload(); window.self.close();</script>";
    exit;
}
?>
<?php
if ($mode === "IncForm") {
    include $T_DirFole;
    include "../title.php";
?>
<head>
<script>
var NUM = "0123456789";
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) return false;
    }
    return true;
}

function AdminPassKleCheckField() {
    var f = document.AdminPassKleInfo;

    if (f.moeny.value === "" || !TypeCheck(f.moeny.value, NUM)) {
        alert("디자인 가격은 숫자로만 입력해 주세요.");
        f.moeny.focus();
        return false;
    }
    return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body class="coolBar" marginwidth="0" marginheight="0">
<br>
<p align="center">
<form name="AdminPassKleInfo" method="post" onsubmit="return AdminPassKleCheckField()" action="<?php echo  $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
<input type="hidden" name="mode" value="IncFormOk">

<table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
<tr><td bgcolor="#6699CC" class="td11" colspan="2">아래의 가격을 숫자로 변경 가능합니다.</td></tr>
<tr>
<td align="center">디자인 가격</td>
<td><input type="text" name="moeny" maxlength="10" size="15" value="<?php echo  $DesignMoney ?>"> 원</td>
</tr>

<tr><td bgcolor="#6699CC" class="td11" colspan="2">
<font style='color:#FFFFFF; line-height:130%;'>
※ 설명글은 마우스를 대면 출력됩니다. <br>
입력하지 않으면 자동 출력되지 않으며, <br>
기존 이미지 제거 시 체크만 하면 됩니다.
</font>
</td></tr>

<?php
$sections = [
    ['Section1', $SectionOne, 'File1', $ImgOne, 'ImeOneChick'],
    ['Section2', $SectionTwo, 'File2', $ImgTwo, 'ImeTwoChick'],
    ['Section3', $SectionTree, 'File3', $ImgTree, 'ImeTreeChick'],
    ['Section4', $SectionFour, 'File4', $ImgFour, 'ImeFourChick'],
    ['Section5', $SectionFive, 'File5', $ImgFive, 'ImeFiveChick']
];

$labels = ['구분', '종류', '인쇄색상', '수량', '디자인'];

foreach ($sections as $i => [$field, $value, $file, $img, $check]) {
    echo "<tr><td align='center'>{$labels[$i]}</td><td>
    <table border='0' width='100%' cellpadding='0' cellspacing='0'>
    <tr>
    <td align='center'><textarea name='{$field}' rows='4' cols='50'>{$value}</textarea></td>
    <td align='center'>
        <table>
        <tr><td align='center'>
        <input type='file' name='{$file}' size='20'>";
    if (!empty($img)) {
        echo "<br><input type='checkbox' name='{$check}'>이미지를 변경하시려면 체크<br>
              <input type='hidden' name='{$file}_Y' value='{$img}'><br>
              <img src='{$upload_dir}/{$img}' width='80' height='95' border='0'>";
    }
    echo "</td></tr></table>
    </td></tr></table>
    </td></tr>";
}
?>
</table>

<br>
<input type="submit" value="수정합니다">
<input type="button" value="창 닫기" onclick="window.self.close();">
</form>
</p>
</body>
<?php
exit;
}

// ========================== [ mode=IncFormOk 처리부 ] ==========================
if ($mode === "IncFormOk") {
    $moeny       = $_REQUEST['moeny'] ?? '';
    $Section1    = $_REQUEST['Section1'] ?? '';
    $Section2    = $_REQUEST['Section2'] ?? '';
    $Section3    = $_REQUEST['Section3'] ?? '';
    $Section4    = $_REQUEST['Section4'] ?? '';
    $Section5    = $_REQUEST['Section5'] ?? '';
    $File1NAME = $File2NAME = $File3NAME = $File4NAME = $File5NAME = '';

    for ($i = 1; $i <= 5; $i++) {
        $fileKey   = "File{$i}";
        $checkKey  = "Ime" . ['One','Two','Tree','Four','Five'][$i - 1] . "Chick";
        $fileOld   = $_REQUEST["{$fileKey}_Y"] ?? '';

        if ($_REQUEST[$checkKey] === "on") {
            if (!empty($_FILES[$fileKey]['name'])) {
                if ($fileOld) @unlink("{$upload_dir}/{$fileOld}");
                include "{$T_DirUrl}/Upload_{$i}.php";
            } else {
                if ($fileOld) @unlink("{$upload_dir}/{$fileOld}");
            }
        } else {
            if ($fileOld) {
                ${$fileKey . "NAME"} = $fileOld;
            } elseif (!empty($_FILES[$fileKey]['name'])) {
                include "{$T_DirUrl}/Upload_{$i}.php";
            }
        }
    }

    // 파일 쓰기
    $fp = fopen($T_DirFole, "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney = \"{$moeny}\";\n");
    fwrite($fp, "\$SectionOne = \"{$Section1}\";\n");
    fwrite($fp, "\$SectionTwo = \"{$Section2}\";\n");
    fwrite($fp, "\$SectionTree = \"{$Section3}\";\n");
    fwrite($fp, "\$SectionFour = \"{$Section4}\";\n");
    fwrite($fp, "\$SectionFive = \"{$Section5}\";\n");
    fwrite($fp, "\$ImgOne = \"{$File1NAME}\";\n");
    fwrite($fp, "\$ImgTwo = \"{$File2NAME}\";\n");
    fwrite($fp, "\$ImgTree = \"{$File3NAME}\";\n");
    fwrite($fp, "\$ImgFour = \"{$File4NAME}\";\n");
    fwrite($fp, "\$ImgFive = \"{$File5NAME}\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo "<script>alert('수정 완료... *^^*\\n\\n{$WebSoftCopyright}');</script>";
    echo "<meta http-equiv='refresh' content='0; url={$_SERVER['PHP_SELF']}?mode=IncForm'>";
    exit;
}
?>
