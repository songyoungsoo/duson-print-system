#!/usr/bin/env python3
"""section_11 (FAQ) 재생성 — 배송 답변을 정확한 문구로 교체"""

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

PROMPT_TEMPLATE = """Create a 1100x900px FAQ section image for a {product_kr} printing product page.

DESIGN:
- White/light background, clean modern style
- Title at top: "자주 묻는 질문" (bold, large, black)
- 5 Q&A items in card/accordion style with subtle dividers

EXACT CONTENT (use these EXACT Korean texts, character-perfect):

Q1: "주문 후 얼마나 걸리나요?"
A1: "주문 및 시안 확정 후 평균 1~2일 이내 출고됩니다(특수용지, 옵션이 있는경우 제외)(주말/공휴일 제외)"

Q2: "시안 수정은 몇 번까지 가능한가요?"
A2: "기본 2회까지 무료 수정 가능하며, 추가 수정은 협의 후 진행됩니다."

Q3: "소량 주문도 가능한가요?"
A3: "네, 소량 주문이 가능하며 합리적인 가격으로 제공합니다."

Q4: "색상이 화면과 다르면 어떻게 하나요?"
A4: "최신 인쇄기로 정확한 색상을 구현하지만, 모니터 환경에 따라 미세한 차이가 있을 수 있습니다."

Q5: "결제 방법은 무엇이 있나요?"
A5: "계좌이체, 카드결제 등 다양한 방법으로 결제 가능합니다."

CRITICAL RULES:
- Q1's answer MUST be EXACTLY: "주문 및 시안 확정 후 평균 1~2일 이내 출고됩니다(특수용지, 옵션이 있는경우 제외)(주말/공휴일 제외)"
- All Korean text must be perfectly accurate and readable
- Q labels bold, A labels regular weight
- Each Q&A in its own card/row with light gray background or border
- Professional e-commerce FAQ style
"""

output_base = Path(__file__).parent.parent / "output"

for folder, kr_name in PRODUCTS.items():
    print(f"\n🖼️  {folder} ({kr_name}) section_11 (FAQ) 생성 중...")

    prompt = PROMPT_TEMPLATE.format(product_kr=kr_name)

    try:
        response = client.models.generate_content(
            model=MODEL,
            contents=prompt,
            config=types.GenerateContentConfig(
                response_modalities=["image", "text"],
                temperature=0.7,
            ),
        )

        saved = False
        for part in response.candidates[0].content.parts:
            if hasattr(part, "inline_data") and part.inline_data:
                out_path = output_base / folder / "sections" / "section_11.png"
                out_path.parent.mkdir(parents=True, exist_ok=True)
                out_path.write_bytes(part.inline_data.data)
                size = len(part.inline_data.data)
                print(f"  ✅ 저장 완료 — {out_path} ({size:,} bytes)")
                saved = True
                break

        if not saved:
            print(f"  ❌ 이미지 없음")

    except Exception as e:
        print(f"  ❌ 오류: {e}")

    time.sleep(5)

print("\n✅ 전체 완료!")
