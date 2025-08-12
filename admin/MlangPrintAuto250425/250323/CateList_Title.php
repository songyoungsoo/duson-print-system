<?php
////////////////// 환경 설정 및 데이터베이스 연결 ////////////////////
include "../../db.php";
include "../config.php";
include "../../MlangPrintAuto/ConDb.php";
////////////////////////////////////////////////////
include "CateAdmin_title.php";
?>

<?php if ($Ttable == "inserted") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=2<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_3) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "sticker") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "NameCard") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "envelope") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "NcrFlambeau") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=2<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_3) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "cadarok") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=2<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_3) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "cadarokTwo") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=2<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_3) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "LittlePrint") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=2<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_3) ?> 입력 '>
<?php } ?>

<?php if ($Ttable == "MerchandiseBond") { ?>
    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?><?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_1) ?> 입력 '>

    <input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?php echo  htmlspecialchars($Ttable) ?>&TreeSelect=1<?php if($Cate){echo("&Cate=$Cate");} ?>', 'WebOffice_Tree<?php echo  $PageCode ?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?php echo  htmlspecialchars($DF_Tatle_2) ?> 입력 '>
<?php } ?>
