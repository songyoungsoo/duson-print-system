<?php
/**
 * 인쇄 작업 템플릿 생성기 (SVG)
 *
 * 사용법:
 * template_generator.php?width=210&height=297&bleed=1.5&safe=2&product=전단지
 * template_generator.php?width=80&height=100&bleed=3&safe=2&product=스티커&domusong=1
 *
 * 파라미터:
 * - width: 재단 사이즈 가로 (mm)
 * - height: 재단 사이즈 세로 (mm)
 * - bleed: 재단 여유선 (mm, 바깥으로)
 * - safe: 안전선 (mm, 안쪽으로)
 * - product: 제품명 (선택, 예: 전단지, 명함, 스티커)
 * - domusong: 도무송 여부 (0 또는 1, 스티커 전용)
 */

// 파라미터 받기 (기본값: A4 전단지)
$width = isset($_GET['width']) ? floatval($_GET['width']) : 210;
$height = isset($_GET['height']) ? floatval($_GET['height']) : 297;
$bleed = isset($_GET['bleed']) ? floatval($_GET['bleed']) : 1.5;
$safe = isset($_GET['safe']) ? floatval($_GET['safe']) : 2;
$product = isset($_GET['product']) ? htmlspecialchars($_GET['product']) : '인쇄물';
$domusong = isset($_GET['domusong']) ? intval($_GET['domusong']) : 0;

// 기본 계산
$bleedWidth = $width + ($bleed * 2);    // 여유선 너비
$bleedHeight = $height + ($bleed * 2);  // 여유선 높이
$safeWidth = $width - ($safe * 2);      // 안전선 너비
$safeHeight = $height - ($safe * 2);    // 안전선 높이

// 도무송 처리 (스티커 전용)
$showDomusongLine = false;
$domusongWidth = 0;
$domusongHeight = 0;
$paperWidth = $bleedWidth;
$paperHeight = $bleedHeight;

if ($domusong && $width > 50 && $height > 50) {
    // 둘 다 50mm 초과일 때만 도무송 최종재단선 표시
    $showDomusongLine = true;
    $domusongWidth = $bleedWidth + 4;   // 여유선 +2mm 양쪽
    $domusongHeight = $bleedHeight + 4;
    $paperWidth = $domusongWidth;
    $paperHeight = $domusongHeight;
} elseif ($domusong) {
    // 50mm 이하일 때는 종이만 50mm 고정
    $paperWidth = max(50, $bleedWidth);
    $paperHeight = max(50, $bleedHeight);
}

// 중앙 배치를 위한 offset 계산
$offsetX = ($paperWidth - $width) / 2;
$offsetY = ($paperHeight - $height) / 2;

// HTTP 헤더 설정
header('Content-Type: image/svg+xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="template_' . $product . '_' . $width . 'x' . $height . '.svg"');

// SVG 출력
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<svg xmlns="http://www.w3.org/2000/svg"
     width="<?php echo $paperWidth; ?>mm"
     height="<?php echo $paperHeight; ?>mm"
     viewBox="0 0 <?php echo $paperWidth; ?> <?php echo $paperHeight; ?>"
     version="1.1">

  <title><?php echo $product; ?> 작업 템플릿 (<?php echo $width; ?>×<?php echo $height; ?>mm<?php echo $domusong ? ' - 도무송' : ''; ?>)</title>

  <defs>
    <style>
      .trim-line { fill: none; stroke: #000000; stroke-width: 1; }
      .bleed-line { fill: none; stroke: #00FF00; stroke-width: 1; stroke-dasharray: 1,3; }
      .safe-line { fill: none; stroke: #0000FF; stroke-width: 1; stroke-dasharray: 1,3; }
      .domusong-line { fill: none; stroke: #FF0000; stroke-width: 1; stroke-dasharray: 1,3; }
      .center-line { fill: none; stroke: #CCCCCC; stroke-width: 0.5; stroke-dasharray: 1,3; opacity: 0.5; }
      .label { font-family: 'Nanum Gothic', 'NanumGothic', 'Gulim', sans-serif; font-size: 3.5px; fill: #333; text-anchor: middle; }
      .label-small { font-family: 'Nanum Gothic', 'NanumGothic', 'Gulim', sans-serif; font-size: 3px; fill: #666; text-anchor: middle; }
      .label-red { font-family: 'Nanum Gothic', 'NanumGothic', 'Gulim', sans-serif; font-size: 3px; fill: #FF0000; font-weight: 600; text-anchor: middle; }
    </style>
  </defs>

  <!-- 배경 (작업 영역 전체) -->
  <rect x="0" y="0" width="<?php echo $paperWidth; ?>" height="<?php echo $paperHeight; ?>"
        fill="#FFFFFF" stroke="none"/>

  <!-- 재단선 (검정) - <?php echo $width; ?>×<?php echo $height; ?>mm -->
  <rect x="<?php echo $offsetX; ?>" y="<?php echo $offsetY; ?>"
        width="<?php echo $width; ?>" height="<?php echo $height; ?>"
        class="trim-line"/>

  <!-- 재단 여유선 (녹색) - 바깥쪽 +<?php echo $bleed; ?>mm -->
  <rect x="<?php echo $offsetX - $bleed; ?>" y="<?php echo $offsetY - $bleed; ?>"
        width="<?php echo $bleedWidth; ?>" height="<?php echo $bleedHeight; ?>"
        class="bleed-line"/>

  <!-- 안전선 (청색) - 안쪽 -<?php echo $safe; ?>mm -->
  <rect x="<?php echo $offsetX + $safe; ?>" y="<?php echo $offsetY + $safe; ?>"
        width="<?php echo $safeWidth; ?>" height="<?php echo $safeHeight; ?>"
        class="safe-line"/>

  <?php if ($showDomusongLine): ?>
  <!-- 도무송 최종재단선 (빨강) - 여유선 +2mm -->
  <rect x="<?php echo $offsetX - $bleed - 2; ?>" y="<?php echo $offsetY - $bleed - 2; ?>"
        width="<?php echo $domusongWidth; ?>" height="<?php echo $domusongHeight; ?>"
        class="domusong-line"/>
  <?php endif; ?>

  <!-- 코너 재단 마크 (검정) -->
  <?php
  $markLen = 5; // 마크 길이
  $trimX = $offsetX;
  $trimY = $offsetY;
  $trimRight = $offsetX + $width;
  $trimBottom = $offsetY + $height;

  // 좌상단
  echo '<line x1="' . $trimX . '" y1="' . ($trimY - $markLen) . '" x2="' . $trimX . '" y2="' . ($trimY + $markLen) . '" stroke="#000000" stroke-width="0.3"/>';
  echo '<line x1="' . ($trimX - $markLen) . '" y1="' . $trimY . '" x2="' . ($trimX + $markLen) . '" y2="' . $trimY . '" stroke="#000000" stroke-width="0.3"/>';

  // 우상단
  echo '<line x1="' . $trimRight . '" y1="' . ($trimY - $markLen) . '" x2="' . $trimRight . '" y2="' . ($trimY + $markLen) . '" stroke="#000000" stroke-width="0.3"/>';
  echo '<line x1="' . ($trimRight - $markLen) . '" y1="' . $trimY . '" x2="' . ($trimRight + $markLen) . '" y2="' . $trimY . '" stroke="#000000" stroke-width="0.3"/>';

  // 좌하단
  echo '<line x1="' . $trimX . '" y1="' . ($trimBottom - $markLen) . '" x2="' . $trimX . '" y2="' . ($trimBottom + $markLen) . '" stroke="#000000" stroke-width="0.3"/>';
  echo '<line x1="' . ($trimX - $markLen) . '" y1="' . $trimBottom . '" x2="' . ($trimX + $markLen) . '" y2="' . $trimBottom . '" stroke="#000000" stroke-width="0.3"/>';

  // 우하단
  echo '<line x1="' . $trimRight . '" y1="' . ($trimBottom - $markLen) . '" x2="' . $trimRight . '" y2="' . ($trimBottom + $markLen) . '" stroke="#000000" stroke-width="0.3"/>';
  echo '<line x1="' . ($trimRight - $markLen) . '" y1="' . $trimBottom . '" x2="' . ($trimRight + $markLen) . '" y2="' . $trimBottom . '" stroke="#000000" stroke-width="0.3"/>';
  ?>

  <!-- 중앙 가이드선 (회색) -->
  <?php
  $centerX = $paperWidth / 2;
  $centerY = $paperHeight / 2;
  echo '<line x1="' . $centerX . '" y1="0" x2="' . $centerX . '" y2="' . $paperHeight . '" class="center-line"/>';
  echo '<line x1="0" y1="' . $centerY . '" x2="' . $paperWidth . '" y2="' . $centerY . '" class="center-line"/>';
  ?>

  <!-- 라벨 텍스트 (중앙 정렬) -->
  <?php
  $centerX = $paperWidth / 2;
  $centerY = $paperHeight / 2;
  $lineHeight = 4;
  $startY = $centerY - 10;
  ?>

  <text x="<?php echo $centerX; ?>" y="<?php echo $startY; ?>" class="label">
    <?php echo $product; ?> 작업 템플릿<?php echo $domusong ? ' (도무송)' : ''; ?>
  </text>

  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight; ?>" class="label-small">
    재단 사이즈: <?php echo $width; ?>×<?php echo $height; ?>mm (검정선)
  </text>

  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 2; ?>" class="label-small">
    재단 여유: +<?php echo $bleed; ?>mm (녹색 점선)
  </text>

  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 3; ?>" class="label-small">
    안전 영역: -<?php echo $safe; ?>mm (청색 점선 안쪽)
  </text>

  <?php if ($showDomusongLine): ?>
  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 4; ?>" class="label-red">
    도무송 최종재단: 여유선 +2mm (빨강 점선)
  </text>
  <?php endif; ?>

  <?php if ($domusong && !$showDomusongLine): ?>
  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 4; ?>" class="label-red">
    ※ 50mm 이하 도무송: 종이 <?php echo $paperWidth; ?>×<?php echo $paperHeight; ?>mm 고정
  </text>
  <?php endif; ?>

  <!-- 하단 주의사항 (중앙 정렬) -->
  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 6; ?>" class="label-small" fill="#FF0000">
    ※ 중요한 텍스트/이미지는 청색 안전선 안쪽에 배치하세요
  </text>

  <text x="<?php echo $centerX; ?>" y="<?php echo $startY + $lineHeight * 7; ?>" class="label-small">
    두손기획인쇄 1688-2384 | www.dsp1830.shop
  </text>

</svg>
