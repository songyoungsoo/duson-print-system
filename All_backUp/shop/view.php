<?php session_start(); 
$session_id = session_id();
$HomeDir="../../";
include "../mlangprintauto/mlangprintautotop.php";
include "../lib/func.php";
$connect = dbconn(); 
?>
<style>
  input {
    width: 150px; /* input�� ���� ���̸� 150px�� ���� */
  }
    
  select {
    width: 150px; /* �ݹڽ�ƼĿ ũ�⿡ ���缭 ���� (���� ����) */
    font-size: 9pt;
  }

  select option {
    border: 1px solid black; /* �׵θ� ���ϰ� */
    padding: 5px; /* �ɼ� �� ���� �߰� */
  }
</style>
<style>
  td,input,select,a{font-size:9pt;}
  border{border-color:red;}
.bold {
	font-weight: bold;
	font-size: 9pt;
	font-family: "����";
}
.boldB {
	font-family: "����";
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
<link href="jQueryAssets/jquery.ui.core.min.css" rel="stylesheet" type="text/css">
<link href="jQueryAssets/jquery.ui.theme.min.css" rel="stylesheet" type="text/css">
<link href="jQueryAssets/jquery.ui.progressbar.min.css" rel="stylesheet" type="text/css">
<script src="jQueryAssets/jquery-1.11.1.min.js"></script>
<script src="jQueryAssets/jquery.ui-1.10.4.progressbar.min.js"></script>
<script type="text/javascript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>
<!-- <script language="javascript"> //�˾�â ����  
  self.name="open"; 
    window.open('/sub/popup_summer.htm','Remote','left=90,top=90,width=460,height=292,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0'); </script> -->
<div align="center" class="center1"><br>
 
  <span class="style7"><strong>[����]</strong> 1��~6���� ���� �� �Է��Ͻð� <strong>�ݾ׺���</strong>�� �Ͻ� �� <strong>�ֹ��ϱ� ��ư</strong>�� ����Ͻñ� �ٶ��ϴ�.</span> <br> 
<br> </div> 
<table width="600" align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5" font-color="#000000">
  <form action="./basket_post.php" method="post"> 
    <input type="hidden" name="no" value="<?=$no?>"> 

    <tr bgcolor="#E1E1FF" style="height: 35px;">
      <td width="100" bgcolor="#F5F5F5" class="boldB">      
        <div align="center">1.����</div>
      <td width="500" bgcolor="#FFFFFF"><select name="jong" value="<?=$jong?>" style="height: 30px;" >
      <option value="jil ��Ʈ��������">��Ʈ����������(90g)</option>
        <option value="jil ��Ʈ��������">��Ʈ����������(90g)</option>
        <option value="jil ��Ʈ������">��Ʈ��������(90g)</option>
        <option value="jka ������Ʈ��������">������Ʈ��������(90g)</option>
        <option value="cka �ʰ�����Ʈ����">�ʰ�����Ʈ��������(90g)</option>
        <option value="cka �ʰ�����Ʈ������">�ʰ�����Ʈ������(90g)</option>
        <option value="jsp ������">������(80g)</option>
        <option value="jsp �������">�������(25g)</option>
        <option value="jsp ������ƼĿ">������ƼĿ(25g)</option>
        <option value="jil ����������">������������(80g)</option>
        <option value="jsp ũ����Ʈ��">ũ����Ʈ��ƼĿ(57g)</option>
        <option value="jsp ������ƼĿ">������ƼĿ-��ȭ����</option>
        <option value="jsp �ݹڽ�ƼĿ">�ݹڽ�ƼĿ-��ȭ����</option>
        <option value="jsp ������ƼĿ">�ѽ�ƼĿ-��ȭ����</option>
      </select>
      <a href="#"><img src="img/m_view.jpg" width="110" height="23" border="0" align="absmiddle" onclick="MM_openBrWindow('material.php','material','scrollbars=yes,width=616,height=400')" /></a>
      <span class="style2">����/�ݹ�/��</span>  ��ȭ����
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">2.����</div></td>
      <td>
        <input type="text" name="garo" size="15" style="height: 30px;"> 
        mm
        <span class="style5">��</span>�ֹ��� <span class="style2">5mm���� ���ϴ� ������  ����</span></td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">3.����</div></td>
      <td>
        <input type="text" name="sero" size="15" style="height: 30px;"> 
        mm
        <span class="style3"><strong>��</strong></span><span class="style2">����, ���ΰ� 50X60mm ���ϴ� ������  ����</span></td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">4.�ż�</div></td>
      <td><select name="mesu" value="<?=$mesu?>" style="height: 30px;">
      <option value="500">500��</option> 
        <option value="1000">1000��</option>
        <option value="2000">2000��</option>
        <option value="3000">3000��</option>
        <option value="4000">4000��</option>
        <option value="5000">5000��</option>
        <option value="6000">6000��</option>
        <option value="7000">7000��</option>
        <option value="8000">8000��</option>
        <option value="9000">9000��</option>
        <option value="10000">10000��</option>
        <option value="20000">20000��</option>
        <option value="30000">30000��</option>
        <option value="40000">40000��</option>
        <option value="50000">50000��</option>
        <option value="60000">60000��</option>
        <option value="70000">70000��</option>
        <option value="80000">80000��</option>
        <option value="90000">90000��</option>
        <option value="100000">100000��</option>
      </select>
      <strong class="style3">��</strong> 
      <span class="style2">10,000���̻� </span>���� ���� <span class="style3"><strong>��</strong></span><span class="style2"> ����Į�� ���ý� ���� ���</span>
      </td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">5.����</div></td>
      <td>
        <select name="uhyung" style="height: 30px;">
          <option value="10000">������+�μ�</option>
          <option value="0">�μ⸸</option>
        </select>
        �ܼ� �۾� �� <strong class="boldB">���̵�</strong>�� ���� <span class="boldB">��� ����</span>
      </td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">6.���</div></td>
      <td>
        <select name="domusong" style="height: 30px;">
          <option value="00000 �簢">�⺻�簢��</option>
          <option value="08000 �簢������">�簢������(50~60mm�̸�)</option>
          <option value="08000 �͵�">�͵���(����)</option>
          <option value="08000 ����">����</option>
          <option value="08000 Ÿ��">Ÿ����</option>
          <option value="19000 ����">��絵����</option>
        </select>
        ������ �� �¿���Ϲи� ���� �ֽ��ϴ� (�������� 1mm �̻�)
      </td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td align="center" colspan="2">
        <span class="center1">
          <input name="submit" type="image" value= img src="img/estimate.gif" width="99" height="31" border="0" />
        </span>
      </td>
    </tr>
  </form>	
</table>

<table width="600"  align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5">
  <tr  align="center" bgcolor="#99CCFF">
    <td width="30" bgcolor="#F5F5F5"><span class="center1">NO
    </span>
    <td width="70" bgcolor="#F5F5F5"><span class="center1">����
    </span>
    <td width="50" bgcolor="#F5F5F5"><span class="center1">����(mm)
    </span>
    <td width="50" bgcolor="#F5F5F5"><span class="center1">����(mm)
    </span>
    <td width="40" bgcolor="#F5F5F5"><span class="center1">�ż�(��)
    </span>
    <td width="70" bgcolor="#F5F5F5"><span class="center1">������<br>
      (Ÿ��)	
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">���Ⱥ�
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">�ݾ�
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">�ΰ�������
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">��Ÿ



    </span>
  <tr align="center" bgcolor="#FFFFFF" style="height: 35px;">

    <td><span class="center1">
      <?=$data[no]?>
    </span>
    <td><span class="center1">
    <?=substr($data[jong],4,12);?>   
    </span>
    <td><span class="center1">
    <?=$data[garo]?>    
    </span>
    <td><span class="center1">
    <?=$data[sero]?>
    </span>
    <td><span class="center1">
   <?=$data[mesu]; ?>
    </span>
    <td><span class="center1">
    <?=substr($data[domusong],6,8);?> 
	</span>
    <td><span class="center1">
    <?=$data[uhyung]?>
    </span>
    <td>
      <span class="center1">
      <?=$data[st_price]?>
      </span>
    <td><span class="center1">
    <?=$data[st_price_vat]?>
    </span>
    <td><span class="center1"><a href=del.php?no=<?=$data[no]?> onclick="return confirm('���� �����ұ��?');">����</a>
	
    </span>
</table>
<span class="center1"><br>
</span>
<div align="center" class="center1">
  <p><span class="style7"><span class="style8">�ù�������</span>�Դϴ�. ��� �۾��� <strong>�Ա� �Ŀ� ����</strong>�˴ϴ�. <br />
    </span><span class="style7"><a href="./basket.php"><img src="img/order.gif" width="99" height="31" border="0" /></a><br>
    <br>
    �ֹ��ϱ⸦ �����ø�<span class="style8"> ���Ͽø���</span>�� �ǾƷ��� ���Դϴ�.</span></p>
</div>
 <span class="center1">
<?    
         // $now=time();
	       // 60*60*24; 
         // if($now-$data[3]<86400) echo "<span style='font-size:8pt; color:#ff0000'><img src=img/newicon.gif></span>";  
 ?>
	 <p align="center"><img src="../mlangprintauto/img/dechre1.png" width="601" height="872" alt=""/></p>
<?
include"../mlangprintauto/DhtmlText.php";
?>
<?
include"../mlangprintauto/mlangprintautoDown.php";
?>
 </span>
<script type="text/javascript">
$(function() {
	$( "#Progressbar1" ).progressbar(); 
});
 </script>
