# 🚨 SOLUTION: Password Reset HTTP 500 Error

## Problem Summary
The password reset page (`/member/password_reset_request.php`) returns HTTP 500 error on production server (dsp114.co.kr).

## Root Cause Analysis

### Primary Cause: Missing Database Table
The `password_resets` table does not exist in the production database. When the form is submitted, the INSERT query fails:

```php
// Line 46 in password_reset_request.php
$insert_query = "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)";
// ❌ Table doesn't exist → mysqli_stmt_execute() fails → 500 error
```

### Secondary Cause: Missing Columns in member Table
Legacy support tries to use non-existent columns:

```php
// Line 99-101 in password_reset_request.php
$update_query = "UPDATE member SET reset_token = ?, reset_expires = ? WHERE id = ?";
// ❌ Columns don't exist → UPDATE fails → 500 error
```

## Immediate Fix Instructions

### Option 1: Run Migration via Web (Recommended)
1. Access: `http://localhost/admin/migrations/run_password_reset_fix.php`
2. Click "Run Migration" button
3. Verify all green checkmarks appear
4. Test password reset functionality

### Option 2: Run SQL Directly
Execute the migration SQL file on production database:
```bash
mysql -u dsp1830 -pds701018 dsp1830 < /var/www/html/admin/migrations/fix_password_reset_tables.sql
```

### Option 3: Manual Database Fix
Connect to production database and run:

```sql
-- Create password_resets table
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_token` (`token`),
  INDEX `idx_email` (`email`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to member table
ALTER TABLE `member` ADD COLUMN `reset_token` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `member` ADD COLUMN `reset_expires` DATETIME DEFAULT NULL;
```

## Production Deployment

### Step 1: Upload Migration Files to Production
```bash
# Upload SQL migration
curl -T /var/www/html/admin/migrations/fix_password_reset_tables.sql \
  ftp://dsp114.co.kr/httpdocs/admin/migrations/fix_password_reset_tables.sql \
  --user "dsp1830:cH*j@yzj093BeTtc"

# Upload PHP runner
curl -T /var/www/html/admin/migrations/run_password_reset_fix.php \
  ftp://dsp114.co.kr/httpdocs/admin/migrations/run_password_reset_fix.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### Step 2: Execute on Production
Via Plesk phpMyAdmin:
1. Login to Plesk: https://cmshom.co.kr:8443/login_up.php
2. Go to Databases → phpMyAdmin
3. Import: `fix_password_reset_tables.sql`

OR via web:
1. Access: https://dsp114.co.kr/admin/migrations/run_password_reset_fix.php
2. Click "Run Migration"

### Step 3: Test Password Reset
1. Go to: https://dsp114.co.kr/member/password_reset_request.php
2. Enter test username and email
3. Check email for reset link
4. Click link and set new password

## Verification Checklist

- [ ] `password_resets` table exists
- [ ] `member` table has `reset_token` and `reset_expires` columns
- [ ] Password reset request page loads without error
- [ ] Form submission sends email successfully
- [ ] Reset link works and allows password change
- [ ] User can login with new password

## Files Involved

| File | Purpose |
|------|---------|
| `/member/password_reset_request.php` | Password reset request form |
| `/member/password_reset.php` | Password reset with token |
| `/admin/migrations/fix_password_reset_tables.sql` | Database migration SQL |
| `/admin/migrations/run_password_reset_fix.php` | PHP migration runner |

## Additional PHP 8.2 Compatibility Notes

Production runs PHP 8.2 which is stricter than local PHP 7.4. Common issues:

1. **mysqli_close() ordering**: Never close DB before includes that use it
2. **Error suppression**: Production has `display_errors = Off`
3. **Undefined array keys**: Use null coalescing operator `??`

## Contact for Issues

If problems persist after migration:
1. Check Plesk error logs
2. Enable temporary debugging in password_reset files
3. Contact system administrator

---

**Created**: 2026-02-24
**Issue**: HTTP 500 on password reset
**Solution**: Create missing database tables/columns