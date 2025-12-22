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

include"../config.php";

$result_ID_inspection= mysqli_query($db, "select * from Mlnag_Results_Admin where id='$id'");
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

$rows_ID_inspection=mysqli_num_rows($result_ID_inspection);
if($rows_ID_inspection){

while($row_ID_inspection= mysqli_fetch_array($result_ID_inspection)) 
{ 
echo ("<script language=javascript>
window.alert('입력하신 $id 와 일치하는 테이블이 존재합니다.\\n\\n변경후 입력해 주시기 바랍니다.');
history.go(-1);
</script>
");
exit;
}

}else{// Mlang_abc_table 에 $table 값과 동일한게 없음 으로 테이블 생성을 실행 시킨다...



$result= mysqli_query($db, "select * from Mlang_${id}_Results"); 
if($result){ // 테이블이db가 존재 함 

echo (" 
<script language=javascript> 
window.alert('EROOR(2): $table 의 ID로 된 테이블이 이미존재 합니다.\\n\\n다른것으로 입력후 실행하십시요...!!'); 
history.go(-1);
</script>"); 
exit; 

}else{ // 테이블이 존재치 않음으로 테이블 생성 

// 1차적으로 id 값을 받아서 Mlnag_Results_Admin 관리 정보를 저장 시킨다...///////////////////////////////////////
	$result = mysqli_query($db, "SELECT max(no) FROM Mlnag_Results_Admin");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysqli_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################
$date = date("Y-m-d");
$dbinsert ="insert into Mlnag_Results_Admin values('$new_no',
'$item',
'$title',
'$id',
'$celect',
'$date'
)";
$result_insert= mysqli_query($db, $dbinsert);

// 2차로 db 를 생성 시켜준다..///////////////////////////////////////////////////////////////////////////////////////
mysqli_query($db, "DROP TABLE IF EXISTS Mlang_${id}_Results"); 
mysqli_query($db, " CREATE TABLE Mlang_${id}_Results ( 
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
PRIMARY KEY (Mlang_bbs_no) 
) "); 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "../../results/upload/$id"; mkdir("$dir", 0755);  exec("chmod 777 $dir"); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 실적물시스템 프로그램을 생성 하였습니다..\\n\\n')
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=list'>
		");
		exit;

} 

}
mysqli_close($db); 
?>