<?php
function dbconn(){
    $host = "localhost"; // 호스트 이름
    $username = "root"; // 사용자 이름
    $password = ""; // 암호
    $database = "dsp1830"; // 데이터베이스 이름
    $connect = mysqli_connect($host, $username, $password, $database);
    if(mysqli_connect_errno()){
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }
    mysqli_set_charset($connect, "utf8");
    return $connect;
}

function passwd($a){
    global $connect;
    $query = "SELECT PASSWORD('$a')";
    $result = mysqli_query($connect, $query);
    $temp = mysqli_fetch_array($result);
    return $temp[0];
}

function member(){
    global $connect, $sing_member;
    $temp = explode("//", $sing_member);
    $user_id = $temp[0];
    $pw = $temp[1];
    
    // users 테이블 사용 (prepared statement)
    $stmt = mysqli_prepare($connect, "SELECT * FROM users WHERE username = ?");
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);
    
    if ($data && passwd($data['password'] ?? $data['pw'] ?? '') == $pw) return $data;
}
?>

<style>
    td, input, li, a {
        font-size: 9pt;
    }
    border {
        border-color: #CCC;
    }
</style>
