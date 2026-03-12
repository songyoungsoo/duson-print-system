#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
V2 Leaflet Factory - OCR & Info Extraction
이미지(전단지, 메뉴판) 또는 텍스트 문서에서 정보를 추출하고 구조화된 JSON으로 반환합니다.
"""

import os
import sys
import json
import argparse
import warnings
from pathlib import Path

# Suppress Deprecation/Future warnings from google.generativeai
warnings.filterwarnings("ignore")

# google.generativeai 임포트 시도
try:
    import google.generativeai as genai
    # .env에서 로드된 환경변수 사용
    genai.configure(api_key=os.environ.get("GEMINI_API_KEY"))
except ImportError:
    genai = None

def extract_info(image_path: str = None, text_file_path: str = None):
    if not genai:
        return {"error": "google.generativeai 패키지가 설치되어 있지 않거나 API 키가 없습니다."}

    contents = []
    
    prompt = """
    제공된 자료(이미지 또는 텍스트 문서)의 내용을 꼼꼼히 읽고, 다음 JSON 형식에 맞춰 구조화해서 추출해주세요.
    알 수 없는 정보는 빈 문자열("") 또는 빈 배열([])로 남겨두세요.
    오직 JSON 형식으로만 응답해야 합니다. 마크다운 코드 블록(```json 등)은 제외하고 순수 JSON 텍스트만 출력하세요.

    {
      "business_name": "가게 상호명 또는 학원명",
      "category": "음식점, 카페/베이커리, 학원/교육, 미용/뷰티, 피트니스, 기타 중 가장 알맞은 것 하나",
      "features": "주요 특징이나 강점 (예: 30년 전통, 원어민 강사 등. 쉼표로 구분된 하나의 문자열)",
      "phone": "전화번호",
      "hours": "영업시간 (또는 상담시간)",
      "address": "주소",
      "items": [
        {
          "name": "메뉴, 상품명 또는 커리큘럼(수업) 이름",
          "description": "상세 설명 (대상, 기간, 내용 등. 없으면 빈 문자열)",
          "price": "가격 또는 수강료 (알 수 없으면 빈 문자열)"
        }
      ]
    }
    """
    contents.append(prompt)

    try:
        # 1. 이미지 처리
        if image_path and os.path.exists(image_path):
            with open(image_path, "rb") as f:
                image_bytes = f.read()
            ext = os.path.splitext(image_path)[1].lower()
            mime_type = "image/jpeg"
            if ext == ".png": mime_type = "image/png"
            elif ext == ".webp": mime_type = "image/webp"
            
            contents.append({
                "mime_type": mime_type,
                "data": image_bytes
            })

        # 2. 텍스트 문서 처리
        if text_file_path and os.path.exists(text_file_path):
            with open(text_file_path, "r", encoding="utf-8", errors="ignore") as f:
                text_content = f.read()
            contents.append(f"\n\n[추가 텍스트 자료 내용]\n{text_content}\n")

        if len(contents) == 1:
            return {"error": "분석할 파일이나 텍스트를 찾을 수 없습니다."}

        # 최신 모델 사용 (Gemini 2.5 Flash)
        model = genai.GenerativeModel('gemini-2.5-flash')
        response = model.generate_content(contents)
        text = response.text.strip()
        
        # 마크다운 잔재 제거
        if text.startswith('```json'):
            text = text[7:]
        if text.startswith('```'):
            text = text[3:]
        if text.endswith('```'):
            text = text[:-3]
            
        return json.loads(text.strip())

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=False, help="추출할 이미지 파일 경로")
    parser.add_argument("--textfile", required=False, help="추출할 텍스트 파일 경로")
    args = parser.parse_args()
    
    result = extract_info(image_path=args.image, text_file_path=args.textfile)
    # PHP가 읽을 수 있게 stdout으로 출력
    print(json.dumps(result, ensure_ascii=False))
