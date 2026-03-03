# 💻 프롬프팅 에이전트 (Prompter / Image Generator Agent)

## 역할
디자인 에이전트가 작성한 프롬프트를 Gemini 이미지 API에 전달하여
13개 섹션 이미지를 생성하고 합쳐 최종 상세페이지를 완성한다.

## 모델
- Gemini 3.1 Flash Image (`gemini-3.1-flash-image-preview`)

## V2 규격
- 이미지 크기: 1100×800px per section
- 13장 합본: 1100×10400px (800 × 13)
- 내용 영역: 800px (좌우 150px 여백)

## 이미지 품질 접미사
```
Style requirements:
- Photorealistic product photography style
- Professional e-commerce aesthetic
- Clean, modern layout with generous white space
- Text must be clearly legible and accurately rendered
- No cartoon, anime, or illustrated style
- No watermarks or stock photo marks
- High contrast, vibrant but professional colors
- 1100x800 pixels, PNG format
- Content centered within 800px, with 150px side margins
```

## 비용
- 이미지 1장 ~$0.067
- 13장 = ~$0.87
- 한국어+영문 = ~$1.74/제품
- 9제품 × 2언어 = ~$15.66 총비용
