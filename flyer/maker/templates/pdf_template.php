<?php
/**
 * A4 전단지 PDF 템플릿 (mPDF 호환)
 *
 * generate.php에서 ob_start() / include / ob_get_clean()으로 HTML 캡처하여 사용.
 * mPDF CSS 제약: flexbox, grid, CSS변수, calc() 사용 불가.
 * 레이아웃은 <table>, float, width% 로 구성.
 *
 * 사용 가능한 변수:
 *   $preset           — 업종 프리셋 배열 (colors, menuLabel 등)
 *   $businessName     — 상호명
 *   $tagline          — 캐치프레이즈
 *   $phone            — 전화번호
 *   $address          — 주소
 *   $hours            — 영업시간
 *   $features         — 특장점 배열 (1~3)
 *   $menuItems        — ['name'=>..., 'price'=>...] 배열
 *   $promotion        — 프로모션 텍스트 (빈 문자열 가능)
 *   $logoPath         — 로고 이미지 절대경로 (null 가능)
 *   $photoPaths       — 사진 절대경로 배열 (0~4)
 *   $mapImagePath     — 약도 이미지 절대경로 (null 가능)
 *   $qrCodePath       — QR 코드 이미지 절대경로 (null 가능)
 *   $websiteUrl       — 웹사이트 URL 문자열
 *   $aiImagePath      — AI 생성 이미지 절대경로 (null 가능)
 *   $needsPage2       — 2페이지 필요 여부
 *   $overflowMenuItems — 12개 초과 메뉴 항목 배열
 */

// --- Color shortcuts ---
$cPrimary   = $preset['colors']['primary'];
$cSecondary = $preset['colors']['secondary'];
$cAccent    = $preset['colors']['accent'];
$cBg        = $preset['colors']['bg'];
$cText      = $preset['colors']['text'];
$menuLabel  = htmlspecialchars($preset['menuLabel'] ?? '메뉴');

// --- Menu layout for page 1 (max 12 items) ---
$page1Menu = array_slice($menuItems, 0, 12);
$menuCols  = (count($page1Menu) > 6) ? 2 : 1;

// --- Lighter shade for alternating rows (mix bg with white) ---
$altRowBg = $cBg;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<style>
/* @page size and margins are set via mPDF constructor — do NOT duplicate here */
body {
    font-family: 'nanumgothic', sans-serif;
    font-size: 10pt;
    line-height: 1.5;
    color: <?= $cText ?>;
    margin: 0;
    padding: 0;
}

/* ---- Accent Bars ---- */
.accent-top {
    background-color: <?= $cPrimary ?>;
    height: 6mm;
    width: 100%;
    border-radius: 1.5mm 1.5mm 0 0;
}
.accent-bottom {
    background-color: <?= $cPrimary ?>;
    height: 4mm;
    width: 100%;
    border-radius: 0 0 1.5mm 1.5mm;
    margin-top: 4mm;
}

/* ---- Header ---- */
.header-name {
    font-size: 22pt;
    font-weight: bold;
    color: <?= $cPrimary ?>;
    line-height: 1.2;
}
.header-tagline {
    font-size: 11pt;
    color: <?= $cSecondary ?>;
    margin-top: 1mm;
}
.header-phone {
    font-size: 15pt;
    font-weight: bold;
    color: <?= $cPrimary ?>;
}

/* ---- Promotion Banner ---- */
.promo-banner {
    background-color: <?= $cAccent ?>;
    color: #FFFFFF;
    font-size: 12pt;
    font-weight: bold;
    text-align: center;
    padding: 3mm 5mm;
    border-radius: 2mm;
    margin: 4mm 0;
}

/* ---- Feature Badges ---- */
.feature-cell {
    background-color: <?= $cSecondary ?>;
    color: #FFFFFF;
    text-align: center;
    font-size: 9pt;
    font-weight: bold;
    padding: 3mm 2mm;
    border-radius: 2mm;
}

/* ---- Menu Section ---- */
.menu-header {
    font-size: 13pt;
    font-weight: bold;
    color: <?= $cPrimary ?>;
    border-bottom: 0.5mm solid <?= $cPrimary ?>;
    padding-bottom: 2mm;
    margin-top: 5mm;
    margin-bottom: 3mm;
}
.menu-name {
    font-size: 10pt;
    padding: 2mm 3mm;
}
.menu-price {
    font-size: 10pt;
    font-weight: bold;
    text-align: right;
    padding: 2mm 3mm;
    color: <?= $cPrimary ?>;
}

/* ---- Footer ---- */
.footer-label {
    font-size: 8pt;
    color: <?= $cSecondary ?>;
    font-weight: bold;
}
.footer-value {
    font-size: 9pt;
    color: <?= $cText ?>;
}

/* ---- Page 2 Sections ---- */
.section-title {
    font-size: 14pt;
    font-weight: bold;
    color: <?= $cPrimary ?>;
    margin: 5mm 0 3mm 0;
    border-left: 2mm solid <?= $cAccent ?>;
    padding-left: 3mm;
}
.photo-cell {
    text-align: center;
    padding: 2mm;
}
.photo-cell img {
    border-radius: 2mm;
    border: 0.3mm solid #DDDDDD;
}
.map-container {
    text-align: center;
    margin: 3mm 0;
}
.map-container img {
    border-radius: 2mm;
    border: 0.3mm solid #CCCCCC;
}
</style>
</head>
<body>

<!-- ==================== PAGE 1 (FRONT) ==================== -->

<!-- Top accent bar -->
<div class="accent-top"></div>

<!-- AI 생성 히어로 이미지 -->
<?php if (!empty($aiImagePath)): ?>
<div style="text-align: center; margin-bottom: 3mm;">
    <img src="<?= htmlspecialchars($aiImagePath) ?>" style="width: 100%; max-height: 75mm; object-fit: cover; border-radius: 2mm;" />
</div>
<?php endif; ?>

<!-- Header: Logo + Business Name + Phone -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 4mm;">
<tr>
<?php if (!empty($logoPath)): ?>
    <td width="18%" valign="middle" style="text-align: center;">
        <img src="<?= htmlspecialchars($logoPath) ?>" style="width: 18mm; max-height: 18mm;" />
    </td>
<?php endif; ?>
    <td valign="middle"<?= !empty($logoPath) ? ' style="padding-left: 3mm;"' : '' ?>>
        <div class="header-name"><?= htmlspecialchars($businessName) ?></div>
<?php if (!empty($tagline)): ?>
        <div class="header-tagline"><?= htmlspecialchars($tagline) ?></div>
<?php endif; ?>
<?php if (!empty($subtitle)): ?>
        <div style="font-size: 9pt; color: <?= $cSecondary ?>; margin-top: 1mm;"><?= htmlspecialchars($subtitle) ?></div>
<?php endif; ?>
    </td>
    <td width="35%" valign="middle" style="text-align: right;">
        <div class="header-phone">TEL <?= htmlspecialchars($phone) ?></div>
    </td>
</tr>
</table>

<!-- Promotion Banner -->
<?php if (!empty($promotion)): ?>
<div class="promo-banner"><?= htmlspecialchars($promotion) ?></div>
<?php endif; ?>

<!-- Feature Badges (horizontal) -->
<?php if (!empty($features)): ?>
<?php $fWidth = floor(100 / count($features)); ?>
<table width="100%" cellpadding="0" cellspacing="3" style="margin: 4mm 0;">
<tr>
<?php foreach ($features as $feat): ?>
    <td width="<?= $fWidth ?>%" class="feature-cell"><?= htmlspecialchars($feat) ?></td>
<?php endforeach; ?>
</tr>
</table>
<?php endif; ?>

<!-- Menu / Service List -->
<?php if (!empty($page1Menu)): ?>
<div class="menu-header"><?= $menuLabel ?></div>

<?php if ($menuCols === 1): ?>
<!-- Single column menu -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<?php foreach ($page1Menu as $i => $item): ?>
    <tr style="background-color: <?= ($i % 2 === 0) ? $altRowBg : '#FFFFFF' ?>;">
        <td class="menu-name"><?= htmlspecialchars($item['name']) ?></td>
        <td class="menu-price" width="30%"><?= htmlspecialchars($item['price']) ?></td>
    </tr>
<?php endforeach; ?>
</table>

<?php else: ?>
<!-- Two column menu -->
<?php
$half = (int)ceil(count($page1Menu) / 2);
$leftItems  = array_slice($page1Menu, 0, $half);
$rightItems = array_slice($page1Menu, $half);
?>
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    <td width="49%" valign="top">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<?php foreach ($leftItems as $i => $item): ?>
            <tr style="background-color: <?= ($i % 2 === 0) ? $altRowBg : '#FFFFFF' ?>;">
                <td class="menu-name"><?= htmlspecialchars($item['name']) ?></td>
                <td class="menu-price" width="35%"><?= htmlspecialchars($item['price']) ?></td>
            </tr>
<?php endforeach; ?>
        </table>
    </td>
    <td width="2%"></td>
    <td width="49%" valign="top">
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<?php foreach ($rightItems as $i => $item): ?>
            <tr style="background-color: <?= ($i % 2 === 0) ? $altRowBg : '#FFFFFF' ?>;">
                <td class="menu-name"><?= htmlspecialchars($item['name']) ?></td>
                <td class="menu-price" width="35%"><?= htmlspecialchars($item['price']) ?></td>
            </tr>
<?php endforeach; ?>
        </table>
    </td>
</tr>
</table>
<?php endif; ?>

<?php endif; ?>

<!-- Footer: Address + Hours + QR -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 5mm; border-top: 0.3mm solid #DDDDDD; padding-top: 3mm;">
<tr>
    <td valign="top" style="padding-right: 3mm;">
        <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <span class="footer-label">[주소]</span><br />
                <span class="footer-value"><?= htmlspecialchars($address) ?></span>
            </td>
        </tr>
<?php if (!empty($hours)): ?>
        <tr>
            <td style="padding-top: 2mm;">
                <span class="footer-label">[영업시간]</span><br />
                <span class="footer-value"><?= htmlspecialchars($hours) ?></span>
            </td>
        </tr>
<?php endif; ?>
<?php if (!empty($websiteUrl)): ?>
        <tr>
            <td style="padding-top: 2mm;">
                <span class="footer-label">[웹사이트]</span><br />
                <span class="footer-value"><?= htmlspecialchars($websiteUrl) ?></span>
            </td>
        </tr>
<?php endif; ?>
        </table>
    </td>
<?php if (!empty($qrCodePath)): ?>
    <td width="30mm" valign="middle" style="text-align: right;">
        <img src="<?= htmlspecialchars($qrCodePath) ?>" style="width: 25mm; height: 25mm;" />
    </td>
<?php endif; ?>
</tr>
</table>

<!-- Bottom accent bar -->
<div class="accent-bottom"></div>


<!-- ==================== PAGE 2 (BACK) — Conditional ==================== -->
<?php if ($needsPage2): ?>
<pagebreak />

<!-- Top accent bar -->
<div class="accent-top"></div>

<!-- Overflow menu items (13+) -->
<?php if (!empty($overflowMenuItems)): ?>
<div class="section-title"><?= $menuLabel ?> (continued)</div>
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<?php foreach ($overflowMenuItems as $i => $item): ?>
    <tr style="background-color: <?= ($i % 2 === 0) ? $altRowBg : '#FFFFFF' ?>;">
        <td class="menu-name"><?= htmlspecialchars($item['name']) ?></td>
        <td class="menu-price" width="30%"><?= htmlspecialchars($item['price']) ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Photo Gallery (2x2 grid) -->
<?php if (!empty($photoPaths)): ?>
<div class="section-title">Gallery</div>
<table width="100%" cellpadding="0" cellspacing="3">
<?php for ($pi = 0; $pi < count($photoPaths); $pi += 2): ?>
    <tr>
        <td width="49%" class="photo-cell">
            <img src="<?= htmlspecialchars($photoPaths[$pi]) ?>" style="width: 85mm; max-height: 70mm;" />
        </td>
<?php if (isset($photoPaths[$pi + 1])): ?>
        <td width="49%" class="photo-cell">
            <img src="<?= htmlspecialchars($photoPaths[$pi + 1]) ?>" style="width: 85mm; max-height: 70mm;" />
        </td>
<?php else: ?>
        <td width="49%"></td>
<?php endif; ?>
    </tr>
<?php endfor; ?>
</table>
<?php endif; ?>

<!-- Map Image -->
<?php if (!empty($mapImagePath)): ?>
<div class="section-title">Location</div>
<div class="map-container">
    <img src="<?= htmlspecialchars($mapImagePath) ?>" style="width: 100%; max-height: 80mm;" />
</div>
<div style="font-size: 9pt; color: <?= $cSecondary ?>; margin-top: 2mm;">
    <?= htmlspecialchars($address) ?>
</div>
<?php endif; ?>

<!-- Footer (repeated) -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 5mm; border-top: 0.3mm solid #DDDDDD; padding-top: 3mm;">
<tr>
    <td valign="middle">
        <span class="footer-label">[주소]</span>
        <span class="footer-value"><?= htmlspecialchars($address) ?></span>
        &nbsp;&nbsp;
        <span class="footer-label">[전화]</span>
        <span class="footer-value"><?= htmlspecialchars($phone) ?></span>
    </td>
<?php if (!empty($qrCodePath)): ?>
    <td width="30mm" valign="middle" style="text-align: right;">
        <img src="<?= htmlspecialchars($qrCodePath) ?>" style="width: 25mm; height: 25mm;" />
    </td>
<?php endif; ?>
</tr>
</table>

<!-- Bottom accent bar -->
<div class="accent-bottom"></div>

<?php endif; /* needsPage2 */ ?>

</body>
</html>
