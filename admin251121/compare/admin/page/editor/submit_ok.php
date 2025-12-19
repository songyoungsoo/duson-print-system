<?
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="수정하기"){
include"../../../db.php";

if($style=="file"){
$tty="..";
include"../upload/data.php";
$query ="UPDATE $page_table SET title='$SUBJECT', connent='$FILELINK_ok', style='$style', cate='$cate' WHERE no='$no'";
}else if($style=="edit"){
$query ="UPDATE $page_table SET title='$SUBJECT', connent='$CONTENT', style='$style', cate='$cate' WHERE no='$no'";
}else{
$query ="UPDATE $page_table SET title='$SUBJECT', connent='$connent', style='$style', cate='$cate' WHERE no='$no'";
}
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB 접속 에러입니다!\")
				history.go(-1);
			</script>";
		exit;

} else {


if($style=="edit"){

	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 [수정]하였습니다.\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=../page_admin.php?mode=modify&no=$no'>
	");
		exit;

}else{
	
	echo ("
		<script language=javascript>
		alert('\\n정보를 정상적으로 [수정]하였습니다.\\n');
        opener.parent.location=\"../page_admin.php?mode=modify&no=$no\"; 
        window.self.close();
		</script>
	");
		exit;
}



}

mysql_close($db);

}

if($mode=="저장하기"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////

include"../../../db.php";

	$result = mysql_query("SELECT max(no) FROM $page_table");
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

if($style=="file"){
$tty="..";
include"../upload/data.php";

//정보 입력
$dbinsert ="insert into $page_table values('$new_no',
'$SUBJECT',
'$FILELINK_ok',
'$style',
'$cate'
)";
$result_insert= mysql_query($dbinsert,$db);

}else if($style=="edit"){

//정보 입력
$dbinsert ="insert into $page_table values('$new_no',
'$SUBJECT',
'$CONTENT',
'$style',
'$cate'
)";
$result_insert= mysql_query($dbinsert,$db);

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 페이지 정보를 저장 시켰습니다.\\n\\n');
		</script>
<meta http-equiv='Refresh' content='0; URL=../page_submit.php?mode=form'>
		");
		exit;

}else{

//정보 입력
$dbinsert ="insert into $page_table values('$new_no',
'$SUBJECT',
'$connent',
'$style',
'$cate'
)";
$result_insert= mysql_query($dbinsert,$db);

}

//완료 메세지를 보인후 페이지를 이동 시킨다
echo ("
		<script language=javascript>
		alert('\\n정상적으로 페이지 정보를 저장 시켰습니다.\\n\\n');
        opener.parent.location=\"../page_submit.php?mode=form\"; 
        window.self.close();
		</script>

		");
		exit;

}
?>


<html>
<head>

<? if($mode=="BR형식미리보기"){$TTU="BR자동입력";} ?>
<? if($mode=="HTML형식미리보기"){$TTU="HTML직접입력";} ?>
<? if($mode=="업로드형식미리보기"){$TTU="파일(업로드)로 입력";} ?>

<title><?echo("$TTU");?> - 미리보기페이지</title>
<meta http-equiv='Content-type' content='text/html; charset=euc-kr'>


<?
$M123="../../";
?>

<?

if($mode=="업로드형식미리보기"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	echo ("
		<script language=javascript>
		alert('\\n업로드 미리보기는 디렉토리 경로의 문제로 인해 미리보기를 지원하지않습니다.\\n');
        window.self.close();
		</script>
	");
		exit;

}
?>



<?

if($mode=="HTML형식미리보기"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

echo("$connent");

}
?>


<style>
body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
</style>

</head>

<body bgcolor='#FFFFFF' LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>



<?
if($mode=="BR형식미리보기"){ /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $CONTENT=$connent;
		$CONTENT = eregi_replace("<", "&lt;", $CONTENT);
		$CONTENT = eregi_replace(">", "&gt;", $CONTENT);
		$CONTENT = eregi_replace("\"", "&quot;", $CONTENT);
		$CONTENT = eregi_replace("\|", "&#124;", $CONTENT);
		$CONTENT = eregi_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = eregi_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;

echo("$connent_text");


}

?>




<p align=center><BR><BR><input type='button' value=' 창 닫기 ' onClick='javascript:window.close();'><BR><BR></p>


</body>

</html>