<?php
/**
 * 우측 사이드바 - 공통 컴포넌트
 * 모든 품목 페이지에서 include로 사용
 *
 * 사용법: <?php include '../includes/right_sidebar.php'; ?>
 */

// 사이드바 표시 옵션 (각 페이지에서 설정 가능)
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
    width: 140px;
    background: #f8f9fa;
    border-left: 1px solid #e9ecef;
    padding: 1rem 0.5rem;
    font-size: 13px;
    min-height: 100vh;
}

.sidebar-section {
    margin-bottom: 1.2rem;
    background: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.sidebar-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    padding: 10px 8px;
    font-weight: 700;
    margin: 0;
    font-size: 12px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.contact-info, .bank-info, .time-info {
    padding: 12px;
}

.contact-item, .bank-item, .time-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dotted #e9ecef;
    font-size: 12px;
}

.contact-item:last-child, .bank-item:last-child, .time-item:last-child {
    border-bottom: none;
}

.contact-label, .time-day {
    font-weight: 500;
    color: #4a5568;
    font-size: 11px;
}

.contact-value, .bank-account, .time-hours {
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
    font-size: 10px;
    font-family: monospace;
}

.bank-owner {
    text-align: center;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e9ecef;
    font-size: 10px;
    color: #4a5568;
    font-weight: 500;
}

.quick-menu {
    padding: 4px;
}

.menu-link {
    display: block;
    padding: 4px 6px;
    color: #4a5568;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.2s ease;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 500;
    border: 1px solid transparent;
}

.menu-link:hover {
    background: linear-gradient(135deg, #e6f3ff 0%, #b3d9ff 100%);
    color: #2d3748;
    transform: translateX(3px);
    border-color: #87ceeb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.menu-link.kakao-link:hover {
    background: linear-gradient(135deg, #fff5cc 0%, #ffeb3b 100%);
    border-color: #ffc107;
}

.time-item.holiday {
    opacity: 0.7;
}

.time-item.holiday .time-hours {
    color: #e53e3e;
}

/* 모바일 반응형 - 카카오톡 이하 모든 사이드바 숨김 */
@media (max-width: 768px) {
    .right-sidebar {
        display: none;
    }
}

/* 태블릿 및 데스크톱에서만 사이드바 표시 */
@media (min-width: 769px) {
    .right-sidebar {
        width: 140px;
        border-left: 1px solid #e9ecef;
        min-height: 100vh;
    }

    .sidebar-section {
        margin-bottom: 1.2rem;
    }

    .contact-item, .bank-item, .time-item {
        padding: 6px 0;
    }

    .menu-link {
        padding: 4px 6px;
        font-size: 11px;
    }
}
</style>