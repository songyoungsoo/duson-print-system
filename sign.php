	<!----------------------------------------------top끝----------------------------------------------->
<table width="510" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td align="left" valign="top">
		
		  <!------------------------ 내용 페이지 호출 시작 ------------------------------------->
<!------------------------ 시안 시작 ------------------------->
<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
<tr><td><img src='/img/12345.gif' width=1 height=5></td></tr></table>

     <table border=0 align=center cellpadding=0 cellspacing=0>
       <tr>

		 <!-------------- 내용 시작 --------------------------->
         <td width=100% valign=top>

<!------------------------------------------- 리스트 시작----------------------------------------->
<table border=0 align=center width=100% cellpadding='0' cellspacing='0' style='word-break:break-all;'>
<tr bgcolor='#F7F7F7'>
<td align=center width=135 height=30><font style='font:bold; color:#3399FF;'>주문인성함</font></td>
<td align=center width=125><font style='font:bold; color:#3399FF;'>주문날짜</font></td>
<td align=center width=125><font style='font:bold; color:#3399FF;'>처리</font></td>
<td align=center width=125><font style='font:bold; color:#3399FF;'>시안</font></td>
</tr>

<?php
// Initialize variables to prevent undefined notices
$TDsearch = isset($_GET['TDsearch']) ? $_GET['TDsearch'] : '';
$TDsearchValue = isset($_GET['TDsearchValue']) ? $_GET['TDsearchValue'] : '';
$OrderCate = isset($_GET['OrderCate']) ? $_GET['OrderCate'] : '';
$OrderStyleYU9OK = isset($_GET['OrderStyleYU9OK']) ? $_GET['OrderStyleYU9OK'] : '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$CountWW = isset($_GET['CountWW']) ? $_GET['CountWW'] : '';
$s = isset($_GET['s']) ? $_GET['s'] : '';
$i = 0;
include "./db.php";
$table="MlangOrder_PrintAuto";

if($TDsearch){ //검색모드일때
$Mlang_query="select * from $table where $TDsearch like '%$TDsearchValue%'";
}else if($OrderCate){

$ToTitle="$OrderCate";
include "./MlangPrintAuto/ConDb.php";
$ThingNoOkp="$View_TtableB";
$Mlang_query="select * from $table where Type='$ThingNoOkp'";

}else if($OrderStyleYU9OK){
$Mlang_query="select * from $table where OrderStyle='$OrderStyleYU9OK'";
}else{ // 일반모드 일때
$Mlang_query="select * from $table";
}

// Replacing deprecated mysql_* functions with mysqli_*
$query = mysqli_query($db, $Mlang_query);
$recordsu = mysqli_num_rows($query); 
$total = mysqli_affected_rows($db);
$listcut= 9;  //한 페이지당 보여줄 목록 게시물수. 
if(!$offset) $offset=0; 

if($CountWW){
    $result = mysqli_query($db, "$Mlang_query ORDER BY $CountWW $s LIMIT $offset,$listcut");
}else{
    $result = mysqli_query($db, "$Mlang_query ORDER BY NO DESC LIMIT $offset,$listcut"); 
}

$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
?>

<tr bgcolor='#FFFFFF'>
<td align=center height=30>
<font style='color:#38409B; font-size:10pt;'><?php echo htmlspecialchars($row['name']); ?></font>
</td>
<td align=center>
<?php echo substr($row['date'], 0,10); ?>
</td>
<td align=center>
<?php if($row['OrderStyle']=="2"){?>접수중..<?php }?>
<?php if($row['OrderStyle']=="3"){?>접수완료<?php }?>
<?php if($row['OrderStyle']=="4"){?>입금대기<?php }?>
<?php if($row['OrderStyle']=="5"){?>시안제작중<?php }?>
<?php if($row['OrderStyle']=="6"){?>시안<?php }?>
<?php if($row['OrderStyle']=="7"){?>교정<?php }?>
<?php if($row['OrderStyle']=="8"){?>작업완료<?php }?>
<?php if($row['OrderStyle']=="9"){?>작업중<?php }?>
<?php if($row['OrderStyle']=="10"){?>교정작업중<?php }?>
</td>
<td align=center>
<a href='#' onClick="javascript:popup=window.open('/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=<?php echo $row['no']; ?>', 'MViertWasd','width=600,height=400,top=0,left=0,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();"><img src='/img/button/sian.gif' border=0 align='absmiddle'></a>
</td>
</tr>

<tr>
<td height=1 bgcolor='#A4D1FF' background='/images/left_menu_back_134ko.gif' colspan=7></td>
</tr>

<?php
        $i++;
} 

}else{

if($TDsearchValue){ // 회원 간단검색 TDsearch //  TDsearchValue
    echo "<tr><td colspan=10><p align=center><BR><BR>$TDsearch 로 검색되는 $TDsearchValue - 관련 검색 자료없음</p></td></tr>";
}else if($OrderCate){
    echo "<tr><td colspan=10><p align=center><BR><BR>$cate 로 검색되는 - 관련 검색 자료없음</p></td></tr>";
}else{
    echo "<tr><td colspan=10><p align=center><BR><BR>등록 자료없음</p></td></tr>";
}

}
?>

</table>

<!------------------------------------------- 리스트 끝----------------------------------------->

		 </td>
		 <!-------------- 내용 끄읕 --------------------------->
       </tr>
     </table>
<!------------------------ 시안 끄읕 ------------------------->		
			<!------------------------ 내용 페이지 호출 끄읕 ------------------------------------->
			</td>
      </tr>
    </table>	
	
<!---------------------------------------down---------------------------------------------->
