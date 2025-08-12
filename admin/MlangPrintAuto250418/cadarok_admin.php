<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "cadarok";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";

$mode = $_GET['mode'] ?? '';
$code = $_GET['code'] ?? '';
$no = $_GET['no'] ?? '';
$Bgcolor1 = "408080";

// INC 불러오기
if (file_exists($T_DirFole)) include $T_DirFole;

// 수정일 경우 개별 데이터 불러오기
if ($mode === 'form' && $code === 'Modify') {
    $query = "SELECT * FROM {$TABLE} WHERE no = '$no'";
    $result = mysqli_query($db, $query);
    $data = mysqli_fetch_assoc($result);
}

include "../title.php";
?>

<head>
    <style>.Left1 { font-size:10pt; color:#FFFFFF; font-weight:bold; }</style>
    <script src="../js/coolbar.js"></script>
    <script>
    function TypeCheck(str, valid) {
        for (let i = 0; i < str.length; i++) {
            if (!valid.includes(str[i])) return false;
        }
        return true;
    }

    function MemberXCheckField() {
        const f = document.myForm;
        const NUM = "0123456789.";

        if (f.RadOne.value === "#") return alert("구분을 선택해주세요!"), f.RadOne.focus(), false;
        if (f.myListTreeSelect.value === "#") return alert("규격을 선택해주세요!"), f.myListTreeSelect.focus(), false;
        if (f.myList.value === "#") return alert("종이종류를 선택해주세요!"), f.myList.focus(), false;

        if (!f.quantity.value || !TypeCheck(f.quantity.value, NUM)) {
            alert("수량을 숫자로 입력해주세요!");
            f.quantity.focus(); return false;
        }

        if (!f.money.value || !TypeCheck(f.money.value, NUM)) {
            alert("가격을 숫자로 입력해주세요!");
            f.money.focus(); return false;
        }

        return true;
    }
    </script>
</head>

<body class='coolBar' leftmargin=0 topmargin=0 marginwidth=0 marginheight=0>
    <form name="myForm" method="post" action="<?php echo  $_SERVER['PHP_SELF'] ?>" onsubmit="return MemberXCheckField()">
        <input type="hidden" name="mode" value="<?php echo  $code === 'Modify' ? 'Modify_ok' : 'form_ok' ?>">
        <input type="hidden" name="no" value="<?php echo  $no ?>">
        <input type="hidden" name="Ttable" value="<?php echo  $T_TABLE ?>">

        <b>&nbsp;&nbsp;▒ <?php echo  $View_TtableC ?> <?php echo  $code === 'Modify' ? '자료 수정' : '신 자료 입력' ?> ▒▒▒▒▒</b><br><br>
        <table align="center" width="100%" cellpadding="5" cellspacing="0" border="0">

            <?php include "{$T_TABLE}_Script.php"; ?>

            <tr>
                <td class="Left1" bgcolor="#<?php echo  $Bgcolor1 ?>" align="right" width="100">수량&nbsp;&nbsp;</td>
                <td><input type="text" name="quantity" size="20" maxlength="20" value="<?php echo  $data['quantity'] ?? '' ?>"> 부</td>
            </tr>

            <tr>
                <td class="Left1" bgcolor="#<?php echo  $Bgcolor1 ?>" align="right">가격&nbsp;&nbsp;</td>
                <td><input type="text" name="money" size="20" maxlength="20" value="<?php echo  $data['money'] ?? '' ?>"></td>
            </tr>

            <tr><td colspan="2" align="center">* 기타는 숫자 수치를 9999로 입력해주세요</td></tr>

            <tr>
                <td>&nbsp;&nbsp;</td>
                <td><input type="submit" value="<?php echo  $code === 'Modify' ? '수정 합니다.' : '저장 합니다.' ?>"></td>
            </tr>
        </table>
    </form>
</body>
<?php
// 안전 필터링
$mode = $_POST['mode'] ?? '';
$T_TABLE = $_POST['Ttable'] ?? '';
$TABLE = "MlangPrintAuto_{$T_TABLE}";

$no = $_POST['no'] ?? 0;
$RadOne = $_POST['RadOne'] ?? '';
$myList = $_POST['myList'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$money = $_POST['money'] ?? '';
$TDesignMoney = $_POST['TDesignMoney'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$quantityTwo = $_POST['quantityTwo'] ?? '';

function redirectWithMessage($message, $redirectURL) {
    echo "<script>alert('{$message}'); location.href='{$redirectURL}';</script>";
    exit;
}

if ($mode === 'form_ok') {
    $query = "
        INSERT INTO {$TABLE} 
        (style, TreeSelect, quantity, money, Section, DesignMoney, POtype, quantityTwo)
        VALUES
        ('$RadOne', '$myListTreeSelect', '$quantity', '$money', '$myList', '$TDesignMoney', '$POtype', '$quantityTwo')
    ";

    if (mysqli_query($db, $query)) {
        redirectWithMessage("자료를 정상적으로 저장하였습니다.", $_SERVER['PHP_SELF'] . "?mode=form&Ttable={$T_TABLE}");
    } else {
        redirectWithMessage("DB 저장 실패!", $_SERVER['PHP_SELF']);
    }
}

if ($mode === 'Modify_ok') {
    $query = "
        UPDATE {$TABLE}
        SET
            style = '$RadOne',
            Section = '$myList',
            quantity = '$quantity',
            money = '$money',
            TreeSelect = '$myListTreeSelect',
            DesignMoney = '$TDesignMoney',
            POtype = '$POtype',
            quantityTwo = '$quantityTwo'
        WHERE no = '$no'
    ";

    if (mysqli_query($db, $query)) {
        redirectWithMessage("정보를 정상적으로 수정하였습니다.", $_SERVER['PHP_SELF'] . "?mode=form&code=Modify&no={$no}&Ttable={$T_TABLE}");
    } else {
        echo "<script>alert('DB 업데이트 오류'); history.back();</script>";
        exit;
    }
}

if ($mode === 'delete') {
    $query = "DELETE FROM {$TABLE} WHERE no = '$no'";
    if (mysqli_query($db, $query)) {
        echo "
        <script>
            alert('{$no}번 자료를 삭제 처리 하였습니다.');
            opener.parent.location.reload();
            window.close();
        </script>";
    } else {
        echo "<script>alert('삭제 실패'); window.close();</script>";
    }
    exit;
}

mysqli_close($db);
?>
<?php
// === INC 설정 편집 폼 ===
if ($mode === 'IncForm') {
    include $T_DirFole;
    include "../title.php";
    ?>
    <head>
        <script src="../js/coolbar.js"></script>
        <style>.td11 { font-weight: bold; color: white; }</style>
    </head>
    <body class="coolBar">
    <form name="AdminPassKleInfo" method="post" action="<?php echo  $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="IncFormOk">

        <table border="1" width="100%" cellpadding="5" cellspacing="0">
            <tr><td class="td11" bgcolor="#6699CC" colspan="2">카다록 정보 수정</td></tr>

            <?php
            $sections = [
                1 => '구분',
                2 => '종이종류',
                3 => '규격',
                4 => '수량',
                5 => '주문방법'
            ];
            foreach ($sections as $i => $label):
                $sectionVar = ${"Section{$i}"} ?? '';
                $imgVar = ${"Img" . ($i === 3 ? "Tree" : ($i === 2 ? "Two" : ($i === 4 ? "Four" : ($i === 5 ? "Five" : "One"))))};
                ?>
                <tr>
                    <td align="center"><?php echo  $label ?></td>
                    <td>
                        <textarea name="Section<?php echo  $i ?>" rows="4" cols="50"><?php echo  htmlspecialchars($sectionVar) ?></textarea>
                        <input type="file" name="File<?php echo  $i ?>" size="20">
                        <?php if ($imgVar): ?>
                            <br><input type="checkbox" name="Ime<?php echo  $i ?>Chick"> 기존 이미지 변경
                            <input type="hidden" name="File<?php echo  $i ?>_Y" value="<?php echo  $imgVar ?>">
                            <img src="<?php echo  $upload_dir ?>/<?php echo  $imgVar ?>" width="80" height="95" style="display:block;margin-top:5px;">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <center>
            <input type="submit" value="수정합니다">
            <input type="button" value="창 닫기" onclick="window.close();">
        </center>
    </form>
    </body>
    <?php
    exit;
}
?>
<?php
function processFileUpload($i, $upload_dir) {
    $fileKey = "File{$i}";
    $fileYKey = "File{$i}_Y";
    $checkKey = "Ime{$i}Chick";
    $uploadFile = $_FILES[$fileKey] ?? null;
    $oldFile = $_POST[$fileYKey] ?? '';
    $replace = isset($_POST[$checkKey]);
    $finalName = '';

    if ($replace && $oldFile && file_exists("$upload_dir/$oldFile")) {
        unlink("$upload_dir/$oldFile");
    }

    if ($uploadFile && $uploadFile['tmp_name']) {
        $ext = pathinfo($uploadFile['name'], PATHINFO_EXTENSION);
        $newName = uniqid("img{$i}_") . "." . $ext;
        move_uploaded_file($uploadFile['tmp_name'], "$upload_dir/$newName");
        $finalName = $newName;
    } elseif (!$replace && $oldFile) {
        $finalName = $oldFile;
    }

    return $finalName;
}

if ($mode === 'IncFormOk') {
    $upload_dir = $upload_dir ?? "../../uploads"; // fallback if not set
    $sectionData = [];

    for ($i = 1; $i <= 5; $i++) {
        $sectionData["Section{$i}"] = $_POST["Section{$i}"] ?? '';
        $imgKey = '';
        switch ($i) {
            case 1:
                $imgKey = 'ImgOne';
                break;
            case 2:
                $imgKey = 'ImgTwo';
                break;
            case 3:
                $imgKey = 'ImgTree';
                break;
            case 4:
                $imgKey = 'ImgFour';
                break;
            case 5:
                $imgKey = 'ImgFive';
                break;
        }
        $$imgKey = processFileUpload($i, $upload_dir);
        $sectionData[$imgKey] = $$imgKey;
    }

    $DesignMoney = $_POST['moeny'] ?? '0';

    // === INC 파일 저장 ===
    $fp = fopen($T_DirFole, 'w');
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney=\"{$DesignMoney}\";\n");
    foreach ($sectionData as $key => $val) {
        fwrite($fp, "\${$key}=\"" . addslashes($val) . "\";\n");
    }
    fwrite($fp, "?>");
    fclose($fp);

    echo "<script>alert('수정 완료!');</script>";
    echo "<meta http-equiv='refresh' content='0; url={$_SERVER['PHP_SELF']}?mode=IncForm'>";
    exit;
}
?>
