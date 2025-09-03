# 📤 서버 업로드 요약

## ✅ 완료된 작업

### 1. 404 오류 해결
- ✅ **favicon.ico** 생성 완료
- ✅ **common-auth.js** - 이미 존재함 (`/js/` 폴더)
- ✅ **placeholder.jpg** - 이미 존재함 (`/assets/images/` 폴더)

### 2. 데이터베이스 설정 수정 완료
```php
$host = "localhost";
$user = "dsp1830";
$password = "ds701018";
$dataname = "dsp1830";
```

## 📁 서버에 업로드할 파일들

### 🔴 즉시 업로드 필요 (전단지 계산 수정 완료)
```
1. /MlangPrintAuto/inserted/calculate_price_ajax.php
2. /MlangPrintAuto/inserted/get_paper_types.php
3. /MlangPrintAuto/inserted/get_paper_sizes.php
4. /MlangPrintAuto/inserted/get_quantities.php
5. /favicon.ico (새로 생성됨)
```

### 🟡 추가 수정 후 업로드 필요
각 제품별 AJAX 파일들의 데이터베이스 설정을 동일하게 수정 필요
- 카다록, 명함, 봉투, 스티커, 포스터 등의 calculate_price_ajax.php 파일들

## 📌 업로드 방법
```bash
# FTP 또는 파일 매니저 사용
# 경로: /home/dsp1830/public_html/
# 권한: 644 (파일), 755 (폴더)
```

## ✔️ 테스트 체크리스트
- [ ] 전단지 페이지 접속
- [ ] 옵션 선택 시 드롭다운 정상 작동
- [ ] 가격 계산 정상 작동
- [ ] 장바구니 담기 정상 작동
- [ ] 콘솔에 404 오류 없음 확인