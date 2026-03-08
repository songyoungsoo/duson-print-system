/**
 * 프리미엄 옵션 DB 로더
 * DB에서 옵션 데이터를 fetch하여 JS 클래스의 basePrices를 덮어씀
 * 실패 시 기존 하드코딩 값 그대로 사용 (fallback 보장)
 *
 * Usage:
 *   const dbPrices = await loadPremiumOptionsFromDB('namecard');
 *   if (dbPrices) manager.applyDBPrices(dbPrices);
 */
async function loadPremiumOptionsFromDB(productType) {
    try {
        const res = await fetch('/api/premium_options.php?product_type=' + encodeURIComponent(productType));
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (!data.success || !data.options) throw new Error('Invalid response');
        return data.options;
    } catch (e) {
        console.warn('[PremiumOptions] DB 로드 실패, 하드코딩 fallback 사용:', e.message);
        return null;
    }
}

/**
 * PremiumOptionsGeneric — 범용 프리미엄 옵션 클래스
 * DB에서 옵션을 fetch하여 동적으로 UI를 렌더링하고 가격을 계산
 * namecard 외 5개 품목(merchandisebond, inserted, littleprint, cadarok, envelope)에서 사용
 *
 * @version 1.0
 * @date 2026-02-26
 */
class PremiumOptionsGeneric {
    /**
     * @param {string} productType - API product_type (예: 'merchandisebond')
     * @param {string} containerId - 옵션 렌더링 컨테이너 ID (예: 'premiumOptionsSection')
     * @param {string} quantityElementId - 수량 select ID (예: 'MY_amount')
     */
    constructor(productType, containerId, quantityElementId) {
        this.productType = productType;
        this.containerId = containerId;
        this.quantityElementId = quantityElementId;
        this.options = [];
        this.currentQuantity = 500;
        this.styleInjected = false;
    }

    /**
     * 초기화: API fetch → 옵션 0개면 섹션 숨김, 아니면 렌더링
     */
    async init() {
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.warn('[PremiumOptionsGeneric] Container not found:', this.containerId);
            return;
        }

        // 수량 초기값 읽기
        const qtyEl = document.getElementById(this.quantityElementId);
        if (qtyEl) {
            this.currentQuantity = parseInt(qtyEl.value) || 500;
        }

        try {
            const data = await loadPremiumOptionsFromDB(this.productType);
            if (!data || data.length === 0) {
                container.style.display = 'none';
                console.log('[PremiumOptionsGeneric] No options for', this.productType, '→ section hidden');
                return;
            }
            this.options = data;
            this.injectStyles();
            this.render(container);
            this.setupQuantityListener();
            this.calculateTotal();
            console.log('[PremiumOptionsGeneric] Initialized for', this.productType, 'with', data.length, 'options');
        } catch (e) {
            container.style.display = 'none';
            console.warn('[PremiumOptionsGeneric] Init failed:', e.message);
        }
    }

    /**
     * CSS 스타일 동적 주입 (additional-options.css 클래스와 동일)
     */
    injectStyles() {
        if (this.styleInjected || document.getElementById('premium-options-generic-style')) return;
        const style = document.createElement('style');
        style.id = 'premium-options-generic-style';
        style.textContent = `
            .premium-generic-section { margin-top: 6px; }
            .premium-generic-section .option-headers-row {
                display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
                padding: 4px 8px; background: #f8f9fa; border-radius: 6px;
                border: 1px solid #e0e0e0; margin-bottom: 0;
            }
            .premium-generic-section .option-checkbox-group {
                display: flex; align-items: center; gap: 3px;
            }
            .premium-generic-section .option-toggle { width: 12px; height: 12px; cursor: pointer; }
            .premium-generic-section .toggle-label {
                font-size: 12px; font-weight: 500; color: #495057; cursor: pointer; white-space: nowrap;
            }
            .premium-generic-section .option-price-display { margin-left: auto; }
            .premium-generic-section .option-price-total {
                font-weight: bold; color: #718096; font-size: 12px;
            }
            .premium-generic-section .option-details {
                padding: 3px 8px; background: #fff; border: 1px solid #e0e0e0;
                border-top: none; border-radius: 0 0 6px 6px;
            }
            .premium-generic-section .option-select {
                width: 100%; padding: 3px 6px; border: 1px solid #ced4da;
                border-radius: 3px; font-size: 13px; color: #495057;
            }
            .premium-generic-section .option-note {
                font-size: 11px; color: #666; margin-top: 4px;
            }
        `;
        document.head.appendChild(style);
        this.styleInjected = true;
    }

    /**
     * 옵션 UI 렌더링
     * @param {HTMLElement} container
     */
    render(container) {
        container.style.display = '';
        container.className = 'premium-generic-section';

        // 헤더 행: 체크박스들 + 총액 표시
        let headerHtml = '<div class="option-headers-row">';
        this.options.forEach(opt => {
            const safeId = 'po_' + opt.option_id;
            headerHtml += `
                <div class="option-checkbox-group">
                    <input type="checkbox" id="${safeId}_enabled" class="option-toggle" value="1">
                    <label for="${safeId}_enabled" class="toggle-label">${this.escapeHtml(opt.option_name)}</label>
                </div>`;
        });
        headerHtml += `
            <div class="option-price-display">
                <span class="option-price-total" id="premiumPriceTotal">(+0원)</span>
            </div>
        </div>`;

        // 상세 패널: 각 옵션의 variant select
        let detailsHtml = '';
        this.options.forEach(opt => {
            const safeId = 'po_' + opt.option_id;
            if (!opt.variants || opt.variants.length === 0) return;

            detailsHtml += `<div class="option-details" id="${safeId}_options" style="display:none;">`;
            detailsHtml += `<select id="${safeId}_variant" class="option-select">`;
            detailsHtml += '<option value="">선택하세요</option>';
            opt.variants.forEach(v => {
                const priceLabel = this.formatPriceLabel(v.pricing_config);
                const selected = v.is_default ? ' selected' : '';
                detailsHtml += `<option value="${v.variant_id}" data-pricing='${JSON.stringify(v.pricing_config)}'${selected}>${this.escapeHtml(v.variant_name)} ${priceLabel}</option>`;
            });
            detailsHtml += '</select>';
            detailsHtml += `<input type="hidden" id="${safeId}_price" value="0">`;
            detailsHtml += '</div>';
        });

        // 숨겨진 총액 필드 (양쪽 이름 모두 지원)
        const hiddenFields = `
            <input type="hidden" name="premium_options_total" id="premium_options_total" value="0">
            <input type="hidden" name="additional_options_total" id="additional_options_total" value="0">
        `;

        container.innerHTML = headerHtml + detailsHtml + hiddenFields;

        // 이벤트 바인딩
        this.options.forEach(opt => {
            const safeId = 'po_' + opt.option_id;
            const toggle = document.getElementById(safeId + '_enabled');
            const details = document.getElementById(safeId + '_options');
            const variantSelect = document.getElementById(safeId + '_variant');

            if (toggle && details) {
                toggle.addEventListener('change', () => {
                    details.style.display = toggle.checked ? 'block' : 'none';
                    if (!toggle.checked) {
                        const priceField = document.getElementById(safeId + '_price');
                        if (priceField) priceField.value = '0';
                        if (variantSelect) variantSelect.selectedIndex = 0;
                    }
                    this.calculateTotal();
                });
            }
            if (variantSelect) {
                variantSelect.addEventListener('change', () => this.calculateTotal());
            }
        });
    }

    /**
     * 가격 라벨 포맷 (select option 텍스트용)
     */
    formatPriceLabel(pc) {
        if (!pc) return '';

        // Format 1: 수량 연동 (namecard, merchandisebond: base_500 + per_unit)
        if (pc.base_500 !== undefined) {
            const base = Number(pc.base_500).toLocaleString();
            const per = pc.per_unit ? Number(pc.per_unit).toLocaleString() : '0';
            const addFee = pc.additional_fee ? ` +${Number(pc.additional_fee).toLocaleString()}원` : '';
            return `(500매 이하 ${base}원, 초과시 매당 ${per}원${addFee})`;
        }

        // Format 2: 구간별 (envelope: tiers[])
        if (pc.tiers && Array.isArray(pc.tiers)) {
            const parts = pc.tiers.map(t => `${t.max_qty}매 이하 ${Number(t.price).toLocaleString()}원`);
            if (pc.over_1000_per_unit) {
                parts.push(`초과시 매당 ${Number(pc.over_1000_per_unit).toLocaleString()}원`);
            }
            return `(${parts.join(', ')})`;
        }

        // Format 3: 정액 (inserted, littleprint, cadarok: base_price)
        if (pc.base_price !== undefined) {
            return `(${Number(pc.base_price).toLocaleString()}원)`;
        }

        return '';
    }

    /**
     * 개별 옵션 가격 계산 (3가지 pricing_config 형식 지원)
     * Format 1 (수량연동): base_500, per_unit, additional_fee
     * Format 2 (구간별): tiers[], over_1000_per_unit
     * Format 3 (정액): base_price
     */
    calculateOptionPrice(pricingConfig, quantity) {
        if (!pricingConfig) return 0;
        const qty = quantity || this.currentQuantity;

        // Format 1: 수량 연동 (namecard, merchandisebond)
        if (pricingConfig.base_500 !== undefined) {
            const base = Number(pricingConfig.base_500) || 0;
            const perUnit = Number(pricingConfig.per_unit) || 0;
            const addFee = Number(pricingConfig.additional_fee) || 0;
            if (qty <= 500) return base;
            return base + ((qty - 500) * perUnit) + addFee;
        }

        // Format 2: 구간별 (envelope)
        if (pricingConfig.tiers && Array.isArray(pricingConfig.tiers)) {
            let price = 0;
            for (const tier of pricingConfig.tiers) {
                if (qty <= tier.max_qty) {
                    price = Number(tier.price) || 0;
                    break;
                }
            }
            if (price === 0 && pricingConfig.over_1000_per_unit) {
                const lastTier = pricingConfig.tiers[pricingConfig.tiers.length - 1];
                price = (Number(lastTier.price) || 0) + ((qty - lastTier.max_qty) * Number(pricingConfig.over_1000_per_unit));
            }
            return price;
        }

        // Format 3: 정액 (inserted, littleprint, cadarok)
        if (pricingConfig.base_price !== undefined) {
            return Number(pricingConfig.base_price) || 0;
        }

        return 0;
    }

    /**
     * 전체 옵션 합산 → hidden field 업데이트 → 메인 calculatePrice() 호출
     */
    calculateTotal() {
        let total = 0;

        this.options.forEach(opt => {
            const safeId = 'po_' + opt.option_id;
            const toggle = document.getElementById(safeId + '_enabled');
            const variantSelect = document.getElementById(safeId + '_variant');
            const priceField = document.getElementById(safeId + '_price');

            if (!toggle || !toggle.checked) {
                if (priceField) priceField.value = '0';
                return;
            }

            let price = 0;
            if (variantSelect && variantSelect.value) {
                const selectedOption = variantSelect.options[variantSelect.selectedIndex];
                const pricingStr = selectedOption.getAttribute('data-pricing');
                if (pricingStr) {
                    try {
                        const pc = JSON.parse(pricingStr);
                        price = this.calculateOptionPrice(pc, this.currentQuantity);
                    } catch (e) { /* ignore parse error */ }
                }
            } else if (opt.variants && opt.variants.length > 0) {
                // 체크만 하고 variant 미선택 시 → 기본(첫 번째) variant 가격
                const defaultV = opt.variants.find(v => v.is_default) || opt.variants[0];
                if (defaultV && defaultV.pricing_config) {
                    price = this.calculateOptionPrice(defaultV.pricing_config, this.currentQuantity);
                }
            }

            if (priceField) priceField.value = price;
            total += price;
        });

        // 양쪽 hidden field 모두 업데이트 (호환성)
        const ptField = document.getElementById('premium_options_total');
        const atField = document.getElementById('additional_options_total');
        if (ptField) ptField.value = total;
        if (atField) atField.value = total;

        // 총액 표시 업데이트
        const totalDisplay = document.getElementById('premiumPriceTotal');
        if (totalDisplay) {
            totalDisplay.textContent = `(+${total.toLocaleString()}원)`;
            totalDisplay.style.color = total > 0 ? '#d4af37' : '#718096';
        }

        // 메인 가격 계산 함수 호출 (자동모드로 alert 방지)
        if (typeof autoCalculatePrice === 'function') {
            autoCalculatePrice();
        } else if (typeof calculatePrice === 'function') {
            calculatePrice(true);
        }

    /**
     * 수량 변경 시 재계산
     */
    updateQuantity(qty) {
        this.currentQuantity = parseInt(qty) || 500;
        this.calculateTotal();
    }

    /**
     * 수량 select 변경 이벤트 리스너 설정
     */
    setupQuantityListener() {
        const qtyEl = document.getElementById(this.quantityElementId);
        if (!qtyEl) return;
        this._qtyHandler = (e) => this.updateQuantity(e.target.value);
        qtyEl.addEventListener('change', this._qtyHandler);
    }

    /**
     * 현재 선택된 옵션 정보 반환 (주문 데이터용)
     */
    getSelectedOptions() {
        const result = {};
        this.options.forEach(opt => {
            const safeId = 'po_' + opt.option_id;
            const toggle = document.getElementById(safeId + '_enabled');
            if (toggle && toggle.checked) {
                const variantSelect = document.getElementById(safeId + '_variant');
                const priceField = document.getElementById(safeId + '_price');
                result[opt.option_name] = {
                    enabled: true,
                    variant_id: variantSelect ? variantSelect.value : null,
                    price: priceField ? parseInt(priceField.value) || 0 : 0
                };
            }
        });
        result.premium_options_total = document.getElementById('premium_options_total')?.value || '0';
        return result;
    }

    /**
     * 수량 읽기
     */
    getQuantity() {
        const qtyEl = document.getElementById(this.quantityElementId);
        return qtyEl ? (parseInt(qtyEl.value) || 500) : 500;
    }

    /**
     * HTML 이스케이프
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

// 전역 노출
window.PremiumOptionsGeneric = PremiumOptionsGeneric;
