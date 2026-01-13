# CHANGELOG - 두손기획인쇄 시스템 변경 이력

> **원칙**: 이 파일에 기록되지 않은 로직 수정은 시스템 표준으로 인정하지 않으며, 발견 즉시 롤백 대상입니다.

---

## 2026-01-14

| 수정자 | 수정항목 | 관련 파일 |
|--------|----------|-----------|
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
