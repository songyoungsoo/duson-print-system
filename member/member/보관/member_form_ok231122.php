<?php

include"../db.php";

//�����κа� ���������� url���� ������ ���´�........
function ERROR($msg)
{
echo "<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
";
exit;
}

// if ( !$name || !$id ) {
	if (!$id ) {
			$msg = "�������� ���ٹ���� �ƴմϴ�."; ERROR($msg); 
}


//db�� �ִ� ���̵� �ߺ� üũ�Ѵ�...
$query = "select * from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows){
echo("
<script language=javascript>
window.alert('\\n$id �� �̵̹�ϵǾ��ִ�\\���̵��̹Ƿ� ��û�ϽǼ� �����ϴ�.\\n');
history.go(-1);
</script>");
exit;

}


###################################################################
	$result = mysql_query("SELECT max(no) FROM member");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
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


// ���� üũ�� ����� db�������� ��Ų��...
$query ="select id from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows)
    {

		echo ("
		<script language=javascript>
		alert('\\n$id �� �̹� ��� �Ǿ��� �ִ� ID �Դϴ�. \\n\\nID �ߺ�Ȯ���� ���û�� �ֽñ� �ٶ��ϴ�.\\n')
		history.go(-1)
		</script>
		");
		exit;
	}
    else
    {

//ȸ�� ���� �Է�
 $zip = "$sample6_postcode"; 
 $zip1 = "$sample6_address";
 $zip2 = "$sample6_detailAddress"; 
 $address3 = "$sample6_extraAddress";
			
		
$date=date("Y-m-d H:i;s");
$dbinsert ="insert into member values('$new_no',
'$id',
'$pass1',
'$name', 
'$phone1',
'$phone2',
'$phone3',
'$hendphone1',
'$hendphone2',
'$hendphone3',
'$email',  
'$sample6_postcode',
'$sample6_address',
'$sample6_detailAddress',
'$sample6_extraAddress',
'$po1', 
'$po2',
'$po3', 
'$po4', 
'$po5', 
'$po6',  
'$po7',
'$connent',
'$date',
'5',
'0',
''
)";
$result_insert= mysql_query($dbinsert,$db);

// ȸ�������ϸ� �׳��� ���� ������Ű��////////////////////////////////////////
/* $regdate_banner = substr($date, 0,10); 

$dir = "./upload/$regdate_banner"; 
$dir_handle = is_dir("$dir"); 
if(!$dir_handle){mkdir("$dir", 0755); exec("chmod 777 $dir");}

$dir_id = "./upload/$regdate_banner/$id"; 
$dir_handle_id = is_dir("$dir_id"); 
if(!$dir_handle_id){mkdir("$dir_id", 0755); exec("chmod 777 $dir_id");} */
//////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////
// ȸ������ ���� �λ������ ������...
include"../admin/member/JoinAdminInfo.php";
$TO_NAME="$name"; 
$TO_EMAIL="$email"; 
$FROM_NAME="$AdminName"; 
$FROM_EMAIL="$AdminMail"; 
$SUBJECT="$MailTitle"; 

if($MailStyle=="html"){
$connent_text=$MailCont;
}else{

        $CONTENT=$MailCont;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;

}

$SEND_CONTENT = " 
<html>
<body bgcolor='#FFFFFF'>
$connent_text
</body>
</html> 
"; 

if($FROM_NAME && $FROM_EMAIL) {
	$from = "\"$FROM_NAME\" <$FROM_EMAIL>";
}
else {
	$from = "$FROM_EMAIL";
}
$TO = "\"$TO_NAME\" <$TO_EMAIL>";
$from = "From:$from\nContent-Type:text/html";
mail($TO, $SUBJECT , $SEND_CONTENT , $from);


//ȸ�����ԿϷ� �޼����� ������ �������� �̵� ��Ų��
echo ("
		<script language=javascript>
		alert('\\n$name �� ������ ��û���ֽ� ID - $id  ��й�ȣ - $pass1 �� ȸ�������� �Ϸ� �Ǿ����ϴ�.\\n\\n�����Ͻ� ������ �α��� �� �Ͻø� �������� ���񽺸� �̿��ϽǼ� �ֽ��ϴ�...\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=/'>
		");
		exit;

	}

?>