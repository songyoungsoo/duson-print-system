<?php
// 관리자가 확인할 경우에만 보임

if(isset($_COOKIE["adminVar"]) && isset($no)){
    include("./dbConn.inc");
    $sql = "SELECT * FROM orderDB WHERE no='$no'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);
    
    $kind = $row["kind"];
    $paper = $row["paper"];
    $size = $row["size"];
    $cnt = $row["cnt"];
    $coating = $row["coating"];
    $reorder = $row["reorder"];
    $printer = $row["printer"];
    $edit = $row["edit"];
    $design = $row["design"];
    $price = $row["price"];
    $num = $row["num"];
    $total = $row["total"];
    $ordername = $row["name"];
    $company = $row["company"];
    $tel = explode("-", $row["tel"]);
    $fax = explode("-", $row["fax"]);
    $cel = explode("-", $row["cell"]);
    $email = $row["email"];
    $memo = $row["memo"];
    $atch1 = $row["file1"];
    $atch2 = $row["file2"];    
    $atch3 = $row["file3"];
    $atch4 = $row["file4"];
    $atch5 = $row["file5"];
    $wdate = $row["ordDate"];
    $viewCheck = $row["viewCheck"];
}

?>

<html>
<head>
<title>카다로그</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<script language="JavaScript" src="./javascript6.js"></script>
<script language="JavaScript" src="./selScript6.js"></script>
<style type="text/css">
  
td {font-size: 12px;}
th {font-size: 12px;}
body{font-size: 12px;}
input {font-size: 12px;}
select {font-size: 12px;}
textarea {font-size: 12px;}

</style>
</head>

<body>
<form action="estimate_auto_PC.php" method="post" name="ordform" id="ordform" enctype="multipart/form-data" onSubmit="return  formChk()">
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr bgcolor="#D0A4DF"> 
          <td height="2" colspan="2"></td>
        </tr>
        <tr> 
          <td width="122" height="25" bgcolor="#F5F5F5"><div align="center"><strong><font color="#A052A0">상품종류선택</font></strong></div></td>
          <td width="567" bgcolor="#FFFFFF"> &nbsp; <input name="kind" type="radio" value="1"  onClick="location.href='?pg=1'">
            명함 
            <input type="radio" name="kind" value="2" onClick="location.href='?pg=2'">
            스티커 
            <input type="radio" name="kind" value="3" onClick="location.href='?pg=3'">
            전단 
            <input type="radio" name="kind" value="4" onClick="location.href='?pg=4'">
            서식지 
            <input type="radio" name="kind" value="5" onClick="location.href='?pg=5'">
            봉투 
            <input name="kind" type="radio" value="6" checked>
        <b>카다로그</b></td>
        </tr>
      </table>
      <br>
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr bgcolor="#D0A4DF"> 
          <td height="2"></td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="left"><strong><font color="#A052A0">&nbsp;&nbsp;&nbsp;&nbsp;상품종류선택</font></strong></div></td>
        </tr>
      </table>
      <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="5"></td>
        </tr>
      </table>
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr> 
          <td width="124" height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">용지</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <select name="paper" id="paper" onChange="selPaper();viewCnt();">
          <option>▒ 용지를 선택해 주세요 ▒</option>
          <option value="1">카다로그 - A4 / 120g / 아트지,스노우지 </option>
          <option value="2">카다로그 - A4 / 150g / 아트지,스노우지</option>
          <option value="3">카다로그 - A4 / 180g / 아트지,스노우지</option>
          <option value="4">카다로그 - A4 / 200g / 아트지, 스노우지</option>
          <option value="5">리플렛 - A4 / 4P / 2단</option>
          <option value="6">리플렛 - A4 / 6P / 3단</option>
          <option value="7">포스터</option>
        </select> </td>
        </tr>
		<tr>
      <td height="0" colspan=2>
        <!--규격메뉴 숨김 -->
        <div  id="sizeA" style="visibility:hidden;position:absolute;"> 
          <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr> 
              <td width="123" height="25" align="center" bgcolor="#F5F5F5"><font color="#666666"> 
                규격 </font></td>
              <td width="1"></td>
              <td  bgcolor="#FFFFFF"> &nbsp; <select name="size" id="size"  onChange="calc()">
                  <option value="1">국 2절 (423*597) 단면</option>
                  <option value="2">4*6 2절 (737*517) 단면</option>
                </select> </td>
            </tr>
          </table>
        </div>
    </tr>		
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">구분</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <select name="gubun" id="gubun" onChange="calc()">
          <option>▒ 구분 ▒</option>
        </select>
        <input name="gubunchk" type="hidden" id="gubunchk"> </td>
        </tr>     
		<tr> 
			
      <td  height="25" align="center" bgcolor="#F5F5F5"><font color="#666666"> 
        표지코팅(단면) </font></td>
          <td  bgcolor="#FFFFFF"> &nbsp; <select name="coating" id="coating">
          <option>▒ 코팅 여부를 선택하세요 ▒</option>
          <option value="1">유광</option>
          <option value="2">무광</option>
        </select> </td>
        </tr>
		<tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">수량</font></div></td>
          
      <td width="568" height="25" bgcolor="#FFFFFF"> 
        <div id='cntA0' style='visibility:visible;'>
			 &nbsp; <select name="cnt" id="cnt" onChange="calc();">
              <option>▒ 수량 ▒</option>
            </select>
          <input name="cntchk" type="hidden" id="cntchk">
        </div>
            </td>
        </tr>
        
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">가격</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="price" type="text" id="price" style="text-align:right">
        원</td>
        </tr>
      </table>
      
  <table width="692" border="0" cellspacing="0" cellpadding="0">
    <tr> 
      <td height="5"></td>
    </tr>
  </table>
  <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
    <tr> 
      <td height="25" bgcolor="#F5F5F5"> <div align="right"><strong><font color="#FF6600">합계금액</font> 
          <input name="total" type="text" id="total"  style="text-align:right;">
          <font color="#666666">원</font>&nbsp;&nbsp;</strong></div></td>
    </tr>
  </table>
  <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="5"></td>
        </tr>
      </table>
      
  <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
    <tr> 
      <td height="25" bgcolor="#FFFFFF"><div align="left"> <font color="#666666">&nbsp; 
          <input name="reorder" type="checkbox" id="reorder" value="Y">
          재주문 <font color="#FF6600">(디자인 비용 없습니다)</font></font></div></td>
    </tr>
    <tr> 
      <td height="40" bgcolor="#FFFFFF"><div align="left"> <font color="#666666">&nbsp; 
          <input name="printer" type="checkbox" id="printer" value="Y">
          인쇄만 의뢰 <font color="#FF6600">(디자인 비용 없습니다)</font></font> <font color="#666666"><br>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 인쇄만 의뢰시 파일첨부하여 주시면 
          인쇄할 수 있는 파일인지 확인 후 작업 진행됩니다.</font></div></td>
    </tr>
    <tr> 
      <td height="25" bgcolor="#FFFFFF"><div align="left"> <font color="#666666">&nbsp; 
          <input type="radio" name="design" value="a">
          카다로그, 브로슈어 디자인 의뢰 : 50,000원 (페이지당)</font></div></td>
    </tr>
    <tr> 
      <td height="25" bgcolor="#FFFFFF"><div align="left"> <font color="#666666">&nbsp; 
          <input type="radio" name="design" value="b">
          3단 리플렛 디자인 의뢰 : 150,000원</font></div></td>
    </tr>
    <tr> 
      <td height="25" bgcolor="#FFFFFF"><div align="left"> <font color="#666666">&nbsp; 
          <input name="design" type="radio" value="c">
          포스터 디자인 의뢰 : 150,000원 
          <input name="designClick" type="hidden" id="designClick" value="N">
          </font></div></td>
    </tr>
    <tr> 
      <td height="40" bgcolor="#FFFFFF"><font color="#666666">&nbsp;&nbsp;&nbsp;※ 
        일반적인 구성일 경우의 디자인 비용입니다.<br>
        &nbsp;&nbsp;&nbsp;※ 기획과 프로젝트, 사진촬영, 고퀄리티 등이 필요한 경우는 별도로 견적 의뢰하시기 바랍니다.</font></td>
    </tr>
  </table>
      
  <br>
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr bgcolor="#D0A4DF"> 
          <td height="2"></td>
        </tr>
        <tr> 
          <td height="24" bgcolor="#F5F5F5"><div align="left"><strong><font color="#A052A0">&nbsp;&nbsp;&nbsp;&nbsp;주문자 
              정보입력 </font></strong><font color="#666666">[상품을 받아보실분의 정보를 입력하세요]</font></div></td>
        </tr>
      </table>
      <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="5"></td>
        </tr>
      </table>
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr> 
          <td width="124" height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">주문자</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <input name="ordername" type="text" id="ordername"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">회사명</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <input name="company" type="text" id="company"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">전화</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <input name="tel1" type="text" id="tel1" size="6" maxlength="3">
            - 
            <input name="tel2" type="text" id="tel2" size="6" maxlength="4">
            - 
            <input name="tel3" type="text" id="tel3" size="6" maxlength="4"> </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">팩스</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <input name="fax1" type="text" id="fax1" size="6" maxlength="3">
            - 
            <input name="fax2" type="text" id="fax2" size="6" maxlength="4">
            - 
            <input name="fax3" type="text" id="fax3" size="6" maxlength="4"> &nbsp;(견적서를 받아보시길 
            원하시면 입력해 주세요) </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">핸드폰</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="cel1" type="text" id="cel1" size="6" maxlength="3">
            - 
            <input name="cel2" type="text" id="cel2" size="6" maxlength="4">
            - 
            <input name="cel3" type="text" id="cel3" size="6" maxlength="4"></td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">이메일</font></div></td>
          <td width="568" bgcolor="#FFFFFF"> &nbsp; <input name="email" type="text" id="email"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">메모</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <textarea name="memo" cols="60" rows="7" wrap="VIRTUAL" id="memo"><? if($HTTP_COOKIE_VARS["adminVar"] && $no){ echo $memo; } ?></textarea> 
          </td>
        </tr>
<?	//관리자일때 주문일자를 넣음
if ($HTTP_COOKIE_VARS["adminVar"] && $no){ 
		echo "<tr> "; 
        echo "  <td height='25' bgcolor='#F5F5F5'><div align='center'><font color='#666666'>주문일자</font></div></td>";
        echo "  <td bgcolor='#FFFFFF'> &nbsp; <input type='text' name='wdate' value=$wdate>";
        echo "  </td></tr> ";
} ?>
      </table>
      <br>
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr bgcolor="#D0A4DF"> 
          <td height="2"></td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="left"><strong><font color="#A052A0">&nbsp;&nbsp;&nbsp;&nbsp;첨부파일 
              </font></strong><font color="#666666">[견적의뢰시 필요한 파일을 올려주세요 / 10M 
              이상의 파일은 웹하드로 올려주세요]</font></div></td>
        </tr>
      </table>
      <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="5"></td>
        </tr>
      </table>

<? if (!$HTTP_COOKIE_VARS["adminVar"] || !$no){ 
	// 관리자가 아닐때 ?>
	<table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr> 
          <td width="124" height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">첨부파일1</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="atch1" type="file" size="40"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">첨부파일2</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="atch2" type="file" size="40"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">첨부파일3</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="atch3" type="file" size="40"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">첨부파일4</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="atch4" type="file" size="40"> 
          </td>
        </tr>
        <tr> 
          <td height="25" bgcolor="#F5F5F5"><div align="center"><font color="#666666">첨부파일5</font></div></td>
          <td bgcolor="#FFFFFF"> &nbsp; <input name="atch5" type="file" size="40"> 
          </td>
        </tr>
      </table>
      <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="60"><div align="center"><input type="image" src="../img/bu_order.gif" width="93" height="32"> 
              &nbsp;<a href='javascript:document.ordform.reset();document.ordform.paper.focus();'><img src="../img/bu_again.gif" width="93" height="32" border=0></a> </div></td>
        </tr>
      </table>
<? } 
else { 
	echo "<table width=692><tr><td width=5></td><td>";
	// 관리자 일때
	$upDir ="/upOrder/$kind/";	
	for ($i=1;$i<=5;$i++){
		$file = "atch".$i;
		if($$file != ""){
			if(strtolower(trim(substr($$file,-3)))=="gif" || strtolower(trim(substr($$file,-3)))=="jpg" || strtolower(trim(substr($$file,-3)))=="bmp"){
				echo "첨부파일 $i : ".$$file."<br><img src='$upDir".$$file."'><br><br>";
			}
			else{
				echo "첨부파일 $i : <a href='$upDir".$$file."' target='_blank'>".$$file."</a><br>";
			}
		}
	}
	echo "</td></tr></table>";
	echo "<table width=692><tr bgcolor=#D0A4DF><td height=2></td></tr>";
	echo "<tr><td align=center><input type=button name=button1 value=확인 onClick=\"location.href='estimate_auto_PC.php?no=$no'\">&nbsp;<input type=button value=삭제 onclick='javascript:del()'>&nbsp;<input type=button value=시안넣기 onClick='javascript:design();'>&nbsp; <input type=button value=닫기 onClick='self.close()'></td></tr></table>";
} ?>
    </form>

<? if($HTTP_COOKIE_VARS["adminVar"] && $no){ ?>
	<script language="JavaScript">
		f = document.ordform;
	//상품종류
	for (i=0;i<f.kind.length;i++){
		if (f.kind[i].value == "<?=$kind?>"){
			f.kind[i].checked = true;
			break;
		}
	}
	// 용지	
	for (i=0;i<f.paper.length;i++){
		if (f.paper.options[i].value=="<?=$paper?>"){
			f.paper.options[i].selected = true;
			selPaper();
			break;
		}
	}
	//규격
	for (i=0;i<f.size.length;i++ ){
		if (f.size.options[i].value=="<?=$size?>"){
			f.size.options[i].selected = true;
			break;
		}
	}
	//구분
	for (i=0;i<f.gubun.length;i++ ){
		if(f.gubun.options[i].index=="<?=$gubun?>"){
			f.gubun.options[i].selected = true;
			break;
		}
	}

	//코팅
	for (i=0;i<f.coating.length;i++ ){
		if(f.coating.options[i].value=="<?=$coating?>"){
			f.coating.options[i].selected = true;
			break;
		}
	}
	//수량
	viewCnt();
	for (i=0;i<f.cnt.length;i++ ){
		if(f.cnt.options[i].index=="<?=$cnt?>"){
			f.cnt.options[i].selected = true;
			break;
		}
	}
	//가격
	f.price.value="<?=$price?>";


	if("<?=$reorder?>"=="Y") f.reorder.checked = true;
	if("<?=$printer?>"=="Y") f.printer.checked = true;
	for (i=0;i<f.design.length ;i++ ){
		if(f.design[i].value=="<?=$design?>"){
			f.design[i].checked = true;
		}
	}
	f.total.value="<?=$total?>";

	f.ordername.value="<?=$ordername?>";
	f.company.value="<?=$company?>";
	f.tel1.value="<?=$tel[0]?>";f.tel2.value="<?=$tel[1]?>";f.tel3.value="<?=$tel[2]?>";
	f.fax1.value="<?=$fax[0]?>";f.fax2.value="<?=$fax[1]?>";f.fax3.value="<?=$fax[2]?>";
	f.cel1.value="<?=$cel[0]?>";f.cel2.value="<?=$cel[1]?>";f.cel3.value="<?=$cel[2]?>";
	f.email.value="<?=$email?>";
	//메모는 줄바꿈 관계로 html태그에 직접 입력

	function del(){
		result=confirm("정말로 삭제하시겠습니까?");
		if(result)	location.href="estimate_auto_PC.php?del=<?=$no?>";
	}

	function design(){
		location.href="/admin/design.php?direct=Y&kind=<?=$kind?>&ordername=<?=$ordername?>&company=<?=$company?>";
	}
</script>
<? } ?>
</body>
</html>