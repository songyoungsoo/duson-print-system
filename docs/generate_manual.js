const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, HeadingLevel,
  BorderStyle, WidthType, ShadingType, VerticalAlign, PageNumber, PageBreak
} = require('docx');

// ─── Colors & Constants ───
const C = {
  primary: '1E4E79',    // header blue
  dark: '333333',
  gray: '666666',
  lightBg: 'F0F4F8',
  tableBorder: '94A3B8',
  headerBg: '1E4E79',
  altRow: 'F8FAFC',
  white: 'FFFFFF',
};
const FONT = 'Arial';
const W = 9360; // usable width DXA (letter 1" margins)

// ─── Helpers ───
const tb = { style: BorderStyle.SINGLE, size: 1, color: C.tableBorder };
const cellBorders = { top: tb, bottom: tb, left: tb, right: tb };

function hCell(text, width) {
  return new TableCell({
    borders: cellBorders, width: { size: width, type: WidthType.DXA },
    shading: { fill: C.headerBg, type: ShadingType.CLEAR },
    verticalAlign: VerticalAlign.CENTER,
    children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text, bold: true, color: C.white, size: 20, font: FONT })] })]
  });
}

function dCell(text, width, opts = {}) {
  return new TableCell({
    borders: cellBorders, width: { size: width, type: WidthType.DXA },
    shading: opts.alt ? { fill: C.altRow, type: ShadingType.CLEAR } : undefined,
    children: [new Paragraph({ alignment: opts.center ? AlignmentType.CENTER : AlignmentType.LEFT,
      children: [new TextRun({ text, size: 20, font: FONT, bold: opts.bold || false })] })]
  });
}

function makeTable(headers, rows, widths) {
  const cw = widths || headers.map(() => Math.floor(W / headers.length));
  return new Table({
    columnWidths: cw, margins: { top: 60, bottom: 60, left: 120, right: 120 },
    rows: [
      new TableRow({ tableHeader: true, children: headers.map((h, i) => hCell(h, cw[i])) }),
      ...rows.map((row, ri) => new TableRow({
        children: row.map((cell, ci) => dCell(cell, cw[ci], { alt: ri % 2 === 1, center: ci === 0, bold: ci === 0 }))
      }))
    ]
  });
}

function h1(text) { return new Paragraph({ heading: HeadingLevel.HEADING_1, spacing: { before: 400, after: 200 }, children: [new TextRun({ text, font: FONT })] }); }
function h2(text) { return new Paragraph({ heading: HeadingLevel.HEADING_2, spacing: { before: 300, after: 150 }, children: [new TextRun({ text, font: FONT })] }); }
function h3(text) { return new Paragraph({ heading: HeadingLevel.HEADING_3, spacing: { before: 200, after: 100 }, children: [new TextRun({ text, font: FONT })] }); }
function p(text, opts = {}) {
  return new Paragraph({ spacing: { after: 120 }, children: [new TextRun({ text, size: 22, font: FONT, bold: opts.bold, color: opts.color || C.dark })] });
}
function bullet(text, ref = 'bl') {
  return new Paragraph({ numbering: { reference: ref, level: 0 }, spacing: { after: 80 },
    children: [new TextRun({ text, size: 22, font: FONT })] });
}
function numbered(text, ref) {
  return new Paragraph({ numbering: { reference: ref, level: 0 }, spacing: { after: 80 },
    children: [new TextRun({ text, size: 22, font: FONT })] });
}
function spacer() { return new Paragraph({ spacing: { after: 200 }, children: [] }); }
function pb() { return new Paragraph({ children: [new PageBreak()] }); }
function tip(text) {
  return new Paragraph({ spacing: { after: 120 }, indent: { left: 360 },
    children: [
      new TextRun({ text: 'TIP: ', bold: true, size: 22, font: FONT, color: '2563EB' }),
      new TextRun({ text, size: 22, font: FONT, color: C.gray })
    ] });
}
function warn(text) {
  return new Paragraph({ spacing: { after: 120 }, indent: { left: 360 },
    children: [
      new TextRun({ text: '! ', bold: true, size: 22, font: FONT, color: 'DC2626' }),
      new TextRun({ text, size: 22, font: FONT, color: C.dark })
    ] });
}

// ─── Numbering configs ───
const numConfigs = [];
const numNames = [];
for (let i = 1; i <= 30; i++) {
  const name = `num-${i}`;
  numNames.push(name);
  numConfigs.push({
    reference: name,
    levels: [{ level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
      style: { paragraph: { indent: { left: 720, hanging: 360 } } } }]
  });
}

// ─── Document ───
const doc = new Document({
  styles: {
    default: { document: { run: { font: FONT, size: 22 } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 36, bold: true, color: C.primary, font: FONT },
        paragraph: { spacing: { before: 360, after: 200 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 28, bold: true, color: C.dark, font: FONT },
        paragraph: { spacing: { before: 240, after: 160 }, outlineLevel: 1 } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, color: C.gray, font: FONT },
        paragraph: { spacing: { before: 200, after: 120 }, outlineLevel: 2 } },
    ]
  },
  numbering: {
    config: [
      { reference: 'bl', levels: [{ level: 0, format: LevelFormat.BULLET, text: '\u2022', alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
      ...numConfigs
    ]
  },
  sections: [
    // ── Cover Page ──
    {
      properties: { page: { margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 } } },
      children: [
        spacer(), spacer(), spacer(), spacer(), spacer(), spacer(),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 200 },
          children: [new TextRun({ text: '\uB450\uC190\uAE30\uD68D\uC778\uC1C4', size: 56, bold: true, color: C.primary, font: FONT })] }),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 600 },
          children: [new TextRun({ text: '\uAD00\uB9AC\uC790 \uB300\uC2DC\uBCF4\uB4DC \uC6B4\uC601 \uB9E4\uB274\uC5BC', size: 40, color: C.dark, font: FONT })] }),
        spacer(),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 },
          children: [new TextRun({ text: 'https://dsp114.co.kr/dashboard/', size: 22, color: C.gray, font: FONT })] }),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 },
          children: [new TextRun({ text: '\uBC84\uC804 1.0 | 2026\uB144 2\uC6D4', size: 22, color: C.gray, font: FONT })] }),
        new Paragraph({ alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: '\uBB38\uC11C \uBD84\uB958: \uC0AC\uB0B4\uC6A9 (\uBE44\uACF5\uAC1C)', size: 20, color: C.gray, font: FONT })] }),
      ]
    },
    // ── TOC + Ch1~7 ──
    {
      properties: {
        page: { margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
          pageNumbers: { start: 1 } }
      },
      headers: {
        default: new Header({ children: [new Paragraph({ alignment: AlignmentType.RIGHT,
          children: [new TextRun({ text: '\uB450\uC190\uAE30\uD68D\uC778\uC1C4 \uAD00\uB9AC\uC790 \uB9E4\uB274\uC5BC', size: 16, color: C.gray, font: FONT })] })] })
      },
      footers: {
        default: new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: '\u2014 ', size: 18, font: FONT }), new TextRun({ children: [PageNumber.CURRENT], size: 18, font: FONT }),
            new TextRun({ text: ' / ', size: 18, font: FONT }), new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 18, font: FONT }),
            new TextRun({ text: ' \u2014', size: 18, font: FONT })] })] })
      },
      children: [
        // ── Chapter 1: 시작하기 ──
        h1('1. \uC2DC\uC791\uD558\uAE30'),

        h2('1.1 \uB300\uC2DC\uBCF4\uB4DC \uC811\uC18D'),
        p('\uC6F9 \uBE0C\uB77C\uC6B0\uC800\uC5D0\uC11C \uB2E4\uC74C \uC8FC\uC18C\uB85C \uC811\uC18D\uD569\uB2C8\uB2E4:'),
        p('https://dsp114.co.kr/dashboard/', { bold: true }),
        p('\uAD00\uB9AC\uC790 \uACC4\uC815\uC73C\uB85C \uB85C\uADF8\uC778\uD558\uBA74 \uB300\uC2DC\uBCF4\uB4DC \uBA54\uC778 \uD654\uBA74\uC774 \uD45C\uC2DC\uB429\uB2C8\uB2E4.'),
        tip('\uAD8C\uC7A5 \uBE0C\uB77C\uC6B0\uC800: Chrome, Edge (\uCD5C\uC2E0 \uBC84\uC804)'),

        h2('1.2 \uB300\uC2DC\uBCF4\uB4DC \uBA54\uC778 \uD654\uBA74'),
        p('\uB300\uC2DC\uBCF4\uB4DC \uBA54\uC778 \uD654\uBA74\uC5D0\uB294 \uB2E4\uC74C \uC694\uC57D \uC815\uBCF4\uAC00 \uD45C\uC2DC\uB429\uB2C8\uB2E4:'),
        bullet('\uC624\uB298\uC758 \uC8FC\uBB38 \uAC74\uC218 / \uB9E4\uCD9C\uC561'),
        bullet('\uC774\uBC88 \uB2EC \uB204\uC801 \uB9E4\uCD9C'),
        bullet('\uBBF8\uACB0\uC81C / \uACB0\uC81C\uC644\uB8CC \uD604\uD669'),
        bullet('\uCD5C\uADFC \uC8FC\uBB38 \uBAA9\uB85D (5\uAC74)'),

        h2('1.3 \uC0AC\uC774\uB4DC\uBC14 \uBA54\uB274 \uAD6C\uC131'),
        p('\uC88C\uCE21 \uC0AC\uC774\uB4DC\uBC14\uC5D0 5\uAC1C \uADF8\uB8F9, 21\uAC1C \uBA54\uB274\uAC00 \uBC30\uCE58\uB418\uC5B4 \uC788\uC2B5\uB2C8\uB2E4.'),
        makeTable(
          ['\uADF8\uB8F9', '\uBA54\uB274 \uD56D\uBAA9', '\uC6A9\uB3C4'],
          [
            ['\uC8FC\uBB38\xB7\uAD50\uC815', '\uAD00\uB9AC\uC790 \uC8FC\uBB38, \uC8FC\uBB38 \uAD00\uB9AC, \uAD50\uC815 \uAD00\uB9AC, \uAD50\uC815 \uB4F1\uB85D, \uACB0\uC81C \uD604\uD669, \uD0DD\uBC30 \uAD00\uB9AC, \uBC1C\uC1A1 \uBAA9\uB85D', '\uC8FC\uBB38 \uC811\uC218\uBD80\uD130 \uBC30\uC1A1\uAE4C\uC9C0 \uC804 \uACFC\uC815'],
            ['\uC18C\uD1B5\xB7\uACAC\uC801', '\uC774\uBA54\uC77C \uBC1C\uC1A1, \uCC44\uD305 \uAD00\uB9AC, \uACAC\uC801 \uAD00\uB9AC, \uACE0\uAC1D \uBB38\uC758', '\uACE0\uAC1D \uCEE4\uBBA4\uB2C8\uCF00\uC774\uC158 \uBC0F \uACAC\uC801'],
            ['\uC81C\uD488\xB7\uAC00\uACA9', '\uC81C\uD488 \uAD00\uB9AC, \uAC00\uACA9 \uAD00\uB9AC, \uACAC\uC801\uC635\uC158, \uC2A4\uD2F0\uCEE4\uC218\uC815, \uAC24\uB7EC\uB9AC \uAD00\uB9AC, \uD488\uBAA9\uC635\uC158', '\uC81C\uD488 \uBC0F \uAC00\uACA9\uD45C \uAD00\uB9AC'],
            ['\uAD00\uB9AC\xB7\uD1B5\uACC4', '\uD68C\uC6D0 \uAD00\uB9AC, \uC8FC\uBB38 \uD1B5\uACC4, \uBC29\uBB38\uC790\uBD84\uC11D, \uC0AC\uC774\uD2B8 \uC124\uC815', '\uD68C\uC6D0/\uD1B5\uACC4/\uC124\uC815'],
            ['\uAE30\uC874 \uAD00\uB9AC\uC790', '\uC8FC\uBB38 \uAD00\uB9AC(\uAD6C), \uAD50\uC815 \uAD00\uB9AC(\uAD6C)', '\uB808\uAC70\uC2DC \uD638\uD658\uC6A9'],
          ],
          [1500, 4000, 3860]
        ),

        pb(),
        // ── Chapter 2: 주문 관리 ──
        h1('2. \uC8FC\uBB38\xB7\uAD50\uC815 \uAD00\uB9AC'),

        h2('2.1 \uAD00\uB9AC\uC790 \uC8FC\uBB38 \uB4F1\uB85D'),
        p('\uC804\uD654/\uBE44\uD68C\uC6D0 \uC8FC\uBB38\uC744 \uAD00\uB9AC\uC790\uAC00 \uC9C1\uC811 \uB4F1\uB85D\uD569\uB2C8\uB2E4.'),
        numbered('\uC0AC\uC774\uB4DC\uBC14 > \uC8FC\uBB38\xB7\uAD50\uC815 > \uAD00\uB9AC\uC790 \uC8FC\uBB38 \uD074\uB9AD', numNames[0]),
        numbered('\uD488\uBAA9 \uC120\uD0DD \u2192 \uCE74\uD14C\uACE0\uB9AC/\uC218\uB7C9/\uC0AC\uC774\uC988 \uC785\uB825', numNames[0]),
        numbered('\uC8FC\uBB38\uC790 \uC815\uBCF4 (\uC774\uB984, \uC804\uD654, \uC774\uBA54\uC77C, \uC8FC\uC18C) \uC785\uB825', numNames[0]),
        numbered('\uAC00\uACA9 \uC218\uB3D9 \uC785\uB825 (\uACF5\uAE09\uAC00\uC561 + VAT \uC790\uB3D9 \uACC4\uC0B0)', numNames[0]),
        numbered('\uBC30\uC1A1\uBC29\uBC95/\uACB0\uC81C\uBC29\uBC95 \uC120\uD0DD \u2192 \uC8FC\uBB38 \uB4F1\uB85D', numNames[0]),
        tip('\uD0DD\uBC30 \uC120\uBD88 \uC120\uD0DD \uC2DC \uD0DD\uBC30\uBE44 \uAE08\uC561\uC744 \uC9C1\uC811 \uC785\uB825\uD560 \uC218 \uC788\uC2B5\uB2C8\uB2E4.'),

        h2('2.2 \uC8FC\uBB38 \uBAA9\uB85D \uBC0F \uC0C1\uD0DC \uAD00\uB9AC'),
        p('\uBAA8\uB4E0 \uC8FC\uBB38\uC744 \uD55C \uD654\uBA74\uC5D0\uC11C \uD655\uC778\uD558\uACE0 \uC0C1\uD0DC\uB97C \uBCC0\uACBD\uD569\uB2C8\uB2E4.'),
        h3('\uC8FC\uBB38 \uC0C1\uD0DC (OrderStyle)'),
        makeTable(
          ['\uCF54\uB4DC', '\uC0C1\uD0DC\uBA85', '\uC124\uBA85'],
          [
            ['0', '\uC811\uC218\uB300\uAE30', '\uACE0\uAC1D\uC774 \uC8FC\uBB38\uC744 \uC81C\uCD9C\uD55C \uC0C1\uD0DC'],
            ['1', '\uC785\uAE08\uB300\uAE30', '\uACB0\uC81C \uB300\uAE30 \uC911'],
            ['2', '\uC785\uAE08\uD655\uC778', '\uACB0\uC81C \uD655\uC778 \uC644\uB8CC'],
            ['3', '\uC811\uC218\uC644\uB8CC', '\uAD00\uB9AC\uC790\uAC00 \uC811\uC218 \uCC98\uB9AC'],
            ['4', '\uC2DC\uC548\uD655\uC778\uC911', '\uAD50\uC815 \uC2DC\uC548 \uD655\uC778 \uB2E8\uACC4'],
            ['5', '\uC778\uC1C4\uC900\uBE44', '\uC778\uC1C4 \uC900\uBE44 \uC911'],
            ['6', '\uC778\uC1C4\uC911', '\uC778\uC1C4 \uC9C4\uD589 \uC911'],
            ['7', '\uD6C4\uAC00\uACF5', '\uD6C4\uCC98\uB9AC \uB2E8\uACC4'],
            ['8', '\uC791\uC5C5\uC644\uB8CC', '\uC81C\uC791 \uC644\uB8CC'],
            ['9', '\uBC1C\uC1A1\uC644\uB8CC', '\uACE0\uAC1D\uC5D0\uAC8C \uBC1C\uC1A1 \uC644\uB8CC'],
            ['10', '\uC218\uB839\uC644\uB8CC/\uBC18\uD488', '\uACE0\uAC1D \uC218\uB839 \uB610\uB294 \uBC18\uD488'],
          ],
          [1200, 2000, 6160]
        ),
        p('\uBAA9\uB85D\uC5D0\uC11C \uB4DC\uB86D\uB2E4\uC6B4\uC73C\uB85C \uC9C1\uC811 \uC0C1\uD0DC \uBCC0\uACBD\uC774 \uAC00\uB2A5\uD569\uB2C8\uB2E4 (\uC778\uB77C\uC778 \uC0C1\uD0DC \uBCC0\uACBD).'),

        h2('2.3 \uC8FC\uBB38 \uC0C1\uC138 \uD654\uBA74'),
        p('\uC8FC\uBB38 \uBAA9\uB85D\uC5D0\uC11C \uC8FC\uBB38\uBC88\uD638\uB97C \uD074\uB9AD\uD558\uBA74 \uC0C1\uC138 \uD654\uBA74\uC73C\uB85C \uC774\uB3D9\uD569\uB2C8\uB2E4.'),
        bullet('\uC8FC\uBB38\uC790 \uC815\uBCF4: \uC774\uB984, \uC804\uD654, \uC774\uBA54\uC77C, \uC8FC\uC18C'),
        bullet('\uD488\uBAA9 \uC815\uBCF4: \uC81C\uD488\uBA85, \uC218\uB7C9, \uC0AC\uC774\uC988, \uB2E8\uAC00'),
        bullet('\uACB0\uC81C \uC815\uBCF4: \uACB0\uC81C\uBC29\uBC95, \uAE08\uC561, \uC785\uAE08\uC790\uBA85'),
        bullet('\uBC30\uC1A1 \uC815\uBCF4: \uBC30\uC1A1\uBC29\uBC95, \uD0DD\uBC30\uBE44, \uC1A1\uC7A5\uBC88\uD638'),
        warn('\uC785\uAE08\uC790\uBA85\uC774 \uC8FC\uBB38\uC790\uBA85\uACFC \uB2E4\uB97C \uACBD\uC6B0 \uC801\uC0C9 \uBC30\uACBD\uC73C\uB85C \uAC15\uC870 \uD45C\uC2DC\uB429\uB2C8\uB2E4.'),

        h2('2.4 \uAD50\uC815 \uAD00\uB9AC'),
        p('\uACE0\uAC1D\uC774 \uC5C5\uB85C\uB4DC\uD55C \uC6D0\uACE0/\uC2DC\uC548 \uD30C\uC77C\uC744 \uD655\uC778\uD558\uACE0 \uAD00\uB9AC\uD569\uB2C8\uB2E4.'),
        numbered('\uC0AC\uC774\uB4DC\uBC14 > \uAD50\uC815 \uAD00\uB9AC \uD074\uB9AD', numNames[1]),
        numbered('\uC8FC\uBB38\uBC88\uD638\uBCC4 \uD30C\uC77C \uBAA9\uB85D \uD655\uC778', numNames[1]),
        numbered('"\uBCF4\uAE30" \uBC84\uD2BC \uD074\uB9AD \u2192 \uC774\uBBF8\uC9C0 \uBDF0\uC5B4\uB85C \uC6D0\uBCF8 \uD06C\uAE30 \uD655\uC778', numNames[1]),
        numbered('\uD544\uC694 \uC2DC \uCD94\uAC00 \uD30C\uC77C \uC5C5\uB85C\uB4DC (\uB4DC\uB798\uADF8&\uB4DC\uB86D \uC9C0\uC6D0)', numNames[1]),
        p('\uC9C0\uC6D0 \uD30C\uC77C \uD615\uC2DD: JPG, PNG, GIF, PDF, AI, PSD, ZIP (\uAC01 20MB \uC81C\uD55C)'),

        h2('2.5 \uACB0\uC81C \uD604\uD669'),
        p('KG\uC774\uB2C8\uC2DC\uC2A4 \uCE74\uB4DC\uACB0\uC81C \uBC0F \uBB34\uD1B5\uC7A5\uC785\uAE08 \uD604\uD669\uC744 \uD655\uC778\uD569\uB2C8\uB2E4.'),
        bullet('\uACB0\uC81C \uC0C1\uD0DC: \uC131\uACF5/\uC2E4\uD328/\uB300\uAE30'),
        bullet('\uACB0\uC81C \uAE08\uC561, \uAC70\uB798\uBC88\uD638(TID), \uACB0\uC81C\uC77C\uC2DC'),
        bullet('\uCE74\uB4DC\uACB0\uC81C \uC644\uB8CC \uC2DC \uAD00\uB9AC\uC790 \uC774\uBA54\uC77C \uC790\uB3D9 \uC54C\uB9BC'),

        h2('2.6 \uD0DD\uBC30 \uAD00\uB9AC'),
        p('\uD0DD\uBC30 \uBC1C\uC1A1 \uC815\uBCF4\uB97C \uAD00\uB9AC\uD569\uB2C8\uB2E4.'),
        h3('\uBC30\uC1A1 \uC815\uBCF4 \uC785\uB825 \uD56D\uBAA9'),
        makeTable(
          ['\uD56D\uBAA9', '\uC124\uBA85', '\uBE44\uACE0'],
          [
            ['\uC6B4\uC784\uAD6C\uBD84', '\uCC29\uBD88/\uC120\uBD88 \uC120\uD0DD', '\uAE30\uBCF8\uAC12: \uCC29\uBD88'],
            ['\uBC15\uC2A4 \uC218\uB7C9', '\uAD00\uB9AC\uC790 \uC9C1\uC811 \uC785\uB825', '\uD68C\uC0AC\uB9C8\uB2E4 \uBC15\uC2A4 \uADDC\uACA9 \uB2E4\uB984'],
            ['\uD0DD\uBC30\uBE44', '\uAD00\uB9AC\uC790 \uC9C1\uC811 \uC785\uB825', '\uC804\uD654 \uD655\uC778 \uD6C4 \uD655\uC815'],
            ['\uC1A1\uC7A5\uBC88\uD638', '\uB85C\uC824\uD0DD\uBC30 \uC1A1\uC7A5\uBC88\uD638', '\uBC1C\uC1A1 \uD6C4 \uC785\uB825'],
          ],
          [2000, 4000, 3360]
        ),
        warn('\uBB34\uAC8C\uB294 \uC790\uB3D9 \uCD94\uC815\uB418\uC9C0\uB9CC, \uBC15\uC2A4\uC218/\uD0DD\uBC30\uBE44\uB294 \uBC18\uB4DC\uC2DC \uAD00\uB9AC\uC790\uAC00 \uC9C1\uC811 \uC785\uB825\uD574\uC57C \uD569\uB2C8\uB2E4.'),
        p('\uD0DD\uBC30\uBE44 \uC120\uBD88 \uD655\uC815 \uC2DC \uACE0\uAC1D\uC5D0\uAC8C \uC774\uBA54\uC77C\uC774 \uC790\uB3D9 \uBC1C\uC1A1\uB418\uBA70, \uACE0\uAC1D\uC740 \uB9C8\uC774\uD398\uC774\uC9C0\uC5D0\uC11C \uACB0\uC81C\uD560 \uC218 \uC788\uC2B5\uB2C8\uB2E4.'),

        pb(),
        // ── Chapter 3: 소통·견적 ──
        h1('3. \uC18C\uD1B5\xB7\uACAC\uC801'),

        h2('3.1 \uC774\uBA54\uC77C \uBC1C\uC1A1'),
        p('\uD68C\uC6D0\uC5D0\uAC8C \uC77C\uAD04 \uC774\uBA54\uC77C\uC744 \uBC1C\uC1A1\uD569\uB2C8\uB2E4.'),
        h3('\uC774\uBA54\uC77C \uC791\uC131 \uBC29\uBC95'),
        numbered('\uC0AC\uC774\uB4DC\uBC14 > \uC774\uBA54\uC77C \uBC1C\uC1A1 \uD074\uB9AD', numNames[2]),
        numbered('\uC218\uC2E0\uC790 \uC120\uD0DD: \uC804\uCCB4 \uD68C\uC6D0 / \uC870\uAC74 \uD544\uD130 / \uC9C1\uC811 \uC785\uB825', numNames[2]),
        numbered('WYSIWYG \uD3B8\uC9D1\uAE30\uB85C \uBCF8\uBB38 \uC791\uC131 (\uAD75\uC740\uAE00\uC528, \uC774\uBBF8\uC9C0 \uC0BD\uC785, \uB9C1\uD06C \uB4F1)', numNames[2]),
        numbered('\uD14C\uC2A4\uD2B8 \uBC1C\uC1A1 \u2192 \uBCF8\uC778 \uC774\uBA54\uC77C\uB85C \uD655\uC778', numNames[2]),
        numbered('\uBCF8 \uBC1C\uC1A1 (100\uBA85\uC529 \uBC30\uCE58, 3\uCD08 \uAC04\uACA9)', numNames[2]),
        warn('\uB124\uC774\uBC84 SMTP \uC0AC\uC6A9: \uC77C\uC77C \uC57D 500\uD1B5 \uD55C\uB3C4. Gmail \uC218\uC2E0 \uC2DC \uC2A4\uD338 \uBD84\uB958 \uAC00\uB2A5\uC131 \uC788\uC74C.'),
        tip('{{name}} \uD0DC\uADF8\uB85C \uC218\uC2E0\uC790 \uC774\uB984 \uC790\uB3D9 \uCE58\uD658 \uAC00\uB2A5'),

        h2('3.2 \uCC44\uD305 \uAD00\uB9AC'),
        p('\uC601\uC5C5\uC2DC\uAC04(09:00~18:30) \uC9C1\uC6D0 \uCC44\uD305 \uC704\uC82F\uACFC \uC57C\uAC04 AI \uCC47\uBD07\uC774 \uC790\uB3D9 \uC804\uD658\uB429\uB2C8\uB2E4.'),
        makeTable(
          ['\uC2DC\uAC04\uB300', '\uC704\uC82F', '\uC0C9\uC0C1'],
          [
            ['09:00~18:30', '\uC9C1\uC6D0 \uCC44\uD305 (\uC2E4\uC2DC\uAC04 \uC751\uB300)', '\uC8FC\uD669\uC0C9'],
            ['18:30~09:00', 'AI \uCC47\uBD07 (\uC790\uB3D9 \uAC00\uACA9 \uC870\uD68C)', '\uBCF4\uB77C\uC0C9'],
          ],
          [2500, 4500, 2360]
        ),
        p('AI \uCC47\uBD07\uC740 9\uAC1C \uD488\uBAA9 \uAC00\uACA9 \uC870\uD68C, \uC778\uC1C4 \uAC00\uC774\uB4DC, \uB514\uC790\uC778\uBE44, \uD30C\uC77C \uC81C\uCD9C \uC548\uB0B4 \uB4F1\uC744 \uC790\uB3D9 \uC751\uB2F5\uD569\uB2C8\uB2E4.'),

        h2('3.3 \uACAC\uC801 \uAD00\uB9AC'),
        p('\uACE0\uAC1D \uACAC\uC801\uC11C\uB97C \uC791\uC131\uD558\uACE0 \uC774\uBA54\uC77C\uB85C \uBC1C\uC1A1\uD569\uB2C8\uB2E4.'),
        h3('\uACAC\uC801\uC11C \uC0C1\uD0DC \uD750\uB984'),
        p('draft(\uC784\uC2DC\uC800\uC7A5) \u2192 sent(\uBC1C\uC1A1\uB428) \u2192 viewed(\uACE0\uAC1D \uC5F4\uB78C) \u2192 accepted/rejected(\uC2B9\uC778/\uAC70\uC808)', { bold: true }),
        h3('\uACAC\uC801\uBC88\uD638 \uCCB4\uACC4'),
        makeTable(
          ['\uC2DC\uC2A4\uD15C', '\uC811\uB450\uC5B4', '\uD615\uC2DD', '\uC608\uC2DC'],
          [
            ['\uAD00\uB9AC\uC790 \uACAC\uC801\uC11C', 'AQ', 'AQ-YYYYMMDD-NNNN', 'AQ-20260208-0004'],
            ['\uD50C\uB85C\uD305 \uACAC\uC801\uBC1B\uAE30', 'FQ', 'FQ-YYYYMMDD-NNN', 'FQ-20260216-001'],
          ],
          [2500, 1500, 3000, 2360]
        ),
        tip('\uACAC\uC801\uC11C\uB294 \uD31D\uC5C5 \uCC3D\uC73C\uB85C \uC5F4\uB9AC\uBA70, \uCF58\uD150\uCE20 \uD06C\uAE30\uC5D0 \uB9DE\uCDB0 \uC790\uB3D9 \uB9AC\uC0AC\uC774\uC988\uB429\uB2C8\uB2E4.'),
        p('\uACAC\uC801 \uBAA9\uB85D\uC5D0\uC11C \uAC1C\uBCC4/\uC77C\uAD04 \uC0AD\uC81C\uAC00 \uAC00\uB2A5\uD569\uB2C8\uB2E4 (\uD558\uB4DC \uC0AD\uC81C, \uBCF5\uAD6C \uBD88\uAC00).'),

        h2('3.4 \uACE0\uAC1D \uBB38\uC758'),
        p('\uD648\uD398\uC774\uC9C0 \uBB38\uC758\uAC8C\uC2DC\uD310\uC5D0\uC11C \uC811\uC218\uB41C \uBB38\uC758 \uBAA9\uB85D\uC744 \uD655\uC778\uD558\uACE0 \uB2F5\uBCC0\uD569\uB2C8\uB2E4.'),

        pb(),
        // ── Chapter 4: 제품·가격 ──
        h1('4. \uC81C\uD488\xB7\uAC00\uACA9 \uAD00\uB9AC'),

        h2('4.1 9\uAC1C \uD488\uBAA9 \uAC1C\uC694'),
        makeTable(
          ['#', '\uD488\uBAA9\uBA85', '\uD3F4\uB354\uBA85', '\uB2E8\uC704', '\uAC00\uACA9 \uBC29\uC2DD'],
          [
            ['1', '\uC804\uB2E8\uC9C0', 'inserted', '\uC5F0', 'DB \uC870\uD68C'],
            ['2', '\uC2A4\uD2F0\uCEE4', 'sticker_new', '\uB9E4', '\uC218\uD559 \uACF5\uC2DD'],
            ['3', '\uC790\uC11D\uC2A4\uD2F0\uCEE4', 'msticker', '\uB9E4', 'DB \uC870\uD68C'],
            ['4', '\uBA85\uD568', 'namecard', '\uB9E4', 'DB \uC870\uD68C'],
            ['5', '\uBD09\uD22C', 'envelope', '\uB9E4', 'DB \uC870\uD68C'],
            ['6', '\uD3EC\uC2A4\uD130', 'littleprint', '\uB9E4', 'DB \uC870\uD68C'],
            ['7', '\uC0C1\uD488\uAD8C', 'merchandisebond', '\uB9E4', 'DB \uC870\uD68C'],
            ['8', '\uCE74\uB2E4\uB85D', 'cadarok', '\uBD80', 'DB \uC870\uD68C'],
            ['9', 'NCR\uC591\uC2DD\uC9C0', 'ncrflambeau', '\uAD8C', 'DB \uC870\uD68C'],
          ],
          [600, 1800, 2200, 900, 3860]
        ),

        h2('4.2 \uC2A4\uD2F0\uCEE4 \uAC00\uACA9 \uACC4\uC0B0 (\uD2B9\uC218)'),
        p('\uC2A4\uD2F0\uCEE4\uB294 \uB2E4\uB978 8\uAC1C \uD488\uBAA9\uACFC \uB2EC\uB9AC DB \uAC00\uACA9\uD45C\uAC00 \uC544\uB2CC \uC218\uD559 \uACF5\uC2DD\uC73C\uB85C \uAC00\uACA9\uC744 \uACC4\uC0B0\uD569\uB2C8\uB2E4.'),
        p('\uC785\uB825 \uD30C\uB77C\uBBF8\uD130: \uC7AC\uC9C8(jong), \uAC00\uB85C(garo), \uC138\uB85C(sero), \uC218\uB7C9(mesu), \uB3C4\uBB34\uC1A1(domusong), \uB514\uC790\uC778(uhyung)'),
        p('\uACC4\uC0B0 \uD750\uB984: \uC7AC\uC9C8\uCF54\uB4DC \u2192 \uC218\uB7C9\uBCC4 \uC694\uC728 \uC870\uD68C \u2192 \uAE30\uBCF8\uAC00\uACA9 \u2192 \uB3C4\uBB34\uC1A1\uBE44 \u2192 \uD2B9\uC218\uC6A9\uC9C0\uBE44 \u2192 \uC0AC\uC774\uC988 \uB9C8\uC9C4 \u2192 \uAD00\uB9AC\uBE44 \u2192 \uB514\uC790\uC778\uBE44 = \uACF5\uAE09\uAC00 \u2192 \xD71.1 = VAT\uD3EC\uD568\uAC00', { bold: true }),
        warn('\uC2A4\uD2F0\uCEE4 \uAC00\uACA9\uC740 DB \uC870\uD68C\uAC00 \uC544\uB2CC \uC218\uD559 \uACF5\uC2DD\uC785\uB2C8\uB2E4. \uD639\uB3D9\uD558\uC9C0 \uB9C8\uC138\uC694.'),

        h2('4.3 \uAC00\uACA9 \uAD00\uB9AC'),
        p('\uAC01 \uD488\uBAA9\uC758 \uCE74\uD14C\uACE0\uB9AC\uBCC4 \uAC00\uACA9\uD45C\uB97C \uC218\uC815\uD569\uB2C8\uB2E4.'),
        bullet('\uCE74\uD14C\uACE0\uB9AC \uCD94\uAC00/\uC218\uC815/\uC0AD\uC81C'),
        bullet('\uC218\uB7C9\uBCC4 \uB2E8\uAC00 \uC124\uC815'),
        bullet('\uC2A4\uD2F0\uCEE4 \uC694\uC728 \uC218\uC815 (\uC2A4\uD2F0\uCEE4\uC218\uC815 \uBA54\uB274)'),

        h2('4.4 \uAC24\uB7EC\uB9AC \uAD00\uB9AC'),
        p('\uACE0\uAC1D \uC8FC\uBB38 \uAD50\uC815 \uC774\uBBF8\uC9C0 \uAC24\uB7EC\uB9AC\uB97C \uAD00\uB9AC\uD569\uB2C8\uB2E4. \uAC24\uB7EC\uB9AC \uC0D8\uD50C + \uC2E4\uC81C \uC8FC\uBB38 \uC774\uBBF8\uC9C0 \uD63C\uD569 \uD45C\uC2DC (24\uAC1C/\uD398\uC774\uC9C0).'),

        pb(),
        // ── Chapter 5: 관리·통계 ──
        h1('5. \uAD00\uB9AC\xB7\uD1B5\uACC4'),

        h2('5.1 \uD68C\uC6D0 \uAD00\uB9AC'),
        p('\uAC00\uC785 \uD68C\uC6D0 \uBAA9\uB85D\uC744 \uD655\uC778\uD558\uACE0 \uAD00\uB9AC\uD569\uB2C8\uB2E4.'),
        bullet('\uD68C\uC6D0 \uBAA9\uB85D \uC870\uD68C / \uAC80\uC0C9'),
        bullet('\uD68C\uC6D0 \uC815\uBCF4 \uC218\uC815'),
        bullet('\uC0AC\uC5C5\uC790 \uC815\uBCF4 \uD655\uC778'),
        p('\uBE44\uBC00\uBC88\uD638\uB294 bcrypt \uC554\uD638\uD654 \uC800\uC7A5\uB418\uBA70, \uAD6C \uD3C9\uBB38 \uBE44\uBC00\uBC88\uD638\uB294 \uB85C\uADF8\uC778 \uC2DC \uC790\uB3D9 \uC5C5\uADF8\uB808\uC774\uB4DC\uB429\uB2C8\uB2E4.'),

        h2('5.2 \uC8FC\uBB38 \uD1B5\uACC4'),
        p('\uAE30\uAC04\uBCC4/\uD488\uBAA9\uBCC4 \uC8FC\uBB38 \uD1B5\uACC4\uB97C \uD655\uC778\uD569\uB2C8\uB2E4.'),
        bullet('\uC694\uC57D \uCE74\uB4DC 4\uAC1C: \uC624\uB298 \uC8FC\uBB38, \uC774\uBC88 \uB2EC, \uC804\uCCB4 \uB204\uC801, \uD3C9\uADE0 \uB2E8\uAC00'),
        bullet('\uAE30\uAC04 \uD544\uD130: \uC624\uB298/7\uC77C/30\uC77C/\uC0AC\uC6A9\uC790 \uC9C0\uC815'),
        bullet('\uD488\uBAA9\uBCC4 \uB9E4\uCD9C \uBE44\uC728'),
        tip('\uCE74\uB4DC \uC22B\uC790\uB294 0\uC5D0\uC11C \uBAA9\uD45C\uAC12\uAE4C\uC9C0 \uCE74\uC6B4\uD2B8\uC5C5 \uC560\uB2C8\uBA54\uC774\uC158\uC73C\uB85C \uD45C\uC2DC\uB429\uB2C8\uB2E4.'),

        h2('5.3 \uBC29\uBB38\uC790 \uBD84\uC11D'),
        p('\uD648\uD398\uC774\uC9C0 \uBC29\uBB38\uC790 \uD1B5\uACC4\uB97C \uD655\uC778\uD569\uB2C8\uB2E4.'),
        bullet('\uC77C\uBCC4/\uC2DC\uAC04\uBCC4 \uBC29\uBB38\uC790 \uCD94\uC774'),
        bullet('\uC778\uAE30 \uD398\uC774\uC9C0 (\uC601\uBB38 URL \u2192 \uD55C\uAE00 \uD488\uBAA9\uBA85 \uC790\uB3D9 \uBCC0\uD658)'),
        bullet('\uC9C4\uC785/\uC774\uD0C8 \uD398\uC774\uC9C0'),
        bullet('\uC2E4\uC2DC\uAC04 \uBC29\uBB38\uC790 \uD14C\uC774\uBE14'),

        h2('5.4 \uC0AC\uC774\uD2B8 \uC124\uC815'),
        bullet('\uC601\uBB38 \uBC84\uC804 \uD45C\uC2DC ON/OFF \uD1A0\uAE00'),
        bullet('\uAE30\uD0C0 \uC0AC\uC774\uD2B8 \uAD00\uB828 \uC124\uC815'),

        pb(),
        // ── Chapter 6: 업무 시나리오 ──
        h1('6. \uC5C5\uBB34 \uC2DC\uB098\uB9AC\uC624'),

        h2('6.1 \uC77C\uBC18 \uC8FC\uBB38 \uCC98\uB9AC \uD50C\uB85C\uC6B0'),
        p('\uACE0\uAC1D\uC774 \uD648\uD398\uC774\uC9C0\uC5D0\uC11C \uC8FC\uBB38\uD55C \uACBD\uC6B0\uC758 \uCC98\uB9AC \uC808\uCC28\uC785\uB2C8\uB2E4.'),
        numbered('\uACE0\uAC1D \uC8FC\uBB38 \u2192 \uB300\uC2DC\uBCF4\uB4DC \uC8FC\uBB38\uBAA9\uB85D\uC5D0 \uC811\uC218\uB300\uAE30(0) \uC0C1\uD0DC\uB85C \uD45C\uC2DC', numNames[3]),
        numbered('\uC785\uAE08 \uD655\uC778 \u2192 \uC0C1\uD0DC\uB97C \uC785\uAE08\uD655\uC778(2)\uC73C\uB85C \uBCC0\uACBD', numNames[3]),
        numbered('\uC6D0\uACE0 \uD655\uC778 \u2192 \uAD50\uC815 \uAD00\uB9AC\uC5D0\uC11C \uD30C\uC77C \uD655\uC778', numNames[3]),
        numbered('\uC2DC\uC548 \uC81C\uC791 \u2192 \uAD50\uC815 \uD30C\uC77C \uC5C5\uB85C\uB4DC \u2192 \uC2DC\uC548\uD655\uC778\uC911(4)', numNames[3]),
        numbered('\uACE0\uAC1D \uD655\uC778 \u2192 \uC778\uC1C4\uC900\uBE44(5) \u2192 \uC778\uC1C4\uC911(6) \u2192 \uD6C4\uAC00\uACF5(7) \u2192 \uC791\uC5C5\uC644\uB8CC(8)', numNames[3]),
        numbered('\uD0DD\uBC30 \uBC1C\uC1A1 \u2192 \uC1A1\uC7A5\uBC88\uD638 \uC785\uB825 \u2192 \uBC1C\uC1A1\uC644\uB8CC(9)', numNames[3]),

        h2('6.2 \uD0DD\uBC30 \uC120\uBD88 \uC8FC\uBB38 \uCC98\uB9AC'),
        numbered('\uACE0\uAC1D\uC774 \uD0DD\uBC30 \uC120\uBD88\uB85C \uC8FC\uBB38', numNames[4]),
        numbered('\uC8FC\uBB38\uC644\uB8CC \uD398\uC774\uC9C0: "\uD0DD\uBC30\uBE44 \uD655\uC815 \uB300\uAE30" + \uACB0\uC81C\uBC84\uD2BC \uBE44\uD65C\uC131', numNames[4]),
        numbered('\uAD00\uB9AC\uC790: \uC8FC\uBB38 \uC0C1\uC138\uC5D0\uC11C \uD0DD\uBC30\uBE44 \uC785\uB825 \u2192 \uC800\uC7A5', numNames[4]),
        numbered('\uACE0\uAC1D\uC5D0\uAC8C \uD0DD\uBC30\uBE44 \uD655\uC815 \uC774\uBA54\uC77C \uC790\uB3D9 \uBC1C\uC1A1', numNames[4]),
        numbered('\uACE0\uAC1D: \uB9C8\uC774\uD398\uC774\uC9C0 \u2192 \uC8FC\uBB38\uC0C1\uC138 \u2192 \uACB0\uC81C\uD558\uAE30 (\uCE74\uB4DC/\uBB34\uD1B5\uC7A5)', numNames[4]),

        h2('6.3 \uACAC\uC801\uC11C \uBC1C\uC1A1 \uD50C\uB85C\uC6B0'),
        numbered('\uACAC\uC801 \uAD00\uB9AC \u2192 \uC0C8 \uACAC\uC801 \uC791\uC131', numNames[5]),
        numbered('\uD488\uBAA9/\uC218\uB7C9/\uAE08\uC561 \uC785\uB825 \u2192 \uC800\uC7A5 (draft)', numNames[5]),
        numbered('\uBBF8\uB9AC\uBCF4\uAE30\uB85C \uD655\uC778', numNames[5]),
        numbered('"\uBC1C\uC1A1" \uBC84\uD2BC \uD074\uB9AD \u2192 \uACE0\uAC1D \uC774\uBA54\uC77C\uB85C \uBC1C\uC1A1 (sent)', numNames[5]),
        numbered('\uACE0\uAC1D \uC5F4\uB78C \u2192 viewed \u2192 \uC2B9\uC778/\uAC70\uC808', numNames[5]),

        h2('6.4 \uC804\uD654 \uC8FC\uBB38 \uB4F1\uB85D'),
        numbered('\uC804\uD654\uB85C \uC8FC\uBB38 \uC811\uC218', numNames[6]),
        numbered('\uAD00\uB9AC\uC790 \uC8FC\uBB38 \uBA54\uB274\uC5D0\uC11C \uC9C1\uC811 \uB4F1\uB85D', numNames[6]),
        numbered('\uD488\uBAA9/\uC218\uB7C9/\uAC00\uACA9 \uC785\uB825 + \uC8FC\uBB38\uC790 \uC815\uBCF4 \uC785\uB825', numNames[6]),
        numbered('\uC8FC\uBB38 \uB4F1\uB85D \uC644\uB8CC \u2192 \uC8FC\uBB38 \uBAA9\uB85D\uC5D0\uC11C \uD655\uC778', numNames[6]),

        pb(),
        // ── Chapter 7: 주의사항 ──
        h1('7. \uC8FC\uC758\uC0AC\uD56D \uBC0F FAQ'),

        h2('7.1 \uC77C\uBC18 \uC8FC\uC758\uC0AC\uD56D'),
        bullet('\uBE0C\uB77C\uC6B0\uC800 \uCE90\uC2DC \uBB38\uC81C: \uD654\uBA74\uC774 \uC774\uC0C1\uD558\uBA74 Ctrl+Shift+Delete \uB85C \uCE90\uC2DC \uC0AD\uC81C \uD6C4 \uC0C8\uB85C\uACE0\uCE68'),
        bullet('\uB3D9\uC2DC \uC811\uC18D: \uC5EC\uB7EC \uD0ED\uC5D0\uC11C \uAC19\uC740 \uC8FC\uBB38\uC744 \uC218\uC815\uD558\uBA74 \uCDA9\uB3CC \uAC00\uB2A5'),
        bullet('\uC138\uC158 \uB9CC\uB8CC: 8\uC2DC\uAC04 \uBBF8\uC0AC\uC6A9 \uC2DC \uC790\uB3D9 \uB85C\uADF8\uC544\uC6C3'),

        h2('7.2 \uACB0\uC81C \uAD00\uB828'),
        bullet('\uCE74\uB4DC\uACB0\uC81C \uD14C\uC2A4\uD2B8: \uB85C\uCEEC\uD638\uC2A4\uD2B8\uC5D0\uC11C\uB294 \uBC18\uB4DC\uC2DC \uD14C\uC2A4\uD2B8 \uBAA8\uB4DC\uB85C \uC0AC\uC6A9'),
        bullet('\uC6B4\uC601 \uC11C\uBC84(dsp114.co.kr)\uC5D0\uC11C\uB9CC \uC2E4\uC81C \uACB0\uC81C \uCC98\uB9AC'),
        bullet('\uACB0\uC81C \uC2E4\uD328 \uC2DC payment/logs/ \uD3F4\uB354 \uD655\uC778'),
        warn('\uD14C\uC2A4\uD2B8 \uC2DC \uC18C\uC561(100~1,000\uC6D0)\uC73C\uB85C \uBA3C\uC800 \uD14C\uC2A4\uD2B8\uD558\uC138\uC694.'),

        h2('7.3 \uD0DD\uBC30 \uAD00\uB828'),
        bullet('\uBB34\uAC8C\uB294 \uC790\uB3D9 \uCD94\uC815\uC774\uBA70, \uC2E4\uC81C\uC640 \uB2E4\uB97C \uC218 \uC788\uC74C'),
        bullet('\uBC15\uC2A4\uC218\uC640 \uD0DD\uBC30\uBE44\uB294 \uBC18\uB4DC\uC2DC \uAD00\uB9AC\uC790\uAC00 \uC9C1\uC811 \uC785\uB825'),
        bullet('\uD0DD\uBC30\uBE44 \uC120\uBD88 \uD655\uC815 \uC2DC \uACE0\uAC1D \uC774\uBA54\uC77C \uC790\uB3D9 \uBC1C\uC1A1'),

        h2('7.4 \uC774\uBA54\uC77C \uAD00\uB828'),
        bullet('\uB124\uC774\uBC84 SMTP: \uC77C\uC77C 500\uD1B5 \uC81C\uD55C'),
        bullet('\uB124\uC774\uBC84 \u2192 \uB124\uC774\uBC84: \uC815\uC0C1 \uBC1C\uC1A1'),
        bullet('\uB124\uC774\uBC84 \u2192 Gmail: \uC2A4\uD338 \uBD84\uB958 \uAC00\uB2A5\uC131 \uC788\uC74C'),

        h2('7.5 \uAE34\uAE09 \uC5F0\uB77D\uCC98'),
        makeTable(
          ['\uD56D\uBAA9', '\uC5F0\uB77D\uCC98'],
          [
            ['\uACE0\uAC1D\uC13C\uD130', '02-2632-1830'],
            ['\uC774\uBA54\uC77C', 'dsp1830@naver.com'],
            ['\uC8FC\uC18C', '\uC11C\uC6B8 \uC601\uB4F1\uD3EC\uAD6C \uC601\uB4F1\uD3EC\uB85C36\uAE3809 \uC1A1\uD638\uBE4C\uB529 1\uCE35'],
            ['\uC6B4\uC601\uC2DC\uAC04', '\uD3C9\uC77C 09:00~18:30'],
          ],
          [2500, 6860]
        ),

        spacer(),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 400 },
          children: [new TextRun({ text: '\u2014 \uB05D \u2014', size: 22, color: C.gray, font: FONT })] }),
      ]
    }
  ]
});

// ─── Generate ───
const outDir = '/var/www/html/docs';
Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync(`${outDir}/두손기획인쇄_관리자매뉴얼.docx`, buffer);
  console.log('DOCX generated successfully:', `${outDir}/두손기획인쇄_관리자매뉴얼.docx`);
}).catch(err => {
  console.error('Error generating DOCX:', err);
  process.exit(1);
});
