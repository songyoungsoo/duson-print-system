# CHANGELOG - 두손기획인쇄 시스템 변경 이력

> **원칙**: 이 파일에 기록되지 않은 로직 수정은 시스템 표준으로 인정하지 않으며, 발견 즉시 롤백 대상입니다.

---

## 2026-02-19

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **비회원 주문 가능하도록 로그인 체크 제거** - upload_modal.js의 openUploadModal() 함수에서 isLoggedIn() 체크 제거. 비회원도 파일 업로드 및 주문 가능 | includes/upload_modal.js |
| Claude | **시간대별 방문자 한국 시간(UTC+9) 표시** - visitor_stats.php의 getHourlyStats()에서 DATE_ADD(visit_time, INTERVAL 9 HOUR) 사용하여 MySQL HOUR() 함수가 한국 시간 기준으로 작동하도록 수정 | dashboard/api/visitor_stats.php |

---

## 2026-02-16

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **배송 무게/박스 추정 시스템** - ShippingCalculator 공통 모듈 신규 생성. 용지 평량×면적×매수 기반 무게 계산, 코팅 가산, 박스 규격별 자동 배치. DB shipping_rates 테이블에서 요금 로드 (fallback 하드코딩). 고객용 estimateFromCart() + 관리자용 estimateFromOrder() | includes/ShippingCalculator.php, includes/shipping_api.php |
| Claude | **주문 페이지 택배 선불 UI** - 택배 선택 시 운임구분(착불/선불) 라디오 표시, 선불 시 AJAX로 무게/박스 추정 표시 (금액 미표시, "추정" 배지, 전화번호 안내) | mlangorder_printauto/OnlineOrder_unified.php |
| Claude | **로젠택배 관리 공통 모듈 교체** - post_list74.php의 하드코딩 shipping_rules → ShippingCalculator::estimateFromOrder() 호출로 교체. 추정무게 컬럼 추가 | shop_admin/post_list74.php |
| Claude | **교정시안 품목명 한글화** - dashboard/proofs/index.php에서 영문 테이블명(sticker_new, inserted 등)을 한글 품목명(스티커, 전단지 등)으로 자동 매핑 표시 | dashboard/proofs/index.php |
| Claude | **대시보드 카테고리 관리 CRUD** - 카테고리 추가/삭제 API 구현 (category_create, category_delete). 가격 데이터 연쇄 삭제 포함 | dashboard/api/products.php |
| Claude | **카테고리 관리 UI 개선** - 스타일 필터(대봉투/소봉투 등) 셀렉트 + 테이블 형식 출력 + 수정 모달(category_update API) + 삭제 확인 + 추가 모달. 기존 리스트 방식에서 테이블 방식으로 전환 | dashboard/products/list.php, dashboard/api/products.php |
| Claude | **FQ 견적번호 체계 도입** - 플로팅 견적받기에 FQ-YYYYMMDD-NNN 형식 자동 번호 생성. AQ(관리자)/FQ(플로팅)/TAX(세금계산서) 3체계 독립 운영 | includes/quote_request_api.php |
| Claude | **견적서 전체 색상 홈페이지 헤더색(#1E4E79) 통일** - 관리자 견적서(QuoteRenderer HTML/Email/PDF) + 플로팅 견적받기 이메일 + 브라우저 미리보기 CSS 전체 적용 | QuoteRenderer.php, quote_request_api.php, layout.php |
| Claude | **문서 업데이트** - AGENTS.md에 배송 추정 시스템, 카테고리 관리, 관리자 주문 등록, 견적번호 체계 섹션 추가 | AGENTS.md, CHANGELOG.md |

---

## 2026-02-15

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **플로팅 견적 위젯 프로덕션 PHP 8.2 Fatal Error 수정** - 4개 제품(포스터/상품권/자석스티커/카다록)에서 `mysqli_close($db)`가 `quote_gauge.php` include보다 먼저 호출되어 PHP 8.2에서 "mysqli object is already closed" Fatal Error 발생. `mysqli_close`를 include 뒤로 이동하여 해결. 로컬 PHP 7.4에서는 증상 없어 발견 어려웠음 | mlangprintauto/littleprint/index.php, merchandisebond/index.php, msticker/index.php, cadarok/index.php |
| Claude | **플로팅 견적 사이드바 옵션 가격 이중 합산 버그 수정** - PriceCalculationService API가 Order_PriceForm에 이미 추가옵션을 포함하여 반환하는데, quote-gauge.js 레거시 경로에서 DOM의 optionTotal을 다시 합산하여 공급가액/합계가 부풀려지던 버그 수정. 스티커/신규포맷 경로는 영향 없음 | js/quote-gauge.js |
| Claude | **플로팅 견적서 이메일 공급받는자/공급자 정보 추가** - 이메일 상단에 공급받는자(견적일, 상호/성명, 연락처, 이메일)와 공급자(등록번호, 상호, 대표자, 연락처) 테이블 50/50 분할 추가. company_info.php SSOT 활용 | includes/quote_request_api.php |
| Claude | **견적서 전체 색상 홈페이지 헤더색 통일** - #1e293b(다크네이비) → #1E4E79(홈페이지 헤더색)로 변경. 적용: 플로팅 이메일, 관리자 견적서(HTML/CSS/PDF), 브라우저 미리보기. 보조색: 테두리 #2a6496, 내부선 #3a7ab5, PDF SetFillColor(30,78,121) | QuoteRenderer.php, quote_request_api.php, layout.php |
| Claude | **견적서 이메일 네이비 격식 테마 통일** - 관리자 견적서(QuoteRenderer)와 플로팅 견적받기(quote_request_api.php) 두 시스템의 이메일 디자인을 격식 테마로 통일 | QuoteRenderer.php, quote_request_api.php, layout.php |
| Claude | **QuoteRenderer 4가지 출력 테마 적용** - renderLegacyHTML(CSS 클래스), renderEmailBody(인라인 스타일), renderLegacyPDF(TCPDF), renderStandardPDF(mPDF CSS) 모두 #1E4E79 컬러 적용 | admin/mlangprintauto/quote/includes/QuoteRenderer.php |
| Claude | **플로팅 견적받기 고객 이메일 변환** - $customerBody 그라데이션 카드 스타일 → 격식 테이블 레이아웃. 헤더("견 적 서"), 라벨 셀, CTA 버튼, 푸터 | includes/quote_request_api.php |
| Claude | **견적서 미리보기 CSS 적용** - layout.php 브라우저 미리보기 페이지 CSS를 #1E4E79 테마로 변환 | mlangprintauto/quote/standard/layout.php |
| Claude | **계좌번호 표시 개선** - payment_info.php 계좌번호 폰트 크기 15→22px, 굵기 600→700, 자간 추가 | sub/customer/payment_info.php |

---

## 2026-02-13

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **프리미엄 옵션 DB화 시스템 구축** - 6개 품목(명함/상품권/전단지/포스터/카다록/봉투)의 프리미엄 옵션 가격을 JS 하드코딩에서 DB 기반으로 전환. 관리자 대시보드에서 CRUD 관리 가능. DB 우선 로드 + 하드코딩 fallback 안전장치 | premium_options, premium_option_variants 테이블 |
| Claude | **관리자 프리미엄 옵션 대시보드** - 6개 품목 탭, 옵션/variant CRUD, 인라인 가격 편집, 정렬순서 변경, 활성/비활성 토글. Tailwind CSS 기반 UI | dashboard/premium-options/index.php |
| Claude | **관리자 프리미엄 옵션 API** - list/create/update/delete/toggle/reorder + 기존 주문 재계산 기능. 3가지 가격 패턴별 재계산 (A: base_perunit, B: multiplier, C: tiered) | dashboard/api/premium_options.php |
| Claude | **고객용 프리미엄 옵션 공개 API** - 인증 불필요, 파일 캐시(5분), 품목별 활성 옵션+가격 데이터 반환 | api/premium_options.php |
| Claude | **공통 프리미엄 옵션 로더** - fetch API로 DB 가격 로드, 실패 시 기존 하드코딩 값 자동 사용 | js/premium-options-loader.js |
| Claude | **고객 페이지 JS DB 통합** - 6개 품목 JS 파일에서 페이지 로드 시 DB 가격 데이터 적용. 기존 계산 로직 변경 없음 | namecard-premium-options.js, merchandisebond-premium-options.js, leaflet-premium-options.js, littleprint-premium-options.js, cadarok-premium-options.js, envelope_tape.js |
| Claude | **대시보드 사이드바 메뉴 추가** - "프리미엄옵션" 메뉴 항목 추가 | dashboard/includes/sidebar.php |
| Claude | **기존 주문 재계산 UI** - 미리보기(변경 전/후 비교 테이블) + 실행 2단계 안전장치. 로딩 스피너, 요약박스, 변경사항 테이블 | dashboard/premium-options/index.php |
| Claude | **추가옵션 가격 표시 누락 수정** - 전단지/포스터/카다록의 코팅/접지/오시 가격, 봉투 양면테이프 가격이 주문페이지에서 표시되지 않던 버그 수정 | includes/ProductSpecFormatter.php |
| Claude | **대시보드 UI 통일 (option_prices 스타일)** - 프리미엄옵션, 옵션가격, 견적관리 3개 페이지를 동일한 스타일로 통일. 행 스트라이프(흰색/#e6f7ff), 33px 행높이, 13px/12px 폰트, #1E4E79 헤더, 980px 전체폭 | dashboard/premium-options/index.php, admin/mlangprintauto/quote/option_prices.php, dashboard/quotes/index.php |
| Claude | **관리자 계정 정보 수정** - CLAUDE.md 접속정보 관리자 계정을 admin/ds701018로 업데이트 | CLAUDE.md |

---

## 2026-02-12

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **방문자분석 페이지 URL 한글화** - 인기 페이지/진입·이탈 페이지/실시간 방문자 테이블에서 URL 경로를 한글 제품명(전단지, 스티커, 명함 등)으로 표시. 클릭 가능한 파란색 링크로 변환. PAGE_NAME_MAP 정확 매칭 + PAGE_PATH_PATTERNS 부분 매칭 2단계 구조 | dashboard/visitors/index.php |
| Claude | **주문통계 숫자 카운트업 애니메이션** - 요약 카드 4개(오늘 주문, 이번달 주문, 이번달 매출, 누적 주문)에 0→목표값 카운트업 애니메이션 추가. easeOutExpo 이징, 800ms 지속시간. 통화 축약(만/억) 포맷 지원 | dashboard/stats/index.php |
| Claude | **대시보드 공통 UI 개선** - 헤더/사이드바/푸터/config 공통 컴포넌트 개선 | dashboard/includes/header.php, sidebar.php, footer.php, config.php |
| Claude | **견적서 UI/UX 개선** - create.php 레이아웃 대폭 개선, 엑셀 스타일 CSS 업데이트, 옵션 가격 연동 개선 | admin/mlangprintauto/quote/create.php, assets/excel-style.css, option_prices.php |
| Claude | **교정관리 API 개선** - 파일 업로드 API 안정성 개선 | dashboard/proofs/api.php, dashboard/proofs/index.php |
| Claude | **인증 시스템 보안 강화** - admin_auth.php, auth.php 보안 로직 개선 | admin/includes/admin_auth.php, includes/auth.php |
| Claude | **주문서 개선** - OnlineOrder_unified.php UI/로직 개선 | mlangorder_printauto/OnlineOrder_unified.php |
| Claude | **Plesk .htaccess 정리** - ImgFolder, upload 디렉토리의 Apache 2.2 호환 불가 .htaccess 삭제 (500 에러 방지) | ImgFolder/.htaccess, mlangorder_printauto/upload/.htaccess |
| Claude | **경쟁사 가격 분석 리포트** - mspg.co.kr 전단지 가격 크롤링 분석. 종이비/판비/인쇄비 역산 분석 문서 | COMPETITOR_PRICING_REPORT.md |
| Claude | **문서 업데이트** - AGENTS.md 교정갤러리/결제/인증 섹션 확장, CLAUDE.md 업데이트 | AGENTS.md, CLAUDE.md |

---

## 2026-02-11

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **SEO 사이트맵 시스템 구축** - Google Search Console & 네이버 서치 어드바이저 등록용 동적 사이트맵 생성 (sitemap.xml, sitemap.php, sitemap.xsl). 32개 페이지 포함 (9개 품목 카테고리 + 장바구니 + 회원 + 게시판 + 회사소개 + 약관/개인정보). XSL 스타일시트로 브라우저에서 예쁘게 표시 | sitemap.php, sitemap.xml, sitemap.xsl |
| Claude | **"두손기획" SEO 리디렉션 페이지** - 과거 브랜드명 검색 고객을 위한 리디렉션 페이지. 메타 키워드 최적화, 3초 후 메인 자동 이동, 검색엔진 친화적 구조 | duson-planning.php |
| Claude | **robots.txt 구축** - 검색엔진 크롤러 지시 파일. 관리자 페이지/시스템 폴더 크롤링 차단. 사이트맵 위치 명시 | robots.txt |
| Claude | **네이버 서치 어드바이저 메타 태그 추가** - 사이트 소유권 확인용 메타 태그 (content="3e4f42759e423f615c3ee556b0505710c6f465bc") | header.php |
| Claude | **견적 시스템 비교 문서 작성** - 대시보드 견적관리 vs 관리자 견적 시스템 프로세스 분석, 상태 변화 다이어그램, 기능 비교표 | claudedocs/quote-system-comparison.md |
| Claude | **다중 서버 배포** - dsp114.com (구 서버, HTTP) + dsp114.co.kr (신 서버, HTTPS) 양 서버에 SEO 파일 배포 완료 | sitemap.xml, robots.txt, duson-planning.php, sitemap.xsl, sitemap.php |

---

## 2026-02-08

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **교정 관리 이미지 뷰어 개선** - "보기" 클릭 → 100% 원본 크기 이미지 오버레이 (스크롤 지원). 여러 이미지 좌우 네비게이션 (화살표 버튼 + 방향키). 이미지 카운터(1/3). 이미지 클릭/ESC/배경 클릭으로 닫기 | dashboard/proofs/index.php |
| Claude | **교정파일 다중 업로드 개선** - 파일 누적 추가, 개별 삭제, 이미지 썸네일 미리보기, 20MB/형식 검증, 중복 방지, 진행률 표시, 업로드 완료 후 페이지 새로고침 없이 행 갱신 | dashboard/proofs/index.php, dashboard/proofs/api.php |
| Claude | **교정파일 업로드 시 파일명 자동 입력** - 이미지 선택 시 원본 파일명이 편집 가능한 입력란에 자동 표시. 수정하면 커스텀 이름으로 저장, 그대로 두면 원본 이름 사용. 확장자 별도 표시, 100자 제한, 동일명 충돌 시 자동 번호 추가 | dashboard/proofs/index.php, dashboard/proofs/api.php |
| Claude | **교정 관리 페이지네이션 표준화** - 이전/다음만 있던 페이지네이션을 « ‹ 1 … 3 [4] 5 … 42 › » 형식으로 변경. 현재 페이지 ±2 범위 표시, 첫/마지막 페이지 바로가기 | dashboard/proofs/index.php |
| Claude | **대시보드 iframe 임베드** - 주문관리(구)/교정관리(구)/견적서(구)/옵션가격을 _blank 대신 대시보드 메인 iframe으로 임베드 | dashboard/embed.php (신규), dashboard/includes/config.php, dashboard/includes/sidebar.php |
| Claude | **관리자 주문 상세 라이트박스** - 파일 링크(Step 2/3/4) 이미지 클릭 → 전체화면 오버레이 (클릭/ESC 닫기). ImgFolder 경로 변환 포함 | admin/mlangprintauto/admin.php, mlangorder_printauto/OrderFormOrderTree.php |
| Claude | **인쇄 레이아웃 A4 관리자/직원용** - 관리자용(130mm) + 절취선 + 직원용 1페이지. 단계적 압축(level 0~2) + JS overflow 감지 → 2페이지 자동 전환 | mlangorder_printauto/OrderFormOrderTree.php |
| Claude | **엑셀 내보내기 품목 JSON 수정** - export_logen_excel74.php에서 Type_1 JSON → spec_* 필드 조합으로 읽기 가능한 텍스트 출력 | shop_admin/export_logen_excel74.php |
| Claude | **로젠택배 날짜 검색 수정** - date 컬럼 datetime 비교 시 종료일 `23:59:59` 누락 → 당일 데이터 조회 불가. 검색+엑셀 내보내기 둘 다 수정 | shop_admin/post_list74.php, shop_admin/export_logen_excel74.php |
| Claude | **견적서 저장 시 status 버그 수정** - "저장" 클릭 시 status가 바로 `sent`로 설정되어 발송 안 했는데 "재발송" 표시. 저장 시 무조건 `draft`, `sent`는 이메일 발송 시에만 변경 | admin/mlangprintauto/quote/includes/AdminQuoteManager.php |

## 2026-02-07

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **견적서 이메일 발송 500 에러 수정** - `use PHPMailer\PHPMailer\Exception;`으로 인해 `catch(Exception)`이 PHP 기본 `\Exception`을 못 잡는 문제. 모든 `catch(Exception)` → `catch(\Throwable)` 변경 | admin/mlangprintauto/quote/api/send_email.php |
| Claude | **가격수정 시 검색 필터 초기화 문제 수정** - products/list.php: `location.reload()` 제거 → DOM 직접 업데이트로 필터 상태 유지. pricing/edit.php: 저장 시 data-original 갱신 + 하이라이트 제거 방식으로 전환 | dashboard/products/list.php, dashboard/pricing/edit.php |
| Claude | **전단지 디자인비 정규화** - 확정 가격표 적용 (A6~A4 단면30K/양면60K, B4·A3 50K/100K, 4절 70K/140K, 국2절 80K/160K). 302행 UPDATE 로컬+프로덕션 완료. 그룹 단위 디자인비 수정 기능 JS 추가 | dashboard/pricing/edit.php |
| Claude | **레거시 카테고리 이름 매핑 INSERT** - `mlangprintauto_transactioncate` 테이블 누락 항목 추가 (no=625, 628~632). 로컬+프로덕션 적용 | DB only |
| Claude | **pricing/edit.php NaN 크래시 수정** - 프로덕션 DB `no=285` money='NaN' → PHP 8.2 `number_format()` fatal error → HTML 렌더링 중단 → JS 미출력. `is_numeric()` 검증 + `ob_flush()` 안전장치 추가. Playwright 검증 완료 (772행 전체 렌더링) | dashboard/pricing/edit.php |
| Claude | **관리자 주문 상세 주문번호 불일치 수정** - 장바구니 그룹핑 `date + no ±50`만 사용 → 마이그레이션 데이터(time=00:00:00)에서 다른 고객 주문 혼합. 그룹핑 쿼리에 `AND name = ?` 조건 추가. `$row` 선택 로직도 `$original_no` 기준으로 수정 | admin/mlangprintauto/admin.php, mlangorder_printauto/OrderFormOrderTree.php |
| Claude | **빈 상품정보 표시 수정** - `Type_1`이 빈 줄바꿈만(`"\n\n\n\n\n"`)인 경우 `!empty()` 통과 → 빈 파싱. `!empty(trim())` 체크로 수정 | mlangorder_printauto/OrderFormOrderTree.php |
| Claude | **OrderView 제목 배경색 디자인 일관성 통일** - 섹션 제목 `#4472C4` → `linear-gradient(135deg, #2c3e50, #34495e)` 다크 그라데이션 (admin.php CSS 디자인 시스템에 맞춤). 버튼 `#4472C4` → `linear-gradient(135deg, #007bff, #0056b3)` + `border-radius: 6px`. 다운로드 링크 → `#007bff`. 전체 8곳 수정 | mlangorder_printauto/OrderFormOrderTree.php |

## 2026-02-04

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **전단지 1.5연 수량 옵션 제거** - 프로덕션 DB에서 비정상 1.5연 데이터 삭제 (백업: mlangprintauto_inserted_backup_20260204), 수량 옵션 12개→11개 정상화 | mlangprintauto/inserted/check_15_quantity.php, mlangprintauto/inserted/delete_15_quantity.php |
| Claude | **추가옵션 가격 자동 업데이트 기능 프로덕션 배포** - 수량 변경 시 코팅/접지/오시 가격 자동 재계산 (1연 80,000원 → 2연 160,000원) | mlangprintauto/inserted/js/additional-options.js, mlangprintauto/inserted/js/leaflet-premium-options.js |
| Claude | **푸터 로고 이미지 순서 수정** - 공정거래위원회(logo-ftc.png 107×35px), 금융결제원(logo-kftc.png 98×35px) 링크와 이미지 정상 매칭 | includes/footer.php |
| Claude | **KB 에스크로 중복 폼 제거** - index.php의 오래된 KB 에스크로 코드 삭제, footer.php의 올바른 코드만 유지 (okbfex.kbstar.com HTTPS), 폼 2개→1개 정리 | index.php |
| Claude | **Playwright 검증 테스트 추가** - 프로덕션 환경 자동 검증 (추가옵션 가격 업데이트, 푸터 로고 크기, KB 에스크로 연결) | tests/verify-additional-options.tier-1.spec.ts, tests/verify-footer-logos.tier-1.spec.ts, tests/verify-kb-escrow-fixed.tier-1.spec.ts |

## 2026-02-02

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **member → users 마이그레이션 6단계 완료** - 전체 활성 PHP 코드가 `users` 테이블을 primary로 사용하도록 전환. `member` 테이블은 backward compatibility용 이중 쓰기로 유지 (DROP 보류). 단계별: 1단계 회원가입/관리자, 2단계 로그인, 3단계 session/ 7개 파일, 4단계 주문 시스템, 5단계 관리자, 6단계 BBS skin 23개 + member/ + lib/ + shop/ + sub/ + mypage/ 등 나머지 전체 | admin/AdminConfig.php, admin/config.php, admin/MlangPoll/admin.php, admin/member/admin.php, admin/member/index.php, session/*.php (7개), member/*.php (6개), bbs/skin/**/*.php (23개), lib/func.php, shop/search_company.php, sub/pw_check.php, mypage/auth_required.php, mlangorder_printauto/*.php (3개) |
| Claude | **레거시 mysql_* → mysqli prepared statement 전환** - shop/search_company.php, sub/pw_check.php 등 구형 mysql_* 함수를 mysqli prepared statement로 전면 재작성 | shop/search_company.php, sub/pw_check.php |
| Claude | **Admin 인증 패턴 통일** - `member WHERE no='1'` → `users WHERE is_admin=1` + bcrypt password_verify 전환 | admin/config.php, admin/AdminConfig.php, admin/MlangPoll/admin.php, bbs/skin/**/*.php |
| Claude | **password_reset.php 버그 수정** - 비밀번호 재설정 시 plaintext 저장 → bcrypt 저장으로 수정 | member/password_reset.php |
| Claude | **회원가입 페이지 제목 변경** - '회원 가입' → '두손기획인쇄 회원가입' | member/form.php |
| Claude | **문서 업데이트** - AGENTS.md 마이그레이션 완료 섹션 추가 (컬럼 매핑, 의도적 member 유지 파일 목록, Admin 패턴) | AGENTS.md, CHANGELOG.md |

## 2026-01-31

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **주문 폼 데이터 흐름 완성** - 물품수령방법(`delivery_method`→`delivery`), 결제방법(`payment_method`→`bank`), 입금자명(`bankname`→`bankname`) DB 저장 구현. INSERT 쿼리 50→54 파라미터 확장 (bind_param 3단계 검증 완료) | ProcessOrder_unified.php |
| Claude | **결제방법 UI 추가** - 계좌이체/카드결제/현금/기타 라디오 버튼, 계좌이체 시 입금자명 필수 입력 + 주문자명 자동채움, 주문자명≠입금자명 시 전화 경고 confirm | OnlineOrder_unified.php |
| Claude | **사업자 주문 상호(회사명) 필드 추가** - 세금계산서 필수 항목 누락 수정, `bizname` DB 컬럼에 `상호명 (사업자번호)` 형식 저장 | OnlineOrder_unified.php, ProcessOrder_unified.php |
| Claude | **사업자 정보 자동 채움** - 로그인 회원의 users 테이블 사업자 정보를 주문 폼에 자동 채움 (7개 필드), 사업장주소 우편번호/주소/상세 자동 파싱 | OnlineOrder_unified.php |
| Claude | **플로팅 채팅 버튼 "상담" 텍스트** + 사이드바 카톡 "상담" 텍스트 제거 | chat/chat.js, chat/chat.css, includes/sidebar.php |
| Claude | **문서 업데이트** - AGENTS.md 주문 데이터 흐름 섹션 추가, CHANGELOG 업데이트 | AGENTS.md, CHANGELOG.md |

## 2026-01-29

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **KG이니시스 실제 결제 시스템 구축** - 테스트→운영 모드 전환, 환경별 자동 URL 감지, 결제 UI 개선 (경고 모달, 전화 안내) | inicis_config.php, inicis_request.php, config.env.php, README_PAYMENT.md |
| Claude | **인증 시스템 일관성 개선** - auth.php에 평문 비밀번호 지원 추가 (자동 bcrypt 업그레이드), 주문 페이지 장바구니 세션 보존 | includes/auth.php (394번 라인), mlangorder_printauto/OnlineOrder_unified.php (1354, 1371번 라인) |
| Claude | **문서 업데이트** - AGENTS.md에 결제/인증 시스템 섹션 추가, Common Pitfalls 업데이트 | AGENTS.md |

## 2026-01-28

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **명함 재질 hover 효과 개선** - 돋보기/어두운배경 제거, "클릭하면 확대되어보입니다" 문구 추가 | explane_namecard.php |

## 2026-01-14

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **[SSOT] 수량/단위 컬럼 분리** - 전단지: 수량 칸에 숫자+(매수), 단위 칸에 '연' | OrderFormOrderTree.php (3개 섹션) |
| Claude | **PriceCalculationService 중앙화** - 모바일 API 12개 마이그레이션 | m/mlangprintauto260104/*/calculate_price_ajax.php |
| Claude | 모바일 CSS 개선 - 셀렉트 박스 44px 높이, 센터 정렬 | css/common-styles.css |
| Claude | 문서 체계 현대화 - 레거시 아카이브, 마스터 명세서 생성 | CLAUDE_DOCS/ |
| Claude | 주문서 출력 정석 형식 (공급가액 → VAT → 합계) | OrderFormPrint.php |
| Claude | Dead Code 삭제 (~150줄) | OrderComplete_universal.php, ProcessOrder_unified.php |
| Claude | 코어 로직 모듈화 | lib/core_print_logic.php |
| Claude | 레거시 파일 격리 (195개, 4.7MB) | legacy_old_backup/ |

## 2026-01-13

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | 전단지 매수 샛밥 방식 적용 (DB 조회 Only) | ProductSpecFormatter.php, SpecDisplayService.php, OrderFormOrderTree.php |
| Claude | QuantityFormatter separator 옵션 추가 | QuantityFormatter.php |
| Claude | 스티커 규격/수량 표시 근본 해결 | ProductSpecFormatter.php, OrderFormOrderTree.php, ProcessOrder_unified.php |

## 2026-01-12

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | 소수점 수량 표시 정리 (10.00권 → 10권) | SpecDisplayService.php |
| Claude | 테이블 컬럼 너비 조정 (규격 44%, 공급가액 13%) | OrderFormOrderTree.php |

## 2026-01-11

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | Playwright E2E 테스트 전체 통과 (44 passed) | tests/ |

## 2026-01-08

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | 견적서 A4 용지 스타일 및 이메일 발송 | quote/ |

---

## 기록 형식

```
| 날짜 | 수정자 | 수정항목 | 관련 파일 |
```

모든 수정은 반드시 이 파일에 기록해야 합니다.
