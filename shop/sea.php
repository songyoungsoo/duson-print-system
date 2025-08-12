<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php
		
include "../lib/func.php"; 
 $connect = dbconn();

	$query = "select * from zipcode group by SIDO";
	$result = mysql_query($query, $connect);
 ?> 
<script>
	function assa(){
		location.href='sea.php?SIDO='+document.sea.SIDO.value;
	}

</script>
<form action="sea.php" name=sea>
<select name=SIDO onchange="assa();">
	<option value=''>지역선택</option>
	<?php
	while($data = mysql_fetch_array($result)){
	?>
		<option value='<?php echo $data[SIDO]?>' <? if($data[SIDO]==$SIDO) echo 'selected'; ?> charset='UTF-8' > <?php echo $data[SIDO]?> 
	<?php
	}
	?>
</select>

<select name=GUGUN>
	<?php
	$query = "select * from zipcode where SIDO='$SIDO'";
	$result = mysql_query($query, $connect);		
	while($data = mysql_fetch_array($result)){
	?>
	 <option value='<?php echo $data[GUGUN]?>' <? if($data[GUGUN]==$GUGUN) echo "selected"; ?> charset='UTF-8'> <?php echo $data[GUGUN]?>
	<?php
	}
	?>
</select>

<input type=submit value='조회하기'>
</form>

</body>
</html>
