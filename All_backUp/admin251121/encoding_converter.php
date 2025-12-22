<?php
/**
 * Admin ?대?? ??泥 ?몄?? 蹂??? ?ㅽ?щ┰?
 * EUC-KR ?? UTF-8 蹂???
 *
 * ?ъ?⑸?: php encoding_converter.php
 */

// 濡?洹 ??? ?ㅼ??
$logFile = __DIR__ . '/encoding_conversion_log_' . date('Y-m-d_H-i-s') . '.txt';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    echo $logMessage;
}

function convertFileEncoding($filePath, $dryRun = false) {
    if (!file_exists($filePath)) {
        writeLog("???쇱? 議댁?ы??吏? ???듬???? $filePath");
        return false;
    }

    // ??蹂 ??? ?쎄린
    $originalContent = file_get_contents($filePath);

    if ($originalContent === false) {
        writeLog("??? ?쎄린 ?ㅽ?? $filePath");
        return false;
    }

    // ??? ?몄?? 媛?吏?
    $currentEncoding = mb_detect_encoding($originalContent, ['UTF-8', 'EUC-KR', 'CP949'], true);

    // ?? ?대? UTF-8? ???쇱?? 嫄대???곌린
    if ($currentEncoding === 'UTF-8') {
        // 異?媛?濡? 源⑥? 臾몄?? 寃?? (占쏙옙占쏙옙占쏙옙 媛??? ?⑦??
        if (strpos($originalContent, '占쏙옙占쏙옙占쏙옙') === false &&
            strpos($originalContent, '占싸깍옙占쏙옙') === false &&
            strpos($originalContent, '占쏙옙占쏙옙占쏙옙') === false) {
            writeLog("???  嫄대???? (?대? UTF-8): $filePath");
            return true;
        }
    }

    writeLog("$filePath - 媛?吏??? ?몄??? " . ($currentEncoding ?: 'UNKNOWN'));

    // EUC-KR???? UTF-8濡? 蹂??? ????
    $convertedContent = mb_convert_encoding($originalContent, 'UTF-8', 'EUC-KR');

    if ($convertedContent === false) {
        writeLog("?몄?? 蹂??? ?ㅽ?? $filePath");
        return false;
    }

    // 蹂??? ?댁?⑹? ??蹂멸낵 ???쇳??吏? ??? (?대? ?щ?瑜 UTF-8? 寃쎌??
    if ($originalContent === $convertedContent) {
        writeLog("???  嫄대???? (蹂??? 遺?????): $filePath");
        return true;
    }

    if (!$dryRun) {
        // 諛깆?? ???
        $backupPath = $filePath . '.backup_' . date('YmdHis');
        if (!copy($filePath, $backupPath)) {
            writeLog("諛깆?? ??? ?ㅽ?? $filePath");
            return false;
        }
        writeLog("諛깆?? ???? $backupPath");

        // UTF-8濡? ???
        if (file_put_contents($filePath, $convertedContent) === false) {
            writeLog("??? ??? ?ㅽ?? $filePath");
            return false;
        }
        writeLog("?? 蹂??? ??猷?: $filePath");
    } else {
        writeLog("??? [DRY RUN] 蹂??? ????: $filePath");
    }

    return true;
}

function scanAndConvert($directory, $dryRun = false) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $totalFiles = 0;
    $convertedFiles = 0;

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $totalFiles++;
            $filePath = $file->getRealPath();

            if (convertFileEncoding($filePath, $dryRun)) {
                $convertedFiles++;
            }
        }
    }

    writeLog("=== 蹂??? ??猷? ??? ===");
    writeLog("珥? PHP ??? ??: $totalFiles");
    writeLog("蹂????? ??? ??: $convertedFiles");

    return [$totalFiles, $convertedFiles];
}

// 硫?? ?ㅽ??
writeLog("=== Admin ?대?? ?몄?? 蹂??? ???? ===");
writeLog("???? ??????由? " . __DIR__);

// DRY RUN ?щ? ???
$dryRun = isset($argv[1]) && $argv[1] === '--dry-run';

if ($dryRun) {
    writeLog("??? DRY RUN 紐⑤??: ?ㅼ?? 蹂?????吏? ??怨? ???몃? ?⑸????");
} else {
    writeLog("??截?  ?ㅼ?? 蹂????? ?????⑸???? 諛깆??? ?????쇰? ???깅?⑸????");
}

// 蹂??? ?ㅽ??
$result = scanAndConvert(__DIR__, $dryRun);

writeLog("=== ?몄?? 蹂??? ??猷? ===");
writeLog("濡?洹 ???? $logFile");

if (!$dryRun) {
    writeLog("諛깆?? ???쇰?ㅼ?? *.backup_YYYYMMDDHHMMSS ?????쇰? ???λ?????듬????");
}
?>