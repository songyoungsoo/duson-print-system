<?php
session_start();
require_once __DIR__ . '/kb_auth.php';
kb_check_auth();
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KB Article</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh}
.container{max-width:900px;margin:0 auto;padding:24px 16px}

.topbar{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.back{color:#94a3b8;text-decoration:none;font-size:13px;display:flex;align-items:center;gap:4px}
.back:hover{color:#f1f5f9}
.topbar-actions{margin-left:auto;display:flex;gap:8px}
.btn-sm{padding:6px 14px;border-radius:6px;border:none;cursor:pointer;font-size:12px;font-weight:600}
.btn-edit{background:#334155;color:#e2e8f0}
.btn-edit:hover{background:#475569}
.btn-del{background:#7f1d1d;color:#fca5a5}
.btn-del:hover{background:#991b1b}

.article-header{margin-bottom:20px}
.article-title{font-size:22px;font-weight:700;color:#f8fafc;margin-bottom:10px;line-height:1.3}
.article-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px}
.tag{background:#1e1b4b;color:#a5b4fc;padding:2px 8px;border-radius:10px;font-size:11px}
.cat-badge{color:#64748b;font-size:11px;background:#1e293b;padding:2px 8px;border-radius:10px;border:1px solid #334155}
.date{color:#64748b;font-size:12px}

.content{background:#1e293b;border:1px solid #334155;border-radius:8px;padding:24px;line-height:1.8;font-size:14px}
.content h1{font-size:20px;font-weight:700;color:#f8fafc;margin:24px 0 12px;padding-bottom:6px;border-bottom:1px solid #334155}
.content h1:first-child{margin-top:0}
.content h2{font-size:17px;font-weight:700;color:#f1f5f9;margin:20px 0 10px}
.content h3{font-size:15px;font-weight:600;color:#e2e8f0;margin:16px 0 8px}
.content p{margin:8px 0;color:#cbd5e1}
.content ul,.content ol{margin:8px 0 8px 20px;color:#cbd5e1}
.content li{margin:4px 0}
.content a{color:#818cf8;text-decoration:underline}
.content strong{color:#f1f5f9;font-weight:600}
.content em{color:#a5b4fc}
.content code{background:#0f172a;color:#a5b4fc;padding:2px 6px;border-radius:4px;font-size:13px;font-family:'Fira Code',monospace}
.content pre{position:relative;margin:12px 0;border-radius:6px;overflow:hidden}
.content pre code{display:block;padding:16px;background:#0f172a;border:1px solid #334155;border-radius:6px;overflow-x:auto;font-size:13px;line-height:1.5}
.content blockquote{border-left:3px solid #6366f1;padding:8px 16px;margin:12px 0;background:#1e1b4b33;color:#a5b4fc}
.content hr{border:none;border-top:1px solid #334155;margin:16px 0}
.content table{width:100%;border-collapse:collapse;margin:12px 0}
.content th,.content td{border:1px solid #334155;padding:8px 12px;text-align:left;font-size:13px}
.content th{background:#0f172a;color:#f1f5f9;font-weight:600}
.content img{max-width:100%;border-radius:6px;margin:8px 0}

.copy-btn{position:absolute;top:8px;right:8px;background:#334155;color:#e2e8f0;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;opacity:0;transition:opacity .15s}
.content pre:hover .copy-btn{opacity:1}
.copy-btn.copied{background:#059669;color:#fff}

.edit-area{display:none}
.edit-area textarea{width:100%;min-height:400px;padding:16px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f1f5f9;font-size:13px;line-height:1.6;font-family:'Fira Code',monospace;resize:vertical;outline:none}
.edit-area textarea:focus{border-color:#6366f1}
.edit-bar{display:flex;gap:8px;margin-top:12px}
.edit-bar input,.edit-bar select{padding:8px 12px;background:#0f172a;border:1px solid #334155;border-radius:6px;color:#f1f5f9;font-size:13px;outline:none;flex:1}
.edit-bar input:focus{border-color:#6366f1}
.edit-actions{display:flex;gap:8px;margin-top:12px;justify-content:flex-end}
.edit-actions button{padding:8px 20px;border-radius:6px;border:none;cursor:pointer;font-size:13px;font-weight:600}
.btn-save{background:#6366f1;color:#fff}
.btn-cancel{background:#334155;color:#e2e8f0}
</style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <a class="back" href="index.php">&#8592; 목록</a>
        <div class="topbar-actions">
            <button class="btn-sm btn-edit" id="btnEdit" onclick="toggleEdit()">수정</button>
            <button class="btn-sm btn-del" onclick="deleteDoc()">삭제</button>
        </div>
    </div>

    <div id="viewArea">
        <div class="article-header">
            <h1 class="article-title" id="artTitle"></h1>
            <div class="article-meta" id="artMeta"></div>
            <div class="date" id="artDate"></div>
        </div>
        <div class="content" id="artContent"></div>
    </div>

    <div class="edit-area" id="editArea">
        <input type="text" id="eTitle" placeholder="제목">
        <div class="edit-bar" style="margin-top:12px">
            <input type="text" id="eTags" placeholder="태그 (쉼표 구분)">
            <select id="eCat">
                <option value="general">일반</option>
                <option value="setup">설치가이드</option>
                <option value="config">설정</option>
                <option value="troubleshoot">트러블슈팅</option>
                <option value="code">코드/스니펫</option>
                <option value="reference">참조</option>
                <option value="workflow">워크플로우</option>
            </select>
        </div>
        <textarea id="eContent" style="margin-top:12px"></textarea>
        <div class="edit-actions">
            <button class="btn-cancel" onclick="toggleEdit()">취소</button>
            <button class="btn-save" onclick="saveEdit()">저장</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
const ART_ID = <?= $id ?>;
let articleData = null;

function loadArticle() {
    fetch('api.php?action=get&id=' + ART_ID)
        .then(r => r.json())
        .then(d => {
            if (d.error) { alert('문서를 찾을 수 없습니다'); location.href = 'index.php'; return; }
            articleData = d;
            document.title = d.title + ' — KB';
            document.getElementById('artTitle').textContent = d.title;

            const nameMap = { general:'일반', setup:'설치가이드', config:'설정', troubleshoot:'트러블슈팅', code:'코드', reference:'참조', workflow:'워크플로우' };
            let metaHtml = '<span class="cat-badge">' + (nameMap[d.category] || d.category) + '</span>';
            if (d.tags) {
                d.tags.split(',').forEach(t => {
                    metaHtml += '<span class="tag">' + escHtml(t.trim()) + '</span>';
                });
            }
            document.getElementById('artMeta').innerHTML = metaHtml;
            document.getElementById('artDate').textContent = d.updated_at.slice(0, 16).replace('T', ' ') + ' 수정';

            document.getElementById('artContent').innerHTML = renderMarkdown(d.content);
            document.querySelectorAll('#artContent pre code').forEach(el => hljs.highlightElement(el));
            addCopyButtons();
        });
}

function renderMarkdown(md) {
    let html = md;

    html = html.replace(/```(\w*)\n([\s\S]*?)```/g, function(_, lang, code) {
        return '<pre><code class="language-' + (lang || 'plaintext') + '">' + escHtml(code.trim()) + '</code></pre>';
    });

    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    html = html.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    html = html.replace(/^# (.+)$/gm, '<h1>$1</h1>');

    html = html.replace(/^\|(.+)\|$/gm, function(line) {
        if (/^\|[\s\-:|]+\|$/.test(line)) return '<!--sep-->';
        const cells = line.split('|').filter((_, i, a) => i > 0 && i < a.length - 1);
        return '<tr>' + cells.map(c => '<td>' + c.trim() + '</td>').join('') + '</tr>';
    });
    html = html.replace(/((<tr>.*<\/tr>\n?)+)/g, function(block) {
        block = block.replace('<!--sep-->', '');
        const firstRow = block.match(/<tr>(.*?)<\/tr>/);
        if (firstRow) {
            const headerRow = firstRow[1].replace(/<td>/g, '<th>').replace(/<\/td>/g, '</th>');
            block = block.replace(firstRow[0], '<thead><tr>' + headerRow + '</tr></thead>');
        }
        return '<table>' + block + '</table>';
    });

    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>');
    html = html.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');
    html = html.replace(/^---$/gm, '<hr>');
    html = html.replace(/^- (.+)$/gm, '<li>$1</li>');
    html = html.replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>');
    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

    const lines = html.split('\n');
    let result = [];
    let inBlock = false;
    lines.forEach(line => {
        if (line.startsWith('<h') || line.startsWith('<pre') || line.startsWith('<ul') || line.startsWith('<ol') || line.startsWith('<table') || line.startsWith('<blockquote') || line.startsWith('<hr') || line.startsWith('<li') || line.startsWith('</')) {
            inBlock = false;
            result.push(line);
        } else if (line.trim() === '') {
            inBlock = false;
            result.push('');
        } else if (!inBlock && !line.startsWith('<')) {
            result.push('<p>' + line + '</p>');
        } else {
            result.push(line);
        }
    });

    return result.join('\n');
}

function addCopyButtons() {
    document.querySelectorAll('#artContent pre').forEach(pre => {
        const btn = document.createElement('button');
        btn.className = 'copy-btn';
        btn.textContent = '복사';
        btn.onclick = function() {
            const code = pre.querySelector('code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                btn.textContent = '완료!';
                btn.classList.add('copied');
                setTimeout(() => { btn.textContent = '복사'; btn.classList.remove('copied'); }, 1500);
            });
        };
        pre.style.position = 'relative';
        pre.appendChild(btn);
    });
}

function toggleEdit() {
    const viewArea = document.getElementById('viewArea');
    const editArea = document.getElementById('editArea');
    const btn = document.getElementById('btnEdit');

    if (editArea.style.display === 'block') {
        editArea.style.display = 'none';
        viewArea.style.display = 'block';
        btn.textContent = '수정';
    } else {
        document.getElementById('eTitle').value = articleData.title;
        document.getElementById('eTags').value = articleData.tags;
        document.getElementById('eCat').value = articleData.category;
        document.getElementById('eContent').value = articleData.content;
        viewArea.style.display = 'none';
        editArea.style.display = 'block';
        btn.textContent = '취소';
    }
}

function saveEdit() {
    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('id', ART_ID);
    fd.append('title', document.getElementById('eTitle').value);
    fd.append('content', document.getElementById('eContent').value);
    fd.append('tags', document.getElementById('eTags').value);
    fd.append('category', document.getElementById('eCat').value);

    fetch('api.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                loadArticle();
                document.getElementById('editArea').style.display = 'none';
                document.getElementById('viewArea').style.display = 'block';
                document.getElementById('btnEdit').textContent = '수정';
            } else {
                alert(d.error || '저장 실패');
            }
        });
}

function deleteDoc() {
    if (!confirm('이 문서를 삭제하시겠습니까?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', ART_ID);
    fetch('api.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) location.href = 'index.php';
            else alert('삭제 실패');
        });
}

function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

loadArticle();
</script>
</body>
</html>
