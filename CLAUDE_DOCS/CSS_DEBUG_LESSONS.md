# CSS 디버깅 교훈록

## 교훈 1: 시각적 중앙 정렬 문제 (2026-01-17)

### 증상
- `text-align: center` 적용됨
- computed style도 `center`
- 하지만 시각적으로 왼쪽 정렬

### 원인
```css
.page-title {
    margin: 0 -20px var(--page-title-margin-bottom);  /* ← 이게 원인 */
    text-align: center;  /* 이건 맞음 */
}
```

### 놓친 이유
1. **증상만 보고 원인 추적 안 함**
   - `h1 left: -10px` 단서를 봤지만 "왜?"를 묻지 않음

2. **내용물 정렬에만 집착**
   - `text-align`, `justify-content` 등 내용물 정렬만 확인
   - 컨테이너 위치(margin, padding)를 점검 안 함

3. **레이어 구분 실패**
   ```
   시각적 중앙 = 컨테이너 위치(margin/padding) + 내용물 정렬(text-align/flex)
   ```

### 올바른 디버깅 순서
1. **컨테이너 위치 확인** → margin, padding, left, right
2. **컨테이너 크기 확인** → width, max-width
3. **내용물 정렬 확인** → text-align, justify-content, align-items

---

## 교훈 2: !important 남용 금지

### 왜 !important를 쓰게 되는가?
1. **빠른 해결 유혹** - 원인 분석 없이 "일단 되게" 하려는 심리
2. **게으른 디버깅** - CSS cascade 추적 대신 sledgehammer 사용
3. **증상 치료** - 근본 원인 대신 표면 증상만 해결

### !important의 문제
- CSS 우선순위 체계 파괴
- 나중에 수정 불가능한 코드 생성
- 다른 !important로만 덮어쓸 수 있음 → 악순환

### 올바른 접근
1. **왜 스타일이 안 먹히는지** 원인 추적
2. **어떤 규칙이 덮어쓰는지** 개발자도구로 확인
3. **specificity 조정** 또는 **로드 순서 조정**으로 해결

---

## CSS 디버깅 체크리스트

### 스타일이 안 먹힐 때
- [ ] 개발자도구에서 해당 요소의 모든 CSS 규칙 확인
- [ ] 취소선 그어진 규칙 → 무엇이 덮어쓰는지 확인
- [ ] computed 탭에서 실제 적용된 값 확인

### 위치/정렬 문제일 때
- [ ] 해당 요소의 margin, padding 확인
- [ ] 부모 요소의 display, flex/grid 속성 확인
- [ ] 부모 요소의 width, max-width 확인
- [ ] position 속성 (relative, absolute) 확인

### 해결책 선택 순서
1. 기존 CSS 수정 (가장 좋음)
2. 더 구체적인 선택자 사용
3. CSS 로드 순서 조정
4. **!important는 절대 금지**

---

## FTP 경로 주의사항

### dsp1830.shop 서버
- FTP 루트 = 웹 루트
- ❌ `ftp://dsp1830.shop/html/css/` (잘못됨)
- ✅ `ftp://dsp1830.shop/css/` (올바름)

---

*Last Updated: 2026-01-17*
