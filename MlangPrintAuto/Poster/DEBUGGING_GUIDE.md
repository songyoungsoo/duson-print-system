# 🔍 수량 드롭다운 디버깅 가이드

## 문제 해결 방법

### 1단계: 메인 페이지에서 개발자 도구 확인
1. **브라우저에서 접속**: `http://localhost/MlangPrintAuto/LittlePrint/index_compact.php`
2. **개발자 도구 열기**: `F12` 또는 우클릭 → 검사
3. **Console 탭으로 이동**
4. **포스터 종류 선택** → 콘솔 로그 확인

### 2단계: 디버깅 도구 사용
**전용 디버깅 페이지**: `http://localhost/MlangPrintAuto/LittlePrint/debug_quantity_issue.php`

이 페이지에서:
- 실시간 로그 확인
- 수동 테스트 실행
- API 직접 호출 테스트

### 3단계: 예상되는 콘솔 로그 흐름

**정상 동작 시:**
```
🔍 loadPaperTypes 호출됨, style: 590
📡 용지 재질 API 호출: get_paper_types.php?style=590
📡 용지 재질 응답 상태: 200
📊 용지 재질 데이터: {success: true, data: [...]}
🔧 updateSelectWithOptions 호출됨: {hasElement: true, optionsLength: 9, defaultText: "용지 재질을 선택해주세요"}
📝 옵션 1: 604 = 120아트/스노우
📝 옵션 2: 605 = 150아트/스노우
...
✅ 9개 옵션이 Section에 추가됨
```

**용지 재질 선택 후:**
```
🔍 loadQuantities 호출됨
Elements found: {typeSelect: true, paperSelect: true, sideSelect: true, quantitySelect: true}
📊 현재 값들: {style: "590", section: "604", potype: "1"}
📡 API 호출: get_quantities.php?style=590&section=604&potype=1
📡 응답 상태: 200
📊 수량 데이터: {success: true, data: [...]}
🔧 updateSelectWithOptions 호출됨: {hasElement: true, optionsLength: 4, defaultText: "수량을 선택해주세요"}
✅ 4개 옵션이 MY_amount에 추가됨
```

### 4단계: 문제별 해결책

#### 문제 1: "Elements found" 에서 false가 나오는 경우
**원인**: HTML 요소 ID가 잘못되었거나 DOM이 로드되지 않음
**해결**: `index_compact.php`에서 요소 ID 확인

#### 문제 2: API 호출은 되지만 data가 비어있는 경우
**원인**: 데이터베이스에 해당 조합의 데이터가 없음
**해결**: `test_api.php`에서 실제 데이터 확인

#### 문제 3: 네트워크 오류가 발생하는 경우
**원인**: API 파일 경로 문제 또는 서버 오류
**해결**: 
- `get_quantities.php` 파일 존재 확인
- 직접 브라우저에서 API URL 접속 테스트

### 5단계: 데이터 확인

**현재 사용 가능한 테스트 데이터:**
- **style**: 590 (소량포스터)
- **section**: 604, 605, 606, 607, 608, 609, 679, 680, 958
- **potype**: 1 (단면), 2 (양면)
- **quantity**: 10, 20, 50, 100

### 6단계: 즉시 해결 방법

만약 문제가 계속되면, 아래 스크립트를 콘솔에서 직접 실행:

```javascript
// 수동으로 수량 옵션 추가
const quantitySelect = document.getElementById('MY_amount');
quantitySelect.innerHTML = `
    <option value="">수량을 선택해주세요</option>
    <option value="10">10매</option>
    <option value="20">20매</option>
    <option value="50">50매</option>
    <option value="100">100매</option>
`;
```

## 연락처
문제가 지속되면 이 가이드의 결과를 개발자에게 전달해주세요.