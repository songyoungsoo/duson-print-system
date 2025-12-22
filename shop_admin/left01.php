<?
   include "lib.php";
?>
<style type="text/css">
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    margin: 0;
    padding: 10px 5px;
    background: #1e3a5f;
    font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
    font-size: 14px;
    min-height: 100vh;
}
.menu-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.menu-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    overflow: hidden;
}
.menu-header {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #fff;
    padding: 10px 12px;
    font-weight: bold;
    font-size: 12px;
    text-align: center;
    letter-spacing: 1px;
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
    padding: 10px 12px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 12px;
}
.menu-list li a:hover {
    background: #dbeafe;
    color: #1d4ed8;
    padding-left: 16px;
}
.menu-list li a:before {
    content: '›';
    margin-right: 6px;
    color: #2563eb;
    font-weight: bold;
}
</style>

<div class="menu-container">
    <div class="menu-section">
        <div class="menu-header">주문정보</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/admin/MlangPrintAuto/OrderList.php" target="main">자동주문접수</a></li>
            <li><a href="http://mail.naver.com/?n=1367814236304&v=f" target="main">네이버메일</a></li>
            <li><a href="http://dsp1830.shop/" target="_blank">dsp1830.shop</a></li>
            <li><a href="http://dsp1830.shop/mlangprintauto/quote/index.php" target="_blank">견적서관리</a></li>
            <li><a href="http://dsp1830.shop/chat/admin.php" onclick="window.open(this.href, 'chatAdmin', 'width=630,height=460,left=0,top=0,scrollbars=yes,resizable=yes'); return false;">채팅창관리</a></li>
            <li><a href="http://dsp1830.shop/admin/mlangprintauto/orderlist.php" target="main">주문관리</a></li>
            <li><a href="https://logis.ilogen.com/" target="main">로젠택배</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">견적정보</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/MlangPrintAuto/inserted/index.php" target="main">전단지</a></li>
            <li><a href="http://dsp1830.shop/shop/view.php" target="main">스티커</a></li>
            <li><a href="http://dsp1830.shop/MlangPrintAuto/sticker/index.php" target="main">종이자석</a></li>
        </ul>
    </div>

    <div class="menu-section">
        <div class="menu-header">교정파일관리</div>
        <ul class="menu-list">
            <li><a href="http://dsp1830.shop/admin/MlangPrintAuto/admin.php?mode=AdminMlangOrdert" target="main">파일올리기</a></li>
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
