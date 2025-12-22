# ⚙️ 04_OPERATIONS - 운영 관리

## 📋 요약
관리자 시스템, 보안, 배포 전략

### 🔐 관리자 시스템
- **경로**: `/admin/` (인증 필요)
- **기능**: 주문 관리, 파일 업로드, 통계
- **권한**: 세션 기반 인증

### 🛡️ 보안 설정
- **DB**: 안전한 쿼리 (safe_mysqli_*)
- **파일**: 업로드 검증 + 확장자 필터
- **세션**: PHP 세션 보안

### 🚀 배포 환경
- **개발**: XAMPP (Windows)
- **운영**: Linux (Cafe24)
- **청소**: production_clean/ (90% 파일 감소)

### 📊 모니터링
- **로그**: Apache + MySQL
- **디버그**: ?debug_db=1
- **성능**: 실시간 모니터링

---
→ **관리자**: [ADMIN_SYSTEM.md](ADMIN_SYSTEM.md)
→ **보안**: [SECURITY.md](SECURITY.md)
→ **배포**: [DEPLOYMENT.md](DEPLOYMENT.md)