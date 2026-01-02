# 레거시 관리자 파일 아카이브

이 디렉토리에는 Phase 2 통합 이전의 레거시 관리자 파일들이 보관되어 있습니다.

## 🗂️ 아카이브 정보

- **아카이브 날짜**: 2025-12-25
- **Phase**: Phase 2 - 관리자 시스템 통합
- **파일 개수**: 27개 (9개 제품 × 3개 파일)

## 📋 보관된 파일 목록

### 명함 (namecard)
- `namecard_admin.php` - 레거시 명함 관리 페이지
- `namecard_list.php` - 레거시 명함 리스트
- `namecard_nofild.php` - 레거시 명함 필드 없음 처리

### 전단지 (inserted)
- `inserted_admin.php`
- `inserted_list.php`
- `inserted_nofild.php`

### 봉투 (envelope)
- `envelope_admin.php`
- `envelope_list.php`
- `envelope_nofild.php`

### 스티커 (sticker)
- `sticker_admin.php`
- `sticker_list.php`
- `sticker_nofild.php`

### 카다록 (cadarok)
- `cadarok_admin.php`
- `cadarok_list.php`
- `cadarok_nofild.php`
- `cadaroktwo_admin.php`
- `cadaroktwo_list.php`
- `cadaroktwo_nofild.php`

### 포스터 (littleprint)
- `littleprint_admin.php`
- `littleprint_list.php`
- `littleprint_nofild.php`

### 상품권 (merchandisebond)
- `merchandisebond_admin.php`
- `merchandisebond_list.php`
- `merchandisebond_nofild.php`

### NCR양식 (ncrflambeau)
- `ncrflambeau_admin.php`
- `ncrflambeau_list.php`
- `ncrflambeau_nofild.php`

## ⚠️ 이동 사유

### 문제점
1. **중복 코드**: 85-90% 동일한 코드가 27개 파일에 반복됨
2. **유지보수 어려움**: 버그 수정 시 27개 파일 모두 수정 필요
3. **일관성 부족**: 각 파일마다 다른 스타일과 구조
4. **확장성 부족**: 새 제품 추가 시 3개 파일 새로 작성

### 해결 방안
Phase 2에서 통합 시스템으로 대체:
- **ProductConfig 기반**: 메타데이터 중앙 관리
- **동적 UI 생성**: 제품별 설정에 따라 자동 생성
- **5개 핵심 파일**: 27개 → 5개로 축소 (83% 감소)

## 🎯 새 시스템 사용법

### 1. 대시보드
```
http://localhost/admin/mlangprintauto/
```
- 전체 통계 및 차트
- 제품별 주문 현황
- 최근 7일 추이

### 2. 제품 관리
```
http://localhost/admin/mlangprintauto/product_manager.php
```
**제품 선택 → 리스트 → 상세/수정/삭제**

#### 지원 제품
- `?product=namecard` - 명함
- `?product=inserted` - 전단지
- `?product=envelope` - 봉투
- `?product=sticker` - 스티커
- `?product=msticker` - 자석스티커
- `?product=cadarok` - 카다록
- `?product=littleprint` - 포스터
- `?product=merchandisebond` - 상품권
- `?product=ncrflambeau` - NCR양식

#### 기능
- ✅ 리스트 조회 (페이지네이션)
- ✅ 검색 기능
- ✅ 상세 조회
- ✅ 수정
- ✅ 삭제

### 3. 주문 관리
```
http://localhost/admin/mlangprintauto/order_manager.php
```
- 통합 주문 조회
- 제품별 필터링
- 날짜 범위 검색
- 주문 상태별 조회

### 4. API 엔드포인트
```
/admin/mlangprintauto/api/product_crud.php
/admin/mlangprintauto/api/get_product_config.php
/admin/mlangprintauto/api/get_categories.php
```

## 📁 새 시스템 파일 구조

```
admin/mlangprintauto/
├── index.php                    # 대시보드
├── product_manager.php          # 제품 통합 관리
├── order_manager.php            # 주문 통합 관리
│
├── includes/
│   └── ProductConfig.php        # 제품 메타데이터
│
├── views/
│   ├── product_selector.php    # 제품 선택
│   ├── product_list.php         # 리스트 뷰
│   ├── product_view.php         # 상세 뷰
│   └── product_edit.php         # 수정 폼
│
├── handlers/
│   └── product_save.php         # 저장 핸들러
│
└── api/
    ├── product_crud.php         # CRUD API
    ├── get_product_config.php   # 설정 조회
    └── get_categories.php       # 카테고리 조회
```

## 🔄 마이그레이션 가이드

### 기존 링크 변경
```php
// ❌ 레거시 방식
href="namecard_admin.php"
href="inserted_list.php"

// ✅ 새 방식
href="product_manager.php?product=namecard"
href="product_manager.php?product=inserted"
```

### 코드 통합 예시
```php
// ❌ 레거시: 각 제품마다 별도 파일
namecard_admin.php  (200 lines)
inserted_admin.php  (200 lines)
envelope_admin.php  (200 lines)
...

// ✅ 새 시스템: 하나의 파일로 통합
product_manager.php (150 lines)
+ ProductConfig.php (메타데이터)
```

## 📊 개선 효과

| 지표 | 레거시 | 새 시스템 | 개선율 |
|------|--------|----------|--------|
| 파일 개수 | 27개 | 5개 | -83% |
| 코드 라인 | ~5,400 | ~900 | -83% |
| 유지보수 | 27곳 수정 | 1곳 수정 | -96% |
| 새 제품 추가 | 3개 파일 작성 | 메타데이터만 추가 | -90% |

## ⚠️ 주의사항

### 레거시 파일 사용 금지
- 이 디렉토리의 파일들은 **참고용**으로만 사용
- 새 개발 시 **절대 사용 금지**
- 기존 링크 발견 시 새 시스템으로 교체

### 복원 절차 (긴급 시)
만약 새 시스템에 문제가 있어 레거시로 복원이 필요한 경우:
```bash
# 1. 레거시 파일 복원
cp legacy/*_admin.php ../
cp legacy/*_list.php ../
cp legacy/*_nofild.php ../

# 2. 새 시스템 백업
mv product_manager.php product_manager.php.backup
mv order_manager.php order_manager.php.backup

# 3. 로그 기록
echo "$(date): Legacy restored" >> migration.log
```

### 삭제 일정
- **2026-03-25**: 레거시 파일 완전 삭제 예정 (3개월 후)
- **조건**: 새 시스템 3개월 안정 운영 확인 후

## 📞 문의

- **이슈**: GitHub Issues
- **문서**: `/CLAUDE_DOCS/04_OPERATIONS/Admin_System_Guide.md`
- **테스트**: `test_crud_e2e.html`

---

*아카이브 생성일: 2025-12-25*
*작성자: Claude Sonnet 4.5*
*Phase: Phase 2 - 관리자 시스템 통합*
