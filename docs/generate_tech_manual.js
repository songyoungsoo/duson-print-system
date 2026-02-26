const fs = require("fs");
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, HeadingLevel,
  BorderStyle, WidthType, ShadingType, VerticalAlign, PageNumber, PageBreak
} = require("docx");

// ─── CONFIG ───
const BRAND = "1E4E79";
const FONT = "맑은 고딕";
const MONO = "Consolas";
const GRAY_BG = "F0F0F0";
const BORDER_CLR = "CCCCCC";
const USABLE_WIDTH = 9360; // DXA, letter with 1" margins

// ─── READ MARKDOWN ───
const md = fs.readFileSync(
  "/var/www/html/docs/두손기획인쇄_기술매뉴얼_Notion.md", "utf8"
);
const lines = md.split("\n");

// ─── INLINE PARSER ───
// Parses bold (**text**), inline code (`code`), and plain text into TextRun[]
function parseInline(text, baseOpts = {}) {
  const runs = [];
  if (!text) return [new TextRun({ text: " ", font: FONT, size: 20, ...baseOpts })];
  const regex = /(\*\*(.+?)\*\*|`([^`]+)`)/g;
  let last = 0;
  let m;
  while ((m = regex.exec(text)) !== null) {
    if (m.index > last) {
      runs.push(new TextRun({ text: text.slice(last, m.index), font: FONT, size: 20, ...baseOpts }));
    }
    if (m[2]) {
      // bold
      runs.push(new TextRun({ text: m[2], font: FONT, size: 20, bold: true, ...baseOpts }));
    } else if (m[3]) {
      // inline code
      runs.push(new TextRun({ text: m[3], font: MONO, size: 18, color: "C7254E",
        shading: { fill: "F9F2F4", type: ShadingType.CLEAR } }));
    }
    last = m.index + m[0].length;
  }
  if (last < text.length) {
    runs.push(new TextRun({ text: text.slice(last), font: FONT, size: 20, ...baseOpts }));
  }
  if (runs.length === 0) {
    runs.push(new TextRun({ text: " ", font: FONT, size: 20, ...baseOpts }));
  }
  return runs;
}

// ─── TABLE PARSER ───
function parseTable(tableLines) {
  // tableLines: array of pipe-delimited lines (header, separator, rows)
  const parseRow = (line) =>
    line.split("|").map(c => c.trim()).filter((_, i, a) => i > 0 && i < a.length);

  const headerCells = parseRow(tableLines[0]);
  const numCols = headerCells.length;
  if (numCols === 0) return null;

  const colWidth = Math.floor(USABLE_WIDTH / numCols);
  const colWidths = Array(numCols).fill(colWidth);
  const thinBorder = { style: BorderStyle.SINGLE, size: 1, color: BORDER_CLR };
  const cellBorders = { top: thinBorder, bottom: thinBorder, left: thinBorder, right: thinBorder };

  const headerRow = new TableRow({
    tableHeader: true,
    children: headerCells.map(cell =>
      new TableCell({
        borders: cellBorders,
        width: { size: colWidth, type: WidthType.DXA },
        shading: { fill: BRAND, type: ShadingType.CLEAR },
        verticalAlign: VerticalAlign.CENTER,
        children: [new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { before: 40, after: 40 },
          children: [new TextRun({ text: cell || " ", bold: true, font: FONT, size: 18, color: "FFFFFF" })]
        })]
      })
    )
  });

  const dataRows = [];
  for (let i = 2; i < tableLines.length; i++) {
    const cells = parseRow(tableLines[i]);
    if (cells.length === 0) continue;
    // Pad or trim to match header col count
    while (cells.length < numCols) cells.push("");
    dataRows.push(new TableRow({
      children: cells.slice(0, numCols).map(cell =>
        new TableCell({
          borders: cellBorders,
          width: { size: colWidth, type: WidthType.DXA },
          verticalAlign: VerticalAlign.CENTER,
          children: [new Paragraph({
            spacing: { before: 30, after: 30 },
            children: parseInline(cell, { size: 18 })
          })]
        })
      )
    }));
  }

  return new Table({
    columnWidths: colWidths,
    rows: [headerRow, ...dataRows]
  });
}

// ─── CODE BLOCK PARAGRAPH ───
function codeBlockParagraph(text) {
  return new Paragraph({
    spacing: { before: 20, after: 20 },
    shading: { fill: GRAY_BG, type: ShadingType.CLEAR },
    indent: { left: 360 },
    children: [new TextRun({ text: text || " ", font: MONO, size: 18, color: "333333" })]
  });
}

// ─── MAIN PARSER ───
const children = [];
let bulletListRef = 0;
const numbConfigs = [];

function getBulletRef() {
  const ref = `bullet-${bulletListRef++}`;
  numbConfigs.push({
    reference: ref,
    levels: [{
      level: 0,
      format: LevelFormat.BULLET,
      text: "\u2022",
      alignment: AlignmentType.LEFT,
      style: { paragraph: { indent: { left: 720, hanging: 360 } } }
    }]
  });
  return ref;
}

let currentBulletRef = null;
let inBulletList = false;

// Chapter detection for page breaks
const chapterPattern = /^## (Ch\d+\.|부록)/;
let firstChapter = true;

let i = 0;
while (i < lines.length) {
  const line = lines[i];

  // ── Blank line ──
  if (line.trim() === "") {
    if (inBulletList) {
      inBulletList = false;
      currentBulletRef = null;
    }
    i++;
    continue;
  }

  // ── Horizontal rule (---) ── skip, page breaks handled at chapter headings
  if (/^---\s*$/.test(line.trim())) {
    if (inBulletList) {
      inBulletList = false;
      currentBulletRef = null;
    }
    i++;
    continue;
  }

  // ── Fenced code block ──
  if (line.trim().startsWith("```")) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const codeLines = [];
    i++; // skip opening fence
    while (i < lines.length && !lines[i].trim().startsWith("```")) {
      codeLines.push(lines[i]);
      i++;
    }
    i++; // skip closing fence

    // Top border for code block
    children.push(new Paragraph({
      spacing: { before: 120, after: 0 },
      shading: { fill: GRAY_BG, type: ShadingType.CLEAR },
      indent: { left: 360 },
      children: [new TextRun({ text: " ", font: MONO, size: 8 })]
    }));

    for (const cl of codeLines) {
      children.push(codeBlockParagraph(cl));
    }

    // Bottom border
    children.push(new Paragraph({
      spacing: { before: 0, after: 120 },
      shading: { fill: GRAY_BG, type: ShadingType.CLEAR },
      indent: { left: 360 },
      children: [new TextRun({ text: " ", font: MONO, size: 8 })]
    }));
    continue;
  }

  // ── Table ──
  if (line.includes("|") && line.trim().startsWith("|")) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const tableLines = [];
    while (i < lines.length && lines[i].includes("|") && lines[i].trim().startsWith("|")) {
      tableLines.push(lines[i]);
      i++;
    }
    if (tableLines.length >= 2) {
      const t = parseTable(tableLines);
      if (t) {
        children.push(new Paragraph({ spacing: { before: 80, after: 0 }, children: [] }));
        children.push(t);
        children.push(new Paragraph({ spacing: { before: 0, after: 80 }, children: [] }));
      }
    }
    continue;
  }

  // ── Headings ──
  // # Title (H1 in markdown = document title)
  if (/^# /.test(line) && !/^## /.test(line)) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const text = line.replace(/^# /, "").trim();
    children.push(new Paragraph({
      heading: HeadingLevel.TITLE,
      spacing: { before: 600, after: 200 },
      alignment: AlignmentType.CENTER,
      children: [new TextRun({ text, font: FONT, size: 48, bold: true, color: BRAND })]
    }));
    i++;
    continue;
  }

  // ## Chapter heading → page break + H1
  if (/^## /.test(line) && !/^### /.test(line)) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const text = line.replace(/^## /, "").trim();
    const isChapter = chapterPattern.test(line);

    if (isChapter && !firstChapter) {
      children.push(new Paragraph({ children: [new PageBreak()] }));
    }
    if (isChapter) firstChapter = false;

    children.push(new Paragraph({
      heading: HeadingLevel.HEADING_1,
      spacing: { before: 360, after: 200 },
      children: [new TextRun({ text, font: FONT, size: 32, bold: true, color: BRAND })]
    }));
    i++;
    continue;
  }

  // ### H2
  if (/^### /.test(line) && !/^#### /.test(line)) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const text = line.replace(/^### /, "").trim();
    children.push(new Paragraph({
      heading: HeadingLevel.HEADING_2,
      spacing: { before: 280, after: 140 },
      children: [new TextRun({ text, font: FONT, size: 26, bold: true, color: "2C5F8A" })]
    }));
    i++;
    continue;
  }

  // #### H3
  if (/^#### /.test(line)) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const text = line.replace(/^#### /, "").trim();
    children.push(new Paragraph({
      heading: HeadingLevel.HEADING_3,
      spacing: { before: 200, after: 100 },
      children: [new TextRun({ text, font: FONT, size: 22, bold: true, color: "3A6B9F" })]
    }));
    i++;
    continue;
  }

  // ── Blockquote ──
  if (/^>\s?/.test(line)) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const quoteLines = [];
    while (i < lines.length && /^>\s?/.test(lines[i])) {
      quoteLines.push(lines[i].replace(/^>\s?/, "").trim());
      i++;
    }
    // Remove empty lines between blockquote parts
    const quoteText = quoteLines.filter(l => l.length > 0);
    for (const ql of quoteText) {
      children.push(new Paragraph({
        spacing: { before: 40, after: 40 },
        indent: { left: 480 },
        border: { left: { style: BorderStyle.SINGLE, size: 6, color: "94A3B8", space: 10 } },
        shading: { fill: "F8FAFC", type: ShadingType.CLEAR },
        children: parseInline(ql, { color: "475569", size: 19 })
      }));
    }
    continue;
  }

  // ── Bullet list ──
  if (/^[-*]\s/.test(line.trim())) {
    if (!inBulletList) {
      currentBulletRef = getBulletRef();
      inBulletList = true;
    }
    const text = line.trim().replace(/^[-*]\s+/, "");
    children.push(new Paragraph({
      numbering: { reference: currentBulletRef, level: 0 },
      spacing: { before: 30, after: 30 },
      children: parseInline(text)
    }));
    i++;
    continue;
  }

  // ── Numbered list (1. 2. 3.) ──
  if (/^\d+\.\s/.test(line.trim())) {
    if (inBulletList) { inBulletList = false; currentBulletRef = null; }
    const text = line.trim().replace(/^\d+\.\s+/, "");
    children.push(new Paragraph({
      spacing: { before: 30, after: 30 },
      indent: { left: 720, hanging: 360 },
      children: parseInline(text)
    }));
    i++;
    continue;
  }

  // ── Regular paragraph ──
  if (inBulletList) { inBulletList = false; currentBulletRef = null; }
  children.push(new Paragraph({
    spacing: { before: 60, after: 60 },
    children: parseInline(line.trim())
  }));
  i++;
}

// ─── BUILD DOCUMENT ───
const doc = new Document({
  styles: {
    default: {
      document: {
        run: { font: FONT, size: 20 }
      }
    },
    paragraphStyles: [
      {
        id: "Title", name: "Title", basedOn: "Normal",
        run: { size: 48, bold: true, color: BRAND, font: FONT },
        paragraph: { spacing: { before: 600, after: 200 }, alignment: AlignmentType.CENTER }
      },
      {
        id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 32, bold: true, color: BRAND, font: FONT },
        paragraph: { spacing: { before: 360, after: 200 }, outlineLevel: 0 }
      },
      {
        id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 26, bold: true, color: "2C5F8A", font: FONT },
        paragraph: { spacing: { before: 280, after: 140 }, outlineLevel: 1 }
      },
      {
        id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
        run: { size: 22, bold: true, color: "3A6B9F", font: FONT },
        paragraph: { spacing: { before: 200, after: 100 }, outlineLevel: 2 }
      }
    ]
  },
  numbering: { config: numbConfigs },
  sections: [{
    properties: {
      page: {
        margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
        pageNumbers: { start: 1 }
      }
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          alignment: AlignmentType.RIGHT,
          children: [new TextRun({
            text: "두손기획인쇄 기술 매뉴얼 v2.0",
            font: FONT, size: 16, color: "999999", italics: true
          })]
        })]
      })
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          alignment: AlignmentType.CENTER,
          children: [
            new TextRun({ text: "Page ", font: FONT, size: 16, color: "999999" }),
            new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 16, color: "999999" }),
            new TextRun({ text: " of ", font: FONT, size: 16, color: "999999" }),
            new TextRun({ children: [PageNumber.TOTAL_PAGES], font: FONT, size: 16, color: "999999" })
          ]
        })]
      })
    },
    children
  }]
});

// ─── SAVE ───
const outPath = "/var/www/html/docs/두손기획인쇄_기술매뉴얼.docx";
Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync(outPath, buffer);
  console.log(`✅ Generated: ${outPath}`);
  console.log(`   Size: ${(buffer.length / 1024).toFixed(1)} KB`);
  console.log(`   Paragraphs: ${children.length}`);
  console.log(`   Bullet configs: ${numbConfigs.length}`);
}).catch(err => {
  console.error("❌ Error:", err.message);
  process.exit(1);
});
