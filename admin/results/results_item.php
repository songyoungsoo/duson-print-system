<?php
$M123="..";
include "../top.php"; 
?>


<table border=0 align=center width=100% cellpadding='10' cellspacing='1' class='coolBar'>

<form name='BankInfo' method='post' OnSubmit='javascript:return BankCheckField()' action='/Mlang/admin/bank.php'>
<INPUT TYPE="hidden" NAME="mode" value='submit'>

<tr>
<td>
<font style='font-size:10pt; font:bold;'>
입력
:
</font>
&nbsp;<INPUT TYPE="text" NAME="fild1" size='15' >&nbsp;&nbsp;

<input type='submit' value=' 입력 합니다.'>

</td></tr>
</form>
</table>



<?php include "../down.php"; ?>