<?
////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
  HEADER("WWW-authenticate: basic realm=\"관리자 인증!\" ");
  HEADER("HTTP/1.0 401 Unauthorized");
  echo("<html><head><script>
       <!--
        function pop()
        { alert('관리자 인증 실패');
             history.go(-1);}
       //--->
        </script>
        </head>
        <body onLoad='pop()'></body>
        </html>
       ");
exit;
}

if(!$PHP_AUTH_USER || !$PHP_AUTH_PW)
{
 authenticate();
}

else
{

include"../../db.php";
$result= mysql_query("select * from $admin_table where no='1'",$db);
$row= mysql_fetch_array($result);

$adminid="$row[id]";
$adminpasswd="$row[pass]";


 if(strcmp($PHP_AUTH_USER,$adminid) || strcmp($PHP_AUTH_PW,$adminpasswd) )
 { authenticate(); }


}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="submit"){

include"../title.php";
$action="admin.php?mode=submitok";
include"form.php";

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="submitok"){

	$result = mysql_query("SELECT max(no) FROM WomanMember");
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

//^^^^^^^^^^^^^^^^^ 업로드  ^^^^^^^^^^^^^^^^^^^^^^^//
$dir = "../../women/upload/$new_no"; 
$dir_handle = is_dir("$dir"); 
if(!$dir_handle){mkdir("$dir", 0755); exec("chmod 777 $dir");}
$upload_dir="$dir";
if($PhotoFileSo){include"UploadSo.php";}
if($PhotoFileBig){include"UploadBig.php";}
//^^^^^^^^^^^^^^^^^ 업로드  ^^^^^^^^^^^^^^^^^^^^^^^//
$date=date("Y-m-d H:i;s");
$dateMember=date("His");
$dbinsert ="insert into WomanMember values('$new_no',
'$dateMember$new_no',   
'$PhotoFileSoName',
'$PhotoFileBigName',
'$name',   
'$nala',   
'$Byear', 
'$Bmonth',  
'$Bday',   
'$zip1', 
'$zip2',
'$school',  
'$wed',   
'$children',
'$job',
'$body',
'$acharacter',
'$cm',
'$kg', 
'$blood',
'$religion',
'$taste',   
'$special',   
'$family',
'$iii_1',   
'$iii_2',
'$iii_3',   
'$cont',
'$I_age1',
'$I_age2',    
'$I_school',  
'$I_cont',
'$sort',
'0',  
'no',  
'$date'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n자료를 정상적으로 저장 하였습니다.\\n\\n자료를 새로 등록하시려면 창을 다시 여세요\\n');
        opener.parent.location=\"index.php\"; 
        window.self.close();
		</script>
	");
		exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modify"){

include"../title.php";

$op="pop";
$db_dir="../..";
include"../../women/WomenViewFild.php";

include"../title.php";
$action="admin.php?mode=modifyok";
include"form.php";

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="delete"){

$result = mysql_query("DELETE FROM WomanMember WHERE no='$no'");
mysql_close();

echo ("
<html>
<script language=javascript>
window.alert('정상적으로 $no번 여성 회원을 삭제 처리 하였습니다.');
opener.parent.location.reload();
window.self.close();
</script>
</html>
");
exit;


} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="modifyok"){

include"../../db.php";

//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
if($PhotoFileSoModify){
$upload_dir="../../women/upload/$no";
include"UploadSo.php";
$YYSoPjFile="$PhotoFileSoName";
if($TTSoFileName){unlink("$upload_dir/$TTSoFileName");}
}else{
$YYSoPjFile="$TTSoFileName";
}

if($PhotoFileBigModify){
$upload_dir="../../women/upload/$no";
include"UploadBig.php";
$YYBigPjFile="$PhotoFileBigName";
if($TTBigFileName){unlink("$upload_dir/$TTBigFileName");}
}else{
$YYBigPjFile="$TTBigFileName";
}
//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//

$query ="UPDATE WomanMember SET 
PhotoFileSo='$YYSoPjFile',  
PhotoFileBig='$YYBigPjFile',  
name='$name',  
nala='$nala',  
Byear='$Byear',  
Bmonth='$Bmonth',  
Bday='$Bday',  
zip1='$zip1',  
zip2='$zip2',  
school='$school',  
wed='$wed',  
children='$children',  
job='$job',  
body='$body',  
acharacter='$acharacter',  
cm='$cm',  
kg='$kg', 
blood='$blood',  
religion='$religion',  
taste='$taste',  
special='$special',  
family='$family',  
iii_1='$iii_1',  
iii_2='$iii_2',  
iii_3='$iii_3',  
cont='$cont',  
I_age1='$I_age1',  
I_age2='$I_age2',  
I_school='$I_school',  
I_cont='$I_cont',  
sort='$sort'
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
        window.self.close();
		</script>
	");
		exit;

}
mysql_close($db);

} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>