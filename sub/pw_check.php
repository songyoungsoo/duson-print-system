<?php 
// MySQL 데이터베이스 연결 정보를 설정합니다.
$servername = "localhost";
$username = "duson1830";
$password = "du1830";
$dbname = "duson1830";

// 입력한 비밀번호를 가져옵니다.
$password_input = $_POST["password"];

// MySQL 데이터베이스와 연결합니다.
$db=mysql_connect($servername,$username,$password);
$query=mysql_select_db("$dbname",$db);

//$conn = mysql($servername, $username, $password, $dbname);
//	$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 오류가 있는지 확인합니다.
//$result= mysql_query($query,$db);
	$sql = "SELECT * FROM member WHERE pass='$password_input'";
	$result= mysql_query($sql,$db);
//		$row = mysql_fetch_arry($result);
	if($result){
//		$row = mysql_fetch_array($result);
//	echo "$row[0]";	
//    $member_no = $row["no"];

	 header("Location: ../MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=77328");
    
    // 회원 번호를 사용하여 페이지를 표시합니다.
	
    // 이 부분에서는 페이지를 표시하는 코드를 작성합니다.
} else {
    // 회원 정보가 없으면 제자리로 돌아갑니다.
    header("Location: ./checkboard.htm");
    exit();
}
//	if(!$result) {
//		echo "
//			<script language=javascript>
//				window.alert(\"DB 접속 에러입니다!\")
//				history.go(-1);
//			</script>";
//		exit;
//
//} else {
//	
//	echo ("
//		<script language=javascript>
//		alert('\\n정보를 정상적으로 수정하였습니다.\\n');
//		opener.parent.location.reload();
//		</script>
//<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=OrderView&no=$no'>
//	");
//		exit;
//
//}

mysql_close($db);
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}

// 입력한 비밀번호와 일치하는 회원 정보를 가져옵니다.
//$sql = "SELECT * FROM member WHERE pass='$password_input'";
//$result= mysql_query($sql,$db);

// 회원 정보가 있는지 확인합니다.
//if ($result->num_rows > 0) {
//	$row = mysql_fetch_row($result);
//	if($row[0]) {
//	   $new_no = $row[0] + 1;
//	} else {
//	   $new_no = 1;
//	}   
	
//	if ($result) {
    // 회원 정보가 있으면 회원 번호를 가져옵니다.
//    $row = mysql_fetch_row($result);
////		$row = mysql_fetch_arry($result);
//    $member_no = $row["no"];
//	 header("Location:../MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=$member_no");
//    
//    // 회원 번호를 사용하여 페이지를 표시합니다.
//	
//    // 이 부분에서는 페이지를 표시하는 코드를 작성합니다.
//} else {
//    // 회원 정보가 없으면 제자리로 돌아갑니다.
//    header("Location: checkboard.htm");
//    exit();
//}

// MySQL 데이터베이스 연결을 닫습니다.
//$conn->close();
?>
