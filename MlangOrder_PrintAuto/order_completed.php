<?php
session_start();
$session_id = session_id();
$no = $_GET['no'];
$HomeDir = "../../";

include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php";

$connect = dbconn();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Order Complete</title>
    <style type="text/css">
        .boldB {
            font-family: "맑은 고딕";
            font-size: 9pt;
            font-weight: bold;
            color: #06F;
        }
        td, input, li, a {
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div align="center">
        <li><br>
        <table align="center" width="650" border="0">
            <tr align="center">
                <td>
                    <p><img src="img/complete.jpg" width="650" height="412" alt="Order Complete" /><br></p>
                    <p>E-mail : dsp1830@naver.com  FAX : 02-2632-1829</p>
                </td>
            </tr>
        </table>
        <p></p>
        <div align="center">
            <a href="view.php"><img src="img/order_com.gif" width="99" height="31" border="0" alt="Order Complete" /></a>
            &nbsp; &nbsp;
            <a href="../stdpay/INIStdPaySample/INIStdPayRequest.php?no=<?php echo $no; ?>"><img src="img/inicis.png" width="174" height="41" alt="Payment Request" />(카드결제요청-전화주세요)</a>
        </div>
        <br>
    </div>
    <?php include "../MlangPrintAuto/DhtmlText.php"; ?>
    <?php include "../MlangPrintAuto/MlangPrintAutoDown.php"; ?>
</body>
</html>
