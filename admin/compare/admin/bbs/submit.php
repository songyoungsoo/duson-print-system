<?
include"../db.php";

if( $table=="A" || $table=="a" ){

echo ("<script language=javascript>
window.alert('테이블명: $table  으로는 테이블을 생성 시킬수 없습니다.\\n\\n다른 이름으로 변경후 실행하여 주십시요');
history.go(-1);
</script>
");
exit;

}

////////////////  Mlnag_BBS_Admin 내에 같은 아이디가 존재 하는지 체크한다. ////////////////////////////////////////////
$result= mysql_query("select * from Mlnag_BBS_Admin where id='$table'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{ 
echo (" 
<script language=javascript> 
window.alert('EROOR(1): $table 의 ID로 된 테이블이 이미존재 합니다.\\n\\n다른것으로 입력후 실행하십시요...!!'); 
history.go(-1);
</script>"); 
exit; 
}

}else{ // Mlang_abc_table 에 $table 값과 동일한게 없음 으로 테이블 생성을 실행 시킨다...

$result= mysql_query("select * from Mlang_${table}_bbs",$db); 
if($result){ // 테이블이db가 존재 함 

echo (" 
<script language=javascript> 
window.alert('EROOR(2): $table 의 ID로 된 테이블이 이미존재 합니다.\\n\\n다른것으로 입력후 실행하십시요...!!'); 
history.go(-1);
</script>"); 
exit; 

}else{ // 테이블이 존재치 않음으로 테이블 생성 


// 1차적으로 id 값을 받아서 Mlnag_BBS_Admin 게시판 관리 정보를 저장 시킨다...///////////////////////////////////////
$date=date("Y-m-d");
$dbinsert ="INSERT INTO Mlnag_BBS_Admin VALUES ('',
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
$result_insert= mysql_query($dbinsert,$db);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 2차로 게시판 db 를 생성 시켜준다..///////////////////////////////////////////////////////////////////////////////////////
mysql_query("DROP TABLE IF EXISTS Mlang_${table}_bbs"); 
mysql_query(" CREATE TABLE Mlang_${table}_bbs ( 
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
mysql_query("DROP TABLE IF EXISTS Mlang_${table}_bbs_coment"); 
mysql_query(" CREATE TABLE Mlang_${table}_bbs_coment ( 
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
mysql_close($db); 

?>