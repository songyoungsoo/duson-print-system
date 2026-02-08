<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

$url = $_GET['url'] ?? '';
if (empty($url)) {
    header('Location: /dashboard/');
    exit;
}

$allowed_prefixes = ['/admin/mlangprintauto/', '/shop_admin/', '/sub/'];
$is_allowed = false;
foreach ($allowed_prefixes as $prefix) {
    if (strpos($url, $prefix) === 0) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    header('Location: /dashboard/');
    exit;
}

$page_title = '임베드';
$encoded_url = urlencode($url);
$best_match_len = 0;
foreach ($DASHBOARD_NAV as $group) {
    foreach ($group['items'] as $item) {
        if (!isset($item['embed'])) continue;
        $param = [];
        parse_str(parse_url($item['path'], PHP_URL_QUERY) ?? '', $param);
        $item_url = $param['url'] ?? '';
        if ($item_url === $url) {
            $page_title = $item['name'];
            break 2;
        }
    }
}

$embed_token = hash_hmac('sha256', $url . date('Y-m-d'), 'duson_embed_2026_secret');
$iframe_url = $url . (strpos($url, '?') !== false ? '&' : '?') . '_eauth=' . $embed_token;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 flex flex-col overflow-hidden bg-gray-50">
    <div class="flex items-center justify-between px-4 py-2 bg-white border-b border-gray-200">
        <h1 class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="text-xs text-gray-400 hover:text-blue-600 flex items-center gap-1">
            새 탭에서 열기
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>
    </div>
    <iframe src="<?php echo htmlspecialchars($iframe_url); ?>" class="flex-1 w-full border-0" style="min-height: calc(100vh - 120px);"></iframe>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
