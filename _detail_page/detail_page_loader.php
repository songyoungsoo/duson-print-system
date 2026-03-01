<?php
/**
 * AI 생성 상세페이지 로더
 * 
 * 각 제품 index.php에서 include하여 사용.
 * ImgFolder/detail_page/{product}/sections/ 폴더에 이미지가 있으면 자동 표시.
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
?>
<!-- AI 생성 상세페이지 (두손기획인쇄 · _detail_page) -->
<div class="ai-detail-page-sections" style="width: 1100px; max-width: 100%; margin: 20px auto 0; padding: 0; line-height: 0;">
<?php foreach ($imageFiles as $file): ?>
<?php if ($file === 'section_13.png'): ?>
    <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;" style="display:block;margin:0;padding:0;line-height:0;cursor:pointer;">
        <img src="<?= htmlspecialchars($sectionsWebPath . '/' . $file) ?>"
             alt="<?= htmlspecialchars($detail_page_product) ?> 주문하기"
             style="width: 100%; display: block; margin: 0; padding: 0;"
             loading="lazy">
    </a>
<?php else: ?>
    <img src="<?= htmlspecialchars($sectionsWebPath . '/' . $file) ?>"
         alt="<?= htmlspecialchars($detail_page_product) ?> 상세 설명"
         style="width: 100%; display: block; margin: 0; padding: 0;"
         loading="lazy">
<?php endif; ?>
<?php endforeach; ?>
</div>
