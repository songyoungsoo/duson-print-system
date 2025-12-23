# GEMINI.md - Project Overview: Duson Gihwoek (두손기획) Printing E-commerce Site

This document provides a comprehensive overview of the Duson Gihwoek printing e-commerce website codebase. It is intended to be used as a reference for developers and AI assistants working on the project.

## Project Overview

This project is a legacy PHP-based e-commerce platform for a Korean printing company named **Duson Gihwoek (두손기획)**. The website allows users to get quotes and place orders for various printing products such as stickers, flyers, business cards, and catalogs.

The codebase is currently undergoing modernization. This involves:
- Migrating from the deprecated `mysql_*` functions to the `mysqli_*` API.
- Replacing PHP short tags (`<?`) with standard tags (`<?php`).
- Refactoring legacy code to improve structure and fix bugs related to variable scope and database connection handling.

### Key Technologies

-   **Backend:** PHP (legacy procedural style)
-   **Database:** MySQL
-   **Frontend:** HTML, CSS, JavaScript, Tailwind CSS (via CDN)
-   **Dependencies:**
    -   `phpmailer/phpmailer`: For sending emails.

## Building and Running

This is a standard PHP application that runs on a web server (like Apache or Nginx) with a MySQL database.

### 1. Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

### 2. Configuration

Database and environment settings are managed in two key files:

-   **`config.env.php`**: This file contains the database credentials for different environments (e.g., local, production).
-   **`db.php`**: This file reads the configuration from `config.env.php`, establishes the database connection using `mysqli`, and sets up global configuration variables. It also includes a sophisticated system for mapping table names, likely to handle case-sensitivity issues between development and production environments.

### 3. Running the Application

1.  Set up a local web server environment (e.g., XAMPP, Docker) with PHP and MySQL.
2.  Import the database schema and data.
3.  Configure your local database credentials in `config.env.php`.
4.  Place the project files in the web server's document root.
5.  Access the project through your web browser.

## Development Conventions

### Code Style

-   The codebase is transitioning from an older, procedural style to a more modern one.
-   **PHP tags:** Always use full `<?php` tags. Avoid short tags (`<?`, `<?=`).
-   **Database API:** Use the `mysqli_*` functions for all database interactions. Do not use the deprecated `mysql_*` functions.
-   **File Naming:** The project seems to follow a convention where product-specific pages are named after the product (e.g., `sticker_admin.php`, `cadarok_admin.php`).

### Database Interaction

-   All database connections should be handled via the central `$db` object created in `db.php`.
-   Do not open or close database connections within individual scripts. The connection is managed globally.
-   Be aware of the **table name mapping system** located in `includes/table_mapper.php` and triggered by wrapper functions in `db.php` (`safe_mysqli_query`, `safe_mysqli_prepare`). Queries containing certain legacy table names are automatically rewritten. This is a critical feature to understand when debugging database queries.

### Project Structure

-   `/`: The root directory contains the main `index.php`, configuration files, and various legacy scripts.
-   `/admin/`: Contains the backend administration pages.
-   `/admin/MlangPrintAuto/`: A key directory containing the admin UI and logic for managing different printing products.
-   `/mlangprintauto/`: Contains the frontend/customer-facing pages for ordering specific products.
-   `/includes/`: Contains shared PHP code, such as functions, authentication logic, and the crucial table mapper.
-   `/css/`: Contains stylesheets.
-   `/js/`: Contains JavaScript files.
-   `composer.json`: Defines PHP dependencies.
-   `db.php`: Handles database connection and global configuration.
-   `config.env.php`: Stores environment-specific settings (e.g., database credentials).
-   `README.md`: This file contains a detailed log of a recent bug fix, which provides valuable insight into the codebase's history and common issues.
