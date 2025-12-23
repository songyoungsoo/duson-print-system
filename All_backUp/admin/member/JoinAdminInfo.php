<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

$AdminName = "두손기획인쇄";
$AdminMail = "dsp1830@naver.com";
$MailTitle = "두손기획인쇄를 방문하여 주셔서 감사드립니다.";
$MailStyle = "br";
$MailCont = "회원님께서는 정상적으로 회원가입이 완료되었습니다.";
?>