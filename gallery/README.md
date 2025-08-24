# 품목별 갤러리 시스템

portfolio_migration_progress.md를 기반으로 제작된 실제 교정 시안 이미지 갤러리 시스템입니다.

## 📋 시스템 개요

### 주요 특징
- ✅ **실제 주문 데이터 활용**: MlangOrder_PrintAuto 테이블의 완성된 주문(OrderStyle=8)만 표시
- ✅ **품목별 분류**: 9개 품목으로 자동 분류 (전단지, 스티커, 명함, 상품권, 봉투, 양식지, 카다로그, 포스터, 종이자석)
- ✅ **개인정보 보호**: 고객명 자동 마스킹 (김** 형태)
- ✅ **듀얼 파일 시스템**: 신구 업로드 구조 모두 지원
- ✅ **반응형 디자인**: 모바일부터 데스크탑까지 완벽 대응
- ✅ **페이지네이션**: 한 페이지당 20개씩 효율적 로딩

### 파일 구조
```
/gallery/
├── category_gallery.php          # 메인 갤러리 페이지
├── category_gallery_popup.php    # 팝업 이미지 뷰어  
├── gallery-style.css            # 공통 CSS 스타일
├── gallery-script.js            # 공통 JavaScript
└── README.md                    # 이 파일
```

## 🔧 설치 및 설정

### 1. 파일 업로드
모든 파일을 `/gallery/` 디렉토리에 업로드하세요.

### 2. 데이터베이스 확인
시스템이 올바르게 작동하려면 다음 조건이 필요합니다:

```sql
-- 완성된 주문만 조회
SELECT * FROM MlangOrder_PrintAuto 
WHERE OrderStyle = '8' 
  AND ThingCate != '' 
  AND ThingCate IS NOT NULL;
```

### 3. 디렉토리 구조 확인
업로드 이미지는 다음 두 구조를 지원합니다:

```
신규: /MlangOrder_PrintAuto/upload/[주문번호]/[이미지파일]
구버전: /MlangOrder_PrintAuto/upload/[날짜코드]/[주문번호]/[이미지파일]
```

## 🎯 사용법

### 기본 접근
```
http://localhost/gallery/category_gallery.php
```

### URL 파라미터
- `category`: 품목 필터 (all, leaflet, sticker, namecard, coupon, envelope, form, catalog, poster, magnet)
- `page`: 페이지 번호 (기본값: 1)
- `search`: 검색어 (고객명, 주문명 검색)

### 사용 예시
```
# 전단지만 보기
http://localhost/gallery/category_gallery.php?category=leaflet

# 명함 2페이지
http://localhost/gallery/category_gallery.php?category=namecard&page=2  

# 검색
http://localhost/gallery/category_gallery.php?search=김철수
```

## 📊 품목 카테고리 매핑

| 카테고리 키 | 한글명 | DB 저장값 |
|------------|--------|-----------|
| `leaflet` | 전단지 | '전단지', '전단지A5', 'inserted' |
| `sticker` | 스티커 | '스티커' |
| `namecard` | 명함 | '명함' |
| `coupon` | 상품권 | '상품권' |
| `envelope` | 봉투 | '봉투', 'envelope' |
| `form` | 양식지 | '양식지', 'NcrFlambeau' |
| `catalog` | 카다로그 | '카다로그', 'cadarok' |
| `poster` | 소량인쇄(포스터) | '포스터', 'LittlePrint', '소량인쇄' |
| `magnet` | 종이자석 | '종이자석', 'msticker' |

## 🚀 기능 상세

### 1. 메인 갤러리 (category_gallery.php)

**주요 기능:**
- 품목별 필터링 및 카운트 표시
- 실시간 검색 (AJAX 기반)
- 20개씩 페이지네이션
- 이미지 레이지 로딩
- 고객명 마스킹 처리
- 반응형 그리드 레이아웃

**버튼 기능:**
- `상세보기`: WindowSian.php로 주문 상세 정보 표시
- `크게보기`: 팝업으로 이미지 확대 표시

### 2. 팝업 뷰어 (category_gallery_popup.php)

**주요 기능:**
- 전체화면 이미지 뷰어
- 키보드 단축키 지원 (ESC, ←→, Space)
- 이미지 줌 인/아웃
- 같은 카테고리 내 이전/다음 네비게이션
- 이미지 다운로드 기능

**키보드 단축키:**
- `ESC`: 팝업 닫기
- `←→`: 이전/다음 이미지
- `Space`: 확대/축소 토글

### 3. 파일 경로 탐지 로직

portfolio_migration_progress.md에 기반한 지능형 파일 찾기:

```php
// 1순위: 새로운 구조 확인
$newPath = "/upload/{주문번호}/{파일명}";
if (file_exists($newPath)) {
    return $newPath;
}

// 2순위: 구버전 구조에서 날짜 디렉토리 스캔  
foreach ($dateDirs as $dateDir) {
    $oldPath = "/upload/{날짜코드}/{주문번호}/{파일명}";
    if (file_exists($oldPath)) {
        return $oldPath;
    }
}
```

## 🎨 커스터마이징

### CSS 변수
주요 색상과 크기는 gallery-style.css 상단에서 수정 가능:

```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;  
    --grid-gap: 25px;
    --border-radius: 16px;
}
```

### JavaScript 설정
gallery-script.js에서 다음 기능 조정 가능:
- 검색 디바운싱 시간 (기본: 500ms)
- 이미지 레이지 로딩 옵션
- 무한스크롤 활성화

## 🔍 트러블슈팅

### 이미지가 표시되지 않는 경우

1. **데이터베이스 확인**
```sql
-- 완성된 주문이 있는지 확인
SELECT COUNT(*) FROM MlangOrder_PrintAuto WHERE OrderStyle = '8';
```

2. **파일 경로 확인**
```php
// 실제 파일 존재 여부 확인
$uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/MlangOrder_PrintAuto/upload';
echo "Upload directory exists: " . (is_dir($uploadPath) ? 'Yes' : 'No');
```

3. **권한 확인**
업로드 디렉토리가 웹서버에서 읽기 가능한지 확인

### 성능 최적화

**이미지 최적화:**
- WebP 형식 지원 추가
- 썸네일 자동 생성 고려
- CDN 연동 검토

**데이터베이스 최적화:**
```sql
-- 인덱스 추가 권장
CREATE INDEX idx_orderstyle_thingcate ON MlangOrder_PrintAuto(OrderStyle, ThingCate);
CREATE INDEX idx_type_date ON MlangOrder_PrintAuto(Type, Date);
```

## 🔗 연동 가이드

### 기존 페이지에 갤러리 삽입

```php
// iframe으로 삽입
echo '<iframe src="/gallery/category_gallery.php?category=leaflet" 
             width="100%" height="800px" frameborder="0"></iframe>';

// 링크로 연결
echo '<a href="/gallery/category_gallery.php?category=namecard" 
         target="_blank">명함 갤러리 보기</a>';
```

### API 형태로 데이터 사용

갤러리의 핵심 로직을 API로 분리하여 AJAX 요청에 활용 가능:

```javascript
fetch('/gallery/api/get_items.php?category=sticker&page=1')
  .then(response => response.json())
  .then(data => {
    // 갤러리 아이템 렌더링
  });
```

## 📝 개발 노트

### portfolio_migration_progress.md 반영사항

1. **실제 주문 데이터 활용**: OrderStyle='8'인 완성된 주문만 표시
2. **파일 시스템 호환**: 신구 두 구조 모두 지원하는 견고한 탐지 로직  
3. **개인정보 보호**: 고객명 마스킹으로 프라이버시 보장
4. **확장 가능**: 모든 제품 카테고리 지원하는 통합 시스템

### 향후 개선사항

- [ ] 관리자 이미지 일괄 업로드 기능
- [ ] 이미지 태깅 및 키워드 검색
- [ ] 고객 만족도 평가 시스템
- [ ] 소셜 미디어 공유 기능
- [ ] 모바일 앱 연동

---

## 📞 지원

문제 발생 시 다음 정보와 함께 문의:
1. 사용 중인 PHP 버전
2. 데이터베이스 쿼리 결과
3. 브라우저 개발자 도구 에러 로그
4. 파일 권한 상태

**제작일**: 2025년 8월 19일  
**기반 문서**: portfolio_migration_progress.md  
**버전**: 1.0.0