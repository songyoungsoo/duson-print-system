#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
실제 주문 데이터로 견적서 PDF 생성
"""

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from reportlab.lib.enums import TA_CENTER, TA_RIGHT
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from datetime import datetime, timedelta
import json
import sys

# 노토 산스 폰트 등록
pdfmetrics.registerFont(TTFont('NotoSans', '/var/www/html/scripts/NotoSansKR-Regular.ttf'))
pdfmetrics.registerFont(TTFont('NotoSans-Bold', '/var/www/html/scripts/NotoSansKR-Bold.ttf'))

# 제품명 매핑
PRODUCT_NAMES = {
    'inserted': '전단지',
    'namecard': '명함',
    'envelope': '봉투',
    'sticker': '스티커',
    'msticker': '자석스티커',
    'cadarok': '카다록',
    'littleprint': '포스터',
    'merchandisebond': '상품권',
    'ncrflambeau': 'NCR양식지'
}

class DusonQuotationFromDB:
    """DB 주문 데이터로 견적서 생성"""

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

    def create_from_order(self, order_data):
        """주문 데이터로 견적서 생성"""
        # 헤더
        self._add_header(order_data)

        # 고객 정보
        self._add_customer(order_data)

        # 제품 정보
        total = self._add_products(order_data)

        # 금액 요약
        self._add_summary(order_data, total)

        # 푸터
        self._add_footer()

        # PDF 빌드
        self.doc.build(self.story)
        return self.output_file

    def _add_header(self, order):
        """헤더"""
        today = datetime.now()
        valid = today + timedelta(days=30)

        header_data = [
            ['', '', ''],
            [Paragraph('<font face="NotoSans-Bold" size=20><b>두손기획인쇄</b></font>',
                      ParagraphStyle('Title', fontName='NotoSans-Bold', fontSize=20)),
             '', ''],
            [Paragraph('<font face="NotoSans" size=10>DUSON PLANNING PRINT</font>',
                      ParagraphStyle('Sub', fontName='NotoSans', fontSize=10)),
             '', ''],
            ['', '', ''],
        ]

        info_style = ParagraphStyle('Info', fontName='NotoSans', fontSize=9)
        quote_no = f'Q{today.strftime("%Y%m%d")}-{order["no"]}'

        header_data.extend([
            [Paragraph('<font face="NotoSans">서울 영등포구 영등포로 36길 9 송호빌딩 1층</font>', info_style),
             '', Paragraph(f'<font face="NotoSans">견적번호: {quote_no}</font>', info_style)],
            [Paragraph('<font face="NotoSans">Tel: 02-2632-1830 | Fax: 02-2632-1829</font>', info_style),
             '', Paragraph(f'<font face="NotoSans">날짜: {today.strftime("%Y년 %m월 %d일")}</font>', info_style)],
            [Paragraph('<font face="NotoSans">Email: dsp1830@naver.com</font>', info_style),
             '', Paragraph(f'<font face="NotoSans">유효기간: {valid.strftime("%Y년 %m월 %d일")}</font>', info_style)]
        ])

        table = Table(header_data, colWidths=[70*mm, 50*mm, 50*mm])
        table.setStyle(TableStyle([
            ('ALIGN', (0, 0), (0, -1), 'LEFT'),
            ('ALIGN', (2, 0), (2, -1), 'RIGHT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ]))

        self.story.append(table)
        self.story.append(Spacer(1, 10*mm))

        # 제목
        title = Paragraph('<font face="NotoSans-Bold"><b>견 적 서</b></font>',
                         ParagraphStyle('Title', fontName='NotoSans-Bold', fontSize=24,
                                      alignment=TA_CENTER, textColor=colors.HexColor('#2c3e50')))
        self.story.append(title)
        self.story.append(Spacer(1, 10*mm))

    def _add_customer(self, order):
        """고객 정보"""
        address = f"{order.get('zip1', '')} {order.get('zip2', '')}".strip()

        customer_data = [
            [Paragraph('<font face="NotoSans-Bold">고객명</font>',
                      ParagraphStyle('B', fontName='NotoSans-Bold')),
             Paragraph(f'<font face="NotoSans">{order["name"]}</font>',
                      ParagraphStyle('N', fontName='NotoSans'))],
            [Paragraph('<font face="NotoSans-Bold">연락처</font>',
                      ParagraphStyle('B', fontName='NotoSans-Bold')),
             Paragraph(f'<font face="NotoSans">{order["phone"]}</font>',
                      ParagraphStyle('N', fontName='NotoSans'))],
            [Paragraph('<font face="NotoSans-Bold">이메일</font>',
                      ParagraphStyle('B', fontName='NotoSans-Bold')),
             Paragraph(f'<font face="NotoSans">{order["email"]}</font>',
                      ParagraphStyle('N', fontName='NotoSans'))],
            [Paragraph('<font face="NotoSans-Bold">주소</font>',
                      ParagraphStyle('B', fontName='NotoSans-Bold')),
             Paragraph(f'<font face="NotoSans">{address if address else "-"}</font>',
                      ParagraphStyle('N', fontName='NotoSans'))]
        ]

        table = Table(customer_data, colWidths=[35*mm, 135*mm])
        table.setStyle(TableStyle([
            ('FONT', (0, 0), (-1, -1), 'NotoSans', 10),
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

    def _add_products(self, order):
        """제품 테이블"""
        p_style = ParagraphStyle('Header', fontName='NotoSans-Bold', fontSize=10, alignment=TA_CENTER)

        data = [[
            Paragraph('<font face="NotoSans-Bold" color="white">품목</font>', p_style),
            Paragraph('<font face="NotoSans-Bold" color="white">규격/사양</font>', p_style),
            Paragraph('<font face="NotoSans-Bold" color="white">수량</font>', p_style),
            Paragraph('<font face="NotoSans-Bold" color="white">단위</font>', p_style),
            Paragraph('<font face="NotoSans-Bold" color="white">단가(원)</font>', p_style),
            Paragraph('<font face="NotoSans-Bold" color="white">금액(원)</font>', p_style)
        ]]

        body_style = ParagraphStyle('Body', fontName='NotoSans', fontSize=9)

        # 제품 타입 변환
        product_type = order.get('product_type') or order.get('Type', '')
        product_name = PRODUCT_NAMES.get(product_type, product_type)

        # 금액 (여러 필드 확인)
        price = int(order.get('st_price', 0) or order.get('money_1', 0) or 0)

        # 제품 사양 정보 (Type_1 우선, ThingCate는 이미지 파일 제외)
        specs = []

        # Type_1에서 제품 상세 사양 가져오기
        if order.get('Type_1'):
            type1_lines = order['Type_1'].replace('\r\n', '\n').split('\n')
            specs.extend([line.strip() for line in type1_lines if line.strip()])

        # ThingCate가 이미지 파일이 아닌 경우만 추가
        thing_cate = order.get('ThingCate', '')
        if thing_cate and not (thing_cate.endswith('.jpg') or thing_cate.endswith('.png') or thing_cate.endswith('.gif')):
            specs.append(thing_cate)

        spec_str = '<br/>'.join(specs) if specs else '상세 사양 참조'

        data.append([
            Paragraph(f'<font face="NotoSans">{product_name}</font>', body_style),
            Paragraph(f'<font face="NotoSans">{spec_str}</font>', body_style),
            Paragraph(f'<font face="NotoSans">-</font>', body_style),
            Paragraph(f'<font face="NotoSans">식</font>', body_style),
            Paragraph(f'<font face="NotoSans">-</font>', body_style),
            Paragraph(f'<font face="NotoSans">{price:,}</font>', body_style)
        ])

        # 추가 옵션
        options_added = 0
        if order.get('coating_enabled') and int(order.get('coating_price', 0) or 0) > 0:
            data.append([
                Paragraph(f'<font face="NotoSans">  + 코팅</font>', body_style),
                Paragraph(f'<font face="NotoSans">{order.get("coating_type", "")}</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph(f'<font face="NotoSans">{int(order["coating_price"]):,}</font>', body_style)
            ])
            options_added += 1

        if order.get('folding_enabled') and int(order.get('folding_price', 0) or 0) > 0:
            data.append([
                Paragraph(f'<font face="NotoSans">  + 접지</font>', body_style),
                Paragraph(f'<font face="NotoSans">{order.get("folding_type", "")}</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph(f'<font face="NotoSans">{int(order["folding_price"]):,}</font>', body_style)
            ])
            options_added += 1

        if order.get('creasing_enabled') and int(order.get('creasing_price', 0) or 0) > 0:
            data.append([
                Paragraph(f'<font face="NotoSans">  + 오시</font>', body_style),
                Paragraph(f'<font face="NotoSans">{order.get("creasing_lines", "")}줄</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph('<font face="NotoSans">-</font>', body_style),
                Paragraph(f'<font face="NotoSans">{int(order["creasing_price"]):,}</font>', body_style)
            ])
            options_added += 1

        # 테이블 생성
        col_widths = [40*mm, 55*mm, 18*mm, 12*mm, 20*mm, 25*mm]
        table = Table(data, colWidths=col_widths, repeatRows=1)

        table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#34495e')),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
            ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
            ('FONT', (0, 1), (-1, -1), 'NotoSans', 9),
            ('ALIGN', (0, 1), (1, -1), 'LEFT'),
            ('ALIGN', (2, 1), (-1, -1), 'RIGHT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#bdc3c7')),
            ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#f8f9fa')]),
            ('LEFTPADDING', (0, 0), (-1, -1), 6),
            ('RIGHTPADDING', (0, 0), (-1, -1), 6),
            ('TOPPADDING', (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ]))

        self.story.append(table)
        return price

    def _add_summary(self, order, total):
        """금액 요약"""
        self.story.append(Spacer(1, 10*mm))

        # 실제 주문 데이터의 금액 사용
        supply_price = int(order.get('money_1', 0) or 0)
        vat = int(order.get('money_3', 0) or 0)
        grand_total = supply_price + vat

        sum_style = ParagraphStyle('Sum', fontName='NotoSans', fontSize=11, alignment=TA_RIGHT)

        summary_data = [
            [Paragraph('<font face="NotoSans">공급가액</font>', sum_style),
             Paragraph(f'<font face="NotoSans">{supply_price:,} 원</font>', sum_style)],
            [Paragraph('<font face="NotoSans">부가세 (10%)</font>', sum_style),
             Paragraph(f'<font face="NotoSans">{vat:,} 원</font>', sum_style)],
            [Paragraph('<font face="NotoSans-Bold"><b>총 금액</b></font>',
                      ParagraphStyle('SumB', fontName='NotoSans-Bold', fontSize=11, alignment=TA_RIGHT)),
             Paragraph(f'<font face="NotoSans-Bold"><b>{grand_total:,} 원</b></font>',
                      ParagraphStyle('SumB', fontName='NotoSans-Bold', fontSize=11, alignment=TA_RIGHT))]
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

        footer_style = ParagraphStyle('Footer', fontName='NotoSans', fontSize=9)

        footer_data = [
            [Paragraph('<font face="NotoSans-Bold"><b>입금 계좌</b></font>',
                      ParagraphStyle('FB', fontName='NotoSans-Bold', fontSize=9)),
             Paragraph('<font face="NotoSans">국민은행 999-1688-2384 | 신한은행 110-342-543507 | 농협 301-2632-1829</font>', footer_style)],
            [Paragraph('<font face="NotoSans-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NotoSans-Bold', fontSize=9)),
             Paragraph('<font face="NotoSans">예금주: 두손기획인쇄 차경선</font>', footer_style)],
            [Paragraph('<font face="NotoSans-Bold"><b>비고</b></font>',
                      ParagraphStyle('FB', fontName='NotoSans-Bold', fontSize=9)),
             Paragraph('<font face="NotoSans">* 견적 금액은 부가세 별도 금액입니다.</font>', footer_style)],
            [Paragraph('<font face="NotoSans-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NotoSans-Bold', fontSize=9)),
             Paragraph('<font face="NotoSans">* 자세한 사항은 전화(02-2632-1830) 또는 이메일(dsp1830@naver.com)로 문의해주세요.</font>', footer_style)],
            [Paragraph('<font face="NotoSans-Bold"><b></b></font>',
                      ParagraphStyle('FB', fontName='NotoSans-Bold', fontSize=9)),
             Paragraph('<font face="NotoSans">* 사업자등록번호: 107-06-45106 | 대표자: 차경선</font>', footer_style)]
        ]

        table = Table(footer_data, colWidths=[25*mm, 145*mm])
        table.setStyle(TableStyle([
            ('FONT', (0, 0), (-1, -1), 'NotoSans', 9),
            ('TEXTCOLOR', (0, 0), (-1, -1), colors.HexColor('#666666')),
            ('ALIGN', (0, 0), (0, -1), 'LEFT'),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
            ('TOPPADDING', (0, 0), (-1, -1), 3),
        ]))

        self.story.append(table)


if __name__ == '__main__':
    import sys
    
    # 명령줄 인자 처리
    if len(sys.argv) >= 3:
        json_input = sys.argv[1]
        output = sys.argv[2]
    else:
        # 기본값
        json_input = '/tmp/order_data.json'
        output = '/var/www/html/docs/duson_quotation_real_order.pdf'
    
    # JSON 파일에서 주문 데이터 읽기
    with open(json_input, 'r', encoding='utf-8') as f:
        order_data = json.load(f)

    # print('📄 실제 주문 데이터로 견적서 생성 중...')
    # print(f'📋 주문번호: {order_data["no"]}')
    # print(f'👤 고객: {order_data["name"]}')
    # print(f'📧 이메일: {order_data["email"]}')
    # print(f'💰 금액: {int(order_data["money_1"]):,}원 + 부가세 {int(order_data["money_3"]):,}원')

    generator = DusonQuotationFromDB(output)
    generator.create_from_order(order_data)

    # print(f'\n✅ 견적서 PDF 생성 완료!')
    # print(f'📁 위치: {output}')
