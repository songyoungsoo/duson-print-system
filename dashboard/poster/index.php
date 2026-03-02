<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

        <!-- 헤더 -->
        <div class="flex items-center gap-3 mb-4">
            <h1 class="text-xl font-bold text-gray-900">🎨 AI 포스터 생성</h1>
            <span class="text-sm text-gray-500">AI가 고객 사업체 전단지 디자인을 자동 생성합니다</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

            <!-- 왼쪽: 입력 폼 (3/5) -->
            <div class="lg:col-span-3 space-y-3">
                <div class="bg-white rounded-lg shadow p-5" id="posterFormArea">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                        사업체 정보 입력
                    </h3>

                    <!-- 업종 -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">업종 <span class="text-red-500">*</span></label>
                        <select id="posterIndustry" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">업종을 선택하세요</option>
                            <option value="korean">한식</option>
                            <option value="japanese">일식</option>
                            <option value="chinese">중식</option>
                            <option value="western">양식/카페</option>
                            <option value="chicken">치킨/호프</option>
                            <option value="academy">학원</option>
                            <option value="fitness">피트니스</option>
                            <option value="beauty">뷰티</option>
                            <option value="general">일반</option>
                        </select>
                    </div>

                    <!-- 상호명 + 전화번호 -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">상호명 <span class="text-red-500">*</span></label>
                            <input type="text" id="posterBusinessName" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="예: 맛있는 한식당">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">전화번호 <span class="text-red-500">*</span></label>
                            <input type="text" id="posterPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="02-1234-5678">
                        </div>
                    </div>

                    <!-- 주소 -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">주소</label>
                        <input type="text" id="posterAddress" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="사업장 주소">
                    </div>

                    <!-- 메뉴/서비스 -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">메뉴/서비스</label>
                        <textarea id="posterMenuItems" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="메뉴명 - 가격 (한 줄에 하나씩)&#10;예: 김치찌개 - 9,000원&#10;된장찌개 - 9,000원&#10;제육볶음 - 10,000원"></textarea>
                    </div>

                    <!-- 특장점 -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">특장점</label>
                        <textarea id="posterFeatures" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="무료배달, 주차가능, 30년 전통 등"></textarea>
                    </div>

                    <!-- 프로모션 + 영업시간 -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">프로모션</label>
                            <input type="text" id="posterPromotion" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="오픈 기념 20% 할인">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">영업시간</label>
                            <input type="text" id="posterHours" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="매일 11:00~22:00">
                        </div>
                    </div>

                    <!-- 생성 버튼 -->
                    <button type="button" id="btnGeneratePoster" class="w-full px-4 py-3 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                        🚀 포스터 생성 시작
                    </button>
                </div>
            </div>

            <!-- 오른쪽: 진행 상황 + 결과 (2/5) -->
            <div class="lg:col-span-2 space-y-3">

                <!-- 안내 카드 (기본 표시) -->
                <div id="posterGuideArea" class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">사용 안내</h3>
                    <div class="text-xs text-gray-600 space-y-2">
                        <p>1. 왼쪽에 고객 사업체 정보를 입력합니다.</p>
                        <p>2. <strong>업종, 상호명, 전화번호</strong>는 필수입니다.</p>
                        <p>3. 메뉴와 특장점을 자세히 입력할수록 더 나은 결과물이 나옵니다.</p>
                        <p>4. 생성에는 약 <strong>2~5분</strong>이 소요됩니다.</p>
                        <p>5. 완료 후 PDF와 PNG 파일을 다운로드할 수 있습니다.</p>
                    </div>
                    <div class="mt-3 p-2 bg-indigo-50 rounded text-xs text-indigo-700 space-y-1">
                        <p>🔍 <strong>AI 파이프라인:</strong> 시장분석 → 카피라이팅 → 디자인 → 이미지 생성 → PDF</p>
                        <p>💡 <strong>Tip:</strong> 메뉴 입력 시 "메뉴명 - 가격" 형식으로 작성하면 전단지에 가격표가 예쁘게 배치됩니다.</p>
                    </div>
                </div>

                <!-- 진행 영역 -->
                <div id="posterProgressArea" class="hidden bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">생성 진행 중</h3>
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                            <svg class="w-7 h-7 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <p id="posterStageText" class="text-sm font-semibold text-gray-900">준비 중...</p>
                        <p id="posterProgressMsg" class="text-xs text-gray-500 mt-1">잠시만 기다려주세요</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="posterProgressBar" class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                    <p id="posterProgressPercent" class="text-xs text-gray-400 text-right mt-1">0%</p>
                </div>

                <!-- 결과 영역 -->
                <div id="posterResultArea" class="hidden bg-white rounded-lg shadow p-5">
                    <div class="text-center mb-3">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-900">포스터 생성 완료!</p>
                    </div>
                    <div class="mb-3 bg-gray-50 rounded-lg p-2 flex items-center justify-center">
                        <img id="posterPreviewImg" src="" alt="포스터 미리보기" class="max-h-80 rounded shadow">
                    </div>
                    <div class="flex gap-2 mb-2">
                        <a id="posterDownloadPdf" href="#" target="_blank" class="flex-1 px-3 py-2 bg-red-600 text-white text-sm text-center rounded-lg hover:bg-red-700 transition-colors">
                            📥 PDF
                        </a>
                        <a id="posterDownloadPng" href="#" target="_blank" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm text-center rounded-lg hover:bg-blue-700 transition-colors">
                            📥 PNG
                        </a>
                    </div>
                    <button type="button" id="btnPosterNewGenerate" class="w-full px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                        🔄 다시 생성
                    </button>
                </div>

                <!-- 에러 영역 -->
                <div id="posterErrorArea" class="hidden bg-white rounded-lg shadow p-5">
                    <div class="text-center mb-3">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mb-2">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <p class="text-sm font-bold text-red-700">생성 실패</p>
                        <p id="posterErrorMsg" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <button type="button" id="btnPosterRetry" class="w-full px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                        ← 다시 시도
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function() {
    // === DOM References ===
    var formArea = document.getElementById('posterFormArea');
    var guideArea = document.getElementById('posterGuideArea');
    var progressArea = document.getElementById('posterProgressArea');
    var resultArea = document.getElementById('posterResultArea');
    var errorArea = document.getElementById('posterErrorArea');

    var btnGenerate = document.getElementById('btnGeneratePoster');
    var btnNewGenerate = document.getElementById('btnPosterNewGenerate');
    var btnRetry = document.getElementById('btnPosterRetry');

    var progressBar = document.getElementById('posterProgressBar');
    var progressPercent = document.getElementById('posterProgressPercent');
    var stageText = document.getElementById('posterStageText');
    var progressMsg = document.getElementById('posterProgressMsg');
    var errorMsg = document.getElementById('posterErrorMsg');
    var previewImg = document.getElementById('posterPreviewImg');
    var downloadPdf = document.getElementById('posterDownloadPdf');
    var downloadPng = document.getElementById('posterDownloadPng');

    // === Area Controls ===
    function showRightArea(area) {
        guideArea.classList.add('hidden');
        progressArea.classList.add('hidden');
        resultArea.classList.add('hidden');
        errorArea.classList.add('hidden');
        area.classList.remove('hidden');
    }

    function resetToForm() {
        showRightArea(guideArea);
        btnGenerate.disabled = false;
        btnGenerate.textContent = '🚀 포스터 생성 시작';
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        stageText.textContent = '\uc900\ube44 \uc911...';
        progressMsg.textContent = '\uc7a0\uc2dc\ub9cc \uae30\ub2e4\ub824\uc8fc\uc138\uc694';
        downloadPdf.style.display = '';
        downloadPng.style.display = '';
    }

    btnNewGenerate.addEventListener('click', resetToForm);
    btnRetry.addEventListener('click', resetToForm);

    // === Stage label map ===
    var stageLabels = {
        'init': '초기화',
        'collect': '📋 데이터 수집',
        'research': '🔍 시장 분석',
        'copywrite': '✍️ AI 카피라이팅',
        'design': '🎨 디자인 프롬프트',
        'generate_front': '🖼️ 앞면 생성',
        'generate_back': '🖼️ 뒷면 생성',
        'assemble': '📄 PDF 생성',
        'preview': '🖼️ 미리보기',
        'complete': '✅ 완료'
    };

    // === SSE Handler ===
    function handleSSEData(data) {
        if (data.type === 'progress') {
            var pct = Math.min(100, Math.max(0, data.progress || 0));
            progressBar.style.width = pct + '%';
            progressPercent.textContent = pct + '%';
            stageText.textContent = stageLabels[data.stage] || data.stage || '';
            progressMsg.textContent = data.message || '';
        } else if (data.type === 'complete') {
            showRightArea(resultArea);
            var pngUrl = data.png_url || '';
            var pdfUrl = data.pdf_url || '';
            var previewUrl = pngUrl || (data.preview_images && data.preview_images[0]) || '';

            if (previewUrl) {
                previewImg.src = previewUrl;
                previewImg.style.display = '';
            } else {
                previewImg.style.display = 'none';
            }

            downloadPdf.href = pdfUrl || '#';
            downloadPng.href = pngUrl || '#';
            if (!pdfUrl) downloadPdf.style.display = 'none';
            if (!pngUrl) downloadPng.style.display = 'none';

            btnGenerate.disabled = false;
            btnGenerate.textContent = '🚀 포스터 생성 시작';
        } else if (data.type === 'error') {
            showRightArea(errorArea);
            errorMsg.textContent = data.message || '알 수 없는 오류가 발생했습니다.';
            btnGenerate.disabled = false;
            btnGenerate.textContent = '🚀 포스터 생성 시작';
        }
    }

    // === Generate Poster ===
    btnGenerate.addEventListener('click', async function() {
        // Validate required fields
        var industry = document.getElementById('posterIndustry').value;
        var businessName = document.getElementById('posterBusinessName').value.trim();
        var phone = document.getElementById('posterPhone').value.trim();

        if (!industry) { alert('업종을 선택해주세요.'); return; }
        if (!businessName) { alert('상호명을 입력해주세요.'); return; }
        if (!phone) { alert('전화번호를 입력해주세요.'); return; }

        // Collect form data
        var payload = {
            industry: industry,
            business_name: businessName,
            phone: phone,
            address: document.getElementById('posterAddress').value.trim(),
            menu_items: document.getElementById('posterMenuItems').value.trim(),
            features: document.getElementById('posterFeatures').value.trim(),
            promotion: document.getElementById('posterPromotion').value.trim(),
            hours: document.getElementById('posterHours').value.trim()
        };

        // Switch to progress view
        btnGenerate.disabled = true;
        btnGenerate.textContent = '생성 중...';
        showRightArea(progressArea);
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';

        try {
            var response = await fetch('/flyer/poster/api/generate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var reader = response.body.getReader();
            var decoder = new TextDecoder();
            var buffer = '';

            function processChunk(result) {
                if (result.done) {
                    if (!progressArea.classList.contains('hidden') && resultArea.classList.contains('hidden')) {
                        showRightArea(errorArea);
                        errorMsg.textContent = '스트림이 예상치 못하게 종료되었습니다.';
                        btnGenerate.disabled = false;
                        btnGenerate.textContent = '🚀 포스터 생성 시작';
                    }
                    return;
                }

                buffer += decoder.decode(result.value, { stream: true });
                var lines = buffer.split('\n');
                buffer = lines.pop();

                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (line.indexOf('data: ') === 0) {
                        try {
                            var data = JSON.parse(line.substring(6));
                            handleSSEData(data);
                        } catch (parseErr) {
                            // skip malformed JSON
                        }
                    }
                }

                return reader.read().then(processChunk);
            }

            reader.read().then(processChunk);

        } catch (fetchErr) {
            showRightArea(errorArea);
            errorMsg.textContent = '서버 연결 오류: ' + fetchErr.message;
            btnGenerate.disabled = false;
            btnGenerate.textContent = '🚀 포스터 생성 시작';
        }
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
