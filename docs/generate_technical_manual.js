const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, HeadingLevel,
  BorderStyle, WidthType, ShadingType, VerticalAlign, PageNumber, PageBreak
} = require('docx');

// ─── Colors & Constants ───
const C = {
  primary: '1E4E79', dark: '333333', gray: '666666',
  tableBorder: '94A3B8', headerBg: '1E4E79', altRow: 'F8FAFC',
  white: 'FFFFFF', codeBg: 'F5F5F5',
};
const FONT = 'Arial';
const CODE_FONT = 'Courier New';
const W = 9360;

// ─── Helpers (from generate_manual.js) ───
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

// ─── Additional Helpers ───
function codeBlock(lines) {
  const noBdr = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
  const nb = { top: noBdr, bottom: noBdr, left: noBdr, right: noBdr };
  return [new Table({
    columnWidths: [W],
    rows: lines.map(line => new TableRow({
      children: [new TableCell({
        borders: nb, width: { size: W, type: WidthType.DXA },
        shading: { fill: C.codeBg, type: ShadingType.CLEAR },
        children: [new Paragraph({ indent: { left: 200 },
          children: [new TextRun({ text: line || ' ', size: 18, font: CODE_FONT, color: C.dark })] })]
      })]
    }))
  })];
}

function flowArrow(steps) {
  return new Paragraph({
    spacing: { before: 120, after: 160 },
    alignment: AlignmentType.CENTER,
    children: [new TextRun({ text: steps.join(' \u2192 '), size: 22, font: FONT, bold: true, color: C.primary })]
  });
}

// ─── Numbering configs ───
const numConfigs = [];
const numNames = [];
for (let i = 1; i <= 15; i++) {
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
          children: [new TextRun({ text: '\uAE30\uC220 \uB9E4\uB274\uC5BC', size: 40, color: C.dark, font: FONT })] }),
        spacer(),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 },
          children: [new TextRun({ text: '\uBB38\uC11C \uAE30\uC900\uC77C: 2026-02-22', size: 22, color: C.gray, font: FONT })] }),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { after: 100 },
          children: [new TextRun({ text: '\uBC84\uC804 1.0', size: 22, color: C.gray, font: FONT })] }),
        new Paragraph({ alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: '\uBB38\uC11C \uBD84\uB958: \uC0AC\uB0B4\uC6A9 (\uBE44\uACF5\uAC1C)', size: 20, color: C.gray, font: FONT })] }),
      ]
    },
    // ── Content (Ch0~Ch7) ──
    {
      properties: {
        page: { margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
          pageNumbers: { start: 1 } }
      },
      headers: {
        default: new Header({ children: [new Paragraph({ alignment: AlignmentType.RIGHT,
          children: [new TextRun({ text: '\uB450\uC190\uAE30\uD68D\uC778\uC1C4 \uAE30\uC220 \uB9E4\uB274\uC5BC', size: 16, color: C.gray, font: FONT })] })] })
      },
      footers: {
        default: new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER,
          children: [new TextRun({ text: '\u2014 ', size: 18, font: FONT }), new TextRun({ children: [PageNumber.CURRENT], size: 18, font: FONT }),
            new TextRun({ text: ' / ', size: 18, font: FONT }), new TextRun({ children: [PageNumber.TOTAL_PAGES], size: 18, font: FONT }),
            new TextRun({ text: ' \u2014', size: 18, font: FONT })] })] })
      },
      children: [

        // ════════════════════════════════════════
        // Ch0. 접속 정보
        // ════════════════════════════════════════
        h1('Ch0. \uC811\uC18D \uC815\uBCF4'),

        h2('0.1 \uC8FC\uC694 \uC811\uC18D \uC815\uBCF4'),
        makeTable(
          ['\uAD6C\uBD84', '\uC811\uC18D \uC8FC\uC18C', '\uC544\uC774\uB514', '\uBE44\uBC00\uBC88\uD638'],
          [
            ['\uD648\uD398\uC774\uC9C0', 'https://dsp114.co.kr', '-', '-'],
            ['\uAD00\uB9AC\uC790 \uB300\uC2DC\uBCF4\uB4DC', 'https://dsp114.co.kr/dashboard/', 'admin', 'admin123'],
            ['\uB370\uC774\uD130\uBCA0\uC774\uC2A4 (MySQL)', 'localhost:3306', 'dsp1830', 'ds701018'],
            ['FTP (\uC6B4\uC601\uC11C\uBC84)', 'ftp://dsp114.co.kr', 'dsp1830', 'cH*j@yzj093BeTtc'],
            ['GitHub', 'github.com/songyoungsoo', 'songyoungsoo', 'yeongsu32@gmail.com'],
            ['\uACE0\uAC1D\uC13C\uD130 \uC804\uD654', '02-2632-1830', '-', '-'],
          ],
          [2000, 3200, 1800, 2360]
        ),

        h2('0.2 \uC11C\uBC84 \uD658\uACBD'),
        bullet('PHP 7.4+ (\uB85C\uCEEC) / PHP 8.2 (\uD504\uB85C\uB355\uC158), MySQL 5.7+'),
        bullet('\uB85C\uCEEC Document Root: /var/www/html'),
        bullet('\uD504\uB85C\uB355\uC158 FTP \uC6F9 \uB8E8\uD2B8: /httpdocs/ (\uBC18\uB4DC\uC2DC \uC774 \uACBD\uB85C!)'),
        bullet('\uD658\uACBD \uC790\uB3D9 \uAC10\uC9C0: config.env.php (SERVER_NAME \uAE30\uBC18)'),
        warn('\uD504\uB85C\uB355\uC158 \uBC30\uD3EC \uC2DC \uBAA8\uB4E0 \uD30C\uC77C\uC740 /httpdocs/ \uD558\uC704\uC5D0 \uC5C5\uB85C\uB4DC\uD574\uC57C \uD569\uB2C8\uB2E4.'),
        tip('PHP 8.2\uC5D0\uC11C\uB294 mysqli_close($db) \uD6C4 $db \uC0AC\uC6A9 \uC2DC Fatal Error \uBC1C\uC0DD. \uD56D\uC0C1 \uD398\uC774\uC9C0 \uB9E8 \uB9C8\uC9C0\uB9C9\uC5D0 \uB2EB\uC744 \uAC83.'),

        pb(),
        // ════════════════════════════════════════
        // Ch1. 대문 페이지
        // ════════════════════════════════════════
        h1('Ch1. \uB300\uBB38 \uD398\uC774\uC9C0 (index.php)'),

        h2('1.1 \uCE90\uB7EC\uC140 \uBC30\uB108'),
        bullet('\uC21C\uC218 CSS+JS \uAD6C\uD604 (\uC678\uBD80 \uB77C\uC774\uBE0C\uB7EC\uB9AC \uC5C6\uC74C)'),
        bullet('8\uAC1C \uC2E4\uC81C + 2\uAC1C \uD074\uB860 = 10\uAC1C (\uBB34\uD55C\uB8E8\uD504)'),
        bullet('setInterval(nextSlide, 4000) \u2014 4\uCD08 \uC790\uB3D9\uC7AC\uC0DD'),
        bullet('CSS transition: transform 1000ms ease-in-out'),

        h3('\uD575\uC2EC JS \uD568\uC218'),
        makeTable(
          ['\uD568\uC218\uBA85', '\uC5ED\uD560'],
          [
            ['nextSlide()', '\uB2E4\uC74C \uC2AC\uB77C\uC774\uB4DC'],
            ['prevSlide()', '\uC774\uC804 \uC2AC\uB77C\uC774\uB4DC'],
            ['goToSlide(index)', '\uD2B9\uC815 \uC2AC\uB77C\uC774\uB4DC\uB85C \uC774\uB3D9'],
            ['toggleHeroVideo()', '\uBE44\uB514\uC624 \uC7AC\uC0DD/\uC815\uC9C0'],
          ],
          [3000, 6360]
        ),

        h3('\uC2AC\uB77C\uC774\uB4DC \uC774\uBBF8\uC9C0 \uACBD\uB85C'),
        ...codeBlock([
          '/slide/slide_inserted.gif',
          '/slide/slide__Sticker.gif',
          '/slide/slide_cadarok.gif',
          '/slide/slide_Ncr.gif',
          '/slide/slide__poster.gif',
          '/slide/slide__Sticker_2.gif',
          '/slide/slide__Sticker_3.gif',
          '/media/explainer_poster.jpg',
        ]),

        h2('1.2 \uC81C\uD488 \uCE74\uB4DC'),
        p('12\uAC1C \uC81C\uD488 \uCE74\uB4DC (9\uAC1C \uC628\uB77C\uC778\uC8FC\uBB38 + 3\uAC1C \uBCC4\uB3C4\uACAC\uC801)'),
        bullet('\uC774\uBBF8\uC9C0 \uACBD\uB85C: /ImgFolder/gate_picto/{product}_s.png'),
        bullet('CSS Grid: .products-grid (css/product-layout.css)'),

        pb(),
        // ════════════════════════════════════════
        // Ch2. 품목별 가격 계산기
        // ════════════════════════════════════════
        h1('Ch2. \uD488\uBAA9\uBCC4 \uAC00\uACA9 \uACC4\uC0B0\uAE30'),

        h2('2.1 \uD328\uD134 A: DB Cascade (8\uAC1C \uC81C\uD488)'),
        p('Cascade \uD750\uB984: \uC885\uB958(MY_type) \u2192 \uC7AC\uC9C8(Section) \u2192 \uC218\uB7C9(MY_amount) \u2192 \uAC00\uACA9'),

        h3('AJAX \uC5D4\uB4DC\uD3EC\uC778\uD2B8'),
        makeTable(
          ['\uC5D4\uB4DC\uD3EC\uC778\uD2B8', '\uBA54\uC11C\uB4DC', '\uD30C\uB77C\uBBF8\uD130', '\uC751\uB2F5'],
          [
            ['get_paper_types.php', 'GET', 'style={typeValue}', '\uC7AC\uC9C8 \uC635\uC158'],
            ['get_quantities.php', 'GET', 'style={}&section={}&potype={}', '\uC218\uB7C9 \uC635\uC158'],
            ['calculate_price_ajax.php', 'GET', 'MY_type, Section, POtype, MY_amount, ordertype', '{price, vat_price}'],
          ],
          [2800, 1000, 3200, 2360]
        ),

        h3('DB \uD14C\uC774\uBE14'),
        bullet('mlangprintauto_transactioncate: BigNo, TreeNo, Ttable, title'),
        bullet('mlangprintauto_{product}: style, Section, quantity, money'),
        p('\uD575\uC2EC JS: handleTypeChange(), handleSectionChange(), calculatePrice()'),

        h2('2.2 \uD328\uD134 B: \uC218\uD559 \uACF5\uC2DD (\uC2A4\uD2F0\uCEE4)'),
        p('\uD30C\uC77C: mlangprintauto/sticker_new/calculate_price_ajax.php (243\uC904)'),
        h3('\uACC4\uC0B0 \uACF5\uC2DD'),
        ...codeBlock([
          '\uAE30\uBCF8\uAC00\uACA9 = (\uAC00\uB85C+4) \u00D7 (\uC138\uB85C+4) \u00D7 \uC218\uB7C9 \u00D7 \uC694\uC728(yoyo)',
          '+ \uB3C4\uBB34\uC1A1\uBE44\uC6A9 (\uCE7C\uD06C\uAE30 \u00D7 \uC218\uB7C9 \uAE30\uBC18)',
          '+ \uD2B9\uC218\uC6A9\uC9C0\uBE44\uC6A9 (\uC720\uD3EC\uC9C0/\uAC15\uC811/\uCD08\uAC15\uC811)',
          '\u00D7 \uC0AC\uC774\uC988 \uB9C8\uC9C4\uBE44\uC728 (\uC18C\uD615 1.0, \uB300\uD615 1.25)',
          '+ \uAE30\uBCF8\uAD00\uB9AC\uBE44(mg) \u00D7 \uC218\uB7C9/1000',
          '+ \uB514\uC790\uC778\uBE44(uhyung)',
          '= \uACF5\uAE09\uAC00\uC561 \u2192 \u00D71.1 = VAT\uD3EC\uD568\uAC00',
        ]),

        h3('\uC7AC\uC9C8\uBCC4 \uC694\uC728 \uD14C\uC774\uBE14'),
        makeTable(
          ['\uCF54\uB4DC', 'DB \uD14C\uC774\uBE14', '\uC7AC\uC9C8'],
          [
            ['jil', 'shop_d1', '\uC544\uD2B8\uC720\uAD11/\uBB34\uAD11/\uBE44\uCF54\uD305, \uBAA8\uC870\uBE44\uCF54\uD305'],
            ['jka', 'shop_d2', '\uAC15\uC811\uC544\uD2B8\uC720\uAD11\uCF54\uD305'],
            ['jsp', 'shop_d3', '\uC720\uD3EC\uC9C0, \uC740\uB370\uB4DC\uB871, \uD22C\uBA85, \uD06C\uB77C\uD504\uD2B8'],
            ['cka', 'shop_d4', '\uCD08\uAC15\uC811\uC544\uD2B8\uCF54\uD305/\uBE44\uCF54\uD305'],
          ],
          [1500, 2500, 5360]
        ),
        warn('\uC2A4\uD2F0\uCEE4 \uAC00\uACA9\uC740 DB \uC870\uD68C\uAC00 \uC544\uB2CC \uC218\uD559 \uACF5\uC2DD\uC785\uB2C8\uB2E4. \uD639\uB3D9\uD558\uC9C0 \uB9C8\uC138\uC694.'),

        pb(),
        // ════════════════════════════════════════
        // Ch3. 갤러리 시스템
        // ════════════════════════════════════════
        h1('Ch3. \uAC24\uB7EC\uB9AC \uC2DC\uC2A4\uD15C'),

        h2('3.1 \uC81C\uD488 \uD398\uC774\uC9C0 \uAC24\uB7EC\uB9AC'),
        p('Include \uCCB4\uC778:'),
        flowArrow(['simple_gallery_include.php', 'gallery_data_adapter.php', 'new_gallery_wrapper.php']),
        bullet('500\u00D7400 \uCEE8\uD14C\uC774\uB108, 200% \uB9C8\uC6B0\uC2A4 \uC624\uBC84 \uC90C'),

        h2('3.2 \uC0D8\uD50C\uB354\uBCF4\uAE30 \uD31D\uC5C5'),
        p('URL: popup/proof_gallery.php?cate={\uC81C\uD488\uBA85}&page={N}'),
        bullet('24\uAC1C/\uD398\uC774\uC9C0, 6\uC5F4 \uADF8\uB9AC\uB4DC'),

        h3('\uB4C0\uC5BC \uC18C\uC2A4'),
        numbered('\uAC24\uB7EC\uB9AC \uC0D8\uD50C: /ImgFolder/sample/{product}/ + /ImgFolder/samplegallery/{product}/', numNames[0]),
        numbered('\uC2E4\uC81C \uC8FC\uBB38 \uC774\uBBF8\uC9C0: /mlangorder_printauto/upload/{\uC8FC\uBB38\uBC88\uD638}/ (DB ThingCate)', numNames[0]),

        h2('3.3 \uAC1C\uC778\uC815\uBCF4 \uBCF4\uD638'),
        p('\uBA85\uD568/\uBD09\uD22C/\uC591\uC2DD\uC9C0/\uC2A4\uD2F0\uCEE4/\uC804\uB2E8\uC9C0 \u2192 \uAC24\uB7EC\uB9AC \uC774\uBBF8\uC9C0\uB9CC \uD45C\uC2DC (\uAC1C\uC778\uC815\uBCF4 \uD544\uD130\uB9C1)'),

        h2('3.4 \uB77C\uC774\uD2B8\uBC15\uC2A4'),
        bullet('\uD074\uB9AD \u2192 fixed overlay \uD45C\uC2DC'),
        bullet('ESC \uB2EB\uAE30, \u2039 \u203A \uBC29\uD5A5\uD0A4 \uB124\uBE44\uAC8C\uC774\uC158'),
        bullet('\uACF5\uD1B5 JS: js/common-gallery-popup.js'),

        pb(),
        // ════════════════════════════════════════
        // Ch4. 파일 업로드 시스템
        // ════════════════════════════════════════
        h1('Ch4. \uD30C\uC77C \uC5C5\uB85C\uB4DC \uC2DC\uC2A4\uD15C'),

        h2('4.1 \uC5C5\uB85C\uB4DC \uBAA8\uB2EC'),
        p('\uD30C\uC77C: includes/upload_modal.php + upload_modal.js'),
        bullet('CSS: css/upload-modal-common.css'),
        bullet('2\uBAA8\uB4DC: \uC644\uC131\uD30C\uC77C \uC5C5\uB85C\uB4DC / \uB514\uC790\uC778 \uC758\uB8B0'),
        bullet('\uB4DC\uB86D\uC874: #modalUploadDropzone'),
        bullet('\uD5C8\uC6A9: JPG, PNG, PDF, AI, EPS, PSD, ZIP (15MB \uC774\uD558)'),

        h3('\uD575\uC2EC JS \uD568\uC218'),
        makeTable(
          ['\uD568\uC218\uBA85', '\uC5ED\uD560'],
          [
            ['selectUploadMethod(type)', '"upload" \uB610\uB294 "design" \uBAA8\uB4DC \uC804\uD658'],
            ['proceedToDesignUpload()', '\uB514\uC790\uC778 \uC758\uB8B0 \uD504\uB85C\uC138\uC2A4 \uC2DC\uC791'],
            ['window.handleModalBasketAdd()', 'POST /mlangprintauto/shop/add_to_basket.php'],
          ],
          [3500, 5860]
        ),

        h2('4.2 \uAD00\uB9AC\uC790 \uAD50\uC815 \uC5C5\uB85C\uB4DC'),
        p('API: POST dashboard/proofs/api.php?action=upload'),
        bullet('\uC800\uC7A5 \uACBD\uB85C: /mlangorder_printauto/upload/{\uC8FC\uBB38\uBC88\uD638}/'),
        bullet('\uC9C0\uC6D0 \uD615\uC2DD: JPG, PNG, GIF, PDF, AI, PSD, ZIP (\uAC01 20MB)'),

        pb(),
        // ════════════════════════════════════════
        // Ch5. 주문 프로세스
        // ════════════════════════════════════════
        h1('Ch5. \uC8FC\uBB38 \uD504\uB85C\uC138\uC2A4'),

        h2('5.1 \uC804\uCCB4 \uD750\uB984'),
        flowArrow(['OnlineOrder_unified.php', 'ProcessOrder_unified.php', 'mlangorder_printauto (DB)', 'OrderComplete_unified.php', 'send_order_email.php']),

        h2('5.2 ProcessOrder_unified.php \uCC98\uB9AC \uB2E8\uACC4'),
        numbered('CSRF \uD1A0\uD070 \uAC80\uC99D', numNames[1]),
        numbered('POST \uB370\uC774\uD130 \uC218\uC9D1', numNames[1]),
        numbered('shop_temp \uC7A5\uBC14\uAD6C\uB2C8 \uC544\uC774\uD15C \uBC18\uBCF5', numNames[1]),
        numbered('mlangorder_printauto INSERT (55\uAC1C bind_param)', numNames[1]),
        numbered('Dual-Write: orders + order_items', numNames[1]),
        numbered('\uD30C\uC77C \uC774\uB3D9: temp \u2192 uploads/orders/{order_no}/', numNames[1]),
        numbered('shop_temp \uC815\uB9AC', numNames[1]),

        h2('5.3 DB \uD14C\uC774\uBE14'),
        makeTable(
          ['\uD14C\uC774\uBE14', '\uC6A9\uB3C4', '\uD575\uC2EC \uCEEC\uB7FC'],
          [
            ['shop_temp', '\uC7A5\uBC14\uAD6C\uB2C8', 'session_id, price, quantity, product_type'],
            ['mlangorder_printauto', '\uC8FC\uBB38', 'no, name, phone, money_5, OrderStyle'],
            ['orders', '\uC8FC\uBB38(\uC2E0\uADDC)', 'order_no, user_id, total_amount'],
            ['order_items', '\uC8FC\uBB38\uD488\uBAA9(\uC2E0\uADDC)', 'order_id, product_type, quantity'],
          ],
          [2500, 2000, 4860]
        ),

        h2('5.4 OrderStyle \uC0C1\uD0DC \uCF54\uB4DC'),
        makeTable(
          ['\uCF54\uB4DC', '\uC0C1\uD0DC'],
          [
            ['0', '\uC811\uC218\uB300\uAE30'], ['1', '\uC785\uAE08\uB300\uAE30'], ['2', '\uC785\uAE08\uD655\uC778'],
            ['3', '\uC811\uC218\uC644\uB8CC'], ['4', '\uC2DC\uC548\uD655\uC778\uC911'], ['5', '\uC778\uC1C4\uC900\uBE44'],
            ['6', '\uC778\uC1C4\uC911'], ['7', '\uD6C4\uAC00\uACF5'], ['8', '\uC791\uC5C5\uC644\uB8CC'],
            ['9', '\uBC1C\uC1A1\uC644\uB8CC'], ['10', '\uC218\uB839\uC644\uB8CC/\uBC18\uD488'],
          ],
          [2000, 7360]
        ),

        h2('5.5 \uC778\uC99D \uC2DC\uC2A4\uD15C'),
        p('\uD30C\uC77C: includes/auth.php'),
        bullet('\uC138\uC158 8\uC2DC\uAC04, \uC790\uB3D9 \uB85C\uADF8\uC778 30\uC77C (remember_tokens \uD14C\uC774\uBE14)'),
        bullet('bcrypt + \uD3C9\uBB38 \uB808\uAC70\uC2DC \uC790\uB3D9 \uC5C5\uADF8\uB808\uC774\uB4DC'),
        bullet('users \uD14C\uC774\uBE14 (primary), member \uD14C\uC774\uBE14 (legacy \uC774\uC911\uC4F0\uAE30)'),

        pb(),
        // ════════════════════════════════════════
        // Ch6. 관리자 대시보드
        // ════════════════════════════════════════
        h1('Ch6. \uAD00\uB9AC\uC790 \uB300\uC2DC\uBCF4\uB4DC'),

        h2('6.1 \uAE30\uC220 \uC2A4\uD0DD'),
        bullet('Tailwind CSS CDN + Chart.js'),
        bullet('\uBE0C\uB79C\uB4DC \uCEEC\uB7EC: #1E4E79'),
        bullet('\uC778\uC99D: $_SESSION[\'admin_username\']'),
        bullet('\uB808\uC774\uC544\uC6C3: h-screen overflow-hidden (\uBDF0\uD3EC\uD2B8 \uACE0\uC815)'),

        h2('6.2 \uD30C\uC77C \uAD6C\uC870'),
        ...codeBlock([
          'dashboard/',
          '\u251C\u2500\u2500 includes/config.php      \u2014 $DASHBOARD_NAV, $PRODUCT_TYPES',
          '\u251C\u2500\u2500 includes/header.php      \u2014 Tailwind CDN, \uC778\uC99D \uCCB4\uD06C',
          '\u251C\u2500\u2500 api/orders.php           \u2014 \uC8FC\uBB38 CRUD',
          '\u251C\u2500\u2500 api/products.php         \u2014 \uC81C\uD488/\uCE74\uD14C\uACE0\uB9AC \uAD00\uB9AC',
          '\u251C\u2500\u2500 api/email.php            \u2014 \uC774\uBA54\uC77C \uCEA0\uD398\uC778',
          '\u251C\u2500\u2500 api/settings.php         \u2014 \uC0AC\uC774\uD2B8 \uC124\uC815',
          '\u251C\u2500\u2500 orders/index.php         \u2014 \uC8FC\uBB38 \uBAA9\uB85D',
          '\u251C\u2500\u2500 orders/view.php          \u2014 \uC8FC\uBB38 \uC0C1\uC138',
          '\u2514\u2500\u2500 proofs/api.php           \u2014 \uAD50\uC815 \uD30C\uC77C API',
        ]),

        h2('6.3 \uC0AC\uC774\uB4DC\uBC14 \uBA54\uB274'),
        makeTable(
          ['\uADF8\uB8F9', '\uBA54\uB274 \uD56D\uBAA9'],
          [
            ['\uC8FC\uBB38\xB7\uAD50\uC815', '\uAD00\uB9AC\uC790 \uC8FC\uBB38, \uC8FC\uBB38 \uAD00\uB9AC, \uAD50\uC815 \uAD00\uB9AC, \uAD50\uC815 \uB4F1\uB85D, \uACB0\uC81C \uD604\uD669, \uD0DD\uBC30 \uAD00\uB9AC, \uBC1C\uC1A1 \uBAA9\uB85D'],
            ['\uC18C\uD1B5\xB7\uACAC\uC801', '\uC774\uBA54\uC77C \uBC1C\uC1A1, \uCC44\uD305 \uAD00\uB9AC, \uACAC\uC801 \uAD00\uB9AC, \uACE0\uAC1D \uBB38\uC758'],
            ['\uC81C\uD488\xB7\uAC00\uACA9', '\uC81C\uD488 \uAD00\uB9AC, \uAC00\uACA9 \uAD00\uB9AC, \uACAC\uC801\uC635\uC158, \uC2A4\uD2F0\uCEE4\uC218\uC815, \uAC24\uB7EC\uB9AC \uAD00\uB9AC, \uD488\uBAA9\uC635\uC158'],
            ['\uAD00\uB9AC\xB7\uD1B5\uACC4', '\uD68C\uC6D0 \uAD00\uB9AC, \uC8FC\uBB38 \uD1B5\uACC4, \uBC29\uBB38\uC790\uBD84\uC11D, \uC0AC\uC774\uD2B8 \uC124\uC815'],
            ['\uAE30\uC874 \uAD00\uB9AC\uC790', '\uC8FC\uBB38 \uAD00\uB9AC(\uAD6C), \uAD50\uC815 \uAD00\uB9AC(\uAD6C)'],
          ],
          [2000, 7360]
        ),

        h2('6.4 API \uD328\uD134'),
        ...codeBlock([
          'GET  /dashboard/api/orders.php?action=list&page=1',
          'POST /dashboard/api/orders.php?action=update  {id, OrderStyle}',
          'POST /dashboard/api/email.php?action=send     {subject, body, recipients}',
          'GET  /dashboard/api/settings.php?action=get',
          'POST /dashboard/api/settings.php?action=save  {key, value}',
        ]),

        pb(),
        // ════════════════════════════════════════
        // Ch7. 네비게이션 시스템
        // ════════════════════════════════════════
        h1('Ch7. \uB124\uBE44\uAC8C\uC774\uC158 \uC2DC\uC2A4\uD15C'),

        h2('7.1 2\uBAA8\uB4DC \uB124\uBE44\uAC8C\uC774\uC158'),
        p('\uD30C\uC77C: includes/nav.php'),
        makeTable(
          ['\uBAA8\uB4DC', '\uB3D9\uC791', '\uC124\uC815\uAC12'],
          [
            ['Simple', '\uC81C\uD488 \uD074\uB9AD \u2192 \uBC14\uB85C \uC774\uB3D9', "nav_default_mode = 'simple'"],
            ['Detailed', '\uD638\uBC84 \u2192 \uC11C\uBE0C\uBA54\uB274 \uBA54\uAC00 \uD328\uB110', "nav_default_mode = 'detailed'"],
          ],
          [2000, 4000, 3360]
        ),
        p('\uBAA8\uB4DC \uC804\uD658: toggleNavMode() \u2192 DB site_settings.nav_default_mode + \uCFE0\uD0A4 nav_mode'),
        bullet('\uBA54\uAC00 \uD328\uB110 \uB370\uC774\uD130: mlangprintauto_transactioncate (BigNo/TreeNo \uACC4\uCE35)'),

        h2('7.2 \uC0AC\uC774\uB4DC\uBC14'),
        p('\uD30C\uC77C: includes/sidebar.php'),
        bullet('\uC6B0\uCE21 \uD50C\uB85C\uD305 \uBA54\uB274 (5\uAC1C \uD328\uB110)'),
        bullet('300ms mouseleave \uB51C\uB808\uC774 + \uD074\uB9AD=\uACE0\uC815(pinned)'),
        bullet('\uCE74\uCE74\uC624\uD1A1: /TALK.svg \uBCA1\uD130 \uC544\uC774\uCF58'),

        h2('7.3 \uC778\uC99D \uD5E4\uB354'),
        p('\uD30C\uC77C: includes/header.php'),
        bullet('$_SESSION[\'user_id\'] \uD655\uC778 \u2192 \uB85C\uADF8\uC778/\uB9C8\uC774\uD398\uC774\uC9C0 \uBD84\uAE30'),
        bullet('\uC7A5\uBC14\uAD6C\uB2C8 \uCE74\uC6B4\uD2B8: SELECT COUNT(*) FROM shop_temp WHERE session_id=?'),

        h2('7.4 AI \uCC47\uBD07'),
        p('\uD30C\uC77C: includes/ai_chatbot_widget.php'),
        bullet('\uC601\uC5C5\uC2DC\uAC04 \uC678(18:30~09:00) \uC790\uB3D9 \uD45C\uC2DC'),
        bullet('API: POST /api/ai_chat.php?action=chat'),
        bullet('\uBCF4\uB77C\uC0C9 \uD14C\uB9C8 (#6366f1)'),
        bullet('footer.php\uC5D0\uC11C 60\uCD08 \uAC04\uACA9 toggleWidgets()\uB85C \uC9C1\uC6D0\uCC44\uD305/AI\uCC47\uBD07 \uBC30\uD0C0\uC801 \uC804\uD658'),

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
  const outPath = `${outDir}/\uB450\uC190\uAE30\uD68D\uC778\uC1C4_\uAE30\uC220\uB9E4\uB274\uC5BC.docx`;
  fs.writeFileSync(outPath, buffer);
  console.log('DOCX generated successfully:', outPath);
  console.log('File size:', buffer.length, 'bytes');
}).catch(err => {
  console.error('Error generating DOCX:', err);
  process.exit(1);
});
