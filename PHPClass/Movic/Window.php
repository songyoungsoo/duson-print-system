<?php
if(!$HomePageMovicDir){$HomePageMovicDir="../..";}
						
if($PCode){

include "$HomePageMovicDir/db.php";
   $result= mysqli_query("select * from MlangHomePage_Movic WHERE no='$PCode'",$db);
   $row= mysqli_fetch_array($result);
		  $PCodeTile="$row['title']";
          $PCodeFile="$row['upfile']";
		  $PCodeCont="$row['cont']";
          $PCodeLink="$row['uplink']";
mysqli_close($db); 
}
?>   

<html>
<head>
<title><?php echo $HomeTitle?></title>
<meta http-equiv='Content-type' content='text/html; charset=UTF-8'>
<META NAME='KEYWORDS' CONTENT='<?php echo $HomeTitle?>'>
<meta name='author' content='Mlang'>
<meta name='classification' content='<?php echo $HomeTitle?>'>
<meta name='description' content='<?php echo $HomeTitle?>'>
<!--------------------------------------------------------------------------------
     디자인 편집툴-포토샵8.0, 플래쉬MX
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang - 메일: webmaster@script.ne.kr
     URL: http://www.websil.net , http://www.script.ne.kr

* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.

   홈페이지 제작/상담: ☏ 011-548-7038, 임태희 (전화안받을시 문자를주셔염*^^*)
   전화를 안받으면 다른 전화번호로 변경된 경우일수 있습니다...
   그럴경우는 http://www.script.ne.kr 홈페이지에 방문하시면 메인 페이지에 전화번호가 공개 되어있음으로
   언제든지 부담없이 전화 하여 주시기 바랍니다.... 감사합니다.*^^*
----------------------------------------------------------------------------------->
<style>
body,td,input,select,submit {color:black; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
</style>
</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

			
			<!------------------------- MovicBox Start ------------------------>
            <table border=0 align=center width=571 cellpadding=0 cellspacing=0>
              <tr>
                <td align=right background='/img/MovicBox/MovicBox_1.gif' width=571 height=36>
				
     <table border=0 align=right cellpadding=0 cellspacing=0>
       <tr>
         <td align=center>
<script language="JavaScript">
function Movic55_jumpMenu(targ,selObj,restore){
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>

<!---------------- 동영상 메뉴 Start ---------------------->
<select onChange="Movic55_jumpMenu('parent',this,0)">
<?php
include "$HomePageMovicDir/db.php";
$result= mysqli_query("select * from MlangHomePage_Movic order by no desc",$db);
$rows=mysqli_num_rows($result);
if($rows){
while($row= mysqli_fetch_array($result)) 
{ 
									 if($PCode=="$row['no']"){
									 echo("<option value='$PHP_SELF?PCode=$row['no']&page=$page' style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;' selected>$row['title']</option>");
									 }else{
									   echo("<option value='$PHP_SELF?PCode=$row['no']&page=$page'>$row['title']</option>");
									 }
}
}else{echo("<option>등록 자료없음<option>");}

mysqli_close($db); 
?>
</select>

<!------------------------- 동영상 메뉴 End ----------------------------------->
         </td>
		 <td>&nbsp;&nbsp;&nbsp;</td>
		 </tr></table>

				</td>
              </tr>
            </table>
            <table border=0 align=center width=580 cellpadding=0 cellspacing=0>
              <tr>                 
                <td width=344 valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td height="12"></td>
                    </tr>
                    <tr>
					<!---- 동영상 호출 ---------------->
                      <td><table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                          <tr> 
                            <td><img src='/img/MovicBox/MovicBox_3_top_1.gif' width=4 height=7></td>
                            <td background='/img/MovicBox/MovicBox_3_top_2.gif' width=334></td>
                            <td><img src='/img/MovicBox/MovicBox_3_top_3.gif' width=6 height=7></td>
                          </tr>
                        </table>
                        <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                          <tr> 
                            <td background='/img/MovicBox/MovicBox_3_center_left.gif'><img src='/img/MovicBox/MovicBox_3_center_left.gif' width=4 height=1></td>
                            <td width=335 height=300 align=center><object id=MediaPlayer 
codebase=http://activex.microsoft.com/activex/controls
/mplayer/en/nsmp2inf.cab#Version=5,1,52,701 
classid=CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95 width=320 
height=280 type=application/x-oleobject 
standby="Loading Microsoft Windows Media Player components..."> 
                                <param name="FileName" value="<?php if($PCode){if($PCodeFile){echo("$HomePageMovicDir/admin/HomePage/Movic/upload/$PCodeFile");}else{echo("$PCodeLink");}}else{if($TopBasicFile){echo("$HomePageMovicDir/admin/HomePage/Movic/upload/$TopBasicFile");}else{echo("$TopBasicLink");}}?>">
                                <!---//초기에 보여줄 파일명--->
                                <param name="AutoStart" value="1">
                                <!---//자동으로 플레이--->
                                <param name="ShowControls" value="1">
                                <!---//콘트롤 보이기--->
                                <param name="ShowStatusBar" value="1">
                                <!---//상태바 보이기--->
                                <param name="EnableTracker" value="1">
                                <!---//트래커 사용가능--->
                                <param name="ShowTracker" value="1">
                                <!---//트래커 보이기--->
                                <param name="ShowAudioControls" value="1">
                                <!---//볼륨컨트롤 보이기--->
                                <param name="ShowDisplay" value="0">
                                <!---//저작권등.. 보이기--->
                              </object> </td>
                            <td background='/img/MovicBox/MovicBox_3_center_right.gif'><img src='/img/MovicBox/MovicBox_3_center_right.gif' width=5 height=1></td>
                          </tr>
                        </table>
                        <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
                          <tr> 
                            <td><img src='/img/MovicBox/MovicBox_3_down_1.gif' width=8 height=6></td>
                            <td background='/img/MovicBox/MovicBox_3_down_2.gif' width=330></td>
                            <td><img src='/img/MovicBox/MovicBox_3_down_3.gif' width=6 height=6></td>
                          </tr>
                        </table></td>
                    </tr>
                  </table></td>
                <!---- 동영상 끝 ---------------->
                <td><img src='/img/12345.gif' width=20 height=1></td>
                <!---- 동영상 내용 호출 ---------->
                <td align=center> <table border=0 align=center cellpadding=0 cellspacing=0>
                    <tr>
                      <td background='/img/MovicBox/MovicBox_2_top.gif' width=216 height=48><font style='color:#5B5B5B;'> 
                        <p align=left style='text-indent:0; margin-left:35pt; margin-top:5pt;'> 
                          <font color='#FFFFFF'>동영상 줄거리..</font>
                        </p>
                        </font> </td>
                    </tr>
                    <tr>
                      <td background='/img/MovicBox/MovicBox_2_center.gif' valign=top height=270> 
                        <!--- 내용 스크롤 시작 -------->
                        <script language="javascript">
//Math.random()
	var scrollerheight=250;		// 스크롤러의 세로 
	var html,total_area=0,wait_flag=true;	
	var bMouseOver = 1;
	var scrollspeed = 1;		// Scrolling 속도         
	var waitingtime = 500;		// 멈추는 시간
	var s_tmp = 0, s_amount = 19;
	var scroll_content=new Array();
	var startPanel=0, n_panel=0, i=0;
	
	function startscroll()
	{ // 스크롤 시작
		i=0;
		for (i in scroll_content)
			n_panel++;
			
		n_panel = n_panel -1 ;
		startPanel = Math.round(Math.random()*n_panel);
		if(startPanel == 0)
		{
			i=0;
			for (i in scroll_content) 
				insert_area(total_area, total_area++); // area 삽입
		}
		else if(startPanel == n_panel)
		{
			insert_area(startPanel, total_area);
			total_area++;
			for (i=0; i<startPanel; i++) 
			{
				insert_area(i, total_area); // area 삽입
				total_area++;
			}
		}
		else if((startPanel > 0) || (startPanel < n_panel))
		{
			insert_area(startPanel, total_area);
			total_area++;
			for (i=startPanel+1; i<=n_panel; i++) 
			{
				insert_area(i, total_area); // area 삽입
				total_area++;
			}
			for (i=0; i<startPanel; i++) 
			{
				insert_area(i, total_area); // area 삽입
				total_area++;
			}
		}
		window.setTimeout("scrolling()",waitingtime);
	}
	function scrolling(){ // 실제로 스크롤 하는 부분
		if (bMouseOver && wait_flag)
		{
			for (i=0;i<total_area;i++){
				tmp = document.getElementById('scroll_area'+i).style;
				tmp.top = parseInt(tmp.top)-scrollspeed;
				if (parseInt(tmp.top) <= -scrollerheight){
					tmp.top = scrollerheight*(total_area-1);
				}
				if (s_tmp++ > (s_amount-1)*scroll_content.length){
					wait_flag=false;
					window.setTimeout("wait_flag=true;s_tmp=0;",waitingtime);
				}
			}
		}
		window.setTimeout("scrolling()",1);
	}
	function insert_area(idx, n){ // area 삽입
		html='<div style="left: 0px; width: 200; position: absolute; top: '+(scrollerheight*n)+'px" id="scroll_area'+n+'">\n';
		html+=scroll_content[idx]+'\n';
		html+='</div>\n';
		document.write(html);
	}
var newsVar = 0;
scroll_content[0]="<?php
if($PCode){$OkV_Cont="$PCodeCont";}else{$OkV_Cont="$TopBasicCont";}

        $CONTENT=$OkV_Cont;
		$CONTENT = preg_replace("<", "&lt;", $CONTENT);
		$CONTENT = preg_replace(">", "&gt;", $CONTENT);
		$CONTENT = preg_replace("\"", "&quot;", $CONTENT);
		$CONTENT = preg_replace("\|", "&#124;", $CONTENT);
		$CONTENT = preg_replace("\r\n\r\n", "<P>", $CONTENT);
		$CONTENT = preg_replace("\r\n", "<BR>", $CONTENT);
		$connent_text=$CONTENT;
echo("$connent_text");
?>";



</script> <table border=0 align=center width=200 cellpadding=0 cellspacing=0>
                          <tr> 
                            <td> <div style="width: 200px; height:250px; position: absolute; overflow:hidden;"  id="scroll_image"> 
                                <script>startscroll();</script>
                              </div></td>
                          </tr>
                        </table>
                        <!--- 내용 스크롤 끄읕 -------->
                      </td>
                    </tr>
                    <tr>
                      <td align=center><img src='/img/MovicBox/MovicBox_2_down.gif' width=216 height=8></td>
                    </tr>
                  </table></td>
                <!---- 동영상 내용 끄읕 ---------->
              </tr>
            </table>
            <!------------------------- MovicBox End ------------------------->

<p align=center>
<input type='button' onClick='javascript:window.close();' value='창닫기-CLOSE' style='background-color:#FFFFFF; color:#539D26; border-width:1; border-style:solid; height:21px; border:1 solid #539D26;'>
<BR><BR>
<?php echo $WebSoftCopyright?>
</p>

</body>

</html>
?>