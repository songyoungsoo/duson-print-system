<?php
////////////////// ������ �α��� ////////////////////
include"../../../db.php";
include"../../config.php";
////////////////////////////////////////////////////

$no="$code";
include"View.php";

if($mode=="bizinfo"){ //��������� ó��

   if($form=="ok"){
	   include"../../../db.php";
       $MAXFSIZE="99999";
       $upload_dir="./upload";  
       if($photofile_1){ include"upload_1.php"; }
	   $query ="UPDATE MlangPrintAuto_BizInfo SET MlangFild_1='$MlangFild_1', MlangFild_2='$MlangFild_2', MlangFild_3='$MlangFild_3', MlangFild_4='$MlangFild_4', MlangFild_5='$MlangFild_5', MlangFild_6='$MlangFild_6', MlangFild_7='$photofile_1Name' WHERE no='1'";
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
		</script>
        <meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=bizinfo'>
	");
		exit;

       }
       mysql_close($db);

   }else{

include"BizInfoView.php";
?>

<html>
<title>�������������</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:����; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=400,availHeight=320);
</script>

</head>

<body>

 <table border=1 align=center cellpadding=5 cellspacing=0 width=340>
 <form method='post' enctype='multipart/form-data' action='<?php echo $PHP_SELF?>'>
 <input type='hidden' name='form' value='ok'>
 <input type='hidden' name='mode' value='bizinfo'>
   <tr>
     <td align=center colspan=4>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��</td>
   </tr>
   <tr>
     <td align=center>��Ϲ�ȣ</td>
	 <td colspan=3><input type='text' name='MlangFild_1' size='39' <?php if ($View_MlangFild_1){echo("value='$View_MlangFild_1'");}?>></td>
   </tr>
   <tr>
     <td align=center>��ȣ</td>
	 <td align=center><input type='text' name='MlangFild_2' size='15' <?php if ($View_MlangFild_2){echo("value='$View_MlangFild_2'");}?>></td>
	 <td align=center>����</td>
	 <td align=center><input type='text' name='MlangFild_3' size='15' <?php if ($View_MlangFild_3){echo("value='$View_MlangFild_3'");}?>></td>
   </tr>
   <tr>
     <td align=center>������ּ�</td>
	 <td colspan=3><input type='text' name='MlangFild_4' size='39' <?php if ($View_MlangFild_4){echo("value='$View_MlangFild_4'");}?>></td>
   </tr>
   <tr>
     <td align=center>����</td>
	 <td align=center><input type='text' name='MlangFild_5' size='15' <?php if ($View_MlangFild_5){echo("value='$View_MlangFild_5'");}?>></td>
	 <td align=center>����</td>
	 <td align=center><input type='text' name='MlangFild_6' size='15' <?php if ($View_MlangFild_6){echo("value='$View_MlangFild_6'");}?>></td>
   </tr>
    <tr>
     <td align=center>����</td>
	 <td colspan=3><input type='file' name='photofile_1' size='23' <?php if ($View_MlangFild_7){echo("value='$View_MlangFild_7'");}?>><BR>
	 �̹����� ũ���� 50X50 �ȼ��� ���ּ���
	 </td>
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
   }
	
} ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


if($mode=="One"){  //����ǥ

include"BizInfoView.php";
?>

<html>
<title>"���ǵ�����"</title>

<head>
<style>
td, table{BORDER-COLOR:#000000; border-collapse:collapse; color:#000000; font-size:10pt; FONT-FAMILY:����; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=600,availHeight=780);
</script>

</head>

<body LEFTMARGIN='10' TOPMARGIN='10' MARGINWIDTH='10' MARGINHEIGHT='10'>

     <table border=1 align=center cellpadding=0 cellspacing=0 width=540>

       <tr>
         <td>
		      <table border=1 align=center width=100% cellpadding=0 cellspacing=0>
               <tr>
                 <td align=right width=100>&nbsp;��&nbsp;</td>
				 <td align=right width=100>&nbsp;ȣ&nbsp;</td>
				 <td align=center width=340 height=40 colspan=4><font style='font:bold; font-size:16pt;'>��&nbsp;��&nbsp;��&nbsp;��&nbsp;ǥ</font></td>
               </tr>
			   <tr>
                 <td align=right rowspan=2 colspan=2 height=30>
<?php
$y=substr($View_date, 0,10);
$b = explode("-", $y);
$z = trim($b[0]);
$x = trim($b[1]);
$c = $b[2];
echo("<font style='font-size:11pt;'>${z}�� ${x}�� ${c}��</font>");
?>&nbsp;
				 </td>
				 <td align=center colspan=4  height=30>��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;��</td>
               </tr>
			   <tr>
				 <td align=center height=30>��Ϲ�ȣ</td>
				 <td align=center colspan=3><b><?php echo $View_MlangFild_1?></b></td>
               </tr>
			   <tr>
                 <td align=right rowspan=2 colspan=2 height=30>
				<font style='font-size:11pt;'><?php echo $View_One_3?><b>&nbsp;����</b></font>&nbsp;
				 </td>
				 <td align=center height=30>��ȣ</td>
				 <td align=center><font style='font-size:11pt; font:bold;'><?php echo $View_MlangFild_2?></font></td>
				 <td align=center>&nbsp;����&nbsp;</td>
				 <td>&nbsp;<font style='font-size:11pt;'><?php echo $View_MlangFild_3?></font>&nbsp;
<div style="position:absolute; top:103; visibility:visible;"><img src='./upload/<?php echo $View_MlangFild_7?>' width=50 height=50></div>
				 &nbsp;&nbsp;
				 </td>
               </tr>
			   <tr>
				 <td align=center height=30>&nbsp;������ּ�&nbsp;</td>
				 <td align=center colspan=3><?php echo $View_MlangFild_4?></td>
               </tr>
			   <tr>
                 <td align=center colspan=2 height=30>
				 <b>�Ʒ���&nbsp;&nbsp;����&nbsp;&nbsp;����մϴ�.</b>
				 </td>
				 <td align=center height=30>����</td>
				 <td align=center><?php echo $View_MlangFild_5?></td>
				 <td align=center>����</td>
				 <td align=center><?php echo $View_MlangFild_6?></td>
               </tr>
			   <tr>
				 <td colspan=6 height=50>&nbsp;&nbsp;
                 <font style='font-size:11pt;'>
				   <b>�հ�ݾ�&nbsp;<big><?$T = "$View_Tree_7"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></big>&nbsp;��</b>
				 <?php if ($EEE=="1"){?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ΰ���: <?$View_Tree_7Ok=$View_Tree_7 * 10/100; $T = "$View_Tree_7Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?>��<?php } ?></font>
                  </td>
               </tr>
             </table>

		 </td>
       </tr>

	   <tr><td height=5></td></tr>

       <tr>
         <td>
		      <table border=1 align=center width=100% cellpadding=5 cellspacing=0>
               <tr>
                 <td align=center width='35%'>ǰ��</td>
				 <td align=center width='15%'>����</td>
				 <td align=center width='15%'>�ܰ�</td>
				 <td align=center width='20%'>���ް���</td>
				 <td align=center width='15%'>����</td>
               </tr>
			   <tr>
                 <td align=center><?php echo $View_Tree_1?></td>
				 <td align=center><?php echo $View_Tree_2?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_3"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?php if ($EEE=="1"){?><?$View_Tree_3Ok=$View_Tree_3 * 10/100; $T = "$View_Tree_3Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?php } ?></td>
               </tr>
			   <tr>
                 <td align=center><?php echo $View_Tree_4?></td>
				 <td align=center><?php echo $View_Tree_5?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_6"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?php if ($EEE=="1"){?><?$View_Tree_6Ok=$View_Tree_6 * 10/100; $T = "$View_Tree_6Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?php } ?></td>
               </tr>
			   <tr>
                 <td align=center><?php echo $View_Tree_8?></td>
				 <td align=center><?php echo $View_Tree_9?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_10"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?php if ($EEE=="1"){?><?$View_Tree_10Ok=$View_Tree_10 * 10/100; $T = "$View_Tree_10Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?php } ?></td>
               </tr>
			   <tr>
                 <td align=center><?php echo $View_Tree_11?></td>
				 <td align=center><?php echo $View_Tree_12?></td>
				 <td align=center>&nbsp;</td>
				 <td align=right><?$T = "$View_Tree_13"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?php if ($EEE=="1"){?><?$View_Tree_13Ok=$View_Tree_13 * 10/100; $T = "$View_Tree_13Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?php } ?></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>&nbsp;</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=center></td>
               </tr>
			   <tr>
                 <td align=center>�հ�</td>
				 <td align=center></td>
				 <td align=center></td>
				 <td align=right><?$T = "$View_Tree_7"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?></td>
				 <td align=right><?php if ($EEE=="1"){?><?$View_Tree_7Ok=$View_Tree_7 * 10/100; $T = "$View_Tree_7Ok"; $T = number_format($T);  echo("$T"); $T = str_replace(",","",$T);?><?php } ?></td>
               </tr>
             </table>
		 </td>
       </tr>

	   <tr>
         <td>&nbsp;&nbsp;<font style='font-size:11pt;'>���</font>
		 <TEXTAREA NAME="cont" ROWS="10" COLS="81"><?php echo $View_cont?></TEXTAREA>
		 </td>
       </tr>
     </table>

</body>
</html>

<?php
} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($mode=="Two"){  //���

	if($Formok=="ok"){
include"../../../db.php";
 // �ڷḦ �����Ѵ�..
$query ="UPDATE MlangPrintAuto_MemberOrderOffice SET cont='$cont' WHERE no='$code'";
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
		</script>
<meta http-equiv='Refresh' content='0; URL=$PHP_SELF?mode=Two&code=$code'>
	");
		exit;

}

mysql_close($db);


    }else{	
?>

<html>
<title>"�츮�� �ְ��� ������"</title>

<head>
<style>
td, table{BORDER-COLOR:#707070; border-collapse:collapse; color:#000000; font-size:9pt; FONT-FAMILY:����; line-height:130%; word-break:break-all;}
input, TEXTAREA {color:#000000; font-size:9pt; border: expression( (this.type=='checkbox'||this.type=='radio')?'':'1px solid #444444' ); vertical-align:middle;} 
TEXTAREA {overflow:hidden} 
</style>

<script>
self.moveTo(0,0);
self.resizeTo(availWidth=560,availHeight=300);
</script>

</head>

<body>

     <table border=0 align=center cellpadding=0 cellspacing=0>
	   <form method='post' action='<?php echo $PHP_SELF?>'>
	   <input type='hidden' name='Formok' value='ok'>
	   <input type='hidden' name='code' value='<?php echo $code?>'>
	   <input type='hidden' name='mode' value='Two'>
       <tr>
         <td height=25><b>����� ������ �Է��ϼ���</b></td>
       </tr>
	   <tr>
         <td>
		 <TEXTAREA NAME="cont" ROWS="10" COLS="80"><?php echo $View_cont?></TEXTAREA>
		 </td>
       </tr>
	   <tr>
         <td align=center height=50>
		 <input type='submit' value=' �����մϴ�.. '>
		 <input type='button' onclick="javascript:window.self.close();" value='â�ݱ�'>
		 </td>
       </tr>
	   </form>
     </table>

</body>
</html>


<?php
	}

} ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>