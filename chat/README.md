# 채팅 시스템 - 두손기획인쇄

고객과 직원이 실시간으로 소통할 수 있는 채팅 시스템입니다.

## 주요 기능

### 채팅 위젯 + AI 야간당번 (2026-02-27)
- **채팅 위젯 (상담연결)** -- 업무시간(09:00~18:30) 직원 실시간 채팅
- **AI 야간당번 (긴급대응)** -- 야간시간(18:30~09:00) AI 자동응답 (가격조회/지식Q&A)
- 시간대별 배타적 표시 (footer.php `toggleWidgets()`)
- 두 시스템은 완전히 독립 (코드, API, 백엔드 모두 별개)

### 고객용 기능 (채팅 위젯)
- 실시간 메시지 전송/수신 (2초마다 자동 업데이트)
- 이미지 첨부 및 전송 (최대 10MB, 설정 가능)
- 읽지 않은 메시지 알림 배지
- 채팅창 열기/닫기 (상태 localStorage 유지)
- 대화 내용 텍스트 파일로 저장
- 반응형 디자인 (PC/모바일 지원)

### 직원용 기능 (채팅 위젯)
- 3명의 직원이 동시에 같은 채팅방 참여
- 모든 고객 채팅 확인 및 응답
- 이미지 전송
- 실시간 메시지 수신

### AI 야간당번 기능
- 9개 인쇄 제품 빠른선택 버튼
- 클릭형 선택지 (재질, 사이즈, 수량 등)
- 스티커 사이즈 직접입력 위젯
- AI 가격 계산 (ChatbotService + Gemini API)
- 지식베이스 Q&A
- 드래그로 위치 이동

## 아키텍처

**두 시스템은 완전히 독립적으로 운영됩니다.**

```
footer.php toggleWidgets() -- 시간대별 배타적 전환
├── 09:00~18:30 (업무시간): 채팅 위젯만 표시
└── 18:30~09:00 (야간시간): 야간당번만 표시

=== 채팅 위젯 (상담연결) ===

includes/chat_widget.php
  └── new ChatWidget({ mode: 'chat' })
        └── chat/chat.js (ChatWidget 클래스)
              └── chat/api.php (채팅방 시스템)
                    └── DB: chatrooms / chatmessages

=== 야간당번 (긴급대응) ===

includes/ai_chatbot_widget.php (자체 내장 JS/CSS, 344줄)
  └── api/ai_chat.php (POST)
        └── v2/src/Services/AI/ChatbotService.php
              ├── DB 가격표 조회 (8개 제품)
              ├── 스티커 수학공식 계산
              └── ChatbotKnowledge.php (Gemini API)
```

### 채팅 위젯 vs AI 야간당번

| 항목 | 채팅 위젯 (상담연결) | AI 야간당번 (긴급대응) |
|------|----------------|-----------------|
| 파일 | `chat_widget.php` -> `chat.js` | `ai_chatbot_widget.php` (자체 내장) |
| API | `chat/api.php` (채팅방 CRUD) | `api/ai_chat.php` (ChatbotService 직접) |
| 백엔드 | DB chatrooms/chatmessages | ChatbotService + Gemini API |
| 세션 | 채팅방 room_id | `$_SESSION['chatbot']` (제품 step 상태) |
| 응답 | 직원이 응답 | AI 즉시 응답 (클릭형 선택지, 가격 계산) |
| 버튼 | infolady 이미지 70x70px | "야간당번" 텍스트, 보라 그라디언트 79x79px |
| 시간대 | 09:00~18:30 | 18:30~09:00 |
| 이름 입력 | 모달 표시 (상호명/성함) | 없음 (즉시 사용) |
| 위치 | CSS 고정 `bottom:24px; right:24px` | 인라인 `bottom:20px; right:80px` |

## 시간대 전환 로직

`includes/footer.php`에서 JS로 제어:

```javascript
// footer.php (line 818~841)
function isBusinessHours() {
    var h = now.getHours(), m = now.getMinutes();
    if (h < 9) return false;
    if (h > 18) return false;
    if (h === 18 && m >= 30) return false;
    return true;
}
function toggleWidgets() {
    var biz = isBusinessHours();
    var staff = document.querySelector('.chat-widget');   // chat.js가 동적 생성
    var ai = document.getElementById('ai-chatbot-widget'); // PHP가 직접 렌더
    // .chat-widget은 chat.js가 동적 생성하므로 retry 로직 포함 (최대 2초)
    if (!staff && retryCount < 20) { retryCount++; setTimeout(toggleWidgets, 100); return; }
    if (staff) staff.style.display = biz ? '' : 'none';
    if (ai) ai.style.display = biz ? 'none' : 'block';
}
// DOMContentLoaded + setInterval(60초)
```

**주의**: `.chat-widget`은 `chat.js`가 동적으로 DOM을 생성하므로, `toggleWidgets()`에 retry 로직이 있음 (100ms x 최대 20회). 이 retry가 없으면 야간에 채팅 위젯이 숨겨지지 않는 race condition 발생.

## 파일 구조

```
chat/
├── config.php          # 데이터베이스 연결 및 설정
├── api.php             # 채팅 API (메시지 송수신, 이미지 업로드, 설정 관리)
├── chat.css            # 채팅 위젯 스타일
├── chat.js             # ChatWidget 클래스 (mode='chat' 전용)
├── admin.php           # 직원용 채팅 관리 페이지
├── demo.php            # 데모 페이지
├── setup_staff.sql     # 직원 정보 테이블
└── README.md           # 이 파일

includes/
├── chat_widget.php         # 채팅 위젯 초기화 (mode='chat'만)
├── ai_chatbot_widget.php   # 야간당번 위젯 (자체 내장 JS/CSS, 독립 시스템)
└── footer.php              # toggleWidgets() 시간대 전환 로직

api/
└── ai_chat.php             # 야간당번 API (ChatbotService 직접 호출)

v2/src/Services/AI/
├── ChatbotService.php      # 가격조회 엔진 (685줄)
└── ChatbotKnowledge.php    # Gemini 지식베이스 (252줄)

dashboard/chat/
├── index.php               # 채팅 관리 목록
└── settings.php            # 채팅 설정 (위치 피커, 시간대 등)
```

## 데이터베이스 테이블

### `chatrooms` - 채팅방 정보
- `id`: 채팅방 ID
- `roomname`: 채팅방 이름
- `roomtype`: 채팅방 타입 (admin_user, user_user, group)
- `createdby`: 생성자 ID
- `createdat`: 생성일시
- `updatedat`: 마지막 업데이트 일시
- `isactive`: 활성 상태
- `ai_active`: AI 모드 채팅방 여부 (0=일반, 1=AI) -- 레거시, 현재 야간당번은 채팅방 미사용

### `chatparticipants` - 채팅방 참여자
- `id`: 참여자 ID
- `roomid`: 채팅방 ID
- `userid`: 사용자 ID
- `username`: 사용자 이름
- `isadmin`: 관리자 여부
- `lastreadat`: 마지막 읽은 시간
- `joinedat`: 참여 일시

### `chatmessages` - 채팅 메시지
- `id`: 메시지 ID
- `roomid`: 채팅방 ID
- `senderid`: 발신자 ID
- `sendername`: 발신자 이름
- `messagetype`: 메시지 타입 (text, image, file)
- `message`: 메시지 내용
- `filepath`: 파일 경로
- `filename`: 원본 파일명
- `filesize`: 파일 크기
- `isread`: 읽음 여부
- `createdat`: 생성일시

### `chatstaff` - 직원 정보
- `id`: 직원 ID
- `staffid`: 직원 고유 ID
- `staffname`: 직원 이름
- `email`: 이메일
- `isonline`: 온라인 상태
- `lastseen`: 마지막 접속 시간

### `chat_config` - 채팅 설정 (대시보드 관리)

| config_key | 기본값 | 설명 |
|-----------|--------|------|
| `widget_enabled` | `1` | 채팅 위젯 표시 여부 |
| `widget_hour_start` | `09:00` | 채팅 위젯 시작 시간 |
| `widget_hour_end` | `18:30` | 채팅 위젯 종료 시간 |
| `widget_button_label` | `상담연결` | 채팅 버튼 라벨 |
| `widget_welcome_msg` | `안녕하세요!...` | 이름 입력 모달 메시지 |
| `widget_poll_interval` | `2000` | 메시지 폴링 간격 (ms) |
| `widget_pos_x` | `92` | 채팅 위젯 X 위치 (%) |
| `widget_pos_y` | `85` | 채팅 위젯 Y 위치 (%) |
| `ai_enabled` | `1` | AI 야간당번 활성화 |
| `ai_wait_seconds` | `60` | AI 진입 대기 시간 (초) |
| `ai_hour_start` | `18:30` | 야간당번 시작 시간 |
| `ai_hour_end` | `09:00` | 야간당번 종료 시간 |
| `ai_display_name` | `긴급대응` | 야간당번 표시 이름 |
| `ai_greeting_msg` | `안녕하세요...` | 야간당번 인사 메시지 |
| `ai_farewell_msg` | `담당자가...` | 야간당번 퇴장 메시지 |
| `ai_pos_x` | `92` | 야간당번 X 위치 (%) |
| `ai_pos_y` | `60` | 야간당번 Y 위치 (%) |
| `ai_button_label` | `AI 상담` | 야간당번 버튼 라벨 |
| `ai_button_color` | `#667eea` | 야간당번 버튼 색상 |
| `offline_message` | `현재 업무시간...` | 업무외 안내 메시지 |
| `notice_message` | (빈 값) | 채팅창 상단 공지 |
| `upload_max_mb` | `10` | 파일 업로드 최대 MB |

## API 엔드포인트

### 채팅 위젯 API (chat/api.php)

**GET:**
- `?action=get_or_create_room` -- 채팅방 가져오기 또는 생성
- `?action=get_messages&room_id={id}&last_id={id}` -- 메시지 조회
- `?action=get_unread_count&room_id={id}` -- 읽지 않은 메시지 수
- `?action=get_config` -- 전체 설정 조회 (JSON)
- `?action=export_chat&room_id={id}` -- 대화 내용 내보내기

**POST:**
- `action=send_message` -- 메시지 전송
- `action=upload_image` -- 이미지 업로드 (최대 10MB)
- `action=mark_as_read` -- 읽음 처리
- `action=save_config` -- 설정 저장 (대시보드용)

### 야간당번 API (api/ai_chat.php)

**POST:**
- `message` -- 사용자 메시지 또는 선택값
- 응답: JSON `{ response, options[], sizeInput, product_type, ... }`
- 내부적으로 `ChatbotService::processMessage()` 호출
- 세션 기반 상태 관리 (`$_SESSION['chatbot']`)

## 대시보드 설정 (/dashboard/chat/settings.php)

### 위치 피커
- 16:9 비율 프리뷰 영역에 W(채팅)와 A(야간당번) 점 표시
- 점 클릭 -> 드래그 이동 또는 빈 영역 클릭으로 이동
- X/Y % 좌표 실시간 표시 + "기본값 복원" 버튼

### 프로덕션 자동 마이그레이션
settings.php 접속 시 자동으로:
1. `chat_config`에 6개 신규 키 INSERT (widget_pos_x/y, ai_pos_x/y, ai_button_label, ai_button_color)
2. `chatrooms` 테이블에 `ai_active` 컬럼 ADD (없을 경우)

## 동작 원리

### 1. 위젯 초기화
```php
// includes/chat_widget.php -- 채팅 위젯만
window.chatWidget = new ChatWidget({ mode: 'chat' });

// includes/ai_chatbot_widget.php -- 야간당번 (자체 내장, ChatWidget 미사용)
// PHP가 직접 HTML 렌더, 자체 JS 함수 (aiChatToggle, aiChatSend 등)
```

### 2. 시간대별 전환
- `footer.php`의 `toggleWidgets()`가 DOMContentLoaded + 60초 간격으로 실행
- 업무시간 -> `.chat-widget` 표시, `#ai-chatbot-widget` 숨김
- 야간시간 -> `.chat-widget` 숨김, `#ai-chatbot-widget` 표시
- 두 위젯은 동시에 표시되지 않음 (배타적)

### 3. 위치
- 채팅 위젯: CSS 고정 (`bottom:24px; right:24px`)
- 야간당번: 인라인 스타일 (`bottom:20px; right:80px`) + 드래그 이동 지원

## 보안 고려사항

- 파일 타입 검증 (이미지만 허용)
- 파일 크기 제한 (설정 가능, 기본 10MB)
- SQL Injection 방지 (Prepared Statement 사용)
- XSS 방지 (HTML Escape)
- 설정 저장 시 allowedKeys 화이트리스트 검증

## 문제 해결

### 위젯이 안 보임
- 시간대 확인 (업무시간=채팅, 야간=야간당번)
- `chat_config` 테이블 존재 확인
- 브라우저 콘솔(F12)에서 JS 에러 확인
- footer.php의 `toggleWidgets()` 실행 여부 확인

### 야간에 채팅 위젯이 안 숨겨짐
- footer.php `toggleWidgets()` retry 로직 확인
- `.chat-widget`은 chat.js가 동적 생성하므로 타이밍 이슈 가능
- retry가 최대 20회(2초) 대기 후 적용

### 채팅방 생성 실패
- `chatrooms` 테이블에 `ai_active` 컬럼 존재 확인
- 대시보드 설정 페이지 한 번 접속하면 자동 마이그레이션

### 야간당번 응답 없음
- `api/ai_chat.php` 파일 존재 확인
- `v2/src/Services/AI/ChatbotService.php` 로드 확인
- PHP 세션 정상 작동 확인
- Gemini API 키 설정 확인

## 향후 개선 사항

- [ ] WebSocket을 이용한 진정한 실시간 통신
- [ ] 파일 첨부 기능 (PDF, 문서 등)
- [ ] 채팅방별 알림 소리
- [ ] 직원 온라인/오프라인 상태 표시
- [ ] 채팅 기록 검색 기능
- [ ] 채팅 통계 대시보드

---

**버전**: 3.0.0
**최종 수정일**: 2026-02-27
