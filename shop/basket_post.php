<?php
header("Content-Type:text/html; charset=UTF-8"); 
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
	if(!$garo) assa('가로사이즈를 입력하세요');
	if(!$sero) assa('세로사이즈를 입력하세요');
	// if($garo<=50) assa('가로사이즈를 50mm이하는 도무송을 선택해야합니다');
	// if ($garo <= 50 && $domusong == '00000 사각') {
	// 	assa('가로사이즈가 50mm 이하일 경우, 도무송을 선택해야 합니다.');
	// }
	// if ($sero <= 50 && $domusong == '00000 사각') {
	// 	assa('가로사이즈가 50mm 이하일 경우, 도무송을 선택해야 합니다.');
	// }
	if (
		($garo < 50 || $sero < 60) &&    // 가로가 50 미만이거나 세로가 60 미만
		($garo < 60 || $sero < 50) &&    // 가로가 60 미만이거나 세로가 50 미만
		$domusong == '00000 사각'       // 도무송 옵션이 '00000 사각' 
	) {
		assa('가로,세로사이즈가 50mmx60mm 미만일 경우, 도무송을 선택해야 합니다.');
	}
	// if($sero<=50) assa('세로사이즈를 50mm이하는 도무송을 선택해야합니다');
	if($garo>590) assa('가로사이즈를 590mm이하만 입력할 수 있습니다');
	if($sero>590) assa('세로사이즈를 590mm이하만 입력할 수 있습니다');
	if(($garo*$sero)>250000 && $mesu>5000) assa('500mm이상 대형사이즈를 5000매이상 주문은 전화요청바랍니다');
      function mesu($msg1){
    echo "
	<script>
	  window.alert('$msg1');
	  history.back(1);
	</script>
	";
	exit;
   }	
	if(10000<$mesu) mesu('1만매 이상은 할인가 적용-전화주시기바랍니다');
		
include "../lib/func.php"; 
   $connect = dbconn();
   $regdate = time();
   $ab=$mesu;
   $gase=$garo*$sero;
   $j= substr($jong,4,10); //jsp 투명
echo $j;

   $j1= substr($jong,0,3); //jsp 투명
   $d = substr($domusong,6,8); //08000 원형
   $d1= substr($domusong,0,5); //08000 원형 
echo $j1;
//exit;
     function assa2($msg){
    echo "
	<script>
	  window.alert('$msg');
	  history.back(1);
	</script>
	";
	exit;
   }	
	if($j=='금지스티커') assa2('금지스티커는 전화 또는 메일로 견적 문의하세요');
	if($j=='금박스티커') assa2('금박스티커는 전화 또는 메일로 견적 문의하세요');
	if($j=='롤형스티커') assa2('롤스티커는 전화 또는 메일로 견적 문의하세요');		
	// if($j=='크라프트지') assa2('크라프트스티커는 전화 또는 메일로 견적 문의하세요');
if ($j1 == 'jil') {   
    $query  = "SELECT * FROM shop_d1"; 
    $result = mysqli_query($connect, $query); 
    $data = mysqli_fetch_array($result); 
} else if ($j1 == 'jka') {   
    $query  = "SELECT * FROM shop_d2"; 
    $result = mysqli_query($connect, $query); 
    $data = mysqli_fetch_array($result); 
} else if ($j1 == 'jsp') {   
    $query  = "SELECT * FROM shop_d3"; 
    $result = mysqli_query($connect, $query); 
    $data = mysqli_fetch_array($result); 
} else if ($j1 == 'cka') {   
    $query  = "SELECT * FROM shop_d4"; 
    $result = mysqli_query($connect, $query); 
    $data = mysqli_fetch_array($result); 
}


    if($ab<=1000){
	   $yoyo=$data[0];//0.15
	   $mg=7000;//이전은15000이었음
	   } else
	if($ab>1000 and $ab<=4000){
	   $yoyo=$data[1];//0.15
	   $mg=6500;
	   } else
	if($ab>4000 and $ab<=5000){
	   $yoyo=$data[2];//0.14
	   $mg=6500;
	   } else
	if($ab>5000 and $ab<=9000){
	   $yoyo=$data[3];//0.13
	   $mg=6000;
	   } else
	if($ab>9000 and $ab<=10000){
	   $yoyo=$data[4];//0.12
	   $mg=5500;
	   }else
	if($ab>10000 and $ab<=50000){
	   $yoyo=$data[5];//0.11
	   $mg=5000;
	   } else 	   	   
	if($ab>50000){
	   $yoyo=$data[6];
	   $mg=5000;//0.11_yoyo는 요율
	   }

//	 if($garo<=49 || $sero<=49){//가로나 세로 사이즈 50mm이하는 톰슨비7000원추가
//	    $add=8000;//이전은5000이었음
//	    }

	// if (($garo < 50 || $sero < 60) && ($garo < 60 || $sero < 50)){ //사이즈에 따른 강제 도무송비용
	// 	$add = 8000; 
	// }
//	if(($garo%5)==0 and ($sero%5)==0){//나눈 몫의 나머지가 0으로 떨어지지 않으면
//	   $add1=0;
//	   }
//	   
//	if(($garo%5)>0 || ($sero%5)>0){
//	   $add1=8000;//이전은5000이었음
//	   }   
	   
	   
    // if($d1>0){//도무송이 있으면 귀돌이 면제
	//    $add=0;
    //    $add1=0;
	//    }

//	if(($add1+$add)==16000){//50mm이하와 5mm이하가 중복이면 한번만 추가하도록하기위해서...
//	   $add1=0;	   
//	  } 	
	   
	if($j1=='jsp' || $j1=='jka' ||  $j1=='cka'){//재질이 특수지나 강접이나 초강접 인경우 톰슨비용추가
	   $ts=14;//이전은6이었음
	   }   
	   
	if($j1=='jil'){//재질일반 경우 톰슨비용가격
	   $ts=9;//이전은6이었음
	   }
	
	if($garo>=$sero){
		     $d2=$garo;
	   }else{
		     $d2=$sero;
	   }//도무송칼의크기변화 가로나 세로 긴쪽 길이를 구함
	   
	    
    if($gase<=18000){
	   $gase=1;
	  }
	if($gase>18000){
	   $gase=1.25;
	  }//큰사이즈작은사이즈보다 25%마진비율 
//톰슨칼+톰슨비용-이전은5 d1=도무송기본가격
if ($d1 > 0 && $mesu == 500) {
    $d1 = (($d1 + ($d2 * 20)) * 900 / 1000) + (900 * $ts);
} elseif ($d1 > 0 && $mesu == 1000) {
    $d1 = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
} elseif ($d1 > 0 && $mesu > 1000) {
    $d1 = (($d1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
} else {
    $d1 = 0;
}
//   if($d1>0 & $mesu=1000){
//	  $d1 = ($d1+($d2*20))*$mesu/1000+($mesu*$ts);
//   }else{
//	   $d1 = 0;
//   }
//톰슨칼+톰슨비용-50mm이하일때(크기가 작을 때)
if ($add > 0 && $mesu == 500) {
    $add = (($add* 900 / 1000 + ($d2 * 20)) * 900 / 1000) + (900 * $ts);
} elseif ($add > 0 && $mesu == 1000) {
    $add = (($add* $mesu / 1000 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
} elseif ($add > 0 && $mesu > 1000) {
    $$add = (($add* $mesu / 1000 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
} else {
    $add = 0;
}
//   if($add>0){
//	  $add = ($add+($d2*20))*$mesu/1000+($mesu*$ts);
//   }else{
//	   $add = 0;
//   }
//톰슨칼+톰슨비용-5mm이하일때(치수가 5단위 이하일때)
//if ($add1 > 0 && $mesu == 1000) {
//    $add1 = (($add1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * $ts);
//} elseif ($add1 > 0 && $mesu > 1000) {
//    $add1 = (($add1 + ($d2 * 20)) * $mesu / 1000) + ($mesu * ($ts / 9));
//} else {
//    $add1 = 0;
//}
//  if($add1>0){
//	  $add1 = ($add1+($d2*20))*$mesu/1000+($mesu*$ts);
//   }else{
//	   $add1 = 0;
//   }
//특수용지기본비용   

   if($j1=='jsp' && $mesu == 500 ){
	$jsp = 10000*($mesu + 400)/1000;
    } elseif ($j1=='jsp' && $mesu > 500 ) {
	$jsp = 10000*$mesu/1000;
	} else{
	 $jsp = 0;
    }
//강접용지기본비용
   if($j1=='jka' && $mesu == 500 ){
	$jka = 4000*($mesu + 400)/1000;
   } elseif ($j1=='jka' && $mesu > 500 ) {
	$jka = 10000*$mesu/1000;
	}else{
	 $jka = 0;
    }
//초강접용지기본비용
	if($j1=='cka' && $mesu == 500 ){
		$cka = 4000*($mesu + 400)/1000;
	   } elseif ($j1=='cka' && $mesu > 500 ) {
		$cka = 10000*$mesu/1000;
		}else{
		 $cka = 0;
		}
	//초강접용지기본비용
	// if($j1=='cka'){
	// 	$cka = 6000*$mesu/1000;
	// 	}else{
	// 	 $cka = 0;
	// 	}
   if($mesu==500){
   $s_price=(($garo+4)*($sero+4)*($mesu+400))*$yoyo+$jsp+$jka+$cka+$d1+$add+$add1;//전체비용계산500일 경우 매수에 400을
   $st_price=round($s_price*$gase,-3)+$uhyung+($mg*($mesu+400)/1000); 
   $st_price_vat=$st_price*1.1;
   //가로x세로x매수*요율+특수용지+강접스티커+도무송+50mm이하/5mm이하일경우 추가 
	} else {
   $s_price=(($garo+4)*($sero+4)*$mesu)*$yoyo+$jsp+$jka+$cka+$d1+$add+$add1;//전체비용계산
   $st_price=round($s_price*$gase,-3)+$uhyung+($mg*$mesu/1000);	//가로x세로x매수*요율+특수용지+강접스티커+도무송+50mm이하/5mm이하일경우 추가 
   $st_price_vat=$st_price*1.1;	
	}
echo "
	(($garo+4)*($sero+4)*$mesu)*$yoyo+$jsp+$jka+$cka+$d1+$add+$add1";
	
   $query = "insert into shop_temp(session_id,parent,jong,garo,sero,mesu,domusong,uhyung,st_price,st_price_vat,regdate)
         values('$session_id','$no','$jong','$garo','$sero','$mesu','$domusong','$uhyung','$st_price','$st_price_vat','$regdate')";
   mysqli_query($connect, $query);
   mysqli_close($connect);  
  
  ?> 
<script>
  location.href="jumun.php";
</script>


