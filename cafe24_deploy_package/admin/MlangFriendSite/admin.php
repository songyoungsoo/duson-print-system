<!DOCTYPE html>
<html lang="ko">
<head>
<title>MlangFriendSite프로그램</title>
<meta http-equiv='Content-type' content='text/html; charset=utf-8'>
<meta name='keywords' content='MlangFriendSite프로그램'>
<meta name='author' content='Mlang'>
<meta name='classification' content='MlangFriendSite프로그램'>
<meta name='description' content='MlangFriendSite프로그램'>

<style>
body, td, input, select, submit {font-family:굴림; font-size: 9pt; color:#000000; font-weight:none; line-height: normal;}
.td11 {font-family:굴림; font-size: 9pt; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td1 {font-family:굴림; font-size: 9pt; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td2 {font-family:굴림; font-size: 9pt; color:#008080; font-weight:none; line-height:130%;}
</style>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php
if ($mode == "AdminModifyPass") {
    include "db.php";
?>

<head>
<script language="javascript">
function AdminPassKleCheckField() {
    var f = document.AdminPassKleInfo;

    if (f.id.value == "") {
        alert("관리ID 을 입력하여주세요?");
        f.id.focus();
        return false;
    }

    if (f.pass.value == "") {
        alert("관리자PASS 을 입력하여주세요?");
        f.pass.focus();
        return false;
    }
}
</script>
</head>

<BR>
<p align=center>
<form name='AdminPassKleInfo' method='post' OnSubmit='javascript:return AdminPassKleCheckField()' action='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>'>
<INPUT TYPE="hidden" name='mode' value='AdminModifyPassOk'>

<table border=0 width=90% align=center cellpadding='5' cellspacing='1' bgcolor='#FFFFFF'>
<tr><td bgcolor='#6699CC' class='td11'>관리자 ID/비빌번호변경</td></tr>
<tr><td align=center>
관리자ID <input type='text' name='id' maxLength='10' size='20' value='<?php echo  htmlspecialchars($adminid) ?>'>&nbsp;&nbsp;
관리자PASS <input type='text' name='pass' maxLength='10' size='20' value='<?php echo  htmlspecialchars($adminpasswd) ?>'>
</td></tr>

<tr><td bgcolor='#6699CC' class='td11'>카테고리명 관리</td></tr>
<tr><td align=center>
<input type='text' name='cate' maxLength='10' size='80' value='<?php echo  htmlspecialchars($AdCate) ?>'>
<BR>
<font style='font-size:8pt; color:#757575;'>(카테고리명에는 절대로 " 쌍콤마 들어가선 안되며 분류는 <b>:</b> 로 해주시기 바랍니다.)</font>
</td></tr>

<tr><td bgcolor='#6699CC' class='td11'>자료호출시 TEXT, PHOTO 형식 - 출력여부</td></tr>
<tr><td align=center>
TEXT <INPUT TYPE="radio" NAME="style" <?php if ($AdStyle == "text") echo "checked"; ?> value='text'>
PHOTO <INPUT TYPE="radio" NAME="style" <?php if ($AdStyle == "photo") echo "checked"; ?> value='photo'>
width <input type='text' name='width' maxLength='10' size='20' value='<?php echo  htmlspecialchars($AdWidth) ?>'>&nbsp;&nbsp;
height <input type='text' name='height' maxLength='10' size='20' value='<?php echo  htmlspecialchars($AdHeight) ?>'>
<BR>
<font style='font-size:8pt; color:#757575;'>(width, height 의 속성은 PHOTO 선택시만 적용되며 이미지의 출력크기를 지정합니다.)</font>
</td></tr>
</table>

<BR>
<input type='submit' value='수정합니다'>
</p>
</form>

<?php
exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "AdminModifyPassOk") {
    include "db.php";

    $fp = fopen("db.php", "w");
    fwrite($fp, "<?php\n");
    fwrite($fp, "\$host=\"$host\";\n");
    fwrite($fp, "\$user=\"$user\";\n");
    fwrite($fp, "\$dataname=\"$dataname\";\n");
    fwrite($fp, "\$password=\"$password\";\n");

    fwrite($fp, "\$adminid=\"$id\";\n");
    fwrite($fp, "\$adminpasswd=\"$pass\";\n");
    fwrite($fp, "\$table=\"MlangFriendSite\";\n");

    fwrite($fp, "\$AdStyle=\"$style\";\n");
    fwrite($fp, "\$AdWidth=\"$width\";\n");
    fwrite($fp, "\$AdHeight=\"$height\";\n");
    fwrite($fp, "\$AdCate=\"$cate\";\n");

    fwrite($fp, "\$db=mysqli_connect(\$host, \$user, \$password, \$dataname);\n");

    fwrite($fp, "\$Copyright=\"Copyright (c) 2004 by <a href='http://www.script.ne.kr' target='_blank'><font style='font-size:8pt; color:#ADADAD;'>스크립트네꺼</font></a> Comp. All right Reserved.\";\n");
    fwrite($fp, "?>");
    fclose($fp);

    echo ("<script language=javascript>
    window.alert('수정 완료....*^^*\\n\\n스크립트네꺼-script.ne.kr');
    opener.parent.location.reload();
    window.self.close();
    </script>");
    exit;
}
?>

<?php
if ($mode == "view") {
    include "db.php";

    $stmt = $db->prepare("SELECT * FROM MlangOrder WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->num_rows;

    if ($rows) {
        while ($row = $result->fetch_assoc()) {
            $BBAdminSelect = $row['AdminSelect'];
?>

<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='#65B1B1'>
<tr><td valign=top>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='#FFFFFF'>
<tr>
<td bgcolor='#65B1B1' width=100 class='td1' align='left'>&nbsp;성  명&nbsp;</td>
<td bgcolor='#FFFFFF'>
<?php echo  htmlspecialchars($row['name']) ?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;나  이&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo  htmlspecialchars($row['nai']) ?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;거주지역&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo  htmlspecialchars($row['house']) ?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;전화번호&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo  htmlspecialchars($row['phone']) ?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담가능시간&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo  htmlspecialchars($row['si']) ?>
</td>
</tr>


<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담 분류&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php echo  htmlspecialchars($row['cont_1']) ?>
</td>
</tr>

<tr>
<td bgcolor='#65B1B1' class='td1' align='left'>&nbsp;상담 내용&nbsp;</td>
<td bgcolor='#FFFFFF' class='td2'>
<?php
        $CONTENT = htmlspecialchars($row['cont_2']);
        $CONTENT = nl2br($CONTENT);
        echo $CONTENT;
?>
</td>
</tr>

</table>

</td></tr></table>

<p align=center>
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
</p>

<?php
        }
    } else {
        echo "<p align=center><b>등록 자료가 없음.</b></p>";
    }

    $db->close();

    if ($BBAdminSelect == "no") {
        include "db.php";
        $stmt = $db->prepare("UPDATE MlangOrder SET AdminSelect='yes' WHERE no = ?");
        $stmt->bind_param("i", $no);
        if (!$stmt->execute()) {
            echo "
                <script language=javascript>
                    window.alert(\"DB 접속 에러입니다!\")
                    history.go(-1);
                </script>";
            exit;
        } else {
            echo "
                <script language=javascript>
                opener.parent.location.reload();
                </script>";
            exit;
        }
    }

    exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    include "db.php";
    $stmt = $db->prepare("DELETE FROM $table WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $db->close();

    echo "
        <script language=javascript>
        alert('\\n정보를 정상적으로 삭제하였습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
        </script>";
    exit;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "AdminSiteSubmit") {
    $Bgcolor_1 = "#FFFFFF";
    $Bgcolor_2 = "#65B1B1";
    $Bgcolor_3 = "#FFFFFF";
    $align_td1 = "left";
    $InputStyle = "style='font-size:10pt; background-color:#DAF8F4; color:#000000; border-style:solid; border:1 solid $Bgcolor_2'";
    include "db.php";

    if ($ModifyCode) include "ViewFild.php";
?>

<head>
<script language=javascript>

var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
    var i;

    for (i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) {
            return false;
        }
    }        
    return true;
}

/////////////////////////////////////////////////////////////////////////////////

function MlangFriendSiteCheckField() {
    var f = document.MlangFriendSiteInfo;

    if (f.title.value == "") {
        alert("사이트 제목 을 입력하여 주세요 *^^*");
        f.title.focus();
        return false;
    }

    if (f.url.value == "") {
        alert("사이트 URL을 입력하여 주세요 *^^*");
        f.url.focus();
        return false;
    }
    if (f.url.value == "") {
        alert("사이트의 URL을 입력 하여 주세요.. *^^*");
        f.url.focus();
        return false;
    }
    if (f.url.value.lastIndexOf(" ") > -1) {
        alert("사이트의 URL 에는 공백이 올수 없습니다... *^^*");
        f.url.focus();
        return false;
    }
    if (f.url.value.lastIndexOf(".") == -1) {
        alert("사이트의 URL을 정상적으로 입력해 주시기 바랍니다... *^^*");
        f.url.focus();
        return false;
    }
    if (f.url.value.lastIndexOf("http://") == -1) {
        alert("http:// 를 포함하여 입력해 주시기 바랍니다... *^^*");
        f.url.focus();
        return false;
    }

    <?php if ($AdCate) { ?>
    if (f.cate.value == "0") {
        alert("사이트의 성격에 맞는 카테고리를 선택 하여 주세요.. *^^*");
        f.cate.focus();
        return false;
    }
    <?php } ?>
}

//////////////// 이미지 미리보기 //////////////////////////////////
/* 소스제작: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {
    Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
    Mlangwindow.document.open();
    Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
    Mlangwindow.document.write("<body>");
    Mlangwindow.document.write("<p align=center><img src=\"" + image + "\"></p>");
    Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='윈도우 닫기' " + "onClick='window.close()'></p>");
    Mlangwindow.document.write("</body></html>");
    Mlangwindow.document.close();
}
</script>

<style>
.td1 {font-family:굴림; font-size: 9pt; color:#FFFFFF; font-weight:bold; line-height: normal;}
.td2 {font-family:굴림; font-size: 9pt; color:#008080; font-weight:none; line-height:130%;}
</style>

</head>

<BR>
<table border=0 align=center width=90% cellpadding='0' cellspacing='1' bgcolor='<?php echo  $Bgcolor_2 ?>'>
<tr><td valign=top>

<table border=0 align=center width=100% cellpadding='8' cellspacing='1' bgcolor='<?php echo  $Bgcolor_1 ?>'>

<form name='MlangFriendSiteInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MlangFriendSiteCheckField()' action='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>'>
<?php if ($ModifyCode) { ?>
<INPUT TYPE="hidden" name='mode' value='FormModifyOk'>
<INPUT TYPE="hidden" name='no' value='<?php echo  $ModifyCode ?>'>
<?php } else { ?>
<INPUT TYPE="hidden" name='mode' value='FormSubmitOk'>
<?php } ?>

<tr>
<td bgcolor='<?php echo  $Bgcolor_2 ?>' width=100 class='td1' align='<?php echo  $align_td1 ?>'>&nbsp;사이트 제목&nbsp;</td>
<td bgcolor='<?php echo  $Bgcolor_3 ?>'>
<input type='text' name='title' maxLength='50' size='50' <?php echo  $InputStyle ?> <?php if ($ModifyCode) echo "value='$GF_title'"; ?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo  $Bgcolor_2 ?>' width=100 class='td1' align='<?php echo  $align_td1 ?>'>&nbsp;사이트 URL&nbsp;</td>
<td bgcolor='<?php echo  $Bgcolor_3 ?>'>
<input type='text' name='url' maxLength='300' size='50' <?php echo  $InputStyle ?> <?php if ($ModifyCode) echo "value='$GF_url'"; ?>>
</td>
</tr>

<tr>
<td bgcolor='<?php echo  $Bgcolor_2 ?>' width=100 class='td1' align='<?php echo  $align_td1 ?>'>&nbsp;사이트 설명&nbsp;</td>
<td bgcolor='<?php echo  $Bgcolor_3 ?>'>
<TEXTAREA NAME="cont" ROWS="10" COLS="50" <?php echo  $InputStyle ?>><?php if ($ModifyCode) echo "$GF_cont"; ?></TEXTAREA>
</td>
</tr>

<tr>
<td bgcolor='<?php echo  $Bgcolor_2 ?>' width=100 class='td1' align='<?php echo  $align_td1 ?>'>&nbsp;사이트 배너&nbsp;</td>
<td bgcolor='<?php echo  $Bgcolor_3 ?>'>
<?php if ($ModifyCode && $GF_upfile) { ?>
<INPUT TYPE="checkbox" NAME="photofileModify">파일을 수정하시려면 체크를 해주세요<BR>
<img src='./upload/<?php echo  $GF_upfile ?>' width=100 height=40>
<?php } ?>
<INPUT type="file" Size=35 name="photofile" onChange="Mlamg_image(this.value)" <?php echo  $InputStyle ?>>
</td>
</tr>

<?php if ($AdCate) { ?>
<tr>
<td bgcolor='<?php echo  $Bgcolor_2 ?>' width=100 class='td1' align='<?php echo  $align_td1 ?>'>&nbsp;가테고리&nbsp;</td>
<td bgcolor='<?php echo  $Bgcolor_3 ?>'>
<?php
    echo "<select name='cate' $InputStyle><OPTION VALUE='0' selected>▒선택하세요▒</OPTION>";
    $CATEGORY_LIST_script = explode(":", $AdCate);
    $k = 0;
    while ($k < sizeof($CATEGORY_LIST_script)) {
        if ($GF_cate == $CATEGORY_LIST_script[$k]) {
            echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";
        } else {
            echo "<OPTION VALUE='$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";
        } 
        $k++;
    } 
    echo "</select>\n";
?>
</td>
</tr>
<?php } ?>

</table>

</td></tr></table>

<p align=center>
<?php if ($ModifyCode) { ?>
<input type='submit' value='수정 합니다.' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
<?php } else { ?>
<input type='submit' value='입력 합니다.' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF; border-style:solid; height:24px; border:2 solid #84D0E0;'>
<?php } ?>
</p>
</form>

<?php
    exit;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "FormSubmitOk") {
    if ($photofile) {
        $upload_dir = "./upload";
        include "upload.php";
    }

    include "db.php";
    $result = $db->query("SELECT max(no) FROM $table");
    if (!$result) {
        echo "
            <script>
                window.alert(\"DB 접속 에러입니다!\")
                history.go(-1)
            </script>";
        exit;
    }
    $row = $result->fetch_row();

    if ($row[0]) {
        $new_no = $row[0] + 1;
    } else {
        $new_no = 1;
    }   
############################################
    $date = date("Y-m-d H:i:s");
    $dbinsert = "INSERT INTO $table VALUES ('$new_no',
    '$title',
    '$cont',
    '$url',
    '$cate',  
    '$photofileNAME',
    '0',
    '$date'
    )";
    $result_insert = $db->query($dbinsert);

    echo ("
        <script language=javascript>
        alert('\\n정상적으로 정보가 저장 되었습니다.\\n\\n')
        opener.parent.location.reload();
        </script>
    <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?mode=AdminSiteSubmit'>
    ");
    exit;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "FormModifyOk") {
    include "db.php";
    $ModifyCode = $no;
    include "ViewFild.php";

    if ($GF_upfile) {
        if ($photofileModify && $photofile) {
            $upload_dir = "./upload";
            include "upload.php";
            unlink("./upload/$GF_upfile");
        } else {
            $photofileNAME = $GF_upfile;
        }
    } else {
        if ($photofile) {
            $upload_dir = "./upload";
            include "upload.php";
        }
    }

    $stmt = $db->prepare("UPDATE $table SET title = ?, cont = ?, url = ?, cate = ?, upfile = ? WHERE no = ?");
    $stmt->bind_param("sssssi", $title, $cont, $url, $cate, $photofileNAME, $no);
    if (!$stmt->execute()) {
        echo "
            <script language=javascript>
                window.alert(\"DB 접속 에러입니다!\")
                history.go(-1);
            </script>";
        exit;
    } else {
        echo "
            <script language=javascript>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
            window.self.close();
            </script>";
    }
    $db->close();
    exit;
}
?>

<?php
$M123 = "..";
include "../top.php"; 
?>

<?php include "db.php"; ?>

<head>
<script>
function Member_Admin_Del(no) {
    if (confirm(no + '번 의 상담 자료를 삭제 하시겠습니까..?\\n\\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
        var str = '<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?no=' + no + '&mode=delete';
        var popup = window.open("", "", "scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href = str;
        popup.focus();
    }
}

function MM_jumpMenu(targ, selObj, restore) {
    eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
    if (restore) selObj.selectedIndex = 0;
}
</script>
</head>

<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>
<a href='index.php' target='_blank'>http://자신의홈페이지주소/설치폴더/index.php</a> 가 <b>자료페이지를 부르는 주소</b>입니다.<BR>
index.php?cate=카테고리명 이 <b> 카테고리 자료페이지를 부르는 주소</b>입니다.
</td>
<td align=right>
<input type='button'  onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=AdminSiteSubmit', 'MlangFriendSiteSubmit','width=600,height=430,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='웹사이트등록하기'>
<input type='button'  onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=AdminModifyPass', 'MlangFriendSiteAdMinModifdy','width=600,height=300,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='프로그램환경설정'>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='8' cellspacing='0' class='coolBar'>
<tr>
<td align=left>
<?php
if ($AdCate) {
    echo "<select onChange=\"MM_jumpMenu('parent',this,0)\" style='background-color:#D8EBFC;'><OPTION selected>▒카테고리별로보기▒</OPTION>";
    $CATEGORY_LIST_script = explode(":", $AdCate);
    $k = 0;
    while ($k < sizeof($CATEGORY_LIST_script)) {
        if ($cate == "$CATEGORY_LIST_script[$k]") {
            echo "<OPTION VALUE='$PHP_SELF?cate=$CATEGORY_LIST_script[$k]' selected style='background-color:#000000; color:#FFFFFF;'>$CATEGORY_LIST_script[$k]</OPTION>";
        } else {
            echo "<OPTION VALUE='$PHP_SELF?cate=$CATEGORY_LIST_script[$k]'>$CATEGORY_LIST_script[$k]</OPTION>";
        } 
        $k++;
    } 
    if ($cate) {
        echo "<option value='$PHP_SELF'>→ 전체목록보기</option></select>\n";
    } else {
        echo "</select>\n";
    }
} 
?>
</td>
</tr>
</table>

<table border=0 align=center width=100% cellpadding='5' cellspacing='1' bgcolor='#6699CC'>
<tr bgcolor='#6699CC'>
<td align=center class='td11'>등록번호</font></td>
<td align=center class='td11'>클릭수</font></td>
<td align=center class='td11'>제목</font></td>
<td align=center class='td11'>배너</td>
<td align=center class='td11'>등록날짜</td>
<td align=center class='td11'>관리</td>
<tr>

<?php
if ($cate) {
    $Mlang_query = "SELECT * FROM $table WHERE cate='$cate'";
} else {
    $Mlang_query = "SELECT * FROM $table";
}

$query = $db->query($Mlang_query);
$recordsu = $query->num_rows;

$listcut = 15;  // 한 페이지당 보여줄 목록 게시물수. 
if (!$offset) $offset = 0; 

$result = $db->query("$Mlang_query ORDER BY no DESC LIMIT $offset, $listcut");
$rows = $result->num_rows;
if ($rows) {
    while ($row = $result->fetch_assoc()) {
?>

<tr bgcolor='#FFFFFF'>
<td align=center><?php echo  $row['no'] ?></td>
<td align=center><?php echo  $row['count'] ?></td>
<td align=center><a href='<?php echo  htmlspecialchars($row['url']) ?>' target='_blank'><?php echo  htmlspecialchars($row['title']) ?></a></td>
<td align=center>
<?php if ($row['upfile']) { ?>
    <a href='<?php echo  htmlspecialchars($row['url']) ?>' target='_blank'><img src='./upload/<?php echo  htmlspecialchars($row['upfile']) ?>' border=0 width=100 height=40></a>
<?php } else { ?>
    배너 없음
<?php } ?>
</td>
<td align=center><?php echo  htmlspecialchars($row['date']) ?></td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=AdminSiteSubmit&ModifyCode=<?php echo  $row['no'] ?>', 'MlangFFFiteModify','width=650,height=500,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 수정 '>
<input type='button' onClick="javascript:Member_Admin_Del('<?php echo  $row['no'] ?>');" value=' 삭제 '>
</td>
<tr>

<?php
        $i++;
    } 
} else {
    if ($search) {
        echo "<tr><td colspan=10 bgcolor='#FFFFFF'><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
    } else {
        echo "<tr><td colspan=10 bgcolor='#FFFFFF'><p align=center><BR><BR>등록 자료없음</p></td></tr>";
    }
}
?>

</table>

<p align='center'>
<?php
if ($rows) {
    $mlang_pagego = $cate ? "cate=$cate" : "";

    $pagecut = 7; 
    $one_bbs = $listcut * $pagecut; 
    $start_offset = intval($offset / $one_bbs) * $one_bbs; 
    $end_offset = intval($recordsu / $one_bbs) * $one_bbs; 
    $start_page = intval($start_offset / $listcut) + 1; 
    $end_page = ($recordsu % $listcut > 0) ? intval($recordsu / $listcut) + 1 : intval($recordsu / $listcut); 
    if ($start_offset != 0) { 
        $apoffset = $start_offset - $one_bbs; 
        echo "<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;"; 
    } 

    for ($i = $start_page; $i < $start_page + $pagecut; $i++) { 
        $newoffset = ($i - 1) * $listcut; 
        if ($offset != $newoffset) {
            echo "&nbsp;<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
        } else {
            echo "&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"; 
        } 
        if ($i == $end_page) break; 
    } 

    if ($start_offset != $end_offset) { 
        $nextoffset = $start_offset + $one_bbs; 
        echo "&nbsp;<a href='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
    } 
    echo "총목록갯수: $end_page 개"; 
}
$db->close();
?> 

</p>

<?php include "../down.php"; ?>
