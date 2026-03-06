#!/usr/bin/env python3
"""
AI 상세페이지 공장 — 범용 업종 버전
업종 + 상품명만 입력하면 13장 기승전결 시나리오를 AI가 설계하고 자동 생성

Usage:
  # 범용 (모든 업종)
  python3 ai_detail_page.py generate "음식점" "시그니처 파스타"
  python3 ai_detail_page.py generate "피트니스" "3개월 멤버십" luxury
  python3 ai_detail_page.py generate "학원" "수학 집중반"
  python3 ai_detail_page.py generate "보석" "18K 다이아 반지" luxury

  # 두손기획인쇄 내장 품목
  python3 ai_detail_page.py generate-builtin namecard
  python3 ai_detail_page.py generate-all-builtin

  # 공통
  python3 ai_detail_page.py copy-only "카페" "시그니처 라떼"
  python3 ai_detail_page.py images-only "피트니스" "3개월 멤버십"
  python3 ai_detail_page.py swap "피트니스" "3개월 멤버십"
  python3 ai_detail_page.py status-all
"""

import asyncio
import aiohttp
import json
import os
import re
import sys
import base64
import time
import shutil
from pathlib import Path
from datetime import datetime

# ─────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────
API_KEY      = os.environ.get('GEMINI_API_KEY', 'AIzaSyAEBMlGYm0cvBsBHMqaCmJRObFKEXN8jXs')
COPY_MODEL   = 'gemini-3-pro-preview'
IMAGE_MODEL  = 'gemini-3-pro-image-preview'
BASE_URL     = 'https://generativelanguage.googleapis.com/v1beta/models'
IMG_BASE_DIR = Path('/var/www/html/ImgFolder')
STAGING_DIR  = IMG_BASE_DIR / 'detail_page_staging'
LIVE_DIR     = IMG_BASE_DIR / 'detail_page'
VER_A_DIR    = IMG_BASE_DIR / 'detail_page_v_a'   # 기존 페이지
VER_B_DIR    = IMG_BASE_DIR / 'detail_page_v_b'   # AI 생성 페이지
AB_STATE     = Path('/var/www/html/scripts/ab_rotation.json')
MAX_PARALLEL = 2
COPY_TOKENS  = 2048

# ─────────────────────────────────────────
# 카피라이팅 품질 규칙 (공통)
# ─────────────────────────────────────────
COPY_RULES = """
[카피라이팅 품질 규칙 — 반드시 준수]
1. 반복 금지: 특정 숫자나 동일 표현을 여러 섹션에 반복 금지. 섹션마다 다른 각도로
2. 스펙 나열 금지: 사양 나열 대신 고객이 느끼는 가치/감정/결과로 표현
3. 기승전결 흐름: 기=문제공감 → 승=품질증명 → 전=활용/과정 → 결=신뢰/행동
4. 헤드라인: 읽는 순간 멈추게 하는 한 줄 (15자 이내)
5. prev_headlines와 겹치는 키워드/뉘앙스 절대 금지
6. 고객 언어: 실제 고객이 쓰는 자연스러운 표현
7. 과장 금지: "최고의", "완벽한" 대신 구체적 특징으로 설득
"""

# ─────────────────────────────────────────
# 팔레트 시스템
# ─────────────────────────────────────────
# (bg, headline, subtext, cta_bg, is_dark)
PALETTE_SETS = {
    'default': [
        ('#0d1b2a', '#e8c898', '#9ab0c8', '#e8c898', True),   # 1  엄숙·럭셔리
        ('#fff0f5', '#e0457a', '#c06090', '#e0457a', False),  # 2  발랄·감성
        ('#f0f8ff', '#2979c8', '#6090b8', '#2979c8', False),  # 3  청량·컬러풀
        ('#f0fff4', '#1a8a4a', '#4a9a6a', '#1a8a4a', False),  # 4  생동감·자연
        ('#fef4ff', '#8b35c8', '#a060c0', '#8b35c8', False),  # 5  세련·파스텔
        ('#fffbf0', '#d47000', '#b08030', '#d47000', False),  # 6  따뜻·활기
        ('#1a1208', '#f0c030', '#987830', '#f0c030', True),   # 7  엄숙·프리미엄
        ('#0f0f0f', '#ff6030', '#bb5030', '#ff6030', True),   # 8  강렬·인더스트리얼
        ('#fff8f0', '#c04828', '#a07050', '#c04828', False),  # 9  따뜻·에너지
        ('#f0f5ff', '#1a3a8a', '#4a6aa8', '#1a3a8a', False),  # 10 전문·신뢰
        ('#f5fff0', '#2a6a2a', '#5a8a5a', '#2a6a2a', False),  # 11 희망·자연
        ('#fff5fa', '#c02060', '#a05080', '#c02060', False),  # 12 감성·로맨틱
        ('#fafafa', '#111111', '#555555', '#111111', False),  # 13 클린·미니멀
    ],
    'luxury': [
        ('#0c0c10', '#d4af37', '#8a7428', '#d4af37', True),   # 1  딥블랙+순금
        ('#1a0a0a', '#e8d5b0', '#b09878', '#c8a84a', True),   # 2  다크와인+샴페인골드
        ('#f8f5f0', '#8a6a20', '#b09050', '#8a6a20', False),  # 3  아이보리+앤틱골드
        ('#0a0a18', '#c0c8e0', '#8090b0', '#9098c0', True),   # 4  딥네이비+실버
        ('#1a1a1a', '#e8e8e8', '#a0a0a0', '#c8c8c8', True),   # 5  건메탈+실버
        ('#fdfaf5', '#7a6040', '#a08060', '#7a6040', False),  # 6  크림+브론즈
        ('#080c14', '#b8d4f8', '#7090c0', '#4878c8', True),   # 7  다크+사파이어
        ('#0d0d0d', '#e8c8d8', '#b090a0', '#c87090', True),   # 8  블랙+로즈골드
        ('#f5f0f8', '#6a3a8a', '#9060b0', '#6a3a8a', False),  # 9  소프트+로열퍼플
        ('#0a1208', '#78c878', '#507850', '#3a8a3a', True),   # 10 딥그린+에메랄드
        ('#faf8f5', '#5a4a2a', '#8a7850', '#5a4a2a', False),  # 11 웜크림+브라운골드
        ('#18080c', '#f0b8c8', '#c08090', '#d87090', True),   # 12 버건디+로즈
        ('#f9f7f5', '#3a3028', '#706858', '#3a3028', False),  # 13 오프화이트+다크브라운
    ],
    'vivid': [
        ('#ff4757', '#ffffff', '#ffcccc', '#ffffff', True),   # 1  레드
        ('#ffa502', '#1a1a1a', '#553300', '#1a1a1a', False),  # 2  오렌지
        ('#2ed573', '#0a3020', '#1a5a30', '#0a3020', False),  # 3  그린
        ('#1e90ff', '#ffffff', '#ccddff', '#ffffff', True),   # 4  블루
        ('#a29bfe', '#1a1040', '#4a3a80', '#1a1040', False),  # 5  퍼플
        ('#fd79a8', '#3a0a20', '#8a3050', '#3a0a20', False),  # 6  핑크
        ('#fdcb6e', '#2a1a00', '#6a4a10', '#2a1a00', False),  # 7  옐로우
        ('#00cec9', '#001a18', '#004a48', '#001a18', True),   # 8  민트
        ('#e17055', '#ffffff', '#ffddd5', '#ffffff', True),   # 9  살몬
        ('#6c5ce7', '#ffffff', '#ddd8ff', '#ffffff', True),   # 10 바이올렛
        ('#00b894', '#ffffff', '#ccffe8', '#ffffff', True),   # 11 에메랄드
        ('#e84393', '#ffffff', '#ffccee', '#ffffff', True),   # 12 마젠타
        ('#2d3436', '#ffffff', '#aaaaaa', '#ffffff', True),   # 13 다크그레이
    ],
    'pastel': [
        ('#ffeef8', '#c0608a', '#e090b0', '#c0608a', False),  # 1  베이비핑크+딥핑크
        ('#eef8ff', '#5080c0', '#80a0d8', '#5080c0', False),  # 2  베이비블루+코발트
        ('#eefff4', '#408060', '#70a080', '#408060', False),  # 3  민트+포레스트
        ('#fffbee', '#c08020', '#d8a848', '#c08020', False),  # 4  버터+앰버
        ('#f4eeff', '#7040c0', '#9060d8', '#7040c0', False),  # 5  라벤더+퍼플
        ('#fff4ee', '#c05830', '#d88060', '#c05830', False),  # 6  피치+코랄
        ('#eefbff', '#3080a0', '#60a0c0', '#3080a0', False),  # 7  스카이+오션
        ('#ffeeff', '#a040a0', '#c070c0', '#a040a0', False),  # 8  핑크라벤더+마젠타
        ('#f0fff4', '#306848', '#60a878', '#306848', False),  # 9  세이지+포레스트
        ('#fff8ee', '#a06028', '#c08848', '#a06028', False),  # 10 크림+브라운
        ('#eef4ff', '#4058a0', '#7088c0', '#4058a0', False),  # 11 블루그레이+네이비
        ('#fff0f0', '#b04040', '#d07070', '#b04040', False),  # 12 로즈+크림슨
        ('#f8f8f8', '#404040', '#808080', '#404040', False),  # 13 소프트화이트+차콜
    ],
    'food': [
        ('#1a0800', '#e8a030', '#b07020', '#e8a030', True),   # 1  다크+황금색
        ('#fff8f0', '#c04820', '#e07040', '#c04820', False),  # 2  크림+토마토레드
        ('#f0fff8', '#1a6a40', '#408060', '#1a6a40', False),  # 3  화이트+허브그린
        ('#fdf5e6', '#8b4513', '#a06030', '#8b4513', False),  # 4  아이보리+브라운
        ('#fff0e8', '#d4480a', '#e07040', '#d4480a', False),  # 5  소프트+스파이시레드
        ('#f5ffe8', '#5a7a1a', '#80a040', '#5a7a1a', False),  # 6  라임그린+올리브
        ('#0a0a0a', '#f8c030', '#c09020', '#f8c030', True),   # 7  블랙+골든
        ('#fff5f0', '#c03818', '#e06040', '#c03818', False),  # 8  웜화이트+칠리
        ('#f8f0e8', '#7a5020', '#a07040', '#7a5020', False),  # 9  베이지+에스프레소
        ('#f0f8f0', '#2a6a2a', '#508050', '#2a6a2a', False),  # 10 민트+포레스트
        ('#fff8e8', '#c07818', '#e0a040', '#c07818', False),  # 11 크림+카라멜
        ('#faf0f5', '#c03878', '#e060a0', '#c03878', False),  # 12 소프트핑크+베리
        ('#fefefe', '#2a2a2a', '#606060', '#2a2a2a', False),  # 13 화이트+차콜
    ],
}

ORDER_KEYWORDS = ['제작', '주문', '신청', '바로가기', '시작', '발행', '예약', '등록', '구매', '상담']

# ─────────────────────────────────────────
# 두손기획인쇄 내장 품목 설정
# ─────────────────────────────────────────
BUILTIN_CONFIG = {
    'namecard':        {'name': '명함',     'order_url': '/mlangprintauto/namecard/',        'deadline': (11, 0),  'palette': 'default'},
    'sticker_new':     {'name': '스티커',   'order_url': '/mlangprintauto/sticker_new/',     'deadline': None,     'palette': 'default'},
    'msticker':        {'name': '자석스티커','order_url': '/mlangprintauto/msticker/',       'deadline': None,     'palette': 'default'},
    'inserted':        {'name': '전단지',   'order_url': '/mlangprintauto/inserted/',        'deadline': None,     'palette': 'default'},
    'envelope':        {'name': '봉투',     'order_url': '/mlangprintauto/envelope/',        'deadline': None,     'palette': 'default'},
    'littleprint':     {'name': '포스터',   'order_url': '/mlangprintauto/littleprint/',     'deadline': None,     'palette': 'default'},
    'merchandisebond': {'name': '상품권',   'order_url': '/mlangprintauto/merchandisebond/', 'deadline': (11, 30), 'palette': 'default'},
    'cadarok':         {'name': '카다록',   'order_url': '/mlangprintauto/cadarok/',         'deadline': None,     'palette': 'default'},
    'ncrflambeau':     {'name': 'NCR양식지','order_url': '/mlangprintauto/ncrflambeau/',     'deadline': None,     'palette': 'default'},
}
BUILTIN_CONFIG['namecard']['palette_override'] = {0: ('#0d1b2a', '#f97316', '#9ab0c8', '#f97316', True)}

# ─────────────────────────────────────────
# 내장 시나리오 (두손기획인쇄 9개 품목)
# ─────────────────────────────────────────
BUILTIN_SCENARIOS = {
    'namecard': [
        {'section':1,  'arc':'기', 'theme':'첫인상의 결정적 순간',
         'prompt':'Close-up cinematic: Two businesspeople exchanging business cards in modern Seoul office. Focus on hands extending a premium matte card. Warm afternoon light, city skyline bokeh. Photorealistic, no text overlays.',
         'copy_guide':'비즈니스 첫 만남, 명함이 당신을 먼저 말합니다.'},
        {'section':2,  'arc':'기', 'theme':'손 안의 무게감',
         'prompt':'Macro: Premium business card on open palm. Golden hour light reveals ultra-fine linen texture. Softly blurred coffee shop background. Jewel-like detail.',
         'copy_guide':'손끝으로 전달되는 신뢰. 용지의 질감이 브랜드를 이야기합니다.'},
        {'section':3,  'arc':'기', 'theme':'기억에 남는 명함',
         'prompt':'Top-down flat lay: Desk scattered with plain cards, one distinctive premium card stands out. Hand reaches to pick it up. Storytelling composition.',
         'copy_guide':'수십 장 명함 중 당신의 명함은 선택받습니까?'},
        {'section':4,  'arc':'승', 'theme':'용지 컬렉션',
         'prompt':'Fan arrangement: Diverse paper samples fanned in arc — matte, cream laid, black, pearl shimmer, kraft. Hand touches one sample. Soft diffused studio lighting.',
         'copy_guide':'일반지부터 수입 고급지까지, 원하는 용지로 브랜드를 완성하세요.'},
        {'section':5,  'arc':'승', 'theme':'코팅의 예술',
         'prompt':'Split comparison: Two cards — left glossy UV reflecting like mirror, right soft-touch matte. Elegant hands hold at angles. Jewelry store lighting.',
         'copy_guide':'유광의 선명함, 무광의 고요함. 코팅 하나로 분위기가 달라집니다.'},
        {'section':6,  'arc':'승', 'theme':'인쇄 정밀도',
         'prompt':'Extreme macro: Business card showing razor-sharp typography. Jeweler\'s loupe partially in frame. Clinical yet beautiful, like luxury watch advertisement.',
         'copy_guide':'잉크 한 점까지, 오차 없이 정확하게.'},
        {'section':7,  'arc':'승', 'theme':'금박·형압 후가공',
         'prompt':'Dramatic single-beam: Luxury card with gold foil and embossing catching warm spotlight. Gold gleams, embossed letters cast shadows. Dark charcoal background.',
         'copy_guide':'형압의 입체감, 금박의 광채. 명함을 럭셔리 브랜드 경험으로.'},
        {'section':8,  'arc':'전', 'theme':'당일 인쇄 공정',
         'prompt':'Industrial cinematic: Modern Korean printing facility. Offset machine in motion, stream of freshly printed cards emerging. Technician monitors quality. Motion blur suggests speed.',
         'copy_guide':'오전에 주문하면 오늘 저녁 손에 쥡니다.'},
        {'section':9,  'arc':'전', 'theme':'11시 마감 빠른 주문',
         'prompt':'Dynamic lifestyle: Young Korean entrepreneur at cafe placing order on smartphone showing 10:47 AM. Morning golden light.',
         'copy_guide':'오전 11시 전 주문하면 당일 출고. 갑작스러운 미팅도 준비됩니다.'},
        {'section':10, 'arc':'전', 'theme':'다양한 전문직 활용',
         'prompt':'Triptych: Doctor giving card in hospital, architect exchanging at blueprint table, creative director at agency pitch. Warm professional Korean settings.',
         'copy_guide':'모든 전문직의 신뢰는 명함에서 시작됩니다.'},
        {'section':11, 'arc':'전', 'theme':'명함 보관과 소장',
         'prompt':'Still life: Elegant leather card holder on mahogany desk, premium cards arranged. Warm afternoon light through blinds. Expensive pen and notebook.',
         'copy_guide':'버리지 않는 명함이 진짜 명함입니다.'},
        {'section':12, 'arc':'결', 'theme':'고객 만족의 순간',
         'prompt':'Warm storytelling: Business meeting — person just received premium card, visibly impressed. Card giver looks confident. Coffee cups, natural light.',
         'copy_guide':'"어디서 하셨어요?" 감탄이 대화를 시작합니다.'},
        {'section':13, 'arc':'결', 'theme':'지금 시작하기',
         'prompt':'Clean aspirational: Fresh stack of crisp premium cards in stylish fan on minimal white surface. Soft morning light. Smartphone beside suggests easy ordering.',
         'copy_guide':'지금 바로 주문하세요. 오늘의 명함이 내일의 비즈니스를 만듭니다.'},
    ],
    'sticker_new': [
        {'section':1,'arc':'기','theme':'브랜드가 붙는 순간','prompt':'Cinematic close-up: Hands carefully applying beautifully designed custom sticker to premium kraft packaging. Satisfying moment of application. Warm studio light.','copy_guide':'붙이는 순간, 브랜드가 완성됩니다.'},
        {'section':2,'arc':'기','theme':'싸구려 스티커의 문제','prompt':'Contrast: Left side cheap sticker peeling off, faded colors. Right side premium sticker perfectly applied, vivid colors, clean die-cut. Side-by-side comparison.','copy_guide':'벗겨지는 스티커는 브랜드를 같이 벗겨냅니다.'},
        {'section':3,'arc':'기','theme':'스티커가 만드는 첫인상','prompt':'Flat lay: Various beautifully branded products — coffee cup, candle jar, packaging box — unified by matching custom stickers. Minimal white surface.','copy_guide':'작은 스티커 하나가 브랜드를 하나로 묶습니다.'},
        {'section':4,'arc':'승','theme':'11종 용지의 선택','prompt':'Product display: 11 sticker material samples in clean grid — transparent PET, white vinyl, kraft, holographic, matte, glossy. Studio lighting emphasizes differences.','copy_guide':'투명부터 홀로그램까지, 소재가 달라지면 메시지가 달라집니다.'},
        {'section':5,'arc':'승','theme':'방수 내구성','prompt':'Macro: Water droplets beading perfectly on sticker surface, vivid colors intact underneath. Dramatic macro lighting. Proof of waterproof quality.','copy_guide':'물에 젖어도, 햇빛에 바래도. 인쇄는 그대로입니다.'},
        {'section':6,'arc':'승','theme':'강력 접착력','prompt':'Close-up: Sticker firmly applied to curved glass bottle surface, no lifting, no bubbles. Another hand tries to peel and cannot.','copy_guide':'한 번 붙으면 끝까지 함께.'},
        {'section':7,'arc':'승','theme':'자유 칼선','prompt':'Top-down: Various die-cut sticker shapes — round, oval, star, custom shape, kiss-cut sheet. Each shape clean and precise. White background.','copy_guide':'원형, 별형, 자유형. 브랜드 모양 그대로 잘라냅니다.'},
        {'section':8,'arc':'전','theme':'패키지 브랜딩','prompt':'Lifestyle: Artisan food products — jam jars, honey, handmade soap — beautifully branded with custom label stickers. Warm kitchen background.','copy_guide':'핸드메이드 제품의 품격을 완성하는 브랜드 라벨.'},
        {'section':9,'arc':'전','theme':'카페·음식점 활용','prompt':'Warm lifestyle: Barista applying branded sticker seal to takeout coffee cup. Cozy Korean cafe environment, morning golden light.','copy_guide':'테이크아웃 한 잔에도, 브랜드는 따라갑니다.'},
        {'section':10,'arc':'전','theme':'다양한 활용','prompt':'Dynamic collage: Stickers on laptop, packaging, event badges, bottle labels, envelope sealing. Shows versatility.','copy_guide':'어디에 붙여도, 브랜드는 일관됩니다.'},
        {'section':11,'arc':'전','theme':'인쇄 공정','prompt':'Industrial close-up: Modern precision sticker printing machine producing perfectly cut sheets. Clean controlled environment.','copy_guide':'정밀한 기계가 만드는 깔끔한 칼선, 선명한 색감.'},
        {'section':12,'arc':'결','theme':'브랜드 성장','prompt':'Warm success: Korean small business owner arranging beautifully branded products for online store photoshoot. Pride visible.','copy_guide':'스티커 하나로 달라진 브랜드가 매출을 바꿉니다.'},
        {'section':13,'arc':'결','theme':'지금 제작하기','prompt':'Clean hero: Perfect roll of custom stickers partially unrolled on minimal white surface. Crisp, vibrant, precise cuts.','copy_guide':'지금 바로 제작하세요. 당신의 브랜드를 스티커에 담습니다.'},
    ],
    # 나머지 7개 품목은 scenarios_for_builtin()에서 AI로 생성
}

# ─────────────────────────────────────────
# UTILS
# ─────────────────────────────────────────
def log(msg: str):
    print(f'[{datetime.now().strftime("%H:%M:%S")}] {msg}', flush=True)


def slugify(text: str) -> str:
    """폴더명 안전 변환"""
    text = re.sub(r'[^\w\s가-힣]', '', text).strip()
    return re.sub(r'\s+', '_', text)


def get_folder_name(industry: str, product: str) -> str:
    return f'{slugify(industry)}_{slugify(product)}'


def extract_json(text: str):
    """마크다운/여분 텍스트에서 JSON 추출 (배열 또는 객체)"""
    text = text.strip()
    try:
        return json.loads(text)
    except Exception:
        pass
    m = re.search(r'''```(?:json)?\s*([\[{].*?)\s*```''', text, re.DOTALL)
    if m:
        try:
            return json.loads(m.group(1))
        except Exception:
            pass
    for start_char, end_char in [('[', ']'), ('{', '}')]:
        s = text.find(start_char)
        e = text.rfind(end_char)
        if s != -1 and e != -1 and e > s:
            try:
                return json.loads(text[s:e+1])
            except Exception:
                pass
    raise ValueError(f'JSON 추출 실패: {text[:100]}')


def get_palettes(style: str, product_code: str = '') -> list:
    palettes = list(PALETTE_SETS.get(style, PALETTE_SETS['default']))
    # 내장 품목 오버라이드 적용
    cfg = BUILTIN_CONFIG.get(product_code, {})
    for idx, override in cfg.get('palette_override', {}).items():
        palettes[idx] = override
    return palettes


def ensure_dirs(folder: str) -> tuple[Path, Path]:
    s = STAGING_DIR / folder
    l = LIVE_DIR / folder
    s.mkdir(parents=True, exist_ok=True)
    l.mkdir(parents=True, exist_ok=True)
    return s, l


# ─────────────────────────────────────────
# AI: 상품 정보 자동 생성
# ─────────────────────────────────────────
async def gen_product_info(session, industry: str, product: str) -> dict:
    prompt = f"""업종: {industry}
상품/서비스명: {product}

위 상품의 마케팅 정보를 분석하여 JSON으로 반환하세요.

{{
  "name": "상품명",
  "desc": "핵심 설명 1-2줄",
  "features": ["핵심 특징 1", "특징 2", "특징 3", "특징 4", "특징 5"],
  "delivery": "제공 방식 또는 배송 정보",
  "usecases": ["주요 활용 상황 1", "상황 2", "상황 3", "상황 4"],
  "target": "주요 타겟 고객층",
  "price_range": "가격대 또는 특징"
}}"""

    url = f'{BASE_URL}/{COPY_MODEL}:generateContent?key={API_KEY}'
    payload = {
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {'responseMimeType': 'application/json', 'maxOutputTokens': 2048},
    }
    try:
        async with session.post(url, json=payload, timeout=aiohttp.ClientTimeout(total=60)) as resp:
            data = await resp.json()
            text = data['candidates'][0]['content']['parts'][0]['text'].strip()
            return extract_json(text)
    except Exception as e:
        log(f'  상품 정보 생성 실패: {e}')
        return {'name': product, 'desc': f'{industry} {product}', 'features': [], 'delivery': '', 'usecases': [], 'target': '', 'price_range': ''}


# ─────────────────────────────────────────
# AI: 13개 기승전결 시나리오 자동 설계
# ─────────────────────────────────────────
async def gen_scenarios(session, industry: str, product: str, info: dict) -> list:
    prompt = f"""업종: {industry}
상품명: {product}
설명: {info.get('desc', '')}
특징: {', '.join(info.get('features', []))}
타겟: {info.get('target', '')}
활용: {', '.join(info.get('usecases', []))}

이 상품의 상세페이지용 13장 기승전결 시나리오를 설계하세요.

기승전결 구조:
- 기(起) 1-3: 고객이 공감하는 문제·욕구·상황 → 감성적 진입
- 승(承) 4-7: 상품의 핵심 강점·품질·차별점 시각적 증명
- 전(轉) 8-11: 실제 사용 장면·제작/서비스 과정·다양한 활용
- 결(結) 12-13: 사회적 증명(고객 만족)·강력한 행동 유도

각 섹션 요구사항:
- prompt: 영문 이미지 생성 프롬프트 (2-3문장, 사진작가 지시문 스타일, photorealistic, commercial photography)
- copy_guide: 카피 방향 한 줄 (한국어)
- 이미지는 실제 사람이 등장하는 생동감 있는 장면 위주

JSON 배열로만 응답:
[
  {{"section":1,"arc":"기","theme":"테마명10자이내","prompt":"English prompt...","copy_guide":"한국어 카피 방향"}},
  ...총 13개
]"""

    url = f'{BASE_URL}/{COPY_MODEL}:generateContent?key={API_KEY}'
    payload = {
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {
            'responseMimeType': 'application/json',
            'maxOutputTokens': 6000,
            'temperature': 0.9,
        },
    }
    try:
        async with session.post(url, json=payload, timeout=aiohttp.ClientTimeout(total=180)) as resp:
            data = await resp.json()
            text = data['candidates'][0]['content']['parts'][0]['text'].strip()
            scenarios = extract_json(text)
            log(f'  시나리오 {len(scenarios)}개 설계 완료')
            return scenarios
    except Exception as e:
        log(f'  시나리오 생성 실패: {e}')
        return []


# ─────────────────────────────────────────
# COPY GENERATION — 순차 + 반복방지
# ─────────────────────────────────────────
async def gen_copy(session, info: dict, idx: int, scenario: dict, prev_headlines: list) -> dict:
    arc_map = {'기': '기-문제공감', '승': '승-품질증명', '전': '전-활용과정', '결': '결-신뢰행동'}
    prev_block = ''
    if prev_headlines:
        prev_block = '\n[이미 사용된 헤드라인 — 반드시 다른 표현 사용]\n' + '\n'.join(f'- {h}' for h in prev_headlines)

    prompt = f"""상품: {info.get('name', '')} ({info.get('desc', '')})
특징: {', '.join(info.get('features', []))}
타겟: {info.get('target', '')}

섹션 {idx+1}/13:
- 위치: {arc_map.get(scenario.get('arc',''), scenario.get('arc',''))}
- 테마: {scenario.get('theme', '')}
- 이미지: {scenario.get('copy_guide', '')}

{COPY_RULES}{prev_block}

JSON으로만 응답:
{{"headline": "15자이내 강렬한 헤드라인", "subtext": "2-3문장 구체적 설명", "cta": "10자이내 행동유도(없으면 빈문자열)"}}"""

    url = f'{BASE_URL}/{COPY_MODEL}:generateContent?key={API_KEY}'
    payload = {
        'contents': [{'parts': [{'text': prompt}]}],
        'generationConfig': {'responseMimeType': 'application/json', 'maxOutputTokens': COPY_TOKENS, 'temperature': 0.85},
    }
    try:
        async with session.post(url, json=payload, timeout=aiohttp.ClientTimeout(total=90)) as resp:
            data = await resp.json()
            text = data['candidates'][0]['content']['parts'][0]['text'].strip()
            return json.loads(text)
    except Exception as e:
        log(f'  [COPY {idx+1:02d}] fallback: {e}')
        guide = scenario.get('copy_guide', '')
        return {'headline': guide.split('.')[0][:15], 'subtext': guide, 'cta': ''}


# ─────────────────────────────────────────
# IMAGE GENERATION — 병렬
# ─────────────────────────────────────────
async def gen_image(session, scenario: dict, staging_dir: Path, sem: asyncio.Semaphore) -> dict:
    async with sem:
        num   = scenario['section']
        fname = f'section_{num:02d}.jpg'
        path  = staging_dir / fname
        log(f'  [IMG {num:02d}] 시작 — {scenario.get("theme", "")}')

        full_prompt = (
            'High-quality product detail page image. '
            'Style: Commercial photography, photorealistic, professional marketing. '
            'Aspect ratio: 3:4 vertical portrait for e-commerce detail page. '
            f'{scenario.get("prompt", "")} '
            'No text overlays, no watermarks. Sharp focus, professional color grading.'
        )

        url = f'{BASE_URL}/{IMAGE_MODEL}:generateContent?key={API_KEY}'
        payload = {
            'contents': [{'parts': [{'text': full_prompt}]}],
            'generationConfig': {'responseModalities': ['IMAGE'], 'maxOutputTokens': 4096},
        }
        # 재시도 3회 (Rate Limit 대응)
        for attempt in range(3):
            try:
                if attempt > 0:
                    wait = 15 * attempt
                    log(f'  [IMG {num:02d}] 재시도 {attempt+1}/3 ({wait}초 대기)...')
                    await asyncio.sleep(wait)
                async with session.post(url, json=payload, timeout=aiohttp.ClientTimeout(total=180)) as resp:
                    data = await resp.json()
                    # Rate limit 응답 체크
                    if resp.status == 429 or 'error' in data:
                        err_msg = data.get('error', {}).get('message', str(resp.status))
                        log(f'  [IMG {num:02d}] API 오류({resp.status}): {err_msg[:60]}')
                        continue
                    for part in data['candidates'][0]['content']['parts']:
                        if 'inlineData' in part:
                            img = base64.b64decode(part['inlineData']['data'])
                            path.write_bytes(img)
                            log(f'  [IMG {num:02d}] 완료 — {len(img)//1024}KB')
                            return {'section': num, 'status': 'ok'}
                    return {'section': num, 'status': 'no_image'}
            except Exception as e:
                log(f'  [IMG {num:02d}] 오류: {e}')
                if attempt == 2:
                    return {'section': num, 'status': 'error', 'error': str(e)}
        return {'section': num, 'status': 'error', 'error': 'max_retries'}


# ─────────────────────────────────────────
# HTML BUILDER
# ─────────────────────────────────────────
def build_html(data: dict, style: str = 'default', order_url: str = '#order',
               deadline: tuple = None, deadline_label: str = '', product_code: str = '') -> str:
    palettes     = get_palettes(style, product_code)
    name         = data.get('product_name', data.get('product', ''))
    industry     = data.get('industry', '')
    gen_at       = data.get('generated_at', '')[:16].replace('T', ' ')

    def deadline_js():
        if not deadline:
            return 'var isOpen=true;'
        h, m = deadline
        return f'var n=new Date();var isOpen=(n.getHours()<{h}||(n.getHours()=={h}&&n.getMinutes()<{m}));'

    def is_deadline_cta(cta: str) -> bool:
        return deadline is not None and ('당일' in cta or '바로가기' in cta or '신청' in cta)

    sections_html = ''
    dl_ids = []

    for i, s in enumerate(data.get('sections', [])):
        c        = s.get('copy', {})
        num      = s.get('section', i + 1)
        headline = c.get('headline', '')
        subtext  = c.get('subtext', '')
        cta      = c.get('cta', '')
        theme    = s.get('theme', '')

        bg, h_color, sub_color, cta_bg, is_dark = palettes[i] if i < len(palettes) else palettes[-1]
        row_style = 'flex-direction:row-reverse;' if i % 2 == 1 else ''

        extra = ''
        if cta:
            if is_deadline_cta(cta):
                did = f's{num}'
                dl_ids.append(did)
                extra = (f'<div><a class="cta-btn" id="{did}-open" href="{order_url}" '
                         f'style="background:{cta_bg};color:#fff;">{cta} →</a>'
                         f'<span class="cta-closed" id="{did}-closed" style="display:none;background:#666;color:#fff;">'
                         f'오늘 {deadline_label} <small>내일 다시 이용해주세요</small></span></div>')
            elif any(kw in cta for kw in ORDER_KEYWORDS):
                extra = f'<a class="cta-btn" href="{order_url}" style="background:{cta_bg};color:#fff;">{cta} →</a>'

        sections_html += f'''
<section class="section" style="background:{bg};{row_style}">
  <div class="img"><img src="section_{num:02d}.jpg" alt="{theme}" loading="lazy"></div>
  <div class="txt">
    <h2 style="color:{h_color};">{headline}</h2>
    <p style="color:{sub_color};">{subtext}</p>
    {extra}
  </div>
</section>'''

    dl_js = ''
    if dl_ids:
        dl_js = f'<script>(function(){{{deadline_js()}\n'
        for did in dl_ids:
            dl_js += (f'var o=document.getElementById("{did}-open"),c=document.getElementById("{did}-closed");'
                      f'if(o&&c){{o.style.display=isOpen?"inline-block":"none";c.style.display=isOpen?"none":"inline-flex";}}\n')
        dl_js += '})();</script>'

    title = f'{industry} {name}' if industry else name

    return f'''<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{title} 상세페이지</title>
<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css" rel="stylesheet">
<style>
*{{box-sizing:border-box;margin:0;padding:0;}}
body{{font-family:'Pretendard','Apple SD Gothic Neo',sans-serif;background:#c8c8c8;}}
.wrap{{width:1100px;margin:0 auto;padding:40px 0 80px;}}
.meta{{font-size:12px;color:#999;margin-bottom:8px;}}
.meta strong{{font-size:16px;font-weight:800;color:#333;margin-right:8px;}}
.sections{{border-radius:20px;overflow:hidden;box-shadow:0 8px 48px rgba(0,0,0,.18);}}
.section{{display:flex;width:1100px;min-height:500px;}}
.section .img{{flex:0 0 540px;width:540px;}}
.section .img img{{width:100%;height:100%;object-fit:cover;display:block;}}
.section .txt{{flex:1;padding:60px 56px;display:flex;flex-direction:column;justify-content:center;}}
h2{{font-size:68px;font-weight:900;line-height:1.1;letter-spacing:-.04em;word-break:keep-all;margin-bottom:24px;}}
.txt p{{font-size:15px;font-weight:400;line-height:1.9;word-break:keep-all;}}
.cta-btn{{display:inline-block;margin-top:32px;padding:14px 28px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:.02em;align-self:flex-start;text-decoration:none;opacity:.92;transition:opacity .2s,transform .15s;}}
.cta-btn:hover{{opacity:1;transform:translateX(3px);}}
.cta-closed{{display:inline-flex;align-items:center;gap:8px;margin-top:32px;padding:14px 28px;border-radius:8px;font-size:13px;font-weight:700;cursor:default;opacity:.8;}}
.cta-closed small{{font-size:11px;font-weight:400;opacity:.75;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="meta"><strong>{title}</strong>{industry and f"업종: {industry} · " or ""}생성: {gen_at}</div>
  <div class="sections">{sections_html}</div>
</div>
{dl_js}
</body>
</html>'''


# ─────────────────────────────────────────
# CORE PIPELINE
# ─────────────────────────────────────────
async def run_pipeline(industry: str, product: str, mode: str = 'both',
                       style: str = 'default', order_url: str = '#order',
                       deadline=None, deadline_label: str = '',
                       product_code: str = '', scenarios: list = None):
    folder       = product_code if product_code else get_folder_name(industry, product)
    staging_dir, _ = ensure_dirs(folder)

    log(f'=== {industry} / {product} [{style}] ===')
    t0 = time.time()

    async with aiohttp.ClientSession() as session:

        # 상품 정보
        if product_code and product_code in BUILTIN_CONFIG:
            info = {'name': BUILTIN_CONFIG[product_code]['name'], 'features': [], 'desc': product, 'usecases': [], 'target': ''}
        else:
            log('상품 정보 분석 중...')
            info = await gen_product_info(session, industry, product)

        # 시나리오 — images 전용 모드면 copy.json에서 먼저 읽기
        if scenarios is None:
            copy_json_path = staging_dir / 'copy.json'
            if mode == 'images' and copy_json_path.exists():
                try:
                    saved = json.loads(copy_json_path.read_text())
                    scenarios = saved.get('sections', [])
                    log(f'기존 copy.json에서 시나리오 {len(scenarios)}개 로드')
                except Exception:
                    scenarios = None
            if not scenarios:
                log('기승전결 시나리오 설계 중...')
                scenarios = await gen_scenarios(session, industry, product, info)
                if not scenarios:
                    log('시나리오 생성 실패'); return

        # 카피 (순차, 반복방지)
        copy_results = []
        if mode in ('both', 'copy'):
            log(f'\n카피 생성 ({len(scenarios)}개 섹션, 반복방지 순차)...')
            prev: list[str] = []
            for i, sc in enumerate(scenarios):
                r = await gen_copy(session, info, i, sc, prev)
                copy_results.append(r)
                prev.append(r.get('headline', ''))
                log(f'  [COPY {i+1:02d}] {r.get("headline","?")}')

            copy_data = {
                'industry': industry, 'product_name': product,
                'product_code': product_code,
                'generated_at': datetime.now().isoformat(),
                'copy_model': COPY_MODEL, 'style': style,
                'sections': [{**sc, 'copy': cr} for sc, cr in zip(scenarios, copy_results)],
            }
            (staging_dir / 'copy.json').write_text(json.dumps(copy_data, ensure_ascii=False, indent=2))

            html = build_html(copy_data, style, order_url, deadline, deadline_label, product_code)
            (staging_dir / 'detail.html').write_text(html)
            log(f'HTML 저장: {staging_dir}/detail.html')

        # 이미지 (병렬)
        if mode in ('both', 'images'):
            log(f'\n이미지 생성 (최대 {MAX_PARALLEL}개 병렬)...')
            sem  = asyncio.Semaphore(MAX_PARALLEL)
            jobs = [gen_image(session, sc, staging_dir, sem) for sc in scenarios]
            results = await asyncio.gather(*jobs)
            ok = sum(1 for r in results if r.get('status') == 'ok')
            log(f'이미지: {ok}/{len(scenarios)}개 성공')

    log(f'\n완료: {time.time()-t0:.1f}초')
    log(f'미리보기: http://localhost/ImgFolder/detail_page_staging/{folder}/detail.html')
    log(f'적용: python3 {sys.argv[0]} swap "{industry}" "{product}"')


# ─────────────────────────────────────────
# COMMANDS
# ─────────────────────────────────────────
async def cmd_generate(industry: str, product: str, mode: str = 'both', style: str = 'default'):
    """범용 업종 생성"""
    await run_pipeline(industry, product, mode, style)


async def cmd_generate_builtin(product_code: str, mode: str = 'both', style: str = None):
    """두손기획인쇄 내장 품목 생성"""
    if product_code not in BUILTIN_CONFIG:
        print(f'알 수 없는 내장 품목: {product_code}')
        print(f'가능: {", ".join(BUILTIN_CONFIG.keys())}')
        return
    cfg      = BUILTIN_CONFIG[product_code]
    _style   = style or cfg.get('palette', 'default')
    scenarios = BUILTIN_SCENARIOS.get(product_code)  # None이면 AI 생성
    await run_pipeline(
        industry='인쇄', product=cfg['name'], mode=mode, style=_style,
        order_url=cfg['order_url'], deadline=cfg.get('deadline'),
        deadline_label=cfg.get('deadline_label', ''),
        product_code=product_code, scenarios=scenarios,
    )


async def cmd_generate_all_builtin(mode: str = 'both'):
    """3개 품목씩 병렬 실행 (~10분으로 단축)"""
    sem = asyncio.Semaphore(3)

    async def run_one(code):
        async with sem:
            log(f'\n{"="*50}')
            log(f'[병렬] {code} 시작...')
            await cmd_generate_builtin(code, mode)
            log(f'[병렬] {code} 완료!')

    await asyncio.gather(*[run_one(code) for code in BUILTIN_CONFIG])
    log(f'\n{"="*50}')
    log(f'전체 {len(BUILTIN_CONFIG)}개 품목 완료!')


async def cmd_swap(industry: str, product: str):
    folder = get_folder_name(industry, product)
    s_dir  = STAGING_DIR / folder
    l_dir  = LIVE_DIR / folder
    if not s_dir.exists():
        print(f'Staging 없음: {s_dir}'); return
    files = list(s_dir.glob('section_*.jpg'))
    if not files:
        print('이미지 없음'); return
    l_dir.mkdir(parents=True, exist_ok=True)
    for f in files:
        shutil.copy2(f, l_dir / f.name)
    for extra in ['copy.json', 'detail.html']:
        src = s_dir / extra
        if src.exists():
            shutil.copy2(src, l_dir / extra)
    log(f'Swap 완료: {folder} {len(files)}개')


async def cmd_swap_builtin(product_code: str):
    s_dir = STAGING_DIR / product_code
    l_dir = LIVE_DIR / product_code
    if not s_dir.exists():
        print(f'Staging 없음'); return
    files = list(s_dir.glob('section_*.jpg'))
    l_dir.mkdir(parents=True, exist_ok=True)
    for f in files:
        shutil.copy2(f, l_dir / f.name)
    for extra in ['copy.json', 'detail.html']:
        src = s_dir / extra
        if src.exists():
            shutil.copy2(src, l_dir / extra)
    log(f'Swap 완료: {product_code} {len(files)}개')


async def cmd_status_all():
    print()
    # 내장 품목
    print('[ 두손기획인쇄 내장 품목 ]')
    for code, cfg in BUILTIN_CONFIG.items():
        s_dir = STAGING_DIR / code
        files = list(s_dir.glob('section_*.jpg')) if s_dir.exists() else []
        mt    = f'({datetime.fromtimestamp(max(f.stat().st_mtime for f in files)).strftime("%m/%d %H:%M")})' if files else ''
        print(f'  {cfg["name"]:8s} ({code}): staging={len(files)}개{mt}')
    # 커스텀 품목
    custom_dirs = [d for d in STAGING_DIR.iterdir() if d.is_dir() and d.name not in BUILTIN_CONFIG] if STAGING_DIR.exists() else []
    if custom_dirs:
        print('\n[ 커스텀 생성 품목 ]')
        for d in sorted(custom_dirs):
            files = list(d.glob('section_*.jpg'))
            mt    = f'({datetime.fromtimestamp(max(f.stat().st_mtime for f in files)).strftime("%m/%d %H:%M")})' if files else ''
            print(f'  {d.name}: {len(files)}개{mt}')
    print()




# ─────────────────────────────────────────
# 주간 로테이션 시스템
# ─────────────────────────────────────────
ROTATION_STATE = Path('/var/www/html/scripts/detail_page_rotation.json')

def load_rotation_state() -> dict:
    if ROTATION_STATE.exists():
        with open(ROTATION_STATE) as f:
            return json.load(f)
    # 초기 상태: 전체 품목 큐에 넣기
    codes = list(BUILTIN_CONFIG.keys())
    return {'queue': codes, 'rotated': [], 'cycle': 1, 'last_rotate': None, 'history': []}

def save_rotation_state(state: dict):
    with open(ROTATION_STATE, 'w', encoding='utf-8') as f:
        json.dump(state, f, ensure_ascii=False, indent=2)

async def cmd_rotate(count: int = 2):
    """매주 N개 품목을 staging → live로 교체"""
    from datetime import date
    state = load_rotation_state()

    # 큐가 비었으면 다음 사이클 시작 (전체 재생성 권고)
    if not state['queue']:
        print(f'\n✅ 사이클 {state["cycle"]} 완료! 모든 품목이 교체되었습니다.')
        print('다음 사이클을 위해 generate-all-builtin 실행 후 rotate를 다시 사용하세요.')
        print(f'교체 이력: {state["history"]}')
        return

    # 이번 주 교체 대상 선택
    targets = state['queue'][:count]
    state['queue'] = state['queue'][count:]

    today = date.today().isoformat()
    log(f'\n[주간 로테이션] 사이클 {state["cycle"]} — {today}')
    log(f'이번 주 교체 대상: {", ".join(targets)}')

    swapped = []
    for code in targets:
        cfg = BUILTIN_CONFIG[code]
        s_dir = STAGING_DIR / code
        l_dir = LIVE_DIR / code

        # staging에 파일이 있는지 확인
        files = list(s_dir.glob('section_*.jpg')) if s_dir.exists() else []
        if not files:
            log(f'  ⚠️  {code}: staging 이미지 없음 → 건너뜀 (generate-builtin {code} 먼저 실행)')
            state['queue'].insert(0, code)  # 다음 주로 미룸
            continue

        # swap 실행
        l_dir.mkdir(parents=True, exist_ok=True)
        for f in files:
            shutil.copy2(f, l_dir / f.name)
        for extra in ['copy.json', 'detail.html']:
            src = s_dir / extra
            if src.exists():
                shutil.copy2(src, l_dir / extra)

        log(f'  ✅ {cfg["name"]} ({code}): {len(files)}개 이미지 교체 완료')
        swapped.append(code)

    state['rotated'].extend(swapped)
    state['last_rotate'] = today
    state['history'].append({'date': today, 'swapped': swapped, 'cycle': state['cycle']})

    # 사이클 완료 체크
    if not state['queue']:
        log(f'\n🎉 사이클 {state["cycle"]} 완료! 9개 품목 전체 교체됨.')
        state['cycle'] += 1
        state['queue'] = list(BUILTIN_CONFIG.keys())  # 다음 사이클 준비
        state['rotated'] = []

    save_rotation_state(state)

    remaining = len(state["queue"])
    log(f'\n남은 큐: {state["queue"]} ({remaining}개 품목, 약 {remaining//count}주 후 완료)')
    log(f'미리보기: http://localhost/ImgFolder/detail_page/{targets[0]}/detail.html')

async def cmd_rotate_status():
    """로테이션 상태 확인"""
    state = load_rotation_state()
    print(f'\n[ 주간 로테이션 상태 ]')
    print(f'  현재 사이클: {state["cycle"]}')
    print(f'  마지막 교체: {state.get("last_rotate", "없음")}')
    print(f'  남은 큐 ({len(state["queue"])}개): {state["queue"]}')
    print(f'  이미 교체됨: {state["rotated"]}')
    if state.get("history"):
        print(f'  교체 이력:')
        for h in state["history"][-5:]:
            print(f'    {h["date"]} — {", ".join(h["swapped"])} (사이클{h["cycle"]})')
    print()


# ─────────────────────────────────────────
# A/B 주간 로테이션 시스템
# ─────────────────────────────────────────
def copy_version(src_base: Path, dst_base: Path, codes: list):
    """품목 폴더 전체를 src → dst로 복사"""
    for code in codes:
        src = src_base / code
        dst = dst_base / code
        if not src.exists():
            continue
        dst.mkdir(parents=True, exist_ok=True)
        for f in src.iterdir():
            if f.is_file():
                shutil.copy2(f, dst / f.name)

def load_ab_state() -> dict:
    if AB_STATE.exists():
        with open(AB_STATE) as f:
            return json.load(f)
    return {'active': None, 'last_switch': None, 'cycle': 0, 'history': []}

def save_ab_state(state: dict):
    with open(AB_STATE, 'w', encoding='utf-8') as f:
        json.dump(state, f, ensure_ascii=False, indent=2)

async def cmd_ab_setup():
    """
    최초 1회 실행: 기존 live → V_A 백업, staging → V_B 복사
    이후 live는 V_A 상태로 시작
    """
    from datetime import date
    codes = list(BUILTIN_CONFIG.keys())

    log('[ A/B 셋업 시작 ]')

    # 1) 기존 live → V_A 백업
    log('기존 페이지 → V_A 백업 중...')
    VER_A_DIR.mkdir(parents=True, exist_ok=True)
    backed = 0
    for code in codes:
        src = LIVE_DIR / code
        if src.exists() and list(src.glob('section_*.jpg')):
            copy_version(LIVE_DIR, VER_A_DIR, [code])
            backed += 1
    log(f'  V_A 백업: {backed}개 품목')

    # 2) staging → V_B 복사
    log('AI 생성 페이지 → V_B 복사 중...')
    VER_B_DIR.mkdir(parents=True, exist_ok=True)
    vb_count = 0
    for code in codes:
        src = STAGING_DIR / code
        if src.exists() and list(src.glob('section_*.jpg')):
            copy_version(STAGING_DIR, VER_B_DIR, [code])
            vb_count += 1
    log(f'  V_B 복사: {vb_count}개 품목')

    # 3) live를 V_A로 세팅 (기존 유지)
    state = {'active': 'A', 'last_switch': date.today().isoformat(), 'cycle': 1, 'history': [
        {'date': date.today().isoformat(), 'switched_to': 'A', 'note': '초기 셋업'}
    ]}
    save_ab_state(state)

    log(f'\n✅ 셋업 완료!')
    log(f'  현재 노출: V_A (기존 페이지)')
    log(f'  다음 교체: 다음 주 월요일 오전 9시 → V_B (AI 생성)')
    log(f'  V_A 품목: {backed}개 / V_B 품목: {vb_count}개')

async def cmd_ab_rotate():
    """매주 실행: A↔B 전환"""
    from datetime import date
    state = load_ab_state()
    codes = list(BUILTIN_CONFIG.keys())

    current = state.get('active', 'A')
    next_ver = 'B' if current == 'A' else 'A'
    src_base = VER_B_DIR if next_ver == 'B' else VER_A_DIR

    log(f'[ A/B 로테이션 ] {current} → {next_ver} 전환 중...')

    switched = 0
    skipped  = []
    for code in codes:
        src = src_base / code
        if not src.exists() or not list(src.glob('section_*.jpg')):
            skipped.append(code)
            continue
        copy_version(src_base, LIVE_DIR, [code])
        switched += 1
        log(f'  ✅ {BUILTIN_CONFIG[code]["name"]} ({code})')

    state['active']      = next_ver
    state['last_switch'] = date.today().isoformat()
    state['cycle']      += 1
    state['history'].append({
        'date': date.today().isoformat(),
        'switched_to': next_ver,
        'switched': switched,
        'skipped': skipped,
    })
    save_ab_state(state)

    label = 'AI 생성 버전' if next_ver == 'B' else '기존 버전'
    log(f'\n✅ 전환 완료: 현재 노출 = {next_ver} ({label})')
    if skipped:
        log(f'⚠️  건너뜀 ({len(skipped)}개): {skipped}')
    log(f'다음 전환: 다음 주 월요일 → {"A (기존)" if next_ver == "B" else "B (AI생성)"}')

async def cmd_ab_status():
    state = load_ab_state()
    active = state.get('active', '미설정')
    label  = {'A': '기존 버전', 'B': 'AI 생성 버전', '미설정': '셋업 필요'}.get(active, active)
    print(f'\n[ A/B 로테이션 상태 ]')
    print(f'  현재 노출: {active} — {label}')
    print(f'  마지막 전환: {state.get("last_switch", "없음")}')
    print(f'  누적 사이클: {state.get("cycle", 0)}')
    # V_A/V_B 품목 수
    va = [c for c in BUILTIN_CONFIG if (VER_A_DIR / c).exists() and list((VER_A_DIR / c).glob('section_*.jpg'))]
    vb = [c for c in BUILTIN_CONFIG if (VER_B_DIR / c).exists() and list((VER_B_DIR / c).glob('section_*.jpg'))]
    print(f'  V_A (기존): {len(va)}개 — {va}')
    print(f'  V_B (AI생성): {len(vb)}개 — {vb}')
    if state.get('history'):
        print(f'  최근 전환 이력:')
        for h in state['history'][-4:]:
            to_label = 'AI생성' if h['switched_to'] == 'B' else '기존'
            print(f'    {h["date"]} → {h["switched_to"]} ({to_label}) {h.get("switched","")}개 전환')
    print()

# ─────────────────────────────────────────
# ENTRY POINT
# ─────────────────────────────────────────
def main():
    args = sys.argv[1:]
    if not args:
        print(__doc__); sys.exit(1)

    cmd = args[0]

    if cmd == 'generate':
        # generate "업종" "상품명" [style]
        if len(args) < 3:
            print('Usage: generate "업종" "상품명" [style]'); sys.exit(1)
        industry = args[1]
        product  = args[2]
        style    = args[3] if len(args) > 3 else 'default'
        mode     = args[4] if len(args) > 4 else 'both'
        asyncio.run(cmd_generate(industry, product, mode, style))

    elif cmd == 'copy-only':
        industry = args[1]; product = args[2]
        style    = args[3] if len(args) > 3 else 'default'
        asyncio.run(cmd_generate(industry, product, 'copy', style))

    elif cmd == 'images-only':
        industry = args[1]; product = args[2]
        style    = args[3] if len(args) > 3 else 'default'
        asyncio.run(cmd_generate(industry, product, 'images', style))

    elif cmd == 'generate-builtin':
        code  = args[1] if len(args) > 1 else 'namecard'
        # args[2]: mode (both/copy/images) 또는 style
        # args[3]: style
        mode_or_style = args[2] if len(args) > 2 else None
        if mode_or_style in ('both', 'copy', 'images', 'images-only', 'copy-only'):
            mode  = 'images' if mode_or_style in ('images', 'images-only') else ('copy' if mode_or_style == 'copy-only' else mode_or_style)
            style = args[3] if len(args) > 3 else None
        else:
            mode  = 'both'
            style = mode_or_style
        asyncio.run(cmd_generate_builtin(code, mode=mode, style=style))

    elif cmd == 'generate-all-builtin':
        asyncio.run(cmd_generate_all_builtin())

    elif cmd == 'swap':
        if len(args) >= 3:
            asyncio.run(cmd_swap(args[1], args[2]))
        else:
            asyncio.run(cmd_swap_builtin(args[1]))

    elif cmd == 'status-all':
        asyncio.run(cmd_status_all())

    elif cmd == 'rotate':
        count = int(args[1]) if len(args) > 1 else 2
        asyncio.run(cmd_rotate(count))

    elif cmd == 'rotate-status':
        asyncio.run(cmd_rotate_status())

    elif cmd == 'ab-setup':
        asyncio.run(cmd_ab_setup())

    elif cmd == 'ab-rotate':
        asyncio.run(cmd_ab_rotate())

    elif cmd == 'ab-status':
        asyncio.run(cmd_ab_status())

    else:
        print(f'알 수 없는 명령: {cmd}')
        print(__doc__); sys.exit(1)


if __name__ == '__main__':
    main()
