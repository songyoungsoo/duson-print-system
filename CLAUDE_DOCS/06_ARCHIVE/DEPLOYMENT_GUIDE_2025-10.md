# 프로덕션 배포 가이드 (2025-10)

## 📋 배포 개요

**배포 대상**: dsp1830.shop
**배포 일자**: 2025-10-10
**주요 변경사항**:
- 포스터(littleprint) 추가 옵션 시스템 구현
- 주문 상세 페이지 표시 개선
- 관리자 페이지 URL 케이스 처리

## 🔴 배포 전 필수 체크리스트

- [ ] 프로덕션 DB 전체 백업 완료
- [ ] 프로덕션 코드 파일 백업 완료
- [ ] 로컬 테스트 완료 확인
- [ ] 스키마 비교 스크립트 실행 완료
- [ ] ALTER TABLE 스크립트 검토 완료
- [ ] 배포 중단 계획 수립

## 📊 변경된 데이터베이스 테이블

### 주요 테이블
1. **mlangprintauto_littleprint** (포스터 가격 테이블)
2. **shop_temp** (장바구니)
3. **shop_order** (주문 아이템)
4. **mlangorder_printauto** (주문 정보)
5. **mlangprintauto_transactioncate** (카테고리)
6. **users** (사용자)

### 추가 옵션 관련 필드
- `coating_enabled` (코팅 활성화)
- `coating_type` (코팅 타입: single/double)
- `coating_price` (코팅 가격)
- `folding_enabled` (접지 활성화)
- `folding_type` (접지 타입: 2fold/3fold/4fold)
- `folding_price` (접지 가격)
- `creasing_enabled` (오시 활성화)
- `creasing_lines` (오시 라인 수)
- `creasing_price` (오시 가격)
- `additional_options` (추가 옵션 JSON)
- `additional_options_total` (추가 옵션 총액)

## 🚀 배포 절차

### 1단계: 프로덕션 DB 백업

```bash
# SSH로 프로덕션 서버 접속
ssh your-user@dsp1830.shop

# 전체 DB 백업 (데이터 포함)
mysqldump -u [user] -p dsp1830 > backup_before_deploy_$(date +%Y%m%d_%H%M%S).sql

# 중요 테이블만 백업
mysqldump -u [user] -p dsp1830 \
  mlangprintauto_littleprint \
  shop_temp \
  shop_order \
  mlangorder_printauto \
  mlangprintauto_transactioncate \
  users \
  > backup_critical_tables_$(date +%Y%m%d_%H%M%S).sql
```

### 2단계: 프로덕션 스키마 덤프 (구조만)

```bash
# 프로덕션 서버에서 실행
mysqldump -u [user] -p --no-data --skip-add-drop-table --skip-comments dsp1830 \
  mlangprintauto_inserted \
  mlangprintauto_envelope \
  mlangprintauto_namecard \
  mlangprintauto_sticker \
  mlangprintauto_msticker \
  mlangprintauto_cadarok \
  mlangprintauto_littleprint \
  mlangprintauto_merchandisebond \
  mlangprintauto_ncrflambeau \
  mlangorder_printauto \
  users \
  mlangprintauto_transactioncate \
  shop_temp \
  shop_order \
  > production_schema_dump.sql

# 로컬로 다운로드
scp your-user@dsp1830.shop:~/production_schema_dump.sql /var/www/html/claudedocs/
```

### 3단계: 스키마 비교 및 ALTER 스크립트 생성

```bash
# 로컬에서 실행
cd /var/www/html
php scripts/compare_db_schema.php \
  claudedocs/local_schema_dump.sql \
  claudedocs/production_schema_dump.sql
```

출력 결과를 확인하고 `scripts/update_production_schema.sql` 파일 검토

### 4단계: 코드 파일 배포

#### 방법 A: FTP 사용
```
업로드할 디렉토리/파일:
- /mlangprintauto/littleprint/
- /mlangorder_printauto/OrderComplete_universal.php
- /includes/AdditionalOptionsDisplay.php
- /admin/MlangPrintAuto/ (전체)
- /css/
- /js/
```

#### 방법 B: rsync 사용 (추천)
```bash
# 테스트 모드 (실제 전송 안 함)
rsync -avzn --exclude '.git' --exclude 'node_modules' \
  /var/www/html/ your-user@dsp1830.shop:/path/to/webroot/

# 실제 전송
rsync -avz --exclude '.git' --exclude 'node_modules' \
  /var/www/html/ your-user@dsp1830.shop:/path/to/webroot/
```

### 5단계: 프로덕션 DB 스키마 업데이트

```bash
# 프로덕션 서버에서 실행
# update_production_schema.sql 파일 업로드 후

# 스크립트 실행 전 확인
cat update_production_schema.sql

# 스크립트 실행
mysql -u [user] -p dsp1830 < update_production_schema.sql

# 결과 확인
mysql -u [user] -p dsp1830 -e "DESCRIBE shop_temp;"
mysql -u [user] -p dsp1830 -e "DESCRIBE shop_order;"
```

### 6단계: 관리자 디렉토리 심볼릭 링크 생성

```bash
# 프로덕션 서버에서 실행
cd /path/to/webroot/admin
ln -s MlangPrintAuto mlangprintauto

# 확인
ls -la | grep mlangprint
```

### 7단계: 프로덕션 테스트

#### 테스트 시나리오
1. **포스터 주문 테스트**
   - https://dsp1830.shop/mlangprintauto/littleprint/
   - 추가 옵션 선택 (코팅, 접지, 오시)
   - 가격 계산 확인
   - 장바구니 추가
   - 주문 완료

2. **주문 상세 페이지 확인**
   - 관리자 로그인
   - 주문 목록에서 최근 주문 선택
   - 상세 정보 표시 확인
   - 추가 옵션 표시 확인

3. **관리자 페이지 URL 테스트**
   - https://dsp1830.shop/admin/MlangPrintAuto/admin.php
   - https://dsp1830.shop/admin/mlangprintauto/admin.php (소문자)
   - 두 URL 모두 정상 작동 확인

## 🔧 수정된 파일 목록

### 프론트엔드
- `mlangprintauto/littleprint/index.php`
- `mlangprintauto/littleprint/calculate_price_ajax.php`
- `mlangprintauto/littleprint/add_to_basket.php`
- `mlangprintauto/littleprint/js/littleprint-premium-options.js`
- `mlangprintauto/littleprint/calculator.js`

### 백엔드
- `mlangorder_printauto/OrderComplete_universal.php`
- `mlangorder_printauto/OnlineOrder_unified.php`
- `includes/AdditionalOptionsDisplay.php`

### 관리자
- `admin/MlangPrintAuto/admin.php`
- `admin/MlangPrintAuto/ProductManager.php`

### CSS
- `css/product-layout.css`
- `css/common-styles.css`
- `assets/css/gallery.css`

## 🔄 롤백 절차

문제 발생 시 즉시 롤백:

```bash
# 1. 백업 DB 복원
mysql -u [user] -p dsp1830 < backup_before_deploy_YYYYMMDD_HHMMSS.sql

# 2. 코드 파일 복원
# (백업된 파일로 덮어쓰기)

# 3. 서비스 재시작
sudo systemctl restart apache2
# 또는
sudo service httpd restart
```

## ⚠️ 주의사항

1. **피크 시간 회피**: 업무 시간 외(새벽 2-4시) 배포 권장
2. **점진적 배포**: 스키마 변경 → 코드 배포 순서 준수
3. **테스트 주문**: 실제 주문 전 테스트 주문으로 검증
4. **모니터링**: 배포 후 1시간 동안 에러 로그 모니터링
5. **고객 공지**: 서비스 중단 시 사전 공지

## 📞 문제 발생 시 연락처

- 개발자: [연락처]
- 시스템 관리자: [연락처]
- 긴급 롤백 담당: [연락처]

## 📝 배포 체크리스트

### 배포 전
- [ ] 로컬 테스트 완료
- [ ] 프로덕션 DB 백업
- [ ] 프로덕션 코드 백업
- [ ] 스키마 비교 완료
- [ ] ALTER 스크립트 검토

### 배포 중
- [ ] 스키마 업데이트 실행
- [ ] 코드 파일 업로드
- [ ] 심볼릭 링크 생성
- [ ] 파일 권한 확인

### 배포 후
- [ ] 포스터 주문 테스트
- [ ] 추가 옵션 가격 계산 확인
- [ ] 주문 상세 페이지 확인
- [ ] 관리자 페이지 URL 확인
- [ ] 에러 로그 확인
- [ ] 고객 테스트 주문 모니터링

## 🎯 성공 기준

- ✅ 모든 테스트 시나리오 통과
- ✅ 에러 로그 없음
- ✅ 기존 기능 정상 작동
- ✅ 새 기능 정상 작동
- ✅ 관리자 페이지 정상 접근

---

**작성일**: 2025-10-10
**작성자**: Claude Code Assistant
**버전**: 1.0
