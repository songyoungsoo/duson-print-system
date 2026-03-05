# Graff KR Homepage Test Plan

> **URL**: https://www.graff.com/kr-ko/home/
> **Created**: 2026-03-04
> **Agent**: playwright-test-planner (Claude Loop)

---

## 1. Global Navigation & Discovery
**Seed**: `tests/seed.spec.ts`

### 1.1 Navigation Menu Hover and Interaction
**Steps**:
1. Navigate to https://www.graff.com/kr-ko/home/
2. Hover over "주얼리" (Jewelry) menu.
3. Verify that the mega-menu dropdown appears.
4. Click on "반지" (Rings) under the sub-category.
5. **Expected Outcome**: The page should redirect to the Rings collection, and the breadcrumb should reflect the path.

### 1.2 Search Functionality
**Steps**:
1. Click the search icon in the header.
2. Type "Butterfly" into the search input.
3. Press Enter.
4. **Expected Outcome**: Search results page should display products from the Butterfly collection.

---

## 2. Product Experience & High-Touch Services

### 2.1 Product Detail Page (PDP) Integrity
**Steps**:
1. From any collection page, click on a specific product image.
2. Verify the product name, price (if applicable), and description are loaded.
3. Interact with the image gallery (next/previous buttons).
4. **Expected Outcome**: High-quality images should switch smoothly, and product specifications should be accurate.

### 2.2 Store Appointment Booking
**Steps**:
1. Navigate to a Product Detail Page.
2. Click the "매장 방문 예약" (Book an Appointment) button.
3. Fill in the required fields: Store, Date, Time, and Personal Info.
4. Click the submit/next button without filling mandatory fields.
5. **Expected Outcome**: Validation errors should highlight missing fields (First Name, Phone Number, etc.).

---

## 3. Localization & Footer Services

### 3.1 Language & Region Validation
**Steps**:
1. Scroll to the footer of the page.
2. Check if the current region is set to "South Korea (한국어)".
3. Click on the region selector and change to "United Kingdom (English)".
4. **Expected Outcome**: The URL should change to `/en-gb/` and the text content should translate to English.

### 3.2 Newsletter Subscription (Edge Case)
**Steps**:
1. Locate the newsletter signup in the footer.
2. Enter an invalid email address (e.g., "invalid-email").
3. Click "Subscribe".
4. **Expected Outcome**: An "Invalid email" error message should be displayed.
