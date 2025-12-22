<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';
$TDsearch = $_GET['TDsearch'] ?? $_POST['TDsearch'] ?? '';
$TDsearchValue = $_GET['TDsearchValue'] ?? $_POST['TDsearchValue'] ?? '';
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;
$money = $_GET['money'] ?? $_POST['money'] ?? '';
$CountWW = $_GET['CountWW'] ?? $_POST['CountWW'] ?? '';
$s = $_GET['s'] ?? $_POST['s'] ?? '';
$cate = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$i = 0; // ✅ 루프 카운터 전역 초기화

if($mode=="LevelModify"){

include"../../db.php";
include"../config.php";
$query ="UPDATE member SET level='$code' WHERE no='$no'";
$result= mysqli_query($db, $query);
// ⚠️  에러 처리 권장: mysqli_error() 사용을 고려하세요

mysqli_close($db); 

echo ("<script language=javascript>
window.alert('회원의 레벨을 조절하였습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?offset=$offset&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue'>
");
exit;

}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="PointModlfy"){

include"../../db.php";
include"../config.php";
$query ="UPDATE member SET money='$money' WHERE no='$no'";
$result= mysqli_query($db, $query);
mysqli_close($db); 

echo ("<script language=javascript>
window.alert('회원의 Point 을 조절하였습니다.');
</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?offset=$offset&TDsearch=$TDsearch&TDsearchValue=$TDsearchValue'>
");
exit;

}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$M123="..";
include"../top.php"; 
?>

<head>
<script>
function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function Member_Admin_Del(no){
	if (confirm(+no+'번 회원을 탈퇴처리 하시겠습니까..?\n\n한번 삭제한 자료는 복구 되지 않으니 신중을 기해주세요.............!!')) {
		str='admin.php?no='+no+'&mode=delete';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function TDsearchCheckField()
{
var f=document.TDsearch;

if (f.TDsearchValue.value == "") {
alert("검색할 검색어 값을 입력해주세요");
f.TDsearchValue.focus();
return false;
}

}
</script>

</head>


<table border=0 align=center width=100% cellpadding='8' cellspacing='3' class='coolBar'>
<tr>
<td align=left>

   <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
     <tr>
	    <form method='post' name='TDsearch' OnSubmit='javascript:return TDsearchCheckField()' action='<?=$PHP_SELF?>'>
	    <td align=left>
		<b>간단 검색 :&nbsp;</b>
		<select name='TDsearch'>
		<option value='id'>회원아이디</option>
		<option value='name'>회원이름</option>
		<option value='email'>E메일</option>
        <input type='text' name='TDsearchValue' size='20'>
        <input type='submit' value=' 검 색 '>
	    </td>
		</form>
	 </tr>
  </table>

</td>


<!------------------ 등록순, 조회순 $CountWW , Logincount  , money ------------------------------>
<td align=right>
<input type='button' value='방문수 ↑' onClick="javascript:window.location.href='<?echo("$PHP_SELF?offset=$offset&cate=$cate$title_search=$title_search&CountWW=Logincount&s=desc");?>';">
<input type='button' value='방문수 ↓' onClick="javascript:window.location.href='<?echo("$PHP_SELF?offset=$offset&cate=$cate$title_search=$title_search&CountWW=Logincount&s=asc");?>';">

<input type='button' value='Point ↑' onClick="javascript:window.location.href='<?echo("$PHP_SELF?offset=$offset&cate=$cate$title_search=$title_search&CountWW=money&s=desc");?>';">
<input type='button' value='Point ↓' onClick="javascript:window.location.href='<?echo("$PHP_SELF?offset=$offset&cate=$cate$title_search=$title_search&CountWW=money&s=asc");?>';">
</td>
<!------------------ 등록순, 조회순 ----------------------------------------------------------->

</tr>
</table>

<?if($search=="yes"){?>
<script language="JavaScript"> 
var f=document.MemberSearch;
f.sex.value="<?=$sex?>"; 
f.wedyes.value="<?=$wedyes?>"; 
f.iii_1.value="<?=$iii_1?>"; 
f.iii_2.value="<?=$iii_2?>"; 
f.iii_3.value="<?=$iii_3?>";
//f.po3.value="<?=$po3?>"; 
f.school.value="<?=$school?>"; 
f.job.value="<?=$job?>"; 
f.yearmonuy.value="<?=$yearmonuy?>";
f.GirlStyle.value="<?=$GirlStyle?>"; 
f.level.value="<?=$level?>"; 
</script>
<?}?>
<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='5' cellspacing='1' class='coolBar'>
<tr>
<td align=center>번호</td>
<td align=center>아이디</td>
<td align=center>회원 이름</td>
<!-- <td align=center>성별</td>
<td align=center>년생</td> -->
<td align=center>방문수</td>
<td align=center>최종방문일</td>
<td align=center>가입날짜</td>
<td align=center>Point</td>
<td align=center>Level</td>
<td align=center>관리기능</td>
</tr>

<?php include"../../db.php";
$table="member";

if($search=="yes"){ //검색모드일때

function ERROR($msg)
{
echo ("<script language=javascript>
window.alert('$msg');
history.go(-1);
</script>
");
exit;
}

// if($iii_1=="no"){$i1="iii_1 like '%%' and";}else{$i1="iii_1='$iii_1' and";
// if($iii_2=="no"){$msg = "몇남의 값이 존재할때 몇녀의 값이 BB 일수 없습니다."; ERROR($msg);}
// if($iii_3=="no"){$msg = "몇남의 값이 존재할때 몇째의 값이 BB 일수 없습니다."; ERROR($msg);}
// }
// //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
// if($iii_2=="no"){$i2="iii_2 like '%%' and";}else{$i2="iii_2='$iii_2' and";
// if($iii_1=="no"){$msg = "몇녀의 값이 존재할때 몇남의 값이 BB 일수 없습니다."; ERROR($msg);}
// if($iii_3=="no"){$msg = "몇녀의 값이 존재할때 몇째의 값이 BB 일수 없습니다."; ERROR($msg);}
// }
// //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
// if($iii_3=="no"){$i3="iii_3 like '%%' and";}else{$i3="iii_3='$iii_3' and";
// if($iii_1=="no"){$msg = "몇째의 값이 존재할때 몇남의 값이 BB 일수 없습니다."; ERROR($msg);}
// if($iii_2=="no"){$msg = "몇째의 값이 존재할때 몇녀의 값이 BB 일수 없습니다."; ERROR($msg);}
// }
// //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
// if($po1=="신장"){$p1="po1 like '%%' and";}else{$p1="po1 like '%$po1%' and";}
// if($po2=="체중"){$p2="po2 like '%%' and";}else{$p2="po2 like '%$po2%' and";}
// if($po3=="no"){$p3="po3 like '%%' and";}else{$p3="po3='$po3' and";}
// //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^//
// if($school=="no"){$school1="school like '%%' and";}else{$school1="school='$school' and";}
// if($job=="no"){$job1="job like '%%' and";}else{$job1="job='$job' and";}
// if($yearmonuy=="no"){$yearmonuy1="yearmonuy like '%%' and";}else{$yearmonuy1="yearmonuy='$yearmonuy' and";}
// if($GirlStyle=="no"){$GirlStyle1="GirlStyle like '%%' and";}else{$GirlStyle1="GirlStyle='$GirlStyle' and";}

$Mlang_query="select * from $table where instr(jumin2,'$sex')='1' and wedyes='$wedyes' and $i1 $i2 $i3 $p1 $p2 $p3 $school1 $job1 $yearmonuy1 $GirlStyle1 level='$level'";

}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue

$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";

}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

$query= mysqli_query($db, $Mlang_query);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut= 30;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

if($CountWW){
$result= mysqli_query($db, "$Mlang_query order by $CountWW $s limit $offset,$listcut");
}else{
$result= mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
}

$rows=mysqli_num_rows($result);
if($rows){


while($row= mysqli_fetch_array($result))
{ 
?>

<tr bgcolor='#575757'>
<td align=center><font color=white><?=$row['no']?></font></td>
<td><a href='#'  onClick="javascript:window.open('MemberImail.php?no=<?=$row['no']?>&code=1', 'member_iemail','width=600,height=500,top=10,left=10,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no');"><font color=white><?= htmlspecialchars($row['id']);?></font></a></td>
<td><font color=white><?= htmlspecialchars($row['name']);?></font></td>
<td align=center><font color=white><?=$row['Logincount']?></font></td>
<td align=center><font color=white><?=$row['EndLogin']?></font></td>
<td align=center><font color=white><?= htmlspecialchars($row['date']);?></font></td>
<td align=center>

  <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
  <form method='post' action='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=$TDsearchValue?>'>
  <INPUT TYPE="hidden" name='mode' value='PointModlfy'>
    <INPUT TYPE="hidden" name='no' value='<?=$row['no']?>'>
  <tr><td align=center><INPUT TYPE="text" NAME="money" size='7' value='<?=$row['money'] ?? 0?>'><input type='submit' value='수정'></td></tr></form>
  </table>

</td>
<td align=center>

<script language="JavaScript">
function LevelModify_<?=$row['no']?>(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
<select onChange="LevelModify_<?=$row['no']?>('parent',this,0)">
<option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=$TDsearchValue?>&mode=LevelModify&code=2&no=<?=$row['no']?>' <?php if($row['level']=="2"){echo("selected");} ?>>2 레벨-부운영자</option>
<option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=$TDsearchValue?>&mode=LevelModify&code=3&no=<?=$row['no']?>' <?php if($row['level']=="3"){echo("selected");} ?>>3 레벨-골드회원</option>
<option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=$TDsearchValue?>&mode=LevelModify&code=4&no=<?=$row['no']?>' <?php if($row['level']=="4"){echo("selected");} ?>>4 레벨-정회원</option>
<option value='<?=$PHP_SELF?>?offset=<?=$offset?>&TDsearch=<?=$TDsearch?>&TDsearchValue=<?=$TDsearchValue?>&mode=LevelModify&code=5&no=<?=$row['no']?>' <?php if($row['level']=="5"){echo("selected");} ?>>5 레벨-일반회원</option>
</select>

</td>
<td align=center>
<input type='button' onClick="javascript:popup=window.open('admin.php?mode=view&no=<?=$row['no']?>', 'MemberModify','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value='회원정보보기'>
<input type='button' onClick="javascript:Member_Admin_Del('<?=$row['no']?>');" value=' 탈퇴 '>
</td>
<tr>

<?php 		$i=$i+1;
} 


}else{

if($search){
echo"<tr><td colspan=10><p align=center><BR><BR>관련 검색 자료없음</p></td></tr>";
}else if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue
echo"<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
}else{
echo"<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
}

}

?>


</table>



<p align='center'>

<?php if($rows){

$mlang_pagego="CountWW=$CountWW&s=$s&cate=$cate$title_search=$title_search"; // 필드속성들 전달값

$pagecut= 7;  //한 장당 보여줄 페이지수 
$one_bbs= $listcut*$pagecut;  //한 장당 실을 수 있는 목록(게시물)수 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  //각 장에 처음 페이지의 $offset값. 
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;  //마지막 장의 첫페이지의 $offset값. 
$start_page= intval($start_offset/$listcut)+1; //각 장에 처음 페이지의 값. 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 
//마지막 장의 끝 페이지. 
if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>...[이전]</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "&nbsp;<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>($i)</a>&nbsp;"; 
}else{echo("&nbsp;<font style='font:bold; color:green;'>($i)</font>&nbsp;"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>[다음]...</a>"; 
} 
echo "총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 

</p>
<!------------------------------------------- 리스트 끝----------------------------------------->

<?php include"../down.php";
?>