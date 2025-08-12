<?php
// PHP 코드 최상단에 작성
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

   session_start(); 
   $session_id = session_id();
   if (isset($_GET['no'])) {
    $no = $_GET['no'];
    // echo "전달된 no 값: " . $no;
} else {
    echo "no 값이 전달되지 않았습니다.";
}
$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php"; 


 
$connect = mysqli_connect("localhost", "duson1830", "du1830", "duson1830");
if (!$connect) {
    die("DB 연결 실패: " . mysqli_connect_error());
}
mysqli_set_charset($connect, "utf8");
?>
<BR>
         <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
	   <tr><td align=center height=10 width=100%></td></tr>
       <tr><td align=center bgcolor='#C3C3C3' height=1 width=100%></td></tr>
	   <tr><td align=center height=10 width=100%></td></tr>
	 </table>
    <h3 align="center">주문내역</h3>
<table  align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'>
<tr align='center' bgcolor='#CCCCFF'> 
    <td align='center' bgcolor='#CCCCFF' width='40'>NO </td>
<td align='center' bgcolor='#CCCCFF' width='320'>주문내역 </td>
<td align='center' bgcolor='#CCCCFF' width='40'>금액 </td>
<td align='center' bgcolor='#CCCCFF' width='80'>부가세포함 </td>
<td align='center' bgcolor='#CCCCFF' width='120'>주문일 </td>
</tr>
<?php
  $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no='$no'";
  // $query = "select * from shop_temp where session_id='$session_id' ";
  $result = mysqli_query($connect, $query);
  $total = 0;
  while ($data = mysqli_fetch_array($result)) {
?>
      <tr align='center' bgcolor='#FFFFFF'>
        <td cellpadding='2' bgcolor='#FFFFFF'> <?php echo  $data['no'] ?></td>
        <td cellpadding='2' bgcolor='#FFFFFF'> <?php echo  $data['Type_1'] ?></td>
        <td cellpadding='2' bgcolor='#FFFFFF'> <?php echo  $data['money_4'] ?></td>
        <td cellpadding='2' bgcolor='#FFFFFF'> <?php echo  $data['money_5'] ?></td>
        <td cellpadding='2' bgcolor='#FFFFFF'> <?php echo  $data['date'] ?></td>
      </tr>
<?php
    $total += $data['money_4'];
  }
?>
<tr align='center bgcolor='#DDECDD'> 
    <td align='center' bgcolor='#DDECDD'> 합계  </td>	 
    <td  cellpadding='2' align='right' bgcolor='#FFFFFF'><strong>￦<?php echo number_format($total)?> </strong></td>
	<td align='center' colspan = 2 cellpadding='2' bgcolor='#DDECDD'> 부가세포함 </td>
    <td align='right' bgcolor='#FFFFFF'><strong>￦<?php echo number_format($total*1.1)?></strong></td>
  </tr>
</table>
<style type="text/css">
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
button {
    cursor: pointer;
}
.btn-submit {
    background-image: url("img/inicis.png");
    background-repeat: no-repeat;
    background-size: 100%;
    width: 157px; /* 버튼의 너비 */
    height: 41px; /* 버튼의 높이 */
    border: none;
}
</style>
<br>
<style>
td,input,li,a{font-size:9pt}
    </style>
<?php 
echo $body;
?>
<table align=center width=600 border=0> 
  <tr align=center> 
    <td width="100%"><font style='font-size:10pt; color:#996633; line-height:130%;'>
<table align=center width=600 border=0> 
  <tr align=left> 
    <td width="70%">※ 안전한 거래와 빠른 납기 질좋은 인쇄물 제작으로 보답하겠습니다.<BR>
※ 입금자와 주문인이 다를경우 꼭 전화를 주셔야 합니다.<BR>
&nbsp;&nbsp; 입금확인후 작업이 진행됩니다.(배송은 착불기준입니다)<BR>
※ 디자인 교정은 우측 상단 '교정보기'에서 확인합니다.<BR>
※ 세금계산서 발행시 사업자등록증을 이메일or 팩스로 전송합니다.
<br>
      E-mail : dsp1830@naver.com | TEL : 1688-2384 | FAX : 02-2632-1829 <BR>
※ 관련 담당자가 빠른 답변을 드릴것 입니다. <BR>

        </TD>
        </TR>
      </table>


     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
	   <tr><td align=center height=10 width=100%></td></tr>
       <tr><td align=center bgcolor='#C3C3C3' height=1 width=100%></td></tr>
	   <tr><td align=center height=10 width=100%></td></tr>
	 </table>


<!--&nbsp;&nbsp;&nbsp;◎ 공급가액 - <font style='font-size:10pt; color:#CC0033; font:bold; line-height:160%;'><?php $T = "$money_4"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></font>원
<BR>
&nbsp;&nbsp;&nbsp;◎ 부가세포함 -<font style='font-size:10pt; color:#CC0033; font:bold; line-height:160%;'> 
<?php $T = "$money_5"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></font>원</font>

<BR>

</font>-->

<font style='font-size:9pt; color:#421718;'><b>주문완료 버튼을 눌러주셔야 정상적으로 주문접수가 됩니다</b></font>
    <BR><font style='font-size:11pt; color:#0080FF;'><b>주문내역은 이메일로 전송됩니다!</b></font>
<br>
             <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
	   <tr><td align=center height=10 width=100%></td></tr>
       <tr><td align=center bgcolor='#C3C3C3' height=1 width=100%></td></tr>
	   <tr><td align=center height=10 width=100%></td></tr>
	 </table>
</table>
<div align=center> <a href=view.php><img src="img/order_com.gif" width="99" height="31" border="0" ></a><br>
 <BR>
<table align=center width=250 border=0> 
  <tr align=left> 
    <td>           
◎ 입금은행 - <font style='font-size:12pt; color:#0080FF; font:bold;'><b>국민은행</b></font><BR><!-----------------<=$View_BankName?> ----------------->
◎ 예&nbsp;금&nbsp;주 - <font style='font-size:12pt; color:#0080FF; font:bold;'><b>차경선 두손기획인쇄</b></font><BR><!-----------------<=$View_TName?> ----------------->
◎ 계좌번호 - <font style='font-size:12pt; color:#0080FF; font:bold;'><b>999-1688-2384</b></font><BR><!----------------- <=$View_BankNo?> ----------------->

        </TD>
        </TR>
      </table><BR>

  <form method="post" action="../stdpay/INIStdPaySample/INIStdPayRequest.php">
    <input type="hidden" name="no" value="<?php echo $no ?>">
<!--
     <button type="img">
        <img src="img/inicis.png" width="157" height="41" border="0" alt="카드결제- 전화주세요"/>
    </button>
-->
    <button type="submit" class="btn-submit">
    </button>
    <br><br>카드결제오류시 전화주시기 바랍니다[ TEL : 1688-2384 ]
  </form>
</div>
<BR>
     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
   <tr><td align=center height=10 width=100%></td></tr>
   <tr><td align=center bgcolor='#C3C3C3' height=1 width=100%></td></tr>
   <tr><td align=center height=10 width=100%></td></tr>
 </table>        
<?php
include "../MlangPrintAuto/DhtmlText.php";
?>
<?php
include "../MlangPrintAuto/MlangPrintAutoDown.php";
?> 