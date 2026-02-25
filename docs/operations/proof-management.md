## 🔍 교정 관리 시스템 (Dashboard Proofs)

### 파일 구조
| 파일 | 용도 |
|------|------|
| `dashboard/proofs/index.php` | 교정 목록 + 이미지 뷰어 + 파일 업로드 UI |
| `dashboard/proofs/api.php` | 파일 목록 조회 / 파일 업로드 API |

### 교정파일 저장 경로
```
/mlangorder_printauto/upload/{주문번호}/
  ├─ 20260208_153000_시안_최종.jpg    (커스텀 이름)
  ├─ 20260208_a3f1b2c4.png            (자동 이름)
  └─ ...
```

### 이미지 뷰어 동작
```
"보기" 클릭 → API 파일 목록 조회 → 이미지 100% 원본 크기 오버레이 (스크롤)
  ├─ 여러 이미지: ‹ › 화살표 + 방향키 네비게이션 + 카운터(1/3)
  ├─ 닫기: 이미지 클릭 / 배경 클릭 / ESC / ✕ 버튼
  ├─ 클릭 닫기: 순수 클릭(이동 5px 미만) → closeImageViewer(), 드래그(5px+) → 무시
  ├─ 확대 상태: 마우스 드래그로 패닝, 줌 버튼(+/−/fit)으로 확대/축소
  └─ 비이미지 파일: 새 탭으로 열기
```

### 교정확정 2단계 확인 (2026-02-23)
```
"교정확정" 클릭
  → 1차 confirm: "오탈자 및 전체를 잘 확인 했습니다... 교정확정 하시겠습니까?"
  → 2차 confirm: "⚠️ 최종 확인 — 교정확정 후에는 취소할 수 없습니다. 정말 인쇄를 진행하시겠습니까?"
  → 둘 다 확인 시 → AJAX POST api.php?action=confirm_proofread
  → 하나라도 취소 → 중단
```

### 파일 업로드 기능
- 파일 누적 추가 (선택/드롭 반복 가능)
- 개별 삭제, 이미지 썸네일 미리보기
- 파일명 자동 입력 (편집 가능, 확장자 별도 표시)
- 20MB/파일 제한, 허용 형식: jpg, jpeg, png, gif, pdf, ai, psd, zip
- 업로드 진행률 표시, 완료 후 페이지 새로고침 없이 행 갱신

### 교정 갤러리 (Public Proof Gallery)

**파일**: `popup/proof_gallery.php`

#### 기능 개요
```
https://dsp114.co.kr/popup/proof_gallery.php?cate=전단지&page=1
```
- 고객 주문 교정 이미지 갤러리
- 24개/페이지, pagination 지원
- 2가지 소스 혼합:
  1. Gallery 샘플: `/ImgFolder/inserted/gallery/` (101개)
  2. 실제 주문 이미지: `/mlangorder_printauto/upload/{주문번호}/` (1,046개)

#### Multi-File Upload JSON Parsing (2026-02-10 수정)

**문제**: `admin.php`에서 다중 파일 업로드 지원 후, `ThingCate` 컬럼에 JSON 배열 저장
```php
// 기존 (단일 파일): "20260208_abc.jpg"
// 신규 (다중 파일): '[{"original_name":"file.jpg","saved_name":"20260208_abc.jpg","size":1024,"type":"jpg"}]'
```

**해결**: `proof_gallery.php` (lines 189-210)에 JSON 파싱 로직 추가
```php
if (strpos($thing_cate, '[{') === 0 || strpos($thing_cate, '{"') === 0) {
    $decoded = json_decode($thing_cate, true);
    if (is_array($decoded)) {
        foreach ($decoded as $file_info) {
            if (isset($file_info['saved_name'])) {
                $files_to_check[] = $file_info['saved_name'];
            }
        }
    }
} else {
    $files_to_check[] = $thing_cate;
}
```

#### upload 디렉토리 이미지 500 에러 해결 (2026-02-10)

**문제**: 갤러리 5페이지 이상에서 upload 디렉토리 이미지 500 Internal Server Error

**원인**: `/httpdocs/mlangorder_printauto/upload/.htaccess` 파일이 Plesk Apache 2.4와 호환되지 않는 구문 포함
- `Options +Indexes` → Plesk에서 AllowOverride 제한으로 500 에러 유발
- `Order allow,deny` / `Allow from all` → Apache 2.2 구문 (mod_access_compat 미설치)
- Apache 2.2 + 2.4 구문 혼합 사용

**해결**: 해당 `.htaccess` 파일 삭제 (FTP로 프로덕션에서 제거)

**Critical Rules**:
- ❌ `/mlangorder_printauto/upload/`에 `.htaccess` 파일 생성 금지 (500 에러 유발)
- ✅ 해당 디렉토리는 `.htaccess` 없이 이미지 정상 서빙됨
- ⚠️ curl 기본 UA는 nginx에서 403 차단됨 (브라우저 UA 필요)

**검증 방법**:
```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
curl -s -o /dev/null -w "%{http_code}" -A "$UA" "https://dsp114.co.kr/mlangorder_printauto/upload/79678/4820231127133915.jpg"
# 200이면 정상
```

