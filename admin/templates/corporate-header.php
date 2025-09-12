<?php
/**
 * Corporate Design System - Header Template
 * Professional financial-style header component
 */

// Get current user info if available
$current_user = $_SESSION['admin_user'] ?? 'Administrator';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'MlangPrintAuto 관리자'); ?></title>
    
    <!-- Corporate Design System CSS -->
    <link rel="stylesheet" href="/admin/css/corporate-design-system.css">
    
    <!-- Page-specific styles -->
    <?php if (isset($additional_styles)): ?>
        <?php foreach ($additional_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <style>
        /* Page-specific overrides */
        .page-title {
            font-size: var(--text-2xl);
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 var(--space-lg) 0;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--text-sm);
            color: var(--text-secondary);
            margin-bottom: var(--space-lg);
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            color: var(--text-tertiary);
        }
        
        .header-actions {
            display: flex;
            gap: var(--space-sm);
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--text-sm);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-inverse);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="corporate-layout">
        <!-- Header -->
        <header class="corporate-header">
            <div class="flex items-center gap-md">
                <h1>🖨️ MlangPrintAuto</h1>
                <span class="badge badge-info">관리자 시스템</span>
            </div>
            
            <nav class="corporate-header-nav">
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($current_user, 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($current_user); ?>님</span>
                    </div>
                    
                    <a href="/admin/config.php?logout=1" class="btn btn-sm btn-outline" 
                       style="border-color: rgba(255,255,255,0.3); color: var(--text-inverse);">
                        로그아웃
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main Layout -->
        <main class="corporate-main">
            <!-- Sidebar Navigation -->
            <aside class="corporate-sidebar">
                <nav class="nav-menu">
                    <li>
                        <a href="/admin/index.php" <?php echo $current_page == 'index' ? 'class="active"' : ''; ?>>
                            📊 대시보드
                        </a>
                    </li>
                    <li>
                        <a href="/admin/MlangPrintAuto/admin.php" <?php echo $current_page == 'admin' ? 'class="active"' : ''; ?>>
                            📋 주문 관리
                        </a>
                        <div class="nav-submenu">
                            <a href="/admin/MlangPrintAuto/cadarok_List.php">📄 카다록 주문</a>
                            <a href="/admin/MlangPrintAuto/NameCard_List.php">💳 명함 주문</a>
                            <a href="/admin/MlangPrintAuto/envelope_List.php">✉️ 봉투 주문</a>
                            <a href="/admin/MlangPrintAuto/sticker_List.php">🏷️ 스티커 주문</a>
                            <a href="/admin/MlangPrintAuto/MerchandiseBond_List.php">📜 상품권 주문</a>
                            <a href="/admin/MlangPrintAuto/NcrFlambeau_List.php">📋 NCR 주문</a>
                        </div>
                    </li>
                    <li>
                        <a href="/admin/member/admin.php" <?php echo $current_page == 'member' ? 'class="active"' : ''; ?>>
                            👥 회원 관리
                        </a>
                    </li>
                    <li>
                        <a href="/admin/quote_management.php" <?php echo $current_page == 'quote_management' ? 'class="active"' : ''; ?>>
                            💰 견적 관리
                        </a>
                    </li>
                    <li>
                        <a href="/admin/bbs_admin.php" <?php echo $current_page == 'bbs_admin' ? 'class="active"' : ''; ?>>
                            💬 게시판 관리
                        </a>
                    </li>
                    <li>
                        <a href="/admin/AdminConfig.php" <?php echo $current_page == 'AdminConfig' ? 'class="active"' : ''; ?>>
                            ⚙️ 시스템 설정
                        </a>
                    </li>
                </nav>
            </aside>

            <!-- Content Area -->
            <section class="corporate-content">
                <div class="corporate-content-inner">
                    <!-- Breadcrumb Navigation -->
                    <?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
                        <nav class="breadcrumb">
                            <a href="/admin/">홈</a>
                            <?php foreach ($breadcrumb as $item): ?>
                                <span class="breadcrumb-separator">›</span>
                                <?php if (isset($item['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($item['url']); ?>">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                <?php else: ?>
                                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                    
                    <!-- Page Title -->
                    <?php if (isset($page_title)): ?>
                        <h2 class="page-title"><?php echo htmlspecialchars($page_title); ?></h2>
                    <?php endif; ?>
                    
                    <!-- Page Content Container -->
                    <div class="compact-container">
                        <div class="compact-body">