"""
이미지 합치기 스크립트 — 13개 섹션 이미지를 하나의 긴 상세페이지로 합침
빌더 조쉬 방식: 1100x1100 이미지 13장 → 1100x14300 최종 이미지
"""

import os
import sys
import logging
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    print("❌ Pillow 패키지를 설치하세요:")
    print("   pip install Pillow")
    exit(1)

logging.basicConfig(
    level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s"
)
logger = logging.getLogger(__name__)


def stitch_images(
    sections_dir: str,
    output_path: str,
    expected_width: int = 1100,
    expected_count: int = 13,
) -> bool:
    """
    섹션 이미지들을 세로로 합쳐 하나의 긴 이미지를 생성합니다.

    Args:
        sections_dir: 섹션 이미지가 저장된 디렉토리
        output_path: 최종 합본 이미지 저장 경로
        expected_width: 기대 이미지 너비 (px)
        expected_count: 기대 섹션 수

    Returns:
        성공 여부
    """
    sections_path = Path(sections_dir)

    # 이미지 파일 수집 (정렬)
    image_files = sorted(
        [
            f
            for f in sections_path.iterdir()
            if f.suffix.lower() in (".png", ".jpg", ".jpeg")
        ],
        key=lambda f: f.name,
    )

    if not image_files:
        logger.error(f"❌ 섹션 이미지가 없습니다: {sections_dir}")
        return False

    logger.info(f"📦 {len(image_files)}개 섹션 이미지 발견 (기대: {expected_count}개)")

    if len(image_files) < expected_count:
        logger.warning(f"⚠️ {expected_count - len(image_files)}개 섹션 누락!")

    # 이미지 로드 및 크기 확인
    images = []
    total_height = 0

    for img_file in image_files:
        try:
            img = Image.open(img_file)

            # 너비가 다르면 리사이즈
            if img.width != expected_width:
                ratio = expected_width / img.width
                new_height = int(img.height * ratio)
                img = img.resize((expected_width, new_height), Image.Resampling.LANCZOS)
                logger.info(
                    f"  📐 리사이즈: {img_file.name} → {expected_width}x{new_height}"
                )

            images.append(img)
            total_height += img.height
            logger.info(f"  ✅ {img_file.name}: {img.width}x{img.height}")

        except Exception as e:
            logger.error(f"  ❌ {img_file.name} 로드 실패: {e}")
            continue

    if not images:
        logger.error("❌ 유효한 이미지가 없습니다.")
        return False

    # 합본 이미지 생성
    logger.info(f"🔧 합본 시작: {expected_width}x{total_height}px")

    final_image = Image.new("RGB", (expected_width, total_height), (255, 255, 255))

    y_offset = 0
    for i, img in enumerate(images):
        final_image.paste(img, (0, y_offset))
        y_offset += img.height
        logger.info(f"  📌 섹션 {i + 1}/{len(images)} 붙이기 완료 (y={y_offset})")

    # 저장
    output = Path(output_path)
    output.parent.mkdir(parents=True, exist_ok=True)
    final_image.save(str(output), "PNG", optimize=True)

    file_size = os.path.getsize(output)
    logger.info(f"✅ 최종 이미지 저장 완료!")
    logger.info(f"   📄 경로: {output}")
    logger.info(f"   📐 크기: {expected_width}x{total_height}px")
    logger.info(f"   💾 용량: {file_size:,} bytes ({file_size / 1024 / 1024:.1f}MB)")
    logger.info(f"   🖼️ 섹션: {len(images)}개")

    return True


def validate_sections(sections_dir: str, expected_count: int = 13) -> dict:
    """섹션 이미지 품질 검증"""
    sections_path = Path(sections_dir)
    results = {"valid": [], "invalid": [], "missing": []}

    for i in range(1, expected_count + 1):
        filename = f"section_{i:02d}.png"
        filepath = sections_path / filename

        if not filepath.exists():
            results["missing"].append(filename)
            continue

        try:
            img = Image.open(filepath)
            # 기본 검증
            if img.width < 100 or img.height < 100:
                results["invalid"].append({"file": filename, "reason": "너무 작음"})
            elif img.getextrema() == ((0, 0), (0, 0), (0, 0)):
                results["invalid"].append(
                    {"file": filename, "reason": "완전 검정 이미지"}
                )
            else:
                results["valid"].append(filename)
        except Exception as e:
            results["invalid"].append({"file": filename, "reason": str(e)})

    return results


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("사용법: python image_stitcher.py <섹션_디렉토리> <출력_파일>")
        print(
            "예시: python image_stitcher.py output/namecard/sections output/namecard/final_detail_page.png"
        )
        sys.exit(1)

    sections_dir = sys.argv[1]
    output_path = sys.argv[2]

    # 검증
    validation = validate_sections(sections_dir)
    if validation["missing"]:
        logger.warning(f"누락된 섹션: {validation['missing']}")
    if validation["invalid"]:
        logger.warning(f"문제 있는 섹션: {validation['invalid']}")

    # 합치기
    success = stitch_images(sections_dir, output_path)
    sys.exit(0 if success else 1)
