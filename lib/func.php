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
    
    $query = "SELECT * FROM member WHERE user_id='$user_id'";
    $result = mysqli_query($connect, $query);
    $data = mysqli_fetch_array($result);
    
    if(passwd($data['pw']) == $pw) return $data;
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
