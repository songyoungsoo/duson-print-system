# 컬러 시스템 마이그레이션 체크리스트

**프로젝트**: 두손기획인쇄 컬러 시스템 통일
**작성일**: 2025-10-11

---

## 📋 Phase 1: 통합 컬러 시스템 구축

### 파일 생성
- [ ] `css/color-system-unified.css` 파일 생성
- [ ] 브랜드 코어 컬러 변수 정의 (--dsp-primary, --dsp-accent)
- [ ] 제품 브랜드 컬러 변수 정의 (10개 제품)
- [ ] 시맨틱 컬러 변수 정의 (success, warning, error, info)
- [ ] 그레이 스케일 변수 정의 (50-900)
- [ ] 텍스트 컬러 변수 정의
- [ ] 배경 컬러 변수 정의
- [ ] 테두리 컬러 변수 정의
- [ ] 투명도 적용 컬러 변수 정의

### 하위 호환성
- [ ] design-tokens.css 호환 Alias 생성
- [ ] brand-design-system.css 호환 Alias 생성
- [ ] mlang-design-system.css 호환 Alias 생성

### 통합 테스트
- [ ] 로컬 환경에서 변수 로딩 확인
- [ ] 브라우저 개발자 도구에서 변수 값 검증
- [ ] 기존 CSS와 충돌 없는지 확인

---

## 📋 Phase 2: 제품별 CSS 마이그레이션

### 전단지 (inserted/leaflet) - 우선순위 1
- [ ] 사용 중인 컬러 값 추출 (Grep)
- [ ] 매핑 테이블 작성
- [ ] `css/leaflet-compact.css` 마이그레이션
- [ ] `mlangprintauto/inserted/styles.css` 마이그레이션
- [ ] `mlangprintauto/inserted/index.php` 인라인 스타일 제거
- [ ] 시각 검증 (전/후 스크린샷)
- [ ] 기능 테스트 (계산기, 장바구니)

### 명함 (namecard) - 우선순위 2
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/namecard-inline-styles.css` 마이그레이션
- [ ] `mlangprintauto/namecard/index.php` 확인
- [ ] 시각 검증
- [ ] 기능 테스트

### 봉투 (envelope) - 우선순위 3
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `mlangprintauto/envelope/css/envelope-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### 스티커 (sticker_new) - 우선순위 4
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/sticker-inline-styles.css` 마이그레이션
- [ ] `mlangprintauto/sticker_new/css/sticker_new-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### 포스터 (littleprint) - 우선순위 5
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/poster.css` 마이그레이션
- [ ] `mlangprintauto/littleprint/css/littleprint-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### 자석스티커 (msticker)
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/msticker-compact.css` 마이그레이션
- [ ] `mlangprintauto/msticker/css/msticker-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### 카다록 (cadarok)
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/cadarok-compact.css` 마이그레이션
- [ ] `mlangprintauto/cadarok/css/cadarok-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### 상품권 (merchandisebond)
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `css/merchandisebond-compact.css` 마이그레이션
- [ ] `mlangprintauto/merchandisebond/css/merchandisebond-inline-extracted.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

### NCR양식 (ncrflambeau)
- [ ] 사용 중인 컬러 값 추출
- [ ] 매핑 테이블 작성
- [ ] `mlangprintauto/ncrflambeau/css/ncrflambeau-compact.css` 마이그레이션
- [ ] 시각 검증
- [ ] 기능 테스트

---

## 📋 Phase 3: 공통 컴포넌트 통일

### Primary 버튼
- [ ] 브랜드 포인트 컬러 (Yellow) 기본 적용
- [ ] 제품별 동적 컬러 시스템 구축
- [ ] `css/btn-primary.css` 업데이트
- [ ] 모든 제품 페이지 적용
- [ ] hover/active 상태 테스트

### 가격 표시
- [ ] `css/unified-price-display.css` 업데이트
- [ ] `--dsp-success` 컬러 적용
- [ ] 그림자 효과 표준화
- [ ] 모든 제품 페이지 적용

### 갤러리 컴포넌트
- [ ] `css/gallery-common.css` 업데이트
- [ ] `--brand-color` 변수 기본값 설정
- [ ] 제품별 동적 컬러 적용 테스트
- [ ] 라이트박스 스타일 통일

### 폼 요소
- [ ] Focus 상태: `--dsp-primary` 적용
- [ ] Error 상태: `--dsp-error` 적용
- [ ] Success 상태: `--dsp-success` 적용
- [ ] 모든 제품 페이지 일관성 확인

### 추가 옵션 UI
- [ ] `css/additional-options.css` 업데이트
- [ ] 체크박스 accent-color 통일
- [ ] 가격 표시 컬러 통일

---

## 📋 Phase 4: 레거시 CSS 정리

### 중복 파일 제거
- [ ] `css/design-tokens.css` 백업 후 통합
- [ ] `css/mlang-design-system.css` 백업 후 통합
- [ ] Phase2 백업 파일 제거 (`*.css.phase2`)
- [ ] 오래된 백업 파일 제거 (`*.css.backup*`)

### Inline 스타일 정리
- [ ] 제품별 `*-inline-extracted.css` 검토
- [ ] 공통 스타일 추출 및 통합
- [ ] 불필요한 인라인 스타일 제거

### CSS 최적화
- [ ] 중복 정의 제거
- [ ] 사용하지 않는 규칙 제거
- [ ] 파일 크기 측정 (전/후)
- [ ] 목표: 30% 이상 감소

---

## 📋 Phase 5: 문서화 및 유지보수 가이드

### 가이드 문서 작성
- [ ] `CLAUDE_DOCS/COLOR_SYSTEM_GUIDE.md` 작성
  - [ ] CSS 변수 사용법
  - [ ] 제품별 브랜드 컬러 가이드
  - [ ] 예제 코드
- [ ] `CLAUDE_DOCS/DEVELOPER_COLOR_GUIDE.md` 작성
  - [ ] 새 제품 추가 시 컬러 정의 방법
  - [ ] 네이밍 규칙
  - [ ] 금지 사항
- [ ] `CLAUDE_DOCS/VISUAL_STYLE_GUIDE.md` 작성
  - [ ] 브랜드 컬러 팔레트
  - [ ] 제품별 컬러 조합 예시
  - [ ] 접근성 가이드라인

### CLAUDE.md 업데이트
- [ ] 컬러 시스템 섹션 추가
- [ ] CSS 로딩 순서 업데이트
- [ ] 개발자 주의사항 추가

---

## 📋 최종 검증

### 시각 검증
- [ ] 전단지 페이지 스크린샷 비교
- [ ] 명함 페이지 스크린샷 비교
- [ ] 봉투 페이지 스크린샷 비교
- [ ] 스티커 페이지 스크린샷 비교
- [ ] 포스터 페이지 스크린샷 비교
- [ ] 자석스티커 페이지 스크린샷 비교
- [ ] 카다록 페이지 스크린샷 비교
- [ ] 상품권 페이지 스크린샷 비교
- [ ] NCR양식 페이지 스크린샷 비교

### 기능 테스트
- [ ] 각 제품 계산기 동작 확인
- [ ] 장바구니 추가 기능 확인
- [ ] 갤러리 모달 동작 확인
- [ ] 파일 업로드 모달 동작 확인
- [ ] 추가 옵션 선택 기능 확인

### 크로스 브라우저 테스트
- [ ] Chrome (최신 버전)
- [ ] Firefox (최신 버전)
- [ ] Safari (최신 버전)
- [ ] Edge (최신 버전)
- [ ] 모바일 Chrome (Android)
- [ ] 모바일 Safari (iOS)

### 성능 테스트
- [ ] CSS 파일 크기 측정 (전/후)
- [ ] 페이지 로딩 속도 비교
- [ ] 렌더링 성능 측정

### 접근성 테스트
- [ ] WCAG AA 대비율 검증 (4.5:1 이상)
- [ ] 색맹 사용자 시뮬레이션 (Chrome DevTools)
- [ ] 키보드 네비게이션 확인
- [ ] 스크린 리더 테스트

---

## 📊 진행 상황 트래킹

### 주간 체크포인트
- [ ] Week 1: Phase 1 완료
- [ ] Week 2: Phase 2 (제품 1-3) 완료
- [ ] Week 3: Phase 2 (제품 4-9) + Phase 3 진행
- [ ] Week 4: Phase 4-5 완료 + 최종 검증

### 문제 발생 시 대응
- [ ] 문제 로그 작성
- [ ] 롤백 절차 확인
- [ ] 대안 검토 및 적용
- [ ] 재테스트

---

## ✅ 프로젝트 완료 조건

### 필수 조건
- [ ] 모든 제품 페이지 마이그레이션 완료
- [ ] Hardcoded 컬러 값 0개
- [ ] 브랜드 가이드라인 100% 준수
- [ ] 모든 기능 정상 동작
- [ ] 문서화 완료

### 품질 조건
- [ ] CSS 파일 크기 30% 이상 감소
- [ ] WCAG AA 접근성 기준 충족
- [ ] 크로스 브라우저 호환성 확인
- [ ] 성능 저하 없음

### 승인
- [ ] 개발팀 리뷰 완료
- [ ] 디자인팀 승인
- [ ] 프로젝트 매니저 최종 승인
- [ ] 프로덕션 배포 준비 완료

---

*마지막 업데이트: 2025-10-11*
