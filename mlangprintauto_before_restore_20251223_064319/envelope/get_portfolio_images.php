<?php
header('Content-Type: application/json; charset=utf-8');

// 봉투 포트폴리오 이미지 API
$category = $_GET['category'] ?? 'envelope';
$mode = $_GET['mode'] ?? 'thumbnail';
$limit = intval($_GET['limit'] ?? 10);

// 봉투 포트폴리오 이미지 목록
$envelope_images = [
    [
        'id' => 1,
        'name' => '우편봉투 A4',
        'thumbnail' => '/mlangprintauto/envelope/images/portfolio/env_a4_thumb.jpg',
        'full' => '/mlangprintauto/envelope/images/portfolio/env_a4_full.jpg',
        'description' => 'A4 우편봉투'
    ],
    [
        'id' => 2,
        'name' => '각대봉투 B5',
        'thumbnail' => '/mlangprintauto/envelope/images/portfolio/env_b5_thumb.jpg',
        'full' => '/mlangprintauto/envelope/images/portfolio/env_b5_full.jpg',
        'description' => 'B5 각대봉투'
    ],
    [
        'id' => 3,
        'name' => '소봉투 엽서',
        'thumbnail' => '/mlangprintauto/envelope/images/portfolio/env_postcard_thumb.jpg',
        'full' => '/mlangprintauto/envelope/images/portfolio/env_postcard_full.jpg',
        'description' => '엽서용 소봉투'
    ],
    [
        'id' => 4,
        'name' => '대봉투 A3',
        'thumbnail' => '/mlangprintauto/envelope/images/portfolio/env_a3_thumb.jpg',
        'full' => '/mlangprintauto/envelope/images/portfolio/env_a3_full.jpg',
        'description' => 'A3 대봉투'
    ]
];

// 기본 이미지로 대체 (실제 이미지가 없는 경우)
foreach ($envelope_images as &$image) {
    $image['thumbnail'] = '/mlangprintauto/images/default_envelope_thumb.jpg';
    $image['full'] = '/mlangprintauto/images/default_envelope_full.jpg';
}

// 제한 수량 적용
$result = array_slice($envelope_images, 0, $limit);

echo json_encode([
    'success' => true,
    'data' => $result,
    'total' => count($envelope_images),
    'category' => $category,
    'mode' => $mode
], JSON_UNESCAPED_UNICODE);
?>