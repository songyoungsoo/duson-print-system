<?php
session_start();

// Check authentication based on access type
$is_customer_access = isset($_GET['customer']) && $_GET['customer'] === '1';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

if ($is_customer_access) {
    // 고객용 접근 - 본인 주문만 확인 가능
    if (!isset($_SESSION['customer_authenticated']) || $_SESSION['customer_authenticated'] !== true) {
        echo "<script>
                alert('로그인이 필요합니다.');
                window.close();
              </script>";
        exit;
    }
} elseif (strpos($referrer, 'checkboard.php') !== false) {
    // 관리자용 접근
    if (!isset($_SESSION['checkboard_authenticated']) || $_SESSION['checkboard_authenticated'] !== true) {
        echo "<script>
                alert('인증이 필요합니다. 메인 화면에서 다시 로그인해주세요.');
                window.close();
              </script>";
        exit;
    }
}

header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Cache-control: private"); // <= it's magical!!

include "../db.php";

$no = $_GET['no'] ?? '';

$result = $db->query("SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'");
$row = $result->fetch_assoc();

if ($row) {
    $ImgFile = $row['ThingCate'] ?? '';
    $View_Type = $row['Type'] ?? '';
    $View_PMmember = $row['PMmember'] ?? '';
    $View_ThingNo = $row['ThingNo'] ?? '';
    $View_OrderStyle = $row['OrderStyle'] ?? '';
    $View_OrderName = $row['name'] ?? '';
    $View_standard = $row['standard'] ?? '';
    $View_pass = $row['pass'] ?? '';
    $view_designer = $row['Designer'] ?? '';
    $View_Phone = $row['phone'] ?? '';

    // 고객 접근 시 본인 주문인지 확인
    if ($is_customer_access) {
        $session_name = $_SESSION['customer_name'] ?? '';
        $session_phone = $_SESSION['customer_phone'] ?? '';
        $session_phone_normalized = preg_replace('/[^0-9]/', '', $session_phone);
        $db_phone_normalized = preg_replace('/[^0-9]/', '', $View_Phone);
        
        if ($View_OrderName !== $session_name || 
            ($session_phone_normalized !== $db_phone_normalized && 
             strpos($db_phone_normalized, $session_phone_normalized) === false)) {
            echo "<script>
                    alert('본인의 주문만 조회하실 수 있습니다.');
                    window.close();
                  </script>
                  <meta charset='UTF-8'>";
            exit;
        }
    }

    if (!$ImgFile) {
        echo "<script>
                alert('데이터 처리 오류. 이미지를 확인할 수 없습니다.');
                window.self.close();
              </script>
              <meta charset='UTF-8'>";
        exit;
    }
} else {
    echo "<script>
            window.alert('데이터가 없습니다.');
            window.self.close();
          </script>
          <meta charset='UTF-8'>";
    exit;
}

$db->close();

include "../admin/MlangPrintAuto/int/info.php";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>이미지 보기</title>
<style>
p, br, body, td, input, select, submit {
    color: black;
    font-size: 9pt;
    font-family: 돋움;
}
.style1 {
    color: #FF0000;
    font-weight: bold;
}
</style>
</head>

<body>
<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center"><img src='/images/t/Top_1.gif' width="188" height="76"></td>
        <td align="center" background='/images/t/Top_2.gif' width="100%">
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center"><img src='/img/12345.gif' width="520" height="1"></td>
                </tr>
            </table>
            <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <font style='font-size:9pt; color:#FFFFFF; line-height:150%;'>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            이 이미지는 RGB 색상으로 표시됩니다. 인쇄 시 CMYK 색상으로 출력되므로 차이가 있을 수 있습니다.<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            확인 후 인쇄하시기 바랍니다.<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            인쇄된 제품의 색상 차이는 환불 사유가 되지 않습니다.
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php
$mode = $_REQUEST['mode'] ?? '';
if ($View_SignMMk == "yes") {
    include "../db.php";
    $AdminChickTYyj = $db->query("SELECT * FROM member WHERE no='1'");
    $row_AdminChickTYyj = $AdminChickTYyj->fetch_assoc();
    $BBSAdminloginKPass = $row_AdminChickTYyj['pass'];

    if (isset($FormPass)) {
        if ($FormPass == $View_pass || $FormPass == $BBSAdminloginKPass) {
            // authorized
        } else {
            echo "<script>
                    window.alert('비밀번호가 틀렸습니다. 다시 입력해주세요.');
                    history.go(-1);
                  </script>";
            exit;
        }
    } else {
        echo "<form method='post' action='{$_SERVER['PHP_SELF']}'>
                <input type='hidden' name='mode' value='$mode'>
                <input type='hidden' name='no' value='$no'>
                <p align='center'><br><br><br><br><br><br>
                    <font style='font:bold; color:#408080;'>이미지 파일 확인을 위해 전화번호 뒷자리 4자리를 입력해주세요.</font>
                    <br><br>
                    <input type='text' name='FormPass' size='20'>
                    <input type='submit' value='확인'>
                    <br><br>
                    <font color='#666666'>문의: 02-2632-1830</font>
                </p>
              </form>
              <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
        exit;
    }
}
?>

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td bgcolor='#FFFFFF' height="10"></td>
    </tr>
    <tr>
        <td bgcolor='#E4E4E4' height="2"></td>
    </tr>
    <tr>
        <td bgcolor='#A9A8A8' height="1"></td>
    </tr>
    <tr>
        <td bgcolor='#FFFFFF' height="5"></td>
    </tr>
    <tr>
        <td>
            <table border="0" align="center" width="100%" cellpadding="2" cellspacing="0">
                <tr>
                    <td width="100">&nbsp;&nbsp;주문번호</td>
                    <td>:&nbsp;&nbsp;&nbsp;<?= $no ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;상품종류</td>
                    <td>:&nbsp;&nbsp;&nbsp;<?= $View_Type ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;디자이너</td>
                    <td>:&nbsp;&nbsp;&nbsp;<?= $view_designer ?>
                        <?php if ($view_designer == "디자이너") { ?>
                        <span class="style1">(02-2671-1830)</span>
                        <?php } elseif ($view_designer == "") { ?>
                        <span class="style1">(02-2632-1820)</span>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;주문상태</td>
                    <td>:&nbsp;&nbsp;&nbsp;<?php
                        switch ($View_OrderStyle) {
                            case "2":
                                echo "접수중..";
                                break;
                            case "3":
                                echo "디자인완료";
                                break;
                            case "4":
                                echo "인쇄중";
                                break;
                            case "5":
                                echo "배송완료";
                                break;
                            case "6":
                                echo "완료";
                                break;
                            case "7":
                                echo "취소";
                                break;
                            case "8":
                                echo "작업완료";
                                break;
                            case "9":
                                echo "작업중";
                                break;
                            case "10":
                                echo "디자인작업중";
                                break;
                            default:
                                echo "알수없음";
                                break;
                        }
                    ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;주문자명(회사명)</td>
                    <td>:&nbsp;&nbsp;&nbsp;<?= $View_OrderName ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td bgcolor='#FFFFFF' height="5"></td>
    </tr>
    <tr>
        <td bgcolor='#A9A8A8' height="1"></td>
    </tr>
    <tr>
        <td bgcolor='#E4E4E4' height="2"></td>
    </tr>
    <tr>
        <td bgcolor='#FFFFFF' height="10"></td>
    </tr>
</table> 

<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <a href='#' onClick="javascript:window.close();"><img src="./upload/<?= $no ?>/<?= $ImgFile ?>" border="0"></a>    
        </td>
    </tr>
</table> 

</body>
</html>
