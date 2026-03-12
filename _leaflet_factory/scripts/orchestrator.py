#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
V2 Leaflet Factory Orchestrator
- 파이프라인 기반 전단지 생성 에이전트
- Gemini Text, Vision, Image API를 활용하여 전단지를 생성합니다.
"""

import os
import sys
import json
import time
import argparse
import base64
import logging
from pathlib import Path

# 로깅 설정
logging.basicConfig(level=logging.INFO, format='%(asctime)s [%(levelname)s] %(message)s')
logger = logging.getLogger(__name__)

# GeminiClient Load (절대 경로 사용)
DETAIL_PAGE_DIR = Path("/var/www/html/_detail_page")
SCRIPTS_DIR = DETAIL_PAGE_DIR / "scripts"

if str(SCRIPTS_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPTS_DIR))

# [FIX] gemini_client.py 내부에서 로깅을 설정할 때 상대경로("logs/...")를 사용하므로, 
# 현재 디렉토리를 잠시 _detail_page로 변경하여 권한 오류(Permission Denied)를 방지합니다.
_orig_cwd = os.getcwd()
try:
    os.chdir(str(DETAIL_PAGE_DIR)) 
    from gemini_client import GeminiClient
    logger.info("GeminiClient successfully imported.")
except ImportError as e:
    logger.error(f"Failed to import GeminiClient: {e}")
    GeminiClient = None
finally:
    os.chdir(_orig_cwd)

# Vision 등 직접 제어를 위해 google.generativeai 임포트 시도
try:
    import google.generativeai as genai
    import warnings
    warnings.filterwarnings("ignore")
    api_key = os.environ.get("GEMINI_API_KEY")
    if api_key:
        genai.configure(api_key=api_key)
    else:
        logger.error("GEMINI_API_KEY environment variable not found.")
except ImportError:
    genai = None

class LeafletOrchestrator:
    def __init__(self, workdir: str):
        self.workdir = Path(workdir)
        self.brief_path = self.workdir / 'brief.json'
        self.status_path = self.workdir / 'status.json'
        
        # 상태 관리 초기 데이터 로드
        try:
            with open(self.status_path, 'r', encoding='utf-8') as f:
                self.status_data = json.load(f)
        except:
            self.status_data = {
                "status": "running",
                "progress": 0,
                "current_step_name": "초기화",
                "logs": ["[System] 오케스트레이터 클래스 생성"]
            }
        
        # Load Brief
        with open(self.brief_path, 'r', encoding='utf-8') as f:
            self.brief = json.load(f)

        if GeminiClient:
            # GeminiClient.__init__는 api_key만 인자로 받으며, 모델은 환경변수에서 읽습니다.
            try:
                self.client = GeminiClient()
                self.update_status(5, "초기화 완료", "[System] AI 엔진 준비 완료.")
            except Exception as e:
                self.client = None
                self.update_status(5, "초기화 오류", f"[Error] GeminiClient 초기화 실패: {e}")
        else:
            self.client = None
            self.update_status(5, "초기화 오류", "[Error] GeminiClient를 로드할 수 없습니다.")

    def update_status(self, progress: int, step_name: str, log_msg: str = None):
        self.status_data["progress"] = progress
        self.status_data["current_step_name"] = step_name
        if log_msg:
            self.status_data["logs"].append(log_msg)
            logger.info(log_msg)
            
        try:
            with open(self.status_path, 'w', encoding='utf-8') as f:
                json.dump(self.status_data, f, ensure_ascii=False, indent=2)
        except Exception as e:
            logger.error(f"Failed to update status file: {e}")

    def _get_image_part(self, image_path: str):
        if not os.path.exists(image_path):
            return None
        try:
            with open(image_path, "rb") as f:
                image_bytes = f.read()
            
            ext = os.path.splitext(image_path)[1].lower()
            mime_type = "image/jpeg"
            if ext == ".png": mime_type = "image/png"
            elif ext == ".webp": mime_type = "image/webp"
            
            return {"mime_type": mime_type, "data": image_bytes}
        except Exception as e:
            logger.error(f"Failed to read image {image_path}: {e}")
            return None

    def step_1_vision_analysis(self):
        uploads = self.brief.get('uploaded_images', [])
        image_mode = self.brief.get('direction', {}).get('image_usage', 'ai_generate')
        
        self.update_status(10, "이미지 분석 및 기획", f"[Vision Agent] 이미지 {len(uploads)}장 분석 시작 (모드: {image_mode})")
        self.vision_context = {}
        
        if uploads and genai:
            self.update_status(15, "이미지 분석 및 기획", "[Vision Agent] Gemini Vision API를 호출하여 이미지를 분석합니다.")
            try:
                model = genai.GenerativeModel('gemini-2.5-flash') 
                prompt = "이 이미지들의 주요 피사체, 전체적인 분위기(Mood), 그리고 메인 컬러들을 분석해서 JSON 형식으로 알려줘. 형식: {\"subjects\": [\"피사체1\"], \"mood\": \"분위기 설명\", \"dominant_colors\": [\"#FFFFFF\", \"#000000\"]}"
                
                contents = [prompt]
                for upload_path in uploads[:3]: 
                    img_part = self._get_image_part(upload_path)
                    if img_part:
                        contents.append(img_part)
                
                if len(contents) > 1:
                    response = model.generate_content(contents)
                    text = response.text
                    if '```json' in text: text = text.split('```json')[1].split('```')[0]
                    elif '```' in text: text = text.split('```')[1].split('```')[0]
                        
                    parsed_json = json.loads(text.strip())
                    # Ensure it's a dictionary. Sometimes AI returns a list of dictionaries.
                    if isinstance(parsed_json, list) and len(parsed_json) > 0:
                        self.vision_context = parsed_json[0]
                    elif isinstance(parsed_json, dict):
                        self.vision_context = parsed_json
                    else:
                        self.vision_context = {}

                    mood = self.vision_context.get('mood', '기본 분위기') if isinstance(self.vision_context, dict) else '기본 분위기'
                    self.update_status(25, "이미지 분석 및 기획", f"[Vision Agent] 분석 완료: {mood}")
                else:
                    self.update_status(25, "이미지 분석 및 기획", "[Vision Agent] 유효한 이미지 에셋이 없어 분석을 건너뜁니다.")
            except Exception as e:
                self.vision_context = {} # Reset to safe dict on error
                self.update_status(25, "이미지 분석 및 기획", f"[Vision Agent Error] 분석 중 오류 발생, 기본값 사용 ({str(e)})")
        else:
            self.vision_context = {}
            self.update_status(25, "이미지 분석 및 기획", "[Vision Agent] 업로드된 이미지 없음. 텍스트 기반으로 기획을 진행합니다.")

    def step_2_copywriting(self):
        self.update_status(35, "카피라이팅 (Copywriter Agent)", "[Copywriter Agent] 가게 정보와 기획 방향에 맞는 카피를 작성합니다...")
        
        biz_name = self.brief.get('business_name', '')
        category = self.brief.get('category', '')
        features = ", ".join(self.brief.get('features', []))
        purpose = self.brief.get('direction', {}).get('purpose', '')
        target_aud = self.brief.get('direction', {}).get('target_audience', '')
        items = self.brief.get('items', [])
        
        items_str = "\n".join([f"- {i['name']}: {i['description']} ({i['price']})" for i in items])
        
        mood_str = self.vision_context.get('mood', '') if isinstance(self.vision_context, dict) else ''
        vision_str = f"이미지 분위기: {mood_str}" if mood_str else "분위기: 트렌디하고 세련된 매거진 스타일"

        prompt = f"""당신은 20년 경력의 B2B/B2C 전단지 카피라이터입니다.
다음에 제공된 정보를 바탕으로 포스터/전단지에 들어갈 시선을 끄는 헤드라인, 서브헤드라인, 그리고 강조 문구를 작성하세요.
JSON 형식으로만 응답해야 합니다.

[가게/비즈니스 정보]
이름: {biz_name}
업종: {category}
목적: {purpose}
타겟: {target_aud}
특징/강점: {features}
메뉴/품목 정보:
{items_str}

{vision_str}

[출력 JSON 형식]
{{
  "headline": "가장 눈에 띄는 15자 이내의 메인 카피",
  "subheadline": "메인 카피를 뒷받침하는 30자 이내의 서브 카피",
  "badge_text": "오픈기념, 할인 등 작게 들어갈 배지 텍스트 (없으면 빈 문자열)"
}}
"""
        try:
            if self.client:
                self.copy_data = self.client.generate_text_json(prompt)
                self.update_status(50, "카피라이팅 완료", f"[Copywriter Agent] 작성된 헤드라인: {self.copy_data.get('headline')}")
            else:
                raise Exception("GeminiClient Not Initialized")
        except Exception as e:
            self.update_status(50, "카피라이팅 완료", f"[Copywriter Agent] API 호출 실패, 기본 카피 사용 ({str(e)})")
            self.copy_data = {
                "headline": f"{biz_name}, 특별한 제안!",
                "subheadline": f"{features}의 가치를 경험해보세요.",
                "badge_text": purpose
            }
        
        with open(self.workdir / 'copy.json', 'w', encoding='utf-8') as f:
            json.dump(self.copy_data, f, ensure_ascii=False, indent=2)

    def step_3_image_generation(self):
        image_mode = self.brief.get('direction', {}).get('image_usage', 'ai_generate')
        uploads = self.brief.get('uploaded_images', [])
        
        self.image_path = None
        
        if image_mode == 'use_original' and uploads:
            self.update_status(60, "이미지 처리", "[System] 업로드된 원본 이미지를 전단지 메인 에셋으로 사용합니다.")
            self.image_path = uploads[0]
            return

        self.update_status(60, "이미지 생성 (Image Agent)", "[Image Agent] 카피와 기획 방향에 맞는 최적의 이미지를 생성합니다...")
        
        biz_name = self.brief.get('business_name', '')
        category = self.brief.get('category', '')
        headline = self.copy_data.get('headline', '')
        
        image_prompt = f"A high-end commercial editorial photography for a {category}. Subject/Concept: {biz_name}. Mood based on text '{headline}'. Aesthetic, trendy, elegant composition, beautiful lighting, cinematic. No text, no words."
        
        subjects = self.vision_context.get('subjects') if isinstance(self.vision_context, dict) else None
        if image_mode == 'reference_only' and subjects:
            subject_str = ", ".join(subjects)
            image_prompt += f" Ensure the main subject conceptually resembles: {subject_str}."

        try:
            if self.client:
                output_path = str(self.workdir / "hero_generated.png")
                self.client.generate_image(prompt=image_prompt, output_path=output_path, aspect_ratio="4:3")
                self.image_path = output_path
                self.update_status(75, "이미지 생성 완료", "[Image Agent] Imagen 3 고품질 렌더링 성공.")
            else:
                raise Exception("GeminiClient Not Initialized")
        except Exception as e:
            self.update_status(75, "이미지 처리", f"[Image Agent Error] 이미지 생성 실패 ({str(e)})")
            if uploads:
                self.image_path = uploads[0]

    def step_4_svg_assembly(self):
        """고품질 매거진/포스터 스타일 SVG 전단지 조립"""
        self.update_status(85, "SVG 조립 및 아트디렉팅", "[Art Director] 레이아웃 계산, 타이포그래피 정렬 및 고품질 SVG 조립 중...")
        
        # 아름다운 테마 색상 (엑셀 스타일 탈피, 고급스러운 매거진 톤)
        bg_color = "#f4f7f6"
        brand_color = "#2c3e50"
        accent_color = "#e67e22"
        card_bg = "#ffffff"
        text_main = "#333333"
        text_muted = "#7f8c8d"

        if isinstance(self.vision_context, dict) and self.vision_context.get('dominant_colors'):
            colors = self.vision_context['dominant_colors']
            if len(colors) > 0: brand_color = colors[0]
            if len(colors) > 1: bg_color = colors[1]

        headline = self.copy_data.get('headline', '')
        subheadline = self.copy_data.get('subheadline', '')
        badge_text = self.copy_data.get('badge_text', '')
        biz_name = self.brief.get('business_name', '')
        contact = self.brief.get('contact', {})
        items = self.brief.get('items', [])
        uploads = self.brief.get('uploaded_images', [])
        layout_style = self.brief.get('direction', {}).get('layout_style', 'auto')
        image_mode = self.brief.get('direction', {}).get('image_usage', 'ai_generate')

        WIDTH = 800
        HEIGHT = 1200
        
        svg_parts = []
        svg_parts.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {WIDTH} {HEIGHT}" width="{WIDTH}" height="{HEIGHT}">')
        
        # 1. Background with subtle gradient and filters
        svg_parts.append(f'''
        <defs>
            <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="{bg_color}" stop-opacity="0.6"/>
                <stop offset="100%" stop-color="#ffffff" stop-opacity="1"/>
            </linearGradient>
            <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="15" stdDeviation="20" flood-color="#000" flood-opacity="0.08"/>
            </filter>
            <filter id="lightShadow" x="-10%" y="-10%" width="120%" height="120%">
                <feDropShadow dx="0" dy="5" stdDeviation="8" flood-color="#000" flood-opacity="0.05"/>
            </filter>
            <clipPath id="heroClip"><rect x="40" y="40" width="720" height="460" rx="24"/></clipPath>
        </defs>
        <rect width="{WIDTH}" height="{HEIGHT}" fill="url(#bgGrad)"/>
        ''')
        
        y_offset = 40
        
        # 볼드 타이포 레이아웃인 경우 이미지를 줄이고 텍스트를 최상단에 거대하게 배치
        if layout_style == 'bold_typo':
            svg_parts.append(f'<text x="400" y="150" font-family="sans-serif" font-size="64" font-weight="900" fill="{brand_color}" text-anchor="middle">{headline}</text>')
            svg_parts.append(f'<text x="400" y="210" font-family="sans-serif" font-size="28" fill="{text_main}" text-anchor="middle">{subheadline}</text>')
            if badge_text:
                svg_parts.append(f'<rect x="340" y="50" width="120" height="36" fill="{accent_color}" rx="18"/>')
                svg_parts.append(f'<text x="400" y="74" font-family="sans-serif" font-size="16" font-weight="bold" fill="#ffffff" text-anchor="middle">{badge_text}</text>')
            y_offset = 260
            
            if self.image_path and os.path.exists(self.image_path):
                try:
                    with open(self.image_path, "rb") as img_f:
                        b64_img = base64.b64encode(img_f.read()).decode('utf-8')
                    ext = os.path.splitext(self.image_path)[1].lower()
                    mime = "image/png" if ext == ".png" else "image/jpeg"
                    img_data_uri = f"data:{mime};base64,{b64_img}"
                    
                    svg_parts.append(f'<clipPath id="smallHeroClip"><rect x="40" y="{y_offset}" width="720" height="300" rx="24"/></clipPath>')
                    svg_parts.append(f'<rect x="40" y="{y_offset}" width="720" height="300" rx="24" fill="{card_bg}" filter="url(#shadow)"/>')
                    svg_parts.append(f'<image x="40" y="{y_offset}" width="720" height="300" preserveAspectRatio="xMidYMid slice" href="{img_data_uri}" clip-path="url(#smallHeroClip)"/>')
                    y_offset += 340
                except:
                    y_offset += 50
                    
        # 기본, 히어로 강조, 매거진 분할, 사이드바이 등은 기존 매거진 스타일(큰 이미지 위에 텍스트 오버레이) 사용
        else:
            if self.image_path and os.path.exists(self.image_path):
                try:
                    with open(self.image_path, "rb") as img_f:
                        b64_img = base64.b64encode(img_f.read()).decode('utf-8')
                    ext = os.path.splitext(self.image_path)[1].lower()
                    mime = "image/png" if ext == ".png" else "image/jpeg"
                    img_data_uri = f"data:{mime};base64,{b64_img}"
                    
                    # 배경 그림자
                    svg_parts.append(f'<rect x="40" y="{y_offset}" width="720" height="460" rx="24" fill="{card_bg}" filter="url(#shadow)"/>')
                    # 마스킹된 이미지
                    svg_parts.append(f'<image x="40" y="{y_offset}" width="720" height="460" preserveAspectRatio="xMidYMid slice" href="{img_data_uri}" clip-path="url(#heroClip)"/>')
                    
                    # 이미지 위에 그라데이션 오버레이 (텍스트 가독성)
                    svg_parts.append(f'''
                    <linearGradient id="overlay" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="50%" stop-color="#000000" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#000000" stop-opacity="0.8"/>
                    </linearGradient>
                    <rect x="40" y="{y_offset}" width="720" height="460" rx="24" fill="url(#overlay)"/>
                    ''')
                    
                    # 이미지 위 텍스트 배치
                    if badge_text:
                        svg_parts.append(f'<rect x="70" y="{y_offset + 30}" width="100" height="32" fill="{accent_color}" rx="16"/>')
                        svg_parts.append(f'<text x="120" y="{y_offset + 51}" font-family="sans-serif" font-size="14" font-weight="bold" fill="#ffffff" text-anchor="middle">{badge_text}</text>')
                    
                    svg_parts.append(f'<text x="70" y="{y_offset + 400}" font-family="sans-serif" font-size="46" font-weight="900" fill="#ffffff" filter="drop-shadow(0px 2px 4px rgba(0,0,0,0.5))">{headline}</text>')
                    svg_parts.append(f'<text x="70" y="{y_offset + 435}" font-family="sans-serif" font-size="22" fill="#ffffff" opacity="0.9" filter="drop-shadow(0px 1px 2px rgba(0,0,0,0.5))">{subheadline}</text>')
                    
                except Exception as e:
                    logger.error(f"Failed to embed image: {e}")
            else:
                # 이미지가 없을 경우 화려한 텍스트 헤더
                svg_parts.append(f'<rect x="40" y="{y_offset}" width="720" height="300" rx="24" fill="{brand_color}" filter="url(#shadow)"/>')
                if badge_text:
                    svg_parts.append(f'<rect x="70" y="{y_offset + 40}" width="100" height="32" fill="{accent_color}" rx="16"/>')
                    svg_parts.append(f'<text x="120" y="{y_offset + 61}" font-family="sans-serif" font-size="14" font-weight="bold" fill="#ffffff" text-anchor="middle">{badge_text}</text>')
                svg_parts.append(f'<text x="70" y="{y_offset + 180}" font-family="sans-serif" font-size="48" font-weight="900" fill="#ffffff">{headline}</text>')
                svg_parts.append(f'<text x="70" y="{y_offset + 230}" font-family="sans-serif" font-size="24" fill="#ffffff" opacity="0.8">{subheadline}</text>')
                y_offset -= 160

            y_offset += 520

        # 3. Items Section 
        if items:
            svg_parts.append(f'<text x="50" y="{y_offset}" font-family="sans-serif" font-size="28" font-weight="800" fill="{text_main}">주요 상품 및 서비스</text>')
            y_offset += 30
            
            # 클래식 그리드는 표 형태로 렌더링
            if layout_style == 'classic_grid':
                svg_parts.append(f'<rect x="50" y="{y_offset}" width="700" height="40" fill="{brand_color}" opacity="0.1" rx="4"/>')
                svg_parts.append(f'<text x="70" y="{y_offset + 26}" font-family="sans-serif" font-size="16" font-weight="bold" fill="{brand_color}">상품명</text>')
                svg_parts.append(f'<text x="250" y="{y_offset + 26}" font-family="sans-serif" font-size="16" font-weight="bold" fill="{brand_color}">상세 설명</text>')
                svg_parts.append(f'<text x="730" y="{y_offset + 26}" font-family="sans-serif" font-size="16" font-weight="bold" fill="{brand_color}" text-anchor="end">가격/조건</text>')
                y_offset += 40
                
                for i, item in enumerate(items[:8]):
                    bg = "#ffffff" if i % 2 == 0 else "#fbfbfb"
                    svg_parts.append(f'<rect x="50" y="{y_offset}" width="700" height="50" fill="{bg}" stroke="#e0e0e0" stroke-width="1"/>')
                    svg_parts.append(f'<text x="70" y="{y_offset + 31}" font-family="sans-serif" font-size="18" font-weight="bold" fill="{text_main}">{item.get("name","")}</text>')
                    svg_parts.append(f'<text x="250" y="{y_offset + 31}" font-family="sans-serif" font-size="16" fill="{text_main}" opacity="0.8">{item.get("description","")}</text>')
                    svg_parts.append(f'<text x="730" y="{y_offset + 31}" font-family="sans-serif" font-size="18" font-weight="bold" fill="{accent_color}" text-anchor="end">{item.get("price","")}</text>')
                    y_offset += 50
                y_offset += 30

            # 나머지 모드는 카드 형태
            else:
                card_width = 345
                card_height = 100
                
                for i, item in enumerate(items[:6]): # 최대 6개 (2열 3행)
                    col = i % 2
                    row = i // 2
                    x_pos = 50 + (col * (card_width + 20))
                    y_pos = y_offset + (row * (card_height + 20))
                    
                    svg_parts.append(f'<rect x="{x_pos}" y="{y_pos}" width="{card_width}" height="{card_height}" rx="12" fill="{card_bg}" filter="url(#lightShadow)"/>')
                    
                    # Left accent border
                    svg_parts.append(f'<rect x="{x_pos}" y="{y_pos}" width="6" height="{card_height}" rx="3" fill="{brand_color}" opacity="0.8"/>')
                    
                    # 텍스트
                    svg_parts.append(f'<text x="{x_pos + 25}" y="{y_pos + 35}" font-family="sans-serif" font-size="20" font-weight="bold" fill="{text_main}">{item.get("name","")}</text>')
                    
                    desc = item.get("description","")
                    if len(desc) > 18: desc = desc[:17] + "..." # Truncate long descriptions
                    svg_parts.append(f'<text x="{x_pos + 25}" y="{y_pos + 60}" font-family="sans-serif" font-size="14" fill="{text_muted}">{desc}</text>')
                    
                    svg_parts.append(f'<text x="{x_pos + 25}" y="{y_pos + 85}" font-family="sans-serif" font-size="18" font-weight="bold" fill="{accent_color}">{item.get("price","")}</text>')
                    
                    # 작은 서브 이미지 표시 (업로드된 사진 매칭 혹은 자동 생성)
                    sub_img_idx = i + 1
                    sub_img_path = None
                    
                    if sub_img_idx < len(uploads) and os.path.exists(uploads[sub_img_idx]):
                        sub_img_path = uploads[sub_img_idx]
                    elif image_mode in ['ai_generate', 'reference_only'] and self.client:
                        # 이미지가 없고 AI 자동 생성이 허용된 경우, 품목별 미니 이미지 생성 시도
                        item_name = item.get("name","")
                        if item_name:
                            gen_path = str(self.workdir / f"item_gen_{i}.png")
                            if not os.path.exists(gen_path):
                                prompt = f"A single, centered, highly detailed, professional commercial food or product photography of '{item_name}'. Clean solid white background. No text. Studio lighting."
                                try:
                                    # 작은 이미지는 1:1 비율로 빠르게 생성 (품질 옵션은 간소화)
                                    self.client.generate_image(prompt=prompt, output_path=gen_path, aspect_ratio="1:1", resize_to=(200, 200), quality_suffix="")
                                    if os.path.exists(gen_path):
                                        sub_img_path = gen_path
                                except Exception as e:
                                    logger.error(f"Failed to generate sub-image for {item_name}: {e}")
                    
                    if sub_img_path:
                        try:
                            with open(sub_img_path, "rb") as sub_f:
                                sub_b64 = base64.b64encode(sub_f.read()).decode('utf-8')
                            sub_ext = os.path.splitext(sub_img_path)[1].lower()
                            sub_mime = "image/png" if sub_ext == ".png" else "image/jpeg"
                            sub_uri = f"data:{sub_mime};base64,{sub_b64}"
                            
                            clip_id = f"subClip{i}"
                            svg_parts.append(f'<defs><clipPath id="{clip_id}"><rect x="{x_pos + card_width - 80}" y="{y_pos + 10}" width="70" height="80" rx="8"/></clipPath></defs>')
                            svg_parts.append(f'<image x="{x_pos + card_width - 80}" y="{y_pos + 10}" width="70" height="80" preserveAspectRatio="xMidYMid slice" href="{sub_uri}" clip-path="url(#{clip_id})"/>')
                        except:
                            pass
                    else:
                        # 실패하거나 생성을 안 한 경우 플레이스홀더
                        svg_parts.append(f'<rect x="{x_pos + card_width - 80}" y="{y_pos + 10}" width="70" height="80" rx="8" fill="{bg_color}" opacity="0.5"/>')
                        svg_parts.append(f'<circle cx="{x_pos + card_width - 45}" cy="{y_pos + 50}" r="15" fill="#ffffff" opacity="0.8"/>')
                        
                rows = (len(items[:6]) + 1) // 2
                y_offset += (rows * (card_height + 20)) + 30

        # 4. Footer Area (Contact Info - Beautiful bottom block)
        footer_y = HEIGHT - 180
        
        svg_parts.append(f'<rect x="40" y="{footer_y}" width="720" height="140" rx="20" fill="{brand_color}" filter="url(#shadow)"/>')
        
        svg_parts.append(f'<text x="80" y="{footer_y + 50}" font-family="sans-serif" font-size="32" font-weight="900" fill="#ffffff">{biz_name}</text>')
        
        contact_y = footer_y + 80
        if contact.get('phone'): 
            svg_parts.append(f'<text x="80" y="{contact_y}" font-family="sans-serif" font-size="16" fill="#ffffff" opacity="0.9">📞 {contact["phone"]}</text>')
        if contact.get('hours'): 
            svg_parts.append(f'<text x="80" y="{contact_y + 25}" font-family="sans-serif" font-size="16" fill="#ffffff" opacity="0.9">⏰ {contact["hours"]}</text>')
        if contact.get('address'): 
            svg_parts.append(f'<text x="350" y="{contact_y}" font-family="sans-serif" font-size="16" fill="#ffffff" opacity="0.9">📍 {contact["address"]}</text>')
        
        # Decorative QR or Logo area
        svg_parts.append(f'<rect x="620" y="{footer_y + 20}" width="100" height="100" fill="#ffffff" rx="12" opacity="0.1"/>')
        svg_parts.append(f'<text x="670" y="{footer_y + 65}" font-family="sans-serif" font-size="14" font-weight="bold" fill="#ffffff" text-anchor="middle" opacity="0.8">SCAN ME</text>')
        svg_parts.append(f'<rect x="630" y="{footer_y + 80}" width="80" height="4" fill="#ffffff" rx="2" opacity="0.5"/>')
        svg_parts.append(f'<rect x="630" y="{footer_y + 90}" width="50" height="4" fill="#ffffff" rx="2" opacity="0.5"/>')

        svg_parts.append('</svg>')
        
        with open(self.workdir / 'leaflet.svg', 'w', encoding='utf-8') as f:
            f.write("\n".join(svg_parts))
            
        self.update_status(95, "최종 마무리", "[System] 고품질 매거진 스타일 SVG 파일 조립 완료.")

    def run(self):
        try:
            self.step_1_vision_analysis()
            self.step_2_copywriting()
            self.step_3_image_generation()
            self.step_4_svg_assembly()
            
            # 완료 상태 기록
            self.status_data["status"] = "completed"
            self.update_status(100, "제작 완료!", "[System] 전단지 팩토리 모든 프로세스가 성공적으로 종료되었습니다.")
            
        except Exception as e:
            self.status_data["status"] = "error"
            self.status_data["error_message"] = str(e)
            self.update_status(self.status_data["progress"], "오류 발생", f"[Error] {str(e)}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="V2 Leaflet Orchestrator")
    parser.add_argument("--workdir", required=True, help="Job Directory")
    args = parser.parse_args()
    
    orchestrator = LeafletOrchestrator(args.workdir)
    orchestrator.run()