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
        
    // ============================================================
    // 카테고리 관리 (mlangprintauto_transactioncate)
    // ============================================================
    
    case 'category_list':
        global $PRODUCT_TYPES;
        $type = $_GET['type'] ?? '';
        
        if (!isset($PRODUCT_TYPES[$type])) {
            jsonResponse(false, 'Invalid product type');
        }
        
        $ttable = $PRODUCT_TYPES[$type]['ttable'] ?? '';
        if (empty($ttable)) {
            jsonResponse(false, 'No ttable configured for this product type');
        }
        
        $query = "SELECT no, BigNo, title, TreeNo, Ttable FROM mlangprintauto_transactioncate WHERE Ttable = ? ORDER BY BigNo ASC, TreeNo ASC, no ASC";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "s", $ttable);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        jsonResponse(true, 'Categories retrieved', $categories);
        break;
    
    case 'category_create':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $level = $_POST['level'] ?? 'style'; // style, section, tree
        $parentNo = $_POST['parentNo'] ?? '0';
        
        if (!isset($PRODUCT_TYPES[$type])) {
            jsonResponse(false, 'Invalid product type');
        }
        
        $ttable = $PRODUCT_TYPES[$type]['ttable'] ?? '';
        if (empty($ttable)) {
            jsonResponse(false, 'No ttable configured');
        }
        
        if (empty($title)) {
            jsonResponse(false, '카테고리명을 입력하세요');
        }
        
        // 중복 체크
        $dupQuery = "SELECT no FROM mlangprintauto_transactioncate WHERE Ttable = ? AND title = ? AND BigNo = ? AND TreeNo = ?";
        $dupStmt = mysqli_prepare($db, $dupQuery);
        
        if ($level === 'style') {
            $bigNo = '0';
            $treeNo = '';
        } elseif ($level === 'section') {
            $bigNo = $parentNo;
            $treeNo = '';
        } else { // tree
            $bigNo = '';
            $treeNo = $parentNo;
        }
        
        mysqli_stmt_bind_param($dupStmt, "ssss", $ttable, $title, $bigNo, $treeNo);
        mysqli_stmt_execute($dupStmt);
        $dupResult = mysqli_stmt_get_result($dupStmt);
        if (mysqli_fetch_assoc($dupResult)) {
            jsonResponse(false, '동일한 이름의 카테고리가 이미 존재합니다');
        }
        mysqli_stmt_close($dupStmt);
        
        // INSERT
        $insertQuery = "INSERT INTO mlangprintauto_transactioncate (Ttable, BigNo, title, TreeNo) VALUES (?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($db, $insertQuery);
        
        // bind_param 3단계 검증
        $placeholder_count = substr_count($insertQuery, '?'); // 4
        $type_string = "ssss";
        $type_count = strlen($type_string); // 4
        $var_count = 4; // ttable, bigNo, title, treeNo
        
        if ($placeholder_count !== $type_count || $type_count !== $var_count) {
            jsonResponse(false, 'Internal bind_param mismatch');
        }
        
        mysqli_stmt_bind_param($insertStmt, $type_string, $ttable, $bigNo, $title, $treeNo);
        
        if (mysqli_stmt_execute($insertStmt)) {
            $newId = mysqli_insert_id($db);
            jsonResponse(true, '카테고리가 추가되었습니다', ['id' => $newId, 'title' => $title]);
        } else {
            jsonResponse(false, '카테고리 추가 실패: ' . mysqli_error($db));
        }
        break;
    
    case 'category_delete':
        global $PRODUCT_TYPES;
        $type = $_POST['type'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        
        if (!isset($PRODUCT_TYPES[$type]) || $id <= 0) {
            jsonResponse(false, 'Invalid parameters');
        }
        
        $ttable = $PRODUCT_TYPES[$type]['ttable'] ?? '';
        $productTable = $PRODUCT_TYPES[$type]['table'];
        
        // 해당 카테고리 조회
        $checkQuery = "SELECT no, BigNo, title, TreeNo FROM mlangprintauto_transactioncate WHERE no = ?";
        $checkStmt = mysqli_prepare($db, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "i", $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        $category = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($checkStmt);
        
        if (!$category) {
            jsonResponse(false, '카테고리를 찾을 수 없습니다');
        }
        
        // 가격 데이터에서 사용 중인지 확인 (style 또는 Section 또는 TreeSelect로 참조)
        $usageCount = 0;
        $usageQuery = "SELECT COUNT(*) as cnt FROM `{$productTable}` WHERE style = ? OR Section = ?";
        $usageStmt = mysqli_prepare($db, $usageQuery);
        $idStr = strval($id);
        mysqli_stmt_bind_param($usageStmt, "ss", $idStr, $idStr);
        mysqli_stmt_execute($usageStmt);
        $usageResult = mysqli_stmt_get_result($usageStmt);
        $usageCount = mysqli_fetch_assoc($usageResult)['cnt'];
        mysqli_stmt_close($usageStmt);
        
        if ($usageCount > 0) {
            jsonResponse(false, "이 카테고리를 참조하는 가격 데이터가 {$usageCount}건 있습니다. 가격 데이터를 먼저 삭제하세요.");
        }
        
        $isStyle = ($category['BigNo'] === '0' || $category['BigNo'] === 0);
        
        if ($isStyle) {
            // 스타일(최상위)이면 하위 섹션/종이도 함께 삭제
            // 하위 항목이 가격 데이터에서 사용 중인지 확인
            $childQuery = "SELECT no FROM mlangprintauto_transactioncate WHERE BigNo = ? OR TreeNo = ?";
            $childStmt = mysqli_prepare($db, $childQuery);
            mysqli_stmt_bind_param($childStmt, "ss", $idStr, $idStr);
            mysqli_stmt_execute($childStmt);
            $childResult = mysqli_stmt_get_result($childStmt);
            
            $childIds = [];
            while ($childRow = mysqli_fetch_assoc($childResult)) {
                $childIds[] = $childRow['no'];
            }
            mysqli_stmt_close($childStmt);
            
            // 하위 항목 가격 참조 확인
            if (!empty($childIds)) {
                foreach ($childIds as $childId) {
                    $childIdStr = strval($childId);
                    $childUsageQuery = "SELECT COUNT(*) as cnt FROM `{$productTable}` WHERE style = ? OR Section = ?";
                    $childUsageStmt = mysqli_prepare($db, $childUsageQuery);
                    mysqli_stmt_bind_param($childUsageStmt, "ss", $childIdStr, $childIdStr);
                    mysqli_stmt_execute($childUsageStmt);
                    $childUsageResult = mysqli_stmt_get_result($childUsageStmt);
                    $childUsage = mysqli_fetch_assoc($childUsageResult)['cnt'];
                    mysqli_stmt_close($childUsageStmt);
                    
                    if ($childUsage > 0) {
                        jsonResponse(false, "하위 카테고리(no:{$childId})를 참조하는 가격 데이터가 {$childUsage}건 있습니다. 가격 데이터를 먼저 삭제하세요.");
                    }
                }
                
                // 하위 삭제
                $delChildQuery = "DELETE FROM mlangprintauto_transactioncate WHERE BigNo = ? OR TreeNo = ?";
                $delChildStmt = mysqli_prepare($db, $delChildQuery);
                mysqli_stmt_bind_param($delChildStmt, "ss", $idStr, $idStr);
                mysqli_stmt_execute($delChildStmt);
                $deletedChildren = mysqli_stmt_affected_rows($delChildStmt);
                mysqli_stmt_close($delChildStmt);
            }
        }
        
        // 본 카테고리 삭제
        $delQuery = "DELETE FROM mlangprintauto_transactioncate WHERE no = ?";
        $delStmt = mysqli_prepare($db, $delQuery);
        mysqli_stmt_bind_param($delStmt, "i", $id);
        
        if (mysqli_stmt_execute($delStmt)) {
            $affected = mysqli_stmt_affected_rows($delStmt);
            if ($affected > 0) {
                $msg = "카테고리 '{$category['title']}'이(가) 삭제되었습니다.";
                if ($isStyle && !empty($childIds)) {
                    $msg .= " (하위 " . count($childIds) . "건도 함께 삭제)";
                }
                jsonResponse(true, $msg);
            } else {
                jsonResponse(false, '카테고리를 찾을 수 없습니다');
            }
        } else {
            jsonResponse(false, '삭제 실패: ' . mysqli_error($db));
        }
        break;
    
    default:
        jsonResponse(false, 'Invalid action');
}
