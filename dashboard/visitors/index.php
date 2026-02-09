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
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="behavior">행동 분석</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="suspicious">의심 활동</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="blocked">차단 목록</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="realtime">실시간</button>
        </div>

        <!-- ═══════ 개요 탭 ═══════ -->
        <div id="tab-overview">
            <!-- 요약 카드 6개 -->
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-4" id="summaryCards">
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
                <div class="bg-white rounded-lg shadow p-4 animate-pulse"><div class="h-14 bg-gray-200 rounded"></div></div>
            </div>

            <!-- 일별 추이 (30일) -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900">일별 방문 추이</h3>
                    <select id="dailyPeriod" class="px-2 py-1 text-xs border border-gray-300 rounded">
                        <option value="7">7일</option>
                        <option value="30" selected>30일</option>
                        <option value="90">90일</option>
                    </select>
                </div>
                <div class="relative" style="height: 200px;"><canvas id="dailyChart"></canvas></div>
            </div>

            <!-- 시간대별 + 유입경로 -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">시간대별 방문자</h3>
                    <div class="relative" style="height: 200px;"><canvas id="hourlyChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">유입 경로</h3>
                    <div class="relative" style="height: 200px;"><canvas id="refererChart"></canvas></div>
                </div>
            </div>

            <!-- 기기/브라우저/OS + 신규vs재방문 -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">기기 유형</h3>
                    <div class="relative" style="height: 180px;"><canvas id="deviceChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">브라우저</h3>
                    <div class="relative" style="height: 180px;"><canvas id="browserChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">운영체제</h3>
                    <div class="relative" style="height: 180px;"><canvas id="osChart"></canvas></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">신규 vs 재방문</h3>
                    <div class="relative" style="height: 180px;"><canvas id="newReturnChart"></canvas></div>
                </div>
            </div>

            <!-- 전환율 -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">방문 → 주문 전환율 (30일)</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3" id="conversionCards"></div>
                <div class="relative" style="height: 180px;"><canvas id="conversionChart"></canvas></div>
            </div>

            <!-- 인기 페이지 -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">오늘 인기 페이지</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500" style="width:100px">조회수</th>
                        </tr></thead>
                        <tbody id="topPagesBody" class="divide-y divide-gray-100">
                            <tr><td colspan="2" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════ 행동 분석 탭 ═══════ -->
        <div id="tab-behavior" style="display:none;">
            <!-- 진입/이탈 페이지 -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">진입 페이지 (랜딩)</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500" style="width:80px">세션수</th>
                        </tr></thead>
                        <tbody id="entryPagesBody" class="divide-y divide-gray-100">
                            <tr><td colspan="2" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">이탈 페이지</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500" style="width:80px">세션수</th>
                        </tr></thead>
                        <tbody id="exitPagesBody" class="divide-y divide-gray-100">
                            <tr><td colspan="2" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════ 의심 활동 탭 ═══════ -->
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
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">접속 횟수</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지 수</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">마지막 접속</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">상태</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">조치</th>
                        </tr></thead>
                        <tbody id="suspiciousBody" class="divide-y divide-gray-100">
                            <tr><td colspan="6" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-gray-400">* 1시간 내 동일 IP에서의 접속 횟수 기준 (봇 제외)</p>
            </div>
        </div>

        <!-- ═══════ 차단 목록 탭 ═══════ -->
        <div id="tab-blocked" style="display:none;">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">차단된 IP 목록</h3>
                    <button onclick="showBlockModal()" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">+ IP 차단</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">사유</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">차단 일시</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">만료</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">조치</th>
                        </tr></thead>
                        <tbody id="blockedBody" class="divide-y divide-gray-100">
                            <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════ 실시간 탭 ═══════ -->
        <div id="tab-realtime" style="display:none;">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">실시간 방문자 (최근 5분)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">IP 주소</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">페이지</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">브라우저</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">시간</th>
                        </tr></thead>
                        <tbody id="realtimeBody" class="divide-y divide-gray-100">
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-400 text-sm">불러오는 중...</td></tr>
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
var charts = {};
var API = '/dashboard/api/visitor_stats.php';
var COLORS = ['#3b82f6','#ef4444','#f59e0b','#10b981','#f97316','#06b6d4','#8b5cf6','#ec4899'];

// ─── 유틸 ───
function makeCard(label, value, sub) {
    var card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow p-3';
    var lbl = document.createElement('div');
    lbl.className = 'text-xs text-gray-500 mb-1'; lbl.textContent = label;
    var val = document.createElement('div');
    val.className = 'text-xl font-bold text-gray-900'; val.textContent = value;
    card.appendChild(lbl); card.appendChild(val);
    if (sub) { var s = document.createElement('div'); s.className = 'text-xs text-gray-400 mt-0.5'; s.textContent = sub; card.appendChild(s); }
    return card;
}

function makeGrowthCard(label, value, sub, growth) {
    var card = makeCard(label, value, '');
    if (sub || growth !== undefined) {
        var s = document.createElement('div'); s.className = 'text-xs text-gray-400 mt-0.5';
        if (sub) s.textContent = sub;
        if (growth !== undefined) {
            var badge = document.createElement('span');
            badge.className = 'ml-1 px-1.5 py-0.5 rounded-full text-xs font-medium ' + (growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700');
            badge.textContent = (growth >= 0 ? '▲' : '▼') + Math.abs(growth) + '%';
            s.appendChild(badge);
        }
        card.appendChild(s);
    }
    return card;
}

function formatDuration(sec) {
    if (sec < 60) return sec + '초';
    if (sec < 3600) return Math.floor(sec / 60) + '분 ' + (sec % 60) + '초';
    return Math.floor(sec / 3600) + '시간 ' + Math.floor((sec % 3600) / 60) + '분';
}

function makeDoughnut(canvasId, labels, data) {
    if (charts[canvasId]) charts[canvasId].destroy();
    charts[canvasId] = new Chart(document.getElementById(canvasId).getContext('2d'), {
        type: 'doughnut',
        data: { labels: labels, datasets: [{ data: data, backgroundColor: COLORS, borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } } }
        }
    });
}

function fillTable(tbodyId, rows, colSpan) {
    var tbody = document.getElementById(tbodyId);
    tbody.textContent = '';
    if (rows.length === 0) {
        var tr = document.createElement('tr'); var td = document.createElement('td');
        td.colSpan = colSpan; td.className = 'px-3 py-4 text-center text-gray-400 text-sm'; td.textContent = '데이터 없음';
        tr.appendChild(td); tbody.appendChild(tr); return;
    }
    rows.forEach(function(r) {
        var tr = document.createElement('tr'); tr.className = 'hover:bg-gray-50';
        r.forEach(function(cell) {
            var td = document.createElement('td');
            td.className = cell.cls; td.textContent = cell.text;
            if (cell.title) td.title = cell.title;
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

// ─── 탭 전환 ───
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
        if (tab === 'behavior') loadBehavior();
        if (tab === 'suspicious') loadSuspicious();
        if (tab === 'blocked') loadBlocked();
        if (tab === 'realtime') loadRealtime();
    });
});

document.getElementById('thresholdSelect').addEventListener('change', loadSuspicious);
document.getElementById('dailyPeriod').addEventListener('change', loadDailyChart);

// ─── 개요 탭 데이터 ───

async function loadSummary() {
    try {
        var res = await fetch(API + '?type=summary');
        var data = await res.json();
        var res2 = await fetch(API + '?type=sessions');
        var sess = await res2.json();

        document.getElementById('activeCount').textContent = formatNumber(data.realtime.active);
        document.getElementById('botCount').textContent = formatNumber(data.realtime.bots_today);

        var c = document.getElementById('summaryCards');
        c.textContent = '';
        c.appendChild(makeGrowthCard('오늘 방문', formatNumber(data.today.visits), '순 방문자: ' + formatNumber(data.today.unique_visitors) + '명', data.today.growth));
        c.appendChild(makeCard('오늘 세션', formatNumber(data.today.sessions), ''));
        c.appendChild(makeCard('평균 체류시간', formatDuration(sess.avg_duration), '페이지뷰: ' + sess.avg_page_views + '회'));
        c.appendChild(makeCard('이탈률', sess.bounce_rate + '%', sess.bounced_sessions + '/' + sess.total_sessions + ' 세션'));
        c.appendChild(makeCard('이번달 방문', formatNumber(data.month.visits), '순 방문자: ' + formatNumber(data.month.unique_visitors) + '명'));
        c.appendChild(makeCard('전체 누적', formatNumber(data.total.visits), '순 방문자: ' + formatNumber(data.total.unique_visitors) + '명'));
    } catch (e) { console.error('Summary error:', e); }
}

async function loadDailyChart() {
    var days = document.getElementById('dailyPeriod').value;
    try {
        var res = await fetch(API + '?type=daily&days=' + days);
        var data = await res.json();
        if (charts.dailyChart) charts.dailyChart.destroy();
        charts.dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(function(d) { return d.day.substring(5); }),
                datasets: [
                    { label: '방문수', data: data.map(function(d) { return d.visits; }), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.3, pointRadius: 2 },
                    { label: '순 방문자', data: data.map(function(d) { return d.unique; }), borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.3, pointRadius: 2, borderDash: [4,2] }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } }
            }
        });
    } catch (e) { console.error('Daily chart error:', e); }
}

async function loadHourlyChart() {
    try {
        var res = await fetch(API + '?type=hourly');
        var data = await res.json();
        if (charts.hourlyChart) charts.hourlyChart.destroy();
        charts.hourlyChart = new Chart(document.getElementById('hourlyChart').getContext('2d'), {
            type: 'bar',
            data: { labels: Array.from({length:24}, function(_,i){return i+'시';}), datasets: [{label:'방문수', data: data.map(function(d){return d.visits;}), backgroundColor:'#3b82f6', borderRadius:3}] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{color:'#f3f4f6'}},x:{grid:{display:false}}} }
        });
    } catch (e) { console.error('Hourly error:', e); }
}

async function loadRefererChart() {
    try {
        var res = await fetch(API + '?type=referers');
        var data = await res.json();
        makeDoughnut('refererChart', data.map(function(d){return d.source;}), data.map(function(d){return d.visits;}));
    } catch (e) { console.error('Referer error:', e); }
}

async function loadDeviceCharts() {
    try {
        var res = await fetch(API + '?type=devices');
        var data = await res.json();
        makeDoughnut('deviceChart', data.devices.map(function(d){return d.name;}), data.devices.map(function(d){return d.count;}));
        makeDoughnut('browserChart', data.browsers.map(function(d){return d.name;}), data.browsers.map(function(d){return d.count;}));
        makeDoughnut('osChart', data.os.map(function(d){return d.name;}), data.os.map(function(d){return d.count;}));
    } catch (e) { console.error('Device error:', e); }
}

async function loadNewReturning() {
    try {
        var res = await fetch(API + '?type=new_returning');
        var data = await res.json();
        makeDoughnut('newReturnChart', ['신규 방문자', '재방문자'], [data.today.new, data.today.returning]);
    } catch (e) { console.error('New/Return error:', e); }
}

async function loadConversion() {
    try {
        var res = await fetch(API + '?type=conversion');
        var data = await res.json();

        var cc = document.getElementById('conversionCards');
        cc.textContent = '';
        cc.appendChild(makeCard('오늘 전환율', data.today.rate + '%', data.today.orders + '건 / ' + data.today.visitors + '명'));
        cc.appendChild(makeCard('이번달 전환율', data.month.rate + '%', data.month.orders + '건 / ' + data.month.visitors + '명'));
        cc.appendChild(makeCard('오늘 주문', formatNumber(data.today.orders) + '건', ''));
        cc.appendChild(makeCard('이번달 주문', formatNumber(data.month.orders) + '건', ''));

        if (charts.conversionChart) charts.conversionChart.destroy();
        charts.conversionChart = new Chart(document.getElementById('conversionChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: data.trend.map(function(d){return d.day.substring(5);}),
                datasets: [
                    { label: '전환율(%)', data: data.trend.map(function(d){return d.rate;}), borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', fill: true, tension: 0.3, pointRadius: 2, yAxisID: 'y' },
                    { label: '주문수', data: data.trend.map(function(d){return d.orders;}), borderColor: '#ef4444', backgroundColor: 'transparent', tension: 0.3, pointRadius: 2, borderDash: [4,2], yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, position: 'left', grid: { color: '#f3f4f6' }, ticks: { callback: function(v){return v+'%';} } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) { console.error('Conversion error:', e); }
}

async function loadTopPages() {
    try {
        var res = await fetch(API + '?type=pages');
        var data = await res.json();
        fillTable('topPagesBody', data.map(function(p) {
            var short = p.page.length > 60 ? p.page.substring(0,60) + '...' : p.page;
            return [
                { cls: 'px-3 py-2 text-sm text-gray-700', text: short, title: p.page },
                { cls: 'px-3 py-2 text-sm text-gray-900 text-right', text: formatNumber(p.views) }
            ];
        }), 2);
    } catch (e) { console.error('Pages error:', e); }
}

// ─── 행동 분석 탭 ───

async function loadBehavior() {
    try {
        var res = await fetch(API + '?type=entry_exit');
        var data = await res.json();
        fillTable('entryPagesBody', data.entry.map(function(p) {
            var short = p.page.length > 50 ? p.page.substring(0,50) + '...' : p.page;
            return [
                { cls: 'px-3 py-2 text-sm text-gray-700', text: short, title: p.page },
                { cls: 'px-3 py-2 text-sm text-gray-900 text-right', text: formatNumber(p.count) }
            ];
        }), 2);
        fillTable('exitPagesBody', data.exit.map(function(p) {
            var short = p.page.length > 50 ? p.page.substring(0,50) + '...' : p.page;
            return [
                { cls: 'px-3 py-2 text-sm text-gray-700', text: short, title: p.page },
                { cls: 'px-3 py-2 text-sm text-gray-900 text-right', text: formatNumber(p.count) }
            ];
        }), 2);
    } catch (e) { console.error('Behavior error:', e); }
}

// ─── 의심/차단/실시간 탭 (기존) ───

async function loadSuspicious() {
    var threshold = document.getElementById('thresholdSelect').value;
    try {
        var res = await fetch(API + '?type=suspicious&threshold=' + threshold);
        var data = await res.json();
        var tbody = document.getElementById('suspiciousBody'); tbody.textContent = '';
        if (data.length === 0) { fillTable('suspiciousBody', [], 6); return; }
        data.forEach(function(s) {
            var tr = document.createElement('tr'); tr.className = 'hover:bg-gray-50';
            var cells = [
                {cls:'px-3 py-2 text-sm font-medium text-gray-900', text:s.ip},
                {cls:'px-3 py-2 text-sm text-gray-700', text:formatNumber(s.count)+'회'},
                {cls:'px-3 py-2 text-sm text-gray-700', text:s.unique_pages+'개'},
                {cls:'px-3 py-2 text-sm text-gray-500', text:s.last_visit.substring(11,19)}
            ];
            cells.forEach(function(c) { var td=document.createElement('td'); td.className=c.cls; td.textContent=c.text; tr.appendChild(td); });
            var tdSt = document.createElement('td'); tdSt.className = 'px-3 py-2';
            var badge = document.createElement('span');
            var stMap = {critical:['bg-red-100 text-red-700','차단 권장'],warning:['bg-yellow-100 text-yellow-700','모니터링'],watch:['bg-blue-100 text-blue-700','감시']};
            var st = stMap[s.status]||stMap.watch;
            badge.className='px-2 py-0.5 text-xs font-medium rounded-full '+st[0]; badge.textContent=st[1];
            tdSt.appendChild(badge); tr.appendChild(tdSt);
            var tdA = document.createElement('td'); tdA.className='px-3 py-2';
            var btn = document.createElement('button'); btn.className='px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700'; btn.textContent='차단';
            btn.setAttribute('data-ip',s.ip); btn.addEventListener('click',function(){quickBlock(this.getAttribute('data-ip'));}); tdA.appendChild(btn); tr.appendChild(tdA);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Suspicious error:', e); }
}

async function loadBlocked() {
    try {
        var res = await fetch(API + '?type=blocked');
        var data = await res.json();
        var tbody = document.getElementById('blockedBody'); tbody.textContent = '';
        if (data.length === 0) { fillTable('blockedBody', [], 5); return; }
        data.forEach(function(b) {
            var tr = document.createElement('tr'); tr.className = 'hover:bg-gray-50';
            [{cls:'px-3 py-2 text-sm font-medium text-gray-900',text:b.ip},{cls:'px-3 py-2 text-sm text-gray-700',text:b.reason||'-'},{cls:'px-3 py-2 text-sm text-gray-500',text:b.blocked_at},{cls:'px-3 py-2 text-sm text-gray-500',text:b.is_permanent?'영구':(b.expires_at||'-')}]
            .forEach(function(c){var td=document.createElement('td');td.className=c.cls;td.textContent=c.text;tr.appendChild(td);});
            var tdA=document.createElement('td');tdA.className='px-3 py-2';
            var btn=document.createElement('button');btn.className='px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100';btn.textContent='해제';
            btn.setAttribute('data-ip',b.ip);btn.addEventListener('click',function(){unblockIP(this.getAttribute('data-ip'));});tdA.appendChild(btn);tr.appendChild(tdA);
            tbody.appendChild(tr);
        });
    } catch (e) { console.error('Blocked error:', e); }
}

async function loadRealtime() {
    try {
        var res = await fetch(API + '?type=realtime');
        var data = await res.json();
        fillTable('realtimeBody', data.map(function(v) {
            var short = v.page.length > 50 ? v.page.substring(0,50) + '...' : v.page;
            return [
                {cls:'px-3 py-2 text-sm text-gray-900',text:v.ip},
                {cls:'px-3 py-2 text-sm text-gray-700',text:short,title:v.page},
                {cls:'px-3 py-2 text-sm text-gray-500',text:v.browser},
                {cls:'px-3 py-2 text-sm text-gray-500',text:v.time.substring(11,19)}
            ];
        }), 4);
    } catch (e) { console.error('Realtime error:', e); }
}

// ─── 모달/차단 ───
function showBlockModal(ip){document.getElementById('blockIP').value=ip||'';document.getElementById('blockModal').classList.remove('hidden');}
function hideBlockModal(){document.getElementById('blockModal').classList.add('hidden');}
function quickBlock(ip){if(confirm(ip+'를 24시간 차단할까요?'))blockIPRequest(ip,'과다 접속',24);}
async function blockIP(){var ip=document.getElementById('blockIP').value;if(!ip){alert('IP 주소를 입력하세요.');return;}await blockIPRequest(ip,document.getElementById('blockReason').value,document.getElementById('blockDuration').value);hideBlockModal();}
async function blockIPRequest(ip,reason,hours){var f=new FormData();f.append('ip',ip);f.append('reason',reason);f.append('hours',hours);try{var r=await fetch(API+'?type=block',{method:'POST',body:f});var d=await r.json();if(d.success){showToast('차단되었습니다.','success');loadSuspicious();loadBlocked();}}catch(e){console.error(e);}}
async function unblockIP(ip){if(!confirm(ip+' 차단을 해제할까요?'))return;var f=new FormData();f.append('ip',ip);try{var r=await fetch(API+'?type=unblock',{method:'POST',body:f});var d=await r.json();if(d.success){showToast('해제되었습니다.','success');loadBlocked();}}catch(e){console.error(e);}}

// ─── 초기화 ───
function refreshAll() {
    loadSummary();
    loadDailyChart();
    loadHourlyChart();
    loadRefererChart();
    loadDeviceCharts();
    loadNewReturning();
    loadConversion();
    loadTopPages();
}
refreshAll();
setInterval(function(){
    loadSummary();
    var active = document.querySelector('.tab-btn.text-blue-600');
    if(active && active.getAttribute('data-tab')==='realtime') loadRealtime();
}, 30000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
