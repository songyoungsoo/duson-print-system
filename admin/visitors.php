<?php
/**
 * 방문자 분석 대시보드
 * Google Analytics 스타일
 */
session_start();
require_once __DIR__ . '/../db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>방문자 분석 - 두손기획</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f5f5f5;
            color: #202124;
        }

        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 500;
            color: #5f6368;
        }

        .header h1::before { content: '👥 '; }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #dadce0;
            background: #fff;
            color: #5f6368;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn:hover { background: #f1f3f4; }
        .btn-primary { background: #1a73e8; color: #fff; border-color: #1a73e8; }
        .btn-danger { background: #d93025; color: #fff; border-color: #d93025; }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }

        /* 실시간 표시 */
        .realtime-bar {
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            font-size: 14px;
        }

        .realtime-bar .pulse {
            width: 10px;
            height: 10px;
            background: #34a853;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .realtime-bar .count {
            font-size: 24px;
            font-weight: 700;
        }

        /* 카드 */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .card .label {
            font-size: 13px;
            color: #5f6368;
            margin-bottom: 8px;
        }

        .card .value {
            font-size: 32px;
            font-weight: 500;
        }

        .card .sub {
            font-size: 12px;
            color: #5f6368;
            margin-top: 4px;
        }

        .growth { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 500; }
        .growth.up { background: #e6f4ea; color: #1e8e3e; }
        .growth.down { background: #fce8e6; color: #d93025; }

        /* 차트 그리드 */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .charts-row { grid-template-columns: 1fr; }
        }

        .chart-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 16px;
            color: #202124;
        }

        .chart-container {
            height: 250px;
            position: relative;
        }

        /* 테이블 */
        .table-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .table-card h3 {
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        th {
            background: #f8f9fa;
            font-weight: 500;
            color: #5f6368;
        }

        tr:hover { background: #f8f9fa; }

        /* 상태 배지 */
        .status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status.critical { background: #fce8e6; color: #d93025; }
        .status.warning { background: #fef7e0; color: #f9ab00; }
        .status.watch { background: #e8f0fe; color: #1a73e8; }
        .status.blocked { background: #5f6368; color: #fff; }

        /* 모달 */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active { display: flex; }

        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            width: 400px;
            max-width: 90%;
        }

        .modal-content h4 {
            margin-bottom: 16px;
            font-size: 18px;
        }

        .modal-content input, .modal-content select {
            width: 100%;
            padding: 10px;
            border: 1px solid #dadce0;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 0;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            color: #5f6368;
            font-size: 14px;
        }

        .tab:hover { color: #1a73e8; }
        .tab.active { color: #1a73e8; border-bottom-color: #1a73e8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>방문자 분석</h1>
        <div class="header-actions">
            <button class="btn" onclick="refreshAll()">🔄 새로고침</button>
            <a href="/admin/dashboard.php" class="btn">📊 주문 통계</a>
            <a href="/admin/mlangprintauto/admin.php" class="btn">← 관리자</a>
        </div>
    </div>

    <!-- 실시간 표시 바 -->
    <div class="realtime-bar">
        <div class="pulse"></div>
        <span>현재 접속자</span>
        <span class="count" id="activeCount">-</span>
        <span style="margin-left: auto;">오늘 봇 방문: <strong id="botCount">-</strong></span>
    </div>

    <div class="container">
        <!-- 탭 -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('overview')">📊 개요</div>
            <div class="tab" onclick="showTab('suspicious')">⚠️ 의심 활동</div>
            <div class="tab" onclick="showTab('blocked')">🚫 차단 목록</div>
            <div class="tab" onclick="showTab('realtime')">🔴 실시간</div>
        </div>

        <!-- 개요 탭 -->
        <div id="tab-overview">
            <!-- 요약 카드 -->
            <div class="summary-grid" id="summaryCards"></div>

            <!-- 차트 -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3>📈 시간대별 방문자</h3>
                    <div class="chart-container">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>🔗 유입 경로</h3>
                    <div class="chart-container">
                        <canvas id="refererChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 인기 페이지 -->
            <div class="table-card">
                <h3>📄 오늘 인기 페이지</h3>
                <table id="topPagesTable">
                    <thead>
                        <tr><th>페이지</th><th style="width:100px;text-align:right">조회수</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- 의심 활동 탭 -->
        <div id="tab-suspicious" style="display:none;">
            <div class="table-card">
                <h3>
                    <span>⚠️ 1시간 내 과다 접속 IP</span>
                    <select id="thresholdSelect" onchange="loadSuspicious()" style="width:auto;padding:6px 12px;">
                        <option value="50">50회 이상</option>
                        <option value="100" selected>100회 이상</option>
                        <option value="200">200회 이상</option>
                    </select>
                </h3>
                <table id="suspiciousTable">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>접속 횟수</th>
                            <th>방문 페이지 수</th>
                            <th>마지막 접속</th>
                            <th>상태</th>
                            <th>조치</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <p style="margin-top:12px;color:#5f6368;font-size:13px;">
                    ※ 1시간 내 동일 IP에서의 접속 횟수 기준 (봇 제외)
                </p>
            </div>
        </div>

        <!-- 차단 목록 탭 -->
        <div id="tab-blocked" style="display:none;">
            <div class="table-card">
                <h3>
                    <span>🚫 차단된 IP 목록</span>
                    <button class="btn btn-primary" onclick="showBlockModal()">+ IP 차단</button>
                </h3>
                <table id="blockedTable">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>사유</th>
                            <th>차단 일시</th>
                            <th>만료</th>
                            <th>조치</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- 실시간 탭 -->
        <div id="tab-realtime" style="display:none;">
            <div class="table-card">
                <h3>🔴 실시간 방문자 (최근 5분)</h3>
                <table id="realtimeTable">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>페이지</th>
                            <th>브라우저</th>
                            <th>시간</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- IP 차단 모달 -->
    <div class="modal" id="blockModal">
        <div class="modal-content">
            <h4>🚫 IP 차단</h4>
            <input type="text" id="blockIP" placeholder="IP 주소 (예: 123.456.789.0)">
            <input type="text" id="blockReason" placeholder="차단 사유">
            <select id="blockDuration">
                <option value="1">1시간</option>
                <option value="24" selected>24시간 (1일)</option>
                <option value="168">7일</option>
                <option value="720">30일</option>
                <option value="0">영구 차단</option>
            </select>
            <div class="modal-actions">
                <button class="btn" onclick="hideBlockModal()">취소</button>
                <button class="btn btn-danger" onclick="blockIP()">차단</button>
            </div>
        </div>
    </div>

    <script>
        let hourlyChart, refererChart;

        // 숫자 포맷
        function formatNumber(num) {
            return new Intl.NumberFormat('ko-KR').format(num);
        }

        // 탭 전환
        function showTab(tab) {
            document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`.tabs .tab[onclick*="${tab}"]`).classList.add('active');

            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = 'none');
            document.getElementById('tab-' + tab).style.display = 'block';

            if (tab === 'suspicious') loadSuspicious();
            if (tab === 'blocked') loadBlocked();
            if (tab === 'realtime') loadRealtime();
        }

        // 요약 로드
        async function loadSummary() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=summary');
                const data = await res.json();

                document.getElementById('activeCount').textContent = data.realtime.active;
                document.getElementById('botCount').textContent = formatNumber(data.realtime.bots_today);

                const growthClass = data.today.growth >= 0 ? 'up' : 'down';
                const growthIcon = data.today.growth >= 0 ? '▲' : '▼';

                document.getElementById('summaryCards').innerHTML = `
                    <div class="card">
                        <div class="label">오늘 방문</div>
                        <div class="value">${formatNumber(data.today.visits)}</div>
                        <div class="sub">순 방문자: ${formatNumber(data.today.unique_visitors)}명
                            <span class="growth ${growthClass}">${growthIcon} ${Math.abs(data.today.growth)}%</span>
                        </div>
                    </div>
                    <div class="card">
                        <div class="label">오늘 세션</div>
                        <div class="value">${formatNumber(data.today.sessions)}</div>
                    </div>
                    <div class="card">
                        <div class="label">이번달 방문</div>
                        <div class="value">${formatNumber(data.month.visits)}</div>
                        <div class="sub">순 방문자: ${formatNumber(data.month.unique_visitors)}명</div>
                    </div>
                    <div class="card">
                        <div class="label">전체 누적</div>
                        <div class="value">${formatNumber(data.total.visits)}</div>
                        <div class="sub">순 방문자: ${formatNumber(data.total.unique_visitors)}명</div>
                    </div>
                `;
            } catch (e) {
                console.error('Summary error:', e);
            }
        }

        // 시간대별 차트
        async function loadHourlyChart() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=hourly');
                const data = await res.json();

                const labels = Array.from({length: 24}, (_, i) => `${i}시`);
                const visits = data.map(d => d.visits);

                if (hourlyChart) hourlyChart.destroy();

                hourlyChart = new Chart(document.getElementById('hourlyChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '방문수',
                            data: visits,
                            backgroundColor: '#4285f4',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f3f4' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            } catch (e) {
                console.error('Hourly chart error:', e);
            }
        }

        // 유입 경로 차트
        async function loadRefererChart() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=referers');
                const data = await res.json();

                const colors = ['#4285f4', '#ea4335', '#fbbc04', '#34a853', '#ff6d01', '#46bdc6', '#7baaf7', '#f07b72'];

                if (refererChart) refererChart.destroy();

                refererChart = new Chart(document.getElementById('refererChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.source),
                        datasets: [{
                            data: data.map(d => d.visits),
                            backgroundColor: colors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 12, padding: 8 } }
                        }
                    }
                });
            } catch (e) {
                console.error('Referer chart error:', e);
            }
        }

        // 인기 페이지
        async function loadTopPages() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=pages');
                const data = await res.json();

                let html = '';
                data.forEach(p => {
                    const shortPage = p.page.length > 60 ? p.page.substring(0, 60) + '...' : p.page;
                    html += `<tr><td title="${p.page}">${shortPage}</td><td style="text-align:right">${formatNumber(p.views)}</td></tr>`;
                });

                document.querySelector('#topPagesTable tbody').innerHTML = html || '<tr><td colspan="2">데이터 없음</td></tr>';
            } catch (e) {
                console.error('Top pages error:', e);
            }
        }

        // 의심 IP
        async function loadSuspicious() {
            const threshold = document.getElementById('thresholdSelect').value;
            try {
                const res = await fetch(`/admin/api/visitor_stats.php?type=suspicious&threshold=${threshold}`);
                const data = await res.json();

                let html = '';
                data.forEach(s => {
                    const statusLabel = s.status === 'critical' ? '🚨 차단 권장' : (s.status === 'warning' ? '⚠️ 모니터링' : '👀 감시');
                    html += `
                        <tr>
                            <td><strong>${s.ip}</strong></td>
                            <td>${formatNumber(s.count)}회</td>
                            <td>${s.unique_pages}개</td>
                            <td>${s.last_visit.substring(11, 19)}</td>
                            <td><span class="status ${s.status}">${statusLabel}</span></td>
                            <td><button class="btn btn-danger" style="padding:4px 8px;font-size:11px;" onclick="quickBlock('${s.ip}')">차단</button></td>
                        </tr>
                    `;
                });

                document.querySelector('#suspiciousTable tbody').innerHTML = html || '<tr><td colspan="6">의심 활동 없음</td></tr>';
            } catch (e) {
                console.error('Suspicious error:', e);
            }
        }

        // 차단 목록
        async function loadBlocked() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=blocked');
                const data = await res.json();

                let html = '';
                data.forEach(b => {
                    const expires = b.is_permanent ? '영구' : (b.expires_at || '-');
                    html += `
                        <tr>
                            <td><strong>${b.ip}</strong></td>
                            <td>${b.reason || '-'}</td>
                            <td>${b.blocked_at}</td>
                            <td>${expires}</td>
                            <td><button class="btn" style="padding:4px 8px;font-size:11px;" onclick="unblockIP('${b.ip}')">해제</button></td>
                        </tr>
                    `;
                });

                document.querySelector('#blockedTable tbody').innerHTML = html || '<tr><td colspan="5">차단된 IP 없음</td></tr>';
            } catch (e) {
                console.error('Blocked error:', e);
            }
        }

        // 실시간 방문자
        async function loadRealtime() {
            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=realtime');
                const data = await res.json();

                let html = '';
                data.forEach(v => {
                    const shortPage = v.page.length > 50 ? v.page.substring(0, 50) + '...' : v.page;
                    html += `
                        <tr>
                            <td>${v.ip}</td>
                            <td title="${v.page}">${shortPage}</td>
                            <td>${v.browser}</td>
                            <td>${v.time.substring(11, 19)}</td>
                        </tr>
                    `;
                });

                document.querySelector('#realtimeTable tbody').innerHTML = html || '<tr><td colspan="4">현재 접속자 없음</td></tr>';
            } catch (e) {
                console.error('Realtime error:', e);
            }
        }

        // 모달
        function showBlockModal(ip = '') {
            document.getElementById('blockIP').value = ip;
            document.getElementById('blockModal').classList.add('active');
        }

        function hideBlockModal() {
            document.getElementById('blockModal').classList.remove('active');
        }

        // 빠른 차단
        function quickBlock(ip) {
            if (confirm(`${ip}를 24시간 차단할까요?`)) {
                blockIPRequest(ip, '과다 접속', 24);
            }
        }

        // IP 차단
        async function blockIP() {
            const ip = document.getElementById('blockIP').value;
            const reason = document.getElementById('blockReason').value;
            const hours = document.getElementById('blockDuration').value;

            if (!ip) {
                alert('IP 주소를 입력하세요.');
                return;
            }

            await blockIPRequest(ip, reason, hours);
            hideBlockModal();
        }

        async function blockIPRequest(ip, reason, hours) {
            const formData = new FormData();
            formData.append('ip', ip);
            formData.append('reason', reason);
            formData.append('hours', hours);

            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=block', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    alert('차단되었습니다.');
                    loadSuspicious();
                    loadBlocked();
                }
            } catch (e) {
                console.error('Block error:', e);
            }
        }

        // IP 차단 해제
        async function unblockIP(ip) {
            if (!confirm(`${ip} 차단을 해제할까요?`)) return;

            const formData = new FormData();
            formData.append('ip', ip);

            try {
                const res = await fetch('/admin/api/visitor_stats.php?type=unblock', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    alert('차단이 해제되었습니다.');
                    loadBlocked();
                }
            } catch (e) {
                console.error('Unblock error:', e);
            }
        }

        // 전체 새로고침
        function refreshAll() {
            loadSummary();
            loadHourlyChart();
            loadRefererChart();
            loadTopPages();
        }

        // 초기 로드
        document.addEventListener('DOMContentLoaded', refreshAll);

        // 30초마다 새로고침
        setInterval(() => {
            loadSummary();
            const activeTab = document.querySelector('.tab.active');
            if (activeTab.textContent.includes('실시간')) loadRealtime();
        }, 30000);
    </script>
</body>
</html>
