# 데이터베이스 (Database)

## 상세 스키마

전체 DB 스키마 문서: `CLAUDE_DOCS/DB_SCHEMA.md`

## 핵심 테이블 요약

| 테이블 | 용도 |
|--------|------|
| `mlangorder_printauto` | 주문 데이터 (주문번호, 결제, 배송 등) |
| `shop_temp` | 장바구니 (세션 기반, 7일 자동 정리) |
| `users` | 회원 (bcrypt + plaintext 자동 업그레이드) |
| `member` | 레거시 회원 (마이그레이션 완료, 읽기 전용) |
| `admin_users` | 관리자 계정 |
| `shipping_rates` | 택배 요금표 (logen_weight, logen_16) |
| `payment_inicis` | 이니시스 결제 로그 |
| `chatrooms` | 실시간 채팅방 (ai_active 플래그 포함) |
| `remember_tokens` | 로그인 유지 토큰 (30일) |
| `mlangprintauto_*` | 제품별 가격표 (8개 제품, 스티커 제외) |
| `shop_d1~d4` | 스티커 재질별 요율 테이블 |

## 접속 정보

| 환경 | Host | User | Pass |
|------|------|------|------|
| 로컬 | localhost | dsp1830 | ds701018 |
| 프로덕션 | dsp114.co.kr | dsp1830 | t3zn?5R56 |

## DB 연결 규칙

- 연결 변수: `$db` (레거시: `$conn = $db`)
- Character set: `utf8mb4`
- 환경 자동 감지: `config.env.php`
- 모든 쿼리는 **prepared statement** 사용 필수
