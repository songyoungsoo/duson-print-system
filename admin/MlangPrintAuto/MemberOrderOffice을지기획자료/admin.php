<?php
////////////////// ������ �α��� ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////

if($mode=="MlangFileOk"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////

$MAXFSIZE="99999";
$upload_dir="./upload";
	if($check){
		if($photofile_4){ include"upload_4.php"; $fileOk="$photofile_4Name"; }else{ if($file){unlink("$upload_dir/$file");} }
	}else{
		$fileOk="$file";
	}

$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET $code='$fileOk' WHERE no='$no'";
$result= mysql_query($query,$db);
	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;

}
mysql_close($db);


} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="MlangFile"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<html>
<title>�ڷ�÷�� ����</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:����; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='checkbox')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=400,availHeight=250);
</script>

</head>

<body>

 <table border=1 align=center cellpadding=5 cellspacing=0 width=340>
 <form method='post' enctype='multipart/form-data' action='<?php echo $PHP_SELF?>'>
 <input type='hidden' name='file' value='<?php echo $file?>'>
 <input type='hidden' name='no' value='<?php echo $no?>'>
 <input type='hidden' name='code' value='<?php echo $code?>'>
 <input type='hidden' name='mode' value='MlangFileOk'>
   <tr>
     <td colspan=2>* ���� ����������</td>
   </tr>
   <tr>
     <td align=center>�������ϸ�</td>
	 <td><?php echo $file?></td>
   </tr>
   <tr>
     <td align=center>����</td>
	 <td><INPUT TYPE="checkbox" NAME="check"> �����������Ϸ��� üũ�� ���ּ���<BR>
	 <font style='font-family:����; font-size: 8pt; color:#336699;'>* üũ�� ���ε��� ���Ͻø� �����ڷḸ ������.</font>
	 </td>
   </tr>
    <tr>
     <td align=center>���ε�</td>
	 <td><input type='file' name='photofile_4' size='23'></td>
   </tr>
 </table>

 <p align=center>
		 <input type='submit' value=' �����մϴ�.. '>
		 <input type='button' onclick="javascript:window.self.close();" value='â�ݱ�'>
 </p>
 </form>

</body>
</html>

<?php
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<?php
if($mode=="form"){ ///////////////////////////////////////////////////////////////////////////////////////////////////////////
$Bgcolor1="408080";

if($code=="modify" || $code=="Print" || $code=="fff"){include"View.php";

 function str_cutting($str, $len){ 
       preg_match('/([\x00-\x7e]|..)*/', substr($str, 0, $len), $rtn); 
       if ( $len < strlen($str) ) $rtn[0].=".."; 
        return $rtn[0]; 
    } 
}
?>

<html>
<title>"�츮�� �ְ��� ������"</title>

<head>

<style>
td, table{BORDER-COLOR:#707070; border-collapse:collapse; color:#000000; font-size:9pt; FONT-FAMILY:����; line-height:130%; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='checkbox')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<SCRIPT LANGUAGE=JAVASCRIPT src='/admin/js/exchange.js'></SCRIPT>

<script language=javascript>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;

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

function MemberXCheckField()
{
var f=document.FrmUserXInfo;

if (f.One_1.value == "") {
alert("�ۼ����� �Է��Ͽ��ּ���!!");
f.One_1.focus();
return false;
}

if (f.One_3.value == "") {
alert("��ü���� �Է��Ͽ��ּ���!!");
f.One_3.focus();
return false;
}

if (f.One_4.value == "") {
alert("������� �Է��Ͽ��ּ���!!");
f.One_4.focus();
return false;
}

if (f.One_6.value == "") {
alert("����ó�� �Է��Ͽ��ּ���!!");
f.One_6.focus();
return false;
}

if (f.One_7.value == "") {
alert("�ڵ����� �Է��Ͽ��ּ���!!");
f.One_7.focus();
return false;
}

if (f.One_9.value == "") {
alert("�ù����� �Է��Ͽ��ּ���!!");
f.One_9.focus();
return false;
}

}
//////////////// �̹��� �̸����� //////////////////////////////////
/* �ҽ�����: http://www.script.ne.kr - Mlang */
function Mlamg_image(image) {
alert("�Է��Ͻ� ������ ["+ image +"] �Դϴ�.");
}

//////////////// �ݾ� ���� ///////////////////////////////////////////
function MlangMoneyTotal() {
var f=document.FrmUserXInfo;

var Tree_3=f.Tree_3.value; 
var Tree_10=f.Tree_10.value; 
var Tree_6=f.Tree_6.value; 
var Tree_13=f.Tree_13.value; 
if(!Tree_3){Tree_3=0;}
if(!Tree_10){Tree_10=0;}
if(!Tree_6){Tree_6=0;}
if(!Tree_13){Tree_13=0;}

f.Tree_7.value = eval(Tree_3)+eval(Tree_10)+eval(Tree_6)+eval(Tree_13);

}

</script>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=660,availHeight=680);

function MlangWindowOne(code){
	var f=document.FrmUserXInfo;
	var money=f.Tree_7.value;
	if(!code){ alert("�μ��忡���� ����� �����մϴ�.")
		}else{
           if (confirm("�ŷ�����ǥ�� �ΰ����� ���Խ�Ű�ø� [Ȯ��]��\n\n�ΰ����� ���� ���������÷��� [���]��\n\n�����Ͽ��ּ���")) {
		      window.open("int.php?EEE=1&mode=One&code="+code+"&momey="+money,"MlangWindowOne","scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
		   }else{
			  window.open("int.php?EEE=2&mode=One&code="+code+"&momey="+money,"MlangWindowOne","scrollbars=yes,resizable=no,width=400,height=50,top=0,left=0");
		   }

	}
}

function MlangWindowTwo(code){
	if(!code){ alert("�μ��忡���� ����� �����մϴ�.")
		}else{
		popup = window.open("int.php?mode=Two&code="+code,"MlangWindowTwo","scrollbars=no,resizable=yes,width=400,height=50,top=0,left=0");
        popup.focus();
	}
}
</script>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>

<table border=0 align=center width=100% cellpadding=5 cellspacing=0>

<?php if ($mode=="form"){?>
<form name='FrmUserXInfo' enctype='multipart/form-data' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
<INPUT TYPE="hidden" name='mode' value='<?php if ($code=="modify"){?>modify_ok<?}else{?>form_ok<?php } ?>'>
<?php if ($code=="modify"){?><INPUT TYPE="hidden" name='no' value='<?php echo $no?>'><?php } ?>
<?php } ?>

<!---------- One ���� -------------------->
<tr>
<td>
      <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=left>
		 <font style='font-size:10pt; font:bold;'>�ۼ���:</font>&nbsp;<INPUT TYPE="text" NAME="One_1" size=30 <?php if ($View_One_1){echo("value='$View_One_1'");}?> style='font-size:10pt; font:bold; height:22;'>
		 </td>
		 <td align=right>
		 ��ü����:
		 <INPUT TYPE="checkbox" NAME="One_2"  value='1' <?php if ($View_One_2=="1"){echo("checked");}?>>�űԾ�ü
		 <INPUT TYPE="checkbox" NAME="One_2"  value='2' <?php if ($View_One_2=="2"){echo("checked");}?>>�ŷ���ü
		 <INPUT TYPE="checkbox" NAME="One_2"  value='3' <?php if ($View_One_2=="3"){echo("checked");}?>>��û
		 </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=50><font color=red>��ü��</font></td>
		 <td><INPUT TYPE="text" NAME="One_3" size=24 <?php if ($View_One_3){echo("value='$View_One_3'");}?>></td>
		 <td align=center width=50><font color=red>�����</font></td>
		 <td><INPUT TYPE="text" NAME="One_4" size=24 <?php if ($View_One_4){echo("value='$View_One_4'");}?>></td>
		 <td align=center width=50>E-mail</td>
		 <td><INPUT TYPE="text" NAME="One_5" size=24 <?php if ($View_One_5){echo("value='$View_One_5'");}?>></td>
       </tr>
	   <tr>
         <td align=center><font color=red>����ó</font></td>
		 <td><INPUT TYPE="text" NAME="One_6" size=24 <?php if ($View_One_6){echo("value='$View_One_6'");}?>></td>
		 <td align=center><font color=red>�ڵ���</font></td>
		 <td><INPUT TYPE="text" NAME="One_7" size=24 <?php if ($View_One_7){echo("value='$View_One_7'");}?>></td>
		 <td align=center>FAX</td>
		 <td><INPUT TYPE="text" NAME="One_8" size=24 <?php if ($View_One_8){echo("value='$View_One_8'");}?>></td>
       </tr>
	   <tr>
         <td align=center><font color=red>�ù���</font></td>
		 <td colspan=6><INPUT TYPE="text" NAME="One_9" size=68 <?php if ($View_One_9){echo("value='$View_One_9'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!---------- One ���� -------------------->

<!---------- Two ���� -------------------->
<tr>
<td>
<table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td><b>���ֹ��Ƿڻ�Ȳ</b></td>
		 <td align=right>
         ���� <INPUT TYPE="text" NAME="Title" size=58 <?php if ($View_Title){echo("value='$View_Title'");}?>>
         </td>
       </tr>
     </table>
</td>
</tr>

<tr>
<td class='coolBar'>

     <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_1 || $View_Two_2){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_1 || $View_Two_2){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>��ǰ��</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_1"  value='1' <?php if ($View_Two_1=="1"){echo("checked");}?>>�ӴϺ���
            <INPUT TYPE="text" NAME="Two_2" size=8 <?php if ($View_Two_2){echo("value='$View_Two_2'");}?>>
			&nbsp;&nbsp;
			������:<INPUT TYPE="checkbox" NAME="Two_3"  value='1' <?php if ($View_Two_3=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_3"  value='2' <?php if ($View_Two_3=="2"){echo("checked");}?>>��
			����:<INPUT TYPE="text" NAME="Two_4" size=8 <?php if ($View_Two_4){echo("value='$View_Two_4'");}?>>
			�Ǽ�:<INPUT TYPE="text" NAME="Two_5" size=8 <?php if ($View_Two_5){echo("value='$View_Two_5'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_47" size=10 <?php if ($View_Two_47){echo("value='$View_Two_47'");}?>>��</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;
		 �μ�:<INPUT TYPE="checkbox" NAME="Two_6"  value='1' <?php if ($View_Two_6=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_6"  value='2' <?php if ($View_Two_6=="2"){echo("checked");}?>>��
			&nbsp;
		 �İ���:<INPUT TYPE="checkbox" NAME="Two_7_1"  value='1' <?php if ($View_Two_7_1=="1"){echo("checked");}?>>�ѹ���
			<INPUT TYPE="checkbox" NAME="Two_7_2"  value='1' <?php if ($View_Two_7_2=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_7_3"  value='1' <?php if ($View_Two_7_3=="1"){echo("checked");}?>>��ũ��ġ
		 </td>
		 <td align=center><INPUT TYPE="text" NAME="Two_48" size=10 <?php if ($View_Two_48){echo("value='$View_Two_48'");}?>>��</td>
       </tr>
	   <tr>
         <td align=center>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_9 || $View_Two_10){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_9 || $View_Two_10){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>������</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_9"  value='1' <?php if ($View_Two_9=="1"){echo("checked");}?>>A2
			<INPUT TYPE="checkbox" NAME="Two_9"  value='2' <?php if ($View_Two_9=="2"){echo("checked");}?>>4��
			<INPUT TYPE="checkbox" NAME="Two_9"  value='3' <?php if ($View_Two_9=="3"){echo("checked");}?>>2��
			<INPUT TYPE="text" NAME="Two_10" size=6 <?php if ($View_Two_10){echo("value='$View_Two_10'");}?>>
			&nbsp;
			����:<INPUT TYPE="text" NAME="Two_11" size=5 <?php if ($View_Two_11){echo("value='$View_Two_11'");}?>>
			&nbsp;
			������:<INPUT TYPE="checkbox" NAME="Two_12"  value='1' <?php if ($View_Two_12=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_12"  value='2' <?php if ($View_Two_12=="2"){echo("checked");}?>>��
			&nbsp;
			��Ÿ:<INPUT TYPE="text" NAME="Two_13" size=6 <?php if ($View_Two_13){echo("value='$View_Two_13'");}?>>
		 </td>
		 <td align=center><INPUT TYPE="text" NAME="Two_49" size=10 <?php if ($View_Two_49){echo("value='$View_Two_49'");}?>>��</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_14 || $View_Two_15){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_14 || $View_Two_15){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>����,���÷�</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_14"  value='1' <?php if ($View_Two_14=="1"){echo("checked");}?>>A4
			<INPUT TYPE="checkbox" NAME="Two_14"  value='2' <?php if ($View_Two_14=="2"){echo("checked");}?>>16��
			<INPUT TYPE="checkbox" NAME="Two_14"  value='3' <?php if ($View_Two_14=="3"){echo("checked");}?>>A3
			<INPUT TYPE="text" NAME="Two_15" size=6 <?php if ($View_Two_15){echo("value='$View_Two_15'");}?>>
			&nbsp;
			����:<INPUT TYPE="text" NAME="Two_16" size=5 <?php if ($View_Two_16){echo("value='$View_Two_16'");}?>>
			&nbsp;
			������:<INPUT TYPE="checkbox" NAME="Two_17"  value='1' <?php if ($View_Two_17=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_17"  value='2' <?php if ($View_Two_17=="2"){echo("checked");}?>>��
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_50" size=10 <?php if ($View_Two_50){echo("value='$View_Two_50'");}?>>��</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 ����:<INPUT TYPE="checkbox" NAME="Two_18"  value='1' <?php if ($View_Two_18=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_18"  value='2' <?php if ($View_Two_18=="2"){echo("checked");}?>>����
			&nbsp;
		 �İ���:
               <INPUT TYPE="text" NAME="Two_19" size=6 <?php if ($View_Two_19){echo("value='$View_Two_19'");}?>>
			   &nbsp;
               <INPUT TYPE="text" NAME="Two_20" size=38 <?php if ($View_Two_20){echo("value='$View_Two_20'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_21_1 || $View_Two_21_2 || $View_Two_21_3 || $View_Two_21_4 || $View_Two_22){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>����,����</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_21_1"  value='1' <?php if ($View_Two_21_1=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_21_2"  value='1' <?php if ($View_Two_21_2=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_21_3"  value='1' <?php if ($View_Two_21_3=="1"){echo("checked");}?>>�ݴ�
			<INPUT TYPE="checkbox" NAME="Two_21_4"  value='1' <?php if ($View_Two_21_4=="1"){echo("checked");}?>>�ֳ�
			&nbsp;
			����:<INPUT TYPE="text" NAME="Two_22" size=5 <?php if ($View_Two_22){echo("value='$View_Two_22'");}?>>
			�Ǽ�:<INPUT TYPE="text" NAME="Two_23" size=5 <?php if ($View_Two_23){echo("value='$View_Two_23'");}?>>
			&nbsp;
			�μ�:<INPUT TYPE="checkbox" NAME="Two_24"  value='1' <?php if ($View_Two_24=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_24"  value='2' <?php if ($View_Two_24=="2"){echo("checked");}?>>��
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_51" size=10 <?php if ($View_Two_51){echo("value='$View_Two_51'");}?>>��</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 ����:<INPUT TYPE="checkbox" NAME="Two_25"  value='1' <?php if ($View_Two_25=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_25"  value='2' <?php if ($View_Two_25=="2"){echo("checked");}?>>����
			&nbsp;
		 �İ���:
               <INPUT TYPE="text" NAME="Two_26" size=6 <?php if ($View_Two_26){echo("value='$View_Two_26'");}?>>
			   &nbsp;
               <INPUT TYPE="text" NAME="Two_27" size=38 <?php if ($View_Two_27){echo("value='$View_Two_27'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_28 || $View_Two_29){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_28 || $View_Two_29){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>����,�ѹ���</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		    <INPUT TYPE="checkbox" NAME="Two_28"  value='1' <?php if ($View_Two_28=="1"){echo("checked");}?>>A4
			<INPUT TYPE="text" NAME="Two_29" size=6 <?php if ($View_Two_29){echo("value='$View_Two_29'");}?>>
			&nbsp;
			����:<INPUT TYPE="text" NAME="Two_30" size=5 <?php if ($View_Two_30){echo("value='$View_Two_30'");}?>>
			&nbsp;
			�μ�:<INPUT TYPE="checkbox" NAME="Two_31"  value='1' <?php if ($View_Two_31=="1"){echo("checked");}?>>��
			<INPUT TYPE="checkbox" NAME="Two_31"  value='2' <?php if ($View_Two_31=="2"){echo("checked");}?>>��
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_52" size=10 <?php if ($View_Two_52){echo("value='$View_Two_52'");}?>>��</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		 ����:<INPUT TYPE="checkbox" NAME="Two_32"  value='1' <?php if ($View_Two_32=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_32"  value='2' <?php if ($View_Two_32=="2"){echo("checked");}?>>����
			&nbsp;
		 �İ���:<INPUT TYPE="checkbox" NAME="Two_33_1"  value='1' <?php if ($View_Two_33_1=="1"){echo("checked");}?>>�ѹ���
			<INPUT TYPE="checkbox" NAME="Two_33_2"  value='1' <?php if ($View_Two_33_2=="1"){echo("checked");}?>>����
			<INPUT TYPE="checkbox" NAME="Two_33_3"  value='1' <?php if ($View_Two_33_3=="1"){echo("checked");}?>>��ũ��ġ
			&nbsp;
               <INPUT TYPE="text" NAME="Two_34" size=17 <?php if ($View_Two_34){echo("value='$View_Two_34'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_35 || $View_Two_36){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_35 || $View_Two_36){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>ī�ٷα�</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 	<INPUT TYPE="checkbox" NAME="Two_35"  value='1' <?php if ($View_Two_35=="1"){echo("checked");}?>>�μ�
			<INPUT TYPE="text" NAME="Two_36" size=8 <?php if ($View_Two_36){echo("value='$View_Two_36'");}?>>&nbsp;&nbsp;
            ������:<INPUT TYPE="text" NAME="Two_37" size=8 <?php if ($View_Two_37){echo("value='$View_Two_37'");}?>>&nbsp;
			����:<INPUT TYPE="text" NAME="Two_38" size=8 <?php if ($View_Two_38){echo("value='$View_Two_38'");}?>>&nbsp;
			�β�:<INPUT TYPE="text" NAME="Two_39" size=8 <?php if ($View_Two_39){echo("value='$View_Two_39'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_53" size=10 <?php if ($View_Two_53){echo("value='$View_Two_53'");}?>>��</td>
       </tr>
	   <tr>
		 <td colspan=2 nowrap>
		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            ����<INPUT TYPE="checkbox" NAME="Two_40"  value='1' <?php if ($View_Two_40=="1"){echo("checked");}?>>��ö
			<INPUT TYPE="text" NAME="Two_41" size=58 <?php if ($View_Two_41){echo("value='$View_Two_41'");}?>>
		 </td>
		 <td align=center>&nbsp;</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_42 || $View_Two_43){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_42 || $View_Two_43){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>��Ƽī</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 &nbsp;����:<INPUT TYPE="text" NAME="Two_42" size=8 <?php if ($View_Two_42){echo("value='$View_Two_42'");}?>>
		 ũ��:<INPUT TYPE="text" NAME="Two_43" size=8 <?php if ($View_Two_43){echo("value='$View_Two_43'");}?>>
		 &nbsp;
		 <INPUT TYPE="checkbox" NAME="Two_44"  value='1' <?php if ($View_Two_44=="1"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Two_44"  value='2' <?php if ($View_Two_44=="2"){echo("checked");}?>>����
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_54" size=10 <?php if ($View_Two_54){echo("value='$View_Two_54'");}?>>��</td>
       </tr>
	   <tr>
         <td align=center width=80>
		      <table border=0 align=center width=100% cellpadding=0 cellspacing=3>
                <tr><td align=center <?php if ($View_Two_45 || $View_Two_46){?>bgcolor='#000000'<?php } ?> height=22><?php if ($View_Two_45 || $View_Two_46){?><font style='color:#FFFFFF; font:bold;'><?}else{?><b><?php } ?>��Ÿ</font></td></tr>
               </table>
		 </td>
		 <td nowrap>
		 &nbsp;<INPUT TYPE="text" NAME="Two_45" size=12 <?php if ($View_Two_45){echo("value='$View_Two_45'");}?>>&nbsp;
		 <INPUT TYPE="text" NAME="Two_46" size=55 <?php if ($View_Two_46){echo("value='$View_Two_46'");}?>>
		 </td>
		 <td width=90 align=center><INPUT TYPE="text" NAME="Two_55" size=10 <?php if ($View_Two_55){echo("value='$View_Two_55'");}?>>��</td>
       </tr>
     </table>

</td>
</tr>
<!---------- Two ���� -------------------->

<!---------- Tree ���� -------------------->
<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center height=22>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td width=1></td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
       </tr>
	   <tr>
         <td align=center><INPUT TYPE="text" NAME="Tree_1" size=16 <?php if ($View_Tree_1){echo("value='$View_Tree_1'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_2" size=9 <?php if ($View_Tree_2){echo("value='$View_Tree_2'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_3" size=10 <?php if ($View_Tree_3){echo("value='$View_Tree_3'");}?> onKeyup='MlangMoneyTotal()' ONKEYPRESS="if ((event.keyCode<48)||(event.keyCode>57)) event.returnValue=false;"></td>
		 <td width=1></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_4" size=16 <?php if ($View_Tree_4){echo("value='$View_Tree_4'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_5" size=9 <?php if ($View_Tree_5){echo("value='$View_Tree_5'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_6" size=10 <?php if ($View_Tree_6){echo("value='$View_Tree_6'");}?> onKeyup='MlangMoneyTotal()' ONKEYPRESS="if ((event.keyCode<48)||(event.keyCode>57)) event.returnValue=false;"></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_7" size=13 <?php if ($View_Tree_7){echo("value='$View_Tree_7'");}?>></td>
       </tr>
	   <tr>
         <td align=center><INPUT TYPE="text" NAME="Tree_8" size=16 <?php if ($View_Tree_8){echo("value='$View_Tree_8'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_9" size=9 <?php if ($View_Tree_9){echo("value='$View_Tree_9'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_10" size=10 <?php if ($View_Tree_10){echo("value='$View_Tree_10'");}?> onKeyup='MlangMoneyTotal()' ONKEYPRESS="if ((event.keyCode<48)||(event.keyCode>57)) event.returnValue=false;"></td>
		 <td width=1></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_11" size=16 <?php if ($View_Tree_11){echo("value='$View_Tree_11'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_12" size=9 <?php if ($View_Tree_12){echo("value='$View_Tree_12'");}?>></td>
		 <td align=center><INPUT TYPE="text" NAME="Tree_13" size=10 <?php if ($View_Tree_13){echo("value='$View_Tree_13'");}?> onKeyup='MlangMoneyTotal()' ONKEYPRESS="if ((event.keyCode<48)||(event.keyCode>57)) event.returnValue=false;"></td>
		 <td align=center><INPUT TYPE="button" onclick="javascript:MlangWindowOne('<?php echo $no?>');" style='width:50;' value='����ǥ'><INPUT TYPE="button" onclick="javascript:MlangWin<?php echo Two('<?=$no?>');" style='width:40;' value='���'></td>
       </tr>
     </table>
</td>
</tr>
<!---------- Tree ���� -------------------->


<!---------- Four ���� -------------------->
<tr>
<td><b>�����,��ۻ�Ȳ</b></td>
</tr>

<tr>
<td class='coolBar'>
     <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center><font color=red>&nbsp;&nbsp;��&nbsp;��&nbsp;��&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_1" size=19 <?php if ($View_Four_1){echo("value='$View_Four_1'");}?>></td>
		 <td align=center><font color=red>&nbsp;&nbsp;��&nbsp;��&nbsp;��&nbsp&nbsp;</font></td>
		 <td align=center><INPUT TYPE="text" NAME="Four_2" size=26 <?php if ($View_Four_2){echo("value='$View_Four_2'");}?>></td>
		 <td align=center>&nbsp;&nbsp;���ݰ�꼭&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='1' <?php if ($View_Four_3=="1"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Four_3"  value='2' <?php if ($View_Four_3=="2"){echo("checked");}?>>�̹���
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;�Ա��Ѿ�&nbsp;<BR>&nbsp;<font style='font-family:����; font-size:8pt;'>(�ΰ�������)</font>&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_4" size=19 <?php if ($View_Four_4){echo("value='$View_Four_4'");}?>></td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_5" size=26 <?php if ($View_Four_5){echo("value='$View_Four_5'");}?>></td>
		 <td align=center>&nbsp;&nbsp;��ۿ��&nbsp;&nbsp;</td>
		 <td>
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='1' <?php if ($View_Four_6=="1"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Four_6"  value='2' <?php if ($View_Four_6=="2"){echo("checked");}?>>����
		 </td>
       </tr>
	   <tr>
         <td align=center>&nbsp;��&nbsp;��&nbsp;��</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_7" size=19 <?php if ($View_Four_7){echo("value='$View_Four_7'");}?>></td>
		 <td align=center>��&nbsp;&nbsp;��</td>
		 <td align=center><font style='font-family:����; font-size:8pt;'>
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='1' <?php if ($View_Four_8=="1"){echo("checked");}?>>�ù�
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='2' <?php if ($View_Four_8=="2"){echo("checked");}?>>��
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='3' <?php if ($View_Four_8=="3"){echo("checked");}?>>ȭ��
		 <INPUT TYPE="checkbox" NAME="Four_8"  value='4' <?php if ($View_Four_8=="4"){echo("checked");}?>>�湮
		 </font></td>
		 <td align=center>&nbsp;&nbsp;�Ϻ�Ȯ��&nbsp;&nbsp;</td>
		 <td align=center><INPUT TYPE="text" NAME="Four_9" size=15 <?php if ($View_Four_9){echo("value='$View_Four_9'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!----------  -------------------->

<!----------  -------------------->
<tr>
<td><b>���۾������Ȳ</b></td>
</tr>

<tr>
<td class='coolBar'>
      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
	  <tr>
         <td align=center>&nbsp;������&nbsp</td>
		 <td colspan=2>
         <INPUT TYPE="checkbox" NAME="Five_1"  value='1' <?php if ($View_Five_1=="1"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='2' <?php if ($View_Five_1=="2"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Five_1"  value='3' <?php if ($View_Five_1=="3"){echo("checked");}?>>��Ÿ
		 <INPUT TYPE="text" NAME="Five_2" size=16 <?php if ($View_Five_2){echo("value='$View_Five_2'");}?>>
		 </td>
		 <td align=center>&nbsp;��������&nbsp</td>
		 <td colspan=2>&nbsp;<INPUT TYPE="text" NAME="Five_3" size=16 <?php if ($View_Five_3){echo("value='$View_Five_3'");}?>></td>
       </tr>
       <tr>
         <td align=center>&nbsp;��&nbsp��&nbsp��&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_4" size=16 <?php if ($View_Five_4){echo("value='$View_Five_4'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;��&nbsp��&nbsp��&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_5" size=16 <?php if ($View_Five_5){echo("value='$View_Five_5'");}?> onClick="Calendar(this);"></td>
		 <td align=center>&nbsp;��&nbsp;&nbsp;��&nbsp</td>
		 <td>&nbsp;<INPUT TYPE="text" NAME="Five_6" size=16 <?php if ($View_Five_6){echo("value='$View_Five_6'");}?>></td>
       </tr>
     </table>
</td>
</tr>
<!---------- -------------------->

<!----------  -------------------->

<tr>
<td class='coolBar'>

     <table border=0 align=center width=100% cellpadding=0 cellspacing=0>
       <tr>
         <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=120>
              <tr>
                <td align=center colspan=2 height=22><b>�����μ� �Ƿ�ó</b></td>
              </tr>
			  <tr>
                <td colspan=2>
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='1' <?php if ($View_Five_7=="1"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='2' <?php if ($View_Five_7=="2"){echo("checked");}?>>����
		 <INPUT TYPE="checkbox" NAME="Five_7"  value='3' <?php if ($View_Five_7=="3"){echo("checked");}?>>��Ÿ
		 <INPUT TYPE="text" NAME="Five_8" size=8 <?php if ($View_Five_8){echo("value='$View_Five_8'");}?>>
				</td>
              </tr>
			  <tr>
                <td align=center>&nbsp;&nbsp;�ݾ�&nbsp;&nbsp;</td>
				<td>&nbsp;<INPUT TYPE="text" NAME="Five_9" size=24 <?php if ($View_Five_9){echo("value='$View_Five_9'");}?>></td>
              </tr>
			  <tr>
                <td align=center>����<BR>����</td>
				<td>&nbsp;<TEXTAREA NAME="Five_10" ROWS="3" COLS="25"><?php if ($View_Five_10){echo("$View_Five_10");}?></TEXTAREA></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
          <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=120>
              <tr>
                <td align=center colspan=2 height=22><b>�����μ� �Ƿ�ó</b></td>
              </tr>
			   <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;������&nbsp;&nbsp;</td>
				<td>
                <INPUT TYPE="checkbox" NAME="Five_13"  value='1' <?php if ($View_Five_13=="1"){echo("checked");}?>>����
				<INPUT TYPE="text" NAME="Five_14" size=15 <?php if ($View_Five_14){echo("value='$View_Five_14'");}?>>
				</td>
              </tr>
			  <tr>
				<td>&nbsp;������:&nbsp;<INPUT TYPE="text" NAME="Five_15" size=15 <?php if ($View_Five_15){echo("value='$View_Five_15'");}?>></td>
              </tr>
			  <tr>
                <td align=center rowspan=2>&nbsp;&nbsp;�μ�ó&nbsp;&nbsp;</td>
				<td height=25>
                <INPUT TYPE="checkbox" NAME="Five_16"  value='1' <?php if ($View_Five_16=="1"){echo("checked");}?>>����
				<INPUT TYPE="text" NAME="Five_17" size=15 <?php if ($View_Five_17){echo("value='$View_Five_17'");}?>>
				</td>
              </tr>
			  <tr>
				<td height=25>&nbsp;�μ��:&nbsp;<INPUT TYPE="text" NAME="Five_18" size=15 <?php if ($View_Five_18){echo("value='$View_Five_18'");}?>></td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
		 <td></td>
		 <td align=center width=32% valign=top>
           <!-----^^^^^^^------->
		    <table border=1 align=center width=100% cellpadding=0 cellspacing=0 height=120>
              <tr>
				<td align=center>�ڷ� ÷��</td>
              </tr>
			  <tr>
				<td align=center>

<script>
function ImgMlangGo(fileurl,code){
	var str;
		if (confirm("[Ȯ��]�����ø� ������ �ٿ�ε尡���� â�� �߰�\n\n[�ּ�]�� �����ø� ������ �����ϽǼ� �ֽ��ϴ�.")) {
		window.open("./upload/"+fileurl,"fileurlged","scrollbars=no,resizable=yes,width=600,height=500,top=0,left=0");
	   }else{
        popup = window.open("<?php echo $PHP_SELF?>?mode=MlangFile&file="+fileurl+"&code="+code+"&no=<?php echo $no?>","Mlangdhdimodu","scrollbars=no,resizable=no,width=400,height=150,top=0,left=0");
        popup.focus();
	   }
}
</script>

<?php if ($View_Five_22){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_22?>','Five_22');"><?php echo str_cutting("$View_Five_22",26)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_1" size=15 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_23){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_23?>','Five_23');"><?php echo str_cutting("$View_Five_23",26)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_2" size=15 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_24){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_24?>','Five_24');"><?php echo str_cutting("$View_Five_24",26)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_3" size=15 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>
<?php if ($View_Five_25){?>&nbsp;<a href="javascript:ImgMlangGo('<?php echo $View_Five_25?>','Five_25');"><?php echo str_cutting("$View_Five_25",26)?></a><BR><?}else{?>
<INPUT TYPE="file" NAME="photofile_4" size=15 onChange="Mlamg_image(this.value)"><BR>
<?php } ?>

				</td>
              </tr>
            </table>
		  <!-----^^^^^^^------->
         </td>
	  </tr>
    </table>

</td>
</tr>
	  <tr>
	    <td align=cemter><TEXTAREA NAME="Five_21" ROWS="6" COLS="103"><?php if ($View_Five_21){echo("$View_Five_21");}?></TEXTAREA></td>
	  </tr>
<!---------- Five ���� -------------------->


<tr>
<td align=right>
         <INPUT TYPE="checkbox" NAME="Five_26"  value='1' <?php if ($View_Five_26=="1"){echo("checked");}?>>��������
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='2' <?php if ($View_Five_26=="2"){echo("checked");}?>>������
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='3' <?php if ($View_Five_26=="3"){echo("checked");}?>>�μ���
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='4' <?php if ($View_Five_26=="4"){echo("checked");}?>>������
		 <INPUT TYPE="checkbox" NAME="Five_26"  value='5' <?php if ($View_Five_26=="5"){echo("checked");}?>>��ǰ
</td>
</tr>

<?php if ($code=="Print"){}else{if($mode=="form"){?>
<tr>
<td align=center>
<input type='submit' value=' <?php if ($code=="modify"){?>����<?}else{?>����<?php } ?> �մϴ�.'>
<BR><BR>
</td>
</tr>
<?}}?>

</table>

<? } //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="form_ok"){

	$result = mysql_query("SELECT max(no) FROM MlangPrintAuto_MemberOrderOffice");
	if (!$result) {
		echo "
			<script>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1)
			</script>";
		exit;
	}
	$row = mysql_fetch_row($result);

	if($row[0]) {
	   $new_no = $row[0] + 1;
	} else {
	   $new_no = 1;
	}   
############################################

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php"; }
if($photofile_2){ include"upload_2.php"; }
if($photofile_3){ include"upload_3.php"; }
if($photofile_4){ include"upload_4.php"; }


$date=date("Y-m-d H:i;s");

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$dbinsert ="insert into MlangPrintAuto_MemberOrderOffice values('$new_no',
'$One_1',
'$One_2',
'$One_3',
'$One_4',
'$One_5',
'$One_6',
'$One_7',
'$One_8',
'$One_9',
'$One_10',
'$One_11',
'$One_12', 
'$Two_1',
'$Two_2',
'$Two_3',
'$Two_4',
'$Two_5',
'$Two_6',
'$Two_7',
'$Two_8',
'$Two_9',
'$Two_10',
'$Two_11',
'$Two_12',
'$Two_13',
'$Two_14',
'$Two_15',
'$Two_16',
'$Two_17',
'$Two_18',
'$Two_19',
'$Two_20',
'$Two_21',
'$Two_22',
'$Two_23',
'$Two_24',
'$Two_25',
'$Two_26',
'$Two_27',
'$Two_28',
'$Two_29',
'$Two_30',
'$Two_31',
'$Two_32',
'$Two_33',
'$Two_34',
'$Two_35',
'$Two_36',
'$Two_37',
'$Two_38',
'$Two_39',
'$Two_40',
'$Two_41',
'$Two_42',
'$Two_43',
'$Two_44',
'$Two_45',
'$Two_46',
'$Two_47',
'$Two_48',
'$Two_49',
'$Two_50',
'$Two_51',
'$Two_52',
'$Two_53',
'$Two_54',
'$Two_55',
'$Two_56',
'$Two_57',
'$Two_58',
'$Tree_1',
'$Tree_2',
'$Tree_3',
'$Tree_4',
'$Tree_5',
'$Tree_6',
'$Tree_7',
'$Tree_8',
'$Tree_9',
'$Tree_10',
'$Tree_11',
'$Tree_12',
'$Tree_13',
'$Tree_14',
'$Tree_15', 
'$Four_1',
'$Four_2',
'$Four_3',
'$Four_4',
'$Four_5',
'$Four_6',
'$Four_7',
'$Four_8',
'$Four_9',
'$Four_10',
'$Four_11',
'$Four_12', 
'$Five_1',
'$Five_2',
'$Five_3',
'$Five_4',
'$Five_5',
'$Five_6',
'$Five_7',
'$Five_8',
'$Five_9',
'$Five_10',
'$Five_11',
'$Five_12',
'$Five_13',
'$Five_14',
'$Five_15',
'$Five_16',
'$Five_17',
'$Five_18',
'$Five_19',
'$Five_20',
'$Five_21',
'$photofile_1Name',
'$photofile_2Name',
'$photofile_3Name',
'$photofile_4Name',
'$Five_26',
'$Five_27',
'$Five_28',
'$Five_29',
'$cont',
'$date',
'$Title'
)";
$result_insert= mysql_query($dbinsert,$db);

	echo ("
		<script language=javascript>
		alert('\\n�ڷḦ ���������� ���� �Ͽ����ϴ�.\\n\\n�ڷḦ ���� ����Ͻ÷��� â�� �ٽ� ������\\n');
        opener.parent.location.reload();
        window.self.close();
		</script>
	");
		exit;


} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="modify_ok"){

$MAXFSIZE="99999";
$upload_dir="./upload";

if($photofile_1){ include"upload_1.php";  $Five_22KKok="Five_22='$photofile_1Name',"; }
if($photofile_2){ include"upload_2.php";  $Five_23KKok="Five_23='$photofile_2Name',";  }
if($photofile_3){ include"upload_3.php";  $Five_24KKok="Five_24='$photofile_3Name',";  }
if($photofile_4){ include"upload_4.php";  $Five_25KKok="Five_25='$photofile_4Name',";  }

$Two_7="${Two_7_1}-${Two_7_2}-${Two_7_3}";
$Two_21="${Two_21_1}-${Two_21_2}-${Two_21_3}-${Two_21_4}";
$Two_33="${Two_33_1}-${Two_33_2}-${Two_33_3}";
$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET
One_1='$One_1',
One_2='$One_2',
One_3='$One_3',
One_4='$One_4',
One_5='$One_5',
One_6='$One_6',
One_7='$One_7',
One_8='$One_8',
One_9='$One_9',
One_10='$One_10',
One_11='$One_11',
One_12='$One_12',
Two_1='$Two_1',
Two_2='$Two_2',
Two_3='$Two_3',
Two_4='$Two_4',
Two_5='$Two_5',
Two_6='$Two_6',
Two_7='$Two_7',
Two_8='$Two_8',
Two_9='$Two_9',
Two_10='$Two_10',
Two_11='$Two_11',
Two_12='$Two_12',
Two_13='$Two_13',
Two_14='$Two_14',
Two_15='$Two_15',
Two_16='$Two_16',
Two_17='$Two_17',
Two_18='$Two_18',
Two_19='$Two_19',
Two_20='$Two_20',
Two_21='$Two_21',
Two_22='$Two_22',
Two_23='$Two_23',
Two_24='$Two_24',
Two_25='$Two_25',
Two_26='$Two_26',
Two_27='$Two_27',
Two_28='$Two_28',
Two_29='$Two_29',
Two_30='$Two_30',
Two_31='$Two_31',
Two_32='$Two_32',
Two_33='$Two_33',
Two_34='$Two_34',
Two_35='$Two_35',
Two_36='$Two_36',
Two_37='$Two_37',
Two_38='$Two_38',
Two_39='$Two_39',
Two_40='$Two_40',
Two_41='$Two_41',
Two_42='$Two_42',
Two_43='$Two_43',
Two_44='$Two_44',
Two_45='$Two_45',
Two_46='$Two_46',
Two_47='$Two_47',
Two_48='$Two_48',
Two_49='$Two_49',
Two_50='$Two_50',
Two_51='$Two_51',
Two_52='$Two_52',
Two_53='$Two_53',
Two_54='$Two_54',
Two_55='$Two_55',
Two_56='$Two_56',
Two_57='$Two_57',
Two_58='$Two_58',
Tree_1='$Tree_1',
Tree_2='$Tree_2',
Tree_3='$Tree_3',
Tree_4='$Tree_4',
Tree_5='$Tree_5',
Tree_6='$Tree_6',
Tree_7='$Tree_7',
Tree_8='$Tree_8',
Tree_9='$Tree_9',
Tree_10='$Tree_10',
Tree_11='$Tree_11',
Tree_12='$Tree_12',
Tree_13='$Tree_13',
Tree_14='$Tree_14',
Tree_15='$Tree_15',
Four_1='$Four_1',
Four_2='$Four_2',
Four_3='$Four_3',
Four_4='$Four_4',
Four_5='$Four_5',
Four_6='$Four_6',
Four_7='$Four_7',
Four_8='$Four_8',
Four_9='$Four_9',
Four_10='$Four_10',
Four_11='$Four_11',
Four_12='$Four_12',
Five_1='$Five_1',
Five_2='$Five_2',
Five_3='$Five_3',
Five_4='$Five_4',
Five_5='$Five_5',
Five_6='$Five_6',
Five_7='$Five_7',
Five_8='$Five_8',
Five_9='$Five_9',
Five_10='$Five_10',
Five_11='$Five_11',
Five_12='$Five_12',
Five_13='$Five_13',
Five_14='$Five_14',
Five_15='$Five_15',
Five_16='$Five_16',
Five_17='$Five_17',
Five_18='$Five_18',
Five_19='$Five_19',
Five_20='$Five_20',
Five_21='$Five_21', $Five_22KKok $Five_23KKok $Five_24KKok $Five_25KKok
Five_26='$Five_26',
Five_27='$Five_27',
Five_28='$Five_28',
Five_29='$Five_29',
Title='$Title'
WHERE no='$no'";
$result= mysql_query($query,$db);


	if(!$result) {
		echo "
			<script language=javascript>
				window.alert(\"DB ���� �����Դϴ�!\")
				history.go(-1);
			</script>";
		exit;

} else {
	
	echo ("
		<script language=javascript>
		alert('\\n������ ���������� �����Ͽ����ϴ�.\\n');
		opener.parent.location.reload();
		</script>
		<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=form&code=modify&no=$no'>
	");
		exit;

}
mysql_close($db);


}
?>