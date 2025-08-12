<?php
$to = "dsp1830@naver.com";
$subject = "subject";
$body = "message";
$from = "duson@dsp114.com";
$headers = "From: $from";
mail($to,$subject,$body, $from, "-f $from");
echo "Mail Sent.";
?> 



