<?php
/**
 * 제품별 Schema.org 구조화 데이터
 * 사용: <?php include_once 'product_schema.php'; echo_product_schema('inserted'); ?>
 */

function echo_product_schema($product_key) {
    $products = [
        'inserted' => [
            'name' => '전단지 인쇄',
            'description' => '전단지 인쇄 전문. A4, A5, B5 전단지 소량부터 대량까지 빠른 제작. 디자인 지원.',
            'category' => '전단지/리플렛',
            'url' => '/mlangprintauto/inserted/',
        ],
        'sticker_new' => [
            'name' => '스티커 제작',
            'description' => '라벨 스티커, 원형·사각·모양 스티커 맞춤 제작. 소량 100매부터 대량까지.',
            'category' => '스티커',
            'url' => '/mlangprintauto/sticker_new/',
        ],
        'namecard' => [
            'name' => '명함 인쇄',
            'description' => '고급 명함, 양면 컬러, 다양한 용지 선택. 100매부터 빠른 제작.',
            'category' => '명함',
            'url' => '/mlangprintauto/namecard/',
        ],
        'envelope' => [
            'name' => '봉투 인쇄',
            'description' => '대봉투, 소봉투, 창봉투 맞춤 제작. 회사 로고·주소 인쇄 가능.',
            'category' => '봉투',
            'url' => '/mlangprintauto/envelope/',
        ],
        'littleprint' => [
            'name' => '포스터·리플렛 인쇄',
            'description' => 'A3, B3 포스터, 2단·3단 리플렛 소량부터 대량까지. 고품질 옵셋 인쇄.',
            'category' => '포스터/리플렛',
            'url' => '/mlangprintauto/littleprint/',
        ],
        'cadarok' => [
            'name' => '카탈로그 제작',
            'description' => '중철·무선 제본 카다록 맞춤 제작. 소량부터 대량까지.',
            'category' => '카탈로그/브로슈어',
            'url' => '/mlangprintauto/cadarok/',
        ],
        'merchandisebond' => [
            'name' => '상품권·쿠폰 인쇄',
            'description' => '매장 상품권, 할인 쿠폰, 이용권 맞춤 제작. 넘버링 가능.',
            'category' => '상품권/쿠폰',
            'url' => '/mlangprintauto/merchandisebond/',
        ],
        'ncrflambeau' => [
            'name' => 'NCR양식지 인쇄',
            'description' => '2매·3매·4매 복사양식지 맞춤 제작. 견적서, 주문서, 계약서 양식.',
            'category' => 'NCR양식지',
            'url' => '/mlangprintauto/ncrflambeau/',
        ],
        'msticker' => [
            'name' => '자석스티커 제작',
            'description' => '냉장고 자석, 차량용 마그넷, 홍보용 자석스티커 맞춤 제작.',
            'category' => '자석스티커',
            'url' => '/mlangprintauto/msticker/',
        ],
    ];

    if (!isset($products[$product_key])) return;
    $p = $products[$product_key];
    $base = 'https://dsp114.co.kr';

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $p['name'] . ' - 두손기획인쇄',
        'description' => $p['description'],
        'category' => $p['category'],
        'url' => $base . $p['url'],
        'provider' => [
            '@type' => 'LocalBusiness',
            'name' => '두손기획인쇄',
            'telephone' => '02-2632-1830',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '영등포로 36길 9 송호빌딩 1층',
                'addressLocality' => '영등포구',
                'addressRegion' => '서울시',
                'postalCode' => '07222',
                'addressCountry' => 'KR',
            ],
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'KR',
        ],
    ];

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
