<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$settings = [];
$q = mysqli_query($db, "SELECT setting_key, setting_value FROM site_settings");
if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
}
$nav_mode = $settings['nav_default_mode'] ?? 'simple';
?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <h1 class="text-lg font-bold text-gray-900 mb-4">사이트 설정</h1>

        <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
            <!-- 네비게이션 모드 -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">네비게이션 기본 모드</h3>
                        <p class="text-xs text-gray-500 mt-1">첫 방문자가 보는 네비게이션의 기본 상태를 설정합니다.</p>
                        <p class="text-xs text-gray-400 mt-0.5">방문자가 직접 토글하면 본인 설정(쿠키)이 우선됩니다.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="navModeLabel" class="text-xs font-medium <?php echo $nav_mode === 'detailed' ? 'text-blue-600' : 'text-gray-500'; ?>">
                            <?php echo $nav_mode === 'detailed' ? '상세 메뉴' : '심플 메뉴'; ?>
                        </span>
                        <button type="button" id="navModeSwitch" onclick="toggleNavSetting()"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $nav_mode === 'detailed' ? 'bg-blue-600' : 'bg-gray-300'; ?>">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 <?php echo $nav_mode === 'detailed' ? 'translate-x-6' : 'translate-x-1'; ?>"></span>
                        </button>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <div class="flex-1 rounded-md border p-3 text-center <?php echo $nav_mode === 'simple' ? 'border-green-300 bg-green-50' : 'border-gray-200'; ?>">
                        <div class="text-lg">🔰</div>
                        <div class="text-xs font-medium mt-1">심플</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">버튼만 표시<br>클릭 시 페이지 이동</div>
                    </div>
                    <div class="flex-1 rounded-md border p-3 text-center <?php echo $nav_mode === 'detailed' ? 'border-blue-300 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="text-lg">📋</div>
                        <div class="text-xs font-medium mt-1">상세</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">hover 시 서브메뉴<br>재질/옵션 바로 선택</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function toggleNavSetting() {
    var sw = document.getElementById('navModeSwitch');
    var label = document.getElementById('navModeLabel');
    var isDetailed = sw.classList.contains('bg-blue-600');
    var newMode = isDetailed ? 'simple' : 'detailed';

    fetch('/dashboard/api/settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', key: 'nav_default_mode', value: newMode})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (newMode === 'detailed') {
                sw.classList.remove('bg-gray-300');
                sw.classList.add('bg-blue-600');
                sw.querySelector('span').classList.remove('translate-x-1');
                sw.querySelector('span').classList.add('translate-x-6');
                label.textContent = '상세 메뉴';
                label.classList.remove('text-gray-500');
                label.classList.add('text-blue-600');
            } else {
                sw.classList.remove('bg-blue-600');
                sw.classList.add('bg-gray-300');
                sw.querySelector('span').classList.remove('translate-x-6');
                sw.querySelector('span').classList.add('translate-x-1');
                label.textContent = '심플 메뉴';
                label.classList.remove('text-blue-600');
                label.classList.add('text-gray-500');
            }
            // 카드 강조 업데이트
            var cards = document.querySelectorAll('.rounded-md.border.p-3');
            cards[0].className = 'flex-1 rounded-md border p-3 text-center ' + (newMode === 'simple' ? 'border-green-300 bg-green-50' : 'border-gray-200');
            cards[1].className = 'flex-1 rounded-md border p-3 text-center ' + (newMode === 'detailed' ? 'border-blue-300 bg-blue-50' : 'border-gray-200');
            showToast('네비게이션 기본 모드가 변경되었습니다.', 'success');
        } else {
            showToast('설정 변경에 실패했습니다.', 'error');
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
