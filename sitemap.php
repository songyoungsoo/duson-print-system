<?php
/**
 * sitemap.xml - 동적 사이트맵 생성
 * Google Search Console SEO 등록용
 *
 * 사용법: https://dsp114.com/sitemap.xml (sitemap.php를 .htaccess로 리라이트)
 *
 * 참고: 관리자 페이지, 로그인 필수 페이지 제외
 */

// 도메인 설정 (구 dsp114.com은 HTTP)
$host = $_SERVER['HTTP_HOST'] ?? 'dsp114.com';

// 구 사이트는 HTTP 강제
if (in_array($host, ['dsp114.com', 'www.dsp114.com'])) {
    $baseUrl = 'http://' . $host;
} else {
    // 로컬 개발 환경은 자동 감지
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $host;
}

// 현재 시간
$currentTime = date('c');

// 기본 우선순위 및 변경 빈도 설정
$defaultPriority = '0.80';
$defaultChangefreq = 'weekly';

// 사이트맵에 포함할 페이지 목록
// loc: URL, priority: 0.0~1.0, changefreq: always, hourly, daily, weekly, monthly, yearly, never
$pages = [
    // 메인 페이지
    [
        'loc' => '',
        'priority' => '1.00',
        'changefreq' => 'daily'
    ],

    // ========== 9개 품목 카테고리 ==========
    // 1. 전단지 (inserted)
    [
        'loc' => 'mlangprintauto/inserted/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/inserted/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 2. 스티커 (sticker_new)
    [
        'loc' => 'mlangprintauto/sticker_new/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/sticker_new/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 3. 자석스티커 (msticker)
    [
        'loc' => 'mlangprintauto/msticker/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/msticker/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 4. 명함 (namecard)
    [
        'loc' => 'mlangprintauto/namecard/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/namecard/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 5. 봉투 (envelope)
    [
        'loc' => 'mlangprintauto/envelope/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/envelope/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 6. 포스터/소량인쇄 (littleprint)
    [
        'loc' => 'mlangprintauto/littleprint/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/littleprint/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 7. 상품권 (merchandisebond)
    [
        'loc' => 'mlangprintauto/merchandisebond/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/merchandisebond/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 8. 카다록 (cadarok)
    [
        'loc' => 'mlangprintauto/cadarok/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/cadarok/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // 9. NCR양식지 (ncrflambeau)
    [
        'loc' => 'mlangprintauto/ncrflambeau/',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'mlangprintauto/ncrflambeau/index.php',
        'priority' => '0.90',
        'changefreq' => 'weekly'
    ],

    // ========== 장바구니 및 주문 ==========
    [
        'loc' => 'mlangprintauto/cart.php',
        'priority' => '0.70',
        'changefreq' => 'monthly'
    ],

    // ========== 회원 관련 (공개 페이지만) ==========
    [
        'loc' => 'member/form.php',
        'priority' => '0.60',
        'changefreq' => 'monthly'
    ],
    [
        'loc' => 'session/login.php',
        'priority' => '0.60',
        'changefreq' => 'monthly'
    ],

    // ========== 마이페이지 (공개만) ==========
    [
        'loc' => 'mypage/',
        'priority' => '0.60',
        'changefreq' => 'monthly'
    ],

    // ========== 고객센터/게시판 ==========
    [
        'loc' => 'bbs/board.php?bo_table=notice',
        'priority' => '0.80',
        'changefreq' => 'daily'
    ],
    [
        'loc' => 'bbs/board.php?bo_table=faq',
        'priority' => '0.70',
        'changefreq' => 'weekly'
    ],
    [
        'loc' => 'bbs/board.php?bo_table=qna',
        'priority' => '0.70',
        'changefreq' => 'daily'
    ],

    // ========== 회사 소개 페이지 ==========
    // 두손기획 (과거 브랜드명, SEO용)
    [
        'loc' => 'duson-planning.php',
        'priority' => '0.80',
        'changefreq' => 'monthly'
    ],
    [
        'loc' => 'sub/company.php',
        'priority' => '0.70',
        'changefreq' => 'monthly'
    ],
    [
        'loc' => 'sub/location.php',
        'priority' => '0.60',
        'changefreq' => 'monthly'
    ],
    [
        'loc' => 'sub/agreement.php',
        'priority' => '0.50',
        'changefreq' => 'yearly'
    ],
    [
        'loc' => 'sub/privacy.php',
        'priority' => '0.50',
        'changefreq' => 'yearly'
    ],

    // ========== 기타 ==========
    [
        'loc' => 'index.php',
        'priority' => '1.00',
        'changefreq' => 'daily'
    ],
];

// XML 헤더 출력
header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/sitemap.xsl"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

// 각 페이지 출력
foreach ($pages as $page) {
    $url = $baseUrl . '/' . $page['loc'];
    $priority = $page['priority'] ?? $defaultPriority;
    $changefreq = $page['changefreq'] ?? $defaultChangefreq;

    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
    echo '    <lastmod>' . $currentTime . '</lastmod>' . "\n";
    echo '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
    echo '    <priority>' . $priority . '</priority>' . "\n";
    echo '  </url>' . "\n";
}

echo '</urlset>';
?>
