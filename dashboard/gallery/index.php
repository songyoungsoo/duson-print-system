<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

        <!-- 개요 뷰 -->
        <div id="overviewView">
            <div class="mb-4">
                <h1 class="text-lg font-bold text-gray-900">갤러리 관리</h1>
                <p class="mt-1 text-xs text-gray-600">제품별 샘플·안전갤러리·주문 이미지 관리</p>
            </div>
            <div id="statsGrid" class="grid grid-cols-2 lg:grid-cols-3 gap-3">
            </div>
        </div>

        <!-- 상세 뷰 -->
        <div id="detailView" class="hidden">
            <div class="flex items-center gap-3 mb-4">
                <button onclick="showOverview()" class="p-1.5 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-bold text-gray-900" id="detailTitle">-</h1>
                    <p class="text-xs text-gray-500" id="detailSubtitle">-</p>
                </div>
                <button onclick="openUploadModal()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    이미지 업로드
                </button>
            </div>

            <!-- 설정 패널 (접이식) -->
            <div id="settingsPanel" class="mb-3 bg-white rounded-lg shadow">
                <button onclick="toggleSettings()" class="w-full flex items-center justify-between px-4 py-2.5 text-left">
                    <span class="text-xs font-semibold text-gray-700">설정</span>
                    <svg id="settingsArrow" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="settingsBody" class="hidden px-4 pb-3 border-t border-gray-100 pt-3">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <span class="text-xs text-gray-600">주문 이미지</span>
                            <div class="relative">
                                <input type="checkbox" id="orderToggle" class="sr-only peer" checked>
                                <div class="w-9 h-5 bg-gray-300 rounded-full peer-checked:bg-blue-600 transition-colors"></div>
                                <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform"></div>
                            </div>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600">기간</span>
                            <input type="date" id="dateFrom" class="text-xs border border-gray-300 rounded px-2 py-1">
                            <span class="text-xs text-gray-400">~</span>
                            <input type="date" id="dateTo" class="text-xs border border-gray-300 rounded px-2 py-1" placeholder="오늘">
                        </div>
                        <button onclick="saveProductSettings()" class="px-3 py-1 bg-gray-800 text-white text-xs rounded hover:bg-gray-700 transition-colors">설정 저장</button>
                    </div>
                </div>
            </div>

            <!-- 탭 -->
            <div class="flex gap-1 mb-3 border-b border-gray-200">
                <button onclick="switchTab('sample')" data-tab="sample" class="tab-btn px-3 py-2 text-xs font-medium border-b-2 border-blue-600 text-blue-600">샘플 이미지</button>
                <button onclick="switchTab('safegallery')" data-tab="safegallery" class="tab-btn px-3 py-2 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">안전 갤러리</button>
                <button onclick="switchTab('order')" data-tab="order" class="tab-btn px-3 py-2 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">주문 이미지</button>
                <button onclick="switchTab('all')" data-tab="all" class="tab-btn px-3 py-2 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">전체</button>
            </div>

            <!-- 이미지 그리드 -->
            <div id="imageGrid" class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
            </div>
            <div id="emptyState" class="hidden text-center py-12 text-gray-400">
                <div class="text-4xl mb-2">🖼️</div>
                <p class="text-sm" id="emptyText">이미지가 없습니다</p>
            </div>
        </div>
    </div>
</main>

<!-- 업로드 모달 -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeUploadModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl w-full max-w-md p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-gray-900">이미지 업로드</h3>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <!-- 업로드 대상 선택 -->
        <div class="flex gap-3 mb-3 p-2 bg-gray-50 rounded-lg">
            <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input type="radio" name="uploadTarget" value="sample" checked class="accent-blue-600">
                <span>샘플 폴더</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input type="radio" name="uploadTarget" value="safegallery" class="accent-blue-600">
                <span>안전 갤러리</span>
            </label>
        </div>
        <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition-colors">
            <div class="text-3xl mb-2">📁</div>
            <p class="text-xs text-gray-600 mb-1">클릭하거나 파일을 드래그하세요</p>
            <p class="text-[10px] text-gray-400">JPG, PNG, GIF, WebP (최대 10MB)</p>
            <input type="file" id="fileInput" multiple accept="image/*" class="hidden">
        </div>
        <div id="fileList" class="mt-3 max-h-40 overflow-y-auto space-y-1"></div>
        <div id="uploadProgress" class="hidden mt-3">
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all" style="width:0%"></div>
            </div>
            <p id="progressText" class="text-[10px] text-gray-500 mt-1 text-center">업로드 중...</p>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button onclick="closeUploadModal()" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">취소</button>
            <button onclick="startUpload()" id="uploadBtn" class="px-3 py-1.5 text-xs text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>업로드</button>
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-xl shadow-2xl w-full max-w-sm p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-2">이미지 삭제</h3>
        <p class="text-xs text-gray-600 mb-4">이 이미지를 삭제하시겠습니까?</p>
        <div id="deletePreview" class="mb-4 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center" style="height:150px">
            <img id="deletePreviewImg" src="" class="max-h-full max-w-full object-contain">
        </div>
        <div class="flex justify-end gap-2">
            <button onclick="closeDeleteModal()" class="px-3 py-1.5 text-xs text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">취소</button>
            <button onclick="confirmDelete()" class="px-3 py-1.5 text-xs text-white bg-red-600 rounded-lg hover:bg-red-700">삭제</button>
        </div>
    </div>
</div>

<!-- 라이트박스 -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl z-10">&times;</button>
    <button onclick="lightboxNav(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-3xl z-10">&#8249;</button>
    <button onclick="lightboxNav(1)" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-3xl z-10">&#8250;</button>
    <img id="lightboxImg" src="" class="max-h-[90vh] max-w-[90vw] object-contain">
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-xs" id="lightboxInfo"></div>
</div>

<script>
(function() {
    var API = '/dashboard/api/gallery.php';
    var currentProduct = null;
    var currentProductName = '';
    var currentTab = 'sample';
    var currentImages = [];
    var lightboxIndex = 0;
    var pendingFiles = [];
    var deleteTarget = null;
    var allSettings = {};
    var settingsOpen = false;

    function el(tag, attrs, children) {
        var node = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function(k) {
                if (k === 'className') node.className = attrs[k];
                else if (k === 'textContent') node.textContent = attrs[k];
                else if (k.indexOf('on') === 0) node.addEventListener(k.slice(2).toLowerCase(), attrs[k]);
                else node.setAttribute(k, attrs[k]);
            });
        }
        if (children) {
            (Array.isArray(children) ? children : [children]).forEach(function(c) {
                if (typeof c === 'string') node.appendChild(document.createTextNode(c));
                else if (c) node.appendChild(c);
            });
        }
        return node;
    }

    // --- 개요 ---
    function loadStats() {
        fetch(API + '?action=stats')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) return;
                var data = res.data;
                allSettings = data.settings || {};
                var grid = document.getElementById('statsGrid');
                grid.textContent = '';
                data.stats.forEach(function(p) {
                    var card = el('div', {className: 'bg-white rounded-lg shadow p-3 cursor-pointer hover:shadow-lg transition-shadow'});
                    card.addEventListener('click', function() { showDetail(p.key, p.name); });

                    if (p.thumbnail) {
                        var img = el('img', {className: 'w-full h-28 object-cover rounded', loading: 'lazy'});
                        img.src = p.thumbnail;
                        img.addEventListener('error', function() {
                            var ph = el('div', {className: 'w-full h-28 bg-gray-100 rounded flex items-center justify-center text-2xl', textContent: '🖼️'});
                            this.parentNode.replaceChild(ph, this);
                        });
                        card.appendChild(img);
                    } else {
                        card.appendChild(el('div', {className: 'w-full h-28 bg-gray-100 rounded flex items-center justify-center text-2xl', textContent: '🖼️'}));
                    }

                    var countParts = [];
                    countParts.push('샘플 ' + p.sampleCount);
                    if (p.safeCount > 0) countParts.push('안전 ' + p.safeCount);
                    if (p.orderEnabled) {
                        countParts.push('주문 ' + p.orderCount);
                    }

                    var info = el('div', {className: 'mt-2'});
                    info.appendChild(el('h3', {className: 'text-sm font-semibold text-gray-900', textContent: p.name}));

                    var countLine = el('div', {className: 'text-[11px] text-gray-500 mt-0.5'});
                    countLine.textContent = countParts.join(' / ');
                    info.appendChild(countLine);

                    if (!p.orderEnabled) {
                        info.appendChild(el('span', {className: 'inline-block mt-0.5 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded', textContent: '주문이미지 OFF'}));
                    }

                    card.appendChild(info);
                    card.appendChild(el('div', {className: 'mt-1.5 text-blue-600 text-xs font-medium', textContent: '관리하기 →'}));
                    grid.appendChild(card);
                });
            });
    }

    window.showOverview = function() {
        document.getElementById('overviewView').classList.remove('hidden');
        document.getElementById('detailView').classList.add('hidden');
        currentProduct = null;
        loadStats();
    };

    // --- 상세 ---
    window.showDetail = function(product, name) {
        currentProduct = product;
        currentProductName = name;
        currentTab = 'sample';
        document.getElementById('overviewView').classList.add('hidden');
        document.getElementById('detailView').classList.remove('hidden');
        document.getElementById('detailTitle').textContent = name + ' 갤러리';
        document.getElementById('detailSubtitle').textContent = '샘플·안전갤러리·주문 이미지 관리';

        // 설정 패널 초기화
        var s = allSettings[product] || {order_enabled: true, order_date_from: '', order_date_to: ''};
        document.getElementById('orderToggle').checked = s.order_enabled;
        document.getElementById('dateFrom').value = s.order_date_from || '';
        document.getElementById('dateTo').value = s.order_date_to || '';

        // 설정 패널 닫기
        settingsOpen = false;
        document.getElementById('settingsBody').classList.add('hidden');
        document.getElementById('settingsArrow').classList.remove('rotate-180');

        // 탭 활성화
        activateTab('sample');
        updateOrderTabState(s.order_enabled);
        loadImages('sample');
    };

    function activateTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            if (btn.dataset.tab === tab) {
                btn.classList.add('border-blue-600', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');
            } else {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });
    }

    function updateOrderTabState(enabled) {
        var orderBtn = document.querySelector('[data-tab="order"]');
        if (orderBtn) {
            if (enabled) {
                orderBtn.disabled = false;
                orderBtn.classList.remove('opacity-40', 'cursor-not-allowed');
            } else {
                orderBtn.disabled = true;
                orderBtn.classList.add('opacity-40', 'cursor-not-allowed');
            }
        }
    }

    window.switchTab = function(tab) {
        var s = allSettings[currentProduct] || {order_enabled: true};
        if (tab === 'order' && !s.order_enabled) return;
        currentTab = tab;
        activateTab(tab);
        loadImages(tab);
    };

    window.toggleSettings = function() {
        settingsOpen = !settingsOpen;
        document.getElementById('settingsBody').classList.toggle('hidden', !settingsOpen);
        document.getElementById('settingsArrow').classList.toggle('rotate-180', settingsOpen);
    };

    window.saveProductSettings = function() {
        if (!currentProduct) return;
        var payload = {};
        payload[currentProduct] = {
            order_enabled: document.getElementById('orderToggle').checked,
            order_date_from: document.getElementById('dateFrom').value,
            order_date_to: document.getElementById('dateTo').value
        };

        fetch(API + '?action=save_settings', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                allSettings = res.data;
                showToast('설정 저장 완료', 'success');
                var s = allSettings[currentProduct] || {order_enabled: true};
                updateOrderTabState(s.order_enabled);
                // 현재 탭이 주문이고 OFF로 변경 시 샘플 탭으로
                if (currentTab === 'order' && !s.order_enabled) {
                    switchTab('sample');
                } else {
                    loadImages(currentTab);
                }
            } else {
                showToast(res.message || '설정 저장 실패', 'error');
            }
        });
    };

    function loadImages(source) {
        var grid = document.getElementById('imageGrid');
        var empty = document.getElementById('emptyState');
        grid.textContent = '';
        grid.appendChild(el('div', {className: 'col-span-full text-center py-8 text-gray-400 text-xs', textContent: '로딩 중...'}));
        empty.classList.add('hidden');

        // 주문 이미지 OFF인데 order 탭이면
        var s = allSettings[currentProduct] || {order_enabled: true};
        if (source === 'order' && !s.order_enabled) {
            grid.textContent = '';
            document.getElementById('emptyText').textContent = '주문 이미지가 비활성화되어 있습니다';
            empty.classList.remove('hidden');
            currentImages = [];
            return;
        }

        fetch(API + '?action=list&product=' + encodeURIComponent(currentProduct) + '&source=' + encodeURIComponent(source))
            .then(function(r) { return r.json(); })
            .then(function(res) {
                grid.textContent = '';
                if (!res.success || !res.data.items.length) {
                    document.getElementById('emptyText').textContent = '이미지가 없습니다';
                    empty.classList.remove('hidden');
                    currentImages = [];
                    return;
                }
                currentImages = res.data.items;
                empty.classList.add('hidden');

                currentImages.forEach(function(img, idx) {
                    var canDelete = (img.source === 'sample' || img.source === 'safegallery');

                    var wrapper = el('div', {
                        className: 'group relative cursor-pointer rounded-lg overflow-hidden bg-gray-100 aspect-square',
                        'data-idx': idx
                    });
                    wrapper.addEventListener('click', function() { openLightbox(idx); });

                    // 이미지 위에 파일 드래그하여 교체 (샘플/안전갤러리만)
                    if (canDelete) {
                        wrapper.addEventListener('dragover', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.classList.add('ring-4', 'ring-blue-500', 'ring-inset');
                        });
                        wrapper.addEventListener('dragleave', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.classList.remove('ring-4', 'ring-blue-500', 'ring-inset');
                        });
                        wrapper.addEventListener('drop', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.classList.remove('ring-4', 'ring-blue-500', 'ring-inset');

                            var files = e.dataTransfer.files;
                            if (files && files.length > 0) {
                                var file = files[0];
                                if (file.type.startsWith('image/')) {
                                    replaceImage(idx, file);
                                }
                            }
                        });
                    }

                    var imgEl = el('img', {className: 'w-full h-full object-cover', loading: 'lazy'});
                    imgEl.src = img.src;
                    imgEl.alt = img.filename || '';
                    imgEl.addEventListener('error', function() { this.src = '/assets/images/placeholder.jpg'; });
                    wrapper.appendChild(imgEl);

                    // 삭제 버튼 (샘플 + 안전갤러리)
                    if (canDelete) {
                        var delBtn = el('button', {className: 'absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs leading-5 text-center opacity-0 group-hover:opacity-100 transition-opacity', textContent: '\u00D7'});
                        (function(i) {
                            delBtn.addEventListener('click', function(e) { e.stopPropagation(); openDeleteModal(i); });
                        })(idx);
                        wrapper.appendChild(delBtn);
                    }

                    // 소스 뱃지
                    var badgeClass, badgeText;
                    if (img.source === 'sample') {
                        badgeClass = 'bg-green-500'; badgeText = '샘플';
                    } else if (img.source === 'safegallery') {
                        badgeClass = 'bg-blue-500'; badgeText = '안전';
                    } else {
                        badgeClass = 'bg-gray-500'; badgeText = '주문';
                    }
                    wrapper.appendChild(el('span', {className: 'absolute bottom-1 left-1 ' + badgeClass + ' text-white text-[9px] px-1 rounded', textContent: badgeText}));

                    grid.appendChild(wrapper);
                });
            });
    }

    // --- 업로드 ---
    window.openUploadModal = function() {
        pendingFiles = [];
        document.getElementById('fileInput').value = '';
        document.getElementById('fileList').textContent = '';
        document.getElementById('uploadProgress').classList.add('hidden');
        document.getElementById('uploadBtn').disabled = true;
        // 라디오 기본값: 현재 탭에 맞게
        var radios = document.querySelectorAll('input[name="uploadTarget"]');
        radios.forEach(function(r) {
            r.checked = (currentTab === 'safegallery' && r.value === 'safegallery') || (currentTab !== 'safegallery' && r.value === 'sample');
        });
        document.getElementById('uploadModal').classList.remove('hidden');
    };
    window.closeUploadModal = function() {
        document.getElementById('uploadModal').classList.add('hidden');
    };

    var dropZone = document.getElementById('dropZone');
    var fileInput = document.getElementById('fileInput');

    dropZone.addEventListener('click', function() { fileInput.click(); });
    dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('border-blue-400', 'bg-blue-50'); });
    dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('border-blue-400', 'bg-blue-50'); });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        addFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', function() { addFiles(fileInput.files); });

    function addFiles(filesList) {
        for (var i = 0; i < filesList.length; i++) { pendingFiles.push(filesList[i]); }
        renderFileList();
    }

    function renderFileList() {
        var list = document.getElementById('fileList');
        list.textContent = '';
        pendingFiles.forEach(function(f, i) {
            var sizeStr = f.size > 1048576 ? (f.size / 1048576).toFixed(1) + 'MB' : Math.round(f.size / 1024) + 'KB';
            var row = el('div', {className: 'flex items-center justify-between text-xs bg-gray-50 rounded px-2 py-1'}, [
                el('span', {className: 'truncate flex-1', textContent: f.name + ' (' + sizeStr + ')'}),
            ]);
            var removeBtn = el('button', {className: 'text-red-400 hover:text-red-600 ml-2', textContent: '\u00D7'});
            (function(idx) {
                removeBtn.addEventListener('click', function() { removeFile(idx); });
            })(i);
            row.appendChild(removeBtn);
            list.appendChild(row);
        });
        document.getElementById('uploadBtn').disabled = pendingFiles.length === 0;
    }

    window.removeFile = function(idx) {
        pendingFiles.splice(idx, 1);
        renderFileList();
    };

    window.startUpload = function() {
        if (!currentProduct || pendingFiles.length === 0) return;

        var target = 'sample';
        var checked = document.querySelector('input[name="uploadTarget"]:checked');
        if (checked) target = checked.value;

        var formData = new FormData();
        formData.append('action', 'upload');
        formData.append('product', currentProduct);
        formData.append('target', target);
        pendingFiles.forEach(function(f) { formData.append('files[]', f); });

        document.getElementById('uploadProgress').classList.remove('hidden');
        document.getElementById('uploadBtn').disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', API);
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var pct = Math.round(e.loaded / e.total * 100);
                document.getElementById('progressBar').style.width = pct + '%';
                document.getElementById('progressText').textContent = pct + '% 업로드 중...';
            }
        });
        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                document.getElementById('progressText').textContent = res.message;
                setTimeout(function() {
                    closeUploadModal();
                    loadImages(currentTab);
                    showToast(res.message, res.success ? 'success' : 'error');
                }, 800);
            } catch(e) {
                document.getElementById('progressText').textContent = '응답 처리 오류';
            }
        };
        xhr.onerror = function() {
            document.getElementById('progressText').textContent = '업로드 실패';
            showToast('업로드 실패', 'error');
        };
        xhr.send(formData);
    };

    // --- 삭제 ---
    window.openDeleteModal = function(idx) {
        var img = currentImages[idx];
        if (!img || (img.source !== 'sample' && img.source !== 'safegallery')) return;
        deleteTarget = img;
        document.getElementById('deletePreviewImg').src = img.src;
        document.getElementById('deleteModal').classList.remove('hidden');
    };
    window.closeDeleteModal = function() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteTarget = null;
    };

    // --- 이미지 교체 ---
    window.replaceImage = function(idx, file) {
        if (!currentProduct) return;
        var img = currentImages[idx];
        if (!img || (img.source !== 'sample' && img.source !== 'safegallery')) return;

        var formData = new FormData();
        formData.append('action', 'replace');
        formData.append('product', currentProduct);
        formData.append('source', img.source);
        formData.append('old_filename', img.filename);
        formData.append('file', file);

        // 진행 표시
        var wrapper = document.querySelector('[data-idx="' + idx + '"]');
        if (wrapper) {
            wrapper.classList.add('opacity-50');
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', API);
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                console.log('교체 진행: ' + Math.round(e.loaded / e.total * 100) + '%');
            }
        });
        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                if (wrapper) {
                    wrapper.classList.remove('opacity-50');
                }
                if (res.success) {
                    showToast('이미지 교체 완료', 'success');
                    loadImages(currentTab);
                } else {
                    showToast(res.message || '교체 실패', 'error');
                }
            } catch(e) {
                if (wrapper) {
                    wrapper.classList.remove('opacity-50');
                }
                showToast('응답 처리 오류', 'error');
            }
        };
        xhr.onerror = function() {
            if (wrapper) {
                wrapper.classList.remove('opacity-50');
            }
            showToast('교체 실패', 'error');
        };
        xhr.send(formData);
    };

    window.confirmDelete = function() {
        if (!deleteTarget) return;
        var formData = new FormData();
        formData.append('action', 'delete');
        formData.append('product', currentProduct);
        formData.append('filename', deleteTarget.filename);
        formData.append('source', deleteTarget.source);

        fetch(API, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                closeDeleteModal();
                if (res.success) {
                    showToast('삭제 완료', 'success');
                    loadImages(currentTab);
                } else {
                    showToast(res.message, 'error');
                }
            });
    };

    // --- 라이트박스 ---
    window.openLightbox = function(idx) {
        lightboxIndex = idx;
        updateLightbox();
        document.getElementById('lightbox').classList.remove('hidden');
        document.addEventListener('keydown', lightboxKeyHandler);
    };
    window.closeLightbox = function() {
        document.getElementById('lightbox').classList.add('hidden');
        document.removeEventListener('keydown', lightboxKeyHandler);
    };
    window.lightboxNav = function(dir) {
        lightboxIndex += dir;
        if (lightboxIndex < 0) lightboxIndex = currentImages.length - 1;
        if (lightboxIndex >= currentImages.length) lightboxIndex = 0;
        updateLightbox();
    };
    function updateLightbox() {
        var img = currentImages[lightboxIndex];
        if (!img) return;
        document.getElementById('lightboxImg').src = img.src;
        var sourceLabel = img.source === 'sample' ? '샘플' : (img.source === 'safegallery' ? '안전갤러리' : '주문');
        document.getElementById('lightboxInfo').textContent = (lightboxIndex + 1) + ' / ' + currentImages.length + '  |  ' + sourceLabel + '  |  ' + (img.filename || '') + '  |  ' + (img.date || '');
    }
    function lightboxKeyHandler(e) {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') lightboxNav(-1);
        if (e.key === 'ArrowRight') lightboxNav(1);
    }

    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) closeLightbox();
    });

    // 초기 로드
    loadStats();
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
