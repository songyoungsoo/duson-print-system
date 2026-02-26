
## 2026-02-18: Mobile Nav Submenu Scroll Issue

**Problem**: Navigation dropdown (z-index: 1000) covers cart content on mobile. Submenu stays open when user scrolls down.

**Solution Applied**: Added scroll event listener in `includes/nav.php` (end of file, after line 309).
- Mobile only: `window.innerWidth <= 768` guard
- Debounced 150ms, triggers only on downward scroll
- Hides `.nav-dropdown-menu` and `.nav-mega-panel` via `style.display = 'none'`
- Resets display after 200ms so hover still works after scroll stops
- Uses `{ passive: true }` for scroll performance

**Files Modified**: `includes/nav.php` (added ~30 lines at end)

**Gotcha**: The `force-close` CSS class approach (from task context) was NOT used because it requires `!important` in CSS (forbidden by project rules). Direct `style.display` manipulation was used instead.
