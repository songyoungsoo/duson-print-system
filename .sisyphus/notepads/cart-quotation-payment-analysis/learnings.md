# Cart Quotation & Mobile Payment Analysis

## Date: 2026-02-18

---

## Issue 1: Cart Quotation - No Email Sending

### Files Analyzed
- **Main cart**: `/var/www/html/mlangprintauto/shop/cart.php` (1059 lines) ← PRIMARY
- **Simple cart**: `/var/www/html/mlangprintauto/cart.php` (210 lines) ← legacy/unused
- **Mobile cart**: `/var/www/html/m/mlangprintauto260104/cart.php` (186 lines) ← mobile version
- **Working reference**: `/var/www/html/includes/quote_request_api.php` (272 lines)

### Current Cart Quotation Behavior (cart.php lines 483-489, 839-926)

The cart has a "견적서 받기" (Get Quotation) button at line 483:
```php
<button type="button" onclick="showQuotation()" class="btn-quote">
    견적서 받기
</button>
```

When clicked, `showQuotation()` (line 839) simply **toggles visibility** of a hidden `#quotationSection` div — it shows a print-only quotation on screen. No email is sent.

There's also a "견적서 인쇄" (Print Quotation) button (line 827) that calls `printQuotation()` which opens a print window.

The `generateQuotePDF()` function (line 988) opens `generate_quote_pdf.php` in a new window — but **that file has NO email sending** (confirmed by grep: no mailer/mail/email/send calls).

### What's Missing vs Working System

| Feature | Working (quote_request_api.php) | Cart (cart.php) |
|---------|--------------------------------|-----------------|
| Customer info collection | ✅ name, phone, email | ✅ customer_info_modal.php (name, phone, company, email) |
| DB save (quote_requests) | ✅ INSERT with FQ-YYYYMMDD-NNN | ❌ Not saved |
| Email to customer | ✅ mailer() with HTML template | ❌ Not sent |
| Admin notification | ✅ mailer() to dsp1830@naver.com | ❌ Not sent |
| Quote number | ✅ FQ-YYYYMMDD-NNN | ❌ None |

### Root Cause
The cart quotation is **print-only** — it was designed for browser printing, not email delivery. The `customer_info_modal.php` collects customer info but only passes it to `generate_quote_pdf.php` as URL params for PDF generation, not for email sending.

### What Needs to Be Added
1. An API endpoint (similar to `quote_request_api.php`) that:
   - Accepts cart items + customer info
   - Saves to `quote_requests` table (or new `cart_quote_requests` table)
   - Sends email to customer with cart quotation HTML
   - Sends admin notification
2. Modify `generateQuoteWithCustomerInfo()` in `customer_info_modal.php` to call this API via AJAX
3. The email template should list ALL cart items (multi-item, unlike floating quote which is single-item)

---

## Issue 2: Mobile Payment "PC Only" Error

### Files Analyzed
- **Primary payment handler**: `/var/www/html/payment/inicis_request.php` (524 lines)
- **Alternative handler**: `/var/www/html/payment/request.php` (360 lines)
- **Config**: `/var/www/html/payment/inicis_config.php` (310 lines)

### Key Finding: `acceptmethod` Parameter

**In `inicis_request.php` (line 514):**
```html
<input type="hidden" name="acceptmethod" value="below1000:HPP(1):cardonly">
```

**In `payment/request.php` (line 356):**
```html
<input type="hidden" name="acceptmethod" value="below1000:HPP(1)">
```

### The Problem: `cardonly` Parameter

The `cardonly` value in `acceptmethod` tells KG이니시스 to **restrict payment to credit card only**. 

According to KG이니시스 documentation, the standard payment (`INIStdPay`) is a **PC-based popup payment window**. When `cardonly` is set AND the user is on mobile:

- The INIStdPay JS SDK detects the mobile browser
- It attempts to redirect to mobile payment flow
- But `cardonly` combined with the standard PC payment form causes a conflict
- Result: "PC only" error or payment window fails to open on mobile

### Additional Mobile Issue: `gopaymethod`

**In `inicis_request.php` (line 513):**
```html
<input type="hidden" name="gopaymethod" value="Card">
```

**In `payment/request.php` (line 355):**
```html
<input type="hidden" name="gopaymethod" value="Card:DirectBank:HPP">
```

`inicis_request.php` only allows `Card` (credit card), while `request.php` allows Card + DirectBank + HPP (휴대폰결제). On mobile, users often prefer HPP (phone billing) or the mobile card payment flow.

### The Real Mobile Payment Issue

KG이니시스 `INIStdPay.js` is the **PC standard payment** SDK. For mobile:
- PC: `INIStdPay.pay()` opens a popup window
- Mobile: The SDK should redirect to mobile payment page OR use `INIpayMobile` SDK

The current `inicis_request.php` uses `INIStdPay.pay('SendPayForm_id')` which is PC-only. On mobile browsers, popup windows are blocked or the payment flow breaks.

### What Needs to Be Fixed

**Option A (Simple fix):** Remove `cardonly` from `acceptmethod` and add mobile payment methods:
```html
<!-- Before -->
<input type="hidden" name="acceptmethod" value="below1000:HPP(1):cardonly">
<input type="hidden" name="gopaymethod" value="Card">

<!-- After -->
<input type="hidden" name="acceptmethod" value="below1000:HPP(1)">
<input type="hidden" name="gopaymethod" value="Card:DirectBank:HPP">
```

**Option B (Proper fix):** Add mobile detection and use appropriate payment flow:
```php
// Detect mobile
$isMobile = preg_match('/Mobile|Android|iPhone|iPad/i', $_SERVER['HTTP_USER_AGENT'] ?? '');

// Use different acceptmethod for mobile
$acceptmethod = $isMobile ? 'below1000:HPP(1)' : 'below1000:HPP(1):cardonly';
```

---

## File Summary

| File | Path | Purpose | Issue |
|------|------|---------|-------|
| Main cart | `mlangprintauto/shop/cart.php` | Cart + quotation display | No email sending |
| Customer modal | `mlangprintauto/shop/customer_info_modal.php` | Collects customer info | Goes to PDF only, not email |
| Quote PDF | `mlangprintauto/shop/generate_quote_pdf.php` | PDF generation | No email |
| Payment (new) | `payment/inicis_request.php` | KG이니시스 payment | `cardonly` + `Card` only = mobile broken |
| Payment (old) | `payment/request.php` | KG이니시스 payment | Works better (Card:DirectBank:HPP) |
| Working quote | `includes/quote_request_api.php` | Floating quote email | ✅ Working reference |

---

## Recommended Fixes

### Fix 1: Cart Quotation Email
- Create `/mlangprintauto/shop/cart_quote_api.php` (similar to `quote_request_api.php`)
- Modify `customer_info_modal.php` `generateQuoteWithCustomerInfo()` to POST to this API
- Email template should iterate over cart items (multi-item format)
- Save to `quote_requests` table with `FQ-` prefix

### Fix 2: Mobile Payment
- In `payment/inicis_request.php` line 513-514:
  - Change `gopaymethod` from `Card` to `Card:DirectBank:HPP`
  - Remove `cardonly` from `acceptmethod`
  - OR add mobile UA detection and set appropriate values

## Cart Quotation Email System (2026-02-18)

### Implementation
- Created `/mlangprintauto/shop/send_cart_quotation.php` - new AJAX endpoint
- Updated `/mlangprintauto/shop/customer_info_modal.php` - AJAX + success modal

### Pattern Used
- Followed `includes/quote_request_api.php` exactly for FQ number generation and DB save
- `quote_requests` table stores single-product fields; cart uses `product_name` as summary (e.g. "전단지 외 2건")
- `options_detail` stores company name + memo
- `spec_quantity` stores item count ("3개 품목")

### Key Decisions
- Did NOT modify `generate_quote_pdf.php` (kept PDF generation intact)
- New endpoint is separate from PDF generator (clean separation)
- Success modal shows quote number + email address + "스팸함 확인" hint
- bind_param: 18 placeholders, type string "ssssissssssiiiiiis" ✓

### bind_param Verification
- placeholders: 18, type chars: 18 ✓
- price_subtotal = totalPrice (공급가액), price_vat = totalVat (부가세), price_total = totalPriceVat (합계)
