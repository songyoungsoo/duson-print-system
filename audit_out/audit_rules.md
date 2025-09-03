# 권장 규칙(자동 생성)

## 공통 폴더 구조
- CSS: `/assets/css/*`
- JS: `/assets/js/*`
- 공통 파츠: `/includes/*` (header.php, nav.php, login.php, footer.php)
- 레이아웃: `/templates/layout.php` (좌: 갤러리 / 우: 계산기)
- 슬라이드: `/slide/*`

## 인라인 금지
- 발견된 인라인 `<style>`: 798개 파일
- 발견된 인라인 `<script>`: 1421개 파일
- **모두 assets로 이전**, 페이지에는 include/link/script만 유지

## include 규칙
- `require_once __DIR__.'/파일.php';` 형식으로 **절대경로화**
- 페이지 파일(index 등)은 **include만** 남기고 로직 금지

## 자산 로드 규칙
- `includes/config.php`에 `asset('/assets/...')` 헬퍼로 `?v=버전` 캐시버스트
- 중복 사용 상위 CSS/JS:
  - CSS: {"https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap":65,"https://fonts.googleapis.com":63,"../../css/namecard-compact.css":63,"https://fonts.gstatic.com":63,"http://www.script.ne.kr/script.css":34,"../../css/common_style.css":33,"../../css/btn-primary.css":28,"../../css/gallery-common.css":26,"https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap":24,"../../css/unified-gallery.css":23}
  - JS : {"../js/coolbar.js":156,"../../includes/js/UniversalFileUpload.js":63,"../../includes/js/GalleryLightbox.js":50,"../js/exchange.js":38,"https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js":24,"../../includes/js/UnifiedGallery.js":21,"https://code.jquery.com/jquery-3.6.0.min.js":21,"../../js/coolbar.js":20,"js/ncrflambeau-compact.js":18,"/js/MlangFalseViewObj.js":17}

## 캐러셀 규격(고정)
- Center-mode Peek: 가운데 2/3·W, 좌/우 각 1/6·W, **항상 우→좌(Forward-Only)**
- 전환 800ms, 자동 2000ms, 호버/포커스 일시정지, prev 비활성

## 접근성/성능
- `aria-roledescription="carousel"`, 도트 `aria-current`, lazy, CLS 방지, reduced-motion 지원

## PHP 호환/보안
- PHP 7.4+ 호환, `mysqli + prepared statements`
- 공통 `functions.php`: `e()`, `money_kr()`, CSRF 토큰, 페이징
