# Admin 품목별 PHP 7.4 호환성 수정 매뉴얼

## 개요
`inserted` 품목에서 수정한 내용을 바탕으로 나머지 7개 품목에 동일한 방식으로 PHP 7.4 호환성 문제를 해결하는 표준 매뉴얼입니다.

## 대상 품목 목록
1. ✅ **inserted** (완료)
2. 🔄 **namecard**
3. 🔄 **cadarok**
4. 🔄 **merchandisebond**
5. 🔄 **envelope**
6. 🔄 **littleprint**
7. 🔄 **ncrflambeau**
8. 🔄 **sticker_new**

## 각 품목별 수정 대상 파일들

### 핵심 파일 패턴
```
{product}_List.php          - 메인 리스트 페이지
{product}_ScriptSearch.php  - 검색 드롭다운 스크립트
{product}_NoFild.php        - 상세 정보 조회 파일
{product}_admin.php         - 관리 페이지
ListSearchBox.php           - 검색 박스 (공통)
CateList.php               - 카테고리 리스트 (공통)
```

## 단계별 수정 절차

### Phase 1: 메인 List 페이지 수정 ({product}_List.php)

#### 1.1 변수 초기화 확인 및 추가
```php
// ✅ PHP 7.4 호환: 입력 변수 초기화
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$search = $_GET['search'] ?? $_POST['search'] ?? '';
// ... 기타 필요한 변수들

// ✅ 추가 변수 초기화 (PHP 7.4 호환)
$cate = $_GET['cate'] ?? $_POST['cate'] ?? '';
$title_search = $_GET['title_search'] ?? $_POST['title_search'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
```

#### 1.2 중복 db.php include 제거
```php
// ❌ 제거 대상
<?php include"../../db.php";

// ✅ 교체 내용
<?php
// ✅ db.php는 이미 top.php에서 include되었으므로 제거
// include"../../db.php";
```

#### 1.3 GGTABLE 변수 올바르게 설정
```php
// ❌ 잘못된 방식
$GGTABLE = $GGTABLESu ?? "MlangPrintAuto_SuCate";

// ✅ 올바른 방식
$GGTABLE = "mlangprintauto_transactioncate";
```

#### 1.4 mysqli_query 매개변수 수정
```php
// ❌ 잘못된 방식 (세 번째 매개변수 $db 제거)
mysqli_query($db, $query, $db)

// ✅ 올바른 방식
mysqli_query($db, $query)
```

#### 1.5 배열 접근 방식 현대화
```php
// ❌ 잘못된 방식
$row[no]
$row[title]

// ✅ 올바른 방식
$row['no']
$row['title']
```

#### 1.6 참조 테이블 쿼리 수정
```php
// ✅ 인쇄색상 조회 - BigNo로 검색
$result_FGTwo=mysqli_query($db, "select * from $GGTABLE where Ttable='{$product}' AND BigNo='{$row['style']}' LIMIT 1");

// ✅ 종이종류 조회 - no로 검색
$result_FGFree=mysqli_query($db, "select * from $GGTABLE where Ttable='{$product}' AND no='{$row['TreeSelect']}'");

// ✅ 종이규격 조회 - no로 검색
$result_FGOne=mysqli_query($db, "select * from $GGTABLE where Ttable='{$product}' AND no='{$row['Section']}'");
```

#### 1.7 ListSearchBox.php include 개선
```php
<?php
// ✅ 검색 박스 include with error handling
if(file_exists("ListSearchBox.php")) {
    include"ListSearchBox.php";
} else {
    echo "<p style='color: red;'>검색 박스 파일이 없습니다: ListSearchBox.php</p>";
}
?>
```

### Phase 2: ScriptSearch 파일 수정 ({product}_ScriptSearch.php)

#### 2.1 PHP 선언 및 변수 초기화 추가
```php
<?php
declare(strict_types=1);

// ✅ PHP 7.4 호환: 입력 변수 초기화
$Ttable = $Ttable ?? '{product}';
$GGTABLE = 'mlangprintauto_transactioncate';
$RadOne = $_GET['RadOne'] ?? $_POST['RadOne'] ?? '';
$myListTreeSelect = $_GET['myListTreeSelect'] ?? $_POST['myListTreeSelect'] ?? '';
$myList = $_GET['myList'] ?? $_POST['myList'] ?? '';
$PHP_SELF = $_SERVER['PHP_SELF'] ?? '';
?>
```

#### 2.2 중복 db.php include 제거
```php
// ❌ 제거 대상
<?php include"../../db.php";

// ✅ 교체 내용
<?php
// ✅ db.php는 이미 {product}_List.php에서 include되었으므로 제거
// include"../../db.php";
```

#### 2.3 중복 mysqli_close 제거
```php
// ❌ 제거 대상
mysqli_close($db);

// ✅ 교체 내용
// ✅ mysqli_close는 {product}_List.php에서 처리하므로 제거
// mysqli_close($db);
```

#### 2.4 중복 FORM 태그 제거
```php
// ❌ 제거 대상
<FORM NAME="myForm" method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?=$PHP_SELF?>'>

// ✅ 교체 내용
<!-- ✅ FORM 태그는 ListSearchBox.php에서 정의되므로 제거 -->
```

#### 2.5 mysqli_query 매개변수 및 배열 접근 수정
```php
// ❌ 잘못된 방식
mysqli_query($db, $query, $db)
$row[no]
echo("'$row_Two[title]',");

// ✅ 올바른 방식
mysqli_query($db, $query)
$row['no']
echo("'" . htmlspecialchars($row_Two['title']) . "',");
```

#### 2.6 데이터 쿼리 수정
```php
// ✅ 올바른 데이터 쿼리 (품목별로 BigNo 값 확인 필요)
$result= mysqli_query($db, "select * from $GGTABLE where Ttable='$Ttable' and (BigNo='0' OR BigNo='802') order by no asc");
```

### Phase 3: NoFild 파일 수정 ({product}_NoFild.php)

#### 3.1 mysqli_query 매개변수 수정
```php
// ❌ 잘못된 방식
$result= mysqli_query($db, "select * from MlangPrintAuto_{product} where no='$no'",$db);

// ✅ 올바른 방식
$result= mysqli_query($db, "select * from MlangPrintAuto_{product} where no='$no'");
```

#### 3.2 배열 접근 방식 현대화 및 안전성 개선
```php
// ❌ 잘못된 방식
$view_style="$row[style]";

// ✅ 올바른 방식
$view_style = $row['style'] ?? '';
```

### Phase 4: 공통 파일 수정 (한번만 수정)

#### 4.1 ListSearchBox.php
```php
<FORM NAME="myForm" method='post' action='<?=$PHP_SELF ?? $_SERVER['PHP_SELF']?>'>

<font color=red>*</font> 신 자료 입력시 동등한 자료가 있는지 필히 확인을 미리해 주시기 바랍니다.<BR>
<font color=red>*</font> 신 자료 입력시 1000 개 수량 밑에 250 등.. 이하의 수량이 밑으로 가면 절대 안되요<BR>
<font color=red>*</font> 자료 LIST 에서 신 자료가 최신으로 호출 됩니다.<BR>

<?php
$Ttable = $TIO_CODE ?? 'inserted';
if(file_exists("${TIO_CODE}_ScriptSearch.php")) {
    include"${TIO_CODE}_ScriptSearch.php";
} else {
    echo "<p style='color: red;'>검색 스크립트 파일이 없습니다: ${TIO_CODE}_ScriptSearch.php</p>";
}
?>
<INPUT TYPE="hidden" name='search' value='yes'>
<INPUT TYPE="submit" value=' 검 색 '>
</FORM>
```

## 품목별 데이터 구조 확인 방법

각 품목마다 `mlangprintauto_transactioncate` 테이블의 데이터 구조가 다를 수 있습니다.

### 데이터 구조 확인 쿼리
```sql
-- 품목별 데이터 확인
SELECT no, Ttable, BigNo, TreeNo, title
FROM mlangprintauto_transactioncate
WHERE Ttable = '{product}'
ORDER BY no ASC;

-- 카테고리 구조 확인
SELECT DISTINCT BigNo FROM mlangprintauto_transactioncate WHERE Ttable = '{product}';
```

### 일반적인 데이터 패턴
- **Main Category**: `BigNo='0'` 또는 특정 값
- **Sub Categories**: `TreeNo`가 main category의 `no` 값
- **Details**: `BigNo`가 main category의 `no` 값

## 테스트 절차

### 1. 각 품목별 테스트 파일 생성
```php
// test_{product}.php
<?php
$TIO_CODE = "{product}";
// ... 테스트 코드
?>
```

### 2. 브라우저 테스트
1. `http://localhost/admin/MlangPrintAuto/test_{product}.php`
2. `http://localhost/admin/MlangPrintAuto/{product}_List.php`

### 3. 확인 사항
- [ ] 검색 바 정상 표시
- [ ] 드롭다운 데이터 정상 로드
- [ ] 참조 데이터 정상 표시 (인쇄색상, 종이종류, 종이규격)
- [ ] PHP 오류 없음

## 주의사항

1. **백업**: 수정 전 각 파일을 백업
2. **데이터 확인**: 각 품목별로 `transactioncate` 테이블의 데이터 구조 확인
3. **점진적 수정**: 한 품목씩 완전히 완료한 후 다음 품목으로 진행
4. **테스트**: 각 단계마다 브라우저에서 동작 확인

## 다음 품목 수정 우선순위

1. **namecard** - 명함 (사용 빈도 높음)
2. **sticker_new** - 스티커 (사용 빈도 높음)
3. **cadarok** - 카다록
4. **envelope** - 봉투
5. **merchandisebond** - 상품권
6. **littleprint** - 포스터
7. **ncrflambeau** - NCR양식

## 작업 진행 상황 추적

### 체크리스트 템플릿
```
품목: {product}
- [ ] {product}_List.php 수정
- [ ] {product}_ScriptSearch.php 수정
- [ ] {product}_NoFild.php 수정
- [ ] 테스트 파일 생성
- [ ] 브라우저 테스트 완료
- [ ] 오류 없음 확인
```

---

**작성일**: 2025년 1월 25일
**기준 품목**: inserted (완료)
**작성자**: Claude Code Assistant