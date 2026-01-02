# Quote Calculator Test Failures Analysis

## Test Results Summary

**Date**: 2025-12-28
**Total Products**: 9
**Passed**: 6
**Failed**: 3

### ✅ Passing Products (6/9)
1. 전단지 (inserted)
2. 명함 (namecard)
3. 스티커 (sticker)
4. 포스터 (littleprint)
5. 상품권 (merchandisebond)
6. NCR양식 (ncrflambeau)

### ❌ Failing Products (3/9)
1. 봉투 (envelope) - Error: "규격 데이터가 비어있음"
2. 자석스티커 (msticker) - Error: "규격 데이터가 비어있음"
3. 카다록 (cadarok) - Error: "규격 데이터가 비어있음"

## Root Cause Analysis

### Investigation Steps

1. ✅ Checked if specification builder functions exist → **YES, they exist**
   - `buildEnvelopeSpecification()` (line 504)
   - `buildMstickerSpecification()` (line 562)
   - `buildCadarokSpecification()` (line 586)

2. ✅ Checked if these functions are called → **YES, they are called** in `proceedWithApply()`
   - envelope: line 222
   - msticker: line 240
   - cadarok: line 249

3. ✅ Checked if quotation-modal-common.js is included → **YES, all 3 include it**

4. ✅ Checked if "견적서에 적용" button exists → **YES, all 3 have the button**

5. ❌ Checked if `window.currentPriceData` is set → **NO, it's never set**
   - These calculators READ window.currentPriceData but never SET it
   - Namecard sets it on line 766: `window.currentPriceData = priceData;`
   - Envelope, msticker, cadarok: NO assignment found

6. ❌ Checked for price calculation functions → **NO functions exist**
   - applyToQuotation() tries to call: autoCalculatePrice, calculatePrice, calc_ajax, calculatePriceAjax
   - None of these functions exist in envelope/msticker/cadarok

### Root Cause

**The 3 failing products do not have price calculation JavaScript functions that:**
1. Call their `calculate_price_ajax.php` backend
2. Store the result in `window.currentPriceData`

Without `window.currentPriceData`, the quote modal system cannot access price information, and more critically, the specification builder functions may be reading from form fields that are empty or not yet populated.

## Proposed Fix

### Option 1: Add Price Calculation Functions (Recommended)

Add AJAX price calculation functions to all 3 products following the namecard pattern:

```javascript
// Add to envelope/msticker/cadarok index.php

async function calculatePrice() {
    try {
        const formData = new FormData();

        // Add form field data
        formData.append('MY_type', document.getElementById('MY_type')?.value || '');
        formData.append('Section', document.getElementById('Section')?.value || '');
        formData.append('POtype', document.getElementById('POtype')?.value || '');
        formData.append('MY_amount', document.getElementById('MY_amount')?.value || '');
        // ... add other required fields

        const response = await fetch('calculate_price_ajax.php', {
            method: 'POST',
            body: formData
        });

        const priceData = await response.json();

        // CRITICAL: Store in window.currentPriceData
        window.currentPriceData = priceData;

        // Update price display
        updatePriceDisplay(priceData);

    } catch (error) {
        console.error('Price calculation error:', error);
    }
}

function updatePriceDisplay(priceData) {
    const priceAmount = document.getElementById('priceAmount');
    if (priceAmount && priceData.total_price) {
        priceAmount.textContent = parseInt(priceData.total_price).toLocaleString() + '원';
    }
}

// Auto-calculate when options change
document.addEventListener('DOMContentLoaded', function() {
    const formElements = ['MY_type', 'Section', 'POtype', 'MY_amount'];
    formElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', calculatePrice);
        }
    });
});
```

### Option 2: Fallback to DOM Reading (Temporary)

Modify `quotation-modal-common.js` to read price data from DOM if `window.currentPriceData` doesn't exist:

```javascript
// In proceedWithApply(), after product type detection:
if (!window.currentPriceData) {
    // Fallback: Read from DOM
    const priceElement = document.getElementById('priceAmount');
    if (priceElement) {
        const priceText = priceElement.textContent.replace(/[^0-9]/g, '');
        window.currentPriceData = {
            total_price: parseInt(priceText) || 0,
            vat_price: Math.round((parseInt(priceText) || 0) * 0.1)
        };
    }
}
```

## Recommendation

Implement **Option 1** for all 3 failing products:
- envelope
- msticker
- cadarok

This ensures:
1. ✅ Price data is properly calculated via AJAX
2. ✅ window.currentPriceData is set for quote modal integration
3. ✅ Specification data can be properly read from populated form fields
4. ✅ Consistent behavior across all 9 products

## Files to Modify

1. `/var/www/html/mlangprintauto/envelope/index.php` - Add calculatePrice() function
2. `/var/www/html/mlangprintauto/msticker/index.php` - Add calculatePrice() function
3. `/var/www/html/mlangprintauto/cadarok/index.php` - Add calculatePrice() function

## Expected Outcome

After implementing the fix, all 9 products should pass the E2E test with:
- ✅ Multi-line specification display
- ✅ Accurate price calculations
- ✅ Proper data transmission to quote table
- ✅ 100% test pass rate (9/9)
