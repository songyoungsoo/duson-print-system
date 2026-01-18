# ImgFolder 구조 및 설정 가이드

이 문서는 ImgFolder 디렉토리 구조와 이미지 동기화 방법을 설명합니다.

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

## 3. 권한 설정

```bash
# ImgFolder 전체 권한 설정
sudo chown -R www-data:www-data /var/www/html/ImgFolder/
sudo chmod -R 755 /var/www/html/ImgFolder/

# 업로드 폴더는 쓰기 권한 필요
sudo chmod -R 775 /var/www/html/ImgFolder/_MlangPrintAuto_*/
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
| 주문 업로드 폴더 | 1년치 주문 파일 | ~10-15GB/년 |
| 갤러리 폴더 | 샘플 이미지 | ~100MB |
| paper_texture | 용지 텍스처 | ~50MB |

**전체 예상 용량**: 3년치 데이터 기준 약 30-50GB

---

*Last Updated: 2026-01-18*
