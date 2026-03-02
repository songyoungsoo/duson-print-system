"""
상세페이지 자동 생성 오케스트레이터
빌더 조쉬 방식: 5개 에이전트 순차 실행 → 이미지 13장 생성 → 합치기

사용법:
    python orchestrator.py --product namecard
    python orchestrator.py --product sticker_new
    python orchestrator.py --product all
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
    pass  # dotenv 없으면 환경변수에서 직접 로드

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


class DetailPageOrchestrator:
    """상세페이지 생성 파이프라인 오케스트레이터"""

    # V2 디자인 제약 — 내용 영역 900px, 폰트 소형화
    V2_DESIGN_CONSTRAINTS = """
## ⚠️ V2 디자인 제약 (반드시 준수)

### 캔버스 / 내용 영역
- 캔버스 전체: 1100x900px (배경색이 꽉 채움)
- 내용은 좌우에 충분한 여백을 두고 중앙에 배치 (여백은 시각적으로만 적용)
- 프롬프트에 항상 명시: "content centered with generous left and right margins, clean visual breathing room on both sides"

### 절대 금지 — 치수/수치 표시
- 이미지 안에 "px", "100px", "900px" 같은 픽셀 수치 절대 표시 금지
- 눈금자, 가이드라인, 치수선, 측정 표시 절대 금지
- 레이아웃 가이드 선, 격자선 절대 금지
- 이것은 내부 레이아웃 지시사항이며 이미지에 표시하지 않음

### 폰트 크기 (압도적으로 큰 폰트 절대 금지)
- 헤드라인: 적당히 크고 읽기 편한 크기
- 서브헤딩: 헤드라인보다 작게
- 본문: 충분히 작고 여유롭게
- 텍스트 전용 섹션(FAQ/스펙/가격): 특히 작고 여유롭게, 화면 가득 채우지 말 것

### 여백 / 레이아웃
- 상하 여백 충분히
- 좌우 여백을 넉넉하게 두어 내용이 중앙에 모이는 느낌
- 텍스트 줄간격 여유롭게
- 한 섹션에 텍스트가 많으면 폰트 더 줄이고 여백 더 늘릴 것

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

    def generate(self, product_type: str, version: int = 1):
        """단일 제품 상세페이지 생성

        Args:
            product_type: 제품 코드 (namecard, inserted, ...)
            version: 1=기존 v1, 2=여백/폰트 개선 v2
        """
        if product_type not in self.products:
            logger.error(f"❌ 알 수 없는 제품: {product_type}")
            logger.info(f"사용 가능: {list(self.products.keys())}")
            return False

        product = self.products[product_type]
        start_time = time.time()

        version_tag = f" [V{version}]" if version > 1 else ""
        logger.info(f"{'=' * 60}")
        logger.info(f"🚀 상세페이지 생성 시작: {product['name_ko']} ({product_type}){version_tag}")
        logger.info(f"{'=' * 60}")

        # 출력 디렉토리 생성 — v2는 하위 폴더로 분리
        if version == 2:
            output_dir = PROJECT_ROOT / "output" / product_type / "v2"
        else:
            output_dir = PROJECT_ROOT / "output" / product_type
        sections_dir = output_dir / "sections"
        sections_dir.mkdir(parents=True, exist_ok=True)

        try:
            # ─── Phase 1: 정보수집 (LLM 없음) ───
            logger.info("\n📋 Phase 1: 정보수집 에이전트")
            product_brief = self._phase1_collect(product)
            self._save_json(output_dir / "product_brief.json", product_brief)

            # ─── Phase 2: 리서치 ───
            logger.info("\n🔍 Phase 2: 리서치 에이전트")
            research_brief = self._phase2_research(product_brief)
            self._save_json(output_dir / "research_brief.json", research_brief)

            # ─── Phase 3A: 카피라이팅 ───
            logger.info("\n✍️ Phase 3: 카피라이팅 에이전트")
            copy_data = self._phase3_copywrite(product_brief, research_brief)
            self._save_json(output_dir / "copy.json", copy_data)

            # ─── Phase 3B: 디자인 ───
            logger.info("\n🎨 Phase 4: 디자인 에이전트")
            design_data = self._phase4_design(product_brief, research_brief, copy_data, version=version)
            self._save_json(output_dir / "design.json", design_data)

            # ─── Phase 4: 이미지 생성 ───
            logger.info("\n💻 Phase 5: 프롬프팅 에이전트 (이미지 생성)")
            self._phase5_generate_images(design_data, sections_dir)

            # ─── Phase 5: 이미지 합치기 ───
            logger.info("\n🔧 Phase 6: 이미지 합치기")
            final_path = str(output_dir / "final_detail_page.png")
            stitch_images(str(sections_dir), final_path)

            # ─── 메타데이터 저장 ───
            elapsed = time.time() - start_time
            metadata = {
                "product_type": product_type,
                "product_name": product["name_ko"],
                "version": version,
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
                "output_files": {
                    "final_image": "final_detail_page.png",
                    "sections": [f"sections/section_{i:02d}.png" for i in range(1, 14)],
                    "briefs": [
                        "product_brief.json",
                        "research_brief.json",
                        "copy.json",
                        "design.json",
                    ],
                },
            }
            self._save_json(output_dir / "metadata.json", metadata)

            logger.info(f"\n{'=' * 60}")
            logger.info(f"✅ 상세페이지 생성 완료!")
            logger.info(f"   제품: {product['name_ko']}")
            logger.info(f"   소요시간: {elapsed:.0f}초")
            logger.info(f"   비용: ${metadata['cost']['estimated_total_usd']}")
            logger.info(f"   결과: {final_path}")
            logger.info(f"{'=' * 60}")
            return True

        except Exception as e:
            logger.error(f"❌ 생성 실패: {e}", exc_info=True)
            return False

    def _phase1_collect(self, product: dict) -> dict:
        """Phase 1: 정보수집 — LLM 없이 설정 데이터 구조화"""
        brand = self.config["brand"]
        return {
            "product": product,
            "brand": brand,
            "context": f"{brand['company_name']}({brand['site_url']})의 {product['name_ko']} 상세페이지",
            "landing_plan_sections": 13,
        }

    def _phase2_research(self, product_brief: dict) -> dict:
        """Phase 2: 리서치 — 경쟁사/SEO/트렌드 조사"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("02_researcher")

        prompt = f"""
당신은 인쇄 업계 시장 조사 전문가입니다.

{agent_prompt}

## 조사 대상 제품
- 제품명: {product["name_ko"]} ({product["name_en"]})
- 타겟 고객: {product["target_audience"]}
- 가격 범위: {product["price_range"]}
- 핵심 특징: {", ".join(product["key_features"])}

위 제품에 대해 research_brief.json 형식으로 리서치 결과를 생성하세요.
한국 인쇄 시장 기준으로 작성하세요.
"""
        return self.client.generate_text_json(prompt)

    def _phase3_copywrite(self, product_brief: dict, research_brief: dict) -> dict:
        """Phase 3: 카피라이팅 — 13섹션 텍스트 생성"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("03_copywriter")

        prompt = f"""
당신은 인쇄 제품 전문 카피라이터입니다.

{agent_prompt}

## 13섹션 구조
{self.landing_plan}

## 제품 정보
{json.dumps(product, ensure_ascii=False, indent=2)}

## 리서치 결과
{json.dumps(research_brief, ensure_ascii=False, indent=2)}

## 브랜드 정보
- 회사명: {product_brief["brand"]["company_name"]}
- 사이트: {product_brief["brand"]["site_url"]}

위 정보를 바탕으로 13개 섹션의 카피를 copy.json 형식으로 생성하세요.
각 섹션에 id, name, headline, body 텍스트를 포함하세요.
"""
        return self.client.generate_text_json(prompt)

    def _phase4_design(
        self, product_brief: dict, research_brief: dict, copy_data: dict, version: int = 1
    ) -> dict:
        """Phase 4: 디자인 — 13개 이미지 프롬프트 생성"""
        product = product_brief["product"]
        agent_prompt = self._load_agent_prompt("04_designer")
        brand = product_brief["brand"]

        # v2 전용 제약 삽입
        v2_block = self.V2_DESIGN_CONSTRAINTS if version == 2 else ""

        prompt = f"""
당신은 한국 e-commerce 상세페이지 전문 디자이너입니다.

{agent_prompt}
{v2_block}

## 제품 정보
{json.dumps(product, ensure_ascii=False, indent=2)}

## 카피라이팅 결과
{json.dumps(copy_data, ensure_ascii=False, indent=2)}

## 브랜드 컬러
- Primary: {brand["brand_color"]}
- Accent: {brand["accent_color"]}

## 이미지 규격
- 캔버스 크기: 1100x900px
- 내용 영역: {"중앙 집중 배치, 좌우 여백 넉넉히" if version == 2 else "전체 폭 사용"}
- 포맷: PNG
- 스타일: 포토리얼리스틱, 모던 한국 e-commerce{"" if version == 1 else ", 여유로운 여백, 작은 폰트"}

위 정보를 바탕으로 13개 섹션의 Gemini 이미지 생성 프롬프트를 design.json 형식으로 생성하세요.
각 섹션에 id, name, prompt (영문 이미지 생성 프롬프트) 를 포함하세요.
프롬프트는 반드시 영어로 작성하고, 한국어 텍스트 내용은 그대로 포함하세요.
{"모든 프롬프트에 다음을 포함하세요: 'content centered with generous margins on both sides, NO pixel labels, NO dimension indicators, NO ruler marks, NO guidelines visible in image'" if version == 2 else ""}
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
    parser = argparse.ArgumentParser(description="상세페이지 자동 생성")
    parser.add_argument(
        "--product",
        required=True,
        help='제품 코드 (namecard, sticker_new, inserted, ...) 또는 "all"',
    )
    parser.add_argument(
        "--version",
        type=int,
        default=1,
        choices=[1, 2],
        help="버전 선택 (1=기존, 2=900px 내용폭+작은폰트 개선버전). 기본값: 1",
    )
    args = parser.parse_args()

    orchestrator = DetailPageOrchestrator()

    if args.product == "all":
        logger.info(f"🚀 전체 9개 제품 상세페이지 생성 시작 [V{args.version}]")
        results = {}
        for product_type in orchestrator.products:
            success = orchestrator.generate(product_type, version=args.version)
            results[product_type] = "✅ 성공" if success else "❌ 실패"

        logger.info("\n📊 전체 결과:")
        for pt, result in results.items():
            logger.info(f"  {result} — {pt}")
    else:
        orchestrator.generate(args.product, version=args.version)


if __name__ == "__main__":
    main()
