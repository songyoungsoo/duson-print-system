<?php
/**
 * 견적서 마이그레이션: dsp1830.shop → 로컬 DB (quotes + quote_items)
 * 
 * 구서버 38건 견적 데이터를 public/view.php 페이지에서 추출하여 신서버에 INSERT
 * 
 * 사용법:
 *   php migrate_quotes.php              -- 실제 실행
 *   php migrate_quotes.php --dry-run    -- SQL만 출력 (DB 변경 없음)
 *   php migrate_quotes.php --sql-file   -- SQL 파일로 저장
 */

// CLI only
if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

$dryRun = in_array('--dry-run', $argv);
$sqlFile = in_array('--sql-file', $argv);

require_once __DIR__ . '/../../db.php';

echo "=== 견적서 마이그레이션 (dsp1830.shop → local) ===\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no DB changes)" : ($sqlFile ? "SQL FILE output" : "LIVE INSERT")) . "\n\n";

// All 38 quotes with their tokens and metadata from index.php scraping
$quoteTokens = [
    ['id' => 161, 'token' => 'fd4ea5038b1f593e6d50762fc21fd5012e62b7df914ce0faa88253fbcd71394b', 'status_text' => '확인', 'created' => '2026-01-24 01:42:00', 'converted_order' => ''],
    ['id' => 160, 'token' => '6306a5ef70b5733d788ac1076dc70b3be5e4eb26c289a27f7e383e0db693ad2e', 'status_text' => '확인', 'created' => '2026-01-15 10:52:00', 'converted_order' => ''],
    ['id' => 159, 'token' => 'c55a4491003300df0ce8296bfadd55fbcbe6456e7bd46b21f483346116b57550', 'status_text' => '작성중', 'created' => '2026-01-15 10:52:00', 'converted_order' => ''],
    ['id' => 158, 'token' => '1b3523e4153ab49f5f5ce51bc9cf46a344d93a1a6eba67dec8949dc816d4980f', 'status_text' => '작성중', 'created' => '2026-01-12 02:14:00', 'converted_order' => ''],
    ['id' => 157, 'token' => '4d467b38c01fd6c8b5196b1f4711ab41c233b1cc03cb68f4ca642716a50be123', 'status_text' => '발송', 'created' => '2025-12-24 11:06:00', 'converted_order' => ''],
    ['id' => 156, 'token' => '6a662261cd0e9568b8ed99a4041c4f1833831f0507d9088ef87f261a9b605401', 'status_text' => '확인', 'created' => '2025-12-23 14:32:00', 'converted_order' => ''],
    ['id' => 155, 'token' => '9eb57ccec304be68c0613ae15e79138ac7762537293217df9cb56856ed6f9873', 'status_text' => '확인', 'created' => '2025-12-19 17:20:00', 'converted_order' => ''],
    ['id' => 154, 'token' => '90dde43a8b3f7a94fb7486e031b010fd11b67c62e4a90308a6251dbf8ddc4e27', 'status_text' => '발송', 'created' => '2025-12-19 14:53:00', 'converted_order' => ''],
    ['id' => 153, 'token' => '089cfceb45f8a2bb6d52b21e330207de5526be1c00d3684bd999d708d77bef35', 'status_text' => '발송', 'created' => '2025-12-19 10:22:00', 'converted_order' => ''],
    ['id' => 152, 'token' => '5d60ba9ee02d1f46e36e045dc8351438eaf5d2e31ca8aefd789c0b6f30aedd68', 'status_text' => '확인', 'created' => '2025-12-19 09:21:00', 'converted_order' => ''],
    ['id' => 151, 'token' => '7b1658c750c17a7b7ffd80c73af8acafdaf05c87e32ef6828f00ce7330c5eb21', 'status_text' => '확인', 'created' => '2025-12-18 17:51:00', 'converted_order' => ''],
    ['id' => 150, 'token' => '5d522bc6bfe53814237f8b98421edb3769fbec5506265148a14f6c39889f3ac4', 'status_text' => '확인', 'created' => '2025-12-18 09:02:00', 'converted_order' => ''],
    ['id' => 149, 'token' => 'c447d2cc7f729dd2cc6cbeb1b29871dbece6c3e699dfd0a4ad8e3ef445c2e63b', 'status_text' => '작성중', 'created' => '2025-12-17 15:43:00', 'converted_order' => ''],
    ['id' => 148, 'token' => '96c696466b74cf99413d1ac66efc931a6dc717bdf1958b622037aeeb5ca05663', 'status_text' => '발송', 'created' => '2025-12-12 11:39:00', 'converted_order' => ''],
    ['id' => 147, 'token' => 'a5e4feee89488fbc6c0555ab862e6908e446ab7d94e6b77c4d6c35db76c1aac8', 'status_text' => '확인', 'created' => '2025-12-12 07:39:00', 'converted_order' => ''],
    ['id' => 141, 'token' => '785e1dfe897a9bcc29be4f2c48c97fc0a9b689b6a3c46f23ec40fc31392c4f6c', 'status_text' => '확인', 'created' => '2025-12-09 15:33:00', 'converted_order' => ''],
    ['id' => 140, 'token' => 'df66d9f0c1e4f4093142af45851a32965d9ac1d5824fd581925419e51fb60d2b', 'status_text' => '발송', 'created' => '2025-12-09 09:25:00', 'converted_order' => ''],
    ['id' => 127, 'token' => '3214a9a7be93378d971faa516968a09ce80bef81c5048fa7e05be3dba2bbd9fb', 'status_text' => '확인', 'created' => '2025-12-04 09:59:00', 'converted_order' => ''],
    ['id' => 124, 'token' => '4b32c0ef1142077ebde3602569dc2f23991a630a794a947dd18b7758c6552207', 'status_text' => '작성중', 'created' => '2025-12-03 15:05:00', 'converted_order' => ''],
    ['id' => 123, 'token' => 'd49798287f2442a243882b791c78b47b653d32017397b517f2743f30d2cb2cd0', 'status_text' => '승인', 'created' => '2025-12-03 13:11:00', 'converted_order' => ''],
    ['id' => 121, 'token' => '027d97b51d04984937fc0c87fc13becc5708eb5affd232218e25b1f20a99342e', 'status_text' => '확인', 'created' => '2025-12-02 14:55:00', 'converted_order' => ''],
    ['id' => 120, 'token' => 'f24fc8ef58e99aa1a6f1995179f2591204d74e1ec13865bbe7bff1adfd543128', 'status_text' => '승인', 'created' => '2025-12-02 14:48:00', 'converted_order' => ''],
    ['id' => 119, 'token' => 'df9282ea2e0d9eb1033e31402f884c8318dba02290290fb691a0c2435d78edb3', 'status_text' => '확인', 'created' => '2025-12-02 13:53:00', 'converted_order' => ''],
    ['id' => 118, 'token' => '00c604e09c2983fe673b156b784bf2e35a975b925b9346651ba065d94c2f5396', 'status_text' => '확인', 'created' => '2025-12-02 13:14:00', 'converted_order' => ''],
    ['id' => 113, 'token' => 'cf6eca3ab18cf00a110636850c5002bbcd3d1fc85eb0ff24bbc9bd8cec49b934', 'status_text' => '발송', 'created' => '2025-12-01 15:47:00', 'converted_order' => ''],
    ['id' => 112, 'token' => '1817c6139989faeb96e5b69c11308a68d3bf3d22ea9f8f2dd52ec9270cd95ec4', 'status_text' => '주문전환', 'created' => '2025-12-01 13:36:00', 'converted_order' => '#103881'],
    ['id' => 111, 'token' => 'bb8eff9ba419f100f4c846e9c97c4b0dea17f5c1ddc2e332c6bb0648c39cf5ef', 'status_text' => '발송', 'created' => '2025-12-01 13:22:00', 'converted_order' => ''],
    ['id' => 109, 'token' => 'da240688f7214a97deb9f6b5d4369451acc5425712dda66bd4e1b37954a1e15a', 'status_text' => '주문전환', 'created' => '2025-12-01 11:24:00', 'converted_order' => '#103883'],
    ['id' => 108, 'token' => 'bfc31103ead144b9142255ff7fbf90fe8420808e69f054ba058c21bfb4da342e', 'status_text' => '확인', 'created' => '2025-12-01 11:09:00', 'converted_order' => ''],
    ['id' => 99, 'token' => '79504aa6dae95e70ba807aafb75aeb065d517ba32cf8de675dc4fc565f2ecc74', 'status_text' => '발송', 'created' => '2025-11-28 15:36:00', 'converted_order' => ''],
    ['id' => 98, 'token' => 'd860f1e116ef262110eed818d8cd3483fcc0f743609fdb4f123ea34a705e57a6', 'status_text' => '발송', 'created' => '2025-11-28 11:16:00', 'converted_order' => ''],
    ['id' => 97, 'token' => 'e87c27b3116e692056de0e960f259d4d59b55d3700e3afc9cfe30c3da8a3586f', 'status_text' => '발송', 'created' => '2025-11-28 11:14:00', 'converted_order' => ''],
    ['id' => 96, 'token' => '2077afda6b58cbbf5b3d27a67779c22097715e36264eebe16ce58c37c3fbc331', 'status_text' => '발송', 'created' => '2025-11-28 09:08:00', 'converted_order' => ''],
    ['id' => 95, 'token' => '577adada0094af68bec297a08f430b7c4b2aaf858080aee95ee0dd8cba7fae99', 'status_text' => '확인', 'created' => '2025-11-27 18:18:00', 'converted_order' => ''],
    ['id' => 94, 'token' => '62c2cb630c177a63f7a0e2cbd940ff20134d72e456e8dd5e8202eaea6a25a594', 'status_text' => '확인', 'created' => '2025-11-27 17:15:00', 'converted_order' => ''],
    ['id' => 93, 'token' => '00c9364c715ad5e1d3b5107fc4795f298fb0b60947f8719d0e91476c2696e1ff', 'status_text' => '확인', 'created' => '2025-11-27 16:28:00', 'converted_order' => ''],
    ['id' => 91, 'token' => '37b67559e99f51e242c8aea924f982b43ba53d17c265efb82dd068d6c9303de3', 'status_text' => '발송', 'created' => '2025-11-27 10:21:00', 'converted_order' => ''],
    ['id' => 90, 'token' => '4fb68995f23a4b315d917679645d1b8ade664ceed400fb41736305c80bf4441b', 'status_text' => '작성중', 'created' => '2025-11-27 10:19:00', 'converted_order' => ''],
];

// Status mapping: Korean text → DB ENUM
$statusMap = [
    '확인' => 'viewed',
    '작성중' => 'draft',
    '발송' => 'sent',
    '승인' => 'accepted',
    '주문전환' => 'converted',
];

$baseUrl = 'http://dsp1830.shop/mlangprintauto/quote/public/view.php?token=';

$successCount = 0;
$errorCount = 0;
$sqlStatements = [];

foreach ($quoteTokens as $idx => $qt) {
    $url = $baseUrl . $qt['token'];
    echo "[" . ($idx + 1) . "/38] Fetching id={$qt['id']} ... ";
    
    // Fetch the page
    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
        ]
    ]);
    
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        echo "FAILED (network error)\n";
        $errorCount++;
        continue;
    }
    
    // Parse HTML
    $data = parseQuoteHtml($html, $qt);
    if ($data === null) {
        echo "FAILED (parse error)\n";
        $errorCount++;
        continue;
    }
    
    echo "OK — {$data['quote_no']}, {$data['customer_company']}, items=" . count($data['items']) . ", total=" . number_format($data['grand_total']) . "\n";
    
    // Generate SQL
    $status = $statusMap[$qt['status_text']] ?? 'draft';
    $convertedOrder = str_replace('#', '', $qt['converted_order']);
    
    // Determine valid_until from parsed data
    $validUntil = $data['valid_until'];
    
    // Quote INSERT
    $quoteSql = sprintf(
        "INSERT INTO quotes (quote_no, quote_type, public_token, customer_name, customer_company, customer_phone, customer_email, supply_total, vat_total, grand_total, valid_until, status, converted_order_no, notes, created_by, created_at, updated_at) VALUES (%s, 'quotation', %s, %s, %s, '', '', %d, %d, %d, %s, %s, %s, %s, 0, %s, %s)",
        quote_str($db, $data['quote_no']),
        quote_str($db, $qt['token']),
        quote_str($db, $data['customer_name']),
        quote_str($db, $data['customer_company']),
        $data['supply_total'],
        $data['vat_total'],
        $data['grand_total'],
        quote_str($db, $validUntil),
        quote_str($db, $status),
        quote_str($db, $convertedOrder),
        quote_str($db, $data['notes']),
        quote_str($db, $qt['created']),
        quote_str($db, $qt['created'])
    );
    
    $sqlStatements[] = $quoteSql;
    
    if (!$dryRun && !$sqlFile) {
        // Check for duplicate quote_no
        $checkStmt = mysqli_prepare($db, "SELECT id FROM quotes WHERE quote_no = ? LIMIT 1");
        mysqli_stmt_bind_param($checkStmt, "s", $data['quote_no']);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        if (mysqli_num_rows($checkResult) > 0) {
            echo "  ⚠️  SKIP (duplicate quote_no: {$data['quote_no']})\n";
            mysqli_stmt_close($checkStmt);
            continue;
        }
        mysqli_stmt_close($checkStmt);
        
        $result = mysqli_query($db, $quoteSql);
        if (!$result) {
            echo "  ❌ INSERT quote failed: " . mysqli_error($db) . "\n";
            $errorCount++;
            continue;
        }
        $newQuoteId = mysqli_insert_id($db);
        echo "  ✅ quotes INSERT → id={$newQuoteId}\n";
        
        // Insert items
        $itemCount = 0;
        foreach ($data['items'] as $itemIdx => $item) {
            $itemSql = sprintf(
                "INSERT INTO quote_items (quote_id, item_no, product_name, specification, quantity, unit, unit_price, supply_price, source_type, notes, created_at) VALUES (%d, %d, %s, %s, %d, %s, %d, %d, 'manual', %s, %s)",
                $newQuoteId,
                $itemIdx + 1,
                quote_str($db, $item['product_name']),
                quote_str($db, $item['specification']),
                $item['quantity'],
                quote_str($db, $item['unit']),
                $item['unit_price'],
                $item['supply_price'],
                quote_str($db, $item['notes']),
                quote_str($db, $qt['created'])
            );
            
            $itemResult = mysqli_query($db, $itemSql);
            if ($itemResult) {
                $itemCount++;
            } else {
                echo "  ❌ INSERT item #{$itemIdx} failed: " . mysqli_error($db) . "\n";
            }
        }
        echo "  ✅ quote_items INSERT → {$itemCount} items\n";
        $successCount++;
    } else {
        // For dry-run / sql-file, also generate item SQL placeholders
        foreach ($data['items'] as $itemIdx => $item) {
            $itemSql = sprintf(
                "INSERT INTO quote_items (quote_id, item_no, product_name, specification, quantity, unit, unit_price, supply_price, source_type, notes, created_at) VALUES (LAST_INSERT_ID(), %d, %s, %s, %d, %s, %d, %d, 'manual', %s, %s)",
                $itemIdx + 1,
                quote_str($db, $item['product_name']),
                quote_str($db, $item['specification']),
                $item['quantity'],
                quote_str($db, $item['unit']),
                $item['unit_price'],
                $item['supply_price'],
                quote_str($db, $item['notes']),
                quote_str($db, $qt['created'])
            );
            $sqlStatements[] = $itemSql;
        }
        $successCount++;
    }
    
    // Be polite to old server
    usleep(300000); // 300ms delay
}

// Output summary
echo "\n=== 마이그레이션 결과 ===\n";
echo "성공: {$successCount}건\n";
echo "실패: {$errorCount}건\n";
echo "총: " . count($quoteTokens) . "건\n";

if ($sqlFile) {
    $sqlOutput = "-- 견적서 마이그레이션 SQL (dsp1830.shop → dsp114.co.kr)\n";
    $sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlOutput .= "SET NAMES utf8mb4;\n\n";
    foreach ($sqlStatements as $sql) {
        $sqlOutput .= $sql . ";\n";
    }
    $outFile = __DIR__ . '/migrate_quotes_' . date('Ymd_His') . '.sql';
    file_put_contents($outFile, $sqlOutput);
    echo "\nSQL 파일 저장: {$outFile}\n";
}

if ($dryRun) {
    echo "\n--- DRY RUN SQL (처음 5개) ---\n";
    foreach (array_slice($sqlStatements, 0, 10) as $sql) {
        echo $sql . ";\n\n";
    }
    echo "... (총 " . count($sqlStatements) . " statements)\n";
}

mysqli_close($db);

// ==================== Helper Functions ====================

function quote_str($db, $value) {
    if ($value === null || $value === '') {
        return "''";
    }
    return "'" . mysqli_real_escape_string($db, $value) . "'";
}

function parseQuoteHtml($html, $qt) {
    // Suppress warnings from DOMDocument
    libxml_use_internal_errors(true);
    
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    
    libxml_clear_errors();
    
    $xpath = new DOMXPath($doc);
    
    $data = [
        'quote_no' => '',
        'customer_name' => '',
        'customer_company' => '',
        'valid_until' => '',
        'supply_total' => 0,
        'vat_total' => 0,
        'grand_total' => 0,
        'notes' => '',
        'items' => [],
    ];
    
    // 1. Extract quote_no from <title> or .quote-no div
    $titleNodes = $xpath->query('//title');
    if ($titleNodes->length > 0) {
        $title = $titleNodes->item(0)->textContent;
        if (preg_match('/QT-\d{8}-\d{3}(?:-v\d+)?/', $title, $m)) {
            $data['quote_no'] = $m[0];
        }
    }
    if (empty($data['quote_no'])) {
        $qnoNodes = $xpath->query("//*[contains(@class, 'quote-no')]");
        if ($qnoNodes->length > 0) {
            $text = $qnoNodes->item(0)->textContent;
            if (preg_match('/QT-\d{8}-\d{3}(?:-v\d+)?/', $text, $m)) {
                $data['quote_no'] = $m[0];
            }
        }
    }
    
    // 2. Extract header info from the main info table
    //    Structure: 견적일 | date | (supplier table rowspan)
    //               회사명 | company
    //               담당자 | name 귀하
    //               유효기간 | date까지
    $tds = $xpath->query("//table[1]//td");
    foreach ($tds as $td) {
        $text = trim($td->textContent);
        $boldText = '';
        // Check if this is a label cell (bold/background)
        $style = $td->getAttribute('style');
        $isBold = (strpos($style, 'bold') !== false || strpos($style, 'f0f0f0') !== false);
        
        if ($isBold && $text === '회사명') {
            // Next sibling should be company name
            $next = $td->nextSibling;
            while ($next && $next->nodeType !== XML_ELEMENT_NODE) {
                $next = $next->nextSibling;
            }
            if ($next) {
                $data['customer_company'] = trim($next->textContent);
            }
        }
        
        if ($isBold && $text === '담당자') {
            $next = $td->nextSibling;
            while ($next && $next->nodeType !== XML_ELEMENT_NODE) {
                $next = $next->nextSibling;
            }
            if ($next) {
                $name = trim($next->textContent);
                $name = str_replace(' 귀하', '', $name);
                $name = str_replace('귀하', '', $name);
                $data['customer_name'] = trim($name);
            }
        }
        
        if ($isBold && $text === '유효기간') {
            $next = $td->nextSibling;
            while ($next && $next->nodeType !== XML_ELEMENT_NODE) {
                $next = $next->nextSibling;
            }
            if ($next) {
                $validText = trim($next->textContent);
                // Parse "2026년 01월 31일까지" → 2026-01-31
                if (preg_match('/(\d{4})년\s*(\d{2})월\s*(\d{2})일/', $validText, $m)) {
                    $data['valid_until'] = "{$m[1]}-{$m[2]}-{$m[3]}";
                }
            }
        }
    }
    
    // 3. Extract totals from tfoot
    $tfootTds = $xpath->query("//table[contains(@class, 'items-table')]//tfoot//td");
    $footerValues = [];
    foreach ($tfootTds as $ftd) {
        $text = trim($ftd->textContent);
        $cleanNum = intval(str_replace([',', ' ', '원'], '', $text));
        if ($cleanNum > 0) {
            $footerValues[] = $cleanNum;
        }
    }
    
    // Footer values order: supply_total, vat_total, [delivery], [discount], grand_total
    if (count($footerValues) >= 3) {
        $data['supply_total'] = $footerValues[0];
        $data['vat_total'] = $footerValues[1];
        $data['grand_total'] = $footerValues[count($footerValues) - 1];
    }
    
    // 4. Extract items from items-table tbody
    $itemRows = $xpath->query("//table[contains(@class, 'items-table')]//tbody//tr");
    foreach ($itemRows as $row) {
        $cells = $xpath->query('.//td', $row);
        if ($cells->length < 7) continue;
        
        $no = trim($cells->item(0)->textContent);
        $productName = trim($cells->item(1)->textContent);
        $spec = trim($cells->item(2)->textContent);
        $qty = trim($cells->item(3)->textContent);
        $unit = trim($cells->item(4)->textContent);
        $unitPrice = trim($cells->item(5)->textContent);
        $supplyPrice = trim($cells->item(6)->textContent);
        $notes = $cells->length >= 8 ? trim($cells->item(7)->textContent) : '';
        
        // Skip empty rows (no product name or no = &nbsp;)
        if (empty($productName) || $no === '' || $no === "\xC2\xA0" || $no === "\xA0") {
            continue;
        }
        
        // Clean numeric values
        $qtyNum = floatval(str_replace(',', '', $qty));
        $unitPriceNum = intval(str_replace(',', '', $unitPrice));
        $supplyPriceNum = intval(str_replace(',', '', $supplyPrice));
        
        $data['items'][] = [
            'product_name' => $productName,
            'specification' => $spec,
            'quantity' => $qtyNum > 0 ? $qtyNum : 1,
            'unit' => !empty($unit) ? $unit : '개',
            'unit_price' => $unitPriceNum,
            'supply_price' => $supplyPriceNum,
            'notes' => $notes,
        ];
    }
    
    // 5. Extract notes from footer-info
    $footerNodes = $xpath->query("//*[contains(@class, 'footer-info')]//p");
    foreach ($footerNodes as $pNode) {
        $pText = trim($pNode->textContent);
        if (strpos($pText, '비 고') !== false || strpos($pText, '비고') !== false) {
            $notesText = preg_replace('/^.*?비\s*고\s*[:：]\s*/u', '', $pText);
            $data['notes'] = trim($notesText);
        }
    }
    
    return $data;
}
