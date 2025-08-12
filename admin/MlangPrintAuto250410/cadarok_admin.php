<?php
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';  // 절대 경로로 변경
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "cadarok";

include "{$T_DirUrl}/ConDb.php";
$T_DirFole = "{$T_DirUrl}/{$T_TABLE}/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";

function handleFileUpload($fileInputName, $existingFilePath, $uploadDir) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        if ($existingFilePath) {
            unlink("$uploadDir/$existingFilePath");
        }
        $fileName = basename($_FILES[$fileInputName]['name']);
        move_uploaded_file($_FILES[$fileInputName]['tmp_name'], "$uploadDir/$fileName");
        return $fileName;
    }
    return $existingFilePath;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
            .Left1 {
                font-size: 10pt;
                color: #FFFFFF;
                font-weight: bold;
            }
        </style>
        <script>
            var NUM = "0123456789.";
            var SALPHA = "abcdefghijklmnopqrstuvwxyz";
            var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

            function TypeCheck(s, spc) {
                for (var i = 0; i < s.length; i++) {
                    if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                        return false;
                    }
                }
                return true;
            }

            function MemberXCheckField() {
                var f = document.myForm;

                if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
                    alert("<?php echo htmlspecialchars($View_TtableC)?> [항목]을 선택해 주세요!!");
                    f.RadOne.focus();
                    return false;
                }

                if (f.myListTreeSelect.value == "#" || f.myListTreeSelect.value == "==================") {
                    alert("<?php echo htmlspecialchars($View_TtableC)?>[그룹]을 선택해 주세요!!");
                    f.myListTreeSelect.focus();
                    return false;
                }

                if (f.myList.value == "#" || f.myList.value == "==================") {
                    alert("<?php echo htmlspecialchars($View_TtableC)?>[구분]을 선택해 주세요!!");
                    f.myList.focus();
                    return false;
                }

                if (f.quantity.value == "") {
                    alert("수량을 입력해 주세요!!");
                    f.quantity.focus();
                    return false;
                }
                if (!TypeCheck(f.quantity.value, NUM)) {
                    alert("수량을 숫자로 입력해 주세요.");
                    f.quantity.focus();
                    return false;
                }

                if (f.money.value == "") {
                    alert("금액을 입력해 주세요!!");
                    f.money.focus();
                    return false;
                }
                if (!TypeCheck(f.money.value, NUM)) {
                    alert("금액을 숫자로 입력해 주세요.");
                    f.money.focus();
                    return false;
                }
            }
        </script>
        <script src="../js/coolbar.js" type="text/javascript"></script>
    </head>

    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

        <?php if (isset($code) && $code == "Modify") { ?>
            <b>&nbsp;&nbsp;해당 <?php echo htmlspecialchars($View_TtableC)?> 데이터 수정</b><BR>
        <?php } else { ?>
            <b>&nbsp;&nbsp;해당 <?php echo htmlspecialchars($View_TtableC)?> 그룹 데이터 입력</b><BR>
        <?php } ?>

        <table border=0 align=center width=100% cellpadding=0 cellspacing=5>

            <?php include "{$T_TABLE}_Script.php"; ?>

            <tr>
                <td bgcolor='#<?php echo htmlspecialchars($Bgcolor1)?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
                <td><INPUT TYPE="text" NAME="quantity" size=20 maxLength='20' <?php if (isset($code) && $code == "Modify") { echo("value='".htmlspecialchars($MlangPrintAutoFildView_quantity)."'"); } ?>>개</td>
            </tr>

            <tr>
                <td bgcolor='#<?php echo htmlspecialchars($Bgcolor1)?>' width=100 class='Left1' align=right>금액&nbsp;&nbsp;</td>
                <td><INPUT TYPE="text" NAME="money" size=20 maxLength='20' <?php if (isset($code) && $code == "Modify") { echo("value='".htmlspecialchars($MlangPrintAutoFildView_money)."'"); } ?>></td>
            </tr>

            <tr>
                <td colspan=2 align='center'>* 최대값은 9999 까지 입력해 주세요</td>
            </tr>

            <tr>
                <td>&nbsp;&nbsp;</td>
                <td>
                    <?php if (isset($code) && $code == "Modify") { ?>
                        <input type='submit' value=' 수정합니다 '>
                    <?php } else { ?>
                        <input type='submit' value=' 입력합니다 '>
                    <?php } ?>
                </td>
            </tr>
        </form>
        </table>

        <?php
    } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (isset($mode) && $mode == "form_ok") {

        $stmt = $db->prepare("INSERT INTO {$TABLE} (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $RadOne, $myListTreeSelect, $quantity, $money, $myList, $TDesignMoney, $POtype, $quantityTwo);
        $stmt->execute();

        echo ("<script>
        alert('데이터가 성공적으로 입력되었습니다.');
        opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=".htmlspecialchars($_SERVER['PHP_SELF'])."?mode=form&Ttable={$Ttable}'>
        ");
        exit;
    } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (isset($mode) && $mode == "Modify_ok") {

        $stmt = $db->prepare("UPDATE {$TABLE} SET style=?, Section=?, quantity=?, money=?, TreeSelect=?, DesignMoney=?, POtype=?, quantityTwo=? WHERE no=?");
        $stmt->bind_param("ssssssssi", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no);
        $stmt->execute();

        echo ("<script>
        alert('데이터가 성공적으로 수정되었습니다.');
        opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=".htmlspecialchars($_SERVER['PHP_SELF'])."?mode=form&code=Modify&no=".htmlspecialchars($no)."&Ttable={$Ttable}'>
        ");
        exit;
    } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (isset($mode) && $mode == "delete") {

        $stmt = $db->prepare("DELETE FROM {$TABLE} WHERE no=?");
        $stmt->bind_param("i", $no);
        $stmt->execute();

        echo ("<html>
        <script>
        window.alert('".htmlspecialchars($no)." 번 데이터가 성공적으로 삭제되었습니다.');
        opener.parent.location.reload();
        window.self.close();
        </script>
        </html>
        ");
        exit;
    } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (isset($mode) && $mode == "IncForm") { // inc 파일을 업데이트하는 경우
        include "{$T_DirFole}";

        include "../title.php";
        ?>

        <head>
            <script>
                var NUM = "0123456789";
                var SALPHA = "abcdefghijklmnopqrstuvwxyz";
                var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

                function TypeCheck(s, spc) {
                    for (var i = 0; i < s.length; i++) {
                        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                            return false;
                        }
                    }
                    return true;
                }

                function AdminPassKleCheckField() {
                    var f = document.AdminPassKleInfo;
                }
            </script>
            <script src="../js/coolbar.js" type="text/javascript"></script>
        </head>

        <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

            <br>
            <p align=center>
                <form name='AdminPassKleInfo' method='post' onsubmit='return AdminPassKleCheckField()' action='<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>' enctype='multipart/form-data'>
                    <input type="hidden" name='mode' value='IncFormOk'>

                    <table border=1 width=100% align=center cellpadding='5' cellspacing='0'>
                        <tr>
                            <td bgcolor='#6699CC' class='td11' colspan=2>
                                <font style='color:#FFFFFF; line-height:130%;'>
                                    아래 항목을 마우스로 클릭하여 선택한 후 저장/수정 할 수 있습니다. 선택된 항목은 자동으로 업데이트됩니다.
                                    <br>
                                    해당 항목은 HTML로 입력됩니다. <br>
                                    <font color=red>*</font> HTML 태그가 적용됩니다.
                                </font>
                            </td>
                        </tr>

                        <tr>
                            <td align=center>항목 1</td>
                            <td>
                                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td align=center><textarea name="Section1" rows="4" cols="50"><?php echo htmlspecialchars($SectionOne)?></textarea></td>
                                        <td align=center>
                                            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                                <tr>
                                                    <td align=center>
                                                        <input type='file' name='File1' size='20'>
                                                        <?php if ($ImgOne): ?><br>
                                                            <input type="checkbox" name="ImeOneChick">이미지를 삭제하려면 선택하세요
                                                            <input type="hidden" name='File1_Y' value='<?php echo htmlspecialchars($ImgOne)?>'>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($ImgOne): ?>
                                                    <td align=center>
                                                        <img src='<?php echo htmlspecialchars($upload_dir)?>/<?php echo htmlspecialchars($ImgOne)?>' width=80 height=95 border=0>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align=center>항목 2</td>
                            <td>
                                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td align=center><textarea name="Section2" rows="4" cols="50"><?php echo htmlspecialchars($SectionTwo)?></textarea></td>
                                        <td align=center>
                                            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                                <tr>
                                                    <td align=center>
                                                        <input type='file' name='File2' size='20'>
                                                        <?php if ($ImgTwo): ?><br>
                                                            <input type="checkbox" name="ImeTwoChick">이미지를 삭제하려면 선택하세요
                                                            <input type="hidden" name='File2_Y' value='<?php echo htmlspecialchars($ImgTwo)?>'>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($ImgTwo): ?>
                                                    <td align=center>
                                                        <img src='<?php echo htmlspecialchars($upload_dir)?>/<?php echo htmlspecialchars($ImgTwo)?>' width=80 height=95 border=0>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align=center>항목 3</td>
                            <td>
                                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td align=center><textarea name="Section3" rows="4" cols="50"><?php echo htmlspecialchars($SectionTree)?></textarea></td>
                                        <td align=center>
                                            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                                <tr>
                                                    <td align=center>
                                                        <input type='file' name='File3' size='20'>
                                                        <?php if ($ImgTree): ?><br>
                                                            <input type="checkbox" name="ImeTreeChick">이미지를 삭제하려면 선택하세요
                                                            <input type="hidden" name='File3_Y' value='<?php echo htmlspecialchars($ImgTree)?>'>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($ImgTree): ?>
                                                    <td align=center>
                                                        <img src='<?php echo htmlspecialchars($upload_dir)?>/<?php echo htmlspecialchars($ImgTree)?>' width=80 height=95 border=0>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align=center>항목 4</td>
                            <td>
                                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td align=center><textarea name="Section4" rows="4" cols="50"><?php echo htmlspecialchars($SectionFour)?></textarea></td>
                                        <td align=center>
                                            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                                <tr>
                                                    <td align=center>
                                                        <input type='file' name='File4' size='20'>
                                                        <?php if ($ImgFour): ?><br>
                                                            <input type="checkbox" name="ImeFourChick">이미지를 삭제하려면 선택하세요
                                                            <input type="hidden" name='File4_Y' value='<?php echo htmlspecialchars($ImgFour)?>'>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($ImgFour): ?>
                                                    <td align=center>
                                                        <img src='<?php echo htmlspecialchars($upload_dir)?>/<?php echo htmlspecialchars($ImgFour)?>' width=80 height=95 border=0>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td align=center>항목 5</td>
                            <td>
                                <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td align=center><textarea name="Section5" rows="4" cols="50"><?php echo htmlspecialchars($SectionFive)?></textarea></td>
                                        <td align=center>
                                            <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                                                <tr>
                                                    <td align=center>
                                                        <input type='file' name='File5' size='20'>
                                                        <?php if ($ImgFive): ?><br>
                                                            <input type="checkbox" name="ImeFiveChick">이미지를 삭제하려면 선택하세요
                                                            <input type="hidden" name='File5_Y' value='<?php echo htmlspecialchars($ImgFive)?>'>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php if ($ImgFive): ?>
                                                    <td align=center>
                                                        <img src='<?php echo htmlspecialchars($upload_dir)?>/<?php echo htmlspecialchars($ImgFive)?>' width=80 height=95 border=0>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <br>
                    <input type='submit' value='저장'>
                    <input type='button' value='닫기' onclick='javascript:window.self.close();'>
            </p>
        </form>

        <?php
        exit;
    } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (isset($mode) && $mode == "IncFormOk") {  // inc 파일을 업데이트하는 경우

        $File1NAME = handleFileUpload('File1', $File1_Y, $upload_dir);
        $File2NAME = handleFileUpload('File2', $File2_Y, $upload_dir);
        $File3NAME = handleFileUpload('File3', $File3_Y, $upload_dir);
        $File4NAME = handleFileUpload('File4', $File4_Y, $upload_dir);
        $File5NAME = handleFileUpload('File5', $File5_Y, $upload_dir);

        $fp = fopen("{$T_DirFole}", "w");
        fwrite($fp, "<?php\n");
        fwrite($fp, "\$DesignMoney=\"" . htmlspecialchars($money) . "\";\n");
        fwrite($fp, "\$SectionOne=\"" . htmlspecialchars($Section1) . "\";\n");
        fwrite($fp, "\$SectionTwo=\"" . htmlspecialchars($Section2) . "\";\n");
        fwrite($fp, "\$SectionTree=\"" . htmlspecialchars($Section3) . "\";\n");
        fwrite($fp, "\$SectionFour=\"" . htmlspecialchars($Section4) . "\";\n");
        fwrite($fp, "\$SectionFive=\"" . htmlspecialchars($Section5) . "\";\n");
        fwrite($fp, "\$ImgOne=\"" . htmlspecialchars($File1NAME) . "\";\n");
        fwrite($fp, "\$ImgTwo=\"" . htmlspecialchars($File2NAME) . "\";\n");
        fwrite($fp, "\$ImgTree=\"" . htmlspecialchars($File3NAME) . "\";\n");
        fwrite($fp, "\$ImgFour=\"" . htmlspecialchars($File4NAME) . "\";\n");
        fwrite($fp, "\$ImgFive=\"" . htmlspecialchars($File5NAME) . "\";\n");
        fwrite($fp, "?>");
        fclose($fp);

        echo ("<script>
        window.alert('저장이 완료되었습니다.');
        </script>
        <meta http-equiv='Refresh' content='0; URL=".htmlspecialchars($_SERVER['PHP_SELF'])."?mode=IncForm'>
        ");
        exit;
    }
?>
