<?php
// 변수 초기화
$mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_POST['mode']) ? $_POST['mode'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$no = isset($_GET['no']) ? $_GET['no'] : (isset($_POST['no']) ? $_POST['no'] : '');
$page = isset($_GET['page']) ? $_GET['page'] : (isset($_POST['page']) ? $_POST['page'] : '');
$category = isset($_GET['category']) ? $_GET['category'] : (isset($_POST['category']) ? $_POST['category'] : '');
$offset = isset($_GET['offset']) ? $_GET['offset'] : (isset($_POST['offset']) ? $_POST['offset'] : '');

$BbsDir = isset($BbsDir) ? $BbsDir : ".";

// 스킨 설정 저장
$original_skin = isset($BBS_ADMIN_skin) ? $BBS_ADMIN_skin : '';

include "$BbsDir/admin_fild.php";

// 스킨이 명시적으로 설정되어 있으면 admin_fild.php에서 가져온 설정을 덮어씌움
if (!empty($original_skin)) {
    $BBS_ADMIN_skin = $original_skin;
}

// 헤더 인클루드 함수 정의
function include_header($header_include) {
    if (!empty($header_include)) {
        if (file_exists($header_include)) {
            include $header_include;
        } else if (strpos($header_include, 'http://') === 0) {
            // URL include가 불가능하므로 대체 메시지 출력
            echo "<!-- 외부 URL include는 서버 설정에 의해 불가능합니다: $header_include -->";
        }
    }
}

// 푸터 인클루드 함수 정의
function include_footer($footer_include) {
    if (!empty($footer_include)) {
        if (file_exists($footer_include)) {
            include $footer_include;
        } else if (strpos($footer_include, 'http://') === 0) {
            // URL include가 불가능하므로 대체 메시지 출력
            echo "<!-- 외부 URL include는 서버 설정에 의해 불가능합니다: $footer_include -->";
        }
    }
}

if(!$mode){
    include_header($BBS_ADMIN_header_include);
    if($BBS_ADMIN_header){echo("$BBS_ADMIN_header");}

    //echo("$BBS_ADMIN_title_SUBJECT_ok"); 현재위치 호출할경우.

    if($BBS_ADMIN_skin){include "$BbsDir/skin/$BBS_ADMIN_skin/list.php";}else{include "$BbsDir/list.php";}

    if($BBS_ADMIN_footer){echo("$BBS_ADMIN_footer");} 
    include_footer($BBS_ADMIN_footer_include);
}


if($mode=="list") { 
    include_header($BBS_ADMIN_header_include);
    if($BBS_ADMIN_header){echo("$BBS_ADMIN_header");}

    //echo("$BBS_ADMIN_title_SUBJECT_ok"); 현재위치 호출할경우.

    if($BBS_ADMIN_skin){include "$BbsDir/skin/$BBS_ADMIN_skin/list.php";}else{include "$BbsDir/list.php";}

    if($BBS_ADMIN_footer){echo("$BBS_ADMIN_footer");} 
    include_footer($BBS_ADMIN_footer_include);
}

if($mode=="write") { 
    include "$BbsDir/write_select.php"; 
    include "$BbsDir/Level_select.php"; 

    if($no){include "$BbsDir/view_fild.php";}

    include_header($BBS_ADMIN_header_include);
    if($BBS_ADMIN_header){echo("$BBS_ADMIN_header");}

    if($BBS_ADMIN_skin=="board"){
        $pp="form";
        include "$BbsDir/write.php";
    }else{
        $pp="form";
        include "$BbsDir/skin/$BBS_ADMIN_skin/write.php";
    }

    if($BBS_ADMIN_footer){echo("$BBS_ADMIN_footer");} 
    include_footer($BBS_ADMIN_footer_include);
}

if($mode=="write_ok") { 
    include "$BbsDir/write_select.php"; 

    // POST 데이터 초기화
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $title = isset($_POST['title']) ? $_POST['title'] : '';

    // 디버그 정보 제거 (정상 작동 확인됨)

    if ( !$name || !$title  ) {
        echo ("<script language=javascript>
        window.alert('작성자와 제목을 모두 입력해주세요.');
        history.go(-1);
        </script>
        ");
        exit;
    }

    if($BBS_ADMIN_skin=="board"){
        $pp="form_ok";
        include "$BbsDir/write.php";
    }else{
        $pp="form_ok";
        include "$BbsDir/skin/$BBS_ADMIN_skin/write.php";
    }
}


if($mode=="modify_ok") { 
    include "$BbsDir/write_select.php"; 

    // POST 데이터 초기화
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $title = isset($_POST['title']) ? $_POST['title'] : '';

    if ( !$name || !$title ) {
        echo ("<script language=javascript>
        window.alert('작성자와 제목을 모두 입력해주세요.');
        history.go(-1);
        </script>
        ");
        exit;
    }

    if($BBS_ADMIN_skin=="board"){
        $pp="modify_ok";
        include "$BbsDir/write.php";
    }else{
        $pp="modify_ok";
        include "$BbsDir/skin/$BBS_ADMIN_skin/write.php";
    }
}


if($mode=="view") { 
    include "$BbsDir/view_fild.php";

    include_header($BBS_ADMIN_header_include);
    if($BBS_ADMIN_header){echo("$BBS_ADMIN_header");}

    if($BBS_ADMIN_skin=="board"){
        include "$BbsDir/view_select.php";
        include "$BbsDir/Level_select.php"; 
        include "$BbsDir/view.php";
    }else{
        include "$BbsDir/view_select.php";
        include "$BbsDir/Level_select.php"; 
        include "$BbsDir/skin/$BBS_ADMIN_skin/view.php";
    }

    if($BBS_ADMIN_footer){echo("$BBS_ADMIN_footer");} 
    include_footer($BBS_ADMIN_footer_include);
}


if($mode=="delete") { 
    include "$BbsDir/view_fild.php";

    include_header($BBS_ADMIN_header_include);
    if($BBS_ADMIN_header){echo("$BBS_ADMIN_header");}

    if($BBS_ADMIN_write_select=="member"){
                        
        if($BbsViewmlang_bbs_member=="$WebtingMemberLogin_id"){}else{
            echo ("<script language=javascript>
            window.alert('$WebtingMemberLogin_id 님께서는 글의 등록자가 아님으로\\n\\n본 글을 [삭제]할 권환이 없습니다.');
            history.go(-1);
            </script>");
            exit; 
        }

    }else{
        $TT_msg="자료 삭제를 하시려면 비밀번호를 입력하셔야 합니다..";
        include "$BbsDir/bbs_chick.php";
    }

    include "$BbsDir/delete.php";
}

// $submit 변수 초기화 및 확인
$submit = isset($_POST['submit']) ? $_POST['submit'] : (isset($_GET['submit']) ? $_GET['submit'] : '');

if($submit) { 
    include "$BbsDir/list.php";
}
?>

<style>
.Copyright{font-family:굴림; font-size: 8pt; color:#5B5B5B; text-decoration:none}
a.Copyright:link, a.Copyright:visited{font-family:굴림; font-size: 8pt; color:#5B5B5B; text-decoration:none}
a.Copyright:hover, a.v:active{font-family:굴림; font-size: 8pt; color:#9900FF; text-decoration:underline}
</style>

<p align=center class='Copyright'><?php echo isset($SoftCopyright) ? $SoftCopyright : ''; ?></p>