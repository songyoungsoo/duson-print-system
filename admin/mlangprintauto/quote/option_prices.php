<?php
/**
 * 추가옵션 가격 관리 페이지
 * 경로: /admin/mlangprintauto/quote/option_prices.php
 *
 * additional_options_config 테이블의 옵션 가격을 엑셀처럼 인라인 편집
 */
require_once __DIR__ . '/../../includes/admin_auth.php';
requireAdminAuth();

require_once __DIR__ . '/../../../db.php';
if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

// --- AJAX POST 처리 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청']);
        exit;
    }

    if (!verifyCsrfToken($input['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF 토큰 오류. 페이지를 새로고침 해주세요.']);
        exit;
    }
    regenerateCsrfToken();

    $action = $input['action'] ?? '';

    try {
        if ($action === 'save_all') {
            $rows = $input['rows'] ?? [];
            $updated = 0;
            $inserted = 0;

            foreach ($rows as $row) {
                $id = intval($row['id'] ?? 0);
                $option_category = trim($row['option_category'] ?? '');
                $option_type = trim($row['option_type'] ?? '');
                $option_name = trim($row['option_name'] ?? '');
                $base_price = intval($row['base_price'] ?? 0);
                $per_qty = intval($row['per_qty'] ?? 0);
                $is_active = intval($row['is_active'] ?? 1);
                $sort_order = intval($row['sort_order'] ?? 0);

                if (empty($option_category) || empty($option_type) || empty($option_name)) {
                    continue;
                }

                if ($id > 0) {
                    // UPDATE
                    $stmt = $db->prepare("UPDATE additional_options_config SET option_category=?, option_type=?, option_name=?, base_price=?, per_qty=?, is_active=?, sort_order=? WHERE id=?");
                    $stmt->bind_param("sssiiiii", $option_category, $option_type, $option_name, $base_price, $per_qty, $is_active, $sort_order, $id);
                    $stmt->execute();
                    if ($stmt->affected_rows >= 0) $updated++;
                    $stmt->close();
                } else {
                    // INSERT
                    $stmt = $db->prepare("INSERT INTO additional_options_config (option_category, option_type, option_name, base_price, per_qty, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssiiii", $option_category, $option_type, $option_name, $base_price, $per_qty, $is_active, $sort_order);
                    $stmt->execute();
                    if ($stmt->insert_id > 0) $inserted++;
                    $stmt->close();
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "저장 완료 (수정: {$updated}, 추가: {$inserted})",
                'csrf_token' => generateCsrfToken()
            ]);
            exit;
        }

        if ($action === 'delete') {
            $id = intval($input['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare("DELETE FROM additional_options_config WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(['success' => true, 'message' => '삭제 완료', 'csrf_token' => generateCsrfToken()]);
            } else {
                echo json_encode(['success' => false, 'message' => '유효하지 않은 ID']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => '알 수 없는 action']);
    } catch (Exception $e) {
        error_log('[option_prices] ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다.']);
    }
    exit;
}

// --- GET: 데이터 로드 ---
$rows = [];
$result = $db->query("SELECT * FROM additional_options_config ORDER BY option_category, sort_order, id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$csrfToken = generateCsrfToken();

$categoryLabels = [
    'coating' => '코팅',
    'folding' => '접지',
    'creasing' => '오시',
    'envelope_tape' => '봉투풀띠',
    'premium' => '프리미엄'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>추가옵션 가격 관리</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; font-size: 13px; background: #e8e8e8; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; padding: 10px; }

        /* Header */
        .page-header {
            background: #1E4E79;
            color: white;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-radius: 8px;
        }
        .page-header h1 { font-size: 15px; font-weight: normal; }
        .header-actions { display: flex; gap: 8px; align-items: center; }

        /* Buttons */
        .btn {
            padding: 5px 14px;
            border: 1px solid #ababab;
            background: linear-gradient(to bottom, #f5f5f5 0%, #e0e0e0 100%);
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            display: inline-block;
            font-family: inherit;
            border-radius: 4px;
        }
        .btn:hover { background: linear-gradient(to bottom, #e8e8e8 0%, #d0d0d0 100%); }
        .btn-primary { background: linear-gradient(to bottom, #1E4E79 0%, #163D5C 100%); border-color: #153A5A; color: white; }
        .btn-primary:hover { background: linear-gradient(to bottom, #163D5C 0%, #153A5A 100%); }
        .btn-save { background: #1E4E79; border-color: #153A5A; color: white; font-weight: 500; padding: 6px 20px; }
        .btn-save:hover { background: #163D5C; }
        .btn-danger { background: #d9534f; border-color: #c9302c; color: white; }
        .btn-danger:hover { background: #c9302c; }
        .btn-sm { padding: 2px 8px; font-size: 11px; }
        .back-link { color: white; text-decoration: none; font-size: 12px; opacity: 0.9; }
        .back-link:hover { opacity: 1; }

        /* Table - Dashboard Style */
        .table-wrap { background: white; border: 1px solid #e5e7eb; margin-bottom: 10px; border-radius: 8px; overflow: hidden; }
        .category-header {
            background: #f0f4f8;
            padding: 6px 12px;
            font-weight: 700;
            font-size: 13px;
            border-bottom: 2px solid #e5e7eb;
            color: #1E4E79;
        }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #f9fafb;
            color: #6b7280;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            letter-spacing: 0.025em;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        tbody td {
            padding: 2px 4px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
        tbody tr { height: 33px; }
        tbody tr:nth-child(odd) { background: #fff; }
        tbody tr:nth-child(even) { background: #e6f7ff; }
        tbody tr:hover { background: #dbeafe; }
        tbody tr.new-row { background: #fffde7; }
        tbody tr.category-row { height: auto; background: transparent; }

        /* Inputs */
        .cell-input {
            width: 100%;
            border: 1px solid transparent;
            padding: 4px 6px;
            font-size: 12px;
            font-family: inherit;
            background: transparent;
            text-align: inherit;
        }
        .cell-input:focus { border-color: #1E4E79; outline: none; background: #fff; }
        .cell-input.num { text-align: right; }
        .cell-input.changed { background: #fef3c7; }
        td.num-cell { text-align: right; }
        td.center-cell { text-align: center; }
        .cell-select {
            width: 100%;
            border: 1px solid transparent;
            padding: 4px 2px;
            font-size: 12px;
            font-family: inherit;
            background: transparent;
            cursor: pointer;
        }
        .cell-select:focus { border-color: #1E4E79; outline: none; }
        input[type="checkbox"] { cursor: pointer; width: 16px; height: 16px; }

        /* Feedback */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 4px;
            color: white;
            font-size: 13px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .toast.show { opacity: 1; }
        .toast.success { background: #16a34a; }
        .toast.error { background: #dc2626; }

        /* Toolbar */
        .toolbar {
            background: #f5f5f5;
            border: 1px solid #d0d0d0;
            padding: 6px 12px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .toolbar-info { font-size: 12px; color: #666; }
        .toolbar-info .count { font-weight: 700; color: #333; }

        /* Delete button in cell */
        .btn-del {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 14px;
            padding: 2px 6px;
        }
        .btn-del:hover { color: #dc2626; }
    </style>
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1>추가옵션 가격 관리</h1>
        <div class="header-actions">
            <a href="/admin/mlangprintauto/quote/" class="back-link">&larr; 견적서 목록</a>
            <a href="/admin/mlangprintauto/" class="back-link">&larr; 관리자홈</a>
        </div>
    </div>

    <div class="toolbar">
        <div>
            <button type="button" class="btn btn-save" onclick="saveAll()">전체 저장</button>
            <button type="button" class="btn" onclick="addRow()">+ 행 추가</button>
        </div>
        <div class="toolbar-info">
            총 <span class="count" id="rowCount"><?php echo count($rows); ?></span>개 옵션
            <span id="changedInfo" style="margin-left:10px; color:#b45309; display:none;">변경사항 있음</span>
        </div>
    </div>

    <div class="table-wrap">
        <table id="optionTable">
            <thead>
                <tr>
                    <th style="width:30px">ID</th>
                    <th style="width:120px">카테고리</th>
                    <th style="width:120px">타입코드</th>
                    <th style="width:160px">옵션명</th>
                    <th style="width:110px">기본가격</th>
                    <th style="width:90px">수량단위(per_qty)</th>
                    <th style="width:50px">활성</th>
                    <th style="width:60px">정렬</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="optionBody">
<?php
$currentCategory = '';
foreach ($rows as $row):
    if ($row['option_category'] !== $currentCategory):
        $currentCategory = $row['option_category'];
        $label = $categoryLabels[$currentCategory] ?? $currentCategory;
?>
                <tr class="category-row" data-category="<?php echo htmlspecialchars($currentCategory); ?>">
                    <td colspan="9" class="category-header"><?php echo htmlspecialchars($label); ?> (<?php echo htmlspecialchars($currentCategory); ?>)</td>
                </tr>
<?php endif; ?>
                <tr data-id="<?php echo $row['id']; ?>" data-orig='<?php echo htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE)); ?>'>
                    <td class="center-cell" style="color:#999; font-size:11px;"><?php echo $row['id']; ?></td>
                    <td>
                        <select class="cell-select" data-field="option_category" onchange="markChanged(this)">
<?php foreach ($categoryLabels as $val => $lbl): ?>
                            <option value="<?php echo $val; ?>"<?php echo $row['option_category'] === $val ? ' selected' : ''; ?>><?php echo $lbl; ?></option>
<?php endforeach; ?>
                        </select>
                    </td>
                    <td><input class="cell-input" data-field="option_type" value="<?php echo htmlspecialchars($row['option_type']); ?>" onchange="markChanged(this)"></td>
                    <td><input class="cell-input" data-field="option_name" value="<?php echo htmlspecialchars($row['option_name']); ?>" onchange="markChanged(this)"></td>
                    <td class="num-cell"><input class="cell-input num" data-field="base_price" type="number" value="<?php echo $row['base_price']; ?>" onchange="markChanged(this)"></td>
                    <td class="num-cell"><input class="cell-input num" data-field="per_qty" type="number" value="<?php echo $row['per_qty']; ?>" onchange="markChanged(this)"></td>
                    <td class="center-cell"><input type="checkbox" data-field="is_active" <?php echo $row['is_active'] ? 'checked' : ''; ?> onchange="markChanged(this)"></td>
                    <td class="num-cell"><input class="cell-input num" data-field="sort_order" type="number" value="<?php echo $row['sort_order']; ?>" style="width:50px" onchange="markChanged(this)"></td>
                    <td class="center-cell"><button class="btn-del" onclick="deleteRow(this)" title="삭제">&times;</button></td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
var csrfToken = '<?php echo $csrfToken; ?>';
var categories = <?php echo json_encode($categoryLabels, JSON_UNESCAPED_UNICODE); ?>;

function markChanged(el) {
    var tr = el.closest('tr');
    if (!tr.classList.contains('new-row')) {
        el.classList.add('changed');
    }
    document.getElementById('changedInfo').style.display = 'inline';
}

function getRowData(tr) {
    var data = { id: parseInt(tr.dataset.id) || 0 };
    tr.querySelectorAll('[data-field]').forEach(function(el) {
        if (el.type === 'checkbox') {
            data[el.dataset.field] = el.checked ? 1 : 0;
        } else {
            data[el.dataset.field] = el.value;
        }
    });
    return data;
}

function isRowChanged(tr) {
    if (tr.classList.contains('new-row')) return true;
    var orig = JSON.parse(tr.dataset.orig || '{}');
    var curr = getRowData(tr);
    return ['option_category','option_type','option_name','base_price','per_qty','is_active','sort_order']
        .some(function(k) { return String(curr[k]) !== String(orig[k]); });
}

function saveAll() {
    var rows = [];
    document.querySelectorAll('#optionBody tr[data-id]').forEach(function(tr) {
        if (isRowChanged(tr)) {
            rows.push(getRowData(tr));
        }
    });

    if (rows.length === 0) {
        showToast('변경사항이 없습니다.', 'error');
        return;
    }

    fetch(location.pathname, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save_all', rows: rows, csrf_token: csrfToken })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.csrf_token) csrfToken = data.csrf_token;
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(function() { showToast('네트워크 오류', 'error'); });
}

function addRow() {
    var tbody = document.getElementById('optionBody');
    var tr = document.createElement('tr');
    tr.dataset.id = '0';
    tr.classList.add('new-row');

    // Build cells using DOM API
    var tdId = document.createElement('td');
    tdId.className = 'center-cell';
    tdId.style.cssText = 'color:#16a34a; font-size:11px;';
    tdId.textContent = 'NEW';
    tr.appendChild(tdId);

    // Category select
    var tdCat = document.createElement('td');
    var sel = document.createElement('select');
    sel.className = 'cell-select';
    sel.dataset.field = 'option_category';
    for (var k in categories) {
        var opt = document.createElement('option');
        opt.value = k;
        opt.textContent = categories[k];
        sel.appendChild(opt);
    }
    tdCat.appendChild(sel);
    tr.appendChild(tdCat);

    // Type code input
    var tdType = document.createElement('td');
    var inType = document.createElement('input');
    inType.className = 'cell-input';
    inType.dataset.field = 'option_type';
    inType.placeholder = '타입코드';
    tdType.appendChild(inType);
    tr.appendChild(tdType);

    // Option name input
    var tdName = document.createElement('td');
    var inName = document.createElement('input');
    inName.className = 'cell-input';
    inName.dataset.field = 'option_name';
    inName.placeholder = '옵션명';
    tdName.appendChild(inName);
    tr.appendChild(tdName);

    // Base price
    var tdPrice = document.createElement('td');
    tdPrice.className = 'num-cell';
    var inPrice = document.createElement('input');
    inPrice.className = 'cell-input num';
    inPrice.dataset.field = 'base_price';
    inPrice.type = 'number';
    inPrice.value = '0';
    tdPrice.appendChild(inPrice);
    tr.appendChild(tdPrice);

    // Per qty
    var tdPerQty = document.createElement('td');
    tdPerQty.className = 'num-cell';
    var inPerQty = document.createElement('input');
    inPerQty.className = 'cell-input num';
    inPerQty.dataset.field = 'per_qty';
    inPerQty.type = 'number';
    inPerQty.value = '0';
    tdPerQty.appendChild(inPerQty);
    tr.appendChild(tdPerQty);

    // Active checkbox
    var tdActive = document.createElement('td');
    tdActive.className = 'center-cell';
    var chk = document.createElement('input');
    chk.type = 'checkbox';
    chk.dataset.field = 'is_active';
    chk.checked = true;
    tdActive.appendChild(chk);
    tr.appendChild(tdActive);

    // Sort order
    var tdSort = document.createElement('td');
    tdSort.className = 'num-cell';
    var inSort = document.createElement('input');
    inSort.className = 'cell-input num';
    inSort.dataset.field = 'sort_order';
    inSort.type = 'number';
    inSort.value = '0';
    inSort.style.width = '50px';
    tdSort.appendChild(inSort);
    tr.appendChild(tdSort);

    // Delete button
    var tdDel = document.createElement('td');
    tdDel.className = 'center-cell';
    var btnDel = document.createElement('button');
    btnDel.className = 'btn-del';
    btnDel.title = '삭제';
    btnDel.textContent = '\u00D7';
    btnDel.addEventListener('click', function() { deleteRow(btnDel); });
    tdDel.appendChild(btnDel);
    tr.appendChild(tdDel);

    tbody.appendChild(tr);
    inType.focus();
    updateCount();
    document.getElementById('changedInfo').style.display = 'inline';
}

function deleteRow(btn) {
    var tr = btn.closest('tr');
    var id = parseInt(tr.dataset.id) || 0;

    if (id === 0) {
        tr.remove();
        updateCount();
        return;
    }

    if (!confirm('이 옵션을 삭제하시겠습니까? (ID: ' + id + ')')) return;

    fetch(location.pathname, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id, csrf_token: csrfToken })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.csrf_token) csrfToken = data.csrf_token;
        if (data.success) {
            tr.remove();
            showToast(data.message, 'success');
            updateCount();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(function() { showToast('네트워크 오류', 'error'); });
}

function updateCount() {
    var count = document.querySelectorAll('#optionBody tr[data-id]').length;
    document.getElementById('rowCount').textContent = count;
}

function showToast(msg, type) {
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast ' + type + ' show';
    setTimeout(function() { el.classList.remove('show'); }, 3000);
}

// Warn on page leave with unsaved changes
window.addEventListener('beforeunload', function(e) {
    var hasChanges = document.querySelector('.cell-input.changed, .new-row');
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
</body>
</html>
