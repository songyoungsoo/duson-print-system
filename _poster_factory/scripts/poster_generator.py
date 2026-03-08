"""
SVG 포스터 생성기 V2 — 1장 인쇄용 포스터 (2130×3000px)

출력: .svg + images/ (Adobe Illustrator에서 편집 가능)
크기: 2130×3000px (A4 + 블리드 15px)

사용법:
    python poster_generator.py --workdir /path/to/job
    python poster_generator.py --workdir /path/to/job --rebuild-svg
    python poster_generator.py --workdir /path/to/job --regen-hero
    python poster_generator.py --workdir /path/to/job --regen-item 2
    python poster_generator.py --workdir /path/to/job --embed
    python poster_generator.py --workdir /path/to/job --layout hero_dominant

workdir 구조 (입력):
    job_dir/
    ├── brief.json      (업종/종목/특징 — Claude가 생성)
    ├── copy.json       (텍스트 카피 — Claude가 생성)
    └── design.json     (이미지 프롬프트 + 스타일 — Claude가 생성)

workdir 구조 (출력):
    job_dir/
    ├── poster.svg      (최종 포스터 — 일러스트레이터에서 편집)
    ├── images/
    │   ├── hero.png    (메인 이미지)
    │   ├── item_01.png (품목1)
    │   ├── item_02.png (품목2)
    │   └── ...
    └── metadata.json   (생성 로그)
"""

import os
import sys
import json
import time
import base64
import argparse
import logging
from pathlib import Path
from datetime import datetime
from typing import Optional

# GeminiClient 재사용 (_detail_page에서 import)
DETAIL_PAGE_DIR = Path(__file__).parent.parent.parent / "_detail_page"
DETAIL_PAGE_SCRIPTS = DETAIL_PAGE_DIR / "scripts"
sys.path.insert(0, str(DETAIL_PAGE_SCRIPTS))

_orig_cwd = os.getcwd()
os.chdir(str(DETAIL_PAGE_DIR))
from gemini_client import GeminiClient

os.chdir(_orig_cwd)

# .env 로드
try:
    from dotenv import load_dotenv

    for env_path in [
        Path(__file__).parent.parent.parent / ".env",
        Path(__file__).parent.parent / ".env",
    ]:
        if env_path.exists():
            load_dotenv(env_path)
            break
except ImportError:
    pass

# 로깅
LOG_DIR = Path(__file__).parent.parent / "logs"
LOG_DIR.mkdir(exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler(LOG_DIR / "poster_generator.log", encoding="utf-8"),
    ],
)
logger = logging.getLogger(__name__)

# ─── 상수 ───
CANVAS_W = 2130
CANVAS_H = 3000
FONT_FAMILY = "'Pretendard', 'Malgun Gothic', 'NanumGothic', 'Noto Sans KR', sans-serif"

LAYOUT_IDS = [
    "classic_grid",
    "hero_dominant",
    "magazine_split",
    "bold_typo",
    "side_by_side",
]

# 포스터용 이미지 프롬프트 접미사 (상세페이지와 다름)
POSTER_QUALITY_SUFFIX = """
Style requirements:
- Photorealistic professional photography
- Clean, modern composition with generous white space
- High contrast, vibrant but professional colors
- No watermarks, stock photo marks, or pixel labels
- No cartoon, anime, or illustrated style
- No ruler marks, guidelines, or dimension indicators
"""

ITEM_QUALITY_SUFFIX = """
Style requirements:
- Photorealistic product/food photography
- Clean composition, single subject focus
- Warm natural lighting, shallow depth of field
- No text, no labels, no overlays
- No cartoon, anime, or illustrated style
- No watermarks or stock photo marks
"""


class PosterGenerator:
    """SVG 기반 인쇄용 포스터 생성 엔진"""

    def __init__(self, workdir: str, embed: bool = False):
        self.workdir = Path(workdir)
        self.images_dir = self.workdir / "images"
        self.images_dir.mkdir(parents=True, exist_ok=True)
        self.embed = embed
        self.cli_layout = None  # --layout CLI override

        # GeminiClient
        orig = os.getcwd()
        os.chdir(str(DETAIL_PAGE_DIR))
        self.client = GeminiClient()
        os.chdir(orig)

        # Config
        config_path = Path(__file__).parent.parent / "config" / "defaults.json"
        if config_path.exists():
            self.config = json.loads(config_path.read_text(encoding="utf-8"))
        else:
            self.config = {}

        # Layout patterns
        layout_path = Path(__file__).parent.parent / "config" / "layout_patterns.json"
        if layout_path.exists():
            self.layout_patterns = json.loads(layout_path.read_text(encoding="utf-8"))
        else:
            self.layout_patterns = {}

        # Layout spec (Art Director output — Phase 3)
        self.layout_spec = self._load_safe("layout_spec.json")
        if self.layout_spec:
            logger.info(f"  \u2b50 layout_spec.json \ub85c\ub4dc\ub428 (layout: {self.layout_spec.get('layout_id', '?')})")

    # ─── Public API ───

    def generate_all(self) -> bool:
        """전체 파이프라인: 히어로 + 품목 이미지 생성 → SVG 조립"""
        start = time.time()

        try:
            design = self._load("design.json")
            copy_data = self._load("copy.json")
            brief = self._load("brief.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        biz_name = brief.get("business_name", "unknown")
        hero_cfg = design.get("hero", {})
        items_cfg = design.get("items", [])
        rate_delay = self.config.get("generation", {}).get(
            "rate_limit_delay_seconds", 10
        )

        logger.info(f"{'=' * 55}")
        logger.info(f"🚀 포스터 생성 시작: {biz_name}")
        logger.info(f"   히어로 1장 + 품목 {len(items_cfg)}장")
        logger.info(f"{'=' * 55}")

        # 1. 히어로 이미지
        logger.info("🖼️  히어로 이미지 생성 중...")
        hero_ok = self.client.generate_image(
            hero_cfg.get("prompt", ""),
            str(self.images_dir / "hero.png"),
            aspect_ratio=self._get_hero_aspect_ratio(design),
            resize_to=None,
            quality_suffix=POSTER_QUALITY_SUFFIX,
        )
        if hero_ok:
            logger.info("  ✅ 히어로 완료")
        else:
            logger.error("  ❌ 히어로 실패")
            return False

        # 2. 품목별 이미지
        item_ok = 0
        for i, item in enumerate(items_cfg):
            time.sleep(rate_delay)
            logger.info(f"🖼️  품목 {i + 1}/{len(items_cfg)}: {item.get('name', '?')}")
            ok = self.client.generate_image(
                item.get("prompt", ""),
                str(self.images_dir / f"item_{i + 1:02d}.png"),
                aspect_ratio=item.get("aspect_ratio", "1:1"),
                resize_to=None,
                quality_suffix=ITEM_QUALITY_SUFFIX,
            )
            if ok:
                item_ok += 1
                logger.info(f"  ✅ {item.get('name')} 완료")
            else:
                logger.error(f"  ❌ {item.get('name')} 실패")

        # 3. SVG 조립
        logger.info("\n📐 SVG 조립 중...")
        svg = self._build_svg(design, copy_data)
        svg_path = self.workdir / "poster.svg"
        svg_path.write_text(svg, encoding="utf-8")
        self._generate_preview()

        # 4. 메타데이터
        elapsed = time.time() - start
        meta = {
            "business_name": biz_name,
            "poster_version": 2,
            "generated_at": datetime.now().isoformat(),
            "elapsed_seconds": round(elapsed, 1),
            "cost": self.client.get_cost_estimate(),
            "hero_ok": hero_ok,
            "items_total": len(items_cfg),
            "items_ok": item_ok,
            "output": "poster.svg",
            "embedded": self.embed,
        }
        self._save("metadata.json", meta)

        logger.info(f"\n{'=' * 55}")
        logger.info(f"✅ 포스터 생성 완료!")
        logger.info(f"   업체: {biz_name}")
        logger.info(f"   이미지: 히어로 + {item_ok}/{len(items_cfg)}개 품목")
        logger.info(f"   시간: {elapsed:.0f}초")
        logger.info(f"   비용: ~${meta['cost'].get('estimated_total_usd', '?')}")
        logger.info(f"   결과: {svg_path}")
        logger.info(f"{'=' * 55}")
        return True

    def rebuild_svg(self) -> bool:
        """기존 이미지로 SVG만 재조립 (레이아웃/텍스트 변경 시)"""
        try:
            design = self._load("design.json")
            copy_data = self._load("copy.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        svg = self._build_svg(design, copy_data)
        svg_path = self.workdir / "poster.svg"
        svg_path.write_text(svg, encoding="utf-8")
        self._generate_preview()
        logger.info(f"✅ SVG 재조립 완료: {svg_path}")
        return True

    def regen_hero(self) -> bool:
        """히어로 이미지만 재생성 → SVG 재조립"""
        try:
            design = self._load("design.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        hero = design.get("hero", {})
        logger.info("🖼️  히어로 이미지 재생성 중...")
        ok = self.client.generate_image(
            hero.get("prompt", ""),
            str(self.images_dir / "hero.png"),
            aspect_ratio=self._get_hero_aspect_ratio(design),
            resize_to=None,
            quality_suffix=POSTER_QUALITY_SUFFIX,
        )
        if not ok:
            logger.error("❌ 히어로 재생성 실패")
            return False

        logger.info("  ✅ 히어로 완료")
        return self.rebuild_svg()

    def regen_item(self, idx: int) -> bool:
        """특정 품목 이미지만 재생성 → SVG 재조립"""
        try:
            design = self._load("design.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        items = design.get("items", [])
        if idx < 1 or idx > len(items):
            logger.error(f"❌ 품목 번호는 1~{len(items)} 사이여야 합니다: {idx}")
            return False

        item = items[idx - 1]
        logger.info(f"🖼️  {item.get('name', '?')} 이미지 재생성 중...")
        ok = self.client.generate_image(
            item.get("prompt", ""),
            str(self.images_dir / f"item_{idx:02d}.png"),
            aspect_ratio=item.get("aspect_ratio", "1:1"),
            resize_to=None,
            quality_suffix=ITEM_QUALITY_SUFFIX,
        )
        if not ok:
            logger.error(f"❌ {item.get('name')} 재생성 실패")
            return False

        logger.info(f"  ✅ {item.get('name')} 완료")
        return self.rebuild_svg()

    def art_direct(self) -> bool:
        """Phase 3: 아트디렉팅 — brief+copy 기반으로 layout_spec.json 자동 생성"""
        try:
            brief = self._load("brief.json")
            copy_data = self._load("copy.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        # Load reference configs
        config_dir = Path(__file__).parent.parent / "config"
        schema = self._load_config(config_dir / "layout_spec_schema.json")
        typo_scale = self._load_config(config_dir / "typography_scale.json")

        # Build prompt
        system_prompt = self._build_art_director_system_prompt(schema, typo_scale)
        user_prompt = self._build_art_director_user_prompt(brief, copy_data)

        logger.info("🎨 아트디렉팅 중... (layout_spec.json 생성)")

        try:
            result = self.client.generate_text_json(user_prompt, system_prompt)
        except Exception as e:
            logger.error(f"❌ 아트디렉팅 실패: {e}")
            return False

        # Validate essential fields
        required = ["layout_id", "zones", "typography", "image_directives"]
        missing = [k for k in required if k not in result]
        if missing:
            logger.error(f"❌ layout_spec에 필수 필드 누락: {missing}")
            return False

        # Save
        self._save("layout_spec.json", result)

        # Reload
        self.layout_spec = result
        logger.info(f"✅ 아트디렉팅 완료 — layout: {result.get('layout_id', '?')}")
        return True

    def _load_config(self, path: Path) -> dict:
        """config 디렉토리에서 JSON 파일 로드"""
        if path.exists():
            return json.loads(path.read_text(encoding="utf-8"))
        return {}

    def _build_art_director_system_prompt(self, schema: dict, typo_scale: dict) -> str:
        """아트디렉터 시스템 프롬프트 구성"""
        # Extract layout options from self.layout_patterns
        layout_options = []
        for lid, ldata in self.layout_patterns.get("layouts", {}).items():
            zones_pct = ldata.get("zones", {})
            best_for = ldata.get("best_for", [])
            layout_options.append(f"  - {lid}: {ldata.get('name', lid)} — zones: {json.dumps(zones_pct)} — best_for: {best_for}")
        layouts_str = "\n".join(layout_options) if layout_options else "  (layout_patterns not loaded)"

        # Extract scale options from typo_scale
        scales = typo_scale.get("scales", {})
        scales_str = "\n".join([f"  - {name}: ratio {s.get('ratio')}, feel: {s.get('feel')}, best_for: {s.get('best_for')}" for name, s in scales.items() if isinstance(s, dict) and 'ratio' in s])

        # Extract schema (only the schema part, not the examples)
        schema_def = json.dumps(schema.get("schema", schema), ensure_ascii=False, indent=2)

        return f"""You are a professional Art Director for print poster design (전단지/포스터).

Your job: Given a business brief and copy text, create a complete layout_spec.json that defines the entire poster composition BEFORE any images are generated.

## KEY PRINCIPLE
히어로 이미지는 전체의 일부 — "따로 놀면 안 돼." Every element must serve the whole composition.

## OUTPUT FORMAT (JSON Schema)
{schema_def}

## AVAILABLE LAYOUTS
{layouts_str}

## AVAILABLE TYPOGRAPHY SCALES
{scales_str}

## LAYOUT OVERRIDES (per layout)
{json.dumps(typo_scale.get('layout_overrides', {}), ensure_ascii=False, indent=2)}

## 기승전결 NARRATIVE STRUCTURE
Map the poster to 4-act narrative:
- 기(起/Intro): 25-35% — First impression, brand recognition
- 승(承/Development): 35-45% — Core info, menu/services
- 전(轉/Climax): 10-15% — Action trigger (promo/discount)
- 결(結/Conclusion): 12-18% — Contact info, CTA

## RULES
1. Canvas is always 2130×3000px with 15px bleed and 65px safety margin
2. Zone y_px values must be non-overlapping and sum to ≤3000
3. Typography size_px must respect the hierarchy: L1 > L2 > L4 > L3 > L5
4. Hero image_directives MUST include forbidden: ["자체 텍스트", "테두리", "프레임", "로고"] — the hero is PURE photography
5. All colors must be valid hex codes
6. Choose layout based on: item count, industry, visual emphasis needed
7. zone_transitions must cover all adjacent zone pairs"""

    def _build_art_director_user_prompt(self, brief: dict, copy_data: dict) -> str:
        """아트디렉터 사용자 프롬프트 구성"""
        biz_name = brief.get("business_name", "")
        industry = brief.get("industry", "")
        category = brief.get("category", "")
        features = brief.get("features", [])

        items = copy_data.get("items", [])
        n_items = len(items)
        hero_copy = copy_data.get("hero", {})
        subtitle = copy_data.get("subtitle", "")
        promo = copy_data.get("promo", {})
        cta = copy_data.get("cta", {})

        items_summary = "\n".join([f"  {i+1}. {item.get('name', '?')} — {item.get('description', '')} [{item.get('price', '')}]" for i, item in enumerate(items)])

        return f"""Create layout_spec.json for this poster:

## BUSINESS
- Name: {biz_name}
- Industry: {industry}
- Category: {category}
- Features: {', '.join(features) if features else 'N/A'}

## COPY CONTENT
- Hero headline: {hero_copy.get('headline', '')}
- Hero badge: {hero_copy.get('badge', '')}
- Subtitle: {subtitle}
- Items ({n_items}):
{items_summary}
- Promo: {promo.get('title', '')} — {promo.get('detail', '')}
- CTA headline: {cta.get('headline', '')}
- CTA phone: {cta.get('phone', '')}
- CTA address: {cta.get('address', '')}
- CTA hours: {cta.get('hours', '')}

## DECISION FACTORS
- Item count: {n_items} (affects grid layout choice)
- Has promo: {'Yes' if promo else 'No'}
- Industry suggests: Consider warm/cool tones, visual weight, energy level

Generate complete layout_spec.json with all required fields: layout_id, zones, typography.assignments, image_directives.hero, color_plan."""

    # ─── SVG Dispatch ───

    def _build_svg(self, design: dict, copy_data: dict) -> str:
        """SVG 문서 생성 — 레이아웃 디스패치 엔트리포인트"""
        layout_id = self._select_layout(design, copy_data)
        style = design.get("style", {})
        dispatch = {
            "classic_grid": self._build_classic_grid,
            "hero_dominant": self._build_hero_dominant,
            "magazine_split": self._build_magazine_split,
            "bold_typo": self._build_bold_typo,
            "side_by_side": self._build_side_by_side,
        }
        builder = dispatch.get(layout_id, self._build_classic_grid)
        logger.info(f"  📐 레이아웃: {layout_id}")
        return builder(style, copy_data)

    def _select_layout(self, design: dict, copy_data: dict) -> str:
        """레이아웃 자동 선택 (CLI → layout_spec → design.json → layout_patterns.json)"""
        # 1. CLI override
        if self.cli_layout:
            return self.cli_layout
        # 2. layout_spec.json (Art Director decision)
        if self.layout_spec and self.layout_spec.get("layout_id"):
            return self.layout_spec["layout_id"]
        # 3. Explicit in design.json
        if design.get("layout"):
            return design["layout"]
        # 3. Auto from layout_patterns.json
        brief = self._load_safe("brief.json")
        industry = brief.get("industry", "").lower() if brief else ""
        n_items = len(copy_data.get("items", []))
        # Check industry mapping
        rules = self.layout_patterns.get("layout_selection_rules", {})
        by_industry = rules.get("by_industry", {})
        for key, layouts in by_industry.items():
            if key == "default":
                continue
            if key.lower() in industry or industry in key.lower():
                return layouts[0]
        # Check item count
        by_count = rules.get("by_item_count", {})
        if n_items <= 2:
            candidates = by_count.get("1-2", ["hero_dominant"])
        elif n_items <= 4:
            candidates = by_count.get("3-4", ["classic_grid"])
        elif n_items <= 6:
            candidates = by_count.get("5-6", ["classic_grid"])
        else:
            candidates = by_count.get("7+", ["magazine_split"])
        return candidates[0] if candidates else "classic_grid"

    # ─── Layout Spec Helpers ───

    def _get_zone(self, zone_id: str, defaults: dict) -> dict:
        """layout_spec.json에서 zone 값을 읽거나 하드코딩 기본값으로 폴백.
        
        Args:
            zone_id: zone ID (예: 'hero', 'slogan', 'items', 'cta')
            defaults: 폴백 기본값 dict (예: {'y_px': 20, 'h_px': 1480})
        
        Returns:
            dict with y_px, h_px (and optionally x_px, w_px)
        """
        if self.layout_spec:
            zones = self.layout_spec.get("zones", {})
            if zone_id in zones:
                spec_zone = zones[zone_id]
                return {
                    "y_px": spec_zone.get("y_px", defaults.get("y_px", 0)),
                    "h_px": spec_zone.get("h_px", defaults.get("h_px", 0)),
                    "x_px": spec_zone.get("x_px", defaults.get("x_px", 0)),
                    "w_px": spec_zone.get("w_px", defaults.get("w_px", CANVAS_W)),
                }
        return defaults

    def _get_hero_aspect_ratio(self, design: dict) -> str:
        """layout_spec에서 히어로 aspect_ratio를 읽거나 design.json에서 폴백."""
        if self.layout_spec:
            directives = self.layout_spec.get("image_directives", {})
            hero_dir = directives.get("hero", {})
            if hero_dir.get("aspect_ratio"):
                return hero_dir["aspect_ratio"]
        return design.get("hero", {}).get("aspect_ratio", "5:4")

    def _get_typo(self, key: str, defaults: dict) -> dict:
        """layout_spec.json에서 typography assignment를 읽거나 기본값으로 폴백.
        
        Args:
            key: assignment key (예: 'business_name', 'slogan', 'item_name')
            defaults: 폴백 기본값 dict (예: {'size_px': 52, 'weight': '700', 'color': '#333'})
        
        Returns:
            dict with size_px, weight, color, alignment
        """
        if self.layout_spec:
            assignments = self.layout_spec.get("typography", {}).get("assignments", {})
            if key in assignments:
                a = assignments[key]
                return {
                    "size_px": a.get("size_px", defaults.get("size_px", 32)),
                    "weight": a.get("weight", defaults.get("weight", "400")),
                    "color": a.get("color", defaults.get("color", "#333333")),
                    "alignment": a.get("alignment", defaults.get("alignment", "center")),
                }
        return defaults

    def _get_transitions(self) -> dict:
        """layout_spec.json에서 zone_transitions를 읽어 {(from, to): type} dict 반환."""
        result = {}
        if self.layout_spec:
            transitions = self.layout_spec.get("color_plan", {}).get("zone_transitions", [])
            for t in transitions:
                key = (t.get("from_zone", ""), t.get("to_zone", ""))
                result[key] = t.get("transition", "hard_cut")
        return result

    def _svg_transition(self, from_id: str, to_id: str, y_boundary: int,
                         accent: str = "#FF6B35", margin: int = 65) -> str:
        """zone 경계에 transition SVG 요소를 생성.
        
        Args:
            from_id: 위쪽 zone ID
            to_id: 아래쪽 zone ID
            y_boundary: 경계 Y 좌표 (from_zone의 bottom = to_zone의 top)
            accent: 강조색
            margin: 좌우 여백
        Returns:
            SVG string (empty if hard_cut/seamless or no layout_spec)
        """
        transitions = self._get_transitions()
        trans_type = transitions.get((from_id, to_id), "hard_cut")
        
        if trans_type == "hard_cut" or trans_type == "seamless":
            return ""
        
        if trans_type == "gradient":
            # Get to_zone's bg color for gradient target
            to_bg = "#FAFAF8"  # default
            if self.layout_spec:
                to_zone = self.layout_spec.get("zones", {}).get(to_id, {})
                to_bg = to_zone.get("bg_color", self.layout_spec.get("color_plan", {}).get("bg_color", "#FAFAF8"))
            
            grad_h = 80  # gradient overlay height
            grad_id = f"grad-{from_id}-{to_id}"
            return f"""
  <!-- Transition: {from_id} \u2192 {to_id} (gradient) -->
  <defs>
    <linearGradient id="{grad_id}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="{to_bg}" stop-opacity="0"/>
      <stop offset="100%" stop-color="{to_bg}" stop-opacity="0.85"/>
    </linearGradient>
  </defs>
  <rect x="0" y="{y_boundary - grad_h}" width="{CANVAS_W}" height="{grad_h}"
        fill="url(#{grad_id})"/>"""
        
        if trans_type == "accent_line":
            return f"""
  <!-- Transition: {from_id} \u2192 {to_id} (accent_line) -->
  <line x1="{margin}" y1="{y_boundary}" x2="{CANVAS_W - margin}" y2="{y_boundary}"
        stroke="{accent}" stroke-width="2" opacity="0.35"/>"""
        
        return ""

    # ─── SVG Common Helpers ───

    def _svg_header(self, bg: str) -> str:
        """XML 선언 + SVG 열기 + defs/style + 배경"""
        return f"""<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink"
     width="{CANVAS_W}" height="{CANVAS_H}"
     viewBox="0 0 {CANVAS_W} {CANVAS_H}">
  <defs>
    <style>
      .title {{ font-family: {FONT_FAMILY}; font-weight: 700; }}
      .body {{ font-family: {FONT_FAMILY}; font-weight: 400; }}
      .light {{ font-family: {FONT_FAMILY}; font-weight: 300; }}
    </style>
  </defs>

  <!-- ===== BACKGROUND ===== -->
  <rect width="{CANVAS_W}" height="{CANVAS_H}" fill="{bg}"/>"""

    def _svg_cta(self, cta: dict, style: dict, y: int, h: int) -> str:
        """CTA/연락처 섹션"""
        if not cta:
            return ""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        hl = cta.get("headline", "")
        phone = cta.get("phone", "")
        addr = cta.get("address", "")
        hours = cta.get("hours", "")
        sns = cta.get("sns", "")
        # Proportional offsets (calibrated for h=360: 75,140,195,240,290)
        hl_y = y + round(h * 75 / 360)
        ph_y = y + round(h * 140 / 360)
        ad_y = y + round(h * 195 / 360)
        hr_y = y + round(h * 240 / 360)
        sn_y = y + round(h * 290 / 360)

        # Typography from layout_spec or hardcoded defaults
        t_hl = self._get_typo("cta_headline", {"size_px": 56, "weight": "700", "color": accent})
        t_ph = self._get_typo("cta_phone", {"size_px": 46, "weight": "700", "color": "#FFFFFF"})
        t_addr = self._get_typo("cta_address", {"size_px": 30, "weight": "400", "color": "#FFFFFF"})
        t_sns = self._get_typo("cta_sns", {"size_px": 28, "weight": "400", "color": accent})

        result = f"""
  <!-- ===== CTA / 연락처 ===== -->
  <rect x="0" y="{y}" width="{CANVAS_W}" height="{h}" fill="{brand}"/>
  <line x1="{CANVAS_W // 2 - 150}" y1="{y + 20}" x2="{CANVAS_W // 2 + 150}" y2="{y + 20}"
        stroke="{accent}" stroke-width="2" opacity="0.5"/>
  <text x="{CANVAS_W // 2}" y="{hl_y}"
        text-anchor="middle" class="title" font-size="{t_hl['size_px']}" font-weight="{t_hl['weight']}" fill="{t_hl['color']}">
    {hl}
  </text>
  <text x="{CANVAS_W // 2}" y="{ph_y}"
        text-anchor="middle" class="title" font-size="{t_ph['size_px']}" font-weight="{t_ph['weight']}" fill="{t_ph['color']}">
    {phone}
  </text>
  <text x="{CANVAS_W // 2}" y="{ad_y}"
        text-anchor="middle" class="body" font-size="{t_addr['size_px']}" font-weight="{t_addr['weight']}" fill="{t_addr['color']}" opacity="0.85">
    {addr}
  </text>
  <text x="{CANVAS_W // 2}" y="{hr_y}"
        text-anchor="middle" class="body" font-size="{t_addr['size_px']}" font-weight="{t_addr['weight']}" fill="{t_addr['color']}" opacity="0.85">
    {hours}
  </text>"""
        if sns:
            result += f"""
  <text x="{CANVAS_W // 2}" y="{sn_y}"
        text-anchor="middle" class="body" font-size="{t_sns['size_px']}" font-weight="{t_sns['weight']}" fill="{t_sns['color']}" opacity="0.9">
    {sns}
  </text>"""
        return result

    def _svg_promo(
        self, promo: dict, style: dict, y: int, h: int, margin: int = 65
    ) -> str:
        """프로모션 배너"""
        if not promo:
            return ""
        accent = style.get("accent_color", "#FF6B35")
        ptitle = promo.get("title", "")
        pdetail = promo.get("detail", "")
        x = margin - 10
        w = CANVAS_W - 2 * x
        title_y = y + round(h * 42 / 100)
        detail_y = y + round(h * 80 / 100)
        # Typography from layout_spec or hardcoded defaults
        t_pt = self._get_typo("promo_title", {"size_px": 38, "weight": "700", "color": "#FFFFFF"})
        t_pd = self._get_typo("promo_detail", {"size_px": 36, "weight": "700", "color": "#FFFFFF"})
        return f"""
  <!-- ===== PROMO ===== -->
  <rect x="{x}" y="{y}" width="{w}" height="{h}"
        rx="12" fill="{accent}"/>
  <text x="{CANVAS_W // 2}" y="{title_y}"
        text-anchor="middle" class="title" font-size="{t_pt['size_px']}" font-weight="{t_pt['weight']}" fill="{t_pt['color']}" letter-spacing="5">
    \u2605  {ptitle}  \u2605
  </text>
  <text x="{CANVAS_W // 2}" y="{detail_y}"
        text-anchor="middle" class="title" font-size="{t_pd['size_px']}" font-weight="{t_pd['weight']}" fill="{t_pd['color']}">
    {pdetail}
  </text>"""

    def _svg_trim_marks(self) -> str:
        """재단선 (블리드 15px)"""
        bleed = 15
        return f"""
  <!-- ===== TRIM MARKS (재단선, 필요시 삭제) ===== -->
  <g opacity="0.3" stroke="#999" stroke-width="0.5">
    <line x1="{bleed}" y1="0" x2="{bleed}" y2="30"/>
    <line x1="0" y1="{bleed}" x2="30" y2="{bleed}"/>
    <line x1="{CANVAS_W - bleed}" y1="0" x2="{CANVAS_W - bleed}" y2="30"/>
    <line x1="{CANVAS_W}" y1="{bleed}" x2="{CANVAS_W - 30}" y2="{bleed}"/>
    <line x1="{bleed}" y1="{CANVAS_H}" x2="{bleed}" y2="{CANVAS_H - 30}"/>
    <line x1="0" y1="{CANVAS_H - bleed}" x2="30" y2="{CANVAS_H - bleed}"/>
    <line x1="{CANVAS_W - bleed}" y1="{CANVAS_H}" x2="{CANVAS_W - bleed}" y2="{CANVAS_H - 30}"/>
    <line x1="{CANVAS_W}" y1="{CANVAS_H - bleed}" x2="{CANVAS_W - 30}" y2="{CANVAS_H - bleed}"/>
  </g>"""

    def _svg_footer(self) -> str:
        """SVG 닫기"""
        return "\n</svg>"

    # ─── Layout 1: Classic Grid (현재 V2 — 동작 변경 없음) ───

    def _build_classic_grid(self, style: dict, copy_data: dict) -> str:
        """클래식 그리드 — 히어로 49% → 슬로건 4% → 특징 3% → 품목 25% → 프로모 5% → CTA 12%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        features = copy_data.get("features", [])
        cta = copy_data.get("cta", {})
        subtitle = copy_data.get("subtitle", "")
        promo = copy_data.get("promo", {})
        n_items = len(items)

        # ─── 레이아웃 계산 (layout_spec 우선, 없으면 하드코딩 폴백) ───
        margin = 65
        top_pad = 20
        item_text_h = 180

        # Zone positions from layout_spec or hardcoded defaults
        hero_zone = self._get_zone("hero", {"y_px": top_pad, "h_px": 1480})
        hero_top = hero_zone["y_px"]
        hero_h = hero_zone["h_px"]

        hero_gap = 30
        slogan_zone = self._get_zone("slogan", {"y_px": hero_top + hero_h + hero_gap, "h_px": 120 if subtitle else 0})
        slogan_top = slogan_zone["y_px"]
        slogan_h = slogan_zone["h_px"] if subtitle else 0

        feat_zone = self._get_zone("features", {"y_px": slogan_top + slogan_h, "h_px": 90 if features else 0})
        feat_top = feat_zone["y_px"]
        feat_h = feat_zone["h_px"] if features else 0

        promo_h = 100 if promo else 0
        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 360, "h_px": 360})
        cta_h = cta_zone["h_px"]

        items_zone = self._get_zone("items", {"y_px": feat_top + feat_h + 30, "h_px": 0})
        items_top = items_zone["y_px"]
        items_avail = CANVAS_H - items_top - promo_h - cta_h - 40

        # 그리드 열수
        if n_items <= 2:
            cols = 2
        elif n_items <= 4:
            cols = n_items
        elif n_items <= 6:
            cols = 3
        else:
            cols = 4
        rows = (n_items + cols - 1) // cols

        gap = 30
        col_w = (CANVAS_W - 2 * margin - (cols - 1) * gap) // cols
        img_h = min(int(col_w * 0.85), (items_avail - rows * item_text_h) // rows)
        cell_h = img_h + item_text_h

        # Typography from layout_spec or hardcoded defaults
        t_slogan = self._get_typo("slogan", {"size_px": 52, "weight": "700", "color": text_c})
        t_features = self._get_typo("features", {"size_px": 30, "weight": "500", "color": text_c})
        t_item_name = self._get_typo("item_name", {"size_px": 40, "weight": "700", "color": text_c})
        t_item_desc = self._get_typo("item_description", {"size_px": 28, "weight": "400", "color": text_c})
        t_item_sub = self._get_typo("item_sub_description", {"size_px": 24, "weight": "300", "color": text_c})
        t_item_price = self._get_typo("item_price", {"size_px": 36, "weight": "700", "color": accent})

        # ─── SVG 조립 ───
        parts = []

        # --- Header ---
        parts.append(self._svg_header(bg))

        # --- Hero ---
        hero_ref = self._img_ref("hero.png")
        if hero_ref:
            parts.append(
                f"""
  <!-- ===== HERO (메인 이미지 + 텍스트) ===== -->
  <image href="{hero_ref}" xlink:href="{hero_ref}"
         x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}"
         preserveAspectRatio="xMidYMid meet"/>"""
            )
        else:
            parts.append(
                f"""
  <!-- HERO placeholder -->
  <rect x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.1"/>
  <text x="{CANVAS_W // 2}" y="{hero_top + hero_h // 2}" text-anchor="middle"
        class="title" font-size="72" fill="{brand}" opacity="0.25">HERO IMAGE</text>"""
            )

        # --- Transition: hero → slogan ---
        trans_svg = self._svg_transition("hero", "slogan", hero_top + hero_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Slogan (슬로건 — 크고 굵게, 독립 섹션) ---
        if subtitle:
            # 상단 장식선
            parts.append(
                f"""
  <!-- ===== SLOGAN ===== -->
  <rect x="0" y="{slogan_top}" width="{CANVAS_W}" height="{slogan_h}" fill="{brand}" opacity="0.04"/>
  <line x1="{margin + 100}" y1="{slogan_top + 15}" x2="{CANVAS_W - margin - 100}" y2="{slogan_top + 15}"
        stroke="{accent}" stroke-width="2" opacity="0.4"/>
  <text x="{CANVAS_W // 2}" y="{slogan_top + slogan_h // 2 + 16}"
        text-anchor="middle" class="title" font-size="{t_slogan['size_px']}" font-weight="{t_slogan['weight']}" fill="{t_slogan['color']}"
        letter-spacing="3">
    {subtitle}
  </text>
  <line x1="{margin + 100}" y1="{slogan_top + slogan_h - 15}" x2="{CANVAS_W - margin - 100}" y2="{slogan_top + slogan_h - 15}"
        stroke="{accent}" stroke-width="2" opacity="0.4"/>"""
            )

        # --- Features strip ---
        if features:
            feat_text = "  \u2022  ".join(features)  # bullet separator
            parts.append(
                f"""
  <!-- ===== FEATURES ===== -->
  <rect x="0" y="{feat_top}" width="{CANVAS_W}" height="{feat_h}" fill="{brand}" opacity="0.08"/>
  <line x1="{margin}" y1="{feat_top}" x2="{CANVAS_W - margin}" y2="{feat_top}"
        stroke="{brand}" stroke-width="1" opacity="0.15"/>
  <text x="{CANVAS_W // 2}" y="{feat_top + feat_h // 2 + 10}"
        text-anchor="middle" class="title" font-size="{t_features['size_px']}" font-weight="{t_features['weight']}" fill="{t_features['color']}" letter-spacing="2">
    {feat_text}
  </text>"""
            )
            # Bottom line: use transition system if layout_spec, else fallback hardcoded line
            trans_svg = self._svg_transition("features", "items", feat_top + feat_h, accent, margin)
            if trans_svg:
                parts.append(trans_svg)
            else:
                parts.append(
                    f"""  <line x1="{margin}" y1="{feat_top + feat_h}" x2="{CANVAS_W - margin}" y2="{feat_top + feat_h}"
        stroke="{brand}" stroke-width="1" opacity="0.15"/>"""
                )

        # --- Items grid ---
        if items:
            parts.append(f"\n  <!-- ===== ITEMS ({n_items}개 품목) ===== -->")
            for idx, item in enumerate(items):
                col = idx % cols
                row = idx // cols
                x = margin + col * (col_w + gap)
                y = items_top + row * (cell_h + gap)

                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                name = item.get("name", "")
                desc = item.get("description", "")
                sub_desc = item.get("sub_description", "")
                price = item.get("price", "")

                cx = x + col_w // 2  # center x

                if item_ref:
                    parts.append(
                        f"""  <g id="item-{idx + 1}">
    <!-- {name} -->
    <image href="{item_ref}" xlink:href="{item_ref}"
           x="{x}" y="{y}" width="{col_w}" height="{img_h}"
           preserveAspectRatio="xMidYMid meet"/>
    <text x="{cx}" y="{y + img_h + 44}"
          text-anchor="middle" class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
      {name}
    </text>
    <text x="{cx}" y="{y + img_h + 80}"
          text-anchor="middle" class="body" font-size="{t_item_desc['size_px']}" font-weight="{t_item_desc['weight']}" fill="{t_item_desc['color']}" opacity="0.65">
      {desc}
    </text>"""
                    )
                    if sub_desc:
                        parts.append(
                            f"""    <text x="{cx}" y="{y + img_h + 110}"
          text-anchor="middle" class="light" font-size="{t_item_sub['size_px']}" font-weight="{t_item_sub['weight']}" fill="{t_item_sub['color']}" opacity="0.5">
      {sub_desc}
    </text>"""
                        )
                    if price:
                        parts.append(
                            f"""    <text x="{cx}" y="{y + img_h + 150}"
          text-anchor="middle" class="title" font-size="{t_item_price['size_px']}" font-weight="{t_item_price['weight']}" fill="{t_item_price['color']}">
      {price}
    </text>"""
                        )
                    parts.append("  </g>")
                else:
                    parts.append(
                        f"""  <g id="item-{idx + 1}">
    <!-- {name} (placeholder) -->
    <rect x="{x}" y="{y}" width="{col_w}" height="{img_h}" rx="12"
          fill="{brand}" opacity="0.06"/>
    <text x="{cx}" y="{y + img_h // 2 + 8}"
          text-anchor="middle" class="body" font-size="32" fill="{brand}" opacity="0.2">
      {name}
    </text>
    <text x="{cx}" y="{y + img_h + 44}"
          text-anchor="middle" class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
      {name}
    </text>
    <text x="{cx}" y="{y + img_h + 80}"
          text-anchor="middle" class="body" font-size="{t_item_desc['size_px']}" font-weight="{t_item_desc['weight']}" fill="{t_item_desc['color']}" opacity="0.65">
      {desc}
    </text>
  </g>"""
                    )

        # --- Transition: items → promo ---
        items_end = items_top + rows * (cell_h + gap) if items else feat_top + feat_h
        trans_svg = self._svg_transition("items", "promo", items_end, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Promo banner ---
        if promo:
            promo_y = items_end + 20
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, margin))

            # --- Transition: promo → cta ---
            trans_svg = self._svg_transition("promo", "cta", promo_y + promo_h, accent, margin)
            if trans_svg:
                parts.append(trans_svg)

        # --- CTA ---
        cta_y = CANVAS_H - cta_h
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        # --- Trim marks ---
        parts.append(self._svg_trim_marks())

        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 2: Hero Dominant ───

    def _build_hero_dominant(self, style: dict, copy_data: dict) -> str:
        """히어로 도미넌트 — 히어로 62% → 슬로건 5% → 품목 가로 14% → 프로모 7% → CTA 12%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        cta = copy_data.get("cta", {})
        subtitle = copy_data.get("subtitle", "")
        promo = copy_data.get("promo", {})
        n_items = min(len(items), 4)  # max 4 items in this layout

        margin = 65

        # Zone positions from layout_spec or hardcoded defaults
        hero_zone = self._get_zone("hero", {"y_px": 0, "h_px": 1860})
        hero_h = hero_zone["h_px"]

        slogan_zone = self._get_zone("slogan", {"y_px": hero_h, "h_px": 150})
        slogan_y = slogan_zone["y_px"]
        slogan_h = slogan_zone["h_px"]

        items_zone = self._get_zone("items", {"y_px": slogan_y + slogan_h, "h_px": 420})
        items_y = items_zone["y_px"]
        items_h = items_zone["h_px"]

        promo_h = 210 if promo else 0
        promo_y = items_y + items_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 360, "h_px": 360})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        # Typography from layout_spec or hardcoded defaults
        t_slogan = self._get_typo("slogan", {"size_px": 56, "weight": "700", "color": text_c})
        t_item_name = self._get_typo("item_name", {"size_px": 36, "weight": "700", "color": text_c})
        t_item_desc = self._get_typo("item_description", {"size_px": 26, "weight": "400", "color": text_c})

        parts = []
        parts.append(self._svg_header(bg))

        # --- Hero (62%) ---
        hero_ref = self._img_ref("hero.png")
        if hero_ref:
            parts.append(
                f"""
  <!-- ===== HERO (62%) ===== -->
  <image href="{hero_ref}" xlink:href="{hero_ref}"
         x="0" y="0" width="{CANVAS_W}" height="{hero_h}"
         preserveAspectRatio="xMidYMid meet"/>"""
            )
        else:
            parts.append(
                f"""
  <!-- HERO placeholder -->
  <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.1"/>
  <text x="{CANVAS_W // 2}" y="{hero_h // 2}" text-anchor="middle"
        class="title" font-size="72" fill="{brand}" opacity="0.25">HERO IMAGE</text>"""
            )

        # --- Transition: hero → slogan ---
        trans_svg = self._svg_transition("hero", "slogan", hero_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Slogan (5%) ---
        if subtitle:
            parts.append(
                f"""
  <!-- ===== SLOGAN ===== -->
  <rect x="0" y="{slogan_y}" width="{CANVAS_W}" height="{slogan_h}" fill="{brand}" opacity="0.05"/>
  <text x="{CANVAS_W // 2}" y="{slogan_y + slogan_h // 2 + 20}"
        text-anchor="middle" class="title" font-size="{t_slogan['size_px']}" font-weight="{t_slogan['weight']}" fill="{t_slogan['color']}"
        letter-spacing="3">
    {subtitle}
  </text>"""
            )

        # --- Items horizontal row (14%) ---
        if items:
            parts.append(
                f"\n  <!-- ===== ITEMS ({n_items}개 품목, 가로 리스트) ===== -->"
            )
            cols = max(n_items, 2)
            col_w = (CANVAS_W - 2 * margin - (cols - 1) * 30) // cols
            img_size = min(150, col_w - 20)

            for idx in range(n_items):
                item = items[idx]
                x = margin + idx * (col_w + 30)
                cx = x + col_w // 2
                img_x = cx - img_size // 2
                img_y = items_y + 30

                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                name = item.get("name", "")
                desc = item.get("description", "")

                if item_ref:
                    parts.append(
                        f"""  <g id="item-{idx + 1}">
    <image href="{item_ref}" xlink:href="{item_ref}"
           x="{img_x}" y="{img_y}" width="{img_size}" height="{img_size}"
           preserveAspectRatio="xMidYMid meet"/>
    <text x="{cx}" y="{img_y + img_size + 40}"
          text-anchor="middle" class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
      {name}
    </text>
    <text x="{cx}" y="{img_y + img_size + 72}"
          text-anchor="middle" class="body" font-size="{t_item_desc['size_px']}" font-weight="{t_item_desc['weight']}" fill="{t_item_desc['color']}" opacity="0.6">
      {desc}
    </text>
  </g>"""
                    )
                else:
                    parts.append(
                        f"""  <g id="item-{idx + 1}">
    <rect x="{img_x}" y="{img_y}" width="{img_size}" height="{img_size}" rx="8"
          fill="{brand}" opacity="0.06"/>
    <text x="{cx}" y="{img_y + img_size + 40}"
          text-anchor="middle" class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
      {name}
    </text>
    <text x="{cx}" y="{img_y + img_size + 72}"
          text-anchor="middle" class="body" font-size="{t_item_desc['size_px']}" font-weight="{t_item_desc['weight']}" fill="{t_item_desc['color']}" opacity="0.6">
      {desc}
    </text>
  </g>"""
                    )

        # --- Transition: items → promo ---
        trans_svg = self._svg_transition("items", "promo", items_y + items_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Promo (7%) ---
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, margin))

        # --- CTA (12%) ---
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 3: Magazine Split ───

    def _build_magazine_split(self, style: dict, copy_data: dict) -> str:
        """매거진 분할 — 히어로 35% → 섹션A 22% → 섹션B 22% → 프로모 6% → CTA 15%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")
        section_bg_alt = style.get("section_bg_alt", "#F0F0F0")

        items = copy_data.get("items", [])
        cta = copy_data.get("cta", {})
        promo = copy_data.get("promo", {})
        sections = copy_data.get("sections", [])

        margin = 65

        # Zone positions from layout_spec or hardcoded defaults
        hero_zone = self._get_zone("hero", {"y_px": 0, "h_px": 1050})
        hero_h = hero_zone["h_px"]

        section_a_zone = self._get_zone("section_a", {"y_px": hero_h, "h_px": 660})
        section_a_y = section_a_zone["y_px"]
        section_h_a = section_a_zone["h_px"]

        section_b_zone = self._get_zone("section_b", {"y_px": section_a_y + section_h_a, "h_px": 660})
        section_b_y = section_b_zone["y_px"]
        section_h_b = section_b_zone["h_px"]

        promo_h = 180 if promo else 0
        promo_y = section_b_y + section_h_b

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 450, "h_px": 450})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        # Typography from layout_spec or hardcoded defaults
        t_section_title = self._get_typo("section_title", {"size_px": 44, "weight": "700", "color": accent})
        t_section_item = self._get_typo("section_item", {"size_px": 30, "weight": "400", "color": text_c})

        # Split items into 2 groups
        if sections and len(sections) >= 2:
            sec_a_title = sections[0].get("title", "서비스 A")
            sec_a_items = sections[0].get("items", [])
            sec_b_title = sections[1].get("title", "서비스 B")
            sec_b_items = sections[1].get("items", [])
        else:
            mid = (len(items) + 1) // 2
            sec_a_title = "주요 서비스"
            sec_a_items = items[:mid]
            sec_b_title = "추가 서비스"
            sec_b_items = items[mid:]

        parts = []
        parts.append(self._svg_header(bg))

        # --- Hero (35%) ---
        hero_ref = self._img_ref("hero.png")
        if hero_ref:
            parts.append(
                f"""
  <!-- ===== HERO (35%) ===== -->
  <image href="{hero_ref}" xlink:href="{hero_ref}"
         x="0" y="0" width="{CANVAS_W}" height="{hero_h}"
         preserveAspectRatio="xMidYMid meet"/>"""
            )
        else:
            parts.append(
                f"""
  <!-- HERO placeholder -->
  <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.1"/>
  <text x="{CANVAS_W // 2}" y="{hero_h // 2}" text-anchor="middle"
        class="title" font-size="72" fill="{brand}" opacity="0.25">HERO IMAGE</text>"""
            )

        # --- Transition: hero → section_a ---
        trans_svg = self._svg_transition("hero", "section_a", hero_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Section A (22%) ---
        parts.append(
            f"""
  <!-- ===== SECTION A ===== -->
  <rect x="0" y="{section_a_y}" width="{CANVAS_W}" height="{section_h_a}"
        fill="{brand}" opacity="0.05"/>
  <text x="{margin}" y="{section_a_y + 60}"
        class="title" font-size="{t_section_title['size_px']}" font-weight="{t_section_title['weight']}" fill="{t_section_title['color']}">
    {sec_a_title}
  </text>
  <line x1="{margin}" y1="{section_a_y + 80}" x2="{CANVAS_W - margin}" y2="{section_a_y + 80}"
        stroke="{accent}" stroke-width="2" opacity="0.3"/>"""
        )
        line_y = section_a_y + 130
        for item in sec_a_items:
            name = item.get("name", "")
            desc = item.get("description", "")
            bullet_text = f"\u2022  {name}"
            if desc:
                bullet_text += f" \u2014 {desc}"
            parts.append(
                f"""  <text x="{margin + 30}" y="{line_y}"
        class="body" font-size="{t_section_item['size_px']}" font-weight="{t_section_item['weight']}" fill="{t_section_item['color']}">
    {bullet_text}
  </text>"""
            )
            line_y += 55


        # --- Transition: section_a → section_b ---
        trans_svg = self._svg_transition("section_a", "section_b", section_a_y + section_h_a, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Section B (22%) ---
        parts.append(
            f"""
  <!-- ===== SECTION B ===== -->
  <rect x="0" y="{section_b_y}" width="{CANVAS_W}" height="{section_h_b}"
        fill="{section_bg_alt}" opacity="0.4"/>
  <text x="{margin}" y="{section_b_y + 60}"
        class="title" font-size="{t_section_title['size_px']}" font-weight="{t_section_title['weight']}" fill="{t_section_title['color']}">
    {sec_b_title}
  </text>
  <line x1="{margin}" y1="{section_b_y + 80}" x2="{CANVAS_W - margin}" y2="{section_b_y + 80}"
        stroke="{accent}" stroke-width="2" opacity="0.3"/>"""
        )
        line_y = section_b_y + 130
        for item in sec_b_items:
            name = item.get("name", "")
            desc = item.get("description", "")
            bullet_text = f"\u2022  {name}"
            if desc:
                bullet_text += f" \u2014 {desc}"
            parts.append(
                f"""  <text x="{margin + 30}" y="{line_y}"
        class="body" font-size="{t_section_item['size_px']}" font-weight="{t_section_item['weight']}" fill="{t_section_item['color']}">
    {bullet_text}
  </text>"""
            )
            line_y += 55

        # --- Promo (6%) ---
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, margin))

        # --- CTA (15%) ---
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 4: Bold Typo ───

    def _build_bold_typo(self, style: dict, copy_data: dict) -> str:
        """볼드 타이포 — 헤더 25% → 히어로 30% → 가격표 20% → 프로모 10% → CTA 15%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        cta = copy_data.get("cta", {})
        promo = copy_data.get("promo", {})
        hero_data = copy_data.get("hero", {})
        biz_name = hero_data.get("headline", "")
        badge = hero_data.get("badge", "")

        margin = 65

        # Zone positions from layout_spec or hardcoded defaults
        header_zone = self._get_zone("header", {"y_px": 0, "h_px": 750})
        header_h = header_zone["h_px"]

        hero_zone = self._get_zone("hero", {"y_px": header_h, "h_px": 900})
        hero_y = hero_zone["y_px"]
        hero_h = hero_zone["h_px"]

        pricelist_zone = self._get_zone("price_list", {"y_px": hero_y + hero_h, "h_px": 600})
        pricelist_y = pricelist_zone["y_px"]
        pricelist_h = pricelist_zone["h_px"]

        promo_h = 300 if promo else 0
        promo_y = pricelist_y + pricelist_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 450, "h_px": 450})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        parts = []
        parts.append(self._svg_header(bg))

        # --- Header (25%) — MASSIVE business name ---
        # Determine text color for contrast on brand bg
        text_on_brand = accent if accent != brand else "#FFFFFF"
        # Typography from layout_spec or hardcoded defaults
        t_biz_name = self._get_typo("business_name", {"size_px": 140, "weight": "900", "color": text_on_brand})
        t_badge = self._get_typo("hero_badge", {"size_px": 32, "weight": "400", "color": text_on_brand})
        t_item_name = self._get_typo("item_name", {"size_px": 40, "weight": "700", "color": text_c})
        t_item_price = self._get_typo("item_price", {"size_px": 48, "weight": "700", "color": accent})
        parts.append(
            f"""
  <!-- ===== HEADER (볼드 타이포) ===== -->
  <rect x="0" y="0" width="{CANVAS_W}" height="{header_h}" fill="{brand}"/>
  <text x="{CANVAS_W // 2}" y="{header_h // 2 + 40}"
        text-anchor="middle" class="title" font-size="{t_biz_name['size_px']}" font-weight="{t_biz_name['weight']}" fill="{t_biz_name['color']}">
    {biz_name}
  </text>"""
        )
        if badge:
            parts.append(
                f"""  <text x="{CANVAS_W // 2}" y="{header_h // 2 + 100}"
        text-anchor="middle" class="body" font-size="{t_badge['size_px']}" font-weight="{t_badge['weight']}" fill="{t_badge['color']}" opacity="0.7">
    {badge}
  </text>"""
            )

        # --- Transition: header → hero ---
        trans_svg = self._svg_transition("header", "hero", header_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Hero (30%) ---
        hero_ref = self._img_ref("hero.png")
        if hero_ref:
            parts.append(
                f"""
  <!-- ===== HERO (30%) ===== -->
  <image href="{hero_ref}" xlink:href="{hero_ref}"
         x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}"
         preserveAspectRatio="xMidYMid meet"/>"""
            )
        else:
            parts.append(
                f"""
  <!-- HERO placeholder -->
  <rect x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.1"/>
  <text x="{CANVAS_W // 2}" y="{hero_y + hero_h // 2}" text-anchor="middle"
        class="title" font-size="72" fill="{brand}" opacity="0.25">HERO IMAGE</text>"""
            )

        # --- Price List (20%) ---
        parts.append(
            f"""
  <!-- ===== PRICE LIST ===== -->
  <rect x="0" y="{pricelist_y}" width="{CANVAS_W}" height="{pricelist_h}"
        fill="{brand}" opacity="0.03"/>"""
        )
        if items:
            n_items = len(items)
            row_h = min(80, (pricelist_h - 60) // max(n_items, 1))
            start_y = pricelist_y + 50
            for idx, item in enumerate(items):
                name = item.get("name", "")
                price = item.get("price", "")
                iy = start_y + idx * row_h
                # Item name (left)
                parts.append(
                    f"""  <text x="{margin + 20}" y="{iy}"
        class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
    {name}
  </text>"""
                )
                # Price (right)
                if price:
                    parts.append(
                        f"""  <text x="{CANVAS_W - margin - 20}" y="{iy}"
        text-anchor="end" class="title" font-size="{t_item_price['size_px']}" font-weight="{t_item_price['weight']}" fill="{t_item_price['color']}">
    {price}
  </text>"""
                    )
                # Separator line
                if idx < n_items - 1:
                    parts.append(
                        f"""  <line x1="{margin}" y1="{iy + 20}" x2="{CANVAS_W - margin}" y2="{iy + 20}"
        stroke="{text_c}" stroke-width="1" opacity="0.15"/>"""
                    )

        # --- Transition: price_list → promo ---
        trans_svg = self._svg_transition("price_list", "promo", pricelist_y + pricelist_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Promo (10%) ---
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, margin))

        # --- CTA (15%) ---
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 5: Side by Side ───

    def _build_side_by_side(self, style: dict, copy_data: dict) -> str:
        """좌우 분할 — 좌(히어로 50%) + 우(텍스트 50%) 70% → 프로모 12% → CTA 18%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        features = copy_data.get("features", [])
        cta = copy_data.get("cta", {})
        promo = copy_data.get("promo", {})
        hero_data = copy_data.get("hero", {})
        subtitle = copy_data.get("subtitle", "")
        biz_name = hero_data.get("headline", "")

        margin = 65
        half_w = CANVAS_W // 2  # 1065

        # Zone positions from layout_spec or hardcoded defaults
        hero_left_zone = self._get_zone("hero_left", {"x_px": 0, "w_px": half_w, "y_px": 0, "h_px": 2100})
        top_h = hero_left_zone["h_px"]

        promo_h = 360 if promo else 0
        promo_y = top_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 540, "h_px": 540})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        # Typography from layout_spec or hardcoded defaults
        t_biz_name = self._get_typo("business_name", {"size_px": 48, "weight": "700", "color": text_c})
        t_subtitle = self._get_typo("hero_subtext", {"size_px": 32, "weight": "500", "color": text_c})
        t_item_name = self._get_typo("item_name", {"size_px": 36, "weight": "700", "color": text_c})
        t_item_price = self._get_typo("item_price", {"size_px": 36, "weight": "700", "color": accent})
        t_item_desc = self._get_typo("item_description", {"size_px": 24, "weight": "300", "color": text_c})
        t_features = self._get_typo("features", {"size_px": 24, "weight": "400", "color": text_c})

        parts = []
        parts.append(self._svg_header(bg))

        # --- LEFT: Hero image (50% width, 70% height) ---
        hero_ref = self._img_ref("hero.png")
        if hero_ref:
            parts.append(
                f"""
  <!-- ===== LEFT: HERO ===== -->
  <image href="{hero_ref}" xlink:href="{hero_ref}"
         x="0" y="0" width="{half_w}" height="{top_h}"
         preserveAspectRatio="xMidYMid meet"/>"""
            )
        else:
            parts.append(
                f"""
  <!-- LEFT: HERO placeholder -->
  <rect x="0" y="0" width="{half_w}" height="{top_h}" fill="{brand}" opacity="0.1"/>
  <text x="{half_w // 2}" y="{top_h // 2}" text-anchor="middle"
        class="title" font-size="56" fill="{brand}" opacity="0.25">HERO</text>"""
            )

        # --- Vertical divider ---
        divider_y1 = int(top_h * 0.05)
        divider_y2 = int(top_h * 0.95)
        parts.append(
            f"""
  <!-- ===== DIVIDER ===== -->
  <line x1="{half_w}" y1="{divider_y1}" x2="{half_w}" y2="{divider_y2}"
        stroke="{accent}" stroke-width="2" opacity="0.4"/>"""
        )

        # --- RIGHT: Text content ---
        rx = half_w + margin  # right content x start
        rw = half_w - 2 * margin  # right content width
        ry = 80  # start y

        parts.append(
            f"""
  <!-- ===== RIGHT: TEXT CONTENT ===== -->
  <text x="{rx}" y="{ry}"
        class="title" font-size="{t_biz_name['size_px']}" font-weight="{t_biz_name['weight']}" fill="{t_biz_name['color']}">
    {biz_name}
  </text>"""
        )
        ry += 50

        if subtitle:
            parts.append(
                f"""  <text x="{rx}" y="{ry}"
        class="body" font-size="{t_subtitle['size_px']}" font-weight="{t_subtitle['weight']}" fill="{t_subtitle['color']}" opacity="0.7">
    {subtitle}
  </text>"""
            )
            ry += 60

        # Accent line under title
        parts.append(
            f"""  <line x1="{rx}" y1="{ry}" x2="{rx + rw}" y2="{ry}"
        stroke="{accent}" stroke-width="2" opacity="0.4"/>"""
        )
        ry += 40

        # Items as vertical price list
        if items:
            parts.append(f"\n  <!-- ===== ITEMS (가격 리스트) ===== -->")
            for idx, item in enumerate(items):
                name = item.get("name", "")
                price = item.get("price", "")
                desc = item.get("description", "")

                # Name + price on same line
                parts.append(
                    f"""  <text x="{rx}" y="{ry}"
        class="title" font-size="{t_item_name['size_px']}" font-weight="{t_item_name['weight']}" fill="{t_item_name['color']}">
    {name}
  </text>"""
                )
                if price:
                    parts.append(
                        f"""  <text x="{rx + rw}" y="{ry}"
        text-anchor="end" class="title" font-size="{t_item_price['size_px']}" font-weight="{t_item_price['weight']}" fill="{t_item_price['color']}">
    {price}
  </text>"""
                    )
                ry += 34
                # Description below
                if desc:
                    parts.append(
                        f"""  <text x="{rx}" y="{ry}"
        class="light" font-size="{t_item_desc['size_px']}" font-weight="{t_item_desc['weight']}" fill="{t_item_desc['color']}" opacity="0.6">
    {desc}
  </text>"""
                    )
                    ry += 30
                ry += 26  # 60px total gap between items

        # Features at bottom of right section
        if features:
            feat_y = max(ry + 40, top_h - 200)
            parts.append(
                f"""
  <!-- ===== FEATURES (우측 하단) ===== -->
  <line x1="{rx}" y1="{feat_y - 20}" x2="{rx + rw}" y2="{feat_y - 20}"
        stroke="{brand}" stroke-width="1" opacity="0.15"/>"""
            )
            for feat in features:
                parts.append(
                    f"""  <text x="{rx}" y="{feat_y}"
        class="body" font-size="{t_features['size_px']}" font-weight="{t_features['weight']}" fill="{t_features['color']}" opacity="0.6">
    \u2022  {feat}
  </text>"""
                )
                feat_y += 36

        # --- Transition: hero_left → promo ---
        trans_svg = self._svg_transition("hero_left", "promo", top_h, accent, margin)
        if trans_svg:
            parts.append(trans_svg)

        # --- Promo (12%, full-width) ---
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, margin))

        # --- CTA (18%, full-width) ---
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Helpers ───

    def _img_ref(self, filename: str) -> Optional[str]:
        """이미지 참조 생성 (외부 경로 또는 base64 데이터 URI)"""
        path = self.images_dir / filename
        if not path.exists():
            return None
        if self.embed:
            b64 = base64.b64encode(path.read_bytes()).decode("utf-8")
            return f"data:image/png;base64,{b64}"
        return f"images/{filename}"

    def _load(self, name: str) -> dict:
        path = self.workdir / name
        if not path.exists():
            raise FileNotFoundError(f"필수 파일 없음: {path}")
        return json.loads(path.read_text(encoding="utf-8"))

    def _load_safe(self, name: str) -> dict:
        """JSON 파일 안전 로드 (없으면 빈 dict 반환)"""
        path = self.workdir / name
        if not path.exists():
            return {}
        try:
            return json.loads(path.read_text(encoding="utf-8"))
        except Exception:
            return {}

    def _save(self, name: str, data: dict):
        path = self.workdir / name
        path.write_text(
            json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8"
        )
        logger.info(f"  💾 저장: {name}")

    def _generate_preview(self):
        """poster.svg를 감싸는 preview.html 생성 (브라우저 확인용)"""
        preview_path = self.workdir / "preview.html"
        html = f"""<!DOCTYPE html>
<html lang="ko"><head><meta charset="utf-8">
<title>포스터 프리뷰</title>
<style>
* {{ margin:0; padding:0; box-sizing:border-box; }}
body {{ background:#2d2d2d; display:flex; justify-content:center; align-items:flex-start; padding:20px; min-height:100vh; }}
.poster-wrap {{ width:710px; max-width:100%; box-shadow:0 8px 40px rgba(0,0,0,0.5); }}
.poster-wrap object {{ width:100%; height:auto; display:block; }}
</style></head>
<body>
<div class="poster-wrap">
  <object data="poster.svg" type="image/svg+xml" width="710" height="{int(710 * CANVAS_H / CANVAS_W)}"></object>
</div>
</body></html>"""
        preview_path.write_text(html, encoding="utf-8")
        logger.info(f"  🌐 프리뷰: {preview_path}")


def main():
    parser = argparse.ArgumentParser(description="SVG 포스터 생성기 V2")
    parser.add_argument(
        "--workdir",
        required=True,
        help="작업 디렉토리 (brief.json, copy.json, design.json 포함)",
    )
    parser.add_argument(
        "--rebuild-svg",
        action="store_true",
        help="SVG만 재조립 (이미지 재생성 없이)",
    )
    parser.add_argument(
        "--regen-hero",
        action="store_true",
        help="히어로 이미지만 재생성",
    )
    parser.add_argument(
        "--regen-item",
        type=int,
        metavar="N",
        help="품목 N번 이미지만 재생성 (1부터 시작)",
    )
    parser.add_argument(
        "--embed",
        action="store_true",
        help="이미지를 base64로 SVG에 임베드 (단일 파일)",
    )
    parser.add_argument(
        "--layout",
        choices=LAYOUT_IDS,
        help="Force specific layout template",
    )
    parser.add_argument(
        "--art-direct",
        action="store_true",
        help="Phase 3: layout_spec.json 자동 생성 (brief+copy → 아트디렉팅)",
    )
    parser.add_argument(
        "--full",
        action="store_true",
        help="전체 파이프라인: 아트디렉팅 → 이미지 생성 → SVG 조립",
    )
    args = parser.parse_args()

    gen = PosterGenerator(args.workdir, embed=args.embed)
    if args.layout:
        gen.cli_layout = args.layout

    if args.full:
        ok = gen.art_direct()
        if ok:
            ok = gen.generate_all()
    elif args.art_direct:
        ok = gen.art_direct()
    elif args.rebuild_svg:
        ok = gen.rebuild_svg()
    elif args.regen_hero:
        ok = gen.regen_hero()
    elif args.regen_item:
        ok = gen.regen_item(args.regen_item)
    else:
        ok = gen.generate_all()

    sys.exit(0 if ok else 1)


if __name__ == "__main__":
    main()
