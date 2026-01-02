<?php
/**
 * 제품 데이터 저장/수정 핸들러
 *
 * POST 데이터를 받아 해당 제품 테이블에 저장합니다.
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

// 이미 product_manager.php에서 세션과 DB 연결이 되어 있음

$product = $_POST['product'] ?? '';
$id = $_POST['id'] ?? '';

if (empty($product) || !ProductConfig::isValidProduct($product)) {
    $_SESSION['error'] = '잘못된 제품 코드입니다.';
    header("Location: product_manager.php");
    exit;
}

$config = ProductConfig::getConfig($product);
$table = $config['table'];
$columns = $config['columns'];

// ID가 있으면 업데이트, 없으면 신규 등록
if (!empty($id)) {
    // UPDATE
    $update_fields = [];
    $update_values = [];
    $types = '';

    foreach ($columns as $key => $db_column) {
        if ($db_column === 'no') continue; // ID는 업데이트 안 함

        if (isset($_POST[$db_column])) {
            $update_fields[] = "{$db_column} = ?";
            $value = $_POST[$db_column];

            // 숫자 필드 타입 결정
            if ($db_column === 'money' || $db_column === 'DesignMoney' ||
                $db_column === 'quantity' || $db_column === 'mesu' ||
                $db_column === 'garo' || $db_column === 'sero') {
                $types .= 'i';
                $update_values[] = (int)$value;
            } else {
                $types .= 's';
                $update_values[] = $value;
            }
        }
    }

    if (empty($update_fields)) {
        $_SESSION['error'] = '수정할 데이터가 없습니다.';
        header("Location: product_manager.php?product={$product}&action=edit&id={$id}");
        exit;
    }

    // WHERE 조건용 ID 추가
    $types .= 'i';
    $update_values[] = $id;

    $query = "UPDATE {$table} SET " . implode(', ', $update_fields) . " WHERE no = ?";

    // 🔴 CRITICAL: bind_param 검증
    // Placeholders: count($update_fields) + 1 (WHERE)
    // Type string length: strlen($types)
    // Variables: count($update_values)
    $placeholder_count = count($update_fields) + 1;
    $type_length = strlen($types);
    $value_count = count($update_values);

    if ($placeholder_count !== $type_length || $type_length !== $value_count) {
        error_log("bind_param mismatch: placeholders={$placeholder_count}, types={$type_length}, values={$value_count}");
        $_SESSION['error'] = 'bind_param 검증 실패: 파라미터 개수 불일치';
        header("Location: product_manager.php?product={$product}&action=edit&id={$id}");
        exit;
    }

    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        $_SESSION['error'] = 'SQL 준비 실패: ' . mysqli_error($db);
        header("Location: product_manager.php?product={$product}&action=edit&id={$id}");
        exit;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$update_values);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = '수정되었습니다.';
        mysqli_stmt_close($stmt);
        header("Location: product_manager.php?product={$product}&action=view&id={$id}");
        exit;
    } else {
        $_SESSION['error'] = '수정 실패: ' . mysqli_error($db);
        mysqli_stmt_close($stmt);
        header("Location: product_manager.php?product={$product}&action=edit&id={$id}");
        exit;
    }

} else {
    // INSERT (신규 등록)
    $insert_fields = [];
    $insert_placeholders = [];
    $insert_values = [];
    $types = '';

    foreach ($columns as $key => $db_column) {
        if ($db_column === 'no') continue; // ID는 AUTO_INCREMENT

        if (isset($_POST[$db_column])) {
            $insert_fields[] = $db_column;
            $insert_placeholders[] = '?';
            $value = $_POST[$db_column];

            // 숫자 필드 타입 결정
            if ($db_column === 'money' || $db_column === 'DesignMoney' ||
                $db_column === 'quantity' || $db_column === 'mesu' ||
                $db_column === 'garo' || $db_column === 'sero') {
                $types .= 'i';
                $insert_values[] = (int)$value;
            } else {
                $types .= 's';
                $insert_values[] = $value;
            }
        }
    }

    if (empty($insert_fields)) {
        $_SESSION['error'] = '입력할 데이터가 없습니다.';
        header("Location: product_manager.php?product={$product}");
        exit;
    }

    $query = "INSERT INTO {$table} (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $insert_placeholders) . ")";

    // 🔴 CRITICAL: bind_param 검증
    // Placeholders: count($insert_placeholders)
    // Type string length: strlen($types)
    // Variables: count($insert_values)
    $placeholder_count = count($insert_placeholders);
    $type_length = strlen($types);
    $value_count = count($insert_values);

    if ($placeholder_count !== $type_length || $type_length !== $value_count) {
        error_log("bind_param mismatch: placeholders={$placeholder_count}, types={$type_length}, values={$value_count}");
        $_SESSION['error'] = 'bind_param 검증 실패: 파라미터 개수 불일치';
        header("Location: product_manager.php?product={$product}");
        exit;
    }

    $stmt = mysqli_prepare($db, $query);

    if (!$stmt) {
        $_SESSION['error'] = 'SQL 준비 실패: ' . mysqli_error($db);
        header("Location: product_manager.php?product={$product}");
        exit;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$insert_values);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($db);
        $_SESSION['message'] = '등록되었습니다.';
        mysqli_stmt_close($stmt);
        header("Location: product_manager.php?product={$product}&action=view&id={$new_id}");
        exit;
    } else {
        $_SESSION['error'] = '등록 실패: ' . mysqli_error($db);
        mysqli_stmt_close($stmt);
        header("Location: product_manager.php?product={$product}");
        exit;
    }
}
?>
