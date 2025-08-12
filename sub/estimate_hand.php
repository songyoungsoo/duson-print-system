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
<title>간편주문</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<script language="JavaScript" src="./javascript_hand.js"></script>
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
<form action="estimate_hand_PC.php" method="post" name="ordform" id="ordform" enctype="multipart/form-data" onSubmit="return  formChk()">
      <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
        <tr bgcolor="#D0A4DF"> 
          <td height="2" colspan="2"></td>
        </tr>
        <tr> 
          <td width="122" height="25" bgcolor="#F5F5F5"><div align="center"><strong><font color="#A052A0">상품종류선택</font></strong></div></td>
          <td width="567" bgcolor="#FFFFFF"> &nbsp; <input name="kind" type="radio" value="1" checked>
        명함 
        <input type="radio" name="kind" value="2">
            스티커 
            <input type="radio" name="kind" value="3">
            전단 
            <input type="radio" name="kind" value="4">
            서식지 
            <input type="radio" name="kind" value="5">
            봉투 
            <input type="radio" name="kind" value="6">
            카다로그</td>
        </tr>
      </table>
      <br>
  <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
    <tr bgcolor="#D0A4DF"> 
      <td height="2"></td>
    </tr>
    <tr> 
      <td height="25" bgcolor="#F5F5F5"><div align="left"><strong><font color="#A052A0">&nbsp;&nbsp;&nbsp;&nbsp;견적내용 
          </font></strong><font color="#666666">[종류, 사이즈, 수량, 용도, 모양 등 내용을 자세히 
          입력하세요]</font><font color="#A052A0"><strong> </strong></font></div></td>
    </tr>
  </table>
  <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td height="5"></td>
        </tr>
      </table>
      
  <table width="692" border="0" cellpadding="0" cellspacing="1" bgcolor="#D7D7D7">
    <tr> 
      <td align="center" bgcolor="#FFFFFF"><textarea name="content" cols="100" rows="8" wrap="VIRTUAL" id="content"><? if($HTTP_COOKIE_VARS["adminVar"] && $no){ echo $content; } ?></textarea> 
      </td>
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
        <input name="fax3" type="text" id="fax3" size="6" maxlength="4"> &nbsp;(견적서를 
        받아보시길 원하시면 입력해 주세요) </td>
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
              &nbsp;<a href='javascript:document.ordform.reset();document.ordform.content.focus();'><img src="../img/bu_again.gif" width="93" height="32" border=0></a> </div></td>
        </tr>
      </table>
<? } 
else { 
	echo "<table width=692><tr><td width=5></td><td>";
	// 관리자 일때
	$upDir ="/upHand/$kind/";	
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
	echo "<tr><td align=center><input type=button name=button1 value=확인 onClick=\"location.href='estimate_hand_PC.php?no=$no'\">&nbsp;<input type=button value=삭제 onclick='javascript:del()'>&nbsp;<input type=button value=시안넣기 onClick='javascript:design();'>&nbsp; <input type=button value=닫기 onClick='window.close()'></td></tr></table>";
} 
?>
    </form>

<?php
if($HTTP_COOKIE_VARS["adminVar"] && $no){ ?>
	<script language="JavaScript">
		f = document.ordform;
		for (i=0;i<=5;i++){
			if(f.kind[i].value=="<?=$kind?>"){
				f.kind[i].checked=true;
				break;
			}
		}
		f.ordername.value="<?=$ordername?>";
		f.company.value="<?=$company?>";
		f.tel1.value="<?=$tel[0]?>";f.tel2.value="<?=$tel[1]?>";f.tel3.value="<?=$tel[2]?>";
		f.fax1.value="<?=$fax[0]?>";f.fax2.value="<?=$fax[1]?>";f.fax3.value="<?=$fax[2]?>";
		f.cel1.value="<?=$cel[0]?>";f.cel2.value="<?=$cel[1]?>";f.cel3.value="<?=$cel[2]?>";
		f.email.value="<?=$email?>";
		//메모는 줄바꿈 관계로 html태그에 직접 입력

		function del(){
			result=confirm("정말로 삭제하시겠습니까?");
			if(result)	location.href="estimate_hand_PC.php?del=<?=$no?>";
		}

		function design(){
			location.href="/admin/design.php?direct=Y&kind=<?=$kind?>&ordername=<?=$ordername?>&company=<?=$company?>";
		}
	</script>
<? } ?>
</body>
</html>