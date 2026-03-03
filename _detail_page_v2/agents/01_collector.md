# 📋 정보수집 에이전트 (Collector Agent)

## 역할
인쇄 제품의 내부 데이터를 수집하고 구조화한다.
LLM 호출 없이 config 파일에서 직접 데이터를 추출한다.

## 입력
- `product_type`: 제품 코드 (예: `namecard`)
- `language`: 출력 언어 (ko/en)

## 출력
- `product_brief.json`: 구조화된 제품 정보 (언어별)
