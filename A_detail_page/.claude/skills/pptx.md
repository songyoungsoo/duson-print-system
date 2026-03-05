---
name: pptx
description: Use this skill any time a .pptx file is involved in any way — as input, output, or both. This includes creating slide decks, pitch decks, or presentations. Trigger whenever the user mentions "deck," "slides," "presentation," or references a .pptx filename.
---

# PPTX Skill

## Quick Reference

| Task | Guide |
|------|-------|
| Read/analyze content | `python -m markitdown presentation.pptx` |
| Edit or create from template | Extract, manipulate XML, Repack |
| Create from scratch | Use Node.js `pptxgenjs` library |

---

## Design Ideas

**Don't create boring slides.** Plain bullets on a white background won't impress anyone.

### Before Starting

- **Pick a bold, content-informed color palette**: The palette should feel designed for THIS topic.
- **Dominance over equality**: One color should dominate (60-70% visual weight), with 1-2 supporting tones and one sharp accent.
- **Dark/light contrast**: Dark backgrounds for title + conclusion slides, light for content ("sandwich" structure). Or commit to dark throughout for a premium feel.

### Color Palettes (Examples)

| Theme | Primary | Secondary | Accent |
|-------|---------|-----------|--------|
| **Midnight Executive** | `1E2761` (navy) | `CADCFC` (ice blue) | `FFFFFF` (white) |
| **Forest & Moss** | `2C5F2D` (forest) | `97BC62` (moss) | `F5F5F5` (cream) |
| **Coral Energy** | `F96167` (coral) | `F9E795` (gold) | `2F3C7E` (navy) |
| **Charcoal Minimal** | `36454F` (charcoal) | `F2F2F2` (off-white) | `212121` (black) |

### For Each Slide

**Every slide needs a visual element** — image, chart, icon, or shape. Text-only slides are forgettable.

**Layout options:**
- Two-column (text left, illustration on right)
- Icon + text rows (icon in colored circle, bold header, description below)
- 2x2 or 2x3 grid (image on one side, grid of content blocks on other)
- Half-bleed image (full left or right side) with content overlay

**Data display:**
- Large stat callouts (big numbers 60-72pt with small labels below)
- Comparison columns (before/after, pros/cons, side-by-side options)

### Typography (MANDATORY CONSTRAINT)

**YOU MUST USE "Pretendard" FOR ALL TEXT.**
Because the font is fixed, you must create hierarchy through size and weight, not font families.

| Element | Font Face | Size | Weight / Style |
|---------|-----------|------|----------------|
| Slide title | Pretendard | 36-44pt | Bold / ExtraBold |
| Section header | Pretendard | 20-24pt | SemiBold |
| Body text | Pretendard | 14-16pt | Regular / Light |
| Captions / Labels | Pretendard | 10-12pt | Regular (Muted color) |

### Avoid (Common Mistakes)

- **Don't repeat the same layout** — vary columns, cards, and callouts across slides
- **Don't center body text** — left-align paragraphs and lists; center only titles
- **Don't skimp on size contrast** — titles need 36pt+ to stand out from 14-16pt body
- **Don't default to blue** — pick colors that reflect the specific topic
- **Don't create text-only slides** — add images, icons, charts, or visual elements; avoid plain title + bullets
- **NEVER use accent lines under titles** — these are a hallmark of AI-generated slides; use whitespace or background color instead

---

## Creating from Scratch with PptxGenJS

Use `pptxgenjs` (Node.js) for programmatic generation.

### Basic Setup
```javascript
const pptxgen = require("pptxgenjs");
let pres = new pptxgen();
pres.layout = 'LAYOUT_16x9';

let slide = pres.addSlide();
// ALWAYS USE Pretendard
slide.addText("Hello World!", { x: 0.5, y: 0.5, fontSize: 36, color: "363636", fontFace: "Pretendard", bold: true });

// Add visual elements: shapes, images, charts
slide.addShape(pres.shapes.RECTANGLE, { x: 1, y: 1.5, w: 8, h: 0.05, fill: { color: "1E2761" } });

pres.writeFile({ fileName: "Presentation.pptx" });
```

### Common Pitfalls
1. **NEVER use "#" with hex colors** - causes file corruption (`color: "FF0000"` is correct, `color: "#FF0000"` is WRONG).
2. **NEVER encode opacity in hex color strings** - Use the `opacity` or `transparency` properties instead.
3. **Use `bullet: true`** - NEVER unicode symbols like "•".
4. **Use `breakLine: true`** between array items for multi-line text blocks.
