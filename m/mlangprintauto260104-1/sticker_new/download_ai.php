<?php
/**
 * 스티커 도무송 AI 템플릿 다운로드
 * Adobe Illustrator 호환 PostScript 파일 생성
 *
 * 3가지 선 포함:
 * - 여유선 (Bleed): 도무송 바깥 +3mm (파란색)
 * - 도무송 칼선 (Die-cut): 실제 크기 (검정색 점선)
 * - 안전선 (Safety): 도무송 안쪽 -3mm (빨간색)
 *
 * 사용법: download_ai.php?garo=50&sero=50&shape=rectangle&corner=3
 */

// 파라미터 받기
$garo = floatval($_GET['garo'] ?? 50);      // 가로 (mm)
$sero = floatval($_GET['sero'] ?? 50);      // 세로 (mm)
$shape = $_GET['shape'] ?? 'rectangle';      // 모양: rectangle, rounded, circle, ellipse
$corner = floatval($_GET['corner'] ?? 0);    // 귀돌이 반경 (mm)

// 여유선/안전선 간격 (mm)
$bleed = 3;   // 여유선: 바깥쪽 +3mm
$safety = 2;  // 안전선: 안쪽 -2mm

// 유효성 검증
$garo = max(10, min(500, $garo));  // 10~500mm (안전선 확보를 위해 최소 10mm)
$sero = max(10, min(500, $sero));  // 10~500mm

// 귀돌이 반경: 가로/세로 중 작은 값 기준 4% (100mm 기준 4mm)
if ($shape === 'rounded') {
    $base_dimension = min($garo, $sero);
    $corner = $base_dimension * 0.04;  // 4% 비율
}
$corner = max(0, min(min($garo, $sero) / 2 - $safety, $corner));  // 최대 반경 제한 (안전선 고려)

// 폰트 크기 스케일링 (90x90mm 기준 12pt)
$base_size = min($garo, $sero);
$font_scale = $base_size / 90;
$center_font_size = max(6, round(12 * $font_scale));  // 중앙 치수 폰트 (최소 6pt)
$label_font_size = max(4, round(8 * $font_scale));    // 선 라벨 폰트 (최소 4pt)
$legend_font_size = max(5, round(7 * $font_scale));   // 범례 폰트 (최소 5pt)

// 파일명 생성 (영어로 변경 - 호환성 향상)
$shape_names_en = [
    'rectangle' => 'Rectangle',
    'rounded' => 'Rounded',
    'circle' => 'Circle',
    'ellipse' => 'Ellipse'
];
$shape_names_ko = [
    'rectangle' => '사각형',
    'rounded' => '귀돌이',
    'circle' => '원형',
    'ellipse' => '타원형'
];
$shape_label_en = $shape_names_en[$shape] ?? $shape;
$shape_label_ko = $shape_names_ko[$shape] ?? $shape;
$filename = "sticker_{$garo}x{$sero}mm_{$shape_label_en}.ai";

// HTTP 헤더 설정
header('Content-Type: application/illustrator');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// mm → pt 변환 (1mm = 2.834645669pt)
$mm_to_pt = 2.834645669;

// 도무송 기준 크기
$w = $garo * $mm_to_pt;
$h = $sero * $mm_to_pt;
$r = $corner * $mm_to_pt;

// 여유선/안전선 크기 (pt)
$bleed_pt = $bleed * $mm_to_pt;
$safety_pt = $safety * $mm_to_pt;

// 범례 영역 크기 (pt)
$legend_height = 50;  // 범례 영역 높이

// 캔버스 크기 (여유선 + 범례 영역 포함)
$canvas_w = $w + ($bleed_pt * 2);
$canvas_h = $h + ($bleed_pt * 2) + $legend_height;

// AI 파일 헤더 (Adobe Illustrator 호환 EPS)
echo "%!PS-Adobe-3.0 EPSF-3.0\n";
echo "%%Creator: Duson Planning Sticker Generator\n";
echo "%%Title: Sticker Template {$garo}x{$sero}mm ({$shape_label_en})\n";
echo "%%CreationDate: " . date('Y-m-d H:i:s') . "\n";
echo "%%BoundingBox: 0 0 " . ceil($canvas_w) . " " . ceil($canvas_h) . "\n";
echo "%%HiResBoundingBox: 0 0 $canvas_w $canvas_h\n";
echo "%%DocumentData: Clean7Bit\n";
echo "%%LanguageLevel: 2\n";
echo "%%Pages: 1\n";
echo "%%EndComments\n\n";

// 프롤로그 (기본 설정)
echo "%%BeginProlog\n";
echo "/mm { 2.834645669 mul } def\n";  // mm 단위 정의
echo "%%EndProlog\n\n";

echo "%%Page: 1 1\n";
echo "gsave\n\n";

// 원점을 이동 (도무송이 캔버스 중앙에 위치, 범례 공간 확보)
echo "% 원점 이동 (여유선 + 범례 영역 고려)\n";
$translate_y = $bleed_pt + $legend_height;  // 범례 공간만큼 위로 올림
echo "$bleed_pt $translate_y translate\n\n";

/**
 * 모양 그리기 함수
 * @param float $width 가로
 * @param float $height 세로
 * @param float $radius 귀돌이 반경
 * @param string $shape 모양 타입
 * @param float $offset 오프셋 (양수: 확대, 음수: 축소)
 */
function drawShape($width, $height, $radius, $shape, $offset = 0) {
    // 오프셋 적용
    $w = $width + ($offset * 2);
    $h = $height + ($offset * 2);
    $r = max(0, $radius + $offset);

    // 시작점 오프셋
    $ox = -$offset;
    $oy = -$offset;

    echo "newpath\n";

    switch ($shape) {
        case 'circle':
            // 원형
            $cx = $width / 2;
            $cy = $height / 2;
            $base_radius = min($width, $height) / 2;
            $actual_radius = $base_radius + $offset;
            echo "$cx $cy $actual_radius 0 360 arc\n";
            break;

        case 'ellipse':
            // 타원형 (베지어 곡선으로 근사)
            $cx = $width / 2;
            $cy = $height / 2;
            $rx = ($width / 2) + $offset;
            $ry = ($height / 2) + $offset;
            // 타원을 4개의 베지어 곡선으로 근사 (kappa = 0.5522847498)
            $k = 0.5522847498;
            $kx = $rx * $k;
            $ky = $ry * $k;
            echo ($cx + $rx) . " $cy moveto\n";
            echo ($cx + $rx) . " " . ($cy + $ky) . " " . ($cx + $kx) . " " . ($cy + $ry) . " $cx " . ($cy + $ry) . " curveto\n";
            echo ($cx - $kx) . " " . ($cy + $ry) . " " . ($cx - $rx) . " " . ($cy + $ky) . " " . ($cx - $rx) . " $cy curveto\n";
            echo ($cx - $rx) . " " . ($cy - $ky) . " " . ($cx - $kx) . " " . ($cy - $ry) . " $cx " . ($cy - $ry) . " curveto\n";
            echo ($cx + $kx) . " " . ($cy - $ry) . " " . ($cx + $rx) . " " . ($cy - $ky) . " " . ($cx + $rx) . " $cy curveto\n";
            break;

        case 'rounded':
            // 귀돌이 (둥근 모서리 사각형)
            if ($r > 0) {
                // 둥근 모서리 사각형
                $x1 = $ox;
                $y1 = $oy;
                $x2 = $ox + $w;
                $y2 = $oy + $h;

                // 하단 직선 시작점
                echo ($x1 + $r) . " $y1 moveto\n";
                // 하단 직선
                echo ($x2 - $r) . " $y1 lineto\n";
                // 우하단 모서리 (중심: x2-r, y1+r)
                echo ($x2 - $r) . " " . ($y1 + $r) . " $r 270 360 arc\n";
                // 우측 직선
                echo "$x2 " . ($y2 - $r) . " lineto\n";
                // 우상단 모서리 (중심: x2-r, y2-r)
                echo ($x2 - $r) . " " . ($y2 - $r) . " $r 0 90 arc\n";
                // 상단 직선
                echo ($x1 + $r) . " $y2 lineto\n";
                // 좌상단 모서리 (중심: x1+r, y2-r)
                echo ($x1 + $r) . " " . ($y2 - $r) . " $r 90 180 arc\n";
                // 좌측 직선
                echo "$x1 " . ($y1 + $r) . " lineto\n";
                // 좌하단 모서리 (중심: x1+r, y1+r)
                echo ($x1 + $r) . " " . ($y1 + $r) . " $r 180 270 arc\n";
            } else {
                // 반경이 0이면 일반 사각형
                echo "$ox $oy moveto\n";
                echo ($ox + $w) . " $oy lineto\n";
                echo ($ox + $w) . " " . ($oy + $h) . " lineto\n";
                echo "$ox " . ($oy + $h) . " lineto\n";
            }
            break;

        case 'rectangle':
        default:
            // 사각형
            echo "$ox $oy moveto\n";
            echo ($ox + $w) . " $oy lineto\n";
            echo ($ox + $w) . " " . ($oy + $h) . " lineto\n";
            echo "$ox " . ($oy + $h) . " lineto\n";
            break;
    }

    echo "closepath\n";
}

// ============================================
// 1. 여유선 (Bleed Line) - 파란색 실선
// ============================================
echo "% 여유선 (Bleed Line) - 도무송 바깥 +{$bleed}mm\n";
echo "0.3 setlinewidth\n";
echo "0 0.7 1 setrgbcolor\n";      // 파란색 (Cyan)
echo "[] 0 setdash\n";              // 실선
drawShape($w, $h, $r, $shape, $bleed_pt);
echo "stroke\n\n";

// ============================================
// 2. 도무송 칼선 (Die-cut Line) - 검정색 점선
// ============================================
echo "% 도무송 칼선 (Die-cut Line) - 실제 크기\n";
echo "0.5 setlinewidth\n";
echo "0 0 0 setrgbcolor\n";         // 검정색
echo "[3 2] 0 setdash\n";           // 점선
drawShape($w, $h, $r, $shape, 0);
echo "stroke\n\n";

// ============================================
// 3. 안전선 (Safety Line) - 빨간색 실선
// ============================================
echo "% 안전선 (Safety Line) - 도무송 안쪽 -{$safety}mm\n";
echo "0.3 setlinewidth\n";
echo "1 0 0.3 setrgbcolor\n";       // 빨간색/마젠타
echo "[] 0 setdash\n";              // 실선
drawShape($w, $h, $r, $shape, -$safety_pt);
echo "stroke\n\n";

// ============================================
// 선 라벨 - 여유선/안전선 밀착 표시 (굴림체)
// ============================================
echo "% Line Labels (GulimChe font)\n";
echo "/GulimChe findfont $label_font_size scalefont setfont\n";

// 여유선 라벨 (파란색, 여유선 위쪽 중앙)
$bleed_label_y = $h + $bleed_pt + 2;  // 여유선 바로 위
$bleed_text = "Bleed +{$bleed}mm";
$bleed_text_width = strlen($bleed_text) * ($label_font_size * 0.5);  // 텍스트 폭 추정
$bleed_label_x = ($w - $bleed_text_width) / 2;  // 중앙 정렬
echo "0 0.7 1 setrgbcolor\n";  // 파란색
echo "$bleed_label_x $bleed_label_y moveto\n";
echo "({$bleed_text}) show\n";

// 안전선 라벨 (빨간색, 안전선 안쪽 중앙)
$safety_label_y = 5;  // 안전선 안쪽 (하단에서 5pt 위)
$safety_text = "Safety -{$safety}mm";
$safety_text_width = strlen($safety_text) * ($label_font_size * 0.5);  // 텍스트 폭 추정
$safety_label_x = ($w - $safety_text_width) / 2;  // 중앙 정렬
echo "1 0 0.3 setrgbcolor\n";  // 빨간색
echo "$safety_label_x $safety_label_y moveto\n";
echo "({$safety_text}) show\n";

// ============================================
// 중앙 치수 표시 (굴림체)
// ============================================
echo "% Center Dimensions (GulimChe font)\n";
echo "/GulimChe findfont $center_font_size scalefont setfont\n";

// 중앙 좌표 계산
$center_x = $w / 2;
$center_y = $h / 2;

// 재단선 치수 (검정색) - Die-cut Size
echo "0 0 0 setrgbcolor\n";
$diecut_text = "Die-cut: {$garo}mm x {$sero}mm";
$diecut_text_width = strlen($diecut_text) * ($center_font_size * 0.32);  // 영어는 한글보다 폭이 좁음
echo ($center_x - $diecut_text_width) . " " . ($center_y + $center_font_size * 0.7) . " moveto\n";
echo "({$diecut_text}) show\n";

// 작업선 치수 (회색) - Work Area Size
echo "0.4 0.4 0.4 setrgbcolor\n";
$work_garo = $garo + 6;  // 양쪽 여유선 3mm * 2
$work_sero = $sero + 6;  // 양쪽 여유선 3mm * 2
$work_text = "Work: {$work_garo}mm x {$work_sero}mm";
$work_text_width = strlen($work_text) * ($center_font_size * 0.32);
echo ($center_x - $work_text_width) . " " . ($center_y - $center_font_size * 0.5) . " moveto\n";
echo "({$work_text}) show\n";

// ============================================
// 범례 (굴림체)
// ============================================
echo "% Legend (GulimChe font)\n";
echo "[] 0 setdash\n";
echo "/GulimChe findfont $legend_font_size scalefont setfont\n";

// 범례 위치 (왼쪽 하단, 범례 영역 내)
$legend_x = -$bleed_pt + 2;
$legend_y = -$translate_y + 10;  // 캔버스 하단 범례 영역에 배치

// 제목 (영어)
echo "0 0 0 setrgbcolor\n";
echo "$legend_x " . ($legend_y + 30) . " moveto\n";
echo "(Duson Planning Sticker Die-cut Template) show\n";

echo "/GulimChe findfont $legend_font_size scalefont setfont\n";

// 크기 정보 (영어) - 행간 14pt
echo "0.3 0.3 0.3 setrgbcolor\n";
echo "$legend_x " . ($legend_y + 16) . " moveto\n";
echo "(Size: {$garo} x {$sero} mm | Shape: {$shape_label_en}) show\n";

// 여유선 범례 - 행간 14pt
echo "0 0.7 1 setrgbcolor\n";
echo "$legend_x " . ($legend_y + 4) . " moveto\n";
echo "10 0 rlineto stroke\n";
echo "0 0.3 0.3 0.3 setrgbcolor\n";
echo ($legend_x + 15) . " " . ($legend_y + 2) . " moveto\n";
echo "(Bleed Line: +{$bleed}mm \\(Extend background/images here\\)) show\n";

// 도무송 범례 - 행간 14pt
echo "0 0 0 setrgbcolor\n";
echo "[3 2] 0 setdash\n";
echo "$legend_x " . ($legend_y - 10) . " moveto\n";
echo "10 0 rlineto stroke\n";
echo "[] 0 setdash\n";
echo "0.3 0.3 0.3 setrgbcolor\n";
echo ($legend_x + 15) . " " . ($legend_y - 12) . " moveto\n";
echo "(Die-cut Line: Actual cut line) show\n";

// 안전선 범례 - 행간 14pt
echo "1 0 0.3 setrgbcolor\n";
echo "[] 0 setdash\n";
echo "$legend_x " . ($legend_y - 24) . " moveto\n";
echo "10 0 rlineto stroke\n";
echo "0.3 0.3 0.3 setrgbcolor\n";
echo ($legend_x + 15) . " " . ($legend_y - 26) . " moveto\n";
echo "(Safety Line: -{$safety}mm \\(Keep text/logos inside\\)) show\n";

// 마무리
echo "\ngrestore\n";
echo "showpage\n";
echo "%%EOF\n";
?>
