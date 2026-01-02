# 데이터베이스 스키마

## 핵심 테이블

### 1. shop_temp (장바구니)

```sql
CREATE TABLE shop_temp (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    member_id INT DEFAULT 0,
    product_type VARCHAR(50) NOT NULL,    -- sticker_new, inserted, namecard...
    product_name VARCHAR(100),
    size VARCHAR(50),
    paper VARCHAR(100),
    quantity VARCHAR(50),                  -- 원본값
    quantity_display VARCHAR(100),         -- 표시용 "0.5연 (2,000매)"
    sides VARCHAR(20),                     -- 단면/양면
    options TEXT,                          -- JSON {"coating":"유광"}
    price INT DEFAULT 0,
    file_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_session (session_id),
    INDEX idx_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. orderform (주문 마스터)

```sql
CREATE TABLE orderform (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) UNIQUE NOT NULL,  -- 20241227143052001
    member_id INT DEFAULT 0,
    
    -- 주문자 정보
    orderer_name VARCHAR(50) NOT NULL,
    orderer_phone VARCHAR(20) NOT NULL,
    orderer_email VARCHAR(100),
    
    -- 배송지 정보
    receiver_name VARCHAR(50) NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    postcode VARCHAR(10),
    address VARCHAR(255),
    address_detail VARCHAR(255),
    
    -- 결제 정보
    payment_method VARCHAR(20),            -- escrow, card, vbank
    total_price INT DEFAULT 0,
    
    -- 상태
    status VARCHAR(20) DEFAULT 'pending',  -- pending/paid/printing/shipping/completed/cancelled
    
    -- 배송 정보
    courier VARCHAR(50),                   -- 택배사
    tracking_no VARCHAR(50),               -- 운송장번호
    
    memo TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_order_no (order_no),
    INDEX idx_member (member_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. orderformtree (주문 상세)

```sql
CREATE TABLE orderformtree (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) NOT NULL,
    product_type VARCHAR(50),
    product_name VARCHAR(100),
    size VARCHAR(50),
    paper VARCHAR(100),
    quantity VARCHAR(50),
    quantity_display VARCHAR(100),
    sides VARCHAR(20),
    options TEXT,
    price INT DEFAULT 0,
    file_path VARCHAR(255),
    
    -- 교정 상태
    proof_status VARCHAR(20) DEFAULT 'pending',  -- pending/uploaded/approved/rejected
    proof_file VARCHAR(255),
    proof_comment TEXT,
    
    INDEX idx_order_no (order_no),
    FOREIGN KEY (order_no) REFERENCES orderform(order_no) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. members (회원)

```sql
CREATE TABLE members (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,        -- password_hash()
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    mobile VARCHAR(20),
    
    -- 기본 배송지
    postcode VARCHAR(10),
    address VARCHAR(255),
    address_detail VARCHAR(255),
    
    -- 사업자 정보 (선택)
    company_name VARCHAR(100),
    business_no VARCHAR(20),               -- 사업자등록번호
    
    -- 상태
    status VARCHAR(20) DEFAULT 'active',   -- active/inactive/blocked
    role VARCHAR(20) DEFAULT 'user',       -- user/admin
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    
    INDEX idx_user_id (user_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5. quotations (견적서)

```sql
CREATE TABLE quotations (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    quote_no VARCHAR(20) UNIQUE NOT NULL,
    member_id INT,
    
    -- 요청 정보
    product_type VARCHAR(50),
    product_name VARCHAR(100),
    specifications TEXT,                   -- 상세 사양
    quantity VARCHAR(50),
    
    -- 견적 금액
    quoted_price INT DEFAULT 0,
    
    -- 상태
    status VARCHAR(20) DEFAULT 'requested', -- requested/quoted/accepted/rejected/ordered
    
    -- 담당자
    admin_id INT,
    admin_comment TEXT,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    quoted_at DATETIME,
    
    INDEX idx_member (member_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 6. proofs (교정 시안)

```sql
CREATE TABLE proofs (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) NOT NULL,
    orderformtree_idx INT NOT NULL,
    
    -- 시안 파일
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255),
    
    -- 상태
    status VARCHAR(20) DEFAULT 'pending',  -- pending/approved/rejected
    
    -- 코멘트
    admin_comment TEXT,                    -- 관리자 메모
    customer_comment TEXT,                 -- 고객 피드백
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME,
    
    INDEX idx_order (order_no),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7. payments (결제)

```sql
CREATE TABLE payments (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(20) NOT NULL,
    
    -- 결제 정보
    payment_method VARCHAR(20),            -- escrow/card/vbank
    amount INT NOT NULL,
    
    -- PG 응답
    pg_tid VARCHAR(100),                   -- PG 거래번호
    pg_result_code VARCHAR(10),
    pg_result_msg VARCHAR(255),
    
    -- 가상계좌 정보
    vbank_name VARCHAR(50),
    vbank_num VARCHAR(50),
    vbank_date VARCHAR(20),
    
    status VARCHAR(20) DEFAULT 'pending',  -- pending/paid/cancelled/refunded
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME,
    
    INDEX idx_order (order_no),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 8. admin_users (관리자)

```sql
CREATE TABLE admin_users (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(50),
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'staff',      -- super/manager/staff
    
    status VARCHAR(20) DEFAULT 'active',
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 테이블 관계도

```
members (1) ─────┬───── (N) orderform
                │            │
                │            ├── (N) orderformtree
                │            │         │
                │            │         └── (N) proofs
                │            │
                │            └── (1) payments
                │
                └───── (N) quotations


shop_temp ───(장바구니 → 주문)──→ orderformtree
```

## 자주 사용하는 쿼리

### 주문 목록 (관리자)
```sql
SELECT o.*, COUNT(t.idx) as item_count, 
       GROUP_CONCAT(t.product_name) as products
FROM orderform o
LEFT JOIN orderformtree t ON o.order_no = t.order_no
WHERE o.status = 'paid'
GROUP BY o.order_no
ORDER BY o.created_at DESC
LIMIT 20;
```

### 회원별 주문 내역
```sql
SELECT o.*, 
       (SELECT COUNT(*) FROM orderformtree WHERE order_no = o.order_no) as item_count
FROM orderform o
WHERE o.member_id = ?
ORDER BY o.created_at DESC;
```

### 일별 매출
```sql
SELECT DATE(created_at) as date, 
       COUNT(*) as order_count,
       SUM(total_price) as total_sales
FROM orderform
WHERE status IN ('paid', 'printing', 'shipping', 'completed')
GROUP BY DATE(created_at)
ORDER BY date DESC;
```
