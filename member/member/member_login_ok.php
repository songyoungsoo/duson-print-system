<?php
if($mode=="member_login"){  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include"../db.php";
$result_id= mysql_query("select * from member where id='$id'",$db);
$rows_id=mysql_num_rows($result_id);
if($rows_id){

while($rows_id= mysql_fetch_array($result_id)) 
{ 
//-----------------------------------------------------//
$result= mysql_query("select * from member where id='$id' and pass='$pass'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($rows= mysql_fetch_array($result)) 
{ 
// 접속기록, 접속카운터를 업테이트 시킨다..
$Countresult= mysql_query("select * from member where id='$id'",$db);
$Countrow= mysql_fetch_array($Countresult);
$LogonCountOk=$Countrow[Logincount]+1;

$Logindate=date("Y-m-d H:i;s");
$query ="UPDATE member SET Logincount='$LogonCountOk', EndLogin='$Logindate' WHERE id='$id'";
mysql_query($query,$db);

// $id_login_ok = $rows['id'];
// $_SESSION['id_login_ok'] = $id_login_ok;

// $query = "SELECT * FROM member WHERE id='$id_login_ok'";
// $result = mysql_query($query, $connect);
// $data = mysql_fetch_array($result);

$id = $_POST['id'];
$pass = $_POST['pass'];

$id = mysql_real_escape_string($id);
$pass = mysql_real_escape_string($pass);
include"../session/lib.php";
$query = "SELECT * FROM member WHERE id='$id' AND pass='$pass'";
$result = mysql_query($query, $connect);

if (!$result) {
    die("쿼리 실행에 실패했습니다: " . mysql_error());
}

$data = mysql_fetch_array($result);
// print_r($data);
// exit;
if ($data) {
    $_SESSION['id_login_ok'] = array(
        'id' => $data['id'],
        // 'pass' => $data['pass']
        'email' => $data['email']
    );

setcookie("id_login_ok", stripslashes($data['id']), 0, "/");

    ?>
    <script>
        location.href='../shop/view.php';
    </script>
    <?php
} else {
    echo "로그인 정보가 올바르지 않습니다.";
}

 
// $_SESSION['id_login_ok'] = array(
//   'id' => $data['id'],
//   // 'pass' => $data['pass']
//   'email' => $data['email']
// );
// $id_login_ok=$row[id];
// $_SESSION['id_login_ok'] = $id_login_ok; 
// @ session_register(id_login_ok); 
// @ setcookie("id_login_ok", stripslashes($row[id]), 0, "/" );

// }
echo ("<html>
<script language=javascript>
window.alert('정상적으로 ♡ $admin_name ♡ 에 로그인 되셨습니다..\\n\\n좋은 하루 되시기를  바랍니다.....*^^*');
window.self.close();
</script>");

if($selfurl){


$selfurl_ok = eregi_replace("@", "&", $selfurl);

echo("<meta http-equiv='Refresh' content='0; URL=$selfurl_ok'>");
}else{
echo("<meta http-equiv='Refresh' content='0; URL=/'>");
}

echo("</html>");
}

}else{
echo ("<html>
<script language=javascript>
window.alert('입력하신 $id (과)와 비밀번호 $pass 가 불일치 합니다..\\n\\n다시 한번 확인해 주시기 바랍니다.....*^^*');
history.go(-1);
</script>
</html>");
exit;
}


//-----------------------------------------------------//
}


}else{


echo ("<html>
<script language=javascript>
window.alert('입력하신 $id  로는 회원 가입이 되어있지 않습니다.\\n\\n다시 한번 확인해 주시기 바랍니다.....*^^*');
history.go(-1);
</script>
</html>");
exit;


}

mysql_close($db); 


}

if($mode=="member_logout") { //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

@ setcookie("bbs_login","",0,"/");
@ setcookie("id_login_ok","",0, "/");
@ session_destroy();

echo ("<html>
<script language=javascript>
window.alert('정상적으로 로그아웃 처리 되었습니다........*^^*');
</script>
<meta http-equiv='Refresh' content='0; URL=/'>
</html>");
exit;


}


if(!$mode){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ("<html><script language=javascript>
window.alert('비정상적인 접속은 허용하지 않아요....잉!!');
</script>
<meta http-equiv='Refresh' content='0; URL=/'>
</html>");
exit;

}
?>
