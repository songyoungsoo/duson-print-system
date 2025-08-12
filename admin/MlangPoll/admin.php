<SCRIPT LANGUAGE="JavaScript">
self.resizeTo(availWidth=650,screen.availHeight)
</SCRIPT>

<?php
include "polldb.php";
include "pollconfig.php";
include "pollfunctions.php";

echo "<table border='0' align='center' cellpadding='5' cellspacing='0'>";

echo	"<tr><td bgcolor='#336699'><div align='center'><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>
		<font style='font:bold; color:#FFFFFF;'>��ǥ���α׷�".$version." ������������</font>
		</font></div></td></tr>";

echo "<tr><td bgcolor='efefef'>";
echo "<form name='polladminform' method='post'>";

if(isset($deleteno))
{return firstscreen();}

if(isset($deleteyes))
{deletepoll($pollid);}

if(isset($modify) || isset($deleteanswer) || isset($addanswer))
{
	if(isset($modify))
	{$domodify='modify';$newanswer=null;}
	
	if(isset($deleteanswer))
	{$domodify='delete';$newanswer=null;}
	
	if(isset($addanswer))
	{
		if($addanswertext)
		{$newanswer = trim($addanswertext);}
		else
		{$newanswer=null;}
		
		if($newanswer==null)
		{return firstscreen();}
		else
		{$domodify='add';}
		
	}
	modifypoll($pollid,$answerarray,$ansid,$deletebox,$domodify,$newanswer);
}

if(!isset($doit) && !isset($addpoll) && !isset($deleteno) && !isset($deleteyes) && !isset($modify) && !isset($deleteanswer) && !isset($addanswer))
{
	$userquery = mysql_query("SELECT * FROM member WHERE id='$username' AND pass='$password'");
	$adminrow = mysql_num_rows($userquery);
	
	if($adminrow==1)
	{firstscreen();}
	else
	{echo ("<html>
<script language=javascript>
window.alert('�α��� ������ ���� �ʽ��ϴ�.\\n\\n���������� �Է½����ֽñ� �ٶ��ϴ�.');
history.go(-1);
</script>
</html>
");
exit;}
}
if(isset($doit) || isset($addpoll))
{

	if(isset($doit))
	{
		if(isset($dopoll))
		{editpoll($dopoll);}
		
		else
		{return firstscreen();}
	}
	if(isset($addpoll))
	{
		addpoll($newquestion, $answer1, $answer2, $answer3, $answer4, $answer5, $answer6, $answer7, $answer8, $answer9, $answer10);
	}
}

function firstscreen()
{
		$pollquery = mysql_query("SELECT * FROM poll");
	
echo "<br></font>";
echo "<table border='0' align='center' cellpadding='2' cellspacing='0'>";
echo "<tr bgcolor='#336699'>";
echo "<td colspan='2'><div align='center'>";
echo "<strong>���� �׸� �����ϱ�</strong>";
echo "</font></div></td></tr>";
echo "<tr bgcolor='cfcfcf'>";
echo "<td>";
echo "<strong>���� ���� �Է�</strong>:";
echo "</font></td>";
echo "<td>";
echo "<input name='newquestion' type='text'>";
echo "</font></td></tr>";

for($counter=1;$counter<11;$counter++)
{
	echo "<tr bgcolor='cfcfcf'>
			<td align=center>
			�׸� ".$counter.":
			</font></td>
			<td>
			<input name='answer".$counter."' type='text'>
			</font></td></tr>";
}

echo "<tr bgcolor='cfcfcf'>";
echo "<td>&nbsp;</td>";
echo "<td>";
echo "<input name='addpoll' type='submit' value='�����Ϸ�'>";			// Add new poll button
echo "</font></td></tr></table>";

echo "<br><br></font>";
echo "<table border=0 align='center' cellpadding=2 cellspacing=0>";
echo "<tr bgcolor='#336699'>";
echo "<td colspan='4'><div align='center'>";
echo "<strong>�����׸� ����</strong>";							// Edit Title first screen
echo "</font></div></td></tr>";

	while($pollrow = mysql_fetch_array($pollquery))
	{
    	echo "<tr bgcolor='cfcfcf'>";
		echo "<td><b>";
		echo $pollrow['question'];
		echo "</b>&nbsp;>>&nbsp;</td>";
		echo "<td bgcolor='bfbfbf'>";
		echo "����&nbsp;";
		echo "<input name='dopoll' type='radio' value='".$pollrow['pollid']."_delete'>";	// Delete radiobutton
		echo "</font></td>";
		echo "<td bgcolor='cfcfcf'>";
		echo "����&nbsp;";
		echo "<input type='radio' name='dopoll' value='".$pollrow['pollid']."_edit'>";		// Edit radiobutton
		echo "</font></td>";
		echo "<td bgcolor='bfbfbf'>";

	if($pollrow['active']=='yes')
	{
		//echo "<center><b>Active!</b></center>";
	}
	else
	{
		echo "�ֻ���&nbsp;";
		echo "<input type='radio' name='dopoll' value='".$pollrow['pollid']."_activate'>";	// Activate Radio button
	}
		echo "</font></td></tr>";
	}
		echo "<tr bgcolor='cfcfcf'>";
		echo "<td colspan='4'><div align='center'>";
		echo "<br><input name='doit' type='submit' value=' OK '><br>";			// Submit Button
		echo "</font></div></td></tr></table>";
		
} // end firstscreen()

echo "</font></form>";

	echo "</font></td></tr></table>";
?>

</body>
</html>
