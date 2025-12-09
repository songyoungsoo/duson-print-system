---
inclusion: always
---

# 📁 통합 업로드 경로 시스템

## 🎯 공식 주문 품목 (9개)

두손기획인쇄 온라인 주문 시스템의 공식 품목 목록:

1. ✅ **전단지** (inserted)
2. ✅ **명함** (namecard)
3. ✅ **봉투** (envelope)
4. ✅ **카다록** (cadarok)
5. ✅ **상품권** (merchandisebond)
6. ✅ **스티커** (sticker_new)
7. ✅ **양식지** (ncrflambeau)
8. ✅ **자석스티커** (msticker)
9. ✅ **포스터** (littleprint)

> **중요**: 리플렛(leaflet)은 주문 품목에서 제외됨

## 📂 구버전 호환 업로드 경로 구조

### 절대 규칙

**모든 품목은 구버전(dsp114.com)과 동일한 경로를 사용해야 합니다:**

```
실제 저장: /ImgFolder/_MlangPrintAuto_{품목}_index.php/YYYY/MMDD/IP주소/타임스탬프/
DB 저장:   ImgFolder = "_MlangPrintAuto_{품목}_index.php/YYYY/MMDD/IP주소/타임스탬프/"
```

### 표준 코드 (모든 add_to_basket.php)

```php
// ✅ 구버전 경로 구조 (절대 변경 금지)
$client_ip = $_SERVER['REMOTE_ADDR'];
$timestamp = time();
$date_y = date('Y', $timestamp);
$date_md = date('md', $timestamp);

// 품목별 경로 (예: inserted)
$relative_path = "_MlangPrintAuto_inserted_index.php/{$date_y}/{$date_md}/{$client_ip}/{$timestamp}/";
$upload_folder = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $relative_path;
$upload_folder_db = $relative_path; // DB 저장용 (ImgFolder 제외)

if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0755, true);
}
```

## 📋 품목별 경로 매핑

| 품목 | 폴더명 | 경로 패턴 |
|------|--------|-----------|
| 전단지 | inserted | `_MlangPrintAuto_inserted_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 명함 | namecard | `_MlangPrintAuto_NameCard_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 봉투 | envelope | `_MlangPrintAuto_envelope_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 카다록 | cadarok | `_MlangPrintAuto_cadarok_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 상품권 | merchandisebond | `_MlangPrintAuto_merchandisebond_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 스티커 | sticker_new | `_MlangPrintAuto_sticker_new_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 양식지 | ncrflambeau | `_MlangPrintAuto_ncrflambeau_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 자석스티커 | msticker | `_MlangPrintAuto_msticker_index.php/YYYY/MMDD/IP/타임스탬프/` |
| 포스터 | littleprint | `_MlangPrintAuto_littleprint_index.php/YYYY/MMDD/IP/타임스탬프/` |

## ⚠️ 절대 금지 사항

1. ❌ 신버전 경로 사용 금지: `mlangorder_printauto/upload/`
2. ❌ 임시 폴더 사용 금지: `temp_{세션ID}_{타임스탬프}/`
3. ❌ 품목별 다른 경로 패턴 사용 금지
4. ❌ DB에 절대 경로 저장 금지 (상대 경로만 저장)

## 🔄 주문 확정 시 처리

**구버전 방식에서는 폴더 이동이 필요 없습니다:**

- 장바구니 단계에서 이미 최종 경로에 저장됨
- `ImgFolder` 필드는 그대로 `mlangorder_printauto` 테이블로 복사
- 파일 이동 없이 경로만 참조

```php
// shop/finalize_order.php
// ImgFolder 값을 그대로 사용 (폴더 이동 불필요)
$img_folder = $cart_item['ImgFolder']; // 예: "_MlangPrintAuto_inserted_index.php/2025/1114/112.185.73.148/1731567890/"
```

## 📋 품목별 구현 상태 (구버전 경로)

| 품목 | 폴더명 | 파일 | 경로 구조 | 상태 |
|------|--------|------|-----------|------|
| 전단지 | inserted | mlangprintauto/inserted/add_to_basket.php | `_MlangPrintAuto_inserted_index.php/...` | ✅ |
| 명함 | namecard | mlangprintauto/namecard/add_to_basket.php | `_MlangPrintAuto_NameCard_index.php/...` | ✅ |
| 봉투 | envelope | mlangprintauto/envelope/add_to_basket.php | `_MlangPrintAuto_envelope_index.php/...` | ✅ |
| 카다록 | cadarok | mlangprintauto/cadarok/add_to_basket.php | `_MlangPrintAuto_cadarok_index.php/...` | ✅ |
| 상품권 | merchandisebond | mlangprintauto/merchandisebond/add_to_basket.php | `_MlangPrintAuto_merchandisebond_index.php/...` | ✅ |
| 스티커 | sticker_new | mlangprintauto/sticker_new/add_to_basket.php | `_MlangPrintAuto_sticker_new_index.php/...` | ✅ |
| 양식지 | ncrflambeau | mlangprintauto/ncrflambeau/add_to_basket.php | `_MlangPrintAuto_ncrflambeau_index.php/...` | ✅ |
| 자석스티커 | msticker | mlangprintauto/msticker/add_to_basket.php | `_MlangPrintAuto_msticker_index.php/...` | ✅ |
| 포스터 | littleprint | mlangprintauto/littleprint/add_to_basket.php | `_MlangPrintAuto_littleprint_index.php/...` | ✅ |

## 🎯 새 품목 추가 시 체크리스트

- [ ] `add_to_basket.php`에 구버전 경로 코드 추가
- [ ] 품목별 경로 패턴 설정: `_MlangPrintAuto_{품목}_index.php/...`
- [ ] `$upload_folder_db` 변수로 DB 저장 (상대 경로)
- [ ] `ImgFolder` 컬럼에 경로 저장 확인
- [ ] 이 문서의 품목 목록에 추가
- [ ] 테스트: 파일 업로드 → 장바구니 → 주문 확정 → 관리자 페이지 확인

## 📥 다운로드 시스템

구버전 경로를 자동으로 감지하여 다운로드 지원:

- **개별 다운로드**: `admin/mlangprintauto/download.php`
- **ZIP 일괄 다운로드**: `admin/mlangprintauto/download_all.php`
- 경로 자동 감지: `ImgFolder/{경로}` → `/ImgFolder/{경로}` → `mlangorder_printauto/upload/{주문번호}`

---

**최종 업데이트**: 2025-11-14 (구버전 경로 구조로 완전 전환)
