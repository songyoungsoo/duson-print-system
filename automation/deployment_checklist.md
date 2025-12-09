# 📋 Cafe24 배포 후 10분 체크리스트

**🎯 목표**: 배포 후 10분 이내에 핵심 기능 검증 완료

---

## ⏱️ **1-2분: 기본 접속 확인**

### ✅ 웹사이트 접속
- [ ] **기본 페이지**: http://dsp1830.shop/ 
  - 두손기획인쇄 로고 표시 여부
  - 제품 메뉴 정상 표시 여부
  - 레이아웃 깨짐 없는지 확인

- [ ] **관리자 페이지**: http://dsp1830.shop/admin/
  - 로그인 페이지 정상 로딩
  - HTTP 500 오류 없는지 확인

### 🔍 즉시 확인사항
```bash
# 브라우저 개발자도구 (F12) 에서
# Console 탭 → JavaScript 오류 없는지 확인
# Network 탭 → 404 오류 파일 없는지 확인
```

---

## ⏱️ **3-4분: 데이터베이스 연결 확인** 

### ✅ DB 연결 테스트
- [ ] **회원 관련 기능**
  - 로그인/로그아웃 정상 동작
  - 회원가입 페이지 정상 표시
  - 기존 회원 데이터 정상 조회

- [ ] **제품 데이터 조회**
  - 견적 계산 페이지에서 재료/크기 옵션 정상 로딩
  - 드롭다운 메뉴에 데이터 정상 표시

### 🔧 문제 발생시 즉시 확인
```bash
# 1. DB 연결정보 확인
cat config/database.php | grep -E "(host|user|password|database)"

# 2. PHP 에러 로그 확인  
tail -f logs/php-error.log

# 3. DB 서비스 상태 (관리자 도구에서)
# phpMyAdmin 접속 후 데이터베이스 연결 테스트
```

---

## ⏱️ **5-6분: 핵심 기능 테스트**

### ✅ 견적 계산 시스템 (가장 중요!)
**우선순위 1**: 자석스티커 (이전에 정상 작동했던 제품)
- [ ] http://dsp1830.shop/MlangPrintAuto/msticker/
  - 크기 선택 시 가격 자동 계산
  - 수량 변경 시 가격 업데이트
  - AJAX 통신 정상 작동

**우선순위 2**: 명함 (수정된 제품)
- [ ] http://dsp1830.shop/MlangPrintAuto/NameCard/
  - 재료/크기 선택 시 자동 계산
  - 장바구니 추가 기능
  - 파일 업로드 기능

**우선순위 3**: 기타 주요 제품 (3개만 선택 테스트)
- [ ] **봉투**: http://dsp1830.shop/MlangPrintAuto/envelope/
- [ ] **포스터**: http://dsp1830.shop/MlangPrintAuto/LittlePrint/  
- [ ] **전단지**: http://dsp1830.shop/MlangPrintAuto/inserted/

### 🔧 견적 계산 실패시 즉시 확인
```javascript
// 브라우저 개발자도구 → Console 탭에서 실행
// AJAX 요청 테스트
fetch('/MlangPrintAuto/msticker/index.php')
  .then(response => response.text())
  .then(html => console.log(html.substring(0, 200)));
```

---

## ⏱️ **7-8분: 업로드 및 파일 처리**

### ✅ 파일 업로드 테스트
- [ ] **이미지 업로드**
  - 명함 페이지에서 이미지 파일 선택
  - 업로드 진행률 표시 정상
  - 업로드 완료 후 미리보기 표시

- [ ] **파일 권한 확인**
```bash
# 업로드 디렉토리 권한 확인
ls -la uploads/ ImgFolder/
# 출력 예시: drwxrwxr-x (775) 권한이어야 함
```

### ✅ 이미지 표시 테스트  
- [ ] **갤러리 이미지**
  - 제품 페이지의 샘플 이미지 정상 표시
  - 이미지 클릭시 확대 기능 동작
  - 이미지 로딩 속도 확인

---

## ⏱️ **9-10분: 보안 및 성능 확인**

### ✅ 보안 기본 설정
- [ ] **민감 파일 접근 차단**
```bash
# 다음 URL들이 403 Forbidden 또는 404 응답해야 함
curl -I http://dsp1830.shop/db.php
curl -I http://dsp1830.shop/config/database.php  
curl -I http://dsp1830.shop/.env
```

- [ ] **디렉토리 브라우징 차단**
```bash
# 디렉토리 목록이 표시되면 안됨
curl -I http://dsp1830.shop/uploads/
curl -I http://dsp1830.shop/includes/
```

### ✅ 성능 확인
- [ ] **응답 속도**
  - 메인 페이지 로딩: 3초 이내  
  - 견적 페이지 로딩: 5초 이내
  - AJAX 응답: 2초 이내

- [ ] **에러 로그 모니터링**
```bash
# 실시간 에러 로그 모니터링 (별도 터미널에서)
tail -f logs/php-error.log

# 최근 에러 확인  
tail -20 logs/php-error.log | grep -i error
```

---

## 🚨 **긴급 상황별 대응 방법**

### ❌ **HTTP 500 오류 (사이트 접속 불가)**
```bash
# 1단계: PHP 에러 로그 확인
tail -50 logs/php-error.log

# 2단계: 파일 권한 복구
chmod 644 *.php
chmod 755 */
chmod 775 uploads logs

# 3단계: DB 연결 확인
mysql -u dsp1830 -p dsp1830 -e "SELECT 1;"

# 최후 수단: 백업으로 롤백
# (사전에 생성한 백업 파일로 복구)
```

### ❌ **견적 계산 안됨**
```bash  
# 1단계: JavaScript 오류 확인
# 브라우저 F12 → Console 탭 확인

# 2단계: AJAX 요청 확인
# 브라우저 F12 → Network 탭 → XHR 요청 상태 확인

# 3단계: 테이블명 대소문자 확인
mysql -u dsp1830 -p dsp1830 -e "SHOW TABLES LIKE 'MlangPrintAuto_%';"
```

### ❌ **파일 업로드 실패**
```bash
# 1단계: 업로드 디렉토리 권한 확인
ls -la uploads/
chmod 775 uploads/

# 2단계: PHP 설정 확인  
grep -E "(upload_max_filesize|post_max_size)" .user.ini

# 3단계: 디스크 용량 확인
df -h
```

---

## 📊 **체크리스트 완료 보고서**

### ✅ 성공 기준 (모두 통과시 배포 성공)
- [ ] 기본 페이지 접속 가능
- [ ] 견적 계산 시스템 정상 동작 (최소 3개 제품)  
- [ ] 파일 업로드 기능 정상
- [ ] DB 연결 및 데이터 조회 정상
- [ ] 보안 설정 활성화 상태
- [ ] 에러 로그에 치명적 오류 없음

### ⚠️ 주의 기준 (일부 문제 있지만 운영 가능)
- [ ] 일부 제품의 견적 계산 오류 (전체 중 20% 미만)
- [ ] 이미지 로딩 속도 다소 느림 (5초 이내)
- [ ] 경고 수준의 PHP 오류 로그 존재

### ❌ 실패 기준 (즉시 롤백 필요)
- [ ] 메인 페이지 접속 불가 (HTTP 500)
- [ ] 모든 제품의 견적 계산 실패
- [ ] 데이터베이스 연결 불가
- [ ] 치명적인 보안 취약점 발견

---

## 🎯 **자동화 테스트 실행**

배포 후 수동 체크리스트와 함께 자동 검증 스크립트를 실행하세요:

```bash
# 웹에서 실행 (관리자 로그인 후)
http://dsp1830.shop/automation/validate_deployment.php

# 또는 SSH/터미널에서 실행
php automation/validate_deployment.php
```

**자동 테스트 결과 해석**:
- 성공률 90% 이상: 배포 성공 ✅
- 성공률 70-89%: 주의 사항 확인 후 운영 ⚠️  
- 성공률 70% 미만: 즉시 롤백 검토 ❌

---

## 📞 **완료 후 체크인**

### 배포 완료 확인사항
- [ ] 모든 체크리스트 항목 확인 완료
- [ ] 자동 검증 스크립트 실행 완료  
- [ ] 성공률 90% 이상 달성
- [ ] 에러 로그에 치명적 문제 없음
- [ ] 고객 공지사항 업데이트 (필요시)

### 모니터링 지속 요청사항
- **첫 1시간**: 5분마다 에러 로그 모니터링
- **첫 24시간**: 1시간마다 주요 기능 동작 확인  
- **첫 1주**: 일일 성능 및 오류율 확인

---

**🎉 축하합니다! XAMPP → Cafe24 마이그레이션이 성공적으로 완료되었습니다.**