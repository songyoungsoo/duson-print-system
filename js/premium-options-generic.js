/**
 * PremiumOptionsGeneric - Config-driven 프리미엄 옵션 시스템
 * PremiumOptionsConfig.php (SSOT)에서 설정을 받아 동적으로 UI 렌더링 + 가격 계산
 */
class PremiumOptionsGeneric {
    constructor(productType, containerId, quantityFieldId, config) {
        this.productType = productType;
        this.container = document.getElementById(containerId);
        this.quantityFieldId = quantityFieldId;
        this.config = config || {};
        this.currentQuantity = 500;
    }

    init() {
        if (!this.container) return;
        if (!this.config || Object.keys(this.config).length === 0) {
            console.warn('[PremiumOptions] 설정이 비어있습니다:', this.productType);
            return;
        }
        this.render();
        this.bindQuantityChange();
        this.container.style.display = '';
        console.log('[PremiumOptions] 초기화 완료:', this.productType, Object.keys(this.config).length + '개 옵션');
    }

    render() {
        var keys = Object.keys(this.config);

        var html = '<div class="premium-options-wrapper" style="margin-top:15px;">';

        html += '<div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:8px 12px; background:#f8f9fa; border-radius:8px; border:1px solid #e0e0e0;">';
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var opt = this.config[key];
            html += '<div style="display:flex; align-items:center; gap:4px;">';
            html += '<input type="checkbox" id="' + key + '_enabled" class="option-toggle" data-key="' + key + '" style="width:16px; height:16px; cursor:pointer;">';
            html += '<label for="' + key + '_enabled" style="font-size:12px; font-weight:500; color:#495057; cursor:pointer; white-space:nowrap;">' + this._esc(opt.name) + '</label>';
            html += '</div>';
        }
        html += '<div style="margin-left:auto;">';
        html += '<span id="premiumPriceTotal" style="font-weight:bold; color:#718096; font-size:12px;">(+0원)</span>';
        html += '</div>';
        html += '</div>';

        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var opt = this.config[key];
            html += '<div id="' + key + '_options" style="display:none; padding:8px 12px; background:#fff; border:1px solid #e0e0e0; border-top:none; border-radius:0 0 8px 8px;">';
            if (opt.type === 'select' && opt.variants) {
                html += '<select id="' + key + '_type" data-key="' + key + '" style="width:100%; padding:6px 10px; border:1px solid #ced4da; border-radius:4px; font-size:13px; color:#495057;">';
                var vKeys = Object.keys(opt.variants);
                for (var j = 0; j < vKeys.length; j++) {
                    var vk = vKeys[j];
                    var vv = opt.variants[vk];
                    var label = typeof vv === 'object' ? vv.label : vv;
                    html += '<option value="' + this._esc(vk) + '">' + this._esc(label) + '</option>';
                }
                html += '</select>';
            }
            html += '<input type="hidden" id="' + key + '_price" value="0">';
            html += '</div>';
        }

        html += '</div>';
        this.container.innerHTML = html;
        this._bindEvents();
    }

    _bindEvents() {
        var self = this;
        var toggles = this.container.querySelectorAll('.option-toggle');
        for (var i = 0; i < toggles.length; i++) {
            toggles[i].addEventListener('change', function(e) {
                self._handleToggle(e.target);
            });
        }

        var selects = this.container.querySelectorAll('select');
        for (var j = 0; j < selects.length; j++) {
            selects[j].addEventListener('change', function() {
                self.calculateAndUpdate();
            });
        }
    }

    bindQuantityChange() {
        var self = this;
        var qField = document.getElementById(this.quantityFieldId);
        if (!qField) return;

        var handler = function(e) {
            self.updateQuantity(e.target.value);
        };
        qField.removeEventListener('change', handler);
        qField.addEventListener('change', handler);
    }

    _handleToggle(toggle) {
        var key = toggle.getAttribute('data-key');
        var detailsDiv = document.getElementById(key + '_options');

        if (toggle.checked) {
            if (detailsDiv) detailsDiv.style.display = 'block';
        } else {
            if (detailsDiv) detailsDiv.style.display = 'none';
            var priceField = document.getElementById(key + '_price');
            if (priceField) priceField.value = '0';
            var selectEl = document.getElementById(key + '_type');
            if (selectEl) selectEl.selectedIndex = 0;
        }
        this.calculateAndUpdate();
    }

    updateQuantity(val) {
        this.currentQuantity = parseInt(val) || 500;
        this.calculateAndUpdate();
    }

    _calcPrice(key) {
        var opt = this.config[key];
        if (!opt) return 0;

        var qty = this.currentQuantity;
        var baseQty = opt.base_qty || 0;
        var basePrice = opt.base_price || 0;
        var perUnit = opt.per_unit || 0;

        // 정액 옵션 (per_unit === 0 && base_qty === 0)
        if (perUnit === 0) return basePrice;

        // 수량 비례 옵션
        var price = basePrice;
        if (baseQty > 0 && qty > baseQty) {
            price += (qty - baseQty) * perUnit;
        }

        // variant별 추가금
        if (opt.type === 'select' && opt.variants) {
            var typeEl = document.getElementById(key + '_type');
            if (typeEl) {
                var selectedVariant = opt.variants[typeEl.value];
                if (selectedVariant && typeof selectedVariant === 'object' && selectedVariant.additional_fee) {
                    price += selectedVariant.additional_fee;
                }
            }
        }

        return Math.round(price);
    }

    calculateAndUpdate() {
        var total = 0;
        var keys = Object.keys(this.config);

        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var enabledEl = document.getElementById(key + '_enabled');
            var priceField = document.getElementById(key + '_price');

            if (enabledEl && enabledEl.checked) {
                var price = this._calcPrice(key);
                total += price;
                if (priceField) priceField.value = price;
            } else {
                if (priceField) priceField.value = '0';
            }
        }

        var totalField = document.getElementById('premium_options_total');
        if (totalField) totalField.value = total;

        var totalDisplay = document.getElementById('premiumPriceTotal');
        if (totalDisplay) {
            totalDisplay.textContent = '(+' + total.toLocaleString() + '원)';
            totalDisplay.style.color = total > 0 ? '#d4af37' : '#718096';
        }

        if (typeof calculatePrice === 'function') {
            calculatePrice();
        }
    }

    getSelectedOptions() {
        var result = {};
        var keys = Object.keys(this.config);

        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var enabledEl = document.getElementById(key + '_enabled');
            if (enabledEl && enabledEl.checked) {
                result[key + '_enabled'] = 1;
                var typeEl = document.getElementById(key + '_type');
                if (typeEl) result[key + '_type'] = typeEl.value;
                var priceEl = document.getElementById(key + '_price');
                if (priceEl) result[key + '_price'] = parseInt(priceEl.value) || 0;
            }
        }
        result.premium_options_total = parseInt(document.getElementById('premium_options_total')?.value) || 0;
        return result;
    }

    reset() {
        var keys = Object.keys(this.config);
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            var enabledEl = document.getElementById(key + '_enabled');
            if (enabledEl) enabledEl.checked = false;
            var detailsDiv = document.getElementById(key + '_options');
            if (detailsDiv) detailsDiv.style.display = 'none';
            var priceField = document.getElementById(key + '_price');
            if (priceField) priceField.value = '0';
            var priceDisplay = document.getElementById(key + '_price_display');
            if (priceDisplay) priceDisplay.textContent = '';
        }
        var totalField = document.getElementById('premium_options_total');
        if (totalField) totalField.value = '0';
        var totalDisplay = document.getElementById('premiumPriceTotal');
        if (totalDisplay) {
            totalDisplay.textContent = '(+0원)';
            totalDisplay.style.color = '#718096';
        }
    }

    getPremiumOptionsTotal() {
        return parseInt(document.getElementById('premium_options_total')?.value) || 0;
    }

    _esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

function getPremiumOptionsTotal() {
    return parseInt(document.getElementById('premium_options_total')?.value) || 0;
}

function recalculatePremiumOptions() {
    if (window.premiumOptionsManager && typeof window.premiumOptionsManager.calculateAndUpdate === 'function') {
        window.premiumOptionsManager.calculateAndUpdate();
    }
}

window.PremiumOptionsGeneric = PremiumOptionsGeneric;
window.getPremiumOptionsTotal = getPremiumOptionsTotal;
window.recalculatePremiumOptions = recalculatePremiumOptions;
