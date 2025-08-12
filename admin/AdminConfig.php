<?php
include "../db.php";
include "./config.php";
include "./title.php";

// if($mode=="modify"){
	
// GET 파라미터에서 mode 가져오기
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

if($mode == "modify") {
?>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=380,availHeight=200);
</script>

<script src="./js/coolbar.js" type="text/javascript"></script>
<script src="./js/login.js" type="text/javascript"></script>

<body class='coolBar'>

<p align=center><font style='color:#000000; line-height:150%; font-size:10pt;'>
변경할 관리자의 <u>아이디와 비밀번호를 입력</u>하여 주십시요...
</font></p>

   <table border=0 align=center cellpadding=5 cellspacing=0>
   <form name='AdminKingInfo' method='post' OnSubmit='javascript:return AdminKingCheckField()' action='<?php echo $_SERVER["PHP_SELF"]?>'>
   <INPUT TYPE="hidden" name='mode' value='AdminOk'>
      <tr>
         <td align=center>아이디</td>
		 <td align=center><input type=text name=id maxlength=12 value="<?php echo isset($adminid) ? $adminid : '' ?>" readonly style='background-color:#429EB2; color:#FFFFFF;'></td>
	  </tr>
      <tr>
         <td align=center>비밀번호</td>
		 <td align=center><input type=password name=pass maxlength=20 value="<?php echo isset($adminpasswd) ? $adminpasswd : '' ?>"></td>
	  </tr>
      <tr>
         <td align=center colspan=2>&nbsp;&nbsp;&nbsp;<input type=submit value=' 변경하기 '></td>
	  </tr>
	</form>
	</table>



</body>
</html>

<?php
}

// if($mode=="AdminOk"){
	if($mode == "AdminOk") {

 // 사용자 입력 데이터에서 필요한 값 가져오기
 $id = $_POST['id']; // 예시로 POST로 받아오는 것으로 가정합니다.
 $pass = $_POST['pass']; // 예시로 POST로 받아오는 것으로 가정합니다.

 // 쿼리 실행
 $query = "UPDATE member SET id='$id', pass='$pass' WHERE no='1'";
 $result = mysqli_query($db, $query);

 // 쿼리 결과 확인 및 처리
 if (!$result) {
	 echo "<script language='javascript'>
		 window.alert(\"정보 수정 중 오류가 발생했습니다!\");
		 history.go(-1);
	 </script>";
	 exit;
 } else {
	 echo "<script language='javascript'>
		 alert('\\n정보를 정상적으로 수정하였습니다.\\n\\n\\n정보를 변경하였음으로 재 로그인 하셔야 합니다.');
		 window.location.href = '" . $_SERVER["PHP_SELF"] . "?mode=modify';
	 </script>";
	 exit;
 }

 // 데이터베이스 연결 종료
 mysqli_close($db);
}

?>