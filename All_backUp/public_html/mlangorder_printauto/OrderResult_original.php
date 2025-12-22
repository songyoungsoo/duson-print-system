<?php
ini_set('display_errors', '0');
session_start();
?>

<?php
$HomeDir = "..";
$PageCode = "PrintAuto";
include "$HomeDir/db.php";
include "$_SERVER[DOCUMENT_ROOT]/mlangprintauto/mlangprintautotop.php";

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

// Include additional files
include "../admin/mlangprintauto/int/info.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>주문 내역</title>
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    .container {
        width: 80%;
        margin: 0 auto;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .table th, .table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .table th {
        background-color: #f4f4f4;
        text-align: center;
    }
    .table td {
        text-align: center;
    }
    .btn-submit {
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
    }
    .btn-submit img {
        vertical-align: middle;
    }
    .info-box {
        border: 1px solid #ddd;
        padding: 10px;
        background-color: #f9f9f9;
        margin-top: 20px;
    }
    .info-box b {
        color: #007bff;
    }
</style>
</head>
<body>
<div class="container">
<?php
$body2 = "<table class='table'>
    <tr> 
        <th width='40'>NO</th>
        <th width='320'>주문내용</th>
        <th width='40'>금액</th>
        <th width='80'>부가세</th>
        <th width='120'>주문일</th>
    </tr>
    <tr> 
        <td>$no</td>
        <td>$Type_1</td>
        <td>$money4</td>    
        <td>$money5</td>
        <td>$date</td>
    </tr>
    <tr> 
        <td>합계</td>    
        <td colspan='2' align='right'><strong>$money4</strong></td>
        <td colspan='2' align='right'><strong>$money5</strong></td>
    </tr>
</table>
<div class='info-box'>
<li>영업 관련 문의: 1688-2384, 02-2632-1830</li>
<li>주소: 서울특별시 영등포구 영등포로36길9 송호빌딩 1층 두손기획인쇄</li>
<li>홈페이지: www.dsp1830.shop</li>
<li>계좌번호: 999-1688-2384 국민은행 예금주: DSP114</li>
</div>";

$body = "<div class='info-box'>
<li>$username 고객님, 아래와 같이 주문이 완료되었습니다.</li>
<table class='table'> 
    <tr> 
        <th>이름/아이디</th>
        <td>$username</td>
    </tr>
    <tr> 
        <th>연락처</th>
        <td>TEL: $phone, Mobile: $Hendphone</td>
    </tr>
    <tr> 
        <th>주소</th>
        <td>$zip1 $zip2</td>
    </tr>
    <tr> 
        <th>이메일</th>
        <td>$email</td>
    </tr>
    <tr>
        <th>내용</th>
        <td>$cont</td>
    </tr>
</table>
</div>
<div class='info-box'>
<li>주문 내역</li>
$body2
</div>";

echo $body;

include_once('../shop/mailer.lib.php');
$content = $body;
$to = $email;
$subject = "$username 님의 주문 내역입니다.";
mailer($fname, $fmail, $to, $subject, $content, $type=1, $file, $cc="", $bcc="");
?>

<table border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td height="20" width="600"></td>
    </tr>
    <tr>
        <td>
            <!-- 중간 공백 -->
            <table border="0" align="center" cellpadding="15" cellspacing="1" bgcolor="#C3C3C3">
                <tr>
                    <td width="598" bgcolor="#FFFFFF">
                        <?php if ($PageSS == "OrderOne") { ?>
                        <p style="font-size:10pt; color:#996633; line-height:130%;">
                        주문이 정상적으로 접수되었습니다.<br>
                        고객님의 주문번호는 <b><?=$no?></b>입니다.<br>
                        <span style="font-size:12pt; color:#0080FF;"><b>주문 내역을 확인해주세요!</b></span>
                        </p>
                        <br>
                        <table border="0" align="center" width="96%" cellpadding="0" cellspacing="0">
                            <tr><td align="center" height="10" width="100%"></td></tr>
                            <tr><td align="center" bgcolor="#C3C3C3" height="1" width="100%"></td></tr>
                            <tr><td align="center" height="10" width="100%"></td></tr>
                        </table>
                        <p style="font-size:10pt; color:#CC0033; font-weight:bold; line-height:160%;"><?=$money4?>원</p>
                        <p style="font-size:10pt; color:#CC0033; font-weight:bold; line-height:160%;"><?=$money5?>원</p>
                        <?php } else { ?>
                        <p style="font-size:10pt; color:#996633; line-height:160%;">
                        주문 처리가 정상적으로 완료되지 않았습니다.<br>
                        </p>
                        <?php } ?>
                        <br><br><p style="font-size:9pt; color:#000000;">주문을 완료하시려면 아래 버튼을 눌러주세요.</p>
                        <br><br><p style="font-size:12pt; color:#0080FF;"><b>주문이 완료되었습니다!</b></p>
                        <p align="center">
                            <input type="image" src='/images/enter.gif' onClick="javascript:window.location='/mlangprintauto/inserted/index.php';">
                        </p>
                        <br>
                        <table align="center" width="250" border="0"> 
                            <tr align="left"> 
                                <td>           
                                <b>입금 안내</b><br>
                                은행: 국민은행<br>
                                계좌번호: 999-1688-2384<br>
                                예금주: DSP114
                                </td>
                            </tr>
                        </table>
                        <br>
                        <form method="post" action="../stdpay/INIStdPaySample/INIStdPayRequest.php">
                            <input type="hidden" name="no" value="<?php echo $no ?>">
                            <div align="center">
                                <button type="submit" class="btn-submit">
                                    <img src="img/inicis.png" width="157" height="41" alt="카드결제 요청 - 클릭하세요"/>
                                </button>
                                <br><br>카드결제에 대해 문의사항이 있으시면 연락주세요 [TEL : 1688-2384]
                            </div>
                        </form>
                        <?php include "../admin/mlangprintauto/int/info.php"; ?>
                        <?php include "./OrderDownText.php"; ?>
                    </td>
                </tr>
            </table>
            <!-- 중간 공백 -->
        </td>
    </tr>
    <tr>
        <td height="30"></td>
    </tr>
    <tr>
        <td height="2" background="/images/dot.gif"></td>
    </tr>
</table>

<br><br><br>

<?php include "$_SERVER[DOCUMENT_ROOT]/mlangprintauto/MlangPrintAutoDown.php"; ?>
</div>
</body>
</html>
