<?php
/**
 * 레이어 팝업 컴포넌트 (DB 기반)
 * site_popups 테이블에서 활성 팝업을 조회하여 레이어로 동시 표시
 * 쿠키로 "안보기" 처리 (popup_hide_{id})
 * 여러 팝업이 있으면 겹쳐서 동시 표시, 각각 독립적으로 닫기 가능
 *
 * 사용법: <?php include 'includes/popup_layer.php'; ?>
 */

// DB 연결 확인
if (!isset($db) || !$db) {
    return;
}

// 테이블 존재 확인
$tableCheck = @mysqli_query($db, "SHOW TABLES LIKE 'site_popups'");
if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
    return;
}

// 활성 팝업 조회 (현재 날짜 기준)
$today = date('Y-m-d');
$sql = "SELECT id, title, image_path, link_url, link_target, hide_option
        FROM site_popups
        WHERE is_active = 1
          AND start_date <= '$today'
          AND end_date >= '$today'
        ORDER BY sort_order ASC, id DESC";
$result = @mysqli_query($db, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    return;
}

// 쿠키 체크하여 표시할 팝업 필터링
$popupsToShow = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cookieName = 'popup_hide_' . $row['id'];
    if (!isset($_COOKIE[$cookieName])) {
        $popupsToShow[] = $row;
    }
}

if (empty($popupsToShow)) {
    return;
}

$hideDaysMap = ['today' => 1, 'week' => 7, 'month' => 30];
$hideLabelMap = ['today' => '오늘 하루 보지 않기', 'week' => '7일간 보지 않기', 'month' => '30일간 보지 않기'];
$popupCount = count($popupsToShow);
?>
<!-- DB 기반 레이어 팝업 (<?php echo $popupCount; ?>개 동시 표시) -->
<style>
.site-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: sitePopupFadeIn 0.3s ease;
}
@keyframes sitePopupFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.site-popup-card {
    background: #fff;
    border-radius: 12px;
    max-width: 480px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    animation: sitePopupSlideUp 0.3s ease;
    position: absolute;
}
@keyframes sitePopupSlideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.site-popup-close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    background: rgba(0,0,0,0.4);
    border: none;
    border-radius: 50%;
    color: #fff;
    font-size: 18px;
    line-height: 32px;
    text-align: center;
    cursor: pointer;
    z-index: 2;
    transition: background 0.2s;
}
.site-popup-close:hover {
    background: rgba(0,0,0,0.7);
}
.site-popup-image {
    display: block;
    width: 100%;
    height: auto;
}
.site-popup-image-link {
    display: block;
    cursor: pointer;
}
.site-popup-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    background: #f9fafb;
    border-top: 1px solid #eee;
}
.site-popup-hide-btn {
    background: none;
    border: none;
    color: #888;
    font-size: 13px;
    cursor: pointer;
    padding: 4px 0;
}
.site-popup-hide-btn:hover {
    color: #333;
}
.site-popup-confirm-btn {
    background: #1a3a5c;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.site-popup-confirm-btn:hover {
    background: #0d2640;
}
@media (max-width: 480px) {
    .site-popup-card {
        border-radius: 10px;
        max-width: calc(100vw - 32px);
    }
    .site-popup-footer {
        padding: 8px 12px;
    }
}
</style>

<div class="site-popup-overlay" id="sitePopupOverlay">
<?php foreach ($popupsToShow as $idx => $popup):
    $popupId = (int)$popup['id'];
    $title = htmlspecialchars($popup['title'] ?? '', ENT_QUOTES, 'UTF-8');
    $imagePath = htmlspecialchars($popup['image_path'] ?? '', ENT_QUOTES, 'UTF-8');
    $linkUrl = htmlspecialchars($popup['link_url'] ?? '', ENT_QUOTES, 'UTF-8');
    $linkTarget = ($popup['link_target'] === '_self') ? '_self' : '_blank';
    $hideOption = $popup['hide_option'] ?? 'today';
    $hideDays = $hideDaysMap[$hideOption] ?? 1;
    $hideLabel = $hideLabelMap[$hideOption] ?? '오늘 하루 보지 않기';
    // 여러 개일 때 살짝 어긋나게 배치 (좌상단 기준 오프셋)
    $offsetX = $idx * 24;
    $offsetY = $idx * 24;
    $zIndex = 100000 + ($popupCount - $idx); // 첫 번째가 가장 위에
?>
    <div class="site-popup-card" id="sitePopupCard_<?php echo $popupId; ?>"
         style="z-index:<?php echo $zIndex; ?>; transform: translate(<?php echo $offsetX; ?>px, <?php echo $offsetY; ?>px);">
        <button type="button" class="site-popup-close" onclick="closeSitePopup(<?php echo $popupId; ?>, 0)" title="닫기">&times;</button>

        <?php if ($imagePath): ?>
            <?php if ($linkUrl): ?>
                <a href="<?php echo $linkUrl; ?>" target="<?php echo $linkTarget; ?>" class="site-popup-image-link">
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo $title; ?>" class="site-popup-image">
                </a>
            <?php else: ?>
                <img src="<?php echo $imagePath; ?>" alt="<?php echo $title; ?>" class="site-popup-image">
            <?php endif; ?>
        <?php endif; ?>

        <div class="site-popup-footer">
            <button type="button" class="site-popup-hide-btn" onclick="closeSitePopup(<?php echo $popupId; ?>, <?php echo $hideDays; ?>)"><?php echo $hideLabel; ?></button>
            <button type="button" class="site-popup-confirm-btn" onclick="closeSitePopup(<?php echo $popupId; ?>, 0)">확인</button>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
function closeSitePopup(popupId, hideDays) {
    if (hideDays > 0) {
        var d = new Date();
        d.setTime(d.getTime() + (hideDays * 24 * 60 * 60 * 1000));
        document.cookie = "popup_hide_" + popupId + "=1;expires=" + d.toUTCString() + ";path=/";
    }
    var card = document.getElementById('sitePopupCard_' + popupId);
    if (card) {
        card.style.opacity = '0';
        card.style.transition = 'opacity 0.2s';
        setTimeout(function() { card.remove(); checkOverlay(); }, 200);
    }
}
function checkOverlay() {
    var overlay = document.getElementById('sitePopupOverlay');
    if (!overlay) return;
    var remaining = overlay.querySelectorAll('.site-popup-card');
    if (remaining.length === 0) {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.2s';
        setTimeout(function() { overlay.remove(); }, 200);
    }
}
</script>
