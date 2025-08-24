<?php session_start(); 
$session_id = session_id();
$HomeDir="../../";
include "../MlangPrintAuto/MlangPrintAutoTop.php";
include "../lib/func.php";
$connect = dbconn(); 
?>
<style>
  input {
    width: 150px; /* input의 가로 길이를 150px로 설정 */
  }
    
  select {
    width: 150px; /* 금박스티커 크기에 맞춰서 설정 (가로 길이) */
    font-size: 9pt;
  }

  select option {
    border: 1px solid black; /* 테두리 진하게 */
    padding: 5px; /* 옵션 간 간격 추가 */
  }
</style>
<style>
  td,input,select,a{font-size:9pt;}
  border{border-color:red;}
.bold {
	font-weight: bold;
	font-size: 9pt;
	font-family: "굴림";
}
.boldB {
	font-family: "굴림";
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
<!-- <script language="javascript"> //팝업창 숨김  
  self.name="open"; 
    window.open('/sub/popup_summer.htm','Remote','left=90,top=90,width=460,height=292,toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0'); </script> -->
<div align="center" class="center1"><br>
 
  <span class="style7"><strong>[사용법]</strong> 1번~6번을 선택 및 입력하시고 <strong>금액보기</strong>를 하신 뒤 <strong>주문하기 버튼</strong>을 사용하시기 바랍니다.</span> <br> 
<br> </div> 
<table width="600" align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5" font-color="#000000">
  <form action="./basket_post.php" method="post"> 
    <input type="hidden" name="no" value="<?=$no?>"> 

    <tr bgcolor="#E1E1FF" style="height: 35px;">
      <td width="100" bgcolor="#F5F5F5" class="boldB">      
        <div align="center">1.재질</div>
      <td width="500" bgcolor="#FFFFFF"><select name="jong" value="<?=$jong?>" style="height: 30px;" >
      <option value="jil 아트유광코팅">아트지유광코팅(90g)</option>
        <option value="jil 아트무광코팅">아트지무광코팅(90g)</option>
        <option value="jil 아트비코팅">아트지비코팅(90g)</option>
        <option value="jka 강접아트유광코팅">강접아트유광코팅(90g)</option>
        <option value="cka 초강접아트코팅">초강접아트유광코팅(90g)</option>
        <option value="cka 초강접아트비코팅">초강접아트비코팅(90g)</option>
        <option value="jsp 유포지">유포지(80g)</option>
        <option value="jsp 은데드롱">은데드롱(25g)</option>
        <option value="jsp 투명스티커">투명스티커(25g)</option>
        <option value="jil 모조비코팅">모조지비코팅(80g)</option>
        <option value="jsp 크라프트지">크라프트스티커(57g)</option>
        <option value="jsp 금지스티커">금지스티커-전화문의</option>
        <option value="jsp 금박스티커">금박스티커-전화문의</option>
        <option value="jsp 롤형스티커">롤스티커-전화문의</option>
      </select>
      <a href="#"><img src="img/m_view.jpg" width="110" height="23" border="0" align="absmiddle" onclick="MM_openBrWindow('material.php','material','scrollbars=yes,width=616,height=400')" /></a>
      <span class="style2">금지/금박/롤</span>  전화문의
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">2.가로</div></td>
      <td>
        <input type="text" name="garo" size="15" style="height: 30px;"> 
        mm
        <span class="style5">※</span>주문은 <span class="style2">5mm단위 이하는 도무송  적용</span></td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">3.세로</div></td>
      <td>
        <input type="text" name="sero" size="15" style="height: 30px;"> 
        mm
        <span class="style3"><strong>※</strong></span><span class="style2">가로, 세로가 50X60mm 이하는 도무송  적용</span></td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">4.매수</div></td>
      <td><select name="mesu" value="<?=$mesu?>" style="height: 30px;">
      <option value="500">500매</option> 
        <option value="1000">1000매</option>
        <option value="2000">2000매</option>
        <option value="3000">3000매</option>
        <option value="4000">4000매</option>
        <option value="5000">5000매</option>
        <option value="6000">6000매</option>
        <option value="7000">7000매</option>
        <option value="8000">8000매</option>
        <option value="9000">9000매</option>
        <option value="10000">10000매</option>
        <option value="20000">20000매</option>
        <option value="30000">30000매</option>
        <option value="40000">40000매</option>
        <option value="50000">50000매</option>
        <option value="60000">60000매</option>
        <option value="70000">70000매</option>
        <option value="80000">80000매</option>
        <option value="90000">90000매</option>
        <option value="100000">100000매</option>
      </select>
      <strong class="style3">※</strong> 
      <span class="style2">10,000매이상 </span>별도 견적 <span class="style3"><strong>※</strong></span><span class="style2"> 후지칼선 선택시 별도 비용</span>
      </td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">5.편집</div></td>
      <td>
        <select name="uhyung" style="height: 30px;">
          <option value="10000">디자인+인쇄</option>
          <option value="0">인쇄만</option>
        </select>
        단순 작업 외 <strong class="boldB">난이도</strong>에 따라 <span class="boldB">비용 협의</span>
      </td>
    </tr>

    <tr bgcolor="#FFFFFF" style="height: 35px;">
      <td bgcolor="#F5F5F5" class="boldB"><div align="center">6.모양</div></td>
      <td>
        <select name="domusong" style="height: 30px;">
          <option value="00000 사각">기본사각형</option>
          <option value="08000 사각도무송">사각도무송(50~60mm미만)</option>
          <option value="08000 귀돌">귀돌이(라운드)</option>
          <option value="08000 원형">원형</option>
          <option value="08000 타원">타원형</option>
          <option value="19000 복잡">모양도무송</option>
        </select>
        도무송 시 좌우상하밀림 현상 있습니다 (오차범위 1mm 이상)
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
    <td width="70" bgcolor="#F5F5F5"><span class="center1">재질
    </span>
    <td width="50" bgcolor="#F5F5F5"><span class="center1">가로(mm)
    </span>
    <td width="50" bgcolor="#F5F5F5"><span class="center1">세로(mm)
    </span>
    <td width="40" bgcolor="#F5F5F5"><span class="center1">매수(매)
    </span>
    <td width="70" bgcolor="#F5F5F5"><span class="center1">도무송<br>
      (타입)	
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">도안비
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">금액
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">부가세포함
    </span>
    <td bgcolor="#F5F5F5"><span class="center1">기타



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
    <td><span class="center1"><a href=del.php?no=<?=$data[no]?> onclick="return confirm('정말 삭제할까요?');">삭제</a>
	
    </span>
</table>
<span class="center1"><br>
</span>
<div align="center" class="center1">
  <p><span class="style7"><span class="style8">택배비는착불</span>입니다. 모든 작업은 <strong>입금 후에 진행</strong>됩니다. <br />
    </span><span class="style7"><a href="./basket.php"><img src="img/order.gif" width="99" height="31" border="0" /></a><br>
    <br>
    주문하기를 누르시면<span class="style8"> 파일올리기</span>가 맨아래에 보입니다.</span></p>
</div>
 <span class="center1">
<?    
         // $now=time();
	       // 60*60*24; 
         // if($now-$data[3]<86400) echo "<span style='font-size:8pt; color:#ff0000'><img src=img/newicon.gif></span>";  
 ?>
	 <p align="center"><img src="../MlangPrintAuto/img/dechre1.png" width="601" height="872" alt=""/></p>
<?
include"../MlangPrintAuto/DhtmlText.php";
?>
<?
include"../MlangPrintAuto/MlangPrintAutoDown.php";
?>
 </span>
<script type="text/javascript">
$(function() {
	$( "#Progressbar1" ).progressbar(); 
});
 </script>
