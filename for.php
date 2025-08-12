<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>FOR</title>
</head>

<body>
	<?php

    $score[0]=78;       
    $score[1]=83;
    $score[2]=80;
    $score[3]=73;
    $score[4]=97;

    $sum = 0;       

    for($a=0; $a<=4; $a++)
    {
      $sum = $sum + $score[$a];
      echo "$sum = $sum + $score[$a]<br>";
    }       

    $avg = $sum/5;

    echo("과목 점수 : $score[0], $score[1], $score[2], $score[3], $score[4]<br>");
    echo("합계 : $sum, 평균 : $avg <br>");

?>
</body>
</html>