<?php
$dbinsert = "INSERT INTO MlangOrder_PrintAuto (
    no, Type, ImgFolder, Type_1_6, money_1, money_2, money_3, money_4, money_5, 
    name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, 
    regdate, PageSSOk, pass, Gensu
) VALUES (
    '$new_no', '$Type', '$ImgFolder', '$Type_1 $Type_2 $Type_3 $Type_4 $Type_5 $Type_6', 
    '$money_1', '$money_2', '$money_3', '$money_4', '$money_5', '$name', '$email', 
    '$zip', '$zip1', '$zip2', '$phone', '$Hendphone', '$delivery', '$bizname', '$bank', 
    '$bankname', '$cont', '$date', '$PageSSOk', '$pass', '$Gensu'
)";

echo $dbinsert; // 디버깅을 위해 SQL 쿼리를 출력합니다.

$result = mysqli_query($conn, $dbinsert);

if ($result) {
    echo "데이터 삽입 성공";
} else {
    echo "데이터 삽입 실패: " . mysqli_error($conn);
}
?>