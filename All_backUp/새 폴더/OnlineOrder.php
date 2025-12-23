<?
$HomeDir="../";
$PageCode="PrintAuto";
include"$HomeDir/db.php";
include"$DOCUMENT_ROOT/MlangPrintAuto/MlangPrintAutoTop.php";
?>

<?
if($mode=="SubmitOk"){
include"../db.php";

$Table_result = mysql_query("SELECT max(no) FROM MlangOrder_PrintAuto");
	if (!$Table_result) {
		echo "
			<script>
			window.alert(\"DB 접속 에러입니다!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($Table_result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	} 
	
// 자료를 업로드할 폴더를 생성 시켜준다.. /////////////////////////////////////////////////////////////////////////////////
$dir = "upload/$new_no"; 
$dir_handle = is_dir("$dir");
if(!$dir_handle){mkdir("$dir", 0755);  exec("chmod 777 $dir");}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
 $zip = "$sample6_postcode"; 
 $zip1 = "$sample6_address";
 $zip2 = "$sample6_detailAddress"; 
 $address3 = "$sample6_extraAddress";
 $name = $_POST[username];	//name에 중복되는것이 있어서username으로
	
// 디비에 관련 자료 저장
if($PageSS=="OrderOne"){$PageSSOk="2";}
if($PageSS=="OrderTwo"){$PageSSOk="1";}

$date=date("Y-m-d H:i:s");
$dbinsert ="insert into MlangOrder_PrintAuto values(
'$new_no',
'$Type', 
'$ImgFolder', 
'$Type_1
$Type_2
$Type_3
$Type_4
$Type_5
$Type_6',
'$money_1',
'$money_2',	
'$money_3',	
'$money_4',	
'$money_5',	
'$name',   
'$email',
'$zip', 
'$zip1',
'$zip2',
'$phone',   
'$Hendphone',
'$delivery', 
'$bizname',
'$bank',
'$bankname',
'$cont', 
'$date',
'$PageSSOk',
'',
'$pass',
'$Gensu',
''
)";

//echo $dbinsert; exit;
$result_insert= mysql_query($dbinsert,$db);
/////////넘겨주는 name에 문제가 있어서 username으로 교체/////
if($result_insert){ 
echo ("<html>
<meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle=$OrderSytle&no=$new_no&username=$_POST[username]&Type_1=$Type_1
$Type_2
$Type_3
$Type_4
$Type_5
$Type_6&money4=$money_4&money5=$money_5&phone=$phone&Hendphone=$Hendphone&zip1=$zip1&zip2=$zip2&email=$email&date=$date&cont=$cont&standard=$standard&page=$page&PageSS=$PageSS'>
</html>		");
		exit;
}else{
echo ("<html>
<meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle=$OrderSytle&no=$new_no&username=$_POST[username]&Type_1=$Type_1
$Type_2
$Type_3
$Type_4
$Type_5
$Type_6&money4=$money_4&money5=$money_5&phone=$phone&Hendphone=$Hendphone&zip1=$zip1&zip2=$zip2&email=$email&date=$date&cont=$cont&standard=$standard&page=$page&PageSS=$PageSS'>
</html>		");
		exit;
}

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../db.php";
?>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<!---------------- 전체를 감싼다. ------------------------>
<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr>
<td align=center> 
	<?include"OrderForm${SubmitMode}.php";?>	
</td>
       </tr>
     </table>

</body>
</html>

<?
include"$DOCUMENT_ROOT/MlangPrintAuto/MlangPrintAutoDown.php";
?>
