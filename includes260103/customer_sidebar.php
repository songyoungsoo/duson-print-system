<?php
/**
 * 고객센터 공통 사이드바
 * 모든 고객센터 페이지에서 사용
 */

// 현재 페이지 확인
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// 메뉴 아이템 정의
$menu_items = [
    [
        'id' => 'how_to_use',
        'title' => '홈페이지 이용방법',
        'url' => '/sub/customer/how_to_use.php',
        'icon' => '📖'
    ],
    [
        'id' => 'notice',
        'title' => '공지사항',
        'url' => '/sub/customer/notice.php',
        'icon' => '📢'
    ],
    [
        'id' => 'work_rules',
        'title' => '인쇄작업규약',
        'url' => '/sub/customer/work_rules.php',
        'icon' => '📋'
    ],
    [
        'id' => 'inquiry',
        'title' => '견적 및 제작관련문의',
        'url' => '/sub/customer/inquiry.php',
        'icon' => '✉️'
    ],
    [
        'id' => 'faq',
        'title' => '자주하는 질문',
        'url' => '/sub/customer/faq.php',
        'icon' => '❓'
    ],
    [
        'id' => 'payment_info',
        'title' => '입금계좌안내',
        'url' => '/sub/customer/payment_info.php',
        'icon' => '💳'
    ],
    [
        'id' => 'same_day',
        'title' => '당일판',
        'url' => '/sub/customer/same_day.php',
        'icon' => '⚡'
    ],
    [
        'id' => 'shipping_info',
        'title' => '배송비안내',
        'url' => '/sub/customer/shipping_info.php',
        'icon' => '🚚'
    ],
    [
        'id' => 'work_guide',
        'title' => '작업가이드',
        'url' => '/sub/customer/work_guide.php',
        'icon' => '🎨'
    ]
];
?>

<aside class="customer-sidebar">
    <div class="sidebar-header">
        <h2>고객센터</h2>
        <p>두손기획인쇄를 이용해주셔서 감사합니다</p>
    </div>

    <nav class="sidebar-menu">
        <ul class="menu-list">
            <?php foreach ($menu_items as $item): ?>
                <li class="menu-item <?php echo ($current_page === $item['id']) ? 'active' : ''; ?>">
                    <a href="<?php echo htmlspecialchars($item['url']); ?>" class="menu-link">
                        <span class="menu-icon"><?php echo $item['icon']; ?></span>
                        <span class="menu-text"><?php echo htmlspecialchars($item['title']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar-contact">
        <h3>📞 고객센터</h3>
        <div class="contact-item">
            <strong>전화:</strong> 1688-2384 / 02-2632-1830
        </div>
        <div class="contact-item">
            <strong>영업시간:</strong><br>
            평일 09:00 ~ 18:00<br>
            토요일 09:00 ~ 13:00
        </div>
    </div>
</aside>
