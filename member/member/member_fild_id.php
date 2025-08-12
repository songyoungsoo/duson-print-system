<?php
include"../db.php";
$MenuLogin_id_result= mysql_query("select * from member where id='$WebtingMemberLogin_id'",$db);
$MenuLogin_id_row= mysql_fetch_array($MenuLogin_id_result);
$MlangMember_id = htmlspecialchars($MenuLogin_id_row[id]);
$MlangMember_pass = htmlspecialchars($MenuLogin_id_row[pass]);  
$MlangMember_name = htmlspecialchars($MenuLogin_id_row[name]); 
$MlangMember_hendphone1 = htmlspecialchars($MenuLogin_id_row[hendphone1]);
$MlangMember_hendphone2 = htmlspecialchars($MenuLogin_id_row[hendphone2]);
$MlangMember_hendphone3 = htmlspecialchars($MenuLogin_id_row[hendphone3]);
$MlangMember_email = htmlspecialchars($MenuLogin_id_row[email]); 
$MlangMember_date = htmlspecialchars($MenuLogin_id_row[date]); 
$MlangMember_Logincount = htmlspecialchars($MenuLogin_id_row[Logincount]);
$MlangMember_EndLogin = htmlspecialchars($MenuLogin_id_row[EndLogin]); 
?>