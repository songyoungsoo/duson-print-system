<?php
<? 
   session_start(); 
   $session_id = session_id();
   function assa($msg){
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
	if(!$priv) assa('개인정보의 수집 및 이용에 동의하셔야 다음단계로 갑니다.'); 

  include "../lib/func.php"; 
  $connect = dbconn(); 

  $order_id = date("Ymdhis"); 
  $regdate = time(); 
$target_dir="../shop/data/";
$total = count($_FILES["file"]["name"]);
for($i=0; $i<$total; $i++) {
$target_file = $target_dir.basename($_FILES["file"]["name"][$i]);
$ext = pathinfo($target_file,PATHINFO_EXTENSION);
$filename = basename($target_file,".$ext");
$num = 1;
if (file_exists($target_file)) {
            while(file_exists($target_file)) {
                $filename2 = $filename."($num)";
                $target_file = $target_dir.$filename2.".$ext";
                $num++;
            }
        }
 if(move_uploaded_file($_FILES["file"]["tmp_name"][$i], $target_file)) {
$sql = "INSERT INTO upload(nickname,starttime,realname,changename)
VALUES ('".$_POST['nickname']."','".$_POST['time']."','".$filename.".$ext"."','$target_file')";
            $res = $conn->query($sql);
        } else {
            echo "<script>parent.alert('업로드를 성공하지 못했습니다.');</script>";
            exit();
        }
}
	$sql2 = "SELECT *FROM upload WHERE nickname='".$_POST['nickname']."' AND starttime='".$_POST['time']."'";
	$res2 = $conn->query($sql2);
	while($row=mysqli_fetch_array($res2)) {
	echo "<div><a href='download.php?filepath=".$row['changename']."&filename=".$row['realname']."'>".$row['realname']."</a><a style='float:right' href='delete.php?filename=".$row['changename']."&time=".$row['starttime']."'>삭제</a></div><br>";
	}

  $zip = "$zip1-$zip2"; 
  $phone = "$phone1-$phone2-$phone3"; 
  $hphone = "$hphone1-$hphone2-$hphone3"; 

  $query = "select * from shop_temp where session_id='$session_id' ";  //가져오구
  $result =mysqli_query($query, $connect); 
  $data = mysqli_fetch_array($result); 
  $total = $data[0];
  $Folder = "../shop/data/".$img_name;
  $j = substr($data['jong'],4,4);
  $j1= substr($data['jong'],0,3);
  $d = substr($data['domusong'],6,4);
  $type= "스티카";
  if($img_name){$ImgFolder = "../shop/data/".$img_name;}
  $Type_1="$j 스티카|크기: $data['garo'] x $data['sero']mm |매수: $data['mesu'] 매|$d"; 
  $OrderStyle= "2";
  $query = "insert into shop_list(order_id, name, address1, address2, 
            phone, hphone, delivery, bank, ipkeum, memo, st_price, regdate, email, password, zip, img) 
            values('$order_id','$name','$address1','$address2','$phone','$hphone','$delivery','$bank','$ipkeum','$memo','$data['st_price']','$regdate','$email','$password','$zip','$img_name') "; 
     mysqli_query($query, $connect);
   $query = "insert into MlangOrder_PrintAuto(Type,ImgFolder,Type_1, name,zip1 ,zip2, 
            phone, hendphone, delivery, bank, bankname, cont, money_2, money_4, money_5, date, OrderStyle, email, pass, zip, ThingCate) 
            values('$type','$Folder','$Type_1','$name','$address1','$address2','$phone','$hphone', '$delivery','$bank','$ipkeum','$memo', '$data['uhyung']', '$data['st_price']','$data['st_price_vat']','$order_id','$OrderStyle','$email','$password','$zip','$img_name') ";
 
      mysqli_query($query, $connect);   
   	 
   
  $body2 = "<table width=600 border=1>"; 
  $query = "select * from shop_temp where session_id='$session_id' ";
  $result = mysqli_query($query, $connect); 
   while($data = mysqli_fetch_array($result)){ 
        $q = "insert into shop_order(order_id,parent,jong,garo,sero,mesu,domusong,uhyung,st_price,st_price_vat,regdate,img) 
              values('$order_id','$data['no']','$data['jong']','$data['garo']','$data['sero']','$data['mesu']','$data['domusong']','$data['uhyung']',
			  '$data['st_price']','$data['st_price_vat']','$regdate','$data[$img_name]') "; 
        $body2 .= " <?php $j = substr($data['jong'],4,4);?>
		             <tr> 
                      <td>재질:$j
                      <td>가로:$data['garo'],세로:$data['sero']
                      <td>매수:$data['mesu']  
                      <td>편집비:$data['uhyung'] 
                      <td>합계: $data['st_price'] VAT별도 
                  "; 

         mysqli_query($q, $connect); 
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

   mail($email,"$name 님 제품 구매를 감사드립니다.",$body, $header); 
   $query  = "delete from shop_temp where session_id='$session_id' "; 
   mysqli_query($query, $connect); 
   mysqli_close($connect);

?> 
<script> 
location.href='order_completed.php'; 
</script>
?>