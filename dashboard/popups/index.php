<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50 overflow-y-auto">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-bold text-gray-900">팝업 관리</h1>
                <p class="text-xs text-gray-500 mt-0.5">고객 사이트에 표시되는 레이어 팝업을 관리합니다.</p>
            </div>
            <button type="button" onclick="toggleForm()" id="addBtn"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors flex items-center gap-1.5">
                <span id="addBtnIcon">＋</span>
                <span id="addBtnText">새 팝업 등록</span>
            </button>
        </div>

        <!-- Create/Edit Form (collapsible) -->
        <div id="popupForm" class="hidden bg-white rounded-lg shadow mb-4">
            <div class="p-4 border-b border-gray-100">
                <h3 id="formTitle" class="text-sm font-semibold text-gray-900">새 팝업 등록</h3>
            </div>
            <div class="p-4">
                <input type="hidden" id="editId" value="">

                <!-- Image Upload -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">팝업 이미지 <span class="text-red-400">*</span></label>
                    <div id="dropZone"
                         class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
                         onclick="document.getElementById('imageInput').click()">
                        <div id="dropZoneContent">
                            <p class="text-gray-400 text-sm mb-1">클릭하거나 이미지를 드래그하여 업로드</p>
                            <p class="text-gray-300 text-[10px]">JPG, PNG, GIF, WEBP · 최대 5MB</p>
                        </div>
                        <div id="imagePreviewWrap" class="hidden">
                            <img id="imagePreview" src="" alt="미리보기" class="max-h-48 mx-auto rounded-md">
                            <p class="text-xs text-gray-400 mt-2">클릭하여 이미지 변경</p>
                        </div>
                    </div>
                    <input type="file" id="imageInput" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewImage(this)">
                </div>

                <!-- Title + Link -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">제목</label>
                        <input type="text" id="popupTitle" placeholder="팝업 제목 (선택)" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">클릭 시 이동 URL</label>
                        <input type="text" id="linkUrl" placeholder="https://..." class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 font-mono">
                    </div>
                </div>

                <!-- Dates + Options -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">시작일 <span class="text-red-400">*</span></label>
                        <input type="date" id="startDate" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">종료일 <span class="text-red-400">*</span></label>
                        <input type="date" id="endDate" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">닫기 옵션</label>
                        <select id="hideOption" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="today">오늘 하루 안보기</option>
                            <option value="week">7일간 안보기</option>
                            <option value="month">30일간 안보기</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">정렬순서</label>
                        <input type="number" id="sortOrder" value="0" min="0" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Link Target -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">링크 열기 방식</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="linkTarget" value="_blank" checked class="text-blue-600 focus:ring-blue-500"> 새 창
                        </label>
                        <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                            <input type="radio" name="linkTarget" value="_self" class="text-blue-600 focus:ring-blue-500"> 현재 창
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" onclick="cancelForm()" class="px-4 py-1.5 text-sm text-gray-600 hover:text-gray-800 transition-colors">취소</button>
                    <button type="button" onclick="savePopup()" id="saveBtn" class="px-4 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50">
                        저장
                    </button>
                </div>
            </div>
        </div>

        <!-- Popup List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">등록된 팝업</h3>
                <span id="popupCount" class="text-xs text-gray-400"></span>
            </div>
            <div id="popupList" class="divide-y divide-gray-100">
                <div class="p-8 text-center text-sm text-gray-400">로딩 중...</div>
            </div>
            <div id="emptyState" class="hidden p-8 text-center">
                <p class="text-sm text-gray-400 mb-1">등록된 팝업이 없습니다.</p>
                <p class="text-xs text-gray-300">위 "새 팝업 등록" 버튼으로 팝업을 추가하세요.</p>
            </div>
        </div>
    </div>
</main>

<script>
// === State ===
var popups = [];
var isEditing = false;
var existingImagePath = '';

// === Init ===
document.addEventListener('DOMContentLoaded', function() {
    loadPopups();
    initDragDrop();
    setDefaultDates();
});

function setDefaultDates() {
    var today = new Date();
    var nextWeek = new Date(today);
    nextWeek.setDate(nextWeek.getDate() + 7);
    document.getElementById('startDate').value = formatDate(today);
    document.getElementById('endDate').value = formatDate(nextWeek);
}

function formatDate(d) {
    var y = d.getFullYear();
    var m = ('0' + (d.getMonth() + 1)).slice(-2);
    var day = ('0' + d.getDate()).slice(-2);
    return y + '-' + m + '-' + day;
}

// === API Call ===
function apiCall(formData) {
    return fetch('/dashboard/api/popups.php', {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); });
}

// === Load Popups ===
function loadPopups() {
    var fd = new FormData();
    fd.append('action', 'list');
    apiCall(fd).then(function(data) {
        if (data.success) {
            popups = data.data;
            renderPopupList();
        } else {
            showToast(data.error || '목록 로드 실패', 'error');
        }
    }).catch(function(err) {
        showToast('네트워크 오류: ' + err.message, 'error');
    });
}

// === Render Popup List ===
function renderPopupList() {
    var container = document.getElementById('popupList');
    var emptyState = document.getElementById('emptyState');
    var countEl = document.getElementById('popupCount');

    countEl.textContent = popups.length + '건';

    if (popups.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    var html = '';

    popups.forEach(function(p) {
        var statusBadge = getStatusBadge(p);
        var hideLabel = { 'today': '하루', 'week': '7일', 'month': '30일' }[p.hide_option] || p.hide_option;

        html += '<div class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors" data-id="' + p.id + '">';

        // Thumbnail
        if (p.image_path) {
            html += '<div class="flex-shrink-0 w-16 h-10 rounded overflow-hidden bg-gray-100 border border-gray-200">';
            html += '<img src="' + escapeHtml(p.image_path) + '" alt="" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML=\'<div class=\\\'flex items-center justify-center w-full h-full text-gray-300 text-xs\\\'>X</div>\'">';
            html += '</div>';
        } else {
            html += '<div class="flex-shrink-0 w-16 h-10 rounded bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-300 text-xs">없음</div>';
        }

        // Info
        html += '<div class="flex-1 min-w-0">';
        html += '<div class="flex items-center gap-2 mb-0.5">';
        html += '<span class="text-sm font-medium text-gray-900 truncate">' + escapeHtml(p.title || '(제목 없음)') + '</span>';
        html += statusBadge;
        html += '</div>';
        html += '<div class="text-xs text-gray-400">';
        html += p.start_date + ' ~ ' + p.end_date;
        html += ' · 안보기: ' + hideLabel;
        html += ' · 순서: ' + p.sort_order;
        if (p.link_url) {
            html += ' · <a href="' + escapeHtml(p.link_url) + '" target="_blank" class="text-blue-400 hover:underline">링크↗</a>';
        }
        html += '</div>';
        html += '</div>';

        // Actions
        html += '<div class="flex items-center gap-2 flex-shrink-0">';

        // Toggle switch
        var toggleClass = p.is_active ? 'bg-blue-600' : 'bg-gray-300';
        var toggleDot = p.is_active ? 'translate-x-5' : 'translate-x-0';
        html += '<button type="button" onclick="togglePopup(' + p.id + ')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors ' + toggleClass + '" title="' + (p.is_active ? '비활성화' : '활성화') + '">';
        html += '<span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform ' + toggleDot + ' ml-0.5"></span>';
        html += '</button>';

        // Edit
        html += '<button type="button" onclick="editPopup(' + p.id + ')" class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors" title="수정">';
        html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
        html += '</button>';

        // Delete
        html += '<button type="button" onclick="deletePopup(' + p.id + ')" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="삭제">';
        html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
        html += '</button>';

        html += '</div>';
        html += '</div>';
    });

    container.innerHTML = html;
}

function getStatusBadge(p) {
    var today = new Date().toISOString().slice(0, 10);

    if (!p.is_active) {
        return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500">비활성</span>';
    }
    if (p.start_date > today) {
        return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-600">예약됨</span>';
    }
    if (p.end_date < today) {
        return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500">종료</span>';
    }
    return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">표시중</span>';
}

// === Form Toggle ===
function toggleForm(forEdit) {
    var form = document.getElementById('popupForm');
    var btn = document.getElementById('addBtn');
    var isHidden = form.classList.contains('hidden');

    if (isHidden) {
        form.classList.remove('hidden');
        btn.querySelector('#addBtnText').textContent = '닫기';
        btn.querySelector('#addBtnIcon').textContent = '✕';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-gray-500', 'hover:bg-gray-600');
        if (!forEdit) {
            resetForm();
        }
    } else {
        form.classList.add('hidden');
        btn.querySelector('#addBtnText').textContent = '새 팝업 등록';
        btn.querySelector('#addBtnIcon').textContent = '＋';
        btn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
        btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        resetForm();
    }
}

function cancelForm() {
    var form = document.getElementById('popupForm');
    var btn = document.getElementById('addBtn');
    form.classList.add('hidden');
    btn.querySelector('#addBtnText').textContent = '새 팝업 등록';
    btn.querySelector('#addBtnIcon').textContent = '＋';
    btn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
    btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
    resetForm();
}

function resetForm() {
    document.getElementById('editId').value = '';
    document.getElementById('popupTitle').value = '';
    document.getElementById('linkUrl').value = '';
    document.getElementById('imageInput').value = '';
    document.getElementById('hideOption').value = 'today';
    document.getElementById('sortOrder').value = '0';
    document.querySelector('input[name="linkTarget"][value="_blank"]').checked = true;
    document.getElementById('formTitle').textContent = '새 팝업 등록';
    document.getElementById('saveBtn').textContent = '저장';
    existingImagePath = '';
    isEditing = false;

    // Reset preview
    document.getElementById('imagePreviewWrap').classList.add('hidden');
    document.getElementById('dropZoneContent').classList.remove('hidden');

    setDefaultDates();
}

// === Image Preview ===
function previewImage(input) {
    if (!input.files || !input.files[0]) return;

    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('파일 크기가 5MB를 초과합니다.', 'error');
        input.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imagePreview').src = e.target.result;
        document.getElementById('imagePreviewWrap').classList.remove('hidden');
        document.getElementById('dropZoneContent').classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

// === Drag & Drop ===
function initDragDrop() {
    var dropZone = document.getElementById('dropZone');

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');

        var files = e.dataTransfer.files;
        if (files.length > 0) {
            var input = document.getElementById('imageInput');
            input.files = files;
            previewImage(input);
        }
    });
}

// === Save (Create/Update) ===
function savePopup() {
    var editId = document.getElementById('editId').value;
    var imageInput = document.getElementById('imageInput');
    var title = document.getElementById('popupTitle').value.trim();
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    // Validation
    if (!imageInput.files.length && !editId && !existingImagePath) {
        showToast('이미지를 업로드하세요.', 'error');
        return;
    }

    if (!startDate || !endDate) {
        showToast('시작일과 종료일을 선택하세요.', 'error');
        return;
    }

    if (startDate > endDate) {
        showToast('종료일은 시작일 이후여야 합니다.', 'error');
        return;
    }

    var fd = new FormData();
    fd.append('action', editId ? 'update' : 'create');
    if (editId) fd.append('id', editId);
    fd.append('title', title);
    fd.append('link_url', document.getElementById('linkUrl').value.trim());
    fd.append('link_target', document.querySelector('input[name="linkTarget"]:checked').value);
    fd.append('start_date', startDate);
    fd.append('end_date', endDate);
    fd.append('hide_option', document.getElementById('hideOption').value);
    fd.append('sort_order', document.getElementById('sortOrder').value);

    if (imageInput.files.length > 0) {
        fd.append('image', imageInput.files[0]);
    }

    var saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = '저장 중...';

    apiCall(fd).then(function(data) {
        if (data.success) {
            showToast(data.message || '저장되었습니다.', 'success');
            cancelForm();
            loadPopups();
        } else {
            showToast(data.error || '저장 실패', 'error');
        }
    }).catch(function(err) {
        showToast('네트워크 오류: ' + err.message, 'error');
    }).finally(function() {
        saveBtn.disabled = false;
        saveBtn.textContent = editId ? '수정' : '저장';
    });
}

// === Edit ===
function editPopup(id) {
    var p = popups.find(function(item) { return item.id == id; });
    if (!p) return;

    isEditing = true;
    existingImagePath = p.image_path || '';

    document.getElementById('editId').value = p.id;
    document.getElementById('popupTitle').value = p.title || '';
    document.getElementById('linkUrl').value = p.link_url || '';
    document.getElementById('startDate').value = p.start_date;
    document.getElementById('endDate').value = p.end_date;
    document.getElementById('hideOption').value = p.hide_option || 'today';
    document.getElementById('sortOrder').value = p.sort_order || 0;
    document.getElementById('formTitle').textContent = '팝업 수정';
    document.getElementById('saveBtn').textContent = '수정';

    // Link target
    var target = p.link_target || '_blank';
    var radio = document.querySelector('input[name="linkTarget"][value="' + target + '"]');
    if (radio) radio.checked = true;

    // Image preview
    if (p.image_path) {
        document.getElementById('imagePreview').src = p.image_path;
        document.getElementById('imagePreviewWrap').classList.remove('hidden');
        document.getElementById('dropZoneContent').classList.add('hidden');
    } else {
        document.getElementById('imagePreviewWrap').classList.add('hidden');
        document.getElementById('dropZoneContent').classList.remove('hidden');
    }

    // Clear file input
    document.getElementById('imageInput').value = '';

    // Show form
    var form = document.getElementById('popupForm');
    if (form.classList.contains('hidden')) {
        toggleForm(true);
    }

    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// === Toggle Active ===
function togglePopup(id) {
    var fd = new FormData();
    fd.append('action', 'toggle');
    fd.append('id', id);

    apiCall(fd).then(function(data) {
        if (data.success) {
            showToast(data.message || '상태 변경됨', 'success');
            loadPopups();
        } else {
            showToast(data.error || '토글 실패', 'error');
        }
    }).catch(function(err) {
        showToast('네트워크 오류: ' + err.message, 'error');
    });
}

// === Delete ===
function deletePopup(id) {
    var p = popups.find(function(item) { return item.id == id; });
    var name = p ? (p.title || '(제목 없음)') : '#' + id;

    if (!confirm('"' + name + '" 팝업을 삭제하시겠습니까?\n이미지 파일도 함께 삭제됩니다.')) return;

    var fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);

    apiCall(fd).then(function(data) {
        if (data.success) {
            showToast(data.message || '삭제됨', 'success');
            loadPopups();
        } else {
            showToast(data.error || '삭제 실패', 'error');
        }
    }).catch(function(err) {
        showToast('네트워크 오류: ' + err.message, 'error');
    });
}

// === Utility ===
function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
