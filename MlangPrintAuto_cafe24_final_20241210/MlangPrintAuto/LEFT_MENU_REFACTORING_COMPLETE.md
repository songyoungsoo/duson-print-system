# 좌측 메뉴 공통화 및 노토 폰트 적용 완료 보고서

## 작업 개요
`MlangPrintAuto/inserted/left.php` 파일을 루트 디렉토리(`C:\xampp\htdocs\left.php`)로 이동시켜 모든 품목에서 공통으로 사용할 수 있도록 하고, 노토 폰트를 적용하는 작업을 완료했습니다.

## 주요 변경사항

### 1. 좌측 메뉴 공통화
- **이동된 파일**: `MlangPrintAuto/inserted/left.php` → `left.php` (루트 디렉토리)
- **목적**: 모든 품목 페이지에서 동일한 좌측 메뉴 사용
- **적용 범위**: 
  - 전단지/리플렛 (`MlangPrintAuto/inserted/index.php`)
  - 명함, 스티커, 봉투, 포스터 등 모든 품목

### 2. 노토 폰트 적용
- **적용 위치**: 
  - `left.php` - 좌측 메뉴
  - `MlangPrintAuto/header.php` - 공통 헤더
  - `MlangPrintAuto/MlangPrintAutoTop.php` - 기존 상단 레이아웃
- **폰트 설정**: `'Noto Sans KR', sans-serif !important`
- **가중치**: 300, 400, 500, 700 지원

### 3. 연결 관계 수정
- **index.php 수정**: `include "left.php"` → `include "../../left.php"`
- **MlangPrintAutoTop.php 수정**: `include "/left.htm"` → `include "/left.php"`
- **경로 호환성**: 상대 경로로 올바르게 연결

## 기술적 개선사항

### 1. 동적 활성 메뉴 표시
```php
function isActive($page_path, $current_page) {
    return strpos($current_page, $page_path) !== false ? 'active' : '';
}
```
- 현재 페이지에 따라 자동으로 활성 메뉴 표시
- 각 품목별 고유 색상 테마 적용

### 2. 반응형 디자인
```css
.vertical-menu a:hover {
    transform: translateX(6px);
}
```
- 호버 시 부드러운 애니메이션 효과
- 각 품목별 차별화된 호버 색상

### 3. 접근성 향상
- 명확한 색상 대비
- 충분한 클릭 영역 (padding: 14px)
- 키보드 네비게이션 지원

## 품목별 메뉴 구성

### 메뉴 항목 및 링크
1. **견적주문** - `/MlangPrintAuto/inserted/index.php` (검은색)
2. **전단지/리플렛** - `/MlangPrintAuto/inserted/index.php?page=inserted` (주황색)
3. **스티커** - `/main_layout.php?page=sticker` (녹색)
4. **명함** - `/MlangPrintAuto/NameCard/index.php` (보라색)
5. **쿠폰** - `/MlangPrintAuto/MerchandiseBond/index.php?page=MerchandiseBond` (분홍색)
6. **봉투** - `/MlangPrintAuto/envelope/index.php?page=envelope` (갈색)
7. **양식지** - `/MlangPrintAuto/NcrFlambeau/index.php?page=NcrFlambeau` (청회색)
8. **카다록** - `/MlangPrintAuto/cadarok/index.php?page=cadarok` (연두색)
9. **포스터** - `/MlangPrintAuto/LittlePrint/index.php?page=LittlePrint` (빨간색)

### 색상 테마
각 품목별로 고유한 색상을 적용하여 사용자가 현재 위치를 쉽게 파악할 수 있도록 했습니다.

## 폰트 적용 상세

### 1. Google Fonts 연결
```html
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
```

### 2. CSS 우선순위 설정
```css
body, table, tr, td, select, input, textarea, button, a, div, span, p, li {
    font-family: 'Noto Sans KR', sans-serif !important;
}
```

### 3. 폰트 가중치 활용
- **300**: 얇은 텍스트
- **400**: 일반 텍스트 (기본)
- **500**: 중간 굵기 (라벨)
- **700**: 굵은 텍스트 (버튼, 제목)

## 호환성 보장

### 1. 기존 기능 유지
- 모든 링크 경로 정상 작동
- JavaScript 함수 호환성 유지
- CSS 스타일 충돌 방지

### 2. 브라우저 호환성
- 모든 주요 브라우저에서 노토 폰트 지원
- 폰트 로딩 실패 시 시스템 폰트로 대체

### 3. 성능 최적화
- Google Fonts preconnect 적용
- 필요한 폰트 가중치만 로드

## 사용 방법

### 새로운 품목 페이지 추가 시
```php
<!-- 좌측 메뉴 영역 -->
<td width="160" rowspan="3" valign="top">
    <?php include "../../left.php"; ?>
</td>
```

### 기존 MlangPrintAutoTop.php 사용 페이지
- 자동으로 새로운 `left.php` 적용됨
- 추가 수정 불필요

## 향후 개선 가능사항

1. **메뉴 관리 시스템**: 데이터베이스 기반 메뉴 관리
2. **다국어 지원**: 메뉴 텍스트 다국어 처리
3. **권한별 메뉴**: 사용자 권한에 따른 메뉴 표시
4. **모바일 최적화**: 반응형 메뉴 구현

## 결론

이번 작업을 통해 좌측 메뉴가 완전히 공통화되어 유지보수성이 크게 향상되었습니다. 노토 폰트 적용으로 한글 가독성이 개선되었으며, 일관된 디자인 시스템이 구축되었습니다. 

모든 품목 페이지에서 동일한 사용자 경험을 제공하며, 새로운 품목 추가 시에도 쉽게 확장할 수 있는 구조가 완성되었습니다.