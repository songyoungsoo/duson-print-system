# Admin Dashboard Learnings

## Payment Status Module (2026-02-06)

### Database Schema Discovery
- `mlangorder_printauto` table does NOT have `payment_status` column
- Payment status derived from `bank` column:
  - Completed: `bank IS NOT NULL AND bank != '' AND bank != '미입금' AND bank != '취소'`
  - Pending: `bank IS NULL OR bank = '' OR bank = '미입금'`
  - Cancelled: `bank = '취소'`

### Amount Calculation
- Total amount = `price_supply + price_vat_amount` (not `price` column)
- Both columns are INT type, default 0

### Filter Implementation
- Period filter: Uses `date >= ?` with calculated date strings
- Status filter: Uses CASE expressions in WHERE clause (no bind params needed)
- Method filter: Direct `bank = ?` comparison
- Search: LIKE pattern on `no` and `bankname` columns

### Pagination Pattern
- 30 items per page (ITEMS_PER_PAGE constant)
- Offset calculation: `($page - 1) * $limit`
- Total pages: `ceil($total_items / $limit)`
- Separate count query with same WHERE clause

### API Response Structure
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "data": [...],
    "pagination": {...},
    "stats": {...}
  }
}
```

### Frontend Pattern
- Tailwind CSS for styling
- Fetch API for AJAX calls
- Status badges with color coding
- Responsive table with overflow-x-auto
- Mobile-friendly pagination

## Main Dashboard Implementation (2026-02-06)

### Query Patterns Used
- Today stats: `WHERE DATE(date) = CURDATE()`
- Month stats: `WHERE DATE_FORMAT(date, '%Y-%m') = current_month`
- Daily trend: `GROUP BY DATE(date)` with 30-day interval
- Pending orders: `WHERE OrderStyle IN ('1', '2', '3') OR OrderStyle IS NULL`

### Chart.js Configuration
- Type: line chart for daily trends
- Responsive: true, maintainAspectRatio: false
- Fill: true with rgba background
- Tension: 0.4 for smooth curves

### Summary Cards Layout
- 4-column grid on desktop (`grid-cols-4`)
- 2-column on tablet (`md:grid-cols-2`)
- 1-column on mobile (default)
- Hover shadow effect for interactivity

### File Structure
- `/dashboard/index.php` - Main dashboard page
- Uses existing layout: header.php, sidebar.php, footer.php
- Queries inline (no separate API for now)
- Chart rendered client-side with PHP data injection

### Database Columns Used
- `mlangorder_printauto.money_5` - Total order amount
- `mlangorder_printauto.OrderStyle` - Order status
- `mlangorder_printauto.date` - Order timestamp
- `customer_inquiries.replied_at` - Inquiry reply status

## Order Statistics Module (2026-02-06)

### Chart Types Implemented
- Line chart: Daily trends with period selector (7/30/90 days)
- Bar chart: Monthly revenue (last 12 months)
- Doughnut chart: Product distribution (top 10 categories)

### API Endpoints Created
- `/dashboard/api/stats.php?type=daily&days=30` - Daily trend data
- `/dashboard/api/stats.php?type=monthly` - Monthly revenue data
- `/dashboard/api/stats.php?type=products` - Product distribution data

### Dynamic Chart Updates
- Period selector triggers fetch() to reload daily chart data
- Chart.update() method refreshes chart without page reload
- Async/await pattern for API calls

### Product Normalization
- Used CASE statement to group similar product types
- Handles variations: 스티커/스티카, 명함/NameCard, etc.
- Returns top 10 categories by order count

### Layout Pattern
- 3-column grid: 2-col daily chart + 1-col doughnut
- Full-width monthly bar chart below
- Responsive: stacks to 1-column on mobile

## Order Management Module (2026-02-06)

### Files Created
- `/dashboard/orders/index.php` - Order list with filters and pagination
- `/dashboard/orders/view.php` - Order detail view with status management
- `/dashboard/api/orders.php` - CRUD API (list/view/update/delete)

### Filter Implementation
- Period: today, 7days, 30days, 3months
- Status: 1 (접수), 2 (진행중), 3 (완료), deleted
- Product type: LIKE search on Type column
- Search: order no, name, email

### Soft Delete Pattern
- Uses OrderStyle = 'deleted' instead of actual DELETE
- Deleted orders excluded from default list view
- Can be filtered explicitly with status=deleted

### Status Management
- Status change via POST to /dashboard/api/orders.php
- Form submission with AJAX (no page reload)
- Alert confirmation before delete

### Pagination
- 30 items per page (ITEMS_PER_PAGE constant)
- Dynamic button rendering (current ±2 pages)
- Previous/Next buttons when applicable

## Member Management Module (2026-02-06)

### Files Created
- `/dashboard/members/index.php` - Member list with search
- `/dashboard/members/view.php` - Member detail view with edit form
- `/dashboard/api/members.php` - CRUD API (list/view/update)

### Search Implementation
- Single search field for username, name, email, phone
- LIKE search across multiple columns
- Real-time search with Enter key support

### Update Pattern
- Only allows updating: name, email, phone
- Username is read-only (primary identifier)
- Password changes not allowed (security)
- AJAX form submission with reload on success

### Data Display
- Basic info: username, name, email, phone
- Address info: postcode, address, detail_address
- Business info: conditional display if business_number exists
- Join info: created_at timestamp

## Product Management Module (2026-02-06)

### Files Created
- `/dashboard/products/index.php` - Product type selector (9 types)
- `/dashboard/products/list.php` - Product option list with inline editing
- `/dashboard/api/products.php` - CRUD API (types/list/view/update)
- Updated `/dashboard/includes/config.php` - Added $PRODUCT_TYPES array

### Product Type Configuration
- Centralized in $PRODUCT_TYPES array (config.php)
- Maps type key → name, table, unit
- 9 product types: namecard, sticker, inserted, envelope, littleprint, merchandisebond, cadarok, ncrflambeau, msticker

### Table Abstraction
- Dynamic table selection based on type parameter
- All product tables follow similar schema (no, style, Section, quantity, money)
- API validates type against $PRODUCT_TYPES before query

### Inline Editing Pattern
- JavaScript prompt() for quick price updates
- No separate edit page (simpler UX)
- AJAX POST with immediate table reload

### Security
- Type validation against whitelist ($PRODUCT_TYPES keys)
- Prepared statements for all queries
- Table name sanitization via config array (not user input)

## Customer Inquiry Module (2026-02-06)

### Files Created
- `/dashboard/inquiries/index.php` - Inquiry list with status filter
- `/dashboard/inquiries/view.php` - Inquiry detail with reply form
- `/dashboard/api/inquiries.php` - CRUD API (list/view/reply)

### Status Filter
- pending: 미답변 (yellow badge)
- answered: 답변완료 (green badge)
- Filter applied via dropdown + button

### Reply Pattern
- Conditional form display (only if no reply exists)
- Reply updates: admin_reply, admin_reply_at, status
- Status auto-changes to 'answered' on reply
- Blue background for answered section (visual distinction)

### Data Display
- Inquiry info: subject, message, category, status, created_at
- Author info: name, email, phone (conditional)
- Reply info: admin_reply, admin_reply_at (conditional)

### Email Sending
- NOT implemented (as per requirements)
- Reply stored in DB only
- Customer must check via website
