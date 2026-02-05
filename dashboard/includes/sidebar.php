<?php
/**
 * Dashboard Sidebar Navigation
 * Responsive with mobile toggle
 */
global $DASHBOARD_MODULES;

// Get current path to highlight active menu
$current_path = $_SERVER['REQUEST_URI'];
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out mt-14 md:mt-0">
    <div class="flex flex-col h-full">
        <!-- Logo / Title -->
        <div class="hidden md:flex items-center px-6 py-4 border-b border-gray-200">
            <h1 class="text-xl font-bold text-gray-800">두손기획 관리</h1>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
            <ul class="space-y-1">
                <?php foreach ($DASHBOARD_MODULES as $key => $module): 
                    $is_active = strpos($current_path, $module['path']) === 0;
                    $active_class = $is_active ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-700 hover:bg-gray-50';
                ?>
                <li>
                    <a href="<?php echo $module['path']; ?>" 
                       class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $active_class; ?>">
                        <span class="text-xl mr-3"><?php echo $module['icon']; ?></span>
                        <span><?php echo $module['name']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <!-- User Info / Logout -->
        <div class="px-3 py-4 border-t border-gray-200">
            <div class="flex items-center px-4 py-2 text-sm text-gray-600">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></span>
            </div>
            <a href="/admin/mlangprintauto/logout.php" 
               class="flex items-center px-4 py-2 mt-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                로그아웃
            </a>
        </div>
    </div>
</aside>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden hidden"></div>

<script>
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }
</script>
