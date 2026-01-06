# Duson Print System (dsp1830.shop) - Comprehensive Test Plan

## Application Overview

This test plan covers the Duson Print System (두손기획인쇄), a PHP 7.4-based print order management system. The system handles 11 different product types including flyers, business cards, envelopes, stickers, and more. Testing strategy follows a parallelization approach with tests grouped by independence level: Group A (read-only, maximum parallelization), Group B (calculation tests), Group C (single feature tests with limited parallelization), Group D (E2E flows with sequential dependencies), and Group E (admin functions requiring authentication).

## Test Scenarios

### 1. Group A-1: Product Page Loading (Read-Only)

**Seed:** `tests/seed.spec.ts`

#### 1.1. 전단지 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/inserted/
  2. Verify page title contains '전단지'
  3. Check that price calculator form exists (form#orderForm or form[name='choiceForm'])
  4. Verify option selection dropdowns are visible (용지, 규격, 수량, 인쇄색상)
  5. Confirm '장바구니 담기' button is visible
  6. Check price display area exists (.price-display, #total-price, .total-amount)

**Expected Results:**
  - Page loads successfully with correct title
  - All form elements are visible and interactive
  - Price calculator interface is complete
  - Navigation and common elements (logo, menu) are present

#### 1.2. 명함 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/namecard/
  2. Verify page title contains '명함'
  3. Check that price calculator form exists
  4. Verify option dropdowns are visible (재질, 수량, 규격)
  5. Confirm '장바구니 담기' button is visible
  6. Check price display area exists

**Expected Results:**
  - Page loads successfully with correct title
  - All namecard-specific options are available
  - Form interface is complete and functional

#### 1.3. 봉투 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/envelope/
  2. Verify page title contains '봉투'
  3. Check that price calculator form exists
  4. Verify envelope-specific options are visible (규격, 용지, 인쇄, 수량)
  5. Confirm '장바구니 담기' button is visible
  6. Check price display area exists

**Expected Results:**
  - Page loads successfully with envelope product options
  - All form elements specific to envelopes are available
  - Interface matches expected envelope ordering workflow

#### 1.4. 스티커 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/sticker_new/
  2. Verify page title contains '스티커'
  3. Check form elements exist
  4. Verify sticker-specific options are available

**Expected Results:**
  - Sticker product page loads correctly
  - All sticker options and calculator are functional

#### 1.5. 자석스티커 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/msticker/
  2. Verify page title contains '자석' or '스티커'
  3. Check form elements and options exist

**Expected Results:**
  - Magnetic sticker page loads with appropriate options

#### 1.6. 카다록 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/cadarok/
  2. Verify page title contains '카다록'
  3. Check form and options exist

**Expected Results:**
  - Catalog product page loads correctly

#### 1.7. 포스터 페이지 로딩 테스트 (littleprint)

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/littleprint/
  2. Verify page title contains '포스터'
  3. Check form elements exist
  4. Note: Directory is 'littleprint' but product name is '포스터' (legacy code)

**Expected Results:**
  - Poster page loads correctly despite legacy directory name

#### 1.8. 상품권 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/merchandisebond/
  2. Verify page title contains '상품권'
  3. Check form and options exist

**Expected Results:**
  - Gift certificate page loads with appropriate options

#### 1.9. NCR양식 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/ncrflambeau/
  2. Verify page title contains 'NCR' or '양식'
  3. Check form elements exist

**Expected Results:**
  - NCR form product page loads correctly

#### 1.10. 리플렛 페이지 로딩 테스트

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. Navigate to http://dsp1830.shop/mlangprintauto/leaflet/
  2. Verify page title contains '리플렛'
  3. Check form and folding options exist

**Expected Results:**
  - Leaflet page loads with folding options visible

#### 1.11. 모든 제품 공통 요소 확인

**File:** `tests/group-a-readonly/page-loading.group-a.spec.ts`

**Steps:**
  1. For each product page (sample 3 products)
  2. Navigate to product URL
  3. Verify logo/home link is visible (a[href*='index'], img[alt*='로고'])
  4. Check navigation menu exists (nav, .navigation, .menu)
  5. Verify footer elements are present

**Expected Results:**
  - All pages share consistent header/footer structure
  - Logo and navigation are accessible on all pages
  - Branding is consistent across product pages

### 2. Group B-1: Basic Price Calculation (Calculation Tests)

**Seed:** `tests/seed.spec.ts`

#### 2.1. 전단지 A4 0.5연 컬러인쇄 기본 가격

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/inserted/
  2. Select option: 용지 (PN_type) = '90g 아트지'
  3. Select option: 규격 (MY_type) = 'A4'
  4. Select option: 수량 (MY_amount) = '0.5연'
  5. Select option: 인쇄색상 (POtype) = '컬러'
  6. Wait 1000ms for AJAX price calculation
  7. Locate price display element (.total-price, #total_price, .price-display)
  8. Extract price value and verify > 0
  9. Verify quantity display shows '2,000매' (0.5연 = 2,000 sheets)

**Expected Results:**
  - Price is calculated and displayed correctly
  - Total price is greater than 0
  - Quantity conversion shows '2,000매' for 0.5연
  - VAT calculation is included in display
  - Price updates automatically via AJAX

#### 2.2. 전단지 A4 1.0연 컬러인쇄 기본 가격

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/inserted/
  2. Select 용지 = '90g 아트지', 규격 = 'A4', 수량 = '1.0연', 색상 = '컬러'
  3. Wait for AJAX calculation
  4. Verify quantity display shows '4,000매' (1.0연 = 4,000 sheets)
  5. Verify price is higher than 0.5연 price

**Expected Results:**
  - Quantity correctly shows '4,000매' for 1.0연
  - Price scales appropriately with quantity increase
  - All calculations complete without errors

#### 2.3. 명함 일반명함 500매 아트지 기본 가격

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/namecard/
  2. Select option: 규격 (MY_type) = '일반 명함'
  3. Select option: 재질 (Section) = '아트지'
  4. Select option: 인쇄 (POtype) = '단면 4도'
  5. Select option: 수량 (MY_amount) = '500'
  6. Wait for AJAX calculation
  7. Verify total price is displayed and > 10,000원

**Expected Results:**
  - Price calculation works for business cards
  - Minimum price threshold (10,000원) is met
  - Different material/printing options affect price correctly

#### 2.4. 봉투 소봉투 1000매 기본 가격

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/envelope/
  2. Select 규격 = '소봉투', 용지 = '모조지', 인쇄 = '단면', 수량 = '1000'
  3. Wait for AJAX calculation
  4. Verify price display is visible and contains valid amount

**Expected Results:**
  - Envelope pricing calculates correctly
  - Price is displayed for 1000 quantity

#### 2.5. 리플렛 A4 0.5연 + 2단접지 가격

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/leaflet/
  2. Select 용지 = '90g 아트지', 규격 = 'A4', 수량 = '0.5연', 색상 = '컬러'
  3. If folding_type select exists, choose '2단접지'
  4. Wait for calculation
  5. Verify folding surcharge is displayed (접지 추가금 40,000원)
  6. Check total price includes base + folding fee

**Expected Results:**
  - Folding option adds correct surcharge (40,000원)
  - Total price = base price + folding fee
  - Folding fee is clearly displayed separately

#### 2.6. 가격 계산 병렬 실행 테스트

**File:** `tests/group-b-calculation/basic-price.group-b.spec.ts`

**Steps:**
  1. Create 3 independent browser contexts
  2. Parallel execution:
  3.   - Context 1: Calculate 전단지 price with 0.5연
  4.   - Context 2: Calculate 명함 price with 500매
  5.   - Context 3: Calculate 봉투 price with 1000매
  6. Wait for all calculations to complete
  7. Verify all 3 prices are displayed correctly
  8. Close all contexts

**Expected Results:**
  - All 3 price calculations complete successfully in parallel
  - No interference between simultaneous calculations
  - Each context maintains independent state

### 3. Group C-1: File Upload Tests (Limited Parallelization)

**Seed:** `tests/seed.spec.ts`

#### 3.1. 전단지 단일 파일 업로드

**File:** `tests/group-c-features/file-upload.group-c.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/inserted/
  2. Locate file upload input element
  3. Upload test file: test_flyer.pdf (1MB)
  4. Wait for upload completion
  5. Verify upload success message appears
  6. Check filename is displayed in upload list
  7. Verify file preview/thumbnail is shown (if applicable)
  8. Confirm file size is validated (< 15MB limit)

**Expected Results:**
  - File uploads successfully without errors
  - Filename is displayed after upload
  - Upload progress indicator works correctly
  - File size validation enforces 15MB limit
  - Session storage contains uploaded file reference

#### 3.2. 명함 다중 파일 업로드

**File:** `tests/group-c-features/file-upload.group-c.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/namecard/
  2. Locate file upload input
  3. Upload multiple files: [test_namecard1.jpg, test_namecard2.jpg]
  4. Wait for all uploads to complete
  5. Verify both filenames appear in upload list
  6. Check that file count is displayed (2 files)
  7. Verify each file has individual preview/thumbnail

**Expected Results:**
  - Multiple files upload successfully
  - All uploaded files are listed individually
  - File count matches number of uploads
  - Each file can be removed independently

#### 3.3. 봉투 대용량 파일 업로드 (15MB 제한 테스트)

**File:** `tests/group-c-features/file-upload.group-c.spec.ts`

**Steps:**
  1. Navigate to /mlangprintauto/envelope/
  2. Attempt to upload test_envelope.ai (10MB, within limit)
  3. Verify upload succeeds
  4. Attempt to upload oversized_file.ai (20MB, exceeds limit)
  5. Verify error message for file size limit
  6. Confirm 15MB limit is enforced

**Expected Results:**
  - 10MB file uploads successfully (within 15MB limit)
  - 20MB file is rejected with clear error message
  - File size validation works correctly
  - Error message indicates size limit (15MB)

#### 3.4. 파일 형식 검증 테스트

**File:** `tests/group-c-features/file-upload.group-c.spec.ts`

**Steps:**
  1. Navigate to any product page with file upload
  2. Test valid formats: .pdf, .jpg, .png, .ai, .psd
  3. Verify each format uploads successfully
  4. Test invalid format: .exe or .txt
  5. Verify rejection with appropriate error message

**Expected Results:**
  - Allowed file types (.pdf, .jpg, .png, .ai, .psd) upload successfully
  - Disallowed file types are rejected
  - Clear error message indicates allowed file types

### 4. Group D-1: E2E Order Flow - 전단지 (Flyer Complete Order)

**Seed:** `tests/seed.spec.ts`

#### 4.1. 전단지 전체 주문 플로우 E2E

**File:** `tests/group-d-e2e/flyer-e2e.group-d.spec.ts`

**Steps:**
  1. Step 1: Navigate to homepage http://dsp1830.shop
  2. Click on '전단지' product link or navigate to /mlangprintauto/inserted/
  3. Step 2: Select product options
  4.   - 용지: '90g 아트지'
  5.   - 규격: 'A4'
  6.   - 수량: '0.5연'
  7.   - 인쇄색상: '컬러'
  8. Step 3: Add coating option
  9.   - Select '양면유광코팅' (double-sided glossy coating)
  10.   - Verify coating fee (+160,000원) is added to total
  11. Step 4: Upload design file
  12.   - Upload test_flyer_e2e.pdf
  13.   - Verify upload completion message
  14. Step 5: Add to cart
  15.   - Click '장바구니 담기' button
  16.   - Wait for success notification
  17.   - Verify AJAX response indicates success
  18. Step 6: Verify database entry (shop_temp table)
  19.   - Confirm INSERT into shop_temp table occurred
  20.   - Verify product code, options, and price are saved
  21. Step 7: Navigate to cart
  22.   - Go to /mlangprintauto/shop/cart.php
  23.   - Verify product appears with:
  24.     * Product name: '전단지'
  25.     * Options: 'A4, 0.5연 (2,000매), 컬러, 양면유광코팅'
  26.     * Uploaded filename: 'test_flyer_e2e.pdf'
  27.     * Correct total price
  28. Step 8: Proceed to order form
  29.   - Click '주문하기' or navigate to /mlangorder_printauto/OnlineOrder_unified.php
  30.   - Verify order summary displays cart items
  31. Step 9: Fill out order form
  32.   - Enter customer information:
  33.     * Name: '테스트주문자'
  34.     * Email: 'test@example.com'
  35.     * Phone: '010-1234-5678'
  36.     * Address: '서울시 테스트구 테스트동'
  37.   - Verify all required fields are filled
  38. Step 10: Submit order
  39.   - Click '주문 완료하기' button
  40.   - Wait for order processing
  41. Step 11: Verify database entry (mlangorder_printauto table)
  42.   - Confirm INSERT into mlangorder_printauto table
  43.   - Verify customer name is NOT '0' (bind_param bug check)
  44.   - Verify all order details are saved correctly
  45. Step 12: Order confirmation page
  46.   - Navigate to /mlangorder_printauto/OrderComplete_universal.php
  47.   - Verify order number is displayed
  48.   - Check order details:
  49.     * Product: '전단지'
  50.     * Quantity display: '0.5연 (2,000매)'
  51.     * Customer name: '테스트주문자'
  52.     * Total amount matches cart
  53.   - Verify confirmation message appears

**Expected Results:**
  - Complete order flow from product selection to confirmation succeeds
  - All price calculations are correct throughout the flow
  - Coating option adds exactly 160,000원 to base price
  - File upload completes and filename is preserved in cart
  - Cart displays all selected options correctly
  - Database entries in shop_temp table are created correctly
  - Database entries in mlangorder_printauto table are created correctly
  - Customer name is saved as '테스트주문자' (NOT '0')
  - Quantity display shows '0.5연 (2,000매)' in order confirmation
  - Order confirmation page displays complete order details
  - No errors occur at any step of the process
  - Session state is maintained throughout the multi-page flow

### 5. Group D-2: E2E Order Flow - 명함 (Business Card Complete Order)

**Seed:** `tests/seed.spec.ts`

#### 5.1. 명함 전체 주문 플로우 E2E

**File:** `tests/group-d-e2e/namecard-e2e.group-d.spec.ts`

**Steps:**
  1. Step 1: Navigate to /mlangprintauto/namecard/
  2. Step 2: Select product options
  3.   - 규격: '일반 명함'
  4.   - 재질: '아트지'
  5.   - 인쇄: '단면 4도'
  6.   - 수량: '500매'
  7. Step 3: Upload design files (multiple files)
  8.   - Upload [test_namecard_front.jpg, test_namecard_back.jpg]
  9.   - Verify both files upload successfully
  10. Step 4: Add to cart and verify cart contents
  11. Step 5: Fill order form with customer details
  12. Step 6: Submit order and verify database entries
  13. Step 7: Verify order confirmation shows:
  14.   - Product: '명함'
  15.   - Quantity: '500매'
  16.   - Both uploaded filenames
  17.   - Correct pricing

**Expected Results:**
  - Business card order completes successfully
  - Multiple file uploads are handled correctly
  - All product-specific options are saved
  - Order confirmation displays all details accurately

### 6. Group D-3: E2E Order Flow - 봉투 (Envelope Complete Order)

**Seed:** `tests/seed.spec.ts`

#### 6.1. 봉투 전체 주문 플로우 E2E

**File:** `tests/group-d-e2e/envelope-e2e.group-d.spec.ts`

**Steps:**
  1. Step 1: Navigate to /mlangprintauto/envelope/
  2. Step 2: Select options: 소봉투, 모조지, 단면, 1000매
  3. Step 3: Add optional tape: 테이프 200개
  4.   - Verify tape cost is calculated and added
  5. Step 4: Upload design file
  6. Step 5: Complete order flow through cart → order form → confirmation
  7. Step 6: Verify tape option and quantity appear in order details

**Expected Results:**
  - Envelope order with optional tape completes successfully
  - Tape quantity and cost are calculated correctly
  - Optional accessories are properly saved in database
  - Order confirmation shows all options including tape
