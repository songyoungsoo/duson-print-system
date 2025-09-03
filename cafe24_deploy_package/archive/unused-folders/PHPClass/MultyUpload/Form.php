<?php
$log_url=preg_replace("/", "_", $PHP_SELF);
$log_y=date("Y");                  // 연도
$log_md=date("md");            //월일
$log_ip="$REMOTE_ADDR";  // 접속 ip
$log_time = time();               //  접속 로그타임
?>

<head>

<SCRIPT LANGUAGE="JavaScript">

function small_window(myurl) {
var newWindow;
var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,width=400,height=200';
newWindow = window.open(myurl, "Add_from_Src_to_Dest", props);
}


function addToParentList(sourceList) {
destinationList = window.document.forms[0].parentList;
for(var count = destinationList.options.length - 1; count >= 0; count--) {
destinationList.options[count] = null;
}
for(var i = 0; i < sourceList.options.length; i++) {
if (sourceList.options[i] != null)
destinationList.options[i] = new Option(sourceList.options[i].text, sourceList.options[i].value );
   }
}


function selectList(sourceList) {
sourceList = window.document.forms[0].parentList;
for(var i = 0; i < sourceList.options.length; i++) {
if (sourceList.options[i] != null)
sourceList.options[i].selected = true;
}
return true;
}


function deleteSelectedItemsFromList(sourceList) {
var maxCnt = sourceList.options.length;
for(var i = maxCnt - 1; i >= 0; i--) { 

if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
window.open('FileDelete.php?FileDelete=ok&Turi=<?php echo $log_url?>&Ty=<?php echo $log_y?>&Tmd=<?php echo $log_md?>&Tip=<?php echo $log_ip?>&Ttime=<?php echo $log_time?>&FileName='+sourceList.options[i].text,'','scrollbars=no,resizable=no,width=100,height=100,top=2000,left=2000');
sourceList.options[i] = null;
      }
   }


}

function FormCheckField()
{
var winopts = "width=780,height=590,toolbar=no,location=no,directories=no,status=yes,menubar=no,status=yes,menubar=no,scrollbars=no,resizable=yes";
var popup = window.open('','MlangMulty<?php echo $log_y?><?php echo $log_md?><?php echo $log_time?>', winopts);
popup.focus();
}

function MlangWinExit() {
if(document.choiceForm.OnunloadChick.value == "on") {
window.open("FileDelete.php?DirDelete=ok&Turi=<?php echo $log_url?>&Ty=<?php echo $log_y?>&Tmd=<?php echo $log_md?>&Tip=<?php echo $log_ip?>&Ttime=<?php echo $log_time?>",'<?php echo $log_time?>','width=100,height=100,left=2000,top=2000,menubar=0,resizable=1,status=0,scrollbars=yes');
}
}
window.onunload = MlangWinExit;
</SCRIPT>

</head>

<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

<form name='choiceForm' method='post' OnSubmit='javascript:return FormCheckField()'  target='MlangMulty<?php echo $log_y?><?php echo $log_md?><?php echo $log_time?>'  action='FormOk.php'>

<input type="text" name="OnunloadChick" value="on">


<input type="text" name="text1">
<input type="text" name="text2">


     <table border=0 align=center width=100% cellpadding=2 cellspacing=0>
       <tr>
         <td width=70%>
		    <select size='5' style="width:100%;" name='parentList' multiple></select>
		  </td>
		 <td width=30%>
<input type='button' onClick="javascript:small_window('FileUp.php?Turi=<?php echo $log_url?>&Ty=<?php echo $log_y?>&Tmd=<?php echo $log_md?>&Tip=<?php echo $log_ip?>&Ttime=<?php echo $log_time?>');" value=' 파일올리기 ' style="width:100; height:40;"><BR>
<input type='button' onclick="javascript:deleteSelectedItemsFromList(parentList);" value=' 삭 제 ' style="width:100; height:40;">

<BR><BR>
<INPUT TYPE="submit" value='저장할때'>

		 </td>
       </tr>
     </table>
?>