const pptxgen = require("pptxgenjs");

let pres = new pptxgen();
pres.layout = 'LAYOUT_16x9';
pres.author = 'AI Agent Team';
pres.title = 'AI Impact on Print & Flyer Design';

const THEME = {
  primary: "1E2761",
  secondary: "CADCFC",
  accent: "F96167",
  white: "FFFFFF",
  darkGray: "212121",
  lightGray: "F5F5F5",
  font: "Pretendard"
};

let slide1 = pres.addSlide();
slide1.background = { color: THEME.primary };
slide1.addShape(pres.shapes.RECTANGLE, { x: 0, y: 0.8, w: 0.3, h: 4, fill: { color: THEME.accent } });
slide1.addText("생성형 AI가", { x: 1.0, y: 1.5, w: 8, h: 0.8, fontSize: 44, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide1.addText("인쇄 및 전단지 디자인에 미치는 영향", { x: 1.0, y: 2.2, w: 8, h: 0.8, fontSize: 36, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide1.addText("2026 산업 동향 및 실무 적용 사례 분석", { x: 1.0, y: 3.5, w: 8, h: 0.5, fontSize: 16, color: THEME.white, fontFace: THEME.font, transparency: 30, margin: 0 });

let slide2 = pres.addSlide();
slide2.background = { color: THEME.lightGray };
slide2.addText("INDUSTRY SHIFT", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.accent, fontFace: THEME.font, bold: true, margin: 0 });
slide2.addText("AI 인프라 도입의 가속화", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide2.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 1.8, w: 4.2, h: 3.0, fill: { color: THEME.white }, shadow: { type: "outer", color: "000000", blur: 10, offset: 3, angle: 90, opacity: 0.05 } });
slide2.addText("85%", { x: 0.5, y: 2.2, w: 4.2, h: 1.2, fontSize: 72, color: THEME.primary, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide2.addText("미국 인쇄 업체의 AI 도입률", { x: 0.5, y: 3.5, w: 4.2, h: 0.5, fontSize: 16, color: THEME.darkGray, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide2.addText([
    { text: "실험 단계를 넘어선 본격적인 도입\n", options: { fontSize: 20, bold: true } },
    { text: "Canva Magic Studio 등 생성형 AI 툴은 기존 전단지(Flyer) 워크플로우를 혁신하는 인프라로 자리 잡았습니다.", options: { fontSize: 15 } }
], { x: 5.2, y: 2.0, w: 4.3, h: 2.5, color: THEME.darkGray, fontFace: THEME.font, lineSpacing: 28, margin: 0 });

let slide3 = pres.addSlide();
slide3.background = { color: THEME.white };
slide3.addText("WORKFLOW DISRUPTION", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.accent, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addText("비용과 시간의 혁신적 단축", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 1.8, w: 4.25, h: 2.5, fill: { color: THEME.primary }, shadow: { type: "outer", color: "000000", blur: 10, offset: 3, angle: 90, opacity: 0.1 } });
slide3.addText("5 Minutes", { x: 0.8, y: 2.1, w: 3.6, h: 0.6, fontSize: 32, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addText("백지에서 인쇄용 초안 완성까지.\n(기존 수작업: 2~4시간)", { x: 0.8, y: 2.8, w: 3.6, h: 1.0, fontSize: 14, color: THEME.white, fontFace: THEME.font, lineSpacing: 20, margin: 0 });
slide3.addShape(pres.shapes.RECTANGLE, { x: 5.25, y: 1.8, w: 4.25, h: 2.5, fill: { color: THEME.lightGray }, shadow: { type: "outer", color: "000000", blur: 10, offset: 3, angle: 90, opacity: 0.05 } });
slide3.addText("18% Cost Reduction", { x: 5.55, y: 2.1, w: 3.6, h: 0.6, fontSize: 32, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addText("색상 보정, 가변 데이터 배치 등 프리프레스(Pre-press) 자동화를 통한 비용 절감", { x: 5.55, y: 2.8, w: 3.6, h: 1.0, fontSize: 14, color: THEME.darkGray, fontFace: THEME.font, lineSpacing: 20, margin: 0 });

let slide4 = pres.addSlide();
slide4.background = { color: THEME.lightGray };
slide4.addText("SKILLSET EVOLUTION", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.accent, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addText("프롬프트 우선(Prompt-First) 워크플로우", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 1.8, w: 4.25, h: 0.5, fill: { color: "DDDDDD" } });
slide4.addShape(pres.shapes.RECTANGLE, { x: 5.25, y: 1.8, w: 4.25, h: 0.5, fill: { color: THEME.primary } });
slide4.addText("과거 (Pixel Pushing)", { x: 0.5, y: 1.8, w: 4.25, h: 0.5, fontSize: 16, color: "666666", fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide4.addText("현재 (Prompt Curation)", { x: 5.25, y: 1.8, w: 4.25, h: 0.5, fontSize: 16, color: THEME.white, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide4.addText([
    { text: "• 빈 캔버스에서 시작\n", options: { breakLine: true } },
    { text: "• 단일 시안 제작에 수 시간 소요", options: {} }
], { x: 0.5, y: 2.3, w: 4.25, h: 2.0, fill: {color: THEME.white}, fontSize: 15, color: THEME.darkGray, fontFace: THEME.font, lineSpacing: 28, margin: 0, valign: "middle" });
slide4.addText([
    { text: "• 프롬프트로 50+ 레이아웃 즉시 생성\n", options: { breakLine: true } },
    { text: "• 브랜드 가이드라인 준수 여부 검토에 집중", options: {} }
], { x: 5.25, y: 2.3, w: 4.25, h: 2.0, fill: {color: "E8F0FE"}, fontSize: 15, color: THEME.primary, fontFace: THEME.font, lineSpacing: 28, margin: 0, valign: "middle" });

let slide5 = pres.addSlide();
slide5.background = { color: THEME.primary };
slide5.addText("CASE STUDIES", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide5.addText("인쇄 플랫폼의 AI 내재화 사례", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide5.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 1.8, w: 1.5, h: 0.4, fill: { color: THEME.accent } });
slide5.addText("국내 (비즈하우스)", { x: 0.5, y: 1.8, w: 1.5, h: 0.4, fontSize: 14, color: THEME.white, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide5.addText([
    { text: "플랫폼 내 AI 서비스 탑재로 신규 가입자 4배 증가\n", options: { bullet: true, breakLine: true } },
    { text: "소상공인들이 디자인 외주 비용 절감", options: { bullet: true } }
], { x: 2.2, y: 1.6, w: 7.3, h: 1.2, fontSize: 15, color: THEME.white, fontFace: THEME.font, lineSpacing: 22 });

let slide6 = pres.addSlide();
slide6.background = { color: THEME.white };
slide6.addShape(pres.shapes.RECTANGLE, { x: 0, y: 0, w: 3.5, h: 5.625, fill: { color: THEME.darkGray } });
slide6.addText("CONCLUSION", { x: 0.3, y: 2.0, w: 2.9, h: 0.5, fontSize: 14, color: THEME.accent, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("디자인 산업의\n양극화와 진화", { x: 0.3, y: 2.5, w: 2.9, h: 1.0, fontSize: 28, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("1. 디자인의 대중화", { x: 4.0, y: 1.0, w: 5.5, h: 0.5, fontSize: 20, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("일반 비즈니스 오너가 전문가 없이도 수준 높은 전단지를 직접 제작 가능.", { x: 4.0, y: 1.6, w: 5.5, h: 0.8, fontSize: 15, color: "666666", fontFace: THEME.font, lineSpacing: 20, margin: 0 });
slide6.addText("2. 전문가의 초능력화", { x: 4.0, y: 2.8, w: 5.5, h: 0.5, fontSize: 20, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("전문 디자이너는 속도와 변주 능력을 무기로 단가 상승 (프리랜서 59% 단가 인상 성공).", { x: 4.0, y: 3.4, w: 5.5, h: 0.8, fontSize: 15, color: "666666", fontFace: THEME.font, lineSpacing: 20, margin: 0 });

pres.writeFile({ fileName: "AI_Impact_on_Flyer_Design.pptx" }).then(fileName => {
    console.log("Successfully created:", fileName);
});
