<?php
include __DIR__."/../../db.php";  // DB 연결 파일
include __DIR__."/../config.php"; // 인증 파일

// GET 파라미터 검증
if (!isset($_GET['code'], $_GET['no']) || $_GET['code'] !== 'start') {
    die("<script>alert('파라미터 오류');window.close();</script>");
}

$no = (int)$_GET['no'];
$db = new mysqli($host, $user, $password, $dataname); // 새 연결 생성
if ($code == "start") {
    $stmt = $db->prepare("SELECT * FROM  Mlang_BBS_Admin WHERE no=?");
    $stmt->bind_param("s", $no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $BBS_ADMIN_no = $row['no'];
        $BBS_ADMIN_title = $row['title'];
        $BBS_ADMIN_id = $row['id'];
        $BBS_ADMIN_pass = $row['pass'];
        $BBS_ADMIN_skin = $row['skin'];
        $BBS_ADMIN_header = $row['header'];
        $BBS_ADMIN_footer = $row['footer'];
        $BBS_ADMIN_header_include = $row['header_include'];
        $BBS_ADMIN_footer_include = $row['footer_include'];
        $BBS_ADMIN_file_select = $row['file_select'];
        $BBS_ADMIN_link_select = $row['link_select'];
        $BBS_ADMIN_recnum = $row['recnum'];
        $BBS_ADMIN_lnum = $row['lnum'];
        $BBS_ADMIN_cutlen = $row['cutlen'];
        $BBS_ADMIN_New_Article = $row['New_Article'];
        $BBS_ADMIN_date_select = $row['date_select'];
        $BBS_ADMIN_name_select = $row['name_select'];
        $BBS_ADMIN_count_select = $row['count_select'];
        $BBS_ADMIN_recommendation_select = $row['recommendation_select'];
        $BBS_ADMIN_secret_select = $row['secret_select'];
        $BBS_ADMIN_write_select = $row['write_select'];
        $BBS_ADMIN_view_select = $row['view_select'];
        $BBS_ADMIN_td_width = $row['td_width'];
        $BBS_ADMIN_td_color1 = $row['td_color1'];
        $BBS_ADMIN_td_color2 = $row['td_color2'];
        $BBS_ADMIN_MAXFSIZE = $row['MAXFSIZE'];
        $BBS_ADMIN_PointBoardView = $row['PointBoardView'];
        $BBS_ADMIN_PointBoard = $row['PointBoard'];
        $BBS_ADMIN_PointComent = $row['PointComent'];
        $BBS_ADMIN_ComentStyle = $row['ComentStyle'];
        $BBS_ADMIN_cate = $row['cate'];
        $BBS_ADMIN_advance = $row['advance'];
        $BBS_ADMIN_NoticeStyle = $row['NoticeStyle'];
        $BBS_ADMIN_NoticeStyleSu = $row['NoticeStyleSu'];
        $BBS_ADMIN_BBS_Level = $row['BBS_Level'];
    } else {
        echo "
            <script>
                alert('게시판 관리 테이블의 데이터를 찾을 수 없습니다.\\n\\n데이터를 확인해주세요..!!');
                opener.parent.location.reload();
                window.self.close();
            </script>";
        exit;
    }

    $stmt->close();
    $db->close();
} else {
    echo "
        <script>
            alert('잘못된 접근입니다.');
            opener.parent.location.reload();
            window.self.close();
        </script>";
    exit;
}
?>

<?php include "../title.php"; ?>

<style>
body, td, input, select, submit {color:#FFFFFF; font-size:9pt; font-family:Arial, sans-serif; word-break:break-all;}
input, select, submit {color:#330000; font-size:9pt; font-family:Arial, sans-serif;}
textarea {background-color:#FFFFFF; color:green; font-size:9pt; font-family:Arial, sans-serif;}
</style>

<script src="../js/coolbar.js" type="text/javascript"></script>

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

function BBSAdminModifyCheckField() {
    var f = document.BBSAdminModify;

    if (f.skin.value == "0") {
        alert("게시판 SKIN을 선택해 주세요.");
        return false;
    }

    if (f.title.value == "") {
        alert("게시판 제목을 입력해 주세요.");
        return false;
    }

    if (f.pass.value == "") {
        alert("게시판 비밀번호를 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.pass.value, ALPHA + NUM)) {
        alert("게시판 비밀번호는 영문자와 숫자로만 입력해 주세요.");
        return false;
    }
    if (f.pass.value.length < 4 || f.pass.value.length > 20) {
        alert("게시판 비밀번호는 4자 이상 20자 이하로 입력해 주세요.");
        return false;
    }

    if (f.MAXFSIZE.value == "") {
        alert("첨부파일 최대 크기를 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.MAXFSIZE.value, NUM)) {
        alert("첨부파일 최대 크기는 숫자로 입력해 주세요.");
        return false;
    }

    if (f.recnum.value == "") {
        alert("게시판 페이지 당 표시할 게시물 수를 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.recnum.value, NUM)) {
        alert("게시판 페이지 당 표시할 게시물 수는 숫자로 입력해 주세요.");
        return false;
    }

    if (f.lnum.value == "") {
        alert("게시판 리스트 이동 링크 수를 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.lnum.value, NUM)) {
        alert("게시판 리스트 이동 링크 수는 숫자로 입력해 주세요.");
        return false;
    }

    if (f.cutlen.value == "") {
        alert("게시판 제목 자르기 길이를 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.cutlen.value, NUM)) {
        alert("게시판 제목 자르기 길이는 숫자로 입력해 주세요.");
        return false;
    }

    if (f.New_Article.value == "") {
        alert("게시판 새로운 글 표시 기간을 입력해 주세요.");
        return false;
    }
    if (!TypeCheck(f.New_Article.value, NUM)) {
        alert("게시판 새로운 글 표시 기간은 숫자로 입력해 주세요.");
        return false;
    }

    if (f.td_width.value == "") {
        alert("게시판 넓이를 입력해 주세요.");
        return false;
    }

    if (f.td_color1.value == "") {
        alert("첫 번째 행 색상을 입력해 주세요.");
        return false;
    }

    if (f.td_color2.value == "") {
        alert("두 번째 행 색상을 입력해 주세요.");
        return false;
    }

    return true;
}

function NoticeStyleChick() {
    var f = document.BBSAdminModify;
    f.NoticeStyleSu.disabled = !f.NoticeStyle[0].checked;
    if (!f.NoticeStyle[1].checked) {
        f.NoticeStyleSu.focus();
    } else {
        f.NoticeStyleSu.value = "<?php echo $BBS_ADMIN_NoticeStyleSu?>";
    }
}
</script>

</head>

<body bgcolor='#D9CEAE' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 align=center width=100% cellpadding=10 cellspacing=0 class='coolBar'>
<tr>
<td width=100% valign=top height=600>

<table border=0 align=center width=100% cellpadding=5 cellspacing=1>

<form name='BBSAdminModify' method='post' onsubmit='return BBSAdminModifyCheckField()' action='<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>'>
<INPUT TYPE='hidden' NAME='code' VALUE="ok">
<INPUT TYPE='hidden' NAME='no' VALUE="<?php echo $BBS_ADMIN_no?>">

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 분류&nbsp;</font></td>
<td width=80% bgcolor='#575757'><?php include "BbsAdminCate.php"; ?></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 ID&nbsp;</font></td>
<td width=80% bgcolor='#575757'><?php echo $BBS_ADMIN_id?></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 제목&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=40 maxLength='100' NAME='title' VALUE="<?php echo $BBS_ADMIN_title?>"></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>비밀번호&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='PASSWORD' SIZE=20 maxLength='20' NAME='pass' VALUE="<?php echo $BBS_ADMIN_pass?>"></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>첨부파일 사용&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='file_select' VALUE="yes" <?php if ($BBS_ADMIN_file_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='file_select' VALUE="no" <?php if ($BBS_ADMIN_file_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>첨부파일 최대 크기&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=10 maxLength='10' NAME='MAXFSIZE' VALUE="<?php echo $BBS_ADMIN_MAXFSIZE?>"> KB</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>링크 사용&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='link_select' VALUE="yes" <?php if ($BBS_ADMIN_link_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='link_select' VALUE="no" <?php if ($BBS_ADMIN_link_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>페이지 당 표시할 게시물 수&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='recnum' VALUE="<?php echo $BBS_ADMIN_recnum?>"> 개</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>리스트 이동 링크 수&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='lnum' VALUE="<?php echo $BBS_ADMIN_lnum?>"> 개</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>제목 자르기 길이&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=5 maxLength='3' NAME='cutlen' VALUE="<?php echo $BBS_ADMIN_cutlen?>"> 자</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>새로운 글 표시 기간&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=5 maxLength='1' NAME='New_Article' VALUE="<?php echo $BBS_ADMIN_New_Article?>"> 일</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>날짜 표시&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='date_select' VALUE="yes" <?php if ($BBS_ADMIN_date_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='date_select' VALUE="no" <?php if ($BBS_ADMIN_date_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>이름 표시&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='name_select' VALUE="yes" <?php if ($BBS_ADMIN_name_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='name_select' VALUE="no" <?php if ($BBS_ADMIN_name_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>조회수 표시&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='count_select' VALUE="yes" <?php if ($BBS_ADMIN_count_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='count_select' VALUE="no" <?php if ($BBS_ADMIN_count_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>추천수 표시&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='recommendation_select' VALUE="yes" <?php if ($BBS_ADMIN_recommendation_select == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='recommendation_select' VALUE="no" <?php if ($BBS_ADMIN_recommendation_select == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>비밀글/공개글&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='secret_select' VALUE="yes" <?php if ($BBS_ADMIN_secret_select == "yes") echo "checked"; ?>>비밀글
<INPUT TYPE='radio' NAME='secret_select' VALUE="no" <?php if ($BBS_ADMIN_secret_select == "no") echo "checked"; ?>>공개글
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>작성 권한&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='write_select' VALUE="member" <?php if ($BBS_ADMIN_write_select == "member") echo "checked"; ?>>회원만
<INPUT TYPE='radio' NAME='write_select' VALUE="guest" <?php if ($BBS_ADMIN_write_select == "guest") echo "checked"; ?>>전체
<INPUT TYPE='radio' NAME='write_select' VALUE="admin" <?php if ($BBS_ADMIN_write_select == "admin") echo "checked"; ?>>관리자만
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>조회 권한&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='view_select' VALUE="member" <?php if ($BBS_ADMIN_view_select == "member") echo "checked"; ?>>회원만
<INPUT TYPE='radio' NAME='view_select' VALUE="guest" <?php if ($BBS_ADMIN_view_select == "guest") echo "checked"; ?>>전체
<INPUT TYPE='radio' NAME='view_select' VALUE="admin" <?php if ($BBS_ADMIN_view_select == "admin") echo "checked"; ?>>관리자만
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 권한&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<SELECT NAME="BBS_Level">
<option value='2' <?php if ($BBS_ADMIN_BBS_Level == "2") echo "selected"; ?>>2 등급-운영자</option>
<option value='3' <?php if ($BBS_ADMIN_BBS_Level == "3") echo "selected"; ?>>3 등급-정회원</option>
<option value='4' <?php if ($BBS_ADMIN_BBS_Level == "4") echo "selected"; ?>>4 등급-일반회원</option>
<option value='5' <?php if ($BBS_ADMIN_BBS_Level == "5") echo "selected"; ?>>5 등급-전체</option>
</SELECT>
( 작성, 조회 권한은 회원만 가능합니다. )
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 넓이&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_width' VALUE="<?php echo $BBS_ADMIN_td_width?>">( 예: 800 또는 100% )</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>첫 번째 행 색상&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_color1' VALUE="<?php echo $BBS_ADMIN_td_color1?>">( 예: FFCCCC 또는 green )</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>두 번째 행 색상&nbsp;</font></td>
<td width=80% bgcolor='#575757'><INPUT TYPE='TEXT' SIZE=10 maxLength='20' NAME='td_color2' VALUE="<?php echo $BBS_ADMIN_td_color2?>">( 예: FFFFFF 또는 white )</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 상단 HTML 코드&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* 아래 입력란에 HTML 코드를 작성하세요....</font><BR>
<textarea cols=64 name=header rows=8><?php echo $BBS_ADMIN_header?></textarea>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>상단 include 파일&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* 절대 경로를 포함한 파일명을 입력하세요..( 예: 경로/파일명/test.htm )</font><BR>
<INPUT TYPE='TEXT' SIZE=40 NAME='header_include' VALUE="<?php echo $BBS_ADMIN_header_include?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>게시판 하단 HTML 코드&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* 아래 입력란에 HTML 코드를 작성하세요....</font><BR>
<textarea cols=64 name=footer rows=8><?php echo $BBS_ADMIN_footer?></textarea>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>하단 include 파일&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>* 절대 경로를 포함한 파일명을 입력하세요..( 예: 경로/파일명/test.htm )</font><BR>
<INPUT TYPE='TEXT' SIZE=40 NAME='footer_include' VALUE="<?php echo $BBS_ADMIN_footer_include?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>포인트 조회&nbsp;</font></td>
<td width=80% bgcolor='#575757'><font style='font-size:8pt;'>게시물 조회 시 부여할 포인트</font><BR><INPUT TYPE='TEXT' SIZE=5 NAME='PointView' VALUE="<?php echo $BBS_ADMIN_PointBoardView?>"></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>포인트 작성&nbsp;</font></td>
<td width=80% bgcolor='#575757'><font style='font-size:8pt;'>게시물 작성 시 부여할 포인트</font><BR><INPUT TYPE='TEXT' SIZE=5 NAME='PointWrite' VALUE="<?php echo $BBS_ADMIN_PointBoard?>"></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>댓글 작성 포인트&nbsp;</font></td>
<td width=80% bgcolor='#575757'><font style='font-size:8pt;'>댓글 작성 시 부여할 포인트</font><BR><INPUT TYPE='TEXT' SIZE=5 NAME='ComentWrite' VALUE="<?php echo $BBS_ADMIN_PointComent?>"></td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>댓글 표시&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='ComentStyle' VALUE="yes" <?php if ($BBS_ADMIN_ComentStyle == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='ComentStyle' VALUE="no" <?php if ($BBS_ADMIN_ComentStyle == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>카테고리&nbsp;</font></td>
<td width=80% bgcolor='#575757'>
<font style='font-size:8pt;'>구분자는 콜론(:)으로 입력해 주세요 (예: 공지사항:뉴스:자유게시판)</font><BR>
<INPUT TYPE='TEXT' SIZE=60 NAME='cate' VALUE="<?php echo $BBS_ADMIN_cate?>">
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>이름 표시</font></td>
<td width=80% bgcolor='#575757'>
<INPUT TYPE='radio' NAME='advance' VALUE="yes" <?php if ($BBS_ADMIN_advance == "yes") echo "checked"; ?>>YES
<INPUT TYPE='radio' NAME='advance' VALUE="no" <?php if ($BBS_ADMIN_advance == "no") echo "checked"; ?>>NO
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>공지사항 스타일</font></td>
<td width=80% bgcolor='#575757'>
<?php if ($BBS_ADMIN_NoticeStyle == "yes") { ?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' checked onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' onClick='NoticeStyleChick();'>NO
<?php } ?>
<?php if ($BBS_ADMIN_NoticeStyle == "no") { ?>
<INPUT TYPE="radio" NAME="NoticeStyle" value='yes' onClick='NoticeStyleChick();'>YES
<INPUT TYPE="radio" NAME="NoticeStyle" value='no' checked onClick='NoticeStyleChick();'>NO
<?php } ?>
</td>
</tr>

<tr>
<td width=20% class='coolBar' align='right'><font style='color:#000000;'>공지사항 스타일 수</font></td>
<td width=80% bgcolor='#575757'>
<input type="text" name="NoticeStyleSu" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">&nbsp;개
<input type="hidden" name="NoticeStyleSuNO" size="5" VALUE="<?php echo $BBS_ADMIN_NoticeStyleSu?>">
</td>
</tr>

</table>

<p align=center>
<input type='submit' value=' 저장합니다.. '>
<BR><BR>
</p>

</td>
</tr>
</table>

</body>
</html>

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['code'] == "ok") {
    $title = $_POST['title'];
    $pass = $_POST['pass'];
    $skin = $_POST['skin'];
    $header = $_POST['header'];
    $footer = $_POST['footer'];
    $header_include = $_POST['header_include'];
    $footer_include = $_POST['footer_include'];
    $file_select = $_POST['file_select'];
    $link_select = $_POST['link_select'];
    $recnum = $_POST['recnum'];
    $lnum = $_POST['lnum'];
    $cutlen = $_POST['cutlen'];
    $New_Article = $_POST['New_Article'];
    $date_select = $_POST['date_select'];
    $name_select = $_POST['name_select'];
    $count_select = $_POST['count_select'];
    $recommendation_select = $_POST['recommendation_select'];
    $secret_select = $_POST['secret_select'];
    $write_select = $_POST['write_select'];
    $view_select = $_POST['view_select'];
    $td_width = $_POST['td_width'];
    $td_color1 = $_POST['td_color1'];
    $td_color2 = $_POST['td_color2'];
    $MAXFSIZE = $_POST['MAXFSIZE'];
    $PointView = $_POST['PointView'];
    $PointWrite = $_POST['PointWrite'];
    $ComentWrite = $_POST['ComentWrite'];
    $ComentStyle = $_POST['ComentStyle'];
    $cate = $_POST['cate'];
    $advance = $_POST['advance'];
    $NoticeStyle = $_POST['NoticeStyle'];
    $NoticeStyleSu = $_POST['NoticeStyleSu'];
    $NoticeStyleSuNO = $_POST['NoticeStyleSuNO'];
    $BBS_Level = $_POST['BBS_Level'];

    if ($NoticeStyle == "yes") {
        $NoticeStyleSuOk = $NoticeStyleSu;
    } else {
        $NoticeStyleSuOk = $NoticeStyleSuNO;
    }

    include "../../db.php";
    $query = "UPDATE  Mlang_BBS_Admin SET 
    title=?, pass=?, skin=?, header=?, footer=?, header_include=?, footer_include=?, file_select=?, link_select=?, recnum=?, lnum=?, cutlen=?, New_Article=?, date_select=?, name_select=?, count_select=?, recommendation_select=?, secret_select=?, write_select=?, view_select=?, td_width=?, td_color1=?, td_color2=?, MAXFSIZE=?, PointBoardView=?, PointBoard=?, PointComent=?, ComentStyle=?, cate=?, advance=?, NoticeStyle=?, NoticeStyleSu=?, BBS_Level=?
    WHERE no=?";

    $stmt = $db->prepare($query);
    $stmt->bind_param("ssssssssssssssssssssssssssssssssssss", $title, $pass, $skin, $header, $footer, $header_include, $footer_include, $file_select, $link_select, $recnum, $lnum, $cutlen, $New_Article, $date_select, $name_select, $count_select, $recommendation_select, $secret_select, $write_select, $view_select, $td_width, $td_color1, $td_color2, $MAXFSIZE, $PointView, $PointWrite, $ComentWrite, $ComentStyle, $cate, $advance, $NoticeStyle, $NoticeStyleSuOk, $BBS_Level, $no);

    $result = $stmt->execute();

    if (!$result) {
        echo "
            <script language='javascript'>
                alert('DB 업데이트 실패!');
                history.go(-1);
            </script>";
        exit;
    } else {
        echo ("
            <script language='javascript'>
            alert('\\n데이터가 성공적으로 업데이트 되었습니다.\\n');
            </script>
        <meta http-equiv='Refresh' content='0; URL=" . htmlspecialchars($_SERVER['PHP_SELF']) . "?code=start&no=$no'>
        ");
        exit;
    }

    $stmt->close();
    $db->close();
}
?>

<?php include "../down.php"; ?>
