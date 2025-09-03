<?php
// 로그인 체크 및 회원 ID 추출
if ((isset($HTTP_COOKIE_VARS['id_login_ok']) && $HTTP_COOKIE_VARS['id_login_ok']) || (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'])) {

    if (isset($HTTP_COOKIE_VARS['id_login_ok']) && $HTTP_COOKIE_VARS['id_login_ok']) {
        $WebtingMemberLogin_id = $HTTP_COOKIE_VARS['id_login_ok'];
    } else if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok']) {
        $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
    }

} else {
    echo "
        <script language='javascript'>
        alert('\\n회원으로 로그인후 이용할수 있습니다.\\n');
        window.self.close();
        </script>
    ";
    exit;
}

$titleT = "자료불량신고";
include "../db.php";

if ($page == "start") {
    ?>
    <html>
    <head>
    <title><?php echo $titleT; ?> - <?php echo $SiteTitle; ?></title>
    <STYLE>
    <!--
    p,br,body,td{color:#000000; font-size:9pt; FONT-FAMILY:굴림;}
    -->
    </STYLE>

    <script language="javascript">
    function SinGoCheckField()
    {
        var f = document.SinGoInfo;
        if (f.cont.value.length < 20 ) {
            alert("신고내용은 20 자 이상을 적어 주셔야 합니다..");
            f.cont.focus();
            return false;
        }
    }
    </script>
    </head>

    <body bgcolor='#FFFFFF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>
    <!------- top,로고 메뉴--------->
    <table border=0 align=center width=100% cellpadding='0' cellspacing='0'>
    <tr bgcolor='#F7FFEF'>
    <td height=43><font style='font-size:20pt; color:#8CDF63;'><?php echo $SiteTitle; ?></font></td>
    <td align=right>
    <font style='font-size:11pt; color:green;'><b><?php echo $titleT; ?></b></font>&nbsp;&nbsp;</td></tr>
    <tr bgcolor='#8CDF63'><td colspan=2><img src='../img/12345.gif' width=5 height=5></td></tr>
    </table>
    <!------- top,로고 메뉴--------->

    <table border=0 align=center width=90% cellpadding='10' cellspacing='0'>
    <form name='SinGoInfo' method='post' OnSubmit='javascript:return SinGoCheckField()' action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'>
    <input type='hidden' name='page' value='form_ok'>
    <input type='hidden' name='no' value='<?php echo $no; ?>'>
    <input type='hidden' name='table' value='<?php echo $table; ?>'>
    <tr><td width=100% valign=top align=center>
    <font style='color:#6E9600; font-size:9pt; line-height:130%;'><b>
    신고를 해주시면 Point 1000 점이 회원님에게 적립됩니다..<BR>
    정당한 자료를 고의적으로 상습 불법 신고자는 회원 자동<font color=red>탈퇴처리</font> 됩니다.
    </b></font>
    <BR><BR>
    <p align=left style='text-indent:0; margin-left:100pt;'>
    신고대상: <font style='color:#7C04E3; font-size:10pt;'><b><?php echo $title; ?></b></font><BR><BR>
    신고회원ID: <?php echo $WebtingMemberLogin_id; ?>
    </p>
    </td></tr>
    <tr><td width=100% valign=top align=center>
    아래에 신고내용 을 입력하여 주세요..*^^*<BR><BR>
    <textarea name="cont" rows="3" cols="50" style='font-size: 9pt; border:1px solid #8CDF63; color:#000000; background-color:#FFFFFF;'></textarea>
    <input type="submit" value="신고접수" style="width:70px; height:45px; font-size: 9pt; border:1px solid green; font-weight:bold; color:#FFFFFF; background-color:#8CDF63;">
    </td></tr></form></table>

    <br><br>
    <table border=0 align=center width=100% cellpadding='10' cellspacing='0' bgcolor='#E7EFE7'>
    <tr><td align=center>
    <font style='line-height:140%; color:#60702F;'>
    본 정보는 회원님들에게 보다 낳은 정보를 제공하기 위한 <?php echo $SiteTitle; ?> 의 노력입니다.<br>
    신고해 주신 내용은 빠른 시간내에 확인하여 올바른 내용으로 변경될 수 있도록 하겠습니다.
    </font>
    </td></tr></table>

    <?php
}

if ($page == "form_ok") {

    // 필수값 체크
    if (!$no || !$table || !$cont) {
        echo "
        <script>
        alert('정상적인 접속 방법이 아닙니다.');
        window.self.close();
        </script>
        ";
        exit;
    }

    //한번 신고한 자료는 두번 신고가 안되게 처리한다...
    $resultGG = mysqli_query($db, "select * from BBS_Singo where BBS_table='$table' and BBS_no='$no' and  Member_id='$WebtingMemberLogin_id'");
    $rowGG = mysqli_fetch_array($resultGG);
    if ($rowGG) {
        $member_ip = $rowGG['Member_ip'];
        echo "<script language='javascript'>
        window.alert('{$WebtingMemberLogin_id} 회원님께서는 {$no} 번의 자료를\\n\\n접속IP: {$member_ip} 로 이미 신고하셨습니다.');
        history.go(-1);
        </script>
        ";
        exit;
    }

    // singo 테이블에 정보를 저장한다
    $result = mysqli_query($db, "SELECT max(no) FROM BBS_Singo");
    if (!$result) {
        echo "
            <script>
                window.alert(\"DB 접속 에러입니다!\")
                history.go(-1)
            </script>";
        exit;
    }
    $row = mysqli_fetch_row($result);

    if ($row[0]) {
        $new_no = $row[0] + 1;
    } else {
        $new_no = 1;
    }

    $date = date("Y-m-d H:i:s");
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
    $dbinsert = "insert into BBS_Singo values('$new_no',
    '$table',
    '$no',
    '$WebtingMemberLogin_id',
    '$REMOTE_ADDR',
    '$cont',
    '$date',
    '1'
    )";
    $result_insert = mysqli_query($db, $dbinsert);

    // 신고후 포인트 적립
    $DbDir = "..";
    $db_dir = "..";
    include "./admin_fild.php";
    include "../member/member_fild_id.php";
    $Point_TT_mode = "ComentSinGo";
    include "PointChick.php";

    //완료 메세지를 보인후 페이지를 이동 시킨다
    echo "
        <script language='javascript'>
        alert('\\n자료불량신고를 해주셔서 매우 감사합니다.\\n\\n신고를 해줌으로써 Point 1000 점이 회원님에게 적립되었습니다.');
        window.self.close();
        </script>
        ";
    exit;
}
?>

</body>
</html>
