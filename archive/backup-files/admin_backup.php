<?php
include "../../db.php";
include "../config.php";

$T_DirUrl = "../../MlangPrintAuto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";

// 데이터베이스 연결 설정
$db = new mysqli($host, $user, $password, $dataname);

// 연결 오류 확인
if ($db->connect_error) {
    die("데이터베이스 연결 실패: " . $db->connect_error);
}

// 문자셋을 utf-8로 설정
$db->set_charset("utf8");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "ModifyOk") { ////////////////////////////////////////////////////////////////////////////////////////////////////

    $query = "UPDATE MlangOrder_PrintAuto SET Type_1=?, name=?, email=?, zip=?, zip1=?, zip2=?, phone=?, Hendphone=?, bizname=?, bank=?, bankname=?, cont=?, Gensu=? WHERE no=?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('sssssssssssssi', $TypeOne, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $Gensu, $no);

    if (!$stmt->execute()) {
        echo "<script language='javascript'>
                window.alert('DB 업데이트 실패!');
                history.go(-1);
              </script>";
        exit;
    } else {
        echo "<script language='javascript'>
                alert('데이터가 성공적으로 업데이트 되었습니다.');
                opener.parent.location.reload();
              </script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>";
        exit;
    }

    $stmt->close();

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "SubmitOk") { ////////////////////////////////////////////////////////////////////////////////////////////////////

    $Table_result = $db->query("SELECT max(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>
                window.alert('DB 조회 실패!');
                history.go(-1);
              </script>";
        exit;
    }
    $row = $Table_result->fetch_row();

    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 데이터 저장을 위한 디렉토리 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777);
    }

    $date = date("Y-m-d H:i:s");
    $dbinsert = "INSERT INTO MlangOrder_PrintAuto (no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, OrderStyle, phone, Gensu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $db->prepare($dbinsert);
    $stmt_insert->bind_param('isssssssssssssssssssssss', $new_no, $Type, $ImgFolder, $TypeOne, $money_1, $money_2, $money_3, $money_4, $money_5, $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $date, 3, $phone, $Gensu);

    if (!$stmt_insert->execute()) {
        echo "<script language='javascript'>
                window.alert('DB 삽입 실패!');
                history.go(-1);
              </script>";
        exit;
    } else {
        echo "<script language='javascript'>
                alert('데이터가 성공적으로 삽입되었습니다.');
                opener.parent.location.reload();
              </script>";
        echo "<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$new_no'>";
        exit;
    }

    $stmt_insert->close();

} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "BankForm") { ////////////////////////////////////////////////////////////////////////////////////////////////////

    include "../title.php";
    include "int/info.php";
    $Bgcolor1 = "408080";
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=500);
</script>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,screen.availHeight)

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
    for (var i=0; i< s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) {
            return false;
        }
    }        
    return true;
}

/////////////////////////////////////////////////////////////////////////////////

function MemberXCheckField() {
    var f=document.myForm;

    if (f.BankName.value == "") {
        alert("은행명을 입력해주세요!!");
        f.BankName.focus();
        return false;
    }

    if (f.TName.value == "") {
        alert("예금주를 입력해주세요!!");
        f.TName.focus();
        return false;
    }

    if (f.BankNo.value == "") {
        alert("계좌번호를 입력해주세요!!");
        f.BankNo.focus();
        return false;
    }
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=5>

<form name='myForm' method='post' <?php if($code!="Text") {?>OnSubmit='javascript:return MemberXCheckField()'<?php } ?> action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='BankModifyOk'>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;은행 정보 수정</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>사용 여부&nbsp;&nbsp;</td>
<td>
<INPUT TYPE="radio" NAME="SignMMk" <?php if($View_SignMMk=="yes"){?>checked<?php } ?> value='yes'>YES
<INPUT TYPE="radio" NAME="SignMMk" <?php if($View_SignMMk=="no"){?>checked<?php } ?> value='no'>NO
</td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;계좌 정보 입력</b></font>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>은행명&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankName" size=20 maxLength='200' value='<?php echo $View_BankName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>예금주&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="TName" size=20 maxLength='200' value='<?php echo $View_TName?>'></td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>계좌번호&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="BankNo" size=40 maxLength='200' value='<?php echo $View_BankNo?>'></td>
</tr>

<tr>
<td colspan=2 bgcolor='#484848'>
<font color=white><b>&nbsp;&nbsp;TEXT 형식으로 입력</b><br>
&nbsp;&nbsp;&nbsp;&nbsp;*단일인용 부호 ' 사용 불가, 이중인용 부호 " 사용 불가</font>
</td>
</tr>

<?php
if ($ConDb_A) {
    $Si_LIST_script = explode(":", $ConDb_A);
    $k = 0; $kt = 0;
    while($k < sizeof($Si_LIST_script)) {
?>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right><?php echo $Si_LIST_script[$k];?>&nbsp;&nbsp;</td>
<td><TEXTAREA NAME="ContText_<?php echo $kt?>" ROWS="4" COLS="58"><?php $temp = "View_ContText_".$kt; $get_temp=$$temp; echo $get_temp;?></TEXTAREA></td>
</tr>
<?php
        $k=$k+1; $kt=$kt+1;
    } 
} 
?>

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' 저장 '>
</td>
</tr>
</FORM>
</table>
<BR>
<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "BankModifyOk") {

    $fp = fopen("$T_DirFole", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$View_SignMMk=\"$SignMMk\";\n");
    fwrite($fp, "\$View_BankName=\"$BankName\";\n");
    fwrite($fp, "\$View_TName=\"$TName\";\n");
    fwrite($fp, "\$View_BankNo=\"$BankNo\";\n");

    if ($ConDb_A) {
        $Si_LIST_script = explode(":", $ConDb_A);
        $k = 0; $kt = 0;
        while($k < sizeof($Si_LIST_script)) {
            $tempTwo = "ContText_".$kt; $get_tempTwo=$$tempTwo;
            fwrite($fp, "\$View_ContText_${kt}=\"$get_tempTwo\";\n");
            $k=$k+1; $kt=$kt+1;
        } 
    } 

    fwrite($fp, "?>");
    fclose($fp);

    echo "<script language=javascript>
    window.alert('저장 완료....*^^*');
    </script>
    <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=BankForm'>";
    exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "OrderView") {

    include "../title.php";
 
    if ($no) {
        $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
        $stmt->bind_param('i', $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {

            if ($row['OrderStyle'] == "2") {
                $query = "UPDATE MlangOrder_PrintAuto SET OrderStyle = '3' WHERE no = ?";
                $stmt_update = $db->prepare($query);
                $stmt_update->bind_param('i', $no);
                $stmt_update->execute();

                echo "<script language=javascript>
                opener.parent.location.reload();
                </script>";
            } 
        }
        $stmt->close();
    }
?>

<style>
a.file:link,  a.file:visited {font-family:굴림; font-size: 10pt; color:#336699; line-height:130%; text-decoration:underline}
a.file:hover, a.file:active {font-family:굴림; font-size: 10pt; color:#333333; line-height:130%; text-decoration:underline}
</style>

<?php
    $ViewDiwr = "../../MlangOrder_PrintAuto";
    include "$ViewDiwr/OrderFormOrderTree.php";
?>

<?php if ($no) { ?>
<BR>
<font style='font:bold; color:#336699;'>* 첨부 파일 *</font> 파일명을 클릭하시면 다운로드/보기 하실 수 있습니다.  =============================<BR>
<table border=0 align=center width=100% cellpadding=20 cellspacing=0>
<tr>
<td>

<?php 
if (is_dir("../../ImgFolder/$View_ImgFolder")) {
    
    $dir_path = "../../ImgFolder/$View_ImgFolder"; 

    if ($View_ImgFolder) {
        $dir_handle = opendir($dir_path);

        // 디렉토리 내의 모든 파일들을 읽어들임
        $i = 1;
        while ($tmp = readdir($dir_handle)) {
            if (($tmp != ".") && ($tmp != "..")) {
                echo (is_file($dir_path.$tmp) ? "" : "[$i] 파일: <a href='$dir_path/$tmp' target='_blank' class='file'>$tmp</a><br>");
                $i++;
            }
        }

        closedir($dir_handle);    
    }
}
?>
</td>
</tr>
</table>
===========================================================================================
<?php } ?>
<p align=center>
<?php if ($no) { ?>
<input type='submit' value=' 주문 정보 수정 '>
<?php } else { ?>
<input type='submit' value=' 주문 정보 수정 '>
<?php } ?>
<input type='button' onClick='javascript:window.close();' value=' 닫기 - CLOSE '>
</p>

</td>
</tr>
</table>
</form>
<BR><BR>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if ($mode == "SinForm") {
    include "../title.php";
?>

<head>
<script language=javascript>
self.moveTo(0,0)
self.resizeTo(availWidth=600,availHeight=200)

function MlangFriendSiteCheckField() {
    var f=document.MlangFriendSiteInfo;

    if (f.photofile.value == "") {
        alert("업로드할 이미지를 선택해 주세요.");
        f.photofile.focus();
        return false;
    }

    <?php
    include "$T_DirFole";
    if ($View_SignMMk == "yes") {  // 사용 여부가 "yes"인 경우
    ?>

    if (f.pass.value == "") {
        alert("비밀번호를 입력해 주세요.");
        f.pass.focus();
        return false;
    }

    <?php
    }
    ?>
}

//////////////// 이미지 미리보기 //////////////////////////////////
function Mlamg_image(image) {
    Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
    Mlangwindow.document.open();
    Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
    Mlangwindow.document.write("<body>");
    Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
    Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' " + "onClick='window.close()'></p>");
    Mlangwindow.document.write("</body></html>");
    Mlangwindow.document.close();
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='SinFormModifyOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php if($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?php } ?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>상품/파일 - 추가/수정</font></td>
</td>
</tr>

<tr>
<td align=right>이미지 파일:&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php
if ($View_SignMMk == "yes") {  // 사용 여부가 "yes"인 경우
        $stmt_SignTy = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
        $stmt_SignTy->bind_param('i', $no);
        $stmt_SignTy->execute();
        $result_SignTy = $stmt_SignTy->get_result();
        $row_SignTy = $result_SignTy->fetch_assoc();
        $ViewSignTy_pass = $row_SignTy['pass']; 
?>
<tr>
<td align=right>비밀번호:&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="pass" size=20 value='<?php echo $ViewSignTy_pass?>'>
</td>
</tr>
<?php
        $stmt_SignTy->close();
}
?>

<tr>
<td>&nbsp;</td>
<td>
<?php if($ModifyCode){?>
<input type='submit' value='저장'>
<?php }else{?>
<input type='submit' value='저장'>
<?php }?>
</td>
</tr>

</table>

</form>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if ($mode == "SinFormModifyOk") {

    if ($ModifyCode == "ok") {
        $TOrderStyle = "7";
    } else {
        $TOrderStyle = "6";
    }
    $ModifyCode = $no;

    $stmt = $db->prepare("SELECT * FROM MlangOrder_PrintAuto WHERE no = ?");
    $stmt->bind_param('i', $ModifyCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;
    if ($rows) {
        while ($row = $result->fetch_assoc()) {
            $GF_upfile = $row['ThingCate'];  
        }
    } else {
        echo "<p align=center><b>DB 내에 $ModifyCode 번호의 데이터가 없습니다.</b></p>";
        exit;
    }

    // 데이터 저장을 위한 디렉토리 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$no"; 
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777);
    }

    if ($GF_upfile) {
        if ($photofileModify && $photofile) {
            $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
            include "upload.php";
            unlink("../../MlangOrder_PrintAuto/upload/$no/$GF_upfile");
        } else {
            $photofileNAME = $GF_upfile;
        }
    } else {
        if ($photofile) {
            $upload_dir = "../../MlangOrder_PrintAuto/upload/$no";
            include "upload.php";
        }
    }

    $query = "UPDATE MlangOrder_PrintAuto SET OrderStyle = ?, ThingCate = ?, pass = ? WHERE no = ?";
    $stmt_update = $db->prepare($query);
    $stmt_update->bind_param('sssi', $TOrderStyle, $photofileNAME, $pass, $no);

    if (!$stmt_update->execute()) {
        echo "<script language=javascript>
                window.alert('DB 업데이트 실패!');
                history.go(-1);
              </script>";
        exit;
    } else {
        echo "<script language=javascript>
                alert('데이터가 성공적으로 업데이트 되었습니다.');
                opener.parent.location.reload();
                window.self.close();
              </script>";
    }

    $stmt_update->close();

    $db->close();
    exit;
}
?>

<?php
if ($mode == "AdminMlangOrdert") { ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    include "../title.php";
?>

<head>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=680,availHeight=400);
</script>

<script language=javascript>
function MlangFriendSiteCheckField() {
    var f=document.MlangFriendSiteInfo;

    if((f.MlangFriendSiteInfo[0].checked==false) && (f.MlangFriendSiteInfo[1].checked==false)){
        alert('파일 형식을 선택해 주세요');
        return false;
    }

    if (f.OrderName.value == "") {
        alert("주문번호를 입력해주세요");
        f.OrderName.focus();
        return false;
    }

    if (f.Designer.value == "") {
        alert("디자이너 이름을 입력해주세요");
        f.Designer.focus();
        return false;
    }

    if (f.OrderStyle.value == "0") {
        alert("주문스타일을 선택해주세요");
        f.OrderStyle.focus();
        return false;
    }

    if (f.date.value == "") {
        alert("주문 날짜를 입력해주세요\n\n마우스를 클릭하시면 입력창이 열립니다.");
        f.date.focus();
        return false;
    }

    if (f.photofile.value == "") {
        alert("업로드할 이미지를 선택해 주세요.");
        f.photofile.focus();
        return false;
    }

    <?php
    include "$T_DirFole";
    if ($View_SignMMk == "yes") {  // 사용 여부가 "yes"인 경우
    ?>

    if (f.pass.value == "") {
        alert("비밀번호를 입력해 주세요.");
        f.pass.focus();
        return false;
    }

    <?php
    }
    ?>
}

//////////////// 이미지 미리보기 //////////////////////////////////
function Mlamg_image(image) {
    Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
    Mlangwindow.document.open();
    Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
    Mlangwindow.document.write("<body>");
    Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
    Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' " + "onClick='window.close()'></p>");
    Mlangwindow.document.write("</body></html>");
    Mlangwindow.document.close();
}

// 이미지 파일 형식 선택
function MlangFriendSiteInfocheck() {
    if (MlangFriendSiteInfo(2).checked==true){
        Mlang_go.innerHTML="<select name='ThingNo'><?php
        include"../../mlangprintauto/ConDb.php";
        if ($ConDb_A) {
            $OrderCate_LIST_script = explode(":", $ConDb_A);
            $k = 0;
            while($k < sizeof($OrderCate_LIST_script)) {
                if($OrderCate == $OrderCate_LIST_script[$k]){
                    echo "<OPTION VALUE='$OrderCate_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$OrderCate_LIST_script[$k]</OPTION>";
                } else {
                    echo "<OPTION VALUE='$OrderCate_LIST_script[$k]'>$OrderCate_LIST_script[$k]</OPTION>";
                }
                $k++;
            } 
        } 
        ?></select>"
    }
    if (MlangFriendSiteInfo(3).checked==true){
        Mlang_go.innerHTML="<INPUT TYPE='text' NAME='ThingNo' size='30'>"
    }
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
<SCRIPT LANGUAGE=JAVASCRIPT src='../js/exchange.js'></SCRIPT>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo $Bgcolor_1?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo $PHP_SELF?>'>

<INPUT TYPE="hidden" name='mode' value='AdminMlangOrdertOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php if($ModifyCode){?><INPUT TYPE="hidden" name='ModifyCode' value='ok'><?php }?>

<tr>
<td bgcolor='#6699CC' colspan=2><font style='color:#FFFFFF; font:bold;'>주문/파일 - 추가/수정</font></td>
</td>

<tr>
<td bgcolor='#6699CC' align=right>파일 형식&nbsp;</td>
<td>
<input type="radio" name="MlangFriendSiteInfo" onClick='MlangFriendSiteInfocheck()'>폴더내파일
<input type="radio" name="MlangFriendSiteInfo" onClick='MlangFriendSiteInfocheck()'>파일직접입력
<BR>
<table border=0 align=center width=100% cellpadding=5 cellspacing=0>
<tr>
<td id='Mlang_go'></td>
</tr>
</table>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>주문번호&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="OrderName" size=20> 
<font style='color:#363636; font-size:8pt;'>(주문번호는 고객이 조회하는 기준입니다. 반드시 입력해주세요)</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>디자이너&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="Designer" size=20> 
</td>
</tr>


<tr>
<td bgcolor='#6699CC' align=right>주문스타일&nbsp;</td>
<td>
<select name='OrderStyle'>
<option value='0'>:::선택:::</option>
<option value='6'>파일</option>
<option value='7'>직접입력</option>
</select>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>주문날짜&nbsp;</td>
<td>
<INPUT TYPE="text" NAME="date" size=20 onClick="Calendar(this);">
<font style='color:#363636; font-size:8pt;'>(입력예:2005-08-10 * 마우스를 클릭하시면 입력창이 열립니다 *)</font>
</td>
</tr>

<tr>
<td bgcolor='#6699CC' align=right>이미지 파일&nbsp;</td>
<td>
<INPUT TYPE="hidden" NAME="photofileModify" value='ok'>
<INPUT type="file" Size=45 name="photofile" onChange="Mlamg_image(this.value)">
</td>
</tr>

<?php
if ($View_SignMMk == "yes") {  // 사용 여부가 "yes"인 경우
?>
<tr>
<td bgcolor='#6699CC' align=right>비밀번호&nbsp;</td>
<td>
<INPUT type="text" Size=25 name="pass">
</td>
</tr>
<?php
}
?>

<tr>
<td align=center colspan=2>
<?php if($ModifyCode){?>
<input type='submit' value='저장'>
<?php }else{?>
<input type='submit' value='저장'>
<?php }?>
</td>

</table>

</form>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if ($mode == "AdminMlangOrdertOk") {

    $ToTitle = $ThingNo;
    include "../../mlangprintauto/ConDb.php";

    if (!$ThingNoOkp) {
        $ThingNoOkp = $ThingNo;
    } else {
        $ThingNoOkp = $View_TtableB;
    }

    $Table_result = $db->query("SELECT max(no) FROM MlangOrder_PrintAuto");
    if (!$Table_result) {
        echo "<script>
                window.alert('DB 조회 실패!');
                history.go(-1);
              </script>";
        exit;
    }
    $row = $Table_result->fetch_row();

    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 데이터 저장을 위한 디렉토리 생성
    $dir = "../../MlangOrder_PrintAuto/upload/$new_no"; 
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777);
    }

    if ($photofile) {
        $upload_dir = "$dir";
        include "upload.php";
    }

    // 데이터 삽입
    $dbinsert = "INSERT INTO MlangOrder_PrintAuto (no, ThingNoOkp, ImgFolder, Type_1, Type_2, Type_3, Type_4, Type_5, Type_6, money_1, money_2, money_3, money_4, money_5, OrderName, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, OrderStyle, photofileNAME, pass, Designer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $db->prepare($dbinsert);
    $stmt_insert->bind_param('isssssssssssssssssssssssssssss', $new_no, $ThingNoOkp, $ImgFolder, $Type_1, $Type_2, $Type_3, $Type_4, $Type_5, $Type_6, $money_1, $money_2, $money_3, $money_4, $money_5, $OrderName, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname, $cont, $date, $OrderStyle, $photofileNAME, $pass, $Designer);

    if (!$stmt_insert->execute()) {
        echo "<script language='javascript'>
                window.alert('DB 삽입 실패!');
                history.go(-1);
              </script>";
        exit;
    } else {
        echo "<script language='javascript'>
                alert('데이터가 성공적으로 삽입되었습니다.');
                opener.parent.location.reload();
                window.self.close();
              </script>";
    }

    $stmt_insert->close();

    $db->close();
    exit;

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
