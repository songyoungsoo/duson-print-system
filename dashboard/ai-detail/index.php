<?php
/**
 * AI 상세페이지 관리 대시보드
 * 8가지 기능: 상태조회, 수동전환, 품목별 핀, 자동로테이션, AI생성, A/B비교, 히스토리, 승격
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 overflow-y-auto bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">AI 상세페이지 관리</h1>
        <p class="mt-1 text-sm text-gray-500">품목별 상세페이지 버전 관리 · A/B 로테이션 제어</p>
    </div>
    <div class="flex items-center gap-2">
        <span id="active-badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
            현재 활성: <span id="active-ver">—</span>
        </span>
        <span id="last-switch" class="text-xs text-gray-400"></span>
    </div>
</div>

<!-- Auto-Rotation Control Bar -->
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">🔄 자동 로테이션</span>
            <button id="auto-toggle" onclick="toggleAuto()" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background:#cbd5e1;">
                <span id="auto-toggle-dot" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1"></span>
            </button>
            <span id="auto-label" class="text-xs text-gray-500">OFF</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <select id="sched-type" class="text-xs border border-gray-200 rounded px-2 py-1.5 focus:ring-1 focus:ring-brand">
                <option value="weekly">매주</option>
                <option value="biweekly">격주</option>
                <option value="monthly">매월</option>
            </select>
            <select id="sched-day" class="text-xs border border-gray-200 rounded px-2 py-1.5 focus:ring-1 focus:ring-brand">
                <option value="1">월요일</option><option value="2">화요일</option><option value="3">수요일</option>
                <option value="4">목요일</option><option value="5">금요일</option><option value="6">토요일</option>
                <option value="0">일요일</option>
            </select>
            <select id="sched-hour" class="text-xs border border-gray-200 rounded px-2 py-1.5 focus:ring-1 focus:ring-brand">
                <?php for ($h = 0; $h <= 23; $h++): ?>
                <option value="<?= $h ?>"><?= sprintf('%02d', $h) ?>시</option>
                <?php endfor; ?>
            </select>
            <button onclick="saveSchedule()" class="text-xs bg-brand text-white px-3 py-1.5 rounded font-semibold hover:bg-brand-dark transition-colors">저장</button>
        </div>
        <div class="sm:ml-auto text-xs text-gray-400" id="next-rotation">—</div>
    </div>
</div>

<!-- Global Actions Bar -->
<div class="flex flex-wrap gap-2 mb-4">
    <button onclick="switchAll()" id="btn-switch-all" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
        🔀 전체 버전 전환
    </button>
    <button onclick="refreshStatus()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
        🔄 새로고침
    </button>
</div>

<!-- Product Grid -->
<div id="product-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    <!-- JS로 동적 생성 -->
</div>

<!-- History Section -->
<div class="bg-white rounded-lg shadow mb-6">
    <button onclick="toggleHistory()" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition-colors">
        <h3 class="text-sm font-semibold text-gray-900">📜 교체 히스토리</h3>
        <svg id="history-chevron" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div id="history-panel" class="hidden border-t border-gray-100">
        <div id="history-list" class="divide-y divide-gray-50 max-h-96 overflow-y-auto"></div>
    </div>
</div>

</div>
</main>

<!-- Preview Modal -->
<div id="preview-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeModal('preview-modal')"></div>
    <div class="absolute inset-4 sm:inset-8 bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
            <h3 id="preview-title" class="text-sm font-bold text-gray-800">미리보기</h3>
            <button onclick="closeModal('preview-modal')" class="p-1 rounded hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <iframe id="preview-iframe" class="flex-1 w-full border-none" src="about:blank"></iframe>
    </div>
</div>

<!-- Compare Modal -->
<div id="compare-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeModal('compare-modal')"></div>
    <div class="absolute inset-4 sm:inset-8 bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
            <h3 id="compare-title" class="text-sm font-bold text-gray-800">A/B 비교</h3>
            <button onclick="closeModal('compare-modal')" class="p-1 rounded hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 flex">
            <div class="flex-1 flex flex-col border-r">
                <div class="px-3 py-1.5 bg-blue-50 text-xs font-bold text-blue-800 text-center">VER A</div>
                <iframe id="compare-iframe-a" class="flex-1 w-full border-none" src="about:blank"></iframe>
            </div>
            <div class="flex-1 flex flex-col">
                <div class="px-3 py-1.5 bg-emerald-50 text-xs font-bold text-emerald-800 text-center">VER B</div>
                <iframe id="compare-iframe-b" class="flex-1 w-full border-none" src="about:blank"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Section Editor Modal -->
<div id="section-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeModal('section-modal')"></div>
    <div class="absolute inset-2 sm:inset-4 bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50">
            <div class="flex items-center gap-3">
                <h3 id="section-modal-title" class="text-sm font-bold text-gray-800">✏️ 섹션 편집</h3>
                <div class="flex border border-gray-300 rounded overflow-hidden">
                    <button id="engine-toggle-fast" onclick="switchEngine('fast')" class="px-3 py-1 text-xs font-semibold rounded-l bg-gray-100 text-gray-600 hover:bg-gray-200">⚡ 빠른</button>
                    <button id="engine-toggle-quality" onclick="switchEngine('quality')" class="px-3 py-1 text-xs font-semibold rounded-r bg-indigo-600 text-white">🎨 고품질</button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <select id="deploy-target" class="text-xs border rounded px-2 py-1">
                    <option value="staging">Staging</option>
                    <option value="v_a">VER A</option>
                    <option value="v_b">VER B</option>
                </select>
                <button onclick="deployOutput()" class="text-xs bg-green-600 text-white px-3 py-1.5 rounded font-semibold hover:bg-green-700 transition-colors">🚀 배포</button>
                <button onclick="closeModal('section-modal')" class="p-1 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
        </div>
        <div class="flex-1 flex overflow-hidden">
            <div id="section-list" class="w-52 border-r overflow-y-auto bg-gray-50 flex-shrink-0"></div>
            <div id="section-editor" class="flex-1 overflow-y-auto p-4">
                <div class="text-center text-gray-400 py-20">
                    <p class="text-lg mb-2">← 왼쪽에서 섹션을 선택하세요</p>
                    <p class="text-sm">copy.json 텍스트를 수정하고 개별 섹션 이미지를 재생성할 수 있습니다.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API = '/dashboard/api/detail-page.php';
let STATE = {};
const DAY_NAMES = ['일', '월', '화', '수', '목', '금', '토'];

// ─── API Calls ───

async function apiGet(action) {
    const r = await fetch(`${API}?action=${action}`);
    return r.json();
}

async function apiPost(action, body = {}) {
    const r = await fetch(`${API}?action=${action}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(body),
    });
    return r.json();
}

// ─── Render ───

async function refreshStatus() {
    const data = await apiGet('status');
    if (!data.success) { showToast('상태 조회 실패', 'error'); return; }
    STATE = data;
    renderHeader(data);
    renderAutoRotation(data.auto_rotation);
    renderProductGrid(data.products, data.active_version);
}

function renderHeader(data) {
    const badge = document.getElementById('active-badge');
    const verEl = document.getElementById('active-ver');
    const switchEl = document.getElementById('last-switch');
    
    verEl.textContent = 'VER ' + data.active_version;
    
    if (data.active_version === 'B') {
        badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800';
    } else {
        badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800';
    }
    
    const btnAll = document.getElementById('btn-switch-all');
    const next = data.active_version === 'A' ? 'B' : 'A';
    btnAll.textContent = `🔀 전체 ${data.active_version}→${next} 전환`;
    
    switchEl.textContent = data.last_switch ? `마지막 전환: ${data.last_switch}` : '';
}

function renderAutoRotation(ar) {
    const enabled = ar?.enabled ?? false;
    const toggle = document.getElementById('auto-toggle');
    const dot = document.getElementById('auto-toggle-dot');
    const label = document.getElementById('auto-label');
    
    toggle.style.background = enabled ? '#3b82f6' : '#cbd5e1';
    dot.className = `inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${enabled ? 'translate-x-6' : 'translate-x-1'}`;
    label.textContent = enabled ? 'ON' : 'OFF';
    label.className = `text-xs ${enabled ? 'text-blue-600 font-semibold' : 'text-gray-500'}`;
    
    document.getElementById('sched-type').value = ar?.schedule ?? 'weekly';
    document.getElementById('sched-day').value = String(ar?.day ?? 1);
    document.getElementById('sched-hour').value = String(ar?.hour ?? 9);
    
    // 다음 전환 예상 표시
    const nextEl = document.getElementById('next-rotation');
    if (!enabled) {
        nextEl.textContent = '자동 전환 비활성';
    } else {
        const schedLabel = {weekly:'매주', biweekly:'격주', monthly:'매월'}[ar.schedule] || '매주';
        nextEl.textContent = `${schedLabel} ${DAY_NAMES[ar.day]}요일 ${String(ar.hour).padStart(2,'0')}:00 자동 전환`;
    }
}

function renderProductGrid(products, activeVer) {
    const grid = document.getElementById('product-grid');
    grid.innerHTML = '';
    
    for (const [code, p] of Object.entries(products)) {
        grid.appendChild(createProductCard(code, p, activeVer));
    }
}

function createProductCard(code, p, activeVer) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow hover:shadow-md transition-shadow';
    
    const effectiveVer = p.effective_version;
    const isPinned = !!p.pinned;
    
    // Version columns
    function verCol(label, ver, colorClass) {
        const exists = ver.exists;
        return `
        <div class="flex-1 text-center px-1">
            <div class="text-[10px] font-bold ${colorClass} mb-1">${label}</div>
            <div class="text-xs ${exists ? 'text-gray-700' : 'text-gray-300'}">🖼️ ${ver.images}/13</div>
            <div class="text-[10px] mt-0.5 ${ver.has_html ? 'text-green-600' : 'text-gray-300'}">${ver.has_html ? '✅ HTML' : '❌ 없음'}</div>
            <div class="text-[10px] mt-0.5 text-gray-400">${ver.modified || '—'}</div>
        </div>`;
    }
    
    // Pin select value
    const pinVal = p.pinned ? p.pinned : 'auto';
    
    card.innerHTML = `
    <div class="p-4">
        <!-- Card Header -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">${p.icon}</span>
                <span class="font-bold text-gray-900 text-sm">${p.label}</span>
                ${isPinned ? `<span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-semibold">${p.pinned} 고정</span>` : ''}
            </div>
            <select onchange="handlePinChange('${code}', this.value)" class="text-[11px] border border-gray-200 rounded px-1.5 py-1 focus:ring-1 focus:ring-brand text-gray-600">
                <option value="auto" ${pinVal === 'auto' ? 'selected' : ''}>자동</option>
                <option value="A" ${pinVal === 'A' ? 'selected' : ''}>A 고정</option>
                <option value="B" ${pinVal === 'B' ? 'selected' : ''}>B 고정</option>
            </select>
        </div>
        
        <!-- Version Columns -->
        <div class="flex gap-1 mb-3 py-2 px-1 bg-gray-50 rounded-lg">
            ${verCol('VER A', p.versions.A, 'text-blue-600')}
            <div class="w-px bg-gray-200"></div>
            ${verCol('VER B', p.versions.B, 'text-emerald-600')}
            <div class="w-px bg-gray-200"></div>
            ${verCol('Staging', p.versions.staging, 'text-amber-600')}
        </div>
        
        <!-- Effective Version -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full ${effectiveVer === 'B' ? 'bg-emerald-500' : 'bg-blue-500'}"></span>
                <span class="text-[11px] font-semibold text-gray-600">적용 중: VER ${effectiveVer}</span>
            </div>
        </div>
        
        <!-- Action Buttons Row 1: Preview -->
        <div class="flex gap-1.5 mb-2">
            <button onclick="openPreview('${code}', 'v_a', '${p.label}')" class="flex-1 text-[11px] px-2 py-1.5 rounded border border-blue-200 text-blue-700 hover:bg-blue-50 font-medium transition-colors ${p.versions.A.has_html ? '' : 'opacity-40 cursor-not-allowed'}" ${p.versions.A.has_html ? '' : 'disabled'}>미리보기 A</button>
            <button onclick="openPreview('${code}', 'v_b', '${p.label}')" class="flex-1 text-[11px] px-2 py-1.5 rounded border border-emerald-200 text-emerald-700 hover:bg-emerald-50 font-medium transition-colors ${p.versions.B.has_html ? '' : 'opacity-40 cursor-not-allowed'}" ${p.versions.B.has_html ? '' : 'disabled'}>미리보기 B</button>
            <button onclick="openCompare('${code}', '${p.label}')" class="flex-1 text-[11px] px-2 py-1.5 rounded border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium transition-colors ${p.versions.A.has_html && p.versions.B.has_html ? '' : 'opacity-40 cursor-not-allowed'}" ${p.versions.A.has_html && p.versions.B.has_html ? '' : 'disabled'}>🔍 비교</button>
        </div>
        
        <!-- Action Buttons Row 2: Generate (2 engines) -->
        <div class="flex gap-1.5">
            <button onclick="generateProduct('${code}', '${p.label}', 'fast')" id="gen-fast-${code}" class="flex-1 text-[11px] px-2 py-1.5 rounded bg-purple-50 text-purple-700 hover:bg-purple-100 font-medium transition-colors" title="사진 전용 이미지 (텍스트 없음, .jpg)">⚡ 빠른</button>
            <button onclick="generateProduct('${code}', '${p.label}', 'quality')" id="gen-quality-${code}" class="flex-1 text-[11px] px-2 py-1.5 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium transition-colors" title="텍스트 포함 이미지 (디자인, .png)">🎨 고품질</button>
            <button onclick="promoteProduct('${code}', 'A', '${p.label}')" class="flex-1 text-[11px] px-2 py-1.5 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium transition-colors ${p.versions.staging.exists ? '' : 'opacity-40 cursor-not-allowed'}" ${p.versions.staging.exists ? '' : 'disabled'}>S→A</button>
            <button onclick="promoteProduct('${code}', 'B', '${p.label}')" class="flex-1 text-[11px] px-2 py-1.5 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium transition-colors ${p.versions.staging.exists ? '' : 'opacity-40 cursor-not-allowed'}" ${p.versions.staging.exists ? '' : 'disabled'}>S→B</button>
        </div>
        <!-- Action Buttons Row 3: Section Edit -->
        <div class="mt-1.5">
            <button onclick="openSectionEditor('${code}', '${p.label}')" class="w-full text-[11px] px-2 py-1.5 rounded bg-amber-50 text-amber-700 hover:bg-amber-100 font-medium transition-colors">✏️ 섹션 편집 · 재생성</button>
        </div>
    </div>`;
    
    return card;
}

// ─── Actions ───

async function toggleAuto() {
    const ar = STATE.auto_rotation || {};
    const newEnabled = !(ar.enabled ?? false);
    const res = await apiPost('toggle_auto', { enabled: newEnabled });
    if (res.success) {
        showToast(`자동 로테이션 ${newEnabled ? '활성화' : '비활성화'}됨`, 'success');
        refreshStatus();
    } else {
        showToast(res.error || '실패', 'error');
    }
}

async function saveSchedule() {
    const res = await apiPost('set_schedule', {
        schedule: document.getElementById('sched-type').value,
        day: parseInt(document.getElementById('sched-day').value),
        hour: parseInt(document.getElementById('sched-hour').value),
    });
    if (res.success) {
        showToast('스케줄이 저장되었습니다', 'success');
        refreshStatus();
    } else {
        showToast(res.error || '실패', 'error');
    }
}

async function switchAll() {
    const cur = STATE.active_version || 'A';
    const next = cur === 'A' ? 'B' : 'A';
    if (!confirm(`전체 버전을 ${cur} → ${next}로 전환하시겠습니까?\n(고정된 품목은 제외됩니다)`)) return;
    
    const res = await apiPost('switch_version', {});
    if (res.success) {
        showToast(`VER ${res.from} → ${res.to} 전환 완료 (${res.switched.length}개 품목)`, 'success');
        refreshStatus();
    } else {
        showToast(res.error || '실패', 'error');
    }
}

async function handlePinChange(code, value) {
    if (value === 'auto') {
        const res = await apiPost('unpin', { product: code });
        if (res.success) {
            showToast(`${STATE.products[code]?.label || code} 고정 해제 → VER ${res.restored_to}`, 'success');
            refreshStatus();
        } else {
            showToast(res.error || '실패', 'error');
        }
    } else {
        const res = await apiPost('pin', { product: code, version: value });
        if (res.success) {
            showToast(`${STATE.products[code]?.label || code} → VER ${value} 고정`, 'success');
            refreshStatus();
        } else {
            showToast(res.error || '실패', 'error');
        }
    }
}

async function generateProduct(code, label, engine) {
    const engineLabel = engine === 'quality' ? '고품질' : '빠른';
    if (!confirm(`${label} 상세페이지를 ${engineLabel} 엔진으로 생성하시겠습니까?\n(Staging에 생성되며, 완료까지 수 분 소요)`)) return;
    
    const btn = document.getElementById(`gen-${engine}-${code}`);
    btn.textContent = '⏳ 생성 중...';
    btn.disabled = true;
    btn.className = btn.className.replace(/hover:bg-\w+-\d+/, '') + ' opacity-60';
    
    const res = await apiPost('generate', { product: code, engine: engine });
    if (res.success) {
        showToast(res.message, 'success');
    } else {
        showToast(res.error || '생성 실패', 'error');
        btn.textContent = engine === 'quality' ? '🎨 고품질' : '⚡ 빠른';
        btn.disabled = false;
    }
}

async function promoteProduct(code, target, label) {
    if (!confirm(`${label}의 Staging을 VER ${target}로 승격하시겠습니까?`)) return;
    
    const res = await apiPost('promote', { product: code, target: target });
    if (res.success) {
        showToast(`${label} → VER ${target} 승격 완료 (${res.files_copied}개 파일)`, 'success');
        refreshStatus();
    } else {
        showToast(res.error || '실패', 'error');
    }
}

// ─── Preview / Compare ───

function openPreview(code, ver, label) {
    const url = `/ImgFolder/detail_page_${ver}/${code}/detail.html`;
    document.getElementById('preview-title').textContent = `${label} — ${ver.replace('v_', 'VER ').toUpperCase()}`;
    document.getElementById('preview-iframe').src = url;
    document.getElementById('preview-modal').classList.remove('hidden');
}

function openCompare(code, label) {
    document.getElementById('compare-title').textContent = `${label} — A/B 비교`;
    document.getElementById('compare-iframe-a').src = `/ImgFolder/detail_page_v_a/${code}/detail.html`;
    document.getElementById('compare-iframe-b').src = `/ImgFolder/detail_page_v_b/${code}/detail.html`;
    document.getElementById('compare-modal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    // Clear iframes
    if (id === 'preview-modal') {
        document.getElementById('preview-iframe').src = 'about:blank';
    } else if (id === 'compare-modal') {
        document.getElementById('compare-iframe-a').src = 'about:blank';
        document.getElementById('compare-iframe-b').src = 'about:blank';
    }
}

// ─── History ───

let historyLoaded = false;

function toggleHistory() {
    const panel = document.getElementById('history-panel');
    const chevron = document.getElementById('history-chevron');
    const isHidden = panel.classList.contains('hidden');
    
    panel.classList.toggle('hidden');
    chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
    
    if (isHidden && !historyLoaded) {
        loadHistory();
    }
}

async function loadHistory() {
    const data = await apiGet('history');
    if (!data.success) return;
    
    const list = document.getElementById('history-list');
    if (!data.history.length) {
        list.innerHTML = '<div class="p-6 text-center text-sm text-gray-400">히스토리가 없습니다</div>';
        return;
    }
    
    const actionLabels = {
        'switch_all': '🔀 전체 버전 전환',
        'switch_product': '🔀 품목 버전 전환',
        'pin': '📌 버전 고정',
        'unpin': '📌 고정 해제',
        'generate': '⚡ AI 생성',
        'promote': '⬆️ 승격',
        'toggle_auto': '🔄 자동 로테이션',
        'set_schedule': '⏰ 스케줄 변경',
        'auto_rotation': '🔄 자동 전환',
        'update_section': '✏️ 섹션 텍스트 수정',
        'regen_section': '🔄 섹션 재생성',
        'deploy_output': '🚀 편집 결과 배포',
    };
    
    list.innerHTML = data.history.map(h => {
        const label = actionLabels[h.action || h.source] || h.action || h.source || '—';
        const details = [];
        if (h.from && h.to) details.push(`${h.from} → ${h.to}`);
        if (h.product) details.push(h.product);
        if (h.switched_to) details.push(`→ VER ${h.switched_to}`);
        if (h.switched && typeof h.switched === 'number') details.push(`${h.switched}개 교체`);
        if (h.enabled !== undefined) details.push(h.enabled ? '활성화' : '비활성화');
        if (h.version) details.push(`VER ${h.version}`);
        if (h.section_id) details.push(`섹션 ${h.section_id}`);
        if (h.target) details.push(`→ ${h.target}`);
        if (h.files) details.push(`${h.files}개 파일`);
        
        return `
        <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50">
            <div class="text-xs text-gray-400 whitespace-nowrap pt-0.5 w-32 flex-shrink-0">${(h.date || '').slice(0, 16)}</div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-gray-700">${label}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">${details.join(' · ') || '—'}</div>
            </div>
            <div class="text-[10px] text-gray-400 whitespace-nowrap">${h.user || '—'}</div>
        </div>`;
    }).join('');
    
    historyLoaded = true;
}

// ─── Keyboard Shortcuts ───

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal('preview-modal');
        closeModal('compare-modal');
        closeModal('section-modal');
    }
});

// ─── Section Editor ───

let SEC = { product: '', engine: 'quality', sections: [], canRegen: false, selected: null };

async function openSectionEditor(code, label) {
    SEC.product = code;
    SEC.selected = null;
    document.getElementById('section-modal-title').textContent = `✏️ ${label} — 섹션 편집`;
    document.getElementById('section-modal').classList.remove('hidden');
    document.getElementById('section-editor').innerHTML = '<div class="flex items-center justify-center py-20"><span class="text-gray-400">로딩 중...</span></div>';
    
    // 엔진 선택 UI 표시 (quality 기본)
    updateEngineToggle();
    
    await loadSections();
}

async function loadSections() {
    const res = await apiGet(`get_sections&product=${SEC.product}&engine=${SEC.engine}`);
    if (!res.success) {
        showToast(res.error || '섹션 로드 실패', 'error');
        document.getElementById('section-editor').innerHTML = `<div class="flex items-center justify-center py-20"><span class="text-red-400">${res.error || '데이터 없음'}</span></div>`;
        return;
    }
    
    SEC.sections = res.sections;
    SEC.canRegen = res.can_regen;
    renderSecList();
    if (res.sections.length > 0) selectSec(res.sections[0].id);
}

function switchEngine(engine) {
    if (SEC.engine === engine) return;
    SEC.engine = engine;
    SEC.selected = null;
    updateEngineToggle();
    document.getElementById('section-editor').innerHTML = '<div class="flex items-center justify-center py-20"><span class="text-gray-400">로딩 중...</span></div>';
    loadSections();
}

function updateEngineToggle() {
    const fastBtn = document.getElementById('engine-toggle-fast');
    const qualBtn = document.getElementById('engine-toggle-quality');
    if (!fastBtn || !qualBtn) return;
    if (SEC.engine === 'fast') {
        fastBtn.className = 'px-3 py-1 text-xs font-semibold rounded-l bg-purple-600 text-white';
        qualBtn.className = 'px-3 py-1 text-xs font-semibold rounded-r bg-gray-100 text-gray-600 hover:bg-gray-200';
    } else {
        fastBtn.className = 'px-3 py-1 text-xs font-semibold rounded-l bg-gray-100 text-gray-600 hover:bg-gray-200';
        qualBtn.className = 'px-3 py-1 text-xs font-semibold rounded-r bg-indigo-600 text-white';
    }
}

function renderSecList() {
    const list = document.getElementById('section-list');
    list.innerHTML = SEC.sections.map(s => `
        <button onclick="selectSec(${s.id})" id="sec-btn-${s.id}"
            class="w-full text-left px-3 py-2.5 text-xs border-b border-gray-200 hover:bg-white transition-colors flex items-center gap-2 ${SEC.selected === s.id ? 'bg-white font-bold border-l-2 border-l-blue-600' : ''}">
            <span class="text-gray-400 w-5 text-right flex-shrink-0">${s.id}</span>
            <span class="truncate flex-1">${s.label}</span>
            ${s.image_exists ? '' : '<span class="text-[10px] text-red-400">🚫</span>'}
        </button>
    `).join('');
}

function selectSec(id) {
    SEC.selected = id;
    renderSecList();
    
    const s = SEC.sections.find(x => x.id === id);
    if (!s) return;
    
    const fields = [];
    if ('headline' in s) fields.push({ key: 'headline', label: '헤드라인', type: 'input', val: s.headline || '' });
    if ('subtext' in s) fields.push({ key: 'subtext', label: '서브텍스트', type: 'input', val: s.subtext || '' });
    if ('badge' in s) fields.push({ key: 'badge', label: '배지', type: 'input', val: s.badge || '' });
    if ('body' in s) fields.push({ key: 'body', label: '본문', type: 'textarea', val: s.body || '' });
    if ('highlight' in s) fields.push({ key: 'highlight', label: '하이라이트', type: 'input', val: s.highlight || '' });
    if ('button_text' in s) fields.push({ key: 'button_text', label: '버튼 텍스트', type: 'input', val: s.button_text || '' });
    
    const editor = document.getElementById('section-editor');
    editor.innerHTML = `
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-base font-bold text-gray-900">섹션 ${s.id}: ${s.label}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">${s.name}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="saveSec(${s.id})" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded font-semibold hover:bg-blue-700 transition-colors">💾 저장</button>
                    <button onclick="regenSec(${s.id})" id="regen-btn-${s.id}"
                        class="text-xs ${SEC.engine === 'quality' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-purple-600 hover:bg-purple-700'} text-white px-3 py-1.5 rounded font-semibold transition-colors ${SEC.canRegen ? '' : 'opacity-40 cursor-not-allowed'}"
                        ${SEC.canRegen ? '' : 'disabled'}>🔄 ${SEC.engine === 'quality' ? '고품질' : '빠른'} 재생성</button>
                </div>
            </div>
            ${s.image_exists ? `
            <div class="mb-4 border rounded-lg overflow-hidden bg-gray-100">
                <img id="sec-img-${s.id}" src="${s.image_url}" alt="Section ${s.id}" class="w-full" style="max-height:400px;object-fit:contain;" loading="lazy">
            </div>` : `
            <div class="mb-4 border rounded-lg p-8 bg-gray-50 text-center text-gray-400 text-sm">
                이미지가 아직 없습니다. "재생성"을 클릭하세요.
            </div>`}
            <div class="space-y-3">
                ${fields.map(f => f.type === 'textarea' ? `
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">${f.label}</label>
                    <textarea id="field-${f.key}" rows="4" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-y">${escH(f.val)}</textarea>
                </div>` : `
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">${f.label}</label>
                    <input id="field-${f.key}" type="text" value="${escH(f.val)}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>`).join('')}
            </div>
            <div class="mt-4 text-[11px] text-gray-400 bg-gray-50 rounded p-3">
                💡 현재 엔진: <strong class="text-gray-600">${SEC.engine === 'quality' ? '🎨 고품질 (텍스트+이미지, .png)' : '⚡ 빠른 (사진만, .jpg)'}</strong><br>
                텍스트를 수정하고 "저장" → "재생성"을 클릭하면 해당 섹션 이미지만 다시 생성됩니다. (~30초, ~$0.07)
            </div>
        </div>`;
}

function escH(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function saveSec(id) {
    const s = SEC.sections.find(x => x.id === id);
    if (!s) return;
    
    const fields = {};
    for (const key of ['headline','body','subtext','badge','highlight','button_text']) {
        const el = document.getElementById(`field-${key}`);
        if (el) { fields[key] = el.value; s[key] = el.value; }
    }
    
    const res = await apiPost('update_section', { product: SEC.product, section_id: id, fields, engine: SEC.engine });
    showToast(res.success ? res.message : (res.error || '저장 실패'), res.success ? 'success' : 'error');
}

async function regenSec(id) {
    if (!confirm(`섹션 ${id}을 재생성하시겠습니까?\n텍스트 변경사항이 자동 저장된 후 재생성됩니다.\n(~30초 소요, ~$0.07)`)) return;
    
    await saveSec(id);
    
    const btn = document.getElementById(`regen-btn-${id}`);
    const origText = btn.textContent;
    btn.textContent = '⏳ 생성 중...';
    btn.disabled = true;
    
    const res = await apiPost('regen_section', { product: SEC.product, section_id: id, engine: SEC.engine });
    if (!res.success) {
        showToast(res.error || '재생성 실패', 'error');
        btn.textContent = origText;
        btn.disabled = false;
        return;
    }
    
    showToast(res.message, 'success');
    
    // Poll for completion
    const poll = setInterval(async () => {
        const st = await apiGet(`regen_status&product=${SEC.product}&section_id=${id}&engine=${SEC.engine}`);
        if (st.completed || st.failed) {
            clearInterval(poll);
            btn.textContent = origText;
            btn.disabled = false;
            if (st.completed) {
                showToast(`섹션 ${id} 재생성 완료!`, 'success');
                const secRes = await apiGet(`get_sections&product=${SEC.product}&engine=${SEC.engine}`);
                if (secRes.success) {
                    SEC.sections = secRes.sections;
                    SEC.canRegen = secRes.can_regen;
                    selectSec(id);
                }
            } else {
                showToast(`섹션 ${id} 재생성 실패`, 'error');
            }
        }
    }, 5000);
    
    // Timeout after 2 minutes
    setTimeout(() => {
        clearInterval(poll);
        if (btn.disabled) {
            btn.textContent = origText;
            btn.disabled = false;
            showToast('재생성이 예상보다 오래 걸리고 있습니다. 잠시 후 새로고침해주세요.', 'warning');
        }
    }, 120000);
}

async function deployOutput() {
    const target = document.getElementById('deploy-target').value;
    const label = { staging: 'Staging', v_a: 'VER A', v_b: 'VER B' }[target] || target;
    const prodLabel = STATE.products?.[SEC.product]?.label || SEC.product;
    
    if (!confirm(`${prodLabel}의 편집 결과를 ${label}에 배포하시겠습니까?\n(기존 파일이 덮어씌워집니다)`)) return;
    
    const res = await apiPost('deploy_output', { product: SEC.product, target });
    if (res.success) {
        showToast(res.message, 'success');
        refreshStatus();
    } else {
        showToast(res.error || '배포 실패', 'error');
    }
}

// ─── Init ───
refreshStatus();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
