## 🤖 영업시간 외 AI 챗봇 위젯 (After-Hours AI Chatbot)

### 시스템 개요

영업시간(09:00~18:30) 외 시간에 자동으로 표시되는 AI 챗봇 위젯.
기존 v2 ChatbotService를 직접 로드하여 DB 기반 실시간 가격 조회 제공.

| 항목 | 값 |
|------|-----|
| **위젯 파일** | `/includes/ai_chatbot_widget.php` |
| **API 엔드포인트** | `/api/ai_chat.php` |
| **ChatbotService** | `/v2/src/Services/AI/ChatbotService.php` (직접 require) |
| **지식 베이스** | `/v2/src/Services/AI/ChatbotKnowledge.php` (시스템 프롬프트 지식) |
| **표시 조건** | 18:30 이후 ~ 09:00 이전 (footer.php 통합 토글) |
| **include 위치** | `/includes/footer.php` (모든 페이지) |
| **테마** | 보라색 그라디언트 (#6366f1) — 주황색 직원 채팅과 구분 |

### 시간 체크 로직 (footer.php 통합 토글)

위젯 시간 제어는 `footer.php`의 통합 스크립트에서 일괄 관리.
`ai_chatbot_widget.php`에는 시간 체크 로직 없음 (순수 UI만).

```javascript
// footer.php — 통합 toggleWidgets() (60초 간격 실행)
function isBusinessHours() {
    var now = new Date();
    var h = now.getHours(), m = now.getMinutes();
    if (h < 9) return false;       // 09:00 이전
    if (h > 18) return false;      // 19:00 이후
    if (h === 18 && m >= 30) return false; // 18:30 이후
    return true;
}
function toggleWidgets() {
    var biz = isBusinessHours();
    var staff = document.querySelector('.chat-widget');   // chat.js가 동적 생성
    var ai = document.getElementById('ai-chatbot-widget'); // 정적 HTML
    if (staff) staff.style.display = biz ? '' : 'none';
    if (ai) ai.style.display = biz ? 'none' : 'block';
}
setInterval(toggleWidgets, 60000);
```

### API 구조 (`/api/ai_chat.php`)

| action | Method | 용도 |
|--------|--------|------|
| `chat` | POST | 메시지 전송 → ChatbotService.chat() 호출 |
| `reset` | POST | 대화 세션 초기화 |

- `V2_ROOT` 상수 정의 후 ChatbotService 직접 require (composer autoloader 불필요)
- `.env` 파일의 `GEMINI_API_KEY` 로드 (없어도 DB 기반 가격 조회는 정상 동작)
- Same-origin Referer 체크 (CSRF 대체)
- 세션 기반 대화 상태 유지 (`$_SESSION['chatbot']`)

### 위젯 UI 구성

- **토글 버튼**: 79×79px 보라색 원형 (10% 축소, 2026-02-21), 모바일 63×63px, "야간/당번" 라벨
- **채팅 창**: 310×420px, position:fixed, 16px border-radius, 우측 edge = 사이드바 카드 우측(`right:12px`) 정렬
- **드래그 이동**: 헤더 바를 마우스/터치로 드래그하여 채팅창 자유 이동 (뷰포트 경계 제한, × 버튼 드래그 제외)
- **사이드바 hover 중단**: 채팅창 열림 시 `.fm-chat-active` 클래스로 사이드바 카드 아이템 `pointer-events:none` 처리, 닫힘 시 복원
- **빠른 선택 버튼**: 스티커/라벨, 전단지/리플렛, 명함/쿠폰, 자석스티커, 봉투 | 카다록, 포스터, 양식지, 상품권 (2줄 배치, 2026-02-21)
- **입력 플레이스홀더**: "궁금한 상품을 선택 또는 입력하세요"
- **스크롤 격리**: `overscroll-behavior: contain` — 채팅창 스크롤 끝 도달 시 바깥 페이지 스크롤 전파 방지
- **메시지 버블**: 사용자(보라색 우측) / 봇(회색 좌측, "야간당번" 아바타)
- **타이핑 인디케이터**: 3-dot 애니메이션
- **모바일 반응형**: ≤768px에서 100% 너비
- **클릭형 선택지**: 번호 입력 대신 클릭으로 옵션 선택 (`.ai-opt-btn` 버튼), 선택 후 이전 버튼 비활성화

### 대화 흐름

```
제품 선택 (빠른 버튼 or 텍스트)
  → 종류 선택 (번호 입력)
    → 용지 선택
      → 수량 선택
        → 인쇄면 선택
          → 디자인 선택
            → ✅ 가격 표시 (VAT 포함)
```

### 직원 채팅 vs AI 챗봇 배타적 전환

| 시간대 | 위젯 | 위치 |
|--------|------|------|
| 09:00~18:30 | 주황색 직원 채팅 (`chat_widget.php`) | bottom-right |
| 18:30~09:00 | 보라색 AI 챗봇 (`ai_chatbot_widget.php`) | bottom:20px, right:80px (창은 right:12px 정렬) |

**배타적 전환 메커니즘**:
- 두 위젯 모두 `footer.php`에서 include (DOM에 항상 존재)
- `toggleWidgets()` 함수가 시간대에 따라 `display` 속성으로 한쪽만 표시
- 직원 채팅(`.chat-widget`)은 `chat.js`가 동적 생성 → `querySelector`로 탐색
- AI 챗봇(`#ai-chatbot-widget`)은 정적 HTML → `getElementById`로 탐색
- 60초 간격 `setInterval`로 영업시간 경계에서 실시간 전환

### 한국어 조사 자동 판별 (ChatbotService.php)

`getParticle()` 헬퍼 — 마지막 글자 받침 유무로 을/를 자동 선택:
```php
private function getParticle(string $text, string $withBatchim, string $withoutBatchim): string
{
    $lastChar = mb_substr($text, -1);
    $code = mb_ord($lastChar);
    if ($code >= 0xAC00 && $code <= 0xD7A3) {
        return (($code - 0xAC00) % 28 === 0) ? $withoutBatchim : $withBatchim;
    }
    return $withBatchim;
}
// 사용: "규격을 선택해주세요" vs "수량를→수량을" 자동 처리
```

### NCR양식지 단계 순서 (CRITICAL)

NCR양식지의 챗봇 대화 단계는 제품 페이지 드롭다운 순서와 반드시 일치해야 함:

```php
// ChatbotService.php — NCR 단계 설정
'ncrflambeau' => [
    'steps' => ['style', 'section', 'tree', 'quantity', 'design'],
    'stepLabels' => ['구분', '규격', '색상', '수량', '디자인'],
],
// style(BigNo=0) → section(BigNo=style) → tree(TreeNo=style) → quantity → design
```

**⚠️ 과거 오류**: stepLabels가 `['매수', '규격', '인쇄도수', ...]`로 잘못 설정되어 있었음. 실제 NCR 페이지의 드롭다운 cascade 순서와 라벨명이 일치하지 않으면 사용자 혼란 발생.

### Critical Rules

1. ❌ `.env` 파일 없어도 동작해야 함 — DB 연결만으로 가격 조회 가능
2. ❌ v2 composer autoloader 의존 금지 — 직접 require_once로 로드
3. ✅ 에러 발생 시 "전화 문의" 안내로 graceful fallback
4. ✅ 세션 쿠키로 대화 상태 유지 (페이지 이동해도 대화 계속)
5. ✅ 선택지는 클릭형 버튼으로 제공 (API `options` 배열 → 프론트 `.ai-opt-btn` 렌더링)
6. ✅ stepLabels는 제품 페이지 실제 드롭다운 라벨과 일치시킬 것
7. ⚠️ `detectProduct()` 키워드 순서: `msticker`를 `sticker`보다 **반드시 먼저** 배치 ("자석스티커"에 "스티커" 부분문자열 포함되어 잘못 매칭됨)
8. ✅ 지식 베이스(`ChatbotKnowledge.php`) 수정 시 Gemini 시스템 프롬프트 토큰 한도 내 유지
9. ✅ `isKnowledgeQuestion()` 키워드 목록은 지식 베이스 컨텐츠와 동기화 유지

### 지식 기반 Q&A (2026-02-21)

제품 가격 조회 외에 인쇄 가이드/규약/디자인비 등의 질문에도 AI가 답변.

**구조**:
```
사용자 메시지 → chat()
  ├─ 제품 키워드 감지 → 가격 조회 플로우 (DB 기반)
  ├─ 지식 키워드 감지 (isKnowledgeQuestion) → callAiForFreeQuestion → Gemini API
  └─ 둘 다 아님 → 품목 선택 메뉴 표시
```

**지식 베이스 컨텐츠** (`ChatbotKnowledge.php`):
- 회사 정보 (연락처, 계좌, 운영시간, 주소)
- 작업 규약 (교정 2회, 납기, 환불, 색상차이, 파일보관 등)
- 디자인 비용표 (서식/카탈로그/전단지/포스터/명함/봉투/스티커/북디자인)
- 파일 제출 안내 (포맷, 해상도, CMYK, 일러스트 윤곽선)
- 인쇄물 규격 사이즈표 (32절~A2, 명함)

**지식 키워드 예시**: 교정, 디자인비, 파일, 해상도, CMYK, 계좌, 운영시간, 배송, 환불, 가이드 등

**Gemini 설정**: temperature 0.3, maxOutputTokens 500


## 🚨 AI 긴급대응 시스템 (Emergency AI Response in Chat)

### 시스템 개요

직원 채팅 위젯에서 고객 메시지에 60초간 무응답 시 AI "긴급대응" 봇이 자동 진입하여 ChatbotService(가격 조회 등)로 응답.
관리자(staff)가 응답하면 AI가 "담당자가 연결되었습니다" 퇴장 메시지를 남기고 비활성화.

| 항목 | 값 |
|------|-----|
| **트리거** | 고객 메시지 후 60초 무응답 |
| **AI 엔진** | `ChatbotService.php` (Gemini + DB 기반 가격 조회) |
| **발신자 ID** | `ai_bot` (senderid), `긴급대응` (sendername) |
| **활성화 플래그** | `chatrooms.ai_active` (TINYINT, 0/1) |
| **시간 추적** | `chatrooms.last_customer_msg_at`, `last_staff_msg_at` |
| **세션 분리** | `$_SESSION['ai_emergency_chatbot']` (야간당번과 독립) |

### 동작 흐름

```
고객 메시지 도착 → 60초 타이머 (getMessages 폴링에 피기백)
  → 관리자 응답 없음 (60초 경과)
  → "긴급대응" 봇 자동 진입 (activateAI)
    → "안녕하세요, 긴급대응입니다. 담당자 연결 전까지 제가 도와드리겠습니다."
    → ChatbotService로 고객 질문에 응답 (가격 조회 등)
  → 고객이 추가 질문 → AI가 계속 응답 (handleAIConversation)
  → 관리자가 메시지 입력
    → "담당자가 연결되었습니다. 이어서 상담 도와드릴 거예요. 감사합니다!" (deactivateAIIfActive)
    → AI 비활성화, 이후 메시지에 AI 개입 안 함
```

### 백엔드 함수 (chat/api.php)

| 함수 | 용도 |
|--------|------|
| `checkAndTriggerAI($roomId)` | getMessages() 폴링에 피기백, 60초 무응답 감지 → activateAI() |
| `handleAIConversation($roomId)` | AI 활성 시 고객 새 메시지에 AI 응답 |
| `activateAI($roomId, $msg)` | ai_active=1 + 인사 메시지 + 첫 응답 |
| `deactivateAIIfActive($roomId)` | staff 메시지 시 퇴장 메시지 + ai_active=0 |
| `insertAIMessage($roomId, $msg)` | senderid='ai_bot', sendername='긴급대응' INSERT |
| `callChatbotService($msg)` | ChatbotService 로드 + 세션 분리 + chat() 호출 |

### DB 스키마 변경 (2026-02-24)

```sql
ALTER TABLE chatrooms ADD COLUMN ai_active TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE chatrooms ADD COLUMN last_customer_msg_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE chatrooms ADD COLUMN last_staff_msg_at TIMESTAMP NULL DEFAULT NULL;
```

### 프론트엔드 UI (보라색 AI 메시지 구분)

- **chat.js**: `appendMessage()`에 `senderid === 'ai_bot'` 감지 → `.ai-bot` CSS 클래스 + 🤖 아바타
- **chat.css**: `.chat-message.ai-bot` 보라색 그라데이션 스타일 (#7c3aed → #a855f7)
- **admin_floating.js**: `appendAdminMessage()`에 ai_bot 감지 + 🤖 아바타
- **admin_floating.php**: `.admin-message.ai-bot` 보라색 CSS

### Critical Rules

1. ✅ `checkAndTriggerAI()`는 `getMessages()` 폴링에만 피기백 — 별도 폴링 없음 (2초 간격 기존 폴링 활용)
2. ✅ 세션 분리: `$_SESSION['ai_emergency_chatbot']` ↔ `$_SESSION['chatbot']` (야간당번)
3. ✅ staff 메시지 시 퇴장 메시지 후 ai_active=0 (순서 중요)
4. ❌ AI 응답 시간 제한 없음 — ChatbotService 호출이 느리면 폴링 응답도 느려진다는 점 인지
5. ❌ `callChatbotService()` 내부에서 `.env` 파일 없어도 DB 기반 가격 조회는 정상 동작해야 함

