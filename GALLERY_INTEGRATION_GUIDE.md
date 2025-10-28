# 새 갤러리 시스템 통합 가이드

## 📋 개요

각 품목 페이지에 **단 3줄**로 새 갤러리를 적용할 수 있습니다.
- ✅ 기존 이미지 데이터 사용
- ✅ 기존 샘플더보기 모달 사용
- ✅ 500×400 메인 + 200% 마우스 오버 줌
- ✅ **계산 로직 절대 건드리지 않음**

## 🚀 적용 방법

### 1단계: 헤더에 JavaScript 추가
```php
<!-- 공통 갤러리 팝업 함수 (샘플더보기 버튼용) -->
<script src="../../js/common-gallery-popup.js"></script>
```

### 2단계: 갤러리 섹션에 코드 추가
```php
<!-- 좌측: 갤러리 -->
<section class="product-gallery">
    <?php
    $gallery_product = '제품명'; // namecard, sticker, envelope 등
    include '../../includes/simple_gallery_include.php';
    ?>
</section>
```

## 📍 품목별 적용 예시

### 명함 (namecard) ✅ 완료
```php
// 파일: /var/www/html/mlangprintauto/namecard/index.php
$gallery_product = 'namecard';
include '../../includes/simple_gallery_include.php';
```

### 전단지 (inserted)
```php
// 파일: /var/www/html/mlangprintauto/inserted/index.php
$gallery_product = 'inserted';
include '../../includes/simple_gallery_include.php';
```

### 일반스티커 (sticker_new)
```php
// 파일: /var/www/html/mlangprintauto/sticker_new/index.php
$gallery_product = 'sticker';
include '../../includes/simple_gallery_include.php';
```

### 자석스티커 (msticker)
```php
// 파일: /var/www/html/mlangprintauto/msticker/index.php
$gallery_product = 'msticker';
include '../../includes/simple_gallery_include.php';
```

### 봉투 (envelope)
```php
// 파일: /var/www/html/mlangprintauto/envelope/index.php
$gallery_product = 'envelope';
include '../../includes/simple_gallery_include.php';
```

### 포스터 (littleprint)
```php
// 파일: /var/www/html/mlangprintauto/littleprint/index.php
$gallery_product = 'littleprint';
include '../../includes/simple_gallery_include.php';
```

### 카탈로그 (cadarok)
```php
// 파일: /var/www/html/mlangprintauto/cadarok/index.php
$gallery_product = 'cadarok';
include '../../includes/simple_gallery_include.php';
```

### 상품권/쿠폰 (merchandisebond)
```php
// 파일: /var/www/html/mlangprintauto/merchandisebond/index.php
$gallery_product = 'merchandisebond';
include '../../includes/simple_gallery_include.php';
```

### 양식지 (ncrflambeau)
```php
// 파일: /var/www/html/mlangprintauto/ncrflambeau/index.php
$gallery_product = 'ncrflambeau';
include '../../includes/simple_gallery_include.php';
```

## 🔧 기존 갤러리 코드 제거

각 품목 페이지에서 다음 코드들을 찾아서 **주석 처리** 또는 **삭제**하세요:

### 제거할 코드 1: 헤드 섹션
```php
// ❌ 제거: 기존 갤러리 초기화
// if (file_exists('../../includes/gallery_helper.php')) {
//     include_once '../../includes/gallery_helper.php';
// }
// if (function_exists("init_gallery_system")) {
//     init_gallery_system("제품명");
// }
```

### 제거할 코드 2: CSS 링크
```html
<!-- ❌ 제거: 기존 갤러리 CSS -->
<!-- <link rel="stylesheet" href="../../css/unified-gallery.css"> -->
```

### 제거할 코드 3: JavaScript
```html
<!-- ❌ 제거: 기존 갤러리 JS (샘플더보기는 제외) -->
<!-- <script src="../../duson/js/gallery-system.js" defer></script> -->
```

### 제거할 코드 4: 갤러리 섹션
```php
<!-- ❌ 제거: 기존 갤러리 렌더링 -->
<?php
// if (function_exists("include_product_gallery")) {
//     include_product_gallery('제품명');
// }
?>
```

## ⚠️ 주의사항

### 절대 건드리면 안 되는 것
- ❌ **가격 계산 JavaScript 파일**
- ❌ **PHP 계산 로직 함수**
- ❌ **데이터베이스 쿼리 코드**
- ❌ **폼 제출 처리 코드**
- ❌ **AJAX 가격 계산 엔드포인트**

### 안전하게 수정 가능한 것
- ✅ 갤러리 관련 HTML/CSS/JS
- ✅ 갤러리 이미지 데이터 로드
- ✅ 샘플더보기 모달

## 🎨 갤러리 기능

### 메인 이미지
- 크기: 500px × 400px 고정
- 중앙 정렬
- 배경: #f5f5f5

### 줌 기능
- 마우스 오버: 즉시 200% 확대
- 마우스 이동: 확대 위치 따라감
- 마우스 아웃: 즉시 원래 크기

### 썸네일
- 크기: 80px × 80px
- 4개 표시
- 클릭 시 메인 이미지 변경

### 샘플더보기 버튼
- 크기: 80px × 80px
- 디자인: 남색 배경 SVG
- 클릭 시: 기존 팝업 갤러리 열림

## 📂 관련 파일

### 새로 생성된 파일
- `/includes/simple_gallery_include.php` - 메인 인클루드 파일
- `/includes/new_gallery_wrapper.php` - 갤러리 렌더링 로직

### 기존 활용 파일
- `/includes/gallery_data_adapter.php` - 이미지 데이터 로드
- `/includes/gallery_component.php` - 모달 렌더링
- `/includes/unified_gallery_modal.php` - 모달 HTML/CSS
- `/js/common-gallery-popup.js` - 샘플더보기 이벤트

## 🧪 테스트 체크리스트

각 품목 적용 후 다음을 확인하세요:

- [ ] 메인 이미지가 500×400 크기로 표시됨
- [ ] 메인 이미지 중앙 정렬 확인
- [ ] 마우스 오버 시 200% 확대 작동
- [ ] 마우스 이동 시 확대 위치 변경
- [ ] 마우스 아웃 시 원래 크기로 복귀
- [ ] 썸네일 4개 표시 확인
- [ ] 썸네일 클릭 시 메인 이미지 변경
- [ ] 샘플더보기 버튼 표시 (남색 SVG)
- [ ] 샘플더보기 클릭 시 팝업 열림
- [ ] **가격 계산 정상 작동** ⭐ 가장 중요!

## 💡 문제 해결

### 갤러리가 표시되지 않음
- `$gallery_product` 변수 설정 확인
- 파일 경로 확인 (`../../includes/simple_gallery_include.php`)
- `common-gallery-popup.js` 로드 확인

### 샘플더보기 버튼이 작동하지 않음
- `<script src="../../js/common-gallery-popup.js"></script>` 추가 확인
- 브라우저 콘솔에서 JavaScript 오류 확인

### 이미지가 로드되지 않음
- `/ImgFolder/sample/{제품명}/` 경로에 이미지 존재 확인
- `gallery_data_adapter.php`의 경로 설정 확인

### 가격 계산이 작동하지 않음
- ⚠️ **즉시 원복 필요!**
- 갤러리 코드만 롤백
- 계산 관련 JavaScript/PHP 파일 확인

## 📝 작업 로그

### 2025-10-28
- ✅ 명함 페이지에 새 갤러리 적용 완료
- ✅ simple_gallery_include.php 생성
- ✅ 기존 데이터/모달 시스템 통합
- ✅ 계산 로직 무손상 확인

### 다음 작업
- [ ] 전단지 페이지 적용
- [ ] 스티커 페이지 적용
- [ ] 기타 품목 순차 적용
