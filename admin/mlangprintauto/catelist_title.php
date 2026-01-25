<?php
declare(strict_types=1); 


// ⚠️  XSS 보호 권장: echo 시 htmlspecialchars() 사용을 고려하세요
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? '';
$name = $_GET['name'] ?? $_POST['name'] ?? '';
$code = $_GET['code'] ?? $_POST['code'] ?? '';
$page = $_GET['page'] ?? $_POST['page'] ?? '';

include"cateadmin_title.php";
?>

<?php if($Ttable=="inserted"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form2','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_3?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>


<?php if($Ttable=="sticker"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="NameCard" || $Ttable=="namecard"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2&category_type=namecard_type<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$Ttable?>_namecard_typeForm','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 명함종류입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2&category_type=namecard_material<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$Ttable?>_namecard_materialForm','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 명함재질입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2&category_type=type<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$Ttable?>_typeForm','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 종류입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2&category_type=material<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$Ttable?>_materialForm','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' 재질입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="envelope"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="NcrFlambeau"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form2','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_3?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="cadarok"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form2','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_3?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="cadarokTwo"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form2','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_3?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="LittlePrint"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=2<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form2','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_3?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

<?php if($Ttable=="MerchandiseBond"){////////////////////////////////////////////////////////////////////////////////////////
?>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?><?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_1?> 입력 '>

<input type='button' onClick="javascript:popup=window.open('./CateAdmin.php?mode=form&Ttable=<?=$Ttable?>&TreeSelect=1<?php if($Cate){echo('&Cate='.$Cate);}?>', 'WebOffice_Tree<?=$PageCode?>Form','width=500,height=120,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();" value=' <?=$DF_Tatle_2?> 입력 '>

<?php } ////////////////////////////////////////////////////////////////////////////////////////////////////////////////// ?>

