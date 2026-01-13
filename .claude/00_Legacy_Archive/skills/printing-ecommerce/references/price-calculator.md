# 가격 계산기

## 개요

인쇄물 가격은 **사이즈 × 용지 × 수량 × 인쇄면 + 추가옵션**으로 계산됩니다.

## 가격 테이블 구조

### prices 테이블
```sql
CREATE TABLE prices (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(50),      -- sticker_new, inserted, namecard...
    size VARCHAR(50),              -- A4, A3, 90x50...
    paper VARCHAR(100),            -- 아트지 150g, 스노우지 200g...
    sides VARCHAR(20),             -- 단면, 양면
    quantity INT,                  -- 수량 (매수)
    price INT,                     -- 가격
    
    INDEX idx_product (product_type, size, paper, sides, quantity)
);
```

### 추가옵션 가격
```sql
CREATE TABLE option_prices (
    idx INT AUTO_INCREMENT PRIMARY KEY,
    product_type VARCHAR(50),
    option_type VARCHAR(50),       -- coating, folding, scoring, cutting
    option_value VARCHAR(50),      -- 유광, 무광, 2단접지...
    base_price INT,                -- 기본 추가금액
    unit_price INT,                -- 수량당 추가금액
    
    INDEX idx_option (product_type, option_type)
);
```

## JavaScript 계산 로직

### 기본 구조
```javascript
class PriceCalculator {
    constructor(productType) {
        this.productType = productType;
        this.priceTable = {};
        this.optionPrices = {};
    }
    
    // 가격표 로드
    async loadPriceTable() {
        const res = await fetch(`/api/prices.php?type=${this.productType}`);
        this.priceTable = await res.json();
    }
    
    // 기본 가격 조회
    getBasePrice(size, paper, sides, quantity) {
        const key = `${size}|${paper}|${sides}|${quantity}`;
        return this.priceTable[key] || 0;
    }
    
    // 추가옵션 가격
    getOptionPrice(optionType, optionValue, quantity) {
        const option = this.optionPrices[optionType]?.[optionValue];
        if (!option) return 0;
        return option.base_price + (option.unit_price * quantity);
    }
    
    // 총 가격 계산
    calculate(formData) {
        let total = this.getBasePrice(
            formData.size, 
            formData.paper, 
            formData.sides, 
            formData.quantity
        );
        
        // 추가옵션 계산
        if (formData.coating) {
            total += this.getOptionPrice('coating', formData.coating, formData.quantity);
        }
        if (formData.folding) {
            total += this.getOptionPrice('folding', formData.folding, formData.quantity);
        }
        if (formData.scoring) {
            total += this.getOptionPrice('scoring', formData.scoring, formData.quantity);
        }
        
        return total;
    }
}
```

### 전단지 "연" 단위 변환
```javascript
// 전단지는 "연" 단위 사용 (1연 = 500매 × 4 = 2,000매 기준으로 변환)
const SHEETS_PER_REAM = {
    'A4': 2000,   // 0.5연 = 1000매
    'A3': 1000,   // 0.5연 = 500매
    'B4': 1500,
    'B5': 2000
};

function convertReamToSheets(ream, size) {
    const sheetsPerReam = SHEETS_PER_REAM[size] || 2000;
    return Math.round(ream * sheetsPerReam);
}

function formatQuantityDisplay(ream, size) {
    const sheets = convertReamToSheets(ream, size);
    return `${ream}연 (${sheets.toLocaleString()}매)`;
}

// 예시
formatQuantityDisplay(0.5, 'A4');  // "0.5연 (1,000매)"
formatQuantityDisplay(1, 'A4');    // "1연 (2,000매)"
```

## PHP 계산 로직 (서버 검증)

```php
class PriceCalculator {
    private $pdo;
    private $productType;
    
    public function __construct($pdo, $productType) {
        $this->pdo = $pdo;
        $this->productType = $productType;
    }
    
    // 기본 가격 조회
    public function getBasePrice($size, $paper, $sides, $quantity) {
        $sql = "SELECT price FROM prices 
                WHERE product_type = ? AND size = ? AND paper = ? 
                AND sides = ? AND quantity = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->productType, $size, $paper, $sides, $quantity]);
        $row = $stmt->fetch();
        return $row ? (int)$row['price'] : 0;
    }
    
    // 추가옵션 가격
    public function getOptionPrice($optionType, $optionValue, $quantity) {
        $sql = "SELECT base_price, unit_price FROM option_prices 
                WHERE product_type = ? AND option_type = ? AND option_value = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->productType, $optionType, $optionValue]);
        $row = $stmt->fetch();
        
        if (!$row) return 0;
        return (int)$row['base_price'] + ((int)$row['unit_price'] * $quantity);
    }
    
    // 서버 검증
    public function validatePrice($formData, $submittedPrice) {
        $calculated = $this->calculate($formData);
        
        // 5% 오차 허용 (반올림 차이)
        $tolerance = $calculated * 0.05;
        return abs($calculated - $submittedPrice) <= $tolerance;
    }
}
```

## 수량별 할인

```php
// 대량 주문 할인율
const BULK_DISCOUNTS = [
    5000  => 0.05,  // 5,000매 이상: 5% 할인
    10000 => 0.10,  // 10,000매 이상: 10% 할인
    50000 => 0.15,  // 50,000매 이상: 15% 할인
];

function applyBulkDiscount($price, $quantity) {
    $discount = 0;
    foreach (BULK_DISCOUNTS as $threshold => $rate) {
        if ($quantity >= $threshold) {
            $discount = $rate;
        }
    }
    return $price * (1 - $discount);
}
```

## 추가옵션 종류

| 옵션 | 값 | 설명 |
|------|-----|------|
| coating | 유광, 무광, 벨벳 | 코팅 |
| folding | 2단, 3단, 대문 | 접지 |
| scoring | 1줄, 2줄, 3줄 | 오시 |
| cutting | 도무송, 톰슨 | 재단 |
| numbering | 일련번호 | 넘버링 |
| perforation | 1줄, 2줄 | 미싱 |

## AJAX 실시간 계산

```javascript
// 옵션 변경시 자동 계산
$('#priceForm select, #priceForm input').on('change', function() {
    calculateAndDisplay();
});

async function calculateAndDisplay() {
    const formData = {
        size: $('#size').val(),
        paper: $('#paper').val(),
        sides: $('#sides').val(),
        quantity: $('#quantity').val(),
        coating: $('#coating:checked').val(),
        folding: $('#folding').val(),
        scoring: $('#scoring:checked').val()
    };
    
    // 서버 계산 (검증용)
    const res = await fetch('/api/calculate_price.php', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    const result = await res.json();
    
    // 가격 표시
    $('#totalPrice').text(result.price.toLocaleString() + '원');
    $('#quantityDisplay').text(result.quantity_display);
}
```
