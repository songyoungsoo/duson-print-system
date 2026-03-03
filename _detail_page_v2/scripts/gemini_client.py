"""
Gemini API Client V2 — 텍스트 생성 + 이미지 생성
캔버스: 1100×800px, 내용 영역: 800×800px (좌우 150px 여백)
google.genai SDK 사용
"""

import os
import json
import time
import logging
from pathlib import Path
from typing import Optional

try:
    from google import genai
    from google.genai import types
except ImportError:
    print("❌ google-genai 패키지를 설치하세요:")
    print("   pip install google-genai")
    exit(1)

logger = logging.getLogger(__name__)


class GeminiClient:
    """Gemini API 래퍼 — google.genai SDK 기반 (V2: 1100x800)"""

    def __init__(self, api_key: Optional[str] = None):
        self.api_key = api_key or os.getenv("GEMINI_API_KEY")
        if not self.api_key:
            raise ValueError(
                "GEMINI_API_KEY가 설정되지 않았습니다. .env 파일을 확인하세요."
            )

        self.client = genai.Client(api_key=self.api_key)

        # 모델 설정
        self.text_model_name = os.getenv("GEMINI_TEXT_MODEL", "gemini-2.5-flash")
        self.image_model_name = os.getenv(
            "GEMINI_IMAGE_MODEL", "gemini-3.1-flash-image-preview"
        )

        # 설정
        self.max_retries = int(os.getenv("MAX_RETRIES", "3"))
        self.retry_delay = int(os.getenv("RETRY_DELAY", "5"))
        self.rate_limit_delay = int(os.getenv("RATE_LIMIT_DELAY", "2"))

        # V2 이미지 규격
        self.canvas_width = int(os.getenv("IMAGE_WIDTH", "1100"))
        self.canvas_height = int(os.getenv("IMAGE_HEIGHT", "800"))
        self.content_width = int(os.getenv("CONTENT_WIDTH", "800"))

        # 비용 추적
        self.total_text_tokens = 0
        self.total_images_generated = 0

        logger.info(
            f"GeminiClient V2 초기화 — 텍스트: {self.text_model_name}, "
            f"이미지: {self.image_model_name}, "
            f"캔버스: {self.canvas_width}x{self.canvas_height}, "
            f"내용폭: {self.content_width}px"
        )

    def generate_text(
        self, prompt: str, system_prompt: str = "", temperature: float = 0.7
    ) -> str:
        """텍스트 생성"""
        full_prompt = f"{system_prompt}\n\n{prompt}" if system_prompt else prompt

        for attempt in range(self.max_retries):
            try:
                response = self.client.models.generate_content(
                    model=self.text_model_name,
                    contents=full_prompt,
                    config=types.GenerateContentConfig(
                        temperature=temperature,
                        max_output_tokens=8192,
                    ),
                )
                result = response.text
                logger.info(f"텍스트 생성 완료 — {len(result)}자")
                return result

            except Exception as e:
                logger.warning(
                    f"텍스트 생성 시도 {attempt + 1}/{self.max_retries} 실패: {e}"
                )
                if attempt < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                else:
                    logger.error(f"텍스트 생성 최종 실패: {e}")
                    raise

    def _repair_json(self, raw: str) -> dict:
        """LLM이 생성한 불완전한 JSON을 복구 시도"""
        import re

        # 마크다운 코드블록 제거
        raw = re.sub(r"```json\s*", "", raw)
        raw = re.sub(r"```\s*$", "", raw)
        # JSON 범위 추출
        start = raw.find("{")
        end = raw.rfind("}") + 1
        if start < 0 or end <= start:
            raise json.JSONDecodeError("No JSON object found", raw, 0)
        raw = raw[start:end]
        # 일반적 LLM JSON 오류 수정
        raw = re.sub(r'(?<=["])\n(?=[^}\]]*["])', "\\n", raw)
        # trailing comma 제거
        raw = re.sub(r",\s*([}\]])", r"\1", raw)
        try:
            return json.loads(raw)
        except json.JSONDecodeError:
            # 최후의 수단: 잘린 JSON 닫기 시도
            depth_brace = raw.count("{") - raw.count("}")
            depth_bracket = raw.count("[") - raw.count("]")
            raw += "]" * depth_bracket + "}" * depth_brace
            return json.loads(raw)

    def generate_text_json(self, prompt: str, system_prompt: str = "") -> dict:
        """JSON 형식 텍스트 생성"""
        json_prompt = (
            f"{prompt}\n\nIMPORTANT: Respond with valid JSON only. "
            "No markdown code blocks. Keep all string values on a single line "
            "(use \\n for newlines inside strings). Ensure all strings are properly terminated."
        )
        full_prompt = (
            f"{system_prompt}\n\n{json_prompt}" if system_prompt else json_prompt
        )

        for attempt in range(self.max_retries):
            try:
                response = self.client.models.generate_content(
                    model=self.text_model_name,
                    contents=full_prompt,
                    config=types.GenerateContentConfig(
                        temperature=0.3,
                        max_output_tokens=16384,
                        response_mime_type="application/json",
                    ),
                )
                result = json.loads(response.text)
                logger.info(
                    f"JSON 생성 완료 — {len(json.dumps(result, ensure_ascii=False))}자"
                )
                return result

            except json.JSONDecodeError as e:
                logger.warning(f"JSON 파싱 실패 (시도 {attempt + 1}): {e}")
                try:
                    result = self._repair_json(response.text)
                    logger.info(
                        f"JSON 복구 성공 — {len(json.dumps(result, ensure_ascii=False))}자"
                    )
                    return result
                except Exception:
                    pass
                if attempt < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                else:
                    raise

            except Exception as e:
                logger.warning(
                    f"JSON 생성 시도 {attempt + 1}/{self.max_retries} 실패: {e}"
                )
                if attempt < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                else:
                    raise

    def generate_image(self, prompt: str, output_path: str) -> bool:
        """이미지 생성 — V2: 1100x800 캔버스, 800px 내용 영역"""
        # V2 품질 보강 접미사
        quality_suffix = f"""
Style requirements:
- Photorealistic product photography style
- Professional e-commerce aesthetic
- Clean, modern layout with generous white space
- Text must be clearly legible and accurately rendered
- No cartoon, anime, or illustrated style
- No watermarks or stock photo marks
- High contrast, vibrant but professional colors
- {self.canvas_width}x{self.canvas_height} pixels, PNG format
- Main content centered within {self.content_width}px wide area
- 150px side margins on each side filled with the section background color
- NO pixel measurements, rulers, guidelines, or dimension labels visible
"""
        full_prompt = f"{prompt}\n\n{quality_suffix}"

        for attempt in range(self.max_retries):
            try:
                response = self.client.models.generate_content(
                    model=self.image_model_name,
                    contents=full_prompt,
                    config=types.GenerateContentConfig(
                        response_modalities=["IMAGE", "TEXT"],
                        image_config=types.ImageConfig(aspect_ratio="4:3"),
                    ),
                )

                # 이미지 데이터 추출
                if response.candidates and response.candidates[0].content.parts:
                    for part in response.candidates[0].content.parts:
                        if part.inline_data:
                            image_data = part.inline_data.data
                            Path(output_path).parent.mkdir(parents=True, exist_ok=True)
                            with open(output_path, "wb") as f:
                                f.write(image_data)

                            # 정확한 1100x800으로 리사이즈
                            try:
                                from PIL import Image as PILImage

                                img = PILImage.open(output_path)
                                if img.size != (self.canvas_width, self.canvas_height):
                                    img = img.resize(
                                        (self.canvas_width, self.canvas_height),
                                        PILImage.Resampling.LANCZOS,
                                    )
                                    img.save(output_path, "PNG")
                                    logger.info(
                                        f"  📐 리사이즈: {img.size} → "
                                        f"{self.canvas_width}x{self.canvas_height}"
                                    )
                            except Exception as resize_err:
                                logger.warning(
                                    f"리사이즈 실패 (원본 유지): {resize_err}"
                                )

                            self.total_images_generated += 1
                            file_size = os.path.getsize(output_path)
                            logger.info(
                                f"이미지 생성 완료 — {output_path} ({file_size:,} bytes)"
                            )
                            return True

                logger.warning(f"이미지 데이터 없음 (시도 {attempt + 1})")
                if attempt < self.max_retries - 1:
                    time.sleep(self.retry_delay)

            except Exception as e:
                logger.warning(
                    f"이미지 생성 시도 {attempt + 1}/{self.max_retries} 실패: {e}"
                )
                if attempt < self.max_retries - 1:
                    time.sleep(self.retry_delay)
                else:
                    logger.error(f"이미지 생성 최종 실패: {output_path} — {e}")
                    return False

        return False

    def get_cost_estimate(self) -> dict:
        """현재까지의 예상 비용"""
        image_cost = self.total_images_generated * 0.067
        return {
            "images_generated": self.total_images_generated,
            "estimated_image_cost_usd": round(image_cost, 3),
            "estimated_total_usd": round(image_cost + 0.18, 3),
        }
