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
        h1('Ch6. 관리자 대시보드'),
        p('접속: https://dsp114.co.kr/dashboard/ (ID: admin / PW: admin123)'),
        p('대시보드는 주문/교정/견적/회원/통계 등 모든 관리 기능을 하나의 인터페이스에서 제공합니다.'),

        // ── 6.1 기술 스택 ──
        h2('6.1 기술 스택 및 레이아웃'),
        h3('프론트엔드 기술'),
        makeTable(
          ['기술', 'CDN/버전', '용도'],
          [
            ['Tailwind CSS', 'CDN (cdn.tailwindcss.com)', '전체 UI 스타일링'],
            ['Chart.js', 'CDN (cdn.jsdelivr.net)', '통계 차트 (일별추이, 품목비율, 매출)'],
            ['Google Fonts', 'Noto Sans KR', '한국어 폰트'],
          ],
          [2000, 3800, 3560]
        ),
        bullet('브랜드 컬러: #1E4E79 (사이드바 헤더, 카드 상단바, 버튼)'),
        bullet('아이콘: 이모지 기반 (별도 아이콘 라이브러리 미사용)'),

        h3('레이아웃 구조'),
        ...codeBlock([
          '<div class="flex h-screen pt-11 overflow-hidden">',
          '  <aside class="w-56 overflow-y-auto">  <!-- 사이드바: 독립 스크롤 -->',
          '  <main class="flex-1 overflow-y-auto">  <!-- 메인 콘텐츠: 독립 스크롤 -->',
          '</div>',
        ]),
        bullet('h-screen overflow-hidden: 뷰포트 고정 (전체 페이지 스크롤 없음)'),
        bullet('사이드바와 메인 콘텐츠가 각각 독립적으로 스크롤'),

        h3('인증 체크 흐름'),
        flowArrow(['auth.php include', 'SESSION 확인', '미인증→로그인 리다이렉트', '인증→페이지 렌더링']),
        bullet('세션 키: $_SESSION[\'admin_username\']'),
        bullet('로그인: /admin/mlangprintauto/login.php'),

        // ── 6.2 파일 구조 ──
        pb(),
        h2('6.2 전체 파일 구조 (45개 PHP 파일)'),
        ...codeBlock([
          'dashboard/',
          '├── index.php                    ← 메인 대시보드',
          '├── embed.php                    ← 레거시 iframe 임베드',
          '├── includes/',
          '│   ├── config.php               ← $DASHBOARD_NAV, $PRODUCT_TYPES',
          '│   ├── auth.php                 ← 인증 체크',
          '│   ├── header.php               ← Tailwind CDN, Chart.js',
          '│   ├── sidebar.php              ← 사이드바 네비게이션',
          '│   └── footer.php               ← 공통 스크립트',
          '├── api/ (16개 파일)',
          '│   ├── base.php                 ← jsonResponse() 헬퍼',
          '│   ├── orders.php               ← 주문 CRUD',
          '│   ├── products.php             ← 제품/카테고리',
          '│   ├── email.php                ← 이메일 캠페인',
          '│   ├── members.php              ← 회원 관리',
          '│   ├── stats.php                ← 주문 통계',
          '│   ├── settings.php             ← 사이트 설정',
          '│   ├── quotes.php               ← 견적 관리',
          '│   ├── payments.php             ← 결제 현황',
          '│   ├── admin-order.php           ← 관리자 주문',
          '│   └── ... (기타 6개)',
          '├── orders/',
          '│   ├── index.php                ← 주문 목록',
          '│   └── view.php                 ← 주문 상세',
          '├── proofs/',
          '│   ├── index.php                ← 교정 관리 (1,295줄)',
          '│   └── api.php                  ← 교정 파일 API',
          '├── admin-order/index.php        ← 관리자 주문 등록',
          '├── email/index.php              ← 이메일 발송 (1,347줄)',
          '├── stats/index.php              ← 주문 통계',
          '├── visitors/index.php           ← 방문자 분석',
          '├── settings/index.php           ← 사이트 설정',
          '└── ... (기타 페이지들)',
        ]),

        // ── 6.3 사이드바 ──
        h2('6.3 사이드바 네비게이션'),
        p('설정: config.php의 $DASHBOARD_NAV 배열 → 6개 그룹'),
        makeTable(
          ['그룹', '메뉴 항목'],
          [
            ['주문·교정', '관리자 주문, 주문 관리, 교정 관리, 교정 등록*, 결제 현황, 택배 관리*, 발송 목록*'],
            ['소통·견적', '이메일 발송, 채팅 관리, 견적 관리, 고객 문의'],
            ['제품·가격', '제품 관리, 가격 관리, 견적옵션*, 스티커수정, 갤러리 관리, 품목옵션'],
            ['관리·통계', '회원 관리, 주문 통계, 방문자분석, 사이트 설정'],
            ['기존 관리자', '주문 관리(구)*, 교정 관리(구)*'],
          ],
          [2000, 7360]
        ),
        tip('* 표시 = embed.php를 통한 레거시 iframe 임베드 메뉴'),
        bullet('채팅 배지: chatmessages WHERE isread=0 카운트 → 빨간 배지'),
        bullet('활성 메뉴: REQUEST_URI 비교 → bg-blue-50 하이라이트'),

        // ── 6.4 메인 대시보드 ──
        pb(),
        h2('6.4 메인 대시보드 (index.php)'),
        p('파일: dashboard/index.php (329줄)'),

        h3('요약 카드 4개 (상단)'),
        makeTable(
          ['카드', 'DB 쿼리', '표시'],
          [
            ['오늘 주문', 'COUNT(*) WHERE DATE(regdate)=CURDATE()', '건수'],
            ['이번달 매출', 'SUM(money_5) WHERE MONTH(regdate)=MONTH(NOW())', '금액(만원)'],
            ['미확인 주문', 'COUNT(*) WHERE OrderStyle IN (0,1,2)', '건수'],
            ['전체 회원수', 'COUNT(*) FROM users WHERE is_admin=0', '명'],
          ],
          [2000, 4500, 2860]
        ),
        bullet('카운트업 애니메이션: animateNumber() + easeOutExpo'),

        h3('일별 주문추이 차트'),
        bullet('Chart.js 라인 차트 (최근 7일)'),
        bullet('데이터: PHP에서 DB 집계 → JS 변수로 전달'),
        bullet('borderColor: #1E4E79 (브랜드 컬러)'),

        h3('퀵 액션 버튼 4개'),
        makeTable(
          ['버튼', '링크'],
          [
            ['주문 등록', '/dashboard/admin-order/'],
            ['교정 관리', '/dashboard/proofs/'],
            ['이메일 발송', '/dashboard/email/'],
            ['견적 작성', '/admin/mlangprintauto/quote/create.php'],
          ],
          [3000, 6360]
        ),

        h3('최근 주문 5건'),
        bullet('SELECT no, name, Pname, money_5, OrderStyle, regdate ORDER BY no DESC LIMIT 5'),
        bullet('행 클릭 → 주문 상세 페이지 이동'),

        h3('[사용법]'),
        numbered('대시보드 접속 시 자동으로 오늘 현황 표시', numNames[2]),
        numbered('요약 카드 클릭 → 각 관리 페이지로 이동', numNames[2]),
        numbered('퀵 액션 버튼으로 자주 쓰는 기능 바로 접근', numNames[2]),
        numbered('최근 주문 행 클릭 → 주문 상세 페이지', numNames[2]),

        // ── 6.5 주문 관리 목록 ──
        pb(),
        h2('6.5 주문 관리 — 목록 (orders/index.php)'),
        p('파일: dashboard/orders/index.php (574줄)'),

        h3('필터 4종'),
        makeTable(
          ['필터', '타입', '파라미터'],
          [
            ['기간', 'date range', 'from, to'],
            ['상태', 'select', 'status (OrderStyle 값)'],
            ['품목', 'select', 'product (9개 제품)'],
            ['검색', 'text', 'search (주문번호/이름/연락처)'],
          ],
          [2000, 2500, 4860]
        ),

        h3('인라인 상태 변경'),
        p('주문 목록에서 직접 상태 드롭다운 변경 → API POST로 즉시 저장'),
        ...codeBlock([
          '// 드롭다운 변경 이벤트',
          'select.addEventListener("change", function() {',
          '  fetch("/dashboard/api/orders.php", {',
          '    method: "POST",',
          '    body: JSON.stringify({ action: "update", id: orderId, OrderStyle: value })',
          '  });',
          '  // 성공 시 행 배경색 flash → 복원',
          '});',
        ]),

        h3('일괄 삭제'),
        bullet('체크박스 선택 → "선택 삭제" 버튼 → action=bulk_delete'),
        bullet('하단 페이지네이션: 총 N건 · X/Y 페이지'),

        h3('[사용법]'),
        numbered('상단 필터바에서 기간/상태/품목/검색어 조합', numNames[3]),
        numbered('각 행의 드롭다운에서 직접 상태 선택 (자동 저장)', numNames[3]),
        numbered('주문번호 클릭 → 주문 상세 페이지', numNames[3]),
        numbered('행 앞 체크박스 선택 → 하단 "선택 삭제"', numNames[3]),

        // ── 6.6 주문 상세 ──
        pb(),
        h2('6.6 주문 관리 — 상세 (orders/view.php)'),
        p('파일: dashboard/orders/view.php (622줄)'),

        h3('화면 구성 (6개 카드)'),
        makeTable(
          ['카드', '내용'],
          [
            ['주문 정보', '주문번호, 품목, 주문일시, 상태 드롭다운'],
            ['제품 규격', 'Type_1 파싱 결과 (종류/재질/수량/인쇄면)'],
            ['주문자 정보', '이름, 전화, 이메일, 주소'],
            ['금액 정보', '공급가액, VAT, 합계, 택배비(선불 시)'],
            ['배송 정보', '배송방법, 운임구분, 택배비, 송장번호'],
            ['원고 파일', '업로드 파일 목록 + 이미지 미리보기'],
          ],
          [2500, 6860]
        ),

        h3('Type_1 필드 파싱 (3가지 형식)'),
        p('형식 1: JSON v2 (최신) — {"product_type":"namecard","style":"일반명함",...}'),
        p('형식 2: 파이프 구분 (레거시) — 일반명함|소프트코팅|양면|500'),
        p('형식 3: 키:값 줄바꿈 (구형) — 종류: 일반명함, 재질: 소프트코팅, ...'),
        ...codeBlock([
          'if (json_decode($type1)) { /* JSON 파싱 */ }',
          'elseif (strpos($type1, "|") !== false) { /* 파이프 파싱 */ }',
          'else { /* 키:값 줄바꿈 파싱 */ }',
        ]),

        h3('품목별 규격 라벨'),
        makeTable(
          ['품목', '라벨 순서'],
          [
            ['명함', '종류 → 재질 → 인쇄면 → 수량'],
            ['전단지', '규격 → 용지 → 인쇄도수 → 수량'],
            ['스티커', '재질 → 가로 → 세로 → 수량 → 모양'],
            ['봉투', '종류 → 재질 → 인쇄면 → 수량'],
            ['카다록', '종류 → 용지 → 페이지수 → 수량'],
            ['NCR양식지', '구분 → 규격 → 색상 → 수량'],
          ],
          [2000, 7360]
        ),

        h3('택배비 VAT 계산'),
        ...codeBlock([
          '$shipping_supply = $logen_delivery_fee;           // 공급가액',
          '$shipping_vat = round($shipping_supply * 0.1);    // VAT 10%',
          '$shipping_total = $shipping_supply + $shipping_vat; // 합계',
          '// 표시: "5,000+VAT 500 = 5,500원"',
        ]),

        h3('입금자명 불일치 강조'),
        p('주문자명 ≠ 입금자명 → 적색 배경 + 흰색 글씨로 강조 표시'),

        h3('[사용법]'),
        numbered('주문 목록에서 주문번호 클릭 → 상세 페이지', numNames[4]),
        numbered('상단 드롭다운에서 상태 변경 (자동 저장)', numNames[4]),
        numbered('Type_1 파싱 결과가 라벨+표로 자동 표시', numNames[4]),
        numbered('원고 파일: 이미지=썸네일, 비이미지=파일명 (클릭→다운로드)', numNames[4]),

        // ── 6.7 교정 관리 ──
        pb(),
        h2('6.7 교정 관리 (proofs/index.php)'),
        p('파일: dashboard/proofs/index.php (1,295줄 — 대시보드 최대 파일)'),
        p('교정 파일 경로: /mlangorder_printauto/upload/{주문번호}/'),

        h3('화면 구성'),
        makeTable(
          ['영역', '기능'],
          [
            ['주문 목록', '주문번호, 품목, 주문자, 교정상태, 파일수'],
            ['이미지 뷰어', '풀스크린 원본 보기, 줌/팬, 썸네일바'],
            ['파일 업로드', '드래그앤드롭 + 파일선택, 다중파일'],
            ['교정 확정', '교정 완료 처리 (OrderStyle 변경)'],
          ],
          [2500, 6860]
        ),

        h3('이미지 뷰어 (줌/팬)'),
        bullet('풀스크린 오버레이 (z-index: 9999)'),
        bullet('마우스 휠 줌: 10% ~ 500%'),
        bullet('마우스 드래그 팬: 이미지 이동'),
        bullet('← → 방향키: 이전/다음 이미지'),
        bullet('ESC / 배경 클릭: 닫기'),
        bullet('하단 썸네일바: 모든 이미지 가로 배치, 클릭 전환'),

        h3('교정 파일 API (proofs/api.php)'),
        makeTable(
          ['action', '메서드', '설명'],
          [
            ['files', 'GET', '교정파일 목록 (order_no)'],
            ['upload', 'POST', '교정파일 업로드 (다중)'],
            ['delete_file', 'POST', '개별 파일 삭제'],
            ['save_phone', 'POST', '연락처 수정'],
            ['check_proof_status', 'GET', '교정 상태 확인'],
            ['confirm_proofreading', 'POST', '교정 확정 (상태 변경)'],
          ],
          [2800, 1500, 5060]
        ),

        h3('[사용법]'),
        numbered('"보기" 클릭 → 이미지 뷰어 오버레이 열림', numNames[5]),
        numbered('마우스 휠로 줌, 드래그로 팬, 방향키로 이전/다음', numNames[5]),
        numbered('드래그앤드롭 또는 파일선택으로 교정파일 업로드', numNames[5]),
        numbered('"교정확정" 버튼 → 주문 상태 자동 변경', numNames[5]),

        // ── 6.8 관리자 주문 등록 ──
        pb(),
        h2('6.8 관리자 주문 등록 (admin-order/index.php)'),
        p('파일: dashboard/admin-order/index.php (808줄)'),
        p('용도: 전화/비회원 주문을 관리자가 직접 등록'),

        h3('화면 구성'),
        makeTable(
          ['영역', '내용'],
          [
            ['품목 선택', '9개 제품 드롭다운 → 카테고리 자동 로드'],
            ['옵션 입력', '품목별 cascade (종류→재질→수량)'],
            ['수동 품목', '자유 텍스트로 품목명+가격 직접 입력'],
            ['주문자 정보', '이름, 전화, 이메일, 주소'],
            ['가격 입력', '공급가액 → VAT 자동 계산 (×1.1)'],
            ['배송/결제', '배송방법, 결제방법, 택배 선불 지원'],
          ],
          [2500, 6860]
        ),

        h3('택배비 선불'),
        bullet('배송방법 "택배" → 운임구분 착불/선불 라디오'),
        bullet('"선불" → 택배비 금액 입력란 → DB logen_fee_type, logen_delivery_fee 저장'),

        h3('[사용법]'),
        numbered('드롭다운에서 제품 선택 → cascade 자동 로드', numNames[6]),
        numbered('"수동 품목 추가" → 품목명/가격 직접 입력', numNames[6]),
        numbered('공급가액 입력 시 VAT(10%) 자동 계산', numNames[6]),
        numbered('택배 선불: 택배비 입력 → DB 저장', numNames[6]),
        numbered('"주문 등록" 클릭 → DB 저장 + 주문 목록 이동', numNames[6]),

        // ── 6.9 이메일 발송 ──
        pb(),
        h2('6.9 이메일 발송 (email/index.php)'),
        p('파일: dashboard/email/index.php (1,347줄)'),
        p('SMTP: 네이버 (smtp.naver.com:465, dsp1830@naver.com)'),

        h3('3탭 구조'),
        makeTable(
          ['탭', '기능'],
          [
            ['작성', '수신자 선택, 제목/본문 편집, 테스트/발송'],
            ['이력', '발송 캠페인 목록, 상태, 성공/실패 카운트'],
            ['템플릿', '저장된 이메일 템플릿 불러오기/삭제'],
          ],
          [2000, 7360]
        ),

        h3('수신자 필터 3종'),
        bullet('전체 회원: users 테이블에서 admin/test/봇 제외'),
        bullet('조건 필터: 최근 로그인 기간 + 이메일 도메인'),
        bullet('직접 입력: 쉼표 구분 이메일 주소'),

        h3('WYSIWYG 에디터'),
        bullet('3가지 모드: 편집기(기본), HTML편집, 미리보기'),
        bullet('서식 도구: B, I, U, H1, H2, 링크, 이미지, 목록, 색상'),
        bullet('이미지 업로드: /dashboard/email/uploads/ (5MB, JPG/PNG/GIF/WebP)'),

        h3('발송 흐름'),
        flowArrow(['action=send', 'campaigns INSERT', 'send_batch (100명)', '3초 대기', '반복', 'completed']),
        bullet('{{name}} 치환: 수신자 이름 자동 삽입 (없으면 "고객")'),
        warn('네이버 SMTP 일일 한도 약 500통, 배치 간격 3초'),

        h3('[사용법]'),
        numbered('수신대상 설정 (전체/조건/직접입력)', numNames[7]),
        numbered('제목/본문 작성 (에디터 도구모음 사용)', numNames[7]),
        numbered('"테스트" → dsp1830@naver.com으로 미리보기', numNames[7]),
        numbered('"발송" → 100명씩 배치 발송 시작', numNames[7]),
        numbered('"이력" 탭에서 발송 상태/성공률 확인', numNames[7]),

        // ── 6.10 회원 관리 ──
        pb(),
        h2('6.10 회원 관리 (members/index.php)'),
        p('파일: dashboard/members/index.php (336줄)'),

        h3('기능'),
        makeTable(
          ['기능', '설명'],
          [
            ['회원 목록', '이름, 이메일, 전화, 가입일, 최근로그인'],
            ['검색', '이름/이메일/전화번호 실시간 검색'],
            ['이메일 오타 검사', 'scan_typos — naver.vom, nate.ocm 등 자동 감지'],
            ['페이지네이션', '20명/페이지'],
          ],
          [2500, 6860]
        ),
        h3('[사용법]'),
        numbered('상단 검색바에 이름/이메일/전화 입력 → 실시간 필터', numNames[8]),
        numbered('"이메일 오타 검사" 버튼 → 문제 이메일 목록', numNames[8]),
        numbered('회원 행 클릭 → 회원 상세 (가입정보, 주문내역)', numNames[8]),

        // ── 6.11 견적 관리 ──
        h2('6.11 견적 관리 (quotes/index.php)'),
        p('DB: admin_quotes + admin_quote_items'),
        h3('견적 상태 흐름'),
        flowArrow(['draft (임시저장)', 'sent (발송)', 'viewed (열람)', 'accepted/rejected']),
        h3('주요 기능'),
        bullet('견적 목록: 번호, 고객명, 금액, 상태'),
        bullet('새 견적/수정/미리보기 → 팝업 창 (window.open)'),
        bullet('이메일 발송: PDF 첨부 → 상태 sent 변경'),
        bullet('삭제: 개별 + 일괄 (체크박스)'),
        bullet('견적번호: AQ-YYYYMMDD-NNNN'),

        h3('[사용법]'),
        numbered('"새 견적" → 팝업에서 고객정보+품목+금액 입력', numNames[9]),
        numbered('"발송" → 고객 이메일로 PDF 첨부 발송', numNames[9]),
        numbered('체크박스 → "선택 삭제"', numNames[9]),

        // ── 6.12 주문 통계 ──
        pb(),
        h2('6.12 주문 통계 (stats/index.php)'),
        p('파일: dashboard/stats/index.php (393줄)'),

        h3('3종 차트 (Chart.js)'),
        makeTable(
          ['차트', '유형', '데이터'],
          [
            ['일별 주문추이', 'Line', '최근 30일 일별 주문 건수/금액'],
            ['품목별 비율', 'Doughnut', '9개 제품별 주문 비율 (%)'],
            ['월별 매출', 'Bar', '최근 12개월 월별 총 매출액'],
          ],
          [2500, 1500, 5360]
        ),

        h3('API 엔드포인트'),
        ...codeBlock([
          'GET /dashboard/api/stats.php?action=daily&days=30',
          '→ { labels: ["2/1",...], orders: [3,5,...], revenue: [150000,...] }',
          '',
          'GET /dashboard/api/stats.php?action=products',
          '→ { labels: ["스티커",...], data: [45,32,...] }',
          '',
          'GET /dashboard/api/stats.php?action=monthly&months=12',
          '→ { labels: ["3월",...], data: [2500000,...] }',
        ]),

        h3('[사용법]'),
        numbered('기간 선택 (7일/30일/90일) → 차트 자동 갱신', numNames[10]),
        numbered('도넛 차트 호버 → 품목별 비율/건수', numNames[10]),
        numbered('막대 차트에서 월별 매출 비교', numNames[10]),

        // ── 6.13 방문자 분석 ──
        h2('6.13 방문자 분석 (visitors/index.php)'),
        h3('기능'),
        makeTable(
          ['기능', '설명'],
          [
            ['실시간 방문자', '현재 접속중 IP/UA/페이지'],
            ['인기 페이지', '방문 횟수 상위 (한글명 표시)'],
            ['진입/이탈', '첫 방문 페이지, 마지막 페이지'],
            ['시간대별', '0~23시 방문 히스토그램'],
          ],
          [2500, 6860]
        ),
        h3('URL 한글화'),
        bullet('30개 정확 매칭: /mlangprintauto/sticker_new/ → 스티커'),
        bullet('17개 부분 매칭: /member/login → 로그인'),
        bullet('getPageName(url) 2단계 매칭 함수'),

        // ── 6.14 사이트 설정 ──
        pb(),
        h2('6.14 사이트 설정 (settings/index.php)'),
        p('DB: site_settings 테이블 (key-value)'),
        h3('3가지 토글'),
        makeTable(
          ['설정키', '기본값', '설명'],
          [
            ['nav_default_mode', 'simple', '네비: simple(바로이동) / detailed(메가메뉴)'],
            ['en_version_enabled', '0', '영문 버전: 0=한국어만, 1=한국어+영어'],
            ['quote_widget_enabled', '1', '견적 위젯: 0=끔, 1=켬'],
          ],
          [2800, 1200, 5360]
        ),
        h3('API'),
        ...codeBlock([
          'GET  /dashboard/api/settings.php?action=get',
          '→ { nav_default_mode: "simple", en_version_enabled: "1", ... }',
          '',
          'POST /dashboard/api/settings.php?action=save',
          'Body: { key: "en_version_enabled", value: "1" }',
        ]),
        h3('[사용법]'),
        numbered('네비 모드: Simple/Detailed 라디오 → 즉시 저장', numNames[11]),
        numbered('영문 버전: ON → 홈페이지 헤더 EN 버튼 표시', numNames[11]),
        numbered('견적 위젯: ON/OFF → 하단 플로팅 견적 위젯', numNames[11]),

        // ── 6.15 결제 현황 ──
        h2('6.15 결제 현황 (payments/index.php)'),
        p('DB: payment_inicis (KG이니시스 결제 기록)'),
        h3('목록 컬럼'),
        makeTable(
          ['컬럼', '내용'],
          [
            ['주문번호', '연결된 주문 (클릭→주문상세)'],
            ['거래번호', '이니시스 TID'],
            ['결제금액', 'VAT 포함'],
            ['결제수단', '카드/무통장'],
            ['상태', '성공/실패/취소'],
          ],
          [2500, 6860]
        ),

        // ── 6.16 API 패턴 총정리 ──
        pb(),
        h2('6.16 API 패턴 총정리'),
        h3('공통 구조'),
        ...codeBlock([
          '// api/base.php — 모든 API가 공유하는 헬퍼',
          'function jsonResponse($success, $message, $data = null) {',
          '    header("Content-Type: application/json");',
          '    echo json_encode(["success"=>$success, "message"=>$message, "data"=>$data]);',
          '    exit;',
          '}',
        ]),
        bullet('인증: auth.php 세션 검증'),
        bullet('분기: $_GET["action"] 또는 $_POST["action"]'),

        h3('주요 API 엔드포인트'),
        makeTable(
          ['파일', 'action', '메서드', '설명'],
          [
            ['orders.php', 'list', 'GET', '주문 목록 (page, status, product, search)'],
            ['orders.php', 'view', 'GET', '주문 상세 (id)'],
            ['orders.php', 'update', 'POST', '상태 변경 (id, OrderStyle)'],
            ['orders.php', 'delete', 'POST', '주문 삭제'],
            ['orders.php', 'bulk_delete', 'POST', '일괄 삭제 (ids[])'],
            ['email.php', 'send', 'POST', '캠페인 발송 시작'],
            ['email.php', 'send_batch', 'POST', '배치 발송 (100명씩)'],
            ['email.php', 'send_test', 'POST', '테스트 발송'],
            ['email.php', 'templates', 'GET', '템플릿 목록'],
            ['stats.php', 'daily', 'GET', '일별 통계 (days)'],
            ['stats.php', 'products', 'GET', '품목별 비율'],
            ['stats.php', 'monthly', 'GET', '월별 매출 (months)'],
            ['settings.php', 'get', 'GET', '전체 설정 조회'],
            ['settings.php', 'save', 'POST', '설정 저장 (key, value)'],
            ['members.php', 'list', 'GET', '회원 목록'],
            ['members.php', 'scan_typos', 'GET', '이메일 오타검사'],
            ['proofs/api.php', 'files', 'GET', '교정파일 목록'],
            ['proofs/api.php', 'upload', 'POST', '교정파일 업로드'],
            ['proofs/api.php', 'confirm_proofreading', 'POST', '교정 확정'],
            ['admin-order.php', 'save', 'POST', '관리자 주문 등록'],
          ],
          [2200, 2000, 1000, 4160]
        ),

        // ── 6.17 레거시 임베드 ──
        h2('6.17 레거시 임베드 (embed.php)'),
        p('기존 관리자 페이지를 iframe으로 대시보드 안에 임베드'),
        makeTable(
          ['사이드바 메뉴', '임베드 URL'],
          [
            ['교정 등록', '/admin/mlangprintauto/admin.php?mode=sian'],
            ['택배 관리', '/shop_admin/post_list74.php'],
            ['발송 목록', '/shop_admin/post_list.php'],
            ['견적옵션', '/admin/mlangprintauto/option_prices.php'],
            ['주문 관리(구)', '/admin/mlangprintauto/admin.php'],
            ['교정 관리(구)', '/admin/mlangprintauto/admin.php?mode=sian'],
          ],
          [2500, 6860]
        ),
        bullet('embed.php?url={URL} → iframe class="w-full h-full"'),
        bullet('사이드바에서 (구) 또는 * 메뉴 클릭 시 iframe 로드'),

        // ── 6.18 가격/품목옵션/갤러리/문의 ──
        h2('6.18 가격 관리 (pricing/)'),
        p('3단 구조: 품목 선택 → Section(종류) 목록 → 수량별 가격 그리드'),
        bullet('스티커: 별도 sticker.php에서 요율 테이블(shop_d1~d4) 관리'),
        bullet('DB: mlangprintauto_transactioncate + mlangprintauto_{product}'),

        h2('6.19 품목옵션 (premium-options/)'),
        bullet('프리미엄 옵션: 박, 넘버링, 미싱, 귀돌이, 오시'),
        bullet('옵션별 가격/설명 수정 + ON/OFF 토글'),

        h2('6.20 갤러리 관리 (gallery/)'),
        bullet('품목별 샘플 이미지 관리 (업로드/삭제/정렬)'),
        bullet('이미지 경로: /ImgFolder/sample/{product}/'),

        h2('6.21 고객 문의 (inquiries/)'),
        bullet('문의 목록 + 상세: 미답변 우선 표시 (빨간 배지)'),
        bullet('답변 작성 → 고객 이메일 자동 알림'),

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
