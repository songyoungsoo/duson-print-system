# TCPDF 설치 가이드

## 방법 1: Composer 사용 (권장)

### 1. Composer 설치 확인
```bash
composer --version
```

### 2. 프로젝트 루트에서 TCPDF 설치
```bash
cd /path/to/your/project
composer require tecnickcom/tcpdf
```

### 3. 한글 폰트 추가
```bash
# 나눔고딕 폰트 다운로드
mkdir -p vendor/tecnickcom/tcpdf/fonts
cd vendor/tecnickcom/tcpdf/fonts

# 나눔고딕 폰트 파일 복사 (시스템에 설치된 경우)
# Windows: C:\Windows\Fonts\NanumGothic.ttf
# macOS: /System/Library/Fonts/NanumGothic.ttf
# Linux: /usr/share/fonts/truetype/nanum/NanumGothic.ttf
```

## 방법 2: 직접 다운로드

### 1. TCPDF 다운로드
- https://tcpdf.org/download 에서 최신 버전 다운로드
- 압축 해제 후 `lib/tcpdf/` 폴더에 복사

### 2. 폴더 구조
```
프로젝트루트/
├── lib/
│   └── tcpdf/
│       ├── tcpdf.php
│       ├── fonts/
│       └── ...
└── MlangPrintAuto/
    └── shop/
        ├── generate_quote_tcpdf.php
        └── ...
```

## 한글 폰트 설정

### 1. 폰트 변환 도구 사용
```php
// tools/tcpdf_addfont.php 실행
require_once('tcpdf_include.php');
$fontname = TCPDF_FONTS::addTTFfont('/path/to/NanumGothic.ttf', 'TrueTypeUnicode', '', 96);
echo $fontname; // 생성된 폰트명 출력
```

### 2. 폰트 사용
```php
$pdf->SetFont('nanumgothic', '', 12);
```

## 테스트

### 1. 기본 테스트
```
URL: http://localhost/MlangPrintAuto/shop/test_tcpdf.php
```

### 2. 견적서 테스트
```
URL: http://localhost/MlangPrintAuto/shop/generate_quote_tcpdf.php
```

## 문제 해결

### 1. 폰트 오류
- 한글 폰트가 제대로 설치되지 않은 경우
- 기본 폰트로 대체: `helvetica` 또는 `dejavusans`

### 2. 메모리 오류
```php
ini_set('memory_limit', '256M');
```

### 3. 권한 오류
- fonts 폴더 쓰기 권한 확인
- cache 폴더 쓰기 권한 확인

## 대안: HTML to PDF

TCPDF 설치가 어려운 경우 기존 HTML 버전 사용:
```
URL: http://localhost/MlangPrintAuto/shop/generate_quote_pdf.php
```