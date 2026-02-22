<?php
session_start();
require_once __DIR__ . '/kb_auth.php';
kb_check_auth();

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'search':
        $q = trim($_GET['q'] ?? '');
        $cat = trim($_GET['category'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        if ($q === '') {
            $where = '1=1';
            $params = [];
            $types = '';
            $order = 'updated_at DESC';
        } else {
            $where = "MATCH(title, content, tags) AGAINST(? IN BOOLEAN MODE)";
            $params = [$q . '*'];
            $types = 's';
            $order = "MATCH(title, content, tags) AGAINST('" . mysqli_real_escape_string($db, $q) . "*' IN BOOLEAN MODE) DESC";
        }

        if ($cat !== '' && $cat !== 'all') {
            $where .= " AND category = ?";
            $params[] = $cat;
            $types .= 's';
        }

        $count_sql = "SELECT COUNT(*) as cnt FROM knowledge_base WHERE $where";
        $stmt = mysqli_prepare($db, $count_sql);
        if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt'];

        $sql = "SELECT id, title, LEFT(content, 200) as snippet, tags, category, created_at, updated_at FROM knowledge_base WHERE $where ORDER BY $order LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        $cats_result = mysqli_query($db, "SELECT category, COUNT(*) as cnt FROM knowledge_base GROUP BY category ORDER BY cnt DESC");
        $categories = [];
        while ($c = mysqli_fetch_assoc($cats_result)) {
            $categories[] = $c;
        }

        echo json_encode([
            'items' => $rows,
            'total' => intval($total),
            'page' => $page,
            'pages' => ceil($total / $limit),
            'categories' => $categories,
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($db, "SELECT * FROM knowledge_base WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        echo json_encode($row ?: ['error' => 'not found'], JSON_UNESCAPED_UNICODE);
        break;

    case 'create':
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $category = trim($_POST['category'] ?? 'general');

        if ($title === '' || $content === '') {
            echo json_encode(['error' => '제목과 내용은 필수입니다']);
            break;
        }

        $stmt = mysqli_prepare($db, "INSERT INTO knowledge_base (title, content, tags, category) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $title, $content, $tags, $category);
        $ok = mysqli_stmt_execute($stmt);
        echo json_encode([
            'success' => $ok,
            'id' => mysqli_insert_id($db),
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $category = trim($_POST['category'] ?? 'general');

        if ($id <= 0 || $title === '' || $content === '') {
            echo json_encode(['error' => 'ID, 제목, 내용은 필수입니다']);
            break;
        }

        $stmt = mysqli_prepare($db, "UPDATE knowledge_base SET title=?, content=?, tags=?, category=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $title, $content, $tags, $category, $id);
        echo json_encode(['success' => mysqli_stmt_execute($stmt)], JSON_UNESCAPED_UNICODE);
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['error' => 'ID 필수']);
            break;
        }
        $stmt = mysqli_prepare($db, "DELETE FROM knowledge_base WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        echo json_encode(['success' => mysqli_stmt_execute($stmt)], JSON_UNESCAPED_UNICODE);
        break;

    case 'categories':
        $result = mysqli_query($db, "SELECT DISTINCT category FROM knowledge_base ORDER BY category");
        $cats = [];
        while ($row = mysqli_fetch_assoc($result)) $cats[] = $row['category'];
        echo json_encode($cats, JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => 'unknown action', 'actions' => ['search','get','create','update','delete','categories']]);
}

if (isset($db) && $db) mysqli_close($db);
