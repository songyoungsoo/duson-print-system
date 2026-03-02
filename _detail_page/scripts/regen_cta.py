#!/usr/bin/env python3
"""section_13 (최종 CTA) 재생성 스크립트 — 품목별 주문하기 버튼, 가격/수량 제거"""

import os, sys, time

sys.path.insert(0, os.path.dirname(__file__))

from google import genai
from google.genai import types
from pathlib import Path
from dotenv import load_dotenv

load_dotenv(Path(__file__).parent.parent / ".env")

client = genai.Client(api_key=os.getenv("GEMINI_API_KEY"))
MODEL = os.getenv("GEMINI_IMAGE_MODEL", "gemini-3.1-flash-image-preview")

PRODUCTS = {
    "namecard": "명함",
    "sticker_new": "스티커",
    "inserted": "전단지",
    "envelope": "봉투",
    "littleprint": "포스터",
    "merchandisebond": "상품권",
    "cadarok": "카다록",
    "ncrflambeau": "NCR양식지",
    "msticker": "자석스티커",
}

PROMPT_TEMPLATE = """Create a 1100x900px product detail page CTA (Call-to-Action) section image.

DESIGN REQUIREMENTS:
- Background: Solid vivid orange (#FF6B00)
- Top area: Large bold white Korean text (2-3 lines), motivating headline about ordering {product_kr}
  - Example: "지금 바로 {product_kr} 주문하세요!" or "두손기획인쇄에서 {product_kr}을 시작하세요!"
- Middle: Smaller white subtext: "두손기획인쇄 | 합리적 가격 · 빠른 제작 · 완벽한 품질"
- Bottom: Large rounded white button with bold dark text reading exactly: "{product_kr} 주문하기"
  - Button style: white background, rounded corners (pill shape), large padding
  - Text inside button: "{product_kr} 주문하기" (NOTHING ELSE - no prices, no quantities, no arrows)

CRITICAL RULES:
- DO NOT include any prices, quantities, or numbers (no "100매", no "원부터", no "3,000원")
- DO NOT include arrows (→) in the button
- The button text must be EXACTLY: "{product_kr} 주문하기"
- All Korean text must be perfectly accurate and readable
- Clean, professional e-commerce style
- No product photos, just text and button on orange background
"""

output_base = Path(__file__).parent.parent / "output"

for folder, kr_name in PRODUCTS.items():
    print(f"\n🖼️  {folder} ({kr_name}) section_13 생성 중...")

    prompt = PROMPT_TEMPLATE.format(product_kr=kr_name)

    try:
        response = client.models.generate_content(
            model=MODEL,
            contents=prompt,
            config=types.GenerateContentConfig(
                response_modalities=["image", "text"],
                temperature=0.8,
            ),
        )

        # Extract image
        saved = False
        for part in response.candidates[0].content.parts:
            if hasattr(part, "inline_data") and part.inline_data:
                out_path = output_base / folder / "sections" / "section_13.png"
                out_path.parent.mkdir(parents=True, exist_ok=True)
                out_path.write_bytes(part.inline_data.data)
                size = len(part.inline_data.data)
                print(f"  ✅ 저장 완료 — {out_path} ({size:,} bytes)")
                saved = True
                break

        if not saved:
            print(f"  ❌ 이미지 없음 — 텍스트만 반환됨")

    except Exception as e:
        print(f"  ❌ 오류: {e}")

    time.sleep(5)  # Rate limit

print("\n✅ 전체 완료!")
