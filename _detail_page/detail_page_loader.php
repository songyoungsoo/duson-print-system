<?php
/**
 * AI 생성 상세페이지 로더 v3 (SEO 텍스트 포함)
 * 
 * v3 변경사항 (2026-03-08):
 *   - 신규 copy.json 포맷 ('section' + 'copy.headline') 지원 추가
 *   - 구형 copy.json 포맷 ('id' + 'headline') 호환 유지
 *   - alt 텍스트에 copy.headline / theme fallback 적용
 *   - copy_guide → highlight fallback 체인 추가
 * 
 * 각 제품 index.php에서 include하여 사용.
 * ImgFolder/detail_page/{product}/sections/ 폴더에 이미지가 있으면 자동 표시.
 * copy.json이 있으면 각 섹션 이미지 아래에 SEO 텍스트를 HTML로 출력.
 * 이미지가 없으면 아무것도 출력하지 않음.
 * 
 * 사용법 (제품 index.php에서):
 *   $detail_page_product = 'namecard';  // 제품 폴더명
 *   include __DIR__ . "/../../_detail_page/detail_page_loader.php";
 */

// 제품 타입이 지정되지 않았으면 중단
if (empty($detail_page_product)) {
    return;
}

// 섹션 이미지 디렉토리 경로 (ImgFolder = 웹 접근 가능 경로)
$sectionsDir = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/detail_page/' . $detail_page_product . '/sections';
$sectionsWebPath = '/ImgFolder/detail_page/' . $detail_page_product . '/sections';

// 디렉토리가 없거나 비어있으면 아무것도 출력하지 않음
if (!is_dir($sectionsDir)) {
    return;
}

// 숨길 섹션 (section_01: 할인배너, section_08: AI가격표)
$hiddenSections = ['section_01.png', 'section_08.png', 'section_11.png'];

// PNG 이미지 파일 수집 (정렬, 숨김 섹션 제외)
$imageFiles = [];
$files = scandir($sectionsDir);
foreach ($files as $file) {
    if (preg_match('/^section_\d{2}\.png$/i', $file) && !in_array($file, $hiddenSections)) {
        $imageFiles[] = $file;
    }
}
sort($imageFiles);

// 이미지가 없으면 중단
if (empty($imageFiles)) {
    return;
}

// ── SEO 텍스트: copy.json 로드 ──
$copyData = null;
$copyJsonPath = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/detail_page/' . $detail_page_product . '/copy.json';
if (file_exists($copyJsonPath)) {
    $rawJson = file_get_contents($copyJsonPath);
    $copyData = json_decode($rawJson, true);
}

// 섹션 번호 → copy 텍스트 매핑 (두 가지 포맷 지원)
$sectionCopyMap = [];
if ($copyData && isset($copyData['sections'])) {
    foreach ($copyData['sections'] as $section) {
        // 신규 포맷: 'section' 키 (2026-03 이후 생성)
        // 구형 포맷: 'id' 키 (2026-03 이전 생성)
        $secId = $section['section'] ?? $section['id'] ?? null;
        if ($secId !== null) {
            $sectionCopyMap[(int)$secId] = $section;
        }
    }
}

/**
 * 섹션 copy 데이터에서 SEO HTML 생성
 */
function renderSeoText($sectionData) {
    if (empty($sectionData)) return '';
    
    $html = '';
    
    // 신규 포맷: 텍스트가 'copy' 객체 안에 중첩
    // 구형 포맷: 텍스트가 최상위에 직접 존재
    $copy = $sectionData['copy'] ?? $sectionData;
    
    // headline
    $headline = $copy['headline'] ?? '';
    if ($headline) {
        $headline = str_replace("\n", ' ', $headline);
        $html .= '<h3>' . htmlspecialchars($headline) . '</h3>';
    }
    
    // subtext (간단 문자열)
    $subtext = $copy['subtext'] ?? '';
    if ($subtext) {
        $html .= '<p>' . htmlspecialchars($subtext) . '</p>';
    }
    
    // body — 문자열 또는 배열 (구형 포맷에서 사용)
    $body = $copy['body'] ?? '';
    if (is_string($body) && $body) {
        $html .= '<p>' . nl2br(htmlspecialchars($body)) . '</p>';
    } elseif (is_array($body)) {
        foreach ($body as $item) {
            if (is_string($item)) {
                $html .= '<p>' . htmlspecialchars($item) . '</p>';
            } elseif (is_array($item)) {
                $title = $item['title'] ?? '';
                $desc = $item['description'] ?? '';
                if ($title) {
                    $html .= '<p><strong>' . htmlspecialchars($title) . '</strong>';
                    if ($desc) {
                        $html .= ' — ' . htmlspecialchars($desc);
                    }
                    $html .= '</p>';
                }
            }
        }
    }
    
    // highlight (구형) 또는 copy_guide (신규)
    $highlight = $copy['highlight'] ?? $sectionData['copy_guide'] ?? '';
    if ($highlight) {
        $html .= '<p><strong>' . htmlspecialchars($highlight) . '</strong></p>';
    }
    
    return $html;
}

// 제품 한글명 매핑 (alt 텍스트용)
$productNameMap = [
    'namecard' => '명함',
    'sticker_new' => '스티커',
    'inserted' => '전단지',
    'envelope' => '봉투',
    'littleprint' => '포스터',
    'merchandisebond' => '상품권',
    'cadarok' => '카다록',
    'ncrflambeau' => 'NCR양식지',
    'msticker' => '자석스티커',
];
$productNameKo = $productNameMap[$detail_page_product] ?? $detail_page_product;
?>
<!-- AI 생성 상세페이지 (두손기획인쇄 · _detail_page) -->
<div class="ai-detail-page-sections" style="width: 1100px; max-width: 100%; margin: 20px auto 0; padding: 0; line-height: 0;">
<?php foreach ($imageFiles as $file): ?>
<?php
    // 파일명에서 섹션 번호 추출 (section_06.png → 6)
    preg_match('/section_(\d{2})/', $file, $m);
    $sectionId = isset($m[1]) ? (int)$m[1] : 0;
    $sectionCopy = $sectionCopyMap[$sectionId] ?? null;
    // 신규 포맷: copy.headline, 구형 포맷: headline
    $copyText = $sectionCopy['copy'] ?? $sectionCopy;
    
    // alt 텍스트: 섹션 headline 또는 기본값
    $altText = $productNameKo . ' ' . ($copyText['headline'] ?? $sectionCopy['theme'] ?? '상세 설명');
    $altText = str_replace("\n", ' ', $altText);
?>
<?php if ($file === 'section_13.png'): ?>
    <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;" style="display:block;margin:0;padding:0;line-height:0;cursor:pointer;">
        <img src="<?= htmlspecialchars($sectionsWebPath . '/' . $file) ?>"
             alt="<?= htmlspecialchars($productNameKo) ?> 주문하기"
             style="width: 100%; display: block; margin: 0; padding: 0;"
             loading="lazy">
    </a>
<?php else: ?>
    <img src="<?= htmlspecialchars($sectionsWebPath . '/' . $file) ?>"
         alt="<?= htmlspecialchars($altText) ?>"
         style="width: 100%; display: block; margin: 0; padding: 0;"
         loading="lazy">
<?php endif; ?>
<?php if ($sectionCopy): ?>
    <div class="ai-seo-text" style="line-height: 1.6; padding: 12px 20px; font-size: 0; overflow: hidden; height: 0; margin: 0;">
        <?= renderSeoText($sectionCopy) ?>
    </div>
<?php endif; ?>
<?php endforeach; ?>
</div>
