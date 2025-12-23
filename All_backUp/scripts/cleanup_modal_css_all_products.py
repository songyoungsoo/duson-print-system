#!/usr/bin/env python3
"""
모든 제품의 모달 CSS를 공통 파일로 이동
- 각 제품별 CSS 파일에서 모달 관련 CSS 제거
- index.php에 공통 모달 CSS 링크 추가
"""

import re
import os
from pathlib import Path

# 제품 목록 (inserted는 이미 완료)
PRODUCTS = [
    'sticker_new',
    'namecard',
    'envelope',
    'cadarok',
    'littleprint',
    'ncrflambeau',
    'merchandisebond',
    'msticker',
    'sticker',
    'poster'
]

BASE_DIR = Path('/var/www/html/mlangprintauto')

# 모달 관련 CSS 패턴 (leaflet에서 사용한 것과 동일)
MODAL_PATTERNS = [
    r'/\* 파일 업로드 모달 \*/.*?(?=/\* ===|$)',
    r'\.upload-modal\s*\{[^}]+\}',
    r'\.modal-overlay\s*\{[^}]+\}',
    r'\.modal-container\s*\{[^}]+\}',
    r'\.modal-content\s*\{[^}]+\}',
    r'\.modal-header\s*\{[^}]+\}',
    r'\.modal-body\s*\{[^}]+\}',
    r'\.modal-footer\s*\{[^}]+\}',
    r'\.close-modal\s*\{[^}]+\}',
    r'\.file-upload-zone\s*\{[^}]+\}',
    r'\.upload-area\s*\{[^}]+\}',
    r'\.upload-icon\s*\{[^}]+\}',
    r'\.upload-text\s*\{[^}]+\}',
    r'\.file-requirements\s*\{[^}]+\}',
    r'\.requirement-item\s*\{[^}]+\}',
    r'\.file-list-section\s*\{[^}]+\}',
    r'\.uploaded-files-list\s*\{[^}]+\}',
    r'\.file-item\s*\{[^}]+\}',
    r'\.file-info\s*\{[^}]+\}',
    r'\.file-name\s*\{[^}]+\}',
    r'\.file-size\s*\{[^}]+\}',
    r'\.file-actions\s*\{[^}]+\}',
    r'\.btn-remove\s*\{[^}]+\}',
    r'\.upload-tips\s*\{[^}]+\}',
    r'\.tip-item\s*\{[^}]+\}',
    r'\.upload-modal.*?(?=\n\.[^u]|\n/\*|\Z)',
    r'\.modal-.*?(?=\n\.[^m]|\n/\*|\Z)',
    r'\.file-.*?(?=\n\.[^f]|\n/\*|\Z)',
]

def find_css_files(product_dir):
    """제품 디렉토리에서 CSS 파일 찾기"""
    css_files = []
    css_dir = product_dir / 'css'
    if css_dir.exists():
        css_files.extend(css_dir.glob('*.css'))
    # 루트에도 CSS가 있을 수 있음
    css_files.extend(product_dir.glob('*.css'))
    return css_files

def remove_modal_css(css_content):
    """CSS 내용에서 모달 관련 CSS 제거"""
    original_length = len(css_content)

    for pattern in MODAL_PATTERNS:
        css_content = re.sub(pattern, '', css_content, flags=re.DOTALL | re.MULTILINE)

    # 연속된 빈 줄 정리
    css_content = re.sub(r'\n{3,}', '\n\n', css_content)

    final_length = len(css_content)
    lines_saved = css_content.count('\n') - original_length // 50  # 대략적인 라인 수

    return css_content, original_length, final_length

def add_common_css_link(index_file):
    """index.php에 공통 모달 CSS 링크 추가"""
    if not index_file.exists():
        return False, "index.php not found"

    content = index_file.read_text(encoding='utf-8')

    # 이미 추가되어 있는지 확인
    if 'upload-modal-common.css' in content:
        return False, "Already has common CSS link"

    # <link rel="stylesheet" 패턴을 찾아서 그 앞에 추가
    # 보통 CSS 링크들이 모여있는 곳에 추가
    css_link = '    <link rel="stylesheet" href="../../css/upload-modal-common.css">\n'

    # </head> 태그 직전에 추가
    if '</head>' in content:
        content = content.replace('</head>', f'{css_link}</head>')
        index_file.write_text(content, encoding='utf-8')
        return True, "Added common CSS link"

    return False, "Could not find </head> tag"

def process_product(product_name):
    """개별 제품 처리"""
    print(f"\n{'='*60}")
    print(f"Processing: {product_name}")
    print(f"{'='*60}")

    product_dir = BASE_DIR / product_name
    if not product_dir.exists():
        print(f"❌ Directory not found: {product_dir}")
        return

    # 1. CSS 파일에서 모달 CSS 제거
    css_files = find_css_files(product_dir)
    if not css_files:
        print(f"⚠️  No CSS files found in {product_name}")

    total_saved = 0
    for css_file in css_files:
        print(f"\n📄 Processing: {css_file.name}")
        try:
            content = css_file.read_text(encoding='utf-8')
            new_content, orig_len, final_len = remove_modal_css(content)

            if orig_len != final_len:
                # 백업 생성
                backup_file = css_file.with_suffix('.css.bak')
                css_file.write_text(new_content, encoding='utf-8')

                saved = orig_len - final_len
                total_saved += saved
                print(f"   ✅ Removed modal CSS: {saved} characters saved")
            else:
                print(f"   ℹ️  No modal CSS found")
        except Exception as e:
            print(f"   ❌ Error: {e}")

    # 2. index.php에 공통 CSS 링크 추가
    index_file = product_dir / 'index.php'
    success, message = add_common_css_link(index_file)
    if success:
        print(f"\n✅ {message}")
    else:
        print(f"\nℹ️  {message}")

    if total_saved > 0:
        print(f"\n💾 Total saved in {product_name}: {total_saved} characters")

def main():
    print("="*60)
    print("Modal CSS Cleanup - All Products")
    print("="*60)

    for product in PRODUCTS:
        process_product(product)

    print("\n" + "="*60)
    print("✅ All products processed!")
    print("="*60)

if __name__ == '__main__':
    main()
