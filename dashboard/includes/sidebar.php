<?php
global $DASHBOARD_NAV;

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_path = rtrim($current_path, '/') . '/';

// Group color themes - pastel backgrounds + active states + hover/label borders
$group_colors = [
    'main'          => ['bg' => 'bg-sky-50/70',      'label' => 'text-sky-500',     'label_border' => 'border-sky-200',   'active_bg' => 'bg-sky-100',     'active_text' => 'text-sky-700',     'active_border' => 'border-sky-400',   'hover_bg' => 'hover:bg-sky-100/60'],
    'order_group'   => ['bg' => 'bg-amber-50/70',    'label' => 'text-amber-500',   'label_border' => 'border-amber-200', 'active_bg' => 'bg-amber-100',   'active_text' => 'text-amber-700',   'active_border' => 'border-amber-400', 'hover_bg' => 'hover:bg-amber-100/60'],
    'comm_group'    => ['bg' => 'bg-violet-50/70',    'label' => 'text-violet-500',  'label_border' => 'border-violet-200','active_bg' => 'bg-violet-100',  'active_text' => 'text-violet-700',  'active_border' => 'border-violet-400','hover_bg' => 'hover:bg-violet-100/60'],
    'product_group' => ['bg' => 'bg-emerald-50/70',   'label' => 'text-emerald-500', 'label_border' => 'border-emerald-200','active_bg' => 'bg-emerald-100', 'active_text' => 'text-emerald-700', 'active_border' => 'border-emerald-400','hover_bg' => 'hover:bg-emerald-100/60'],
    'admin_group'   => ['bg' => 'bg-slate-50/70',     'label' => 'text-slate-500',   'label_border' => 'border-slate-200', 'active_bg' => 'bg-slate-100',   'active_text' => 'text-slate-700',   'active_border' => 'border-slate-400', 'hover_bg' => 'hover:bg-slate-100/60'],
    'legacy_group'  => ['bg' => 'bg-stone-50/70',     'label' => 'text-stone-400',   'label_border' => 'border-stone-200', 'active_bg' => 'bg-stone-100',   'active_text' => 'text-stone-600',   'active_border' => 'border-stone-400', 'hover_bg' => 'hover:bg-stone-100/60'],
];
?>

<style>
    /* Custom scrollbar for sidebar */
    #sidebar nav::-webkit-scrollbar { width: 3px; }
    #sidebar nav::-webkit-scrollbar-track { background: transparent; }
    #sidebar nav::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    #sidebar nav::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-[200px] bg-white/95 backdrop-blur-sm border-r border-gray-200 shadow-sm transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out mt-14 md:mt-0">
    <div class="flex flex-col h-full">
        <!-- Brand Header -->
        <div class="hidden md:flex items-center gap-2.5 px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-slate-800 to-slate-700">
            <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                <span class="text-base">&#x1F5A8;&#xFE0F;</span>
            </div>
            <div class="min-w-0">
                <h1 class="text-[13px] font-bold text-white leading-tight">두손기획</h1>
                <p class="text-[10px] text-slate-300 leading-tight">Print Management</p>
            </div>
        </div>

        <nav class="flex-1 py-1.5 overflow-y-auto">
            <?php foreach ($DASHBOARD_NAV as $group_key => $group):
                $colors = $group_colors[$group_key] ?? $group_colors['main'];
            ?>
                <div class="<?php echo $colors['bg']; ?> mx-1.5 mb-1 rounded-lg">
                    <?php if (!empty($group['label'])): ?>
                        <div class="pt-2 pb-1 px-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold <?php echo $colors['label']; ?> uppercase tracking-widest"><?php echo $group['label']; ?></span>
                                <span class="flex-1 border-t <?php echo $colors['label_border']; ?>"></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pt-1.5"></div>
                    <?php endif; ?>

                    <ul class="pb-1 px-1">
                        <?php foreach ($group['items'] as $key => $module):
                            $item_path = rtrim(parse_url($module['path'], PHP_URL_PATH), '/') . '/';
                            $is_embed = !empty($module['embed']);
                            $is_active = $is_embed
                                ? ($current_path === '/dashboard/embed.php/' && isset($_GET['url']) && strpos($module['path'], urlencode($_GET['url'])) !== false)
                                : ($current_path === $item_path);
                            $is_external = !empty($module['external']);
                            $active_class = $is_active
                                ? $colors['active_bg'] . ' ' . $colors['active_text'] . ' font-semibold border-l-[3px] ' . $colors['active_border'] . ' shadow-sm'
                                : 'text-gray-600 ' . $colors['hover_bg'] . ' hover:text-gray-900 hover:translate-x-0.5 border-l-[3px] border-transparent';
                        ?>
                        <li>
                            <a href="<?php echo $module['path']; ?>"
                               <?php if ($is_external): ?>target="_blank" rel="noopener"<?php endif; ?>
                               class="group/item flex items-center pl-2.5 pr-2 py-[6px] rounded-lg text-[13px] leading-tight transition-all duration-150 <?php echo $active_class; ?>">
                                <span class="text-sm mr-1.5 w-5 text-center flex-shrink-0 transition-transform duration-150 group-hover/item:scale-110"><?php echo $module['icon']; ?></span>
                                <span class="truncate"><?php echo $module['name']; ?></span>
                                <?php if ($is_external): ?>
                                    <svg class="w-2.5 h-2.5 text-gray-300 flex-shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- User Section -->
        <div class="px-2 py-2 border-t border-gray-200 bg-gray-50/80">
            <div class="flex items-center px-2 py-1.5 text-[11px] text-gray-600">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold mr-2 flex-shrink-0">
                    <?php echo mb_substr($_SESSION['admin_username'] ?? 'A', 0, 1); ?>
                </div>
                <div class="min-w-0">
                    <span class="block font-semibold text-gray-800 text-[12px] truncate"><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
                    <span class="block text-[10px] text-gray-400 leading-tight">관리자</span>
                </div>
            </div>
            <a href="/admin/mlangprintauto/logout.php"
               class="flex items-center justify-center mx-1 mt-1 px-2 py-1.5 text-[11px] text-red-500 border border-red-200 hover:bg-red-50 hover:border-red-300 rounded-lg transition-colors">
                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
