<?php
if(!strcmp($mode,"modify")) {

$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";

$login_dir="..";
$db_dir="..";

include"$db_dir/db.php";
include"../top.php";

$result= mysql_query("select * from member where id='$WebtingMemberLogin_id'",$db);
$row= mysql_fetch_array($result);
$no="$row[no]";
mysql_close($db); 

include"./member_fild.php";
$action="$PHP_SELF?mode=modify_ok";
$MdoifyMode="view";
include"./form.php";

include"../down.php";

}elseif(!strcmp($mode,"modify_ok")) {

include"../db.php";

if($PhoFileChick){

//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
if($photofile){
$upload_dir="./$PhotoFileDir";
include"./upload.php";
if($PhotoFileDirName){unlink("$upload_dir/$PhotoFileDirName");}
}else{
echo ("<script language=javascript>
window.alert('������ �ڷḦ �����Ѵٰ� üũ�ϼ̽��ϴ�.\\n\\n�׷��� ������ �����ڷᰡ ���� �ֳ׿� *^^*');
history.go(-1);
</script>
");
exit;
}
//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//

$query ="UPDATE member SET 
pass='$pass1',  
phone1='$phone1',  
phone2='$phone2',  
phone3 ='$phone3', 
hendphone1='$hendphone1',  
hendphone2='$hendphone2',  
hendphone3='$hendphone3',  
email='$email',  
sample6_postcode='$sample6_postcode',
sample6_address='$sample6_address',
sample6_detailAddress='$sample6_detailAddress', 
sample6_extraAddress='$sample6_extraAddress',
po1='$po1',  
po2='$po2',  
po3='$po3',  
po4='$po4',  
po5='$po5',  
po6='$po6',  
po7='$po7',  
connent='$connent'  
WHERE no='$no'";

}else{ 

$query ="UPDATE member SET 
pass='$pass1',  
phone1='$phone1',  
phone2='$phone2',  
phone3 ='$phone3', 
hendphone1='$hendphone1',  
hendphone2='$hendphone2',  
hendphone3='$hendphone3',  
email='$email',  
sample6_postcode='$sample6_postcode',
sample6_address='$sample6_address',
sample6_detailAddress='$sample6_detailAddress', 
sample6_extraAddress='$sample6_extraAddress',
po1='$po1',  
po2='$po2',  
po3='$po3',  
po4='$po4',  
po5='$po5',  
po6='$po6',  
po7='$po7',  
connent='$connent'  
WHERE no='$no'";

}

$result= mysql_query($query,$db);


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=modify'>	
			");
		exit;

}
mysql_close($db);

}
?>
