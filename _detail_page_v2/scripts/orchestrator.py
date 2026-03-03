"""
상세페이지 자동 생성 오케스트레이터 V2
V2: 1100×800 캔버스, 800px 내용폭, 한국어+영문 지원

사용법:
    python orchestrator.py --product namecard --lang ko
    python orchestrator.py --product namecard --lang en
    python orchestrator.py --product namecard --lang all
    python orchestrator.py --product all --lang all
"""

import os
import sys
import json
import time
import argparse
import logging
from pathlib import Path
from datetime import datetime

# 프로젝트 루트 설정
PROJECT_ROOT = Path(__file__).parent.parent
sys.path.insert(0, str(PROJECT_ROOT / "scripts"))

from gemini_client import GeminiClient
from image_stitcher import stitch_images, validate_sections

# .env 파일 로드
try:
    from dotenv import load_dotenv

    load_dotenv(PROJECT_ROOT / ".env")
except ImportError:
    # dotenv 없으면 수동 파싱
    env_path = PROJECT_ROOT / ".env"
    if env_path.exists():
        with open(env_path) as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, _, value = line.partition("=")
                    os.environ.setdefault(key.strip(), value.strip())

# 로깅 설정
log_dir = PROJECT_ROOT / "logs"
log_dir.mkdir(exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler(log_dir / "orchestrator.log", encoding="utf-8"),
    ],
)
logger = logging.getLogger(__name__)


class DetailPageOrchestratorV2:
    """상세페이지 생성 파이프라인 오케스트레이터 V2"""

    # V2 디자인 제약 — 1100x800 캔버스, 800px 내용폭
    DESIGN_CONSTRAINTS = """
## ⚠️ V2 디자인 제약 (반드시 준수)

### 캔버스 / 내용 영역
- 캔버스 전체: 1100x800px (배경색이 꽉 채움)
- 내용 영역: 중앙 800px (좌우 150px 마진은 배경색으로 채움)
- 프롬프트에 항상 명시: "1100x800 pixel canvas, content centered within 800px wide area, 150px side margins filled with background color"

### 절대 금지 — 치수/수치 표시
- 이미지 안에 "px", "100px", "800px" 같은 픽셀 수치 절대 표시 금지
- 눈금자, 가이드라인, 치수선, 측정 표시 절대 금지
- 레이아웃 가이드 선, 격자선 절대 금지

### 폰트 크기
- 헤드라인: 적당히 크고 읽기 편한 크기
- 서브헤딩: 헤드라인보다 작게
- 본문: 충분히 작고 여유롭게
- 텍스트 전용 섹션: 특히 작고 여유롭게, 화면 가득 채우지 말 것

### 여백 / 레이아웃
- 상하 여백 충분히
- 좌우 150px 여백으로 내용이 중앙에 모이는 느낌
- 텍스트 줄간격 여유롭게
- 여유로운 화이트스페이스

### 분위기
- 세련되고 편안한 느낌 (압박감 없음)
- 여유로운 화이트스페이스
- 텍스트가 숨 쉬는 레이아웃
"""

    def __init__(self):
        self.client = GeminiClient()
        self.config = self._load_config()
        self.products = self._load_products()
        self.landing_plan = self._load_landing_plan()

    def _load_config(self) -> dict:
        config_path = PROJECT_ROOT / "config" / "settings.json"
        with open(config_path, "r", encoding="utf-8") as f:
            return json.load(f)

    def _load_products(self) -> dict:
        products_path = PROJECT_ROOT / "config" / "products.json"
        with open(products_path, "r", encoding="utf-8") as f:
            data = json.load(f)
        return {p["type"]: p for p in data["products"]}

    def _load_landing_plan(self) -> str:
        plan_path = PROJECT_ROOT / "prompts" / "landing_page_plan.md"
        with open(plan_path, "r", encoding="utf-8") as f:
            return f.read()

    def _load_agent_prompt(self, agent_name: str) -> str:
        agent_path = PROJECT_ROOT / "agents" / f"{agent_name}.md"
        with open(agent_path, "r", encoding="utf-8") as f:
            return f.read()

    def _get_localized(self, product: dict, field: str, lang: str) -> str:
        """제품 데이터에서 언어별 필드 가져오기"""
        lang_field = f"{field}_{lang}"
        if lang_field in product:
            val = product[lang_field]
            if isinstance(val, list):
                return ", ".join(val)
            return val
        # fallback to base field
        if field in product:
            val = product[field]
            if isinstance(val, list):
                return ", ".join(val)
            return val
        return ""

    def generate(self, product_type: str, lang: str = "ko"):
        """단일 제품, 단일 언어 상세페이지 생성"""
        if product_type not in self.products:
            logger.error(f"❌ 알 수 없는 제품: {product_type}")
            logger.info(f"사용 가능: {list(self.products.keys())}")
            return False

        product = self.products[product_type]
        start_time = time.time()

        lang_label = "한국어" if lang == "ko" else "English"
        product_name = self._get_localized(product, "name", lang)

        logger.info(f"{'=' * 60}")
        logger.info(
            f"🚀 상세페이지 생성 시작: {product_name} ({product_type}) [{lang_label}]"
        )
        logger.info(f"{'=' * 60}")

        # 출력 디렉토리: output/{product}/{lang}/
        output_dir = PROJECT_ROOT / "output" / product_type / lang
        sections_dir = output_dir / "sections"
        sections_dir.mkdir(parents=True, exist_ok=True)

        try:
            # ─── Phase 1: 정보수집 (LLM 없음) ───
            logger.info("\n📋 Phase 1: 정보수집 에이전트")
            product_brief = self._phase1_collect(product, lang)
            self._save_json(output_dir / "product_brief.json", product_brief)

            # ─── Phase 2: 리서치 ───
            logger.info("\n🔍 Phase 2: 리서치 에이전트")
            research_brief = self._phase2_research(product_brief, lang)
            self._save_json(output_dir / "research_brief.json", research_brief)

            # ─── Phase 3: 카피라이팅 ───
            logger.info("\n✍️ Phase 3: 카피라이팅 에이전트")
            copy_data = self._phase3_copywrite(product_brief, research_brief, lang)
            self._save_json(output_dir / "copy.json", copy_data)

            # ─── Phase 4: 디자인 ───
            logger.info("\n🎨 Phase 4: 디자인 에이전트")
            design_data = self._phase4_design(
                product_brief, research_brief, copy_data, lang
            )
            self._save_json(output_dir / "design.json", design_data)

            # ─── Phase 5: 이미지 생성 ───
            logger.info("\n💻 Phase 5: 프롬프팅 에이전트 (이미지 생성)")
            self._phase5_generate_images(design_data, sections_dir)

            # ─── Phase 6: 이미지 합치기 ───
            logger.info("\n🔧 Phase 6: 이미지 합치기")
            final_path = str(output_dir / "final_detail_page.png")
            stitch_images(
                str(sections_dir),
                final_path,
                expected_width=self.config["image"]["width"],
                expected_height=self.config["image"]["height"],
            )

            # ─── 메타데이터 저장 ───
            elapsed = time.time() - start_time
            metadata = {
                "product_type": product_type,
                "product_name": product_name,
                "language": lang,
                "canvas_size": f"{self.config['image']['width']}x{self.config['image']['height']}",
                "content_width": self.config["image"]["content_width"],
                "generated_at": datetime.now().isoformat(),
                "elapsed_seconds": round(elapsed, 1),
                "cost": self.client.get_cost_estimate(),
                "phases_completed": [
                    "collect",
                    "research",
                    "copywrite",
                    "design",
                    "generate",
                    "stitch",
                ],
            }
            self._save_json(output_dir / "metadata.json", metadata)

            logger.info(f"\n{'=' * 60}")
            logger.info(f"✅ 상세페이지 생성 완료!")
            logger.info(f"   제품: {product_name} [{lang_label}]")
            logger.info(f"   소요시간: {elapsed:.0f}초")
            logger.info(f"   비용: ${metadata['cost']['estimated_total_usd']}")
            logger.info(f"   결과: {final_path}")
            logger.info(f"{'=' * 60}")
            return True

        except Exception as e:
            logger.error(f"❌ 생성 실패: {e}", exc_info=True)
            return False

    def _phase1_collect(self, product: dict, lang: str) -> dict:
        """Phase 1: 정보수집 — LLM 없이 설정 데이터 구조화"""
        brand = self.config["brand"]
        company = brand["company_name"] if lang == "ko" else brand["company_name_en"]
        return {
            "product": product,
            "brand": brand,
            "language": lang,
            "context": f"{company} ({brand['site_url']}) — {self._get_localized(product, 'name', lang)}",
            "landing_plan_sections": 13,
            "canvas_size": f"{self.config['image']['width']}x{self.config['image']['height']}",
            "content_width": self.config["image"]["content_width"],
        }

    def _phase2_research(self, product_brief: dict, lang: str) -> dict:
        """Phase 2: 리서치"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("02_researcher")

        if lang == "ko":
            prompt = f"""
당신은 인쇄 업계 시장 조사 전문가입니다.

{agent_prompt}

## 조사 대상 제품
- 제품명: {product["name_ko"]}
- 타겟 고객: {product["target_audience_ko"]}
- 가격 범위: {product["price_range_ko"]}
- 핵심 특징: {", ".join(product["key_features_ko"])}

위 제품에 대해 research_brief.json 형식으로 리서치 결과를 생성하세요.
한국 인쇄 시장 기준으로 작성하세요.
"""
        else:
            prompt = f"""
You are a printing industry market research specialist.

{agent_prompt}

## Product to Research
- Product: {product["name_en"]}
- Target audience: {product["target_audience_en"]}
- Price range: {product["price_range_en"]}
- Key features: {", ".join(product["key_features_en"])}

Generate research_brief.json for this product.
Focus on global/international printing market.
All text in English.
"""
        return self.client.generate_text_json(prompt)

    def _phase3_copywrite(
        self, product_brief: dict, research_brief: dict, lang: str
    ) -> dict:
        """Phase 3: 카피라이팅 — 13섹션 텍스트 생성"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("03_copywriter")
        brand = product_brief["brand"]
        company = brand["company_name"] if lang == "ko" else brand["company_name_en"]

        if lang == "ko":
            prompt = f"""
당신은 인쇄 제품 전문 카피라이터입니다.

{agent_prompt}

## 13섹션 구조
{self.landing_plan}

## 제품 정보
- 제품명: {product["name_ko"]}
- 타겟 고객: {product["target_audience_ko"]}
- 가격 범위: {product["price_range_ko"]}
- 핵심 특징: {", ".join(product["key_features_ko"])}
- 인기 수량: {product["popular_qty"]}{product["unit_ko"]}

## 리서치 결과
{json.dumps(research_brief, ensure_ascii=False, indent=2)}

## 브랜드 정보
- 회사명: {company}
- 사이트: {brand["site_url"]}

위 정보를 바탕으로 13개 섹션의 한국어 카피를 copy.json 형식으로 생성하세요.
각 섹션에 id, name, headline, subtext/body 텍스트를 포함하세요.
모든 텍스트는 한국어로 작성하세요.
"""
        else:
            prompt = f"""
You are an expert e-commerce copywriter for printing products.

{agent_prompt}

## 13-Section Structure
{self.landing_plan}

## Product Info
- Product: {product["name_en"]}
- Target audience: {product["target_audience_en"]}
- Price range: {product["price_range_en"]}
- Key features: {", ".join(product["key_features_en"])}
- Popular quantity: {product["popular_qty"]} {product["unit_en"]}

## Research Results
{json.dumps(research_brief, ensure_ascii=False, indent=2)}

## Brand Info
- Company: {company}
- Website: {brand["site_url"]}

Generate copy for all 13 sections in copy.json format.
Each section must include: id, name, headline, subtext/body.
ALL text must be in English. Do NOT use Korean.
"""
        return self.client.generate_text_json(prompt)

    def _phase4_design(
        self, product_brief: dict, research_brief: dict, copy_data: dict, lang: str
    ) -> dict:
        """Phase 4: 디자인 — 13개 이미지 프롬프트 생성"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("04_designer")
        brand = product_brief["brand"]

        text_lang_instruction = (
            "All text in Korean (한국어). Use Korean Gothic font style (Pretendard/Noto Sans KR feel)."
            if lang == "ko"
            else "All text in English. Use modern sans-serif font style (Inter/Helvetica feel)."
        )

        image_emphasis = self._get_localized(product, "image_emphasis", lang)

        prompt = f"""
You are an expert Korean e-commerce detail page designer.

{agent_prompt}

{self.DESIGN_CONSTRAINTS}

## Product Info
- Product: {self._get_localized(product, "name", lang)}
- Features: {self._get_localized(product, "key_features", lang)}
- Image emphasis: {image_emphasis}

## Copy Data
{json.dumps(copy_data, ensure_ascii=False, indent=2)}

## Brand Colors
- Primary: {brand["brand_color"]}
- Accent: {brand["accent_color"]}
- Light BG: {brand["light_bg"]}

## Image Specification
- Canvas: 1100x800px
- Content area: centered 800px wide, 150px margins each side
- Side margins: filled with section background color
- Format: PNG
- Style: photorealistic, modern e-commerce
- {text_lang_instruction}

## CRITICAL RULES FOR EVERY PROMPT
1. Every prompt MUST specify: "Create a 1100x800 pixel image"
2. Every prompt MUST include: "content centered within 800px wide area, with 150px side margins"
3. Every prompt MUST include: "NO pixel measurements, rulers, or guidelines visible"
4. Side margins MUST use the section's background color (NOT always white)

Generate design.json with 13 section prompts. Each section needs:
- id (1-13)
- name (section identifier)
- prompt (detailed English image generation prompt — MUST include canvas size and content width rules)
- style_notes
- mood
"""
        return self.client.generate_text_json(prompt)

    def _phase5_generate_images(self, design_data: dict, sections_dir: Path):
        """Phase 5: 이미지 생성 — 13장 순차 생성"""
        sections = design_data.get("sections", [])
        if not sections:
            logger.error("디자인 데이터에 섹션이 없습니다!")
            return

        for i, section in enumerate(sections):
            section_id = section.get("id", i + 1)
            prompt = section.get("prompt", "")

            if not prompt:
                logger.warning(f"섹션 {section_id}: 프롬프트 없음, 건너뜀")
                continue

            output_path = str(sections_dir / f"section_{section_id:02d}.png")

            logger.info(
                f"  🖼️ 섹션 {section_id}/13 생성 중... ({section.get('name', '')})"
            )
            success = self.client.generate_image(prompt, output_path)

            if success:
                logger.info(f"  ✅ 섹션 {section_id} 완료")
            else:
                logger.error(f"  ❌ 섹션 {section_id} 실패")

            # Rate limit 대기
            if i < len(sections) - 1:
                time.sleep(self.config["generation"]["rate_limit_delay_seconds"])

    def _save_json(self, path: Path, data: dict):
        """JSON 파일 저장"""
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        logger.info(f"  💾 저장: {path.name}")


def main():
    parser = argparse.ArgumentParser(description="상세페이지 자동 생성 V2 (1100x800)")
    parser.add_argument(
        "--product",
        required=True,
        help='제품 코드 (namecard, sticker_new, ...) 또는 "all"',
    )
    parser.add_argument(
        "--lang",
        default="ko",
        choices=["ko", "en", "all"],
        help="언어 선택 (ko=한국어, en=English, all=둘 다). 기본값: ko",
    )
    args = parser.parse_args()

    orchestrator = DetailPageOrchestratorV2()

    # 언어 목록 결정
    languages = ["ko", "en"] if args.lang == "all" else [args.lang]

    # 제품 목록 결정
    if args.product == "all":
        product_types = list(orchestrator.products.keys())
    else:
        product_types = [args.product]

    total = len(product_types) * len(languages)
    logger.info(
        f"🚀 총 {total}개 상세페이지 생성 시작 ({len(product_types)}제품 × {len(languages)}언어)"
    )

    results = {}
    for product_type in product_types:
        for lang in languages:
            key = f"{product_type}/{lang}"
            success = orchestrator.generate(product_type, lang=lang)
            results[key] = "✅ 성공" if success else "❌ 실패"

    logger.info(f"\n{'=' * 60}")
    logger.info("📊 전체 결과:")
    for key, result in results.items():
        logger.info(f"  {result} — {key}")
    logger.info(f"{'=' * 60}")

    # 실패 건수 확인
    failures = sum(1 for r in results.values() if "실패" in r)
    if failures:
        logger.warning(f"⚠️ {failures}건 실패!")


if __name__ == "__main__":
    main()
