# Gemini CLI Project Context: Duson Planning Print System

This `GEMINI.md` file provides essential context for interacting with the Duson Planning Print System project, a PHP-based print order management system.

## Project Overview

The Duson Planning Print System (두손기획인쇄) is a web application developed in PHP 7.4+ with a MySQL 5.7+ backend. The frontend utilizes PHP templates and JavaScript. It serves as a comprehensive system for managing print orders, offering various product categories such as flyers, stickers, business cards, and more.

The project is structured to support local development, testing, and deployment to a production server via FTP.

## Technologies Used

*   **Backend**: PHP 7.4+ with MySQL 5.7+
*   **Frontend**: PHP templates, JavaScript
*   **Database**: MySQL 5.7+
*   **Testing**: Playwright (E2E testing)
*   **Version Control**: Git
*   **Payment Gateway**: KG Inicis

## Building and Running

### Local Development Environment

To start the local development server (assuming a WSL2 environment with Apache and MySQL):

```bash
sudo service apache2 start
sudo service mysql start
```

Access the application in your browser at `http://localhost/`.

### Testing

End-to-end tests are performed using Playwright.

To run all tests:

```bash
npx playwright test
```

To run a specific group of tests (e.g., `group-a-readonly`):

```bash
npx playwright test --project="group-a-readonly"
```

## Deployment

The system is deployed to `dsp114.co.kr` via FTP. The critical web root directory on the production server is `/httpdocs/`.

**FTP Connection Details (for `dsp114.co.kr`):**
*   Host: `dsp114.co.kr`
*   User: `dsp1830`
*   Password: `cH*j@yzj093BeTtc` (Note: This is a sensitive credential and should be handled with care.)
*   Port: `21` (FTP)

**Web Root Path:** `httpdocs/` within the FTP root (`/`).

**Example for single file upload:**

```bash
curl -T /var/www/html/payment/inicis_return.php \
  ftp://dsp114.co.kr/httpdocs/payment/inicis_return.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

Refer to `DEPLOYMENT.md` for a comprehensive deployment guide.

## Development Conventions

*   **PHP `mysqli_stmt_bind_param`**: Always use 3-step verification for argument count matching (`$placeholder_count`, `$type_count`, `$var_count`).
*   **CSS**: Avoid using `!important`. Resolve specificity issues through proper CSS cascade and selector weighting.

## Key Files and Directories

*   `README.md`: Project overview, quick start, deployment, and conventions.
*   `DEPLOYMENT.md`: Detailed guide for deployment procedures.
*   `AGENTS.md`: Guidelines for AI agents (deployment, code rules).
*   `CLAUDE.md`: Specific working guidelines for Claude AI.
*   `config.env.php`: Environment configuration settings.
*   `db.php`: Database connection and utility functions.
*   `package.json`: Node.js project configuration, including Playwright dependencies and scripts.
*   `payment/`: Contains modules for KG Inicis payment system integration, including `inicis_config.php`.
*   `mlangprintauto/`: Houses individual product pages (e.g., `namecard/`, `inserted/`).
*   `mlangorder_printauto/`: Contains logic for order processing.
*   `admin/`: Administration panel files.
*   `scripts/`: Various utility scripts, potentially including deployment scripts.
*   `tests/` / `specs/`: Playwright test files.

## Related Utilities (Documentation Only)

*   `README_KOREAN_INPUT_KEEPER.md`: Documentation for a "Korean Input Mode Auto-Keeper" Python program. While the documentation is present, the corresponding Python script (`korean_input_keeper.py`) was not found in the current directory. This utility is described as a Windows program to automatically maintain Korean input mode.
