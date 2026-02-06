<?php
require_once __DIR__ . '/base.php';
require_once __DIR__ . '/../includes/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'types';

switch ($action) {
    case 'types':
        global $PRODUCT_TYPES;
        $types = [];
        foreach ($PRODUCT_TYPES as $key => $config) {
            $types[] = [
                'key' => $key,
                'name' => $config['name'],
                'table' => $config['table'],
                'unit' => $config['unit']
            ];
        }
        jsonResponse(true, 'Product types', $types);
        break;
        
    case 'list':
        global $PRODUCT_TYPES;
        $type = $_GET['type'] ?? '';
        $page = intval($_GET['page'] ?? 1);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        if (!isset($PRODUCT_TYPES[$type])) {
            jsonResponse(false, 'Invalid product type');
        }
        
        $table = $PRODUCT_TYPES[$type]['table'];
        
        $count_query = "SELECT COUNT(*) as total FROM `{$table}`";
        $count_result = mysqli_query($db, $count_query);
        $total_items = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_items / $limit);
        
        $query = "SELECT * FROM `{$table}` ORDER BY no DESC LIMIT {$limit} OFFSET {$offset}";
        $result = mysqli_query($db, $query);
        
        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        
        jsonResponse(true, 'Products retrieved', [
            'data' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => intval($total_items),
                'per_page' => $limit
            ]
        ]);
        break;
        
    case 'view':
        global $PRODUCT_TYPES;
        $type = $_GET['type'] ?? '';
        $id = intval($_GET['id'] ?? 0);
        
        if (!isset($PRODUCT_TYPES[$type]) || $id <= 0) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $table = $PRODUCT_TYPES[$type]['table'];
        $query = "SELECT * FROM `{$table}` WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            jsonResponse(true, 'Product found', $row);
        } else {
            jsonResponse(false, 'Product not found');
        }
        break;
        
    case 'create':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        
        if (!isset($PRODUCT_TYPES[$type])) {
            jsonResponse(false, 'Invalid product type');
        }
        
        $config = $PRODUCT_TYPES[$type];
        $table = $config['table'];
        $hasTreeSelect = $config['hasTreeSelect'] ?? false;
        $hasPOtype = $config['hasPOtype'] ?? false;
        
        $style = $_POST['style'] ?? '';
        $section = $_POST['section'] ?? '';
        $quantity = floatval($_POST['quantity'] ?? 0);
        $money = $_POST['money'] ?? '0';
        $designMoney = $_POST['designMoney'] ?? '10000';
        $treeSelect = $hasTreeSelect ? ($_POST['treeSelect'] ?? '0') : '0';
        $poType = $hasPOtype ? ($_POST['poType'] ?? '') : '';
        
        if (empty($style) || empty($section) || $quantity <= 0) {
            jsonResponse(false, 'Required fields missing');
        }
        
        if ($hasTreeSelect && $hasPOtype) {
            $query = "INSERT INTO `{$table}` (style, Section, quantity, money, DesignMoney, TreeSelect, POtype) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssdssss", $style, $section, $quantity, $money, $designMoney, $treeSelect, $poType);
        } else if ($hasTreeSelect) {
            $query = "INSERT INTO `{$table}` (style, Section, quantity, money, DesignMoney, TreeSelect) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssdsss", $style, $section, $quantity, $money, $designMoney, $treeSelect);
        } else if ($hasPOtype) {
            $query = "INSERT INTO `{$table}` (style, Section, quantity, money, DesignMoney, POtype) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssdsss", $style, $section, $quantity, $money, $designMoney, $poType);
        } else {
            $query = "INSERT INTO `{$table}` (style, Section, quantity, money, DesignMoney) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssdss", $style, $section, $quantity, $money, $designMoney);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($db);
            jsonResponse(true, 'Product created successfully', ['id' => $newId]);
        } else {
            jsonResponse(false, 'Failed to create product: ' . mysqli_error($db));
        }
        break;
    
    case 'update':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        $money = $_POST['money'] ?? null;
        $designMoney = $_POST['designMoney'] ?? null;
        $style = $_POST['style'] ?? null;
        $section = $_POST['section'] ?? null;
        $quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : null;
        $treeSelect = $_POST['treeSelect'] ?? null;
        $poType = $_POST['poType'] ?? null;
        
        if (!isset($PRODUCT_TYPES[$type]) || $id <= 0) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $config = $PRODUCT_TYPES[$type];
        $table = $config['table'];
        $hasTreeSelect = $config['hasTreeSelect'] ?? false;
        $hasPOtype = $config['hasPOtype'] ?? false;
        
        $updates = [];
        $params = [];
        $types = '';
        
        if ($style !== null) { $updates[] = "style = ?"; $params[] = $style; $types .= 's'; }
        if ($section !== null) { $updates[] = "Section = ?"; $params[] = $section; $types .= 's'; }
        if ($quantity !== null) { $updates[] = "quantity = ?"; $params[] = $quantity; $types .= 'd'; }
        if ($money !== null) { $updates[] = "money = ?"; $params[] = $money; $types .= 's'; }
        if ($designMoney !== null) { $updates[] = "DesignMoney = ?"; $params[] = $designMoney; $types .= 's'; }
        if ($hasTreeSelect && $treeSelect !== null) { $updates[] = "TreeSelect = ?"; $params[] = $treeSelect; $types .= 's'; }
        if ($hasPOtype && $poType !== null) { $updates[] = "POtype = ?"; $params[] = $poType; $types .= 's'; }
        
        if (empty($updates)) {
            jsonResponse(false, 'No fields to update');
        }
        
        $params[] = $id;
        $types .= 'i';
        
        $query = "UPDATE `{$table}` SET " . implode(', ', $updates) . " WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Product updated successfully');
        } else {
            jsonResponse(false, 'Failed to update product');
        }
        break;
    
    case 'delete':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        
        if (!isset($PRODUCT_TYPES[$type]) || $id <= 0) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $table = $PRODUCT_TYPES[$type]['table'];
        $query = "DELETE FROM `{$table}` WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            if ($affected > 0) {
                jsonResponse(true, 'Product deleted successfully');
            } else {
                jsonResponse(false, 'Product not found');
            }
        } else {
            jsonResponse(false, 'Failed to delete product');
        }
        break;
        
    default:
        jsonResponse(false, 'Invalid action');
}
