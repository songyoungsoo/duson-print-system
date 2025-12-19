<?  
   session_start(); 
   $session_id = session_id();
$HomeDir="../../";
include "../mlangprintauto/mlangprintautotop.php";
include "../lib/func.php"; 
$connect = dbconn(); 
?>

<style>
  td,input,select,a{font-size:9pt; border-color:#DAFEFB;}
  border{border-color:red;}
.bold {
	font-weight: bold;
	font-size: 9pt;
	font-family: "돋움";
}
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
a:link {
	font-weight: bold;
}
.center1 {
	text-align: center;
}
.style2 {
	color: #0066FF;
	font-weight: bold;
}
.style3 {color: #FF0000}
.style5 {color: #FF0000; font-weight: bold; }
.style7 {color: #666666}
.style8 {
	color: #0033FF;
	font-weight: bold;
}
</style>
<script type="text/javascript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>
<!-- <script language="javascript"> //팝업창 숨김  
  self.name="open"; 
    window.open('/sub/popup_summer.php','Remote','left=90,top=90,width=460,height=292,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0'); </script> -->
<div align="center" class="center1"><br>
 
  <span class="style7"><strong>[사용법]</strong> 1번~6번을 선택 및 입력하시고 <strong>금액보기</strong>를 하신 뒤 <strong>주문하기 버튼</strong>을 사용하시기 바랍니다.</span> <br> 
<br> </div> 
<table width="600"  align="center"  border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5"
 font-color="#000000">
<form action=./order_view_3.php method=post> 
  
  <input type=text name=name value='<?php echo $name?>'> 
  
  <tr bgcolor="#E1E1FF" >
    <td  width="100" bgcolor="#F5F5F5" class="boldB">      
      <div align="center">1.재질      </div>
    <td width="500" bgcolor="#FFFFFF"><select name="jong" value="<?php echo $jong?>">

<?    
         // $now=time();
	       // 60*60*24; 
         // if($now-$data[3]<86400) echo "<span style='font-size:8pt; color:#ff0000'><img src=img/newicon.gif></span>";  
 ?>
<?php
include "../mlangprintauto/DhtmlText.php";
?>
<?php
include "../mlangprintauto/mlangprintautoDown.php";
?>
 </span>