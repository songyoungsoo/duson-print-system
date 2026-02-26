# API 레퍼런스

## 상세 API 문서

전체 API 스펙: `CLAUDE_DOCS/API_SPEC.md`

## 주요 API 엔드포인트

| 엔드포인트 | Method | 용도 |
|-----------|--------|------|
| `/api/ai_chat.php` | POST | AI 챗봇 (야간당번) |
| `/includes/shipping_api.php` | POST | 배송 추정/요금/저장 |
| `/chat/api.php` | POST | 실시간 채팅 + 긴급대응 AI |
| `/payment/inicis_request.php` | POST | 이니시스 결제 요청 |
| `/payment/inicis_return.php` | POST | 이니시스 결제 결과 처리 |
| `/dashboard/proofs/api.php` | POST | 교정 파일 관리 |
| `/mlangprintauto/quote/api.php` | POST | 견적서 CRUD |

## AJAX 가격 조회 (제품별)

| 제품 | 파일 | 방식 |
|------|------|------|
| 스티커 | `sticker_new/calculate_price_ajax.php` | **수학 공식** (SSOT) |
| 기타 8개 | `mlangprintauto/{folder}/` | DB 가격표 lookup |

## 공통 규칙

- 모든 API는 `$_SERVER['HTTP_REFERER']` Same-origin 체크
- JSON 응답: `Content-Type: application/json`
- 에러 응답: `{"success": false, "message": "에러 내용"}`
