<?php
include "lib.php";

$id = $_POST['id'];
$pass = $_POST['pass'];

$id = mysql_real_escape_string($id);
$pass = mysql_real_escape_string($pass);

$query = "SELECT * FROM member WHERE id='$id' AND pass='$pass'";
$result = mysql_query($query, $connect);

if (!$result) {
    die("쿼리 실행에 실패했습니다: " . mysql_error());
}

$data = mysql_fetch_array($result);

if ($data) {
    $_SESSION['isLogin'] = array(
        'id' => $data['id'],
        // 'pass' => $data['pass']
        'email' => $data['email']
    );
    ?>
    <script>
        location.href='index.php';
    </script>
    <?php
} else {
    echo "로그인 정보가 올바르지 않습니다.";
}
?>
