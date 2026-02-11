<?php
/**
 * Dashboard Header
 * Tailwind CSS + Chart.js CDN
 * Brand color: #1E4E79 (쇼핑몰 헤더와 통일)
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo DASHBOARD_TITLE; ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">

    <script>
        // Tailwind Config
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans KR', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#1E4E79',
                            dark: '#153A5A',
                            light: '#2D6FA8',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Top Header Bar (쇼핑몰 #1E4E79 통일) -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-brand" style="border-bottom:1px solid #153A5A;">
        <div class="flex items-center justify-between px-4 h-11">
            <div class="flex items-center gap-2.5">
                <!-- 모바일 메뉴 토글 -->
                <button id="mobile-menu-toggle" class="md:hidden p-1.5 rounded hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <!-- 로고 -->
                <a href="/dashboard/" class="flex items-center gap-2 text-white no-underline">
                    <img src="/ImgFolder/dusonlogo1.png" alt="로고" class="h-7 w-7 rounded-md bg-white/10 object-contain p-0.5" onerror="this.style.display='none'">
                    <div class="leading-tight">
                        <span class="text-sm font-bold">두손기획인쇄</span>
                        <span class="hidden sm:inline text-[10px] text-white/50 ml-1.5">관리자</span>
                    </div>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="/" target="_blank" class="text-[11px] text-white/60 hover:text-white/90 transition-colors hidden sm:inline">사이트 보기 ↗</a>
                <div class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center text-[10px] text-white font-bold">
                        <?php echo mb_substr($_SESSION['admin_username'] ?? 'A', 0, 1); ?>
                    </div>
                    <span class="text-xs text-white/80 hidden sm:inline"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Container (top header 44px = h-11) -->
    <div class="flex min-h-screen pt-11">
