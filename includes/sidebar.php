<?php
/**
 * 우측 사이드바 - 독립 컴포넌트
 * 모든 품목 페이지에서 include로 사용
 *
 * 사용법: <?php include '../includes/sidebar.php'; ?>
 */

// 사이드바 표시 옵션 (각 페이지에서 설정 가능)
$show_contact = isset($show_contact) ? $show_contact : true;
$show_menu = isset($show_menu) ? $show_menu : true;
$show_bank = isset($show_bank) ? $show_bank : true;
?>

<!-- 우측 사이드바 시작 -->
<div class="right-sidebar">

    <!-- 카톡상담 특별 섹션 (최상단) -->
    <div class="kakao-special-section">
        <a href="http://pf.kakao.com/_pEGhj/chat" target="_blank" class="kakao-special-link">
            <img src="/WEBSILDESIGN/images/talk.jpg" alt="카톡상담" class="kakao-image">
        </a>
    </div>

    <?php if($show_contact): ?>
    <!-- 고객센터 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">📞 고객센터</div>
        <div class="sidebar-contact-info">
            <div class="sidebar-contact-item">
                <div class="sidebar-contact-name">대표</div>
                <div class="sidebar-contact-number">1688-2384</div>
            </div>
            <div class="sidebar-contact-item">
                <div class="sidebar-contact-name">직통</div>
                <div class="sidebar-contact-number">02-2632-1830</div>
            </div>
            <div class="sidebar-contact-item">
                <div class="sidebar-contact-name">팩스</div>
                <div class="sidebar-contact-number">02-2632-1829</div>
            </div>
            <div class="sidebar-contact-item">
                <div class="sidebar-contact-name">야간</div>
                <div class="sidebar-contact-number">010-3712-1830</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_menu): ?>
    <!-- 파일전송 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">📂 파일전송</div>
        <div class="file-transfer-section">
            <!-- 웹하드 -->
            <div class="file-item">
                <a href="http://www.webhard.co.kr/" target="_blank" class="file-link">
                    <div class="file-service">웹하드 바로가기</div>
                    <div class="file-credentials">ID: duson1830<br>PW: 1830</div>
                </a>
            </div>
            <!-- 이메일 -->
            <div class="file-item">
                <a href="mailto:dsp1830@naver.com" class="file-link">
                    <div class="file-service">📧 이메일 전송</div>
                    <div class="file-email">dsp1830@naver.com</div>
                </a>
            </div>
        </div>
    </div>

    <!-- 업무안내 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">📋 업무안내</div>
        <div class="business-menu">
            <a href="/sub/attention.htm" class="business-link">📝 작업시 유의사항</a>
            <a href="/sub/expense.htm" class="business-link">💰 편집디자인비용</a>
            <a href="https://map.kakao.com/link/search/서울시 영등포구 영등포로 36길 9 송호빌딩" target="_blank" class="business-link">🗺️ 오시는길</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_bank): ?>
    <!-- 입금안내 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">🏦 입금안내</div>
        <div class="bank-info">
            <div class="bank-item">
                <div class="bank-name">국민</div>
                <div class="bank-account">999-1688-2384</div>
            </div>
            <div class="bank-item">
                <div class="bank-name">신한</div>
                <div class="bank-account">110-342-543507</div>
            </div>
            <div class="bank-item">
                <div class="bank-name">농협</div>
                <div class="bank-account">301-2632-1830-11</div>
            </div>
            <div class="bank-owner">
                <div>예금주: 두손기획인쇄</div>
                <div>차경선</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 운영시간 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">⏰ 운영시간</div>
        <div class="time-info">
            <div class="time-item">
                <span class="time-day">평일</span>
                <span class="time-hours">09:00-18:00</span>
            </div>
            <div class="time-item">
                <span class="time-day">토요일</span>
                <span class="time-hours">09:00-13:00</span>
            </div>
            <div class="time-item holiday">
                <span class="time-day">일/공휴일</span>
                <span class="time-hours">휴무</span>
            </div>
        </div>
    </div>

</div>

<style>
/* Google Fonts - Noto Sans KR */
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700&display=swap');

/* 우측 사이드바 - 내용에 맞게 높이 조절 */
.right-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    width: 165px;
    background: #f8f9fa;
    border-left: 1px solid #e9ecef;
    padding: 5px;
    font-size: 13px;
    max-height: 100vh;
    z-index: 100;
    box-shadow: -2px 0 8px rgba(0,0,0,0.1);
    overflow: hidden;
    box-sizing: border-box;
    font-family: 'Noto Sans KR', 'Noto Sans', sans-serif;
}

/* 카톡상담 특별 섹션 */
.kakao-special-section {
    margin-bottom: 5px;
    text-align: center;
}

.kakao-special-link {
    display: inline-block;
    transition: transform 0.2s ease;
    border-radius: 8px;
    overflow: hidden;
}

.kakao-special-link:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.kakao-image {
    width: 95%;
    height: auto;
    display: block;
    border-radius: 8px;
    margin: 0 auto;
}

.sidebar-section {
    margin-bottom: 5px;
    background: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.sidebar-title {
    background: #364052;
    color: white;
    text-align: center;
    padding: 6px;
    font-weight: 700;
    margin: 0;
    font-size: 12px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.sidebar-contact-info, .bank-info, .time-info {
    padding: 5px;
}

.sidebar-contact-item, .bank-item, .time-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 0;
    border-bottom: 1px dotted #e9ecef;
    font-size: 11px;
    line-height: 1.3;
}

.sidebar-contact-item:last-child, .bank-item:last-child, .time-item:last-child {
    border-bottom: none;
}

/* 고객센터 전화번호 스타일 */
.sidebar-contact-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 11px;
}

.sidebar-contact-number {
    color: #e53e3e;
    font-weight: 700;
    font-size: 11px;
    font-family: monospace;
}

.contact-label, .time-day {
    font-weight: 500;
    color: #4a5568;
    font-size: 11px;
}

.contact-value, .time-hours {
    color: #2d3748;
    font-weight: 600;
    font-size: 11px;
}

.bank-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 11px;
}

.bank-account {
    color: #e53e3e;
    font-weight: 700;
    font-size: 11px;
    font-family: monospace;
}

.bank-owner {
    text-align: center;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #e9ecef;
    font-size: 10px;
    color: #4a5568;
    font-weight: 500;
    line-height: 1.3;
}

.bank-owner div {
    margin: 0;
    padding: 0;
}

/* 파일전송 섹션 */
.file-transfer-section {
    padding: 6px;
    text-align: center;
}

.file-item {
    margin-bottom: 4px;
    padding: 4px;
    border-bottom: 1px dotted #e9ecef;
}

.file-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.file-link {
    display: block;
    text-decoration: none;
    color: inherit;
    text-align: center;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.file-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%);
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.file-service {
    font-size: 11px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 3px;
    text-align: center;
}

.file-credentials {
    font-size: 12px;
    color: #007bff;
    font-family: 'Noto Sans KR', 'Noto Sans', monospace;
    font-weight: 700;
    background: #f8f9fa;
    padding: 3px 6px;
    border-radius: 3px;
    display: block;
    line-height: 1.4;
    text-align: center;
    margin: 0 auto;
    width: fit-content;
}

.file-email {
    font-size: 11px;
    color: #007bff;
    font-weight: 500;
    text-align: center;
    display: block;
}

/* 업무안내 섹션 */
.business-menu {
    padding: 4px;
}

.business-link {
    display: block;
    padding: 5px 6px;
    color: #4a5568;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
    margin-bottom: 3px;
    font-size: 11px;
    font-weight: 500;
    border: 1px solid transparent;
}

.business-link:last-child {
    margin-bottom: 0;
}

.business-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%);
    color: #2d3748;
    transform: translateX(2px);
    border-color: #87ceeb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.time-item.holiday {
    opacity: 0.7;
}

.time-item.holiday .time-hours {
    color: #e53e3e;
}

/* 창이 줄어들면 사이드바 숨김 (1124px 이하) */
@media (max-width: 1124px) {
    .right-sidebar {
        display: none;
    }
}

/* 큰 화면에서 사이드바 표시 (1125px 이상) */
@media (min-width: 1125px) {
    .right-sidebar {
        display: block;
    }
}
</style>
