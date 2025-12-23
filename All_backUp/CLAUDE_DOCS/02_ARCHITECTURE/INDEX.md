# 🏗️ 02_ARCHITECTURE - 시스템 구조

## 📋 요약
기술 스택, 디렉토리 구조, 시스템 아키텍처

### ⚙️ 기술 스택
- **Backend**: PHP 7.4+ + MySQL 8.0
- **Frontend**: HTML5 + CSS3 + Vanilla JS
- **Server**: Apache 2.4 (XAMPP)

### 📂 디렉토리 구조
```
C:\xampp\htdocs\
├── mlangprintauto/     # 9개 제품 모듈
├── admin/              # 관리자 시스템
├── includes/           # 공통 라이브러리
├── css/               # 통합 스타일
└── js/                # 공통 스크립트
```

### 🔄 요청 흐름
1. 제품 페이지 로드 → AJAX 갤러리
2. 옵션 선택 → JS 실시간 계산
3. 장바구니 → PHP 검증 + 세션
4. 주문 완료 → DB 저장 + 고유 ID

### 🗄️ DB 구조
- **제품 테이블**: mlangprintauto_[product]
- **공통 테이블**: member_*, shop_*, admin_*
- **세션**: PHP 세션 (장바구니 + 인증)

---
→ **상세**: [TECH_STACK.md](TECH_STACK.md)