## 💬 채팅 시스템 — 채팅 위젯 + AI 야간당번

### 시스템 개요

두 가지 독립적인 고객 소통 채널을 운영:

1. **채팅 위젯 (상담연결)** — 업무시간 중 직원과 실시간 채팅
2. **AI 야간당번 (긴급대응)** — 업무시간 외 AI 자동응답

각각의 화면 위치를 대시보드 비주얼 피커(X/Y % 좌표)로 자유 배치 가능.

> **이전 시스템**: `ai_chatbot_widget.php` (레거시, 미사용) → @./ai-chatbot.md

| 항목 | 값 |
|------|-----|
| **위젯 초기화** | `/includes/chat_widget.php` |
| **JS 엔진** | `/chat/chat.js` — `ChatWidget` 클래스 |
| **CSS** | `/chat/chat.css` |
| **API** | `/chat/api.php` |
| **설정 관리** | `/dashboard/chat/settings.php` |
| **설정 DB** | `chat_config` 테이블 (22개 키) |

---

### 위젯 구조

```
includes/chat_widget.php
├── new ChatWidget({ mode: 'chat' })  → window.chatWidget     (상담연결)
└── new ChatWidget({ mode: 'ai' })    → window.aiChatWidget   (AI 야간당번)
```

| 항목 | 상담연결 (chat) | AI 야간당번 (ai) |
|------|----------------|-----------------|
| 버튼 | infolady 이미지 (70×70px) | 🤖 이모지 + 블루 그라디언트 (60×60px) |
| DOM prefix | `chat-` | `ai-` |
| 시간대 | `widget_hour_start` ~ `widget_hour_end` (기본 09:00~18:30) | `ai_hour_start` ~ `ai_hour_end` (기본 18:30~09:00) |
| 이름 모달 | 표시 (상호명/성함 입력) | 건너뛰기 (자동 skipName) |
| 채팅방 | `ai_active=0` | `ai_active=1` (`?ai_mode=1` 파라미터) |
| 헤더 | 보라색 그라디언트 | 블루 그라디언트 (#667eea) |
| localStorage | `chat_room_id`, `chat_is_open` | `ai_room_id`, `ai_is_open` |
| 위치 설정 | `widget_pos_x`, `widget_pos_y` | `ai_pos_x`, `ai_pos_y` |

---

### 위치 설정 (대시보드)

`/dashboard/chat/settings.php` 상단의 비주얼 위치 피커:

- 16:9 비율 프리뷰 박스에 **W**(보라, 채팅위젯)와 **A**(파랑, AI챗봇) 점 표시
- 점 클릭 후 드래그, 또는 빈 영역 클릭으로 선택된 점 이동
- X/Y % 좌표 실시간 표시 + "기본값 복원" 버튼
- CSS 적용: `left: X%; top: Y%; transform: translate(-50%, -50%)`

---

### 설정 키 (chat_config 테이블)

#### 채팅 위젯 (group: widget)

| 키 | 기본값 | 설명 |
|----|--------|------|
| `widget_enabled` | `1` | 위젯 표시 여부 |
| `widget_hour_start` | `09:00` | 표시 시작 시간 |
| `widget_hour_end` | `18:30` | 표시 종료 시간 |
| `widget_button_label` | `상담연결` | 버튼 라벨 |
| `widget_welcome_msg` | `안녕하세요!...` | 이름 모달 메시지 |
| `widget_poll_interval` | `2000` | 폴링 간격 (ms) |
| `widget_pos_x` | `92` | X 위치 (%) |
| `widget_pos_y` | `85` | Y 위치 (%) |

#### AI 야간당번 (group: ai)

| 키 | 기본값 | 설명 |
|----|--------|------|
| `ai_enabled` | `1` | AI 자동응답 활성화 |
| `ai_wait_seconds` | `60` | 직원 무응답 시 AI 진입 대기 (초) |
| `ai_hour_start` | `18:30` | 운영 시작 시간 |
| `ai_hour_end` | `09:00` | 운영 종료 시간 |
| `ai_display_name` | `긴급대응` | AI 표시 이름 |
| `ai_greeting_msg` | `안녕하세요...` | AI 인사 메시지 |
| `ai_farewell_msg` | `담당자가...` | AI 퇴장 메시지 |
| `ai_pos_x` | `92` | X 위치 (%) |
| `ai_pos_y` | `60` | Y 위치 (%) |
| `ai_button_label` | `AI 상담` | 버튼 라벨 |
| `ai_button_color` | `#667eea` | 버튼 그라디언트 색상 |

#### 추가 설정 (group: extra)

| 키 | 기본값 | 설명 |
|----|--------|------|
| `offline_message` | `현재 업무시간...` | 업무외 안내 |
| `notice_message` | (빈 값) | 채팅창 상단 공지 |
| `upload_max_mb` | `10` | 파일 업로드 최대 MB |

---

### DB 스키마 변경

#### chatrooms 테이블

```sql
-- AI 야간당번 추가 컬럼 (2026-02-27)
ALTER TABLE chatrooms ADD COLUMN ai_active TINYINT(1) NOT NULL DEFAULT 0 AFTER isactive;
```

- `ai_active=0`: 일반 상담 채팅방
- `ai_active=1`: AI 야간당번 채팅방 (`?ai_mode=1`으로 생성)

---

### 프로덕션 자동 마이그레이션

`settings.php` 접속 시 자동 실행 (일회성):

```php
if (!isset($config['widget_pos_x'])) {
    // 1. chat_config에 6개 신규 키 INSERT
    // 2. chatrooms에 ai_active 컬럼 ADD (없을 경우)
    // 3. config 리로드
}
```

---

### 파일 매핑

| 파일 | 역할 |
|------|------|
| `includes/chat_widget.php` | 위젯 초기화 (CSS/JS 로드 + 채팅위젯/AI야간당번 인스턴스 생성) |
| `chat/chat.js` | ChatWidget 클래스 (mode='chat'\|'ai', pfx별 DOM 고유화) |
| `chat/chat.css` | 상담연결 + AI 위젯 스타일 (모바일 반응형 포함) |
| `chat/api.php` | 채팅 API (방 생성, 메시지 송수신, 설정 CRUD, AI 모드 분기) |
| `chat/admin.php` | 직원용 채팅 관리 (기존) |
| `dashboard/chat/settings.php` | 설정 관리 UI (위치 피커, 시간대, AI 버튼 커스터마이징) |
| `chat/chat.js.bak` | 리팩토링 전 백업 |
| `includes/ai_chatbot_widget.php` | **레거시 (미사용)** — 어디서도 include 안 됨 |

---

### ❌ 절대 하지 말 것

```php
// ❌ ai_chatbot_widget.php를 다시 include하지 말 것 (레거시, 채팅 시스템으로 대체됨)
include 'includes/ai_chatbot_widget.php';

// ❌ chat.js에서 mode 없이 ChatWidget 생성하지 말 것
new ChatWidget();  // mode 미지정 → 'chat'으로 기본 동작하지만 명시 권장

// ❌ 위치를 CSS bottom/right로 직접 변경하지 말 것 (applyPosition이 덮어씀)
.chat-widget { bottom: 20px; right: 20px; }  // chat_config의 pos_x/y가 우선
```

---

마지막 업데이트: 2026-02-27
