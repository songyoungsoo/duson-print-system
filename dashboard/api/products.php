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
        
    case 'update':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        $money = $_POST['money'] ?? '';
        
        if (!isset($PRODUCT_TYPES[$type]) || $id <= 0) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $table = $PRODUCT_TYPES[$type]['table'];
        $query = "UPDATE `{$table}` SET money = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "si", $money, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            jsonResponse(true, 'Product updated successfully');
        } else {
            jsonResponse(false, 'Failed to update product');
        }
        break;
        
    default:
        jsonResponse(false, 'Invalid action');
}
