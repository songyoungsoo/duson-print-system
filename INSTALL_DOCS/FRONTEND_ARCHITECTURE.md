# Frontend Architecture Documentation

**Duson Planning Print System (두손기획인쇄)**
**Version:** 1.0
**Last Updated:** 2026-01-18

---

## Table of Contents

1. [Overview](#1-overview)
2. [CSS Architecture](#2-css-architecture)
3. [JavaScript Architecture](#3-javascript-architecture)
4. [UI Components](#4-ui-components)
5. [Product-Specific Files](#5-product-specific-files)
6. [Installation Files Checklist](#6-installation-files-checklist)
7. [Responsive Design](#7-responsive-design)
8. [Best Practices](#8-best-practices)

---

## 1. Overview

### 1.1 Technology Stack
- **CSS:** CSS3 with CSS Custom Properties (CSS Variables)
- **JavaScript:** Vanilla ES6+ (no framework dependency)
- **Fonts:** Noto Sans KR (Korean web font)
- **Icons:** Unicode emoji + SVG inline icons

### 1.2 Design Philosophy
- **Wall Street Professional Design:** Subdued office atmosphere
- **Mobile-First Responsive:** 768px breakpoint for mobile/tablet
- **Design Token System:** Centralized CSS variables
- **Specificity-Based Cascade:** No `!important` usage (by policy)

### 1.3 Directory Structure
```
/var/www/html/
├── css/                          # Global stylesheets
│   ├── design-tokens.css         # CSS variables (SSOT)
│   ├── common-styles.css         # Main common styles
│   ├── mlang-design-system.css   # Unified design system
│   ├── product-layout.css        # Product page layouts
│   └── [product]-compact.css     # Product-specific styles
├── js/                           # JavaScript files
│   ├── common.js                 # Legacy utility functions
│   ├── common-unified.js         # Modern unified utilities
│   ├── common-price-display.js   # Price display helpers
│   ├── common-auth.js            # Authentication UI
│   └── [product].js              # Product-specific scripts
├── includes/                     # PHP include templates
│   ├── header-ui.php             # Common header UI
│   ├── mlang_css_loader.php      # Smart CSS loader
│   ├── upload_modal.php          # File upload modal
│   └── gallery_component.php     # Gallery component
└── mlangprintauto/               # Product pages
    └── [product]/                # Per-product directories
```

---

## 2. CSS Architecture

### 2.1 Design Token System (`design-tokens.css`)

The design token file is the **Single Source of Truth (SSOT)** for all styling values.

#### Color System
```css
/* Primary Brand Colors */
--color-primary: #1e3c72;
--color-primary-light: #2a5298;
--color-primary-dark: #152a54;

/* Semantic Colors */
--color-success: #28a745;
--color-danger: #dc3545;
--color-warning: #ffc107;
--color-info: #17a2b8;

/* Product-Specific Colors */
--color-namecard: #667eea;      /* Purple */
--color-envelope: #ff9800;      /* Orange */
--color-leaflet: #4caf50;       /* Green */
--color-sticker: #e91e63;       /* Pink */
--color-msticker: #9c27b0;      /* Deep Purple */
--color-poster: #2196f3;        /* Blue */
--color-cadarok: #ff5722;       /* Deep Orange */
```

#### Spacing System (4px Base Unit)
```css
--spacing-1: 4px;   --spacing-2: 8px;   --spacing-3: 12px;
--spacing-4: 16px;  --spacing-5: 20px;  --spacing-6: 24px;
--spacing-8: 32px;  --spacing-10: 40px; --spacing-12: 48px;
```

#### Typography System
```css
--font-family-primary: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
--font-size-sm: 0.875rem;   /* 14px */
--font-size-base: 1rem;     /* 16px */
--font-size-lg: 1.125rem;   /* 18px */
--font-size-xl: 1.25rem;    /* 20px */
```

### 2.2 CSS File Hierarchy

```
Level 1: design-tokens.css      (CSS Variables)
Level 2: common-styles.css      (Base/Reset styles)
Level 3: mlang-design-system.css (Component styles)
Level 4: product-layout.css     (Layout system)
Level 5: [product]-compact.css  (Product-specific overrides)
```

### 2.3 CSS File Inventory

| File | Size | Purpose |
|------|------|---------|
| `common-styles.css` | 57KB | Main common styles, header, navigation |
| `mlang-design-system.css` | 39KB | Unified design system with themes |
| `product-layout.css` | 22KB | Product page grid layouts |
| `design-tokens.css` | 8KB | CSS custom properties |
| `sticker-compact.css` | 11KB | Sticker product styles |
| `msticker-compact.css` | 13KB | Magnet sticker styles |
| `cadarok-compact.css` | 17KB | Catalog product styles |
| `merchandisebond-compact.css` | 19KB | Gift certificate styles |
| `cart-styles.css` | 4KB | Shopping cart styles |
| `unified-price-display.css` | 3KB | Price display component |
| `loading-spinner.css` | 4KB | Loading animation |
| `upload-modal-common.css` | 9KB | File upload modal |
| `responsive-layout.css` | 2KB | Responsive breakpoints |
| `unified-gallery.css` | 12KB | Product gallery styles |

### 2.4 CSS Loading Mechanism

The `mlang_css_loader.php` provides smart CSS loading:

```php
// Usage in product pages
include "../../includes/mlang_css_loader.php";
load_mlang_css('namecard');  // Loads theme-specific styles

// Auto-detection mode
load_mlang_css_auto();       // Detects product from URL path
```

---

## 3. JavaScript Architecture

### 3.1 Common Scripts

#### `common.js` - Legacy Utilities
```javascript
// Key Functions:
formatNumber(num)          // Number formatting with commas
validateForm(formName)     // Basic form validation
showLoading() / hideLoading()  // Loading indicator
ajaxRequest(url, method, data, callback)  // AJAX helper
setCookie() / getCookie() / eraseCookie()  // Cookie management
```

#### `common-unified.js` - Modern Utilities
```javascript
// Key Functions:
window.openGalleryPopup(category)   // Gallery popup
window.openUploadModal()            // File upload modal
window.closeUploadModal()           // Close upload modal
processFiles(files)                 // File processing
updateModalFileList()               // Update file list UI
```

#### `common-price-display.js` - Price Display
```javascript
// Key Functions:
updatePriceDetailsCommon(priceData, options)  // Common price display
updatePosterPriceDetails(priceData)           // Poster-specific
updateStickerPriceDetails(priceData, editFee) // Sticker-specific
updateStandardPriceDetails(priceData)         // Standard display
```

#### `common-auth.js` - Authentication UI
```javascript
// Key Functions:
showLoginModal() / hideLoginModal()  // Login modal
showLoginTab(event)                  // Switch to login tab
showRegisterTab(event)               // Switch to register tab
toggleUserMenu()                     // User dropdown menu
```

#### `loading-spinner.js` - Loading Animation
```javascript
// Key Functions:
showDusonLoading(message)    // Show branded loading spinner
hideDusonLoading()           // Hide loading spinner
attachLoadingToForms()       // Auto-attach to forms
fetchWithLoading(url, options, message)  // Fetch wrapper
```

#### `quotation-modal-common.js` - Quotation System
```javascript
// Key Functions:
applyToQuotation()           // Apply to quotation
validateRequiredFields()     // Validate required inputs
proceedWithApply()           // Process quotation application
```

### 3.2 Product-Specific Scripts

| Script | Product | Key Features |
|--------|---------|--------------|
| `namecard.js` | Business Cards | Dynamic options, price calculation |
| `sticker.js` | Stickers | Size-based pricing, material options |
| `msticker.js` | Magnet Stickers | Calculator, gallery integration |
| `envelope.js` | Envelopes | Size selection, quantity pricing |
| `poster.js` | Posters | Large format pricing |
| `merchandisebond.js` | Gift Certificates | Serial numbering options |
| `cadarok-logic.js` | Catalogs | Page count calculation |
| `inserted-logic.js` | Flyers/Leaflets | Sheet count, paper options |
| `littleprint-logic.js` | Small Print | Poster variant logic |

### 3.3 Script Dependencies

```
common.js
├── common-unified.js (extends common)
│   ├── common-price-display.js (price utilities)
│   ├── common-auth.js (auth UI)
│   └── loading-spinner.js (loading UI)
└── [product].js (product-specific)
    └── quotation-modal-common.js (quotation features)
```

---

## 4. UI Components

### 4.1 Product Navigation (`.product-nav`)

Desktop: Horizontal flex layout with wrapped buttons
Mobile: 3x3 CSS Grid layout

```css
/* Desktop */
.product-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

/* Mobile (768px) */
@media (max-width: 768px) {
    .product-nav {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 4px;
    }
}
```

**Button Classes:**
- `.nav-btn` - Base navigation button
- `.nav-btn.active` - Currently selected product

### 4.2 Price Calculator UI (`.price-display`)

```html
<div class="price-display calculated">
    <div class="price-label">Estimated Price</div>
    <div class="price-amount" id="priceAmount">100,000 won (VAT incl.)</div>
    <div class="price-details" id="priceDetails">
        <span>Print: 90,909 won</span>
        <span>VAT Incl: <span class="vat-amount">100,000 won</span></span>
    </div>
</div>
```

**Key Classes:**
- `.price-display` - Container
- `.price-display.calculated` - Active state with shadow
- `.price-amount` - Main price text (0.98rem, bold)
- `.price-details` - Breakdown (flex row, centered)
- `.vat-amount` - Red color highlight for VAT total

### 4.3 Gallery Component

**Structure:**
```html
<div class="product-gallery">
    <div class="lightbox-viewer" id="mainViewer"></div>
    <div class="thumbnail-strip">
        <img class="thumbnail active" src="...">
        <img class="thumbnail" src="...">
        <button class="gallery-more-thumb">More</button>
    </div>
</div>
```

**Key Features:**
- Lightbox viewer: 450x450px with hover zoom (135%)
- Thumbnails: 80x80px with active border highlight
- GPU-accelerated transforms (`will-change`, `translateZ(0)`)

### 4.4 File Upload Modal

**Structure:**
```html
<div id="uploadModal" class="upload-modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">Brand + Title + Close</div>
        <div class="modal-body">
            <div class="upload-container">
                <div class="upload-left">Dropzone</div>
                <div class="upload-right">Memo textarea</div>
            </div>
            <div class="uploaded-files">File list</div>
        </div>
        <div class="modal-footer">Action buttons</div>
    </div>
</div>
```

**Features:**
- Drag-and-drop file upload
- 15MB file size limit
- Supported formats: jpg, jpeg, png, pdf, ai, eps, psd, zip

### 4.5 Loading Spinner

**Branded "Duson" spinner with petal animation:**
```html
<div id="dusonLoadingOverlay" class="duson-loading-overlay">
    <div class="duson-spinner-container">
        <div class="duson-spinner">
            <div class="duson-petal"></div> x12
        </div>
        <div class="duson-center-logo">
            <span>Two Hands</span>
        </div>
    </div>
</div>
```

### 4.6 Cart Table

```html
<table class="cart-table">
    <colgroup>
        <col class="col-product">  <!-- 15% -->
        <col class="col-spec">     <!-- 42% -->
        <col class="col-qty">      <!-- 10% -->
        <col class="col-unit">     <!-- 8% -->
        <col class="col-price">    <!-- 15% -->
        <col class="col-action">   <!-- 10% -->
    </colgroup>
    <thead>
        <tr><th class="cart-th">...</th></tr>
    </thead>
    <tbody>
        <tr><td class="cart-td">...</td></tr>
    </tbody>
</table>
```

---

## 5. Product-Specific Files

### 5.1 Product Directory Structure

Each product follows this structure:
```
/mlangprintauto/[product]/
├── index.php                    # Main product page (calculator view)
├── calculate_price_ajax.php     # Price calculation API
├── add_to_basket.php            # Add to cart API
├── get_paper_types.php          # Dynamic options API
├── get_quantities.php           # Quantity options API
└── includes/
    └── product_gallery.php      # Product-specific gallery
```

### 5.2 Standard Products (9 Total)

| # | Product Name | Folder | CSS File | JS File |
|---|--------------|--------|----------|---------|
| 1 | Flyers | `inserted` | `common-styles.css` | `inserted-logic.js` |
| 2 | Stickers | `sticker_new` | `sticker-compact.css` | `sticker.js` |
| 3 | Magnet Stickers | `msticker` | `msticker-compact.css` | `msticker.js` |
| 4 | Business Cards | `namecard` | `namecard-inline-styles.css` | `namecard.js` |
| 5 | Envelopes | `envelope` | `common-styles.css` | `envelope.js` |
| 6 | Posters | `littleprint` | `poster.css` | `poster.js` |
| 7 | Gift Certificates | `merchandisebond` | `merchandisebond-compact.css` | `merchandisebond.js` |
| 8 | Catalogs | `cadarok` | `cadarok-compact.css` | `cadarok-logic.js` |
| 9 | NCR Forms | `ncrflambeau` | `common-styles.css` | (inline) |

---

## 6. Installation Files Checklist

### 6.1 Required CSS Files (Core)

```
css/
├── design-tokens.css           # REQUIRED - CSS variables
├── common-styles.css           # REQUIRED - Base styles
├── mlang-design-system.css     # REQUIRED - Component system
├── product-layout.css          # REQUIRED - Layout system
├── unified-price-display.css   # REQUIRED - Price display
├── unified-gallery.css         # REQUIRED - Gallery styles
├── cart-styles.css             # REQUIRED - Cart page
├── loading-spinner.css         # REQUIRED - Loading animation
├── upload-modal-common.css     # REQUIRED - Upload modal
├── responsive-layout.css       # REQUIRED - Responsive breakpoints
├── color-system-unified.css    # RECOMMENDED - Color variables
└── base.css                    # RECOMMENDED - Resets
```

### 6.2 Required CSS Files (Product-Specific)

```
css/
├── sticker-compact.css         # For sticker_new
├── sticker-inline-styles.css   # For sticker_new
├── msticker-compact.css        # For msticker
├── namecard-inline-styles.css  # For namecard
├── cadarok-compact.css         # For cadarok
├── merchandisebond-compact.css # For merchandisebond
└── poster.css                  # For littleprint/poster
```

### 6.3 Required JavaScript Files

```
js/
├── common.js                   # REQUIRED - Legacy utilities
├── common-unified.js           # REQUIRED - Modern utilities
├── common-price-display.js     # REQUIRED - Price display
├── common-auth.js              # REQUIRED - Auth UI
├── loading-spinner.js          # REQUIRED - Loading
├── quotation-modal-common.js   # REQUIRED - Quotation
├── gallery-unified.js          # REQUIRED - Gallery system
├── unified-gallery-popup.js    # REQUIRED - Gallery popup
├── common-gallery-popup.js     # REQUIRED - Gallery helpers
└── price-data-adapter.js       # RECOMMENDED - Price adapter
```

### 6.4 Required JavaScript Files (Product-Specific)

```
js/
├── namecard.js                 # For namecard
├── sticker.js                  # For sticker_new
├── msticker.js                 # For msticker
├── envelope.js                 # For envelope
├── poster.js                   # For littleprint
├── poster_main.js              # For poster
├── merchandisebond.js          # For merchandisebond
├── cadarok-logic.js            # For cadarok
├── inserted-logic.js           # For inserted
├── littleprint-logic.js        # For littleprint
├── merchandisebond-logic.js    # For merchandisebond
├── msticker-logic.js           # For msticker
└── sticker-logic.js            # For sticker_new
```

### 6.5 Required PHP Includes

```
includes/
├── header-ui.php               # REQUIRED - Header UI
├── mlang_css_loader.php        # REQUIRED - CSS loader
├── upload_modal.php            # REQUIRED - Upload modal
├── loading-spinner.php         # REQUIRED - Loading spinner
├── gallery_component.php       # REQUIRED - Gallery
├── unified_gallery_modal.php   # REQUIRED - Gallery modal
├── login_modal.php             # REQUIRED - Login UI
├── auth.php                    # REQUIRED - Authentication
├── functions.php               # REQUIRED - Utilities
├── QuantityFormatter.php       # REQUIRED - Quantity SSOT
├── ProductSpecFormatter.php    # REQUIRED - Spec formatting
├── ImagePathResolver.php       # REQUIRED - Image paths
├── StandardUploadHandler.php   # REQUIRED - File uploads
└── footer.php                  # RECOMMENDED - Footer
```

### 6.6 Total File Count Summary

| Category | Required | Recommended | Total |
|----------|----------|-------------|-------|
| CSS Core | 10 | 2 | 12 |
| CSS Product | 7 | 0 | 7 |
| JS Core | 10 | 1 | 11 |
| JS Product | 13 | 0 | 13 |
| PHP Includes | 13 | 1 | 14 |
| **Total** | **53** | **4** | **57** |

---

## 7. Responsive Design

### 7.1 Breakpoints

```css
/* Mobile First Approach */
/* Default: Mobile (< 768px) */

/* Tablet */
@media (min-width: 769px) and (max-width: 1024px) { ... }

/* Desktop */
@media (min-width: 1025px) { ... }

/* Small Mobile */
@media (max-width: 480px) { ... }
```

### 7.2 Grid System

```css
/* Desktop: 50/50 Two-Column */
.product-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-8);
}

/* Mobile: Single Column Stack */
@media (max-width: 1024px) {
    .product-content {
        grid-template-columns: 1fr;
    }
}
```

### 7.3 Container Widths

| Component | Max Width | Mobile Behavior |
|-----------|-----------|-----------------|
| `.product-container` | 1200px | 100% with padding |
| `.cart-container` | 1145px | Full width |
| `.mlang-container` | 1200px | Responsive padding |

---

## 8. Best Practices

### 8.1 CSS Guidelines

1. **Never use `!important`** - Use specificity hierarchy instead
2. **Use design tokens** - Reference `var(--token)` for all values
3. **Follow naming conventions** - BEM-like: `.component`, `.component__element`, `.component--modifier`
4. **Mobile-first media queries** - Start with mobile, enhance for desktop

### 8.2 JavaScript Guidelines

1. **Check element existence** - Always verify `getElementById` returns truthy
2. **Use debounce for inputs** - Prevent excessive calculations
3. **Store price data globally** - `window.currentPriceData` for quotation system
4. **Handle AJAX errors** - Always provide user feedback on failure

### 8.3 File Loading Order

```html
<!-- CSS Loading Order -->
<link rel="stylesheet" href="/css/design-tokens.css">
<link rel="stylesheet" href="/css/common-styles.css">
<link rel="stylesheet" href="/css/mlang-design-system.css">
<link rel="stylesheet" href="/css/product-layout.css">
<link rel="stylesheet" href="/css/[product]-compact.css">
<link rel="stylesheet" href="/css/loading-spinner.css">
<link rel="stylesheet" href="/css/upload-modal-common.css">

<!-- JS Loading Order -->
<script src="/js/common.js"></script>
<script src="/js/common-unified.js"></script>
<script src="/js/common-price-display.js"></script>
<script src="/js/common-auth.js"></script>
<script src="/js/loading-spinner.js"></script>
<script src="/js/[product].js"></script>
```

### 8.4 Accessibility Considerations

- All interactive elements have `tabindex="0"`
- Form inputs have associated labels
- Images have meaningful `alt` attributes
- Modals can be closed with ESC key
- Focus trapping in modals

---

## Appendix A: CSS Variable Quick Reference

```css
/* Colors */
--color-primary: #1e3c72;
--color-success: #28a745;
--color-danger: #dc3545;

/* Spacing */
--spacing-2: 8px;
--spacing-4: 16px;
--spacing-6: 24px;

/* Typography */
--font-size-sm: 0.875rem;
--font-size-base: 1rem;
--font-weight-medium: 500;
--font-weight-bold: 700;

/* Borders */
--border-radius-sm: 4px;
--border-radius-md: 8px;
--border-radius-lg: 12px;

/* Shadows */
--shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
--shadow-card: 0 10px 40px rgba(0, 0, 0, 0.1);

/* Z-Index */
--z-index-modal: 1050;
--z-index-tooltip: 1070;

/* Form Elements */
--form-input-height: 32px;
--form-input-radius: var(--border-radius-sm);
```

---

## Appendix B: File Size Summary

| Directory | File Count | Total Size |
|-----------|------------|------------|
| `/css/` | 57 files | ~650 KB |
| `/js/` | 26 files | ~600 KB |
| `/includes/` | 70+ files | ~500 KB |
| **Total Frontend** | **150+ files** | **~1.75 MB** |

---

*Document generated for Duson Planning Print System installation reference.*
*For questions, contact: songyoungsoo / yeongsu32@gmail.com*
