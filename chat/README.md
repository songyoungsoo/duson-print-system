# 📱 채팅 시스템 - 두손기획인쇄

고객과 직원이 실시간으로 소통할 수 있는 채팅 시스템입니다.

## ✨ 주요 기능

### 🎯 고객용 기능
- ✅ 실시간 메시지 전송/수신 (2초마다 자동 업데이트)
- ✅ 이미지 첨부 및 전송 (최대 5MB)
- ✅ 읽지 않은 메시지 알림
- ✅ 채팅창 열기/닫기
- ✅ 대화 내용 텍스트 파일로 저장
- ✅ 반응형 디자인 (PC/모바일 지원)

### 👥 직원용 기능
- ✅ 3명의 직원이 동시에 같은 채팅방 참여
- ✅ 모든 고객 채팅 확인 및 응답
- ✅ 이미지 전송
- ✅ 실시간 메시지 수신

## 📁 파일 구조

```
chat/
├── config.php          # 데이터베이스 연결 및 설정
├── api.php             # 채팅 API (메시지 송수신, 이미지 업로드)
├── chat.css            # 고객용 채팅 위젯 스타일
├── chat.js             # 고객용 채팅 위젯 JavaScript
├── admin.php           # 직원용 채팅 관리 페이지
├── demo.php            # 데모 페이지
├── setup_staff.sql     # 직원 정보 테이블
└── README.md           # 이 파일
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

### `chatsettings` - 채팅 설정
- `id`: 설정 ID
- `userid`: 사용자 ID
- `isminimized`: 최소화 상태
- `notificationenabled`: 알림 활성화
- `soundenabled`: 소리 활성화

## 🚀 설치 방법

### 1. 데이터베이스 설정

```bash
# 채팅 시스템 테이블 생성
mysql -u dsp1830 -pds701018 dsp1830 < create_chat_system.sql

# 직원 정보 추가
mysql -u dsp1830 -pds701018 dsp1830 < setup_staff.sql
```

### 2. 파일 업로드 디렉토리 생성

`chat_uploads/` 폴더가 자동으로 생성되지만, 권한을 확인하세요:

```bash
chmod 755 chat_uploads/
```

### 3. 사이트에 적용

기존 웹사이트의 모든 페이지 `</body>` 태그 직전에 추가:

```html
<!-- 채팅 시스템 -->
<link rel="stylesheet" href="/chat/chat.css">
<script src="/chat/chat.js"></script>
```

## 📖 사용 방법

### 고객용

1. 웹사이트 방문
2. 우측 하단의 보라색 채팅 버튼 클릭
3. 메시지 입력 또는 이미지 첨부
4. 직원의 응답 대기

### 직원용

1. `/chat/admin.php` 접속
2. 상단에서 직원 선택 (직원1, 직원2, 직원3)
3. 고객 채팅방 목록 확인
4. 채팅방 선택 후 응답

**현재 직원 계정:**
- 직원1 (ID: staff1)
- 직원2 (ID: staff2)
- 직원3 (ID: staff3)

## 🎨 커스터마이징

### 색상 변경 (`chat.css`)

```css
/* 메인 색상 (보라색 그라데이션) */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* 원하는 색상으로 변경 예시 (파란색) */
background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
```

### 위치 변경

```css
.chat-widget {
    bottom: 20px;  /* 하단 여백 */
    right: 20px;   /* 우측 여백 */
}
```

### 크기 변경

```css
.chat-window {
    width: 380px;   /* 너비 */
    height: 550px;  /* 높이 */
}
```

## 🔧 API 엔드포인트

### GET /chat/api.php

- `?action=get_or_create_room` - 채팅방 가져오기 또는 생성
- `?action=get_messages&room_id={id}&last_id={id}` - 메시지 조회
- `?action=get_unread_count&room_id={id}` - 읽지 않은 메시지 수
- `?action=export_chat&room_id={id}` - 대화 내용 내보내기

### POST /chat/api.php

- `action=send_message` - 메시지 전송
  - `room_id`: 채팅방 ID
  - `message`: 메시지 내용

- `action=upload_image` - 이미지 업로드
  - `room_id`: 채팅방 ID
  - `image`: 이미지 파일 (최대 5MB)

- `action=mark_as_read` - 읽음 처리
  - `room_id`: 채팅방 ID

## 📊 동작 원리

### 1. 고객이 채팅 시작
- 채팅 버튼 클릭
- 자동으로 채팅방 생성
- 모든 온라인 직원이 자동으로 참여

### 2. 실시간 메시지
- JavaScript가 2초마다 서버에 새 메시지 확인
- 새 메시지 있으면 자동으로 표시
- 직원도 동일한 방식으로 실시간 수신

### 3. 이미지 전송
- 파일 선택 시 서버로 업로드
- `chat_uploads/` 폴더에 저장
- 메시지로 이미지 경로 저장

## 🔒 보안 고려사항

- ✅ 파일 타입 검증 (이미지만 허용)
- ✅ 파일 크기 제한 (5MB)
- ✅ SQL Injection 방지 (Prepared Statement 사용)
- ✅ XSS 방지 (HTML Escape)
- ⚠️ 추가 권장사항:
  - HTTPS 사용
  - 직원 로그인 인증 강화
  - 파일명 암호화

## 🐛 문제 해결

### 채팅이 열리지 않음
- 브라우저 콘솔(F12) 확인
- `/chat/api.php` 경로 확인
- 데이터베이스 연결 확인

### 이미지 업로드 안됨
- `chat_uploads/` 폴더 권한 확인 (755)
- PHP `upload_max_filesize` 설정 확인
- 파일 형식 확인 (jpg, png, gif, webp)

### 메시지가 전송되지 않음
- 네트워크 탭에서 API 응답 확인
- 데이터베이스 테이블 존재 확인
- PHP 에러 로그 확인

## 📝 향후 개선 사항

- [ ] WebSocket을 이용한 진정한 실시간 통신
- [ ] 파일 첨부 기능 (PDF, 문서 등)
- [ ] 채팅방별 알림 소리
- [ ] 직원 온라인/오프라인 상태 표시
- [ ] 채팅 기록 검색 기능
- [ ] 채팅 통계 대시보드

## 📞 문의

두손기획인쇄
- 전화: 02-2632-1830
- 팩스: 02-2632-1829
- 이메일: dsp1830@naver.com

---

**버전**: 1.0.0
**최종 수정일**: 2025-11-25
