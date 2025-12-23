<? 
   session_start(); 
   $session_id = session_id();
    

  include "../lib/func.php"; 
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


  $query = "select * from shop_temp where session_id='$session_id' ";  //가져오구
  $result =mysql_query($query, $connect); 
  $data = mysql_fetch_array($result); 
  $total = $data[0];
  $Folder = "../shop/data/".$img_name;
  $j = substr($data[jong],4,4);
  $j1= substr($data[jong],0,3);
  $d = substr($data[domusong],6,4);
  $type= "스티카";
  if($img_name){$ImgFolder = "../shop/data/".$img_name;}
  $Type_1="$j 스티카|크기: $data[garo] x $data[sero]mm |매수: $data[mesu] 매|$d"; 
  $OrderStyle= "2";
  $query = "insert into shop_list(order_id, name, zip, address1, address2, address3, 
            phone, hphone, delivery, bank, ipkeum, memo, st_price, regdate, email, password, img) 
            values('$order_id','$name','$zip','$address1','$address2','$address3','$phone','$hphone','$delivery','$bank','$ipkeum','$memo','$data[st_price]','$regdate','$email','$password','$img_name') "; 
     mysql_query($query, $connect);
   $query = "insert into mlangorder_printauto(Type,ImgFolder,Type_1, name,zip1 ,zip2, 
            phone, hendphone, delivery, bank, bankname, cont, money_2, money_4, money_5, date, OrderStyle, email, pass, zip, ThingCate) 
            values('$type','$Folder','$Type_1','$name','$address1','$address2','$phone','$hphone', '$delivery','$bank','$ipkeum','$memo', '$data[uhyung]', '$data[st_price]','$data[st_price_vat]','$order_id','$OrderStyle','$email','$password','$zip','$img_name') ";
 
      mysql_query($query, $connect);   
   	 
   
  $body2 = "<table width=600 border=1>"; 
  $query = "select * from shop_temp where session_id='$session_id' ";
  $result = mysql_query($query, $connect); 
   while($data = mysql_fetch_array($result)){ 
        $q = "insert into shop_order(order_id,parent,jong,garo,sero,mesu,domusong,uhyung,st_price,st_price_vat,regdate,img) 
              values('$order_id','$data[no]','$data[jong]','$data[garo]','$data[sero]','$data[mesu]','$data[domusong]','$data[uhyung]',
			  '$data[st_price]','$data[st_price_vat]','$regdate','$data[$img_name]') "; 
        $body2 .= " <?php $j = substr($data[jong],4,4);?>
		             <tr> 
                      <td>재질:$j
                      <td>가로:$data[garo],세로:$data[sero]
                      <td>매수:$data[mesu]  
                      <td>편집비:$data[uhyung] 
                      <td>합계: $data[st_price] VAT별도 
                  "; 

         mysql_query($q, $connect); 
   } 
  $body2 .= "</table> "; 

  $body  = "<li> $name 님의 스티커구매내역입니다. "; 
  $body .= "<table width=600 border=1> 
        <tr> 
          <td> 이름 
          <td> $name 
        <tr> 
          <td> 연락처 
          <td> $phone  , $hphone 
        <tr> 
          <td> 수령지 주소 
          <td> $address1 $address2 
        <tr> 
          <td> 요구사항 
          <td> $memo
		         <tr> 
          <td> 특이사항
		  <td> 유포지,데드롱,투명지 등 특수재질은 <br>하루나 이틀늦어질수 있습니다
		</table> 
          <br> 
        <li> 주문 목록 
          "; 

  $body .= $body2; 
  $header = "From:두손기획<dsp1830@naver.com>\n"; 
  $header .= "Content-Type:text/html\n"; 
  ?> 

  <?php

/**
 * This example shows settings to use when sending via Google's Gmail servers.
 * This uses traditional id & password authentication - look at the gmail_xoauth.phps
 * example to see how to use XOAUTH2.
 * The IMAP section shows how to save this message to the 'Sent Mail' folder using IMAP commands.
 */

 

include "PHPMailer.php";
include "SMTP.php"; 


//Create a new PHPMailer instance
$mail = new PHPMailer();

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// SMTP::DEBUG_OFF = off (for production use)
// SMTP::DEBUG_CLIENT = client messages
// SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_OFF;

//Set the hostname of the mail server
$mail->Host = 'smtp.naver.com';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 465;

//Set the encryption mechanism to use - STARTTLS or SMTPS
$mail->SMTPSecure = "ssl";

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = 'ysungx';

//Password to use for SMTP authentication
$mail->Password = 's33059792';

//Password to use for SMTP authentication
$mail->CharSet = 'UTF-8';

//Set who the message is to be sent from
$mail->setFrom('ysungx@naver.com', '송영수');

//Set an alternative reply-to address
$mail->addReplyTo('yeongsu32@gmail.com', '영수');

//Set who the message is to be sent to
$mail->addAddress("$email", "$name");//'yeongsu32@gmail.com', '영수'

//Set the subject line
$mail->Subject = "$name 님 제품 구매를 감사드립니다";//'메일 테스트입니다. ';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML("$body");//테스트입니다. <br> 안녕하세요. 영수입니다. 

//Replace the plain text body with one created manually
$mail->AltBody = '동해물과 백두산이 마르고 닳도록';

//Attach an image file
$mail->addAttachment('a.jpg');

//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
    //Section 2: IMAP
    //Uncomment these to save your message in the 'Sent Mail' folder.
    #if (save_mail($mail)) {
    #    echo "Message saved!";
    #}
}
 

  // mail($email,"$name 님 제품 구매를 감사드립니다.",$body, $header); 
   $query  = "delete from shop_temp where session_id='$session_id' "; 
   mysql_query($query, $connect); 
   mysql_close($connect);

?>
<script> 
location.href='order_completed.php'; 
</script> 

