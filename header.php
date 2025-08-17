<?php 
if (!isset($page_title)) {
    $page_title = "두손기획인쇄 - 견적안내";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>▒ <?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<META NAME='KEYWORDS' CONTENT='두손기획인쇄, 전단지, 리플렛, 명함, 스티커, 봉투, 인쇄'>
<meta name='author' content='Mlang'>
<meta name='classification' content='두손기획인쇄'>
<meta name='description' content='두손기획인쇄 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.'>
<link rel="stylesheet" type="text/css" href="<?php echo isset($css_path) ? $css_path : '/css/styles.css'; ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

<style type="text/css">
    body, table, tr, td, select, input, textarea, button, a, div, span, p, li {
        font-family: 'Noto Sans KR', sans-serif !important;
        font-size: 14px;
        color: #333333;
        word-break: break-all;
    }
    
    a:link { color: #333333; text-decoration: none; }
    a:hover { color: #666666; text-decoration: none; }
    a:visited { color: #666666; text-decoration: none; }
    
    .input {
        font-family: 'Noto Sans KR', sans-serif !important;
        font-size: 14px;
        background-color: #FFFFFF;
        color: #336699;
        line-height: 1.4;
        border: 1px solid #ccc;
        box-sizing: border-box;
        height: 36px;
        padding: 6px 8px;
    }
    
    .label-text {
        font-family: 'Noto Sans KR', sans-serif !important;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .main-content {
        font-family: 'Noto Sans KR', sans-serif !important;
    }
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
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
function WEBSILDESIGNWINDOW(theURL, width, height, scrollbars) {
  var features = 'width=' + width + ',height=' + height + ',scrollbars=' + scrollbars + ',resizable=yes,toolbar=no,menubar=no,location=no,status=no';
  window.open(theURL, 'WebsilWindow', features);
}
</script>
</head>
<body background="/img/bg.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- 상단 내비게이션 -->
<table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr style="background-image: url('http://dsp114.com/img/top231205.gif');
  background-repeat: no-repeat;
  background-size: cover;
  width: 990px;
  height: 52px;">
    <td align="center" border="0">
      <?php 
      $session_file = $_SERVER['DOCUMENT_ROOT']."/session/index.php";
      if (file_exists($session_file)) {
        include $session_file;
      } else {
        // 세션 파일이 없을 경우 기본 로그인 링크 표시
        echo '<a href="/member/login.php" style="font-weight: bold;">로그인</a> | ';
      }
      ?>
      <a href="http://localhost" style="font-weight: bold;">HOME</a>|
      <a href="/sub/info.php" style="font-weight: bold;">회사소개</a>|
      <a href="/sub/leaflet.php" style="font-weight: bold;">포트폴리오</a>|
      <a href="/sub/estimate_auto.php" style="font-weight: bold;">견적안내</a>|
      <a href="/sub/checkboard.php" style="font-weight: bold;color: white; background-color: orange; padding: 7px;">교정보기</a>|
      <a href="/bbs/qna.php" style="font-weight: bold;">고객문의</a>|
    </td>
  </tr>
</table>
<!-- 로고 영역 -->
<table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="400" height="150"><img src="/img/11.jpg" width="400" height="150" /></td>
    <td width="590" height="150"><img src="/WEBSILDESIGN/swf/WEBSILDESIGN.gif" width="590" height="150" /></td>
  </tr>
  <tr> 
    <td height="10" colspan="2"></td>
  </tr>
</table>
<!-- 메인 컨텐츠 영역 시작 -->
<table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td colspan="2">
      <table width="990" border="0" cellspacing="0" cellpadding="0">
        <tr>