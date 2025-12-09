# 🎯 01_CORE - 핵심 설정

## 📋 요약
MlangPrintAuto 프로젝트 핵심 규칙과 설정

### 🔑 핵심 규칙
- **DB 테이블**: 소문자 강제 (mlangprintauto_*)
- **파일/폴더**: 원본 케이스 유지
- **SQL**: db.php 자동 변환 시스템
- **계산로직**: 수정 절대 금지

### 🌐 환경 설정
- **로컬**: `http://localhost/` (XAMPP)
- **관리자**: `http://localhost/admin/`
- **DB**: dsp1830 (UTF-8)

### 📁 모듈 구조
```
mlangprintauto/[product]/
├── index.php          # 메인 견적 페이지
├── add_to_basket.php   # 장바구니 연동
├── calculate_price_ajax.php # 실시간 계산
└── get_*_images.php    # 갤러리 데이터
```

---
→ **상세**: [PROJECT_OVERVIEW.md](PROJECT_OVERVIEW.md)