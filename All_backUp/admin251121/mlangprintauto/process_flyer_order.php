<?php
declare(strict_types=1);

// 주문 처리 백엔드
header('Content-Type: application/json; charset=utf-8');
include "../../db.php";

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

// 입력 데이터 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 데이터입니다.']);
    exit;
}

// 필수 필드 검증
$required_fields = ['style', 'section', 'quantity', 'treeselect', 'po_type', 'total_cost'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "필수 필드가 누락되었습니다: {$field}"]);
        exit;
    }
}

// 데이터 정리
$order_data = [
    'style' => htmlspecialchars($input['style']),
    'section' => htmlspecialchars($input['section']),
    'quantity' => htmlspecialchars($input['quantity']),
    'treeselect' => htmlspecialchars($input['treeselect']),
    'po_type' => htmlspecialchars($input['po_type']),
    'total_cost' => htmlspecialchars($input['total_cost']),
    'customer_name' => htmlspecialchars($input['customer_name'] ?? ''),
    'customer_phone' => htmlspecialchars($input['customer_phone'] ?? ''),
    'customer_email' => htmlspecialchars($input['customer_email'] ?? ''),
    'order_memo' => htmlspecialchars($input['order_memo'] ?? ''),
    'order_date' => date('Y-m-d H:i:s'),
    'order_status' => 'pending'
];

try {
    // 주문 테이블에 삽입 (테이블이 없다면 생성)
    $create_table_query = "
        CREATE TABLE IF NOT EXISTS flyer_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_no VARCHAR(20) UNIQUE NOT NULL,
            style VARCHAR(10) NOT NULL,
            section VARCHAR(10) NOT NULL,
            quantity VARCHAR(10) NOT NULL,
            treeselect VARCHAR(10) NOT NULL,
            po_type VARCHAR(10) NOT NULL,
            total_cost VARCHAR(20) NOT NULL,
            customer_name VARCHAR(100),
            customer_phone VARCHAR(20),
            customer_email VARCHAR(100),
            order_memo TEXT,
            order_date DATETIME NOT NULL,
            order_status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    if (!mysqli_query($db, $create_table_query)) {
        throw new Exception("테이블 생성 실패: " . mysqli_error($db));
    }

    // 주문번호 생성 (FLY + YYYYMMDD + 일련번호)
    $order_prefix = 'FLY' . date('Ymd');
    $count_query = "SELECT COUNT(*) as count FROM flyer_orders WHERE order_no LIKE '{$order_prefix}%'";
    $count_result = mysqli_query($db, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $order_no = $order_prefix . sprintf('%03d', $count_row['count'] + 1);

    // 주문 데이터 삽입
    $insert_query = "
        INSERT INTO flyer_orders (
            order_no, style, section, quantity, treeselect, po_type, total_cost,
            customer_name, customer_phone, customer_email, order_memo,
            order_date, order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($db, $insert_query);
    if (!$stmt) {
        throw new Exception("쿼리 준비 실패: " . mysqli_error($db));
    }

    mysqli_stmt_bind_param($stmt, 'sssssssssssss',
        $order_no,
        $order_data['style'],
        $order_data['section'],
        $order_data['quantity'],
        $order_data['treeselect'],
        $order_data['po_type'],
        $order_data['total_cost'],
        $order_data['customer_name'],
        $order_data['customer_phone'],
        $order_data['customer_email'],
        $order_data['order_memo'],
        $order_data['order_date'],
        $order_data['order_status']
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("주문 저장 실패: " . mysqli_stmt_error($stmt));
    }

    $insert_id = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);

    // 성공 응답
    echo json_encode([
        'success' => true,
        'message' => '주문이 성공적으로 접수되었습니다.',
        'data' => [
            'order_id' => $insert_id,
            'order_no' => $order_no,
            'total_cost' => $order_data['total_cost']
        ]
    ]);

} catch (Exception $e) {
    // 오류 응답
    echo json_encode([
        'success' => false,
        'message' => '주문 처리 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}

// 데이터베이스 연결 종료
mysqli_close($db);
?>