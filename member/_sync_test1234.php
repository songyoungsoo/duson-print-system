<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../db.php";

$stmt = mysqli_prepare($db, "SELECT * FROM users WHERE username = ?");
$username = 'test1234';
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die("test1234 not found in users table");
}

$check = mysqli_prepare($db, "SELECT no FROM member WHERE id = ?");
mysqli_stmt_bind_param($check, "s", $username);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) > 0) {
    mysqli_stmt_close($check);
    die("test1234 already exists in member table");
}
mysqli_stmt_close($check);

$phone_parts = explode('-', $user['phone'] ?? '');
$p1 = $phone_parts[0] ?? '';
$p2 = $phone_parts[1] ?? '';
$p3 = $phone_parts[2] ?? '';

$u_pass = $user['password'] ?? '';
$u_name = $user['name'] ?? '';
$u_email = $user['email'] ?? '';
$u_postcode = $user['postcode'] ?? '';
$u_address = $user['address'] ?? '';
$u_detail = $user['detail_address'] ?? '';
$u_extra = $user['extra_address'] ?? '';
$u_bn = $user['business_number'] ?? '';
$u_bname = $user['business_name'] ?? '';
$u_bowner = $user['business_owner'] ?? '';
$u_btype = $user['business_type'] ?? '';
$u_bitem = $user['business_item'] ?? '';
$u_baddr = $user['business_address'] ?? '';
$u_tax = $user['tax_invoice_email'] ?? '';
$u_created = $user['created_at'] ?? date('Y-m-d H:i:s');
$u_level = $user['level'] ?? '5';
$h1 = '';
$h2 = '';
$h3 = '';

$query = "INSERT INTO member (
    id, pass, name, phone1, phone2, phone3,
    hendphone1, hendphone2, hendphone3, email,
    sample6_postcode, sample6_address, sample6_detailAddress, sample6_extraAddress,
    po1, po2, po3, po4, po5, po6, po7,
    date, level, Logincount
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$ins = mysqli_prepare($db, $query);
if (!$ins) {
    die("Prepare failed: " . mysqli_error($db));
}

mysqli_stmt_bind_param($ins, "ssssssssssssssssssssss" . "s",
    $username, $u_pass, $u_name, $p1, $p2, $p3,
    $h1, $h2, $h3, $u_email,
    $u_postcode, $u_address, $u_detail, $u_extra,
    $u_bn, $u_bname, $u_bowner, $u_btype, $u_bitem, $u_baddr, $u_tax,
    $u_created, $u_level
);

if (mysqli_stmt_execute($ins)) {
    $new_no = mysqli_insert_id($db);
    echo "SUCCESS: test1234 inserted into member table (no=$new_no)";
} else {
    echo "ERROR: " . mysqli_stmt_error($ins);
}

mysqli_stmt_close($ins);
mysqli_close($db);
?>
