<?php
// 변수 초기화 (Notice 에러 방지)
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');
$DbDir = isset($DbDir) ? $DbDir : '..';

if(!$DbDir){$DbDir="..";}

// 게시판 관리 필드 //////////////////////////////////////////////////////////////////////////////////////////////////////////////
include "$DbDir/db.php";
$result = mysqli_query($db, "select * from mlang_bbs_admin where id='$table'");
if (!$result) {
    // 쿼리 실패 시 에러 메시지
    if (!$result) {
        // 테이블 조회 실패한 경우 기본값 설정
        echo "<p style='color:red;'>게시판 관리 정보를 가져오는데 실패했습니다: " . mysqli_error($db) . "</p>";
        echo "<p>테이블 이름: mlang_bbs_admin</p>";
        echo "<p>게시판 ID: $table</p>";
        
        // 기본값 설정
        $BBS_ADMIN_title = "게시판";
        $BBS_ADMIN_skin = "board";
        $BBS_ADMIN_td_width = "100%";
        $BBS_ADMIN_td_color1 = "#000000";
        $BBS_ADMIN_td_color2 = "#FFFFFF";
        $BBS_ADMIN_recnum = 15;
        $BBS_ADMIN_lnum = 8;
        $BBS_ADMIN_cutlen = 100;
        $BBS_ADMIN_New_Article = 3;
        $BBS_ADMIN_date_select = "yes";
        $BBS_ADMIN_name_select = "yes";
        $BBS_ADMIN_count_select = "yes";
        $BBS_ADMIN_recommendation_select = "yes";
        $BBS_ADMIN_secret_select = "yes";
        $BBS_ADMIN_write_select = "guest";
        $BBS_ADMIN_view_select = "guest";
        $BBS_ADMIN_td_width = "100%";
        $BBS_ADMIN_td_color1 = "#000000";
        $BBS_ADMIN_td_color2 = "#FFFFFF";
        $BBS_ADMIN_MAXFSIZE = "5000000";
        $BBS_ADMIN_PointBoardView = "0";
        $BBS_ADMIN_PointBoard = "0";
        $BBS_ADMIN_PointComent = "0";
        $BBS_ADMIN_ComentStyle = "yes";
        $BBS_ADMIN_cate = "no";
        $BBS_ADMIN_advance = "no";
        $BBS_ADMIN_NoticeStyle = "no";
        $BBS_ADMIN_NoticeStyleSu = "0";
        $BBS_ADMIN_BBS_Level = "0";
        
        $BBS_ADMIN_title_SUBJECT = htmlspecialchars($BBS_ADMIN_title);
        $BBS_ADMIN_title_SUBJECT_ok = "▒ $BBS_ADMIN_title_SUBJECT";
        
        mysqli_close($db);
        // return 문 제거 - 함수가 아닌 곳에서는 사용하지 않음
        $rows = 1; // 기본값 설정으로 처리된 것으로 간주
    }
}

$rows = mysqli_num_rows($result);
if ($rows) {

while($row= mysqli_fetch_array($result)) 
{ 
//  no       : 게시판 번호
//  title      : 게시판 제목
//  id        : 게시판 ID
//  pass    : 게시판 비밀번호
//  header  : 윗 html 내용
//  footer   : 아래 html 내용
//  header_include  : 윗 INCLUDE 파일
//  footer_include   : 아래 INCLUDE 파일    
//  file_select  : 파일을 받을 건가의 선택여부
//  link_select  : 링크을 할 건가의 선택여부
//  recnum : 한페이지당 출력수
//  lnum    : 페이지이동 메뉴수
//  cutlen  :  제목글자수 끊기
//  New_Article   : 새글표시 유지기간
//  date_select    : 등록일 출력여부
//  name_select   : 이름 출력여부
//  count_select   : 조회수 출력여부
//  recommendation_select   : 추천수 출력여부
//  secret_select   : 공개 비공개 출력여부
//  write_select     : 쓰기 권한 - member(회원들), guest(아무나), admin(관리자만)
//  td_width            : 게시판의 넓이
//  td_color1          : 제목 등... 상단색
//  td_color2          : 리스트 목록색
//  MAXFSIZE         : 첨부파일의 용량

$BBS_ADMIN_no=$row['no'];
$BBS_ADMIN_title=$row['title']; 
$BBS_ADMIN_id=$row['id']; 
$BBS_ADMIN_pass=$row['pass']; 
$BBS_ADMIN_skin=$row['skin']; 
$BBS_ADMIN_header=$row['header']; 
$BBS_ADMIN_footer=$row['footer']; 
$BBS_ADMIN_header_include=$row['header_include']; 
$BBS_ADMIN_footer_include=$row['footer_include'];   
$BBS_ADMIN_file_select=$row['file_select']; 
$BBS_ADMIN_link_select=$row['link_select']; 
$BBS_ADMIN_recnum=$row['recnum']; 
$BBS_ADMIN_lnum=$row['lnum']; 
$BBS_ADMIN_cutlen=$row['cutlen']; 
$BBS_ADMIN_New_Article=$row['New_Article']; 
$BBS_ADMIN_date_select=$row['date_select']; 
$BBS_ADMIN_name_select=$row['name_select']; 
$BBS_ADMIN_count_select=$row['count_select']; 
$BBS_ADMIN_recommendation_select=$row['recommendation_select']; 
$BBS_ADMIN_secret_select=$row['secret_select']; 
$BBS_ADMIN_write_select=$row['write_select']; 
$BBS_ADMIN_view_select=$row['view_select']; 
$BBS_ADMIN_td_width=$row['td_width'];
$BBS_ADMIN_td_color1=$row['td_color1']; 
$BBS_ADMIN_td_color2=$row['td_color2']; 
$BBS_ADMIN_MAXFSIZE=$row['MAXFSIZE'];
$BBS_ADMIN_PointBoardView=$row['PointBoardView']; 
$BBS_ADMIN_PointBoard=$row['PointBoard']; 
$BBS_ADMIN_PointComent=$row['PointComent']; 
$BBS_ADMIN_ComentStyle=$row['ComentStyle']; 
$BBS_ADMIN_cate=$row['cate']; 
$BBS_ADMIN_advance=$row['advance']; 
$BBS_ADMIN_NoticeStyle=$row['NoticeStyle']; 
$BBS_ADMIN_NoticeStyleSu=$row['NoticeStyleSu']; 
$BBS_ADMIN_BBS_Level=$row['BBS_Level']; 

}

} else {
    // 게시판 테이블에 자료가 없는 경우 기본값 설정
    echo "<p style='color:orange;'>게시판 테이블에 자료가 없습니다. 기본값을 사용합니다.</p>";
    
    // 기본값 설정
    $BBS_ADMIN_title = "게시판";
    $BBS_ADMIN_skin = "board";
    $BBS_ADMIN_td_width = "100%";
    $BBS_ADMIN_td_color1 = "#000000";
    $BBS_ADMIN_td_color2 = "#FFFFFF";
    $BBS_ADMIN_recnum = 15;
    $BBS_ADMIN_lnum = 8;
    $BBS_ADMIN_cutlen = 100;
    $BBS_ADMIN_New_Article = 3;
    $BBS_ADMIN_date_select = "yes";
    $BBS_ADMIN_name_select = "yes";
    $BBS_ADMIN_count_select = "yes";
    $BBS_ADMIN_recommendation_select = "yes";
    $BBS_ADMIN_secret_select = "yes";
    $BBS_ADMIN_write_select = "guest";
    $BBS_ADMIN_view_select = "guest";
    $BBS_ADMIN_td_width = "100%";
    $BBS_ADMIN_td_color1 = "#000000";
    $BBS_ADMIN_td_color2 = "#FFFFFF";
    $BBS_ADMIN_MAXFSIZE = "5000000";
    $BBS_ADMIN_PointBoardView = "0";
    $BBS_ADMIN_PointBoard = "0";
    $BBS_ADMIN_PointComent = "0";
    $BBS_ADMIN_ComentStyle = "yes";
    $BBS_ADMIN_cate = "no";
    $BBS_ADMIN_advance = "no";
    $BBS_ADMIN_NoticeStyle = "no";
    $BBS_ADMIN_NoticeStyleSu = "0";
    $BBS_ADMIN_BBS_Level = "0";
    $BBS_ADMIN_header = "";
    $BBS_ADMIN_footer = "";
    $BBS_ADMIN_header_include = "";
    $BBS_ADMIN_footer_include = "";
    $BBS_ADMIN_file_select = "yes";
    $BBS_ADMIN_link_select = "yes";
    $BBS_ADMIN_no = "1";
    $BBS_ADMIN_id = $table;
    $BBS_ADMIN_pass = "";
}

mysqli_close($db); 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


$BBS_ADMIN_title_SUBJECT = htmlspecialchars($BBS_ADMIN_title);

$BBS_ADMIN_title_SUBJECT_ok="▒ $BBS_ADMIN_title_SUBJECT"; 
?>