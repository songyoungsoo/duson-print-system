function fmtNum(n) {
  return Number(n || 0).toLocaleString('ko-KR');
}

function animatePrice(el, target, dur) {
  dur = dur || 800;
  var st = null;
  function ease(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
  function step(ts) {
    if (!st) st = ts;
    var p = Math.min((ts - st) / dur, 1);
    el.textContent = Math.round(ease(p) * target).toLocaleString();
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function getSelectedText(selectId) {
  var el = document.getElementById(selectId);
  if (!el || el.selectedIndex < 0) return '-';
  return el.options[el.selectedIndex].text;
}

function getOptionLabel(type, value) {
  var labels = {
    single: '단면유광코팅', double: '양면유광코팅',
    single_matte: '단면무광코팅', double_matte: '양면무광코팅',
    '2fold': '2단접지', '3fold': '3단접지',
    accordion: '병풍접지', gate: '대문접지'
  };
  if (type === 'creasing') return value + '줄 오시';
  return labels[value] || value;
}

function shortenPaper(text) {
  if (!text || text === '-') return '-';
  text = text.replace(/\(합판전단\)/g, '').replace(/\(독판전단\)/g, '').trim();
  return text.length > 12 ? text.substring(0, 12) : text;
}

function shortenQty(text) {
  if (!text || text === '-') return '-';
  var m = text.match(/([\d.]+연)\s*\(([\d,]+매)\)/);
  if (m) return m[1] + '×1건';
  return text.length > 12 ? text.substring(0, 12) : text;
}

function updateQfSpecs() {
  var pd = window.currentPriceData;

  var paper = getSelectedText('MY_Fsd');
  if (paper === '-') paper = getSelectedText('jong');
  if (paper.indexOf('선택') > -1 || paper.indexOf('먼저') > -1) paper = '-';
  if (paper === '-' && pd && pd.specData) paper = pd.specData.jong || '-';
  document.getElementById('qf-paper').textContent = shortenPaper(paper);

  var colorText = getSelectedText('MY_type');
  if (colorText === '-') colorText = getSelectedText('color');
  var sides = document.getElementById('POtype') || document.getElementById('sides');
  var sidesText = sides && sides.value === '2' ? '양면' : '단면';
  if (colorText !== '-') {
    document.getElementById('qf-color').textContent = sidesText + (colorText.indexOf('4도') > -1 ? '4도' : colorText);
  }

  var sizeText = getSelectedText('PN_type') || '-';
  if (sizeText === '-') sizeText = getSelectedText('size') || '-';
  if (sizeText === '-' && pd && pd.specData) {
    var g = pd.specData.garo, s = pd.specData.sero;
    if (g && s) sizeText = g + 'x' + s;
  }
  var sizeMatch = sizeText.match(/([A-Z]\d|[0-9]+절|\d+x\d+)/i);
  document.getElementById('qf-size').textContent = sizeMatch ? sizeMatch[0] : (sizeText.length > 12 ? sizeText.substring(0,12) : sizeText);

  var qtyText = getSelectedText('MY_amount');
  if (qtyText === '-') qtyText = getSelectedText('mesu') || getSelectedText('quantity') || '-';
  document.getElementById('qf-qty').textContent = qtyText === '' || qtyText === '-' || qtyText.indexOf('선택') > -1 ? '-' : shortenQty(qtyText);
}

function updateQfOptions() {
  var container = document.getElementById('qf-options-list');
  var html = '';
  var optTotal = 0;

  var coatingEnabled = document.getElementById('coating_enabled');
  var coatingType = document.getElementById('coating_type');
  var coatingPrice = parseInt(document.getElementById('coating_price')?.value) || 0;
  if (coatingEnabled && coatingEnabled.checked && coatingType && coatingType.value) {
    html += '<div class="qd-opt-item"><span class="qd-opt-name"><span class="qd-opt-dot"></span>' +
      getOptionLabel('coating', coatingType.value) + '</span><span class="qd-opt-val">' +
      fmtNum(coatingPrice) + '</span></div>';
    optTotal += coatingPrice;
  }

  var foldingEnabled = document.getElementById('folding_enabled');
  var foldingType = document.getElementById('folding_type');
  var foldingPrice = parseInt(document.getElementById('folding_price')?.value) || 0;
  if (foldingEnabled && foldingEnabled.checked && foldingType && foldingType.value) {
    html += '<div class="qd-opt-item"><span class="qd-opt-name"><span class="qd-opt-dot"></span>' +
      getOptionLabel('folding', foldingType.value) + '</span><span class="qd-opt-val">' +
      fmtNum(foldingPrice) + '</span></div>';
    optTotal += foldingPrice;
  }

  var creasingEnabled = document.getElementById('creasing_enabled');
  var creasingLines = document.getElementById('creasing_lines');
  var creasingPrice = parseInt(document.getElementById('creasing_price')?.value) || 0;
  if (creasingEnabled && creasingEnabled.checked && creasingLines && creasingLines.value) {
    html += '<div class="qd-opt-item"><span class="qd-opt-name"><span class="qd-opt-dot"></span>' +
      getOptionLabel('creasing', creasingLines.value) + '</span><span class="qd-opt-val">' +
      fmtNum(creasingPrice) + '</span></div>';
    optTotal += creasingPrice;
  }

  container.innerHTML = html || '<div class="qd-opt">선택 없음</div>';
  return optTotal;
}

function parsePrice(val) {
  if (!val) return 0;
  return parseInt(String(val).replace(/,/g, '')) || 0;
}

var _lastTotal = 0;

var QF_PRODUCT_NAMES = {
  inserted:'전단지', sticker_new:'스티커', namecard:'명함',
  envelope:'봉투', littleprint:'포스터', msticker:'자석스티커',
  merchandisebond:'상품권', cadarok:'카다록', ncrflambeau:'NCR양식지'
};

function qfGetProductInfo() {
  var m = location.pathname.match(/mlangprintauto\/([^/]+)/);
  var t = m ? m[1] : '';
  return { type: t, name: QF_PRODUCT_NAMES[t] || t };
}

function qfRequestQuote() {
  if (_lastTotal <= 0) {
    alert('가격이 계산되지 않았습니다.\n사양을 선택해주세요.');
    return;
  }
  var user = window._qfUser || {};
  if (user.logged_in && user.name && user.email) {
    if (confirm(user.name + '님(' + user.email + ')에게\n견적서를 이메일로 발송할까요?')) {
      qfSendQuote(user.name, user.phone || '', user.email);
    }
  } else {
    qfOpenModal(user);
  }
}

function qfOpenModal(user) {
  var modal = document.getElementById('qfModal');
  if (!modal) return;
  document.getElementById('qfm-name').value = (user && user.name) || '';
  document.getElementById('qfm-phone').value = (user && user.phone) || '';
  document.getElementById('qfm-email').value = (user && user.email) || '';
  document.getElementById('qfm-error').style.display = 'none';
  modal.style.display = 'flex';
  var fields = ['qfm-name', 'qfm-phone', 'qfm-email'];
  for (var i = 0; i < fields.length; i++) {
    var f = document.getElementById(fields[i]);
    if (f && !f.value.trim()) { f.focus(); break; }
  }
}

function qfCloseModal() {
  var modal = document.getElementById('qfModal');
  if (modal) modal.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') qfCloseModal();
});
document.addEventListener('click', function(e) {
  if (e.target && e.target.id === 'qfModal') qfCloseModal();
});

function qfShowError(msg) {
  var el = document.getElementById('qfm-error');
  if (el) { el.textContent = msg; el.style.display = 'block'; }
}

function qfShowToast(msg, isError) {
  var toast = document.getElementById('qfToast');
  if (!toast) return;
  toast.textContent = msg;
  toast.style.background = isError ? '#dc2626' : '#1e293b';
  toast.style.display = 'block';
  setTimeout(function() { toast.style.display = 'none'; }, 3500);
}

function qfCollectSpecData() {
  return {
    paper: (document.getElementById('qf-paper') || {}).textContent || '-',
    color: (document.getElementById('qf-color') || {}).textContent || '-',
    size: (document.getElementById('qf-size') || {}).textContent || '-',
    qty: (document.getElementById('qf-qty') || {}).textContent || '-'
  };
}

function qfCollectPriceData() {
  return {
    print: parsePrice((document.getElementById('qf-print-price') || {}).textContent),
    design: parsePrice((document.getElementById('qf-design-price') || {}).textContent),
    option: parsePrice((document.getElementById('qf-option-price') || {}).textContent),
    subtotal: parsePrice((document.getElementById('qf-subtotal') || {}).textContent),
    vat: parsePrice((document.getElementById('qf-vat') || {}).textContent),
    total: _lastTotal
  };
}

function qfCollectOptionsText() {
  var items = document.querySelectorAll('#qf-options-list .qd-opt-name');
  var arr = [];
  for (var i = 0; i < items.length; i++) {
    var txt = items[i].textContent.replace(/^\s*[●•]\s*/, '').trim();
    if (txt) arr.push(txt);
  }
  return arr.join(', ');
}

function qfSubmitQuote() {
  var name = document.getElementById('qfm-name').value.trim();
  var phone = document.getElementById('qfm-phone').value.trim();
  var email = document.getElementById('qfm-email').value.trim();
  if (!name) { qfShowError('이름/상호를 입력해주세요.'); return; }
  if (!phone || !/^[\d\-]{8,15}$/.test(phone)) { qfShowError('올바른 전화번호를 입력해주세요.'); return; }
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { qfShowError('올바른 이메일을 입력해주세요.'); return; }
  document.getElementById('qfm-error').style.display = 'none';
  qfSendQuote(name, phone, email);
}

function qfSendQuote(name, phone, email) {
  var product = qfGetProductInfo();
  var spec = qfCollectSpecData();
  var price = qfCollectPriceData();
  var opts = qfCollectOptionsText();

  var btn = document.querySelector('.qfm-submit');
  if (btn) { btn.disabled = true; btn.textContent = '발송 중...'; }

  var payload = {
    name: name, phone: phone, email: email,
    product_type: product.type, product_name: product.name,
    spec_paper: spec.paper, spec_color: spec.color,
    spec_size: spec.size, spec_quantity: spec.qty,
    price_print: price.print, price_design: price.design,
    price_option: price.option, price_subtotal: price.subtotal,
    price_vat: price.vat, price_total: price.total,
    options_detail: opts
  };

  fetch('/includes/quote_request_api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(payload)
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      qfCloseModal();
      qfShowToast('견적서가 이메일로 발송되었습니다.', false);
    } else {
      qfShowError(data.message || '발송에 실패했습니다.');
      qfShowToast(data.message || '발송 실패', true);
    }
  })
  .catch(function(e) {
    qfShowError('네트워크 오류가 발생했습니다.');
    qfShowToast('네트워크 오류', true);
  })
  .finally(function() {
    if (btn) { btn.disabled = false; btn.textContent = '견적서 발송'; }
  });
}

function updateQfPricing() {
  var pd = window.currentPriceData;
  if (!pd) return;

  // 두 가지 가격 포맷 지원:
  // 전단지(inserted): Price, DS_Price, Order_Price, VAT_PriceForm, Total_PriceForm (AJAX 레거시)
  // 기타 품목: base_price, design_price, total_price(공급가), total_with_vat, vat_price (JS 계산)
  var isSticker = (pd.raw_price !== undefined || pd.raw_price_vat !== undefined);
  var isNewFormat = (pd.base_price !== undefined || pd.total_price !== undefined);

  var printPrice, designPrice, subtotal, vatPrice, totalWithVat;

  if (isSticker) {
    subtotal = parsePrice(pd.raw_price) || parsePrice(pd.price);
    totalWithVat = parsePrice(pd.raw_price_vat) || parsePrice(pd.price_vat);
    var editFee = parseInt((document.getElementById('stickerForm') || {}).uhyung?.value) || 0;
    printPrice = subtotal - editFee;
    designPrice = editFee;
    vatPrice = totalWithVat - subtotal;
  } else if (isNewFormat) {
    printPrice = parsePrice(pd.base_price);
    designPrice = parsePrice(pd.design_price);
    subtotal = parsePrice(pd.total_supply_price || pd.total_price);
    vatPrice = parsePrice(pd.vat_price || pd.vat_amount);
    totalWithVat = parsePrice(pd.final_total_with_vat || pd.total_with_vat);
    if (totalWithVat === 0 && subtotal > 0) {
      totalWithVat = subtotal + Math.round(subtotal * 0.1);
    }
    if (vatPrice === 0 && subtotal > 0) {
      vatPrice = Math.round(subtotal * 0.1);
    }
  } else {
    printPrice = parsePrice(pd.Price || pd.PriceForm);
    designPrice = parsePrice(pd.DS_Price || pd.DS_PriceForm);
    var orderPrice = parsePrice(pd.Order_Price || pd.Order_PriceForm);
    vatPrice = parsePrice(pd.VAT_PriceForm);
    var totalPrice = parsePrice(pd.Total_PriceForm);
    var optionTotal = parseInt(document.getElementById('additional_options_total')?.value) || 0;
    subtotal = orderPrice + optionTotal;
    var vatOnOptions = Math.round(optionTotal * 0.1);
    totalWithVat = totalPrice + optionTotal + vatOnOptions;
    vatPrice = vatPrice + vatOnOptions;
    if (totalPrice === 0 && orderPrice === 0) { totalWithVat = 0; subtotal = 0; }
  }

  document.getElementById('qf-print-price').textContent = fmtNum(printPrice);

  var designRow = document.getElementById('qf-design-row');
  if (designPrice > 0) {
    designRow.style.display = '';
    document.getElementById('qf-design-price').textContent = fmtNum(designPrice);
  } else {
    designRow.style.display = 'none';
  }

  var optionRow = document.getElementById('qf-option-row');
  var optVal = isSticker ? 0 : (isNewFormat ? parsePrice(pd.additional_options_total) : (parseInt(document.getElementById('additional_options_total')?.value) || 0));
  if (optVal > 0) {
    optionRow.style.display = '';
    document.getElementById('qf-option-price').textContent = fmtNum(optVal);
  } else {
    optionRow.style.display = 'none';
  }

  document.getElementById('qf-subtotal').textContent = fmtNum(subtotal);
  document.getElementById('qf-vat').textContent = fmtNum(vatPrice);

  var totalEl = document.getElementById('qf-total');
  if (totalWithVat !== _lastTotal) {
    animatePrice(totalEl, totalWithVat, 800);
    _lastTotal = totalWithVat;
  }
}

function updateQf() {
  updateQfSpecs();
  updateQfOptions();
  updateQfPricing();
}

document.addEventListener('priceUpdated', function() { updateQf(); });

document.addEventListener('DOMContentLoaded', function() {
  var specIds = ['MY_type','MY_Fsd','PN_type','POtype','MY_amount','ordertype','jong','color','size','sides','mesu','quantity','uhyung','domusong','garo','sero'];
  specIds.forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function() { setTimeout(updateQfSpecs, 150); });
  });

  ['coating_enabled','folding_enabled','creasing_enabled'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function() { setTimeout(updateQf, 200); });
  });

  ['coating_type','folding_type','creasing_lines'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function() { setTimeout(updateQf, 200); });
  });

  updateQfSpecs();

  var _lastPdHash = '';
  var pollCount = 0;
  var pollInterval = setInterval(function() {
    pollCount++;
    var pd = window.currentPriceData;
    if (pd) {
      var hash = (pd.total_with_vat || 0) + '|' + (pd.Total_PriceForm || 0) + '|' + (pd.base_price || 0) + '|' + (pd.total_price || 0) + '|' + (pd.raw_price_vat || 0);
      var hasPrice = parsePrice(pd.Order_PriceForm) > 0 || parsePrice(pd.total_price) > 0 || parsePrice(pd.base_price) > 0 || parsePrice(pd.total_with_vat) > 0 || parsePrice(pd.raw_price_vat) > 0;
      if (hasPrice && hash !== _lastPdHash) {
        _lastPdHash = hash;
        updateQf();
      }
    }
    if (pollCount > 120) clearInterval(pollInterval);
  }, 500);
});
