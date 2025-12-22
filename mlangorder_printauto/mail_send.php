<?php
session_start();
$session_id = session_id();

include "../lib/func.php";
$connect = dbconn();

$order_id = date("Ymdhis");
$regdate = time();

if ($img_name) {
    move_uploaded_file($img, "../shop/data/" . $img_name);
}

$query = "SELECT * FROM mlangorder_printauto WHERE name='$name' AND  date='$date'"; //가져오구
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_array($result);

$Folder = "../shop/data/" . $img_name;
$jd = date('Y/m/d H:i:s', $data['regdate']);
$total = 0;
$type = "스티카";
if ($img_name) {
    $ImgFolder = "../shop/data/" . $img_name;
}
$Type_1 = $data['Type_1'];
$OrderStyle = "2";


$body2 = "<table  align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'>
            <tr bgcolor='#CCCCFF'> 
                <td bgcolor='#CCCCFF' width='50'>NO </td>
				<td bgcolor='#CCCCFF' width='300'>주문내 </td>
				<td bgcolor='#CCCCFF' width='100'>금액 </td>
				<td bgcolor='#CCCCFF' width='100'>부가세포함 </td>
				<td bgcolor='#CCCCFF' width='50'>주문 </td>
				</tr> ";
$query = "SELECT * FROM mlangorder_printauto WHERE name='$name' AND  date='$date'";
$result = mysqli_query($connect, $query);
while ($data = mysqli_fetch_array($result)) {


    $body2 .= "		  
				<tr align='center' bgcolor='#FFFFFF'>
				<td bgcolor='#FFFFFF'>" . $data['no'] . "  </td>
				<td bgcolor='#FFFFFF'>" . $data['Type_1'] . "</td>
				<td bgcolor='#FFFFFF'>" . $data['money_4'] . " </td>    
				<td bgcolor='#FFFFFF'>" . $data['money_5'] . " </td>
				<td bgcolor='#FFFFFF'>" . $data['date'] . " </td>
		   </tr>
		    ";
    $total += $data['money_4'];
}
$vat = $total * 1.1;
$body3 = "<tr bgcolor='#DDECDD'> 
    <td colspan =2 bgcolor='#DDECDD'> 합계  </td>
	 
    <td bgcolor='#FFFFFF'><strong>￦$total</strong></td>
	<td > 부가세포함 </td>
    <td bgcolor='#FFFFFF'><strong>(￦$vat)</strong></td>
  </tr>
  ";

$body3 .= "</table> <br>
			두손기획인쇄 대표전화:1688-2384, 02-2632-1830<br>

			서울 영등포구 영등포로36길9 송호빌딩1층<br>

			주소창 검색창에 두손기획인쇄로 검색<br>

			www.dsp1830.shop<br>   

			전단지 스티커 카달로그 각종서식 봉투 명함 각종 인쇄<br>

			국민은행 999-1688-2384  차경선두손기획<br>

			신한은행 110-342-543507 차경선두손기획<br>

			농협  301-2632-1830-11  차경선두손기획<br>";
$body = "<li> $data[name]님의 스티커구매내역입니다. ";
$body .= "<table align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'> 
        <tr> 
          <td bgcolor='#CCCCFF'> 이름/상호 
          <td bgcolor='#FFFFFF'> $data[name] 
		</tr>
        <tr> 
          <td  bgcolor='#CCCCFF'> 연락처 
          <td bgcolor='#FFFFFF'> $data[phone], $data[Hendphone]
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> 수령지 주소 
          <td bgcolor='#FFFFFF'> $data[zip1]$data[zip2]
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> 전달사항 
          <td bgcolor='#FFFFFF'> $data[cont]
		</tr>			
		<tr>
          <td  bgcolor='#CCCCFF'> 비고
		  <td bgcolor='#FFFFFF'>  특수 작업은 <br>다소 늦어질수 있습니다
		</tr>			

		</table> <br>
       <li>주문 목록 
          ";
$body .= $body2 . $body3;
// $header = "From:duson<duson@dsp114.com>\n"; 
// $header .= "Content-Type:text/html\n"; 
include_once('mailer.lib.php');
$content = $body;
$to = "$email";
$subject = "$name 님 제품 구매를 감사드립니다.";
echo $body;
//echo $body;
//echo $subject;
//echo $content;
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "1");
mailer("$fname", "$fmail", "$to", "$subject", "$content", 1); //$fname, $fmail, $to, $subject, $content, $type=0,
//mailer("test", "dsp1830@naver.com", "dsp1830@naver.com", "테스트메일", "잘가야", 1);
// mail($email,"$name 님 제품 구매를 감사드립니다.",$body, $header); 
$query  = "DELETE FROM shop_temp WHERE session_id='$session_id' ";
mysqli_query($connect, $query);
mysqli_close($connect);
?>