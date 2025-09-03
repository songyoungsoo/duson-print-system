# 🚀 XAMPP → Cafe24 마이그레이션 마스터 가이드

**DevOps + Architect 통합 워크플로우**  
**프로젝트**: 두손기획인쇄 견적 시스템 이관  
**환경**: Windows XAMPP → Linux Cafe24 호스팅

---

## 📋 **가이드 구성**

| 문서 | 용도 | 실행 시점 |
|------|------|-----------|
| `MIGRATION_WORKFLOW_GUIDE.md` | 📖 전체 워크플로우 이해 | 배포 계획 단계 |
| `package_for_cafe24.php` | 🛠️ 자동 배포 패키징 | 배포 직전 |
| `validate_deployment.php` | 🧪 배포 후 자동 검증 | 배포 직후 |
| `deployment_checklist.md` | ✅ 수동 체크리스트 | 배포 후 10분 |
| `MASTER_MIGRATION_GUIDE.md` | 📚 통합 실행 가이드 | **지금 읽는 문서** |

---

## 🎯 **마이그레이션 목표**

✅ **해결된 기존 문제들**:
- ~~MySQL 테이블명 대소문자 문제~~ → 수정 완료 (101개 파일)
- ~~PHP 문법 오류~~ → 수정 완료 (13개 오류) 
- ~~누락 파일 참조~~ → 수정 완료 (28개 파일)
- ~~배포 패키지~~ → 생성 완료 (`MlangPrintAuto_cafe24_final_20241210.zip`)

🎯 **이번 워크플로우로 달성할 목표**:
- **자동화된 배포 프로세스** 구축
- **10분 이내 검증** 체계 확립  
- **롤백 계획** 수립
- **모니터링 및 유지보수** 가이드 제공

---

## ⚡ **빠른 실행 가이드** (바쁜 경우 이것만 보세요)

### 🚀 **즉시 배포 (30분)**

```bash
# 1. 자동 패키징 (5분)
cd C:\xampp\htdocs\automation
php package_for_cafe24.php

# 2. 카페24 업로드 (10분)  
# → FTP로 생성된 zip 파일 업로드
# → 파일매니저에서 압축 해제

# 3. 자동 검증 (3분)
# → 브라우저에서 http://dsp1830.shop/automation/validate_deployment.php 실행

# 4. 수동 체크리스트 (10분)
# → automation/deployment_checklist.md 따라 실행

# 5. 완료 확인 (2분)
# → 성공률 90% 이상이면 완료!
```

### 🔧 **문제 발생시**
```bash
# HTTP 500 → 에러 로그 확인
tail -f logs/php-error.log

# 견적 계산 안됨 → 브라우저 개발자도구에서 JavaScript 오류 확인

# 긴급 롤백 → 백업 파일로 복구
```

---

## 📖 **단계별 상세 실행**

### **Phase 1: 사전 준비 (10분)**

#### 1.1 환경 분석
현재 상태를 파악하고 준비 작업을 수행합니다.

```bash
# 로컬 개발 환경 정보 수집
php -v
php -m | grep -E "(mysqli|gd|mbstring|curl)"
mysql -V

# 프로젝트 크기 확인
du -sh C:\xampp\htdocs
find C:\xampp\htdocs -name "*.php" | wc -l
```

#### 1.2 백업 생성
```bash
# 로컬 백업
mysqldump -u duson1830 -p duson1830 > backup_local_$(date +%Y%m%d).sql
tar -czf htdocs_backup_$(date +%Y%m%d).tar.gz C:\xampp\htdocs

# Cafe24 서버 백업 (FTP/SSH로)
tar -czf cafe24_backup_$(date +%Y%m%d).tar.gz /home/hosting_users/dsp1830/www
```

---

### **Phase 2: 자동 패키징 (5분)**

#### 2.1 패키징 스크립트 실행
```bash
cd C:\xampp\htdocs\automation
php package_for_cafe24.php
```

**생성되는 파일들**:
- `cafe24_deploy_YYYYMMDD_HHMMSS.zip` (배포 패키지)
- `DEPLOYMENT_README.txt` (배포 가이드)
- `.htaccess` (Apache 보안 설정)
- `.user.ini` (PHP 최적화 설정)  
- `config/database.php` (환경별 DB 설정)

#### 2.2 패키지 검증
```bash
# ZIP 파일 내용 확인
unzip -l deployment/cafe24_deploy_*.zip | head -20

# 핵심 파일 존재 여부 확인
unzip -l deployment/cafe24_deploy_*.zip | grep -E "(index\.php|db\.php|\.htaccess)"
```

---

### **Phase 3: 서버 배포 (10분)**

#### 3.1 Cafe24 FTP 업로드
```bash
# FTP 클라이언트 또는 파일매니저 사용
# 1. cafe24_deploy_*.zip 파일을 웹 루트에 업로드
# 2. 압축 해제 (모든 파일이 루트에 배치되도록)
# 3. 압축 파일 삭제
```

#### 3.2 파일 권한 설정
```bash
# SSH 접속 가능한 경우
find /home/hosting_users/dsp1830/www -type d -exec chmod 755 {} \;
find /home/hosting_users/dsp1830/www -type f -exec chmod 644 {} \;
chmod 775 /home/hosting_users/dsp1830/www/uploads
chmod 775 /home/hosting_users/dsp1830/www/logs
```

#### 3.3 필수 디렉토리 생성
```bash
mkdir -p logs temp cache
touch logs/php-error.log
```

---

### **Phase 4: 자동 검증 (3분)**

#### 4.1 자동 검증 스크립트 실행
```bash
# 웹 브라우저에서 (관리자 로그인 후)
http://dsp1830.shop/automation/validate_deployment.php

# 또는 SSH에서
cd /home/hosting_users/dsp1830/www
php automation/validate_deployment.php
```

#### 4.2 검증 결과 분석
```bash
# 성공률 해석
90% 이상: ✅ 배포 성공
70-89%: ⚠️ 주의사항 확인 필요  
70% 미만: ❌ 롤백 검토
```

**주요 검증 항목**:
- HTTP 응답 테스트 (9개 제품 페이지)
- 데이터베이스 연결 및 문자셋
- PHP 확장 모듈 및 설정
- 파일 권한 및 보안 설정  
- 성능 (응답시간, 메모리 사용량)

---

### **Phase 5: 수동 검증 (10분)**

#### 5.1 핵심 기능 테스트
`automation/deployment_checklist.md`를 참조하여 다음 순서로 진행:

1. **기본 접속** (1-2분)
   - http://dsp1830.shop/ 메인 페이지
   - http://dsp1830.shop/admin/ 관리자 페이지

2. **데이터베이스** (3-4분)  
   - 로그인/로그아웃
   - 제품 옵션 로딩 (드롭다운)

3. **견적 계산** (5-6분) - **가장 중요!**
   - 자석스티커: http://dsp1830.shop/MlangPrintAuto/msticker/
   - 명함: http://dsp1830.shop/MlangPrintAuto/NameCard/
   - 봉투: http://dsp1830.shop/MlangPrintAuto/envelope/

4. **파일 업로드** (7-8분)
   - 이미지 업로드 테스트
   - 권한 확인

5. **보안 & 성능** (9-10분)
   - 민감 파일 접근 차단 확인
   - 응답 속도 확인

#### 5.2 문제 발생시 즉시 대응
```bash
# HTTP 500 오류
tail -50 logs/php-error.log
chmod 644 *.php; chmod 755 */; chmod 775 uploads logs

# 견적 계산 실패  
# → 브라우저 F12 → Console/Network 탭 확인

# DB 연결 오류
mysql -u dsp1830 -p dsp1830 -e "SELECT 1;"
```

---

### **Phase 6: 모니터링 및 완료 (지속)**

#### 6.1 즉시 모니터링 (첫 1시간)
```bash
# 실시간 에러 로그 모니터링
tail -f logs/php-error.log

# 5분마다 주요 페이지 확인
while true; do
  curl -s -I http://dsp1830.shop/ | head -1
  curl -s -I http://dsp1830.shop/MlangPrintAuto/msticker/ | head -1
  sleep 300
done
```

#### 6.2 성능 모니터링 (첫 24시간)  
- **응답시간**: 3초 이내 유지
- **에러율**: 1% 미만 유지
- **가용성**: 99.9% 이상 유지

#### 6.3 완료 보고
```bash
# 배포 완료 체크리스트
□ 자동 검증 성공률 90% 이상 달성
□ 수동 체크리스트 모든 항목 통과
□ 에러 로그에 치명적 문제 없음
□ 고객 공지사항 업데이트 (필요시)
□ 첫 1시간 모니터링 계획 수립

# 성공 메시지
echo "🎉 XAMPP → Cafe24 마이그레이션 완료!"
echo "📊 성공률: [자동검증결과]%"
echo "⏱️ 총 소요시간: [실제시간]분"
```

---

## 🚨 **긴급상황 대응 매뉴얼**

### **Level 1: 부분 기능 오류 (5분 복구)**
**증상**: 일부 제품 견적 계산 안됨, 이미지 로딩 느림
```bash
# 해결방법
# 1. PHP 에러 로그 확인
tail -20 logs/php-error.log

# 2. 특정 파일만 롤백
cp backup/MlangPrintAuto/NameCard/index.php MlangPrintAuto/NameCard/

# 3. 캐시 클리어
rm -rf temp/* cache/*
```

### **Level 2: 전체 사이트 다운 (15분 복구)**
**증상**: HTTP 500, 메인 페이지 접속 불가
```bash
# 해결방법
# 1. 즉시 백업으로 롤백
cd /home/hosting_users/dsp1830/www
rm -rf * .[^.]*
tar -xzf ../backups/cafe24_backup_YYYYMMDD.tar.gz

# 2. DB도 롤백 (필요시)
mysql -u dsp1830 -p dsp1830 < backups/database_backup_YYYYMMDD.sql

# 3. 기본 동작 확인
curl -I http://dsp1830.shop/
```

### **Level 3: 데이터 손상 (30분 복구)**
**증상**: DB 테이블 손상, 데이터 불일치
```bash
# 해결방법
# 1. DB 완전 복구
mysql -u dsp1830 -p -e "DROP DATABASE dsp1830; CREATE DATABASE dsp1830;"
mysql -u dsp1830 -p dsp1830 < backups/full_backup_YYYYMMDD.sql

# 2. 파일도 완전 복구
# Level 2와 동일한 절차

# 3. 데이터 무결성 검증
mysql -u dsp1830 -p dsp1830 -e "SELECT COUNT(*) FROM users;"
mysql -u dsp1830 -p dsp1830 -e "SHOW TABLES LIKE 'MlangPrintAuto_%';"
```

---

## 📊 **성능 기준 및 KPI**

### **응답시간 기준**
- 메인 페이지: < 3초
- 견적 페이지: < 5초  
- AJAX 요청: < 2초
- 파일 업로드: < 30초 (50MB)

### **가용성 기준**
- 월 가용성: 99.9% (43.8분 다운타임 허용)
- 일 가용성: 99.5% (7.2분 다운타임 허용)
- 피크타임 가용성: 100% (오전 9시-오후 6시)

### **보안 기준**
- 민감 파일 접근 차단: 100%
- 디렉토리 브라우징 차단: 100% 
- SQL Injection 방지: 모든 쿼리 prepared statement 사용
- XSS 방지: 모든 출력 htmlspecialchars 처리

---

## 🔄 **지속적 개선 계획**

### **1주 후 개선 작업**
- [ ] 성능 최적화 (DB 쿼리, 이미지 압축)
- [ ] 보안 강화 (SSL 인증서, 추가 보안 헤더)
- [ ] 모니터링 자동화 (알림 시스템 구축)

### **1개월 후 고도화**
- [ ] CDN 적용 검토
- [ ] 백업 자동화 시스템 구축  
- [ ] 성능 모니터링 대시보드 구축
- [ ] 사용자 피드백 기반 개선사항 적용

---

## 📞 **지원 및 문의**

### **기술 지원**
- **개발팀**: claude@anthropic.com
- **인프라팀**: [카페24 고객센터]
- **긴급상황**: [24시간 대응 번호]

### **문서 업데이트**
이 가이드는 배포 경험을 바탕으로 지속적으로 업데이트됩니다.
- **버전**: 1.0.0
- **최종 수정**: 2025-01-09
- **다음 리뷰**: 2025-01-16

---

**🎯 이제 준비가 완료되었습니다!**  
**위 가이드를 따라 단계별로 진행하면 안전하고 성공적인 마이그레이션이 가능합니다.**

**DevOps + Architect 통합 접근으로 인프라 안정성과 시스템 설계 우수성을 동시에 보장합니다.** 🚀