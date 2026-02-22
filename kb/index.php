<?php
session_start();
require_once __DIR__ . '/kb_auth.php';
kb_check_auth();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Knowledge Vault</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh}

.container{max-width:900px;margin:0 auto;padding:24px 16px}

.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.header h1{font-size:20px;font-weight:700;color:#f8fafc;letter-spacing:-0.5px}
.header h1 span{color:#6366f1}
.btn-new{background:#6366f1;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;transition:background .15s}
.btn-new:hover{background:#4f46e5}

.search-box{position:relative;margin-bottom:16px}
.search-box input{width:100%;padding:12px 16px 12px 40px;background:#1e293b;border:1px solid #334155;border-radius:8px;color:#f1f5f9;font-size:15px;outline:none;transition:border .15s}
.search-box input:focus{border-color:#6366f1}
.search-box input::placeholder{color:#64748b}
.search-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748b;font-size:16px}
.search-count{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#64748b;font-size:12px}

.cats{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap}
.cat-btn{padding:5px 12px;border-radius:14px;border:1px solid #334155;background:transparent;color:#94a3b8;cursor:pointer;font-size:12px;transition:all .15s}
.cat-btn:hover,.cat-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}

.list{display:flex;flex-direction:column;gap:8px}
.card{background:#1e293b;border:1px solid #334155;border-radius:8px;padding:14px 16px;cursor:pointer;transition:all .15s;text-decoration:none;display:block}
.card:hover{border-color:#6366f1;transform:translateY(-1px)}
.card-title{font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:6px}
.card-snippet{font-size:13px;color:#94a3b8;line-height:1.5;margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.card-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tag{background:#1e1b4b;color:#a5b4fc;padding:2px 8px;border-radius:10px;font-size:11px}
.card-date{color:#64748b;font-size:11px;margin-left:auto}
.card-cat{color:#64748b;font-size:11px;background:#0f172a;padding:2px 8px;border-radius:10px}

.pager{display:flex;justify-content:center;align-items:center;gap:8px;margin-top:20px;color:#64748b;font-size:13px}
.pager button{background:#1e293b;border:1px solid #334155;color:#e2e8f0;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:12px}
.pager button:hover{border-color:#6366f1}
.pager button:disabled{opacity:.3;cursor:default}

.empty{text-align:center;padding:60px 20px;color:#64748b}
.empty p{font-size:14px;margin-bottom:16px}

.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:100;align-items:center;justify-content:center}
.modal-bg.show{display:flex}
.modal{background:#1e293b;border:1px solid #334155;border-radius:12px;width:95%;max-width:700px;max-height:90vh;overflow-y:auto;padding:24px}
.modal h2{font-size:16px;font-weight:700;color:#f1f5f9;margin-bottom:16px}
.modal label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;margin-top:12px}
.modal input,.modal textarea,.modal select{width:100%;padding:10px 12px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f1f5f9;font-size:14px;outline:none;font-family:inherit}
.modal input:focus,.modal textarea:focus{border-color:#6366f1}
.modal textarea{min-height:280px;resize:vertical;font-family:'Fira Code',monospace,inherit;font-size:13px;line-height:1.6}
.modal-footer{display:flex;justify-content:flex-end;gap:8px;margin-top:20px}
.modal-footer button{padding:8px 20px;border-radius:6px;border:none;cursor:pointer;font-size:13px;font-weight:600}
.btn-cancel{background:#334155;color:#e2e8f0}
.btn-save{background:#6366f1;color:#fff}
.btn-save:hover{background:#4f46e5}
.tag-hint{font-size:11px;color:#64748b;margin-top:2px}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><span>KB</span> Knowledge Vault</h1>
        <button class="btn-new" onclick="openModal()">+ 새 문서</button>
    </div>

    <div class="search-box">
        <span class="search-icon">&#128269;</span>
        <input type="text" id="searchInput" placeholder="검색어 입력... (제목, 내용, 태그)" autofocus>
        <span class="search-count" id="searchCount"></span>
    </div>

    <div class="cats" id="catBar"></div>

    <div class="list" id="results"></div>

    <div class="pager" id="pager"></div>
</div>

<div class="modal-bg" id="modalBg" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <h2 id="modalTitle">새 문서 작성</h2>
        <input type="hidden" id="editId">

        <label>제목</label>
        <input type="text" id="fTitle" placeholder="예: OpenCode WSL 설치 가이드">

        <label>카테고리</label>
        <select id="fCat">
            <option value="general">일반</option>
            <option value="setup">설치가이드</option>
            <option value="config">설정</option>
            <option value="troubleshoot">트러블슈팅</option>
            <option value="code">코드/스니펫</option>
            <option value="reference">참조</option>
            <option value="workflow">워크플로우</option>
        </select>

        <label>태그</label>
        <input type="text" id="fTags" placeholder="쉼표로 구분: opencode, wsl, 설치">
        <div class="tag-hint">쉼표로 구분. 검색에 활용됩니다.</div>

        <label>내용 (마크다운)</label>
        <textarea id="fContent" placeholder="마크다운으로 작성...&#10;&#10;## 설치 명령어&#10;```bash&#10;curl -fsSL https://opencode.ai/install | bash&#10;```"></textarea>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">취소</button>
            <button class="btn-save" onclick="saveDoc()">저장</button>
        </div>
    </div>
</div>

<script>
let currentCat = 'all';
let currentPage = 1;
let debounceTimer;

const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { currentPage = 1; loadResults(); }, 250);
});

function loadResults() {
    const q = searchInput.value.trim();
    const params = new URLSearchParams({ action: 'search', q, category: currentCat, page: currentPage });

    fetch('api.php?' + params)
        .then(r => r.json())
        .then(data => {
            renderCategories(data.categories);
            renderCards(data.items, q);
            renderPager(data);
            document.getElementById('searchCount').textContent = data.total > 0 ? data.total + '건' : '';
        });
}

function renderCategories(cats) {
    const bar = document.getElementById('catBar');
    let html = '<button class="cat-btn ' + (currentCat === 'all' ? 'active' : '') + '" onclick="filterCat(\'all\')">전체</button>';
    const nameMap = { general:'일반', setup:'설치가이드', config:'설정', troubleshoot:'트러블슈팅', code:'코드', reference:'참조', workflow:'워크플로우' };
    cats.forEach(c => {
        const name = nameMap[c.category] || c.category;
        html += '<button class="cat-btn ' + (currentCat === c.category ? 'active' : '') + '" onclick="filterCat(\'' + c.category + '\')">' + name + ' (' + c.cnt + ')</button>';
    });
    bar.innerHTML = html;
}

function renderCards(items, q) {
    const list = document.getElementById('results');
    if (items.length === 0) {
        list.innerHTML = '<div class="empty"><p>' + (q ? '"' + escHtml(q) + '" 검색 결과 없음' : '아직 문서가 없습니다') + '</p><button class="btn-new" onclick="openModal()">+ 첫 문서 작성</button></div>';
        return;
    }

    list.innerHTML = items.map(item => {
        const tags = item.tags ? item.tags.split(',').map(t => '<span class="tag">' + escHtml(t.trim()) + '</span>').join('') : '';
        const snippet = highlightMatch(escHtml(item.snippet), q);
        const nameMap = { general:'일반', setup:'설치가이드', config:'설정', troubleshoot:'트러블슈팅', code:'코드', reference:'참조', workflow:'워크플로우' };
        const catName = nameMap[item.category] || item.category;
        return '<a class="card" href="article.php?id=' + item.id + '">' +
            '<div class="card-title">' + highlightMatch(escHtml(item.title), q) + '</div>' +
            '<div class="card-snippet">' + snippet + '</div>' +
            '<div class="card-meta">' + tags + '<span class="card-cat">' + catName + '</span><span class="card-date">' + item.updated_at.slice(0, 10) + '</span></div>' +
            '</a>';
    }).join('');
}

function renderPager(data) {
    const pg = document.getElementById('pager');
    if (data.pages <= 1) { pg.innerHTML = ''; return; }
    pg.innerHTML = '<button ' + (data.page <= 1 ? 'disabled' : '') + ' onclick="goPage(' + (data.page-1) + ')">&#8249;</button>' +
        '<span>' + data.page + ' / ' + data.pages + '</span>' +
        '<button ' + (data.page >= data.pages ? 'disabled' : '') + ' onclick="goPage(' + (data.page+1) + ')">&#8250;</button>';
}

function filterCat(cat) { currentCat = cat; currentPage = 1; loadResults(); }
function goPage(p) { currentPage = p; loadResults(); }

function highlightMatch(text, q) {
    if (!q) return text;
    const words = q.split(/\s+/).filter(Boolean);
    words.forEach(w => {
        const re = new RegExp('(' + w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        text = text.replace(re, '<mark style="background:#6366f1;color:#fff;padding:0 2px;border-radius:2px">$1</mark>');
    });
    return text;
}

function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function openModal(id) {
    document.getElementById('modalBg').classList.add('show');
    if (id) {
        document.getElementById('modalTitle').textContent = '문서 수정';
        fetch('api.php?action=get&id=' + id).then(r => r.json()).then(d => {
            document.getElementById('editId').value = d.id;
            document.getElementById('fTitle').value = d.title;
            document.getElementById('fCat').value = d.category;
            document.getElementById('fTags').value = d.tags;
            document.getElementById('fContent').value = d.content;
        });
    } else {
        document.getElementById('modalTitle').textContent = '새 문서 작성';
        document.getElementById('editId').value = '';
        document.getElementById('fTitle').value = '';
        document.getElementById('fCat').value = 'general';
        document.getElementById('fTags').value = '';
        document.getElementById('fContent').value = '';
    }
}

function closeModal() {
    document.getElementById('modalBg').classList.remove('show');
}

function saveDoc() {
    const id = document.getElementById('editId').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('title', document.getElementById('fTitle').value);
    fd.append('content', document.getElementById('fContent').value);
    fd.append('tags', document.getElementById('fTags').value);
    fd.append('category', document.getElementById('fCat').value);

    fetch('api.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success || d.id) {
                closeModal();
                loadResults();
            } else {
                alert(d.error || '저장 실패');
            }
        });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
    if (e.key === '/' && document.activeElement !== searchInput) {
        e.preventDefault();
        searchInput.focus();
    }
});

loadResults();
</script>
</body>
</html>
