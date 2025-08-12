<?php
include "../../db.php";
include "../config.php";

// 변수 초기화
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$code = isset($_GET['code']) ? $_GET['code'] : '';
$View_TtableC = isset($View_TtableC) ? $View_TtableC : '';

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "MerchandiseBond";

include "$T_DirUrl/ConDb.php";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form") {

    include "../title.php";
    include "int/info.php";
    $Bgcolor1 = "408080";

    if ($code == "Modify") {
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
    <script language="javascript">
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
                alert("<?php echo  htmlspecialchars($View_TtableC) ?> [종류] 을 선택하여주세요!!");
                f.RadOne.focus();
                return false;
            }

            if (f.quantity.value == "") {
                alert("수량을 입력하여주세요!!");
                f.quantity.focus();
                return false;
            }
            if (!TypeCheck(f.quantity.value, NUM)) {
                alert("수량은 숫자로만 입력해 주셔야 합니다.");
                f.quantity.focus();
                return false;
            }

            if (f.myList.value == "#" || f.myList.value == "==================") {
                alert("<?php echo  htmlspecialchars($View_TtableC) ?>[후가공] 을 선택하여주세요!!");
                f.myList.focus();
                return false;
            }

            if (f.money.value == "") {
                alert("가격을 입력하여주세요!!");
                f.money.focus();
                return false;
            }
            if (!TypeCheck(f.money.value, NUM)) {
                alert("가격은 숫자로만 입력해 주셔야 합니다.");
                f.money.focus();
                return false;
            }

            if (f.TDesignMoney.value == "") {
                alert("디자인비 을 입력하여주세요!!");
                f.TDesignMoney.focus();
                return false;
            }
            if (!TypeCheck(f.TDesignMoney.value, NUM)) {
                alert("디자인비 은 숫자로만 입력해 주셔야 합니다.");
                f.TDesignMoney.focus();
                return false;
            }
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<?php if ($code == "Modify") { ?>
    <b>&nbsp;&nbsp;▒ <?php echo  htmlspecialchars($View_TtableC) ?> 자료 수정 ▒▒▒▒▒</b><br>
<?php } else { ?>
    <b>&nbsp;&nbsp;▒ <?php echo  htmlspecialchars($View_TtableC) ?> 신 자료 입력 ▒▒▒▒▒</b><br>
<?php } ?>

<table border=0 align=center width=100% cellpadding=0 cellspacing=5>

<?php include "{$T_TABLE}_Script.php";
$MlangPrintAutoFildView_POtype = isset($MlangPrintAutoFildView_POtype) ? $MlangPrintAutoFildView_POtype : '기본값'; ?>

<tr>
    <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
    <td><input type="text" name="quantity" size=20 maxLength='20' <?php if ($code == "Modify") {
        echo "value='" . htmlspecialchars($MlangPrintAutoFildView_quantity) . "'";
    } ?>></td>
</tr>

<tr>
    <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>인쇄면&nbsp;&nbsp;</td>
    <td>
        <select name="POtype">
            <option value='1' <?php if ($MlangPrintAutoFildView_POtype == "1") {
                echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";
            } ?>>단면</option>
            <option value='2' <?php if ($MlangPrintAutoFildView_POtype == "2") {
                echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";
            } ?>>양면</option>
        </select>
    </td>
</tr>

<tr>
    <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>후가공&nbsp;&nbsp;</td>
    <td>
        <select name="myList">
            <option value='#'>:::::: 선택하세요 ::::::</option>
            <?php if ($code == "Modify" && $MlangPrintAutoFildView_Section) {
                echo "<option value='" . htmlspecialchars($MlangPrintAutoFildView_Section) . "' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>";
                include "../../db.php";
                $result = mysqli_query($db, "SELECT * FROM $GGTABLE WHERE no='$MlangPrintAutoFildView_Section'");
                $row = mysqli_fetch_array($result);
                if ($row) {
                    echo htmlspecialchars($row['title']);
                }
                mysqli_close($db);
                echo "</option>";
            } ?>
        </select>
    </td>
</tr>

<tr>
    <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>가격&nbsp;&nbsp;</td>
    <td><input type="text" name="money" size=20 maxLength='20' <?php if ($code == "Modify") {
        echo "value='" . htmlspecialchars($MlangPrintAutoFildView_money) . "'";
    } ?>></td>
</tr>

<tr>
    <td bgcolor='#<?php echo  $Bgcolor1 ?>' width=100 class='Left1' align=right>디자인비&nbsp;&nbsp;</td>
    <td><input type="text" name="TDesignMoney" size=20 maxLength='20' <?php if ($code == "Modify") {
        echo "value='" . htmlspecialchars($MlangPrintAutoFildView_DesignMoney) . "'";
    } else {
        echo "value='" . htmlspecialchars($DesignMoney) . "'";
    } ?>></td>
</tr>

<tr>
    <td>&nbsp;&nbsp;</td>
    <td>
        <?php if ($code == "Modify") { ?>
            <input type='submit' value=' 수정 합니다.'>
        <?php } else { ?>
            <input type='submit' value=' 저장 합니다.'>
        <?php } ?>
    </td>
</tr>
</form>
</table>

<?php
}
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 if ($mode == "form_ok") {

    // 준비된 명령문으로 SQL 인젝션 방지
    $stmt = $db->prepare("INSERT INTO $TABLE (style, Section, quantity, money, DesignMoney, POtype) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddds", $RadOne, $myList, $quantity, $money, $TDesignMoney, $POtype);

    if ($stmt->execute()) {
        echo ("
            <script language=javascript>
            alert('\\n자료를 정상적으로 저장하였습니다.\\n');
            opener.parent.location.reload();
            </script>
            <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&Ttable=$Ttable'>
        ");
    } else {
        echo "<script>alert('데이터 저장 중 오류가 발생했습니다.');</script>";
    }

    $stmt->close();
    exit;
}

if ($mode == "Modify_ok") {

    $stmt = $db->prepare("UPDATE $TABLE SET style = ?, Section = ?, quantity = ?, money = ?, DesignMoney = ?, POtype = ? WHERE no = ?");
    $stmt->bind_param("ssddds", $RadOne, $myList, $quantity, $money, $TDesignMoney, $POtype, $no);

    if ($stmt->execute()) {
        echo ("
            <script language=javascript>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
            </script>
            <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&code=Modify&no=$no&Ttable=$Ttable'>
        ");
    } else {
        echo "
            <script language=javascript>
                alert('DB 접속 에러입니다!');
                history.go(-1);
            </script>
        ";
    }

    $stmt->close();
    exit;
}

if ($mode == "delete") {

    $stmt = $db->prepare("DELETE FROM $TABLE WHERE no = ?");
    $stmt->bind_param("i", $no);

    if ($stmt->execute()) {
        echo ("
            <html>
            <script language=javascript>
                alert('" . htmlspecialchars($no) . "번 자료를 삭제 처리하였습니다.');
                opener.parent.location.reload();
                window.self.close();
            </script>
            </html>
        ");
    } else {
        echo "<script>alert('삭제 중 오류가 발생했습니다.');</script>";
    }

    $stmt->close();
    exit;
}

// 데이터베이스 연결 종료
// $db->close();
?>


<?php
if ($mode == "IncForm") { // inc 파일을 수정하는 폼
    include "int/info.php";
    include "../title.php";
?>

<head>
    <script language="javascript">
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

            if (f.moeny.value == "") {
                alert("디자인 가격을 입력하여주세요?");
                f.moeny.focus();
                return false;
            }
            if (!TypeCheck(f.moeny.value, NUM)) {
                alert("디자인 가격은 숫자로만 입력해 주셔야 합니다.");
                f.moeny.focus();
                return false;
            }
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<br>
<p align="center">
    <form name="AdminPassKleInfo" method="post" onsubmit="return AdminPassKleCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="IncFormOk">

        <table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
            <tr>
                <td bgcolor="#6699CC" class="td11" colspan="2">아래의 가격을 숫자로 변경 가능합니다.</td>
            </tr>
            <tr>
                <td align="center">디자인 가격</td>
                <td><input type="text" name="moeny" maxlength="10" size="15" value="<?php echo  htmlspecialchars($DesignMoney) ?>"> 원</td>
            </tr>

            <tr>
                <td bgcolor="#6699CC" class="td11" colspan="2">
                    <font style="color:#FFFFFF; line-height:130%;">
                        아래의 내용은 마우스를 대면 나오는 설명글입니다. 사진/내용을 입력하지 않으면 자동으로 호출되지 않습니다.<br>
                        기존 사진자료가 있을 경우 자료를 지우려면 사진 미입력 후 체크 버튼에 체크만 하시면 자료가 지워집니다.<br>
                        <font color="red">*</font> 문구 입력 시 HTML을 인식, 엔터를 치면 자동 br로 처리, # 입력 시 공백 하나 ##(두 개) 입력 시 공백 2칸씩으로 처리됨
                    </font>
                </td>
            </tr>

            <?php
            // Section 관련 데이터 배열화
            $sections = [
                ["name" => "Section1", "label" => "종류", "value" => $SectionOne, "img" => $ImgOne, "imgName" => "File1"],
                ["name" => "Section2", "label" => "수량", "value" => $SectionTwo, "img" => $ImgTwo, "imgName" => "File2"],
                ["name" => "Section3", "label" => "인쇄면", "value" => $SectionTree, "img" => $ImgTree, "imgName" => "File3"],
                ["name" => "Section4", "label" => "후가공", "value" => $SectionFour, "img" => $ImgFour, "imgName" => "File4"],
                ["name" => "Section5", "label" => "디자인편집", "value" => $SectionFive, "img" => $ImgFive, "imgName" => "File5"]
            ];

            // 섹션 출력 반복문
            foreach ($sections as $section) {
                $name = htmlspecialchars($section["name"]);
                $label = htmlspecialchars($section["label"]);
                $value = htmlspecialchars($section["value"]);
                $img = htmlspecialchars($section["img"]);
                $imgName = htmlspecialchars($section["imgName"]);
            ?>

            <tr>
                <td align="center"><?php echo  $label ?></td>
                <td>
                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <textarea name="<?php echo  $name ?>" rows="4" cols="50"><?php echo  $value ?></textarea>
                            </td>
                            <td align="center">
                                <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <input type="file" name="<?php echo  $imgName ?>" size="20">
                                            <?php if ($img) { ?>
                                                <br>
                                                <input type="checkbox" name="<?php echo  $imgName ?>Chick">이미지를 변경하려면 체크해 주세요
                                                <input type="hidden" name="<?php echo  $imgName ?>_Y" value="<?php echo  $img ?>">
                                            <?php } ?>
                                        </td>
                                        <?php if ($img) { ?>
                                            <td align="center">
                                                <img src="<?php echo  htmlspecialchars($upload_dir) ?>/<?php echo  $img ?>" width="80" height="95" border="0">
                                            </td>
                                        <?php } ?>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <?php } ?>
        </table>

        <br>
        <input type="submit" value="수정합니다">
        <input type="button" value="창 닫기" onClick="window.self.close();">
    </form>
</p>

<?php
exit;
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "IncFormOk") {  // inc 파일 결과 처리

    // 파일 업로드 설정 배열
    $uploads = [
        ["check" => "ImeOneChick", "file" => "File1", "fileY" => "File1_Y", "uploadScript" => "$T_DirUrl/Upload_1.php", "nameVar" => "File1NAME"],
        ["check" => "ImeTwoChick", "file" => "File2", "fileY" => "File2_Y", "uploadScript" => "$T_DirUrl/Upload_2.php", "nameVar" => "File2NAME"],
        ["check" => "ImeTreeChick", "file" => "File3", "fileY" => "File3_Y", "uploadScript" => "$T_DirUrl/Upload_3.php", "nameVar" => "File3NAME"],
        ["check" => "ImeFourChick", "file" => "File4", "fileY" => "File4_Y", "uploadScript" => "$T_DirUrl/Upload_4.php", "nameVar" => "File4NAME"],
        ["check" => "ImeFiveChick", "file" => "File5", "fileY" => "File5_Y", "uploadScript" => "$T_DirUrl/Upload_5.php", "nameVar" => "File5NAME"]
    ];

    foreach ($uploads as $upload) {
        $checkVar = $$upload["check"];
        $fileVar = $$upload["file"];
        $fileYVar = $$upload["fileY"];

        if ($checkVar == "on") {
            if ($fileVar) {
                if ($fileYVar && file_exists("$upload_dir/$fileYVar")) {
                    unlink("$upload_dir/$fileYVar");
                }
                include $upload["uploadScript"];
            } elseif ($fileYVar && file_exists("$upload_dir/$fileYVar")) {
                unlink("$upload_dir/$fileYVar");
            }
        } else {
            ${$upload["nameVar"]} = $fileYVar ? $fileYVar : ($fileVar ? include $upload["uploadScript"] : "");
        }
    }

    // 설정 파일에 데이터 쓰기
    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney=\"" . addslashes($moeny) . "\";\n");
    fwrite($fp, "\$SectionOne=\"" . addslashes($Section1) . "\";\n");
    fwrite($fp, "\$SectionTwo=\"" . addslashes($Section2) . "\";\n");
    fwrite($fp, "\$SectionTree=\"" . addslashes($Section3) . "\";\n");
    fwrite($fp, "\$SectionFour=\"" . addslashes($Section4) . "\";\n");
    fwrite($fp, "\$SectionFive=\"" . addslashes($Section5) . "\";\n");
    fwrite($fp, "\$ImgOne=\"" . addslashes($File1NAME) . "\";\n");
    fwrite($fp, "\$ImgTwo=\"" . addslashes($File2NAME) . "\";\n");
    fwrite($fp, "\$ImgTree=\"" . addslashes($File3NAME) . "\";\n");
    fwrite($fp, "\$ImgFour=\"" . addslashes($File4NAME) . "\";\n");
    fwrite($fp, "\$ImgFive=\"" . addslashes($File5NAME) . "\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo ("<script language='javascript'>
        window.alert('수정 완료....*^^*\\n\\n" . addslashes($WebSoftCopyright) . "');
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=IncForm'>");
    exit;
}
?>
