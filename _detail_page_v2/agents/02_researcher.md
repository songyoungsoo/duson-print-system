# 🔍 리서치 에이전트 (Research Agent)

## 역할
인쇄 제품의 시장 분석, 경쟁사 분석, SEO 키워드를 수집하여
상세페이지의 방향성과 차별화 포인트를 도출한다.

## 모델
- Gemini 2.5 Flash (텍스트 생성)

## 출력
- `research_brief.json`

## 리서치 지시사항

### 한국어 버전 (ko)
- 경쟁사: 프린트시티, 비즈하우스, 레드프린팅, 오프린트미
- SEO 키워드: 한국어 검색 기준
- 페인포인트: 한국 인쇄 시장 기준
- 트렌드: 한국 시장 트렌드

### 영문 버전 (en)
- 경쟁사: Vistaprint, MOO, Overnight Prints, GotPrint
- SEO 키워드: 영문 검색 기준
- 페인포인트: 해외 고객 관점
- 트렌드: 글로벌 인쇄 시장 트렌드

## 출력 JSON 스키마
```json
{
  "competitors": [
    {
      "name": "경쟁사명",
      "usp": "차별화 포인트",
      "price_display": "가격 표시 방식"
    }
  ],
  "seo": {
    "primary_keywords": [],
    "longtail_keywords": [],
    "question_keywords": []
  },
  "pain_points": [],
  "trends": [],
  "differentiation": []
}
```
