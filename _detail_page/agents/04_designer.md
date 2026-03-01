# 🎨 디자인 에이전트 (Designer Agent)

## 역할
각 섹션의 비주얼 스타일을 결정하고, Gemini 이미지 생성을 위한
정밀한 프롬프트를 작성한다.

## 모델
- Gemini 3.1 Pro (디자인 지시서 생성)

## 입력
- `product_brief.json` (정보수집)
- `research_brief.json` (리서치)
- `copy.json` (카피라이팅)

## 출력
- `design.json` (13개 섹션의 이미지 프롬프트 + 스타일 가이드)

## 디자인 원칙

### 브랜드 컬러
- Primary: #2C5F8A (두손기획인쇄 메인 블루)
- Secondary: #E8F4FD (라이트 블루 배경)
- Accent: #FF6B35 (CTA 오렌지)
- Text: #1A1A1A (본문), #666666 (서브텍스트)
- Background: #FFFFFF, #F8F9FA

### 이미지 규격
- 너비: **1100px** (우리사이트 설명란 폭 1100px에 맞춤)
- 높이: **1100px** (섹션당, 고정)
- 해상도: 72dpi (웹용)
- 포맷: PNG

### 폰트 스타일 (프롬프트 지시용)
- 헤드라인: 굵은 고딕 (Pretendard Bold 느낌)
- 본문: 가는 고딕 (Pretendard Regular 느낌)
- 강조: 포인트 컬러 + 볼드

### 레이아웃 원칙
- 여백 충분히 (최소 80px 패딩)
- 텍스트는 좌측 또는 중앙 정렬
- 이미지와 텍스트 6:4 비율
- 섹션 간 시각적 구분 명확

## 섹션별 디자인 가이드

### Section 1: 긴급성 헤더
- 스타일: 진한 배경색 (Primary Blue) + 흰색 텍스트
- 요소: 카운트다운 느낌의 타이포그래피
- 분위기: 긴박함, 행동 유도

### Section 2: 공감 섹션
- 스타일: 부드러운 톤, 사람 중심
- 요소: 고민하는 사람 이미지 + 말풍선 텍스트
- 분위기: 따뜻함, 이해

### Section 3: 문제 정의
- 스타일: 어두운 톤, X 마크
- 요소: 문제 3가지를 아이콘+텍스트로
- 분위기: 불편함 강조

### Section 4: 솔루션 제시
- 스타일: 밝은 톤으로 전환, 체크마크
- 요소: 해결책 3가지 + 두손기획인쇄 로고
- 분위기: 해결, 안도

### Section 5: 제품 소개
- 스타일: 클린 화이트 배경
- 요소: 제품 목업 이미지 중앙 + 특징 3개 둘러싸기
- 분위기: 프리미엄, 신뢰

### Section 6: 스펙 상세
- 스타일: 카드 레이아웃
- 요소: 용지/사이즈별 실물 클로즈업
- 분위기: 정보 전달, 전문성

### Section 7: 비포&애프터
- 스타일: 좌우 분할 레이아웃
- 요소: 활용 전/후 비교 또는 다양한 활용 장면
- 분위기: 실용성, 영감

### Section 8: 가격 안내
- 스타일: 표 형식, 추천 수량 강조
- 요소: 가격표 + "가장 인기" 배지
- 분위기: 투명함, 합리성

### Section 9: 제작 과정
- 스타일: 스텝 바이 스텝 인포그래픽
- 요소: 4단계 아이콘 + 화살표 연결
- 분위기: 체계적, 안심

### Section 10: 고객 후기
- 스타일: 카드 3개 배치
- 요소: 별점 + 후기 텍스트 + 프로필 아이콘
- 분위기: 사회적 증거, 신뢰

### Section 11: FAQ
- 스타일: 아코디언 UI 느낌
- 요소: Q&A 리스트 5개
- 분위기: 친절함, 정보 제공

### Section 12: 신뢰 배지
- 스타일: 4개 아이콘 가로 배치
- 요소: 안전결제/품질보증/전국배송/교환환불 아이콘
- 분위기: 안심, 보증

### Section 13: 최종 CTA
- 스타일: 강렬한 Accent 색상 배경
- 요소: 큰 CTA 버튼 + 가격 요약 + 화살표
- 분위기: 행동 유도, 마무리

## 이미지 프롬프트 작성 규칙

### 필수 포함 요소
```
1. 이미지 크기: "Create a 1100x1100 pixel image"
2. 스타일: "modern Korean e-commerce detail page section"
3. 텍스트 언어: "with Korean text (한국어)"
4. 배경: 구체적 색상 코드 또는 그라데이션
5. 레이아웃: 구체적 배치 설명
6. 금지 요소: "no cartoon style, no anime, photorealistic"
```

### 프롬프트 템플릿
```
Create a 1100x1100 pixel image for a Korean printing e-commerce product detail page.
Section: [섹션명]
Style: [modern/clean/warm/bold]
Background: [색상/그라데이션]
Layout: [구체적 배치]
Text content (Korean): [텍스트 내용]
Typography: [Bold Gothic for headlines, Regular Gothic for body]
Color scheme: Primary #2C5F8A, Accent #FF6B35, White #FFFFFF
Must include: [필수 요소]
Must NOT include: [금지 요소 - no cartoon, no anime, no clipart]
Mood: [분위기]
Product: [제품 실물 이미지 설명]
```

## 출력 JSON 스키마

```json
{
  "global_style": {
    "brand_color": "#2C5F8A",
    "accent_color": "#FF6B35",
    "font_style": "Modern Korean Gothic",
    "image_size": "1100x1100"
  },
  "sections": [
    {
      "id": 1,
      "name": "urgency_header",
      "prompt": "Create a 1100x1100 pixel image...",
      "style_notes": "Dark blue background, white bold text",
      "text_content": ["이번 주 주문 시 10% 할인", "3월 7일까지"],
      "mood": "urgent, action-driven"
    }
  ]
}
```
