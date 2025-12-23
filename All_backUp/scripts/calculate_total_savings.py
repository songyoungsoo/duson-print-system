#!/usr/bin/env python3
"""
전체 CSS 절약량 계산
"""

# 스크립트 실행 결과에서 수집한 데이터
savings_data = {
    'inserted': 488,  # leaflet-compact.css에서 절약한 라인 수 (처음 수동 정리)
    'sticker_new': 3303,  # characters
    'ncrflambeau': 1032,  # characters
    'envelope': 2,
    'cadarok': 2,
    'littleprint': 2,
    'merchandisebond': 2,
    'msticker': 2,
}

# 공통 CSS 파일
common_css_lines = 255  # upload-modal-common.css

print("="*70)
print("CSS 정리 전체 절약량 계산")
print("="*70)

print("\n📊 제품별 절약량:")
print("-"*70)
total_chars = 0
for product, saved in savings_data.items():
    if product == 'inserted':
        print(f"  {product:20s}: {saved:>6,} lines (leaflet-compact.css)")
        # 라인당 평균 ~50 characters로 환산
        total_chars += saved * 50
    else:
        print(f"  {product:20s}: {saved:>6,} characters")
        total_chars += saved

print("-"*70)
print(f"  {'합계':20s}: {total_chars:>6,} characters")

# 라인 수로 환산 (평균 80 characters per line)
total_lines = total_chars // 80
print(f"  {'라인 수 환산':20s}: ~{total_lines:>5,} lines")

print(f"\n✅ 공통 CSS 파일 생성: {common_css_lines} lines")
print(f"   - 10개 제품에서 중복 제거")
print(f"   - 파일명: css/upload-modal-common.css")

print(f"\n💾 순 절약량:")
print(f"   - 제거된 중복 코드: ~{total_lines:,} lines")
print(f"   - 공통 파일 생성: {common_css_lines} lines")
print(f"   - 순 절약: ~{total_lines - common_css_lines:,} lines")
print(f"   - 절약률: {((total_lines - common_css_lines) / total_lines * 100):.1f}%")

print("\n" + "="*70)
print("✅ CSS 정리 완료!")
print("="*70)
print(f"\n📁 처리된 제품: {len(savings_data)}개")
print(f"🔧 수정된 파일: {len(savings_data)} CSS files + {len(savings_data)} index.php files")
print(f"📦 생성된 공통 파일: 1개 (upload-modal-common.css)")
print(f"💡 각 제품 페이지 로딩 속도 개선 예상")
