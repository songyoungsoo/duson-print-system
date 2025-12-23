<?php
/**
 * 테스트용 세금계산서 샘플 데이터 생성
 * 경로: /admin/create_sample_tax_invoices.php
 * 실행: http://localhost/admin/create_sample_tax_invoices.php
 */

session_start();

// 관리자 권한 확인 (개발 중에는 주석 처리 가능)
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     die('관리자 권한이 필요합니다.');
// }

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// db.php에서 이미 연결된 $db 사용
$mysqli = $db;

echo "<!DOCTYPE html>
<html lang='ko'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>샘플 세금계산서 생성</title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #1466BA;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #bee5eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #1466BA;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0d4a8a;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📄 테스트용 세금계산서 생성</h1>";

// 1. 사용자 확인
$user_query = "SELECT id, name, email FROM users ORDER BY id ASC LIMIT 1";
$user_result = $mysqli->query($user_query);

if ($user_result && $user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];
    echo "<div class='info'>✓ 사용자 확인: ID={$user['id']}, 이름={$user['name']}, 이메일={$user['email']}</div>";
} else {
    echo "<div class='error'>✗ 사용자를 찾을 수 없습니다. users 테이블에 데이터가 있는지 확인하세요.</div>";
    echo "</div></body></html>";
    exit;
}

// 2. 주문 확인 (입금완료 상태)
$order_query = "SELECT no, name, money_5, date FROM mlangorder_printauto 
                WHERE OrderStyle = '입금완료' 
                ORDER BY no DESC LIMIT 3";
$order_result = $mysqli->query($order_query);

$orders = [];
if ($order_result && $order_result->num_rows > 0) {
    while ($row = $order_result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo "<div class='info'>✓ 입금완료 주문 " . count($orders) . "건 확인</div>";
} else {
    echo "<div class='error'>✗ 입금완료 주문을 찾을 수 없습니다. 임의의 주문번호로 생성합니다.</div>";
    // 임의의 주문번호 생성
    $orders = [
        ['no' => 99001, 'name' => '명함 인쇄', 'money_5' => 500000, 'date' => '2024-11-09'],
        ['no' => 99002, 'name' => '전단지 인쇄', 'money_5' => 300000, 'date' => '2024-11-08'],
        ['no' => 99003, 'name' => '스티커 인쇄', 'money_5' => 200000, 'date' => '2024-11-07']
    ];
}

// 3. 기존 샘플 데이터 삭제
$delete_query = "DELETE FROM tax_invoices WHERE invoice_number LIKE 'TAX20241109%'";
$mysqli->query($delete_query);

// 4. 샘플 세금계산서 생성
$created_invoices = [];
$sample_data = [
    [
        'invoice_number' => 'TAX20241109000001',
        'nts_confirm_num' => '202411091234567890',
        'issue_date' => '2024-11-09',
        'status' => 'issued',
        'created_at' => '2024-11-09 10:30:00'
    ],
    [
        'invoice_number' => 'TAX20241109000002',
        'nts_confirm_num' => '202411091234567891',
        'issue_date' => '2024-11-08',
        'status' => 'issued',
        'created_at' => '2024-11-08 14:20:00'
    ],
    [
        'invoice_number' => 'TAX20241109000003',
        'nts_confirm_num' => '202411091234567892',
        'issue_date' => '2024-11-07',
        'status' => 'issued',
        'created_at' => '2024-11-07 09:15:00'
    ]
];

foreach ($orders as $index => $order) {
    $sample = $sample_data[$index];
    
    $total_amount = intval($order['money_5']);
    $supply_amount = round($total_amount / 1.1);
    $tax_amount = $total_amount - $supply_amount;
    
    $api_response = json_encode([
        'success' => true,
        'ntsConfirmNum' => $sample['nts_confirm_num'],
        'issueDate' => str_replace('-', '', $sample['issue_date']),
        'message' => '발급완료 (테스트 데이터)'
    ], JSON_UNESCAPED_UNICODE);
    
    $insert_query = "INSERT INTO tax_invoices (
        user_id, 
        order_no, 
        invoice_number, 
        nts_confirm_num,
        issue_date, 
        supply_amount, 
        tax_amount, 
        total_amount, 
        status,
        api_response,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($insert_query);
    $stmt->bind_param(
        "iisssiiisss",
        $user_id,
        $order['no'],
        $sample['invoice_number'],
        $sample['nts_confirm_num'],
        $sample['issue_date'],
        $supply_amount,
        $tax_amount,
        $total_amount,
        $sample['status'],
        $api_response,
        $sample['created_at']
    );
    
    if ($stmt->execute()) {
        $created_invoices[] = [
            'id' => $stmt->insert_id,
            'invoice_number' => $sample['invoice_number'],
            'nts_confirm_num' => $sample['nts_confirm_num'],
            'order_no' => $order['no'],
            'order_name' => $order['name'],
            'total_amount' => $total_amount,
            'status' => $sample['status']
        ];
    } else {
        echo "<div class='error'>✗ 세금계산서 생성 실패: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

// 5. 결과 표시
if (count($created_invoices) > 0) {
    echo "<div class='success'>✓ 총 " . count($created_invoices) . "건의 샘플 세금계산서가 생성되었습니다.</div>";
    
    echo "<h2>생성된 세금계산서 목록</h2>";
    echo "<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>계산서번호</th>
                    <th>국세청승인번호</th>
                    <th>주문번호</th>
                    <th>품목</th>
                    <th>금액</th>
                    <th>상태</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($created_invoices as $invoice) {
        echo "<tr>
                <td>{$invoice['id']}</td>
                <td>{$invoice['invoice_number']}</td>
                <td>{$invoice['nts_confirm_num']}</td>
                <td>#{$invoice['order_no']}</td>
                <td>{$invoice['order_name']}</td>
                <td>" . number_format($invoice['total_amount']) . "원</td>
                <td>발급완료</td>
              </tr>";
    }
    
    echo "</tbody></table>";
    
    echo "<a href='/mypage/tax_invoices.php' class='btn'>세금계산서 페이지로 이동</a>";
    echo " <a href='/admin/index.php' class='btn' style='background: #6c757d;'>관리자 홈으로 이동</a>";
} else {
    echo "<div class='error'>✗ 세금계산서 생성에 실패했습니다.</div>";
}

echo "</div></body></html>";

$mysqli->close();
?>
