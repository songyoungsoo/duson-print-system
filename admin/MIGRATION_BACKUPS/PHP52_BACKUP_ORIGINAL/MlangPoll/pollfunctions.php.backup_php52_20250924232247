<?
/***************************************************************************************
								VIEW POLL TO MODIFY
***************************************************************************************/

function editpoll($dopoll)
{
	$poll = explode("_", $dopoll);
	
	$pollid = $poll[0];
	
	if($poll[1]=='delete')
	{
		echo "</font>";
		echo "<form name='deletepollform' method='post'>";
		echo "<font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
		echo "<input name='deleteyes' type='submit' value='Delete'>";		// Delete poll button
		echo "or";
		echo "<input name='deleteno' type='submit' value='Go Back'>";		// go back button
		echo "<input name='pollid' type='hidden' value='".$pollid."'>";
		echo "</font></form>";
		echo "<font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
	}
	if($poll[1]=='edit')
	{
		echo "</font>";
		echo "<form name='modifypollform' method='post'>";
		echo "<font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";

		$query = mysql_query("SELECT * FROM poll WHERE pollid=$pollid");
		
		while($row = mysql_fetch_array($query))
		{
			$question = $row['question'];
		}		
		
		$query = mysql_query("SELECT * FROM poll_answers WHERE pollid='$pollid' ORDER BY answerid ASC");

		while($therow = mysql_fetch_array($query))
		{
			$answerid = $therow['answerid'];
			$answer = $therow['answers'];
			$answerarray[] = $answerid."_".$answer;
		}
		$counter=1;

		echo "<div align=center>";
		echo "<h3>".$question."</h3></div>";
		echo "<table width=100% border=0 cellspacing=0 cellpadding=2>";

		foreach ( $answerarray as $val )
		{
			$data = explode("_", $val);
	
			$answerid = $data[0];
			$answer = $data[1];

		echo "<tr>";
		echo "<td><font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
		echo "Answer&nbsp;".$counter.":";
		echo "</font></td>";
		echo "<td><font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
		echo "<input name='answerarray[]' type='text' value='".$answer."'>";
		echo "</font></td>";
		echo "<td bgcolor='bfbfbf'><font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
		echo "<input name='deletebox' type='radio' value='".$answerid."' checked>";		// radiobutton for delete answer
		echo "</font></td></tr>";
		echo "<input name='ansid[]' type='hidden' value='".$answerid."'>";

		$counter++;
		}
		echo "<tr><td colspan=2><input name=modify type=submit value='Make Changes'></td>
					<td bgcolor=bfbfbf><input name=deleteanswer type=submit value=Delete></td></tr>"; // delete answer button
		echo "<tr><td colspan=3>&nbsp;</td></tr>";
		echo "<tr><td colspan=2 bgcolor=cfcfcf><input name=addanswertext type=text></td>
					<td bgcolor=cfcfcf><input name=addanswer type=submit value='Add to poll'></td></tr>"; // add to poll button
        echo "</table>";
        
		echo "<div align=center>";
		echo "<br><input name='deleteno' type='submit' value='Cancel'>"; // Cancel button
		echo "</div>";
		
		echo "<input name='pollid' type='hidden' value='".$pollid."'>";
        echo "</font></form>";
		echo "<font size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
	}
	elseif($poll[1]=='activate')
	{
		activatepoll($pollid);
	}
}

/***************************************************************************************
									 ACTIVATE POLL
***************************************************************************************/

function activatepoll($pollid)
{
	$changesindb = mysql_query("UPDATE poll SET active='no' WHERE active='yes'");
	$changesindb = mysql_query("UPDATE poll SET active='yes' WHERE pollid=$pollid");
	return firstscreen();
}

/***************************************************************************************
									 DELETE POLL
***************************************************************************************/

function deletepoll($pollid)
{
		$changesindb = mysql_query ("DELETE FROM poll WHERE pollid=$pollid");
		$changesindb = mysql_query ("DELETE FROM poll_answers WHERE pollid=$pollid");
		return firstscreen();
}

/***************************************************************************************
									 EDIT/MODIFY POLL
***************************************************************************************/

function modifypoll($pollid,$answerarray,$ansid,$deletebox,$domodify,$newanswer)
{
		$counter1 = 1;
		$counter2 = 1;
		
		foreach ( $answerarray as $val )
		{
			$answer[$counter1] = $val;
			$counter1++;			
		}
		foreach($ansid as $val2)
		{
			$answerid[$counter2] = $val2;
			$counter2++;
		}
		
		if($domodify=='modify')
		{
			for($counter=1;$counter<$counter1;$counter++)
			{
				$changesindb = mysql_query("UPDATE poll_answers SET answers='$answer[$counter]' WHERE answerid=$answerid[$counter]");
			}
		}
		if($domodify=='delete')
		{
				$changesindb = mysql_query ("DELETE FROM poll_answers WHERE answerid=$deletebox");
		}
		if($domodify=='add')
		{
			$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$newanswer')");
		}
		return firstscreen();
}

/***************************************************************************************
									 ADD POLL
***************************************************************************************/

function addpoll($newquestion, $answer1, $answer2, $answer3, $answer4, $answer5, $answer6, $answer7, $answer8, $answer9, $answer10)
{
	if(!$newquestion || !$answer1 || !$answer2)
	{
		return firstscreen();
	}
	else
	{

		$newpollquest = mysql_query("INSERT INTO poll (question) VALUES ('$newquestion')");
		
		$querypoll = mysql_query("SELECT * FROM poll WHERE question='$newquestion'");

		while($pollrow=mysql_fetch_array($querypoll))
		{
			$pollid = $pollrow['pollid'];
		}
		if($answer1 && $answer2)
		{	
		$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer1')");
		$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer2')");
		}
		if($answer3)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer3')");}
		if($answer4)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer4')");}
		if($answer5)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer5')");}
		if($answer6)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer6')");}
		if($answer7)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer7')");}
		if($answer8)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer8')");}
		if($answer9)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer9')");}
		if($answer10)
		{$newpollanswer = mysql_query("INSERT INTO poll_answers (pollid,answers) VALUES ('$pollid','$answer10')");}
	return firstscreen();
	}
}
?>