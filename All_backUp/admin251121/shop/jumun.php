<?php
   session_start(); 
   $session_id = session_id();
$HomeDir="../../";
include "../mlangprintauto/mlangprintautotop.php";
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
  td,input,select,a{font-size:9pt}
  border{border-color:red}
.bold {
	font-weight: bold;
	font-family: "돋움";
	font-size: 9pt;
	text-decoration: blink;
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
.style2 {	color: #0066FF;
	font-weight: bold;
}
.style5 {color: #FF0000; font-weight: bold; }
.style3 {color: #FF0000}
.style7 {color: #666666}
.style8 {	color: #0033FF;
	font-weight: bold;
}
</style>
<div align="center">
    <br>
    <span class="style7"><strong>[사용법]</strong> 1번~6번을 선택 및 입력하시고 <strong>금액보기</strong>를 하신 뒤 <strong>주문하기 버튼</strong>을 사용하시기 바랍니다.</span><br> 
<br> </div> 
<table width="600"  align="center"  border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5"
 font-color="#000000">
<form action=basket_post.php method=post> 
  <input type=hidden name=no value='<?php echo $no?>'> 
  
  <tr bgcolor="#E1E1FF" style="height: 35px;" >
    <td  width="100" bgcolor="#F5F5F5">      
        <div align="center"><span class="boldB">1.재질</span>             </div>
    <td width="500" bgcolor="#FFFFFF">
        <select name="jong" value="<?php echo $jong?>" style="height: 30px;">
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
  아트지코팅과 비코팅은가격동일  
    <tr bgcolor="#FFFFFF" style="height: 35px;">
    <td  width="100" bgcolor="#F5F5F5" class="boldB"><div align="center">2.가로
    </div>
    <td><input type=text name=garo size=15 style="height: 30px;">       
    mm
      <span class="style5">※</span> 가로, 세로 <span class="style2">5mm단위 이하는 도무송  적용</span>
    <tr bgcolor="#FFFFFF" style="height: 35px;">    
    <td width="100" bgcolor="#F5F5F5" class="boldB"><div align="center">3.세로
    </div>
    <td width=500><input type=text name=sero size=15 style="height: 30px;">       
      mm
      <span class="style3"><strong>※ </strong></span><span class="style2">가로, 세로가 50X60mm 이하는 도무송  적용</span>
    <tr bgcolor="#FFFFFF" style="height: 35px;"> 
	<td width="100" bgcolor="#F5F5F5" class="boldB"><div align="center">4.매수
	  </div>
	<td width="500" >	
	  <select name="mesu" value="<?php echo $mesu?>" style="height: 30px;">
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
	  <option value="100000">100,000매</option>
	  </select>
	  <strong class="style3">※</strong> <span class="style2">10,000매이상</span>별도견적<span class="style3"><strong>※</strong></span><span class="style2">후지칼선선택시 별도비용</span>    
        <tr bgcolor="#FFFFFF" style="height: 35px;">
    <td width="100" bgcolor="#F5F5F5" class="boldB"><div align="center">5.편집
    </div>
    <td width="500" >
	<select name="uhyung" style="height: 30px;">
	  <option value="10000">디자인+인쇄 
	  <option value="0">인쇄만 
	  </select>
	단순작업외<span class="boldB"> 난이도</span> 따라 <span class="boldB">작업비 협의</span>
    <tr bgcolor="#FFFFFF" style="height: 35px;">     
	<td width="100" bgcolor="#F5F5F5"><div align="center"><span class="boldB">6.모양</span>
	  </div>
	<td width="500" >
	<select name="domusong" style="height: 30px;">
  <option value="00000 사각">기본사각형 </option>
  <option value="08000 사각도무송">사각도무송(50~60mm미만)</option>
    <option value="08000 귀돌">귀돌이(라운드) </option>
    <option value="08000 원형">원형 </option>
    <option value="08000 타원">타원형 </option>
<!--    <option value="13000 단순">모양(A)단순 </option> -->
    <option value="19000 복잡">모양도무송 </option>
  </select>
  (둥근사각,원,타원)선택
	,모양도무송은 난이도별 별도견적<tr bgcolor="#FFFFFF">
    <td align="center" colspan="2">
    <input name="submit" type="image" value= img src="img/estimate.gif" width="99" height="31" border="0" ></tr>
</form>	
</table>
<table width="600"  align="center" border="0" cellspacing="1" cellpadding="3" bgcolor="#c5c5c5">
  <tr  align="center" bgcolor="#CEE7FF">
    <td width="30" bgcolor="#F5F5F5">NO
    <td width="70" bgcolor="#F5F5F5">재질
    <td width="50" bgcolor="#F5F5F5">가로(mm)
    <td width="50" bgcolor="#F5F5F5">세로(mm)
    <td width="40" bgcolor="#F5F5F5">매수(매)
    <td width="70" bgcolor="#F5F5F5">도무송<br>
      (타입)	
    <td bgcolor="#F5F5F5">도안비
    <td bgcolor="#F5F5F5">금액
    <td bgcolor="#F5F5F5">부가세포함
    <td bgcolor="#F5F5F5">기타
<?php
//order by 필드명
  //limit [시작번호],갯수
 $query="select * from shop_temp where session_id='$session_id' order by no desc";  
$result = mysqli_query($connect, $query);
while ($data = mysqli_fetch_array($result)) {
?>
  <tr align="center" bgcolor="#FFFFFF" style="height: 35px;">
    <td><?php echo  $data['no'] ?></td>
    <td><?php echo  substr($data['jong'], 4, 12); ?></td>
    <td><?php echo  $data['garo'] ?></td>
    <td><?php echo  $data['sero'] ?></td>
    <td><?php echo  $data['mesu'] ?></td>
    <td><?php echo  substr($data['domusong'], 6, 8); ?></td>
    <td><?php echo  $data['uhyung'] ?></td>
    <td><span class="bold"><?php echo  $data['st_price'] ?></span></td>
    <td><span class="bold"><?php echo  $data['st_price_vat'] ?></span></td>
    <td><a href="del.php?no=<?php echo  $data['no'] ?>" onclick="return confirm('정말 삭제할까요?');">삭제</a></td>
  </tr>
<?php
}
?>
</table>
<br>
<div align="center">
  <p><a href=basket.php><img src="img/order.gif" width="99" height="31" border="0" /></a>        <br>
    <span class="style7"><span class="style8">택배비는착불</span>입니다. 모든 작업은 <strong>입금 후에 진행</strong>됩니다. <br />
    <br />
    주문하기를 누르시면<span class="style8"> 파일올리기</span>가 맨아래에 보입니다.</span></p>
</div>
 <?    
         // $now=time();
	       // 60*60*24; 
         // if($now-$data[3]<86400) echo "<span style='font-size:8pt; color:#ff0000'><img src=img/newicon.gif></span>";  
 ?>
	<p align="center"><img src="../mlangprintauto/img/dechre1.png" width="601" height="872" alt=""/></p>
<?php
include "../mlangprintauto/DhtmlText.php";
?>


<?php
include "../mlangprintauto/mlangprintautoDown.php";
?>
