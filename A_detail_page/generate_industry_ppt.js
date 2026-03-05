const pptxgen = require("pptxgenjs");

let pres = new pptxgen();
pres.layout = "LAYOUT_16x9";
pres.author = "AI Agent Team";
pres.title = "Commercial Printing Industry Status 2026";

const THEME = {
  primary: "2C5F2D",
  secondary: "97BC62",
  accent: "F5F5F5",
  white: "FFFFFF",
  darkGray: "1A1A1A",
  lightGray: "E8EAE6",
  font: "Pretendard"
};

let slide1 = pres.addSlide();
slide1.background = { color: THEME.primary };
slide1.addShape(pres.shapes.RECTANGLE, { x: 0, y: 0, w: 0.4, h: 5.625, fill: { color: THEME.secondary } });
slide1.addText("2026", { x: 1.0, y: 1.5, w: 8, h: 0.8, fontSize: 24, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide1.addText("상업 인쇄 산업 현황", { x: 1.0, y: 2.0, w: 8, h: 1.0, fontSize: 54, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide1.addText("글로벌 및 국내 시장 동향과 미래 전망", { x: 1.0, y: 3.2, w: 8, h: 0.5, fontSize: 20, color: THEME.accent, fontFace: THEME.font, margin: 0 });

let slide2 = pres.addSlide();
slide2.background = { color: THEME.lightGray };
slide2.addText("MARKET OVERVIEW", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide2.addText("디지털 시대를 돌파하는 6천억 달러 산업", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide2.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 1.8, w: 4.25, h: 2.5, fill: { color: THEME.white } });
slide2.addText("$594B", { x: 0.5, y: 2.2, w: 4.25, h: 1.0, fontSize: 60, color: THEME.primary, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide2.addText("2026 글로벌 상업 인쇄 시장 규모", { x: 0.5, y: 3.2, w: 4.25, h: 0.8, fontSize: 15, color: THEME.darkGray, fontFace: THEME.font, align: "center", lineSpacing: 22, margin: 0 });
slide2.addShape(pres.shapes.RECTANGLE, { x: 5.25, y: 1.8, w: 4.25, h: 2.5, fill: { color: THEME.primary } });
slide2.addText("39조원", { x: 5.25, y: 2.2, w: 4.25, h: 1.0, fontSize: 60, color: THEME.secondary, fontFace: THEME.font, bold: true, align: "center", margin: 0 });
slide2.addText("2030년 국내 시장 전망치", { x: 5.25, y: 3.2, w: 4.25, h: 0.8, fontSize: 15, color: THEME.white, fontFace: THEME.font, align: "center", lineSpacing: 22, margin: 0 });

let slide3 = pres.addSlide();
slide3.background = { color: THEME.white };
slide3.addText("PARADIGM SHIFT", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addText("니치(Niche)에서 메인스트림(Mainstream)으로", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addText("디지털 인쇄로의 전면적 전환", { x: 0.5, y: 1.8, w: 4.5, h: 0.5, fontSize: 22, color: THEME.primary, fontFace: THEME.font, bold: true, margin: 0 });
slide3.addShape(pres.shapes.RECTANGLE, { x: 5.5, y: 1.8, w: 4.0, h: 3.0, fill: { color: THEME.lightGray } });
slide3.addText("디지털 패키징 성장률", { x: 5.5, y: 2.2, w: 4.0, h: 0.8, fontSize: 18, color: THEME.darkGray, fontFace: THEME.font, align: "center", bold: true, margin: 0 });
slide3.addText("9.95%", { x: 5.5, y: 3.0, w: 4.0, h: 1.0, fontSize: 72, color: THEME.primary, fontFace: THEME.font, bold: true, align: "center", margin: 0 });

let slide4 = pres.addSlide();
slide4.background = { color: THEME.darkGray };
slide4.addText("GROWTH SECTOR", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addText("차세대 성장 동력: 패키징 & 라벨", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addShape(pres.shapes.RECTANGLE, { x: 0.5, y: 2.0, w: 2.8, h: 2.5, fill: { color: "2A2A2A" } });
slide4.addText("시장 규모 확충", { x: 0.7, y: 2.2, w: 2.4, h: 0.5, fontSize: 18, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addText("국내 종이 패키징 시장 지속 성장", { x: 0.7, y: 2.8, w: 2.4, h: 1.5, fontSize: 15, color: THEME.white, fontFace: THEME.font, lineSpacing: 24, margin: 0 });
slide4.addShape(pres.shapes.RECTANGLE, { x: 3.6, y: 2.0, w: 2.8, h: 2.5, fill: { color: "2A2A2A" } });
slide4.addText("친환경 전환", { x: 3.8, y: 2.2, w: 2.4, h: 0.5, fontSize: 18, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide4.addText("플라스틱 대체 수요 급증", { x: 3.8, y: 2.8, w: 2.4, h: 1.5, fontSize: 15, color: THEME.white, fontFace: THEME.font, lineSpacing: 24, margin: 0 });

let slide5 = pres.addSlide();
slide5.background = { color: THEME.lightGray };
slide5.addText("CHALLENGES", { x: 0.5, y: 0.5, w: 8, h: 0.5, fontSize: 14, color: "#D32F2F", fontFace: THEME.font, bold: true, margin: 0 });
slide5.addText("산업이 직면한 3대 위협", { x: 0.5, y: 0.8, w: 8, h: 0.6, fontSize: 28, color: THEME.darkGray, fontFace: THEME.font, bold: true, margin: 0 });
slide5.addText("1. 인력난 (Labor Shortage)\n2. 운영 비용 상승 (Operating Costs)\n3. 경제 불확실성 (Economic Uncertainty)", { x: 0.5, y: 1.8, w: 9, h: 3.5, fontFace: THEME.font, fontSize: 20, color: THEME.primary, lineSpacing: 26, margin: 0 });

let slide6 = pres.addSlide();
slide6.background = { color: THEME.primary };
slide6.addShape(pres.shapes.RECTANGLE, { x: 6.5, y: 0, w: 3.5, h: 5.625, fill: { color: THEME.secondary } });
slide6.addText("FUTURE OUTLOOK", { x: 0.5, y: 1.0, w: 5.5, h: 0.5, fontSize: 14, color: THEME.secondary, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("돌파구: 핵심 기회 요인", { x: 0.5, y: 1.3, w: 5.5, h: 0.6, fontSize: 32, color: THEME.white, fontFace: THEME.font, bold: true, margin: 0 });
slide6.addText("ADAPT\nOR\nFADE", { x: 6.5, y: 2.0, w: 3.5, h: 2.0, fontSize: 48, color: THEME.primary, fontFace: THEME.font, bold: true, align: "center", margin: 0 });

pres.writeFile({ fileName: "Printing_Industry_Status.pptx" }).then(fileName => {
    console.log("Successfully created: " + fileName);
});
