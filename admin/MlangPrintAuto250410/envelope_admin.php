<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "envelope";

include "$T_DirUrl/ConDb.php";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";
$MlangPrintAutoFildView_POtype = $_GET['MlangPrintAutoFildView_POtype'] ?? $_POST['MlangPrintAutoFildView_POtype'] ?? null;
$mode = $_GET['mode'] ?? $_POST['mode'] ?? null;
$code = $_GET['code'] ?? $_POST['code'] ?? null;
$no = $_GET['no'] ?? $_POST['no'] ?? null;

if ($mode == "form") {
    include "../title.php";
    include "int/info.php";
    $Bgcolor1 = "408080";

    if ($code == "Modify") {
        include "./{$T_TABLE}_NoFild.php";
    }
    ?>

    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>Form Page</title>
        <style>
            .Left1 {font-size:10pt; color:#FFFFFF; font-weight: bold;}
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
                    alert("<?php echo $View_TtableC?> [분류]을 선택해 주세요!!");
                    f.RadOne.focus();
                    return false;
                }

                if (f.myList.value == "#" || f.myList.value == "==================") {
                    alert("<?php echo $View_TtableC?>[항목]을 선택해 주세요!!");
                    f.myList.focus();
                    return false;
                }

                if (f.quantity.value == "") {
                    alert("수량을 입력해 주세요!!");
                    f.quantity.focus();
                    return false;
                }
                if (!TypeCheck(f.quantity.value, NUM)) {
                    alert("수량은 숫자로 입력해 주세요.");
                    f.quantity.focus();
                    return false;
                }

                if (f.money.value == "") {
                    alert("금액을 입력해 주세요!!");
                    f.money.focus();
                    return false;
                }
                if (!TypeCheck(f.money.value, NUM)) {
                    alert("금액은 숫자로 입력해 주세요.");
                    f.money.focus();
                    return false;
                }

                if (f.TDesignMoney.value == "") {
                    alert("디자인비를 입력해 주세요!!");
                    f.TDesignMoney.focus();
                    return false;
                }
                if (!TypeCheck(f.TDesignMoney.value, NUM)) {
                    alert("디자인비는 숫자로 입력해 주세요.");
                    f.TDesignMoney.focus();
                    return false;
                }

                return true;
            }
        </script>
    </head>
    <body>
        <?php if ($code == "Modify") { ?>
            <b>&nbsp;&nbsp;<?php echo  $View_TtableC ?> 자료 수정</b><br>
        <?php } else { ?>
            <b>&nbsp;&nbsp;<?php echo  $View_TtableC ?> 자료 입력</b><br>
        <?php } ?>

        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
            <form name="myForm" method="post" action="<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return MemberXCheckField()">
                <input type="hidden" name="mode" value="<?php echo  $code == 'Modify' ? 'Modify_ok' : 'form_ok' ?>">
                <input type="hidden" name="no" value="<?php echo  $no ?>">
                <input type="hidden" name="Ttable" value="<?php echo  $Ttable ?>">

                <?php include "{$T_TABLE}_Script.php"; ?>

                <tr>
                    <td bgcolor="<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">포장형태&nbsp;&nbsp;</td>
                    <td>
                        <select name="POtype">
                            <option value="2" <?php echo  $MlangPrintAutoFildView_POtype == "2" ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "" ?>>봉투2종</option>
                            <option value="1" <?php echo  $MlangPrintAutoFildView_POtype == "1" ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "" ?>>봉투1종</option>
                            <option value="3" <?php echo  $MlangPrintAutoFildView_POtype == "3" ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "" ?>>기타4종(특수)</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">수량&nbsp;&nbsp;</td>
                    <td><input type="text" name="quantity" size="20" maxlength="20" value="<?php echo  $code == 'Modify' ? htmlspecialchars($MlangPrintAutoFildView_quantity) : '' ?>">개</td>
                </tr>

                <tr>
                    <td bgcolor="<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">금액&nbsp;&nbsp;</td>
                    <td><input type="text" name="money" size="20" maxlength="20" value="<?php echo  $code == 'Modify' ? htmlspecialchars($MlangPrintAutoFildView_money) : '' ?>"></td>
                </tr>

                <tr>
                    <td bgcolor="<?php echo  $Bgcolor1 ?>" width="100" class="Left1" align="right">디자인비&nbsp;&nbsp;</td>
                    <td><input type="text" name="TDesignMoney" size="20" maxlength="20" value="<?php echo  $code == 'Modify' ? htmlspecialchars($MlangPrintAutoFildView_DesignMoney) : '' ?>"></td>
                </tr>

                <tr>
                    <td>&nbsp;&nbsp;</td>
                    <td>
                        <input type="submit" value="<?php echo  $code == 'Modify' ? ' 수정 합니다.' : ' 입력 합니다.' ?>">
                    </td>
                </tr>
            </form>
        </table>
    </body>
    </html>

    <?php
}

if ($mode == "form_ok") {
    $stmt = $db->prepare("INSERT INTO $TABLE (style, Section, quantity, money, DesignMoney, POtype) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssdss', $_POST['RadOne'], $_POST['myList'], $_POST['quantity'], $_POST['money'], $_POST['TDesignMoney'], $_POST['POtype']);
    $stmt->execute();

    echo ("
    <script language='javascript'>
    alert('\\n자료가 성공적으로 입력되었습니다.\\n');
    opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=form&Ttable=$Ttable'>
    ");
    exit;
}

if ($mode == "Modify_ok") {
    $stmt = $db->prepare("UPDATE $TABLE SET style=?, Section=?, quantity=?, money=?, DesignMoney=?, POtype=? WHERE no=?");
    $stmt->bind_param('sssdssi', $_POST['RadOne'], $_POST['myList'], $_POST['quantity'], $_POST['money'], $_POST['TDesignMoney'], $_POST['POtype'], $_POST['no']);
    $stmt->execute();

    echo ("
    <script language='javascript'>
    alert('\\n자료가 성공적으로 수정되었습니다.\\n');
    opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=form&code=Modify&no=" . htmlspecialchars($_POST['no']) . "&Ttable=$Ttable'>
    ");
    exit;
}

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM $TABLE WHERE no=?");
    $stmt->bind_param('i', $no);
    $stmt->execute();

    echo ("
    <script language='javascript'>
    window.alert('$no 번 자료가 삭제되었습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    ");
    exit;
}

if ($mode == "IncForm") {
    include "int/info.php";
    include "../title.php";
    ?>

    <!DOCTYPE html>
    <html lang="ko">
    <head>
        <meta charset="UTF-8">
        <title>Inc Form</title>
        <script>
            var NUM = "0123456789";

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

                if (f.moeny.value == "") {
                    alert("금액을 입력해 주세요?");
                    f.moeny.focus();
                    return false;
                }
                if (!TypeCheck(f.moeny.value, NUM)) {
                    alert("금액은 숫자로 입력해 주세요.");
                    f.moeny.focus();
                    return false;
                }

                return true;
            }
        </script>
    </head>
    <body>
        <br>
        <p align="center">
        <form name="AdminPassKleInfo" method="post" onsubmit="return AdminPassKleCheckField()" action="<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
            <input type="hidden" name="mode" value="IncFormOk">

            <table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
                <tr>
                    <td bgcolor="#6699CC" class="td11" colspan="2">아래 필드의 내용을 입력 후 저장 버튼을 누르세요.</td>
                </tr>
                <tr>
                    <td align="center">디자인 금액</td>
                    <td><input type="text" name="moeny" maxlength="10" size="15" value="<?php echo  $DesignMoney ?>"> 원</td>
                </tr>

                <tr>
                    <td bgcolor="#6699CC" class="td11" colspan="2">
                        <font style="color:#FFFFFF; line-height:130%;">
                            아래 이미지를 클릭하시면 확대된 이미지를 볼 수 있습니다. 추가/수정 시에는 해당 이미지를 업로드 하시거나, 이미지를 삭제하시려면 삭제를 선택하세요.
                            <br>
                            <font color="red">*</font>
                            아래 항목들은 HTML 입력을 지원하며, 줄바꿈은 자동으로 처리됩니다. # 입력 시 문단 구분, ##(공백) 입력 시 줄바꿈 두 번 처리됩니다.
                        </font>
                    </td>
                </tr>
                <tr>
                    <td align="center">섹션 1</td>
                    <td>
                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center"><textarea name="Section1" rows="4" cols="50"><?php echo  $SectionOne ?></textarea></td>
                                <td align="center">
                                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center">
                                                <input type="file" name="File1" size="20">
                                                <?php if ($ImgOne) { ?><br>
                                                    <input type="checkbox" name="ImeOneChick">이미지를 삭제하려면 선택하세요
                                                    <input type="hidden" name="File1_Y" value="<?php echo  $ImgOne ?>">
                                                <?php } ?>
                                            </td>
                                            <?php if ($ImgOne) { ?>
                                                <td align="center">
                                                    <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgOne ?>" width="80" height="95" border="0">
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center">섹션 2</td>
                    <td>
                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center"><textarea name="Section2" rows="4" cols="50"><?php echo  $SectionTwo ?></textarea></td>
                                <td align="center">
                                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center">
                                                <input type="file" name="File2" size="20">
                                                <?php if ($ImgTwo) { ?><br>
                                                    <input type="checkbox" name="ImeTwoChick">이미지를 삭제하려면 선택하세요
                                                    <input type="hidden" name="File2_Y" value="<?php echo  $ImgTwo ?>">
                                                <?php } ?>
                                            </td>
                                            <?php if ($ImgTwo) { ?>
                                                <td align="center">
                                                    <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgTwo ?>" width="80" height="95" border="0">
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center">섹션 3</td>
                    <td>
                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center"><textarea name="Section3" rows="4" cols="50"><?php echo  $SectionTree ?></textarea></td>
                                <td align="center">
                                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center">
                                                <input type="file" name="File3" size="20">
                                                <?php if ($ImgTree) { ?><br>
                                                    <input type="checkbox" name="ImeTreeChick">이미지를 삭제하려면 선택하세요
                                                    <input type="hidden" name="File3_Y" value="<?php echo  $ImgTree ?>">
                                                <?php } ?>
                                            </td>
                                            <?php if ($ImgTree) { ?>
                                                <td align="center">
                                                    <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgTree ?>" width="80" height="95" border="0">
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center">섹션 4</td>
                    <td>
                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center"><textarea name="Section4" rows="4" cols="50"><?php echo  $SectionFour ?></textarea></td>
                                <td align="center">
                                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center">
                                                <input type="file" name="File4" size="20">
                                                <?php if ($ImgFour) { ?><br>
                                                    <input type="checkbox" name="ImeFourChick">이미지를 삭제하려면 선택하세요
                                                    <input type="hidden" name="File4_Y" value="<?php echo  $ImgFour ?>">
                                                <?php } ?>
                                            </td>
                                            <?php if ($ImgFour) { ?>
                                                <td align="center">
                                                    <img src="<?php echo  $upload_dir ?>/<?php echo  $ImgFour ?>" width="80" height="95" border="0">
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <br>
            <input type="submit" value="저장합니다">
            <input type="button" value="창 닫기" onClick="javascript:window.self.close();">
        </form>
    </body>
    </html>

    <?php
}

if ($mode == "IncFormOk") {
    $upload_dir = "../../uploads"; // Change this to your actual upload directory

    $File1NAME = uploadFile('File1', $upload_dir);
    $File2NAME = uploadFile('File2', $upload_dir);
    $File3NAME = uploadFile('File3', $upload_dir);
    $File4NAME = uploadFile('File4', $upload_dir);

    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney=\"{$_POST['moeny']}\";\n");
    fwrite($fp, "\$SectionOne=\"{$_POST['Section1']}\";\n");
    fwrite($fp, "\$SectionTwo=\"{$_POST['Section2']}\";\n");
    fwrite($fp, "\$SectionTree=\"{$_POST['Section3']}\";\n");
    fwrite($fp, "\$SectionFour=\"{$_POST['Section4']}\";\n");
    fwrite($fp, "\$ImgOne=\"$File1NAME\";\n");
    fwrite($fp, "\$ImgTwo=\"$File2NAME\";\n");
    fwrite($fp, "\$ImgTree=\"$File3NAME\";\n");
    fwrite($fp, "\$ImgFour=\"$File4NAME\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo ("<script language='javascript'>
    window.alert('작업 완료....*^^*\\n\\n$WebSoftCopyright');
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=IncForm'>
    ");
    exit;
}

function uploadFile($fileInputName, $upload_dir) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES[$fileInputName]['tmp_name'];
        $name = basename($_FILES[$fileInputName]['name']);
        move_uploaded_file($tmp_name, "$upload_dir/$name");
        return $name;
    }
    return null;
}
?>
