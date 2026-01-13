# FAQ / 공지사항 시스템

## 테이블 구조

### 공지사항
```sql
CREATE TABLE notices (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_important TINYINT(1) DEFAULT 0,   -- 중요 공지 (상단 고정)
    is_popup TINYINT(1) DEFAULT 0,        -- 팝업 공지
    view_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',  -- active/hidden
    admin_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status, created_at),
    INDEX idx_important (is_important, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### FAQ 카테고리
```sql
CREATE TABLE faq_categories (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    sort_order INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    
    INDEX idx_sort (sort_order)
);

-- 기본 카테고리
INSERT INTO faq_categories (name, sort_order) VALUES
('주문/결제', 1),
('배송', 2),
('교정/시안', 3),
('인쇄/제작', 4),
('회원/기타', 5);
```

### FAQ
```sql
CREATE TABLE faqs (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    view_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_category (category_id, sort_order),
    FULLTEXT INDEX ft_search (question, answer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 공지사항

### 목록 페이지 (notice/list.php)
```php
// 중요 공지 (상단 고정)
$important = $pdo->query("
    SELECT * FROM notices 
    WHERE status = 'active' AND is_important = 1 
    ORDER BY created_at DESC
")->fetchAll();

// 일반 공지 (페이징)
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM notices WHERE status = 'active' AND is_important = 0")->fetchColumn();

$notices = $pdo->prepare("
    SELECT * FROM notices 
    WHERE status = 'active' AND is_important = 0
    ORDER BY created_at DESC 
    LIMIT ?, ?
");
$notices->execute([$offset, $limit]);
```

### 목록 UI
```html
<div class="notice-list">
    <h2>공지사항</h2>
    
    <table>
        <thead>
            <tr>
                <th width="60">번호</th>
                <th>제목</th>
                <th width="100">작성일</th>
                <th width="60">조회</th>
            </tr>
        </thead>
        <tbody>
            <!-- 중요 공지 -->
            <?php foreach ($important as $row): ?>
            <tr class="important">
                <td><span class="badge">중요</span></td>
                <td><a href="view.php?idx=<?= $row['idx'] ?>"><?= h($row['title']) ?></a></td>
                <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                <td><?= number_format($row['view_count']) ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- 일반 공지 -->
            <?php foreach ($notices as $i => $row): ?>
            <tr>
                <td><?= $total - $offset - $i ?></td>
                <td><a href="view.php?idx=<?= $row['idx'] ?>"><?= h($row['title']) ?></a></td>
                <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                <td><?= number_format($row['view_count']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- 페이징 -->
    <?php include 'inc/pagination.php'; ?>
</div>
```

### 상세 페이지 (notice/view.php)
```php
$idx = intval($_GET['idx']);

// 조회수 증가
$pdo->prepare("UPDATE notices SET view_count = view_count + 1 WHERE idx = ?")->execute([$idx]);

// 공지 조회
$notice = $pdo->prepare("SELECT * FROM notices WHERE idx = ? AND status = 'active'")->execute([$idx])->fetch();

if (!$notice) {
    header('Location: list.php');
    exit;
}

// 이전/다음 글
$prev = $pdo->prepare("SELECT idx, title FROM notices WHERE idx < ? AND status = 'active' ORDER BY idx DESC LIMIT 1")->execute([$idx])->fetch();
$next = $pdo->prepare("SELECT idx, title FROM notices WHERE idx > ? AND status = 'active' ORDER BY idx ASC LIMIT 1")->execute([$idx])->fetch();
```

### 팝업 공지
```php
// inc/popup_notice.php
$popups = $pdo->query("
    SELECT * FROM notices 
    WHERE status = 'active' AND is_popup = 1 
    ORDER BY created_at DESC
")->fetchAll();

foreach ($popups as $popup) {
    $cookie_name = 'popup_' . $popup['idx'];
    
    // 오늘 하루 안 보기 체크
    if (isset($_COOKIE[$cookie_name])) continue;
    
    echo "
    <div class='popup-notice' id='popup_{$popup['idx']}'>
        <div class='popup-content'>
            <h3>" . h($popup['title']) . "</h3>
            <div class='popup-body'>" . nl2br(h($popup['content'])) . "</div>
            <div class='popup-footer'>
                <label>
                    <input type='checkbox' onclick='closePopupToday({$popup['idx']})'>
                    오늘 하루 안 보기
                </label>
                <button onclick='closePopup({$popup['idx']})'>닫기</button>
            </div>
        </div>
    </div>
    ";
}
?>

<script>
function closePopup(idx) {
    document.getElementById('popup_' + idx).style.display = 'none';
}

function closePopupToday(idx) {
    document.cookie = 'popup_' + idx + '=1; path=/; max-age=86400';
    closePopup(idx);
}
</script>
```

## FAQ

### FAQ 목록 (faq/index.php)
```php
// 카테고리 목록
$categories = $pdo->query("
    SELECT * FROM faq_categories WHERE status = 'active' ORDER BY sort_order
")->fetchAll();

// 선택된 카테고리
$cat_id = intval($_GET['cat'] ?? 0);

// FAQ 목록
if ($cat_id) {
    $faqs = $pdo->prepare("
        SELECT f.*, c.name as category_name
        FROM faqs f
        JOIN faq_categories c ON f.category_id = c.idx
        WHERE f.category_id = ? AND f.status = 'active'
        ORDER BY f.sort_order
    ");
    $faqs->execute([$cat_id]);
} else {
    // 전체 카테고리
    $faqs = $pdo->query("
        SELECT f.*, c.name as category_name
        FROM faqs f
        JOIN faq_categories c ON f.category_id = c.idx
        WHERE f.status = 'active'
        ORDER BY c.sort_order, f.sort_order
    ");
}
$faqs = $faqs->fetchAll();
```

### FAQ UI (아코디언)
```html
<div class="faq-page">
    <h2>자주 묻는 질문</h2>
    
    <!-- 검색 -->
    <form class="faq-search" action="" method="GET">
        <input type="text" name="q" placeholder="검색어를 입력하세요" value="<?= h($_GET['q'] ?? '') ?>">
        <button type="submit">검색</button>
    </form>
    
    <!-- 카테고리 탭 -->
    <div class="faq-tabs">
        <a href="?cat=0" class="<?= $cat_id == 0 ? 'active' : '' ?>">전체</a>
        <?php foreach ($categories as $cat): ?>
        <a href="?cat=<?= $cat['idx'] ?>" class="<?= $cat_id == $cat['idx'] ? 'active' : '' ?>">
            <?= h($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- FAQ 목록 -->
    <div class="faq-list">
        <?php foreach ($faqs as $faq): ?>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                <span class="category"><?= h($faq['category_name']) ?></span>
                <span class="q">Q. <?= h($faq['question']) ?></span>
                <span class="icon">▼</span>
            </div>
            <div class="faq-answer">
                <p>A. <?= nl2br(h($faq['answer'])) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.faq-answer { display: none; padding: 15px; background: #f9f9f9; }
.faq-item.active .faq-answer { display: block; }
.faq-item.active .icon { transform: rotate(180deg); }
</style>

<script>
function toggleFaq(el) {
    el.parentElement.classList.toggle('active');
}
</script>
```

### FAQ 검색
```php
$search = $_GET['q'] ?? '';

if ($search) {
    // FULLTEXT 검색
    $stmt = $pdo->prepare("
        SELECT f.*, c.name as category_name,
               MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
        FROM faqs f
        JOIN faq_categories c ON f.category_id = c.idx
        WHERE f.status = 'active' 
          AND MATCH(question, answer) AGAINST(? IN NATURAL LANGUAGE MODE)
        ORDER BY relevance DESC
    ");
    $stmt->execute([$search, $search]);
    $faqs = $stmt->fetchAll();
}
```

## 관리자: 공지/FAQ 관리

### 공지 작성 (admin/notice/write.php)
```html
<form action="save.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    
    <div class="form-group">
        <label>제목</label>
        <input type="text" name="title" required>
    </div>
    
    <div class="form-group">
        <label>내용</label>
        <textarea name="content" rows="15" required></textarea>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_important" value="1">
            중요 공지 (상단 고정)
        </label>
    </div>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="is_popup" value="1">
            팝업 공지
        </label>
    </div>
    
    <button type="submit">등록</button>
</form>
```

### FAQ 관리 (admin/faq/)
```php
// 순서 변경 (드래그 앤 드롭)
// ajax/update_faq_order.php
$orders = $_POST['orders'];  // [faq_id => sort_order, ...]

foreach ($orders as $idx => $order) {
    $pdo->prepare("UPDATE faqs SET sort_order = ? WHERE idx = ?")
        ->execute([$order, $idx]);
}
```

## 인쇄 관련 FAQ 예시

```sql
INSERT INTO faqs (category_id, question, answer, sort_order) VALUES
(1, '최소 주문 수량이 있나요?', '네, 제품별로 최소 주문 수량이 다릅니다.\n- 명함: 100매부터\n- 스티커: 100매부터\n- 전단지: 500매(0.25연)부터', 1),
(1, '견적은 어떻게 받나요?', '홈페이지 상단의 [견적요청] 메뉴에서 원하시는 사양을 입력해주시면, 영업일 기준 1일 이내에 견적서를 이메일로 보내드립니다.', 2),
(2, '배송은 얼마나 걸리나요?', '결제 및 시안 확정 후 영업일 기준 2~3일 소요됩니다.\n급행 인쇄(당일/익일)도 가능하며, 추가 비용이 발생합니다.', 1),
(2, '배송비는 얼마인가요?', '5만원 이상 주문 시 무료배송입니다.\n5만원 미만 주문 시 3,000원의 배송비가 부과됩니다.\n제주/도서산간 지역은 추가 배송비가 발생합니다.', 2),
(3, '시안 확인은 어떻게 하나요?', '주문 후 담당자가 시안을 제작하여 이메일로 보내드립니다.\n[교정보기] 페이지에서 시안을 확인하시고 승인/수정요청을 해주시면 됩니다.', 1),
(3, '시안 수정은 몇 번까지 가능한가요?', '기본 2회까지 무료 수정 가능합니다.\n3회 이상 수정 시 추가 비용이 발생할 수 있습니다.', 2),
(4, '인쇄 파일은 어떤 형식으로 보내야 하나요?', 'AI, PSD, PDF 형식을 권장합니다.\n- 해상도: 300dpi 이상\n- 색상모드: CMYK\n- 재단선(도련): 3mm\n- 폰트: 아웃라인 처리', 1),
(4, '양면 인쇄 시 주의사항이 있나요?', '앞면과 뒷면의 방향(천지좌우)을 명확히 표시해주세요.\n파일이 두 개인 경우 파일명에 앞면/뒷면을 표기해주세요.', 2);
```
