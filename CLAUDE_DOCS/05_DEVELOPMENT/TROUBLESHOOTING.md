# 🛠 Troubleshooting & Updates

## 📅 Recent Updates Timeline

### 2025 Q1 - Current Sprint
```
┌─────────────────────────────────────────────────────────────┐
│ January 2025                                                │
├─────────────────────────────────────────────────────────────┤
│ 📌 Jan 03 - Documentation Refactoring                      │
│   └─ Restructured all CLAUDE documentation                 │
│   └─ Added mobile responsiveness focus                     │
│   └─ Created security/performance checklists               │
└─────────────────────────────────────────────────────────────┘
```

### 2024 Q4 - System Improvements
```
┌─────────────────────────────────────────────────────────────┐
│ December 2024                                               │
├─────────────────────────────────────────────────────────────┤
│ 🛒 Dec 10 - Shopping Cart Enhancement                      │
│   └─ Improved order processing logic                       │
│   └─ Fixed session handling bugs                           │
│   └─ Added cart persistence feature                        │
└─────────────────────────────────────────────────────────────┘
```

### 2024 Q3 - Module Refactoring
```
┌─────────────────────────────────────────────────────────────┐
│ September 2024                                              │
├─────────────────────────────────────────────────────────────┤
│ 📄 Sep 03 - NameCard Module Refactoring                    │
│   └─ Modularized pricing calculator                        │
│   └─ Separated config from logic                           │
│   └─ Created reusable components                           │
│                                                             │
│ 🎨 Sep 02 - Flyer System Refactoring                      │
│   └─ Restructured directory layout                         │
│   └─ Implemented new pricing engine                        │
│   └─ Added template management                             │
└─────────────────────────────────────────────────────────────┘
```

### Update Categories
| Type | Icon | Description | Frequency |
|------|------|-------------|-----------|
| **Feature** | 🚀 | New functionality | Monthly |
| **Enhancement** | 📈 | Improvements | Weekly |
| **Bug Fix** | 🐛 | Issue resolution | As needed |
| **Security** | 🔒 | Security patches | Critical |
| **Performance** | ⚡ | Speed optimization | Quarterly |
| **Documentation** | 📝 | Doc updates | Ongoing |

## 🔧 Common Issues & Solutions

### 1. Database Connection Issues

#### 🔴 Problem: "mysqli_connect_error(): Connection refused"
**📍 Symptoms:**
- Cannot access website
- White screen or error 500
- "Database connection failed" message

**🔍 Root Causes:**
1. MySQL service not running
2. Incorrect credentials in `db.php`
3. Database server overloaded
4. Firewall blocking port 3306

**✅ Solutions:**
```bash
# Step 1: Check MySQL service
sudo service mysql status
# If stopped, start it:
sudo service mysql start

# Step 2: Verify credentials
cat /path/to/db.php
# Ensure correct:
# - DB_HOST (usually 'localhost')
# - DB_USER
# - DB_PASS
# - DB_NAME

# Step 3: Test connection manually
mysql -u username -p database_name

# Step 4: Check error logs
tail -f /var/log/mysql/error.log
```

**🛡️ Prevention:**
- Implement connection pooling
- Add retry logic with exponential backoff
- Monitor database health

---

### 2. Session Management Problems

#### 🔴 Problem: "Session lost after login"
**📍 Symptoms:**
- User logged out unexpectedly
- Cart items disappearing
- "Please login again" messages

**🔍 Root Causes:**
1. `session_start()` not called
2. Session directory permissions
3. Session timeout too short
4. Cookie domain mismatch

**✅ Solutions:**
```php
// Step 1: Ensure session_start() at beginning
<?php
session_start(); // Must be first
// ... rest of code

// Step 2: Check session save path
echo ini_get('session.save_path');
// Verify permissions:
// chmod 1733 /var/lib/php/sessions

// Step 3: Increase session timeout
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

// Step 4: Debug session
var_dump($_SESSION);
var_dump(session_id());
```

**🛡️ Prevention:**
- Create session wrapper class
- Implement session regeneration
- Add session validation checks

---

### 3. File Upload Failures

#### 🔴 Problem: "File upload failed"
**📍 Symptoms:**
- "Upload failed" error message
- Files not appearing in directory
- Timeout during upload

**🔍 Root Causes:**
1. Incorrect folder permissions
2. PHP upload limits too low
3. Disk space full
4. Invalid file types

**✅ Solutions:**
```bash
# Step 1: Fix folder permissions
chmod 755 /path/to/upload/directory
chown www-data:www-data /path/to/upload/directory

# Step 2: Update PHP limits in php.ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300

# Step 3: Restart Apache
sudo service apache2 restart

# Step 4: Check disk space
df -h
```

```php
// Step 5: Debug upload
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
    ];
    echo $errors[$_FILES['file']['error']];
}
```

**🛡️ Prevention:**
- Implement chunked uploads
- Add progress indicators
- Validate before upload

---

### 4. Character Encoding Issues

#### 🔴 Problem: "Korean text shows as ???"
**📍 Symptoms:**
- Korean characters display incorrectly
- Question marks or boxes instead of text
- Data corruption in database

**🔍 Root Causes:**
1. Database charset not UTF-8
2. PHP/HTML charset mismatch
3. Missing charset headers

**✅ Solutions:**
```sql
-- Step 1: Fix database charset
ALTER DATABASE dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tablename CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```php
// Step 2: Set connection charset
mysqli_set_charset($conn, "utf8mb4");

// Step 3: Add HTML meta tag
<meta charset="UTF-8">

// Step 4: Set PHP headers
header('Content-Type: text/html; charset=UTF-8');
```

**🛡️ Prevention:**
- Always use UTF-8 everywhere
- Set charset in connection string
- Validate input encoding

---

### 5. Performance Degradation

#### 🔴 Problem: "Pages loading slowly"
**📍 Symptoms:**
- Page load time > 3 seconds
- Timeout errors
- High server CPU usage

**🔍 Root Causes:**
1. Missing database indexes
2. No caching implemented
3. Unoptimized queries
4. Large images not optimized

**✅ Solutions:**
```sql
-- Step 1: Add indexes
EXPLAIN SELECT * FROM orders WHERE user_id = 123;
CREATE INDEX idx_user_id ON orders(user_id);

-- Step 2: Find slow queries
SHOW PROCESSLIST;
SET GLOBAL slow_query_log = 'ON';
```

```php
// Step 3: Implement caching
$cache_file = 'cache/data_'.md5($query).'.cache';
if (file_exists($cache_file) && time() - filemtime($cache_file) < 3600) {
    $data = unserialize(file_get_contents($cache_file));
} else {
    $data = fetch_from_database();
    file_put_contents($cache_file, serialize($data));
}
```

**🛡️ Prevention:**
- Regular performance audits
- Implement APM monitoring
- Optimize queries proactively

## 📊 Issue Priority Matrix

| Severity | Response Time | Examples | Action |
|----------|--------------|----------|--------|
| **🔴 Critical** | < 1 hour | System down, data loss | Immediate fix, all hands |
| **🟠 High** | < 4 hours | Login broken, payment fail | Same day fix |
| **🟡 Medium** | < 24 hours | Slow performance, UI bugs | Next day fix |
| **🟢 Low** | < 1 week | Minor UI issues | Scheduled fix |

## 🚨 Emergency Procedures

### System Down Protocol
1. **Immediate Actions** (0-5 minutes)
   - [ ] Check server status
   - [ ] Verify database connection
   - [ ] Review error logs
   - [ ] Enable maintenance mode

2. **Diagnosis** (5-15 minutes)
   - [ ] Identify root cause
   - [ ] Document timeline
   - [ ] Notify stakeholders
   - [ ] Prepare fix strategy

3. **Recovery** (15+ minutes)
   - [ ] Apply fix
   - [ ] Test thoroughly
   - [ ] Monitor closely
   - [ ] Write incident report

### Data Recovery Checklist
- [ ] Stop all write operations
- [ ] Backup current state
- [ ] Identify corruption point
- [ ] Restore from last good backup
- [ ] Replay transaction logs
- [ ] Verify data integrity
- [ ] Resume operations gradually

## 🔍 Debugging Tools & Commands

### Essential Commands
```bash
# PHP Error Logs
tail -f /var/log/apache2/error.log

# MySQL Logs
tail -f /var/log/mysql/error.log

# System Resources
top
htop
free -m
df -h

# Network Issues
netstat -tuln
ping dsp1830.shop
traceroute dsp1830.shop

# Process Management
ps aux | grep apache
ps aux | grep mysql
systemctl status apache2
systemctl status mysql
```

### Useful PHP Debug Snippets
```php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memory usage
echo "Peak memory: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB\n";

// Execution time
$start = microtime(true);
// ... code ...
echo "Time: " . (microtime(true) - $start) . " seconds\n";

// Backtrace
debug_print_backtrace();

// Variable dump
echo '<pre>'; var_dump($variable); echo '</pre>';
```

## 📝 Incident Log Template

```markdown
### Incident #[NUMBER] - [DATE]
**Severity**: 🔴 Critical / 🟠 High / 🟡 Medium / 🟢 Low
**Duration**: [START TIME] - [END TIME]
**Affected Systems**: [LIST]

**Description**:
[What happened]

**Root Cause**:
[Why it happened]

**Resolution**:
[How it was fixed]

**Prevention**:
[Future prevention measures]

**Lessons Learned**:
[Key takeaways]
```

---
*Last Updated: 2025-01-03*  
*Version: 2.0*  
*Emergency Contact: dev@dsp1830.shop*