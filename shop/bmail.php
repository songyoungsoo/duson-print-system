<? 
   session_start(); 
   $session_id = session_id();
include "../lib/func.php"; 
$connect = dbconn(); 
?>
<style type="text/css">
.boldB {
	font-family: "돋움";
	font-size: 9pt;
	font-weight: bold;
	color: #06F;
}
</style>
<div align="center">
    <li> 주문내역입니다. <br>
    <table width="600"  align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5">
      <tr  align="center" bgcolor="#E1E1FF"">
        <td width="20">NO
        <td width="50">재질
        <td width="50">가로(mm)
        <td width="50">세로(mm)
        <td width="40">매수(매)
        <td width="70">도무송<br>(타입)	
        <td>도안비
        <td>금액
        <td>부가세포함
        <td>기타
  <? 
  $query = "select * from shop_temp where session_id='$session_id' "; 
  $result = mysql_query($query,$connect); 
  $total = 0; 
  while($data= mysql_fetch_array($result)){ 
?> 
      <tr align="center" bgcolor="#FFFFFF">
        <?php $j = substr($data[jong],4,4);?>
        <?php $j1= substr($data[jong],0,3);?>
        <?php $d = substr($data[domusong],6,4);?>
        <?php $d1= substr($data[domusong],0,5);?>
        <td><?php echo $data[no]?>
        <td><?php echo $j?>   
        <td><?php echo $data[garo]?>    
        <td><?php echo $data[sero]?>
        <td><?php echo $data[mesu]?>
        <td><?php echo $d?>
        <td><?php echo $data[uhyung]?>
        <td> <strong><?php echo $data[st_price]?> </strong>
        <td><strong>
        <?php echo $data[st_price_vat]?>
        </strong>
        <td><a href=del_b.php?no=<?php echo $data[no]?> onclick="return confirm('정말 삭제할까요?');">삭제</a>
  <? 
   $total += $data[st_price]; 
  } 
?>	

  <tr bgcolor="#CCCCFF"> 
    <td bgcolor="#DDECDD"> 합계 
    <td colspan="6" bgcolor="#DDECDD">
    <td bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total)?> 
    </strong>
    <td colspan="2" bgcolor="#DDECDD"><strong>￦
        <?php echo number_format($total*1.1)?>
        </strong>
    </table> 
  </div>