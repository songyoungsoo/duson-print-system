<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 min-h-0 bg-gray-50 overflow-y-auto">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-bold text-gray-900">NAS 동기화</h1>
                <p class="text-xs text-gray-500 mt-0.5" id="nasTargetLabel">dsp1830.ipdisk.co.kr → /HDD2/share</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="connectionStatus" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                    미연결
                </span>
            </div>
        </div>

        <!-- NAS 접속 설정 -->
        <div class="bg-white rounded-lg shadow mb-3">
            <button type="button" onclick="toggleConnectionPanel()" class="w-full p-4 flex items-center justify-between hover:bg-gray-50 transition-colors rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="text-sm">🔌</span>
                    <h3 class="text-sm font-semibold text-gray-900">NAS 접속 설정</h3>
                    <span id="activeProfileBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">기본 NAS (dsp1830)</span>
                </div>
                <svg id="connPanelArrow" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="connectionPanel" class="hidden border-t border-gray-100">
                <div class="p-4">
                    <!-- 프로필 선택 -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">저장된 프로필</label>
                        <div class="flex items-center gap-2">
                            <select id="profileSelect" onchange="loadProfile(this.value)" class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="default">기본 NAS (dsp1830)</option>
                                <option value="sknas205">2차 NAS (sknas205)</option>
                            </select>
                            <button type="button" onclick="saveProfile()" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors" title="현재 입력값을 새 프로필로 저장">
                                💾 저장
                            </button>
                            <button type="button" onclick="deleteProfile()" class="px-2 py-1.5 bg-gray-100 text-red-500 text-xs font-medium rounded-md hover:bg-red-50 transition-colors" title="선택된 프로필 삭제" id="deleteProfileBtn" style="display:none;">
                                🗑️
                            </button>
                        </div>
                    </div>
                    <!-- 접속정보 입력 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">호스트 (도메인/IP)</label>
                            <input type="text" id="nasHost" value="dsp1830.ipdisk.co.kr" placeholder="nas.example.com"
                                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono"
                                   oninput="updateTargetLabel()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">원격 경로</label>
                            <input type="text" id="nasRoot" value="/HDD2/share" placeholder="/volume1/web"
                                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono"
                                   oninput="updateTargetLabel()">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">사용자명</label>
                            <input type="text" id="nasUser" value="admin" placeholder="admin"
                                   class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">비밀번호</label>
                            <div class="relative">
                                <input type="password" id="nasPass" value="1830" placeholder="••••••••"
                                       class="w-full px-3 py-1.5 pr-8 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                                    <span id="pwToggleIcon">👁️</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">접속정보는 브라우저 localStorage에만 저장되며, 서버에 저장하지 않습니다.</p>
                </div>
            </div>
        </div>

        <!-- FTP 연결 테스트 -->
        <div class="bg-white rounded-lg shadow mb-3">
            <div class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">FTP 연결 상태</h3>
                        <p class="text-xs text-gray-500 mt-0.5">NAS 서버 접속 가능 여부를 확인합니다.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="runAction('git_status')" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                            Git 상태
                        </button>
                        <button type="button" onclick="runAction('status')" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                            NAS 목록
                        </button>
                        <button type="button" onclick="runAction('test')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors">
                            연결 테스트
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 동기화 모드 선택 -->
        <div class="bg-white rounded-lg shadow mb-3">
            <div class="p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">동기화 모드</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <!-- 전체 미러링 -->
                    <label class="sync-mode-card relative flex flex-col p-3 rounded-lg border-2 cursor-pointer transition-all hover:border-blue-300"
                           id="card-mirror">
                        <input type="radio" name="sync_mode" value="mirror" class="sr-only" onchange="selectMode(this)" checked>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">🔄</span>
                            <span class="text-sm font-semibold text-gray-900">전체 미러링</span>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">서버 전체를 NAS에 동기화합니다. bbs 제외, ImgFolder/upload 날짜 필터 적용.</p>
                    </label>

                    <!-- 변경분만 -->
                    <label class="sync-mode-card relative flex flex-col p-3 rounded-lg border-2 cursor-pointer transition-all hover:border-blue-300"
                           id="card-changed">
                        <input type="radio" name="sync_mode" value="changed" class="sr-only" onchange="selectMode(this)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">📝</span>
                            <span class="text-sm font-semibold text-gray-900">변경분만</span>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">특정 날짜 이후 변경된 파일만 동기화합니다.</p>
                    </label>

                    <!-- 특정 파일 -->
                    <label class="sync-mode-card relative flex flex-col p-3 rounded-lg border-2 cursor-pointer transition-all hover:border-blue-300"
                           id="card-file">
                        <input type="radio" name="sync_mode" value="file" class="sr-only" onchange="selectMode(this)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">📄</span>
                            <span class="text-sm font-semibold text-gray-900">특정 파일</span>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">지정한 파일/디렉토리만 NAS에 업로드합니다.</p>
                    </label>
                </div>

                <!-- 변경분 옵션: 날짜 입력 -->
                <div id="changedOptions" class="mt-3 hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">기준 날짜 (이후 변경분)</label>
                    <input type="date" id="sinceDate" value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>"
                           class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- 특정 파일 옵션: 경로 입력 -->
                <div id="fileOptions" class="mt-3 hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">파일 경로 (웹루트 기준)</label>
                    <input type="text" id="filePath" placeholder="예: dashboard/nas-sync/index.php"
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono">
                    <p class="text-[10px] text-gray-400 mt-1">상대 경로 입력. 예: includes/header.php, mlangprintauto/namecard/</p>
                </div>

                <!-- Dry-run 토글 + 실행 버튼 -->
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="dryRun" checked
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-xs font-medium text-gray-700">Dry-run (미리보기)</span>
                        </label>
                        <span class="text-[10px] text-amber-600 font-medium" id="dryRunHint">✓ 실제 전송 없이 변경사항만 확인</span>
                    </div>
                    <button type="button" id="syncBtn" onclick="executeSync()"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg id="syncSpinner" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="syncBtnText">동기화 실행</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 출력 콘솔 -->
        <div class="bg-white rounded-lg shadow">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">실행 결과</h3>
                <div class="flex items-center gap-2">
                    <span id="resultTime" class="text-[10px] text-gray-400"></span>
                    <button type="button" onclick="clearOutput()" class="text-[10px] text-gray-400 hover:text-gray-600 transition-colors">
                        지우기
                    </button>
                </div>
            </div>
            <div id="outputConsole" class="p-4 font-mono text-xs leading-relaxed bg-gray-900 text-green-400 rounded-b-lg overflow-auto" style="min-height:200px; max-height:500px; white-space:pre-wrap;">
대기 중... 위에서 동기화 모드를 선택하고 실행하세요.</div>
        </div>

        <!-- 동기화 정보 -->
        <div class="mt-3 bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">동기화 규칙</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-600">
                <div class="flex items-start gap-2">
                    <span class="text-red-400 mt-0.5">✕</span>
                    <div>
                        <span class="font-medium text-gray-800">제외 항목</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">bbs/ 폴더 전체, .git/, node_modules/, *.log</p>
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-amber-400 mt-0.5">◐</span>
                    <div>
                        <span class="font-medium text-gray-800">ImgFolder</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">2026-02-10 이후 데이터만 동기화</p>
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-amber-400 mt-0.5">◐</span>
                    <div>
                        <span class="font-medium text-gray-800">upload/</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">mlangorder_printauto/upload 중 2026년 이후만</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// === State ===
var currentMode = 'mirror';
var isRunning = false;
var hasGit = true;  // 환경 정보로 업데이트됨
var STORAGE_KEY = 'nas_sync_profiles';

// === NAS 접속정보 가져오기 ===
function getNasCredentials() {
    return {
        nas_host: document.getElementById('nasHost').value.trim(),
        nas_user: document.getElementById('nasUser').value.trim(),
        nas_pass: document.getElementById('nasPass').value,
        nas_root: document.getElementById('nasRoot').value.trim()
    };
}

// === 프로필 관리 (localStorage) ===
function getProfiles() {
    try {
        var data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        return data;
    } catch (e) {
        return {};
    }
}

function renderProfileSelect() {
    var select = document.getElementById('profileSelect');
    var profiles = getProfiles();
    var currentVal = select.value;

    // 기존 옵션 제거 (기본 2개 프로필 유지: default, sknas205)
    while (select.options.length > 2) {
        select.remove(2);
    }

    // 저장된 프로필 추가
    for (var name in profiles) {
        if (profiles.hasOwnProperty(name)) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name + ' (' + profiles[name].nas_host + ')';
            select.appendChild(opt);
        }
    }

    // 이전 선택 복원
    if (currentVal) select.value = currentVal;

    // 삭제 버튼 표시 여부
    document.getElementById('deleteProfileBtn').style.display = (select.value === 'default' || select.value === 'sknas205') ? 'none' : '';
}

function loadProfile(name) {
    document.getElementById('deleteProfileBtn').style.display = (name === 'default' || name === 'sknas205') ? 'none' : '';

    if (name === 'default') {
        document.getElementById('nasHost').value = 'dsp1830.ipdisk.co.kr';
        document.getElementById('nasUser').value = 'admin';
        document.getElementById('nasPass').value = '1830';
        document.getElementById('nasRoot').value = '/HDD2/share';
        updateTargetLabel();
        updateProfileBadge('기본 NAS (dsp1830)');
        return;
    }

    if (name === 'sknas205') {
        document.getElementById('nasHost').value = 'sknas205.ipdisk.co.kr';
        document.getElementById('nasUser').value = 'sknas205';
        document.getElementById('nasPass').value = 'sknas205204203';
        document.getElementById('nasRoot').value = '/HDD1/duson260118';
        updateTargetLabel();
        updateProfileBadge('2차 NAS (sknas205)');
        return;
    }

    var profiles = getProfiles();
    var p = profiles[name];
    if (!p) return;

    document.getElementById('nasHost').value = p.nas_host || '';
    document.getElementById('nasUser').value = p.nas_user || '';
    document.getElementById('nasPass').value = p.nas_pass || '';
    document.getElementById('nasRoot').value = p.nas_root || '';
    updateTargetLabel();
    updateProfileBadge(name);

    // 연결 상태 초기화
    updateConnectionStatus(null);
}

function saveProfile() {
    var creds = getNasCredentials();
    if (!creds.nas_host) {
        showToast('호스트를 입력하세요.', 'error');
        return;
    }

    var name = prompt('프로필 이름을 입력하세요:', creds.nas_host);
    if (!name || !name.trim()) return;
    name = name.trim();

    if (name === 'default') {
        showToast('기본 프로필은 수정할 수 없습니다.', 'error');
        return;
    }

    var profiles = getProfiles();
    profiles[name] = creds;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(profiles));

    renderProfileSelect();
    document.getElementById('profileSelect').value = name;
    document.getElementById('deleteProfileBtn').style.display = '';
    updateProfileBadge(name);
    showToast('프로필 "' + name + '" 저장됨', 'success');
}

function deleteProfile() {
    var select = document.getElementById('profileSelect');
    var name = select.value;
    if (name === 'default' || name === 'sknas205') return;

    if (!confirm('프로필 "' + name + '"을(를) 삭제하시겠습니까?')) return;

    var profiles = getProfiles();
    delete profiles[name];
    localStorage.setItem(STORAGE_KEY, JSON.stringify(profiles));

    select.value = 'default';
    loadProfile('default');
    renderProfileSelect();
    showToast('프로필 삭제됨', 'success');
}

function updateProfileBadge(name) {
    document.getElementById('activeProfileBadge').textContent = name;
}

// === 접속 설정 패널 토글 ===
function toggleConnectionPanel() {
    var panel = document.getElementById('connectionPanel');
    var arrow = document.getElementById('connPanelArrow');
    panel.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

function togglePasswordVisibility() {
    var pw = document.getElementById('nasPass');
    var icon = document.getElementById('pwToggleIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.textContent = '🔒';
    } else {
        pw.type = 'password';
        icon.textContent = '👁️';
    }
}

function updateTargetLabel() {
    var host = document.getElementById('nasHost').value.trim() || '...';
    var root = document.getElementById('nasRoot').value.trim() || '/';
    document.getElementById('nasTargetLabel').textContent = host + ' → ' + root;
}

// === Mode Selection ===
function selectMode(radio) {
    currentMode = radio.value;

    // Update card styles
    document.querySelectorAll('.sync-mode-card').forEach(function(card) {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
    });
    var activeCard = document.getElementById('card-' + currentMode);
    if (activeCard) {
        activeCard.classList.remove('border-gray-200');
        activeCard.classList.add('border-blue-500', 'bg-blue-50');
    }

    // Toggle option panels
    document.getElementById('changedOptions').classList.toggle('hidden', currentMode !== 'changed');
    document.getElementById('fileOptions').classList.toggle('hidden', currentMode !== 'file');
}

// === API Call ===
function runAction(action, extraData) {
    if (isRunning) return;

    isRunning = true;
    var btn = document.getElementById('syncBtn');
    var spinner = document.getElementById('syncSpinner');
    var btnText = document.getElementById('syncBtnText');
    btn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = '실행 중...';

    var formData = new FormData();
    formData.append('action', action);

    // NAS 접속정보 항상 포함
    var creds = getNasCredentials();
    formData.append('nas_host', creds.nas_host);
    formData.append('nas_user', creds.nas_user);
    formData.append('nas_pass', creds.nas_pass);
    formData.append('nas_root', creds.nas_root);

    if (extraData) {
        for (var key in extraData) {
            if (extraData.hasOwnProperty(key)) {
                formData.append(key, extraData[key]);
            }
        }
    }

    var startTime = Date.now();
    var displayHost = creds.nas_host || '(미설정)';
    writeOutput('▶ [' + displayHost + '] ' + action + (extraData ? ' ' + JSON.stringify(extraData) : '') + '\n─────────────────────────────────────\n');

    fetch('/dashboard/api/nas-sync.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
        document.getElementById('resultTime').textContent = elapsed + '초 소요';

        if (data.success) {
            appendOutput(data.output || '(출력 없음)');
            appendOutput('\n─────────────────────────────────────\n✅ 완료 (' + elapsed + '초)');
        } else {
            appendOutput(data.output || data.error || '알 수 없는 오류');
            appendOutput('\n─────────────────────────────────────\n❌ 실패 (code: ' + data.return_code + ')');
        }

        // Update connection status on test
        if (action === 'test') {
            updateConnectionStatus(data.success);
        }
    })
    .catch(function(err) {
        appendOutput('\n❌ 네트워크 오류: ' + err.message);
    })
    .finally(function() {
        isRunning = false;
        btn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = '동기화 실행';
    });
}

// === Execute Sync ===
function executeSync() {
    var creds = getNasCredentials();
    if (!creds.nas_host || !creds.nas_user) {
        showToast('NAS 호스트와 사용자명을 입력하세요.', 'error');
        return;
    }

    var dryRun = document.getElementById('dryRun').checked;

    // Confirm if NOT dry-run
    if (!dryRun) {
        if (!confirm('실제로 ' + creds.nas_host + ' NAS에 파일을 전송합니다.\n계속하시겠습니까?')) return;
    }

    var extra = {};
    if (dryRun) extra.dry_run = '1';

    switch (currentMode) {
        case 'mirror':
            runAction('mirror', extra);
            break;
        case 'changed':
            var since = document.getElementById('sinceDate').value;
            if (since) extra.since = since;
            runAction('changed', extra);
            break;
        case 'file':
            var fp = document.getElementById('filePath').value.trim();
            if (!fp) {
                showToast('파일 경로를 입력하세요.', 'error');
                return;
            }
            extra.file_path = fp;
            runAction('file', extra);
            break;
    }
}

// === Console Output ===
function writeOutput(text) {
    var console_el = document.getElementById('outputConsole');
    console_el.textContent = text;
    console_el.scrollTop = console_el.scrollHeight;
}

function appendOutput(text) {
    var console_el = document.getElementById('outputConsole');
    console_el.textContent += text;
    console_el.scrollTop = console_el.scrollHeight;
}

function clearOutput() {
    document.getElementById('outputConsole').textContent = '대기 중...';
    document.getElementById('resultTime').textContent = '';
}

// === Connection Status Badge ===
function updateConnectionStatus(connected) {
    var badge = document.getElementById('connectionStatus');
    if (connected === null) {
        badge.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> 미연결';
    } else if (connected) {
        badge.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-medium bg-green-100 text-green-700';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> 연결됨';
    } else {
        badge.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-medium bg-red-100 text-red-700';
        badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> 연결 실패';
    }
}

// === Init ===
document.addEventListener('DOMContentLoaded', function() {
    selectMode(document.querySelector('input[name="sync_mode"]:checked'));
    renderProfileSelect();

    // 환경 정보 조회 (git 가용 여부)
    checkEnvironment();

    // 마지막 사용 프로필 복원
    var lastProfile = localStorage.getItem('nas_sync_last_profile');
    if (lastProfile) {
        document.getElementById('profileSelect').value = lastProfile;
        loadProfile(lastProfile);
    }

    // Dry-run checkbox label update
    document.getElementById('dryRun').addEventListener('change', function() {
        var hint = document.getElementById('dryRunHint');
        if (this.checked) {
            hint.textContent = '✓ 실제 전송 없이 변경사항만 확인';
            hint.className = 'text-[10px] text-amber-600 font-medium';
        } else {
            hint.textContent = '⚠ 실제 파일을 NAS에 전송합니다!';
            hint.className = 'text-[10px] text-red-600 font-medium';
        }
    });

    // 프로필 변경 시 마지막 사용 기록
    document.getElementById('profileSelect').addEventListener('change', function() {
        localStorage.setItem('nas_sync_last_profile', this.value);
    });
});

// === 환경 정보 조회 ===
function checkEnvironment() {
    var formData = new FormData();
    formData.append('action', 'env_info');
    fetch('/dashboard/api/nas-sync.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            hasGit = data.has_git;
            if (!hasGit) {
                // "변경분만" 모드 비활성화
                var changedCard = document.getElementById('card-changed');
                var changedRadio = changedCard.querySelector('input[type="radio"]');
                changedRadio.disabled = true;
                changedCard.classList.add('opacity-50');
                changedCard.style.cursor = 'not-allowed';
                changedCard.querySelector('p').textContent = 'Git이 없는 환경입니다. 로컬 서버에서만 사용 가능합니다.';

                // "Git 상태" 버튼 레이블 변경
                var gitBtn = document.querySelector('[onclick="runAction(\'git_status\')"]');
                if (gitBtn) gitBtn.textContent = '파일 상태';
            }
        }
    })
    .catch(function() { /* 실패 시 무시 - 기본값 사용 */ });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
