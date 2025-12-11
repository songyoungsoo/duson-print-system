<?php
/**
 * 마이페이지 공통 사이드바 컴포넌트
 * 경로: /mypage/sidebar.php
 */

// 현재 페이지 파악
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="mypage-sidebar">
    <div class="sidebar-header">
        <h3>🏠 마이페이지</h3>
        <p class="user-welcome"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>님</p>
    </div>

    <nav class="sidebar-nav">
        <a href="/mypage/index.php" class="nav-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">마이페이지 홈</span>
        </a>

        <a href="/mypage/index.php#order-history" class="nav-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📦</span>
            <span class="nav-text">주문조회&배송조회</span>
        </a>

        <a href="/mypage/tax_invoices.php" class="nav-item <?php echo $current_page == 'tax_invoices.php' ? 'active' : ''; ?>">
            <span class="nav-icon">🧾</span>
            <span class="nav-text">전자세금계산서</span>
        </a>

        <a href="/mypage/transactions.php" class="nav-item <?php echo $current_page == 'transactions.php' ? 'active' : ''; ?>">
            <span class="nav-icon">💳</span>
            <span class="nav-text">거래내역조회</span>
        </a>

        <div class="nav-divider"></div>

        <a href="/mypage/profile.php" class="nav-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <span class="nav-icon">👤</span>
            <span class="nav-text">회원정보수정</span>
        </a>

        <a href="/mypage/change_password.php" class="nav-item <?php echo $current_page == 'change_password.php' ? 'active' : ''; ?>">
            <span class="nav-icon">🔒</span>
            <span class="nav-text">비밀번호변경</span>
        </a>

        <a href="/mypage/business_certificate.php" class="nav-item <?php echo $current_page == 'business_certificate.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📄</span>
            <span class="nav-text">사업자등록증</span>
        </a>

        <div class="nav-divider"></div>

        <a href="/mypage/withdraw.php" class="nav-item <?php echo $current_page == 'withdraw.php' ? 'active' : ''; ?>">
            <span class="nav-icon">⚠️</span>
            <span class="nav-text">회원탈퇴</span>
        </a>
    </nav>
</div>

<style>
.mypage-sidebar {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.sidebar-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #1466BA;
}

.sidebar-header h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
}

.user-welcome {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: #555;
    transition: all 0.2s;
    font-size: 14px;
}

.nav-item:hover {
    background: #e9ecef;
    color: #1466BA;
}

.nav-item.active {
    background: #1466BA;
    color: white;
    font-weight: 600;
}

.nav-icon {
    font-size: 18px;
    margin-right: 10px;
    width: 24px;
    text-align: center;
}

.nav-text {
    flex: 1;
}

.nav-divider {
    height: 1px;
    background: #dee2e6;
    margin: 8px 0;
}

@media (max-width: 768px) {
    .mypage-sidebar {
        padding: 15px;
    }

    .nav-item {
        padding: 10px 12px;
        font-size: 13px;
    }

    .nav-icon {
        font-size: 16px;
        margin-right: 8px;
    }
}
</style>
