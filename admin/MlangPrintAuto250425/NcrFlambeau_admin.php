<?php
include "../../db.php";
include "../config.php";

$code = $_GET['code'] ?? $_POST['code'] ?? '';

$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "NcrFlambeau";

include "$T_DirUrl/ConDb.php";
$T_DirFole = "$T_DirUrl/$T_TABLE/inc.php";
$TABLE = "MlangPrintAuto_{$T_TABLE}";

$mode = $_GET['mode'] ?? $_POST['mode'] ?? null; // Initialize $mode from GET or POST request
if ($mode === "form") {
    include "../title.php";
    include $T_DirFole;
    $Bgcolor1 = "408080";
}
    if ($code === "Modify") {
        include "./{$T_TABLE}_NoFild.php";
    }
?>
<head>
<style>
.Left1 { font-size: 10pt; color: #FFFFFF; font-weight: bold; }
</style>
<script>
const NUM = "0123456789.";
const SALPHA = "abcdefghijklmnopqrstuvwxyz";
const ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, validChars) {
  for (let i = 0; i < s.length; i++) {
    if (validChars.indexOf(s.charAt(i)) < 0) {
      return false;
    }
  }
  return true;
}

function MemberXCheckField() {
  const f = document.myForm;

  if (f.RadOne.value === "#" || f.RadOne.value === "==================") {
    alert("<?php echo $View_TtableC?> [인쇄색상] 을 선택하여주세요!!");
    f.RadOne.focus();
    return false;
  }

  if (f.myListTreeSelect.value === "#" || f.myListTreeSelect.value === "==================") {
    alert("<?php echo $View_TtableC?>[종이종류] 을 선택하여주세요!!");
    f.myListTreeSelect.focus();
    return false;
  }

  if (f.myList.value === "#" || f.myList.value === "==================") {
    alert("<?php echo $View_TtableC?>[종이규격] 을 선택하여주세요!!");
    f.myList.focus();
    return false;
  }

  if (f.quantity.value.trim() === "") {
    alert("수량을 입력하여주세요!!");
    f.quantity.focus();
    return false;
  }

  if (!TypeCheck(f.quantity.value, NUM)) {
    alert("수량은 숫자로만 입력해 주셔야 합니다.");
    f.quantity.focus();
    return false;
  }

  if (f.money.value.trim() === "") {
    alert("가격을 입력하여주세요!!");
    f.money.focus();
    return false;
  }

  if (!TypeCheck(f.money.value, NUM)) {
    alert("가격은 숫자로만 입력해 주셔야 합니다.");
    f.money.focus();
    return false;
  }

  if (f.TDesignMoney.value.trim() === "") {
    alert("디자인비를 입력하여주세요!!");
    f.TDesignMoney.focus();
    return false;
  }

  if (!TypeCheck(f.TDesignMoney.value, NUM)) {
    alert("디자인비는 숫자로만 입력해 주셔야 합니다.");
    f.TDesignMoney.focus();
    return false;
  }

  return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body class="coolBar" style="margin:0;">
<?php if ($code === "Modify") { ?>
  <b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 자료 수정 ▒▒▒▒▒</b><br>
<?php } else { ?>
  <b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 신 자료 입력 ▒▒▒▒▒</b><br>
<?php } ?>

<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo $PHP_SELF?>">
  <table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
    <?php include "{$T_TABLE}_Script.php"; ?>	

    <tr>
      <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">수량&nbsp;&nbsp;</td>
      <td><input type="text" name="quantity" size="20" maxlength="20" value="<?php echo ($code === "Modify") ? $MlangPrintAutoFildView_quantity : ''?>"> 권</td>
    </tr>

    <!--
    <tr>
      <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">수량(옆)&nbsp;&nbsp;</td>
      <td><input type="text" name="quantityTwo" size="20" maxlength="20" value="<?php echo ($code === "Modify") ? $MlangPrintAutoFildView_quantityTwo : ''?>"> 장</td>
    </tr>
    -->

    <tr>
      <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">가격&nbsp;&nbsp;</td>
      <td><input type="text" name="money" size="20" maxlength="20" value="<?php echo ($code === "Modify") ? $MlangPrintAutoFildView_money : ''?>"> 원</td>
    </tr>

    <tr>
      <td bgcolor="#<?php echo $Bgcolor1?>" width="100" class="Left1" align="right">디자인비&nbsp;&nbsp;</td>
      <td><input type="text" name="TDesignMoney" size="20" maxlength="20" value="<?php echo ($code === "Modify") ? $MlangPrintAutoFildView_DesignMoney : $DesignMoney?>"> 원</td>
    </tr>

    <tr>
      <td>&nbsp;&nbsp;</td>
      <td>
        <input type="submit" value="<?php echo ($code === "Modify") ? '수정 합니다.' : '저장 합니다.'?>">
      </td>
    </tr>
  </table>

  <?php
include "../../db.php"; // $db는 mysqli 연결 객체로 가정합니다.

if ($mode === "form_ok") {
    $stmt = $db->prepare("INSERT INTO $TABLE (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo);
    $success = $stmt->execute();

    if ($success) {
        echo "
        <script>
            alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
            opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&Ttable=$Ttable'>";
    } else {
        echo "
        <script>
            alert('DB 저장 에러 발생: {$stmt->error}');
            history.go(-1);
        </script>";
    }
    $stmt->close();
    exit;
}

if ($mode === "Modify_ok") {
    $stmt = $db->prepare("UPDATE $TABLE SET style=?, Section=?, quantity=?, money=?, TreeSelect=?, DesignMoney=?, POtype=?, quantityTwo=? WHERE no=?");
    $stmt->bind_param("ssssssssi", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no);
    $success = $stmt->execute();

    if ($success) {
        echo "
        <script>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
        </script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=Modify&no=$no&Ttable=$Ttable'>";
    } else {
        echo "
        <script>
            alert('DB 수정 에러 발생: {$stmt->error}');
            history.go(-1);
        </script>";
    }
    $stmt->close();
    exit;
}

if ($mode === "delete") {
    $stmt = $db->prepare("DELETE FROM $TABLE WHERE no = ?");
    $stmt->bind_param("i", $no);
    $success = $stmt->execute();

    echo "
    <html>
    <script>
        alert('$no번 자료를 삭제 처리 하였습니다.');
        opener.parent.location.reload();
        window.self.close();
    </script>
    </html>";
    $stmt->close();
    exit;
}

$db->close();
?>


<?php
if ($mode == "IncForm") {
    include "$T_DirFole";
    include "../title.php";
?>

<head>
<script>
var NUM = "0123456789";
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
  for (var i = 0; i < s.length; i++) {
    if (spc.indexOf(s.charAt(i)) < 0) return false;
  }
  return true;
}

function AdminPassKleCheckField() {
  var f = document.AdminPassKleInfo;

  if (f.money.value == "") {
    alert("디자인 가격을 입력하여주세요?");
    f.money.focus();
    return false;
  }
  if (!TypeCheck(f.money.value, NUM)) {
    alert("디자인 가격은 숫자로만 입력해 주셔야 합니다.");
    f.money.focus();
    return false;
  }
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<br>
<p align="center">
<form name="AdminPassKleInfo" method="post" onsubmit="return AdminPassKleCheckField()" action="<?php echo $PHP_SELF?>" enctype="multipart/form-data">
<input type="hidden" name="mode" value="IncFormOk">

<table border="1" width="100%" align="center" cellpadding="5" cellspacing="0">
<tr><td bgcolor="#6699CC" class="td11" colspan="2">아래의 가격을 숫자로 변경 가능합니다.</td></tr>
<tr>
  <td align="center">디자인 가격</td>
  <td><input type="text" name="money" maxlength="10" size="15" value="<?php echo $DesignMoney?>"> 원</td>
</tr>

<tr><td bgcolor="#6699CC" class="td11" colspan="2">
<font style="color:#FFFFFF; line-height:130%;">
아래의 내용은 마우스를 대면 나오는 설명글 입니다, 사진/내용을 입력하지 않으면 자동으로 호출되지 않습니다.<br>
기존 사진자료가 있을경우 자료를 지우려면 사진 미입력 후 체크만 하시면 자료가 지워집니다.<br>
<font color="red">*</font> 문구 입력시 HTML을 인식, 엔터를 치면 자동 br 로 처리, # 입력시 공백 하나, ##(두개) 입력시 공백 두 칸씩으로 처리됨
</font>
</td></tr>

<?php
// 반복되는 섹션을 배열로 구성하여 처리
$sections = [
  ['label' => '인쇄색상', 'name' => 'Section1', 'img' => $ImgOne, 'file' => 'File1', 'check' => 'ImeOneChick', 'text' => $SectionOne],
  ['label' => '종이종류', 'name' => 'Section3', 'img' => $ImgTree, 'file' => 'File3', 'check' => 'ImeTreeChick', 'text' => $SectionTree],
  ['label' => '종이규격', 'name' => 'Section2', 'img' => $ImgTwo, 'file' => 'File2', 'check' => 'ImeTwoChick', 'text' => $SectionTwo],
  ['label' => '수량',     'name' => 'Section4', 'img' => $ImgFour, 'file' => 'File4', 'check' => 'ImeFourChick', 'text' => $SectionFour],
  ['label' => '디자인',   'name' => 'Section5', 'img' => $ImgFive, 'file' => 'File5', 'check' => 'ImeFiveChick', 'text' => $SectionFive],
];

foreach ($sections as $sec) {
?>
<tr>
  <td align="center"><?php echo $sec['label']?></td>
  <td>
    <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td align="center"><textarea name="<?php echo $sec['name']?>" rows="4" cols="50"><?php echo $sec['text']?></textarea></td>
      <td align="center">
        <table border="0" width="100%">
          <tr>
            <td align="center">
              <input type="file" name="<?php echo $sec['file']?>" size="20">
              <?php if ($sec['img']) { ?>
              <br><input type="checkbox" name="<?php echo $sec['check']?>">이미지를 변경하시려면 체크해주세요
              <input type="hidden" name="<?php echo $sec['file']?>_Y" value="<?php echo $sec['img']?>">
              <?php } ?>
            </td>
            <?php if ($sec['img']) { ?>
            <td align="center">
              <img src="<?php echo $upload_dir?>/<?php echo $sec['img']?>" width="80" height="95" border="0">
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
<input type="button" value="창 닫기" onclick="window.self.close();">
</p>
</form>

<?php
exit;
} // End of IncForm block
?>

<?php
if ($mode === "IncFormOk") {
    // 업로드된 이미지 처리 함수
    function handleImageUpload($checkFlag, $fileField, $fileFieldY, $uploadScript, $index, &$fileNameVar)
    {
        global $upload_dir, $T_DirUrl;

        $file = $_FILES[$fileField] ?? null;
        $prev = $_POST[$fileFieldY] ?? '';
        $checked = isset($_POST[$checkFlag]);

        if ($checked) {
            if ($file && $file['name']) {
                if ($prev && file_exists("$upload_dir/$prev")) {
                    unlink("$upload_dir/$prev");
                }
                include "$T_DirUrl/$uploadScript";
            } else {
                if ($prev && file_exists("$upload_dir/$prev")) {
                    unlink("$upload_dir/$prev");
                }
            }
        } else {
            if ($prev) {
                $fileNameVar = $prev;
            } elseif ($file && $file['name']) {
                include "$T_DirUrl/$uploadScript";
            }
        }
    }

    // 각 파일 업로드 처리
    handleImageUpload('ImeOneChick',  'File1', 'File1_Y', 'Upload_1.php', 1, $File1NAME);
    handleImageUpload('ImeTwoChick',  'File2', 'File2_Y', 'Upload_2.php', 2, $File2NAME);
    handleImageUpload('ImeTreeChick', 'File3', 'File3_Y', 'Upload_3.php', 3, $File3NAME);
    handleImageUpload('ImeFourChick', 'File4', 'File4_Y', 'Upload_4.php', 4, $File4NAME);
    handleImageUpload('ImeFiveChick', 'File5', 'File5_Y', 'Upload_5.php', 5, $File5NAME);

    // POST 데이터
    $money = $_POST['money'] ?? '';
    $Section1 = $_POST['Section1'] ?? '';
    $Section2 = $_POST['Section2'] ?? '';
    $Section3 = $_POST['Section3'] ?? '';
    $Section4 = $_POST['Section4'] ?? '';
    $Section5 = $_POST['Section5'] ?? '';

    // inc.php 파일 작성
    $fp = fopen($T_DirFole, "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$DesignMoney = \"$money\";\n");
    fwrite($fp, "\$SectionOne = \"$Section1\";\n");
    fwrite($fp, "\$SectionTwo = \"$Section2\";\n");
    fwrite($fp, "\$SectionTree = \"$Section3\";\n");
    fwrite($fp, "\$SectionFour = \"$Section4\";\n");
    fwrite($fp, "\$SectionFive = \"$Section5\";\n");
    fwrite($fp, "\$ImgOne = \"$File1NAME\";\n");
    fwrite($fp, "\$ImgTwo = \"$File2NAME\";\n");
    fwrite($fp, "\$ImgTree = \"$File3NAME\";\n");
    fwrite($fp, "\$ImgFour = \"$File4NAME\";\n");
    fwrite($fp, "\$ImgFive = \"$File5NAME\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo "<script>
        alert('수정 완료....*^^*\\n\\n$WebSoftCopyright');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=IncForm'>";
    exit;
}
?>
