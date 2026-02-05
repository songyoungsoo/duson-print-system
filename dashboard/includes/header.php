<?php
/**
 * Dashboard Header
 * Tailwind CSS + Chart.js CDN
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Mobile Menu Toggle -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50 px-4 py-3 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-800">두손기획 관리</h1>
        <button id="mobile-menu-toggle" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>
    
    <!-- Layout Container -->
    <div class="flex min-h-screen pt-14 md:pt-0">
