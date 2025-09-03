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
	if(!$garo) assa('가로사이즈를 입력하세요');
	if(!$sero) assa('세로사이즈를 입력하세요');	

		
include "../lib/func.php"; 
   $connect = dbconn();
   $regdate = time();
   $ab=$mesu;
   
   $j= substr($jong,4,4);
   $j1= substr($jong,0,3);
   $d = substr($domusong,6,4);
   $d1= substr($domusong,0,5);   

if($j1==jil){   
   $query  = "select * from shop_data1"; 
   $result =mysqli_query($query, $connect); 
   $data = mysqli_fetch_array($result); 
    }
	else if($j1==jka){   
   $query  = "select * from shop_data2"; 
   $result =mysqli_query($query, $connect); 
   $data = mysqli_fetch_array($result); 
    }
	else if($j1==jsp){   
   $query  = "select * from shop_data3"; 
   $result =mysqli_query($query, $connect); 
   $data = mysqli_fetch_array($result); 
    }

    if($ab<=1000){
	   $yoyo=$data[s1];//0.15
	   $mg=19000;
	   } else
	if($ab>1000 and $ab<=4000){
	   $yoyo=$data[s2];//0.15
	   $mg=18000;
	   } else
	if($ab>4000 and $ab<=5000){
	   $yoyo=$data[s3];//0.14
	   $mg=17000;
	   } else
	if($ab>5000 and $ab<=9000){
	   $yoyo=$data[s4];//0.13
	   $mg=16000;
	   } else
	if($ab>9000 and $ab<=10000){
	   $yoyo=$data[s5];//0.12
	   $mg=15000;
	   }else
	if($ab>10000 and $ab<=50000){
	   $yoyo=$data[s6];//0.11
	   $mg=14000;
	   } else 	   	   
	if($ab>50000){
	   $yoyo=$data[s7];
	   $mg=13000;//0.11_yoyo는 요율
	   } 
   
   $st_price=($garo*$sero*$mesu)*$yoyo+($d1*$mesu/1000)+($j1==jsp)*20000;
   $st_price=round($st_price,-3)+$uhyung+($mg*$mesu/1000);
   $st_price_vat=$st_price*1.1;
	 
   $query = "insert into shop_temp(session_id,parent,jong,garo,sero,mesu,domusong,uhyung,st_price,st_price_vat,regdate)
         values('$session_id','$no','$jong','$garo','$sero','$mesu','$domusong','$uhyung','$st_price','$st_price_vat','$regdate')";
   mysqli_query($query,$connect);
   	 
?>
<script>
  location.href="view.php";
</script>


