# 🔧 카다록 가격 계산 오류 해결 가이드

## 📋 문제 상황
카다록 페이지(`MlangPrintAuto/cadarok/index.php`)에서 가격 계산 버튼을 클릭해도 가격이 계산되지 않는 문제 발생

## 🔍 발생한 오류들

### 1. JavaScript 문법 오류
```
index.php:891 Uncaught SyntaxError: Invalid or unexpected token
index.php:374 Uncaught ReferenceError: calc_ok is not defined
index.php:517 Uncaught SyntaxError: Invalid or unexpected token
```

### 2. PHP Fatal Error
```
Fatal error: Cannot redeclare getOptions() (previously declared in C:\xampp\htdocs\MlangPrintAuto\cadarok\index.php:49) in C:\xampp\htdocs\MlangPrintAuto\cadarok\index.php on line 88
```

## 🛠️ 해결 과정

### 1단계: 문제 진단
- 브라우저 개발자 도구(F12)에서 JavaScript 오류 확인
- PHP Fatal Error 로그 확인
- 디버그 파일 생성하여 데이터베이스 연결 및 데이터 상태 확인

### 2단계: 디버그 파일 생성
**파일**: `MlangPrintAuto/cadarok/debug_price.php`
- 데이터베이스 연결 테스트
- 필요한 테이블 존재 여부 확인
- 샘플 가격 계산 테스트
- 카다록 데이터 존재 여부 확인

### 3단계: 테스트 파일 생성
**파일**: `MlangPrintAuto/cadarok/test_calc.php`
- 간단한 테스트용 가격 계산 파일
- JavaScript 오류와 데이터베이스 문제 구분을 위한 용도

### 4단계: 누락된 PHP 변수 정의
**문제**: 페이지 구조 변경 시 필요한 PHP 변수들이 정의되지 않음

**해결**: 필수 변수들 추가 정의
```php
// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";
$MultyUploadDir = "../../PHPClass/MultyUpload";

// 로그 정보에서 필요한 변수들 추출
$log_url = $log_info['url'];
$log_y = $log_info['y'];
$log_md = $log_info['md'];
$log_ip = $log_info['ip'];
$log_time = $log_info['time'];
```

### 5단계: 폼 구조 수정
**문제**: 중복된 폼 태그로 인한 JavaScript 오류

**해결**: 폼 구조 정리
```php
// 중복된 폼 태그 제거
<form name="choiceForm" method="post" action="order_process.php">
    <!-- 단일 폼 구조로 통일 -->
</form>
```

### 6단계: Hidden 필드 추가
**문제**: 가격 계산 결과를 저장할 hidden 필드 누락

**해결**: 필수 hidden 필드들 추가
```php
<input type="hidden" name="Price" value="">
<input type="hidden" name="DS_Price" value="">
<input type="hidden" name="Order_Price" value="">
<input type="hidden" name="PriceForm" value="">
<input type="hidden" name="DS_PriceForm" value="">
<input type="hidden" name="Order_PriceForm" value="">
<input type="hidden" name="VAT_PriceForm" value="">
<input type="hidden" name="Total_PriceForm" value="">
<input type="hidden" name="StyleForm" value="">
<input type="hidden" name="SectionForm" value="">
<input type="hidden" name="QuantityForm" value="">
<input type="hidden" name="DesignForm" value="">
<input type="hidden" name="OnunloadChick" value="off">
```

### 7단계: 중복 함수 정의 제거
**문제**: `getOptions()` 함수가 두 번 정의되어 PHP Fatal Error 발생

**해결**: 중복된 함수 정의 제거
```php
// 첫 번째 정의만 유지, 두 번째 정의 삭제
function getOptions($connect, $GGTABLE, $page, $BigNo) {
    $options = [];
    $res = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$BigNo' ORDER BY no ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $options[] = $row;
    }
    return $options;
}
```

### 8단계: 초기 데이터 로드 로직 구현
**해결**: 페이지 로드 시 필요한 초기 데이터 설정
```php
// 초기 구분값 가져오기
$initial_type = "";
$type_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $initial_type = $type_row['no'];
}

// 초기 규격 옵션 가져오기
$size_options = getOptions($connect, $GGTABLE, $page, $initial_type);

// 초기 종이종류 옵션 가져오기
$paper_options = [];
if (!empty($initial_size)) {
    $paper_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND TreeNo='$initial_type' ORDER BY no ASC");
    while ($paper_row = mysqli_fetch_assoc($paper_result)) {
        $paper_options[] = $paper_row;
    }
}
```

## ✅ 최종 해결 결과

### 수정된 파일들
1. **`MlangPrintAuto/cadarok/index.php`** - 메인 페이지 수정
2. **`MlangPrintAuto/cadarok/debug_price.php`** - 디버그 파일 생성
3. **`MlangPrintAuto/cadarok/test_calc.php`** - 테스트 파일 생성
4. **`MlangPrintAuto/cadarok/price_cal.php`** - 디버그 로그 추가

### 해결된 문제들
- ✅ JavaScript 문법 오류 해결
- ✅ PHP Fatal Error 해결
- ✅ 누락된 변수 정의 완료
- ✅ 폼 구조 정리 완료
- ✅ Hidden 필드 추가 완료
- ✅ 중복 함수 정의 제거 완료
- ✅ 초기 데이터 로드 로직 구현 완료

## 🔍 디버깅 방법론

### 1. 체계적 접근
1. **브라우저 개발자 도구** 활용
2. **디버그 파일** 생성하여 단계별 확인
3. **테스트 파일** 생성하여 문제 범위 좁히기
4. **로그 추가**하여 실행 흐름 추적

### 2. 문제 분리
- **JavaScript 오류** vs **PHP 오류** 구분
- **클라이언트 사이드** vs **서버 사이드** 문제 분리
- **데이터베이스 문제** vs **코드 로직 문제** 구분

### 3. 단계별 검증
- 각 수정 사항을 개별적으로 테스트
- 문제 해결 후 원래 코드로 복원하여 최종 검증

## 🚨 예방 방법

### 1. 코드 구조 변경 시 주의사항
- 기존 변수 의존성 확인
- 함수 중복 정의 방지
- 폼 구조 일관성 유지

### 2. 테스트 방법
- 브라우저 개발자 도구 상시 확인
- 단계별 기능 테스트
- 디버그 파일 활용한 데이터 검증

### 3. 문서화
- 변경 사항 기록
- 문제 해결 과정 문서화
- 재사용 가능한 디버그 도구 보관

## 📚 참고 자료

### 생성된 디버그 도구들
- `debug_price.php` - 데이터베이스 및 가격 계산 디버그
- `test_calc.php` - 간단한 가격 계산 테스트

### 관련 파일들
- `price_cal.php` - 실제 가격 계산 로직
- `index.php` - 메인 페이지
- `includes/functions.php` - 공통 함수들

---

**작성일**: 2025년 8월 8일  
**해결 완료**: 카다록 가격 계산 기능 정상화  
**적용 범위**: 카다록 페이지 전체  
**다음 단계**: 다른 품목 페이지에 동일한 패턴 적용 시 참조