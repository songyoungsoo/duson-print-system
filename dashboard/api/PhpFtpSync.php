<?php
/**
 * PhpFtpSync — Pure-PHP FTP synchronization class.
 *
 * Replaces bash/lftp shell scripts with portable PHP FTP extension calls.
 * Works on any PHP 7.3+ server (local dev, production Plesk, ipTIME NAS).
 *
 * Usage:
 *   $sync = new PhpFtpSync('nas.example.com', 'user', 'pass', '/HDD2/share');
 *   $sync->connect();
 *   $result = $sync->uploadDirectory('/var/www/html', '/HDD2/share');
 *   $sync->disconnect();
 */
class PhpFtpSync
{
    /** @var string */
    private $host;
    /** @var string */
    private $user;
    /** @var string */
    private $pass;
    /** @var string Remote root path */
    private $remoteRoot;
    /** @var int */
    private $port;
    /** @var resource|null FTP connection */
    private $conn;
    /** @var array Log messages */
    private $log;
    /** @var int */
    private $uploaded;
    /** @var int */
    private $skipped;
    /** @var int */
    private $failed;

    /**
     * Default exclude patterns matching the bash sync scripts.
     * @var array
     */
    private $defaultExcludes = array(
        '.git/',
        'node_modules/',
        'test-results/',
        'playwright-report/',
        '.claude/',
        'CLAUDE_DOCS/',
        '*.tar',
        '*.zip',
        '*.tar.gz',
        '.gitignore',
        '.gitattributes',
        'package-lock.json',
        'playwright.config.*',
        'tests/',
        'bbs/',
    );

    /**
     * @param string $host       FTP hostname
     * @param string $user       FTP username
     * @param string $pass       FTP password
     * @param string $remoteRoot Remote base directory
     * @param int    $port       FTP port (default 21)
     */
    public function __construct($host, $user, $pass, $remoteRoot, $port = 21)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->remoteRoot = rtrim($remoteRoot, '/');
        $this->port = (int) $port;
        $this->conn = null;
        $this->log = array();
        $this->resetCounters();
    }

    // ------------------------------------------------------------------
    //  Connection
    // ------------------------------------------------------------------

    /**
     * Open FTP connection and switch to passive mode.
     *
     * @param int $timeout Connection timeout in seconds
     * @return array ['success' => bool, 'log' => string]
     */
    public function connect($timeout = 10)
    {
        $this->addLog("Connecting to {$this->host}:{$this->port} ...");

        $conn = @ftp_connect($this->host, $this->port, $timeout);
        if (!$conn) {
            $this->addLog("ERROR: Could not connect to {$this->host}:{$this->port}");
            return $this->result(false);
        }

        $login = @ftp_login($conn, $this->user, $this->pass);
        if (!$login) {
            @ftp_close($conn);
            $this->addLog("ERROR: Login failed for user '{$this->user}'");
            return $this->result(false);
        }

        // ipTIME NAS requires passive mode
        if (!@ftp_pasv($conn, true)) {
            $this->addLog("WARNING: Could not enable passive mode");
        }

        $this->conn = $conn;
        $this->addLog("Connected (passive mode)");
        return $this->result(true);
    }

    /**
     * Close the FTP connection.
     */
    public function disconnect()
    {
        if ($this->conn) {
            @ftp_close($this->conn);
            $this->conn = null;
            $this->addLog("Disconnected");
        }
    }

    // ------------------------------------------------------------------
    //  Public API
    // ------------------------------------------------------------------

    /**
     * Upload a single file to the remote server.
     *
     * @param string $localPath  Absolute local file path
     * @param string $remotePath Absolute remote file path
     * @param bool   $dryRun     If true, log but do not upload
     * @return array
     */
    public function uploadFile($localPath, $remotePath, $dryRun = false)
    {
        $this->resetCounters();

        if (!$this->ensureConnected()) {
            return $this->result(false);
        }

        if (!file_exists($localPath) || !is_file($localPath)) {
            $this->addLog("ERROR: Local file not found: {$localPath}");
            $this->failed++;
            return $this->result(false);
        }

        $ok = $this->doUploadFile($localPath, $remotePath, $dryRun);
        return $this->result($ok);
    }

    /**
     * Mirror a local directory to a remote directory.
     * Only uploads files whose size differs or that don't exist remotely.
     *
     * @param string     $localDir  Absolute local directory
     * @param string     $remoteDir Absolute remote directory
     * @param array|null $excludes  Exclude patterns (null = use defaults)
     * @param bool       $dryRun    If true, log but do not upload
     * @return array
     */
    public function uploadDirectory($localDir, $remoteDir, $excludes = null, $dryRun = false)
    {
        set_time_limit(0);
        $this->resetCounters();

        if (!$this->ensureConnected()) {
            return $this->result(false);
        }

        if ($excludes === null) {
            $excludes = $this->defaultExcludes;
        }

        $localDir = rtrim($localDir, '/');
        $remoteDir = rtrim($remoteDir, '/');

        if (!is_dir($localDir)) {
            $this->addLog("ERROR: Local directory not found: {$localDir}");
            return $this->result(false);
        }

        $this->addLog(($dryRun ? '[DRY-RUN] ' : '') . "Mirror: {$localDir} -> {$remoteDir}");
        $this->addLog("Excludes: " . implode(', ', $excludes));

        $this->mirrorRecursive($localDir, $remoteDir, $localDir, $excludes, $dryRun);

        $this->addLog("Done. Uploaded: {$this->uploaded}, Skipped: {$this->skipped}, Failed: {$this->failed}");
        return $this->result($this->failed === 0);
    }

    /**
     * Sync files modified after a specific date.
     *
     * @param string     $localDir  Absolute local directory
     * @param string     $remoteDir Absolute remote directory
     * @param string     $sinceDate Date string parseable by strtotime (e.g. '2026-03-01')
     * @param array|null $excludes  Exclude patterns (null = use defaults)
     * @param bool       $dryRun    If true, log but do not upload
     * @return array
     */
    public function syncChanged($localDir, $remoteDir, $sinceDate, $excludes = null, $dryRun = false)
    {
        set_time_limit(0);
        $this->resetCounters();

        if (!$this->ensureConnected()) {
            return $this->result(false);
        }

        if ($excludes === null) {
            $excludes = $this->defaultExcludes;
        }

        $threshold = strtotime($sinceDate);
        if ($threshold === false) {
            $this->addLog("ERROR: Invalid date: {$sinceDate}");
            return $this->result(false);
        }

        $localDir = rtrim($localDir, '/');
        $remoteDir = rtrim($remoteDir, '/');

        if (!is_dir($localDir)) {
            $this->addLog("ERROR: Local directory not found: {$localDir}");
            return $this->result(false);
        }

        $this->addLog(($dryRun ? '[DRY-RUN] ' : '') . "Sync changed since: {$sinceDate} (" . date('Y-m-d H:i:s', $threshold) . ")");

        $files = $this->findChangedFiles($localDir, $localDir, $threshold, $excludes);
        $total = count($files);
        $this->addLog("Found {$total} changed file(s)");

        foreach ($files as $relPath) {
            $localPath = $localDir . '/' . $relPath;
            $remotePath = $remoteDir . '/' . $relPath;
            $this->doUploadFile($localPath, $remotePath, $dryRun);
        }

        $this->addLog("Done. Uploaded: {$this->uploaded}, Skipped: {$this->skipped}, Failed: {$this->failed}");
        return $this->result($this->failed === 0);
    }

    /**
     * Recursively list files on the remote server.
     *
     * @param string $dir Remote directory to list
     * @return array ['success' => bool, 'files' => array, ...]
     */
    public function listRemoteFiles($dir)
    {
        set_time_limit(0);
        $this->resetCounters();

        if (!$this->ensureConnected()) {
            $result = $this->result(false);
            $result['files'] = array();
            return $result;
        }

        $dir = rtrim($dir, '/');
        $this->addLog("Listing remote: {$dir}");

        $files = array();
        $this->listRemoteRecursive($dir, $files);

        $this->addLog("Found " . count($files) . " remote file(s)");
        $result = $this->result(true);
        $result['files'] = $files;
        return $result;
    }

    /**
     * Return all accumulated log messages.
     *
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    // ------------------------------------------------------------------
    //  Internal: recursive mirror
    // ------------------------------------------------------------------

    /**
     * Recursively mirror a local directory to remote.
     *
     * @param string $currentDir Current local directory being processed
     * @param string $remoteDir  Corresponding remote directory
     * @param string $baseDir    The original local root (for relative path calculation)
     * @param array  $excludes   Exclude patterns
     * @param bool   $dryRun     Dry-run mode
     */
    private function mirrorRecursive($currentDir, $remoteDir, $baseDir, $excludes, $dryRun)
    {
        $handle = @opendir($currentDir);
        if (!$handle) {
            $this->addLog("ERROR: Cannot open directory: {$currentDir}");
            $this->failed++;
            return;
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $localPath = $currentDir . '/' . $entry;
            $remotePath = $remoteDir . '/' . $entry;

            // Skip symlinks
            if (is_link($localPath)) {
                $this->addLog("SKIP (symlink): {$localPath}");
                $this->skipped++;
                continue;
            }

            // Calculate relative path for exclusion checking
            $relPath = substr($localPath, strlen($baseDir) + 1);

            if ($this->isExcluded($entry, $relPath, $excludes)) {
                $this->skipped++;
                continue;
            }

            if (is_dir($localPath)) {
                // Early skip for bbs/ at any depth
                if ($entry === 'bbs') {
                    $this->addLog("SKIP (bbs/): {$relPath}/");
                    $this->skipped++;
                    continue;
                }

                $this->mirrorRecursive($localPath, $remotePath, $baseDir, $excludes, $dryRun);
            } elseif (is_file($localPath)) {
                $localSize = filesize($localPath);
                $remoteSize = @ftp_size($this->conn, $remotePath);

                // Upload if remote doesn't exist (-1) or sizes differ
                if ($remoteSize === -1 || $remoteSize !== $localSize) {
                    $this->doUploadFile($localPath, $remotePath, $dryRun);
                } else {
                    $this->skipped++;
                }
            }
        }

        closedir($handle);
    }

    // ------------------------------------------------------------------
    //  Internal: find changed files
    // ------------------------------------------------------------------

    /**
     * Recursively find local files modified after a threshold time.
     *
     * @param string $currentDir Current directory
     * @param string $baseDir    Base directory for relative paths
     * @param int    $threshold  Unix timestamp threshold
     * @param array  $excludes   Exclude patterns
     * @return array Relative file paths
     */
    private function findChangedFiles($currentDir, $baseDir, $threshold, $excludes)
    {
        $result = array();
        $handle = @opendir($currentDir);
        if (!$handle) {
            return $result;
        }

        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $currentDir . '/' . $entry;

            // Skip symlinks
            if (is_link($fullPath)) {
                continue;
            }

            $relPath = substr($fullPath, strlen($baseDir) + 1);

            if ($this->isExcluded($entry, $relPath, $excludes)) {
                continue;
            }

            if (is_dir($fullPath)) {
                // Early skip for bbs/
                if ($entry === 'bbs') {
                    continue;
                }
                $sub = $this->findChangedFiles($fullPath, $baseDir, $threshold, $excludes);
                foreach ($sub as $f) {
                    $result[] = $f;
                }
            } elseif (is_file($fullPath)) {
                if (filemtime($fullPath) > $threshold) {
                    $result[] = $relPath;
                }
            }
        }

        closedir($handle);
        return $result;
    }

    // ------------------------------------------------------------------
    //  Internal: remote listing
    // ------------------------------------------------------------------

    /**
     * Recursively list remote files via ftp_rawlist.
     *
     * @param string $dir   Remote directory
     * @param array  $files Accumulator (passed by reference)
     */
    private function listRemoteRecursive($dir, &$files)
    {
        $raw = @ftp_rawlist($this->conn, $dir);
        if (!is_array($raw)) {
            return;
        }

        foreach ($raw as $line) {
            // Parse Unix-style: drwxr-xr-x 2 owner group size month day time name
            $parts = preg_split('/\s+/', $line, 9);
            if (count($parts) < 9) {
                continue;
            }

            $perms = $parts[0];
            $size = (int) $parts[4];
            $name = $parts[8];

            if ($name === '.' || $name === '..') {
                continue;
            }

            $fullPath = $dir . '/' . $name;

            if (substr($perms, 0, 1) === 'd') {
                // Directory — recurse
                $this->listRemoteRecursive($fullPath, $files);
            } elseif (substr($perms, 0, 1) === 'l') {
                // Symlink — skip
                continue;
            } else {
                $files[] = array(
                    'path' => $fullPath,
                    'size' => $size,
                );
            }
        }
    }

    // ------------------------------------------------------------------
    //  Internal: file upload
    // ------------------------------------------------------------------

    /**
     * Upload a single file, creating remote directories as needed.
     *
     * @param string $localPath  Absolute local path
     * @param string $remotePath Absolute remote path
     * @param bool   $dryRun     Dry-run flag
     * @return bool
     */
    private function doUploadFile($localPath, $remotePath, $dryRun)
    {
        $size = filesize($localPath);
        $sizeKb = round($size / 1024, 1);

        if ($dryRun) {
            $this->addLog("[DRY-RUN] Would upload: {$remotePath} ({$sizeKb} KB)");
            $this->uploaded++;
            return true;
        }

        // Ensure remote directory exists
        $remoteDir = dirname($remotePath);
        if (!$this->mkdirRecursive($remoteDir)) {
            $this->addLog("ERROR: Cannot create remote dir: {$remoteDir}");
            $this->failed++;
            return false;
        }

        $ok = @ftp_put($this->conn, $remotePath, $localPath, FTP_BINARY);
        if ($ok) {
            $this->addLog("UPLOADED: {$remotePath} ({$sizeKb} KB)");
            $this->uploaded++;
            return true;
        }

        $this->addLog("FAILED: {$remotePath} — upload error");
        $this->failed++;
        return false;
    }

    // ------------------------------------------------------------------
    //  Internal: recursive mkdir
    // ------------------------------------------------------------------

    /**
     * Create a remote directory path recursively (mkdir -p equivalent).
     *
     * @param string $dir Absolute remote directory path
     * @return bool
     */
    private function mkdirRecursive($dir)
    {
        if ($dir === '' || $dir === '/' || $dir === '.') {
            return true;
        }

        // Check if directory already exists by trying to chdir into it
        $original = @ftp_pwd($this->conn);
        if (@ftp_chdir($this->conn, $dir)) {
            // Restore working directory
            @ftp_chdir($this->conn, $original);
            return true;
        }

        // Parent doesn't exist — create recursively
        $parent = dirname($dir);
        if ($parent !== $dir && !$this->mkdirRecursive($parent)) {
            return false;
        }

        // Create this level
        $ok = @ftp_mkdir($this->conn, $dir);

        // Restore working directory
        @ftp_chdir($this->conn, $original);

        return $ok !== false;
    }

    // ------------------------------------------------------------------
    //  Internal: exclude matching
    // ------------------------------------------------------------------

    /**
     * Check if a file/directory should be excluded.
     *
     * Supports patterns:
     *   - 'name/'       → exclude directory named 'name' at any level
     *   - '*.ext'       → exclude files matching glob extension
     *   - 'exact'       → exclude exact filename match
     *   - 'pattern.*'   → wildcard match (e.g. playwright.config.*)
     *
     * @param string $entry   The filename (basename)
     * @param string $relPath Relative path from base directory
     * @param array  $excludes Exclude patterns
     * @return bool
     */
    private function isExcluded($entry, $relPath, $excludes)
    {
        foreach ($excludes as $pattern) {
            // Directory pattern: 'name/'
            if (substr($pattern, -1) === '/') {
                $dirName = substr($pattern, 0, -1);
                // Match directory name at any depth
                if ($entry === $dirName) {
                    return true;
                }
                // Match if relative path starts with or contains this dir
                if (strpos($relPath, $dirName . '/') === 0
                    || strpos($relPath, '/' . $dirName . '/') !== false) {
                    return true;
                }
                continue;
            }

            // Glob pattern with * (e.g. '*.tar', '*.zip', 'playwright.config.*')
            if (strpos($pattern, '*') !== false) {
                $regex = '/^' . str_replace(
                    array('\*'),
                    array('.*'),
                    preg_quote($pattern, '/')
                ) . '$/';
                if (preg_match($regex, $entry)) {
                    return true;
                }
                continue;
            }

            // Exact filename match
            if ($entry === $pattern) {
                return true;
            }
        }

        return false;
    }

    // ------------------------------------------------------------------
    //  Internal: helpers
    // ------------------------------------------------------------------

    /**
     * Ensure an active FTP connection exists.
     *
     * @return bool
     */
    private function ensureConnected()
    {
        if (!$this->conn) {
            $this->addLog("ERROR: Not connected. Call connect() first.");
            return false;
        }
        return true;
    }

    /**
     * Add a timestamped log entry.
     *
     * @param string $message
     */
    private function addLog($message)
    {
        $this->log[] = '[' . date('H:i:s') . '] ' . $message;
    }

    /**
     * Reset upload/skip/fail counters.
     */
    private function resetCounters()
    {
        $this->uploaded = 0;
        $this->skipped = 0;
        $this->failed = 0;
    }

    /**
     * Build a standard result array.
     *
     * @param bool $success
     * @return array
     */
    private function result($success)
    {
        return array(
            'success'  => $success,
            'uploaded' => $this->uploaded,
            'skipped'  => $this->skipped,
            'failed'   => $this->failed,
            'log'      => implode("\n", $this->log),
        );
    }
}
