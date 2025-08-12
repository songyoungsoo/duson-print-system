<?php
/**
 * users 테이블에 사업자 정보 필드 추가
 * 기존 auth.php의 테이블 생성 부분을 업데이트
 */

// 사업자 정보 필드가 포함된 users 테이블 생성 쿼리
$create_table_query = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    is_business TINYINT(1) DEFAULT 0 COMMENT '사업자 여부',
    business_number VARCHAR(20) DEFAULT NULL COMMENT '사업자등록번호',
    business_owner VARCHAR(100) DEFAULT NULL COMMENT '대표자명',
    business_type VARCHAR(100) DEFAULT NULL COMMENT '업태',
    business_item VARCHAR(100) DEFAULT NULL COMMENT '종목',
    business_address TEXT DEFAULT NULL COMMENT '사업장 주소',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_business_number (business_number),
    INDEX idx_is_business (is_business)
)";

// 기존 테이블에 사업자 필드 추가하는 함수
function addBusinessFieldsToUsers($connect) {
    $business_fields = [
        'is_business' => 'TINYINT(1) DEFAULT 0',
        'business_number' => 'VARCHAR(20) DEFAULT NULL',
        'business_owner' => 'VARCHAR(100) DEFAULT NULL', 
        'business_type' => 'VARCHAR(100) DEFAULT NULL',
        'business_item' => 'VARCHAR(100) DEFAULT NULL',
        'business_address' => 'TEXT DEFAULT NULL',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    foreach ($business_fields as $field => $definition) {
        $check_column = mysqli_query($connect, "SHOW COLUMNS FROM users LIKE '$field'");
        if (mysqli_num_rows($check_column) == 0) {
            $alter_query = "ALTER TABLE users ADD COLUMN $field $definition";
            mysqli_query($connect, $alter_query);
        }
    }
    
    // 인덱스 추가
    $indexes = [
        'idx_business_number' => 'business_number',
        'idx_is_business' => 'is_business'
    ];
    
    foreach ($indexes as $index_name => $column) {
        $check_index = mysqli_query($connect, "SHOW INDEX FROM users WHERE Key_name = '$index_name'");
        if (mysqli_num_rows($check_index) == 0) {
            mysqli_query($connect, "CREATE INDEX $index_name ON users($column)");
        }
    }
}
?>