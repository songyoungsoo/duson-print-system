<?php
global $DASHBOARD_NAV;

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_path = rtrim($current_path, '/') . '/';

// Group color themes
$group_colors = [
    'main'          => ['dot' => 'bg-blue-500',   'label' => 'text-blue-500',   'active_bg' => 'bg-blue-50',  'active_text' => 'text-blue-700',  'active_border' => 'border-blue-500'],
    'order_group'   => ['dot' => 'bg-orange-400', 'label' => 'text-orange-400', 'active_bg' => 'bg-orange-50','active_text' => 'text-orange-700','active_border' => 'border-orange-400'],
    'comm_group'    => ['dot' => 'bg-violet-400', 'label' => 'text-violet-400', 'active_bg' => 'bg-violet-50','active_text' => 'text-violet-700','active_border' => 'border-violet-400'],
    'product_group' => ['dot' => 'bg-emerald-400','label' => 'text-emerald-400','active_bg' => 'bg-emerald-50','active_text' => 'text-emerald-700','active_border' => 'border-emerald-400'],
    'admin_group'   => ['dot' => 'bg-slate-400',  'label' => 'text-slate-400',  'active_bg' => 'bg-slate-50', 'active_text' => 'text-slate-700', 'active_border' => 'border-slate-400'],
    'legacy_group'  => ['dot' => 'bg-gray-300',   'label' => 'text-gray-400',   'active_bg' => 'bg-gray-50',  'active_text' => 'text-gray-600',  'active_border' => 'border-gray-400'],
];
?>

<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out mt-14 md:mt-0">
    <div class="flex flex-col h-full">
        <div class="hidden md:flex items-center px-5 py-3 border-b border-gray-200">
            <h1 class="text-lg font-bold text-gray-800">&#x1F5A8;&#xFE0F; 두손기획</h1>
        </div>

        <nav class="flex-1 px-2 py-2 overflow-y-auto">
            <?php foreach ($DASHBOARD_NAV as $group_key => $group):
                $colors = $group_colors[$group_key] ?? $group_colors['main'];
            ?>
                <?php if (!empty($group['label'])): ?>
                    <div class="mt-3 mb-0.5 px-3 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $colors['dot']; ?>"></span>
                        <span class="text-[10px] font-bold <?php echo $colors['label']; ?> uppercase tracking-wider"><?php echo $group['label']; ?></span>
                    </div>
                <?php endif; ?>

                <ul>
                    <?php foreach ($group['items'] as $key => $module):
                        $item_path = rtrim(parse_url($module['path'], PHP_URL_PATH), '/') . '/';
                        $is_embed = !empty($module['embed']);
                        $is_active = $is_embed
                            ? ($current_path === '/dashboard/embed.php/' && isset($_GET['url']) && strpos($module['path'], urlencode($_GET['url'])) !== false)
                            : ($current_path === $item_path);
                        $is_external = !empty($module['external']);
                        $active_class = $is_active
                            ? $colors['active_bg'] . ' ' . $colors['active_text'] . ' font-medium border-l-2 ' . $colors['active_border']
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-2 border-transparent';
                    ?>
                    <li>
                        <a href="<?php echo $module['path']; ?>"
                           <?php if ($is_external): ?>target="_blank" rel="noopener"<?php endif; ?>
                           class="flex items-center pl-3 pr-2 py-[6px] rounded-r text-[13px] leading-tight transition-colors <?php echo $active_class; ?>">
                            <span class="text-sm mr-2 w-4 text-center flex-shrink-0"><?php echo $module['icon']; ?></span>
                            <span class="flex-1 truncate"><?php echo $module['name']; ?></span>
                            <?php if ($is_external): ?>
                                <svg class="w-3 h-3 text-gray-300 flex-shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </nav>

        <div class="px-2 py-2 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center px-3 py-1.5 text-[13px] text-gray-600">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px] font-bold mr-2 flex-shrink-0">
                    <?php echo mb_substr($_SESSION['admin_username'] ?? 'A', 0, 1); ?>
                </div>
                <span class="font-medium truncate"><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
            </div>
            <a href="/admin/mlangprintauto/logout.php"
               class="flex items-center px-3 py-1.5 text-[13px] text-red-500 hover:bg-red-50 rounded transition-colors">
                <svg class="w-3.5 h-3.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
