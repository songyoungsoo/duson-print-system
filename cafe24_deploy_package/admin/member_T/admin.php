<?php
////////////////// 인증 로그인 ////////////////////
function authenticate()
{
    header("WWW-Authenticate: Basic realm=\"관리자 페이지!\"");
    header("HTTP/1.0 401 Unauthorized");
    echo "<html><head><script>
        function pop() { alert('인증 실패'); history.go(-1); }
        </script></head>
        <body onLoad='pop()'></body>
        </html>";
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    authenticate();
} else {
    include "../../db.php";

    $stmt = $db->prepare("SELECT * FROM member WHERE no = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $adminid = $row['id'];
    $adminpasswd = $row['pass'];

    if ($stmt->num_rows == 0 || strcmp($_SERVER['PHP_AUTH_USER'], $adminid) || strcmp($_SERVER['PHP_AUTH_PW'], $adminpasswd)) {
        authenticate();
    }
    $stmt->close();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form") {
    include "../title.php";
    $Bgcolor1 = "408080";

    if ($code == "modify") {
        include "../../db.php";
        $stmt = $db->prepare("SELECT * FROM member_T WHERE no = ?");
        $stmt->bind_param("i", $no);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $Viewname = $row['name'];
        $Viewyear = $row['year'];
        $Viewmap = $row['map'];
        $Viewjob = $row['job'];
        $Viewphoto = $row['photo'];
        $stmt->close();
    }
?>

<head>
<style>
.Left1 {font-size:10pt; color:#FFFFFF; font:bold;}
</style>
<script language=javascript>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

////////////////////////////////////////////////////////////////////////////////
function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i+1)) < 0) {
            return false;
        }
    }
    return true;
}

/////////////////////////////////////////////////////////////////////////////////

function MemberXCheckField() {
    var f = document.FrmUserXInfo;

    if (f.name.value == "") {
        alert("이름을 입력해주세요!!");
        f.name.focus();
        return false;
    }

    if (f.year.value == "0") {
        alert("년도를 선택해주세요!!");
        f.year.focus();
        return false;
    }

    if (f.map.value == "0") {
        alert("지역을 선택해주세요!!");
        f.map.focus();
        return false;
    }

    if (f.job.value == "0") {
        alert("직업을 선택해주세요!!");
        f.job.focus();
        return false;
    }

    <?php if ($code != "modify") { ?>
    if (f.photofile.value == "") {
        alert("사진을 입력해 주세요.");
        f.photofile.focus();
        return false;
    }
    if ((f.photofile.value.lastIndexOf(".jpg") == -1) && (f.photofile.value.lastIndexOf(".gif") == -1)) {
        alert("사진 파일은 JPG 또는 GIF 파일만 가능합니다.");
        f.photofile.focus();
        return false;
    }
    <?php } ?>
}
//////////////// 이미지 미리보기 //////////////////////////////////
function Mlamg_image(image) {
    Mlangwindow = window.open("", "Image_Mlang", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,copyhistory=0,width=600,height=400,top=0,left=0");
    Mlangwindow.document.open();
    Mlangwindow.document.write("<html><head><title>이미지 미리보기</title></head>");
    Mlangwindow.document.write("<body>");
    Mlangwindow.document.write("<p align=center><a href=\"#\" onClick=\"javascript:window.close();\"><img src=\"" + image + "\" border=\"0\"></a></p>");
    Mlangwindow.document.write("<p align=center><INPUT TYPE='button' VALUE='창 닫기' onClick='window.close()'></p>");
    Mlangwindow.document.write("</body></html>");
    Mlangwindow.document.close();
}
</script>
<script src="../js/coolbar.js" type="text/javascript"></script>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<b>&nbsp;&nbsp;새 회원 정보를 <?php if ($code == "modify") { ?>수정<?php } else { ?>입력<?php } ?>합니다.</b><BR>
<table border=0 align=center width=100% cellpadding=0 cellspacing=5>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code == "modify") { ?>modify_ok<?php } else { ?>form_ok<?php } ?>'>
<?php if ($code == "modify") { ?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>이름&nbsp;&nbsp;</td>
<td><INPUT TYPE="text" NAME="name" size=20 maxLength='20' value='<?php if ($code == "modify") { echo("$Viewname"); } ?>'></td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>년도&nbsp;&nbsp;</td>
<td>
<select name=year>
<option value=0>선택하세요 ::::::</option>
<?php
$i = 1900;
while ($i < 2100) {
    $i = $i + 1;
    echo "<option value='$i'>$i 년</option>";
}
?>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>지역&nbsp;&nbsp;</td>
<td>
<select name=map>
<option value=0>선택하세요 ::::::</option>
<option value=서울>서울</option>
<option value=인천>인천</option>
<option value=대전>대전</option>
<option value=부산>부산</option>
<option value=대구>대구</option>
<option value=광주>광주</option>
<option value=울산>울산</option>
<option value=경기>경기</option>
<option value=강원>강원</option>
<option value=충북>충북</option>
<option value=충남>충남</option>
<option value=전북>전북</option>
<option value=전남>전남</option>
<option value=경북>경북</option>
<option value=경남>경남</option>
<option value=제주>제주</option>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>직업&nbsp;&nbsp;</td>
<td>
<select name=job>
<option value=0>선택하세요 ::::::</option>
<option value="기획/경영">기획/경영</option>
<option value="사무/총무">사무/총무</option>
<option value="마케팅/홍보">마케팅/홍보</option>
<option value="영업/판매">영업/판매</option>
<option value="고객상담/서비스">고객상담/서비스</option>
<option value="생산/제조">생산/제조</option>
<option value="IT/인터넷">IT/인터넷</option>
<option value="연구개발/설계">연구개발/설계</option>
<option value="디자인">디자인</option>
<option value="교육/강사">교육/강사</option>
<option value="의료/보건">의료/보건</option>
<option value="전문직">전문직</option>
<option value="무역/유통">무역/유통</option>
<option value="건설/건축">건설/건축</option>
<option value="운전/운송">운전/운송</option>
<option value="기타">기타</option>
</select>
</td>
</tr>
<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>사진&nbsp;&nbsp;</td>
<td>
<?php if ($code == "modify") { ?>
<img src='../../IndexSoft/member_T/upload/<?php echo $Viewphoto?>' width=50><BR>
<INPUT TYPE="hidden" name='TTFileName' value='<?php echo $Viewphoto?>'>
<INPUT TYPE="checkbox" name='PhotoFileModify'> 사진을 변경하려면 체크하세요!!<BR>
<?php } ?>
<INPUT TYPE="file" NAME="photofile" size=30 onChange="Mlamg_image(this.value)"><BR>
사진의 크기는 105 X 130 으로 맞춰 주세요
</td>
</tr>
<tr>
<td>&nbsp;&nbsp;</td>
<td>
<input type='submit' value=' <?php if ($code == "modify") { ?>수정<?php } else { ?>등록<?php } ?> 합니다.'>
</td>
</tr>
</table>

<?php if ($code == "modify") { ?>
<script language="JavaScript">
var f = document.FrmUserXInfo;
f.year.value = "<?php echo $Viewyear?>"; 
f.map.value = "<?php echo $Viewmap?>"; 
f.job.value = "<?php echo $Viewjob?>";
</script>
<?php } ?>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "form_ok") {
    $stmt = $db->prepare("SELECT MAX(no) FROM member_T");
    $stmt->execute();
    $stmt->bind_result($max_no);
    $stmt->fetch();
    $stmt->close();

    $new_no = $max_no ? $max_no + 1 : 1;

    $upload_dir = "../../IndexSoft/member_T/upload";
    include "upload.php";

    $stmt = $db->prepare("INSERT INTO member_T (no, name, year, map, job, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $new_no, $name, $year, $map, $job, $PhotofileName);
    $stmt->execute();

    echo ("
    <script language=javascript>
    alert('\\n데이터가 성공적으로 등록되었습니다.\\n\\n데이터를 추가 등록하시려면 창을 다시 여세요.\\n');
    opener.parent.location='index.php'; 
    window.self.close();
    </script>
    ");
    exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "delete") {
    $stmt = $db->prepare("DELETE FROM member_T WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();

    echo ("
    <html>
    <script language=javascript>
    window.alert('관리자님, 회원번호 $no 의 데이터를 삭제 처리 하였습니다.');
    opener.parent.location.reload();
    window.self.close();
    </script>
    </html>
    ");
    exit;
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "modify_ok") {
    if ($PhotoFileModify) {
        $upload_dir = "../../IndexSoft/member_T/upload";
        include "upload.php";
        $YYPjFile = $PhotofileName;
        if ($TTBigFileName) {
            unlink("$upload_dir/$TTFileName");
        }
    } else {
        $YYPjFile = $TTFileName;
    }

    $stmt = $db->prepare("UPDATE member_T SET name = ?, year = ?, map = ?, job = ?, photo = ? WHERE no = ?");
    $stmt->bind_param("sssssi", $name, $year, $map, $job, $YYPjFile, $no);
    $stmt->execute();

    if (!$stmt->affected_rows) {
        echo "
        <script language=javascript>
        window.alert(\"DB 업데이트 실패!\")
        history.go(-1);
        </script>";
        exit;
    } else {
        echo ("
        <script language=javascript>
        alert('\\n데이터가 성공적으로 업데이트 되었습니다.\\n');
        opener.parent.location.reload();
        window.self.close();
        </script>
        ");
        exit;
    }
    $stmt->close();
    $db->close();
}
?>