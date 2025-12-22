<?php
/**
 * 우측 사이드바 - 모든 페이지에서 공통 사용
 * 기존 right.php를 현대적인 디자인으로 교체
 */

// 사이드바 표시 옵션 (기본값 모두 표시)
$show_contact = isset($show_contact) ? $show_contact : true;
$show_menu = isset($show_menu) ? $show_menu : true;
$show_bank = isset($show_bank) ? $show_bank : true;
?>

<!-- 우측 사이드바 시작 -->
<div class="right-sidebar">

    <?php if($show_contact): ?>
    <!-- 고객센터 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">📞 고객센터</div>
        <div class="contact-info">
            <div class="contact-item">
                <span class="contact-label">대표전화:</span>
                <span class="contact-value">1688-2384</span>
            </div>
            <div class="contact-item">
                <span class="contact-label">직통:</span>
                <span class="contact-value">02-2632-1830</span>
            </div>
            <div class="contact-item">
                <span class="contact-label">팩스:</span>
                <span class="contact-value">02-2632-1829</span>
            </div>
            <div class="contact-item">
                <span class="contact-label">야간:</span>
                <span class="contact-value">010-3712-1830</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_menu): ?>
    <!-- 빠른메뉴 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">⚡ 빠른메뉴</div>
        <div class="quick-menu">
            <a href="/account/orders.php" class="menu-link">📋 주문내역</a>
            <a href="/shop/cart.php" class="menu-link">🛒 장바구니</a>
            <a href="mailto:dsp1830@naver.com" class="menu-link">✉️ 이메일문의</a>
            <a href="http://pf.kakao.com/_pEGhj/chat" target="_blank" class="menu-link kakao-link">💬 카톡상담</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_bank): ?>
    <!-- 입금안내 섹션 -->
    <div class="sidebar-section">
        <div class="sidebar-title">🏦 입금안내</div>
        <div class="bank-info">
            <div class="bank-item">
                <div class="bank-name">국민은행</div>
                <div class="bank-account">999-1688-2384</div>
            </div>
            <div class="bank-item">
                <div class="bank-name">신한은행</div>
                <div class="bank-account">110-342-543507</div>
            </div>
            <div class="bank-item">
                <div class="bank-name">농협</div>
                <div class="bank-account">301-2632-1829</div>
            </div>
            <div class="bank-owner">
                <span>예금주: 두손기획인쇄 차경선</span>
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
/* 우측 사이드바 전용 스타일 */
.right-sidebar {
    width: 160px !important;
    background: #f8f9fa !important;
    border-left: 1px solid #e9ecef !important;
    padding: 1rem 0.5rem !important;
    font-size: 13px !important;
    min-height: 100vh !important;
    font-family: 'Noto Sans KR', sans-serif !important;
}

.sidebar-section {
    margin-bottom: 1.2rem !important;
    background: white !important;
    border-radius: 8px !important;
    padding: 0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    overflow: hidden !important;
    border: 1px solid #e9ecef !important;
}

.sidebar-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    text-align: center !important;
    padding: 10px 8px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    font-size: 12px !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3) !important;
    border-bottom: 1px solid rgba(255,255,255,0.2) !important;
}

.contact-info, .bank-info, .time-info {
    padding: 12px !important;
}

.contact-item, .bank-item, .time-item {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 6px 0 !important;
    border-bottom: 1px dotted #e9ecef !important;
    font-size: 12px !important;
}

.contact-item:last-child, .bank-item:last-child, .time-item:last-child {
    border-bottom: none !important;
}

.contact-label, .time-day {
    font-weight: 500 !important;
    color: #4a5568 !important;
    font-size: 11px !important;
}

.contact-value, .bank-account, .time-hours {
    color: #2d3748 !important;
    font-weight: 600 !important;
    font-size: 11px !important;
}

.bank-name {
    font-weight: 600 !important;
    color: #2d3748 !important;
    font-size: 11px !important;
}

.bank-account {
    color: #e53e3e !important;
    font-weight: 700 !important;
    font-size: 10px !important;
    font-family: monospace !important;
}

.bank-owner {
    text-align: center !important;
    margin-top: 8px !important;
    padding-top: 8px !important;
    border-top: 1px solid #e9ecef !important;
    font-size: 10px !important;
    color: #4a5568 !important;
    font-weight: 500 !important;
}

.quick-menu {
    padding: 8px !important;
}

.menu-link {
    display: block !important;
    padding: 8px 10px !important;
    color: #4a5568 !important;
    text-decoration: none !important;
    border-radius: 4px !important;
    transition: all 0.2s ease !important;
    margin-bottom: 4px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    border: 1px solid transparent !important;
}

.menu-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%) !important;
    color: #2d3748 !important;
    transform: translateX(3px) !important;
    border-color: #87ceeb !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.menu-link.kakao-link:hover {
    background: linear-gradient(135deg, #fff5cc 0%, #ffeb3b 100%) !important;
    border-color: #ffc107 !important;
}

.time-item.holiday {
    opacity: 0.7 !important;
}

.time-item.holiday .time-hours {
    color: #e53e3e !important;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .right-sidebar {
        display: none !important;
    }
}

/* 태블릿 및 데스크톱에서만 사이드바 표시 */
@media (min-width: 769px) {
    .right-sidebar {
        width: 160px !important;
        border-left: 1px solid #e9ecef !important;
        min-height: 100vh !important;
    }

    .sidebar-section {
        margin-bottom: 1.2rem !important;
    }

    .contact-item, .bank-item, .time-item {
        padding: 6px 0 !important;
    }

    .menu-link {
        padding: 8px 10px !important;
        font-size: 11px !important;
    }
}
</style>