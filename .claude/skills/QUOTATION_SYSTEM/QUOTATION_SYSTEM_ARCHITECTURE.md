# 견적서 시스템 전체 아키텍처 설계서

## 1. 설계 원칙

### 핵심 철학
```
"shop_temp 구조를 그대로 복제한 quotation_temp를 사용하여
9개 취급품목의 계산기 + 임의 품목 입력을 통합 관리하고,
PDF/이메일/웹 출력을 단일 렌더러로 일관성 있게 처리한다"
```

### 데이터 흐름
```
┌─────────────────────────────────────────────────────────────────────┐
│                        입력 (Input)                                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   [9개 품목 계산기]              [임의 품목 입력]                    │
│   ├─ sticker_new (스티커)        ├─ product_name (품명)             │
│   ├─ inserted (전단지)           ├─ specification (규격)            │
│   ├─ namecard (명함)             ├─ quantity (수량)                 │
│   ├─ envelope (봉투)             ├─ unit (단위)                     │
│   ├─ cadarok (카다로그)          ├─ unit_price (단가)               │
│   │   └─ leaflet (이미지용)      └─ supply_price (공급가)           │
│   ├─ littleprint (포스터)                                           │
│   │   └─ poster (매핑용)                                            │
│   ├─ merchandisebond (상품권)                                       │
│   ├─ msticker (자석스티커)                                          │
│   └─ ncrflambeau (NCR양식)                                          │
│                                                                     │
│                           ↓                                         │
│              ┌─────────────────────────────┐                        │
│              │     quotation_temp          │                        │
│              │   (shop_temp 100% 동일)     │                        │
│              │   + quote_id 컬럼 추가      │                        │
│              │   + source_type 컬럼 추가   │                        │
│              └─────────────────────────────┘                        │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                        저장 (Storage)                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   [quotations 테이블]              [quote_items 테이블]             │
│   ├─ 견적서 헤더 정보               ├─ 품목별 상세 정보              │
│   ├─ 고객 정보                      ├─ quotation_temp 원본 복사      │
│   ├─ 금액 합계                      └─ source_type (calculator/manual)│
│   └─ 상태/버전 관리                                                 │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│                        출력 (Output)                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│              ┌─────────────────────────────┐                        │
│              │    QuoteItemRenderer        │                        │
│              │    (공통 렌더러 클래스)      │                        │
│              └─────────────────────────────┘                        │
│                           ↓                                         │
│         ┌─────────────┼─────────────┐                               │
│         ↓             ↓             ↓                               │
│   [PDF 출력]    [이메일 발송]   [웹 미리보기]                       │
│   generate_pdf  send_email     view.php                             │
│                                                                     │
│   ※ 모두 동일한 데이터, 동일한 형식으로 표시                        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. 테이블 설계

### 2.1 quotation_temp (신규 생성)

shop_temp와 **100% 동일 구조** + 견적서 전용 컬럼 추가

```sql
CREATE TABLE IF NOT EXISTS `quotation_temp` (
  -- ===== shop_temp 동일 컬럼 (57개) =====
  `no` int NOT NULL AUTO_INCREMENT COMMENT '고유번호',
  `session_id` varchar(100) NOT NULL COMMENT '세션ID',
  `order_id` varchar(50) DEFAULT NULL COMMENT '주문ID (주문 시 생성)',
  `parent` varchar(50) DEFAULT NULL COMMENT '부모 정보',
  `product_type` varchar(50) NOT NULL DEFAULT 'manual' COMMENT '상품유형',
  `jong` varchar(200) DEFAULT NULL COMMENT '스티커 종류',
  `garo` varchar(50) DEFAULT NULL COMMENT '가로',
  `sero` varchar(50) DEFAULT NULL COMMENT '세로',
  `mesu` varchar(50) DEFAULT NULL COMMENT '매수 (스티커/전단지)',
  `domusong` varchar(200) DEFAULT NULL COMMENT '옵션 정보',
  `uhyung` int DEFAULT '0' COMMENT '디자인 여부',
  `MY_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호1',
  `MY_Fsd` varchar(50) DEFAULT NULL COMMENT '카테고리 번호2',
  `PN_type` varchar(50) DEFAULT NULL COMMENT '카테고리 번호3',
  `MY_amount` decimal(10,2) DEFAULT NULL COMMENT '연수 (전단지 등)',
  `unit` varchar(10) DEFAULT '매' COMMENT '단위',
  `POtype` varchar(10) DEFAULT NULL COMMENT 'PO타입 (단면/양면)',
  `ordertype` varchar(50) DEFAULT NULL COMMENT '주문타입',
  `st_price` decimal(10,2) DEFAULT '0.00' COMMENT '공급가액 (VAT 제외)',
  `st_price_vat` decimal(10,2) DEFAULT '0.00' COMMENT 'VAT 포함 가격',
  `MY_comment` text COMMENT '요청사항/메모',
  `img` varchar(200) DEFAULT NULL COMMENT '이미지 파일명',
  `regdate` int DEFAULT NULL COMMENT '등록시간 (timestamp)',
  `Section` varchar(50) DEFAULT NULL,
  `TreeSelect` varchar(50) DEFAULT NULL,
  `work_memo` text,
  `upload_method` varchar(20) DEFAULT 'upload',
  `uploaded_files_info` text,
  `upload_folder` varchar(255) DEFAULT NULL,
  `uploaded_files` text,
  `ThingCate` varchar(255) DEFAULT NULL,
  `ImgFolder` varchar(255) DEFAULT NULL,
  `coating_enabled` tinyint(1) DEFAULT '0' COMMENT '코팅 옵션',
  `coating_type` varchar(20) DEFAULT NULL,
  `coating_price` int DEFAULT '0',
  `folding_enabled` tinyint(1) DEFAULT '0' COMMENT '접지 옵션',
  `folding_type` varchar(20) DEFAULT NULL,
  `folding_price` int DEFAULT '0',
  `creasing_enabled` tinyint(1) DEFAULT '0' COMMENT '오시 옵션',
  `creasing_lines` int DEFAULT '0',
  `creasing_price` int DEFAULT '0',
  `additional_options_total` int DEFAULT '0',
  `selected_options` text,
  `premium_options` text,
  `premium_options_total` int DEFAULT '0',
  `envelope_tape_enabled` tinyint(1) DEFAULT '0',
  `envelope_tape_quantity` int DEFAULT '0',
  `envelope_tape_price` int DEFAULT '0',
  `envelope_additional_options_total` int DEFAULT '0',
  `MY_type_name` varchar(100) DEFAULT NULL,
  `Section_name` varchar(100) DEFAULT NULL,
  `POtype_name` varchar(100) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `additional_options` text,
  `original_filename` text,
  
  -- ===== 견적서 전용 컬럼 (추가) =====
  `quote_id` int DEFAULT NULL COMMENT '연결된 견적서 ID (저장 후)',
  `source_type` enum('calculator','manual') DEFAULT 'manual' COMMENT '입력 방식',
  `product_name` varchar(200) DEFAULT NULL COMMENT '품명 (임의입력용)',
  `specification` text COMMENT '규격/사양 (임의입력용)',
  `unit_price` decimal(10,2) DEFAULT '0.00' COMMENT '단가 (임의입력용)',
  `notes` text COMMENT '비고',
  
  PRIMARY KEY (`no`),
  KEY `idx_session` (`session_id`),
  KEY `idx_quote_id` (`quote_id`),
  KEY `idx_product_type` (`product_type`),
  KEY `idx_source_type` (`source_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='견적서 임시 품목 테이블 (shop_temp 복제 구조)';
```

### 2.2 quote_items (수정)

quotation_temp의 데이터를 그대로 저장할 수 있는 구조

```sql
-- 기존 quote_items 테이블에 컬럼 추가 (이미 일부 완료)
ALTER TABLE quote_items
  -- shop_temp 핵심 컬럼 추가
  ADD COLUMN `jong` varchar(200) DEFAULT NULL AFTER `product_type`,
  ADD COLUMN `garo` varchar(50) DEFAULT NULL AFTER `jong`,
  ADD COLUMN `sero` varchar(50) DEFAULT NULL AFTER `garo`,
  ADD COLUMN `domusong` varchar(200) DEFAULT NULL AFTER `mesu`,
  ADD COLUMN `uhyung` int DEFAULT '0' AFTER `domusong`,
  ADD COLUMN `Section` varchar(50) DEFAULT NULL AFTER `POtype`,
  ADD COLUMN `TreeSelect` varchar(50) DEFAULT NULL AFTER `Section`,
  
  -- 옵션 컬럼
  ADD COLUMN `coating_enabled` tinyint(1) DEFAULT '0',
  ADD COLUMN `coating_type` varchar(20) DEFAULT NULL,
  ADD COLUMN `coating_price` int DEFAULT '0',
  ADD COLUMN `folding_enabled` tinyint(1) DEFAULT '0',
  ADD COLUMN `folding_type` varchar(20) DEFAULT NULL,
  ADD COLUMN `folding_price` int DEFAULT '0',
  ADD COLUMN `creasing_enabled` tinyint(1) DEFAULT '0',
  ADD COLUMN `creasing_lines` int DEFAULT '0',
  ADD COLUMN `creasing_price` int DEFAULT '0',
  
  -- 이름 컬럼
  ADD COLUMN `MY_type_name` varchar(100) DEFAULT NULL,
  ADD COLUMN `Section_name` varchar(100) DEFAULT NULL,
  ADD COLUMN `POtype_name` varchar(100) DEFAULT NULL;
```

---

## 3. 9개 품목별 표시 형식 통일

### 3.0 품목 타입 매핑 규칙 (★ 중요)

| 품목 | 정식 명칭 | 매핑/레거시 | 비고 |
|------|----------|-------------|------|
| **스티커** | `sticker_new` | `sticker` | 레거시 호환 |
| **전단지** | `inserted` | - | |
| **명함** | `namecard` | - | |
| **봉투** | `envelope` | - | |
| **카다로그** | `cadarok` | `leaflet` | 이미지 가져올 때만 leaflet |
| **포스터** | `littleprint` | `poster` | poster로 매핑 |
| **상품권** | `merchandisebond` | - | |
| **자석스티커** | `msticker` | - | 별도 품목 |
| **NCR양식** | `ncrflambeau` | - | |

```php
// 품목 타입 정규화 예시
$typeNormalization = [
    'sticker' => 'sticker_new',    // 레거시 스티커 → 정식
    'poster' => 'littleprint',     // poster → 정식
    'leaflet' => 'cadarok'         // leaflet → 정식 (이미지용)
];
```

### 3.1 표시 규칙 (DisplayRules)

| 품목 | 정식 타입 | 규격 표시 | 수량 표시 | 단위 |
|------|----------|----------|----------|------|
| **스티커** | sticker_new | 재질: {jong} / 크기: {garo}×{sero}mm / 모양: {domusong} | {mesu} | 매 |
| **전단지** | inserted | {색상}/{용지}/{크기}/{단면양면}/인쇄방식 + **{MY_amount}연 ({mesu}매)** | {MY_amount} | 연 |
| **명함** | namecard | {타입}/{코팅}/{면수}/{수량}/인쇄방식 | {MY_amount} | 매 |
| **봉투** | envelope | {봉투종류}/{용지}/{색상}/인쇄방식 | {MY_amount} | 매 |
| **카다로그** | cadarok | {용지}/{크기}/{페이지}/{부수}/인쇄방식 | {MY_amount} | 부 |
| **포스터** | littleprint | {용지}/{크기}/{수량}/인쇄방식 | {MY_amount} | 매 |
| **상품권** | merchandisebond | {타입}/{크기}/{수량}/인쇄방식 | {MY_amount} | 매 |
| **자석스티커** | msticker | {타입}/{크기}/{면수}/{수량}/인쇄방식 | {MY_amount} | 매 |
| **NCR양식** | ncrflambeau | {양식종류}/{규격}/{색상}/{수량}/인쇄방식 | {MY_amount} | 권 |
| **임의입력** | manual | {직접 입력한 specification} | {quantity} | {unit} |

### 3.2 전단지 연/매수 환산 규칙

```php
// 전단지(inserted)의 연수 → 매수 환산
// 1연 = 4,000매 기준

function getYeonMaesuDisplay($myAmount, $mesu) {
    if ($myAmount <= 0 || $mesu <= 0) return '';
    
    // 연수 표시 (정수면 정수, 소수면 소수점 1자리)
    $yeonDisplay = floor($myAmount) == $myAmount 
        ? number_format($myAmount) 
        : number_format($myAmount, 1);
    
    return $yeonDisplay . '연 (' . number_format($mesu) . '매)';
}

// 예시:
// MY_amount=0.5, mesu=2000 → "0.5연 (2,000매)"
// MY_amount=1, mesu=4000 → "1연 (4,000매)"
// MY_amount=2.5, mesu=10000 → "2.5연 (10,000매)"
```

---

## 4. 공통 렌더러 클래스

### 4.1 QuoteItemRenderer.php

```php
<?php
/**
 * 견적서 품목 공통 렌더러
 * PDF, 이메일, 웹 미리보기에서 동일하게 사용
 */
class QuoteItemRenderer {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * 품목 데이터를 표시용 배열로 변환
     * @param array $item quote_items 또는 quotation_temp 데이터
     * @return array 표시용 데이터
     */
    public function formatItem($item) {
        $productType = $item['product_type'] ?? 'manual';
        
        return [
            'product_name' => $this->getProductName($item),
            'specification' => $this->getSpecification($item),
            'quantity_display' => $this->getQuantityDisplay($item),
            'unit' => $this->getUnit($item),
            'unit_price' => $this->getUnitPrice($item),
            'supply_price' => $this->getSupplyPrice($item),
            'vat_amount' => $this->getVatAmount($item),
            'total_price' => $this->getTotalPrice($item),
            'notes' => $item['notes'] ?? ''
        ];
    }
    
    /**
     * 품명 반환
     */
    public function getProductName($item) {
        $productType = $item['product_type'] ?? 'manual';
        
        // 임의 입력인 경우
        if ($productType === 'manual' && !empty($item['product_name'])) {
            return $item['product_name'];
        }
        
        // 9개 품목 계산기
        $typeNames = [
            'sticker' => '스티커',
            'inserted' => '전단지',
            'namecard' => '명함',
            'envelope' => '봉투',
            'cadarok' => '카다로그',
            'poster' => '포스터',
            'merchandisebond' => '상품권',
            'msticker' => '자석스티커',
            'ncrflambeau' => 'NCR양식'
        ];
        
        return $typeNames[$productType] ?? $item['product_name'] ?? '품목';
    }
    
    /**
     * 규격/사양 반환 (품목별 형식)
     */
    public function getSpecification($item) {
        $productType = $item['product_type'] ?? 'manual';
        $spec = '';
        
        switch ($productType) {
            case 'sticker':
                $spec = $this->formatStickerSpec($item);
                break;
            case 'inserted':
                $spec = $this->formatInsertedSpec($item);
                break;
            case 'namecard':
                $spec = $this->formatNamecardSpec($item);
                break;
            case 'envelope':
                $spec = $this->formatEnvelopeSpec($item);
                break;
            case 'cadarok':
                $spec = $this->formatCadarokSpec($item);
                break;
            case 'poster':
                $spec = $this->formatPosterSpec($item);
                break;
            case 'merchandisebond':
                $spec = $this->formatMerchandisebondSpec($item);
                break;
            case 'msticker':
                $spec = $this->formatMstickerSpec($item);
                break;
            case 'ncrflambeau':
                $spec = $this->formatNcrSpec($item);
                break;
            default:
                $spec = $item['specification'] ?? '';
        }
        
        return $spec;
    }
    
    /**
     * 스티커 규격 형식
     */
    private function formatStickerSpec($item) {
        $parts = [];
        if (!empty($item['jong'])) $parts[] = "재질: {$item['jong']}";
        if (!empty($item['garo']) && !empty($item['sero'])) {
            $parts[] = "크기: {$item['garo']}×{$item['sero']}mm";
        }
        if (!empty($item['domusong'])) {
            $shape = str_replace(['00000 ', '08000 '], '', $item['domusong']);
            $parts[] = "모양: {$shape}";
        }
        return implode(' / ', $parts);
    }
    
    /**
     * 전단지 규격 형식 (연/매수 포함)
     */
    private function formatInsertedSpec($item) {
        $parts = [];
        
        // 기본 사양
        if (!empty($item['MY_type_name'])) $parts[] = $item['MY_type_name'];
        if (!empty($item['Section_name'])) $parts[] = $item['Section_name'];
        if (!empty($item['POtype_name'])) $parts[] = $item['POtype_name'];
        
        // 인쇄방식
        $ordertype = $item['ordertype'] ?? '';
        if ($ordertype === 'print') $parts[] = '인쇄만 의뢰';
        elseif ($ordertype === 'design') $parts[] = '디자인+인쇄';
        
        $spec = implode(' / ', $parts);
        
        // ★ 핵심: 연/매수 표시 추가
        $yeonMaesu = $this->getYeonMaesuDisplay($item);
        if ($yeonMaesu) {
            $spec .= "\n" . $yeonMaesu;
        }
        
        return $spec;
    }
    
    /**
     * 연/매수 표시 문자열 반환
     */
    public function getYeonMaesuDisplay($item) {
        $myAmount = floatval($item['MY_amount'] ?? 0);
        $mesu = intval($item['mesu'] ?? 0);
        
        if ($myAmount <= 0 || $mesu <= 0) return '';
        
        $yeonDisplay = floor($myAmount) == $myAmount 
            ? number_format($myAmount) 
            : number_format($myAmount, 1);
        
        return $yeonDisplay . '연 (' . number_format($mesu) . '매)';
    }
    
    // ... 나머지 품목별 formatXxxSpec 메서드들 ...
    
    /**
     * 수량 표시 반환
     */
    public function getQuantityDisplay($item) {
        $qty = floatval($item['quantity'] ?? $item['MY_amount'] ?? 0);
        
        return ($qty == intval($qty)) 
            ? number_format($qty) 
            : rtrim(rtrim(number_format($qty, 2), '0'), '.');
    }
    
    /**
     * 단위 반환
     */
    public function getUnit($item) {
        return $item['unit'] ?? '개';
    }
    
    /**
     * 공급가액 반환
     */
    public function getSupplyPrice($item) {
        return intval($item['supply_price'] ?? $item['st_price'] ?? 0);
    }
    
    /**
     * VAT 반환
     */
    public function getVatAmount($item) {
        $supply = $this->getSupplyPrice($item);
        $total = $this->getTotalPrice($item);
        return $total - $supply;
    }
    
    /**
     * 합계(VAT포함) 반환
     */
    public function getTotalPrice($item) {
        return intval($item['total_price'] ?? $item['st_price_vat'] ?? 0);
    }
    
    /**
     * 단가 반환 (역계산 검증)
     */
    public function getUnitPrice($item) {
        $supply = $this->getSupplyPrice($item);
        $qty = floatval($item['quantity'] ?? $item['MY_amount'] ?? 0);
        
        if ($qty <= 0) return '-';
        
        $unitPrice = $supply / $qty;
        $calculated = round($unitPrice * $qty);
        
        // 역계산 검증: 단가 × 수량 = 공급가액이면 표시
        if ($calculated == $supply) {
            return number_format($unitPrice, 0);
        }
        
        return '-'; // 무한소수는 생략
    }
}
```

---

## 5. 품목 입력 UI 통합

### 5.1 create.php 구조

```
┌─────────────────────────────────────────────────────────────────────┐
│                    견적서 작성 페이지                                │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  [탭 1: 계산기 품목]           [탭 2: 임의 품목]                     │
│  ┌─────────────────────┐     ┌─────────────────────┐               │
│  │ 품목 선택:          │     │ 품명: [          ] │               │
│  │ ○ 스티커            │     │ 규격: [          ] │               │
│  │ ○ 전단지            │     │ 수량: [    ] 단위 │               │
│  │ ○ 명함              │     │ 단가: [          ] │               │
│  │ ○ 봉투              │     │ 공급가: 자동계산   │               │
│  │ ○ 카다로그          │     │ [+ 품목 추가]      │               │
│  │ ○ 포스터            │     └─────────────────────┘               │
│  │ ○ 상품권            │                                           │
│  │ ○ 자석스티커        │                                           │
│  │ ○ NCR양식           │                                           │
│  │                     │                                           │
│  │ [계산기 열기]        │                                           │
│  └─────────────────────┘                                           │
│                                                                     │
│  ═══════════════════════════════════════════════════════════════   │
│                                                                     │
│  [품목 목록 - quotation_temp 기반]                                  │
│  ┌──┬────────┬────────────────────┬──────┬────┬────────┬────────┐  │
│  │NO│ 품명   │ 규격/사양          │ 수량 │단위│ 공급가 │ 삭제   │  │
│  ├──┼────────┼────────────────────┼──────┼────┼────────┼────────┤  │
│  │1 │스티커  │아트유광/60×90mm    │1,000 │매  │ 16,300 │  [×]   │  │
│  │2 │전단지  │칼라/A4/단면        │  0.5 │연  │ 49,000 │  [×]   │  │
│  │  │        │0.5연 (2,000매)     │      │    │        │        │  │
│  │3 │택배    │선불                │    1 │건  │  4,000 │  [×]   │  │
│  └──┴────────┴────────────────────┴──────┴────┴────────┴────────┘  │
│                                                                     │
│                              합계: 69,300원 (VAT별도)               │
│                                                                     │
│                    [미리보기]  [저장]  [저장 후 이메일 발송]         │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 5.2 계산기 연동 방식

```javascript
// 계산기에서 품목 추가 시
function addFromCalculator(productType, calculatorData) {
    // calculatorData = shop_temp 형식과 동일
    
    fetch('/quote/api/add_item.php', {
        method: 'POST',
        body: JSON.stringify({
            session_id: sessionId,
            source_type: 'calculator',
            product_type: productType,
            ...calculatorData  // shop_temp 컬럼들 그대로 전달
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshItemList();  // 품목 목록 갱신
        }
    });
}

// 임의 품목 추가 시
function addManualItem(productName, specification, quantity, unit, unitPrice) {
    fetch('/quote/api/add_item.php', {
        method: 'POST',
        body: JSON.stringify({
            session_id: sessionId,
            source_type: 'manual',
            product_type: 'manual',
            product_name: productName,
            specification: specification,
            MY_amount: quantity,
            unit: unit,
            unit_price: unitPrice,
            st_price: quantity * unitPrice,
            st_price_vat: Math.round(quantity * unitPrice * 1.1)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshItemList();
        }
    });
}
```

---

## 6. 출력 통합 (PDF/이메일/웹)

### 6.1 공통 데이터 로드

```php
// QuoteManager.php에서 품목 로드 시 QuoteItemRenderer 사용

public function getById($id) {
    // 견적서 헤더 로드
    $quote = $this->loadQuoteHeader($id);
    
    // 품목 로드 및 렌더링
    $renderer = new QuoteItemRenderer($this->db);
    $rawItems = $this->loadQuoteItems($id);
    
    $quote['items'] = array_map(function($item) use ($renderer) {
        return array_merge($item, $renderer->formatItem($item));
    }, $rawItems);
    
    return $quote;
}
```

### 6.2 PDF/이메일/웹 동일 템플릿

```php
// 품목 출력 (PDF, 이메일, 웹 모두 동일)
foreach ($items as $index => $item) {
    echo "<tr>";
    echo "<td>{$index + 1}</td>";
    echo "<td>{$item['product_name']}</td>";
    echo "<td>{$item['specification']}</td>";  // 연/매수 포함
    echo "<td>{$item['quantity_display']}</td>";
    echo "<td>{$item['unit']}</td>";
    echo "<td>{$item['unit_price']}</td>";
    echo "<td>{$item['supply_price']}</td>";
    echo "<td>{$item['notes']}</td>";
    echo "</tr>";
}
```

---

## 7. 구현 우선순위

### Phase 1: 기반 구축 (1-2일)
1. ✅ quotation_temp 테이블 생성 (shop_temp 복제)
2. ✅ quote_items 테이블 컬럼 추가
3. ✅ QuoteItemRenderer 클래스 생성

### Phase 2: 입력 UI (2-3일)
1. create.php 탭 UI 구현
2. 계산기 연동 (9개 품목)
3. 임의 품목 입력 폼
4. quotation_temp CRUD API

### Phase 3: 출력 통합 (1-2일)
1. generate_pdf.php → QuoteItemRenderer 적용
2. send_email.php → QuoteItemRenderer 적용
3. view.php → QuoteItemRenderer 적용

### Phase 4: 테스트/배포 (1일)
1. 전체 플로우 테스트
2. 기존 데이터 마이그레이션
3. 운영 배포

---

## 8. 파일 구조

```
/mlangprintauto/quote/
├── api/
│   ├── add_item.php          # quotation_temp에 품목 추가
│   ├── remove_item.php       # quotation_temp에서 품목 삭제
│   ├── save.php              # 견적서 저장 (quotation_temp → quote_items)
│   ├── generate_pdf.php      # PDF 생성 (QuoteItemRenderer 사용)
│   └── send_email.php        # 이메일 발송 (QuoteItemRenderer 사용)
├── includes/
│   ├── QuoteManager.php      # 견적서 관리 클래스
│   ├── QuoteItemRenderer.php # ★ 공통 렌더러 (신규)
│   ├── ProductSpecFormatter.php  # 기존 (레거시)
│   └── PriceHelper.php       # 가격 계산 헬퍼
├── create.php                # 견적서 작성 페이지
├── edit.php                  # 견적서 수정 페이지
├── view.php                  # 견적서 조회/미리보기
└── list.php                  # 견적서 목록
```

---

## 9. 핵심 요약

| 구분 | 내용 |
|------|------|
| **데이터 소스** | quotation_temp (shop_temp 100% 복제) |
| **입력 방식** | 9개 품목 계산기 + 임의 품목 통합 |
| **표시 형식** | QuoteItemRenderer로 일관성 보장 |
| **연/매수** | 전단지는 규격 컬럼에 "0.5연 (2,000매)" 형식 |
| **출력** | PDF/이메일/웹 모두 동일 렌더러 사용 |

---

## 변경 이력

| 버전 | 날짜 | 내용 |
|------|------|------|
| 1.0 | 2025-12-27 | 초안 작성 |
