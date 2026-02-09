<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- 헤더 -->
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">방문자 분석</h1>
                <p class="mt-1 text-sm text-gray-600">실시간 방문자 현황 및 트래픽 분석</p>
            </div>
            <button onclick="refreshAll()" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">새로고침</button>
        </div>

        <!-- 실시간 바 -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg px-5 py-3 mb-4 flex items-center gap-4">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-400"></span>
            </span>
            <span class="text-sm">현재 접속자</span>
            <span class="text-2xl font-bold" id="activeCount">-</span>
            <span class="ml-auto text-sm">오늘 봇 방문: <strong id="botCount">-</strong></span>
        </div>

        <!-- 탭 -->
        <div class="flex gap-1 mb-4 border-b border-gray-200">
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600" data-tab="overview">개요</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="suspicious">의심 활동</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="blocked">차단 목록</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="realtime">실시간</button>
        </div>

        <!-- 개요 탭 -->
        <div id="tab-overview">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4" id="summaryCards">
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-16 bg-gray-200 rounded"></div></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">시간대별 방문자</h3>
                    <div class="relative" style="height: 240px;"><canvas id="hourlyChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">유입 경로</h3>
                    <div class="relative" style="height: 240px;"><canvas id="refererChart"></canvas></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">오늘 인기 페이지</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500" style="width:100px">조회수</th>
                            </tr>
                        </thead>
                        <tbody id="topPagesBody" class="divide-y divide-gray-100">
                            <tr><td colspan="2" class="px-3 py-8 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 의심 활동 탭 -->
        <div id="tab-suspicious" style="display:none;">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">1시간 내 과다 접속 IP</h3>
                    <select id="thresholdSelect" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg">
                        <option value="50">50회 이상</option>
                        <option value="100" selected>100회 이상</option>
                        <option value="200">200회 이상</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">접속 횟수</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지 수</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">마지막 접속</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">상태</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">조치</th>
                            </tr>
                        </thead>
                        <tbody id="suspiciousBody" class="divide-y divide-gray-100">
                            <tr><td colspan="6" class="px-3 py-8 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-gray-400">* 1시간 내 동일 IP에서의 접속 횟수 기준 (봇 제외)</p>
            </div>
        </div>

        <!-- 차단 목록 탭 -->
        <div id="tab-blocked" style="display:none;">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">차단된 IP 목록</h3>
                    <button onclick="showBlockModal()" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">+ IP 차단</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">사유</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">차단 일시</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">만료</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">조치</th>
                            </tr>
                        </thead>
                        <tbody id="blockedBody" class="divide-y divide-gray-100">
                            <tr><td colspan="5" class="px-3 py-8 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 실시간 탭 -->
        <div id="tab-realtime" style="display:none;">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">실시간 방문자 (최근 5분)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">브라우저</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">시간</th>
                            </tr>
                        </thead>
                        <tbody id="realtimeBody" class="divide-y divide-gray-100">
                            <tr><td colspan="4" class="px-3 py-8 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- IP 차단 모달 -->
<div id="blockModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-[90%]">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">IP 차단</h4>
        <input type="text" id="blockIP" placeholder="IP 주소 (예: 123.456.789.0)" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 text-sm">
        <input type="text" id="blockReason" placeholder="차단 사유" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 text-sm">
        <select id="blockDuration" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-4 text-sm">
            <option value="1">1시간</option>
            <option value="24" selected>24시간 (1일)</option>
            <option value="168">7일</option>
            <option value="720">30일</option>
            <option value="0">영구 차단</option>
        </select>
        <div class="flex gap-3 justify-end">
            <button onclick="hideBlockModal()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100">취소</button>
            <button onclick="blockIP()" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">차단</button>
        </div>
    </div>
</div>

<script>
var hourlyChart, refererChart;
var API = '/dashboard/api/visitor_stats.php';

// 탭 전환
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var tab = this.getAttribute('data-tab');
        document.querySelectorAll('.tab-btn').forEach(function(b) {
            b.classList.remove('border-blue-500', 'text-blue-600');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        this.classList.remove('border-transparent', 'text-gray-500');
        this.classList.add('border-blue-500', 'text-blue-600');

        document.querySelectorAll('[id^="tab-"]').forEach(function(el) { el.style.display = 'none'; });
        document.getElementById('tab-' + tab).style.display = 'block';

        if (tab === 'suspicious') loadSuspicious();
        if (tab === 'blocked') loadBlocked();
        if (tab === 'realtime') loadRealtime();
    });
});

document.getElementById('thresholdSelect').addEventListener('change', loadSuspicious);

function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// 요약 카드
async function loadSummary() {
    try {
        var res = await fetch(API + '?type=summary');
        var data = await res.json();

        document.getElementById('activeCount').textContent = formatNumber(data.realtime.active);
        document.getElementById('botCount').textContent = formatNumber(data.realtime.bots_today);

        var container = document.getElementById('summaryCards');
        container.textContent = '';

        var cards = [
            { label: '오늘 방문', value: formatNumber(data.today.visits), sub: '순 방문자: ' + formatNumber(data.today.unique_visitors) + '명', growth: data.today.growth },
            { label: '오늘 세션', value: formatNumber(data.today.sessions), sub: '' },
            { label: '이번달 방문', value: formatNumber(data.month.visits), sub: '순 방문자: ' + formatNumber(data.month.unique_visitors) + '명' },
            { label: '전체 누적', value: formatNumber(data.total.visits), sub: '순 방문자: ' + formatNumber(data.total.unique_visitors) + '명' }
        ];

        cards.forEach(function(c) {
            var card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow p-4';

            var lbl = document.createElement('div');
            lbl.className = 'text-xs text-gray-500 mb-1';
            lbl.textContent = c.label;

            var val = document.createElement('div');
            val.className = 'text-2xl font-bold text-gray-900';
            val.textContent = c.value;

            card.appendChild(lbl);
            card.appendChild(val);

            if (c.sub) {
                var sub = document.createElement('div');
                sub.className = 'text-xs text-gray-400 mt-1';
                sub.textContent = c.sub;
                if (c.growth !== undefined) {
                    var badge = document.createElement('span');
                    badge.className = 'ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ' +
                        (c.growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700');
                    badge.textContent = (c.growth >= 0 ? '▲ ' : '▼ ') + Math.abs(c.growth) + '%';
                    sub.appendChild(badge);
                }
                card.appendChild(sub);
            }

            container.appendChild(card);
        });
    } catch (e) { console.error('Summary error:', e); }
}

// 시간대별 차트
async function loadHourlyChart() {
    try {
        var res = await fetch(API + '?type=hourly');
        var data = await res.json();
        var labels = Array.from({length: 24}, function(_, i) { return i + '시'; });
        var visits = data.map(function(d) { return d.visits; });

        if (hourlyChart) hourlyChart.destroy();
        hourlyChart = new Chart(document.getElementById('hourlyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: '방문수', data: visits, backgroundColor: '#3b82f6', borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) { console.error('Hourly chart error:', e); }
}

// 유입 경로 차트
async function loadRefererChart() {
    try {
        var res = await fetch(API + '?type=referers');
        var data = await res.json();
        var colors = ['#3b82f6','#ef4444','#f59e0b','#10b981','#f97316','#06b6d4','#8b5cf6','#ec4899'];

        if (refererChart) refererChart.destroy();
        refererChart = new Chart(document.getElementById('refererChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.map(function(d) { return d.source; }),
                datasets: [{ data: data.map(function(d) { return d.visits; }), backgroundColor: colors, borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } } }
            }
        });
    } catch (e) { console.error('Referer chart error:', e); }
}

// 인기 페이지
async function loadTopPages() {
    try {
        var res = await fetch(API + '?type=pages');
        var data = await res.json();
        var tbody = document.getElementById('topPagesBody');
        tbody.textContent = '';

        if (data.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 2; td.className = 'px-3 py-4 text-center text-gray-400 text-sm'; td.textContent = '데이터 없음';
            tr.appendChild(td); tbody.appendChild(tr);
            return;
        }

        data.forEach(function(p) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';
            var tdPage = document.createElement('td');
            tdPage.className = 'px-3 py-2 text-sm text-gray-700';
            tdPage.textContent = p.page.length > 60 ? p.page.substring(0, 60) + '...' : p.page;
            tdPage.title = p.page;
            var tdViews = document.createElement('td');
            tdViews.className = 'px-3 py-2 text-sm text-gray-900 text-right';
            tdViews.textContent = formatNumber(p.views);
            tr.appendChild(tdPage); tr.appendChild(tdViews);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Top pages error:', e); }
}

// 의심 IP
async function loadSuspicious() {
    var threshold = document.getElementById('thresholdSelect').value;
    try {
        var res = await fetch(API + '?type=suspicious&threshold=' + threshold);
        var data = await res.json();
        var tbody = document.getElementById('suspiciousBody');
        tbody.textContent = '';

        if (data.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 6; td.className = 'px-3 py-4 text-center text-gray-400 text-sm'; td.textContent = '의심 활동 없음';
            tr.appendChild(td); tbody.appendChild(tr);
            return;
        }

        data.forEach(function(s) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            var tdIP = document.createElement('td');
            tdIP.className = 'px-3 py-2 text-sm font-medium text-gray-900'; tdIP.textContent = s.ip;

            var tdCount = document.createElement('td');
            tdCount.className = 'px-3 py-2 text-sm text-gray-700'; tdCount.textContent = formatNumber(s.count) + '회';

            var tdPages = document.createElement('td');
            tdPages.className = 'px-3 py-2 text-sm text-gray-700'; tdPages.textContent = s.unique_pages + '개';

            var tdTime = document.createElement('td');
            tdTime.className = 'px-3 py-2 text-sm text-gray-500'; tdTime.textContent = s.last_visit.substring(11, 19);

            var tdStatus = document.createElement('td');
            tdStatus.className = 'px-3 py-2';
            var badge = document.createElement('span');
            var statusMap = { critical: ['bg-red-100 text-red-700', '차단 권장'], warning: ['bg-yellow-100 text-yellow-700', '모니터링'], watch: ['bg-blue-100 text-blue-700', '감시'] };
            var st = statusMap[s.status] || statusMap.watch;
            badge.className = 'px-2 py-0.5 text-xs font-medium rounded-full ' + st[0];
            badge.textContent = st[1];
            tdStatus.appendChild(badge);

            var tdAction = document.createElement('td');
            tdAction.className = 'px-3 py-2';
            var btn = document.createElement('button');
            btn.className = 'px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700';
            btn.textContent = '차단';
            btn.setAttribute('data-ip', s.ip);
            btn.addEventListener('click', function() { quickBlock(this.getAttribute('data-ip')); });
            tdAction.appendChild(btn);

            tr.appendChild(tdIP); tr.appendChild(tdCount); tr.appendChild(tdPages);
            tr.appendChild(tdTime); tr.appendChild(tdStatus); tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Suspicious error:', e); }
}

// 차단 목록
async function loadBlocked() {
    try {
        var res = await fetch(API + '?type=blocked');
        var data = await res.json();
        var tbody = document.getElementById('blockedBody');
        tbody.textContent = '';

        if (data.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 5; td.className = 'px-3 py-4 text-center text-gray-400 text-sm'; td.textContent = '차단된 IP 없음';
            tr.appendChild(td); tbody.appendChild(tr);
            return;
        }

        data.forEach(function(b) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            var tdIP = document.createElement('td');
            tdIP.className = 'px-3 py-2 text-sm font-medium text-gray-900'; tdIP.textContent = b.ip;

            var tdReason = document.createElement('td');
            tdReason.className = 'px-3 py-2 text-sm text-gray-700'; tdReason.textContent = b.reason || '-';

            var tdDate = document.createElement('td');
            tdDate.className = 'px-3 py-2 text-sm text-gray-500'; tdDate.textContent = b.blocked_at;

            var tdExpires = document.createElement('td');
            tdExpires.className = 'px-3 py-2 text-sm text-gray-500'; tdExpires.textContent = b.is_permanent ? '영구' : (b.expires_at || '-');

            var tdAction = document.createElement('td');
            tdAction.className = 'px-3 py-2';
            var btn = document.createElement('button');
            btn.className = 'px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100';
            btn.textContent = '해제';
            btn.setAttribute('data-ip', b.ip);
            btn.addEventListener('click', function() { unblockIP(this.getAttribute('data-ip')); });
            tdAction.appendChild(btn);

            tr.appendChild(tdIP); tr.appendChild(tdReason); tr.appendChild(tdDate);
            tr.appendChild(tdExpires); tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Blocked error:', e); }
}

// 실시간 방문자
async function loadRealtime() {
    try {
        var res = await fetch(API + '?type=realtime');
        var data = await res.json();
        var tbody = document.getElementById('realtimeBody');
        tbody.textContent = '';

        if (data.length === 0) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            td.colSpan = 4; td.className = 'px-3 py-4 text-center text-gray-400 text-sm'; td.textContent = '현재 접속자 없음';
            tr.appendChild(td); tbody.appendChild(tr);
            return;
        }

        data.forEach(function(v) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            var tdIP = document.createElement('td');
            tdIP.className = 'px-3 py-2 text-sm text-gray-900'; tdIP.textContent = v.ip;

            var tdPage = document.createElement('td');
            tdPage.className = 'px-3 py-2 text-sm text-gray-700';
            tdPage.textContent = v.page.length > 50 ? v.page.substring(0, 50) + '...' : v.page;
            tdPage.title = v.page;

            var tdBrowser = document.createElement('td');
            tdBrowser.className = 'px-3 py-2 text-sm text-gray-500'; tdBrowser.textContent = v.browser;

            var tdTime = document.createElement('td');
            tdTime.className = 'px-3 py-2 text-sm text-gray-500'; tdTime.textContent = v.time.substring(11, 19);

            tr.appendChild(tdIP); tr.appendChild(tdPage); tr.appendChild(tdBrowser); tr.appendChild(tdTime);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Realtime error:', e); }
}

// 모달
function showBlockModal(ip) {
    document.getElementById('blockIP').value = ip || '';
    document.getElementById('blockModal').classList.remove('hidden');
}
function hideBlockModal() {
    document.getElementById('blockModal').classList.add('hidden');
}

function quickBlock(ip) {
    if (confirm(ip + '를 24시간 차단할까요?')) {
        blockIPRequest(ip, '과다 접속', 24);
    }
}

async function blockIP() {
    var ip = document.getElementById('blockIP').value;
    var reason = document.getElementById('blockReason').value;
    var hours = document.getElementById('blockDuration').value;
    if (!ip) { alert('IP 주소를 입력하세요.'); return; }
    await blockIPRequest(ip, reason, hours);
    hideBlockModal();
}

async function blockIPRequest(ip, reason, hours) {
    var formData = new FormData();
    formData.append('ip', ip);
    formData.append('reason', reason);
    formData.append('hours', hours);
    try {
        var res = await fetch(API + '?type=block', { method: 'POST', body: formData });
        var data = await res.json();
        if (data.success) { showToast('차단되었습니다.', 'success'); loadSuspicious(); loadBlocked(); }
    } catch (e) { console.error('Block error:', e); }
}

async function unblockIP(ip) {
    if (!confirm(ip + ' 차단을 해제할까요?')) return;
    var formData = new FormData();
    formData.append('ip', ip);
    try {
        var res = await fetch(API + '?type=unblock', { method: 'POST', body: formData });
        var data = await res.json();
        if (data.success) { showToast('차단이 해제되었습니다.', 'success'); loadBlocked(); }
    } catch (e) { console.error('Unblock error:', e); }
}

function refreshAll() {
    loadSummary();
    loadHourlyChart();
    loadRefererChart();
    loadTopPages();
}

refreshAll();

setInterval(function() {
    loadSummary();
    var activeTab = document.querySelector('.tab-btn.text-blue-600');
    if (activeTab && activeTab.getAttribute('data-tab') === 'realtime') loadRealtime();
}, 30000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
