#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
두손기획인쇄 견적서 PDF 생성기
PDF Skill (reportlab) 사용 + 나눔고딕 폰트 + 실제 회사 정보
"""

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from reportlab.lib.enums import TA_CENTER, TA_RIGHT, TA_LEFT
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from datetime import datetime, timedelta

# 나눔고딕 폰트 등록
pdfmetrics.registerFont(TTFont('NanumGothic', '/tmp/NanumGothic.ttf'))
pdfmetrics.registerFont(TTFont('NanumGothic-Bold', '/tmp/NanumGothic.ttf'))  # Bold는 같은 파일 사용

class DusonQuotation:
    """두손기획인쇄 견적서 생성"""

    def __init__(self, output_file):
        self.output_file = output_file
        self.doc = SimpleDocTemplate(
            output_file,
            pagesize=A4,
            rightMargin=20*mm,
            leftMargin=20*mm,
            topMargin=15*mm,
            bottomMargin=15*mm
        )
        self.story = []
        self.width, self.height = A4

    def create(self, data):
        """견적서 생성"""
        # 1. 헤더
        self._add_header(data)

        # 2. 고객 정보
        self._add_customer(data['customer'])

        # 3. 제품 테이블
        total = self._add_products(data['products'])

        # 4. 금액 요약
        self._add_summary(total)

        # 5. 푸터
        self._add_footer()

        # PDF 빌드
        self.doc.build(self.story)
        return self.output_file

    def _add_header(self, data):
        """헤더 영역"""
        # 회사명과 기본 정보
        header_data = [
            ['', '', ''],
            [Paragraph('<font face="NanumGothic-Bold" size=20><b>두손기획인쇄</b></font>',
                      ParagraphStyle('Title', fontName='NanumGothic-Bold', fontSize=20)),
             '',
             ''],
            [Paragraph('<font face="NanumGothic" size=10>DUSON PLANNING PRINT</font>',
                      ParagraphStyle('Sub', fontName='NanumGothic', fontSize=10)),
             '',
             ''],
            ['', '', ''],
        ]

        # 회사 정보와 견적서 정보
        info_style = ParagraphStyle('Info', fontName='NanumGothic', fontSize=9)
        header_data.extend([
            [Paragraph('<font face="NanumGothic">서울 영등포구 영등포로 36길 9 송호빌딩 1층</font>', info_style),
             '',
             Paragraph(f'<font face="NanumGothic">견적번호: {data["quote_no"]}</font>', info_style)],
            [Paragraph('<font face="NanumGothic">Tel: 02-2632-1830 | Fax: 02-2632-1829</font>', info_style),
             '',
             Paragraph(f'<font face="NanumGothic">날짜: {data["date"]}</font>', info_style)],
            [Paragraph('<font face="NanumGothic">Email: dsp1830@naver.com</font>', info_style),
             '',
             Paragraph(f'<font face="NanumGothic">유효기간: {data["valid_until"]}</font>', info_style)]
        ])

        header_table = Table(header_data, colWidths=[70*mm, 50*mm, 50*mm])
        header_table.setStyle(TableStyle([
            ('ALIGN', (0, 0), (0, -1), 'LEFT'),
            ('ALIGN', (2, 0), (2, -1), 'RIGHT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.HexColor('#1a1a1a')),
        ]))

        self.story.append(header_table)
        self.story.append(Spacer(1, 10*mm))

        # 제목
        title_style = ParagraphStyle(
            'Title',
            fontName='NanumGothic-Bold',
            fontSize=24,
            alignment=TA_CENTER,
            textColor=colors.HexColor('#2c3e50'),
            spaceAfter=20
        )
        title = Paragraph('<font face="NanumGothic-Bold"><b>견 적 서</b></font>', title_style)
        self.story.append(title)
        self.story.append(Spacer(1, 10*mm))

    def _add_customer(self, customer):
        """고객 정보"""
        customer_data = [
            [Paragraph('<font face="NanumGothic-Bold">고객명</font>',
                      ParagraphStyle('B', fontName='NanumGothic-Bold')),
             Paragraph(f'<font face="NanumGothic">{customer["name"]}</font>',
                      ParagraphStyle('N', fontName='NanumGothic'))],
            [Paragraph('<font face="NanumGothic-Bold">연락처</font>',
                      ParagraphStyle('B', fontName='NanumGothic-Bold')),
             Paragraph(f'<font face="NanumGothic">{customer["phone"]}</font>',
                      ParagraphStyle('N', fontName='NanumGothic'))],
            [Paragraph('<font face="NanumGothic-Bold">이메일</font>',
                      ParagraphStyle('B', fontName='NanumGothic-Bold')),
             Paragraph(f'<font face="NanumGothic">{customer["email"]}</font>',
                      ParagraphStyle('N', fontName='NanumGothic'))],
            [Paragraph('<font face="NanumGothic-Bold">주소</font>',
                      ParagraphStyle('B', fontName='NanumGothic-Bold')),
             Paragraph(f'<font face="NanumGothic">{customer.get("address", "-")}</font>',
                      ParagraphStyle('N', fontName='NanumGothic'))]
        ]

        table = Table(customer_data, colWidths=[35*mm, 135*mm])
        table.setStyle(TableStyle([
            ('FONT', (0, 0), (-1, -1), 'NanumGothic', 10),
            ('BACKGROUND', (0, 0), (0, -1), colors.HexColor('#ecf0f1')),
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#bdc3c7')),
            ('ALIGN', (0, 0), (0, -1), 'CENTER'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            ('LEFTPADDING', (0, 0), (-1, -1), 8),
            ('RIGHTPADDING', (0, 0), (-1, -1), 8),
            ('TOPPADDING', (0, 0), (-1, -1), 6),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ]))

        self.story.append(table)
        self.story.append(Spacer(1, 10*mm))

    def _add_products(self, products):
        """제품 테이블"""
        # 헤더 - Paragraph로 한글 처리
        p_style = ParagraphStyle('Header', fontName='NanumGothic-Bold', fontSize=10, alignment=TA_CENTER)

        data = [[
            Paragraph('<font face="NanumGothic-Bold">품목</font>', p_style),
            Paragraph('<font face="NanumGothic-Bold">규격/사양</font>', p_style),
            Paragraph('<font face="NanumGothic-Bold">수량</font>', p_style),
            Paragraph('<font face="NanumGothic-Bold">단위</font>', p_style),
            Paragraph('<font face="NanumGothic-Bold">단가(원)</font>', p_style),
            Paragraph('<font face="NanumGothic-Bold">금액(원)</font>', p_style)
        ]]

        total = 0
        body_style = ParagraphStyle('Body', fontName='NanumGothic', fontSize=9)

        for p in products:
            # 사양 포맷팅
            specs = []
            if p.get('size'): specs.append(f"크기: {p['size']}")
            if p.get('paper'): specs.append(f"용지: {p['paper']}")
            if p.get('color'): specs.append(f"인쇄: {p['color']}")
            if p.get('sides'): specs.append(f"면수: {p['sides']}")
            spec_str = '<br/>'.join(specs) if specs else '-'

            amount = p['quantity'] * p['unit_price']
            total += amount

            data.append([
                Paragraph(f'<font face="NanumGothic">{p["name"]}</font>', body_style),
                Paragraph(f'<font face="NanumGothic">{spec_str}</font>', body_style),
                Paragraph(f'<font face="NanumGothic">{p["quantity"]:,}</font>', body_style),
                Paragraph(f'<font face="NanumGothic">{p["unit"]}</font>', body_style),
                Paragraph(f'<font face="NanumGothic">{p["unit_price"]:,}</font>', body_style),
                Paragraph(f'<font face="NanumGothic">{amount:,}</font>', body_style)
            ])

            # 추가 옵션
            if 'options' in p:
                for opt in p['options']:
                    opt_price = opt.get('price', 0)
                    total += opt_price
                    data.append([
                        Paragraph(f'<font face="NanumGothic">  + {opt["name"]}</font>', body_style),
                        Paragraph(f'<font face="NanumGothic">{opt.get("details", "-")}</font>', body_style),
                        Paragraph('<font face="NanumGothic">-</font>', body_style),
                        Paragraph('<font face="NanumGothic">-</font>', body_style),
                        Paragraph('<font face="NanumGothic">-</font>', body_style),
                        Paragraph(f'<font face="NanumGothic">{opt_price:,}</font>', body_style)
                    ])

        # 테이블 생성
        col_widths = [40*mm, 55*mm, 18*mm, 12*mm, 20*mm, 25*mm]
        table = Table(data, colWidths=col_widths, repeatRows=1)

        table.setStyle(TableStyle([
            # 헤더
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#34495e')),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
            ('ALIGN', (0, 0), (-1, 0), 'CENTER'),

            # 본문
            ('FONT', (0, 1), (-1, -1), 'NanumGothic', 9),
            ('ALIGN', (0, 1), (1, -1), 'LEFT'),
            ('ALIGN', (2, 1), (-1, -1), 'RIGHT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),

            # 그리드
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#bdc3c7')),
            ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#f8f9fa')]),

            # 패딩
            ('LEFTPADDING', (0, 0), (-1, -1), 6),
            ('RIGHTPADDING', (0, 0), (-1, -1), 6),
            ('TOPPADDING', (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ]))

        self.story.append(table)
        return total

    def _add_summary(self, total):
        """금액 요약"""
        self.story.append(Spacer(1, 10*mm))

        vat = int(total * 0.1)
        grand_total = total + vat

        sum_style = ParagraphStyle('Sum', fontName='NanumGothic', fontSize=11, alignment=TA_RIGHT)

        summary_data = [
            [Paragraph('<font face="NanumGothic">공급가액</font>', sum_style),
             Paragraph(f'<font face="NanumGothic">{total:,} 원</font>', sum_style)],
            [Paragraph('<font face="NanumGothic">부가세 (10%)</font>', sum_style),
             Paragraph(f'<font face="NanumGothic">{vat:,} 원</font>', sum_style)],
            [Paragraph('<font face="NanumGothic-Bold"><b>총 금액</b></font>',
                      ParagraphStyle('SumB', fontName='NanumGothic-Bold', fontSize=11, alignment=TA_RIGHT)),
             Paragraph(f'<font face="NanumGothic-Bold"><b>{grand_total:,} 원</b></font>',
                      ParagraphStyle('SumB', fontName='NanumGothic-Bold', fontSize=11, alignment=TA_RIGHT))]
        ]

        table = Table(summary_data, colWidths=[140*mm, 30*mm])
        table.setStyle(TableStyle([
            ('ALIGN', (0, 0), (-1, -1), 'RIGHT'),
            ('BACKGROUND', (0, 2), (-1, 2), colors.HexColor('#ecf0f1')),
            ('LINEABOVE', (0, 2), (-1, 2), 2, colors.HexColor('#34495e')),
            ('TOPPADDING', (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ]))

        self.story.append(table)

    def _add_footer(self):
        """푸터"""
        self.story.append(Spacer(1, 15*mm))

        footer_style = ParagraphStyle('Footer', fontName='NanumGothic', fontSize=9)

        footer_data = [
            [Paragraph('<font face="NanumGothic-Bold"><b>입금 계좌</b></font>',
                      ParagraphStyle('FB', fontName='NanumGothic-Bold', fontSize=9)),
             Paragraph('<font face="NanumGothic">국민은행 999-1688-2384 | 신한은행 110-342-543507 | 농협 301-2632-1829</font>', footer_style)],
            [Paragraph('<font face="NanumGothic-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NanumGothic-Bold', fontSize=9)),
             Paragraph('<font face="NanumGothic">예금주: 두손기획인쇄 차경선</font>', footer_style)],
            [Paragraph('<font face="NanumGothic-Bold"><b>비고</b></font>',
                      ParagraphStyle('FB', fontName='NanumGothic-Bold', fontSize=9)),
             Paragraph('<font face="NanumGothic">* 견적 금액은 부가세 별도 금액입니다.</font>', footer_style)],
            [Paragraph('<font face="NanumGothic-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NanumGothic-Bold', fontSize=9)),
             Paragraph('<font face="NanumGothic">* 자세한 사항은 전화(02-2632-1830) 또는 이메일(dsp1830@naver.com)로 문의해주세요.</font>', footer_style)],
            [Paragraph('<font face="NanumGothic-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NanumGothic-Bold', fontSize=9)),
             Paragraph('<font face="NanumGothic">* 사업자등록번호: 107-06-45106 | 대표자: 차경선</font>', footer_style)]
        ]

        table = Table(footer_data, colWidths=[25*mm, 145*mm])
        table.setStyle(TableStyle([
            ('FONT', (0, 0), (-1, -1), 'NanumGothic', 9),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.HexColor('#666666')),
            ('ALIGN', (0, 0), (0, -1), 'LEFT'),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
            ('TOPPADDING', (0, 0), (-1, -1), 3),
        ]))

        self.story.append(table)


# 샘플 데이터
def get_sample_data():
    today = datetime.now()
    valid = today + timedelta(days=30)

    return {
        'quote_no': f'Q{today.strftime("%Y%m%d")}-001',
        'date': today.strftime('%Y년 %m월 %d일'),
        'valid_until': valid.strftime('%Y년 %m월 %d일'),
        'customer': {
            'name': '홍길동',
            'phone': '010-1234-5678',
            'email': 'hong@example.com',
            'address': '인천광역시 남동구 논현동'
        },
        'products': [
            {
                'name': '전단지 (Flyer)',
                'size': 'A4 (210x297mm)',
                'paper': '스노우화이트 150g',
                'color': '양면 4도',
                'sides': '양면',
                'quantity': 1000,
                'unit': '매',
                'unit_price': 100,
                'options': [
                    {'name': '코팅', 'details': '양면 유광코팅', 'price': 50000},
                    {'name': '접지', 'details': '2단 접지', 'price': 20000}
                ]
            },
            {
                'name': '명함 (Business Card)',
                'size': '90x50mm',
                'paper': '랑데부 260g',
                'color': '단면 4도',
                'sides': '단면',
                'quantity': 200,
                'unit': '매',
                'unit_price': 200
            }
        ]
    }


if __name__ == '__main__':
    output = '/var/www/html/docs/duson_quotation_final.pdf'

    print('📄 두손기획인쇄 견적서 생성 중...')
    print('🔤 폰트: 나눔고딕')
    print('🏢 회사: 두손기획인쇄 (실제 정보 적용)')

    generator = DusonQuotation(output)
    generator.create(get_sample_data())

    print(f'\n✅ 견적서 PDF 생성 완료!')
    print(f'📁 위치: {output}')
    print(f'\n📊 회사 정보:')
    print(f'   - 상호: 두손기획인쇄')
    print(f'   - 대표: 차경선')
    print(f'   - 주소: 서울 영등포구 영등포로 36길 9 송호빌딩 1층')
    print(f'   - 전화: 02-2632-1830')
    print(f'   - 계좌: 국민 999-1688-2384 | 신한 110-342-543507 | 농협 301-2632-1829')
    print(f'\n📦 견적 내용:')
    print(f'   - 전단지 1,000매 (코팅+접지 옵션)')
    print(f'   - 명함 200매')
    print(f'   - 공급가액: 210,000원')
    print(f'   - 부가세: 21,000원')
    print(f'   - 총 금액: 231,000원')
