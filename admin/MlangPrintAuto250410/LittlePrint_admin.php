<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "LittlePrint";

include "$T_DirUrl/ConDb.php";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_${T_TABLE}";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form") {
    include "../title.php";
    include $T_DirFole;

    $Bgcolor1 = "408080";

    if ($code == "Modify") {
        include "./${T_TABLE}_NoFild.php";
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

        ////////////////////////////////////////////////////////////////////////////////////////////////
        function TypeCheck(s, spc) {
            for (var i = 0; i < s.length; i++) {
                if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                    return false;
                }
            }
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

        function MemberXCheckField() {
            var f = document.myForm;

            if (f.RadOne.value === "#" || f.RadOne.value === "==================") {
                alert("<?php echo  htmlspecialchars($View_TtableC) ?> [종류] 을 선택하여 주세요!!");
                f.RadOne.focus();
                return false;
            }

            if (f.myListTreeSelect.value === "#" || f.myListTreeSelect.value === "==================") {
                alert("<?php echo  htmlspecialchars($View_TtableC) ?> [종이종류] 을 선택하여 주세요!!");
                f.myListTreeSelect.focus();
                return false;
            }

            if (f.myList.value === "#" || f.myList.value === "==================") {
                alert("<?php echo  htmlspecialchars($View_TtableC) ?> [종이규격] 을 선택하여 주세요!!");
                f.myList.focus();
                return false;
            }

            if (f.quantity.value === "") {
                alert("수량을 입력하여 주세요!!");
                f.quantity.focus();
                return false;
            }
            if (!TypeCheck(f.quantity.value, NUM)) {
                alert("수량은 숫자로만 입력해 주셔야 합니다.");
                f.quantity.focus();
                return false;
            }

            if (f.money.value === "") {
                alert("가격을 입력하여 주세요!!");
                f.money.focus();
                return false;
            }
            if (!TypeCheck(f.money.value, NUM)) {
                alert("가격은 숫자로만 입력해 주셔야 합니다.");
                f.money.focus();
                return false;
            }

            if (f.TDesignMoney.value === "") {
                alert("디자인비를 입력하여 주세요!!");
                f.TDesignMoney.focus();
                return false;
            }
            if (!TypeCheck(f.TDesignMoney.value, NUM)) {
                alert("디자인비는 숫자로만 입력해 주셔야 합니다.");
                f.TDesignMoney.focus();
                return false;
            }
        }
    </script>
    <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="coolBar">

<?php if ($code == "Modify") { ?>
    <b>&nbsp;&nbsp;▒ <?php echo  htmlspecialchars($View_TtableC) ?> 자료 수정 ▒▒▒▒▒</b><br>
<?php } else { ?>
    <b>&nbsp;&nbsp;▒ <?php echo  htmlspecialchars($View_TtableC) ?> 신 자료 입력 ▒▒▒▒▒</b><br>
<?php } ?>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
    <?php include "{$T_TABLE}_Script.php"; ?>

    <tr>
        <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">인쇄면&nbsp;&nbsp;</td>
        <td>
            <select name="POtype">
                <option value="2" <?php echo  isset($MlangPrintAutoFildView_POtype) && $MlangPrintAutoFildView_POtype == "2" ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "" ?>>양면</option>
                <option value="1" <?php echo  isset($MlangPrintAutoFildView_POtype) && $MlangPrintAutoFildView_POtype == "1" ? "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'" : "" ?>>단면</option>
            </select>
        </td>
    </tr>    

    <tr>
        <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">수량&nbsp;&nbsp;</td>
        <td><input type="text" name="quantity" size="20" maxlength="20" value="<?php echo  $code == "Modify" && isset($MlangPrintAutoFildView_quantity) ? htmlspecialchars($MlangPrintAutoFildView_quantity) : '' ?>">매</td>
    </tr>

    <tr>
        <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">가격&nbsp;&nbsp;</td>
        <td><input type="text" name="money" size="20" maxlength="20" value="<?php echo  $code == "Modify" && isset($MlangPrintAutoFildView_money) ? htmlspecialchars($MlangPrintAutoFildView_money) : '' ?>"></td>
    </tr>

    <tr>
        <td bgcolor="#<?php echo  htmlspecialchars($Bgcolor1) ?>" width="100" class="Left1" align="right">디자인비&nbsp;&nbsp;</td>
        <td><input type="text" name="TDesignMoney" size="20" maxlength="20" value="<?php echo  $code == "Modify" && isset($MlangPrintAutoFildView_DesignMoney) ? htmlspecialchars($MlangPrintAutoFildView_DesignMoney) : htmlspecialchars($DesignMoney) ?>"></td>
    </tr>

    <tr>
        <td>&nbsp;&nbsp;</td>
        <td>
            <?php if ($code == "Modify") { ?>
                <input type="submit" value=" 수정 합니다.">
            <?php } else { ?>
                <input type="submit" value=" 저장 합니다.">
            <?php } ?>
        </td>
    </tr>
</form>
</table>

<?php
}

 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 if ($mode == "form_ok") {
	 $stmt = $db->prepare("INSERT INTO $TABLE (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	 $stmt->bind_param("ssddsdds", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo);
 
	 if ($stmt->execute()) {
		 echo "
			 <script language='javascript'>
				 alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
				 opener.parent.location.reload();
			 </script>
			 <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&Ttable=" . htmlspecialchars($Ttable) . "'>";
	 } else {
		 echo "<script language='javascript'>alert('DB 저장 중 오류가 발생했습니다.');</script>";
	 }
	 $stmt->close();
	 exit;
 }
 
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 if ($mode == "Modify_ok") {
	 $stmt = $db->prepare("UPDATE $TABLE SET style = ?, Section = ?, quantity = ?, money = ?, TreeSelect = ?, DesignMoney = ?, POtype = ?, quantityTwo = ? WHERE no = ?");
	 $stmt->bind_param("ssddsddsi", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no);
 
	 if ($stmt->execute()) {
		 echo "
			 <script language='javascript'>
				 alert('\\n정보를 정상적으로 수정하였습니다.\\n');
				 opener.parent.location.reload();
			 </script>
			 <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=form&code=Modify&no=" . htmlspecialchars($no) . "&Ttable=" . htmlspecialchars($Ttable) . "'>";
	 } else {
		 echo "<script language='javascript'>alert('DB 수정 중 오류가 발생했습니다.'); history.go(-1);</script>";
	 }
	 $stmt->close();
	 $db->close();
	 exit;
 }
 
 ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 if ($mode == "delete") {
	 $stmt = $db->prepare("DELETE FROM $TABLE WHERE no = ?");
	 $stmt->bind_param("i", $no);
 
	 if ($stmt->execute()) {
		 echo "
			 <script language='javascript'>
				 alert('$no 번 자료를 삭제 처리 하였습니다.');
				 opener.parent.location.reload();
				 window.self.close();
			 </script>";
	 } else {
		 echo "<script language='javascript'>alert('삭제 중 오류가 발생했습니다.');</script>";
	 }
	 $stmt->close();
	 $db->close();
	 exit;
 }
 ?> ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


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

        ////////////////////////////////////////////////////////////////////////////////////////////////
        function TypeCheck(s, spc) {
            for (var i = 0; i < s.length; i++) {
                if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                    return false;
                }
            }
            return true;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////

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

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="coolBar">

    <br>
    <p align="center">
    <form name="AdminPassKleInfo" method="post" onsubmit="return AdminPassKleCheckField()" action="<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="IncFormOk">

        <table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
            <tr>
                <td bgcolor="#6699CC" class="td11" colspan="2">
                    아래의 가격을 숫자로 변경 가능합니다.
                </td>
            </tr>
            <tr>
                <td align="center">디자인 가격</td>
                <td><input type="text" name="moeny" maxlength="10" size="15" value="<?php echo  htmlspecialchars($DesignMoney) ?>"> 원</td>
            </tr>

            <tr>
                <td bgcolor="#6699CC" class="td11" colspan="2">
                    <font style="color:#FFFFFF; line-height:130%;">
                        아래의 내용은 마우스를 대면 나오는 설명글 입니다, 사진/내용을 입력하지 않으면 자동으로 호출되지 않습니다.<br>
                        기존 사진자료가 있을경우 자료를 지우려면 사진 미입력 후 체크버튼에 체크만 하시면 자료가 지워집니다.<br>
                        <font color="red">*</font> 문구 입력시 HTML을 인식, 엔터를 치면 자동으로 줄바꿈이 처리됩니다. `#` 입력 시 공백 한 칸, `##` 입력 시 공백 두 칸이 추가됩니다.
                    </font>
                </td>
            </tr>

            <!-- 이미지 업로드 및 미리보기 영역 -->
            <?php
            $sections = [
                ["종류", "Section1", "ImgOne", "File1", "ImeOneChick"],
                ["종이규격", "Section2", "ImgTwo", "File2", "ImeTwoChick"],
                ["종이종류", "Section3", "ImgTree", "File3", "ImeTreeChick"],
                ["수량", "Section4", "ImgFour", "File4", "ImeFourChick"],
                ["디자인", "Section5", "ImgFive", "File5", "ImeFiveChick"],
            ];

            foreach ($sections as $section) {
                list($label, $textareaName, $imgVar, $fileInputName, $checkboxName) = $section;
                $imgValue = $$imgVar;
            ?>
                <tr>
                    <td align="center"><?php echo  htmlspecialchars($label) ?></td>
                    <td>
                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <textarea name="<?php echo  $textareaName ?>" rows="4" cols="50"><?php echo  htmlspecialchars($$textareaName) ?></textarea>
                                </td>
                                <td align="center">
                                    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center">
                                                <input type="file" name="<?php echo  $fileInputName ?>" size="20">
                                                <?php if (!empty($imgValue)) { ?><br>
                                                    <input type="checkbox" name="<?php echo  $checkboxName ?>">이미지를 변경하시려면 체크를 해주세요
                                                    <input type="hidden" name="<?php echo  $fileInputName ?>_Y" value="<?php echo  htmlspecialchars($imgValue) ?>">
                                                <?php } ?>
                                            </td>
                                            <?php if (!empty($imgValue)) { ?>
                                                <td align="center">
                                                    <img src="<?php echo  htmlspecialchars($upload_dir . "/" . $imgValue) ?>" width="80" height="95" border="0">
                                                </td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php
            }
            ?>

        </table>

        <br>
        <input type="submit" value="수정합니다">
        <input type="button" value="창 닫기" onclick="window.self.close();">
    </form>

<?php
    exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "IncFormOk") { // inc 파일 결과 처리 

    // 이미지 파일 처리 설정
    $fileSettings = [
        ["name" => "File1", "check" => "ImeOneChick", "old" => "File1_Y", "new" => "File1NAME", "upload" => "$T_DirUrl/Upload_1.php"],
        ["name" => "File2", "check" => "ImeTwoChick", "old" => "File2_Y", "new" => "File2NAME", "upload" => "$T_DirUrl/Upload_2.php"],
        ["name" => "File3", "check" => "ImeTreeChick", "old" => "File3_Y", "new" => "File3NAME", "upload" => "$T_DirUrl/Upload_3.php"],
        ["name" => "File4", "check" => "ImeFourChick", "old" => "File4_Y", "new" => "File4NAME", "upload" => "$T_DirUrl/Upload_4.php"],
        ["name" => "File5", "check" => "ImeFiveChick", "old" => "File5_Y", "new" => "File5NAME", "upload" => "$T_DirUrl/Upload_5.php"],
    ];

    foreach ($fileSettings as $file) {
        $fileName = $file['name'];
        $checkName = $file['check'];
        $oldFile = $file['old'];
        $newFile = $file['new'];
        $uploadScript = $file['upload'];

        // 체크박스가 체크되었을 경우
        if ($$checkName == "on") {
            if ($$fileName) {
                if ($$oldFile && file_exists("$upload_dir/$$oldFile")) {
                    unlink("$upload_dir/$$oldFile");
                }
                include $uploadScript;
            } elseif ($$oldFile && file_exists("$upload_dir/$$oldFile")) {
                unlink("$upload_dir/$$oldFile");
            }
        } else {
            $$newFile = $$oldFile ? $$oldFile : ($$fileName ? include $uploadScript : "");
        }
    }

    // 파일 쓰기 작업
    if ($fp = fopen($T_DirFole, "w")) {
        fwrite($fp, "<?php\n");
        fwrite($fp, "\$DesignMoney = \"" . addslashes($moeny) . "\";\n");
        fwrite($fp, "\$SectionOne = \"" . addslashes($Section1) . "\";\n");
        fwrite($fp, "\$SectionTwo = \"" . addslashes($Section2) . "\";\n");
        fwrite($fp, "\$SectionTree = \"" . addslashes($Section3) . "\";\n");
        fwrite($fp, "\$SectionFour = \"" . addslashes($Section4) . "\";\n");
        fwrite($fp, "\$SectionFive = \"" . addslashes($Section5) . "\";\n");
        fwrite($fp, "\$ImgOne = \"" . addslashes($File1NAME) . "\";\n");
        fwrite($fp, "\$ImgTwo = \"" . addslashes($File2NAME) . "\";\n");
        fwrite($fp, "\$ImgTree = \"" . addslashes($File3NAME) . "\";\n");
        fwrite($fp, "\$ImgFour = \"" . addslashes($File4NAME) . "\";\n");
        fwrite($fp, "\$ImgFive = \"" . addslashes($File5NAME) . "\";\n");
        fwrite($fp, "?>");
        fclose($fp);
    } else {
        echo "<script>alert('파일 쓰기 오류가 발생했습니다.');</script>";
        exit;
    }

    echo "<script language='javascript'>
        alert('수정 완료....*^^*\\n\\n" . htmlspecialchars($WebSoftCopyright) . "');
    </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=IncForm'>";
    exit;
}
?>
