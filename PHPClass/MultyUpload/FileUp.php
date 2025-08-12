<?php
include "../../db.php";

// 파일 업로드 변수 초기화
$MlangMultyFile = $_FILES['MlangMultyFile']['tmp_name'] ?? '';
$MlangMultyFile_name = $_FILES['MlangMultyFile']['name'] ?? '';
$MlangMultyFile_size = $_FILES['MlangMultyFile']['size'] ?? 0;

if($MlangMultyFile){  // 파일이 넘어옴으로 업로드를 처리한다...
    include "upload.php";
}
?>

<html>
<head>
<SCRIPT LANGUAGE="JavaScript">
window.moveTo(screen.width/5, screen.height/5); 
</SCRIPT>

<?php if($MlangMultyFile){?>
<script language="JavaScript">

// 결과 값을 보낸다...
function addSelectedItemsToParent() {
try {
    if(self.opener.document.choiceForm && self.opener.document.choiceForm.OnunloadChick) {
        self.opener.document.choiceForm.OnunloadChick.value = "off";
    } else if(self.opener.document.namecardForm && self.opener.document.namecardForm.OnunloadChick) {
        self.opener.document.namecardForm.OnunloadChick.value = "off";
    }
    
    if(self.opener.addToParentList) {
        self.opener.addToParentList(window.document.forms[0].destList);
    }
    window.self.close();
} catch(e) {
    console.log('Error in addSelectedItemsToParent:', e);
    window.self.close();
}
}

// 이전 기록 파일 여부 체크
function fillInitialDestList() {
try {
    var destList = window.document.forms[0].destList; 
    var srcList;
    if(self.opener.document.choiceForm && self.opener.document.choiceForm.parentList) {
        srcList = self.opener.document.choiceForm.parentList;
    } else if(self.opener.document.namecardForm && self.opener.document.namecardForm.parentList) {
        srcList = self.opener.document.namecardForm.parentList;
    } else if(self.opener.window.document.forms[0] && self.opener.window.document.forms[0].parentList) {
        srcList = self.opener.window.document.forms[0].parentList;
    } else {
        return; // parentList를 찾을 수 없으면 종료
    }
    
    for (var count = destList.options.length - 1; count >= 0; count--) {
        destList.options[count] = null;
    }
    for(var i = 0; i < srcList.options.length; i++) { 
        if (srcList.options[i] != null)
            destList.options[i] = new Option(srcList.options[i].text);
    }
} catch(e) {
    console.log('Error in fillInitialDestList:', e);
}
}
// 파일 업로드 한값을 보낸다..
function addSrcToDestList() {
destList = window.document.forms[0].destList;
srcList = window.document.forms[0].srcList; 
var len = destList.length;

// 보내는 곳에서 같은 파일인지의 여부를 검색한다.
for(var i = 0; i < srcList.length; i++) {
if ((srcList.options[i] != null) && (srcList.options[i].selected)) {
var found = false;
for(var count = 0; count < len; count++) {
if (destList.options[count] != null) {
if (srcList.options[i].text == destList.options[count].text) {
alert('동일한 파일이 이미 존재 합니다.');
//found = true;
break;
      }
   }
}
if (found != true) {
destList.options[len] = new Option(srcList.options[i].text); 
len++;
         }
      }
   }
}

</script>
<?php }else{ // 파일여부 제어  ?>

<script language="JavaScript">
function MultyFileUpCheckField()
{
var f=document.MultyFileUpInfo;

if(f.MlangMultyFile.value==""){
alert("파일을 찾아보기를 누르시어 업로드 해주세요");
return false
}

<?php if($Mode=="img"){?>
if((f.MlangMultyFile.value.lastIndexOf(".jpg")==-1) && (f.MlangMultyFile.value.lastIndexOf(".gif")==-1)){
alert("파일 등록은 JPG 와 GIF 형식인 이미지 파일만 하실수 있습니다.")
return false
}
<?php }?>

}
</script>

<?php }?>

</head>

<body <?php if($MlangMultyFile){?>onLoad="javascript:fillInitialDestList(); addSrcToDestList(); addSelectedItemsToParent();"<?php }?> LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>


<?php if($MlangMultyFile){?>

<form>
<select size="0" name="srcList" multiple style='width:0; heiht:0;  border-style:solid;'>
<option value="<?php echo $MlangMultyFile?>" selected><?php echo $MlangMultyFile_name?></option>
</select>
<select size="0" name="destList" multiple style='width:0; heiht:0;  border-style:solid;'></select>
</form>

<?php }else{?>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td bgcolor='#61AFAF' height=30>&nbsp;&nbsp;<font style='font-size:9pt; color:#FFFFFF;'>＊</font>&nbsp;<font style='color:#FFFFFF; font-size:9pt; font:bold;'>파일 업로드</font></td>
       </tr>
     </table>

<table border=0 align=center width=100% cellpadding=10 cellspacing=10>
<form name='MultyFileUpInfo' method='post' enctype='multipart/form-data' OnSubmit='javascript:return MultyFileUpCheckField()' action='FileUp.php'>
<INPUT TYPE="hidden" name='Turi' value='<?php echo $Turi?>'>
<INPUT TYPE="hidden" name='Ty' value='<?php echo $Ty?>'>
<INPUT TYPE="hidden" name='Tmd' value='<?php echo $Tmd?>'>
<INPUT TYPE="hidden" name='Tip' value='<?php echo $Tip?>'>
<INPUT TYPE="hidden" name='Ttime' value='<?php echo $Ttime?>'>

  <tr>
    <td bgcolor='#006666' align='center'>
	  <INPUT TYPE="file" NAME="MlangMultyFile" size='27'>
	</td>
  </tr>

  <tr>
    <td align='center'>
      <INPUT TYPE="submit" value=' 확 인 '>
	</td>
  </tr>

</form>
</table>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td bgcolor='#61AFAF' height=1 width=100%></td>
       </tr>
     </table>

<font style='font-size:8pt; line-height:150%;'><?php echo $WebSoftCopyright2?></font>

<?php }?>

</body>

</html>