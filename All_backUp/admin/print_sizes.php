<?php
/**
 * 인쇄 규격 관리 페이지
 *
 * @date 2025-12-03
 */

session_start();
require_once __DIR__ . '/../db.php';

// 인증 체크 (필요시 활성화)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: /admin/login.php');
//     exit;
// }

$message = '';
$messageType = '';

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $width = intval($_POST['width'] ?? 0);
            $height = intval($_POST['height'] ?? 0);
            $jeolsu = intval($_POST['jeolsu'] ?? 0);
            $series = strtoupper(trim($_POST['series'] ?? 'A'));
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $description = trim($_POST['description'] ?? '');

            if (empty($name) || $width <= 0 || $height <= 0 || $jeolsu <= 0) {
                $message = '필수 항목을 모두 입력해주세요.';
                $messageType = 'error';
            } else {
                $sheets_per_yeon = 500 * $jeolsu;  // 1연당 매수 계산
                $stmt = mysqli_prepare($db, "INSERT INTO print_sizes (name, width, height, jeolsu, series, sheets_per_yeon, sort_order, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "siiisiis", $name, $width, $height, $jeolsu, $series, $sheets_per_yeon, $sort_order, $description);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "'{$name}' 규격이 추가되었습니다.";
                    $messageType = 'success';
                } else {
                    $message = '추가 실패: ' . mysqli_error($db);
                    $messageType = 'error';
                }
                mysqli_stmt_close($stmt);
            }
            break;

        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $width = intval($_POST['width'] ?? 0);
            $height = intval($_POST['height'] ?? 0);
            $jeolsu = intval($_POST['jeolsu'] ?? 0);
            $series = strtoupper(trim($_POST['series'] ?? 'A'));
            $sort_order = intval($_POST['sort_order'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($id <= 0) {
                $message = '잘못된 요청입니다.';
                $messageType = 'error';
            } else {
                $sheets_per_yeon = 500 * $jeolsu;  // 1연당 매수 계산
                $stmt = mysqli_prepare($db, "UPDATE print_sizes SET name=?, width=?, height=?, jeolsu=?, series=?, sheets_per_yeon=?, sort_order=?, description=?, is_active=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "siiisiisii", $name, $width, $height, $jeolsu, $series, $sheets_per_yeon, $sort_order, $description, $is_active, $id);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "'{$name}' 규격이 수정되었습니다.";
                    $messageType = 'success';
                } else {
                    $message = '수정 실패: ' . mysqli_error($db);
                    $messageType = 'error';
                }
                mysqli_stmt_close($stmt);
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = mysqli_prepare($db, "DELETE FROM print_sizes WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "규격이 삭제되었습니다.";
                    $messageType = 'success';
                } else {
                    $message = '삭제 실패: ' . mysqli_error($db);
                    $messageType = 'error';
                }
                mysqli_stmt_close($stmt);
            }
            break;

        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                mysqli_query($db, "UPDATE print_sizes SET is_active = NOT is_active WHERE id = $id");
                $message = "상태가 변경되었습니다.";
                $messageType = 'success';
            }
            break;
    }
}

// 규격 목록 조회
$sizes = [];
$result = mysqli_query($db, "SELECT * FROM print_sizes ORDER BY series ASC, sort_order ASC, jeolsu ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sizes[] = $row;
    }
}

// 수정 모드 체크
$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = mysqli_prepare($db, "SELECT * FROM print_sizes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $editId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editData = mysqli_fetch_assoc($result);
    if ($editData) {
        $editMode = true;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>인쇄 규격 관리 - 두손기획</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1E4E79;
        }
        .message {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* 폼 스타일 */
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-section h2 {
            color: #1E4E79;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 120px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
            color: #555;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #1E4E79;
            outline: none;
        }
        .form-group.wide { flex: 2; }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-primary {
            background: #1E4E79;
            color: white;
        }
        .btn-primary:hover { background: #163a5c; }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover { background: #c82333; }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* 테이블 스타일 */
        .table-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: #1E4E79;
            color: white;
            padding: 15px 20px;
        }
        .table-header h2 {
            margin: 0;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 13px;
        }
        tr:hover { background: #f8f9fa; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .series-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .series-A { background: #e3f2fd; color: #1565c0; }
        .series-B { background: #fff3e0; color: #e65100; }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }

        .actions {
            display: flex;
            gap: 5px;
        }

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #0056b3;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .form-group { min-width: 100%; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📐 인쇄 규격 관리</h1>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="info-box">
            💡 <strong>1연 = 전지 500장</strong> | 매수 = 500 × 절수 × 연수
            | 예: A4 (8절) 1연 = 500 × 8 = 4,000장
        </div>

        <!-- 추가/수정 폼 -->
        <div class="form-section">
            <h2><?php echo $editMode ? '✏️ 규격 수정' : '➕ 새 규격 추가'; ?></h2>

            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'add'; ?>">
                <?php if ($editMode): ?>
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>규격명 *</label>
                        <input type="text" name="name" required placeholder="예: A4"
                               value="<?php echo $editMode ? htmlspecialchars($editData['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>시리즈 *</label>
                        <select name="series" required>
                            <option value="A" <?php echo ($editMode && $editData['series'] === 'A') ? 'selected' : ''; ?>>A 시리즈</option>
                            <option value="B" <?php echo ($editMode && $editData['series'] === 'B') ? 'selected' : ''; ?>>B 시리즈 (JIS)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>가로 (mm) *</label>
                        <input type="number" name="width" required min="1" placeholder="210"
                               value="<?php echo $editMode ? $editData['width'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>세로 (mm) *</label>
                        <input type="number" name="height" required min="1" placeholder="297"
                               value="<?php echo $editMode ? $editData['height'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>절수 *</label>
                        <input type="number" name="jeolsu" required min="1" placeholder="8"
                               value="<?php echo $editMode ? $editData['jeolsu'] : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>정렬 순서</label>
                        <input type="number" name="sort_order" min="0" placeholder="0"
                               value="<?php echo $editMode ? $editData['sort_order'] : '0'; ?>">
                    </div>
                    <div class="form-group wide">
                        <label>설명</label>
                        <input type="text" name="description" placeholder="설명 (선택사항)"
                               value="<?php echo $editMode ? htmlspecialchars($editData['description'] ?? '') : ''; ?>">
                    </div>
                    <?php if ($editMode): ?>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_active" id="is_active"
                                   <?php echo $editData['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active" style="margin:0;">활성화</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editMode ? '수정 저장' : '추가'; ?>
                    </button>
                    <?php if ($editMode): ?>
                    <a href="print_sizes.php" class="btn btn-secondary">취소</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 규격 목록 -->
        <div class="table-section">
            <div class="table-header">
                <h2>📋 등록된 규격 (<?php echo count($sizes); ?>개)</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="text-center">시리즈</th>
                        <th>규격명</th>
                        <th class="text-right">가로</th>
                        <th class="text-right">세로</th>
                        <th class="text-center">절수</th>
                        <th class="text-right">1연당 매수</th>
                        <th>설명</th>
                        <th class="text-center">상태</th>
                        <th class="text-center">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sizes)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding:30px; color:#999;">
                            등록된 규격이 없습니다.
                            <a href="create_print_sizes_table.php">초기 데이터 생성</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sizes as $size): ?>
                    <tr>
                        <td class="text-center">
                            <span class="series-badge series-<?php echo $size['series']; ?>">
                                <?php echo $size['series']; ?>
                            </span>
                        </td>
                        <td><strong><?php echo htmlspecialchars($size['name']); ?></strong></td>
                        <td class="text-right"><?php echo number_format($size['width']); ?>mm</td>
                        <td class="text-right"><?php echo number_format($size['height']); ?>mm</td>
                        <td class="text-center"><?php echo $size['jeolsu']; ?>절</td>
                        <td class="text-right"><?php echo number_format($size['sheets_per_yeon']); ?>장</td>
                        <td style="color:#666; font-size:12px;">
                            <?php echo htmlspecialchars($size['description'] ?? ''); ?>
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-<?php echo $size['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $size['is_active'] ? '활성' : '비활성'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?edit=<?php echo $size['id']; ?>" class="btn btn-secondary btn-sm">수정</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $size['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">삭제</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px; text-align:center; color:#666; font-size:12px;">
            <a href="/admin/">← 관리자 메인으로</a>
        </div>
    </div>
</body>
</html>
