<?php
// PHP 오류를 표시하지 않도록 설정
ini_set('display_errors', '0');

// 세션 시작
session_start();
session_cache_limiter("no-cache, must-revalidate"); 
header("Expires: 0");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 2001 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Cache-control: private");

$HomeDir = "..";
$PageCode = "PrintAuto";
include "$HomeDir/db.php";
include "$_SERVER[DOCUMENT_ROOT]/mlangprintauto/mlangprintautotop.php";

if (!$page) {
    $page = "NameCard";
}
?>

<head>
    <script language="javascript">
        // 숫자와 알파벳 검사 함수
        var NUM = "0123456789"; 
        var SALPHA = "abcdefghijklmnopqrstuvwxyz";
        var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

        function TypeCheck(s, spc) {
            for (var i = 0; i < s.length; i++) {
                if (spc.indexOf(s.substring(i, i + 1)) < 0) {
                    return false;
                }
            }        
            return true;
        }

        // 우편번호 찾기 창 열기
        function zipcheck() {
            window.open("zip.php?mode=search", "zip", "scrollbars=yes,resizable=yes,width=550,height=510,top=10,left=50");
        }

        // 폼 필드 검사 함수
        function JoinCheckField() {
            var f = document.JoinInfo;

            if (f.name.value == "") {
                alert("신청자 이름/상호를 입력해 주세요");
                f.name.focus();
                return false;
            }

            if (f.phone.value == "") {
                alert("전화번호를 입력해 주세요.");
                f.phone.focus();
                return false;
            }
        }

        // 금액 계산 함수
        function AdminMoneyXSUOk(form) {
            form.AdminMoneyXSUView.value = form.AdminMoney.value * form.Su.value;

            var str = form.AdminMoneyXSUView.value;
            var retValue = "";
            var retValue2 = "";

            for (var i = 0; i < str.length; i++) {  
                if (str.charAt(str.length - i - 1) != ",") {
                    retValue2 = str.charAt(str.length - i - 1) + retValue2;
                }
            }

            for (var i = 0; i < retValue2.length; i++) { 
                if (i > 0 && (i % 3) == 0) { 
                    retValue = retValue2.charAt(retValue2.length - i - 1) + "," + retValue; 
                } else { 
                    retValue = retValue2.charAt(retValue2.length - i - 1) + retValue; 
                } 
            } 

            form.AdminMoneyXSUView.value = "합계금액 " + retValue + "원";
        }
    </script>
    <link href="css/board.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
    <form name="JoinInfo" method="post" enctype="multipart/form-data" onsubmit="return JoinCheckField()" action="<?=$PHP_SELF?>">
        <input type="hidden" name="PageSS" value="<?=$SubmitMode?>">
        <input type="hidden" name="mode" value="SubmitOk">
        <tr>
            <td><img src="/img/T_4.gif" width="452" height="77"></td>
        </tr>
        <tr>
            <td height="15"></td>
        </tr>
        <tr>
            <td align="left" valign="top"><img src="images/auto_ok_03.gif" width="77" height="16"></td>
        </tr>
        <tr>
            <td align="center" valign="top">
                <!-------------------------------- 주문확인서 ----------------------------------------------->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid;border-color:#e4e4e4">
                    <tr>
                        <td><?php include "TOrderResult.php"; ?></td>
                    </tr>
                </table>
                <!-------------------------------- 주문확인서 끝 ----------------------------------------------->
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td><img src="images/auto_ok_06.gif" width="77" height="17"> <span class="style1">(* 신청자 정보를 정확히 입력해 주세요.) </span></td>
        </tr>
        <tr>
            <td height="1" bgcolor="#e4e4e4"></td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td align="center" valign="top">
                <!-------------------------------- 신청자정보입력 -------------------------------------->
                <table width="100%" border="0" cellspacing="2" cellpadding="0">
                    <tr>
                        <td width="99" align="center" bgcolor="#E4E4E4"><strong> 성명/상호</strong></td>
                        <td width="129" align="left"><input name="name" type="text" size="20"></td>
                        <td width="102" align="center" bgcolor="#E4E4E4"><strong> E-MAIL</strong></td>
                        <td width="230" align="left"><input name="email" type="text" size="20"></td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#E4E4E4"><strong>전화번호</strong></td>
                        <td align="left"><input name="phone" type="text" size="20"></td>
                        <td align="center" bgcolor="#E4E4E4"><strong>휴대폰</strong></td>
                        <td align="left"><input name="Hendphone" type="text" size="20"></td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#E4E4E4"><strong>사업자명</strong></td>
                        <td colspan="3" align="left"><input type="text" name="bizname" size="57"></td>
                    </tr>
                </table>
                <!-------------------------------- 신청자정보입력 끝 -------------------------------------->
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td><img src="images/auto_ok_10.gif" width="77" height="15"></td>
        </tr>
        <tr>
            <td height="1" bgcolor="#E4E4E4"></td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td align="center">
                <textarea name="cont" cols="88" rows="5"><?=$textarea?></textarea>
            </td>
        </tr>
        <tr>
            <td height="5"></td>
        </tr>
        <tr>
            <td align="center"><input type="image" src='/img/Y_3.gif'></td>
        </tr>
    </form>
</table>
</body>
</html>
