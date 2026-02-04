# CHANGELOG - 두손기획인쇄 시스템 변경 이력

> **원칙**: 이 파일에 기록되지 않은 로직 수정은 시스템 표준으로 인정하지 않으며, 발견 즉시 롤백 대상입니다.

---

## 2026-02-04

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
| Claude | **전단지 1.5연 수량 옵션 제거** - 프로덕션 DB에서 비정상 1.5연 데이터 삭제 (백업: mlangprintauto_inserted_backup_20260204), 수량 옵션 12개→11개 정상화 | mlangprintauto/inserted/check_15_quantity.php, mlangprintauto/inserted/delete_15_quantity.php |
| Claude | **추가옵션 가격 자동 업데이트 기능 프로덕션 배포** - 수량 변경 시 코팅/접지/오시 가격 자동 재계산 (1연 80,000원 → 2연 160,000원) | mlangprintauto/inserted/js/additional-options.js, mlangprintauto/inserted/js/leaflet-premium-options.js |
| Claude | **푸터 로고 이미지 교체** - 공정거래위원회 logo-ftc.png→logo-kftc.png (107×35px), 금융결제원 logo-kftc.png→logo-ftc.png (98×35px) | includes/footer.php |
| Claude | **Playwright 검증 테스트 추가** - 프로덕션 환경 자동 검증 (추가옵션 가격 업데이트, 푸터 로고 크기) | tests/verify-additional-options.tier-1.spec.ts, tests/verify-footer-logos.tier-1.spec.ts |

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
