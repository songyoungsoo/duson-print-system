HANDOFF CONTEXT
===============

USER REQUESTS (AS-IS)
---------------------
- "견적서 때문에 문제가 생겨서 품목별계산과는 별도로 독립시켜놓았는데 맞는지 살펴봐"
- "장바구니는 shop_temp 를 사용하고 견적서는 중간저장을 어디를 사용하고있나요?"
- "품목옵션에서는 무언가 수정도 하고 그랬던거 같은데 견적옵션에는 수정 할것이 없나요?"
- "품목옵션은 공용으로 사용해도 괜찮은지 파악하고 문제가 없으면 굳이 2군데를 고칠 필요가 없고 번거로운것 같아"
- "프로덕션 FTP 배포 + 나머지 5개 위젯 premium_options 전송"
- "E2E 테스트를 진행해줘 백그라운드로 실행해도 좋으니 결과값이 오류가 없도록 실행해줘"

GOAL
----
프로덕션 DB에 premium_options 컬럼 추가 확인 + littleprint 위젯 loadQuantities() 버그 수정

WORK COMPLETED
--------------
- 견적서 시스템이 품목별 계산기와 올바르게 독립되어 있는지 전체 아키텍처 검증 완료
- 고객 경로 필드명 불일치 수정: add_to_quotation_temp.php에 price/vat_price/premium_options_data fallback 추가 (commit 406d382e)
- 관리자 견적 위젯 6개(namecard, merchandisebond, cadarok, envelope, inserted, littleprint)에 premium_options JSON 전송 코드 추가
- 백엔드 파이프라인 수정: add_calculator_item.php에 premium_options 필드 추가, AdminQuoteManager.php INSERT에 premium_options 컬럼+bind_param 추가 (36 params)
- 옵션 테이블 통합 분석 완료 — 결론: 통합 불가, 현행 유지가 정석 (additional_options_config vs premium_options+premium_option_variants 구조적 차이)
- AGENTS.md에 Common Pitfalls #22-#24 추가, docs/operations/quote-system.md 아키텍처 섹션 신규 추가
- 프로덕션 FTP 배포 완료 — 9개 파일 전부 IP(175.119.156.249) 직접 접속으로 업로드 성공
- E2E 테스트 작성 및 실행 — 8 passed, 1 skipped (littleprint widget bug)

CURRENT STATE
-------------
- Branch: domain-change-dsp114com, remote와 동기화 완료 (HEAD: e917fc65)
- Working tree: clean (uncommitted 변경 없음)
- 로컬 DB: admin_quotation_temp에 premium_options TEXT 컬럼 추가됨 (E2E 테스트 에이전트가 ALTER TABLE 실행)
- 프로덕션 DB: premium_options 컬럼 존재 여부 미확인 — 확인 필요!
- E2E 테스트: tests/admin-quote/premium-options-e2e.group-e.spec.ts (미커밋, 에이전트가 수정한 버전)
- Admin 실제 비밀번호: ds701018 (admin123 아님)

PENDING TASKS
-------------
- 프로덕션 DB에 premium_options 컬럼 존재 확인 (없으면 ALTER TABLE admin_quotation_temp ADD premium_options TEXT 실행 필요)
- littleprint 위젯 loadQuantities() 버그 수정 — filter_Section과 filter_TreeSelect 파라미터가 뒤바뀜
- E2E 테스트 파일 커밋 (현재 미커밋 상태)
- 고객 경로 나머지 5개 품목의 addToQuotation()에 premium_options_data 전송 추가 (장기 과제)
- 도메인 설정 작업 (사용자 지시: "내일 다시 작업")

KEY FILES
---------
- admin/mlangprintauto/quote/widgets/namecard.php — 명함 위젯 (premium_options 패턴 기준)
- admin/mlangprintauto/quote/widgets/littleprint.php — 포스터 위젯 (loadQuantities 버그 있음)
- admin/mlangprintauto/quote/api/add_calculator_item.php — 계산기 품목 추가 API
- admin/mlangprintauto/quote/includes/AdminQuoteManager.php — DB INSERT 매니저 (36 bind_param)
- mlangprintauto/quote/add_to_quotation_temp.php — 고객 경로 견적 저장
- admin/mlangprintauto/quote/create.php — 관리자 견적 메인 페이지 (postMessage handler)
- tests/admin-quote/premium-options-e2e.group-e.spec.ts — E2E 테스트 파일
- docs/operations/quote-system.md — 견적서 시스템 아키텍처 문서

IMPORTANT DECISIONS
-------------------
- 옵션 테이블 통합 불가 판단: additional_options_config(flat, 관리자용)과 premium_options+premium_option_variants(normalized, 고객용)은 구조적으로 다름 — 현행 이중 시스템 유지
- 관리자 견적서는 dumb storage 패턴: 프론트가 계산한 값을 그대로 DB에 저장 (백엔드가 재계산하지 않음)
- FTP 배포 시 DNS 불안정하면 IP(175.119.156.249) 직접 접속 사용
- 견적서 프론트 필드명(price/vat_price/premium_options_data)과 백엔드 필드명(calculated_price/calculated_vat_price/premium_options)이 다름 — fallback으로 양쪽 모두 처리

EXPLICIT CONSTRAINTS
--------------------
- "도메인은 설정이 잘못되었고 내일 다시 작업한다니까 놓아두고" — 도메인 작업 하지 않음
- "고객견적서는 받아간 것으로 끝이고 관리자이메일로 오게만들어서 누가 견적을 어떤것을 받아갔는지만 확인 용이고 실제 운영에는 관련이 없다" — 고객 견적서는 참조용

CONTEXT FOR CONTINUATION
------------------------
- 프로덕션 DB 접속: mysql -h 175.119.156.249 -u dsp1830 -p (비번: ds701018)
- 프로덕션 FTP: ftp://175.119.156.249 user=dsp1830 pass=cH*j@yzj093BeTtc (DNS 불안정 시 IP 사용)
- admin_quotation_temp 테이블에 premium_options 컬럼이 프로덕션에 없으면 INSERT가 실패함 — 반드시 확인
- littleprint 위젯의 loadQuantities() 함수에서 filter_Section에 PN_type 값이, filter_TreeSelect에 Section 값이 들어가는 파라미터 스왑 버그 있음
- 위젯 커버리지: namecard, merchandisebond, cadarok, envelope, inserted, littleprint 6개 완료 / msticker, ncrflambeau, sticker 3개는 옵션 없어서 수정 불필요
