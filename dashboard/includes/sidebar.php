<?php
global $DASHBOARD_NAV;

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_path = rtrim($current_path, '/') . '/';
?>

<style>
/* === Shop-admin style sidebar === */
#sidebar {
    background: #152238;
    font-family: 'Noto Sans KR', 'Malgun Gothic', sans-serif;
}

#sidebar nav::-webkit-scrollbar { width: 4px; }
#sidebar nav::-webkit-scrollbar-track { background: transparent; }
#sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

/* Brand */
.sa-brand {
    padding: 8px 10px;
    background: linear-gradient(135deg, #0f1a2e 0%, #1a2d4a 100%);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.sa-brand-logo {
    width: 28px; height: 28px;
    border-radius: 6px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.sa-brand h1 { font-size: 13px; font-weight: 700; color: #fff; }
.sa-brand p { font-size: 9px; color: rgba(255,255,255,0.45); letter-spacing: 0.5px; }

/* Menu section card */
.sa-section {
    background: #fff;
    border-radius: 6px;
    margin: 4px 5px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
    overflow: hidden;
}

/* Section header - brand navy (#1E4E79 from top-header) */
.sa-header {
    background: #1E4E79;
    color: #fff;
    padding: 4px 10px;
    font-size: 10.5px;
    font-weight: 700;
    text-align: center;
    letter-spacing: 1px;
}

/* Menu items */
.sa-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.sa-list li {
    border-bottom: 1px solid #f0f0f0;
}
.sa-list li:last-child {
    border-bottom: none;
}
.sa-item {
    display: flex;
    align-items: center;
    padding: 5px 8px;
    color: #374151;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.15s ease;
}
.sa-item:hover {
    background: #e8f0fe;
    color: #1a3a6a;
    padding-left: 12px;
}
.sa-item.active {
    background: #dbeafe;
    color: #1a3a6a;
    font-weight: 600;
    border-left: 3px solid #1E4E79;
    padding-left: 5px;
}
.sa-item .sa-icon {
    width: 16px;
    text-align: center;
    margin-right: 5px;
    font-size: 11px;
    flex-shrink: 0;
}
.sa-item .sa-arrow {
    color: #1E4E79;
    font-weight: 700;
    margin-right: 4px;
    font-size: 10px;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.15s;
}
.sa-item:hover .sa-arrow,
.sa-item.active .sa-arrow {
    opacity: 1;
}
.sa-item .sa-ext {
    margin-left: auto;
    opacity: 0.3;
    flex-shrink: 0;
}

/* User section */
.sa-user {
    padding: 6px 6px;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.15);
}
.sa-avatar {
    width: 24px; height: 24px;
    border-radius: 5px;
    background: linear-gradient(135deg, #3b5998, #2b4080);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0;
}
.sa-logout {
    display: flex; align-items: center; justify-content: center;
    margin-top: 4px; padding: 4px;
    border-radius: 5px; font-size: 10px;
    color: rgba(255,180,180,0.8);
    border: 1px solid rgba(255,180,180,0.15);
    background: rgba(255,100,100,0.05);
    text-decoration: none;
    transition: all 0.15s;
}
.sa-logout:hover {
    color: #ffa0a0;
    background: rgba(255,100,100,0.1);
    border-color: rgba(255,180,180,0.3);
}
</style>

<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-[200px] transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out mt-11 md:mt-0 overflow-y-auto">
    <div class="flex flex-col h-full">
        <!-- Brand (상단 헤더로 이동됨, 사이드바에서는 숨김) -->

        <nav class="flex-1 overflow-y-auto" style="padding: 2px 0 2px;">
            <?php foreach ($DASHBOARD_NAV as $group_key => $group): ?>
                <div class="sa-section">
                    <?php if (!empty($group['label'])): ?>
                        <div class="sa-header"><?php echo $group['label']; ?></div>
                    <?php else: ?>
                        <div class="sa-header">대시보드</div>
                    <?php endif; ?>

                    <ul class="sa-list">
                        <?php foreach ($group['items'] as $key => $module):
                            $item_path = rtrim(parse_url($module['path'], PHP_URL_PATH), '/') . '/';
                            $is_embed = !empty($module['embed']);
                            $is_active = $is_embed
                                ? ($current_path === '/dashboard/embed.php/' && isset($_GET['url']) && strpos($module['path'], urlencode($_GET['url'])) !== false)
                                : ($current_path === $item_path);
                            $is_external = !empty($module['external']);
                        ?>
                        <li>
                            <a href="<?php echo $module['path']; ?>"
                               <?php if ($is_external): ?>target="_blank" rel="noopener"<?php endif; ?>
                               class="sa-item<?php echo $is_active ? ' active' : ''; ?>">
                                <span class="sa-arrow">›</span>
                                <span class="sa-icon"><?php echo $module['icon']; ?></span>
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo $module['name']; ?></span>
                                <?php if ($is_external): ?>
                                    <svg class="sa-ext" style="width:10px; height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- User -->
        <div class="sa-user">
            <div style="display:flex; align-items:center; gap:8px; padding:2px 4px;">
                <div class="sa-avatar"><?php echo mb_substr($_SESSION['admin_username'] ?? 'A', 0, 1); ?></div>
                <div style="min-width:0; flex:1;">
                    <div style="font-size:12px; font-weight:600; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>
                    </div>
                    <span style="font-size:10px; color:rgba(255,255,255,0.4);">관리자</span>
                </div>
            </div>
            <a href="/auth/logout.php?redirect=/dashboard/" class="sa-logout">
                <svg style="width:12px; height:12px; margin-right:4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                로그아웃
            </a>
        </div>
    </div>
</aside>

<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden hidden"></div>

<script>
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }

    if (mobileMenuToggle) mobileMenuToggle.addEventListener('click', toggleSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
</script>
