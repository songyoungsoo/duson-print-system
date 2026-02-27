# 📱 채팅 시스템 - 두손기획인쇄

고객과 직원이 실시간으로 소통할 수 있는 채팅 시스템입니다.

## ✨ 주요 기능

### 🎯 채팅 위젯 + AI 야간당번 (2026-02-27)
- ✅ **채팅 위젯 (상담연결)** — 업무시간(09:00~18:30) 직원 실시간 채팅
- ✅ **AI 야간당번 (긴급대응)** — 야간시간(18:30~09:00) AI 자동응답
- ✅ 각각 독립 버튼으로 화면 표시
- ✅ 대시보드에서 X/Y % 좌표로 자유 위치 설정 (비주얼 피커)
- ✅ AI 버튼 라벨/색상 커스터마이징

### 🎯 고객용 기능
- ✅ 실시간 메시지 전송/수신 (2초마다 자동 업데이트)
- ✅ 이미지 첨부 및 전송 (최대 10MB, 설정 가능)
- ✅ 읽지 않은 메시지 알림 배지
- ✅ 채팅창 열기/닫기 (상태 localStorage 유지)
- ✅ 대화 내용 텍스트 파일로 저장
- ✅ 반응형 디자인 (PC/모바일 지원)

### 👥 직원용 기능
- ✅ 3명의 직원이 동시에 같은 채팅방 참여
- ✅ 모든 고객 채팅 확인 및 응답
- ✅ 이미지 전송
- ✅ 실시간 메시지 수신

## 🏗️ 아키텍처

```
┌──────────────────────────────────────────────────────────┐
│  includes/chat_widget.php                                 │
│  ├── new ChatWidget({ mode: 'chat' })  → 채팅 위젯       │
│  └── new ChatWidget({ mode: 'ai' })    → AI 야간당번      │
└──────────────────────────────────────────────────────────┘
         │                        │
         ▼                        ▼
┌──────────────────┐   ┌──────────────────┐
│  chat-toggle-btn │   │  ai-toggle-btn   │
│  채팅 위젯        │   │  AI 야간당번       │
│  infolady 이미지  │   │  🤖 블루그라디언트  │
│  09:00 ~ 18:30   │   │  18:30 ~ 09:00   │
│  이름 모달 표시   │   │  이름 모달 건너뛰기 │
│  chat_room_id    │   │  ai_room_id      │
└──────────────────┘   └──────────────────┘
         │                        │
         ▼                        ▼
┌──────────────────────────────────────┐
│  chat/api.php                        │
│  ├── ?action=get_or_create_room      │
│  │   └── ai_mode=1 → ai_active=1    │
│  ├── ?action=send_message            │
│  ├── ?action=get_messages            │
│  └── ?action=save_config (설정 저장)  │
└──────────────────────────────────────┘
```

### ChatWidget 클래스 (chat.js)

```javascript
class ChatWidget {
    constructor(options) {
        this.mode = options.mode || 'chat';  // 'chat' | 'ai'
        this.pfx = this.mode === 'ai' ? 'ai' : 'chat';  // DOM ID prefix
    }
    // 모든 DOM ID → ${pfx}-toggle-btn, ${pfx}-window, ${pfx}-messages ...
    // localStorage → ${pfx}_room_id, ${pfx}_is_open ...
    // mode='ai' → skipName, ?ai_mode=1, ai_hour 시간대, 🤖 버튼
}
```

### 채팅 위젯 vs AI 야간당번

| 항목 | 채팅 위젯 (상담연결) | AI 야간당번 (긴급대응) |
|------|----------------|-----------------|
| 버튼 | infolady 이미지 70×70px | 🤖 이모지 60×60px, 블루 그라디언트 |
| 시간대 | `widget_hour_start` ~ `widget_hour_end` | `ai_hour_start` ~ `ai_hour_end` |
| 이름 모달 | 표시 (상호명/성함 입력) | 건너뛰기 (skipName 자동) |
| 채팅방 생성 | `ai_active=0` | `ai_active=1`, `?ai_mode=1` |
| 헤더 색상 | 보라색 그라디언트 | 블루 그라디언트 (#667eea) |
| localStorage | `chat_room_id`, `chat_is_open` | `ai_room_id`, `ai_is_open` |

## 📁 파일 구조

```
chat/
├── config.php          # 데이터베이스 연결 및 설정
├── api.php             # 채팅 API (메시지 송수신, 이미지 업로드, 설정 관리)
├── chat.css            # 위젯 스타일 (채팅 위젯 + AI 야간당번)
├── chat.js             # ChatWidget 클래스 (채팅 위젯 / AI 야간당번 모드)
├── admin.php           # 직원용 채팅 관리 페이지
├── chat.js.bak         # 리팩토링 전 백업
├── demo.php            # 데모 페이지
├── setup_staff.sql     # 직원 정보 테이블
└── README.md           # 이 파일

includes/
├── chat_widget.php     # 위젯 초기화 (채팅 위젯 + AI 야간당번)
└── ai_chatbot_widget.php  # (레거시, 미사용 — 어디서도 include 안 됨)

dashboard/chat/
├── index.php           # 채팅 관리 목록
└── settings.php        # 채팅 설정 (위치 피커, AI 버튼, 시간대 등)
```

## 🗄️ 데이터베이스 테이블

### `chatrooms` - 채팅방 정보
- `id`: 채팅방 ID
- `roomname`: 채팅방 이름
- `roomtype`: 채팅방 타입 (admin_user, user_user, group)
- `createdby`: 생성자 ID
- `createdat`: 생성일시
- `updatedat`: 마지막 업데이트 일시
- `isactive`: 활성 상태
- `ai_active`: AI 모드 채팅방 여부 (0=일반, 1=AI)

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
| `ai_enabled` | `1` | AI 자동응답 활성화 |
| `ai_wait_seconds` | `60` | AI 진입 대기 시간 (초) |
| `ai_hour_start` | `18:30` | AI 운영 시작 시간 |
| `ai_hour_end` | `09:00` | AI 운영 종료 시간 |
| `ai_display_name` | `긴급대응` | AI 표시 이름 |
| `ai_greeting_msg` | `안녕하세요...` | AI 인사 메시지 |
| `ai_farewell_msg` | `담당자가...` | AI 퇴장 메시지 |
| `ai_pos_x` | `92` | AI 위젯 X 위치 (%) |
| `ai_pos_y` | `60` | AI 위젯 Y 위치 (%) |
| `ai_button_label` | `AI 상담` | AI 버튼 라벨 |
| `ai_button_color` | `#667eea` | AI 버튼 그라디언트 색상 |
| `offline_message` | `현재 업무시간...` | 업무외 안내 메시지 |
| `notice_message` | (빈 값) | 채팅창 상단 공지 |
| `upload_max_mb` | `10` | 파일 업로드 최대 MB |

## 🔧 API 엔드포인트

### GET /chat/api.php

- `?action=get_or_create_room` — 채팅방 가져오기 또는 생성
  - `&ai_mode=1` — AI 모드 채팅방 생성 (ai_active=1)
- `?action=get_messages&room_id={id}&last_id={id}` — 메시지 조회
- `?action=get_unread_count&room_id={id}` — 읽지 않은 메시지 수
- `?action=get_config` — 전체 설정 조회 (JSON)
- `?action=export_chat&room_id={id}` — 대화 내용 내보내기

### POST /chat/api.php

- `action=send_message` — 메시지 전송
- `action=upload_image` — 이미지 업로드 (최대 10MB)
- `action=mark_as_read` — 읽음 처리
- `action=save_config` — 설정 저장 (대시보드용)

## ⚙️ 대시보드 설정 (/dashboard/chat/settings.php)

### 위치 피커
- 16:9 비율 프리뷰 영역에 W(채팅)와 A(AI) 점 표시
- 점 클릭 → 드래그 이동 또는 빈 영역 클릭으로 이동
- X/Y % 좌표 실시간 표시 + "기본값 복원" 버튼

### 프로덕션 자동 마이그레이션
settings.php 접속 시 자동으로:
1. `chat_config`에 6개 신규 키 INSERT (widget_pos_x/y, ai_pos_x/y, ai_button_label, ai_button_color)
2. `chatrooms` 테이블에 `ai_active` 컬럼 ADD (없을 경우)

## 📊 동작 원리

### 1. 위젯 초기화
```php
// includes/chat_widget.php
window.chatWidget = new ChatWidget({ mode: 'chat' });
window.aiChatWidget = new ChatWidget({ mode: 'ai' });
```

### 2. 시간대별 표시
- 각 위젯이 `loadConfig()`로 설정 로드
- 현재 시간이 해당 위젯 시간대 범위 내 → 버튼 표시
- 범위 외 → 버튼 숨김 (클릭 시 offline_message alert)

### 3. 위치 적용
- `applyPosition()`에서 X/Y % 좌표를 CSS로 변환
- `left: X%; top: Y%; transform: translate(-50%, -50%)`

### 4. 채팅방 분리
- chat 모드: `localStorage.chat_room_id` → 일반 채팅방
- ai 모드: `localStorage.ai_room_id` → AI 채팅방 (`ai_active=1`)

## 🔒 보안 고려사항

- ✅ 파일 타입 검증 (이미지만 허용)
- ✅ 파일 크기 제한 (설정 가능, 기본 10MB)
- ✅ SQL Injection 방지 (Prepared Statement 사용)
- ✅ XSS 방지 (HTML Escape)
- ✅ 설정 저장 시 allowedKeys 화이트리스트 검증

## 🐛 문제 해결

### 위젯이 안 보임
- 시간대 설정 확인 (대시보드 → 채팅 설정)
- `chat_config` 테이블 존재 확인
- 브라우저 콘솔(F12)에서 JS 에러 확인

### 채팅방 생성 실패
- `chatrooms` 테이블에 `ai_active` 컬럼 존재 확인
- 대시보드 설정 페이지 한 번 접속하면 자동 마이그레이션

### 위치가 안 바뀜
- 대시보드에서 저장 후 사이트 새로고침 (캐시: `?v=20260227`)
- `chat_config`에 `widget_pos_x/y`, `ai_pos_x/y` 키 확인

## 📝 향후 개선 사항

- [ ] WebSocket을 이용한 진정한 실시간 통신
- [ ] 파일 첨부 기능 (PDF, 문서 등)
- [ ] 채팅방별 알림 소리
- [ ] 직원 온라인/오프라인 상태 표시
- [ ] 채팅 기록 검색 기능
- [ ] 채팅 통계 대시보드
- [ ] AI 위젯에서 v2 ChatbotService 연동 (자동 가격 조회)

## 📞 문의

두손기획인쇄
- 전화: 02-2632-1830
- 팩스: 02-2632-1829
- 이메일: dsp1830@naver.com

---

**버전**: 2.0.0
**최종 수정일**: 2026-02-27
