# 레거시 파일 마이그레이션 로그

## 📅 마이그레이션 정보

- **실행 날짜**: 2025-12-25
- **실행자**: Claude Sonnet 4.5
- **Phase**: Phase 2 - 관리자 시스템 통합
- **목표**: 27개 레거시 파일 → 5개 통합 시스템

## 📦 이동된 파일

### 관리자 파일 (27개)

#### 명함 (3개)
- namecard_admin.php
- namecard_list.php
- namecard_nofild.php

#### 전단지 (3개)
- inserted_admin.php
- inserted_list.php
- inserted_nofild.php

#### 봉투 (3개)
- envelope_admin.php
- envelope_list.php
- envelope_nofild.php

#### 스티커 (3개)
- sticker_admin.php
- sticker_list.php
- sticker_nofild.php

#### 카다록 (6개)
- cadarok_admin.php
- cadarok_list.php
- cadarok_nofild.php
- cadaroktwo_admin.php
- cadaroktwo_list.php
- cadaroktwo_nofild.php

#### 포스터 (3개)
- littleprint_admin.php
- littleprint_list.php
- littleprint_nofild.php

#### 상품권 (3개)
- merchandisebond_admin.php
- merchandisebond_list.php
- merchandisebond_nofild.php

#### NCR양식 (3개)
- ncrflambeau_admin.php
- ncrflambeau_list.php
- ncrflambeau_nofild.php

### Script 파일 (18개)

#### 일반 Script
- namecard_script.php
- inserted_script.php
- envelope_script.php
- sticker_script.php
- cadarok_script.php
- cadaroktwo_script.php
- littleprint_script.php
- merchandisebond_script.php
- ncrflambeau_script.php

#### Search Script
- namecard_scriptsearch.php
- inserted_scriptsearch.php
- envelope_scriptsearch.php
- sticker_scriptsearch.php
- cadarok_scriptsearch.php
- cadaroktwo_scriptsearch.php
- littleprint_scriptsearch.php
- merchandisebond_scriptsearch.php
- ncrflambeau_scriptsearch.php

#### 기타
- debug_script.html

### 문서 (1개)
- README.md (새로 생성)

**총 이동 파일: 46개**

## ✅ 대체 시스템

### 새로 생성된 파일 (Phase 2)

#### 핵심 페이지 (3개)
- `index.php` - 대시보드 (Chart.js)
- `product_manager.php` - 통합 제품 관리
- `order_manager.php` - 통합 주문 관리

#### 뷰 파일 (4개)
- `views/product_selector.php` - 제품 선택
- `views/product_list.php` - 리스트
- `views/product_view.php` - 상세
- `views/product_edit.php` - 수정

#### 핸들러 (1개)
- `handlers/product_save.php` - 저장 처리

#### 설정 (1개)
- `includes/ProductConfig.php` - 메타데이터 (확장)

#### API (유지)
- `api/product_crud.php`
- `api/get_product_config.php`
- `api/get_categories.php`

**총 신규/수정 파일: 12개**

## 📊 코드 감소 통계

| 항목 | 레거시 | 새 시스템 | 감소율 |
|------|--------|----------|--------|
| 파일 개수 | 46개 | 12개 | -74% |
| 관리 파일 | 27개 | 5개 | -81% |
| 예상 코드 라인 | ~8,000 | ~1,500 | -81% |

## 🔍 마이그레이션 체크리스트

### 이동 전 검증
- [x] 레거시 파일 목록 확인
- [x] 의존성 분석 완료
- [x] 새 시스템 테스트 완료
- [x] API 엔드포인트 검증
- [x] 백업 계획 수립

### 이동 작업
- [x] legacy/ 디렉토리 생성
- [x] README.md 작성
- [x] 관리자 파일 이동 (27개)
- [x] Script 파일 이동 (18개)
- [x] 파일 권한 설정
- [x] 마이그레이션 로그 작성

### 이동 후 검증
- [ ] 새 시스템 정상 작동 확인
- [ ] 링크 깨짐 없음 확인
- [ ] 데이터 조회 정상 확인
- [ ] API 호출 정상 확인

## ⚠️ 주의사항

### 발견된 의존성
1. **Script 파일 의존성**
   - `*_script.php` 파일들이 `*_admin.php`에 의존
   - 해당 파일들도 함께 legacy로 이동

2. **직접 링크 확인 필요**
   - 외부에서 직접 레거시 파일을 링크하는 경우 확인 필요
   - 발견 시 새 시스템으로 교체

### 복원 절차
긴급 복원이 필요한 경우:
```bash
cd /var/www/html/admin/mlangprintauto
cp legacy/*_admin.php .
cp legacy/*_list.php .
cp legacy/*_nofild.php .
cp legacy/*_script*.php .
```

## 📈 다음 단계

### Phase 3: 로그인/마이페이지 구축
- remember_tokens 구현
- 마이페이지 구조 생성 (6개 페이지)
- 주문 내역 페이지
- 견적 내역 페이지
- 파일 다운로드 페이지

### Phase 4: 교정 확인 시스템
- proof_status 테이블 생성
- 관리자 교정 관리 페이지
- 고객 교정 확인 페이지

### Phase 5: 견적 시스템 통합
- 계산기 모달 통합
- 제품 페이지 "견적 추가" 버튼

## 📝 참고 문서

- 계획서: `/home/ysung/.claude/plans/whimsical-percolating-cake.md`
- 문서: `/CLAUDE_DOCS/INDEX.md`
- 테스트: `test_crud_e2e.html`

## 🔗 관련 커밋

```bash
git log --oneline --grep="Phase 2" | head -10
```

---

*마이그레이션 완료: 2025-12-25*
*다음 검증: 새 시스템 작동 확인*
