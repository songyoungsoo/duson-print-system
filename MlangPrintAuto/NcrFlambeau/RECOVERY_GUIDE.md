# 🚨 NcrFlambeau 컴팩트 버전 복구 가이드

## 📊 계산 프로그램 실패 시 복구

### **1단계: 디버그 페이지로 문제 확인**
```
URL: http://localhost/MlangPrintAuto/NcrFlambeau/debug_ncrflambeau.php
```

**확인 사항:**
- [ ] 데이터베이스 연결 상태
- [ ] 필요한 테이블 존재 여부
- [ ] 카테고리 데이터 존재
- [ ] 가격 데이터 샘플 확인

### **2단계: AJAX API 개별 테스트**
```
규격 옵션: http://localhost/MlangPrintAuto/NcrFlambeau/get_sizes.php?style=475
색상 옵션: http://localhost/MlangPrintAuto/NcrFlambeau/get_colors.php?style=475
수량 옵션: http://localhost/MlangPrintAuto/NcrFlambeau/get_quantities.php?style=475&section=484&treeselect=505
```

**정상 응답 예시:**
```json
{
    "success": true,
    "message": "조회 완료",
    "data": [
        {"no": "484", "title": "계약서(A4)"}
    ]
}
```

### **3단계: 가격 계산 API 테스트**
브라우저 개발자 도구(F12) → Network 탭에서 확인:
```
POST: calculate_price_ajax.php
데이터: MY_type=475&MY_Fsd=484&PN_type=505&MY_amount=60&ordertype=print
```

### **4단계: 일반적인 문제 해결**

#### **A. JSON 파싱 오류**
**증상:** "서버 응답 처리 중 오류"
**원인:** PHP Notice/Warning이 JSON에 섞임
**해결:**
```php
// 모든 AJAX 파일 상단에 확인
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
```

#### **B. 데이터베이스 연결 오류**
**증상:** "데이터베이스 연결 실패"
**해결:**
```php
// db.php 파일 확인
$db = mysqli_connect("localhost", "username", "password", "database");
if (!$db) {
    die("연결 실패: " . mysqli_connect_error());
}
```

#### **C. 테이블 데이터 없음**
**증상:** "해당 조건의 가격 정보를 찾을 수 없습니다"
**해결:**
```sql
-- 데이터 확인
SELECT * FROM MlangPrintAuto_ncrflambeau LIMIT 5;
SELECT * FROM MlangPrintAuto_transactionCate WHERE Ttable='NcrFlambeau';
```

## 🖼️ 갤러리 실패 시 복구

### **1단계: 갤러리 API 테스트**
```
URL: http://localhost/MlangPrintAuto/NcrFlambeau/get_ncrflambeau_images.php
```

**정상 응답:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "양식지 샘플 1",
            "image_path": "/uploads/portfolio/sample1.jpg",
            "thumbnail_path": "/uploads/portfolio/thumb/sample1.jpg"
        }
    ]
}
```

### **2단계: 이미지 파일 확인**
```
경로: C:\xampp\htdocs\uploads\portfolio\
썸네일: C:\xampp\htdocs\uploads\portfolio\thumb\
```

### **3단계: 갤러리 JavaScript 오류 확인**
브라우저 개발자 도구(F12) → Console 탭:
```javascript
// 오류 예시
Uncaught TypeError: Cannot read property 'forEach' of undefined
```

### **4단계: 갤러리 복구 방법**

#### **A. 이미지 데이터 없음**
**해결:** 기본 샘플 이미지 생성
```php
// get_ncrflambeau_images.php에서 기본 이미지 제공
$default_images = [
    [
        'id' => 1,
        'title' => '양식지 샘플 1',
        'image_path' => '/images/samples/ncrflambeau_sample1.jpg',
        'thumbnail_path' => '/images/samples/thumb/ncrflambeau_sample1.jpg'
    ]
];
```

#### **B. JavaScript 애니메이션 오류**
**해결:** 애니메이션 함수 초기화
```javascript
// ncrflambeau-compact.js 확인
function animate() {
    const zoomBox = document.getElementById('zoomBox');
    if (!zoomBox) {
        requestAnimationFrame(animate);
        return;
    }
    // ... 애니메이션 로직
}
```

## 🔄 긴급 복구 방법

### **방법 1: 기존 버전으로 즉시 롤백**
```php
// index_compact.php 상단에 추가
<?php
header("Location: index.php");
exit;
?>
```

### **방법 2: 네비게이션 링크 변경**
```php
// includes/nav.php에서
<a href="/MlangPrintAuto/NcrFlambeau/index.php">📋 양식지</a>
```

### **방법 3: 컴팩트 버전 비활성화**
```php
// index_compact.php 상단에 추가
<?php
echo "<h1>시스템 점검 중입니다.</h1>";
echo "<a href='index.php'>기존 버전 사용하기</a>";
exit;
?>
```

## 🛠️ 예방적 유지보수

### **주간 점검 사항**
- [ ] 디버그 페이지 정상 작동 확인
- [ ] 모든 AJAX API 응답 테스트
- [ ] 갤러리 이미지 로딩 확인
- [ ] 가격 계산 정확성 검증

### **월간 점검 사항**
- [ ] 데이터베이스 백업
- [ ] 로그 파일 정리
- [ ] 성능 모니터링
- [ ] 사용자 피드백 검토

### **백업 파일 목록**
```
MlangPrintAuto/NcrFlambeau/
├── index_original.php          # 원본 백업
├── index_compact_backup.php    # 컴팩트 버전 백업
├── js/ncrflambeau-compact_backup.js
├── css/ncrflambeau-compact_backup.css
└── RECOVERY_GUIDE.md          # 이 가이드
```

## 📞 문제 해결 체크리스트

### **계산 프로그램 문제**
1. [ ] 디버그 페이지 확인
2. [ ] AJAX API 개별 테스트
3. [ ] 브라우저 콘솔 오류 확인
4. [ ] 데이터베이스 데이터 확인
5. [ ] PHP 오류 로그 확인

### **갤러리 문제**
1. [ ] 갤러리 API 응답 확인
2. [ ] 이미지 파일 존재 확인
3. [ ] JavaScript 오류 확인
4. [ ] CSS 스타일 확인
5. [ ] 애니메이션 함수 확인

### **전체 시스템 문제**
1. [ ] 서버 상태 확인
2. [ ] 데이터베이스 연결 확인
3. [ ] 파일 권한 확인
4. [ ] 기존 버전으로 롤백
5. [ ] 사용자 공지

---

**작성일**: 2025년 8월 14일  
**업데이트**: 문제 발생 시 즉시 업데이트  
**긴급 연락**: 개발팀  
**백업 위치**: MlangPrintAuto/NcrFlambeau/backups/