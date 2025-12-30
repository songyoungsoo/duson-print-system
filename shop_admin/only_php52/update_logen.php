<?
header('Content-Type: application/json; charset=euc-kr');

include "lib_mysql.php";
$connect = dbconn();

$no = isset($_POST['no']) ? intval($_POST['no']) : 0;
$field = isset($_POST['field']) ? $_POST['field'] : '';
$value = isset($_POST['value']) ? $_POST['value'] : '';

if($no <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid no'));
    exit;
}

// 허용된 필드만 수정 가능
$allowed_fields = array('logen_box_qty', 'logen_delivery_fee', 'logen_fee_type');
if(!in_array($field, $allowed_fields)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid field'));
    exit;
}

$value = mysql_real_escape_string($value, $connect);
$query = "UPDATE MlangOrder_PrintAuto SET $field = '$value' WHERE no = $no";
$result = mysql_query($query, $connect);

if($result) {
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false, 'message' => mysql_error($connect)));
}
?>
