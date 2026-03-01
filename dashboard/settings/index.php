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
$qw_enabled = ($settings['quote_widget_enabled'] ?? '1') === '1';
$qw_right = intval($settings['quote_widget_right'] ?? 20);
$qw_top = intval($settings['quote_widget_top'] ?? 50);
$en_enabled = ($settings['en_version_enabled'] ?? '0') === '1';
?>

<main class="flex-1 bg-gray-50 overflow-y-auto">
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

            <!-- 영문 버전 표시 -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">영문 버전 (EN)</h3>
                        <p class="text-xs text-gray-500 mt-1">홈페이지 상단 헤더에 EN 버튼을 표시하여 영문 페이지로 이동할 수 있게 합니다.</p>
                        <p class="text-xs text-gray-400 mt-0.5">비활성화 시 EN 버튼이 헤더에서 숨겨집니다.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="enLabel" class="text-xs font-medium <?php echo $en_enabled ? 'text-blue-600' : 'text-gray-500'; ?>">
                            <?php echo $en_enabled ? '표시' : '숨김'; ?>
                        </span>
                        <button type="button" id="enSwitch" onclick="toggleEnVersion()"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $en_enabled ? 'bg-blue-600' : 'bg-gray-300'; ?>">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 <?php echo $en_enabled ? 'translate-x-6' : 'translate-x-1'; ?>"></span>
                        </button>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <div class="flex-1 rounded-md border p-3 text-center <?php echo !$en_enabled ? 'border-gray-300 bg-gray-50' : 'border-gray-200'; ?>">
                        <div class="text-lg">🇰🇷</div>
                        <div class="text-xs font-medium mt-1">한국어만</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">EN 버튼 숨김<br>국내 고객 전용</div>
                    </div>
                    <div class="flex-1 rounded-md border p-3 text-center <?php echo $en_enabled ? 'border-blue-300 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="text-lg">🌐</div>
                        <div class="text-xs font-medium mt-1">한국어 + 영어</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">EN 버튼 표시<br>해외 고객 접근 가능</div>
                    </div>
                </div>
            </div>

            <!-- 견적 위젯 설정 -->
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">실시간 견적 위젯</h3>
                        <p class="text-xs text-gray-500 mt-1">제품 페이지 우측에 표시되는 플로팅 견적 위젯의 위치와 표시 여부를 설정합니다.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="qwEnabledLabel" class="text-xs font-medium <?php echo $qw_enabled ? 'text-blue-600' : 'text-gray-500'; ?>">
                            <?php echo $qw_enabled ? '표시' : '숨김'; ?>
                        </span>
                        <button type="button" id="qwEnabledSwitch" onclick="toggleQuoteWidget()"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 <?php echo $qw_enabled ? 'bg-blue-600' : 'bg-gray-300'; ?>">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 <?php echo $qw_enabled ? 'translate-x-6' : 'translate-x-1'; ?>"></span>
                        </button>
                    </div>
                </div>

                <div id="qwPositionControls" class="<?php echo $qw_enabled ? '' : 'opacity-50 pointer-events-none'; ?>">
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <!-- Right (px) -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">우측 여백 (right)</label>
                            <div class="flex items-center gap-2">
                                <input type="range" id="qwRight" min="0" max="200" value="<?php echo $qw_right; ?>"
                                    class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                    oninput="document.getElementById('qwRightVal').textContent=this.value">
                                <span id="qwRightVal" class="text-xs font-mono text-gray-700 w-8 text-right"><?php echo $qw_right; ?></span>
                                <span class="text-xs text-gray-400">px</span>
                            </div>
                        </div>
                        <!-- Top (%) -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">상단 위치 (top)</label>
                            <div class="flex items-center gap-2">
                                <input type="range" id="qwTop" min="10" max="90" value="<?php echo $qw_top; ?>"
                                    class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                                    oninput="document.getElementById('qwTopVal').textContent=this.value">
                                <span id="qwTopVal" class="text-xs font-mono text-gray-700 w-8 text-right"><?php echo $qw_top; ?></span>
                                <span class="text-xs text-gray-400">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" onclick="saveQuoteWidgetPosition()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors">
                            위치 저장
                        </button>
                    </div>
                </div>

                <!-- 미리보기 -->
                <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-xs text-gray-400 mb-2">미리보기</div>
                    <div class="relative bg-white rounded border border-gray-200 h-40 overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center text-xs text-gray-300">제품 페이지</div>
                        <div id="qwPreview" class="absolute bg-blue-500 text-white text-[9px] rounded px-1.5 py-3 leading-tight text-center shadow-md"
                             style="right:<?php echo min($qw_right / 5, 40); ?>px; top:<?php echo $qw_top; ?>%; transform:translateY(-50%);">
                            실시간<br>견적
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function saveWidgetSetting(key, value, callback) {
    fetch('/dashboard/api/settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', key: key, value: String(value)})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && callback) callback(data);
        else if (!data.success) showToast(data.message || '저장 실패', 'error');
    });
}

function toggleQuoteWidget() {
    var sw = document.getElementById('qwEnabledSwitch');
    var label = document.getElementById('qwEnabledLabel');
    var controls = document.getElementById('qwPositionControls');
    var isOn = sw.classList.contains('bg-blue-600');
    var newVal = isOn ? '0' : '1';

    saveWidgetSetting('quote_widget_enabled', newVal, function() {
        if (newVal === '1') {
            sw.classList.remove('bg-gray-300'); sw.classList.add('bg-blue-600');
            sw.querySelector('span').classList.remove('translate-x-1'); sw.querySelector('span').classList.add('translate-x-6');
            label.textContent = '표시'; label.classList.remove('text-gray-500'); label.classList.add('text-blue-600');
            controls.classList.remove('opacity-50', 'pointer-events-none');
        } else {
            sw.classList.remove('bg-blue-600'); sw.classList.add('bg-gray-300');
            sw.querySelector('span').classList.remove('translate-x-6'); sw.querySelector('span').classList.add('translate-x-1');
            label.textContent = '숨김'; label.classList.remove('text-blue-600'); label.classList.add('text-gray-500');
            controls.classList.add('opacity-50', 'pointer-events-none');
        }
        showToast('견적 위젯 ' + (newVal === '1' ? '표시' : '숨김') + '으로 변경되었습니다.', 'success');
    });
}

function saveQuoteWidgetPosition() {
    var rightVal = document.getElementById('qwRight').value;
    var topVal = document.getElementById('qwTop').value;
    var saved = 0;
    var total = 2;

    function checkDone() {
        saved++;
        if (saved >= total) {
            showToast('위젯 위치가 저장되었습니다. (right: ' + rightVal + 'px, top: ' + topVal + '%)', 'success');
            updatePreview();
        }
    }

    saveWidgetSetting('quote_widget_right', rightVal, checkDone);
    saveWidgetSetting('quote_widget_top', topVal, checkDone);
}

function updatePreview() {
    var preview = document.getElementById('qwPreview');
    var right = parseInt(document.getElementById('qwRight').value);
    var top = parseInt(document.getElementById('qwTop').value);
    preview.style.right = Math.min(right / 5, 40) + 'px';
    preview.style.top = top + '%';
}

// 슬라이더 드래그 시 미리보기 실시간 업데이트
document.addEventListener('DOMContentLoaded', function() {
    var rightSlider = document.getElementById('qwRight');
    var topSlider = document.getElementById('qwTop');
    if (rightSlider) rightSlider.addEventListener('input', updatePreview);
    if (topSlider) topSlider.addEventListener('input', updatePreview);
});

function toggleEnVersion() {
    var sw = document.getElementById('enSwitch');
    var label = document.getElementById('enLabel');
    var isOn = sw.classList.contains('bg-blue-600');
    var newVal = isOn ? '0' : '1';

    saveWidgetSetting('en_version_enabled', newVal, function() {
        if (newVal === '1') {
            sw.classList.remove('bg-gray-300'); sw.classList.add('bg-blue-600');
            sw.querySelector('span').classList.remove('translate-x-1'); sw.querySelector('span').classList.add('translate-x-6');
            label.textContent = '표시'; label.classList.remove('text-gray-500'); label.classList.add('text-blue-600');
        } else {
            sw.classList.remove('bg-blue-600'); sw.classList.add('bg-gray-300');
            sw.querySelector('span').classList.remove('translate-x-6'); sw.querySelector('span').classList.add('translate-x-1');
            label.textContent = '숨김'; label.classList.remove('text-blue-600'); label.classList.add('text-gray-500');
        }
        // 카드 강조 업데이트
        var enCards = document.getElementById('enSwitch').closest('.p-5').querySelectorAll('.rounded-md.border.p-3');
        enCards[0].className = 'flex-1 rounded-md border p-3 text-center ' + (newVal === '0' ? 'border-gray-300 bg-gray-50' : 'border-gray-200');
        enCards[1].className = 'flex-1 rounded-md border p-3 text-center ' + (newVal === '1' ? 'border-blue-300 bg-blue-50' : 'border-gray-200');
        showToast('영문 버전 ' + (newVal === '1' ? '표시' : '숨김') + '으로 변경되었습니다.', 'success');
    });
}

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
