# Requirements Document

## Introduction

This specification addresses a critical bug in the online order submission system at dsp114.com where orders submitted through the MlangOrder_PrintAuto module fail due to field mismatches between the INSERT query in OnlineOrder.php and the actual MlangOrder_PrintAuto database table schema. The system currently attempts to insert data but the number or order of fields does not align with the table structure, causing order submission failures.

## Glossary

- **OnlineOrder.php**: The PHP script that processes online order submissions for the print automation system
- **MlangOrder_PrintAuto Table**: The MySQL database table that stores order information for the print automation module (contains 30 columns including auto-increment primary key)
- **INSERT Query**: The SQL statement that attempts to add new order records to the database
- **Field Mismatch**: A condition where the number, order, or names of fields in an INSERT statement do not correspond to the actual table schema
- **Positional INSERT**: An INSERT statement that relies on value order without explicitly naming columns (current problematic approach)
- **Named Column INSERT**: An INSERT statement that explicitly specifies column names, making it more maintainable and less error-prone
- **PHP 5.2 Environment**: Legacy PHP version requiring mysql_* functions (not mysqli or PDO)
- **EUC-KR Encoding**: Korean character encoding used throughout the system for proper Korean text handling

## Requirements

### Requirement 1

**User Story:** As a customer, I want to successfully submit orders through the online order form, so that my print orders are processed and recorded in the system.

#### Acceptance Criteria

1. WHEN a customer submits an order via the OrderOne form THEN the System SHALL insert the order data into the MlangOrder_PrintAuto table with all 30 fields correctly mapped to their corresponding columns
2. WHEN the INSERT operation executes THEN the System SHALL use explicit column names rather than positional values to ensure correct field mapping
3. WHEN the order submission completes successfully THEN the System SHALL redirect the customer to the OrderResult page with the new order number
4. WHEN an INSERT operation fails THEN the System SHALL log the MySQL error message and display a user-friendly error message
5. WHEN order data is prepared for insertion THEN the System SHALL handle NULL values appropriately for optional fields (Designer, logen_box_qty, logen_delivery_fee, logen_fee_type)

### Requirement 2

**User Story:** As a system administrator, I want the order submission code to match the current database schema, so that the system remains maintainable and reliable.

#### Acceptance Criteria

1. WHEN the database schema is examined THEN the System SHALL document all column names, data types, and constraints for the MlangOrder_PrintAuto table
2. WHEN the INSERT query is constructed THEN the System SHALL explicitly specify column names rather than relying on positional insertion
3. WHEN code modifications are made THEN the System SHALL preserve all existing functionality for both OrderOne and OrderTwo submission modes
4. WHEN field mappings are updated THEN the System SHALL ensure backward compatibility with existing order processing workflows
5. WHEN the code is modified THEN the System SHALL maintain PHP 5.2 compatibility using mysql_* functions
6. WHEN Korean text is processed THEN the System SHALL preserve EUC-KR encoding throughout the data flow

### Requirement 3

**User Story:** As a developer, I want clear error handling and logging for database operations, so that I can quickly diagnose and fix issues when they occur.

#### Acceptance Criteria

1. WHEN a database connection fails THEN the System SHALL display an appropriate error message and prevent further execution
2. WHEN an INSERT query fails THEN the System SHALL capture the MySQL error message and log it for debugging
3. WHEN data validation fails THEN the System SHALL identify which specific fields are invalid and inform the user
4. WHEN debugging is required THEN the System SHALL provide the ability to output the constructed SQL query for inspection
