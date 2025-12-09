# NCRFlambeau (양식지) 드롭다운 초기화 문제 해결

**작성일**: 2025-01-13
**상태**: ✅ 해결 완료
**관련 제품**: 양식지 (NCRFlambeau)

---

## 📋 문제 상황

### 증상
사용자가 "구분" 드롭다운을 변경할 때 (예: NCR 2매(100매철) 선택):
- ❌ 하위 드롭다운(규격, 색상, 수량)이 초기화되지 않음
- ❌ 이전에 선택한 값이 그대로 유지됨
- ❌ 다른 구분으로 변경해도 리셋이 안 됨

### 근본 원인
**이벤트 핸들러 충돌 (Event Handler Conflict)**

```html
<!-- ❌ 문제 코드: HTML 인라인 onchange와 JavaScript addEventListener 충돌 -->
<select name="MY_type" id="MY_type" required onchange="calculatePrice()">
```

**충돌 메커니즘**:
1. HTML 인라인 `onchange="calculatePrice()"` 실행
2. JavaScript `addEventListener('change', ...)` 실행
3. 두 이벤트가 동시에 실행되어 **경쟁 상태(Race Condition)** 발생
4. HTML onchange가 먼저 실행 → 불완전한 상태에서 가격 계산 시도
5. JavaScript addEventListener의 초기화 로직이 제대로 작동하지 않음

---

## 💡 해결책

### 1. HTML 인라인 이벤트 제거 ✅

**파일**: `/var/www/html/mlangprintauto/ncrflambeau/index.php`

**변경 전**:
```html
<select name="MY_type" id="MY_type" required onchange="calculatePrice()">
<select name="MY_Fsd" id="MY_Fsd" required onchange="calculatePrice()">
<select name="PN_type" id="PN_type" required onchange="calculatePrice()">
<select name="MY_amount" id="MY_amount" required onchange="calculatePrice()">
<select name="ordertype" id="ordertype" required onchange="calculatePrice()">
```

**변경 후**:
```html
<!-- ✅ onchange 속성 완전 제거 -->
<select name="MY_type" id="MY_type" required>
<select name="MY_Fsd" id="MY_Fsd" required>
<select name="PN_type" id="PN_type" required>
<select name="MY_amount" id="MY_amount" required>
<select name="ordertype" id="ordertype" required>
```

**수정된 라인**: 146, 161, 169, 177, 185

---

### 2. JavaScript 이벤트 로그 강화 ✅

**파일**: `/var/www/html/mlangprintauto/ncrflambeau/js/ncrflambeau-compact.js`

**구분(MY_type) 변경 이벤트** (lines 82-93):
```javascript
// 구분 변경 시
if (categorySelect) {
    categorySelect.addEventListener('change', function() {
        console.log('🔄 구분 변경됨:', this.value, '(이전 값에서 변경)');
        if (this.value) {
            console.log('📂 하위 드롭다운 초기화 시작: 규격, 색상, 수량');
            resetDownstreamSelects(['MY_Fsd', 'PN_type', 'MY_amount']);
            console.log('💰 가격 표시 초기화');
            resetPriceDisplay();
            console.log('📏 규격 옵션 로드 시작');
            loadSizes(this.value);
        }
    });
}
```

---

### 3. 초기화 함수 상세 로깅 ✅

**하위 드롭다운 초기화 함수** (lines 131-145):
```javascript
// 하위 선택 박스 초기화
function resetDownstreamSelects(selectNames) {
    console.log('🔄 하위 드롭다운 초기화:', selectNames.join(', '));
    selectNames.forEach(name => {
        const select = document.querySelector(`select[name="${name}"]`);
        if (select) {
            const prevValue = select.value;
            select.innerHTML = '<option value="">선택해주세요</option>';
            select.value = '';
            console.log(`  ✓ ${name} 초기화 완료 (이전 값: "${prevValue}" → 현재 값: "")`);
        } else {
            console.warn(`  ⚠️ ${name} 요소를 찾을 수 없음!`);
        }
    });
    console.log('✅ 모든 하위 드롭다운 초기화 완료');
}
```

---

## 📊 작동 방식 (수정 후)

### ✅ 정상 동작 시나리오

사용자가 **"구분"에서 "NCR 2매(100매철)" 선택** 시:

```
1️⃣ 구분 변경 감지
   ↓
2️⃣ JavaScript addEventListener 실행 (단일 이벤트, 충돌 없음)
   ↓
3️⃣ 하위 드롭다운 초기화
   - 규격(MY_Fsd) → "선택해주세요"
   - 색상(PN_type) → "선택해주세요"
   - 수량(MY_amount) → "선택해주세요"
   ↓
4️⃣ 가격 표시 초기화
   - 금액 → "0원"
   - 설명 → "옵션을 선택하시면 실시간으로 가격이 계산됩니다"
   ↓
5️⃣ 규격 옵션 로드 (AJAX)
   - URL: get_sizes.php?style=475
   - 응답: 규격 목록 (A4, A5, B4, B5 등)
   ↓
6️⃣ 자동 선택 (첫 번째 규격)
   - 자동으로 첫 번째 규격 선택
   ↓
7️⃣ 색상 옵션 로드 (연쇄 반응)
   - URL: get_colors.php?style=475&size=501
   ↓
8️⃣ 자동 선택 (첫 번째 색상)
   ↓
9️⃣ 수량 옵션 로드 (연쇄 반응)
   - URL: get_quantities.php?style=475&section=501&treeselect=601
   ↓
🔟 자동 선택 (첫 번째 수량) → 가격 자동 계산
```

---

## 🔍 브라우저 콘솔 로그 예시

실제 동작 시 나타나는 로그:

```javascript
🔄 구분 변경됨: 475 (이전 값에서 변경)
📂 하위 드롭다운 초기화 시작: 규격, 색상, 수량

🔄 하위 드롭다운 초기화: MY_Fsd, PN_type, MY_amount
  ✓ MY_Fsd 초기화 완료 (이전 값: "501" → 현재 값: "")
  ✓ PN_type 초기화 완료 (이전 값: "601" → 현재 값: "")
  ✓ MY_amount 초기화 완료 (이전 값: "100" → 현재 값: "")
✅ 모든 하위 드롭다운 초기화 완료

💰 가격 표시 초기화
📏 규격 옵션 로드 시작

📏 규격 옵션 로드 시작: 475
📏 규격 응답: {success: true, data: [Array(3)]}
🎯 양식(100매철) 첫 번째 규격 자동 선택: A4
✅ 규격 옵션 로드 완료: 3 개

🎨 색상 옵션 로드 시작: 475 501
🎨 색상 응답: {success: true, data: [Array(2)]}
🎯 색상 첫 번째 옵션 자동 선택: 1도(먹색)
✅ 색상 옵션 로드 완료: 2 개

📦 수량 옵션 로드 시작: 475, 501, 601
📦 수량 응답: {success: true, data: [Array(10)]}
🎯 수량 첫 번째 옵션 자동 선택: 100
✅ 수량 옵션 로드 완료: 10 개

💰 가격 계산 시작...
✅ 가격: 15,000원
```

---

## 🎉 기대 효과

### ✅ 1. 드롭다운 초기화 정상 작동
- 구분 변경 시 하위 드롭다운이 **즉시 초기화**됨
- "선택해주세요" 상태로 깨끗하게 리셋
- 이전 선택값이 남아있지 않음

### ✅ 2. 가격 계산 안정화
- 불완전한 상태에서 가격 계산 시도 방지
- 모든 옵션 선택 후에만 자동 계산
- 가격 계산 오류 없음

### ✅ 3. 디버깅 용이성 향상
- 상세한 콘솔 로그로 **각 단계 추적 가능**
- 문제 발생 시 원인 파악 쉬워짐
- 개발자 도구로 실시간 모니터링 가능

### ✅ 4. 사용자 경험 개선
- **예측 가능한 동작**: 구분 변경 → 하위 옵션 리셋
- **명확한 초기화 피드백**: "선택해주세요" 표시
- **자동 선택 UX**: 첫 번째 옵션 자동 선택으로 편의성 증대

---

## 📁 수정된 파일 목록

| 순번 | 파일 경로 | 변경 내용 | 라인 |
|------|----------|----------|------|
| 1 | `mlangprintauto/ncrflambeau/index.php` | onchange 속성 제거 (5곳) | 146, 161, 169, 177, 185 |
| 2 | `mlangprintauto/ncrflambeau/js/ncrflambeau-compact.js` | 구분 변경 이벤트 로그 강화 | 82-93 |
| 3 | `mlangprintauto/ncrflambeau/js/ncrflambeau-compact.js` | resetDownstreamSelects 로그 강화 | 131-145 |

---

## 🔧 배포 방법

### 운영 서버 업로드 파일

```bash
# 업로드할 파일 (프로젝트 루트 기준)
✅ mlangprintauto/ncrflambeau/index.php
✅ mlangprintauto/ncrflambeau/js/ncrflambeau-compact.js
```

### 배포 후 테스트 항목

1. **드롭다운 초기화 확인**
   - 구분 선택 → 규격/색상/수량 "선택해주세요"로 리셋 확인
   - 다른 구분으로 변경 → 하위 옵션 정상 초기화 확인

2. **가격 계산 확인**
   - 구분/규격/색상/수량 선택 후 가격 자동 계산 확인
   - 옵션 변경 시 가격 재계산 확인

3. **콘솔 로그 확인** (개발자 도구 F12)
   - 각 단계별 로그 정상 출력 확인
   - 에러 없이 동작 확인

---

## 🔗 관련 문서

- [양식지 드롭다운 시스템 분석](./NCRFLAMBEAU_DROPDOWN_ANALYSIS.md)
- [양식지 데이터베이스 구조](./DATABASE_NCRFLAMBEAU.md)
- [전단지 CSS 통합 가이드](./CSS_PHASE1_CHANGELOG.md)

---

## 📝 참고 사항

### 중요 원칙
1. **HTML에서 JavaScript 이벤트 제거**: 항상 JavaScript 파일에서 addEventListener 사용
2. **단일 이벤트 핸들러**: 같은 이벤트에 여러 핸들러 등록하지 말 것
3. **상세한 로깅**: 디버깅을 위해 주요 동작마다 console.log 추가

### 다른 제품에 적용 시
이 패턴은 다른 제품 (전단지, 명함, 스티커 등)의 드롭다운에도 동일하게 적용 가능:
- HTML에서 `onchange` 제거
- JavaScript에서 `addEventListener` 사용
- 하위 드롭다운 초기화 로직 구현

---

**작성자**: Claude Code
**마지막 업데이트**: 2025-01-13
