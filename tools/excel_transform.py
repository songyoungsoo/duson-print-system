#!/usr/bin/env python3
"""
엑셀 변환 스크립트
- D~H 컬럼만 추출
- 서체: Noto Sans KR
- 행 높이: 20
- 제품종류/제품사양: 8pt 폰트
- A4 세로 인쇄용 레이아웃
"""

import sys
import os

# 프로젝트 lib 폴더에서 모듈 로드 (웹서버에서 실행 시 필요)
script_dir = os.path.dirname(os.path.abspath(__file__))
lib_path = os.path.join(script_dir, 'lib')
sys.path.insert(0, lib_path)
import zipfile
import xml.etree.ElementTree as ET
from openpyxl import Workbook
from openpyxl.styles import Font, Alignment, Border, Side
from openpyxl.worksheet.page import PageMargins


def read_xlsx_data(xlsx_path):
    """xlsx 파일에서 데이터 추출 (스타일 문제 우회)"""
    rows = []

    with zipfile.ZipFile(xlsx_path, 'r') as z:
        # shared strings 읽기
        shared_strings = []
        if 'xl/sharedStrings.xml' in z.namelist():
            with z.open('xl/sharedStrings.xml') as f:
                tree = ET.parse(f)
                root = tree.getroot()
                ns = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
                for si in root.findall('.//ns:si', ns):
                    t = si.find('.//ns:t', ns)
                    if t is not None and t.text:
                        shared_strings.append(t.text)
                    else:
                        shared_strings.append('')

        # sheet1 읽기
        with z.open('xl/worksheets/sheet1.xml') as f:
            tree = ET.parse(f)
            root = tree.getroot()
            ns = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}

            for row in root.findall('.//ns:row', ns):
                row_data = {}
                for cell in row.findall('ns:c', ns):
                    ref = cell.get('r')
                    col_letter = ''.join(filter(str.isalpha, ref))

                    value = None
                    v = cell.find('ns:v', ns)
                    if v is not None:
                        cell_type = cell.get('t')
                        if cell_type == 's':
                            idx = int(v.text)
                            value = shared_strings[idx] if idx < len(shared_strings) else ''
                        else:
                            value = v.text

                    row_data[col_letter] = value
                rows.append(row_data)

    return rows


def transform_excel(input_path, output_path):
    """엑셀 변환 메인 함수"""

    # 원본 데이터 읽기
    rows = read_xlsx_data(input_path)

    if not rows:
        print("데이터가 없습니다")
        return False

    # 새 워크북 생성
    wb = Workbook()
    ws = wb.active
    ws.title = "거래내역"

    # A4 세로 인쇄 설정
    ws.page_setup.orientation = 'portrait'
    ws.page_setup.paperSize = ws.PAPERSIZE_A4
    ws.page_setup.fitToPage = True
    ws.page_setup.fitToWidth = 1
    ws.page_setup.fitToHeight = 0

    # 페이지 여백 (인치)
    ws.page_margins = PageMargins(
        left=0.5, right=0.5,
        top=0.5, bottom=0.5,
        header=0.3, footer=0.3
    )

    # 폰트 정의
    font_normal = Font(name='Noto Sans KR', size=10)
    font_small = Font(name='Noto Sans KR', size=8)  # 제품종류, 제품사양용
    font_header = Font(name='Noto Sans KR', size=10, bold=True)

    # 테두리 정의
    thin_border = Border(
        left=Side(style='thin'),
        right=Side(style='thin'),
        top=Side(style='thin'),
        bottom=Side(style='thin')
    )

    # 정렬 정의
    align_left = Alignment(horizontal='left', vertical='center', wrap_text=True)
    align_center = Alignment(horizontal='center', vertical='center')
    align_right = Alignment(horizontal='right', vertical='center')

    # D, E, F, G, H 컬럼 추출 (새 파일에서 A, B, C, D, E)
    target_cols = ['D', 'E', 'F', 'G', 'H']
    # 헤더명
    headers = ['인쇄물제목', '제품종류', '제품사양', '매출액', '세금']

    for row_idx, row_data in enumerate(rows, 1):
        for new_col_idx, orig_col in enumerate(target_cols, 1):
            value = row_data.get(orig_col, '')
            cell = ws.cell(row=row_idx, column=new_col_idx, value=value)

            # 첫 번째 행(헤더)
            if row_idx == 1:
                cell.font = font_header
                cell.alignment = align_center
            else:
                # 제품종류(B), 제품사양(C)은 8pt
                if new_col_idx in [2, 3]:
                    cell.font = font_small
                    cell.alignment = align_left
                # 매출액(D), 세금(E)은 오른쪽 정렬
                elif new_col_idx in [4, 5]:
                    cell.font = font_normal
                    cell.alignment = align_right
                else:
                    cell.font = font_normal
                    cell.alignment = align_left

            cell.border = thin_border

        # 행 높이 20
        ws.row_dimensions[row_idx].height = 20

    # 컬럼 너비 설정 (A4 세로에 맞게 조정)
    # A4 세로: 약 210mm = 약 80 문자 너비
    # 총 너비를 약 75-80으로 맞춤
    column_widths = {
        'A': 20,  # 인쇄물제목 (줄임)
        'B': 12,  # 제품종류 (8pt라서 좁게)
        'C': 30,  # 제품사양 (8pt라서 좁게)
        'D': 10,  # 매출액
        'E': 8,   # 세금
    }

    for col, width in column_widths.items():
        ws.column_dimensions[col].width = width

    # 인쇄 영역 설정
    last_row = len(rows)
    ws.print_area = f'A1:E{last_row}'

    # 첫 행 반복 인쇄 (헤더)
    ws.print_title_rows = '1:1'

    # 저장
    wb.save(output_path)
    print(f"✅ 변환 완료: {output_path}")
    print(f"총 {len(rows)} 행 처리됨")
    return True


if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("Usage: python excel_transform.py <input.xlsx> <output.xlsx>")
        sys.exit(1)

    input_file = sys.argv[1]
    output_file = sys.argv[2]

    success = transform_excel(input_file, output_file)
    sys.exit(0 if success else 1)
