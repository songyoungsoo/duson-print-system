<?php
global $DASHBOARD_NAV;

$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_path = rtrim($current_path, '/') . '/';
?>

<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out mt-14 md:mt-0">
    <div class="flex flex-col h-full">
        <div class="hidden md:flex items-center px-6 py-4 border-b border-gray-200">
            <h1 class="text-xl font-bold text-gray-800">🖨️ 두손기획</h1>
        </div>
        
        <nav class="flex-1 px-3 py-3 overflow-y-auto">
            <?php foreach ($DASHBOARD_NAV as $group_key => $group): ?>
                <?php if (!empty($group['label'])): ?>
                    <div class="mt-4 mb-1 px-4">
                        <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider"><?php echo $group['label']; ?></span>
                    </div>
                <?php endif; ?>
                
                <ul class="space-y-0.5">
                    <?php foreach ($group['items'] as $key => $module):
                        $item_path = rtrim(parse_url($module['path'], PHP_URL_PATH), '/') . '/';
                        $is_active = ($current_path === $item_path);
                        $is_external = !empty($module['external']);
                        $active_class = $is_active 
                            ? 'bg-blue-50 text-blue-700 font-medium border-l-[3px] border-blue-600' 
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                    ?>
                    <li>
                        <a href="<?php echo $module['path']; ?>" 
                           <?php if ($is_external): ?>target="_blank" rel="noopener"<?php endif; ?>
                           class="flex items-center px-4 py-2.5 rounded-r-lg text-sm transition-colors <?php echo $active_class; ?>">
                            <span class="text-base mr-3 w-5 text-center"><?php echo $module['icon']; ?></span>
                            <span class="flex-1"><?php echo $module['name']; ?></span>
                            <?php if ($is_external): ?>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </nav>
        
        <div class="px-3 py-3 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center px-4 py-2 text-sm text-gray-600">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold mr-2">
                    <?php echo mb_substr($_SESSION['admin_username'] ?? 'A', 0, 1); ?>
                </div>
                <span class="font-medium"><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
            </div>
            <a href="/admin/mlangprintauto/logout.php" 
               class="flex items-center px-4 py-2 mt-1 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
