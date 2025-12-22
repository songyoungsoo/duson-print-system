# 🔍 파일 업로드 문제 해결 보고서

**날짜**: 2025-11-19
**문제**: 폴더는 생성되지만 파일이 저장되지 않음
**영향 제품**: inserted, envelope, littleprint, cadarok

---

## 📊 증상 분석

### 관찰된 현상
```
/www/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120/1763537333
```
- ✅ 폴더 생성됨 (타임스탬프 포함)
- ❌ 파일 없음 (빈 디렉토리)
- ❌ 관리자 페이지: "업로드된 파일이 없습니다"

### 진단 결론
폴더 생성 = PHP 실행됨 → 문제는 `$_FILES` 데이터 또는 JavaScript 전송에 있음

---

## 🔧 이미 수정된 사항 (2025-11-19)

### JavaScript 코드 수정 완료

**4개 제품 모두 수정 완료**:
1. **envelope/index.php** (Line 513-515)
2. **littleprint/index.php** (Line 402-404)
3. **cadarok/index.php** (Line 510-512)
4. **inserted/index.php** (Line 634-636)

**수정 내용**:
```javascript
// ❌ BEFORE (2가지 버그)
uploadedFiles.forEach((file, index) => {
    formData.append("uploaded_files[" + index + "]", file);
});

// ✅ AFTER (완전 수정)
uploadedFiles.forEach((fileObj, index) => {
    formData.append("uploaded_files[]", fileObj.file);
});
```

**버그**:
1. ❌ `uploaded_files[index]` → PHP 배열 인식 실패
2. ❌ `file` → 래퍼 객체 전송 (실제 File 아님)

**수정**:
1. ✅ `uploaded_files[]` → PHP 자동 배열 인식
2. ✅ `fileObj.file` → 실제 File 객체 전송

### FTP 업로드 완료

모든 파일이 웹 서버에 정상 배포됨:
```bash
curl -s --ftp-pasv ftp://..../envelope/index.php | strings | grep "fileObj.file"
# ✅ formData.append("uploaded_files[]", fileObj.file);

curl -s --ftp-pasv ftp://..../littleprint/index.php | strings | grep "fileObj.file"
# ✅ formData.append("uploaded_files[]", fileObj.file);

curl -s --ftp-pasv ftp://..../cadarok/index.php | strings | grep "fileObj.file"
# ✅ formData.append("uploaded_files[]", fileObj.file);

curl -s --ftp-pasv ftp://..../inserted/index.php | strings | grep "fileObj.file"
# ✅ formData.append("uploaded_files[]", fileObj.file);
```

---

## 🎯 다음 단계: 실제 테스트 필요

### ⚠️ 중요: 브라우저 캐시 문제

PHP 파일이 업데이트되었지만 **브라우저가 오래된 JavaScript를 캐시**하고 있을 수 있습니다.

#### 브라우저 캐시 클리어 방법:

**크롬/엣지**:
1. `Ctrl + Shift + Delete`
2. "캐시된 이미지 및 파일" 체크
3. "데이터 삭제" 클릭

**또는 강력 새로고침**:
- `Ctrl + F5` (Windows)
- `Cmd + Shift + R` (Mac)

### 🧪 디버깅 테스트 페이지

**테스트 페이지 URL**:
```
http://dsp1830.shop/mlangprintauto/inserted/test_upload.html
```

**테스트 방법**:
1. 위 URL 접속
2. 파일 2-3개 선택
3. "TEST 1: 기본 업로드" 버튼 클릭
4. "TEST 2: 모달 방식" 버튼 클릭
5. 결과 확인 (브라우저 개발자 도구 콘솔도 확인)

**예상 결과**:
```json
{
  "success": true,
  "debug": {
    "has_uploaded_files": true,
    "file_count": 2,
    "files_list": [
      {
        "name": "test1.jpg",
        "size": 12345,
        "tmp_exists": true,
        "error": 0
      },
      {
        "name": "test2.png",
        "size": 23456,
        "tmp_exists": true,
        "error": 0
      }
    ]
  }
}
```

**만약 실패한다면**:
- `has_uploaded_files`: false → JavaScript가 파일을 전송하지 못함
- `tmp_exists`: false → 서버가 파일을 받지 못함
- `error`: 0이 아님 → 업로드 오류 코드 확인

---

## 🔍 잠재적 원인 분석

### 1️⃣ 브라우저 캐시 (가능성: 70%)
**증상**: 수정된 JavaScript가 로드되지 않음
**해결**: 강력 새로고침 또는 캐시 클리어

### 2️⃣ PHP OpCache (가능성: 20%)
**증상**: 서버가 오래된 PHP 파일 실행
**해결**: 5-10분 대기 (자동 클리어) 또는 서버 재시작

### 3️⃣ upload_modal.js 미업데이트 (가능성: 5%)
**증상**: 파일 래퍼 객체 구조가 다름
**확인**: 로컬 upload_modal.js가 최신인지 확인

### 4️⃣ 서버 권한 문제 (가능성: 3%)
**증상**: PHP가 ImgFolder에 쓰기 권한 없음
**확인**: PHP 에러 로그 확인

### 5️⃣ 파일 크기 제한 (가능성: 2%)
**증상**: 큰 파일 업로드 시 실패
**확인**: php.ini 설정 (upload_max_filesize, post_max_size)

---

## 📋 체크리스트

### 즉시 실행
- [ ] 브라우저 캐시 완전 클리어
- [ ] http://dsp1830.shop/mlangprintauto/inserted/test_upload.html 테스트
- [ ] 브라우저 개발자 도구 Network 탭 확인
- [ ] 브라우저 콘솔에서 JavaScript 오류 확인

### 테스트 후 확인
- [ ] test_upload.html에서 TEST 1, TEST 2 모두 성공하는지
- [ ] 실제 제품 페이지에서 파일 업로드 시도
- [ ] ImgFolder 디렉토리에 파일 저장 확인
- [ ] 관리자 페이지에서 파일 목록 표시 확인

### 여전히 실패 시
- [ ] PHP 에러 로그 확인 (서버 관리자에게 요청)
- [ ] 브라우저 Network 탭에서 실제 전송 데이터 확인
- [ ] upload_modal.js를 웹 서버에 재업로드

---

## 🎓 학습 포인트

### 이번 버그의 교훈

1. **FormData 배열 표기법**
   - PHP는 `uploaded_files[]`만 배열로 인식
   - `uploaded_files[0]`, `uploaded_files[1]`은 별도 키로 인식

2. **File 객체 래퍼 패턴**
   - `upload_modal.js`가 `{id, file, name, size, type}` 구조 생성
   - FormData에는 `fileObj.file` (실제 File)을 전송해야 함
   - 래퍼 객체 전체를 전송하면 PHP가 인식 못함

3. **디버깅 접근법**
   - 폴더 생성 = PHP 실행 확인
   - 파일 없음 = JavaScript 또는 `$_FILES` 문제
   - 체계적 격리 테스트로 원인 특정

---

## 📞 추가 지원

문제가 지속되면 다음 정보와 함께 요청:

1. **브라우저 콘솔 로그** (F12 → Console 탭)
2. **Network 탭 스크린샷** (add_to_basket.php 요청의 Payload)
3. **test_upload.html 테스트 결과** (JSON 응답 전체)
4. **PHP 에러 로그** (가능하다면)

---

**작성자**: Claude Code
**마지막 업데이트**: 2025-11-19 17:30
