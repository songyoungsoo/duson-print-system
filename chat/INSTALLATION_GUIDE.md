# 🎉 채팅 시스템 설치 완료!

## ✅ 설치된 항목

### 1. 데이터베이스 테이블 (5개)
- ✅ `chatrooms` - 채팅방 정보
- ✅ `chatparticipants` - 채팅방 참여자
- ✅ `chatmessages` - 채팅 메시지
- ✅ `chatstaff` - 직원 정보 (3명 등록됨)
- ✅ `chatsettings` - 채팅 설정

### 2. 파일 시스템
```
c:\xampp\htdocs\chat\
├── config.php          ✅ 설정 파일
├── api.php             ✅ API 핸들러
├── chat.css            ✅ 고객용 스타일
├── chat.js             ✅ 고객용 JavaScript
├── admin.php           ✅ 직원용 관리 페이지
├── demo.php            ✅ 데모 페이지
├── README.md           ✅ 사용 설명서
└── INSTALLATION_GUIDE.md ✅ 이 파일

c:\xampp\htdocs\chat_uploads\
└── (이미지 업로드 폴더 - 자동 생성됨)
```

### 3. 등록된 직원
- 직원1 (ID: staff1)
- 직원2 (ID: staff2)
- 직원3 (ID: staff3)

---

## 🚀 빠른 시작

### 1단계: 데모 페이지 확인
브라우저에서 접속:
```
http://localhost/chat/demo.php
```

우측 하단의 보라색 채팅 버튼을 클릭하여 테스트하세요!

### 2단계: 직원용 관리 페이지
브라우저에서 접속:
```
http://localhost/chat/admin.php
```

상단에서 "직원1", "직원2", 또는 "직원3"을 선택하여 로그인하세요.

### 3단계: 기존 사이트에 적용

**방법 1: 직접 추가**

기존 웹사이트 파일의 `</body>` 태그 직전에 다음 코드 추가:

```html
<!-- 채팅 시스템 -->
<link rel="stylesheet" href="/chat/chat.css">
<script src="/chat/chat.js"></script>
```

**방법 2: 공통 푸터에 추가**

푸터 파일 (예: `footer.php`, `includes/footer.php`)에 위 코드를 추가하면
모든 페이지에 자동으로 적용됩니다.

예시:
```php
<!-- c:\xampp\htdocs\includes\footer.php -->
</div>

<!-- 채팅 시스템 -->
<link rel="stylesheet" href="/chat/chat.css">
<script src="/chat/chat.js"></script>

</body>
</html>
```

---

## 📱 기능 테스트

### 고객 입장에서 테스트
1. ✅ 채팅 버튼 클릭
2. ✅ 텍스트 메시지 전송
3. ✅ 이미지 업로드 (카메라 아이콘 클릭)
4. ✅ 채팅창 닫기 (최소화 버튼)
5. ✅ 대화 내용 저장 (다운로드 아이콘)

### 직원 입장에서 테스트
1. ✅ `/chat/admin.php` 접속
2. ✅ 직원 선택 (직원1, 직원2, 직원3)
3. ✅ 고객의 메시지 확인
4. ✅ 응답 메시지 전송
5. ✅ 이미지 전송

---

## 🎨 색상 커스터마이징

### 보라색 → 파란색으로 변경

**chat.css 파일에서 찾기:**
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

**다음으로 변경:**
```css
background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
```

### 보라색 → 초록색으로 변경
```css
background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
```

### 보라색 → 빨간색으로 변경
```css
background: linear-gradient(135deg, #f2709c 0%, #ff9472 100%);
```

---

## 🔧 고급 설정

### 채팅창 위치 변경

**chat.css에서:**
```css
.chat-widget {
    bottom: 20px;   /* 하단 여백 */
    right: 20px;    /* 우측 여백 */
}
```

**좌측 하단으로 이동:**
```css
.chat-widget {
    bottom: 20px;
    left: 20px;     /* right 대신 left 사용 */
}
```

### 채팅창 크기 변경

**chat.css에서:**
```css
.chat-window {
    width: 380px;   /* 너비 */
    height: 550px;  /* 높이 */
}
```

### 폴링 주기 변경 (메시지 확인 간격)

**chat.js에서:**
```javascript
this.pollInterval = setInterval(() => {
    this.loadMessages();
}, 2000); // 2000 = 2초
```

**5초로 변경:**
```javascript
}, 5000); // 5000 = 5초
```

---

## 🔍 문제 해결

### ❌ 채팅 버튼이 보이지 않음

**원인:**
- CSS/JS 파일 경로가 잘못됨
- 파일이 로드되지 않음

**해결:**
1. 브라우저에서 F12 → Console 탭 확인
2. 에러 메시지 확인
3. 경로가 `/chat/chat.css`와 `/chat/chat.js`가 맞는지 확인

### ❌ 메시지가 전송되지 않음

**원인:**
- API 경로 문제
- 데이터베이스 연결 실패

**해결:**
1. F12 → Network 탭에서 `/chat/api.php` 요청 확인
2. 응답에 에러가 있는지 확인
3. `config.php`에서 DB 연결 정보 확인

### ❌ 이미지가 업로드되지 않음

**원인:**
- 업로드 폴더 권한 문제
- 파일 크기 초과

**해결:**
```bash
# 폴더 권한 확인
ls -la c:\xampp\htdocs\chat_uploads

# 권한 설정 (필요시)
chmod 755 c:\xampp\htdocs\chat_uploads
```

### ❌ 직원 페이지에서 채팅방이 보이지 않음

**원인:**
- 아직 고객이 채팅을 시작하지 않음

**해결:**
1. 먼저 고객 페이지에서 채팅 시작
2. 직원 페이지 새로고침
3. 채팅방 목록 확인

---

## 📊 데이터 확인 (MySQL)

### 채팅 테이블 확인
```sql
-- 채팅방 목록
SELECT * FROM chatrooms ORDER BY createdat DESC;

-- 메시지 목록
SELECT * FROM chatmessages ORDER BY createdat DESC LIMIT 10;

-- 직원 목록
SELECT * FROM chatstaff;

-- 참여자 목록
SELECT * FROM chatparticipants;
```

### 통계 조회
```sql
-- 총 채팅방 수
SELECT COUNT(*) as total_rooms FROM chatrooms;

-- 총 메시지 수
SELECT COUNT(*) as total_messages FROM chatmessages;

-- 오늘 메시지 수
SELECT COUNT(*) as today_messages
FROM chatmessages
WHERE DATE(createdat) = CURDATE();
```

---

## 🎯 다음 단계

### 즉시 사용 가능
- ✅ 고객 채팅 기능
- ✅ 직원 응답 기능
- ✅ 이미지 전송
- ✅ 대화 저장

### 추가 개선 가능
- 🔄 WebSocket으로 진정한 실시간 통신
- 🔔 브라우저 알림 (Notification API)
- 📁 파일 첨부 (PDF, 문서 등)
- 🎨 다크모드 지원
- 📈 채팅 통계 대시보드

---

## 📞 지원

문제가 발생하거나 질문이 있으시면:

1. `README.md` 파일 참고
2. 브라우저 개발자 도구(F12) 확인
3. MySQL 에러 로그 확인

---

**🎉 축하합니다! 채팅 시스템이 성공적으로 설치되었습니다!**

**테스트 URL:**
- 고객용 데모: http://localhost/chat/demo.php
- 직원용 관리: http://localhost/chat/admin.php

**바로 시작하세요!** 👍
