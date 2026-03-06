# AI 상세페이지 생성기

## 개요

AI를 활용하여 제품 상세페이지(13섹션 이미지 + 카피 + HTML)를 자동 생성하는 시스템입니다.

## 구조

```
/var/www/html/scripts/
└── ai_detail_page_auto.php  # 메인 스크립트
```

## 사용법

```bash
# 상태 확인
php scripts/ai_detail_page_auto.php status

# 생성 (대기폴더에 저장)
php scripts/ai_detail_page_auto.php generate [제품]

# 미리보기
php scripts/ai_detail_page_auto.php preview [제품]

# 교체 (대기 → 실제)
php scripts/ai_detail_page_auto.php swap [제품]
```

## 제품 목록

| 키 | 제품명 |
|---|--------|
| sticker_new | 스티커 |
| namecard | 명함 |
| inserted | 전단지 |
| envelope | 봉투 |
| littleprint | 포스터 |
| merchandisebond | 상품권 |
| cadarok | 카다록 |
| ncrflambeau | NCR양식지 |
| msticker | 자석스티커 |

## 출력 구조

```
/var/www/html/ImgFolder/
├── detail_page/              # 실제 사용 이미지
│   └── {product}/
│       ├── section_01.png
│       ├── ...
│       ├── section_13.png
│       ├── copies.json
│       └── detail.html
│
└── detail_page_staging/      # 대기폴더 (신규 생성)
    └── {product}/
```

## 이미지 + 카피 연동

1. **Nano Banana Pro** → 이미지 생성
2. **Gemini 2.5 Flash** → 카피 생성 (이미지 컨텍스트 포함)
3. **HTML** → 이미지 배경 + 텍스트 오버레이

## 카피 규칙 (네이버 광고 검수)

❌ **금지가:**
- 과대광고: "세계제일", "최고급", "최우수", "업계 최초"
- 타업체 비교/ 폄하: "타사 대비", "경쟁사보다"
- 허위과장: "100%", "절대", "무한"

✅ **허용:**
- 구체적 사실: "10년 경험", "고객 만족 99%"
- 제품 특성: "고품질", "친환경"

## HTML 템플릿

- 배경: 섹션별 이미지
- 텍스트: HTML 오버레이 (수정 가능)
- 폰트: Pretendard (CDN)

## Commit History

- 2026-03-06: 초안 생성 (ai_detail_page_auto.php)

---

**API Key:** `.env` 또는 스크립트 내 정의
**모델:** 
- 텍스트: gemini-2.5-flash
- 이미지: nano-banana-pro-preview
