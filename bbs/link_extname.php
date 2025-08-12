<?php
$FileExtName = substr(strrchr($BbsViewMlang_bbs_link,"."),1);

if($FileExtName=="jpg" || $FileExtName=="JPG" || $FileExtName=="gif" || $FileExtName=="GIF" || $FileExtName=="png" || $FileExtName=="PNG" || $FileExtName=="bmp" || $FileExtName=="BMP" ){
?>

<script>
function init() {
preLoad.style.visibility = "hidden";
menuR.style.visibility = "visible";
}



fix_w = 560; // 고정할 크기 (픽셀 단위)
function resize(i) {
if (i.width > fix_w) {
i.width = fix_w;
i.onclick = function() {
window.open(i.src, "_blank");
}
i.style.cursor = "pointer";
}
}

</script>
<body onload="init()">

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
	 <tr>
         <td align=center><span id="preLoad" style="visibility: visible; font-size: 13pt; font:bold; font-family: 돋움; color:#336666">이미지(사진)을 열고 있습니다.......</span></td>
       </tr>
       <tr>
         <td align=center><IMG SRC="<?php echo("$Homedir/bbs/upload/$table/$BbsViewMlang_bbs_file");?>" ONLOAD="resize(this)" id="menuR" style="visibility: hidden"></td>
       </tr>
     </table>

<?php
}
//////////////////////////////////////////////////////////////////////////
if($FileExtName=="swf" || $FileExtName=="SWF" ){
echo("<embed src='$BbsViewMlang_bbs_link'>");
}
/////////////////////////////////////////////////////////////////////////
if($FileExtName=="asf" || $FileExtName=="ASF" || $FileExtName=="wmv" || $FileExtName=="WMV" || $FileExtName=="avi"|| $FileExtName=="AVI"|| $FileExtName=="mpeg"|| $FileExtName=="MPEG"|| $FileExtName=="mpg"|| $FileExtName=="MPG"){
?>

<head>
<script language="javascript">
function ds(){ 
document.MediaPlayer1.DisplaySize = 1; 

document.MediaPlayer1.Play(); 

document.MediaPlayer1.focus(); 

} 

function os(){ 

document.MediaPlayer1.DisplaySize = 2; 

document.MediaPlayer1.Play(); 

document.MediaPlayer1.focus(); 

}

function fs(){ 

document.MediaPlayer1.DisplaySize = 3; 

document.MediaPlayer1.Play(); 

document.MediaPlayer1.focus(); 

} 

</script>
</head>


<object id=MediaPlayer1 
codebase=http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701 
classid=CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95 type=application/x-oleobject 
standby="Loading...파일을 여는중... 스크립트네꺼">
<param name="FileName" value="<?php echo("$BbsViewMlang_bbs_link");?>">
<param name="AutoStart" value="1">
<param name="ShowControls" value="1">
<param name="ShowStatusBar" value="1">
<param name="EnableTracker" value="1">
<param name="ShowTracker" value="1">
<param name="ShowAudioControls" value="1">
<param name="ShowDisplay" value="0">
<param name="DisplaySize" value="0">
</object>
<BR>
<input type=button value="200%" onclick=ds()>
<input type=button value="100%" onclick=os()>
<input type=button value="Full" onclick=fs()>


<?php
}
?>
	  </td>
       </tr>
     </table>
?>