# 두손기획인쇄 주문관리 시스템 보안 설정 가이드

## 🔐 시스템 개요

두손기획인쇄의 주문관리 시스템(`checkboard.php`)에 대한 보안 접근 시스템이 구축되었습니다.

### 주요 특징
- **🎨 Cafe24 스타일 UI** - Sky-Indigo 색상 테마 적용
- **📱 반응형 디자인** - 모바일 친화적 인터페이스
- **🔒 다중 인증 방식** - 전화번호 및 관리자 비밀번호 지원
- **⏰ 세션 관리** - 8시간 자동 타임아웃
- **🛡️ 보안 헤더** - 실시간 세션 상태 표시

## 📁 설치된 파일

### 1. `checkboard_auth.php` - 인증 페이지
- 현대적인 로그인 인터페이스
- 다중 비밀번호 지원
- 보안 안내 및 연락처 정보

### 2. `checkboard.php` (수정됨)
- 인증 체크 로직 추가
- 보안 헤더 및 로그아웃 버튼
- 세션 타임아웃 관리

### 3. `WindowSian.php` (수정됨)
- checkboard 접근 시 인증 확인
- 무단 접근 방지 강화

## 🔑 인증 방법

### 기본 비밀번호
```php
$valid_passwords = [
    // 회사 전화번호 뒷자리
    '1830',     // 02-2632-1830
    '2384',     // 1688-2384
    '1829',     // 농협 계좌번호 뒷자리
    
    // 관리자 전용
    'duson2025',
    'print1830',
    'admin123'
];
```

### 비밀번호 추가/수정 방법
`checkboard_auth.php` 파일의 24-30번째 줄에서 수정:
```php
$valid_passwords = [
    // 새 비밀번호 추가
    '새로운비밀번호',
    '0000',  // 예시
];
```

## 🛠 커스터마이징

### 1. UI 색상 변경
`checkboard_auth.php`에서 CSS 변수 수정:
```css
/* Sky-Indigo 그라디언트 (현재 설정) */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* 다른 색상 예시 */
background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); /* 빨강-노랑 */
background: linear-gradient(135deg, #00c851 0%, #007E33 100%); /* 녹색 */
```

### 2. 세션 타임아웃 조정
`checkboard.php` 11번째 줄에서 시간 조정:
```php
// 현재: 8시간 (28800초)
if (isset($_SESSION['auth_timestamp']) && (time() - $_SESSION['auth_timestamp']) > 28800) {

// 예시: 4시간으로 변경
if (isset($_SESSION['auth_timestamp']) && (time() - $_SESSION['auth_timestamp']) > 14400) {
```

### 3. 회사 정보 수정
`checkboard_auth.php`에서 연락처 정보 수정:
```html
<div class="contact-info">
    <strong>문의사항이 있으시면:</strong><br>
    📞 <span class="contact-phone">02-2632-1830</span> | 
    📞 <span class="contact-phone">1688-2384</span>
</div>
```

## 🔄 접근 흐름

1. **사용자가 `checkboard.php` 접근**
   ↓
2. **인증 확인 (없으면 `checkboard_auth.php`로 리다이렉트)**
   ↓
3. **비밀번호 입력 및 검증**
   ↓
4. **성공 시 세션 생성 및 메인 페이지 접근**
   ↓
5. **시안 보기 클릭 시 `WindowSian.php` 호출**
   ↓
6. **인증된 세션 확인 후 이미지 표시**

## 🚨 보안 기능

### 자동 로그아웃
- **세션 타임아웃**: 8시간 후 자동 로그아웃
- **수동 로그아웃**: 우상단 로그아웃 버튼
- **브라우저 종료**: 세션 자동 만료

### 접근 제어
- **직접 URL 접근 차단**: 인증 없이는 접근 불가
- **세션 검증**: 모든 페이지에서 인증 상태 확인
- **Referrer 체크**: WindowSian.php 무단 접근 방지

### 보안 헤더
- **실시간 상태**: 현재 세션 정보 표시
- **Cache 비활성화**: 브라우저 캐시 방지
- **CSRF 보호**: 세션 기반 보안

## 🎯 사용자 경험

### 접근 방법 안내
```
✅ 추천 방법:
1. 회사 전화번호 뒷자리 4자리 (1830, 2384)
2. 기억하기 쉬운 관리자 코드

❌ 보안상 피해야 할 것:
- 생일, 주민등록번호 등 개인정보
- 단순한 숫자 조합 (1234, 0000)
- 사전에 있는 단어
```

### 에러 처리
- **잘못된 비밀번호**: 친화적 오류 메시지
- **세션 만료**: 자동 재인증 안내
- **시스템 오류**: 연락처 정보 제공

## 📞 지원 및 문의

### 기술 지원
- **시스템 문제**: 개발자에게 문의
- **비밀번호 변경**: 관리자 권한 필요
- **접근 권한**: 보안 담당자 승인

### 사용자 지원
- **전화**: 02-2632-1830, 1688-2384
- **업무시간**: 평일 09:00-18:00
- **응급상황**: 관리자 직통

## 🔧 유지보수

### 정기 점검 항목
1. **비밀번호 보안성 검토** (월 1회)
2. **세션 로그 분석** (주 1회)
3. **접근 통계 확인** (일 1회)
4. **보안 업데이트 적용** (수시)

### 백업 및 복원
```bash
# 인증 시스템 백업
cp checkboard_auth.php checkboard_auth.php.backup
cp checkboard.php checkboard.php.backup

# 복원 시
cp checkboard_auth.php.backup checkboard_auth.php
cp checkboard.php.backup checkboard.php
```

---

**최종 업데이트**: 2025-12-10  
**버전**: 1.0  
**개발자**: AI Assistant (Claude)  
**테스트 상태**: ✅ 완료