<?php
////////////////// 관리자 로그인 ////////////////////
include "../../db.php";
include "../config.php";

// 변수 초기화
$T_DirUrl = "../../MlangPrintAuto";
$T_TABLE = "sticker";

// ${} 대신 {$} 사용
include "{$T_DirUrl}/ConDb.php";  
$T_DirFole = "{$T_DirUrl}/{$T_TABLE}/inc.php";  
$TABLE = "MlangPrintAuto_{$T_TABLE}";  
 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$mode = $_GET['mode'] ?? $_POST['mode'] ?? null;
$code = $_GET['code'] ?? $_POST['code'] ?? null;
$Ttable = $_GET['Ttable'] ?? $_POST['Ttable'] ?? null;
$RadOne = $_POST['RadOne'] ?? '';
$myList = $_POST['myList'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$money = $_POST['money'] ?? '';
$myListTreeSelect = $_POST['myListTreeSelect'] ?? '';
$TDesignMoney = $_POST['TDesignMoney'] ?? '';
$POtype = $_POST['POtype'] ?? '';
$quantityTwo = $_POST['quantityTwo'] ?? '';
$no = $_POST['no'] ?? $_GET['no'] ?? null;
$PHP_SELF = $_SERVER['PHP_SELF'];
$MlangPrintAutoFildView_POtype = $_POST['POtype'] ?? '';
$MlangPrintAutoFildView_quantity = $_POST['quantity'] ?? '';
$MlangPrintAutoFildView_quantityTwo = $_POST['quantityTwo'] ?? '';
$MlangPrintAutoFildView_money = $_POST['money'] ?? '';
$MlangPrintAutoFildView_DesignMoney = $_POST['TDesignMoney'] ?? '';
$MlangPrintAutoFildView_style = $_POST['RadOne'] ?? '';
$MlangPrintAutoFildView_TreeSelect = $_POST['myListTreeSelect'] ?? '';
$MlangPrintAutoFildView_Section = $_POST['myList'] ?? '';
$View_TtableC = '';

if ($mode === "form") {
    include "../title.php";
    include "$T_DirFole";
    $Bgcolor1 = "408080";
  
    if ($code === "Modify") include "./${T_TABLE}_NoFild.php";
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
          for (let i = 0; i < s.length; i++) {
              if (spc.indexOf(s.substring(i, i + 1)) < 0) return false;
          }
          return true;
      }

      function MemberXCheckField() {
          var f = document.myForm;

          if (f.RadOne.value == "#" || f.RadOne.value == "==================") {
              alert("<?php echo $View_TtableC?> [인쇄색상] 을 선택하여주세요!!");
              f.RadOne.focus(); return false;
          }
          if (f.myListTreeSelect.value == "#" || f.myListTreeSelect.value == "==================") {
              alert("<?php echo $View_TtableC?>[종이종류] 을 선택하여주세요!!");
              f.myListTreeSelect.focus(); return false;
          }
          if (f.myList.value == "#" || f.myList.value == "==================") {
              alert("<?php echo $View_TtableC?>[종이규격] 을 선택하여주세요!!");
              f.myList.focus(); return false;
          }
          if (f.quantity.value === "") {
              alert("수량을 입력하여주세요!!");
              f.quantity.focus(); return false;
          }
          if (!TypeCheck(f.quantity.value, NUM)) {
              alert("수량은 숫자로만 입력해 주셔야 합니다.");
              f.quantity.focus(); return false;
          }
          if (f.quantityTwo.value === "") {
              alert("수량(옆)을 입력하여주세요!!");
              f.quantityTwo.focus(); return false;
          }
          if (f.money.value === "") {
              alert("가격을 입력하여주세요!!");
              f.money.focus(); return false;
          }
          if (!TypeCheck(f.money.value, NUM)) {
              alert("가격은 숫자로만 입력해 주셔야 합니다.");
              f.money.focus(); return false;
          }
          if (f.TDesignMoney.value === "") {
              alert("디자인비 을 입력하여주세요!!");
              f.TDesignMoney.focus(); return false;
          }
          if (!TypeCheck(f.TDesignMoney.value, NUM)) {
              alert("디자인비 은 숫자로만 입력해 주셔야 합니다.");
              f.TDesignMoney.focus(); return false;
          }

          return true;
      }
  </script>
  <script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body style="margin:0;" class="coolBar">

<?php if ($code === "Modify"): ?>
  <b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 자료 수정 ▒▒▒▒▒</b><br>
<?php else: ?>
  <b>&nbsp;&nbsp;▒ <?php echo $View_TtableC?> 신 자료 입력 ▒▒▒▒▒</b><br>
<?php endif; ?>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="5">
<form name="myForm" method="post" onsubmit="return MemberXCheckField()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="mode" value="<?php echo $code === 'Modify' ? 'Modify_ok' : 'form_ok'; ?>">
    <input type="hidden" name="Ttable" value="<?php echo htmlspecialchars($Ttable); ?>">
<?php include "{$T_TABLE}_Script.php"; ?>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>인쇄면&nbsp;&nbsp;</td>
<td>
<select name="POtype">
<option value='1' <?php if ($MlangPrintAutoFildView_POtype == "1") echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'"; ?>>단면</option>
<option value='2' <?php if ($MlangPrintAutoFildView_POtype == "2") echo "selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'"; ?>>양면</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>수량&nbsp;&nbsp;</td>
<td><input type="text" name="quantity" size=20 maxLength='20' value='<?php if ($code == "Modify") echo $MlangPrintAutoFildView_quantity; ?>'>연</td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>수량(옆)&nbsp;&nbsp;</td>
<td><input type="text" name="quantityTwo" size=20 maxLength='20' value='<?php if ($code == "Modify") echo $MlangPrintAutoFildView_quantityTwo; ?>'>장</td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>가격&nbsp;&nbsp;</td>
<td><input type="text" name="money" size=20 maxLength='20' value='<?php if ($code == "Modify") echo $MlangPrintAutoFildView_money; ?>'></td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>디자인비&nbsp;&nbsp;</td>
<td><input type="text" name="TDesignMoney" size=20 maxLength='20' value='<?php if ($code == "Modify") { echo $MlangPrintAutoFildView_DesignMoney; } else { echo $DesignMoney; } ?>'></td>
</tr>
<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type="hidden" name="mode" value="form_ok">
<input type="hidden" name="Ttable" value="<?php echo $Ttable?>">
<input type='submit' value='<?php echo ($code == "Modify") ? "수정" : "저장"; ?> 합니다.'>
</td>
</tr>
</form>
</table>
    <?php
} ?>

<?php
// 폼 저장
if ($mode === "form_ok") {
    $db = mysqli_connect($host, $user, $password, $dataname);
    $stmt = mysqli_prepare($db, "INSERT INTO $TABLE (style, Section, quantity, money, TreeSelect, DesignMoney, POtype, quantityTwo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssssss", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo ("<script>
        alert('\\n자료를 정상적으로 저장 하였습니다.\\n');
        opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=form&Ttable=$Ttable'>");
    exit;
}

// 수정 처리
elseif ($mode === "Modify_ok") {
    $db = mysqli_connect($host, $user, $password, $dataname);
    $stmt = mysqli_prepare($db, "UPDATE $TABLE SET style=?, Section=?, quantity=?, money=?, TreeSelect=?, DesignMoney=?, POtype=?, quantityTwo=? WHERE no=?");
    mysqli_stmt_bind_param($stmt, "sssssssss", $RadOne, $myList, $quantity, $money, $myListTreeSelect, $TDesignMoney, $POtype, $quantityTwo, $no);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        echo "<script>alert(\"DB 접속 에러입니다!\"); history.go(-1);</script>";
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($db);

    echo ("<script>
        alert('\\n정보를 정상적으로 수정하였습니다.\\n');
        opener.parent.location.reload();
    </script>
    <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=form&code=Modify&no=$no&Ttable=$Ttable'>");
    exit;
}

// 삭제
elseif ($mode === "delete") {
    $db = mysqli_connect($host, $user, $password, $dataname);
    $stmt = mysqli_prepare($db, "DELETE FROM $TABLE WHERE no = ?");
    mysqli_stmt_bind_param($stmt, "s", $no);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($db);

    echo ("<html>
    <script>
        alert('$no번 자료을 삭제 처리 하였습니다.');
        opener.parent.location.reload();
        window.close();
    </script>
    </html>");
    exit;
}
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 ?> 
 
 <?php
// inc 파일을 수정하는 폼
if ($mode === "IncForm") {
    // 기본값 먼저 설정
    $DesignMoney = "0";
    $SectionOne = "";
    $SectionTwo = "";
    $SectionTree = "";
    $SectionFour = "";
    $SectionFive = "";
    $ImgOne = "";
    $ImgTwo = "";
    $ImgTree = "";
    $ImgFour = "";
    $ImgFive = "";
    
    // inc.php 파일에서 기존 데이터 로드 (있으면 덮어쓰기)
    if (file_exists($T_DirFole)) {
        include "$T_DirFole";
    }
    
    include "../title.php";
?>

<head>
<script>
var NUM = "0123456789";
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (let i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) return false;
    }
    return true;
}

function AdminPassKleCheckField() {
    var f = document.AdminPassKleInfo;

    if (f.moeny.value === "") {
        alert("디자인 가격을 입력하여주세요?");
        f.moeny.focus(); return false;
    }
    if (!TypeCheck(f.moeny.value, NUM)) {
        alert("디자인 가격은 숫자로만 입력해 주셔야 합니다.");
        f.moeny.focus(); return false;
    }

    return true;
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<BR>
<p align=center>
<form name='AdminPassKleInfo' method='post' OnSubmit='return AdminPassKleCheckField()' action='<?php echo $PHP_SELF?>' enctype='multipart/form-data'>
<INPUT TYPE="hidden" name='mode' value='IncFormOk'>

<table border=1 width=100% align=center cellpadding='5' cellspacing='0'>
<tr><td bgcolor='#6699CC' class='td11' colspan=2>아래의 가격을 숫자로 변경 가능합니다.</td></tr>
<tr>
<td align=center>디자인 가격</td>
<td><input type='text' name='moeny' maxLength='10' size='15' value='<?php echo $DesignMoney?>'> 원</td>
</tr>
<tr>
    <td bgcolor='#6699CC' class='td11' colspan=2><font style='color:#FFFFFF; line-height:130%;'>
아래의 내용은 마우스를 대면 나오는 설명글 입니다, 사진/내용 을 입력하지 않으면 자동으로 호출되지 않습니다,<br>
기존 사진자료가 있을경우 자료를 지우려면 사진 미입력 후 체크버튼에 체크만 하시면 자료가 지워집니다.<br>
<font color=red>*</font> 문구 입력 시 HTML을 인식, 엔터를 치면 자동 br로 처리, # 입력 시 공백 하나 ##(두개) 입력 시 공백 2칸씩 처리됨</font></td></tr>

<?php
$sections = [
  1 => ['label' => '인쇄색상', 'section' => $SectionOne, 'img' => $ImgOne],
  3 => ['label' => '종이종류', 'section' => $SectionTree, 'img' => $ImgTree],
  2 => ['label' => '종이규격', 'section' => $SectionTwo, 'img' => $ImgTwo],
  4 => ['label' => '수량',     'section' => $SectionFour, 'img' => $ImgFour],
  5 => ['label' => '디자인',   'section' => $SectionFive, 'img' => $ImgFive],
];

foreach ($sections as $num => $data) {
?>
<tr>
<td align=center><?php echo $data['label']?></td>
<td>
  <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
    <tr>
      <td align=center><TEXTAREA NAME="Section<?php echo $num?>" ROWS="4" COLS="50"><?php echo $data['section']?></TEXTAREA></td>
      <td align=center>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
          <tr>
            <td align=center>
              <input type='file' name='File<?php echo $num?>' size='20'>
              <?php if ($data['img']) { ?>
              <br><input type="checkbox" name="Ime<?php echo $num?>Chick">이미지를 변경하시려면 체크해 주세요
              <input type="hidden" name='File<?php echo $num?>_Y' value='<?php echo $data['img']?>'>
              <?php } ?>
            </td>
            <?php if ($data['img']) { ?>
            <td align=center>
              <img src='<?php echo $upload_dir?>/<?php echo $data['img']?>' width=80 height=95 border=0>
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
<input type='submit' value='수정합니다'>
<input type='button' value='창 닫기' onClick='window.self.close();'>
</p>
</form>

<?php
exit;
} //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<?php
// inc 파일 결과 처리
if ($mode === "IncFormOk") {
    // 기존 데이터 로드 (inc.php에서)
    if (file_exists($T_DirFole)) {
        include "$T_DirFole";
    }
    
    // POST 데이터 받기
    $moeny = $_POST['moeny'] ?? $DesignMoney ?? "0";
    $Section1 = $_POST['Section1'] ?? $SectionOne ?? "";
    $Section2 = $_POST['Section2'] ?? $SectionTwo ?? "";
    $Section3 = $_POST['Section3'] ?? $SectionTree ?? "";
    $Section4 = $_POST['Section4'] ?? $SectionFour ?? "";
    $Section5 = $_POST['Section5'] ?? $SectionFive ?? "";
    
    // 체크박스 데이터
    $ImeOneChick = $_POST['Ime1Chick'] ?? '';
    $ImeTwoChick = $_POST['Ime2Chick'] ?? '';
    $ImeTreeChick = $_POST['Ime3Chick'] ?? '';
    $ImeFourChick = $_POST['Ime4Chick'] ?? '';
    $ImeFiveChick = $_POST['Ime5Chick'] ?? '';
    
    // 기존 파일명
    $File1_Y = $_POST['File1_Y'] ?? $ImgOne ?? '';
    $File2_Y = $_POST['File2_Y'] ?? $ImgTwo ?? '';
    $File3_Y = $_POST['File3_Y'] ?? $ImgTree ?? '';
    $File4_Y = $_POST['File4_Y'] ?? $ImgFour ?? '';
    $File5_Y = $_POST['File5_Y'] ?? $ImgFive ?? '';

    // 파일 업로드 처리 함수
    function handleImageUpload($field, $existing, $chkFlag, $uploadScript, $upload_dir) {
        $newName = $existing; // 기본적으로 기존 파일명 유지

        if ($chkFlag === "on") {
            // 체크박스가 체크된 경우 (이미지 변경/삭제)
            if ($_FILES[$field]['name']) {
                // 새 파일이 업로드된 경우
                if ($existing && file_exists("$upload_dir/$existing")) {
                    unlink("$upload_dir/$existing");
                }
                include "$uploadScript";
                // Upload 스크립트에서 $newName이 설정됨
            } else {
                // 새 파일이 없으면 기존 파일 삭제
                if ($existing && file_exists("$upload_dir/$existing")) {
                    unlink("$upload_dir/$existing");
                }
                $newName = "";
            }
        } else {
            // 체크박스가 체크되지 않은 경우
            if ($_FILES[$field]['name']) {
                // 새 파일이 업로드된 경우에만 변경
                if ($existing && file_exists("$upload_dir/$existing")) {
                    unlink("$upload_dir/$existing");
                }
                include "$uploadScript";
                // Upload 스크립트에서 $newName이 설정됨
            }
            // 그렇지 않으면 기존 파일명 유지
        }

        return $newName;
    }

    // 업로드 실행
    $File1NAME = handleImageUpload('File1', $File1_Y, $ImeOneChick, "$T_DirUrl/Upload_1.php", $upload_dir);
    $File2NAME = handleImageUpload('File2', $File2_Y, $ImeTwoChick, "$T_DirUrl/Upload_2.php", $upload_dir);
    $File3NAME = handleImageUpload('File3', $File3_Y, $ImeTreeChick, "$T_DirUrl/Upload_3.php", $upload_dir);
    $File4NAME = handleImageUpload('File4', $File4_Y, $ImeFourChick, "$T_DirUrl/Upload_4.php", $upload_dir);
    $File5NAME = handleImageUpload('File5', $File5_Y, $ImeFiveChick, "$T_DirUrl/Upload_5.php", $upload_dir);

    // inc.php 내용 작성
    $fwriteContent = "<?php\n";
    $fwriteContent .= "\$DesignMoney=\"" . addslashes($moeny) . "\";\n";
    $fwriteContent .= "\$SectionOne=\"" . addslashes($Section1) . "\";\n";
    $fwriteContent .= "\$SectionTwo=\"" . addslashes($Section2) . "\";\n";
    $fwriteContent .= "\$SectionTree=\"" . addslashes($Section3) . "\";\n";
    $fwriteContent .= "\$SectionFour=\"" . addslashes($Section4) . "\";\n";
    $fwriteContent .= "\$SectionFive=\"" . addslashes($Section5) . "\";\n";
    $fwriteContent .= "\$ImgOne=\"" . addslashes($File1NAME) . "\";\n";
    $fwriteContent .= "\$ImgTwo=\"" . addslashes($File2NAME) . "\";\n";
    $fwriteContent .= "\$ImgTree=\"" . addslashes($File3NAME) . "\";\n";
    $fwriteContent .= "\$ImgFour=\"" . addslashes($File4NAME) . "\";\n";
    $fwriteContent .= "\$ImgFive=\"" . addslashes($File5NAME) . "\";\n";
    $fwriteContent .= "?>";

    // 파일 저장
    if (file_put_contents($T_DirFole, $fwriteContent)) {
        echo ("<script>
            alert('수정이 완료되었습니다.');
        </script>
        <meta http-equiv='Refresh' content='0; URL={$_SERVER['PHP_SELF']}?mode=IncForm'>");
    } else {
        echo ("<script>
            alert('파일 저장 중 오류가 발생했습니다.');
            history.go(-1);
        </script>");
    }
    exit;
}
?>
 