<SCRIPT LANGUAGE="JavaScript">
self.resizeTo(availWidth=650,screen.availHeight)
</SCRIPT>

<?php 
include "polldb.php";
include "pollconfig.php";
include "pollfunctions.php";

echo "<table border='0' align='center' cellpadding='5' cellspacing='0'>";

echo	"<tr><td bgcolor='#336699'><div align='center'><font size='2' face='Verdana, Arial, Helvetica, sans-serif'>
		<font style='font:bold; color:#FFFFFF;'>투표프로그램".$version." 관리자페이지</font>
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
	// ✅ users 테이블 조회 (member → users 마이그레이션, prepared statement + bcrypt)
	$stmt = mysqli_prepare($db, "SELECT * FROM users WHERE username = ?");
	mysqli_stmt_bind_param($stmt, "s", $username);
	mysqli_stmt_execute($stmt);
	$userquery_result = mysqli_stmt_get_result($stmt);

	if ($userquery_result && $admin_row = mysqli_fetch_assoc($userquery_result)) {
	    $adminrow = 0;
	    $stored_pass = $admin_row['password'];
	    // bcrypt 또는 평문 비밀번호 비교
	    if (strlen($stored_pass) === 60 && strpos($stored_pass, '$2y$') === 0) {
	        if (password_verify($password, $stored_pass)) $adminrow = 1;
	    } else {
	        if ($password === $stored_pass) $adminrow = 1;
	    }
	} else {
	    $adminrow = 0;
	}
	mysqli_stmt_close($stmt);
	
	if($adminrow==1)
	{firstscreen();}
	else
	{echo ("<html>
<script language=javascript>
window.alert('로그인 정보가 맞지 않습니다.\\n\\n정상적으로 입력시켜주시기 바랍니다.');
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
		$pollquery = mysqli_query($db, "SELECT * FROM poll");
	
echo "<br></font>";
echo "<table border='0' align='center' cellpadding='2' cellspacing='0'>";
echo "<tr bgcolor='#336699'>";
echo "<td colspan='2'><div align='center'>";
echo "<strong>설문 항목 생성하기</strong>";
echo "</font></div></td></tr>";
echo "<tr bgcolor='cfcfcf'>";
echo "<td>";
echo "<strong>설문 제목 입력</strong>:";
echo "</font></td>";
echo "<td>";
echo "<input name='newquestion' type='text'>";
echo "</font></td></tr>";

for($counter=1;$counter<11;$counter++)
{
	echo "<tr bgcolor='cfcfcf'>
			<td align=center>
			항목 ".$counter.":
			</font></td>
			<td>
			<input name='answer".$counter."' type='text'>
			</font></td></tr>";
}

echo "<tr bgcolor='cfcfcf'>";
echo "<td>&nbsp;</td>";
echo "<td>";
echo "<input name='addpoll' type='submit' value='생성완료'>";			// Add new poll button
echo "</font></td></tr></table>";

echo "<br><br></font>";
echo "<table border=0 align='center' cellpadding=2 cellspacing=0>";
echo "<tr bgcolor='#336699'>";
echo "<td colspan='4'><div align='center'>";
echo "<strong>설문항목 관리</strong>";							// Edit Title first screen
echo "</font></div></td></tr>";

	while($pollrow = mysqli_fetch_array($pollquery))
	{
    	echo "<tr bgcolor='cfcfcf'>";
		echo "<td><b>";
		echo $pollrow['question'];
		echo "</b>&nbsp;>>&nbsp;</td>";
		echo "<td bgcolor='bfbfbf'>";
		echo "삭제&nbsp;";
		echo "<input name='dopoll' type='radio' value='".$pollrow['pollid']."_delete'>";	// Delete radiobutton
		echo "</font></td>";
		echo "<td bgcolor='cfcfcf'>";
		echo "수정&nbsp;";
		echo "<input type='radio' name='dopoll' value='".$pollrow['pollid']."_edit'>";		// Edit radiobutton
		echo "</font></td>";
		echo "<td bgcolor='bfbfbf'>";

	if($pollrow['active']=='yes')
	{
		//echo "<center><b>Active!</b></center>";
	}
	else
	{
		echo "최상위&nbsp;";
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
