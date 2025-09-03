<?php
// session_start();
// session_cache_limiter('nocache, must-revalidate'); 
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
?>
<!--------------------------------------------------------------------------------
     디자인 편집툴-포토샵8.0, 플래쉬MX
     프로그램 제작툴-에디터플러스2
     프로그램언어: PHP, javascript, DHTML, html
     제작자: Mlang - 메일: webmaster@script.ne.kr
     URL: http://www.websil.net , http://www.script.ne.kr

* 현 사이트는 MYSQLDB(MySql데이터베이스) 화 작업되어져 있는 홈페이지 입니다.
* 홈페이지의 해킹, 사고등으로 자료가 없어질시 5분안에 복구가 가능합니다.
* 현사이트는 PHP프로그램화 되어져 있음으로 웹초보자가 자료를 수정/삭제 가능합니다.
* 페이지 수정시 의뢰자가 HTML에디터 추가를 원하면 프로그램을 지원합니다.
* 모든 페이지는 웹상에서 관리할수 있습니다.

   홈페이지 제작/상담: ☏ 010-8946-7038, 임태희 (전화안받을시 문자를주셔염*^^*)
   전화를 안받으면 다른 전화번호로 변경된 경우일수 있습니다...
   그럴경우는 http://www.websil.net 홈페이지에 방문하시면 메인 페이지에 전화번호가 공개 되어있음으로
   언제든지 부담없이 전화 하여 주시기 바랍니다.... 감사합니다.*^^*
----------------------------------------------------------------------------------->

<?php
$SoftUrl="/MlangPrintAuto";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//kr">
<html>
<head>
<title>▒ 두손기획 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<META NAME='KEYWORDS' CONTENT='<?=$HomeTitle?>'>
<meta name='author' content='Mlang'>
<meta name='classification' content='<?=$HomeTitle?>'>
<meta name='description' content='<?=$HomeTitle?>'>
<style type="text/css">
    table { font-size: 12px; }
    a:link { color: #333333; text-decoration: none; }
    a:hover { color: #666666; text-decoration: none; }
    a:visited { color: #666666; text-decoration: none; }
  </style>

<style>
body,td,input,select,a,submit {color:black; font-size:9pt; FONT-FAMILY:굴림; word-break:break-all;}
</style>

<script language="JavaScript" type="text/JavaScript">

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
</script>
</head>

<body background="/img/bg.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="MM_preloadImages('/img/main_m1a.jpg','/img/main_m2a.jpg','/img/main_m3a.jpg','/img/main_m5a.jpg','/img/main_m6a.jpg','/img/main_m7a.jpg','/img/main_m8a.jpg','/img/main_m10a.jpg','/img/main_m11a.jpg')">
<table align="center" width="990" border="0" cellpadding="0" cellspacing="0">
  <tr background="/img/bg.gif"> 
    <td width="990" valign="top"> 
      <!-- 메인 이미지 로고 시작 -->
      <?php include $_SERVER['DOCUMENT_ROOT'] . "/top5.php"; ?>
      <!-- 메인 이미지 로고 끝 -->
    </td>
  </tr>
</table>

<table align="center" width="990" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="160" height="1" valign="top"> 
      <p> 
        <!-- 왼쪽 배너 메뉴 시작 -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm"; ?>
        <!-- 왼쪽 배너 메뉴 끝 -->
      </p>
    </td>
    <td width="9"><img src="/img/space.gif" width="9" height="9"></td>
    <td VALIGN=TOP> 
      <!-- 본문 내용 시작 -->
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td width="692" valign="top" align="center"> 
            <!-- 주문서 시작 -->
            <table width="692" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td><a href="/sub/leaflet.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image22','','/img/main_m10a.jpg',1)"><img src="/img/main_m10.jpg" name="Image22" width="77" height="32" border="0"></a></td>
                <td><a href="/sub/sticker.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image25','','/img/main_m7a.jpg',1)"><img src="/img/main_m7.jpg" name="Image25" width="77" height="32" border="0"></a></td>
                <td><a href="/sub/catalog.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image20','','/img/main_m2a.jpg',1)"><img src="/img/main_m2.jpg" name="Image20" width="77" height="32" border="0"></a></td>
                <td><a href="/sub/brochure.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image21','','/img/main_m3a.jpg',1)"><img src="/img/main_m3.jpg" name="Image21" width="77" height="32" border="0"></a></td>
               <td><a href="/sub/bookdesign.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image26','','/img/main_m8a.jpg',1)"><img src="/img/main_m8.jpg" name="Image26" width="77" height="32" border="0"></a></td>
               <td><a href="/sub/poster.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image27','','/img/main_m11a.jpg',1)"><img src="/img/main_m11.jpg" name="Image27" width="76" height="32" border="0"></a></td>
               <td><a href="/sub/namecard.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image23','','/img/main_m5a.jpg',1)"><img src="/img/main_m5.jpg" name="Image23" width="77" height="32" border="0"></a></td>
               <td><a href="/sub/envelope.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image24','','/img/main_m6a.jpg',1)"><img src="/img/main_m6.jpg" name="Image24" width="77" height="32" border="0"></a></td>              
               <td><a href="/sub/symbol.htm" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('Image19','','/img/main_m1a.jpg',1)"><img src="/img/main_m1.jpg" name="Image19" width="77" height="32" border="0"></a></td>

              </tr>
            </table>
             <!-- 주문서 끝 -->
             </td>
        </tr>
        <tr> 
          <td height="1" valign="top" bgcolor="#D2D2D2"></td>
        </tr>
        <tr> 
          <td valign="top">&nbsp;</td>
        </tr>
        <tr> 
          <td valign="top"> 
            <p align="center"><img src="/img/t_auto.gif" width="692" height="59"></p>
            <!-- 주문서 내용 시작 -->
      <table width="692" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td height="60"> 
                  <!--주문 메뉴 이미지  -->
<body onLoad="MM_preloadImages('/images/AutoP_Two_01.gif','/images/AutoP_Two_02.gif','/images/AutoP_Two_03.gif','/images/AutoP_Two_04.gif','/images/AutoP_Two_05.gif','/images/AutoP_Two_06.gif','/images/AutoP_Two_07.gif','/images/AutoP_Two_08.gif')"><table width="620" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>

    <td><a href="/shop/view.php" onMouseOver="MM_swapImage('Image2','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/shop/view.php"){}else{echo("_Two");}?>_02.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/shop/view.php"){echo("_Two");}?>_02.gif" name="Image2" width="68" height="78" border="0" id="Image2"></a></td>
    
        <td><a href="/mlangprintauto/inserted/index.php" onMouseOver="MM_swapImage('Image1','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/inserted/index.php"){}else{echo("_Two");}?>_01.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/inserted/index.php"){echo("_Two");}?>_01.gif" name="Image1" width="71" height="78" border="0" id="Image1"></a></td>
    
    <td><a href="/mlangprintauto/sticker/index.php" onMouseOver="MM_swapImage('Image9','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/sticker/index.php"){}else{echo("_Two");}?>_09.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/sticker/index.php"){echo("_Two");}?>_09.gif" name="Image9" width="70" height="78" border="0" id="Image9"></a></td>

    <td><a href="/mlangprintauto/namecard/index.php" onMouseOver="MM_swapImage('Image3','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/namecard/index.php"){}else{echo("_Two");}?>_03.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/namecard/index.php"){echo("_Two");}?>_03.gif" name="Image3" width="68" height="78" border="0" id="Image3"></a></td>

    <td><a href="/mlangprintauto/merchandisebond/index.php" onMouseOver="MM_swapImage('Image4','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/merchandisebond/index.php"){}else{echo("_Two");}?>_04.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/merchandisebond/index.php"){echo("_Two");}?>_04.gif" name="Image4" width="68" height="78" border="0" id="Image4"></a></td>

    <td><a href="/mlangprintauto/envelope/index.php" onMouseOver="MM_swapImage('Image5','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/envelope/index.php"){}else{echo("_Two");}?>_05.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/envelope/index.php"){echo("_Two");}?>_05.gif" name="Image5" width="68" height="78" border="0" id="Image5"></a></td>

    <td><a href="/mlangprintauto/ncrflambeau/index.php" onMouseOver="MM_swapImage('Image6','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/ncrflambeau/index.php"){}else{echo("_Two");}?>_06.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/ncrflambeau/index.php"){echo("_Two");}?>_06.gif" name="Image6" width="69" height="78" border="0" id="Image6"></a></td>

    <td><a href="/mlangprintauto/cadarok/index.php" onMouseOver="MM_swapImage('Image7','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/cadarok/index.php"){}else{echo("_Two");}?>_07.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/cadarok/index.php"){echo("_Two");}?>_07.gif" name="Image7" width="68" height="78" border="0" id="Image7"></a></td>

    <td><a href="/mlangprintauto/littleprint/index.php" onMouseOver="MM_swapImage('Image8','','/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/littleprint/index.php"){}else{echo("_Two");}?>_08.gif',1)" onMouseOut="MM_swapImgRestore()"><img src="/images/AutoP<?if($_SERVER['PHP_SELF']=="/mlangprintauto/littleprint/index.php"){echo("_Two");}?>_08.gif" name="Image8" width="70" height="78" border="0" id="Image8"></a></td>
  </tr>
</table>
<table border=0 align=center width=620 cellpadding=0 cellspacing=0>
       <tr>
         <td width=100%>
