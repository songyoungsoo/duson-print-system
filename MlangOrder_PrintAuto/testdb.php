<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// 데이터베이스 연결 설정
$servername = "localhost"; // 데이터베이스 서버 이름
$username = "duson1830"; // 데이터베이스 사용자 이름
$password = "du1830"; // 데이터베이스 비밀번호
$dbname = "duson1830"; // 데이터베이스 이름

$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables from GET parameters
$OrderStyle = isset($_GET['OrderStyle']) ? $_GET['OrderStyle'] : '';
$no = isset($_GET['no']) ? $_GET['no'] : '';
$username = isset($_GET['username']) ? $_GET['username'] : '';
$Type_1 = isset($_GET['Type_1']) ? $_GET['Type_1'] : '';
$money4 = isset($_GET['money4']) ? $_GET['money4'] : '';
$money5 = isset($_GET['money5']) ? $_GET['money5'] : '';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$Hendphone = isset($_GET['Hendphone']) ? $_GET['Hendphone'] : '';
$zip1 = isset($_GET['zip1']) ? $_GET['zip1'] : '';
$zip2 = isset($_GET['zip2']) ? $_GET['zip2'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$cont = isset($_GET['cont']) ? $_GET['cont'] : '';
$standard = isset($_GET['standard']) ? $_GET['standard'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : '';
$PageSS = isset($_GET['PageSS']) ? $_GET['PageSS'] : '';

$new_no = '123456'; // 임시값으로 새 주문 번호를 설정합니다.
$PageSSOk = 'YES'; // 임시값으로 PageSSOk를 설정합니다;

// Prepare the SQL statement with placeholders
$stmt = $conn->prepare("INSERT INTO MlangOrder_PrintAuto (
    no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, 
    name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, 
    regdate, PageSSOk, pass, Gensu
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// 오류가 발생하는지 확인
if (!$stmt) {
    die("Statement preparation failed: " . $conn->error);
}

// Bind the parameters to the SQL query
$stmt->bind_param('sssssssssssssssssssssssss', 
    $new_no, $Type, $ImgFolder, $Type_1, $money_1, $money_2, $money_3, $money_4, $money_5, 
    $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $delivery, $bizname, $bank, $bankname, 
    $cont, $date, $PageSSOk, $pass, $Gensu);

// Execute the prepared statement
if ($stmt->execute()) {
    echo "데이터 삽입 성공";
} else {
    echo "데이터 삽입 실패: " . $stmt->error;
}

// Close the statement and the connection
$stmt->close();
$conn->close();
?>
