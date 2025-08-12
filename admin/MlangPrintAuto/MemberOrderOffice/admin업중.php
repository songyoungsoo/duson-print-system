<?php
////////////////// 관리자 로그인 ////////////////////
include "../../../db.php";
include "../../config.php";
////////////////////////////////////////////////////

if ($mode == "MlangFileOk") { ///////////////////////////////////////////////////////////////////////////////////////////////////////////

    $MAXFSIZE = "99999";
    $upload_dir = "./upload";
    if (isset($check)) {
        if (!empty($_FILES['photofile_4']) && $_FILES['photofile_4']['error'] == 0) {
            include "upload_4.php";
            $fileOk = $_FILES['photofile_4']['name'];
        } else {
            if ($file) {
                unlink("$upload_dir/$file");
            }
        }
    } else {
        $fileOk = $file;
    }

    $query = "UPDATE MlangPrintAuto_MemberOrderOffice SET $code='$fileOk' WHERE no='$no'";
    $result = mysqli_query($db, $query);
    if (!$result) {
        echo "
            <script>
                window.alert('DB 접속 에러입니다!')
                history.go(-1);
            </script>";
        exit;
    } else {
        echo ("
            <script>
            alert('\\n정보를 정상적으로 수정하였습니다.\\n');
            opener.parent.location.reload();
            window.self.close();
            </script>
        ");
        exit;
    }
    mysqli_close($db);
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($mode == "MlangFile") { ///////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<html>
<title>자료첨부 수정</title>

<head>
<style>
    td, table {
        BORDER-COLOR: #000000;
        border-collapse: collapse;
        color: #000000;
        font-size: 10pt;
        FONT-FAMILY: 돋움;
        word-break: break-all;
    }

    input, TEXTAREA {
        color: #000000;
        font-size: 9pt;
        border: 1px solid #444444;
        vertical-align: middle;
    }

    TEXTAREA {
        overflow: hidden
    }
</style>

<script>
    self.moveTo(0, 0);
    self.resizeTo(400, 250);
</script>

</head>

<body>

<table border=1 align=center cellpadding=5 cellspacing=0 width=340>
    <form method='post' enctype='multipart/form-data' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>
        <input type='hidden' name='file' value='<?php echo  htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>'>
        <input type='hidden' name='no' value='<?php echo  htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>'>
        <input type='hidden' name='code' value='<?php echo  htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>'>
        <input type='hidden' name='mode' value='MlangFileOk'>
        <tr>
            <td colspan=2>* 파일 수정페이지</td>
        </tr>
        <tr>
            <td align=center>현재파일명</td>
            <td><?php echo  htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td align=center>변경</td>
            <td><INPUT TYPE="checkbox" NAME="check"> 파일을 수정하려면 체크해주세요<BR>
                <font style='font-family:돋움; font-size: 8pt; color:#336699;'>* 체크 후 업로드를 안 하면 기존 자료만 삭제됨.</font>
            </td>
        </tr>
        <tr>
            <td align=center>업로드</td>
            <td><input type='file' name='photofile_4' size='23'></td>
        </tr>
    </form>
</table>

<p align=center>
    <input type='submit' value=' 저장합니다.. '>
    <input type='button' onclick="javascript:window.self.close();" value='창닫기'>
</p>

</body>
</html>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if ($mode == "form") { ///////////////////////////////////////////////////////////////////////////////////////////////////////////
$Bgcolor1 = "408080";

if ($code == "modify" || $code == "Print" || $code == "fff") {
    include "View.php";

    function str_cutting($str, $len)
    {
        preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn);
        if ($len < strlen($str)) $rtn[0] .= "..";
        return $rtn[0];
    }
}
?>

<html>
<title>"웹실디자인"</title>

<head>

<style>
    td, table {
        BORDER-COLOR: #707070;
        border-collapse: collapse;
        color: #000000;
        font-size: 9pt;
        FONT-FAMILY: 굴림;
        line-height: 130%;
        word-break: break-all;
    }

    input, TEXTAREA {
        color: #000000;
        font-size: 9pt;
        border: 1px solid #444444;
        vertical-align: middle;
    }

    TEXTAREA {
        overflow: hidden
    }
</style>

<SCRIPT src='/admin/js/exchange.js'></SCRIPT>

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

function MemberXCheckField() {
    var f = document.FrmUserXInfo;

    if (f.One_1.value == "") {
        alert("작성자를 입력하여 주세요!!");
        f.One_1.focus();
        return false;
    }

    if (f.One_3.value == "") {
        alert("업체명을 입력하여 주세요!!");
        f.One_3.focus();
        return false;
    }

    if (f.One_4.value == "") {
        alert("담당자를 입력하여 주세요!!");
        f.One_4.focus();
        return false;
    }

    if (f.One_6.value == "") {
        alert("연락처를 입력하여 주세요!!");
        f.One_6.focus();
        return false;
    }

    if (f.One_7.value == "") {
        alert("핸드폰을 입력하여 주세요!!");
        f.One_7.focus();
        return false;
    }

    if (f.One_9.value == "") {
        alert("택배지를 입력하여 주세요!!");
        f.One_9.focus();
        return false;
    }
}

function Mlamg_image(image) {
    alert("입력하신 파일은 [" + image + "] 입니다.");
}

function MlangMoneyTotal() {
    var f = document.FrmUserXInfo;

	var Tree_3 = parseFloat(f.Tree_3.value) || 0;
var Tree_10 = parseFloat(f.Tree_10.value) || 0;
var Tree_6 = parseFloat(f.Tree_6.value) || 0;
var Tree_13 = parseFloat(f.Tree_13.value) || 0;

f.Tree_7.value = Tree_3 + Tree_10 + Tree_6 + Tree_13;
}
</script>

<script>
self.moveTo(0, 0);
self.resizeTo(660, 680);

function MlangWindowOne(code) {
    var f = document.FrmUserXInfo;
    var money = f.Tree_7.value;
    if (!code) {
        alert("인쇄모드에서만 사용이 가능합니다.");
    } else {
        if (confirm("거래명세표에 부가세를 포함시키시면 [확인]을\n\n부가세를 포함하지 않으시면 [취소]를\n\n선택해주세요")) {
            window.open("int.php?EEE=1&mode=One&code=" + code + "&money=" + money, "MlangWindowOne", "scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
        } else {
            window.open("int.php?EEE=2&mode=One&code=" + code + "&money=" + money, "MlangWindowOne", "scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
        }
    }
}

function MlangWindowTwo(code) {
    if (!code) {
        alert("인쇄모드에서만 사용이 가능합니다.");
    } else {
        var popup = window.open("int.php?mode=Two&code=" + code, "MlangWindowTwo", "scrollbars=no,resizable=yes,width=400,height=50,top=0,left=0");
        popup.focus();
    }
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=0>

<?php if ($mode == "form") { ?>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' onsubmit='return MemberXCheckField()' action='<?php echo  htmlspecialchars($_SERVER['PHP_SELF']) ?>'>
    <input type="hidden" name='mode' value='<?php echo  ($code == "modify") ? "modify_ok" : "form_ok" ?>'>
    <?php if ($code == "modify") { ?>
        <input type="hidden" name='no' value='<?php echo  htmlspecialchars($no, ENT_QUOTES, 'UTF-8') ?>'>
    <?php } ?>
<?php } ?>

<!---------- One 시작 -------------------->
<tr>
<td>
    <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td align=left>
                <font style='font-size:10pt; font:bold;'>작성자:</font>&nbsp;<input type="text" name="One_1" size=30 value="<?php echo  htmlspecialchars($View_One_1 ?? '', ENT_QUOTES, 'UTF-8') ?>" style='font-size:10pt; font:bold; height:22;'>
            </td>
            <td align=right>
                업체구분:
                <input type="checkbox" name="One_2" value='1' <?php echo  ($View_One_2 == "1") ? "checked" : "" ?>>신규업체
                <input type="checkbox" name="One_2" value='2' <?php echo  ($View_One_2 == "2") ? "checked" : "" ?>>거래업체
                <input type="checkbox" name="One_2" value='3' <?php echo  ($View_One_2 == "3") ? "checked" : "" ?>>하청
            </td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td class='coolBar'>
    <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td align=center width=50><font color=red>업체명</font></td>
            <td><input type="text" name="One_3" size=24 value="<?php echo  htmlspecialchars($View_One_3 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
            <td align=center width=50><font color=red>담당자</font></td>
            <td><input type="text" name="One_4" size=24 value="<?php echo  htmlspecialchars($View_One_4 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
            <td align=center width=50>E-mail</td>
            <td><input type="text" name="One_5" size=24 value="<?php echo  htmlspecialchars($View_One_5 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
        </tr>
        <tr>
            <td align=center><font color=red>연락처</font></td>
            <td><input type="text" name="One_6" size=24 value="<?php echo  htmlspecialchars($View_One_6 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
            <td align=center><font color=red>핸드폰</font></td>
            <td><input type="text" name="One_7" size=24 value="<?php echo  htmlspecialchars($View_One_7 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
            <td align=center>FAX</td>
            <td><input type="text" name="One_8" size=24 value="<?php echo  htmlspecialchars($View_One_8 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
        </tr>
        <tr>
            <td align=center><font color=red>택배지</font></td>
            <td colspan=6><input type="text" name="One_9" size=68 value="<?php echo  htmlspecialchars($View_One_9 ?? '', ENT_QUOTES, 'UTF-8') ?>"></td>
        </tr>
    </table>
</td>
</tr>
<!---------- One 끄읕 -------------------->

<!---------- Two 시작 -------------------->
<tr>
<td><b>■주문의뢰상황</b></td>
</tr>

<tr>
<td class='coolBar'>
    <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td align=center width=80>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                    <tr>
                        <td align=center <?php echo  ($View_Two_1 || $View_Two_2) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_1 || $View_Two_2) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>상품권</font></td>
                    </tr>
                </table>
            </td>
            <td nowrap>
                <input type="checkbox" name="Two_1" value='1' <?php echo  ($View_Two_1 == "1") ? "checked" : "" ?>>머니빌지
                <input type="text" name="Two_2" size=8 value="<?php echo  htmlspecialchars($View_Two_2 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;&nbsp;
                디자인:<input type="checkbox" name="Two_3" value='1' <?php echo  ($View_Two_3 == "1") ? "checked" : "" ?>>유
                <input type="checkbox" name="Two_3" value='2' <?php echo  ($View_Two_3 == "2") ? "checked" : "" ?>>무
                수량:<input type="text" name="Two_4" size=8 value="<?php echo  htmlspecialchars($View_Two_4 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                건수:<input type="text" name="Two_5" size=8 value="<?php echo  htmlspecialchars($View_Two_5 ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </td>
            <td width=90 align=center><input type="text" name="Two_47" size=10 value="<?php echo  htmlspecialchars($View_Two_47 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
        </tr>
        <tr>
            <td colspan=2 nowrap>
                &nbsp;&nbsp;&nbsp;
                인쇄:<input type="checkbox" name="Two_6" value='1' <?php echo  ($View_Two_6 == "1") ? "checked" : "" ?>>양
                <input type="checkbox" name="Two_6" value='2' <?php echo  ($View_Two_6 == "2") ? "checked" : "" ?>>단
                &nbsp;
                후가공:<input type="checkbox" name="Two_7_1" value='1' <?php echo  ($View_Two_7_1 == "1") ? "checked" : "" ?>>넘버링
                <input type="checkbox" name="Two_7_2" value='1' <?php echo  ($View_Two_7_2 == "1") ? "checked" : "" ?>>난수
                <input type="checkbox" name="Two_7_3" value='1' <?php echo  ($View_Two_7_3 == "1") ? "checked" : "" ?>>스크래치
            </td>
            <td align=center><input type="text" name="Two_48" size=10 value="<?php echo  htmlspecialchars($View_Two_48 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
        </tr>
        <tr>
            <td align=center width=80>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                    <tr>
                        <td align=center <?php echo  ($View_Two_9 || $View_Two_10) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_9 || $View_Two_10) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>포스터</font></td>
                    </tr>
                </table>
            </td>
            <td nowrap>
                <input type="checkbox" name="Two_9" value='1' <?php echo  ($View_Two_9 == "1") ? "checked" : "" ?>>A2
                <input type="checkbox" name="Two_9" value='2' <?php echo  ($View_Two_9 == "2") ? "checked" : "" ?>>4절
                <input type="checkbox" name="Two_9" value='3' <?php echo  ($View_Two_9 == "3") ? "checked" : "" ?>>2절
                <input type="text" name="Two_10" size=6 value="<?php echo  htmlspecialchars($View_Two_10 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;
                수량:<input type="text" name="Two_11" size=5 value="<?php echo  htmlspecialchars($View_Two_11 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;
                디자인:<input type="checkbox" name="Two_12" value='1' <?php echo  ($View_Two_12 == "1") ? "checked" : "" ?>>유
                <input type="checkbox" name="Two_12" value='2' <?php echo  ($View_Two_12 == "2") ? "checked" : "" ?>>무
                &nbsp;
                기타:<input type="text" name="Two_13" size=6 value="<?php echo  htmlspecialchars($View_Two_13 ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </td>
            <td align=center><input type="text" name="Two_49" size=10 value="<?php echo  htmlspecialchars($View_Two_49 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
        </tr>
        <tr>
            <td align=center width=80>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                    <tr>
                        <td align=center <?php echo  ($View_Two_14 || $View_Two_15) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_14 || $View_Two_15) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>전단,리플렛</font></td>
                    </tr>
                </table>
            </td>
            <td nowrap>
                <input type="checkbox" name="Two_14" value='1' <?php echo  ($View_Two_14 == "1") ? "checked" : "" ?>>A4
                <input type="checkbox" name="Two_14" value='2' <?php echo  ($View_Two_14 == "2") ? "checked" : "" ?>>16절
                <input type="checkbox" name="Two_14" value='3' <?php echo  ($View_Two_14 == "3") ? "checked" : "" ?>>A3
                <input type="text" name="Two_15" size=6 value="<?php echo  htmlspecialchars($View_Two_15 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;
                수량:<input type="text" name="Two_16" size=5 value="<?php echo  htmlspecialchars($View_Two_16 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;
                디자인:<input type="checkbox" name="Two_17" value='1' <?php echo  ($View_Two_17 == "1") ? "checked" : "" ?>>유
                <input type="checkbox" name="Two_17" value='2' <?php echo  ($View_Two_17 == "2") ? "checked" : "" ?>>무
            </td>
            <td width=90 align=center><input type="text" name="Two_50" size=10 value="<?php echo  htmlspecialchars($View_Two_50 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
        </tr>
        <tr>
            <td colspan=2 nowrap>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                구분:<input type="checkbox" name="Two_18" value='1' <?php echo  ($View_Two_18 == "1") ? "checked" : "" ?>>합판
                <input type="checkbox" name="Two_18" value='2' <?php echo  ($View_Two_18 == "2") ? "checked" : "" ?>>독판
                &nbsp;
                후가공:
                <input type="text" name="Two_19" size=6 value="<?php echo  htmlspecialchars($View_Two_19 ?? '', ENT_QUOTES, 'UTF-8') ?>">
                &nbsp;
                <input type="text" name="Two_20" size=38 value="<?php echo  htmlspecialchars($View_Two_20 ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </td>
            <td align=center>&nbsp;</td>
        </tr>
        <tr>
            <td align=center width=80>
                <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                    <tr>
                        <td align=center <?php echo  ($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>명함,쿠폰</font></td>
                    </tr>
                </table>
            </td>
			<td nowrap>
    <input type="checkbox" name="Two_21_1" value='1' <?php echo  ($View_Two_21_1 == "1") ? "checked" : "" ?>>코팅
    <input type="checkbox" name="Two_21_2" value='1' <?php echo  ($View_Two_21_2 == "1") ? "checked" : "" ?>>무코
    <input type="checkbox" name="Two_21_3" value='1' <?php echo  ($View_Two_21_3 == "1") ? "checked" : "" ?>>반누
    <input type="checkbox" name="Two_21_4" value='1' <?php echo  ($View_Two_21_4 == "1") ? "checked" : "" ?>>휘나
    &nbsp;
    수량:<input type="text" name="Two_22" size=5 value="<?php echo  htmlspecialchars($View_Two_22 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    건수:<input type="text" name="Two_23" size=5 value="<?php echo  htmlspecialchars($View_Two_23 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    &nbsp;
    인쇄:<input type="checkbox" name="Two_24" value='1' <?php echo  ($View_Two_24 == "1") ? "checked" : "" ?>>양
    <input type="checkbox" name="Two_24" value='2' <?php echo  ($View_Two_24 == "2") ? "checked" : "" ?>>단
</td>
<td width=90 align=center><input type="text" name="Two_51" size=10 value="<?php echo  htmlspecialchars($View_Two_51 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
</tr>
<tr>
    <td colspan=2 nowrap>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        구분:<input type="checkbox" name="Two_25" value='1' <?php echo  ($View_Two_25 == "1") ? "checked" : "" ?>>합판
        <input type="checkbox" name="Two_25" value='2' <?php echo  ($View_Two_25 == "2") ? "checked" : "" ?>>독판
        &nbsp;
        후가공:
        <input type="text" name="Two_26" size=6 value="<?php echo  htmlspecialchars($View_Two_26 ?? '', ENT_QUOTES, 'UTF-8') ?>">
        &nbsp;
        <input type="text" name="Two_27" size=38 value="<?php echo  htmlspecialchars($View_Two_27 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </td>
    <td align=center>&nbsp;</td>
</tr>
<tr>
    <td align=center width=80>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
            <tr>
                <td align=center <?php echo  ($View_Two_28 || $View_Two_29) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_28 || $View_Two_29) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>복권,넘버링</font></td>
            </tr>
        </table>
    </td>
    <td nowrap>
        <input type="checkbox" name="Two_28" value='1' <?php echo  ($View_Two_28 == "1") ? "checked" : "" ?>>A4
        <input type="text" name="Two_29" size=6 value="<?php echo  htmlspecialchars($View_Two_29 ?? '', ENT_QUOTES, 'UTF-8') ?>">
        &nbsp;
        수량:<input type="text" name="Two_30" size=5 value="<?php echo  htmlspecialchars($View_Two_30 ?? '', ENT_QUOTES, 'UTF-8') ?>">
        &nbsp;
        인쇄:<input type="checkbox" name="Two_31" value='1' <?php echo  ($View_Two_31 == "1") ? "checked" : "" ?>>양
        <input type="checkbox" name="Two_31" value='2' <?php echo  ($View_Two_31 == "2") ? "checked" : "" ?>>단
    </td>
    <td width=90 align=center><input type="text" name="Two_52" size=10 value="<?php echo  htmlspecialchars($View_Two_52 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
</tr>
<tr>
    <td colspan=2 nowrap>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        구분:<input type="checkbox" name="Two_32" value='1' <?php echo  ($View_Two_32 == "1") ? "checked" : "" ?>>합판
        <input type="checkbox" name="Two_32" value='2' <?php echo  ($View_Two_32 == "2") ? "checked" : "" ?>>독판
        &nbsp;
        후가공:<input type="checkbox" name="Two_33_1" value='1' <?php echo  ($View_Two_33_1 == "1") ? "checked" : "" ?>>넘버링
        <input type="checkbox" name="Two_33_2" value='1' <?php echo  ($View_Two_33_2 == "1") ? "checked" : "" ?>>난수
        <input type="checkbox" name="Two_33_3" value='1' <?php echo  ($View_Two_33_3 == "1") ? "checked" : "" ?>>스크래치
        &nbsp;
        <input type="text" name="Two_34" size=17 value="<?php echo  htmlspecialchars($View_Two_34 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </td>
    <td align=center>&nbsp;</td>
</tr>
<tr>
    <td align=center width=80>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
            <tr>
                <td align=center <?php echo  ($View_Two_35 || $View_Two_36) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_35 || $View_Two_36) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>카다로그</font></td>
            </tr>
        </table>
    </td>
    <td nowrap>
        <input type="checkbox" name="Two_35" value='1' <?php echo  ($View_Two_35 == "1") ? "checked" : "" ?>>부수
        <input type="text" name="Two_36" size=8 value="<?php echo  htmlspecialchars($View_Two_36 ?? '', ENT_QUOTES, 'UTF-8') ?>">&nbsp;&nbsp;
        페이지:<input type="text" name="Two_37" size=8 value="<?php echo  htmlspecialchars($View_Two_37 ?? '', ENT_QUOTES, 'UTF-8') ?>">&nbsp;
        지질:<input type="text" name="Two_38" size=8 value="<?php echo  htmlspecialchars($View_Two_38 ?? '', ENT_QUOTES, 'UTF-8') ?>">&nbsp;
        두께:<input type="text" name="Two_39" size=8 value="<?php echo  htmlspecialchars($View_Two_39 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </td>
    <td width=90 align=center><input type="text" name="Two_53" size=10 value="<?php echo  htmlspecialchars($View_Two_53 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
</tr>
<tr>
    <td colspan=2 nowrap>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        제본:<input type="checkbox" name="Two_40" value='1' <?php echo  ($View_Two_40 == "1") ? "checked" : "" ?>>중철
        <input type="text" name="Two_41" size=58 value="<?php echo  htmlspecialchars($View_Two_41 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </td>
    <td align=center>&nbsp;</td>
</tr>
<tr>
    <td align=center width=80>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
            <tr>
                <td align=center <?php echo  ($View_Two_42 || $View_Two_43) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_42 || $View_Two_43) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>스티커</font></td>
            </tr>
        </table>
    </td>
    <td nowrap>
        &nbsp;수량:<input type="text" name="Two_42" size=8 value="<?php echo  htmlspecialchars($View_Two_42 ?? '', ENT_QUOTES, 'UTF-8') ?>">
        크기:<input type="text" name="Two_43" size=8 value="<?php echo  htmlspecialchars($View_Two_43 ?? '', ENT_QUOTES, 'UTF-8') ?>">
        &nbsp;
        <input type="checkbox" name="Two_44" value='1' <?php echo  ($View_Two_44 == "1") ? "checked" : "" ?>>코팅
        <input type="checkbox" name="Two_44" value='2' <?php echo  ($View_Two_44 == "2") ? "checked" : "" ?>>무코
    </td>
    <td width=90 align=center><input type="text" name="Two_54" size=10 value="<?php echo  htmlspecialchars($View_Two_54 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
</tr>
<tr>
    <td align=center width=80>
        <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
            <tr>
                <td align=center <?php echo  ($View_Two_45 || $View_Two_46) ? "bgcolor='#000000'" : "" ?> height=22><?php echo  ($View_Two_45 || $View_Two_46) ? "<font style='color:#FFFFFF; font:bold;'>" : "<b>" ?>기타</font></td>
            </tr>
        </table>
    </td>
    <td nowrap>
        &nbsp;<input type="text" name="Two_45" size=12 value="<?php echo  htmlspecialchars($View_Two_45 ?? '', ENT_QUOTES, 'UTF-8') ?>">&nbsp;
        <input type="text" name="Two_46" size=55 value="<?php echo  htmlspecialchars($View_Two_46 ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </td>
    <td width=90 align=center><input type="text" name="Two_55" size=10 value="<?php echo  htmlspecialchars($View_Two_55 ?? '', ENT_QUOTES, 'UTF-8') ?>">원</td>
</tr>
</table>
</td>
</tr>
<!---------- Two 끄읕 -------------------->

<!---------- Four 시작 --------------------여기부터 업데이트> 
<tr>
<td><b>■결제,배송상황</b></td>
</tr>

<tr>
<td class='coolBar'>
     <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><font color=red>&nbsp;&nbsp;은&nbsp;행&nbsp;명&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_1" size=19 <?php if ($View_Four_1){echo("value='$View_Four_1'");}?>></td>
		 <td align=center><font color=red>&nbsp;&nbsp;입&nbsp;금&nbsp;자&nbsp&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_2" size=26 <?php if ($View_Four_2){echo("value='$View_Four_2'");}?>></td>
		 <td align=center>&nbsp;&nbsp;세금계산서&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='1' <?php if ($View_Four_3=="1"){echo("checked");}?>>발행
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='2' <?php if ($View_Four_3=="2"){echo("checked");}?>>미발행
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;입금총액&nbsp;<BR>&nbsp;<font style='font-family:돋움; font-size:8pt;'>(부가세포함)</font>&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_4" size=19 <?php if ($View_Four_4){echo("value='$View_Four_4'");}?>></td>
		 <td align=center>&nbsp;비&nbsp;&nbsp;고&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_5" size=26 <?php if ($View_Four_5){echo("value='$View_Four_5'");}?>></td>
		 <td align=center>&nbsp;&nbsp;배송요금&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='1' <?php if ($View_Four_6=="1"){echo("checked");}?>>선불
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='2' <?php if ($View_Four_6=="2"){echo("checked");}?>>착불
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;부&nbsp;가&nbsp;세</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_7" size=19 <?php if ($View_Four_7){echo("value='$View_Four_7'");}?>></td>
		 <td align=center>배&nbsp;&nbsp;송</td>
		 <td align=center><font style='font-family:돋움; font-size:8pt;'>
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='1' <?php if ($View_Four_8=="1"){echo("checked");}?>>택배
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='2' <?php if ($View_Four_8=="2"){echo("checked");}?>>퀵
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='3' <?php if ($View_Four_8=="3"){echo("checked");}?>>화물
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='4' <?php if ($View_Four_8=="4"){echo("checked");}?>>방문
		 </font></td>
		 <td align=center>&nbsp;&nbsp;완불확인&nbsp;&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_9" size=15 <?php if ($View_Four_9){echo("value='$View_Four_9'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!----------  -------------------->

<!----------  -------------------->
<tr>
<td><b>■작업진행상황</b></td>
</tr>

<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
	  <tr>
         <td align=center>&nbsp;진행방법&nbsp</td>
		 <td colspan=2>
         <INPUT TYPE="checkbox" NAME="Five_1"  value='1' <?php if ($View_Five_1=="1"){echo("checked");}?>>합판
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='2' <?php if ($View_Five_1=="2"){echo("checked");}?>>독판
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='3' <?php if ($View_Five_1=="3"){echo("checked");}?>>기타
		 <INPUT TYPE="text" NAME="Five_2" size=16 <?php if ($View_Five_2){echo("value='$View_Five_2'");}?>>
		 </td>
		 <td align=center>&nbsp;진행담당자&nbsp</td>
		 <td colspan=2>&nbsp;<INPUT TYPE="text" NAME="Five_3" size=16 <?php if ($View_Five_3){echo("value='$View_Five_3'");}?>></td>
       </tr>
       <tr>
         <td align=center>&nbsp;배&nbsp송&nbsp일&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_4" size=16 <?php if ($View_Five_4){echo("value='$View_Five_4'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;도&nbsp착&nbsp일&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_5" size=16 <?php if ($View_Five_5){echo("value='$View_Five_5'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;유&nbsp;&nbsp;형&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_6" size=16 <?php if ($View_Five_6){echo("value='$View_Five_6'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!---------- -------------------->

<!----------  -------------------->

<tr>
<td class='coolBar'>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center colspan=2 height=22><b>합판인쇄 의뢰처</b></td>
              </tr>
			  <tr>
                <td colspan=2>
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='1' <?php if ($View_Five_7=="1"){echo("checked");}?>>OO
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='2' <?php if ($View_Five_7=="2"){echo("checked");}?>>OO
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='3' <?php if ($View_Five_7=="3"){echo("checked");}?>>기타
		 <INPUT TYPE="text" NAME="Five_8" size=8 <?php if ($View_Five_8){echo("value='$View_Five_8'");}?>>
				</td>
              </tr>
			  <tr>
                <td align=center>&nbsp;&nbsp;금액&nbsp;&nbsp;</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_9" size=24 <?php if ($View_Five_9){echo("value='$View_Five_9'");}?>></td>
              </tr>
			  <tr>
                <td align=center>접수<BR>파일</td>
				<td>&nbsp;<TEXTAREA NAME="Five_10" ROWS="3" COLS="25"><?php if ($View_Five_10){echo("$View_Five_10");}?></TEXTAREA></td>
              </tr>
			  <tr>
                <td align=center>번호</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_11" size=24 <?php if ($View_Five_11){echo("value='$View_Five_11'");}?>></td>
              </tr>
			  <tr>
                <td align=center>비고</td>
				<td>&nbsp;<TEXTAREA NAME="Five_12" ROWS="3" COLS="25"><?php if ($View_Five_12){echo("$View_Five_12");}?></TEXTAREA></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center colspan=2 height=22><b>독판인쇄 의뢰처</b></td>
              </tr>
			   <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;지업사&nbsp;&nbsp;</td>
				<td>
                <INPUT TYPE="checkbox" NAME="Five_13"  value='1' <?php if ($View_Five_13=="1"){echo("checked");}?>>OO
				<INPUT TYPE="text" NAME="Five_14" size=15 <?php if ($View_Five_14){echo("value='$View_Five_14'");}?>>
				</td>
              </tr>
			  <tr>
				<td>&nbsp;용지대:&nbsp;<INPUT TYPE="text" NAME="Five_15" size=15 <?php if ($View_Five_15){echo("value='$View_Five_15'");}?>></td>
              </tr>
			  <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;인쇄처&nbsp;&nbsp;</td>
				<td height=25>
                <INPUT TYPE="checkbox" NAME="Five_16"  value='1' <?php if ($View_Five_16=="1"){echo("checked");}?>>OO
				<INPUT TYPE="text" NAME="Five_17" size=15 <?php if ($View_Five_17){echo("value='$View_Five_17'");}?>>
				</td>
              </tr>
			  <tr>
				<td height=25>&nbsp;인쇄대:&nbsp;<INPUT TYPE="text" NAME="Five_18" size=15 <?php if ($View_Five_18){echo("value='$View_Five_18'");}?>></td>
              </tr>
			  <tr>
                <td align=center>후가공</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_19" size=22 <?php if ($View_Five_19){echo("value='$View_Five_19'");}?>></td>
              </tr>
			  <tr>
                <td align=center>비고</td>
				<td>&nbsp;<TEXTAREA NAME="Five_20" ROWS="3" COLS="23"><?php if ($View_Five_20){echo("$View_Five_20");}?></TEXTAREA></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
           <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=188>
              <tr>
                <td align=center height=22>작업자 지원</td>
				<td align=center>자료 첨부</td>
              </tr>
			  <tr>
                <td align=center height=22><TEXTAREA NAME="Five_21" ROWS="6" COLS="14"><?php if ($View_Five_21){echo("$View_Five_21");}?></TEXTAREA></td>
				<td>

<script>
function ImgMlangGo(fileurl,code){
	var str;
		if (confirm("[확인]누르시면 파일을 다운로드가능한 창이 뜨고\n\n[최소]을 누르시면 파일을 수정하실수 있습니다.")) {
		window.open("./upload/"+fileurl,"fileurlged","scrollbars=no,resizable=yes,width=600,height=500,top=0,left=0");
	   }else{
        popup = window.open("<?php echo $PHP_SELF?>?mode=MlangFile&file="+fileurl+"&code="+code+"&no=<?php echo $no?>","Mlangdhdimodu","scrollbars=no,resizable=no,width=400,height=150,top=0,left=0");
        popup.focus();
	   }
}
</script>

<?php if ($View_Five_22){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_22?>','Five_22');"><?php echo str_cutting("$View_Five_22",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_1" size=1 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_23){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_23?>','Five_23');"><?php echo str_cutting("$View_Five_23",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_2" size=1 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_24){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_24?>','Five_24');"><?php echo str_cutting("$View_Five_24",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_3" size=1 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_25){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_25?>','Five_25');"><?php echo str_cutting("$View_Five_25",16)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_4" size=1 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>

				</td>
              </tr>
			  <tr>
                <td colspan=2>
				<p align=left style='text-indent:0; margin-right:5pt; margin-left:5pt; margin-top:6pt; margin-bottom:6pt;'>
				<font style='font-family:돋움; font-size:8pt;'>
				작업자가필요한자료등을첨부하여사용<BR>
				(그림, 난수자료, 일러파일 등)<BR>
				작업중에도 자료을 올릴수 있습니다.)
				</font></p>
				</td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
	  </tr>
    </table>

</td>
</tr>
<!---------- Five 끄읕 -------------------->


<tr>
<td align=right>
         <INPUT TYPE="checkbox" NAME="Five_26"  value='1' <?php if ($View_Five_26=="1"){echo("checked");}?>>디자인중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='2' <?php if ($View_Five_26=="2"){echo("checked");}?>>교정중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='3' <?php if ($View_Five_26=="3"){echo("checked");}?>>인쇄중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='4' <?php if ($View_Five_26=="4"){echo("checked");}?>>가공중
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='5' <?php if ($View_Five_26=="5"){echo("checked");}?>>납품
</td>
</tr>

<?php if ($code=="Print"){}else{if($mode=="form"){?>
<tr>
<td align=center>
<input type='submit' value=' <?php if ($code=="modify"){?>수정<?}else{?>저장<?php } ?> 합니다.'>
<BR><BR>
</td>
</tr>
<?}}?>

</table>

<? } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM MlangPrintAuto_MemberOrderOffice");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php"; }
if($photofile_2){ include"upload_2.php"; }
if($photofile_3){ include"upload_3.php"; }
if($photofile_4){ include"upload_4.php"; }


$date=date("Y-m-d H:i;s");

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$dbinsert ="insert into MlangPrintAuto_MemberOrderOffice values('$new_no',
'$One_1',
'$One_2',
'$One_3',
'$One_4',
'$One_5',
'$One_6',
'$One_7',
'$One_8',
'$One_9',
'$One_10',
'$One_11',
'$One_12', 
'$Two_1',
'$Two_2',
'$Two_3',
'$Two_4',
'$Two_5',
'$Two_6',
'$Two_7',
'$Two_8',
'$Two_9',
'$Two_10',
'$Two_11',
'$Two_12',
'$Two_13',
'$Two_14',
'$Two_15',
'$Two_16',
'$Two_17',
'$Two_18',
'$Two_19',
'$Two_20',
'$Two_21',
'$Two_22',
'$Two_23',
'$Two_24',
'$Two_25',
'$Two_26',
'$Two_27',
'$Two_28',
'$Two_29',
'$Two_30',
'$Two_31',
'$Two_32',
'$Two_33',
'$Two_34',
'$Two_35',
'$Two_36',
'$Two_37',
'$Two_38',
'$Two_39',
'$Two_40',
'$Two_41',
'$Two_42',
'$Two_43',
'$Two_44',
'$Two_45',
'$Two_46',
'$Two_47',
'$Two_48',
'$Two_49',
'$Two_50',
'$Two_51',
'$Two_52',
'$Two_53',
'$Two_54',
'$Two_55',
'$Two_56',
'$Two_57',
'$Two_58',
'$Tree_1',
'$Tree_2',
'$Tree_3',
'$Tree_4',
'$Tree_5',
'$Tree_6',
'$Tree_7',
'$Tree_8',
'$Tree_9',
'$Tree_10',
'$Tree_11',
'$Tree_12',
'$Tree_13',
'$Tree_14',
'$Tree_15', 
'$Four_1',
'$Four_2',
'$Four_3',
'$Four_4',
'$Four_5',
'$Four_6',
'$Four_7',
'$Four_8',
'$Four_9',
'$Four_10',
'$Four_11',
'$Four_12', 
'$Five_1',
'$Five_2',
'$Five_3',
'$Five_4',
'$Five_5',
'$Five_6',
'$Five_7',
'$Five_8',
'$Five_9',
'$Five_10',
'$Five_11',
'$Five_12',
'$Five_13',
'$Five_14',
'$Five_15',
'$Five_16',
'$Five_17',
'$Five_18',
'$Five_19',
'$Five_20',
'$Five_21',
'$photofile_1Name',
'$photofile_2Name',
'$photofile_3Name',
'$photofile_4Name',
'$Five_26',
'$Five_27',
'$Five_28',
'$Five_29',
'$cont',
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="modify_ok"){

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php";  $Five_22KKok="Five_22='$photofile_1Name',"; }
if($photofile_2){ include"upload_2.php";  $Five_23KKok="Five_23='$photofile_2Name',";  }
if($photofile_3){ include"upload_3.php";  $Five_24KKok="Five_24='$photofile_3Name',";  }
if($photofile_4){ include"upload_4.php";  $Five_25KKok="Five_25='$photofile_4Name',";  }

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET
One_1='$One_1',
One_2='$One_2',
One_3='$One_3',
One_4='$One_4',
One_5='$One_5',
One_6='$One_6',
One_7='$One_7',
One_8='$One_8',
One_9='$One_9',
One_10='$One_10',
One_11='$One_11',
One_12='$One_12',
Two_1='$Two_1',
Two_2='$Two_2',
Two_3='$Two_3',
Two_4='$Two_4',
Two_5='$Two_5',
Two_6='$Two_6',
Two_7='$Two_7',
Two_8='$Two_8',
Two_9='$Two_9',
Two_10='$Two_10',
Two_11='$Two_11',
Two_12='$Two_12',
Two_13='$Two_13',
Two_14='$Two_14',
Two_15='$Two_15',
Two_16='$Two_16',
Two_17='$Two_17',
Two_18='$Two_18',
Two_19='$Two_19',
Two_20='$Two_20',
Two_21='$Two_21',
Two_22='$Two_22',
Two_23='$Two_23',
Two_24='$Two_24',
Two_25='$Two_25',
Two_26='$Two_26',
Two_27='$Two_27',
Two_28='$Two_28',
Two_29='$Two_29',
Two_30='$Two_30',
Two_31='$Two_31',
Two_32='$Two_32',
Two_33='$Two_33',
Two_34='$Two_34',
Two_35='$Two_35',
Two_36='$Two_36',
Two_37='$Two_37',
Two_38='$Two_38',
Two_39='$Two_39',
Two_40='$Two_40',
Two_41='$Two_41',
Two_42='$Two_42',
Two_43='$Two_43',
Two_44='$Two_44',
Two_45='$Two_45',
Two_46='$Two_46',
Two_47='$Two_47',
Two_48='$Two_48',
Two_49='$Two_49',
Two_50='$Two_50',
Two_51='$Two_51',
Two_52='$Two_52',
Two_53='$Two_53',
Two_54='$Two_54',
Two_55='$Two_55',
Two_56='$Two_56',
Two_57='$Two_57',
Two_58='$Two_58',
Tree_1='$Tree_1',
Tree_2='$Tree_2',
Tree_3='$Tree_3',
Tree_4='$Tree_4',
Tree_5='$Tree_5',
Tree_6='$Tree_6',
Tree_7='$Tree_7',
Tree_8='$Tree_8',
Tree_9='$Tree_9',
Tree_10='$Tree_10',
Tree_11='$Tree_11',
Tree_12='$Tree_12',
Tree_13='$Tree_13',
Tree_14='$Tree_14',
Tree_15='$Tree_15',
Four_1='$Four_1',
Four_2='$Four_2',
Four_3='$Four_3',
Four_4='$Four_4',
Four_5='$Four_5',
Four_6='$Four_6',
Four_7='$Four_7',
Four_8='$Four_8',
Four_9='$Four_9',
Four_10='$Four_10',
Four_11='$Four_11',
Four_12='$Four_12',
Five_1='$Five_1',
Five_2='$Five_2',
Five_3='$Five_3',
Five_4='$Five_4',
Five_5='$Five_5',
Five_6='$Five_6',
Five_7='$Five_7',
Five_8='$Five_8',
Five_9='$Five_9',
Five_10='$Five_10',
Five_11='$Five_11',
Five_12='$Five_12',
Five_13='$Five_13',
Five_14='$Five_14',
Five_15='$Five_15',
Five_16='$Five_16',
Five_17='$Five_17',
Five_18='$Five_18',
Five_19='$Five_19',
Five_20='$Five_20',
Five_21='$Five_21', $Five_22KKok $Five_23KKok $Five_24KKok $Five_25KKok
Five_26='$Five_26',
Five_27='$Five_27',
Five_28='$Five_28',
Five_29='$Five_29'
WHERE no='$no'";
$result= mysql_query($query,$db);


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
		opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysql_close($db);


}
?>