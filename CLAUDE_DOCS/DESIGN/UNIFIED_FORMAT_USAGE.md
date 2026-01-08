# formatUnified() 사용 가이드

**작성일**: 2026-01-07
**상태**: ✅ 구현 완료 (11개 품목 전체 테스트 통과)

---

## 📌 개요

`ProductSpecFormatter::formatUnified()` 메서드는 모든 제품을 동일한 4줄 구조로 표시합니다.

### 기존 방식 (format)
```php
$result = $specFormatter->format($item);
// ['line1' => '규격', 'line2' => '옵션', 'additional' => '추가옵션']
```

### 새로운 방식 (formatUnified)
```php
$result = $specFormatter->formatUnified($item);
// ['line1' => '규격', 'line2' => '옵션', 'line3' => '추가옵션', 'line4' => '특수옵션']
```

---

## 🎯 고정 양식 구조

```
┌─────────────────────────────────────────────────────┐
│ 1줄: [제품종류] / [재질/용지] / [규격/크기]         │
│ 2줄: [인쇄옵션] / [수량 + 단위] / [디자인]          │
│ 3줄: [추가옵션] (코팅, 접지, 오시 - 해당 제품만)    │
│ 4줄: [특수옵션] (프리미엄 옵션, 양면테이프)          │
└─────────────────────────────────────────────────────┘
```

---

## 📝 사용 예시

### 1. 장바구니 페이지 (shop_cart.php)

```php
<?php
require_once 'includes/ProductSpecFormatter.php';
$specFormatter = new ProductSpecFormatter($db);

// 장바구니 아이템 조회
$query = "SELECT * FROM shop_temp WHERE user_no = ? ORDER BY no DESC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($item = mysqli_fetch_assoc($result)) {
    $unified = $specFormatter->formatUnified($item);
    ?>
    <div class="cart-item">
        <div class="product-name">
            <?= htmlspecialchars(ProductSpecFormatter::getProductTypeName($item['product_type'])) ?>
        </div>

        <!-- 1줄: 규격 -->
        <div class="spec-line spec-line-1">
            <?= htmlspecialchars($unified['line1']) ?>
        </div>

        <!-- 2줄: 옵션 -->
        <div class="spec-line spec-line-2">
            <?= htmlspecialchars($unified['line2']) ?>
        </div>

        <!-- 3줄: 추가옵션 (있는 경우만) -->
        <?php if (!empty($unified['line3'])): ?>
        <div class="spec-line spec-line-3 additional">
            <span class="icon">➕</span> 추가 옵션: <?= htmlspecialchars($unified['line3']) ?>
        </div>
        <?php endif; ?>

        <!-- 4줄: 특수옵션 (있는 경우만) -->
        <?php if (!empty($unified['line4'])): ?>
        <div class="spec-line spec-line-4 special">
            <span class="icon">✨</span> <?= htmlspecialchars($unified['line4']) ?>
        </div>
        <?php endif; ?>

        <div class="price">
            <?= number_format(ProductSpecFormatter::getPrice($item)) ?>원
        </div>
    </div>
    <?php
}
?>
```

### 2. 주문 완료 페이지 (OrderComplete_universal.php)

```php
<?php
require_once 'includes/ProductSpecFormatter.php';
$specFormatter = new ProductSpecFormatter($db);

// 주문 아이템 조회
$query = "SELECT * FROM mlangorder_printauto WHERE user_id = ? AND order_no = ? ORDER BY no";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "is", $user_no, $order_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($item = mysqli_fetch_assoc($result)) {
    // Type_1 JSON 파싱 (자동으로 formatUnified 내부에서 처리됨)
    $unified = $specFormatter->formatUnified($item);
    ?>
    <div class="order-item">
        <div class="product-info">
            <strong><?= htmlspecialchars(ProductSpecFormatter::getProductTypeName($item['product_type'])) ?></strong>
            <div><?= htmlspecialchars($unified['line1']) ?></div>
            <div><?= htmlspecialchars($unified['line2']) ?></div>
            <?php if (!empty($unified['line3'])): ?>
                <div class="additional">➕ <?= htmlspecialchars($unified['line3']) ?></div>
            <?php endif; ?>
            <?php if (!empty($unified['line4'])): ?>
                <div class="premium">✨ <?= htmlspecialchars($unified['line4']) ?></div>
            <?php endif; ?>
        </div>
        <div class="price-info">
            <?= number_format(ProductSpecFormatter::getPrice($item)) ?>원
        </div>
    </div>
    <?php
}
?>
```

### 3. 관리자 주문 목록 (admin/order_list.php)

```php
<?php
require_once '../includes/ProductSpecFormatter.php';
$specFormatter = new ProductSpecFormatter($db);

// 주문 조회
$query = "SELECT * FROM mlangorder_printauto WHERE DATE(order_date) = CURDATE() ORDER BY no DESC";
$result = mysqli_query($db, $query);

while ($item = mysqli_fetch_assoc($result)) {
    $unified = $specFormatter->formatUnified($item);
    ?>
    <tr>
        <td><?= $item['order_no'] ?></td>
        <td><?= htmlspecialchars(ProductSpecFormatter::getProductTypeName($item['product_type'])) ?></td>
        <td>
            <?= htmlspecialchars($unified['line1']) ?><br>
            <span style="color:#666;"><?= htmlspecialchars($unified['line2']) ?></span>
            <?php if (!empty($unified['line3'])): ?>
                <br><span style="color:#2e7d32;">➕ <?= htmlspecialchars($unified['line3']) ?></span>
            <?php endif; ?>
            <?php if (!empty($unified['line4'])): ?>
                <br><span style="color:#e65100;">✨ <?= htmlspecialchars($unified['line4']) ?></span>
            <?php endif; ?>
        </td>
        <td><?= number_format(ProductSpecFormatter::getPrice($item)) ?>원</td>
        <td><?= $item['order_status'] ?></td>
    </tr>
    <?php
}
?>
```

### 4. 견적서 페이지 (quote/create.php)

```php
<?php
require_once '../includes/ProductSpecFormatter.php';
$specFormatter = new ProductSpecFormatter($db);

// 견적서 아이템 조회
$query = "SELECT * FROM quotation_temp WHERE quotation_id = ? ORDER BY no";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $quotation_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$totalPrice = 0;
?>
<table class="quotation-table">
    <thead>
        <tr>
            <th>No</th>
            <th>품목</th>
            <th>규격/사양</th>
            <th>수량</th>
            <th>단가</th>
            <th>금액</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $no = 1;
    while ($item = mysqli_fetch_assoc($result)) {
        $unified = $specFormatter->formatUnified($item);
        $quantity = ProductSpecFormatter::getQuantity($item);
        $unit = ProductSpecFormatter::getUnit($item);
        $price = ProductSpecFormatter::getPrice($item);
        $totalPrice += $price;
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars(ProductSpecFormatter::getProductTypeName($item['product_type'])) ?></td>
            <td>
                <?= htmlspecialchars($unified['line1']) ?><br>
                <?= htmlspecialchars($unified['line2']) ?>
                <?php if (!empty($unified['line3'])): ?>
                    <br><small style="color:#2e7d32;">➕ <?= htmlspecialchars($unified['line3']) ?></small>
                <?php endif; ?>
                <?php if (!empty($unified['line4'])): ?>
                    <br><small style="color:#e65100;">✨ <?= htmlspecialchars($unified['line4']) ?></small>
                <?php endif; ?>
            </td>
            <td><?= number_format($quantity) ?> <?= $unit ?></td>
            <td><?= number_format($price) ?>원</td>
            <td><?= number_format($price) ?>원</td>
        </tr>
        <?php
    }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right; font-weight:bold;">합계</td>
            <td style="font-weight:bold;"><?= number_format($totalPrice) ?>원</td>
        </tr>
    </tfoot>
</table>
```

---

## 🎨 추천 CSS 스타일

```css
/* 제품 표시 공통 스타일 */
.spec-line {
    padding: 4px 0;
    font-size: 14px;
    line-height: 1.6;
}

/* 1줄: 규격 (굵게) */
.spec-line-1 {
    font-weight: 600;
    color: #333;
}

/* 2줄: 옵션 */
.spec-line-2 {
    color: #666;
}

/* 3줄: 추가옵션 (녹색 배경) */
.spec-line-3.additional {
    color: #2e7d32;
    background: #e8f5e9;
    padding: 6px 10px;
    border-radius: 4px;
    margin-top: 4px;
    font-size: 13px;
}

/* 4줄: 특수옵션 (주황색 배경) */
.spec-line-4.special {
    color: #e65100;
    background: #fff3e0;
    padding: 6px 10px;
    border-radius: 4px;
    margin-top: 4px;
    font-size: 13px;
}

/* 아이콘 */
.icon {
    margin-right: 4px;
}
```

---

## ✅ 제품별 출력 예시

### 명함
```
일반명함 / 스노우지 250g / 90mm x 50mm
단면칼라 / 1,000매 / 인쇄만
✨ 박:금박
```

### 전단지
```
일반4도 / 모조지 80g / A4
단면 / 1연 (4,000매) / 인쇄만
➕ 추가 옵션: 코팅:단면유광 / 접지:2단접지
```

### 봉투
```
소봉투 / 소봉투(100모조 220*105)
마스터1도 / 1,000매 / 인쇄만
양면테이프: 500개
```

### 스티커
```
사각 / 아트유광코팅 / 90mm x 50mm
500매 / 인쇄만
```

### 카다록
```
중철 / A4
4도4도 / 500부 / 인쇄만
➕ 추가 옵션: 코팅:양면유광
```

### NCR양식지
```
2도 / NCR 2도
4도 / 500권 / 인쇄만
✨ 넘버링:2개
```

---

## 🔄 기존 코드 마이그레이션

### Before (기존 방식)
```php
$result = $specFormatter->format($item);
echo htmlspecialchars($result['line1']);
echo '<br>';
echo htmlspecialchars($result['line2']);
if (!empty($result['additional'])) {
    echo '<br><small>' . htmlspecialchars($result['additional']) . '</small>';
}
```

### After (통합 방식)
```php
$unified = $specFormatter->formatUnified($item);
echo htmlspecialchars($unified['line1']);
echo '<br>';
echo htmlspecialchars($unified['line2']);
if (!empty($unified['line3'])) {
    echo '<br><small class="additional">➕ ' . htmlspecialchars($unified['line3']) . '</small>';
}
if (!empty($unified['line4'])) {
    echo '<br><small class="special">✨ ' . htmlspecialchars($unified['line4']) . '</small>';
}
```

---

## 🧪 테스트 방법

테스트 파일 실행:
```bash
http://localhost/test_unified_format.php
```

11개 품목 전체 테스트 결과:
- ✓ 명함 (표준 필드)
- ✓ 명함 (레거시 필드)
- ✓ 전단지
- ✓ 봉투 (표준 필드)
- ✓ 봉투 (레거시 필드 - 천 단위 변환)
- ✓ 스티커
- ✓ 카다록
- ✓ 포스터
- ✓ 자석스티커
- ✓ NCR양식지
- ✓ 상품권

---

## 📚 관련 문서

- **구현 상세**: `CLAUDE_DOCS/DESIGN/UNIFIED_DISPLAY_TEMPLATE.md`
- **필드 매핑**: 11개 품목별 완전한 매핑 테이블
- **테스트 코드**: `test_unified_format.php`

---

## ⚠️ 주의사항

1. **하위 호환성**: 기존 `format()` 메서드는 그대로 유지되어 있으므로 기존 페이지는 변경 없이 작동합니다.

2. **점진적 마이그레이션**: 새로운 페이지부터 `formatUnified()`를 사용하고, 기존 페이지는 필요에 따라 점진적으로 업데이트하세요.

3. **HTML 이스케이프**: 사용자 입력 데이터를 표시할 때는 반드시 `htmlspecialchars()`를 사용하세요.

4. **빈 줄 처리**: `line3`와 `line4`는 해당 제품에만 표시되므로 `if (!empty($unified['line3']))`로 체크 후 표시하세요.

---

**작성자**: Claude Code
**버전**: 1.0
**테스트**: ✅ 11개 품목 전체 통과
**적용 가능**: 장바구니, 주문, 주문완료, 관리자, 견적서, 마이페이지
