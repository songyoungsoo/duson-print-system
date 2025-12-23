# 로젠택배 API 자동 배송 시스템 설정 가이드

## 📋 시스템 개요

**목적**: 수동 CSV 다운로드/업로드 방식 → 원클릭 자동 배송 접수

**기능**:
- 선택한 주문을 로젠 API로 자동 접수
- 송장번호 즉시 발급 및 DB 자동 저장
- 배송 상태 실시간 조회 (cron job)

## 🚀 설치 순서

### 1단계: 비밀번호 설정 ✅ **반드시 수행**

파일: `shop_admin/logen_api_config.php`

```php
// 실제 로젠택배 API 비밀번호로 변경
define('LOGEN_PASSWORD', 'YOUR_PASSWORD_HERE');  // ← 여기에 실제 비밀번호 입력
```

**보안 주의**: 이 파일은 Git에 커밋하지 마세요! (.gitignore에 이미 추가됨)

---

### 2단계: 데이터베이스 테이블 생성

브라우저에서 접속:
```
http://dsp1830.shop/shop_admin/create_logen_shipment_table.php
```

**실행 결과**: `logen_shipment` 테이블이 생성됨

**테이블 구조**:
- order_no: 주문번호 (mlangorder_printauto.no)
- invoice_no: 송장번호 (로젠 API 발급)
- shipment_status: 배송 상태
- api_response: API 응답 JSON

---

### 3단계: 사용 방법

**관리자 페이지**: `shop_admin/post_list52.php`

1. 배송 접수할 주문 체크박스 선택
2. **🚀 로젠 API 자동 접수 (선택)** 버튼 클릭
3. 확인 팝업에서 [확인] 클릭
4. API 처리 완료 후 송장번호 자동 저장
5. 페이지 자동 새로고침

**결과**:
- `mlangorder_printauto.waybill`: 송장번호 저장
- `logen_shipment`: 배송 정보 저장

---

### 4단계: 로그 확인 (문제 발생 시)

파일 위치: `shop_admin/logs/logen_api.log`

**로그 내용**:
- API 요청 파라미터
- API 응답 데이터
- 오류 메시지 (있는 경우)

**로그 예시**:
```
[2025-12-14 14:30:15] registerShipment
Request: {
  "custCd": "53058114",
  "userId": "du1830",
  "shipments": [...]
}
Response: {
  "success": true,
  "invoiceNo": "123456789012"
}
```

---

## 🔧 파일 구조

```
shop_admin/
├── logen_api_config.php           # API 설정 (비밀번호 포함)
├── logen_api_handler.php          # API 통신 핵심 클래스
├── logen_auto_register.php        # 자동 배송 접수 처리
├── create_logen_shipment_table.php # DB 테이블 생성
├── post_list52.php                # 관리자 페이지 (버튼 추가)
└── logs/
    └── logen_api.log              # API 호출 로그
```

---

## ⚠️ 주의사항

### API 자격 증명
- **custCd**: 53058114 (고객사 코드)
- **userId**: du1830 (API 사용자명)
- **password**: ⚠️ **반드시 실제 값으로 변경**

### 테스트 모드
`logen_api_config.php`:
```php
define('LOGEN_TEST_MODE', true);   // 테스트: true, 운영: false
```

**테스트 모드 특징**:
- SSL 검증 스킵 (개발 환경)
- API 로그 상세 기록

**운영 모드 전환**:
1. 테스트 완료 후 `LOGEN_TEST_MODE` → `false` 변경
2. 실제 API 엔드포인트 확인
3. SSL 인증서 검증 활성화

---

## 🐛 문제 해결

### 1. "송장번호가 발급되지 않습니다"
**원인**: API 엔드포인트 또는 인증 정보 오류

**해결**:
1. `shop_admin/logs/logen_api.log` 확인
2. API 응답 에러 메시지 확인
3. 로젠택배 고객센터(1588-9988) 문의

### 2. "선택한 주문이 접수되지 않습니다"
**원인**: 주문 데이터 누락 (이름, 주소, 전화번호)

**해결**:
1. `mlangorder_printauto` 테이블 데이터 확인
2. 필수 필드 채워져 있는지 확인:
   - name, zip1, zip2, phone, Hendphone

### 3. "API 통신 오류"
**원인**: 네트워크 문제 또는 서버 IP 미등록

**해결**:
1. 서버에서 로젠 API URL 접근 가능 확인
2. 로젠택배에 현재 서버 IP 화이트리스트 등록 요청
3. 방화벽 설정 확인

---

## 📞 지원

**로젠택배 API 문의**:
- 고객센터: 1588-9988
- 이메일: webmaster@ilogen.com
- 개발자 센터: https://openapihome.ilogen.com/

**시스템 관련 문의**:
- 로그 파일 확인: `shop_admin/logs/logen_api.log`
- DB 테이블 확인: `logen_shipment`, `mlangorder_printauto`

---

## ✅ 설치 완료 체크리스트

- [ ] logen_api_config.php에 실제 비밀번호 입력
- [ ] create_logen_shipment_table.php 실행 (테이블 생성)
- [ ] post_list52.php에서 버튼 확인
- [ ] 테스트 주문 1건으로 자동 접수 시험
- [ ] 송장번호 정상 발급 확인
- [ ] DB에 저장된 데이터 확인
- [ ] 로그 파일 생성 확인

---

**최종 업데이트**: 2025-12-14
**버전**: 1.0.0
