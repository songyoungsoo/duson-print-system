#!/bin/bash
#
# FTP Upload Script with Exclusion Filters
# Uploads /var/www/html to FTP server with selective filtering
#
# Excludes:
# - Test files (test*, Test*)
# - Backup files/folders (*_backup*, backup_*, *사본*, *실험*)
# - Date-stamped folders (YYYYMMDD, YYMMDD)
# - Temporary files
#
# Features:
# - Overwrites existing files on server
# - Preserves directory structure
# - Shows upload progress
#

# Load FTP credentials
if [ ! -f "/var/www/html/.env.ftp" ]; then
    echo "❌ Error: .env.ftp file not found"
    echo "Create /var/www/html/.env.ftp with FTP credentials"
    exit 1
fi

source /var/www/html/.env.ftp

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo "📦 Installing lftp..."
    sudo apt-get update && sudo apt-get install -y lftp
fi

# Color codes for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}  FTP Upload - Filtered Sync${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""
echo -e "${GREEN}Source:${NC} /var/www/html"
echo -e "${GREEN}Target:${NC} ftp://${FTP_HOST}${FTP_REMOTE_PATH}"
echo ""

# Exclusion patterns
EXCLUDE_PATTERNS=(
    # Test files and directories
    "test_*"
    "test-*"
    "Test*"
    "tests/"
    "test-results/"
    "test-screenshots/"

    # Backup files and directories
    "*_backup*"
    "backup_*"
    "*backup*/"
    "*.apache_backup"

    # Korean patterns (사본, 실험)
    "*사본*"
    "*실험*"

    # Date-stamped folders (YYYYMMDD, YYMMDD)
    "*202[0-9][0-1][0-9][0-3][0-9]*"
    "*2[0-9][0-1][0-9][0-3][0-9]*"

    # Temporary and development files
    "*.tmp"
    "*.log"
    "*.swp"
    "*~"
    ".DS_Store"
    "Thumbs.db"

    # Environment and configuration
    ".env*"
    ".git*"
    ".vscode/"
    ".idea/"

    # Documentation (Claude specific)
    "CLAUDE_DOCS/"
    "claudedocs/"
    "SuperClaude/"
    "CLAUDE.md"
    "layout_structure.txt"

    # Scripts directory (keep local only)
    "scripts/"

    # Playwright reports
    "playwright-report/"

    # SQL backup files
    "*.sql"
    "*backup*.sql"
    "C:xampp*"

    # Deployment guides with dates
    "DEPLOYMENT_GUIDE_*.md"
)

# Build exclude string for lftp
EXCLUDE_STRING=""
for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    EXCLUDE_STRING="${EXCLUDE_STRING} --exclude ${pattern}"
done

echo -e "${YELLOW}Excluded patterns:${NC}"
printf '%s\n' "${EXCLUDE_PATTERNS[@]}" | sed 's/^/  - /'
echo ""

# Confirmation prompt
read -p "$(echo -e ${YELLOW}Continue with upload? [y/N]:${NC} )" -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Upload cancelled${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}Starting upload...${NC}"
echo ""

# Execute lftp mirror command
lftp -c "
set ftp:ssl-allow no
set ftp:list-options -a
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5

open ftp://${FTP_USER}:${FTP_PASS}@${FTP_HOST}:${FTP_PORT}

mirror --reverse \
       --verbose \
       --delete \
       --overwrite \
       --parallel=3 \
       ${EXCLUDE_STRING} \
       /var/www/html \
       ${FTP_REMOTE_PATH}

bye
"

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ Upload completed successfully!${NC}"
else
    echo -e "${RED}❌ Upload failed with exit code: ${EXIT_CODE}${NC}"
    echo -e "${YELLOW}Check FTP credentials and connection${NC}"
fi

echo -e "${BLUE}================================================${NC}"

exit $EXIT_CODE
