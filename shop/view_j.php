<?  
   session_start(); 
   $session_id = session_id();
$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
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

<!-- <script language="javascript"> //팝업창 숨김  
  self.name="open"; 
    window.open('/sub/popup_summer.php','Remote','left=90,top=90,width=460,height=292,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0'); </script> -->
<div align="center" class="center1"><br>
 
  <span class="style7"><strong>[나의 주문내역] 주문시 </strong>이름과 <strong>비밀번호</strong>입력해주세요</span></div> 
<table width="600"  align="center"  border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5"
 font-color="#000000">
<form action=./order_view_3.php method=post> 
  
  <input type=hidden name=name value='<?php echo $name?>'>
  <input type=hidden name=pass value='<?php echo $pass?>'>
  <tr bgcolor="#FFFFFF">
    <td width="100" bgcolor="#F5F5F5" class="boldB"><div align="center">이름/상호
    </div>
    <td>
        <input type=text name=name size=25><tr bgcolor="#FFFFFF">    
    <td bgcolor="#F5F5F5" class="boldB"><div align="center">비밀번호</div>
    <td width=500>
        <input type=text name=pass size=15>
        <span class="style3"><strong></strong></span><span class="style2"> </span>
    <tr bgcolor="#FFFFFF">
      <td align="center" colspan="2"><span class="center1">
        <input name="submit" type="image" value= img src="img/estimate.gif" width="99" height="31" border="0" />
      </span>	</tr>
</form>	
</table>
<span class="center1"><br>
</span>

 <span class="center1">
<?    
         // $now=time();
	       // 60*60*24; 
         // if($now-$data[3]<86400) echo "<span style='font-size:8pt; color:#ff0000'><img src=img/newicon.gif></span>";  
 ?>
<?php
include "../MlangPrintAuto/DhtmlText.php";
?>
<?php
include "../MlangPrintAuto/MlangPrintAutoDown.php";
?>
 </span>