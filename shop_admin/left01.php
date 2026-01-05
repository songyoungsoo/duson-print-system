<?
   include "lib.php";
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600&display=swap" rel="stylesheet">
<style type="text/css">
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    margin: 0;
    padding: 5px 4px;
    background: #1e3a5f;
    font-family: 'Noto Sans KR', sans-serif;
    font-size: 12px;
    font-weight: 600;
}
.menu-container {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.menu-section {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    overflow: hidden;
}
.menu-header {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff;
    padding: 6px 10px;
    font-weight: 600;
    font-size: 11px;
    text-align: center;
    letter-spacing: 0.5px;
}
.menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.menu-list li {
    border-bottom: 1px solid #f0f0f0;
}
.menu-list li:last-child {
    border-bottom: none;
}
.menu-list li a {
    display: block;
    padding: 6px 10px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 11px;
}
.menu-list li a:hover {
    background: #dbeafe;
    color: #1d4ed8;
    padding-left: 14px;
}
.menu-list li a:before {
    content: '›';
    margin-right: 5px;
    color: #2563eb;
    font-weight: bold;
}
</style>

<div class="menu-container">
    <div class="menu-section">
        <div class="menu-header">📊 통계</div>
        <ul class="menu-list">
            <li><a href="/admin/dashboard.php" target="main">통계 대시보드</a></li>
            <li><a href="/admin/visitors.php" target="main">방문자 분석</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">주문정보</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/admin/mlangprintauto/orderlist.php" target="main">자동주문접수</a></li>
            <li><a href="http://mail.naver.com/" target="main">네이버메일</a></li>
            <li><a href="http://dsp1830.shop/" target="_blank">dsp1830.shop</a></li>
            <li><a href="http://dsp1830.shop/mlangprintauto/quote/index.php" target="_blank">견적서관리</a></li>
            <li><a href="http://dsp1830.shop/chat/admin.php" onclick="window.open(this.href, 'chatAdmin', 'width=630,height=460,left=0,top=0,scrollbars=yes,resizable=yes'); return false;">채팅창관리</a></li>
            <li><a href="http://dsp1830.shop/tools/excel_converter_01.php" target="main">엑셀변환</a></li>
            <li><a href="https://logis.ilogen.com/" target="_blank">로젠택배</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">견적정보</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/mlangprintauto/inserted/index.php" target="main">전단지</a></li>
        <li><a href="http://dsp1830.shop/mlangprintauto/sticker_new/index.php" target="main">스티커</a></li>
            <li><a href="http://dsp1830.shop/mlangprintauto/msticker/index.php" target="main">종이자석</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">교정파일관리</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/admin/mlangprintauto/admin.php?mode=AdminMlangOrdert" onclick="window.open(this.href, 'fileUpload', 'width=390,height=700,scrollbars=yes,resizable=yes'); return false;">파일올리기</a></li>
            <li><a href="http://www.dsp1830.shop/sub/checkboard.htm" target="main">교정보기</a></li>
            <li><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php" target="main">웹하드SNS</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">주문/택배관리</div>
        <ul class="menu-list">
            <li><a href="order_list.php" target="main">스티커주문</a></li>
            <li><a href="post_list74.php" target="main">택배정보</a></li>
            <li><a href="post_all_list.php" target="main">작업및주소</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">주문/금액관리</div>
        <ul class="menu-list">
            <li><a href="data_edit.php" target="main">금액수정</a></li>
        </ul>
    </div>
</div>
