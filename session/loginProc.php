<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "db.php";

$id = $_POST['id'];
$pass = $_POST['pass'];

$id = mysqli_real_escape_string($id);
$pass = mysqli_real_escape_string($pass);

$query = "SELECT * FROM member WHERE id='$id' AND pass='$pass'";
$result = mysqli_query($query, $db);

if (!$result) {
    die("쿼리 실행에 실패했습니다: " . mysqli_error());
}

$data = mysqli_fetch_array($result);
// print_r($data);
// exit;
if ($data) {
    $_SESSION['id_login_ok'] = array(
        'id' => $data['id'],
        // 'pass' => $data['pass']
        'email' => $data['email']
    );
    ?>
    <script>
        location.href='index_01.php';
    </script>
    <?php
} else {
    echo "로그인 정보가 올바르지 않습니다.";
}
?>