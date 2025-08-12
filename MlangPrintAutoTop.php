<?php
// 세션 캐시 설정을 먼저 하고 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_cache_limiter('nocache, must-revalidate');
    session_start();
}

// 헤더 설정
@header("Cache-Control: no-cache, must-revalidate");
@header("Pragma: no-cache");

$HomeTitle="두손기획인쇄-자동견적프로그램";
?>

<html>
<head>
<title><?=$HomeTitle?></title>
<meta http-equiv='Content-type' content='text/html; charset=euc-kr'>
<META NAME='KEYWORDS' CONTENT='<?=$HomeTitle?>'>
<meta name='author' content='Mlang'>
<meta name='classification' content='<?=$HomeTitle?>'>
<meta name='description' content='<?=$HomeTitle?>'>
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

<style>
body,td,table {color:black; font-size:9pt; FONT-FAMILY:'Noto Sans KR', 'Malgun Gothic', '맑은 고딕', sans-serif; word-break:break-all;}

/* 모던 UI를 위한 추가 스타일 */
:root {
    --primary-color: #3498db;
    --secondary-color: #2980b9;
    --accent-color: #e74c3c;
    --light-gray: #f5f5f5;
    --dark-gray: #333333;
    --medium-gray: #999999;
    --border-radius: 4px;
    --box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

.modern-header {
    background: linear-gradient(to right, #3498db, #2980b9);
    color: white;
    padding: 15px 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.modern-logo {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    padding: 10px 0;
}

.modern-nav {
    display: flex;
    justify-content: center;
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin: 10px 0;
}

.modern-nav a {
    padding: 12px 15px;
    color: var(--dark-gray);
    text-decoration: none;
    transition: var(--transition);
    font-weight: 500;
    text-align: center;
    flex: 1;
    border-bottom: 3px solid transparent;
}

.modern-nav a:hover, .modern-nav a.active {
    color: var(--primary-color);
    background-color: var(--light-gray);
    border-bottom: 3px solid var(--primary-color);
}

.sub-nav {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin: 15px 0;
    background-color: #f8f9fa;
    border-radius: var(--border-radius);
    padding: 5px;
}

.sub-nav a {
    padding: 8px 15px;
    margin: 5px;
    color: var(--dark-gray);
    text-decoration: none;
    transition: var(--transition);
    border-radius: var(--border-radius);
    font-size: 14px;
}

.sub-nav a:hover, .sub-nav a.active {
    background-color: var(--primary-color);
    color: white;
}

.product-nav {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin: 15px 0;
}

.product-item {
    width: 70px;
    margin: 5px;
    text-align: center;
    transition: var(--transition);
}

.product-box {
    height: 70px;
    background-color: white;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--box-shadow);
    margin-bottom: 5px;
    transition: var(--transition);
    border: 1px solid #e0e0e0;
}

.product-item:hover .product-box {
    background-color: var(--light-gray);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-item.active .product-box {
    background-color: var(--primary-color);
    color: white;
}

.product-item p {
    margin: 5px 0 0 0;
    font-size: 12px;
    color: var(--dark-gray);
}

.section-title {
    text-align: center;
    padding: 15px 0;
    margin: 20px 0 10px 0;
    font-size: 20px;
    color: var(--dark-gray);
    border-bottom: 1px solid #e0e0e0;
}
</style>

</head>

<body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0'>

<?php
$SoftUrl="/MlangPrintAuto";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//kr">
<html>
<head>
<title>▒ 두손기획 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body style="background-color: #f8f9fa; margin: 0; padding: 0;">
<div class="modern-header">
    <div class="modern-logo">두손기획인쇄</div>
    <div style="text-align: center; font-size: 14px; margin-bottom: 10px;">기획에서 인쇄까지 원스톱으로 해결해 드립니다</div>
</div>

<table align="center" width="990" border="0" cellpadding="0" cellspacing="0">
  <tr> 
    <td width="990" valign="top"> 
      <!-- 모던 상단 메뉴 시작 -->
      <div class="modern-nav">
        <a href="http://www.dsp114.com/index.htm">HOME</a>
        <a href="/sub/info.htm">회사소개</a>
        <a href="/sub/leaflet.php">포트폴리오</a>
        <a href="/sub/estimate_auto.htm" class="active">자동견적</a>
        <a href="/sub/checkboard.php">교정보기</a>
        <a href="/bbs/qna.php">고객문의</a>
      </div>
      <!-- 모던 상단 메뉴 끝 -->
    </td>
  </tr>
</table>

<table align="center" width="990" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="160" height="1" valign="top"> <p> 
        <!--왼쪽 배너 메뉴  시작-->
        <?php 
        // 왼쪽 메뉴 파일 경로 확인 및 포함
        $left_menu_path = $_SERVER['DOCUMENT_ROOT'] . "/left.htm";
        if (file_exists($left_menu_path)) {
            include $left_menu_path;
        } else {
            // 대체 경로 시도
            $alt_path = "../left.htm";
            if (file_exists($alt_path)) {
                include $alt_path;
            } else {
                echo "<!-- 왼쪽 메뉴 파일을 찾을 수 없습니다 -->";
            }
        }
        ?>
        <!-- 왼쪽 배너 메뉴 끝 -->
      </p></td>
    <td width="9"></td>
	<td VALIGN=TOP> 
      <!--본문 내용 시작-->
      <table border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td width="692" valign="top" align="center"> 
            <!-- 주문서 시작 -->
            <div class="sub-nav">
              <a href="/sub/leaflet.htm">전단지</a>
              <a href="/sub/sticker.htm">스티커</a>
              <a href="/sub/catalog.htm">카탈로그</a>
              <a href="/sub/brochure.htm">브로슈어</a>
              <a href="/sub/bookdesign.htm">책자디자인</a>
              <a href="/sub/poster.htm">포스터</a>
              <a href="/sub/namecard.htm">명함</a>
              <a href="/sub/envelope.htm">봉투</a>
              <a href="/sub/seosig.htm">서식류</a>
            </div>
            
            <div class="section-title">자동견적 프로그램</div>
            
            <!-- 제품 네비게이션 -->
            <div class="product-nav">
              <a href="/shop/view.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/shop/view.php") echo 'active'; ?>">
                <div class="product-box">견적</div>
                <p>견적</p>
              </a>
              
              <a href="/MlangPrintAuto/inserted/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/inserted/index.php") echo 'active'; ?>">
                <div class="product-box">삽지</div>
                <p>삽지</p>
              </a>
              
              <a href="/MlangPrintAuto/msticker/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/msticker/index.php") echo 'active'; ?>">
                <div class="product-box">자석스티커</div>
                <p>자석스티커</p>
              </a>
              
              <a href="/MlangPrintAuto/NameCard/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/NameCard/index.php") echo 'active'; ?>">
                <div class="product-box">명함</div>
                <p>명함</p>
              </a>
              
              <a href="/MlangPrintAuto/MerchandiseBond/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/MerchandiseBond/index.php") echo 'active'; ?>">
                <div class="product-box">상품권</div>
                <p>상품권</p>
              </a>
              
              <a href="/MlangPrintAuto/envelope/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/envelope/index.php") echo 'active'; ?>">
                <div class="product-box">봉투</div>
                <p>봉투</p>
              </a>
              
              <a href="/MlangPrintAuto/NcrFlambeau/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/NcrFlambeau/index.php") echo 'active'; ?>">
                <div class="product-box">NCR</div>
                <p>NCR</p>
              </a>
              
              <a href="/MlangPrintAuto/cadarok/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/cadarok/index.php") echo 'active'; ?>">
                <div class="product-box">카다록</div>
                <p>카다록</p>
              </a>
              
              <a href="/MlangPrintAuto/LittlePrint/index.php" class="product-item <?php if ($_SERVER['PHP_SELF']=="/MlangPrintAuto/LittlePrint/index.php") echo 'active'; ?>">
                <div class="product-box">소량인쇄</div>
                <p>소량인쇄</p>
              </a>
            </div>
<table border=0 align=center width=620 cellpadding=0 cellspacing=0>
       <tr>
         <td width=100%>
<?php
// 로그인 체크 (PHP 7.4 호환)
if(isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok']){
    $WebtingMemberLogin_id = $_COOKIE['id_login_ok'];
} else {
    // 로그인되지 않은 상태 - 에러 메시지 없이 진행
}
?>