<?php
function dbconn() {
    $host = "localhost";
    $user = "duson1830";
    $password = "du1830";
    $dataname = "duson1830";

    $conn = new mysqli($host, $user, $password, $dataname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
    return $conn;
}

function passwd($a, $conn) {
    $query = "SELECT PASSWORD(?) AS password";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $a);
    $stmt->execute();
    $result = $stmt->get_result();
    $temp = $result->fetch_assoc();
    return $temp['password'];
}

function member($conn, $sing_member) {
    $temp = explode("//", $sing_member);
    $user_id = $temp[0];
    $pw = $temp[1];

    $query = "SELECT * FROM member WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (passwd($data['pw'], $conn) == $pw) {
        return $data;
    } else {
        return null;
    }
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
