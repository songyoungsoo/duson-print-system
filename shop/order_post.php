<? 
// PHP 코드 최상단에 작성
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

   session_start(); 
   $session_id = session_id();
function assa($msg){
    $msg = iconv("UTF-8", "UTF-8", $msg); // $msg를 UTF-8에서 UTF-8 인코딩으로 변환
    echo "
    <script>
        window.alert('$msg');
        history.back(1);
    </script>
    ";
    exit;
}

	if(!$phone1) assa('전화번호를 입력하세요');	 
	if(!$phone2) assa('전화번호를 입력하세요');	 
	if(!$phone3) assa('전화번호를 입력하세요');	 
	if(!$name) assa('이름을 입력하세요');	
  if(!$email) assa('이메일을 입력하세요');	 
	if(!$name) assa('이름을 입력하세요');	
	if(!$priv) assa('개인정보의 수집 및 이용에 동의하셔야 다음단계로 갑니다.'); 

  // include "../db.php"; 

  function dbconn(){
    $connect = mysql_connect("localhost","duson1830","du1830");
    mysql_select_db("duson1830",$connect);
    mysql_query("SET NAMES utf8");
    return $connect;
  }  
  $connect = dbconn(); 

  $order_id = date("Ymdhis"); 
  $regdate = time(); 

  if($img_name){ 
      move_uploaded_file($img,"../shop/data/".$img_name); 
  } 

  $phone = "$phone1-$phone2-$phone3"; 
  $hphone = "$hphone1-$hphone2-$hphone3";
  $zip = "$sample6_postcode"; 
  $address1 = "$sample6_address";
  $address2 = "$sample6_detailAddress";
  $address3 = "$sample6_extraAddress";
 
  $zip1 = "$sample6_address";
  $zip2 = "$sample6_detailAddress";


  $query = "select sum(st_price) from shop_temp where session_id='$session_id' ";  //가져오구
  $result =mysql_query($query, $connect); 
  $data = mysql_fetch_array($result); 
  $total = $data[0];
  $query = "insert into shop_list(order_id, name, zip, address1, address2, address3, 
            phone, hphone, delivery, bank, ipkeum, memo, st_price, regdate, email, password, img)  values('$order_id','$name','$zip','$address1','$address2','$address3','$phone','$hphone','$delivery','$bank','$ipkeum','$memo','$data[st_price]','$regdate','$email','$password','$img_name') "; 
     mysql_query($query, $connect);

  $body2 = "<table  align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'>
            <tr bgcolor='#CCCCFF'> 
                <td bgcolor='#CCCCFF' width='30'>NO </td>
				<td bgcolor='#CCCCFF' width='50'>재질 </td>
				<td bgcolor='#CCCCFF' width='50'>가로(mm) </td>
				<td bgcolor='#CCCCFF' width='50'>세로(mm) </td>
				<td bgcolor='#CCCCFF' width='40'>매수(매) </td>
				<td bgcolor='#CCCCFF' width='70'>도무송<br>(타입) </td>	
				<td bgcolor='#CCCCFF'>도안비 </td>
				<td bgcolor='#CCCCFF'>금액 </td>
				<td bgcolor='#CCCCFF'>부가세포함 </td>
				<td bgcolor='#CCCCFF'>주문일 </td>
				</tr> "; 
  $query = "select * from shop_temp where session_id='$session_id' ";
  $result = mysql_query($query, $connect); 
   while($data = mysql_fetch_array($result)){ 
  $Folder = "../shop/data/".$img_name;
  $jd = date('Y/m/d H:i:s',$data[regdate]);
  $j = substr($data[jong],4,12);
  $j1= substr($data[jong],0,3);
  $d = substr($data[domusong],6,4);
  
  $type= "스티카";
  if($img_name){$ImgFolder = "../shop/data/".$img_name;}
  $Type_1="$j 스티카|크기: $data[garo] x $data[sero]mm |매수: $data[mesu] 매|$d"; 
  $OrderStyle= "2";
 
   $query = "insert into MlangOrder_PrintAuto(Type,ImgFolder,Type_1, name,zip1 ,zip2, 
            phone, hendphone, delivery, bank, bankname, cont, money_2, money_4, money_5, date, OrderStyle, email, pass, zip, ThingCate) 
            values('$type','$Folder','$Type_1','$name','$address1','$address2','$phone','$hphone', '$delivery','$bank','$ipkeum','$memo', '$data[uhyung]', '$data[st_price]','$data[st_price_vat]','$order_id','$OrderStyle','$email','$password','$zip','$img_name') "; 
   mysql_query($query, $connect);   
        $q = "insert into shop_order(order_id,parent,jong,garo,sero,mesu,domusong,uhyung,st_price,st_price_vat,regdate,img) 
              values('$order_id','$data[no]','$data[jong]','$data[garo]','$data[sero]','$data[mesu]','$data[domusong]','$data[uhyung]',
			  '$data[st_price]','$data[st_price_vat]','$regdate','$data[$img_name]') "; 
     
        $body2 .= "		  
				<tr align='center' bgcolor='#FFFFFF'>
				<?php $j = substr($data[jong],4,8);?>
				<?php $j1= substr($data[jong],0,3);?>
				<?php $d = substr($data[domusong],6,4);?>
				<?php $d1= substr($data[domusong],0,5);?>
				<td bgcolor='#FFFFFF'>$data[no]  </td>
				<td bgcolor='#FFFFFF'>$j </td>
				<td bgcolor='#FFFFFF'>$data[garo] </td>    
				<td bgcolor='#FFFFFF'>$data[sero] </td>
				<td bgcolor='#FFFFFF'>$data[mesu] </td>
				<td bgcolor='#FFFFFF'>$d </td>
				<td bgcolor='#FFFFFF'>$data[uhyung] </td>
				<td bgcolor='#FFFFFF'><strong>$data[st_price] </strong> </td>
				<td bgcolor='#FFFFFF'><strong>$data[st_price_vat]</strong> </td>
				<td bgcolor='#FFFFFF'>$jd</td>
		   </tr>
		    ";
 // $total += $data[st_price]; 
  mysql_query($q, $connect);
	  }	   
  $vat = $total*1.1;
  $body3 = "<tr bgcolor='#DDECDD'> 
    <td colspan = 2bgcolor='#DDECDD'> 합계  </td>
	 
    <td colspan = 3 bgcolor='#FFFFFF'><strong>￦$total</strong></td>
	<td colspan = 2 > 부가세포함 </td>
    <td colspan = 3 bgcolor='#FFFFFF'><strong>(￦$vat)</strong></td>
  </tr>
  "; 
 
 //mysql_query($query, $connect); 
 $body3 .= "</table> <br>
			두손기획인쇄 대표전화:1688-2384, 02-2632-1830<br>

			서울 영등포구 영등포로36길9 송호빌딩1층<br>

			주소창 검색창에 두손기획인쇄로 검색<br>

			localhost<br>   

			전단지 스티커 카달로그 각종서식 봉투 명함 각종 인쇄<br>

			국민은행 999-1688-2384  차경선두손기획<br>

			신한은행 110-342-543507 차경선두손기획<br>

			농협  301-2632-1830-11  차경선두손기획<br>"; 	  
	  
  $name = $_POST[name];  
  $body  = "<li> $name 님의 스티커구매내역입니다. "; 
  $body .= "<table align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'> 
        <tr> 
          <td bgcolor='#CCCCFF'> 이름/상호 
          <td bgcolor='#FFFFFF'> $name 
		</tr>
        <tr> 
          <td  bgcolor='#CCCCFF'> 연락처 
          <td bgcolor='#FFFFFF'> $phone  , $hphone 
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> 수령지 주소 
          <td bgcolor='#FFFFFF'> $address1 $address2 
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> 전달사항 
          <td bgcolor='#FFFFFF'> $memo
		</tr>			
		<tr>
          <td  bgcolor='#CCCCFF'> 비고
		  <td bgcolor='#FFFFFF'> 유포지,데드롱,투명지 등 특수재질은 <br>하루나 이틀늦어질수 있습니다
		</tr>			

		</table> <br>
       <li>주문 목록 
          "; 
$body .= $body2. $body3; 
 // $header = "From:duson<duson@dsp114.com>\n"; 
 // $header .= "Content-Type:text/html\n"; 
include_once('mailer.lib.php');
$content = $body;
$to = "$email";
$subject = "$name 님 제품 구매를 감사드립니다.";
//echo $body;
//echo $subject;
//echo $content;
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "1");
mailer("$fname", "$fmail", "$to", "$subject", "$content", $type=1);//$fname, $fmail, $to, $subject, $content, $type=0,
//mailer("test", "dsp1830@naver.com", "dsp1830@naver.com", "테스트메일", "잘가야", 1);
  //mail($email,"$name 님 제품 구매를 감사드립니다.",$body, $header); 
   $query  = "delete from shop_temp where session_id='$session_id' "; 
   mysql_query($query, $connect);
  //  include "./lib.php"; 
// $connect = dbconn(); 
$query = "select * from MlangOrder_PrintAuto where email='$email' and name='$name' order by no desc limit 1";
$result = mysql_query($query, $connect); 
$data = mysql_fetch_array($result);
$no = $data['no']; 

?>  
<script> 
	location.href='order_completed.php?no=<?php echo $no?>'; 
</script>
<!-- <script>
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "order_completed.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
      // 처리 결과를 받아서 처리할 내용을 여기에 작성합니다.
    }
  }
  var data = "no=" + encodeURIComponent('<?php echo $no ?>') + "&body=" + encodeURIComponent('<?php echo $body ?>');
  xhr.send(data);
</script> -->
<?php mysql_close($connect); ?>
