<HEAD>
<SCRIPT LANGUAGE="JavaScript">
self.moveTo(0,0)
self.resizeTo(availWidth=350,availHeight=260)

function Activity(name, list){
this.name = name;
this.list = list;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

var acts = new Array();

<?php
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>
acts[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php 
              $result_Two= mysqli_query($db, "select * from $GGTABLE where TreeNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) { 
                                echo("'{$row_Two['title']}',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
?>


var VL = new Array();

<?php
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>
VL[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php 
              $result_Two= mysqli_query($db, "select * from $GGTABLE where TreeNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) { 
                                echo("'{$row_Two['no']}',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
?>

 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var actsmyListTreeSelect = new Array();

<?php
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>
actsmyListTreeSelect[<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php 
              $result_Two= mysqli_query($db, "select * from $GGTABLE where BigNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) { 
                                echo("'{$row_Two['title']}',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
?>

var VLmyListTreeSelect = new Array();

<?php
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
	$g=0;
while($row= mysqli_fetch_array($result)) { 
?>
VLmyListTreeSelect [<?php echo $g?>] = new Activity('<?php echo $row['no']?>', [<?php 
              $result_Two= mysqli_query($db, "select * from $GGTABLE where BigNo='{$row['no']}' order by no asc");
                $rows_Two=mysqli_num_rows($result_Two);
                  if($rows_Two){
                     while($row_Two= mysqli_fetch_array($result_Two)) { 
                                echo("'{$row_Two['no']}',");
                               }  }
      ?>'==================']);

<?php 	$g++;
}

}else{echo("<option>등록자료  없음</option>");}
?>


function updateList(str){

var frm = document.myForm;
var oriLen = frm.myList.length;
var numActs;

for (var i = 0; i < acts.length; i++){
if (str == acts[i].name) {
numActs = acts[i].list.length;
for (var j = 0; j < numActs; j++)
frm.myList.options[j] = new Option(acts[i].list[j], VL[i].list[j]);
for (var j = numActs; j < oriLen; j++)
frm.myList.options[numActs] = null;
}
}

/////////////////////////

var myListTreeSelectfrm = document.myForm;
var myListTreeSelectoriLen = myListTreeSelectfrm.myListTreeSelect.length;
var nummyListTreeSelectActs;

for (var i = 0; i < actsmyListTreeSelect .length; i++){
if (str == actsmyListTreeSelect[i].name) {
nummyListTreeSelectActs = actsmyListTreeSelect[i].list.length;
for (var j = 0; j < nummyListTreeSelectActs; j++)
myListTreeSelectfrm.myListTreeSelect.options[j] = new Option(actsmyListTreeSelect[i].list[j], VLmyListTreeSelect[i].list[j]);
for (var j = nummyListTreeSelectActs; j < myListTreeSelectoriLen; j++)
myListTreeSelectfrm.myListTreeSelect.options[nummyListTreeSelectActs] = null;
}
}



}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

</SCRIPT>

</HEAD>

<FORM NAME="myForm" method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'> 

<?php if($code=="Modify"){?>
<INPUT TYPE="hidden" name='mode' value='Modify_ok'>
<INPUT TYPE="hidden" name='no' value='<?php echo $no?>'>
<?php }else{?>
<INPUT TYPE="hidden" name='mode' value='form_ok'>
<?php }?>

<INPUT TYPE="hidden" name='Ttable' value='<?php echo $Ttable?>'>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>구분&nbsp;&nbsp;</td>
<td>
<select name=RadOne onChange='updateList(this.value)'>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and BigNo='0' order by no asc");
$rows=mysqli_num_rows($result);
if($rows){
$r=0;
while($row= mysqli_fetch_array($result)) 
{ 
?>
<option value='<?php echo $row['no']?>' <?php if($code=="Modify"){if($MlangPrintAutoFildView_style==$row['no']){echo("selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'");}}?>><?php echo $row['title']?></option>
<?php 	$r++;
}

}else{echo("<option>등록자료  없음</option>");}
?>
</select>
</td>
</tr>

<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>규격&nbsp;&nbsp;</td>
<td>
<select name=myListTreeSelect>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php if($code=="Modify"){if($MlangPrintAutoFildView_TreeSelect){echo("<option value='$MlangPrintAutoFildView_TreeSelect' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");
$result=mysqli_query($db, "select * from $GGTABLE where no='$MlangPrintAutoFildView_TreeSelect'");
$row= mysqli_fetch_array($result);
if($row){ echo($row['title']); }
echo("</option>");}}?>
</select>
</SELECT>
</td>
</tr>


<tr>
<td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>종이종류&nbsp;&nbsp;</td>
<td>
<select name=myList>
<option value='#'>:::::: 선택하세요 ::::::</option>
<?php if($code=="Modify"){if($MlangPrintAutoFildView_Section){echo("<option value='$MlangPrintAutoFildView_Section' selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'>");
$result=mysqli_query($db, "select * from $GGTABLE where no='$MlangPrintAutoFildView_Section'");
$row= mysqli_fetch_array($result);
if($row){ echo($row['title']); }
echo("</option>");}}?>
</select>
</SELECT>
</td>
</tr>
