## 📦 대시보드 카테고리 관리 (Dashboard Category Management)

### 시스템 개요

대시보드에서 품목(카테고리)별 가격 데이터를 관리하는 시스템.

| 항목 | 값 |
|------|-----|
| **UI** | `/dashboard/products/list.php` |
| **API** | `/dashboard/api/products.php` (4개 action) |
| **DB 테이블** | `catelist` (카테고리 메타) + `mlangprintauto_*` (품목별 가격) |

### API 엔드포인트 (`/dashboard/api/products.php`)

| action | Method | 용도 |
|--------|--------|------|
| `category_list` | GET | 품목별 카테고리 목록 조회 (스타일 필터 지원) |
| `category_create` | POST | 새 카테고리 추가 |
| `category_update` | POST | 카테고리명/설명 수정 |
| `category_delete` | POST | 카테고리 삭제 (가격 데이터 연쇄 삭제) |

### 카테고리 관리 UI 기능

- **스타일 필터**: 전체/대봉투/소봉투 등 드롭다운 필터
- **테이블 형식**: ID, 카테고리명, 설명, 수정/삭제 버튼
- **수정 모달**: 인라인 편집 (카테고리명 + 설명)
- **삭제 확인**: confirm 다이얼로그 + 연쇄 삭제 경고
- **추가 모달**: 카테고리 코드 + 이름 + 설명 입력

### 교정시안 품목명 한글화

**구현 위치**: `dashboard/proofs/index.php`

영문 테이블명 → 한글 품목명 자동 매핑:
```php
$PRODUCT_NAME_MAP = [
    'sticker_new' => '스티커', 'inserted' => '전단지',
    'namecard' => '명함', 'envelope' => '봉투',
    'littleprint' => '포스터', 'merchandisebond' => '상품권',
    'msticker' => '자석스티커', 'cadarok' => '카다록',
    'ncrflambeau' => 'NCR양식지'
];
```

