"""
Gemini API Client — 텍스트 생성 + 이미지 생성
google.genai SDK 사용 (2026-03 마이그레이션)
모델: gemini-3.1-flash-image-preview (한글 텍스트 정확 렌더링)
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

# 로깅 설정
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler("logs/gemini_client.log", encoding="utf-8"),
    ],
)
logger = logging.getLogger(__name__)


class GeminiClient:
    """Gemini API 래퍼 — google.genai SDK 기반"""

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

        # 비용 추적
        self.total_text_tokens = 0
        self.total_images_generated = 0

        logger.info(
            f"GeminiClient 초기화 완료 — 텍스트: {self.text_model_name}, 이미지: {self.image_model_name}"
        )

    def generate_text(
        self, prompt: str, system_prompt: str = "", temperature: float = 0.7
    ) -> str:
        """텍스트 생성 (리서치/카피라이팅/디자인 에이전트용)"""
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
        # 일반적 LLM JSON 오류 수정: 문자열 내 이스케이프 안 된 줄바꿈
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
        json_prompt = f"{prompt}\n\nIMPORTANT: Respond with valid JSON only. No markdown code blocks. Keep all string values on a single line (use \\n for newlines inside strings). Ensure all strings are properly terminated."
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
                # JSON 복구 시도
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

    def generate_image(
        self,
        prompt: str,
        output_path: str,
        aspect_ratio: str = "5:4",
        resize_to: tuple = (1100, 900),
        quality_suffix: str = None,
    ) -> bool:
        """이미지 생성 — gemini-3.1-flash-image-preview

        Args:
            aspect_ratio: Gemini 비율 ("1:1","3:4","4:3","4:5","5:4","9:16","16:9")
            resize_to: 출력 크기 tuple (None이면 원본 유지)
            quality_suffix: 프롬프트 접미사 (None이면 기본값, ""이면 접미사 없음)
        """
        if quality_suffix is None:
            quality_suffix = """
Style requirements:
- Photorealistic product photography style
- Professional Korean e-commerce aesthetic
- Clean, modern layout with generous white space
- Korean text must be clearly legible and accurately rendered
- No cartoon, anime, or illustrated style
- No watermarks or stock photo marks
- High contrast, vibrant but professional colors
"""
        full_prompt = f"{prompt}\n\n{quality_suffix}" if quality_suffix else prompt

        for attempt in range(self.max_retries):
            try:
                response = self.client.models.generate_content(
                    model=self.image_model_name,
                    contents=full_prompt,
                    config=types.GenerateContentConfig(
                        response_modalities=["IMAGE", "TEXT"],
                        image_config=types.ImageConfig(aspect_ratio=aspect_ratio),
                    ),
                )

                # 이미지 데이터 추출
                if response.candidates and response.candidates[0].content.parts:
                    for part in response.candidates[0].content.parts:
                        if part.inline_data:
                            image_data = part.inline_data.data
                            # 파일 저장
                            Path(output_path).parent.mkdir(parents=True, exist_ok=True)
                            with open(output_path, "wb") as f:
                                f.write(image_data)

                            # Post-resize (선택)
                            if resize_to:
                                try:
                                    from PIL import Image as PILImage
                                    img = PILImage.open(output_path)
                                    if img.size != resize_to:
                                        img = img.resize(resize_to, PILImage.Resampling.LANCZOS)
                                        img.save(output_path, "PNG")
                                        logger.info(f"  📐 리사이즈: {img.size} → {resize_to[0]}x{resize_to[1]}")
                                except Exception as resize_err:
                                    logger.warning(f"리사이즈 실패 (원본 유지): {resize_err}")

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
