<?
include"$DbDir/db.php";
// 주소검책창 뛰우기
if(!strcmp($mode,"search")) {
?>


<html>
<head>
<title>주소자동검색-<?=$admin_name?></title>
<STYLE>
<!--
p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림;}
b {color:black; font-size:10pt; FONT-FAMILY:굴림;}
-->
</STYLE>
<script language=javascript>
function ZipCheckField()
{
var f=document.zip;
if (f.zip_search.value == "") {
alert("검색하실 내용을 입력하여주세요");
return false;
}
}
</script>
</head>

<body bgcolor='#F7FFEF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<BR><BR><BR><BR><BR>


<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='/img/12345.gif' height=5></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=40 align=center bgcolor='#D2F9BF' width=100%><img src='/img/12345.gif' height=30 width=200></td></tr>
<tr><td bgcolor='#D2F9BF' valign=top>

<table border=0 width=80% align=center cellpadding='5' cellspacing='1' bgcolor='#8CDF63'>
<tr>
<td align=left  width=100%>
&nbsp;&nbsp;<b>아래에 찾으실 시,군,읍,동 을 입력하세요</b>
</td>
</tr>
<form name='zip' method='post' OnSubmit='javascript:return ZipCheckField()' action='<?echo"$PHP_SELF";?>'>
<input type='hidden' name='mode' value='zip_ok'>
<input type='hidden' name='DbDir' value='<?=$DbDir?>'>
<input type='hidden' name='formname' value='<?=$formname?>'>
<tr>
<td  height=110 align=center bgcolor='#F7FFEF' width=100%>
<BR>
예) 전라북도 <u>무주군</u> 을 찾고자할경우 => 무주군 만을 입력한다.
<BR><BR>
<font color=green>찾을단어입력:</font>
<INPUT onmouseover="this.style.backgroundColor='#FFFFFF'" maxLength="10" size="25" style="font-size:9pt; background-color:#FFFFFF; color:#000000; border-width:1; border-style:solid; height:22px; border:1 solid #8CDF63;" name="zip_search">
<INPUT type='submit' size="20" style="font-size:9pt; background-color:#000000; color:#FFFFFF; border-width:1; border-style:solid; height:22px; border:1 solid #1F5E00;" value='주소검색'>
&nbsp;&nbsp;&nbsp;
<BR><BR>

</td>
</tr>
</form>
</table>

</td></tr></table>


<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=40 align=center bgcolor='#D2F9BF' width=100%><img src='/img/12345.gif' height=10 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='/img/12345.gif' height=5></td></tr>
<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright ⓒ 2003 <?=$admin_name?> Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='/img/12345.gif' height=50 width=200></td></tr>
</table>


</body>
</html>


<?
// 검색결과 보여주기
}elseif(!strcmp($mode,"zip_ok")) {
?>

<html>
<head>
<title>주소자동검색-<?=$admin_name?></title>
<STYLE>
<!--
p,br,body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림;}
-->
</STYLE>
		<SCRIPT language=JavaScript>
		function Copy(zip,zip1,zip2) {

			top.opener.document.<?=$formname?>.zip.value = zip;
			top.opener.document.<?=$formname?>.zip1.value = zip1;
			top.opener.document.<?=$formname?>.zip2.value = zip2;

			top.opener.document.<?=$formname?>.zip2.focus();

			parent.window.close();

		}
		</SCRIPT>
</head>
<body bgcolor='#F7FFEF' LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'><tr><td width=100% height=500 valign=middle>
<BR>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='/img/12345.gif' height=5></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='/img/12345.gif' height=20 width=200></td></tr>
<tr><td bgcolor='#D2F9BF'>
<?
$query= mysql_query("select * from zipcode where SIDO like '%$zip_search%' or GUGUN like '%$zip_search%' or DONG like '%$zip_search%'",$db);
$recordsu= mysql_num_rows($query);
$total = mysql_affected_rows();

$listcut= 15;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

$result= mysql_query("select * from zipcode where SIDO like '%$zip_search%' or GUGUN like '%$zip_search%' or DONG like '%$zip_search%' order by NO desc limit $offset,$listcut",$db);
$rows=mysql_num_rows($result);
if($rows){

echo"<table border=0 width=90% align=center cellpadding='2' cellspacing='0'>
<tr><td>
<b><font color=green><u>$zip_search</u></font> 으로 관련 주소가 <font color=red>$total</font> 개 검색 되었습니다.</b>
</td></tr>
<tr><td  height=5 align=left width=100%><img src='/img/12345.gif' height=5></td></tr>
";

$i=1+$offset;
while($row= mysql_fetch_array($result)) 
{ 


echo"
<tr><td>
$i )
<font color=green>$row[ZIPCODE]</font>
 $row[SIDO]
 $row[GUGUN]
 $row[DONG]
 $row[BUNJI]
<a href=\"javascript: Copy('$row[ZIPCODE]','$row[SIDO] $row[GUGUN] $row[DONG]','$row[BUNJI]')\">[선택]</a>
</td></tr>
";

		$i=$i+1;
} 

echo"</table>";
}
else{
echo"
<p align=center>
<font style='font-size:10pt; color:red;'><u><b>$zip_search</b></u></font><font style='font-size:10pt; color:green;'> 로 검색되는 자료없습니다.</font><BR><BR>
(살고계시는 <b>마을의 동</b> 만을 입력하여) 다시 한번 검색 하여 주시기 바랍니다.
<BR><BR>
<input type='button' onClick='javascript:history.back();' value='다시 검색하로가기'>
</p>

</td></tr></table>

<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='/img/12345.gif' height=20 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='/img/12345.gif' height=5></td></tr>
<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright ⓒ 2003 $admin_name Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='/img/12345.gif' height=50 width=200></td></tr>
</table>

</td></tr></table>


</body>
</html>
";
		exit;
}

mysql_close($db); 

?>


</td></tr></table>



<table border=0 width=100% align=center cellpadding='0' cellspacing='0'>
<tr><td  height=20 align=center bgcolor='#D2F9BF' width=100%><img src='/img/12345.gif' height=20 width=200></td></tr>
<tr><td width=100% bgcolor='#6FB745' height=1></td></tr>
<tr><td  height=5 align=left bgcolor='#8CDF63' width=100%><img src='/img/12345.gif' height=5></td></tr>


<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<?
$pagecut= 5;  //한 장당 보여줄 페이지수 
$one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
$start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//마지막 장의 끝 페이지. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&mode=zip_ok&zip_search=$zip_search&DbDir=$DbDir&formname=$formname'>...[이전]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset) 
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&mode=zip_ok&zip_search=$zip_search&DbDir=$DbDir&formname=$formname'>"; 
echo "[$i]"; 
if($offset!= $newoffset) 
  echo "</a>&nbsp;"; 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&mode=zip_ok&zip_search=$zip_search&DbDir=$DbDir&formname=$formname'>[다음]...</a>"; 
} 
echo " 검색된 총목록갯수: $end_page 개
"; 
?> 
</td></tr>



<tr><td  height=20 align=center bgcolor='#F7FFEF' width=100% valign=bottom>
<BR>
<font style='font-size:8pt; color:green'>
Copyright ⓒ 2003 <?=$admin_name?> Corp. All rights reserved. 
</font>
</td></tr>
<tr><td  height=50 align=center width=100%><img src='/img/12345.gif' height=50 width=200></td></tr>
</table>

</td></tr></table>

</body>
</html>

<?
} else {

echo"
<script language=javascript>
alert('\\n정상적인 접근이 아닙니다.\\n\프로그램제작: http://www.script.ne.kr - Mlang\n\\n');
window.close();
</script>
";
exit;

}
?>