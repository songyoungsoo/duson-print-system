# 카탈로그(cadarok) 관리자 페이지 수정 내역
**날짜**: 2025년 11월 8일  
**작업자**: Kiro AI Assistant

## 📋 수정 개요

봉투(envelope) 관리자 페이지에서 발견된 "구분"과 "종류"가 나타나지 않는 문제를 카탈로그(cadarok)에도 동일하게 수정했습니다.

## 🔧 수정된 파일 (3개)

### 1. admin/MlangPrintAuto/cadarok_Script.php
**주요 수정 사항:**

#### A. Short tag 제거
```php
// 수정 전
<?  → <?php
<?= → <?php echo
```

#### B. Variable scope 문제 해결
```php
// 수정 전
<?php include"../../db.php";
$result= mysqli_query($db, "...", $db);
mysqli_close($db);

// 수정 후
<?php
$result= mysqli_query($db, "...");수정251108/README.md
// include와 mysqli_close 제거
```

**이유**: 
- `include "../../db.php"`를 하면 새로운 스코프가 생겨서 부모 파일의 변수($GGTABLE, $Ttable 등)에 접근 불가
- 부모 파일(cadarok_admin.php)의 DB connection 사용

#### C. mysqli 파라미터 오류 수정
```php
// 수정 전
mysqli_query($db, $query, $db)  // ❌ 3개 파라미터

// 수정 후
mysqli_query($db, $query)  // ✅ 2개 파라미터
```

#### D. 변수명 버그 수정
```php
// 규격 필드 - 수정 전 (버그)
if($MlangPrintAutoFildView_Section){  // 잘못된 변수
    $result=mysqli_query($db, "... where no='$MlangPrintAutoFildView_Section'");

// 규격 필드 - 수정 후
if($MlangPrintAutoFildView_TreeSelect){  // 올바른 변수
    $result=mysqli_query($db, "... where no='$MlangPrintAutoFildView_TreeSelect'");

// 종이종류 필드 - 수정 전 (버그)
if($MlangPrintAutoFildView_TreeSelect){  // 잘못된 변수
    $result=mysqli_query($db, "... where no='$MlangPrintAutoFildView_TreeSelect'");

// 종이종류 필드 - 수정 후
if($MlangPrintAutoFildView_Section){  // 올바른 변수
    $result=mysqli_query($db, "... where no='$MlangPrintAutoFildView_Section'");
```

### 2. admin/MlangPrintAuto/cadarok_admin.php
**주요 수정 사항:**

#### A. Short tag 수정 (IncFormOk 부분)
```php
// 수정 전
fwrite($fp, "<?\n");

// 수정 후
fwrite($fp, "<?php\n");
```

#### B. JavaScript 유효성 검사 함수 수정
```php
// 수정 전
function MemberXCheckField() {
    // ... 검사 로직 ...
    // return true 없음 ❌
}

// 수정 후
function MemberXCheckField() {
    // ... 검사 로직 ...
    return true;  // ✅ 추가
}
```

**이유**: 모든 검사를 통과해도 return true가 없어서 폼이 제출되지 않았음

#### C. mysqli 파라미터 오류 수정
```php
// form_ok (신규 저장) - 수정 전
$result_insert= mysqli_query($db, $dbinsert, $db);  // ❌

// form_ok (신규 저장) - 수정 후
$result_insert= mysqli_query($db, $dbinsert);  // ✅
if(!$result_insert) {
    echo "... 에러 메시지 ...";
    exit;
}

// Modify_ok (수정) - 수정 전
$result= mysqli_query($db, $query, $db);  // ❌

// Modify_ok (수정) - 수정 후
$result= mysqli_query($db, $query);  // ✅
```

### 3. mlangprintauto/cadarok/inc.php (신규 생성)
**설명 텍스트 파일 생성**

```php
<?php
$DesignMoney = "30000";
$SectionOne = "<br>카탈로그 종류별 규격과 용도를 확인하세요.<br>다양한 크기와 제본 방식 제공";
$SectionTwo = "<br>주요 용지 종류

✔ 아트지 : 고급 광택 용지
✔ 스노우지 : 부드러운 무광 용지
✔ 모조지 : 일반 용지";
$SectionTree = "<br>카탈로그 규격은 A4, A5, B5 등 다양한 크기로 제작 가능합니다.";
$SectionFour = "<br>수량별 금액을 확인하신 후 주문해주세요.";
$SectionFive = "";
$ImgOne = "";
$ImgTwo = "";
$ImgTree = "";
$ImgFour = "";
$ImgFive = "";
?>
```

## 🐛 발견된 문제점 및 원인

### 1. Short PHP tags 문제
- `<?`와 `<?=`가 PHP 7.4에서 기본적으로 비활성화된 `short_open_tag` 때문에 작동하지 않음
- 해결: `<?`를 `<?php`로, `<?=`를 `<?php echo`로 변경

### 2. Variable scope 문제
- `envelope_Script.php` 안에서 `include "../../db.php"`를 하면 새로운 스코프가 생겨서 부모 파일의 변수에 접근 불가
- 해결: 모든 `include "../../db.php"` 제거, 부모 파일의 DB connection 사용

### 3. mysqli_query 파라미터 오류
- 레거시 `mysql_query`에서는 connection을 마지막 파라미터로 넣을 수 있었지만, `mysqli_query`는 2개 파라미터만 받음
- 해결: `mysqli_query($db, $query, $db)` → `mysqli_query($db, $query)`로 수정

### 4. DB connection 조기 종료
- Script 파일에서 `mysqli_close($db)`를 호출하여 부모 파일의 DB connection을 닫아버림
- 해결: 모든 `mysqli_close($db)` 호출 제거

### 5. 변수명 혼동
- 규격(myListTreeSelect)과 종이종류(myList) 필드의 변수명이 서로 바뀌어 있었음
- 해결: 올바른 변수명으로 수정

### 6. JavaScript 유효성 검사 미완성
- `MemberXCheckField()` 함수가 `return true`를 하지 않아 폼 제출 불가
- 해결: 함수 끝에 `return true` 추가

## ✅ 수정 결과

### 수정 전:
- ❌ 구분 dropdown: 표시되지 않음
- ❌ 규격 dropdown: 표시되지 않음
- ❌ 종이종류 dropdown: 표시되지 않음
- ❌ 수정 버튼: 작동하지 않음
- ❌ PHP 에러 발생

### 수정 후:
- ✅ 구분 dropdown: 정상 표시
- ✅ 규격 dropdown: 구분 선택 시 동적 로드
- ✅ 종이종류 dropdown: 구분 선택 시 동적 로드
- ✅ 수정 버튼: 정상 작동
- ✅ DB 저장/수정: 정상 작동
- ✅ HTTP 200 OK 응답
- ✅ PHP 에러 없음

## 📊 테스트 URL

```
http://dsp1830.shop/admin/mlangprintauto/cadarok_admin.php?mode=form&code=Modify&no=XXX&Ttable=cadarok
```

## 🔄 동일한 수정이 필요한 다른 파일

이 수정 패턴은 다른 제품 관리자 페이지에도 적용 가능합니다:
- envelope (이미 수정 완료)
- cadarok (이번 수정)
- sticker
- namecard
- littleprint
- merchandisebond
- ncrflambeau
- msticker

## 📝 참고 사항

1. **envelope_admin.php 수정 내역 참조**
   - 동일한 문제와 해결 방법 적용
   - Short tag, mysqli, variable scope 문제 해결

2. **데이터베이스 구조**
   - 카탈로그는 3단계 구조: 구분 → 규격 → 종이종류
   - BigNo='0': 1단계 (구분)
   - BigNo='구분no': 2단계 (규격)
   - TreeNo='구분no': 3단계 (종이종류)

3. **FTP 업로드 정보**
   - 서버: ftp://dsp1830.shop
   - 계정: dsp1830
   - 업로드 경로: /admin/MlangPrintAuto/

## 🎯 핵심 교훈

1. **Short tag 사용 금지**: PHP 7.4+ 환경에서는 항상 `<?php` 사용
2. **Variable scope 주의**: include 시 스코프 문제 고려
3. **mysqli 파라미터 확인**: mysqli_query는 2개 파라미터만 사용
4. **DB connection 관리**: 부모 파일의 connection을 닫지 말 것
5. **JavaScript 유효성 검사**: 반드시 return true/false 명시
6. **변수명 일관성**: 필드명과 변수명의 일관성 유지

---

**작업 완료 시간**: 2025-11-08 09:40
**테스트 상태**: ✅ 정상 작동 확인
