# 🎨 Frontend Architecture & User Interface System

## 🏗️ Frontend Architecture Overview

### Design Philosophy
- **Aesthetic**: Wall Street Professional Style - 정숙한 사무실 분위기
- **Layout Strategy**: Ultra-compact with maximum space utilization
- **Color Palette**: `#f7fafc` (background), `#edf2f7` (sections), `#2d3748` (text)
- **Typography**: Noto Sans KR, header size ≤1.1rem
- **User Experience**: Efficiency-focused, minimal animations

### Frontend Layer Architecture
```
┌─────────────────────────────────────────────────┐
│                Presentation Layer                │
│        HTML5 Semantic + CSS3 + JavaScript       │
├─────────────────────────────────────────────────┤
│              Component Layer                     │
│    Gallery │ Calculator │ Forms │ Navigation     │
├─────────────────────────────────────────────────┤
│               Interaction Layer                  │
│        jQuery AJAX │ Event Handlers             │
├─────────────────────────────────────────────────┤
│                API Layer                         │
│      calculate_price_ajax.php │ get_images.php   │
└─────────────────────────────────────────────────┘
```

## 📦 Component System Architecture

### Core UI Components
| Component | Purpose | Technology | Architecture Pattern |
|-----------|---------|------------|---------------------|
| **Gallery System** | Product visualization | jQuery + CSS Grid | Module-based |
| **Price Calculator** | Dynamic pricing | AJAX + JavaScript | State-driven |
| **Form Controls** | User input | HTML5 + CSS | Component-based |
| **Shopping Cart** | Order management | Session + AJAX | Service-oriented |
| **Navigation** | Site navigation | PHP includes + CSS | Template-based |

### Component File Structure
```
mlangprintauto/[product]/
├── index.php                    # Main component assembly
├── js/[product].js             # Component logic
├── css/[product]-compact.css    # Component styles
├── get_[product]_images.php    # Gallery API endpoint
└── calculate_price_ajax.php     # Pricing API endpoint

Shared Components:
css/
├── common-styles.css           # Global component styles
├── unified-calculator-layout.css  # Calculator components
└── page-title-common.css       # Header components
```

## 🎯 Product Module UI Architecture

### Standard Module Interface
Each product module follows a consistent UI pattern:

```
┌─────────────────────────────────────────────────┐
│              Product Header (1.1rem)            │
├─────────────────┬───────────────────────────────┤
│                 │                               │
│   Gallery       │    Price Calculator           │
│   Component     │    Component                  │
│   (40% width)   │    (60% width)               │
│                 │                               │
├─────────────────┴───────────────────────────────┤
│           Options & Controls                    │
│       (Horizontal form layout)                  │
├─────────────────────────────────────────────────┤
│            Action Buttons                       │
│      [장바구니] [주문하기] [파일업로드]            │
└─────────────────────────────────────────────────┘
```

### Product-Specific Implementations
| Product | Gallery Type | Calculator Features | Special Components |
|---------|-------------|--------------------|--------------------|
| **명함** | Thumbnail grid | Size/quantity based | Upload preview |
| **전단지** | Large preview | Paper/size matrix | Template selector |
| **포스터** | Zoom viewer | Material/size grid | Size calculator |
| **스티커** | Shape gallery | Cut/material options | Shape selector |
| **봉투** | Template grid | Size/envelope type | Address preview |
| **상품권** | Design gallery | Quantity discounts | Value selector |
| **NCR양식** | Multi-part view | Form/carbon options | Part configuration |

## 🎨 CSS Design System

### Layout Architecture
```css
/* Core Layout Classes */
.form-group-horizontal    /* Label-select inline layout */
.form-row                /* Two-column grid layout */
.form-compact            /* Ultra-compact spacing */
.design-inline           /* Design option inline layout */

/* Component Classes */
.option-label            /* Form labels (consistent styling) */
.option-select           /* Select dropdowns (unified appearance) */
.price-display           /* Price container */
.price-breakdown         /* Detailed price itemization */
.gallery-container       /* Image gallery wrapper */
.calculator-panel        /* Price calculator container */
```

### Responsive Design Strategy
| Breakpoint | Target | Layout Strategy | Priority |
|------------|---------|----------------|----------|
| **Desktop** (1920px+) | Primary | Full feature set | ✅ Complete |
| **Laptop** (1366-1920px) | Secondary | Adaptive scaling | ✅ Complete |
| **Tablet** (768-1365px) | Tertiary | Component stacking | ⚠️ Partial |
| **Mobile** (320-767px) | Future | Touch-optimized | ❌ Planned |

### CSS File Organization
```
css/
├── 📄 Global Styles
│   ├── common-styles.css              # Site-wide styles (!important)
│   ├── unified-calculator-layout.css  # Calculator components
│   └── page-title-common.css          # Header standardization
│
├── 📄 Product-Specific
│   ├── namecard-compact.css          # 명함 module styles
│   ├── leaflet-compact.css           # 전단지 module styles
│   ├── sticker-compact.css           # 스티커 module styles
│   └── [product]-compact.css         # Other product modules
│
└── 📄 Specialized
    ├── flyer-title-gray.css          # 전단지 title styling
    └── envelope-gallery-calculator-sync.css  # 봉투 specific
```

## ⚡ JavaScript Architecture

### Component-Based JavaScript Structure
```javascript
// Standard Module Pattern
var ProductModule = {
    // Core functions
    init: function() { /* Initialization */ },
    calculatePrice: function() { /* AJAX price calculation */ },
    updateGallery: function() { /* Gallery management */ },
    handleFormSubmit: function() { /* Form processing */ },

    // AJAX endpoints
    priceEndpoint: 'calculate_price_ajax.php',
    imageEndpoint: 'get_[product]_images.php',

    // Event handlers
    bindEvents: function() { /* Event binding */ }
};
```

### AJAX Request Architecture
```
User Interaction (JavaScript)
    ↓
Form Data Collection
    ↓
AJAX Request (jQuery)
    ↓
PHP Endpoint (calculate_price_ajax.php)
    ↓
Database Query (MySQLi)
    ↓
JSON Response
    ↓
DOM Update (JavaScript)
    ↓
User Interface Refresh
```

## 📊 Performance Architecture

### Frontend Performance Metrics
| Metric | Current | Target | Strategy |
|--------|---------|--------|----------|
| **First Contentful Paint** | 1.2s | <800ms | CSS optimization |
| **Largest Contentful Paint** | 2.5s | <1.5s | Image optimization |
| **Time to Interactive** | 3.0s | <2.0s | JavaScript bundling |
| **Cumulative Layout Shift** | 0.15 | <0.1 | Layout stabilization |

### Optimization Strategy
```
Level 1: Critical Path
├── Inline critical CSS
├── Defer non-critical JavaScript
└── Optimize font loading

Level 2: Resource Optimization
├── Image compression (WebP format)
├── JavaScript minification
└── CSS bundling

Level 3: Advanced Features
├── Service Worker implementation
├── Progressive Web App features
└── Advanced caching strategies
```

## 🔧 Component Development Guidelines

### HTML Structure Standards
```html
<!-- Standard Product Module Template -->
<div class="product-container">
    <header class="product-header">
        <h1 class="page-title">[Product Name] 온라인 견적</h1>
    </header>

    <div class="product-main">
        <div class="gallery-section">
            <!-- Gallery component -->
        </div>

        <div class="calculator-section">
            <!-- Calculator component -->
            <div class="form-group-horizontal">
                <label class="option-label">옵션:</label>
                <select class="option-select">...</select>
            </div>
        </div>
    </div>

    <div class="price-display">
        <!-- Price display component -->
    </div>
</div>
```

### Form Layout Patterns
```html
<!-- 1. Single Line Layout -->
<div class="form-group-horizontal">
    <label class="option-label">크기:</label>
    <select class="option-select">...</select>
</div>

<!-- 2. Inline Design Layout -->
<div class="design-inline">
    <label class="option-label">편집디자인:</label>
    <select class="option-select">...</select>
    <input type="text" placeholder="기타">
</div>

<!-- 3. Two-Column Grid -->
<div class="form-row">
    <div class="form-group-horizontal">
        <label class="option-label">용지:</label>
        <select class="option-select">...</select>
    </div>
    <div class="form-group-horizontal">
        <label class="option-label">수량:</label>
        <select class="option-select">...</select>
    </div>
</div>
```

## 🚀 Development Standards

### CSS Development Rules
```css
/* MANDATORY: All styles must use !important for specificity */
.page-title {
    font-size: 1.1rem !important;
    color: #2d3748 !important;
    margin: 5px 0 !important;
}

/* FORBIDDEN: No inline styles in HTML */
/* ❌ <div style="color: red;"> */
/* ✅ <div class="error-text"> */

/* REQUIRED: Consistent class naming */
.form-group-horizontal  /* layout-type-direction */
.option-label          /* component-type */
.price-display         /* component-purpose */
```

### JavaScript Development Standards
```javascript
// MANDATORY: Use jQuery for AJAX
$.ajax({
    url: 'calculate_price_ajax.php',
    method: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
        updatePriceDisplay(response);
    }
});

// REQUIRED: Error handling
function updatePriceDisplay(data) {
    if (data.error) {
        console.error('Price calculation error:', data.error);
        return;
    }
    // Update UI
}
```

## 📱 Mobile Architecture (Future)

### Mobile-First Strategy
```
Phase 1: Responsive Foundation
├── Viewport meta tags
├── Flexible grid system
└── Touch event handling

Phase 2: Mobile Components
├── Swipeable galleries
├── Collapsible calculators
└── Touch-optimized forms

Phase 3: Progressive Web App
├── Service Worker
├── Offline capability
└── App-like experience
```

### Mobile Layout Architecture (Planned)
```
Mobile Stack Layout:
┌─────────────────┐
│   Navigation    │  ← Hamburger menu
├─────────────────┤
│   Product       │  ← Swipeable gallery
│   Gallery       │
├─────────────────┤
│   Calculator    │  ← Collapsible panel
│   (Collapsed)   │
├─────────────────┤
│   Quick Actions │  ← Sticky footer
│   [Cart] [Order]│
└─────────────────┘
```

## 🔍 Quality Assurance

### Browser Support Matrix
| Browser | Desktop | Mobile | Testing Priority |
|---------|---------|--------|------------------|
| **Chrome** | ✅ Full | ⏳ Planned | Primary |
| **Firefox** | ✅ Full | ⏳ Planned | Secondary |
| **Safari** | ✅ Partial | ⏳ Planned | Secondary |
| **Edge** | ✅ Full | ⏳ Planned | Tertiary |
| **IE11** | ❌ No | ❌ No | Deprecated |

### Testing Standards
- **Desktop**: 1920x1080, 1366x768 resolutions
- **Performance**: <2s load time, <100ms interaction
- **Accessibility**: WCAG 2.1 AA compliance target
- **Cross-browser**: Chrome, Firefox, Safari testing

---
*Frontend Architecture Version: 2.1*
*Last Updated: 2025-01-19*
*Maintained by: Frontend Architecture Team*