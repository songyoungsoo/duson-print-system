<?php
if(!$DbDir){ $DbDir="../../.."; }
include"$DbDir/db.php";

$result= mysql_query("select * from MlangPrintAuto_MemberOrderOffice where no='$no'",$db);
$row= mysql_fetch_array($result);

$View_One_1="$row[One_1]";
$View_One_2="$row[One_2]";
$View_One_3="$row[One_3]";
$View_One_4="$row[One_4]";
$View_One_5="$row[One_5]";
$View_One_6="$row[One_6]";
$View_One_7="$row[One_7]";
$View_One_8="$row[One_8]";
$View_One_9="$row[One_9]"; 
$View_One_10="$row[One_10]"; 
$View_One_11="$row[One_11]"; 
$View_One_12="$row[One_13]"; 

$View_Two_1="$row[Two_1]";
$View_Two_2="$row[Two_2]";
$View_Two_3="$row[Two_3]";
$View_Two_4="$row[Two_4]";
$View_Two_5="$row[Two_5]";
$View_Two_6="$row[Two_6]";
$View_Two_7="$row[Two_7]";
$View_Two_8="$row[Two_8]";
$View_Two_9="$row[Two_9]";
$View_Two_10="$row[Two_10]";
$View_Two_11="$row[Two_11]";
$View_Two_12="$row[Two_12]";
$View_Two_13="$row[Two_13]";
$View_Two_14="$row[Two_14]";
$View_Two_15="$row[Two_15]";
$View_Two_16="$row[Two_16]";
$View_Two_17="$row[Two_17]";
$View_Two_18="$row[Two_18]";
$View_Two_19="$row[Two_19]";
$View_Two_20="$row[Two_20]";
$View_Two_21="$row[Two_21]";
$View_Two_22="$row[Two_22]";
$View_Two_23="$row[Two_23]";
$View_Two_24="$row[Two_24]";
$View_Two_25="$row[Two_25]";
$View_Two_26="$row[Two_26]";
$View_Two_27="$row[Two_27]";
$View_Two_28="$row[Two_28]";
$View_Two_29="$row[Two_29]";
$View_Two_30="$row[Two_30]";
$View_Two_31="$row[Two_31]";
$View_Two_32="$row[Two_32]";
$View_Two_33="$row[Two_33]";
$View_Two_34="$row[Two_34]";
$View_Two_35="$row[Two_35]";
$View_Two_36="$row[Two_36]";
$View_Two_37="$row[Two_37]";
$View_Two_38="$row[Two_38]";
$View_Two_39="$row[Two_39]";
$View_Two_40="$row[Two_40]";
$View_Two_41="$row[Two_41]";
$View_Two_42="$row[Two_42]";
$View_Two_43="$row[Two_43]";
$View_Two_44="$row[Two_44]";
$View_Two_45="$row[Two_45]";
$View_Two_46="$row[Two_46]";
$View_Two_47="$row[Two_47]";
$View_Two_48="$row[Two_48]";
$View_Two_49="$row[Two_49]";
$View_Two_50="$row[Two_50]";
$View_Two_51="$row[Two_51]";
$View_Two_52="$row[Two_52]";
$View_Two_53="$row[Two_53]";
$View_Two_54="$row[Two_54]";
$View_Two_55="$row[Two_55]"; 
$View_Two_56="$row[Two_56]"; 
$View_Two_57="$row[Two_57]"; 
$View_Two_58="$row[Two_58]"; 

$View_Tree_1="$row[Tree_1]";
$View_Tree_2="$row[Tree_2]";
$View_Tree_3="$row[Tree_3]";
$View_Tree_4="$row[Tree_4]";
$View_Tree_5="$row[Tree_5]";
$View_Tree_6="$row[Tree_6]";
$View_Tree_7="$row[Tree_7]";
$View_Tree_8="$row[Tree_8]";
$View_Tree_9="$row[Tree_9]";
$View_Tree_10="$row[Tree_10]";
$View_Tree_11="$row[Tree_11]";
$View_Tree_12="$row[Tree_12]";
$View_Tree_13="$row[Tree_13]";
$View_Tree_14="$row[Tree_14]";
$View_Tree_15="$row[Tree_15]"; 

$View_Four_1="$row[Four_1]";
$View_Four_2="$row[Four_2]";
$View_Four_3="$row[Four_3]";
$View_Four_4="$row[Four_4]";
$View_Four_5="$row[Four_5]";
$View_Four_6="$row[Four_6]";
$View_Four_7="$row[Four_7]";
$View_Four_8="$row[Four_8]";
$View_Four_9="$row[Four_9]";
$View_Four_10="$row[Four_10]";
$View_Four_11="$row[Four_11]";
$View_Four_12="$row[Four_12]"; 

$View_Five_1="$row[Five_1]";
$View_Five_2="$row[Five_2]";
$View_Five_3="$row[Five_3]";
$View_Five_4="$row[Five_4]";
$View_Five_5="$row[Five_5]";
$View_Five_6="$row[Five_6]";
$View_Five_7="$row[Five_7]";
$View_Five_8="$row[Five_8]";
$View_Five_9="$row[Five_9]";
$View_Five_10="$row[Five_10]";
$View_Five_11="$row[Five_11]";
$View_Five_12="$row[Five_12]";
$View_Five_13="$row[Five_13]";
$View_Five_14="$row[Five_14]";
$View_Five_15="$row[Five_15]";
$View_Five_16="$row[Five_16]";
$View_Five_17="$row[Five_17]";
$View_Five_18="$row[Five_18]";
$View_Five_19="$row[Five_19]";
$View_Five_20="$row[Five_20]";
$View_Five_21="$row[Five_21]";
$View_Five_22="$row[Five_22]";
$View_Five_23="$row[Five_23]";
$View_Five_24="$row[Five_24]";
$View_Five_25="$row[Five_25]";
$View_Five_26="$row[Five_26]";
$View_Five_27="$row[Five_27]";
$View_Five_28="$row[Five_28]";
$View_Five_29="$row[Five_29]"; 

$View_cont="$row[cont]"; 
$View_date="$row[date]"; 

//üũ�ڽ� ȣ��//////////////////////////////////////////////////////

$View_Two_7Ok=explode("-",$View_Two_7);
$View_Two_7_1=$View_Two_7Ok[0];
$View_Two_7_2=$View_Two_7Ok[1];
$View_Two_7_3=$View_Two_7Ok[2];

$View_Two_21Ok=explode("-",$View_Two_21);
$View_Two_21_1=$View_Two_21Ok[0];
$View_Two_21_2=$View_Two_21Ok[1];
$View_Two_21_3=$View_Two_21Ok[2];
$View_Two_21_4=$View_Two_21Ok[3];

$View_Two_33Ok=explode("-",$View_Two_33);
$View_Two_33_1=$View_Two_33Ok[0];
$View_Two_33_2=$View_Two_33Ok[1];
$View_Two_33_3=$View_Two_33Ok[2];


mysql_close($db); 
?>