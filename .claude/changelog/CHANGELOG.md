# CHANGELOG - 두손기획인쇄 시스템 변경 이력

> **원칙**: 이 파일에 기록되지 않은 로직 수정은 시스템 표준으로 인정하지 않으며, 발견 즉시 롤백 대상입니다.

---

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
