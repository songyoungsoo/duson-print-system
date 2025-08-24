# 갤러리 시스템 빠른 참조 가이드

**최종 업데이트**: 2025년 08월 23일  
**시스템 버전**: v1.0.0 (완성판)

---

## ⚡ 3줄 갤러리 구현

### 새 제품에 갤러리 추가하기
```php
// 1. 헬퍼 포함
include_once "../../includes/gallery_helper.php";

// 2. 갤러리 호출 (단 한 줄!)
include_product_gallery('your_product_name', ['mainSize' => [500, 400]]);

// 3. 끝! 완전한 갤러리 시스템이 작동합니다.
```

---

## 🛠️ 필수 파일 구조

```
프로젝트/
├── api/gallery_items.php (통합 API)
├── includes/
│   ├── gallery_helper.php (3줄 헬퍼)
│   └── gallery_data_adapter.php (데이터 로더)
└── MlangPrintAuto/
    └── {product}/
        ├── index.php (3줄 구현)
        └── get_{product}_images.php (전용 API)
```

---

## 📊 API 엔드포인트

### 메인 통합 API
```
GET /api/gallery_items.php?product={product}&page={page}&per_page={limit}
```

### 제품별 직접 API
```
전단지: /MlangPrintAuto/inserted/get_leaflet_images.php
명함:   /MlangPrintAuto/NameCard/get_portfolio_images.php  
스티커: /MlangPrintAuto/sticker_new/get_sticker_images.php
```

---

## 🔧 문제 해결

### "이미지가 안 보여요"
```php
// 1. 파일 존재 확인
$fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
if (!file_exists($fullPath)) {
    echo "파일 없음: " . $fullPath;
}

// 2. 권한 확인
chmod 755 /path/to/images/
chmod 644 /path/to/image.jpg
```

### "팝업이 안 열려요" 
```javascript
// 브라우저 개발자 도구에서 확인
fetch('/api/gallery_items.php?product=inserted&page=1')
  .then(response => response.json())
  .then(data => console.log(data));
```

### "페이지네이션이 이상해요"
```php
// 올바른 offset 계산
$offset = ($page - 1) * $perPage;
$items = array_slice($allItems, $offset, $perPage);
```

---

## 🎯 스티커 특수 시스템

### A/B/C 경로 확인
```php
// A경로: 데이터베이스 실제 주문
SELECT no, ThingCate FROM MlangOrder_PrintAuto WHERE Type LIKE '%스티커%'

// B경로: 정적 갤러리 (126개)
/ImgFolder/sticker/gallery/

// C경로: 포트폴리오 게시판 (5개)  
SELECT Mlang_bbs_file FROM Mlang_portfolio_bbs WHERE CATEGORY LIKE '%스티커%'
```

---

## 🔒 보안 체크리스트

### 개인정보 보호
```php
// ✅ 고객명 마스킹
$maskedName = mb_substr($name, 0, 1) . '***';

// ✅ 최신 데이터만
AND date >= '2020-01-01'

// ✅ 파일 존재 확인
if (file_exists($fullPath)) { /* 추가 */ }
```

### CSS 마스킹 (명함 전용)
```css
.namecard-privacy-protection .gallery-main-img::after {
    width: 40%; height: 35%;
    background: 체커보드패턴;
    backdrop-filter: blur(6px);
}
```

---

## 📱 모바일 대응

### 반응형 CSS
```css
@media (max-width: 768px) {
    .gallery-thumbnails {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .gallery-modal {
        width: 95vw;
        height: 90vh;
    }
}
```

---

## ⚡ 성능 최적화

### 데이터베이스
```sql
-- 인덱스 추가
CREATE INDEX idx_type_date ON MlangOrder_PrintAuto (Type, date);

-- 효율적 쿼리
SELECT no, ThingCate, name FROM table 
WHERE conditions 
ORDER BY date DESC 
LIMIT 100;
```

### 파일 시스템
```php
// 캐싱으로 중복 체크 방지
private static $fileCache = [];

public static function fileExists($path) {
    if (!isset(self::$fileCache[$path])) {
        self::$fileCache[$path] = file_exists($path);
    }
    return self::$fileCache[$path];
}
```

---

## 🚀 새 제품 추가 가이드

### 1단계: API 생성
```php
// /MlangPrintAuto/newproduct/get_newproduct_images.php
$productTypes = ['신제품', 'newproduct'];
// ... 표준 API 로직 복사
```

### 2단계: 통합 API 매핑
```php  
// /api/gallery_items.php에 추가
$productApiMap = [
    // ... 기존 매핑
    'newproduct' => '/MlangPrintAuto/newproduct/get_newproduct_images.php'
];
```

### 3단계: 메인갤러리 구현
```php
// /MlangPrintAuto/newproduct/index.php
include_once "../../includes/gallery_helper.php";
include_product_gallery('newproduct');
```

### 4단계: 데이터 어댑터 매핑
```php
// /includes/gallery_data_adapter.php에 추가
$productTypeMap = [
    // ... 기존 매핑  
    'newproduct' => ['신제품', 'newproduct']
];
```

---

## 📋 테스트 체크리스트

### 기능 테스트
- [ ] 메인갤러리 4개 썸네일 표시
- [ ] "더 많은 샘플 보기" 버튼 작동  
- [ ] 팝업갤러리 이미지 로딩
- [ ] 페이지네이션 정상 작동
- [ ] 모바일 반응형 확인

### 성능 테스트  
- [ ] API 응답 500ms 이하
- [ ] 이미지 로딩 적정 속도
- [ ] 대용량 갤러리 정상 처리

### 보안 테스트
- [ ] 개인정보 마스킹 적용
- [ ] SQL Injection 방지
- [ ] XSS 방지

---

## 🔍 디버깅 도구

### API 응답 확인
```bash
# API 직접 호출 테스트
curl "http://localhost/api/gallery_items.php?product=sticker&page=1"

# 특정 제품 API 테스트  
curl "http://localhost/MlangPrintAuto/sticker_new/get_sticker_images.php"
```

### 데이터베이스 직접 쿼리
```sql
-- 이미지 있는 주문 확인
SELECT COUNT(*) FROM MlangOrder_PrintAuto 
WHERE Type LIKE '%스티커%' AND ThingCate IS NOT NULL;

-- 최신 주문 확인
SELECT no, name, ThingCate, date FROM MlangOrder_PrintAuto 
WHERE Type LIKE '%스티커%' ORDER BY date DESC LIMIT 10;
```

### 파일 시스템 확인
```bash
# 이미지 디렉토리 확인
ls -la /C/xampp/htdocs/ImgFolder/sticker/gallery/

# 파일 권한 확인  
stat /path/to/image.jpg
```

---

## 📞 지원 정보

### 로그 파일 위치
```
에러 로그: C:\xampp\apache\logs\error.log
PHP 에러: C:\xampp\php\logs\php_error.log  
사용자 정의: /logs/gallery_*.log
```

### 자주 하는 질문

**Q: 새 이미지가 갤러리에 안 나타나요**  
A: 파일명에 특수문자가 있는지, 파일 권한이 755/644인지 확인

**Q: 페이지네이션이 이상해요**  
A: `$offset = ($page - 1) * $perPage` 계산식 확인

**Q: 모바일에서 이상해요**  
A: 반응형 CSS와 viewport 메타태그 확인

---

## 🎉 완료!

이 가이드로 **두손기획인쇄 갤러리 시스템**을 완벽하게 이해하고 유지보수할 수 있습니다.

**핵심만 기억하세요**:
- ✅ **3줄 구현**: `include_once + include_product_gallery() + 끝`
- ✅ **API 매핑**: `/api/gallery_items.php`에서 제품별 라우팅
- ✅ **스티커 특수**: A/B/C 경로 통합 시스템  
- ✅ **개인정보 보호**: 마스킹 필수 적용

---

**빠른 참조 가이드 v1.0**  
*2025년 08월 23일 - 프로덕션 완성판*