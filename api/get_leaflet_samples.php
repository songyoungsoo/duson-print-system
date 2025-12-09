<?php
/**
 * 전단지 샘플 이미지 제공 API
 * 실제 이미지가 없을 때 대체 이미지를 생성하여 제공
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 샘플 데이터 생성 (실제 이미지가 없으므로 플레이스홀더 제공)
$sampleImages = [];

// 전단지 샘플 데이터 생성
$leafletTypes = [
    ['name' => 'A4 전단지', 'color' => '#4CAF50', 'icon' => '📄'],
    ['name' => 'A5 전단지', 'color' => '#2196F3', 'icon' => '📋'],
    ['name' => '리플렛', 'color' => '#FF9800', 'icon' => '📑'],
    ['name' => '브로슈어', 'color' => '#9C27B0', 'icon' => '📰'],
    ['name' => '홍보 전단', 'color' => '#F44336', 'icon' => '📢'],
    ['name' => '이벤트 전단', 'color' => '#00BCD4', 'icon' => '🎉'],
    ['name' => '메뉴판', 'color' => '#8BC34A', 'icon' => '🍽️'],
    ['name' => '카탈로그', 'color' => '#795548', 'icon' => '📚']
];

// SVG 플레이스홀더 생성 함수
function createPlaceholderSVG($title, $color, $icon, $index) {
    $width = 400;
    $height = 300;
    
    // 그라데이션 색상 생성
    $lightColor = adjustBrightness($color, 40);
    $darkColor = adjustBrightness($color, -20);
    
    $svg = '<?xml version="1.0" encoding="UTF-8"?>
    <svg width="'.$width.'" height="'.$height.'" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="grad'.$index.'" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:'.$lightColor.';stop-opacity:1" />
                <stop offset="100%" style="stop-color:'.$darkColor.';stop-opacity:1" />
            </linearGradient>
            <pattern id="pattern'.$index.'" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                <rect width="40" height="40" fill="url(#grad'.$index.')" />
                <circle cx="20" cy="20" r="2" fill="white" opacity="0.1"/>
            </pattern>
        </defs>
        
        <!-- 배경 -->
        <rect width="'.$width.'" height="'.$height.'" fill="url(#grad'.$index.')" />
        
        <!-- 패턴 오버레이 -->
        <rect width="'.$width.'" height="'.$height.'" fill="url(#pattern'.$index.')" opacity="0.3" />
        
        <!-- 중앙 원 -->
        <circle cx="'.($width/2).'" cy="'.($height/2 - 20).'" r="60" fill="white" opacity="0.2"/>
        <circle cx="'.($width/2).'" cy="'.($height/2 - 20).'" r="50" fill="white" opacity="0.3"/>
        
        <!-- 아이콘 텍스트 (이모지는 SVG에서 직접 표시 안됨) -->
        <text x="'.($width/2).'" y="'.($height/2 - 10).'" text-anchor="middle" font-family="Arial" font-size="48" fill="white">
            '.$icon.'
        </text>
        
        <!-- 제목 -->
        <text x="'.($width/2).'" y="'.($height/2 + 40).'" text-anchor="middle" font-family="Noto Sans KR, Arial" font-size="20" font-weight="bold" fill="white">
            '.$title.'
        </text>
        
        <!-- 샘플 번호 -->
        <text x="'.($width/2).'" y="'.($height/2 + 65).'" text-anchor="middle" font-family="Arial" font-size="14" fill="white" opacity="0.8">
            샘플 #'.($index + 1).'
        </text>
        
        <!-- 장식 요소 -->
        <rect x="20" y="20" width="60" height="2" fill="white" opacity="0.5"/>
        <rect x="20" y="26" width="40" height="2" fill="white" opacity="0.3"/>
        
        <rect x="'.($width - 80).'" y="'.($height - 32).'" width="60" height="2" fill="white" opacity="0.5"/>
        <rect x="'.($width - 60).'" y="'.($height - 26).'" width="40" height="2" fill="white" opacity="0.3"/>
    </svg>';
    
    return $svg;
}

// 색상 밝기 조정 함수
function adjustBrightness($hexColor, $percent) {
    $hex = ltrim($hexColor, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

// 이미지 데이터 생성
foreach ($leafletTypes as $index => $type) {
    $svg = createPlaceholderSVG($type['name'], $type['color'], $type['icon'], $index);
    $base64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
    
    $sampleImages[] = [
        'id' => 'sample_' . ($index + 1),
        'title' => $type['name'] . ' 샘플',
        'path' => $base64,
        'image_path' => $base64,
        'thumbnail' => $base64,
        'thumbnail_path' => $base64,
        'url' => $base64,
        'thumb' => $base64,
        'category' => 'inserted',
        'type' => 'leaflet',
        'type_name' => '전단지',
        'description' => $type['name'] . ' 디자인 샘플입니다',
        'is_placeholder' => true,
        'color' => $type['color'],
        'icon' => $type['icon']
    ];
}

// 요청 파라미터 처리
$showAll = isset($_GET['all']) && $_GET['all'] === 'true';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : ($showAll ? 12 : 4);

// 페이지네이션 적용
$totalCount = count($sampleImages);
$totalPages = ceil($totalCount / $perPage);
$offset = ($page - 1) * $perPage;
$pagedImages = array_slice($sampleImages, $offset, $perPage);

// JSON 응답
echo json_encode([
    'success' => true,
    'data' => $pagedImages,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $perPage,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'has_next' => $page < $totalPages,
        'has_prev' => $page > 1,
        'next_page' => $page < $totalPages ? $page + 1 : null,
        'prev_page' => $page > 1 ? $page - 1 : null
    ],
    'count' => count($pagedImages),
    'source' => 'sample_gallery',
    'category' => 'inserted',
    'version' => '1.0',
    'description' => '전단지 디자인 샘플 갤러리',
    'note' => '다양한 전단지 디자인 샘플을 확인하실 수 있습니다'
], JSON_UNESCAPED_UNICODE);
?>