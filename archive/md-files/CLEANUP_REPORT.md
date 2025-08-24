# 🧹 두손기획인쇄 시스템 파일 정리 보고서

## 📅 정리 일시: 2024-12-16

## 🎯 정리 대상 파일 분류

### 1. 테스트 파일 (test*)
- **수량**: 약 100개 이상의 테스트 파일 발견
- **위치**: 주로 MlangPrintAuto 폴더 내
- **예시**: test_calc.php, test_connection.php, test_ajax.html 등
- **처리**: 모든 테스트 파일 제거 권장

### 2. 디버그 파일 (debug*)
- **수량**: 약 80개 이상의 디버그 파일 발견
- **위치**: 각 제품 폴더별로 산재
- **예시**: debug_price.php, debug_data.php, debug_mapping.php 등
- **처리**: 개발 완료된 디버그 파일 제거

### 3. 백업 파일 (*backup*, *복사본*)
- **수량**: 약 30개의 백업 파일 발견
- **위치**: 전체 디렉토리에 산재
- **예시**: index_backup.php, style - 복사본.css 등
- **처리**: 필요없는 백업 파일 제거

### 4. 중복 폴더
- **발견된 중복 폴더**:
  - MlangPrintAuto/NameCard - 0809정상 (중복)
  - MlangPrintAuto/NameCard - 파일정리됨 (중복)
  - MlangPrintAuto/envelope - 정상250809 (중복)
  - MlangPrintAuto/envelope - 모양바꾸려하기전 (중복)
  - MlangPrintAuto/MerchandiseBond - 정상0809 (중복)
  - MlangPrintAuto/NcrFlambeau - 250814정상 (중복)
  - MlangPrintAuto/inserted - 갤러리없는정상 (중복)
  - MlangPrintAuto/cadarok_backup (백업)
  - MlangPrintAuto/envelope_backup_20250816_095248 (백업)
  - MlangPrintAuto/MerchandiseBond_backup_20250816_103355 (백업)

### 5. 오래된 버전 파일
- admin/MlangPrintAuto250410 (오래된 버전)
- admin/MlangPrintAuto250418 (오래된 버전)
- admin/MlangPrintAuto250425 (오래된 버전)
- index250*.php 파일들

### 6. 임시 파일
- trace_values.php
- add_simple_data.php
- add_test_data.php

## 📊 정리 예상 효과

### 파일 수 감소
- **현재**: 약 5,000+ 파일
- **정리 후**: 약 3,000 파일 (40% 감소 예상)

### 디스크 공간 절약
- **예상 절약 공간**: 약 50-100MB

### 프로젝트 구조 개선
- 더 깔끔한 폴더 구조
- 빠른 파일 탐색
- 유지보수 용이성 향상

## ⚠️ 주의 사항

### 삭제하지 말아야 할 파일
1. **현재 사용 중인 시스템 파일**
   - index.php (메인 파일)
   - db.php (데이터베이스 설정)
   - 각 제품별 주요 index.php

2. **중요 설정 파일**
   - .htaccess
   - config.php 파일들

3. **현재 운영 중인 폴더**
   - MlangPrintAuto/ (메인)
   - admin/ (관리자)
   - shop/ (쇼핑카트)
   - member/ (회원)

## 🔧 정리 작업 진행 방법

### Step 1: 백업 생성
```bash
# 전체 백업 생성 (권장)
tar -czf duson_backup_20241216.tar.gz C:\xampp\htdocs
```

### Step 2: 테스트 파일 제거
- test_*.php 파일들
- *_test.php 파일들
- test_*.html 파일들

### Step 3: 디버그 파일 제거
- debug_*.php 파일들
- *_debug.php 파일들

### Step 4: 중복 폴더 정리
- "정상", "복사본" 등이 붙은 폴더 제거
- backup 폴더들 제거

### Step 5: 오래된 버전 제거
- 날짜가 붙은 오래된 파일들 제거

## ✅ 권장 정리 목록

다음 항목들을 안전하게 삭제할 수 있습니다:

1. **테스트 파일 (전체)**
2. **디버그 파일 (전체)**
3. **백업 폴더**:
   - MlangPrintAuto/cadarok_backup
   - MlangPrintAuto/envelope_backup_20250816_095248
   - MlangPrintAuto/MerchandiseBond_backup_20250816_103355

4. **중복 폴더**:
   - MlangPrintAuto/NameCard - 0809정상
   - MlangPrintAuto/NameCard - 파일정리됨
   - MlangPrintAuto/envelope - 정상250809
   - MlangPrintAuto/envelope - 모양바꾸려하기전
   - MlangPrintAuto/MerchandiseBond - 정상0809
   - MlangPrintAuto/NcrFlambeau - 250814정상
   - MlangPrintAuto/inserted - 갤러리없는정상

5. **오래된 관리자 폴더**:
   - admin/MlangPrintAuto250410
   - admin/MlangPrintAuto250418
   - admin/MlangPrintAuto250425

## 🚀 정리 실행 명령어

정리를 실행하시려면 다음 단계를 따르세요:

### 안전한 정리 (백업 폴더로 이동)
```bash
# 백업 폴더 생성
mkdir C:\xampp\htdocs\_to_delete_20241216

# 파일 이동 (삭제 대신)
move "C:\xampp\htdocs\MlangPrintAuto\*test*.php" "C:\xampp\htdocs\_to_delete_20241216\"
move "C:\xampp\htdocs\MlangPrintAuto\*debug*.php" "C:\xampp\htdocs\_to_delete_20241216\"
```

## 📝 정리 후 확인 사항

1. **시스템 정상 작동 확인**
   - 모든 제품 페이지 접근 가능
   - 주문 시스템 정상 작동
   - 관리자 페이지 정상 작동

2. **데이터베이스 연결 확인**
   - db.php 파일 유지
   - 연결 테스트

3. **이미지 및 업로드 폴더 확인**
   - uploads/ 폴더 유지
   - images/ 폴더 유지

---

**주의**: 정리 작업 전 반드시 전체 백업을 생성하시기 바랍니다!