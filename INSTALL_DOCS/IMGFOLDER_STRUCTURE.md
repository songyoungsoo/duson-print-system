# 이미지 폴더 구조 및 설정 가이드

이 문서는 두손기획인쇄 시스템의 이미지 디렉토리 구조와 동기화 방법을 설명합니다.

---

## 0. 이미지 폴더 개요

| 폴더 | 용도 | 구조 |
|------|------|------|
| `ImgFolder/` | 제품별 주문 업로드 파일 | 연도/월/주문번호 |
| `mlangorder_printauto/upload/` | **교정 완성 이미지** | 주문번호별 폴더 |

**교정 이미지 (upload)**: 고객이 확인하는 완성품 미리보기 이미지. 재주문 참조용으로 중요.

---

## 1. 디렉토리 구조

```
/var/www/html/ImgFolder/
│
├── _MlangPrintAuto_[product]_index.php/    # 주문 업로드 파일 (연도별)
│   ├── 2024/
│   │   └── [월]/[주문번호]/                 # 예: 2024/01/84001/
│   ├── 2025/
│   │   └── [월]/[주문번호]/
│   └── 2026/
│       └── [월]/[주문번호]/
│
├── [product]/gallery/                       # 갤러리 샘플 이미지
│   └── *.jpg, *.png
│
├── samplegallery/[product]/                 # 샘플 갤러리 (대체 경로)
│   └── *.jpg, *.png
│
├── paper_texture/                           # 용지 텍스처 이미지
│   ├── 일반용지/
│   ├── 명함재질/
│   ├── 봉투재질/
│   ├── 스티커용지/
│   └── 실제 명함샘플/
│
└── gate_picto/                              # 게이트 픽토그램
```

---

## 2. 제품별 폴더 매핑

### 주문 업로드 폴더 (연도/월/주문번호 구조)

| 제품 | 폴더명 |
|------|--------|
| 전단지 | `_MlangPrintAuto_inserted_index.php/` |
| 스티커 | `_MlangPrintAuto_sticker_new_index.php/` |
| 자석스티커 | `_MlangPrintAuto_msticker_index.php/` |
| 명함 | `_MlangPrintAuto_NameCard_index.php/` |
| 봉투 | `_MlangPrintAuto_envelope_index.php/` |
| 포스터 | `_MlangPrintAuto_littleprint_index.php/` |
| 상품권 | `_MlangPrintAuto_MerchandiseBond_index.php/` |
| 카다록 | `_MlangPrintAuto_cadarok_index.php/` |
| NCR양식지 | `_MlangPrintAuto_NcrFlambeau_index.php/` |

### 갤러리 폴더 (샘플 이미지)

| 제품 | 갤러리 경로 |
|------|------------|
| 전단지 | `leaflet/gallery/` |
| 명함 | `namecard/gallery/` |
| 봉투 | `envelope/gallery/` |
| 포스터 | `littleprint/gallery/` |
| 자석스티커 | `msticker/gallery/` |
| 상품권 | `merchandisebond/gallery/` |

---

## 2-2. 교정이미지 폴더 (upload)

```
/var/www/html/mlangorder_printauto/upload/
│
├── [주문번호]/                    # 예: 84509/
│   └── [파일명].jpg               # 예: 7120260116161218.jpg
│
├── 84500/
│   └── 11620260115120130.jpg
├── 84501/
│   └── 5920260115112426.jpg
└── ...
```

**파일명 패턴**: `[ID][년도][월일][시분초].jpg`
- 예: `7120260116161218.jpg` = ID(71) + 2026년 01월 16일 16:12:18

**현재 데이터**:
- 주문번호 범위: 59981 ~ 84509 (약 17,600개 폴더)
- 용량: ~5GB

---

## 3. 권한 설정

```bash
# ImgFolder 전체 권한 설정
sudo chown -R www-data:www-data /var/www/html/ImgFolder/
sudo chmod -R 755 /var/www/html/ImgFolder/

# 업로드 폴더는 쓰기 권한 필요
sudo chmod -R 775 /var/www/html/ImgFolder/_MlangPrintAuto_*/

# 교정이미지 폴더 권한 설정
sudo chown -R www-data:www-data /var/www/html/mlangorder_printauto/upload/
sudo chmod -R 775 /var/www/html/mlangorder_printauto/upload/
```

---

## 4. 신규 설치 시 필요한 폴더

최소 설치 시 다음 폴더를 생성해야 합니다:

```bash
#!/bin/bash
# create_imgfolder_structure.sh

BASE="/var/www/html/ImgFolder"

# 주문 업로드 폴더
mkdir -p "$BASE/_MlangPrintAuto_inserted_index.php"
mkdir -p "$BASE/_MlangPrintAuto_sticker_new_index.php"
mkdir -p "$BASE/_MlangPrintAuto_msticker_index.php"
mkdir -p "$BASE/_MlangPrintAuto_NameCard_index.php"
mkdir -p "$BASE/_MlangPrintAuto_envelope_index.php"
mkdir -p "$BASE/_MlangPrintAuto_littleprint_index.php"
mkdir -p "$BASE/_MlangPrintAuto_MerchandiseBond_index.php"
mkdir -p "$BASE/_MlangPrintAuto_cadarok_index.php"
mkdir -p "$BASE/_MlangPrintAuto_NcrFlambeau_index.php"

# 갤러리 폴더
mkdir -p "$BASE/leaflet/gallery"
mkdir -p "$BASE/namecard/gallery"
mkdir -p "$BASE/envelope/gallery"
mkdir -p "$BASE/littleprint/gallery"
mkdir -p "$BASE/msticker/gallery"
mkdir -p "$BASE/merchandisebond/gallery"
mkdir -p "$BASE/samplegallery"

# 용지 텍스처
mkdir -p "$BASE/paper_texture"

# 권한 설정
chown -R www-data:www-data "$BASE"
chmod -R 775 "$BASE"

echo "ImgFolder structure created successfully!"
```

---

## 5. 기존 데이터 용량 참고

| 폴더 | 설명 | 예상 용량 |
|------|------|----------|
| ImgFolder 주문 업로드 | 1년치 주문 파일 | ~10-15GB/년 |
| ImgFolder 갤러리 | 샘플 이미지 | ~100MB |
| ImgFolder paper_texture | 용지 텍스처 | ~50MB |
| **교정이미지 (upload)** | **주문별 완성 이미지** | **~5GB (17,600건)** |

**전체 예상 용량**: 3년치 데이터 기준 약 40-60GB

---

## 6. 동기화 스크립트 사용법

```bash
# ImgFolder 동기화 (2026년만)
./scripts/sync_imgfolder.sh lftp dsp1830.shop --user=dsp1830 --pass=ds701018

# 교정이미지 동기화 (84000번 이후 = 2026년)
./scripts/sync_imgfolder.sh upload-lftp dsp1830.shop --user=dsp1830 --pass=ds701018

# 교정이미지 동기화 (특정 범위)
./scripts/sync_imgfolder.sh upload-lftp dsp1830.shop --user=dsp1830 --pass=ds701018 --from=80000

# rsync 사용 (SSH 접속 가능한 경우)
./scripts/sync_imgfolder.sh upload-rsync user@server:/var/www/html/mlangorder_printauto/upload/
```

---

*Last Updated: 2026-01-18*
