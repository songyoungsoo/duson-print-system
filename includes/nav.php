<?php
/**
 * 공통 네비게이션 파일
 * 경로: includes/nav.php
 * 스타일: /css/common-styles.css의 .product-nav 섹션
 */

$current_page = isset($current_page) ? $current_page : '';

$nav_default_mode = 'simple';
if (isset($db) && $db) {
    $nav_setting_q = mysqli_query($db, "SELECT setting_value FROM site_settings WHERE setting_key='nav_default_mode' LIMIT 1");
    if ($nav_setting_q && $row = mysqli_fetch_assoc($nav_setting_q)) {
        $nav_default_mode = $row['setting_value'];
    }
}
$nav_user_mode = isset($_COOKIE['nav_mode']) ? $_COOKIE['nav_mode'] : null;
$nav_active_mode = $nav_user_mode ? $nav_user_mode : $nav_default_mode;

// 전단지 용지 옵션 (칼라CMYK no=802 하위 용지)
$nav_leaflet_papers = [];
if (isset($db) && $db) {
    $nav_q = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE TreeNo='802' ORDER BY no ASC");
    if ($nav_q) {
        while ($r = mysqli_fetch_assoc($nav_q)) {
            $nav_leaflet_papers[] = $r;
        }
    }
}

// 6개 제품 메가 패널 데이터 일괄 조회 (봉투, 카다록, 포스터, 양식지, 상품권, 자석스티커)
$nav_mega_products = [
    'envelope'        => ['folder' => 'envelope',        'label' => '✉️ 봉투',  'ttable' => 'envelope'],
    'cadarok'         => ['folder' => 'cadarok',          'label' => '📖 카다록', 'ttable' => 'cadarok'],
    'littleprint'     => ['folder' => 'littleprint',      'label' => '🎨 포스터', 'ttable' => 'LittlePrint'],
    'ncrflambeau'     => ['folder' => 'ncrflambeau',      'label' => '📋 양식지', 'ttable' => 'NcrFlambeau'],
    'merchandisebond' => ['folder' => 'merchandisebond',  'label' => '🎫 상품권', 'ttable' => 'MerchandiseBond'],
    'msticker'        => ['folder' => 'msticker',         'label' => '자석스티커', 'ttable' => 'msticker'],
];
$nav_mega_data = [];
if (isset($db) && $db) {
    foreach ($nav_mega_products as $key => $info) {
        $ttable = mysqli_real_escape_string($db, $info['ttable']);
        $types = [];
        $tq = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='{$ttable}' AND BigNo='0' AND title != '1' ORDER BY no ASC");
        if ($tq) {
            while ($r = mysqli_fetch_assoc($tq)) {
                $subs = [];
                $sq = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='{$ttable}' AND BigNo='{$r['no']}' AND title != '1' ORDER BY no ASC");
                if ($sq) { while ($s = mysqli_fetch_assoc($sq)) { $subs[] = $s; } }
                $r['subs'] = $subs;
                $types[] = $r;
            }
        }
        $nav_mega_data[$key] = $types;
    }
}
?>
<!-- 네비게이션 메뉴 -->
<div class="cart-nav-wrapper">
    <div class="nav-mode-bar">
        <span class="nav-mode-guide" id="navModeGuide"><?php echo $nav_active_mode === 'detailed' ? '📋 재질/옵션을 알고 계시면 서브메뉴에서 바로 선택하세요' : '🔰 버튼을 클릭하면 제품 페이지로 바로 이동합니다'; ?></span>
        <button type="button" class="nav-mode-toggle" id="navModeToggle" onclick="toggleNavMode()">
            <span class="toggle-icon" id="navToggleIcon"><?php echo $nav_active_mode === 'detailed' ? '🔰' : '📋'; ?></span>
            <span id="navToggleLabel"><?php echo $nav_active_mode === 'detailed' ? '심플 메뉴' : '상세 메뉴'; ?></span>
        </button>
    </div>
    <div class="product-nav<?php echo $nav_active_mode === 'detailed' ? ' nav-detailed-mode' : ''; ?>" id="productNav">
        <?php
        $nav_sticker_groups = [
            '일반스티커' => [
                'jil 아트유광코팅' => '아트지유광',
                'jil 아트무광코팅' => '아트지무광',
                'jil 아트비코팅' => '아트지비코팅',
                'jil 모조비코팅' => '모조지비코팅',
            ],
            '강접스티커' => [
                'jka 강접아트유광코팅' => '강접아트유광',
                'cka 초강접아트코팅' => '초강접아트유광',
                'cka 초강접아트비코팅' => '초강접아트비코팅',
            ],
            '특수재질' => [
                'jsp 유포지' => '유포지',
                'jsp 은데드롱' => '은데드롱',
                'jsp 투명스티커' => '투명스티커',
                'jsp 크라프트지' => '크라프트스티커',
            ],
        ];
        ?>
        <div class="nav-btn-dropdown">
            <a href="/mlangprintauto/sticker_new/index.php" class="nav-btn <?php echo ($current_page == 'sticker') ? 'active' : ''; ?>">
               <span style="display:inline-block; transform:scaleX(0.95); transform-origin:center;">스티커/라벨 <span class="nav-arrow">▾</span></span>
            </a>
            <div class="nav-dropdown-menu nav-mega-panel">
                <?php foreach ($nav_sticker_groups as $group_name => $materials): ?>
                <div class="nav-mega-group">
                    <a href="/mlangprintauto/sticker_new/index.php" class="nav-mega-heading"><?php echo $group_name; ?></a>
                    <div class="nav-mega-items nav-mega-cols-2">
                        <?php foreach ($materials as $val => $label): ?>
                        <a href="/mlangprintauto/sticker_new/index.php?jong=<?php echo urlencode($val); ?>" class="nav-mega-item"><?php echo htmlspecialchars($label); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="tel:1688-2384" class="nav-mega-notice">
                    <span class="notice-title">롤스티커</span>
                    <span class="notice-list">
                        <span>· 금지스티커</span><span>· 금박스티커</span>
                        <span>· 홀로그램스티커</span><span>· 보안스티커</span>
                        <span>· 가맹점스티커</span><span>· 주차스티커</span>
                    </span>
                    <span class="notice-phone">☎ 1688-2384 전화문의</span>
                </a>
            </div>
        </div>

        <div class="nav-btn-dropdown">
            <a href="/mlangprintauto/inserted/index.php" class="nav-btn <?php echo ($current_page == 'leaflet') ? 'active' : ''; ?>">
               <span style="display:inline-block; transform:scaleX(0.95); transform-origin:center;">전단지/리플렛 <span class="nav-arrow">▾</span></span>
            </a>
            <?php if (!empty($nav_leaflet_papers)): ?>
            <div class="nav-dropdown-menu nav-mega-panel">
                <div class="nav-mega-group">
                    <a href="/mlangprintauto/inserted/index.php" class="nav-mega-heading">칼라(CMYK)</a>
                    <div class="nav-mega-items nav-mega-cols-2">
                        <?php foreach ($nav_leaflet_papers as $p):
                            $is_hapan = ($p['no'] == '626');
                            $display_title = $is_hapan ? '90g아트지(합판일반전단)' : htmlspecialchars(trim(preg_replace('/\(.*?\)/', '', $p['title'])));
                            $highlight_class = $is_hapan ? ' nav-mega-item-highlight' : '';
                        ?>
                        <a href="/mlangprintauto/inserted/index.php?type=<?php echo $p['no']; ?>" class="nav-mega-item<?php echo $highlight_class; ?>"><?php echo $display_title; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="nav-btn-dropdown">
            <a href="/mlangprintauto/namecard/index.php" class="nav-btn <?php echo ($current_page == 'namecard') ? 'active' : ''; ?>">
               명함/쿠폰 <span class="nav-arrow">▾</span>
            </a>
            <?php
            $nav_nc_types = [];
            if (isset($db) && $db) {
                $nq = mysqli_query($db, "SELECT t.no, t.title FROM mlangprintauto_transactioncate t WHERE t.Ttable='NameCard' AND t.BigNo='0' AND t.title != '1' ORDER BY no ASC");
                if ($nq) { while ($r = mysqli_fetch_assoc($nq)) {
                    $subs = [];
                    $sq = mysqli_query($db, "SELECT no, title FROM mlangprintauto_transactioncate WHERE Ttable='NameCard' AND BigNo='{$r['no']}' ORDER BY no ASC");
                    if ($sq) { while ($s = mysqli_fetch_assoc($sq)) { $subs[] = $s; } }
                    $r['subs'] = $subs;
                    $nav_nc_types[] = $r;
                } }
            }
            ?>
            <?php if (!empty($nav_nc_types)): ?>
            <div class="nav-dropdown-menu nav-mega-panel">
                <?php foreach ($nav_nc_types as $nctype): ?>
                <div class="nav-mega-group">
                    <a href="/mlangprintauto/namecard/index.php?type=<?php echo $nctype['no']; ?>" class="nav-mega-heading"><?php
                        echo htmlspecialchars(trim(preg_replace('/\(.*?\)/', '', $nctype['title'])));
                    ?></a>
                    <?php if (!empty($nctype['subs'])): ?>
                    <div class="nav-mega-items">
                        <?php foreach ($nctype['subs'] as $sub): ?>
                        <a href="/mlangprintauto/namecard/index.php?type=<?php echo $nctype['no']; ?>&section=<?php echo $sub['no']; ?>" class="nav-mega-item"><?php
                            echo htmlspecialchars(trim(preg_replace('/\(.*?\)/', '', $sub['title'])));
                        ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php
        // 자석스티커 서브메뉴 커스텀 라벨 (DB 타이틀 → 표시 라벨)
        $msticker_labels = [
            '742' => '종이자석스티커',
            '753' => '전체자석스티커',
        ];
        ?>
        <?php foreach ($nav_mega_products as $mega_key => $mega_info): ?>
        <?php $mega_types = isset($nav_mega_data[$mega_key]) ? $nav_mega_data[$mega_key] : []; ?>
        <div class="nav-btn-dropdown">
            <a href="/mlangprintauto/<?php echo $mega_info['folder']; ?>/index.php" class="nav-btn <?php echo ($current_page == $mega_key) ? 'active' : ''; ?>">
               <?php echo $mega_info['label']; ?> <span class="nav-arrow">▾</span>
            </a>
            <?php if (!empty($mega_types)): ?>
            <div class="nav-dropdown-menu nav-mega-panel">
                <?php foreach ($mega_types as $mtype): ?>
                <div class="nav-mega-group">
                    <a href="/mlangprintauto/<?php echo $mega_info['folder']; ?>/index.php?type=<?php echo $mtype['no']; ?>" class="nav-mega-heading"><?php
                        if ($mega_key === 'msticker' && isset($msticker_labels[$mtype['no']])) {
                            echo htmlspecialchars($msticker_labels[$mtype['no']]);
                        } else {
                            echo htmlspecialchars(trim(preg_replace('/\(.*?\)/', '', $mtype['title'])));
                        }
                    ?></a>
                    <?php if (!empty($mtype['subs'])): ?>
                    <div class="nav-mega-items">
                        <?php foreach ($mtype['subs'] as $msub): ?>
                        <a href="/mlangprintauto/<?php echo $mega_info['folder']; ?>/index.php?type=<?php echo $mtype['no']; ?>&section=<?php echo $msub['no']; ?>" class="nav-mega-item"><?php
                            echo ($mega_key === 'envelope') ? htmlspecialchars(trim($msub['title'])) : htmlspecialchars(trim(preg_replace('/\(.*?\)/', '', $msub['title'])));
                        ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
function toggleNavMode() {
    var nav = document.getElementById('productNav');
    var guide = document.getElementById('navModeGuide');
    var icon = document.getElementById('navToggleIcon');
    var label = document.getElementById('navToggleLabel');
    var isDetailed = nav.classList.toggle('nav-detailed-mode');

    if (isDetailed) {
        guide.textContent = '📋 재질/옵션을 알고 계시면 서브메뉴에서 바로 선택하세요';
        icon.textContent = '🔰';
        label.textContent = '심플 메뉴';
    } else {
        guide.textContent = '🔰 버튼을 클릭하면 제품 페이지로 바로 이동합니다';
        icon.textContent = '📋';
        label.textContent = '상세 메뉴';
    }

    var d = new Date();
    d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
    document.cookie = 'nav_mode=' + (isDetailed ? 'detailed' : 'simple') + ';expires=' + d.toUTCString() + ';path=/';
}
</script>
