# Design Document

## Overview

This design addresses the field mismatch issue in OnlineOrder.php where the INSERT statement does not align with the MlangOrder_PrintAuto table schema. The current code uses positional value insertion with 29 values, but the table has 30 columns. Additionally, the code must maintain PHP 5.2 compatibility and EUC-KR encoding for Korean text.

The solution involves:
1. Analyzing the exact field mapping between POST data and database columns
2. Rewriting the INSERT query to use explicit column names
3. Properly handling NULL values for optional fields
4. Maintaining backward compatibility with existing order processing

## Architecture

The fix will be implemented within the existing OnlineOrder.php file structure:

```
OnlineOrder.php
├── Database Connection (db.php)
├── Mode Check (if $mode == "SubmitOk")
│   ├── Get Next Order Number
│   ├── Create Upload Directory
│   ├── Map POST Data to Variables
│   ├── Construct INSERT Query (FIX HERE)
│   ├── Execute Query
│   └── Redirect to OrderResult.php
└── Display Order Form
```

## Components and Interfaces

### Current Field Mapping Issue

**Current INSERT (29 values):**
```
1. $new_no
2. $Type
3. $ImgFolder
4. $Type_1\n$Type_2\n$Type_3\n$Type_4\n$Type_5\n$Type_6 (concatenated)
5. $money_1
6. $money_2
7. $money_3
8. $money_4
9. $money_5
10. $name
11. $email
12. $zip
13. $zip1
14. $zip2
15. $phone
16. $Hendphone
17. $delivery
18. $bizname
19. $bank
20. $bankname
21. $cont
22. $date
23. $PageSSOk
24. '' (empty)
25. $pass
26. $Gensu
27. '' (empty)
```

**Actual Table Schema (30 columns):**
```
1. no (auto_increment)
2. Type
3. ImgFolder
4. Type_1
5. money_1
6. money_2
7. money_3
8. money_4
9. money_5
10. name
11. email
12. zip
13. zip1
14. zip2
15. phone
16. Hendphone
17. delivery
18. bizname
19. bank
20. bankname
21. cont
22. date
23. OrderStyle
24. ThingCate
25. pass
26. Gensu
27. Designer
28. logen_box_qty
29. logen_delivery_fee
30. logen_fee_type
```

### Corrected Field Mapping

The INSERT query will be rewritten to explicitly name columns and correctly map values:

```php
INSERT INTO MlangOrder_PrintAuto (
    no, Type, ImgFolder, Type_1, 
    money_1, money_2, money_3, money_4, money_5,
    name, email, zip, zip1, zip2,
    phone, Hendphone, delivery, bizname,
    bank, bankname, cont, date,
    OrderStyle, ThingCate, pass, Gensu,
    Designer, logen_box_qty, logen_delivery_fee, logen_fee_type
) VALUES (
    '$new_no', '$Type', '$ImgFolder', '$Type_1\n$Type_2\n$Type_3\n$Type_4\n$Type_5\n$Type_6',
    '$money_1', '$money_2', '$money_3', '$money_4', '$money_5',
    '$name', '$email', '$zip', '$zip1', '$zip2',
    '$phone', '$Hendphone', '$delivery', '$bizname',
    '$bank', '$bankname', '$cont', '$date',
    '$PageSSOk', NULL, '$pass', '$Gensu',
    NULL, NULL, NULL, NULL
)
```

## Data Models

### Input Data Sources

**From POST/GET variables:**
- `$Type` - Order type
- `$ImgFolder` - Image folder path
- `$Type_1` through `$Type_6` - Type details (concatenated with newlines)
- `$money_1` through `$money_5` - Price fields
- `$_POST[username]` → `$name` - Customer name
- `$email` - Customer email
- `$sample6_postcode` → `$zip` - Postal code
- `$sample6_address` → `$zip1` - Address line 1
- `$sample6_detailAddress` → `$zip2` - Address line 2
- `$phone` - Phone number
- `$Hendphone` - Mobile phone number
- `$delivery` - Delivery method
- `$bizname` - Business name
- `$bank` - Bank name
- `$bankname` - Account holder name
- `$cont` - Order contents/notes
- `$pass` - Order password
- `$Gensu` - Quantity
- `$PageSS` → `$PageSSOk` - Order style (OrderOne=2, OrderTwo=1)

**Generated values:**
- `$new_no` - Next order number from MAX(no) + 1
- `$date` - Current timestamp (Y-m-d H:i:s)

**NULL values for unused fields:**
- `ThingCate` - Not currently used
- `Designer` - Not currently used
- `logen_box_qty` - Logistics field (not used)
- `logen_delivery_fee` - Logistics field (not used)
- `logen_fee_type` - Logistics field (not used)

## Data Flow

```
1. User submits order form → OnlineOrder.php?SubmitMode=OrderOne
2. Form includes OrderForm${SubmitMode}.php
3. Form submits with mode=SubmitOk
4. OnlineOrder.php processes:
   a. Get next order number
   b. Create upload directory
   c. Map POST variables
   d. Execute INSERT with explicit columns
   e. Redirect to OrderResult.php
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Since this is a bug fix for a specific legacy system rather than new feature development, the correctness properties are primarily example-based tests that verify the fix works correctly in the production environment.

### Example 1: Successful order insertion with all fields mapped
*For a* valid order submission through OrderOne mode, the system should successfully insert a record into MlangOrder_PrintAuto with all 30 columns populated correctly, including NULL values for unused logistics fields.
**Validates: Requirements 1.1**

### Example 2: INSERT query uses explicit column names
*For the* constructed INSERT query, the SQL string should contain explicit column names in the format "INSERT INTO MlangOrder_PrintAuto (column1, column2, ...) VALUES (...)" rather than "INSERT INTO MlangOrder_PrintAuto VALUES (...)".
**Validates: Requirements 1.2, 2.2**

### Example 3: Successful redirect after order submission
*For a* successful order insertion, the system should redirect to OrderResult.php with the new order number and all relevant order parameters in the query string.
**Validates: Requirements 1.3**

### Example 4: Error handling for failed INSERT
*For a* simulated database error during INSERT, the system should capture the MySQL error message using mysql_error() and either log it or display it for debugging purposes.
**Validates: Requirements 1.4, 3.2**

### Example 5: NULL handling for optional fields
*For an* order submission, the fields Designer, logen_box_qty, logen_delivery_fee, and logen_fee_type should be stored as NULL in the database when not provided.
**Validates: Requirements 1.5**

### Example 6: Both OrderOne and OrderTwo modes work
*For* order submissions through both OrderOne (PageSSOk=2) and OrderTwo (PageSSOk=1) modes, both should successfully insert records with the correct OrderStyle value.
**Validates: Requirements 2.3**

### Example 7: PHP 5.2 compatibility maintained
*For the* modified code, only mysql_* functions (mysql_query, mysql_fetch_row, mysql_error) should be used, not mysqli_* or PDO functions.
**Validates: Requirements 2.5**

### Example 8: EUC-KR encoding preserved for Korean text
*For an* order submission containing Korean characters in fields like name, address, and cont, the Korean text should be correctly stored in the database and retrievable without corruption.
**Validates: Requirements 2.6**

### Example 9: Database connection error handling
*For a* simulated database connection failure, the system should display the error alert "DB 접속 에러입니다!" and prevent further execution.
**Validates: Requirements 3.1**

### Example 10: SQL debug output capability
*For* debugging purposes, uncommenting the line "echo $dbinsert; exit;" should display the complete constructed SQL query before execution.
**Validates: Requirements 3.4**

## Error Handling

### Database Connection Errors
- Check `$Table_result` after `mysql_query()`
- Display JavaScript alert with Korean message
- Use `history.go(-1)` to return user to form
- Exit script execution

### INSERT Query Errors
- Check `$result_insert` after `mysql_query()`
- Currently: redirects to OrderResult.php regardless of success/failure
- **Improvement needed**: Add `mysql_error()` logging when INSERT fails
- Consider adding error parameter to OrderResult.php redirect

### File System Errors
- Directory creation uses `mkdir()` with 0755 permissions
- Follows up with `exec("chmod 777 $dir")` for write access
- No explicit error checking (assumes success)

### Data Validation
- Minimal validation in current code
- Relies on database constraints (NOT NULL, data types)
- **Note**: No explicit validation for required fields before INSERT

## Testing Strategy

### Manual Testing Approach

Given the legacy PHP 5.2 environment and production database, testing will be primarily manual:

1. **Pre-deployment Testing**
   - Test on development/staging database with same schema
   - Submit test orders through OrderOne mode
   - Submit test orders through OrderTwo mode
   - Verify records appear correctly in database
   - Test with Korean characters in various fields
   - Test with empty optional fields

2. **SQL Query Verification**
   - Uncomment debug line to view generated SQL
   - Verify column names are explicitly listed
   - Verify value count matches column count (30)
   - Verify NULL values for unused fields

3. **Error Scenario Testing**
   - Test with invalid database credentials (connection error)
   - Test with duplicate order number (if unique constraint exists)
   - Verify error messages display correctly

4. **Backward Compatibility Testing**
   - Verify existing OrderResult.php still receives correct parameters
   - Verify upload directory creation still works
   - Verify redirect URLs are properly formatted

### Unit Testing Limitations

Traditional unit testing frameworks are not practical for this scenario because:
- PHP 5.2 has limited testing framework support
- Code uses global variables and direct database access
- Production environment constraints
- Legacy codebase structure

### Deployment Verification

After deployment:
1. Monitor error logs for MySQL errors
2. Verify orders are successfully created
3. Check database records for correct field mapping
4. Verify Korean text displays correctly in admin panel
5. Test both OrderOne and OrderTwo submission paths

## Implementation Notes

### PHP 5.2 Considerations
- Use `mysql_*` functions (deprecated in PHP 5.5+, removed in PHP 7.0+)
- No namespace support
- No anonymous functions
- Limited error handling capabilities
- Short open tags `<?` are acceptable in this environment

### EUC-KR Encoding
- Ensure file is saved as EUC-KR
- Database connection should use EUC-KR charset
- No charset conversion needed if consistent throughout
- Korean characters in SQL strings will be preserved

### Security Considerations
- **SQL Injection Risk**: Current code is vulnerable (no escaping)
- **Recommendation**: Add `mysql_real_escape_string()` for all user inputs
- **Note**: This fix focuses on field mapping; security improvements are separate concern

### Backward Compatibility
- Maintain exact same redirect URL format
- Keep same variable names for OrderResult.php
- Preserve upload directory structure
- Keep same error message format
