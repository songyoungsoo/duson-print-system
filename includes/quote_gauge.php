<?php
require_once __DIR__ . '/../db.php';
$_qf_settings = [];
$_qf_q = mysqli_query($db, "SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'quote_widget_%'");
if ($_qf_q) { while ($_qf_r = mysqli_fetch_assoc($_qf_q)) { $_qf_settings[$_qf_r['setting_key']] = $_qf_r['setting_value']; } }
$_qf_enabled = ($_qf_settings['quote_widget_enabled'] ?? '1') === '1';
$_qf_right = intval($_qf_settings['quote_widget_right'] ?? 20);
$_qf_top = intval($_qf_settings['quote_widget_top'] ?? 50);
if (!$_qf_enabled) return;
?>
<link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="qf" id="quoteFloat" style="right:<?php echo $_qf_right; ?>px;top:<?php echo $_qf_top; ?>%">
  <div class="qf-box">
    <div class="qf-head">
      <div class="qf-head-title"><span class="qf-head-dot"></span>실시간 견적받기</div>
    </div>
    <div class="qf-price-area">
      <div class="qf-price"><span id="qf-total">0</span><span class="w">원</span></div>
      <div class="qf-vat-hint">VAT 포함</div>
    </div>
    <div class="qf-detail">
      <div class="qd-body">
        <div class="qd-tag">SPEC</div>
        <div class="qd-row"><span class="qd-k">용지</span><span class="qd-v" id="qf-paper">-</span></div>
        <div class="qd-row"><span class="qd-k">인쇄</span><span class="qd-v" id="qf-color">-</span></div>
        <div class="qd-row"><span class="qd-k">사이즈</span><span class="qd-v" id="qf-size">-</span></div>
        <div class="qd-row"><span class="qd-k">수량</span><span class="qd-v" id="qf-qty">-</span></div>
        <div class="qd-div"></div>
        <div class="qd-tag">인쇄외옵션</div>
        <div id="qf-options-list"><div class="qd-opt">선택 없음</div></div>
        <div class="qd-div"></div>
        <div class="qd-tag">PRICING</div>
        <div class="qd-pr"><span class="qd-pk">인쇄비</span><span class="qd-pv" id="qf-print-price">0</span></div>
        <div class="qd-pr" id="qf-design-row" style="display:none"><span class="qd-pk">디자인</span><span class="qd-pv" id="qf-design-price">0</span></div>
        <div class="qd-pr" id="qf-option-row" style="display:none"><span class="qd-pk">옵션</span><span class="qd-pv" id="qf-option-price">0</span></div>
        <div class="qd-pr sub"><span class="qd-pk">합계</span><span class="qd-pv" id="qf-subtotal">0</span></div>
        <div class="qd-pr vat"><span class="qd-pk">부가세(10%)</span><span class="qd-pv" id="qf-vat">0</span></div>
      </div>
      <div class="qd-foot">
        <button class="qd-fbtn ord" onclick="document.getElementById('btn-upload-order')?.click()">주문하기</button>
        <button class="qd-fbtn qt" onclick="window.print()">견적인쇄</button>
      </div>
    </div>
    <div class="qf-btn-area">
      <button class="qf-btn" onclick="document.getElementById('btn-upload-order')?.click()">견적받기</button>
    </div>
  </div>
</div>
