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
