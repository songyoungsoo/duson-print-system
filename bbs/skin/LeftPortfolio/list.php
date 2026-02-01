<?php 
 function str_cutting($str, $len){ 
       preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn); 
       if ( $len < strlen($str) ) $rtn[0].=".."; 
        return $rtn[0]; 
    } 

$x="$BBS_ADMIN_cutlen";
?> 

<!------------------------------------------- 리스트 시작----------------------------------------->
<?php
if(!$BbsDir){$BbsDir=".";}
if(!$DbDir){$DbDir="..";}
include "$DbDir/db.php";

if($search){ //검색모드일때

if($cate=="title"){$TgCate="Mlang_bbs_title";}
if($cate=="connent"){$TgCate="Mlang_bbs_connent";}
if($cate=="id"){$TgCate="Mlang_bbs_member";}

if($CATEGORY){
$Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from mlang_{$table}_bbs where $TgCate like '%$search%' and  Mlang_bbs_reply='0'";
}

}else{ // 일반모드 일때

if($CATEGORY){
$Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0' and CATEGORY='$CATEGORY'";
}else{
$Mlang_query="select * from mlang_{$table}_bbs where Mlang_bbs_reply='0'";
}

}

$query= mysqli_query($db, "$Mlang_query",$db);
$recordsu= mysqli_num_rows($query);
$total = mysqli_affected_rows($db);
?>


<table border=0 align=center width=<?php echo $BBS_ADMIN_td_width?> cellpadding='5' cellspacing='1' style='word-break:break-all;'>
<tr>
<td align=right>
<!------------ 검색 --------------------------------------------------->

<head>

<?php
$result= mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1",$db);
$row= mysqli_fetch_array($result);
$BBSAdminloginKK=$row['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
?>
<script>
function AdminBdgDel(no){
	var str;
		if (confirm("게시판의 자료를 삭제하려 하십니다.\n\n한번 삭제한 자료는 두번다시 복구되지 않습니다.\n\n삭제하시려면 확인을 누르시기 바랍니다.")) {
		str='/admin/int/delete.php?no='+no+'&bbs=del&table=<?php echo("mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000");
        popup.document.location.href=str;
        popup.focus();
	}
}

function AdminBdCount(no){
	var str;
		if (confirm("카운터의 자료를 수정 하시겠습니까...?")) {
		str='/admin/int/delete.php?AdminCode21=form&no='+no+'&table=<?php echo("mlang_{$table}_bbs");?>';
        popup = window.open("","","scrollbars=no,resizable=yes,width=400,height=20,top=120,left=20");
        popup.document.location.href=str;
        popup.focus();
	}
}
</script>
<?php }?>

<script language=javascript>
////////////////////////////////////////////////////////////////////////////////
function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////

function SearchCheckField()
{
var f=document.MlangSearch;


if (f.title_search.value == "") {
alert("검색할 검색어를 입력하여 주세요..!!");
return false;
}

}

</script>
</head>
<table border=0 align=right cellpadding='0' cellspacing='10'>
<tr>
<form name='MlangSearch' method='post' OnSubmit='javascript:return SearchCheckField()' action='<?php echo("$PHP_SELF");?>'>
<input type='hidden' name='search' value='yes'>
<input type='hidden' name='table' value='<?php echo $table?>'>
<input type='hidden' name='mode' value='list'>
<td>
<font style='font-size:9pt;'>
<input type='hidden' name='cate' value='title'>
제목
<input type='text' name='search' size='20' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
<input type='submit' value=' 검 색' style='background-color:<?php echo $BBS_ADMIN_td_color2?>; color:<?php echo $BBS_ADMIN_td_color1?>; border-style:solid; border:1 solid #<?php echo $BBS_ADMIN_td_color1?>; font-size:9pt;'>
</font>
</td>
</form>
</tr>
</table>

</td>
</tr>
</table>
<!------------ 검색 --------------------------------------------------->

<?php
$listcut= 10;
if(!$offset) $offset=0; 
?>

<head>
<script language="JavaScript1.1"> 
function popUp(L, e) {
if(n4) {
var barron = document.layers[L]
barron.left = e.pageX 
barron.top = e.pageY + 5
barron.visibility = "visible"
}
else if(e4) {
var barron = document.all[L]
barron.style.pixelLeft = event.clientX + document.body.scrollLeft 
barron.style.pixelTop = event.clientY + document.body.scrollTop + 5
barron.style.visibility = "visible"
}
}
function popDown(L) {
if(n4) document.layers[L].visibility = "hidden"
else if(e4) document.all[L].style.visibility = "hidden"
}
n4 = (document.layers) ? 1 : 0
e4 = (document.all) ? 1 : 0

///////////////////////////////////////////////////////////

function HANA_findObj(n, d) { //v4.0
var p,i,x; if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=HANA_findObj(n,d.layers[i].document);
if(!x && document.getElementById) x=document.getElementById(n); return x;
}
function HANA_showHideLayers() { //v3.0
var i,p,v,obj,args=HANA_showHideLayers.arguments;
for (i=0; i<(args.length-2); i+=3) if ((obj=HANA_findObj(args[i]))!=null) { v=args[i+2];
if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v='hide')?'hidden':v; }
obj.visibility=v; }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var photos=new Array() 
var which=0 

/************ 이미지를 설정 하세요. 가능하면 동일 사이즈가 좋습니다 ************/ 
<?php
$result= mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut",$db);
$rows=mysqli_num_rows($result);
if($rows){ $i=1+$offset; $N=0;}
if($rows){  while($row= mysqli_fetch_array($result)){ 
$BbsViewtMlang_bbs_title=htmlspecialchars($row['Mlang_bbs_title']);
$BbsViewMlang_bbs_connent=htmlspecialchars($row['Mlang_bbs_connent']); // 작은이미지
$BbsViewMlang_bbs_link=htmlspecialchars($row['Mlang_bbs_link']);  // 내용
$BbsViewMlang_bbs_file=htmlspecialchars($row['Mlang_bbs_file']);	// 큰이미지
?>

photos[<?php echo $N?>]="<?php if($BbsViewMlang_bbs_file){echo("/bbs/upload/{$table}/{$BbsViewMlang_bbs_file}");}else{echo("{$BbsDir}/skin/{$BBS_ADMIN_skin}/img/NoSajin.gif");}?>"; 

<?php
      $i=$i+1; $N=$N+1;
       } }  ///////////////////////////////////////////////////////////////////////
?>
/******************************************************************************/ 

var preloadedimages=new Array() 
for (i=0;i<photos.length;i++){ 
preloadedimages[i]=new Image() 
preloadedimages[i].src=photos[i] 
} 


function applyeffect(){ 
if (document.all){ 
photoslider.filters.revealTrans.Transition=Math.floor(Math.random()*23) 
photoslider.filters.revealTrans.stop() 
photoslider.filters.revealTrans.apply() 
} 
} 

function playeffect(){ 
if (document.all) 
photoslider.filters.revealTrans.play() 
} 

function keeptrack(){ 
window.status="Image "+(which+1)+" of "+photos.length 
} 

/******************************************************************************/ 
<?php
if($rows){  for ($N=0; $N<$total; $N++) { ////////////////////////////
?>

function nextward<?php echo $N?>(){ 
applyeffect() 
document.images.photoslider.src=photos[<?php echo $N?>] 
playeffect() 
keeptrack() 
}

<?php
      $i=$i+1;
       } }  ///////////////////////////////////////////////////////////////////////
?>
/******************************************************************************/ 

function transport(File){ 
window.open('/bbs/upload/<?php echo $table?>/'+File);
} 
</script>
</head>

<!--------------- 리스트 호출 시작 ------------------------------------------------------>
<table border=0 align=center width=650 cellpadding='3' cellspacing='0'>
  <tr>
    <td width=200 align=center valign=top>
<!----- 목록 시작 ----------->

<table border=0 align=center width=100% cellpadding='0' cellspacing='5'>
  <tr>
<?php
$result= mysqli_query($db, "$Mlang_query order by Mlang_bbs_no desc limit $offset,$listcut",$db);
$rows=mysqli_num_rows($result);
if($rows){ $i=1+$offset; $N=0; $says=$listcut/5; }
if($rows){  while($row= mysqli_fetch_array($result)){ 
$BbsViewtMlang_bbs_title=htmlspecialchars($row['Mlang_bbs_title']);
$BbsViewMlang_bbs_connent=htmlspecialchars($row['Mlang_bbs_connent']); // 작은이미지
$BbsViewMlang_bbs_link=htmlspecialchars($row['Mlang_bbs_link']);  // 내용
$BbsViewMlang_bbs_file=htmlspecialchars($row['Mlang_bbs_file']);	// 큰이미지
?>
<td align=center>  

<table border=0 align=center width="61" height="75" cellpadding=0 cellspacing=3 bgcolor='#F6F6F6'>
<tr><td align=center><a href="<?php if($BbsViewMlang_bbs_file){echo("javascript:transport('{$BbsViewMlang_bbs_file}');");}else{echo("javascript:alert('큰이미지없음..*^^*');");}?>" onmouseover="popUp('D<?php echo $N?>', event); HANA_showHideLayers('HANA<?php echo $N?>','','show'); nextward<?php echo $N?>();return false" onMouseOut="popDown('D<?php echo $N?>'); HANA_showHideLayers('HANA<?php echo $N?>','','hide')"><img src="<?php if($BbsViewMlang_bbs_connent){echo("/bbs/upload/{$table}/{$BbsViewMlang_bbs_connent}");}else{echo("{$BbsDir}/skin/{$BBS_ADMIN_skin}/img/NoSajin.gif");}?>" width="61" height="75" border=0></a></td></tr></table>  

<?php
/////////////////////////// 관리자 모드 호출 START //////////////////
$AdminChickTYyj= mysqli_query($db, "SELECT username AS id, password AS pass, name, email FROM users WHERE is_admin = 1 LIMIT 1");
$row_AdminChickTYyj= mysqli_fetch_array($AdminChickTYyj);
$BBSAdminloginKK=$row_AdminChickTYyj['id'];
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK){
echo("<table border=0 align=center width='100%' cellpadding=0 cellspacing=3 bgcolor='#D9D4BF'>
<tr><td align=center>");
$AdminYdddNo=$row['Mlang_bbs_no'];
include "$BbsDir/AmdinCount.php";
echo("</td></tr></table>");
}
/////////////////////////// 관리자 모드 호출 END    //////////////////
?>

<!------------<div id='HANA<?php echo $N?>' style='position:absolute; left:447; top:324; visibility:hidden; FILTER: alpha(opacity=70);z-index:0'>
<table width=450 cellpadding=0 cellspacing=0 border=0>
<tr>
<td bgcolor=#000000 width=100% height=50 align=center>
<marquee direction="up" height="44" width=440 scrolldelay='300'>
<?php
	    $CONTENT=$BbsViewMlang_bbs_link;
		$CONTENT = preg_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = preg_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;

		echo("<font color=#FFFFFF>$connent_text</font>");
?>
</marquee>
</td>
</tr>
</table>
</div>-------->

<div id=D<?php echo $N?> style="position:absolute; left:0; top:0; width:200; padding:3px;
background-color:#F6F6F6; border:2 solid #D9D4BF; visibility:hidden; FILTER: alpha(opacity=80);z-index:0">
<font color=#575757><?php echo $BbsViewtMlang_bbs_title?></font>
</div>

</td>

<?php
if ($i%$says) {}else{
echo("
<!--------- 라인분류 if -------------->
</tr>
<tr>
<!--------- 라인분류 if -------------->
");
}
      $i=$i+1; $N=$N+1;
       } }  ///////////////////////////////////////////////////////////////////////
?>
</tr></table>
<!----- 목록 끄읕 ----------->
	</td>
	<td>&nbsp;</td>
	<td align=center width=440>
	<!--- 큰사진+내용 시작------>
<table border=0 align=center cellpadding=0 cellspacing=5 bgcolor='#F6F6F6'>
<tr><td align=center><script>document.write('<img src="'+photos[0]+'" name="photoslider" style="filter:revealTrans(duration=0,transition=0)" border=0 width=298 height=411>');</script></td></tr></table>  
	<!--- 큰사진+내용 끄읕------>
</td>
   </tr>
</table>
<!--------------- 리스트 호출 끄읕 ------------------------------------------------------>

<p align='center'>
<font style='font-size:10pt;'>
<?php
if($rows){

if($search){
$mlang_pagego="cate=$cate&search=$search&table=$table&mode=list&page=$page&PCode=$PCode"; 
}else{
$mlang_pagego="table=$table&mode=list&page=$page&PCode=$PCode"; 
}

$pagecut= $BBS_ADMIN_lnum;  
$one_bbs= $listcut*$pagecut; 
$start_offset= intval($offset/$one_bbs)*$one_bbs;  
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;
$start_page= intval($start_offset/$listcut)+1; 
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut); 

if($start_offset!= 0) 
{ 
  $apoffset= $start_offset- $one_bbs; 
  echo "<a href='$PHP_SELF?offset=$apoffset&$mlang_pagego'>◀</a>&nbsp;"; 
} 

for($i= $start_page; $i< $start_page+$pagecut; $i++) 
{ 
$newoffset= ($i-1)*$listcut; 

if($offset!= $newoffset){
  echo "<a href='$PHP_SELF?offset=$newoffset&$mlang_pagego'>[$i]</a>"; 
}else{echo("<font style='font:bold; color:green;'>[$i]</font>"); } 

if($i==$end_page) break; 
} 

if($start_offset!= $end_offset) 
{ 
  $nextoffset= $start_offset+ $one_bbs; 
  echo "&nbsp;<a href='$PHP_SELF?offset=$nextoffset&$mlang_pagego'>▶</a>"; 
} 
echo "&nbsp;&nbsp;총목록갯수: $end_page 개"; 


}

mysqli_close($db); 
?> 
</font>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href='<?php echo  htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=write&table=<?php echo $table?>&page=<?php echo $page?>&PCode=<?php echo $PCode?>'><img src='<?php echo $BbsDir?>/skin/<?php echo $BBS_ADMIN_skin?>/img/write.gif' border=0 align=absmiddle></a>	
</p>
<!------------------------------------------- 리스트 끝----------------------------------------->
?>