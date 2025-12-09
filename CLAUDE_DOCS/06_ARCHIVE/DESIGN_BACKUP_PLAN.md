# 🎯 전단지 디자인 기반 9개 품목 통합 설계서

## 📋 이미지 분석 결과

### 전단지 페이지 구조 분석:
- **상단 헤더**: 녹색 배경 "전단지 견적 안내"
- **좌측 갤러리**: 샘플 이미지들 (4x2 그리드)
- **우측 계산기**: 
  - 인쇄색상: 칼라인쇄(CMYK) 
  - 종이중량: 100g아트지(90g~95g)
  - 종이규격: B5(16절)182x257
  - 인쇄면: 단면 (양면단)
  - 수량: 4000매 (0.5절)
  - 편집디자인: 인쇄만 의뢰 (별별 종합환료)
- **견적 결과**: 민트색 박스 "68,000원"
- **주문 버튼**: 보라색 "파일 업로드 및 주문하기"

## 🔄 백업 및 복제 계획

### 1단계: 기존 폴더 백업 (01 접미사)
```bash
mlangprintauto/
├── namecard/        → namecard01/     (백업)
├── poster/          → poster01/       (백업)
├── envelope/        → envelope01/     (백업)
├── ncrflambeau/     → ncrflambeau01/  (백업)
├── sticker/         → sticker01/      (백업)
├── merchandisebond/ → merchandisebond01/ (백업)
├── cadarok/         → cadarok01/      (백업)
├── littleprint/     → littleprint01/  (백업)
├── msticker/        → msticker01/     (백업)
└── inserted/        → inserted01/     (백업)
```

### 2단계: 전단지 기반 새 폴더 생성
```bash
mlangprintauto/
├── namecard/        (새로 생성 - 전단지 모양)
├── poster/          (새로 생성 - 전단지 모양)
├── envelope/        (새로 생성 - 전단지 모양)
├── ncrflambeau/     (새로 생성 - 전단지 모양)
├── sticker/         (새로 생성 - 전단지 모양)
├── merchandisebond/ (새로 생성 - 전단지 모양)
├── cadarok/         (새로 생성 - 전단지 모양)
├── littleprint/     (새로 생성 - 전단지 모양)
├── msticker/        (새로 생성 - 전단지 모양)
└── inserted/        (새로 생성 - 전단지 모양)
```

## 📐 전단지 템플릿 구조

### 디자인 사양:
- **헤더 높이**: 64px
- **헤더 색상**: 녹색 그라데이션 (#4CAF50 → #66BB6A)
- **레이아웃**: 좌우 2단 (갤러리 50% : 계산기 50%)
- **갤러리**: 4x2 이미지 그리드
- **계산기**: 6개 필드 (2x3 그리드)
- **결과박스**: 민트색 (#E8F5E9) 배경
- **버튼**: 보라색 그라데이션 (#7C4DFF → #9575CD)

### 품목별 맞춤 설정:
```php
$products = [
    'namecard' => [
        'title' => '명함 견적 안내',
        'icon' => '💳',
        'fields' => [
            '종류', '재질', '인쇄면', '수량', '코팅', '편집디자인'
        ]
    ],
    'poster' => [
        'title' => '포스터 견적 안내', 
        'icon' => '🎨',
        'fields' => [
            '구분', '종이종류', '종이규격', '인쇄면', '코팅', '편집디자인'
        ]
    ],
    'envelope' => [
        'title' => '봉투 견적 안내',
        'icon' => '✉️', 
        'fields' => [
            '종류', '규격', '용지', '인쇄', '수량', '편집디자인'
        ]
    ],
    // ... 나머지 품목들
];
```

## 🔧 구현 파일 구조

### 각 품목 폴더 내용:
```
namecard/
├── index.php              (전단지 모양 메인 페이지)
├── config/
│   └── namecard.config.php (품목별 설정)
├── includes/
│   └── namecard_calc.php  (기존 계산 로직 유지)
├── css/
│   └── product-specific.css (품목별 추가 스타일)
└── js/
    └── namecard.js        (기존 JavaScript 유지)
```

### 공통 CSS 적용:
```css
/* 전단지 디자인 CSS */
/css/flier-template.css
/css/flier-structural-fix.css
```

## 📊 품목별 상세 명세

### 1️⃣ 명함 (NameCard)
```php
'namecard' => [
    'title' => '💳 명함 견적 안내',
    'db_table' => 'mlangprintauto_namecard',
    'fields' => [
        ['label' => '종류', 'name' => 'type'],
        ['label' => '재질', 'name' => 'material'],
        ['label' => '인쇄면', 'name' => 'side'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '코팅', 'name' => 'coating'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 2️⃣ 포스터_리틀프린트 (Poster/LittlePrint)
```php
'poster_littleprint' => [
    'title' => '🎨 포스터_리틀프린트 견적 안내',
    'db_table' => 'mlangprintauto_littleprint',
    'fields' => [
        ['label' => '구분', 'name' => 'category'],
        ['label' => '종이종류', 'name' => 'paper_type'],
        ['label' => '종이규격', 'name' => 'paper_size'],
        ['label' => '인쇄면', 'name' => 'print_side'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 3️⃣ 봉투 (Envelope)
```php
'envelope' => [
    'title' => '✉️ 봉투 견적 안내',
    'db_table' => 'mlangprintauto_envelope',
    'fields' => [
        ['label' => '종류', 'name' => 'type'],
        ['label' => '규격', 'name' => 'size'],
        ['label' => '용지', 'name' => 'paper'],
        ['label' => '인쇄', 'name' => 'print_type'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 4️⃣ NCR/전표 (NcrFlambeau)
```php
'ncrflambeau' => [
    'title' => '📋 NCR/전표 견적 안내',
    'db_table' => 'mlangprintauto_ncrflambeau',
    'fields' => [
        ['label' => '종류', 'name' => 'type'],
        ['label' => '규격', 'name' => 'size'],
        ['label' => '제본', 'name' => 'binding'],
        ['label' => '천공', 'name' => 'punch'],
        ['label' => '넘버링', 'name' => 'numbering'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 5️⃣ 스티커 (Sticker)
```php
'sticker' => [
    'title' => '🏷️ 스티커 견적 안내',
    'db_table' => 'mlangprintauto_sticker_new',
    'fields' => [
        ['label' => '종류', 'name' => 'jong'],
        ['label' => '가로', 'name' => 'garo'],
        ['label' => '세로', 'name' => 'sero'],
        ['label' => '수량', 'name' => 'mesu'],
        ['label' => '편집디자인', 'name' => 'uhyung'],
        ['label' => '모양', 'name' => 'domusong']
    ],
    'calc_function' => 'calculateStickerPrice'
]
```

### 6️⃣ 상품권/쿠폰 (MerchandiseBond)
```php
'merchandisebond' => [
    'title' => '🎫 상품권/쿠폰 견적 안내',
    'db_table' => 'mlangprintauto_merchandisebond',
    'fields' => [
        ['label' => '종류', 'name' => 'type'],
        ['label' => '재질', 'name' => 'material'],
        ['label' => '인쇄면', 'name' => 'print_side'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 7️⃣ 카다록 (Cadarok)
```php
'cadarok' => [
    'title' => '📖 카다록 견적 안내',
    'db_table' => 'mlangprintauto_cadarok',
    'fields' => [
        ['label' => '형태', 'name' => 'type'],
        ['label' => '규격', 'name' => 'size'],
        ['label' => '용지', 'name' => 'paper'],
        ['label' => '인쇄면', 'name' => 'print_side'],
        ['label' => '코팅', 'name' => 'coating'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 9️⃣ 자석스티커 (MSticker)
```php
'msticker' => [
    'title' => '🧲 자석스티커 견적 안내',
    'db_table' => 'mlangprintauto_msticker',
    'fields' => [
        ['label' => '형태', 'name' => 'shape'],
        ['label' => '재질', 'name' => 'material'],
        ['label' => '크기', 'name' => 'size'],
        ['label' => '인쇄면', 'name' => 'print_side'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

### 🔟 전단지 (Inserted)
```php
'inserted' => [
    'title' => '📄 전단지 견적 안내',
    'db_table' => 'mlangprintauto_inserted',
    'fields' => [
        ['label' => '인쇄색상', 'name' => 'color'],
        ['label' => '종이중량', 'name' => 'paper_weight'],
        ['label' => '종이규격', 'name' => 'paper_size'],
        ['label' => '인쇄면', 'name' => 'print_side'],
        ['label' => '수량', 'name' => 'quantity'],
        ['label' => '편집디자인', 'name' => 'design']
    ],
    'calc_function' => 'calculateProductPrice'
]
```

## 🚀 구현 순서

### Phase 1: 백업 및 준비
1. 기존 10개 폴더를 각각 `폴더명01`로 백업
2. 전단지 기본 템플릿 생성

### Phase 2: 새 폴더 생성 
1. 품목별 새 폴더 생성
2. 전단지 템플릿 복사 및 품목별 설정

### Phase 3: 계산 로직 연동
1. 기존 계산 함수를 새 구조에 연결
2. 데이터베이스 테이블 매핑 확인

### Phase 4: 테스트 및 검증
1. 각 품목별 기능 테스트
2. 계산 로직 정확성 검증
3. 디자인 일관성 확인

## ✅ 예상 결과
- **디자인 통일**: 모든 품목이 전단지와 동일한 디자인
- **기능 보존**: 기존 계산 로직 100% 유지
- **안전성**: 기존 파일들 01 폴더에 완전 백업
- **확장성**: 새 품목 추가 용이