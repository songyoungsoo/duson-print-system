# SEO 최적화

## 메타태그 설정

### 기본 템플릿 (inc/meta.php)
```php
<?php
function getMetaTags($page_type = 'default', $data = []) {
    $site_name = '두손기획인쇄';
    $base_url = 'https://dsp1830.shop';
    
    $defaults = [
        'title' => '두손기획인쇄 - 스티커, 전단지, 명함 인쇄 전문',
        'description' => '고품질 인쇄 서비스. 스티커, 전단지, 명함, 봉투, 카달로그 등 다양한 인쇄물을 합리적인 가격에 제작해드립니다.',
        'keywords' => '인쇄, 스티커인쇄, 전단지인쇄, 명함인쇄, 봉투인쇄, 카달로그인쇄',
        'image' => $base_url . '/images/og-image.jpg',
        'url' => $base_url . $_SERVER['REQUEST_URI'],
    ];
    
    // 페이지별 메타 정보
    switch ($page_type) {
        case 'product':
            $meta = [
                'title' => $data['product_name'] . ' - ' . $site_name,
                'description' => $data['product_name'] . ' 인쇄 서비스. ' . ($data['description'] ?? $defaults['description']),
                'keywords' => $data['product_name'] . ', ' . $data['product_name'] . '인쇄, ' . $defaults['keywords'],
            ];
            break;
            
        case 'notice':
            $meta = [
                'title' => $data['title'] . ' - 공지사항 - ' . $site_name,
                'description' => mb_substr(strip_tags($data['content']), 0, 150),
            ];
            break;
            
        case 'faq':
            $meta = [
                'title' => '자주 묻는 질문 (FAQ) - ' . $site_name,
                'description' => '인쇄 주문, 배송, 결제 관련 자주 묻는 질문과 답변입니다.',
            ];
            break;
            
        default:
            $meta = [];
    }
    
    return array_merge($defaults, $meta, $data);
}
?>
```

### 메타태그 출력 (inc/header.php)
```php
<?php
$meta = getMetaTags($page_type ?? 'default', $page_data ?? []);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 기본 메타태그 -->
    <title><?= h($meta['title']) ?></title>
    <meta name="description" content="<?= h($meta['description']) ?>">
    <meta name="keywords" content="<?= h($meta['keywords']) ?>">
    
    <!-- Open Graph (Facebook, KakaoTalk) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= h($meta['title']) ?>">
    <meta property="og:description" content="<?= h($meta['description']) ?>">
    <meta property="og:image" content="<?= h($meta['image']) ?>">
    <meta property="og:url" content="<?= h($meta['url']) ?>">
    <meta property="og:site_name" content="두손기획인쇄">
    <meta property="og:locale" content="ko_KR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= h($meta['title']) ?>">
    <meta name="twitter:description" content="<?= h($meta['description']) ?>">
    <meta name="twitter:image" content="<?= h($meta['image']) ?>">
    
    <!-- 검색엔진 설정 -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="naver-site-verification" content="your-naver-code">
    <meta name="google-site-verification" content="your-google-code">
    
    <!-- Canonical URL (중복 콘텐츠 방지) -->
    <link rel="canonical" href="<?= h($meta['url']) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/css/style.css?v=<?= filemtime('css/style.css') ?>">
</head>
```

## 구조화된 데이터 (Schema.org)

### 조직 정보 (모든 페이지)
```html
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "두손기획인쇄",
    "url": "https://dsp1830.shop",
    "logo": "https://dsp1830.shop/images/logo.png",
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+82-2-0000-0000",
        "contactType": "customer service",
        "availableLanguage": "Korean"
    },
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "KR",
        "addressRegion": "서울",
        "addressLocality": "OO구"
    },
    "sameAs": [
        "https://www.facebook.com/dsp1830",
        "https://www.instagram.com/dsp1830"
    ]
}
</script>
```

### 제품 페이지
```php
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "<?= h($product['name']) ?>",
    "description": "<?= h($product['description']) ?>",
    "image": "<?= h($product['image']) ?>",
    "brand": {
        "@type": "Brand",
        "name": "두손기획인쇄"
    },
    "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "KRW",
        "lowPrice": "<?= $product['min_price'] ?>",
        "highPrice": "<?= $product['max_price'] ?>",
        "availability": "https://schema.org/InStock"
    }
}
</script>
```

### FAQ 페이지
```php
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        <?php foreach ($faqs as $i => $faq): ?>
        <?= $i > 0 ? ',' : '' ?>
        {
            "@type": "Question",
            "name": "<?= h($faq['question']) ?>",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "<?= h($faq['answer']) ?>"
            }
        }
        <?php endforeach; ?>
    ]
}
</script>
```

### 빵부스러기 (Breadcrumb)
```php
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "홈",
            "item": "https://dsp1830.shop"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "<?= h($category_name) ?>",
            "item": "https://dsp1830.shop/sub/<?= $category_slug ?>.php"
        },
        {
            "@type": "ListItem",
            "position": 3,
            "name": "<?= h($product_name) ?>"
        }
    ]
}
</script>
```

## Sitemap.xml

```php
<?php
// sitemap.php
header('Content-Type: application/xml; charset=utf-8');

$base_url = 'https://dsp1830.shop';

// 정적 페이지
$static_pages = [
    ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/sub/sticker_new.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/sub/inserted.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/sub/namecard.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/sub/envelope.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/sub/cadarok.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/sub/littleprint.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => '/faq/', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/notice/', 'priority' => '0.6', 'changefreq' => 'weekly'],
];

// 공지사항 (동적)
$notices = $pdo->query("SELECT idx, updated_at FROM notices WHERE status = 'active' ORDER BY created_at DESC LIMIT 100")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($static_pages as $page): ?>
    <url>
        <loc><?= $base_url . $page['url'] ?></loc>
        <changefreq><?= $page['changefreq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
    <?php endforeach; ?>
    
    <?php foreach ($notices as $notice): ?>
    <url>
        <loc><?= $base_url ?>/notice/view.php?idx=<?= $notice['idx'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($notice['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <?php endforeach; ?>
</urlset>
```

## robots.txt

```
# robots.txt
User-agent: *
Allow: /

# 크롤링 제외
Disallow: /admin/
Disallow: /member/
Disallow: /mlangprintauto/shop/
Disallow: /api/
Disallow: /inc/
Disallow: /uploads/print_files/

# Sitemap
Sitemap: https://dsp1830.shop/sitemap.xml
```

## URL 최적화

### .htaccess (Clean URL)
```apache
RewriteEngine On
RewriteBase /

# 제품 페이지 SEO URL
# /print/sticker → /sub/sticker_new.php
RewriteRule ^print/sticker/?$ /sub/sticker_new.php [L]
RewriteRule ^print/flyer/?$ /sub/inserted.php [L]
RewriteRule ^print/namecard/?$ /sub/namecard.php [L]

# 공지사항
# /notice/123 → /notice/view.php?idx=123
RewriteRule ^notice/([0-9]+)/?$ /notice/view.php?idx=$1 [L]

# FAQ
# /faq/order → /faq/?cat=1
RewriteRule ^faq/([a-z]+)/?$ /faq/index.php?category=$1 [L]

# HTTPS 강제
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# www 제거
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
```

## 페이지 속도 최적화

### 이미지 최적화
```php
// 이미지 lazy loading
<img src="placeholder.jpg" data-src="actual-image.jpg" class="lazy" alt="설명">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lazyImages = document.querySelectorAll('img.lazy');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => observer.observe(img));
});
</script>
```

### CSS/JS 압축 (htaccess)
```apache
# Gzip 압축
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json
</IfModule>

# 브라우저 캐싱
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## 검색엔진 등록

### 네이버 웹마스터 도구
1. https://searchadvisor.naver.com 접속
2. 사이트 추가 및 소유권 확인
3. sitemap.xml 제출
4. robots.txt 검증

### 구글 Search Console
1. https://search.google.com/search-console 접속
2. 속성 추가
3. sitemap.xml 제출
4. 색인 요청

### 다음 검색등록
1. https://register.search.daum.net 접속
2. 사이트 등록
