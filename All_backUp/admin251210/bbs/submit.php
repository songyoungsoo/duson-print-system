<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"../db.php";

if( $table=="A" || $table=="a" ){

echo ("<script language=javascript>
window.alert('테이블명: $table  으로는 테이블을 생성 시킬수 없습니다.\\n\\n다른 이름으로 변경후 실행하여 주십시요');
history.go(-1);
</script>
");
exit;

}

////////////////  mlang_bbs_admin 내에 같은 아이디가 존재 하는지 체크한다. ////////////////////////////////////////////
$result= mysqli_query($db, "select * from mlang_bbs_admin where id='$table'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
echo (" 
<script language=javascript> 
window.alert('EROOR(1): $table 의 ID로 된 테이블이 이미존재 합니다.\\n\\n다른것으로 입력후 실행하십시요...!!'); 
history.go(-1);
</script>"); 
exit; 
}

}else{ // Mlang_abc_table 에 $table 값과 동일한게 없음 으로 테이블 생성을 실행 시킨다...

$result= mysqli_query($db, "select * from Mlang_${table}_bbs"); 
if($result){ // 테이블이db가 존재 함 

echo (" 
<script language=javascript> 
window.alert('EROOR(2): $table 의 ID로 된 테이블이 이미존재 합니다.\\n\\n다른것으로 입력후 실행하십시요...!!'); 
history.go(-1);
</script>"); 
exit; 

}else{ // 테이블이 존재치 않음으로 테이블 생성 


// 1차적으로 id 값을 받아서 mlang_bbs_admin 게시판 관리 정보를 저장 시킨다...///////////////////////////////////////
$date=date("Y-m-d");
$dbinsert ="INSERT INTO mlang_bbs_admin VALUES ('',
'$title', 
'$table', 
'$pass',
'$skin',
'', 
'',   
'', 
'', 
'yes',   
'yes',   
'15',   
'8',   
'100',   
'3',   
'yes',   
'yes',   
'yes', 
'yes', 	
'yes', 
'member',
'member', 
'96%',
'237CBE',
'FFFFFF',
'2000',
'0 ',  
'0 ',  
'0 ',
'yes', 
'$date',
'',
'no',
'no',
'5',
'5'
)";
$result_insert= mysqli_query($db, $dbinsert);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 2차로 게시판 db 를 생성 시켜준다..///////////////////////////////////////////////////////////////////////////////////////
mysqli_query($db, "DROP TABLE IF EXISTS Mlang_${table}_bbs"); 
mysqli_query($db, " CREATE TABLE Mlang_${table}_bbs ( 
Mlang_bbs_no mediumint(12) unsigned NOT NULL auto_increment, 
Mlang_bbs_member varchar(100) NOT NULL default '',                
Mlang_bbs_title text,                                                                 
Mlang_bbs_style varchar(100) NOT NULL default 'br',                
Mlang_bbs_connent text,                                                             
Mlang_bbs_link text,                                                                   
Mlang_bbs_file text,                                                                  
Mlang_bbs_pass varchar(100) NOT NULL default '',                  
Mlang_bbs_count int(12) NOT NULL default '0',                              
Mlang_bbs_rec int(12) NOT NULL default '0',                                                  
Mlang_bbs_secret varchar(100) NOT NULL default 'yes',                  
Mlang_bbs_reply int(12) NOT NULL default '0',                                           
Mlang_date datetime NOT NULL default '0000-00-00 00:00:00',	
CATEGORY text,
NoticeSelect varchar(100) NOT NULL default 'no',      
PRIMARY KEY (Mlang_bbs_no) 
) "); 
// Coment  테이블을 생성시킨다..////////////////////////////////////////////////////////////////////////////////////////////////
mysqli_query($db, "DROP TABLE IF EXISTS Mlang_${table}_bbs_coment"); 
mysqli_query($db, " CREATE TABLE Mlang_${table}_bbs_coment ( 
Mlang_coment_no mediumint(12) unsigned NOT NULL auto_increment, 
Mlang_coment_BBS_no varchar(100) NOT NULL default '',  
Mlang_coment_member_id varchar(100) NOT NULL default '',   
Mlang_coment_title text,
Mlang_date datetime NOT NULL default '0000-00-00 00:00:00',	
PRIMARY KEY (Mlang_coment_no) 
) "); 


// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../bbs/upload/$table"; mkdir("$dir", 0755);  exec("chmod 777 $dir"); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



echo ("
<html>
<script language=javascript>
window.alert('테이블명: $table 으로\\n\\n테이블 제목: $title 을 정상적으로 생성완료 하였습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>
</html>
");
exit;

} 

}
mysqli_close($db); 

?>