# Implementation Plan

- [x] 1. Backup current OnlineOrder.php file





  - Create a backup copy of OnlineOrder.php as OnlineOrder.php.backup
  - Ensure backup is in same directory for easy rollback if needed
  - _Requirements: 2.3, 2.4_


- [x] 2. Fix the INSERT query with explicit column names




  - [x] 2.1 Rewrite the INSERT statement to use explicit column names


    - Replace positional VALUES insertion with named column INSERT
    - Map all 30 columns: no, Type, ImgFolder, Type_1, money_1-5, name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, date, OrderStyle, ThingCate, pass, Gensu, Designer, logen_box_qty, logen_delivery_fee, logen_fee_type
    - Use NULL for unused fields: ThingCate, Designer, logen_box_qty, logen_delivery_fee, logen_fee_type
    - Maintain PHP 5.2 syntax and mysql_* functions
    - Preserve EUC-KR encoding for Korean text
    - _Requirements: 1.1, 1.2, 1.5, 2.2, 2.5, 2.6_
  - [x] 2.2 Add SQL debug output capability

    - Ensure the commented debug line "//echo $dbinsert; exit;" is present for troubleshooting
    - _Requirements: 3.4_

- [x] 3. Improve error handling for INSERT failures




  - [x] 3.1 Add MySQL error logging when INSERT fails



    - Capture mysql_error() when $result_insert is false
    - Add error message to redirect or display to user
    - Maintain existing error handling for connection failures
    - _Requirements: 1.4, 3.2_
  - [x] 3.2 Add basic input validation


    - Check that required fields (name, phone, email) are not empty before INSERT
    - Display user-friendly error message if validation fails
    - _Requirements: 3.3_


- [ ] 4. Test the fix with manual verification



  - [ ] 4.1 Test OrderOne submission mode

    - Submit a test order through OrderOne form
    - Verify record is inserted into MlangOrder_PrintAuto table
    - Check that all 30 fields are correctly populated
    - Verify NULL values for unused logistics fields
    - Confirm Korean text (name, address) is stored correctly in EUC-KR
    - _Requirements: 1.1, 1.5, 2.6_
  - [ ] 4.2 Test OrderTwo submission mode

    - Submit a test order through OrderTwo form
    - Verify OrderStyle field is set to '1' (not '2')
    - Confirm record is inserted successfully
    - _Requirements: 2.3_
  - [ ] 4.3 Test redirect functionality

    - Verify successful submission redirects to OrderResult.php
    - Check that all query parameters are passed correctly
    - Confirm new order number is included in redirect
    - _Requirements: 1.3_
  - [ ] 4.4 Test error scenarios
    - Simulate database connection failure (verify error alert displays)
    - Test with invalid data to trigger INSERT failure
    - Verify error messages are displayed/logged appropriately
    - _Requirements: 1.4, 3.1, 3.2_
  - [-] 4.5 Verify SQL query structure

    - Uncomment debug line to view generated SQL
    - Confirm INSERT uses explicit column names
    - Verify column count matches table schema (30 columns)
    - Check that only mysql_* functions are used (PHP 5.2 compatibility)
    - _Requirements: 1.2, 2.2, 2.5_

- [ ] 5. Final verification and cleanup
  - Ensure all tests pass and orders are successfully submitted
  - Remove or re-comment any debug output lines
  - Verify no PHP errors or warnings in error logs
  - Confirm Korean text displays correctly in admin panel
  - Ask the user if any issues arise
  - _Requirements: 1.1, 2.3, 2.4, 2.6_
