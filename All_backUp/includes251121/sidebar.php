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
                <div class="bank-account">301-2632-1829</div>
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
/* 좌측 사이드바 전용 스타일 - 플로팅 방식 */
.right-sidebar {
    position: fixed !important;
    top: 120px !important;
    left: 20px !important;
    width: 176px !important;
    background: #f8f9fa !important;
    border: 1px solid #e9ecef !important;
    border-radius: 8px !important;
    padding: 0.5rem !important;
    font-size: 14px !important;
    z-index: 1000 !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    max-height: calc(100vh - 140px) !important;
    overflow-y: auto !important;
}

/* 카톡상담 특별 섹션 */
.kakao-special-section {
    margin-bottom: 0.3rem !important;
    text-align: center !important;
}

.kakao-special-link {
    display: block !important;
    transition: transform 0.2s ease !important;
    border-radius: 8px !important;
    overflow: hidden !important;
}

.kakao-special-link:hover {
    transform: scale(1.05) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
}

.kakao-image {
    width: 100% !important;
    height: auto !important;
    display: block !important;
    border-radius: 8px !important;
}

.sidebar-section {
    margin-bottom: 0.4rem !important;
    background: white !important;
    border-radius: 8px !important;
    padding: 0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    overflow: hidden !important;
    border: 1px solid #e9ecef !important;
}

.sidebar-title {
    background: #6c757d !important;
    color: white !important;
    text-align: center !important;
    padding: 4px 6px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    font-size: 11px !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
    border-bottom: 1px solid rgba(255,255,255,0.2) !important;
}

.sidebar-contact-info, .bank-info, .time-info {
    padding: 3px !important;
}

.sidebar-contact-item, .bank-item, .time-item {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 1px 0 !important;
    border-bottom: 1px dotted #e9ecef !important;
    font-size: 13px !important;
    line-height: 1.1 !important;
}

.sidebar-contact-item:last-child, .bank-item:last-child, .time-item:last-child {
    border-bottom: none !important;
}

/* 사이드바 고객센터 전화번호 스타일 (입금안내와 동일) */
.sidebar-contact-name {
    font-weight: 600 !important;
    color: #2d3748 !important;
    font-size: 12px !important;
}

.sidebar-contact-number {
    color: #e53e3e !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    font-family: monospace !important;
}

.contact-label, .time-day {
    font-weight: 500 !important;
    color: #4a5568 !important;
    font-size: 12px !important;
}

.contact-value, .bank-account, .time-hours {
    color: #2d3748 !important;
    font-weight: 600 !important;
    font-size: 12px !important;
}

.bank-name {
    font-weight: 600 !important;
    color: #2d3748 !important;
    font-size: 12px !important;
}

.bank-account {
    color: #e53e3e !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    font-family: monospace !important;
}

.bank-owner {
    text-align: center !important;
    margin-top: 4px !important;
    padding-top: 4px !important;
    border-top: 1px solid #e9ecef !important;
    font-size: 11px !important;
    color: #4a5568 !important;
    font-weight: 500 !important;
    line-height: 1.0 !important;
}

.bank-owner div {
    margin: 0 !important;
    padding: 0 !important;
}

/* 파일전송 섹션 */
.file-transfer-section {
    padding: 3px !important;
}

.file-item {
    margin-bottom: 3px !important;
    padding: 3px !important;
    border-bottom: 1px dotted #e9ecef !important;
}

.file-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
}

.file-link {
    display: block !important;
    text-decoration: none !important;
    color: inherit !important;
    text-align: center !important;
    padding: 2px !important;
    border-radius: 4px !important;
    transition: all 0.2s ease !important;
}

.file-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.file-service {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #2d3748 !important;
    margin-bottom: 2px !important;
}

.file-credentials {
    font-size: 12px !important;
    color: #007bff !important;
    font-family: monospace !important;
    background: #f8f9fa !important;
    padding: 2px 4px !important;
    border-radius: 3px !important;
    display: inline-block !important;
    line-height: 1.2 !important;
}

.file-email {
    font-size: 11px !important;
    color: #007bff !important;
    font-weight: 500 !important;
}

/* 업무안내 섹션 */
.business-menu {
    padding: 2px !important;
}

.business-link {
    display: block !important;
    padding: 3px 4px !important;
    color: #4a5568 !important;
    text-decoration: none !important;
    border-radius: 4px !important;
    transition: all 0.2s ease !important;
    margin-bottom: 1px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    border: 1px solid transparent !important;
}

.business-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%) !important;
    color: #2d3748 !important;
    transform: translateX(3px) !important;
    border-color: #87ceeb !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.time-item.holiday {
    opacity: 0.7 !important;
}

.time-item.holiday .time-hours {
    color: #e53e3e !important;
}

/* 모바일 반응형 - 카카오톡 이하 모든 사이드바 숨김 */
@media (max-width: 768px) {
    .right-sidebar {
        display: none !important;
    }
}

/* 태블릿 및 데스크톱에서만 사이드바 표시 */
@media (min-width: 769px) {
    .right-sidebar {
        position: fixed !important;
        top: 120px !important;
        left: 20px !important;
        width: 176px !important;
        z-index: 1000 !important;
    }
}
</style>