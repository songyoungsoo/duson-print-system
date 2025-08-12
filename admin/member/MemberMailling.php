<?php
$M123 = "..";
include "../top.php"; 

if (isset($_POST['mode']) && $_POST['mode'] == "go") {
    include "../../db.php";
    include "./MaillingJoinAdminInfo.php";
?>

<BR><BR>

<FORM ACTION='<?php echo $_SERVER['PHP_SELF']; ?>' METHOD='POST'>
<INPUT TYPE=HIDDEN NAME=mode VALUE='sendmail_ok'>
<INPUT TYPE=HIDDEN NAME=ADMIN_TITLE VALUE='<?php echo $admin_name; ?>'>
<INPUT TYPE=HIDDEN NAME=ADMIN_URL VALUE='<?php echo $admin_url; ?>'>
<DIV ALIGN=LEFT>
<TABLE WIDTH=700 BORDER=0 CELLPADDING=5 CELLSPACING=5 align=center class='coolBar'>
<TR>
<TD COLSPAN=2>
<?php
// 데이터 조회
$table = "member";

$Mlang_query_inquiry = "SELECT * FROM $table";
$query_inquiry = $db->query($Mlang_query_inquiry);
$total_inquiry = $query_inquiry->num_rows;
$db->close();
?>

+ 총 <?php echo $total_inquiry; ?>명의 회원에게 메일을 발송합니다.
<INPUT TYPE=HIDDEN NAME=MEMBER_DATA VALUE='<?php echo $total_inquiry; ?>'>
</TD>
</TR>

<TR>
<TD WIDTH=15%>&nbsp;&nbsp; 제목</TD><TD WIDTH=85%><INPUT TYPE=TEXT NAME='SUBJECT' SIZE=50 VALUE='<?php echo $admin_name; ?>님께 보내는 회원 전체 메일입니다.'></TD>
</TR>

<TR>
<TD WIDTH=15%>&nbsp;&nbsp; 이름/이메일</TD><TD WIDTH=85%><INPUT TYPE=TEXT NAME='ADMIN_EMAIL' SIZE=50 VALUE='<?php echo $admin_email; ?>'> <INPUT TYPE=TEXT NAME='ADMIN_NAME' SIZE=30 VALUE='<?php echo $admin_name; ?>'></TD>
</TR>

<TR>
<TD COLSPAN=2>&nbsp;

폰트 : <SELECT NAME='FONT_FAMILY' size='1'>
<option selected value='굴림'>굴림</OPTION>
<OPTION VALUE='돋움'>돋움</OPTION>
<OPTION VALUE='바탕'>바탕</OPTION>
<OPTION VALUE='궁서'>궁서</OPTION>
<OPTION VALUE='Arial'>Arial</OPTION>
<OPTION VALUE='Verdana'>Verdana</OPTION>
</SELECT>

글자색 : <SELECT NAME='FONT_COLOR' size='1'>
<option selected value='black'>검정</OPTION>
<OPTION VALUE='white'>흰색</OPTION>
<OPTION VALUE='navy'>네이비</OPTION>
<OPTION VALUE='blue'>파랑</OPTION>
<OPTION VALUE='red'>빨강</OPTION>
<OPTION VALUE='purple'>보라</OPTION>
<OPTION VALUE='gray'>회색</OPTION>
<OPTION VALUE='yellow'>노랑</OPTION>
<OPTION VALUE='teal'>청록</OPTION>
</SELECT>

배경색 : <SELECT NAME='BGCOLOR' size='1'>
<option selected value='FFFFFF'>흰색</OPTION>
<OPTION VALUE='navy'>네이비</OPTION>
<OPTION VALUE='blue'>파랑</OPTION>
<OPTION VALUE='red'>빨강</OPTION>
<OPTION VALUE='purple'>보라</OPTION>
<OPTION VALUE='gray'>회색</OPTION>
<OPTION VALUE='yellow'>노랑</OPTION>
<OPTION VALUE='teal'>청록</OPTION>
</SELECT>

글자크기 : <SELECT NAME='FONT_SIZE' size='1'>
<OPTION VALUE='1'>1</OPTION>
<OPTION VALUE='2'>2</OPTION>
<OPTION VALUE='3' selected>3</OPTION>
<OPTION VALUE='4'>4</OPTION>
<OPTION VALUE='5'>5</OPTION>
<OPTION VALUE='6'>6</OPTION>
<OPTION VALUE='7'>7</OPTION>
</SELECT>

HTML : <SELECT NAME='HTML' size='1'>
<OPTION VALUE='Y'>ON</OPTION>
<OPTION VALUE='N' selected>OFF</OPTION>
</SELECT>


</TD>
</TR>

<TR>
<TD COLSPAN=2 ALIGN=CENTER><TEXTAREA NAME=CONTENT ROWS=18 COLS=94 STYLE='width:99%;'></TEXTAREA></TD>
</TR>

<TR>
<TD COLSPAN=2> + 메일 하단에 포함될 고정 내용을 입력해 주세요.</TD>
</TR>

<TR>
<TD COLSPAN=2>
<TEXTAREA NAME=CONTENT1 ROWS=5 COLS=94 STYLE='width:99%;'>
홈페이지 : <?php echo $admin_url; ?>

이메일 : <?php echo $admin_email; ?>


CopyRight (c) <?php echo $admin_name; ?> All Rights Reserved.</TEXTAREA></TD>
</TR>

</TABLE>

<p align=center>
<INPUT TYPE=SUBMIT VALUE=' 메일 발송 '>
</p>

</DIV>
</FORM>

</BODY>
</HTML>

<?php
}

if (isset($_POST['mode']) && $_POST['mode'] == "sendmail_ok") {

    function ERROR($msg)
    {
        echo "<script language='javascript'>
        window.alert('$msg');
        history.go(-1);
        </script>";
        exit;
    }

    $ADMIN_NAME = $_POST['ADMIN_NAME'];
    $ADMIN_EMAIL = $_POST['ADMIN_EMAIL'];
    $SUBJECT = $_POST['SUBJECT'];
    $CONTENT = $_POST['CONTENT'];
    $CONTENT1 = $_POST['CONTENT1'];
    $FONT_FAMILY = $_POST['FONT_FAMILY'];
    $FONT_COLOR = $_POST['FONT_COLOR'];
    $BGCOLOR = $_POST['BGCOLOR'];
    $FONT_SIZE = $_POST['FONT_SIZE'];
    $HTML = $_POST['HTML'];
    $MEMBER_DATA = $_POST['MEMBER_DATA'];

    if (!$ADMIN_NAME) {
        ERROR("관리자 이름을 입력하세요.");
    }
    if (!$ADMIN_EMAIL) {
        ERROR("관리자 이메일 주소를 입력하세요.");
    }
    if (!$SUBJECT) {
        ERROR("제목을 입력하세요.");
    }
    if (!$CONTENT) {
        ERROR("내용을 입력하세요.");
    }

    if ($HTML != "Y") {    
        $CONTENT = nl2br(htmlspecialchars(stripslashes($CONTENT), ENT_QUOTES));
    } else {
        $CONTENT = stripslashes($CONTENT);
    }
    $CONTENT1 = nl2br(htmlspecialchars(stripslashes($CONTENT1), ENT_QUOTES));

    $i = 0;
    $xx = 0;

    include "../../db.php";
    $result = $db->query("SELECT * FROM member");
    $rows = $result->num_rows;

    if ($rows > 0) {
        while ($i < $MEMBER_DATA) { 
            $row = $result->fetch_assoc();
            $MEMBER_ID = $row['id'];
            $MEMBER_NAME = $row['name'];
            $MEMBER_EMAIL = $row['email'];
            $REMAIL = "Y";
            $REGIS_OK = "checked";

            if ($REGIS_OK == "checked" && $REMAIL == "Y") {
                $SEND_CONTENT = "<HTML>
                <HEAD>
                <STYLE>
                <!--
                A:link {text-decoration:none;color:black;}
                A:visited {text-decoration:none;color:black;}
                A:hover {  text-decoration:underline;  color:#081E8A;}
                p,br,body,td {color:black; font-size:9pt; line-height:140%;}
                -->
                </STYLE>
                </HEAD>
                <BODY BGCOLOR='$BGCOLOR'>
                <table border=0 align=center width=100% cellpadding='5' cellspacing='1' BGCOLOR='#339999'>
                <TR>
                <TD><font style='color:#FFFFFF'>$SUBJECT</font></TD>
                </TR>
                <TR>
                <TD BGCOLOR='$BGCOLOR' HEIGHT='400' VALIGN='TOP'>
                <BR>
                <FONT COLOR='$FONT_COLOR' FACE='$FONT_FAMILY' SIZE='$FONT_SIZE'>$CONTENT</FONT>
                </TD>
                </TR>
                </TABLE>
                <BR><p align=center><font style='color:#939393; font-size:9pt;'>$CONTENT1</font></p>
                <BR><BR><BR>
                </BODY>
                </HTML>";

                $from = "$ADMIN_NAME <$ADMIN_EMAIL>";
                $TO = "$MEMBER_NAME <$MEMBER_EMAIL>";
                $headers = "From: $from\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                mail($TO, $SUBJECT, $SEND_CONTENT, $headers);

                $xx++;
                echo "<TABLE WIDTH=700 align=center><TR><TD>[$xx] $MEMBER_NAME ($MEMBER_EMAIL) 발송완료.</TD></TR></TABLE>";
            }
            $i++;
        }
        echo "<SCRIPT LANGUAGE=JAVASCRIPT>
        window.alert('총 $i 명의 회원님께 $xx 건의 이메일을 발송 완료했습니다.');
        </SCRIPT>";

        echo "<P><TABLE WIDTH=700 ALIGN=CENTER><TR><TD ALIGN=CENTER><input type='button' onClick='javascript:history.go(-1)' value='회원 전체 메일 보내기 페이지로 돌아가기'></TD></TR></TABLE><BR><BR><BR>";
        exit;
    } else {
        echo ("<script language='javascript'>
        window.alert('데이터가 없습니다.');
        history.go(-1);
        </script>");
        exit;
    }

    $db->close();
}
?>

<?php
include "../down.php";
?>