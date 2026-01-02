# 관리자 대시보드

## 대시보드 구성

```
┌─────────────────────────────────────────────────────┐
│ 오늘의 요약                                          │
│ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐           │
│ │신규  │ │결제  │ │인쇄중│ │배송중│ │완료  │           │
│ │주문  │ │완료  │ │     │ │     │ │     │           │
│ │ 12  │ │  8  │ │  5  │ │  3  │ │ 15  │           │
│ └─────┘ └─────┘ └─────┘ └─────┘ └─────┘           │
├─────────────────────────────────────────────────────┤
│ 매출 현황              │ 교정 대기                    │
│ ┌─────────────────┐   │ ┌─────────────────────┐    │
│ │ [차트]           │   │ │ 주문번호 상품   상태    │    │
│ │                  │   │ │ 001    명함   대기    │    │
│ │                  │   │ │ 002    전단지  요청    │    │
│ └─────────────────┘   │ └─────────────────────┘    │
├─────────────────────────────────────────────────────┤
│ 최근 주문                                           │
│ ┌─────────────────────────────────────────────────┐│
│ │ 주문번호   고객명   상품      금액      상태     ││
│ │ 202412...  홍길동   명함 외1  53,000   결제완료  ││
│ │ ...                                              ││
│ └─────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────┘
```

## 관리자 인증

### 로그인 (admin/login.php)
```php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['idx'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role'] = $admin['role'];
        
        // 로그인 기록
        $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE idx = ?")
            ->execute([$admin['idx']]);
        
        header('Location: /admin/dashboard.php');
    } else {
        $error = '아이디 또는 비밀번호가 일치하지 않습니다.';
    }
}
```

### 인증 체크 (admin/inc/auth.php)
```php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// 권한 체크 함수
function checkPermission($required_role) {
    $roles = ['staff' => 1, 'manager' => 2, 'super' => 3];
    $user_level = $roles[$_SESSION['admin_role']] ?? 0;
    $required_level = $roles[$required_role] ?? 999;
    
    return $user_level >= $required_level;
}
```

## 대시보드 데이터 (dashboard.php)

```php
require_once 'inc/auth.php';

// 오늘 주문 현황
$sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
    SUM(CASE WHEN status = 'printing' THEN 1 ELSE 0 END) as printing,
    SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END) as shipping,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(total_price) as total_sales
FROM orderform 
WHERE DATE(created_at) = CURDATE()";
$today = $pdo->query($sql)->fetch();

// 이번 달 매출
$sql = "SELECT DATE(created_at) as date, SUM(total_price) as sales
FROM orderform
WHERE status NOT IN ('pending', 'cancelled')
  AND YEAR(created_at) = YEAR(CURDATE())
  AND MONTH(created_at) = MONTH(CURDATE())
GROUP BY DATE(created_at)
ORDER BY date";
$monthly_sales = $pdo->query($sql)->fetchAll();

// 교정 대기 목록
$sql = "SELECT o.order_no, o.orderer_name, t.product_name, t.proof_status
FROM orderformtree t
JOIN orderform o ON t.order_no = o.order_no
WHERE t.proof_status IN ('pending', 'uploaded', 'rejected')
ORDER BY o.created_at DESC
LIMIT 10";
$proof_pending = $pdo->query($sql)->fetchAll();

// 최근 주문
$sql = "SELECT o.*, 
    (SELECT product_name FROM orderformtree WHERE order_no = o.order_no LIMIT 1) as first_product,
    (SELECT COUNT(*) FROM orderformtree WHERE order_no = o.order_no) as item_count
FROM orderform o
ORDER BY o.created_at DESC
LIMIT 20";
$recent_orders = $pdo->query($sql)->fetchAll();
```

## 주문 관리 (admin/OrderForm/)

### 주문 목록 (list.php)
```php
// 필터링
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$where = ["created_at BETWEEN ? AND ?"];
$params = [$date_from . ' 00:00:00', $date_to . ' 23:59:59'];

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(order_no LIKE ? OR orderer_name LIKE ? OR orderer_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "SELECT * FROM orderform WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$orders = $pdo->prepare($sql);
$orders->execute($params);
```

### 주문 상세 (detail.php)
```php
$order_no = $_GET['no'];

// 주문 기본 정보
$order = $pdo->prepare("SELECT * FROM orderform WHERE order_no = ?")->execute([$order_no])->fetch();

// 주문 상품
$items = $pdo->prepare("SELECT * FROM orderformtree WHERE order_no = ?")->execute([$order_no])->fetchAll();

// 결제 정보
$payment = $pdo->prepare("SELECT * FROM payments WHERE order_no = ?")->execute([$order_no])->fetch();

// 교정 이력
$proofs = $pdo->prepare("SELECT * FROM proofs WHERE order_no = ? ORDER BY created_at")->execute([$order_no])->fetchAll();
```

### 상태 변경
```php
// admin/OrderForm/update_status.php
$order_no = $_POST['order_no'];
$new_status = $_POST['status'];

$allowed_statuses = ['pending', 'paid', 'printing', 'shipping', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    die('잘못된 상태값');
}

$sql = "UPDATE orderform SET status = ?, updated_at = NOW() WHERE order_no = ?";
$pdo->prepare($sql)->execute([$new_status, $order_no]);

// 상태 변경 이력 저장
$sql = "INSERT INTO order_logs (order_no, action, old_value, new_value, admin_id, created_at)
        VALUES (?, 'status_change', ?, ?, ?, NOW())";
$pdo->prepare($sql)->execute([$order_no, $old_status, $new_status, $_SESSION['admin_id']]);

// 이메일 알림 (옵션)
if ($new_status === 'shipping') {
    sendShippingNoticeEmail($order_no);
}
```

## 회원 관리

### 회원 목록
```php
$sql = "SELECT m.*, 
    (SELECT COUNT(*) FROM orderform WHERE member_id = m.idx) as order_count,
    (SELECT SUM(total_price) FROM orderform WHERE member_id = m.idx AND status != 'cancelled') as total_spent
FROM members m
ORDER BY m.created_at DESC";
```

### 회원 상세
```php
$member_id = $_GET['id'];

$member = $pdo->prepare("SELECT * FROM members WHERE idx = ?")->execute([$member_id])->fetch();
$orders = $pdo->prepare("SELECT * FROM orderform WHERE member_id = ? ORDER BY created_at DESC LIMIT 20")->execute([$member_id])->fetchAll();
```

## 통계/리포트

### 기간별 매출
```php
// 월별 매출
$sql = "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as order_count,
    SUM(total_price) as total_sales
FROM orderform
WHERE status NOT IN ('pending', 'cancelled')
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC
LIMIT 12";
```

### 상품별 판매량
```php
$sql = "SELECT 
    product_type,
    product_name,
    COUNT(*) as order_count,
    SUM(price) as total_sales
FROM orderformtree t
JOIN orderform o ON t.order_no = o.order_no
WHERE o.status NOT IN ('pending', 'cancelled')
GROUP BY product_type, product_name
ORDER BY total_sales DESC";
```

### 엑셀 다운로드
```php
// admin/export_orders.php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 헤더
$sheet->setCellValue('A1', '주문번호');
$sheet->setCellValue('B1', '주문자');
$sheet->setCellValue('C1', '상품');
$sheet->setCellValue('D1', '금액');
$sheet->setCellValue('E1', '상태');
$sheet->setCellValue('F1', '주문일');

// 데이터
$row = 2;
foreach ($orders as $order) {
    $sheet->setCellValue("A$row", $order['order_no']);
    $sheet->setCellValue("B$row", $order['orderer_name']);
    // ...
    $row++;
}

// 다운로드
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="orders.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
```

## 주문 상세 레이아웃 (OrderView)

### CSS 레이아웃 구조 (admin.php)
```css
/* 메인 컨테이너 - 700px 중앙 정렬 */
.admin-container {
    max-width: 700px;
    width: calc(100vw - 30px);
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* 내부 요소 - 100% 너비 채우기 */
.admin-header,
.admin-content,
.info-grid,
.form-section,
.btn-group,
.file-section {
    width: 100%;
    box-sizing: border-box;
}

/* 2컬럼 그리드 */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
```

### 주요 색상 (2026-01-02 업데이트)
| 요소 | 색상 코드 | 용도 |
|------|-----------|------|
| 공급가액 배경 | `#f8f9fa` | 연한 그레이 |
| 부가세포함금액 배경 | `#e9ecef` | 연한 그레이 |
| 헤더 그라데이션 | `#2c3e50 → #34495e` | 어두운 블루 |
| 포인트 컬러 | `#3498db` | 밝은 블루 |

### 관련 파일
- `admin/mlangprintauto/admin.php` - CSS 스타일 및 파일 섹션
- `mlangorder_printauto/OrderFormOrderTree.php` - 주문서 HTML 구조

## 사이드바 메뉴 구조

```html
<nav class="admin-sidebar">
    <ul>
        <li><a href="/admin/dashboard.php">대시보드</a></li>
        <li>
            <span>주문관리</span>
            <ul>
                <li><a href="/admin/OrderForm/">전체 주문</a></li>
                <li><a href="/admin/OrderForm/?status=pending">입금대기</a></li>
                <li><a href="/admin/OrderForm/?status=paid">결제완료</a></li>
                <li><a href="/admin/OrderForm/?status=printing">인쇄중</a></li>
                <li><a href="/admin/OrderForm/?status=shipping">배송중</a></li>
            </ul>
        </li>
        <li>
            <span>교정관리</span>
            <ul>
                <li><a href="/admin/proofs/">교정 대기</a></li>
                <li><a href="/admin/proofs/?status=rejected">수정요청</a></li>
            </ul>
        </li>
        <li><a href="/admin/members/">회원관리</a></li>
        <li><a href="/admin/quotations/">견적관리</a></li>
        <li><a href="/admin/reports/">통계/리포트</a></li>
        <li><a href="/admin/settings/">설정</a></li>
    </ul>
</nav>
```
