# 택배주소록 시스템 가이드

## 서버 정보

### dsp1830.shop (PHP 7.4, UTF-8)
- **URL**: http://dsp1830.shop/shop_admin/post_list74.php
- **데이터베이스**: dsp1830 / dsp1830 / ds701018
- **문자셋**: UTF-8
- **PHP 버전**: 7.4
- **테이블명**: `mlangorder_printauto` (소문자)

### dsp114.com (PHP 5.2, EUC-KR)
- **URL**: http://dsp114.com/shop_admin/post_list52.php
- **데이터베이스**: duson1830 / duson1830 / du1830
- **문자셋**: EUC-KR
- **PHP 버전**: 5.2
- **테이블명**: `MlangOrder_PrintAuto` (대소문자 혼합)

## 주요 파일

### 1. post_list74.php (dsp1830.shop)
- **경로**: `/shop_admin/post_list74.php`
- **기능**: 주문 목록 조회 및 검색
- **특징**:
  - 페이지당 20개 항목 표시
  - 검색 기능: 이름, 회사, 날짜범위, 주문번호범위
  - JSON 데이터 파싱 (formatted_display)
  - 한 줄 형식 표시 (| 구분자 사용)

### 2. export_excel74.php (dsp1830.shop)
- **경로**: `/shop_admin/export_excel74.php`
- **기능**: 엑셀 파일 다운로드
- **특징**:
  - 선택된 항목 또는 전체 다운로드
  - UTF-8 BOM 포함 (한글 지원)
  - 주문번호 및 날짜 컬럼 제외 (택배사 호환)

### 3. db.php
- **경로**: `/db.php`
- **기능**: 환경별 자동 데이터베이스 연결
- **특징**:
  - 로컬/운영 환경 자동 감지
  - `safe_mysqli_query()` 함수 제공
  - 테이블명 자동 매핑

### 4. config.env.php
- **경로**: `/config.env.php`
- **기능**: 환경 설정 관리
- **특징**:
  - 로컬: localhost 감지
  - 운영: dsp114.com, dsp1830.shop 감지

## 데이터베이스 구조

### mlangorder_printauto 테이블

| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| no | mediumint(8) | 주문번호 (PK) |
| date | datetime | 주문일시 |
| name | varchar(250) | 수하인명 |
| bizname | varchar(50) | 회사명 |
| zip | varchar(10) | 우편번호 |
| zip1 | varchar(250) | 주소1 |
| zip2 | varchar(250) | 주소2 |
| phone | varchar(20) | 전화번호 |
| Hendphone | varchar(20) | 핸드폰 |
| Type | varchar(250) | 품목 타입 |
| Type_1 | text | 주문 상세 (JSON) |
| ImgFolder | text | 업로드 경로 |
| uploaded_files | text | 업로드 파일 목록 (JSON) |

## 검색 조건

### WHERE 절
```sql
(zip1 like '%구%') or (zip2 like '%-%')
```
- 주소가 있는 주문만 조회
- "구"가 포함되거나 "-"가 포함된 주소

### 추가 검색 옵션
- **이름 검색**: `name LIKE '%검색어%'`
- **회사 검색**: `bizname LIKE '%검색어%'`
- **날짜 범위**: `date >= '시작일' AND date <= '종료일'`
- **주문번호 범위**: `no >= 시작번호 AND no <= 종료번호`

## JSON 데이터 형식

### Type_1 컬럼 (주문 상세)
```json
{
  "product_type": "inserted",
  "MY_type": "802",
  "MY_Fsd": "626",
  "PN_type": "821",
  "POtype": "1",
  "MY_amount": "0.5",
  "ordertype": "print",
  "formatted_display": "인쇄색상: 칼라인쇄(CMYK)\n용지: 90g아트지(합판인쇄)\n규격: A4 (210x297)\n인쇄면: 단면\n수량: 1매\n디자인: 인쇄만",
  "created_at": "2025-11-27 17:52:42"
}
```

### 화면 표시 형식
**변경 전** (줄바꿈):
```
인쇄색상: 칼라인쇄(CMYK)
용지: 90g아트지(합판인쇄)
규격: A4 (210x297)
인쇄면: 단면
수량: 1매
디자인: 인쇄만
```

**변경 후** (한 줄, | 구분):
```
칼라인쇄(CMYK) | 90g아트지(합판인쇄) | A4 (210x297) | 단면 | 1매 | 인쇄만
```

## 데이터 처리 로직

### 1. 수하인명 처리
```php
if (name != '0' && !empty(name)) {
    표시: name
} else if (!empty(bizname)) {
    표시: bizname
} else {
    표시: '-'
}
```

### 2. JSON 파싱 및 포맷팅
```php
// JSON 확인
if (Type_1[0] == '{') {
    $json = json_decode(Type_1);

    // formatted_display 추출
    $formatted = $json['formatted_display'];

    // "항목명: " 제거
    $formatted = preg_replace('/^[^:]+:\s*/m', '', $formatted);

    // 줄바꿈을 | 로 변경
    $formatted = str_replace("\n", ' | ', $formatted);
}
```

## 엑셀 다운로드 형식

### 헤더 (9개 컬럼)
| 수하인명 | 우편번호 | 주소 | 전화 | 핸드폰 | 박스수량 | 택배비 | 운임구분 | 품목명 | 기타 | 배송메세지 |
|---------|---------|------|------|--------|---------|--------|---------|-------|------|----------|

### 특징
- **제외**: 주문번호, 날짜 컬럼 (택배사 양식 호환)
- **인코딩**: UTF-8 BOM 포함
- **형식**: HTML 테이블 (.xls)
- **파일명**: `order_list_YYYY-MM-DD_HHmmss.xls`

## 페이징 설정

- **페이지당 항목 수**: 20개
- **페이지 링크 표시**: 10개씩
- **정렬**: 주문번호 내림차순 (최신순)

## FTP 업로드 방법

```bash
# 단일 파일 업로드
curl -T /path/to/file.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018

# 여러 파일 업로드
curl -T /tmp/post_list74.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018 && \
curl -T /tmp/export_excel74.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018
```

## 트러블슈팅

### 1. 데이터베이스 연결 오류
- `db.php` 경로 확인: `include "../db.php";`
- 데이터베이스 계정 확인: dsp1830 / ds701018
- `safe_mysqli_query()` 함수 사용

### 2. 한글 깨짐
- UTF-8 BOM 포함 확인
- `mysqli_set_charset($db, 'utf8mb4');`
- HTML 헤더: `<meta charset="utf-8">`

### 3. 테이블을 찾을 수 없음
- 테이블명 대소문자 확인
- dsp1830.shop: `mlangorder_printauto` (소문자)
- dsp114.com: `MlangOrder_PrintAuto` (혼합)

### 4. JSON 파싱 오류
- JSON 형식 확인: `$data[0] == '{'`
- `json_decode($data, true)` 사용
- `formatted_display` 키 존재 확인

## 향후 개선 사항

### 1. 주문 폼 수정
- `name` 필드가 "0"으로 저장되는 문제 해결
- 필수 입력 필드 검증 추가

### 2. 데이터 동기화
- dsp114.com ↔ dsp1830.shop 데이터 동기화 시스템
- 주문번호 충돌 방지

### 3. 보안 강화
- 관리자 인증 시스템 추가
- SQL Injection 방지 강화

### 4. 사용성 개선
- Ajax 기반 실시간 검색
- 대량 주문 처리 기능
- 배송 상태 추적

## 참고 사항

- **서버 경로**: dsp1830.shop → `/dsp1830/www/`
- **로컬 경로**: `/var/www/html/`
- **테스트 파일**: `check_columns.php`, `test_db.php`, `test_path.php`
- **운영 환경에서 에러 표시 비활성화 권장**

---

**작성일**: 2025-11-27
**작성자**: Claude Code Assistant
**버전**: 1.0
