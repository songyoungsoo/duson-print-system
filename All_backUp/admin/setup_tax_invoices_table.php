<?php
/**
 * tax_invoices 테이블 생성 및 설정
 * 경로: /admin/setup_tax_invoices_table.php
 * 실행: http://localhost/admin/setup_tax_invoices_table.php
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

echo "<!DOCTYPE html>
<html lang='ko'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>세금계산서 테이블 설정</title>
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
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
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
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 세금계산서 테이블 설정</h1>";

// 1. 테이블 존재 확인
$check_table = "SHOW TABLES LIKE 'tax_invoices'";
$result = mysqli_query($db, $check_table);

if (mysqli_num_rows($result) > 0) {
    echo "<div class='info'>✓ tax_invoices 테이블이 이미 존재합니다.</div>";
    
    // 테이블 구조 확인
    $desc_query = "DESCRIBE tax_invoices";
    $desc_result = mysqli_query($db, $desc_query);
    
    echo "<h3>현재 테이블 구조:</h3>";
    echo "<pre>";
    $columns = [];
    while ($row = mysqli_fetch_assoc($desc_result)) {
        $columns[] = $row['Field'];
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }
    echo "</pre>";
    
    // 필요한 컬럼 확인 및 추가
    $required_columns = ['nts_confirm_num', 'api_response'];
    $missing_columns = array_diff($required_columns, $columns);
    
    if (!empty($missing_columns)) {
        echo "<div class='info'>누락된 컬럼을 추가합니다: " . implode(', ', $missing_columns) . "</div>";
        
        if (in_array('nts_confirm_num', $missing_columns)) {
            $alter1 = "ALTER TABLE tax_invoices ADD COLUMN nts_confirm_num VARCHAR(50) NULL COMMENT '국세청 승인번호' AFTER invoice_number";
            if (mysqli_query($db, $alter1)) {
                echo "<div class='success'>✓ nts_confirm_num 컬럼 추가 완료</div>";
            } else {
                echo "<div class='error'>✗ nts_confirm_num 컬럼 추가 실패: " . mysqli_error($db) . "</div>";
            }
        }
        
        if (in_array('api_response', $missing_columns)) {
            $alter2 = "ALTER TABLE tax_invoices ADD COLUMN api_response TEXT NULL COMMENT 'API 응답 데이터' AFTER status";
            if (mysqli_query($db, $alter2)) {
                echo "<div class='success'>✓ api_response 컬럼 추가 완료</div>";
            } else {
                echo "<div class='error'>✗ api_response 컬럼 추가 실패: " . mysqli_error($db) . "</div>";
            }
        }
    }
    
    // status 컬럼 타입 확인 및 수정
    $check_status = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'tax_invoices' 
                     AND COLUMN_NAME = 'status'";
    $status_result = mysqli_query($db, $check_status);
    $status_row = mysqli_fetch_assoc($status_result);
    
    if ($status_row && strpos($status_row['COLUMN_TYPE'], 'failed') === false) {
        echo "<div class='info'>status 컬럼에 'failed' 값을 추가합니다.</div>";
        $alter3 = "ALTER TABLE tax_invoices MODIFY COLUMN status ENUM('pending', 'issued', 'cancelled', 'failed') DEFAULT 'pending'";
        if (mysqli_query($db, $alter3)) {
            echo "<div class='success'>✓ status 컬럼 수정 완료</div>";
        } else {
            echo "<div class='error'>✗ status 컬럼 수정 실패: " . mysqli_error($db) . "</div>";
        }
    }
    
} else {
    echo "<div class='info'>tax_invoices 테이블을 생성합니다.</div>";
    
    $create_table = "CREATE TABLE tax_invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_no INT NOT NULL,
        invoice_number VARCHAR(50) UNIQUE NOT NULL COMMENT '계산서 번호',
        nts_confirm_num VARCHAR(50) NULL COMMENT '국세청 승인번호',
        issue_date DATE NOT NULL COMMENT '발행일',
        supply_amount INT NOT NULL COMMENT '공급가액',
        tax_amount INT NOT NULL COMMENT '세액',
        total_amount INT NOT NULL COMMENT '합계금액',
        status ENUM('pending', 'issued', 'cancelled', 'failed') DEFAULT 'pending',
        api_response TEXT NULL COMMENT 'API 응답 데이터',
        pdf_path VARCHAR(255) DEFAULT NULL COMMENT 'PDF 파일 경로',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_order_no (order_no),
        INDEX idx_nts_confirm_num (nts_confirm_num),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='전자세금계산서'";
    
    if (mysqli_query($db, $create_table)) {
        echo "<div class='success'>✓ tax_invoices 테이블 생성 완료</div>";
    } else {
        echo "<div class='error'>✗ 테이블 생성 실패: " . mysqli_error($db) . "</div>";
    }
}

// 2. users 테이블에 사업자 정보 컬럼 확인
$check_users = "SHOW COLUMNS FROM users LIKE 'business_type'";
$users_result = mysqli_query($db, $check_users);

if (mysqli_num_rows($users_result) == 0) {
    echo "<div class='info'>users 테이블에 사업자 정보 컬럼을 추가합니다.</div>";
    
    $alter_users1 = "ALTER TABLE users ADD COLUMN business_type VARCHAR(100) NULL COMMENT '업태' AFTER business_owner";
    $alter_users2 = "ALTER TABLE users ADD COLUMN business_item VARCHAR(100) NULL COMMENT '종목' AFTER business_type";
    
    if (mysqli_query($db, $alter_users1)) {
        echo "<div class='success'>✓ business_type 컬럼 추가 완료</div>";
    }
    
    if (mysqli_query($db, $alter_users2)) {
        echo "<div class='success'>✓ business_item 컬럼 추가 완료</div>";
    }
}

echo "<h3>✅ 설정 완료</h3>";
echo "<p>이제 샘플 데이터를 생성할 수 있습니다.</p>";
echo "<a href='create_sample_tax_invoices.php' class='btn'>샘플 데이터 생성하기</a>";

echo "</div></body></html>";

mysqli_close($db);
?>
