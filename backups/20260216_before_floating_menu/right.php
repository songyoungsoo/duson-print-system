<?php
/**
 * 우측 사이드바 - 모든 페이지에서 공통 사용
 * 프로덕션 버전 (dsp1830.shop)
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
/* =====================================================
   우측 사이드바 전용 스타일 (right-sidebar scope)
   - !important 최소화, 높은 특이성으로 우선순위 확보
   ===================================================== */

/* 메인 컨테이너 */
div.right-sidebar {
    position: fixed;
    right: 0;
    top: 0;
    width: 170px;
    min-height: 100vh;
    background: #f8f9fa;
    border-left: 1px solid #e9ecef;
    padding: 12px 8px;
    font-family: 'Noto Sans KR', -apple-system, sans-serif;
    font-size: 13px;
    z-index: 100;
    overflow-y: auto;
    box-shadow: -2px 0 8px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

/* 섹션 박스 */
div.right-sidebar .sidebar-section {
    margin-bottom: 14px;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    overflow: hidden;
}

/* 섹션 타이틀 */
div.right-sidebar .sidebar-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    text-align: center;
    padding: 10px 8px;
    font-size: 13px;
    font-weight: 700;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.25);
}

/* 컨텐츠 영역 패딩 */
div.right-sidebar .contact-info,
div.right-sidebar .bank-info,
div.right-sidebar .time-info {
    padding: 10px 12px;
}

/* 아이템 행 (flex 레이아웃) */
div.right-sidebar .contact-item,
div.right-sidebar .bank-item,
div.right-sidebar .time-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px dotted #e9ecef;
    font-size: 12px;
    line-height: 1.4;
}

div.right-sidebar .contact-item:last-child,
div.right-sidebar .bank-item:last-child,
div.right-sidebar .time-item:last-child {
    border-bottom: none;
}

/* 라벨 (왼쪽) */
div.right-sidebar .contact-label,
div.right-sidebar .time-day {
    font-size: 11px;
    font-weight: 500;
    color: #555;
    flex-shrink: 0;
}

/* 값 (오른쪽) */
div.right-sidebar .contact-value,
div.right-sidebar .time-hours {
    font-size: 12px;
    font-weight: 600;
    color: #222;
    text-align: right;
}

/* 은행 정보 */
div.right-sidebar .bank-name {
    font-size: 12px;
    font-weight: 600;
    color: #333;
}

div.right-sidebar .bank-account {
    font-size: 11px;
    font-weight: 700;
    color: #d32f2f;
    font-family: 'Consolas', 'Monaco', monospace;
    letter-spacing: -0.3px;
}

div.right-sidebar .bank-owner {
    text-align: center;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e9ecef;
    font-size: 11px;
    color: #555;
    font-weight: 500;
}

/* 빠른메뉴 */
div.right-sidebar .quick-menu {
    padding: 8px;
}

div.right-sidebar .menu-link {
    display: block;
    padding: 8px 10px;
    margin-bottom: 4px;
    font-size: 12px;
    font-weight: 500;
    color: #444;
    text-decoration: none;
    border-radius: 5px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

div.right-sidebar .menu-link:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
    border-color: #90caf9;
    transform: translateX(2px);
}

div.right-sidebar .menu-link.kakao-link:hover {
    background: linear-gradient(135deg, #fff9c4 0%, #ffee58 100%);
    color: #795548;
    border-color: #ffc107;
}

/* 휴일 표시 */
div.right-sidebar .time-item.holiday {
    opacity: 0.75;
}

div.right-sidebar .time-item.holiday .time-hours {
    color: #d32f2f;
}

/* =====================================================
   반응형: 1124px 이하에서 숨김
   ===================================================== */
@media (max-width: 1124px) {
    div.right-sidebar {
        display: none;
    }
}
</style>
