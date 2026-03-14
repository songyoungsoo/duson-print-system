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
    "block_grid",
]

POSTER_QUALITY_SUFFIX_FALLBACK = (
    "Photorealistic professional photography. "
    "Clean, modern composition with generous white space. "
    "High contrast, vibrant but professional colors. "
    "No watermarks, stock photo marks, pixel labels, cartoon, anime, or illustrated style. "
    "No ruler marks, guidelines, or dimension indicators."
)

ITEM_QUALITY_SUFFIX_FALLBACK = (
    "Photorealistic product/food photography. "
    "Clean composition, single subject focus. "
    "Warm natural lighting, shallow depth of field. "
    "No text, no labels, no overlays. "
    "No cartoon, anime, or illustrated style. No watermarks or stock photo marks."
)


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

        # Style presets
        style_path = Path(__file__).parent.parent / "config" / "style_presets.json"
        if style_path.exists():
            self.style_presets = json.loads(style_path.read_text(encoding="utf-8"))
        else:
            self.style_presets = {}
        self.active_style = None

        # Layout spec (Art Director output — Phase 3)
        self.layout_spec = self._load_safe("layout_spec.json")
        if self.layout_spec:
            logger.info(
                f"  \u2b50 layout_spec.json \ub85c\ub4dc\ub428 (layout: {self.layout_spec.get('layout_id', '?')})"
            )

    # ─── Style Preset Resolution ───

    def _resolve_style(self, brief: dict) -> dict:
        """brief.json의 poster_style / industry / tone으로 스타일 프리셋 결정"""
        import random

        presets = self.style_presets.get("presets", {})
        rules = self.style_presets.get("auto_select_rules", {})
        if not presets:
            return {}

        # 1. brief에 명시적 poster_style 지정
        explicit = brief.get("poster_style", "")
        if explicit and explicit in presets:
            logger.info(f"  \U0001f3a8 스타일 프리셋: {explicit} (명시적 선택)")
            return presets[explicit]

        # 2. 업종 매칭
        industry = brief.get("industry", brief.get("category", ""))
        by_industry = rules.get("by_industry", {})
        for keyword, preset_id in by_industry.items():
            if keyword in industry or industry in keyword:
                if preset_id in presets:
                    logger.info(
                        f"  \U0001f3a8 스타일 프리셋: {preset_id} (업종 '{industry}' 매칭)"
                    )
                    return presets[preset_id]

        # 3. 톤 키워드 매칭
        tone = brief.get("tone", "")
        by_tone = rules.get("by_tone_keywords", {})
        for keyword, preset_id in by_tone.items():
            if keyword in tone:
                if preset_id in presets:
                    logger.info(
                        f"  \U0001f3a8 스타일 프리셋: {preset_id} (톤 '{keyword}' 매칭)"
                    )
                    return presets[preset_id]

        # 4. 랜덤 fallback (다양성 확보)
        fallback_id = rules.get("fallback", "warm_cozy")
        all_ids = list(presets.keys())
        if len(all_ids) > 1:
            fallback_id = random.choice(all_ids)
            logger.info(
                f"  \U0001f3a8 스타일 프리셋: {fallback_id} (랜덤 선택 — 매칭 없음)"
            )
        else:
            logger.info(f"  \U0001f3a8 스타일 프리셋: {fallback_id} (기본값)")
        return presets.get(fallback_id, {})

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

        self.active_style = self._resolve_style(brief)
        hero_suffix = self.active_style.get(
            "hero_suffix", POSTER_QUALITY_SUFFIX_FALLBACK
        )
        item_suffix = self.active_style.get("item_suffix", ITEM_QUALITY_SUFFIX_FALLBACK)

        logger.info(f"{'=' * 55}")
        logger.info(f"🚀 포스터 생성 시작: {biz_name}")
        logger.info(f"   히어로 1장 + 품목 {len(items_cfg)}장")
        logger.info(f"{'=' * 55}")

        # 1. 히어로 이미지
        logger.info("🖼️  히어로 이미지 생성 중...")
        hero_ok = self.client.generate_image(
            self._enrich_hero_prompt(hero_cfg),
            str(self.images_dir / "hero.png"),
            aspect_ratio=self._get_hero_aspect_ratio(design),
            resize_to=None,
            quality_suffix=hero_suffix,
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
                self._enrich_item_prompt(item, i),
                str(self.images_dir / f"item_{i + 1:02d}.png"),
                aspect_ratio=item.get("aspect_ratio", "1:1"),
                resize_to=None,
                quality_suffix=item_suffix,
            )
            if ok:
                item_ok += 1
                logger.info(f"  ✅ {item.get('name')} 완료")
            else:
                logger.error(f"  ❌ {item.get('name')} 실패")

        # 3. 이벤트 배너 이미지 (block_grid용)
        event_prompt = design.get("event", {}).get("prompt", "")
        if event_prompt:
            time.sleep(rate_delay)
            logger.info("🖼️  이벤트 배너 이미지 생성 중...")
            event_ok = self.client.generate_image(
                event_prompt,
                str(self.images_dir / "event.png"),
                aspect_ratio=design.get("event", {}).get("aspect_ratio", "16:9"),
                resize_to=None,
                quality_suffix=hero_suffix,
            )
            if event_ok:
                logger.info("  ✅ 이벤트 배너 완료")
            else:
                logger.warning("  ⚠️ 이벤트 배너 실패 (계속 진행)")

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

        brief = self._load_safe("brief.json") or {}
        self.active_style = self._resolve_style(brief)
        hero_suffix = self.active_style.get(
            "hero_suffix", POSTER_QUALITY_SUFFIX_FALLBACK
        )

        hero = design.get("hero", {})
        logger.info("🖼️  히어로 이미지 재생성 중...")
        ok = self.client.generate_image(
            self._enrich_hero_prompt(hero),
            str(self.images_dir / "hero.png"),
            aspect_ratio=self._get_hero_aspect_ratio(design),
            resize_to=None,
            quality_suffix=hero_suffix,
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

        brief = self._load_safe("brief.json") or {}
        self.active_style = self._resolve_style(brief)
        item_suffix = self.active_style.get("item_suffix", ITEM_QUALITY_SUFFIX_FALLBACK)

        item = items[idx - 1]
        logger.info(f"🖼️  {item.get('name', '?')} 이미지 재생성 중...")
        ok = self.client.generate_image(
            self._enrich_item_prompt(item, idx - 1),
            str(self.images_dir / f"item_{idx:02d}.png"),
            aspect_ratio=item.get("aspect_ratio", "1:1"),
            resize_to=None,
            quality_suffix=item_suffix,
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

    def auto_copy(self) -> bool:
        """Phase 1: brief.json → copy.json 자동 생성 (Gemini)"""
        try:
            brief = self._load("brief.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        logger.info("✍️  카피 생성 중... (copy.json)")

        self.active_style = self._resolve_style(brief)
        copy_tone = self.active_style.get("copy_tone", {})
        style_name = self.active_style.get("name", "")

        tone_guide = ""
        if copy_tone:
            tone_guide = f"""
## VOICE & TONE GUIDE (스타일: {style_name})
- 목소리: {copy_tone.get("voice", "")}
- 자주 쓸 표현: {", ".join(copy_tone.get("keywords", []))}
- 피할 표현: {", ".join(copy_tone.get("avoid", []))}
- 헤드라인 스타일: {copy_tone.get("headline_style", "")}
- 헤드라인 예시 (참고만, 그대로 복사 금지): {", ".join(copy_tone.get("example_headlines", []))}
"""

        system_prompt = f"""You are a top-tier Korean advertising copywriter — think 제일기획, 이노션 수준.
당신은 단어 하나하나가 매출로 직결되는 인쇄 포스터 카피를 작성합니다.
판에 박힌 '최고의', '특별한', '프리미엄'은 절대 쓰지 마세요. 실제 카피라이터처럼 생각하세요.

## CREATIVE PRINCIPLES
1. 헤드라인은 고객의 발걸음을 멈추게 하는 한마디 — 짧을수록 강하다 (2-8글자)
2. 서브카피는 "왜 여기?" 질문에 한 문장으로 답한다
3. 품목 설명은 맛/경험을 자극하는 감각적 표현 (시각+미각+촉각)
4. 이벤트 문구는 긴급성+혜택을 동시에 전달
5. CTA는 행동을 유도하는 완성된 문장
6. 업종별 톤을 철저히 지킨다 — 고기집에 '소확행', 병원에 '맛있다!'는 금지
{tone_guide}
## OUTPUT FORMAT (JSON)
Return a JSON object with this EXACT structure:
{{
  "hero": {{
    "headline": "대표 헤드라인 (가게명 또는 캐치프레이즈, 2-8글자)",
    "subtext": "부제 (위치·특징·컨셉, 20자 이내)",
    "badge": "뱃지 텍스트 (GRAND OPEN, NEW, SPECIAL 등)"
  }},
  "subtitle": "서브헤드라인 (핵심 셋링포인트, 15-30자)",
  "items": [
    {{"name": "품목명", "description": "감각적 한줄 설명", "price": "₩가격"}},
    ...
  ],
  "features": ["특징1", "특징2", "특징3"],
  "promo": {{
    "title": "이벤트 제목 (긴급성 포함)",
    "detail": "이벤트 세부내용 (구체적 혜택)"
  }},
  "cta": {{
    "headline": "CTA 헤드라인 (행동 유도 문장)",
    "phone": "전화번호",
    "address": "주소/위치",
    "hours": "영업시간"
  }}
}}

## RULES
1. items는 4-6개. brief에 메뉴가 있으면 그대로 사용, 없으면 업종에 맞는 대표 메뉴 창작
2. 가격은 brief에 있으면 그대로, 없으면 업종 평균 가격대로 합리적으로 설정
3. hero.headline은 짧고 강렬하게 — '정성을 담은 한 그릇', '불 위의 예술' 같은 감각적 표현
4. 한국어 자연스러운 구어체. 영어는 badge에만 허용 (GRAND OPEN, NEW 등)
5. promo가 brief에 없으면 업종+시즌에 맞는 자연스러운 프로모션 창작
6. cta.phone/address/hours는 brief에 있으면 그대로, 없으면 빈 문자열
7. description은 '~입니다' 체 금지. 명사형 종결 또는 감각 묘사 ('직화의 풍미 그대로', '매일 굽는 수제 크로와상')"""

        items_from_brief = brief.get("items", brief.get("menu", []))
        # Support menu_items as comma-separated string
        if not items_from_brief and brief.get("menu_items"):
            menu_str = brief["menu_items"]
            if isinstance(menu_str, str):
                items_from_brief = [
                    {"name": m.strip()} for m in menu_str.split(",") if m.strip()
                ]
        features_from_brief = brief.get("features", [])
        promo_from_brief = brief.get("promo", brief.get("promotion", {}))
        # Support promo as a simple string
        if isinstance(promo_from_brief, str) and promo_from_brief:
            promo_from_brief = {"title": promo_from_brief, "detail": ""}
        # Support contact as nested dict OR top-level phone/address fields
        contact = brief.get("contact", {})
        if not contact:
            top_phone = brief.get("phone", "")
            top_addr = brief.get("address", "")
            top_hours = brief.get("hours", "")
            if top_phone or top_addr or top_hours:
                contact = {"phone": top_phone, "address": top_addr, "hours": top_hours}

        # Flexible business name extraction
        biz_name = (
            brief.get("business_name", "")
            or brief.get("store_name", "")
            or brief.get("name", "")
        )

        user_prompt = f"""Create copy.json for this business:

## BUSINESS
- 상호: {biz_name}
- 업종: {brief.get("industry", "")}
- 카테고리: {brief.get("category", "")}
- 특징: {", ".join(features_from_brief) if features_from_brief else "N/A"}
- 타겟: {brief.get("target_audience", "")}
- 톤: {brief.get("tone", "")}
- 분위기: {brief.get("mood", "")}
- 목적: {brief.get("purpose", "")}"""

        if items_from_brief:
            if isinstance(items_from_brief[0], dict):
                items_str = "\n".join(
                    [
                        f"  - {it.get('name', '?')}: {it.get('description', '')} [{it.get('price', '')}]"
                        for it in items_from_brief
                    ]
                )
            else:
                items_str = "\n".join([f"  - {it}" for it in items_from_brief])
            user_prompt += f"\n\n## 메뉴/품목 (사장님 제공)\n{items_str}"

        if isinstance(promo_from_brief, dict) and promo_from_brief:
            user_prompt += f"\n\n## 프로모션\n- 제목: {promo_from_brief.get('title', '')}\n- 내용: {promo_from_brief.get('detail', '')}"
        elif isinstance(promo_from_brief, str) and promo_from_brief:
            user_prompt += f"\n\n## 프로모션\n{promo_from_brief}"

        if contact:
            user_prompt += f"\n\n## 연락처\n- 전화: {contact.get('phone', '')}\n- 주소: {contact.get('address', '')}\n- 영업시간: {contact.get('hours', '')}"

        try:
            result = self.client.generate_text_json(user_prompt, system_prompt)
        except Exception as e:
            logger.error(f"❌ 카피 생성 실패: {e}")
            return False

        # Validate essential fields
        if not result.get("hero") or not result.get("items"):
            logger.error("❌ copy.json에 필수 필드(hero, items) 누락")
            return False

        # Post-fixup: inject contact info from brief into CTA if Gemini left it empty
        if contact:
            cta = result.get("cta", {})
            if not cta.get("phone") and contact.get("phone"):
                cta["phone"] = contact["phone"]
            if not cta.get("address") and contact.get("address"):
                cta["address"] = contact["address"]
            if not cta.get("hours") and contact.get("hours"):
                cta["hours"] = contact["hours"]
            result["cta"] = cta

        self._save("copy.json", result)
        logger.info(f"✅ 카피 생성 완료 — {len(result.get('items', []))}개 품목")
        return True

    def auto_design(self) -> bool:
        """Phase 2: brief+copy → design.json 자동 생성 (Gemini)"""
        try:
            brief = self._load("brief.json")
            copy_data = self._load("copy.json")
        except FileNotFoundError as e:
            logger.error(f"❌ {e}")
            return False

        logger.info("🎨 디자인 생성 중... (design.json)")

        if not self.active_style:
            self.active_style = self._resolve_style(brief)

        # Load color palettes for reference
        config_dir = Path(__file__).parent.parent / "config"
        palettes = self._load_config(config_dir / "color_palettes.json")
        palette_info = ""
        if palettes:
            industry = brief.get("industry", brief.get("category", ""))
            palette_data = palettes.get("palettes", palettes)
            for pid, pdata in palette_data.items():
                if not isinstance(pdata, dict):
                    continue
                industries = pdata.get("industries", [])
                if any(t in industry for t in industries):
                    palette_info += f"\nRecommended palette '{pid}': brand={pdata.get('brand_color', '')}, accent={pdata.get('accent_color', '')}, bg={pdata.get('bg_color', '')}, mood={pdata.get('mood', '')}"

        style_direction = ""
        if self.active_style:
            style_direction = f"""
## PHOTOGRAPHY STYLE DIRECTION (MANDATORY)
Style: {self.active_style.get("name_en", "")} ({self.active_style.get("name", "")})
Color mood: {self.active_style.get("color_mood", "")}
Hero photo direction: {self.active_style.get("hero_suffix", "")[:200]}
Item photo direction: {self.active_style.get("item_suffix", "")[:200]}
ALL image prompts MUST follow this style direction. Do NOT deviate."""

        system_prompt = f"""You are a professional Art Director creating image generation prompts for a print poster.

## OUTPUT FORMAT (JSON)
Return a JSON object with this EXACT structure:
{{
  "style": {{
    "brand_color": "#hex (업종 이미지에 맞는 메인 색상)",
    "accent_color": "#hex (강조 색상, 가격/CTA용)",
    "bg_color": "#hex (배경, 밝고 따뜻한 톤)",
    "text_color": "#hex (본문 텍스트 색상)"
  }},
  "hero": {{
    "prompt": "영문 이미지 생성 프롬프트 (food/product photography, MUST include 'no text, no labels, no borders')",
    "aspect_ratio": "16:9"
  }},
  "items": [
    {{"name": "품목명", "prompt": "영문 이미지 프롬프트", "aspect_ratio": "1:1"}},
    ...
  ],
  "event": {{
    "prompt": "영문 이벤트/프로모션 배너 이미지 프롬프트 (optional, only if promo exists)",
    "aspect_ratio": "16:9"
  }}
}}

## RULES
1. hero.prompt는 포스터 배경용 사진 — 텍스트 오버레이 될 예정이므로 사진에 텍스트 절대 금지
2. hero.prompt MUST end with: 'no text, no labels, no borders, no frames, no logos'
3. hero.aspect_ratio는 반드시 Gemini 지원 비율: 1:1, 2:3, 3:2, 3:4, 4:3, 4:5, 5:4, 9:16, 16:9, 21:9
4. items는 copy.json의 items와 1:1 대응 (같은 수, 같은 순서)
5. 각 item prompt는 해당 품목의 식재료/상품을 매력적으로 촬영한 푸드/상품 사진
6. style 색상은 업종 이미지에 맞게 선택 (음식점=따뜻/빨강, 카페=브라운/베이지, 학원=파랑/노랑 등)
7. prompt는 전부 영문으로 작성 (Gemini 이미지 생성용)
8. If the business has a promo/event, generate an event image prompt. The event image should be an eye-catching promotional banner photo. End with 'no text, no labels, no borders'{palette_info}{style_direction}"""

        items = copy_data.get("items", [])
        items_str = "\n".join(
            [
                f"  {i + 1}. {it.get('name', '?')}: {it.get('description', '')}"
                for i, it in enumerate(items)
            ]
        )

        user_prompt = f"""Create design.json for this poster:

## BUSINESS
- 상호: {brief.get("business_name", "")}
- 업종: {brief.get("industry", "")}
- 톤: {brief.get("tone", "따뜻하고 전문적인")}

## COPY CONTENT
- 헤드라인: {copy_data.get("hero", {}).get("headline", "")}
- 서브: {copy_data.get("subtitle", "")}
- 품목 ({len(items)}개):
{items_str}

Produce the hero prompt and {len(items)} item prompts. Remember: hero is a BACKGROUND photo, items are individual product close-ups."""

        try:
            result = self.client.generate_text_json(user_prompt, system_prompt)
        except Exception as e:
            logger.error(f"❌ 디자인 생성 실패: {e}")
            return False

        # Validate
        if not result.get("style") or not result.get("hero"):
            logger.error("❌ design.json에 필수 필드(style, hero) 누락")
            return False

        self._save("design.json", result)
        logger.info(
            f"✅ 디자인 생성 완료 — hero + {len(result.get('items', []))}개 품목 프롬프트"
        )
        return True

    def auto_all(self) -> bool:
        """brief.json만으로 전체 파이프라인 자동 실행.
        brief → copy → design → art_direct → images → SVG"""
        logger.info("\n" + "═" * 55)
        logger.info("🚀 전자동 파이프라인 시작")
        logger.info("═" * 55)

        # Phase 1: Copy
        if not (self.workdir / "copy.json").exists():
            if not self.auto_copy():
                return False
        else:
            logger.info("ℹ️  copy.json 이미 존재 — 생략")

        # Phase 2: Design
        if not (self.workdir / "design.json").exists():
            if not self.auto_design():
                return False
        else:
            logger.info("ℹ️  design.json 이미 존재 — 생략")

        # Phase 3: Art Direct
        if not self.art_direct():
            return False

        # Phase 4-6: Images + SVG
        return self.generate_all()

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
            layout_options.append(
                f"  - {lid}: {ldata.get('name', lid)} — zones: {json.dumps(zones_pct)} — best_for: {best_for}"
            )
        layouts_str = (
            "\n".join(layout_options)
            if layout_options
            else "  (layout_patterns not loaded)"
        )

        # Extract scale options from typo_scale
        scales = typo_scale.get("scales", {})
        scales_str = "\n".join(
            [
                f"  - {name}: ratio {s.get('ratio')}, feel: {s.get('feel')}, best_for: {s.get('best_for')}"
                for name, s in scales.items()
                if isinstance(s, dict) and "ratio" in s
            ]
        )

        # Extract schema (only the schema part, not the examples)
        schema_def = json.dumps(
            schema.get("schema", schema), ensure_ascii=False, indent=2
        )

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
{json.dumps(typo_scale.get("layout_overrides", {}), ensure_ascii=False, indent=2)}

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

        items_summary = "\n".join(
            [
                f"  {i + 1}. {item.get('name', '?')} — {item.get('description', '')} [{item.get('price', '')}]"
                for i, item in enumerate(items)
            ]
        )

        return f"""Create layout_spec.json for this poster:

## BUSINESS
- Name: {biz_name}
- Industry: {industry}
- Category: {category}
- Features: {", ".join(features) if features else "N/A"}

## COPY CONTENT
- Hero headline: {hero_copy.get("headline", "")}
- Hero badge: {hero_copy.get("badge", "")}
- Subtitle: {subtitle}
- Items ({n_items}):
{items_summary}
- Promo: {promo.get("title", "")} — {promo.get("detail", "")}
- CTA headline: {cta.get("headline", "")}
- CTA phone: {cta.get("phone", "")}
- CTA address: {cta.get("address", "")}
- CTA hours: {cta.get("hours", "")}

## DECISION FACTORS
- Item count: {n_items} (affects grid layout choice)
- Has promo: {"Yes" if promo else "No"}
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
            "block_grid": self._build_block_grid,
        }
        builder = dispatch.get(layout_id, self._build_classic_grid)
        logger.info(f"  📐 레이아웃: {layout_id}")
        return builder(style, copy_data)

    def _select_layout(self, design: dict, copy_data: dict) -> str:
        import random

        # 1. CLI override
        if self.cli_layout:
            return self.cli_layout
        # 2. layout_spec.json (Art Director decision)
        if self.layout_spec and self.layout_spec.get("layout_id"):
            return self.layout_spec["layout_id"]
        # 3. Explicit in design.json
        if design.get("layout"):
            return design["layout"]
        # 4. Auto from layout_patterns.json (weighted random from top candidates)
        brief = self._load_safe("brief.json")
        industry = brief.get("industry", "").lower() if brief else ""
        n_items = len(copy_data.get("items", []))
        rules = self.layout_patterns.get("layout_selection_rules", {})
        by_industry = rules.get("by_industry", {})
        for key, layouts in by_industry.items():
            if key == "default":
                continue
            if key.lower() in industry or industry in key.lower():
                if len(layouts) >= 2:
                    pick = random.choices(
                        layouts[:3], weights=[50, 30, 20][: len(layouts[:3])]
                    )[0]
                    logger.info(
                        f"  🎲 레이아웃 랜덤 선택: {pick} (후보: {layouts[:3]})"
                    )
                    return pick
                return layouts[0]
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

    # Valid Gemini aspect ratios
    VALID_ASPECT_RATIOS = {
        "1:1",
        "1:4",
        "1:8",
        "2:3",
        "3:2",
        "3:4",
        "4:1",
        "4:3",
        "4:5",
        "5:4",
        "8:1",
        "9:16",
        "16:9",
        "21:9",
    }

    def _get_hero_aspect_ratio(self, design: dict) -> str:
        """layout_spec에서 히어로 aspect_ratio를 읽거나 design.json에서 폴백.
        Gemini에서 지원하지 않는 비율이면 가장 가까운 유효 비율로 변환."""
        raw_ratio = None
        if self.layout_spec:
            directives = self.layout_spec.get("image_directives", {})
            hero_dir = directives.get("hero", {})
            if hero_dir.get("aspect_ratio"):
                raw_ratio = hero_dir["aspect_ratio"]
        if not raw_ratio:
            raw_ratio = design.get("hero", {}).get("aspect_ratio", "16:9")
        # Validate against Gemini's allowed ratios
        if raw_ratio in self.VALID_ASPECT_RATIOS:
            return raw_ratio
        # Convert to float and find nearest valid ratio
        try:
            parts = raw_ratio.split(":")
            target = float(parts[0]) / float(parts[1])
        except (ValueError, IndexError, ZeroDivisionError):
            return "16:9"
        best = "16:9"
        best_diff = float("inf")
        for valid in self.VALID_ASPECT_RATIOS:
            vp = valid.split(":")
            vr = float(vp[0]) / float(vp[1])
            diff = abs(vr - target)
            if diff < best_diff:
                best_diff = diff
                best = valid
        logger.info(f"  ⚠️ 비율 변환: {raw_ratio} → {best} (Gemini 호환)")
        return best

    def _enrich_hero_prompt(self, hero_cfg: dict) -> str:
        """layout_spec.image_directives.hero 제약조건을 hero prompt에 자동 주입.

        design.json의 원본 prompt는 수정하지 않고, 메모리에서만 enrichment.
        이미 [LAYOUT CONSTRAINTS] 태그가 있으면 중복 주입 방지.
        """
        prompt = hero_cfg.get("prompt", "")
        if not self.layout_spec:
            return prompt

        directives = self.layout_spec.get("image_directives", {}).get("hero", {})
        if not directives:
            return prompt

        # 중복 방지
        if "[LAYOUT CONSTRAINTS]" in prompt:
            return prompt

        parts = []

        # fill_mode
        fill_mode = directives.get("fill_mode")
        if fill_mode == "full_bleed":
            parts.append(
                "Fill the ENTIRE frame edge to edge — no white space, no margins, no borders."
            )

        # content_focus → COMPOSITION
        content_focus = directives.get("content_focus")
        if content_focus:
            parts.append(f"COMPOSITION: {content_focus}")

        # tone_constraint → TONE
        tone = directives.get("tone_constraint")
        if tone:
            parts.append(f"TONE: {tone}")

        # text_safe_areas
        safe_areas = directives.get("text_safe_areas", [])
        if safe_areas:
            areas_desc = [
                f"{a.get('position', '')} ({a.get('purpose', '')})" for a in safe_areas
            ]
            parts.append(
                f"TEXT SAFE AREAS (keep visually clean/dark for text overlay): {', '.join(areas_desc)}"
            )

        # forbidden
        forbidden = directives.get("forbidden", [])
        if forbidden:
            parts.append(f"FORBIDDEN: absolutely no {', '.join(forbidden)}")

        if parts:
            prompt = prompt.rstrip() + " [LAYOUT CONSTRAINTS] " + " ".join(parts)
            logger.info(
                f"  📐 히어로 프롬프트 enrichment 적용 ({len(parts)}개 제약조건)"
            )

        return prompt

    def _enrich_item_prompt(self, item_cfg: dict, idx: int) -> str:
        """layout_spec.image_directives.items의 style_constraint를 item prompt에 자동 주입.

        idx: 0-based index into items array.
        """
        prompt = item_cfg.get("prompt", "")
        if not self.layout_spec:
            return prompt

        items_directives = self.layout_spec.get("image_directives", {}).get("items", [])
        if idx >= len(items_directives):
            return prompt

        # 중복 방지
        if "[STYLE CONSTRAINT]" in prompt:
            return prompt

        style = items_directives[idx].get("style_constraint", "")
        if style:
            prompt = (
                prompt.rstrip()
                + f" [STYLE CONSTRAINT] {style}. No text, no labels, no overlays."
            )
            logger.info(f"  📐 품목 {idx + 1} 프롬프트 enrichment 적용")

        return prompt

    # Minimum font sizes for 2130×3000 canvas (prevents microscopic text from layout_spec)
    _TYPO_MINIMUMS = {
        "business_name": 80,
        "hero_headline": 80,
        "hero_badge": 24,
        "hero_subtext": 28,
        "slogan": 36,
        "subtitle": 36,
        "features": 24,
        "features_text": 24,
        "item_name": 32,
        "item_description": 20,
        "item_sub_description": 18,
        "item_price": 28,
        "section_title": 36,
        "section_item": 26,
        "promo_title": 36,
        "promo_headline": 36,
        "promo_detail": 26,
        "promo_body": 26,
        "cta_headline": 36,
        "cta_phone": 48,
        "cta_address": 24,
        "cta_hours": 24,
        "cta_sns": 22,
    }

    def _get_typo(self, key: str, defaults: dict) -> dict:
        """layout_spec.json에서 typography assignment를 읽거나 기본값으로 폴백.

        Args:
            key: assignment key (예: 'business_name', 'slogan', 'item_name')
            defaults: 폴백 기본값 dict (예: {'size_px': 52, 'weight': '700', 'color': '#333'})

        Returns:
            dict with size_px, weight, color, alignment
        """
        result = defaults
        if self.layout_spec:
            assignments = self.layout_spec.get("typography", {}).get("assignments", {})
            if key in assignments:
                a = assignments[key]
                result = {
                    "size_px": a.get("size_px", defaults.get("size_px", 32)),
                    "weight": a.get("weight", defaults.get("weight", "400")),
                    "color": a.get("color", defaults.get("color", "#333333")),
                    "alignment": a.get(
                        "alignment", defaults.get("alignment", "center")
                    ),
                }
        # Enforce minimum sizes for readability on 2130×3000 canvas
        min_size = self._TYPO_MINIMUMS.get(key, 16)
        if result.get("size_px", 32) < min_size:
            result["size_px"] = min_size
        return result

    def _get_transitions(self) -> dict:
        """layout_spec.json에서 zone_transitions를 읽어 {(from, to): type} dict 반환."""
        result = {}
        if self.layout_spec:
            transitions = self.layout_spec.get("color_plan", {}).get(
                "zone_transitions", []
            )
            for t in transitions:
                key = (t.get("from_zone", ""), t.get("to_zone", ""))
                result[key] = t.get("transition", "hard_cut")
        return result

    def _svg_transition(
        self,
        from_id: str,
        to_id: str,
        y_boundary: int,
        accent: str = "#FF6B35",
        margin: int = 65,
    ) -> str:
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
                to_bg = to_zone.get(
                    "bg_color",
                    self.layout_spec.get("color_plan", {}).get("bg_color", "#FAFAF8"),
                )

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
        """XML 선언 + SVG 열기 + 전문 defs(필터/그라디언트/스타일) + 배경"""
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
      .heavy {{ font-family: {FONT_FAMILY}; font-weight: 900; }}
    </style>

    <!-- ── Card drop shadow ── -->
    <filter id="card-shadow" x="-5%" y="-3%" width="110%" height="115%">
      <feDropShadow dx="0" dy="6" stdDeviation="12" flood-color="#000000" flood-opacity="0.12"/>
    </filter>

    <!-- ── Elevated element shadow (stronger) ── -->
    <filter id="elevated-shadow" x="-5%" y="-5%" width="110%" height="120%">
      <feDropShadow dx="0" dy="10" stdDeviation="20" flood-color="#000000" flood-opacity="0.18"/>
    </filter>

    <!-- ── Text shadow for readability on images ── -->
    <filter id="text-shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="3" stdDeviation="6" flood-color="#000000" flood-opacity="0.6"/>
    </filter>

    <!-- ── Light text glow (softer, for subtitles on images) ── -->
    <filter id="text-glow" x="-15%" y="-15%" width="130%" height="130%">
      <feGaussianBlur in="SourceAlpha" stdDeviation="4" result="blur"/>
      <feFlood flood-color="#000000" flood-opacity="0.35" result="color"/>
      <feComposite in="color" in2="blur" operator="in" result="shadow"/>
      <feMerge>
        <feMergeNode in="shadow"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>

    <!-- ── Hero gradient overlay (bottom fade to dark) ── -->
    <linearGradient id="hero-fade" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#000000" stop-opacity="0"/>
      <stop offset="45%" stop-color="#000000" stop-opacity="0"/>
      <stop offset="80%" stop-color="#000000" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0.75"/>
    </linearGradient>

    <!-- ── Hero top vignette (subtle for badge readability) ── -->
    <linearGradient id="hero-top-fade" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.35"/>
      <stop offset="25%" stop-color="#000000" stop-opacity="0.1"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0"/>
    </linearGradient>

    <!-- ── Full hero scrim (combined top + bottom fade for text readability) ── -->
    <linearGradient id="hero-scrim" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#000000" stop-opacity="0.15"/>
      <stop offset="35%" stop-color="#000000" stop-opacity="0.05"/>
      <stop offset="65%" stop-color="#000000" stop-opacity="0.15"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0.65"/>
    </linearGradient>

    <!-- ── Promo banner subtle depth gradient ── -->
    <linearGradient id="promo-depth" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.1"/>
      <stop offset="100%" stop-color="#000000" stop-opacity="0.15"/>
    </linearGradient>
  </defs>

  <!-- ===== BACKGROUND ===== -->
  <rect width="{CANVAS_W}" height="{CANVAS_H}" fill="{bg}"/>"""

    def _svg_cta(self, cta: dict, style: dict, y: int, h: int) -> str:
        if not cta:
            return ""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        hl = cta.get("headline", "")
        phone = cta.get("phone", "")
        addr = cta.get("address", "")
        hours = cta.get("hours", "")
        sns = cta.get("sns", "")
        cx = CANVAS_W // 2

        t_hl = self._get_typo(
            "cta_headline", {"size_px": 48, "weight": "700", "color": accent}
        )
        t_ph = self._get_typo(
            "cta_phone", {"size_px": 64, "weight": "900", "color": "#FFFFFF"}
        )
        t_addr = self._get_typo(
            "cta_address", {"size_px": 28, "weight": "400", "color": "#FFFFFF"}
        )
        t_sns = self._get_typo(
            "cta_sns", {"size_px": 26, "weight": "400", "color": accent}
        )

        # Vertical rhythm with proportional spacing
        slots = []
        if hl:
            slots.append(("hl", t_hl))
        if phone:
            slots.append(("phone", t_ph))
        if addr:
            slots.append(("addr", t_addr))
        if hours:
            slots.append(("hours", t_addr))
        if sns:
            slots.append(("sns", t_sns))
        n_slots = max(len(slots), 1)
        pad_top = max(40, int(h * 0.08))
        pad_bot = max(30, int(h * 0.06))
        usable = h - pad_top - pad_bot
        spacing = usable // (n_slots + 1)

        result = f"""
  <!-- ===== CTA FOOTER ===== -->
  <rect x="0" y="{y}" width="{CANVAS_W}" height="{h}" fill="{brand}"/>
  <rect x="0" y="{y}" width="{CANVAS_W}" height="3" fill="{accent}" opacity="0.8"/>"""

        cur_y = y + pad_top + spacing
        for slot_id, t in slots:
            if slot_id == "hl":
                result += f"""
  <text x="{cx}" y="{cur_y}"
        text-anchor="middle" class="title" font-size="{t["size_px"]}" font-weight="{t["weight"]}" fill="{t["color"]}"
        letter-spacing="2">{hl}</text>"""
            elif slot_id == "phone":
                result += f"""
  <text x="{cx}" y="{cur_y}"
        text-anchor="middle" class="heavy" font-size="{t["size_px"]}" font-weight="{t["weight"]}" fill="{t["color"]}"
        letter-spacing="3">{phone}</text>"""
            elif slot_id == "addr":
                if phone and addr:
                    sep_y = cur_y - spacing // 2
                    result += f"""
  <line x1="{cx - 120}" y1="{sep_y}" x2="{cx + 120}" y2="{sep_y}"
        stroke="{accent}" stroke-width="1.5" opacity="0.35"/>"""
                result += f"""
  <text x="{cx}" y="{cur_y}"
        text-anchor="middle" class="body" font-size="{t["size_px"]}" font-weight="{t["weight"]}" fill="{t["color"]}" opacity="0.8">{addr}</text>"""
            elif slot_id == "hours":
                result += f"""
  <text x="{cx}" y="{cur_y}"
        text-anchor="middle" class="body" font-size="{t["size_px"]}" font-weight="{t["weight"]}" fill="{t["color"]}" opacity="0.8">{hours}</text>"""
            elif slot_id == "sns":
                result += f"""
  <text x="{cx}" y="{cur_y}"
        text-anchor="middle" class="body" font-size="{t["size_px"]}" font-weight="{t["weight"]}" fill="{t["color"]}" opacity="0.9">{sns}</text>"""
            cur_y += spacing
        return result

    def _svg_promo(
        self, promo: dict, style: dict, y: int, h: int, margin: int = 65
    ) -> str:
        if not promo:
            return ""
        accent = style.get("accent_color", "#FF6B35")
        ptitle = promo.get("title", "")
        pdetail = promo.get("detail", "")
        cx = CANVAS_W // 2

        t_pt = self._get_typo(
            "promo_title", {"size_px": 42, "weight": "700", "color": "#FFFFFF"}
        )
        t_pd = self._get_typo(
            "promo_detail", {"size_px": 30, "weight": "500", "color": "#FFFFFF"}
        )

        title_y = y + int(h * 0.42)
        detail_y = y + int(h * 0.76)

        result = f"""
  <!-- ===== PROMO BANNER ===== -->
  <rect x="0" y="{y}" width="{CANVAS_W}" height="{h}" fill="{accent}"/>
  <rect x="0" y="{y}" width="{CANVAS_W}" height="{h}" fill="url(#promo-depth)" opacity="0.4"/>"""
        if ptitle:
            result += f"""
  <text x="{cx}" y="{title_y}"
        text-anchor="middle" class="title" font-size="{t_pt["size_px"]}" font-weight="{t_pt["weight"]}" fill="{t_pt["color"]}"
        letter-spacing="4">\u2605  {ptitle}  \u2605</text>"""
        if pdetail:
            result += f"""
  <text x="{cx}" y="{detail_y}"
        text-anchor="middle" class="body" font-size="{t_pd["size_px"]}" font-weight="{t_pd["weight"]}" fill="{t_pd["color"]}"
        opacity="0.95">{pdetail}</text>"""
        return result

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

    # ─── Layout 6: Block Grid (퍼즐/레고 조합) ───

    def _grid_to_px(self, block: dict, row_heights: list) -> tuple:
        """그리드 좌표를 픽셀 (x, y, w, h)로 변환.

        Config values:
        - margin=30, gap=12, cols=4
        - col_w = (CANVAS_W - 2*margin - (cols-1)*gap) / cols

        Returns: (x, y, w, h) in pixels
        """
        margin = 30
        gap = 12
        num_cols = 4
        col_w = (CANVAS_W - 2 * margin - (num_cols - 1) * gap) / num_cols

        col = block["col"]
        row = block["row"]
        colspan = block["colspan"]
        rowspan = block.get("rowspan", 1)

        x = margin + col * (col_w + gap)
        y = margin + sum(row_heights[:row]) + row * gap
        w = colspan * col_w + (colspan - 1) * gap
        h = sum(row_heights[row : row + rowspan]) + (rowspan - 1) * gap

        return (int(x), int(y), int(w), int(h))

    def _render_hero_block(
        self, x: int, y: int, w: int, h: int, style: dict, copy_data: dict
    ) -> str:
        """블록 그리드용 히어로 블록 렌더링 — 이미지 + 그라데이션 + 텍스트"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        hero_copy = copy_data.get("hero", {})
        headline = hero_copy.get("headline", "")
        badge = hero_copy.get("badge", "")
        subtext = hero_copy.get("subtext", copy_data.get("subtitle", ""))

        # Clip path for rounded corners
        clip_id = f"hero-clip-{x}-{y}"
        hero_ref = self._img_ref("hero.png")

        parts = [
            f"""
  <!-- ===== BLOCK: HERO ===== -->
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>
    </clipPath>
    <linearGradient id="hero-grad-{x}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="70%" stop-color="#000" stop-opacity="0"/>
      <stop offset="100%" stop-color="#000" stop-opacity="0.65"/>
    </linearGradient>
  </defs>
  <g clip-path="url(#{clip_id})">
"""
        ]

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="{x}" y="{y}" width="{w}" height="{h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{brand}" opacity="0.15"/>"""
            )

        # Dark gradient overlay for text readability
        parts.append(f"""    <rect x="{x}" y="{y}" width="{w}" height="{h}"
          fill="url(#hero-grad-{x})"/>""")

        # Text overlay
        cx = x + w // 2
        if badge:
            badge_y = y + int(h * 0.35)
            parts.append(f"""    <text x="{cx}" y="{badge_y}"
          text-anchor="middle" class="body" font-size="26" font-weight="500"
          fill="{accent}" letter-spacing="4" opacity="0.9">{badge}</text>""")

        hl_y = y + int(h * 0.50)
        parts.append(f"""    <text x="{cx}" y="{hl_y}"
          text-anchor="middle" class="title" font-size="68" font-weight="700"
          fill="#FFFFFF" letter-spacing="2">{headline}</text>""")

        if subtext:
            sub_y = y + int(h * 0.60)
            parts.append(f"""    <text x="{cx}" y="{sub_y}"
          text-anchor="middle" class="body" font-size="30" font-weight="400"
          fill="#FFFFFF" opacity="0.85">{subtext}</text>""")

        parts.append("  </g>")
        return "\n".join(parts)

    def _render_item_block(
        self, x: int, y: int, w: int, h: int, style: dict, item: dict, idx: int
    ) -> str:
        """블록 그리드용 품목 블록 렌더링 — 이미지 상단 60% + 텍스트 하단 40%"""
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        text_c = style.get("text_color", "#333333")
        bg = style.get("bg_color", "#FAFAF8")

        name = item.get("name", "")
        desc = item.get("description", "")
        price = item.get("price", "")

        img_h = int(h * 0.60)
        text_h = h - img_h
        text_y = y + img_h
        cx = x + w // 2

        clip_id = f"item-clip-{x}-{y}"
        item_ref = self._img_ref(f"item_{idx + 1:02d}.png")

        # Typography
        t_name = self._get_typo(
            "item_name", {"size_px": 34, "weight": "700", "color": text_c}
        )
        t_desc = self._get_typo(
            "item_description", {"size_px": 24, "weight": "400", "color": text_c}
        )
        t_price = self._get_typo(
            "item_price", {"size_px": 30, "weight": "700", "color": accent}
        )

        # Scale font sizes for narrow blocks (1-col ~509px)
        scale = min(1.0, w / 800)
        name_size = max(22, int(t_name["size_px"] * scale))
        desc_size = max(16, int(t_desc["size_px"] * scale))
        price_size = max(20, int(t_price["size_px"] * scale))

        parts = [
            f"""
  <!-- ===== BLOCK: ITEM {idx + 1} ({name}) ===== -->
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{x}" y="{y}" width="{w}" height="{img_h}" rx="8"/>
    </clipPath>
  </defs>
  <g>
    <!-- Border/card effect -->
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8"
          fill="{bg}" stroke="{brand}" stroke-width="1" stroke-opacity="0.1"/>"""
        ]

        # Image area (top 60%)
        if item_ref:
            parts.append(f"""    <g clip-path="url(#{clip_id})">
      <image href="{item_ref}" xlink:href="{item_ref}"
             x="{x}" y="{y}" width="{w}" height="{img_h}"
             preserveAspectRatio="xMidYMid slice"/>
    </g>""")
        else:
            parts.append(f"""    <rect x="{x}" y="{y}" width="{w}" height="{img_h}" rx="8"
          fill="{brand}" opacity="0.06"/>
    <text x="{cx}" y="{y + img_h // 2 + 8}"
          text-anchor="middle" class="body" font-size="28" fill="{brand}" opacity="0.2">{name}</text>""")

        # Text area (bottom 40%)
        name_y = text_y + int(text_h * 0.30)
        desc_y = text_y + int(text_h * 0.55)
        price_y = text_y + int(text_h * 0.82)

        parts.append(f"""    <text x="{cx}" y="{name_y}"
          text-anchor="middle" class="title" font-size="{name_size}" font-weight="{t_name["weight"]}" fill="{t_name["color"]}">
      {name}
    </text>""")

        if desc:
            parts.append(f"""    <text x="{cx}" y="{desc_y}"
          text-anchor="middle" class="body" font-size="{desc_size}" font-weight="{t_desc["weight"]}" fill="{t_desc["color"]}" opacity="0.65">
      {desc}
    </text>""")

        if price:
            parts.append(f"""    <text x="{cx}" y="{price_y}"
          text-anchor="middle" class="title" font-size="{price_size}" font-weight="{t_price["weight"]}" fill="{t_price["color"]}">
      {price}
    </text>""")

        parts.append("  </g>")
        return "\n".join(parts)

    def _render_event_block(
        self, x: int, y: int, w: int, h: int, style: dict, promo: dict
    ) -> str:
        """블록 그리드용 이벤트/프로모션 블록 렌더링 — 이미지 좌측 50% + 텍스트 우측 50%"""
        accent = style.get("accent_color", "#FF6B35")
        ptitle = promo.get("title", "")
        pdetail = promo.get("detail", "")

        if not promo:
            return ""

        event_ref = self._img_ref("event.png")
        clip_id = f"event-clip-{x}-{y}"

        parts = [
            f"""
  <!-- ===== BLOCK: EVENT/PROMO ===== -->
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>
    </clipPath>
  </defs>"""
        ]

        if event_ref:
            # Image fills left 50%, accent bg fills right 50%
            img_w = w // 2
            text_x = x + img_w
            text_w = w - img_w
            text_cx = text_x + text_w // 2

            img_clip_id = f"event-img-clip-{x}-{y}"
            parts.append(f"""  <defs>
    <clipPath id="{img_clip_id}">
      <rect x="{x}" y="{y}" width="{img_w}" height="{h}" rx="8"/>
    </clipPath>
  </defs>
  <g clip-path="url(#{clip_id})">
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8" fill="{accent}"/>
    <g clip-path="url(#{img_clip_id})">
      <image href="{event_ref}" xlink:href="{event_ref}"
             x="{x}" y="{y}" width="{img_w}" height="{h}"
             preserveAspectRatio="xMidYMid slice"/>
    </g>
    <text x="{text_cx}" y="{y + int(h * 0.40)}"
          text-anchor="middle" class="title" font-size="34" font-weight="700"
          fill="#FFFFFF" letter-spacing="3">\u2605  {ptitle}  \u2605</text>
    <text x="{text_cx}" y="{y + int(h * 0.65)}"
          text-anchor="middle" class="title" font-size="28" font-weight="700"
          fill="#FFFFFF">{pdetail}</text>
  </g>""")
        else:
            # No event image: full accent background with centered text
            cx = x + w // 2
            parts.append(f"""  <g clip-path="url(#{clip_id})">
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8" fill="{accent}"/>
    <text x="{cx}" y="{y + int(h * 0.40)}"
          text-anchor="middle" class="title" font-size="34" font-weight="700"
          fill="#FFFFFF" letter-spacing="3">\u2605  {ptitle}  \u2605</text>
    <text x="{cx}" y="{y + int(h * 0.65)}"
          text-anchor="middle" class="title" font-size="28" font-weight="700"
          fill="#FFFFFF">{pdetail}</text>
  </g>""")

        return "\n".join(parts)

    def _render_cta_block(
        self, x: int, y: int, w: int, h: int, style: dict, cta: dict
    ) -> str:
        """블록 그리드용 CTA 블록 렌더링 — 좁은 블록에 맞춤 (CANVAS_W 대신 블록 너비 사용)"""
        if not cta:
            return ""

        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        hl = cta.get("headline", "")
        phone = cta.get("phone", "")
        addr = cta.get("address", "")
        hours = cta.get("hours", "")

        cx = x + w // 2
        clip_id = f"cta-clip-{x}-{y}"

        # Scale font sizes for narrow block (~509px for 1-col)
        scale = min(1.0, w / 800)
        hl_size = max(24, int(48 * scale))
        ph_size = max(22, int(40 * scale))
        addr_size = max(16, int(26 * scale))

        # Proportional vertical offsets
        hl_y = y + int(h * 0.22)
        ph_y = y + int(h * 0.42)
        ad_y = y + int(h * 0.60)
        hr_y = y + int(h * 0.78)

        return f"""
  <!-- ===== BLOCK: CTA ===== -->
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>
    </clipPath>
  </defs>
  <g clip-path="url(#{clip_id})">
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8" fill="{brand}"/>
    <line x1="{cx - min(60, w // 4)}" y1="{y + 12}" x2="{cx + min(60, w // 4)}" y2="{y + 12}"
          stroke="{accent}" stroke-width="2" opacity="0.5"/>
    <text x="{cx}" y="{hl_y}"
          text-anchor="middle" class="title" font-size="{hl_size}" font-weight="700" fill="{accent}">{hl}</text>
    <text x="{cx}" y="{ph_y}"
          text-anchor="middle" class="title" font-size="{ph_size}" font-weight="700" fill="#FFFFFF">{phone}</text>
    <text x="{cx}" y="{ad_y}"
          text-anchor="middle" class="body" font-size="{addr_size}" font-weight="400" fill="#FFFFFF" opacity="0.85">{addr}</text>
    <text x="{cx}" y="{hr_y}"
          text-anchor="middle" class="body" font-size="{addr_size}" font-weight="400" fill="#FFFFFF" opacity="0.85">{hours}</text>
  </g>"""

    def _build_block_grid(self, style: dict, copy_data: dict) -> str:
        """블록 그리드 — 퍼즐/레고 조합 레이아웃 (6번째 레이아웃)"""
        # 1. Load grid patterns config
        config_dir = Path(__file__).parent.parent / "config"
        patterns = self._load_config(config_dir / "block_grid_patterns.json")

        # 2. Get item count, clamp to 1-8
        items = copy_data.get("items", [])
        n_items = max(1, min(8, len(items)))

        # 3. Look up pattern
        pattern = patterns.get("patterns", {}).get(str(n_items))
        if not pattern:
            logger.warning(
                f"  ⚠️ block_grid: {n_items}개 품목 패턴 없음 → classic_grid 폴백"
            )
            return self._build_classic_grid(style, copy_data)

        row_heights = pattern["row_heights"]
        blocks = pattern["blocks"]

        # 4. Extract style/copy
        bg = style.get("bg_color", "#FAFAF8")
        promo = copy_data.get("promo", {})
        cta = copy_data.get("cta", {})

        # 5. Build SVG
        parts = [self._svg_header(bg)]

        for block in blocks:
            x, y, w, h = self._grid_to_px(block, row_heights)
            btype = block["type"]

            if btype == "hero":
                parts.append(self._render_hero_block(x, y, w, h, style, copy_data))
            elif btype == "item":
                idx = block["item_idx"]
                if idx < len(items):
                    parts.append(
                        self._render_item_block(x, y, w, h, style, items[idx], idx)
                    )
            elif btype == "event":
                parts.append(self._render_event_block(x, y, w, h, style, promo))
            elif btype == "cta":
                parts.append(self._render_cta_block(x, y, w, h, style, cta))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 1: Classic Grid (현재 V2 — 동작 변경 없음) ───

    def _build_classic_grid(self, style: dict, copy_data: dict) -> str:
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        features = copy_data.get("features", [])
        cta = copy_data.get("cta", {})
        subtitle = copy_data.get("subtitle", "")
        promo = copy_data.get("promo", {})
        hero_data = copy_data.get("hero", {})
        headline = hero_data.get("headline", "")
        badge = hero_data.get("badge", "")
        subtext = hero_data.get("subtext", "")
        n_items = len(items)

        M = 100
        CARD_R = 16
        CARD_GAP = 30
        CARD_PAD = 16

        hero_zone = self._get_zone("hero", {"y_px": 0, "h_px": 1350})
        hero_top = hero_zone["y_px"]
        hero_h = hero_zone["h_px"]

        has_tagline = bool(subtitle or features)
        tagline_h_val = 120 if has_tagline else 0
        tagline_zone = self._get_zone(
            "slogan", {"y_px": hero_top + hero_h, "h_px": tagline_h_val}
        )
        tagline_top = tagline_zone["y_px"]
        tagline_h = tagline_zone["h_px"] if has_tagline else 0

        feat_below_tagline = 0
        if subtitle and features:
            feat_below_tagline = 70

        promo_h = 130 if promo else 0
        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 380, "h_px": 380})
        cta_h = cta_zone["h_px"]

        items_start = tagline_top + tagline_h + feat_below_tagline + 40
        items_zone = self._get_zone("items", {"y_px": items_start, "h_px": 0})
        items_top = items_zone["y_px"]
        items_avail = CANVAS_H - items_top - promo_h - cta_h - 60

        if n_items <= 2:
            cols = 2
        elif n_items <= 3:
            cols = 3
        elif n_items <= 4:
            cols = min(n_items, 4)
        elif n_items <= 6:
            cols = 3
        else:
            cols = 4
        rows = (n_items + cols - 1) // cols

        col_w = (CANVAS_W - 2 * M - (cols - 1) * CARD_GAP) // cols
        card_h = min(int(items_avail / rows) - CARD_GAP, int(col_w * 1.35))
        img_h = int(card_h * 0.62)

        t_headline = self._get_typo(
            "business_name", {"size_px": 100, "weight": "900", "color": "#FFFFFF"}
        )
        t_badge = self._get_typo(
            "hero_badge", {"size_px": 30, "weight": "600", "color": "#FFFFFF"}
        )
        t_subtext = self._get_typo(
            "hero_subtext", {"size_px": 36, "weight": "400", "color": "#FFFFFF"}
        )
        t_slogan = self._get_typo(
            "slogan", {"size_px": 40, "weight": "700", "color": "#FFFFFF"}
        )
        t_features = self._get_typo(
            "features", {"size_px": 28, "weight": "500", "color": text_c}
        )
        t_item_name = self._get_typo(
            "item_name", {"size_px": 38, "weight": "700", "color": text_c}
        )
        t_item_desc = self._get_typo(
            "item_description", {"size_px": 24, "weight": "400", "color": text_c}
        )
        t_item_sub = self._get_typo(
            "item_sub_description", {"size_px": 22, "weight": "300", "color": text_c}
        )
        t_item_price = self._get_typo(
            "item_price", {"size_px": 36, "weight": "700", "color": accent}
        )

        parts = []
        parts.append(self._svg_header(bg))

        hero_ref = self._img_ref("hero.png")
        parts.append(f"""
  <!-- ===== HERO ===== -->
  <defs>
    <clipPath id="hero-main-clip">
      <rect x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}"/>
    </clipPath>
  </defs>
  <g clip-path="url(#hero-main-clip)">""")

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.15"/>"""
            )

        parts.append(
            f"""    <rect x="0" y="{hero_top}" width="{CANVAS_W}" height="{hero_h}" fill="url(#hero-scrim)"/>"""
        )

        hero_cx = CANVAS_W // 2
        if badge:
            badge_y = hero_top + int(hero_h * 0.15)
            parts.append(f"""    <text x="{hero_cx}" y="{badge_y}"
          text-anchor="middle" class="body" font-size="{t_badge["size_px"]}" font-weight="{t_badge["weight"]}"
          fill="{t_badge["color"]}" letter-spacing="6" opacity="0.9"
          filter="url(#text-glow)">{badge}</text>""")

        if headline:
            hl_y = hero_top + int(hero_h * 0.72)
            parts.append(f"""    <text x="{hero_cx}" y="{hl_y}"
          text-anchor="middle" class="heavy" font-size="{t_headline["size_px"]}" font-weight="{t_headline["weight"]}"
          fill="{t_headline["color"]}" letter-spacing="4"
          filter="url(#text-shadow)">{headline}</text>""")

        if subtext:
            sub_y = hero_top + int(hero_h * 0.72) + int(t_headline["size_px"] * 0.6)
            parts.append(f"""    <text x="{hero_cx}" y="{sub_y}"
          text-anchor="middle" class="body" font-size="{t_subtext["size_px"]}" font-weight="{t_subtext["weight"]}"
          fill="{t_subtext["color"]}" opacity="0.9"
          filter="url(#text-glow)">{subtext}</text>""")

        parts.append("  </g>")

        trans_svg = self._svg_transition("hero", "slogan", hero_top + hero_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        if has_tagline:
            band_text = subtitle if subtitle else "  \u00b7  ".join(features)
            parts.append(f"""
  <!-- ===== TAGLINE BAND ===== -->
  <rect x="0" y="{tagline_top}" width="{CANVAS_W}" height="{tagline_h}" fill="{brand}"/>
  <text x="{CANVAS_W // 2}" y="{tagline_top + tagline_h // 2 + int(t_slogan["size_px"] * 0.35)}"
        text-anchor="middle" class="title" font-size="{t_slogan["size_px"]}" font-weight="{t_slogan["weight"]}" fill="{t_slogan["color"]}"
        letter-spacing="3">{band_text}</text>""")

            if subtitle and features:
                feat_text = "  \u00b7  ".join(features)
                feat_y = tagline_top + tagline_h
                parts.append(f"""
  <rect x="0" y="{feat_y}" width="{CANVAS_W}" height="{feat_below_tagline}" fill="{bg}"/>
  <text x="{CANVAS_W // 2}" y="{feat_y + feat_below_tagline // 2 + 10}"
        text-anchor="middle" class="body" font-size="{t_features["size_px"]}" font-weight="{t_features["weight"]}" fill="{t_features["color"]}"
        opacity="0.7" letter-spacing="2">{feat_text}</text>""")

        if items:
            parts.append(f"\n  <!-- ===== ITEMS ({n_items}) ===== -->")
            for idx, item in enumerate(items):
                col = idx % cols
                row_idx = idx // cols
                card_x = M + col * (col_w + CARD_GAP)
                card_y = items_top + row_idx * (card_h + CARD_GAP)

                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                name = item.get("name", "")
                desc = item.get("description", "")
                sub_desc = item.get("sub_description", "")
                price = item.get("price", "")
                cx = card_x + col_w // 2
                clip_id = f"item-img-clip-{idx}"

                parts.append(f"""  <g id="item-card-{idx + 1}" filter="url(#card-shadow)">
    <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{card_h}"
          rx="{CARD_R}" fill="#FFFFFF"/>
    <defs>
      <clipPath id="{clip_id}">
        <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{img_h}" rx="{CARD_R}"/>
      </clipPath>
    </defs>""")

                if item_ref:
                    parts.append(f"""    <g clip-path="url(#{clip_id})">
      <image href="{item_ref}" xlink:href="{item_ref}"
             x="{card_x}" y="{card_y}" width="{col_w}" height="{img_h}"
             preserveAspectRatio="xMidYMid slice"/>
    </g>""")
                else:
                    parts.append(f"""    <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{img_h}"
          rx="{CARD_R}" fill="{brand}" opacity="0.06"/>
    <text x="{cx}" y="{card_y + img_h // 2 + 10}"
          text-anchor="middle" class="body" font-size="32" fill="{brand}" opacity="0.2">{name}</text>""")

                name_y = card_y + img_h + CARD_PAD + 32
                parts.append(f"""    <text x="{cx}" y="{name_y}"
          text-anchor="middle" class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">
      {name}
    </text>""")

                if desc:
                    desc_y = name_y + int(t_item_name["size_px"] * 1.1)
                    parts.append(f"""    <text x="{cx}" y="{desc_y}"
          text-anchor="middle" class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.6">
      {desc}
    </text>""")

                if sub_desc:
                    sub_y = (
                        name_y
                        + int(t_item_name["size_px"] * 1.1)
                        + int(t_item_desc["size_px"] * 1.3)
                    )
                    parts.append(f"""    <text x="{cx}" y="{sub_y}"
          text-anchor="middle" class="light" font-size="{t_item_sub["size_px"]}" font-weight="{t_item_sub["weight"]}" fill="{t_item_sub["color"]}" opacity="0.5">
      {sub_desc}
    </text>""")

                if price:
                    price_y = card_y + card_h - CARD_PAD - 8
                    parts.append(f"""    <text x="{cx}" y="{price_y}"
          text-anchor="middle" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">
      {price}
    </text>""")

                parts.append("  </g>")

        items_end = (
            items_top + rows * (card_h + CARD_GAP) if items else tagline_top + tagline_h
        )
        trans_svg = self._svg_transition("items", "promo", items_end, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        if promo:
            promo_y = items_end + 20
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, M))
            trans_svg = self._svg_transition(
                "promo", "cta", promo_y + promo_h, accent, M
            )
            if trans_svg:
                parts.append(trans_svg)

        cta_y = CANVAS_H - cta_h
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 2: Hero Dominant ───

    def _build_hero_dominant(self, style: dict, copy_data: dict) -> str:
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        cta = copy_data.get("cta", {})
        subtitle = copy_data.get("subtitle", "")
        promo = copy_data.get("promo", {})
        hero_data = copy_data.get("hero", {})
        headline = hero_data.get("headline", "")
        badge = hero_data.get("badge", "")
        subtext = hero_data.get("subtext", "")
        n_items = min(len(items), 4)

        M = 100
        CARD_R = 16
        CARD_GAP = 24

        hero_zone = self._get_zone("hero", {"y_px": 0, "h_px": 1860})
        hero_h = hero_zone["h_px"]

        slogan_zone = self._get_zone("slogan", {"y_px": hero_h, "h_px": 130})
        slogan_y = slogan_zone["y_px"]
        slogan_h = slogan_zone["h_px"]

        items_zone = self._get_zone("items", {"y_px": slogan_y + slogan_h, "h_px": 440})
        items_y = items_zone["y_px"]
        items_h = items_zone["h_px"]

        promo_h = 150 if promo else 0
        promo_y = items_y + items_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 380, "h_px": 380})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        t_headline = self._get_typo(
            "business_name", {"size_px": 110, "weight": "900", "color": "#FFFFFF"}
        )
        t_badge = self._get_typo(
            "hero_badge", {"size_px": 28, "weight": "600", "color": "#FFFFFF"}
        )
        t_subtext = self._get_typo(
            "hero_subtext", {"size_px": 34, "weight": "400", "color": "#FFFFFF"}
        )
        t_slogan = self._get_typo(
            "slogan", {"size_px": 42, "weight": "700", "color": "#FFFFFF"}
        )
        t_item_name = self._get_typo(
            "item_name", {"size_px": 34, "weight": "700", "color": text_c}
        )
        t_item_desc = self._get_typo(
            "item_description", {"size_px": 22, "weight": "400", "color": text_c}
        )

        parts = []
        parts.append(self._svg_header(bg))

        hero_ref = self._img_ref("hero.png")
        hero_cx = CANVAS_W // 2

        parts.append(f"""
  <!-- ===== HERO (62%) ===== -->
  <defs>
    <clipPath id="hd-hero-clip">
      <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}"/>
    </clipPath>
  </defs>
  <g clip-path="url(#hd-hero-clip)">""")

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="0" y="0" width="{CANVAS_W}" height="{hero_h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.15"/>"""
            )

        parts.append(
            f"""    <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="url(#hero-scrim)"/>"""
        )

        if badge:
            parts.append(f"""    <text x="{hero_cx}" y="{int(hero_h * 0.35)}"
          text-anchor="middle" class="body" font-size="{t_badge["size_px"]}" font-weight="{t_badge["weight"]}"
          fill="{t_badge["color"]}" letter-spacing="6" opacity="0.9"
          filter="url(#text-glow)">{badge}</text>""")

        if headline:
            parts.append(f"""    <text x="{hero_cx}" y="{int(hero_h * 0.48)}"
          text-anchor="middle" class="heavy" font-size="{t_headline["size_px"]}" font-weight="{t_headline["weight"]}"
          fill="{t_headline["color"]}" letter-spacing="4"
          filter="url(#text-shadow)">{headline}</text>""")

        if subtext:
            parts.append(f"""    <text x="{hero_cx}" y="{int(hero_h * 0.56)}"
          text-anchor="middle" class="body" font-size="{t_subtext["size_px"]}" font-weight="{t_subtext["weight"]}"
          fill="{t_subtext["color"]}" opacity="0.9"
          filter="url(#text-glow)">{subtext}</text>""")

        parts.append("  </g>")

        trans_svg = self._svg_transition("hero", "slogan", hero_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        if subtitle:
            parts.append(f"""
  <!-- ===== SLOGAN BAND ===== -->
  <rect x="0" y="{slogan_y}" width="{CANVAS_W}" height="{slogan_h}" fill="{brand}"/>
  <text x="{CANVAS_W // 2}" y="{slogan_y + slogan_h // 2 + int(t_slogan["size_px"] * 0.35)}"
        text-anchor="middle" class="title" font-size="{t_slogan["size_px"]}" font-weight="{t_slogan["weight"]}" fill="{t_slogan["color"]}"
        letter-spacing="3">{subtitle}</text>""")

        if items:
            cols = max(n_items, 2)
            col_w = (CANVAS_W - 2 * M - (cols - 1) * CARD_GAP) // cols
            card_img_h = min(220, items_h - 140)

            for idx in range(n_items):
                item = items[idx]
                card_x = M + idx * (col_w + CARD_GAP)
                card_y = items_y + 20
                card_total_h = items_h - 40
                cx = card_x + col_w // 2
                clip_id = f"hd-item-clip-{idx}"

                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                name = item.get("name", "")
                desc = item.get("description", "")

                parts.append(f"""  <g id="item-{idx + 1}" filter="url(#card-shadow)">
    <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{card_total_h}"
          rx="{CARD_R}" fill="#FFFFFF"/>
    <defs>
      <clipPath id="{clip_id}">
        <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{card_img_h}" rx="{CARD_R}"/>
      </clipPath>
    </defs>""")

                if item_ref:
                    parts.append(f"""    <g clip-path="url(#{clip_id})">
      <image href="{item_ref}" xlink:href="{item_ref}"
             x="{card_x}" y="{card_y}" width="{col_w}" height="{card_img_h}"
             preserveAspectRatio="xMidYMid slice"/>
    </g>""")
                else:
                    parts.append(f"""    <rect x="{card_x}" y="{card_y}" width="{col_w}" height="{card_img_h}"
          rx="{CARD_R}" fill="{brand}" opacity="0.06"/>""")

                name_y = card_y + card_img_h + 40
                parts.append(f"""    <text x="{cx}" y="{name_y}"
          text-anchor="middle" class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">
      {name}
    </text>""")
                if desc:
                    parts.append(f"""    <text x="{cx}" y="{name_y + 34}"
          text-anchor="middle" class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.6">
      {desc}
    </text>""")
                parts.append("  </g>")

        trans_svg = self._svg_transition("items", "promo", items_y + items_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, M))

        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 3: Magazine Split ───

    def _build_magazine_split(self, style: dict, copy_data: dict) -> str:
        brand = style.get("brand_color", "#333333")
        accent = style.get("accent_color", "#FF6B35")
        bg = style.get("bg_color", "#FAFAF8")
        text_c = style.get("text_color", "#333333")

        items = copy_data.get("items", [])
        cta = copy_data.get("cta", {})
        promo = copy_data.get("promo", {})
        sections = copy_data.get("sections", [])
        hero_data = copy_data.get("hero", {})
        headline = hero_data.get("headline", "")
        badge = hero_data.get("badge", "")

        M = 100
        CARD_R = 12
        img_size = 200
        text_x = M + img_size + 40
        global_item_idx = 0

        hero_zone = self._get_zone("hero", {"y_px": 0, "h_px": 1050})
        hero_h = hero_zone["h_px"]

        section_a_zone = self._get_zone("section_a", {"y_px": hero_h, "h_px": 660})
        section_a_y = section_a_zone["y_px"]
        section_h_a = section_a_zone["h_px"]

        section_b_zone = self._get_zone(
            "section_b", {"y_px": section_a_y + section_h_a, "h_px": 660}
        )
        section_b_y = section_b_zone["y_px"]
        section_h_b = section_b_zone["h_px"]

        promo_h = 140 if promo else 0
        promo_y = section_b_y + section_h_b

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 400, "h_px": 400})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        t_section_title = self._get_typo(
            "section_title", {"size_px": 44, "weight": "700", "color": accent}
        )
        t_item_name = self._get_typo(
            "item_name", {"size_px": 36, "weight": "700", "color": text_c}
        )
        t_item_desc = self._get_typo(
            "item_description", {"size_px": 24, "weight": "400", "color": text_c}
        )
        t_item_price = self._get_typo(
            "item_price", {"size_px": 40, "weight": "800", "color": accent}
        )
        t_section_item = self._get_typo(
            "section_item", {"size_px": 30, "weight": "400", "color": text_c}
        )

        if sections and len(sections) >= 2:
            sec_a_title = sections[0].get("title", "\uc8fc\uc694 \uc11c\ube44\uc2a4")
            sec_a_items = sections[0].get("items", [])
            sec_b_title = sections[1].get("title", "\ucd94\uac00 \uc11c\ube44\uc2a4")
            sec_b_items = sections[1].get("items", [])
        else:
            mid = (len(items) + 1) // 2
            sec_a_title = "\uc8fc\uc694 \uc11c\ube44\uc2a4"
            sec_a_items = items[:mid]
            sec_b_title = "\ucd94\uac00 \uc11c\ube44\uc2a4"
            sec_b_items = items[mid:]

        parts = []
        parts.append(self._svg_header(bg))

        hero_ref = self._img_ref("hero.png")
        hero_cx = CANVAS_W // 2

        parts.append(f"""
  <!-- ===== HERO (35%) ===== -->
  <defs>
    <clipPath id="ms-hero-clip">
      <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}"/>
    </clipPath>
  </defs>
  <g clip-path="url(#ms-hero-clip)">""")

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="0" y="0" width="{CANVAS_W}" height="{hero_h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.15"/>"""
            )

        parts.append(
            f"""    <rect x="0" y="0" width="{CANVAS_W}" height="{hero_h}" fill="url(#hero-scrim)"/>"""
        )

        if badge:
            parts.append(f"""    <text x="{hero_cx}" y="{int(hero_h * 0.25)}"
          text-anchor="middle" class="body" font-size="28" font-weight="600"
          fill="#FFFFFF" letter-spacing="6" opacity="0.9"
          filter="url(#text-glow)">{badge}</text>""")
        if headline:
            parts.append(f"""    <text x="{hero_cx}" y="{int(hero_h * 0.55)}"
          text-anchor="middle" class="heavy" font-size="90" font-weight="900"
          fill="#FFFFFF" letter-spacing="4"
          filter="url(#text-shadow)">{headline}</text>""")

        parts.append("  </g>")

        trans_svg = self._svg_transition("hero", "section_a", hero_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        def _render_section(sec_y, sec_h, sec_title, sec_items, sec_bg_opacity):
            nonlocal global_item_idx
            sec_parts = []
            sec_parts.append(f"""
  <rect x="0" y="{sec_y}" width="{CANVAS_W}" height="{sec_h}" fill="{brand}" opacity="{sec_bg_opacity}"/>
  <text x="{M}" y="{sec_y + 60}"
        class="title" font-size="{t_section_title["size_px"]}" font-weight="{t_section_title["weight"]}" fill="{t_section_title["color"]}">
    {sec_title}
  </text>
  <line x1="{M}" y1="{sec_y + 80}" x2="{CANVAS_W - M}" y2="{sec_y + 80}"
        stroke="{accent}" stroke-width="2" opacity="0.3"/>""")
            iy = sec_y + 110
            row_h = max(230, (sec_h - 110) // max(len(sec_items), 1))
            for item in sec_items:
                name = item.get("name", "")
                desc = item.get("description", "")
                price = item.get("price", "")
                item_ref = self._img_ref(f"item_{global_item_idx + 1:02d}.png")
                clip_id = f"ms-item-clip-{global_item_idx}"

                if item_ref:
                    sec_parts.append(f"""  <defs>
    <clipPath id="{clip_id}">
      <rect x="{M}" y="{iy}" width="{img_size}" height="{img_size}" rx="{CARD_R}"/>
    </clipPath>
  </defs>
  <g clip-path="url(#{clip_id})">
    <image href="{item_ref}" xlink:href="{item_ref}"
           x="{M}" y="{iy}" width="{img_size}" height="{img_size}"
           preserveAspectRatio="xMidYMid slice"/>
  </g>""")
                    sec_parts.append(f"""  <text x="{text_x}" y="{iy + 55}"
        class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">{name}</text>""")
                    if desc:
                        sec_parts.append(f"""  <text x="{text_x}" y="{iy + 100}"
        class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.65">{desc}</text>""")
                    if price:
                        sec_parts.append(f"""  <text x="{CANVAS_W - M}" y="{iy + 55}"
        text-anchor="end" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">{price}</text>""")
                else:
                    bullet_text = f"\u2022  {name}"
                    if desc:
                        bullet_text += f" \u2014 {desc}"
                    sec_parts.append(f"""  <text x="{M + 30}" y="{iy + 30}"
        class="body" font-size="{t_section_item["size_px"]}" font-weight="{t_section_item["weight"]}" fill="{t_section_item["color"]}">{bullet_text}</text>""")

                iy += row_h
                global_item_idx += 1
            return "\n".join(sec_parts)

        parts.append(
            _render_section(section_a_y, section_h_a, sec_a_title, sec_a_items, "0.04")
        )

        trans_svg = self._svg_transition(
            "section_a", "section_b", section_a_y + section_h_a, accent, M
        )
        if trans_svg:
            parts.append(trans_svg)

        parts.append(
            _render_section(section_b_y, section_h_b, sec_b_title, sec_b_items, "0.08")
        )

        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, M))
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 4: Bold Typo ───

    def _build_bold_typo(self, style: dict, copy_data: dict) -> str:
        """볼드 타이포 — 브랜드 헤더 25% → 히어로+스크림 30% → 카드형 가격표 20% → 프로모 10% → CTA 15%"""
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
        subtext = hero_data.get("subtext", "")

        M = 100
        CARD_R = 16
        CARD_GAP = 24

        # Zone positions
        header_zone = self._get_zone("header", {"y_px": 0, "h_px": 750})
        header_h = header_zone["h_px"]

        hero_zone = self._get_zone("hero", {"y_px": header_h, "h_px": 900})
        hero_y = hero_zone["y_px"]
        hero_h = hero_zone["h_px"]

        pricelist_zone = self._get_zone(
            "price_list", {"y_px": hero_y + hero_h, "h_px": 600}
        )
        pricelist_y = pricelist_zone["y_px"]
        pricelist_h = pricelist_zone["h_px"]

        promo_h = 300 if promo else 0
        promo_y = pricelist_y + pricelist_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 450, "h_px": 450})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        # Typography
        text_on_brand = accent if accent != brand else "#FFFFFF"
        t_biz_name = self._get_typo(
            "business_name", {"size_px": 140, "weight": "900", "color": text_on_brand}
        )
        t_badge = self._get_typo(
            "hero_badge", {"size_px": 32, "weight": "600", "color": text_on_brand}
        )
        t_item_name = self._get_typo(
            "item_name", {"size_px": 38, "weight": "700", "color": text_c}
        )
        t_item_desc = self._get_typo(
            "item_description", {"size_px": 24, "weight": "400", "color": text_c}
        )
        t_item_price = self._get_typo(
            "item_price", {"size_px": 42, "weight": "700", "color": accent}
        )

        parts = []
        parts.append(self._svg_header(bg))

        # ── HEADER (25%) — brand bg + massive typography ──
        parts.append(f"""
  <!-- ===== HEADER (볼드 타이포) ===== -->
  <rect x="0" y="0" width="{CANVAS_W}" height="{header_h}" fill="{brand}"/>
  <line x1="{CANVAS_W // 2 - 120}" y1="{header_h // 2 - 90}" x2="{CANVAS_W // 2 + 120}" y2="{header_h // 2 - 90}"
        stroke="{accent}" stroke-width="3" opacity="0.6"/>""")

        if badge:
            parts.append(f"""  <text x="{CANVAS_W // 2}" y="{header_h // 2 - 50}"
        text-anchor="middle" class="body" font-size="{t_badge["size_px"]}" font-weight="{t_badge["weight"]}"
        fill="{t_badge["color"]}" letter-spacing="6" opacity="0.8">{badge}</text>""")

        parts.append(f"""  <text x="{CANVAS_W // 2}" y="{header_h // 2 + 50}"
        text-anchor="middle" class="heavy" font-size="{t_biz_name["size_px"]}" font-weight="{t_biz_name["weight"]}"
        fill="{t_biz_name["color"]}" letter-spacing="6">{biz_name}</text>""")

        if subtext:
            parts.append(f"""  <text x="{CANVAS_W // 2}" y="{header_h // 2 + 120}"
        text-anchor="middle" class="body" font-size="34" font-weight="400"
        fill="{text_on_brand}" opacity="0.7">{subtext}</text>""")

        parts.append(f"""  <line x1="{CANVAS_W // 2 - 120}" y1="{header_h - 60}" x2="{CANVAS_W // 2 + 120}" y2="{header_h - 60}"
        stroke="{accent}" stroke-width="3" opacity="0.6"/>""")

        # ── Transition: header → hero ──
        trans_svg = self._svg_transition("header", "hero", header_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        # ── HERO (30%) — image with scrim overlay ──
        hero_ref = self._img_ref("hero.png")
        parts.append(f"""
  <!-- ===== HERO (30%) ===== -->
  <defs>
    <clipPath id="bt-hero-clip">
      <rect x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}"/>
    </clipPath>
  </defs>
  <g clip-path="url(#bt-hero-clip)">""")

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}" fill="{brand}" opacity="0.15"/>"""
            )

        parts.append(
            f"""    <rect x="0" y="{hero_y}" width="{CANVAS_W}" height="{hero_h}" fill="url(#hero-scrim)"/>"""
        )
        parts.append("  </g>")

        # ── PRICE LIST (20%) — card rows with images ──
        n_items = len(items) if items else 0
        img_size = 180
        parts.append(f"""
  <!-- ===== PRICE LIST ===== -->
  <rect x="0" y="{pricelist_y}" width="{CANVAS_W}" height="{pricelist_h}" fill="{bg}"/>""")

        if items:
            has_any_image = any(
                self._img_ref(f"item_{i + 1:02d}.png") for i in range(n_items)
            )
            row_h = max(200, (pricelist_h - 60) // max(n_items, 1))
            start_y = pricelist_y + 30

            for idx, item in enumerate(items):
                name = item.get("name", "")
                price = item.get("price", "")
                desc = item.get("description", "")
                iy = start_y + idx * row_h
                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                card_y = iy
                card_h = row_h - CARD_GAP
                clip_id = f"bt-item-clip-{idx}"

                if has_any_image and item_ref:
                    # Card row: white card bg + clipped image + text
                    parts.append(f"""
  <g filter="url(#card-shadow)">
    <rect x="{M}" y="{card_y}" width="{CANVAS_W - 2 * M}" height="{card_h}"
          rx="{CARD_R}" fill="#FFFFFF"/>
  </g>
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{M + 12}" y="{card_y + 12}" width="{img_size}" height="{card_h - 24}" rx="12"/>
    </clipPath>
  </defs>
  <g clip-path="url(#{clip_id})">
    <image href="{item_ref}" xlink:href="{item_ref}"
           x="{M + 12}" y="{card_y + 12}" width="{img_size}" height="{card_h - 24}"
           preserveAspectRatio="xMidYMid slice"/>
  </g>""")
                    text_x = M + img_size + 40
                    parts.append(f"""  <text x="{text_x}" y="{card_y + card_h // 2 - 10}"
        class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">{name}</text>""")
                    if desc:
                        parts.append(f"""  <text x="{text_x}" y="{card_y + card_h // 2 + 28}"
        class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.6">{desc}</text>""")
                    if price:
                        parts.append(f"""  <text x="{CANVAS_W - M - 24}" y="{card_y + card_h // 2 + 8}"
        text-anchor="end" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">{price}</text>""")
                else:
                    # Text-only row with subtle separator
                    parts.append(f"""  <text x="{M + 24}" y="{card_y + card_h // 2 + 8}"
        class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">{name}</text>""")
                    if price:
                        parts.append(f"""  <text x="{CANVAS_W - M - 24}" y="{card_y + card_h // 2 + 8}"
        text-anchor="end" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">{price}</text>""")
                    if idx < n_items - 1:
                        sep_y = card_y + card_h
                        parts.append(f"""  <line x1="{M + 24}" y1="{sep_y}" x2="{CANVAS_W - M - 24}" y2="{sep_y}"
        stroke="{text_c}" stroke-width="1" opacity="0.1"/>""")

        # ── Transition: price_list → promo ──
        trans_svg = self._svg_transition(
            "price_list", "promo", pricelist_y + pricelist_h, accent, M
        )
        if trans_svg:
            parts.append(trans_svg)

        # ── Promo (10%) ──
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, M))

        # ── CTA (15%) ──
        if cta:
            parts.append(self._svg_cta(cta, style, cta_y, cta_h))

        parts.append(self._svg_trim_marks())
        parts.append(self._svg_footer())
        return "\n".join(parts)

    # ─── Layout 5: Side by Side ───

    def _build_side_by_side(self, style: dict, copy_data: dict) -> str:
        """좌우 분할 — 좌(히어로+스크림 50%) + 우(카드형 텍스트 50%) 70% → 프로모 12% → CTA 18%"""
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
        badge = hero_data.get("badge", "")

        M = 60
        CARD_R = 16
        CARD_GAP = 20
        half_w = CANVAS_W // 2

        hero_left_zone = self._get_zone(
            "hero_left", {"x_px": 0, "w_px": half_w, "y_px": 0, "h_px": 2100}
        )
        top_h = hero_left_zone["h_px"]

        promo_h = 360 if promo else 0
        promo_y = top_h

        cta_zone = self._get_zone("cta", {"y_px": CANVAS_H - 540, "h_px": 540})
        cta_y = cta_zone["y_px"]
        cta_h = cta_zone["h_px"]

        t_biz_name = self._get_typo(
            "business_name", {"size_px": 60, "weight": "900", "color": text_c}
        )
        t_subtitle = self._get_typo(
            "hero_subtext", {"size_px": 30, "weight": "400", "color": text_c}
        )
        t_item_name = self._get_typo(
            "item_name", {"size_px": 34, "weight": "700", "color": text_c}
        )
        t_item_price = self._get_typo(
            "item_price", {"size_px": 34, "weight": "700", "color": accent}
        )
        t_item_desc = self._get_typo(
            "item_description", {"size_px": 22, "weight": "400", "color": text_c}
        )
        t_features = self._get_typo(
            "features", {"size_px": 24, "weight": "400", "color": text_c}
        )

        parts = []
        parts.append(self._svg_header(bg))

        # ── LEFT: Hero with scrim + overlay text ──
        hero_ref = self._img_ref("hero.png")
        parts.append(f"""
  <!-- ===== LEFT: HERO ===== -->
  <defs>
    <clipPath id="sbs-hero-clip">
      <rect x="0" y="0" width="{half_w}" height="{top_h}"/>
    </clipPath>
    <linearGradient id="sbs-hero-grad" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#000000" stop-opacity="0.15"/>
      <stop offset="0.6" stop-color="#000000" stop-opacity="0.05"/>
      <stop offset="1" stop-color="#000000" stop-opacity="0.55"/>
    </linearGradient>
  </defs>
  <g clip-path="url(#sbs-hero-clip)">""")

        if hero_ref:
            parts.append(f"""    <image href="{hero_ref}" xlink:href="{hero_ref}"
           x="0" y="0" width="{half_w}" height="{top_h}"
           preserveAspectRatio="xMidYMid slice"/>""")
        else:
            parts.append(
                f"""    <rect x="0" y="0" width="{half_w}" height="{top_h}" fill="{brand}" opacity="0.15"/>"""
            )

        parts.append(
            f"""    <rect x="0" y="0" width="{half_w}" height="{top_h}" fill="url(#sbs-hero-grad)"/>"""
        )

        hero_cx = half_w // 2
        if badge:
            parts.append(f"""    <text x="{hero_cx}" y="{top_h - 180}"
          text-anchor="middle" class="body" font-size="28" font-weight="600"
          fill="#FFFFFF" letter-spacing="5" opacity="0.85"
          filter="url(#text-glow)">{badge}</text>""")

        parts.append(f"""    <text x="{hero_cx}" y="{top_h - 100}"
          text-anchor="middle" class="heavy" font-size="72" font-weight="900"
          fill="#FFFFFF" letter-spacing="3"
          filter="url(#text-shadow)">{biz_name}</text>""")
        parts.append("  </g>")

        # ── Accent strip between left and right ──
        parts.append(f"""
  <rect x="{half_w}" y="0" width="6" height="{top_h}" fill="{accent}"/>""")

        # ── RIGHT: Text content panel ──
        rx = half_w + 6 + M
        rw = half_w - 6 - 2 * M
        ry = 100

        parts.append(f"""
  <!-- ===== RIGHT: TEXT CONTENT ===== -->
  <line x1="{rx}" y1="{ry - 20}" x2="{rx + 80}" y2="{ry - 20}"
        stroke="{accent}" stroke-width="3"/>
  <text x="{rx}" y="{ry + 40}"
        class="heavy" font-size="{t_biz_name["size_px"]}" font-weight="{t_biz_name["weight"]}" fill="{t_biz_name["color"]}">{biz_name}</text>""")
        ry += 80

        if subtitle:
            parts.append(f"""  <text x="{rx}" y="{ry}"
        class="body" font-size="{t_subtitle["size_px"]}" font-weight="{t_subtitle["weight"]}" fill="{t_subtitle["color"]}" opacity="0.65">{subtitle}</text>""")
            ry += 50

        ry += 30

        # ── Items as mini-cards ──
        if items:
            n_items = len(items)
            has_any_image = any(
                self._img_ref(f"item_{i + 1:02d}.png") for i in range(n_items)
            )
            img_sz = 130
            card_inner_h = max(img_sz + 16, 160)

            for idx, item in enumerate(items):
                name = item.get("name", "")
                price = item.get("price", "")
                desc = item.get("description", "")
                item_ref = self._img_ref(f"item_{idx + 1:02d}.png")
                clip_id = f"sbs-item-clip-{idx}"

                if has_any_image and item_ref:
                    parts.append(f"""
  <g filter="url(#card-shadow)">
    <rect x="{rx - 8}" y="{ry}" width="{rw + 16}" height="{card_inner_h}"
          rx="{CARD_R}" fill="#FFFFFF"/>
  </g>
  <defs>
    <clipPath id="{clip_id}">
      <rect x="{rx}" y="{ry + 8}" width="{img_sz}" height="{img_sz}" rx="10"/>
    </clipPath>
  </defs>
  <g clip-path="url(#{clip_id})">
    <image href="{item_ref}" xlink:href="{item_ref}"
           x="{rx}" y="{ry + 8}" width="{img_sz}" height="{img_sz}"
           preserveAspectRatio="xMidYMid slice"/>
  </g>""")
                    text_x = rx + img_sz + 20
                    parts.append(f"""  <text x="{text_x}" y="{ry + card_inner_h // 2 - 12}"
        class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">{name}</text>""")
                    if desc:
                        parts.append(f"""  <text x="{text_x}" y="{ry + card_inner_h // 2 + 20}"
        class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.6">{desc}</text>""")
                    if price:
                        parts.append(f"""  <text x="{rx + rw + 8}" y="{ry + card_inner_h // 2 + 6}"
        text-anchor="end" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">{price}</text>""")
                    ry += card_inner_h + CARD_GAP
                else:
                    parts.append(f"""  <text x="{rx}" y="{ry + 28}"
        class="title" font-size="{t_item_name["size_px"]}" font-weight="{t_item_name["weight"]}" fill="{t_item_name["color"]}">{name}</text>""")
                    if desc:
                        parts.append(f"""  <text x="{rx}" y="{ry + 58}"
        class="body" font-size="{t_item_desc["size_px"]}" font-weight="{t_item_desc["weight"]}" fill="{t_item_desc["color"]}" opacity="0.6">{desc}</text>""")
                    if price:
                        parts.append(f"""  <text x="{rx + rw}" y="{ry + 28}"
        text-anchor="end" class="title" font-size="{t_item_price["size_px"]}" font-weight="{t_item_price["weight"]}" fill="{t_item_price["color"]}">{price}</text>""")
                    ry += 90
                    if idx < n_items - 1:
                        parts.append(f"""  <line x1="{rx}" y1="{ry - 10}" x2="{rx + rw}" y2="{ry - 10}"
        stroke="{text_c}" stroke-width="1" opacity="0.1"/>""")

        # ── Features at bottom of right panel ──
        if features:
            feat_y = max(ry + 50, top_h - 260)
            parts.append(f"""
  <line x1="{rx}" y1="{feat_y - 30}" x2="{rx + rw}" y2="{feat_y - 30}"
        stroke="{brand}" stroke-width="1" opacity="0.12"/>""")
            for feat in features:
                parts.append(f"""  <text x="{rx + 20}" y="{feat_y}"
        class="body" font-size="{t_features["size_px"]}" font-weight="{t_features["weight"]}" fill="{t_features["color"]}" opacity="0.55">\u2022  {feat}</text>""")
                feat_y += 38

        # ── Transition ──
        trans_svg = self._svg_transition("hero_left", "promo", top_h, accent, M)
        if trans_svg:
            parts.append(trans_svg)

        # ── Promo (12%) ──
        if promo:
            parts.append(self._svg_promo(promo, style, promo_y, promo_h, M))

        # ── CTA (18%) ──
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
    parser.add_argument(
        "--auto",
        action="store_true",
        help="전자동: brief.json만으로 copy→design→art_direct→이미지→SVG 전체 실행",
    )
    args = parser.parse_args()

    gen = PosterGenerator(args.workdir, embed=args.embed)
    if args.layout:
        gen.cli_layout = args.layout

    if args.auto:
        ok = gen.auto_all()
    elif args.full:
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
