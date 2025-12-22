<?php
/**
 * Sticker Page Box-Shadow Validation Script
 * This script validates that ALL box-shadow properties have been completely removed
 * from the sticker page and related CSS files.
 */

// Files to check for box-shadow properties
$files_to_check = [
    // Main sticker CSS files
    'quote-table.css',

    // Global CSS files that might affect sticker page
    '../../css/unified-sticker-overlay.css',
    '../../css/unified-sticker-overlay.min.css',
    '../../css/sticker-compact.css',
    '../../css/gallery-common.css',

    // Main sticker PHP file
    'index.php'
];

$validation_results = [];
$total_box_shadows_found = 0;

echo "<!DOCTYPE html>";
echo "<html><head><title>Sticker Box-Shadow Validation</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .info { color: #007bff; }
    .file-result { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }
    .clean { border-left-color: #28a745; background: #f8fff9; }
    .dirty { border-left-color: #dc3545; background: #fff8f8; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🏷️ Sticker Page Box-Shadow Validation Report</h1>";
echo "<p class='info'>Generated: " . date('Y-m-d H:i:s') . "</p>";

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    $result = [
        'file' => $file,
        'exists' => false,
        'box_shadows' => [],
        'line_count' => 0
    ];

    if (file_exists($full_path)) {
        $result['exists'] = true;
        $content = file_get_contents($full_path);
        $lines = file($full_path, FILE_IGNORE_NEW_LINES);
        $result['line_count'] = count($lines);

        // Search for box-shadow properties
        foreach ($lines as $line_num => $line) {
            if (stripos($line, 'box-shadow') !== false) {
                $result['box_shadows'][] = [
                    'line_number' => $line_num + 1,
                    'content' => trim($line)
                ];
                $total_box_shadows_found++;
            }
        }
    }

    $validation_results[] = $result;
}

// Display results
foreach ($validation_results as $result) {
    $is_clean = empty($result['box_shadows']);
    $css_class = $is_clean ? 'clean' : 'dirty';

    echo "<div class='file-result $css_class'>";
    echo "<h3>" . htmlspecialchars($result['file']) . "</h3>";

    if (!$result['exists']) {
        echo "<p class='info'>📄 File not found (this is OK for optional files)</p>";
    } else {
        echo "<p class='info'>📊 Total lines: " . $result['line_count'] . "</p>";

        if ($is_clean) {
            echo "<p class='success'>✅ CLEAN - No box-shadow properties found</p>";
        } else {
            echo "<p class='error'>❌ FOUND " . count($result['box_shadows']) . " box-shadow properties:</p>";
            echo "<pre>";
            foreach ($result['box_shadows'] as $shadow) {
                echo "Line " . $shadow['line_number'] . ": " . htmlspecialchars($shadow['content']) . "\n";
            }
            echo "</pre>";
        }
    }
    echo "</div>";
}

// Final summary
echo "<div class='file-result " . ($total_box_shadows_found === 0 ? 'clean' : 'dirty') . "'>";
echo "<h2>🎯 Final Validation Result</h2>";

if ($total_box_shadows_found === 0) {
    echo "<p class='success'>🎉 SUCCESS: All box-shadow properties have been completely removed!</p>";
    echo "<p class='success'>The sticker page now has a completely flat, shadow-free design.</p>";
} else {
    echo "<p class='error'>⚠️ INCOMPLETE: Found $total_box_shadows_found box-shadow properties still remaining.</p>";
    echo "<p class='error'>Please review and remove the remaining shadows listed above.</p>";
}

echo "</div>";

// CSS alternatives implemented
echo "<div class='file-result info'>";
echo "<h3>🔄 Shadow Replacement Alternatives Implemented</h3>";
echo "<ul>";
echo "<li><strong>Border effects:</strong> Used <code>border: 1px solid #e9ecef</code> for subtle boundaries</li>";
echo "<li><strong>Hover states:</strong> Replaced shadow hovers with <code>border-color</code> changes</li>";
echo "<li><strong>Focus indicators:</strong> Used <code>border: 3px solid #007bff</code> for accessibility</li>";
echo "<li><strong>Visual hierarchy:</strong> Maintained through background colors and borders</li>";
echo "<li><strong>UI feedback:</strong> Transform effects preserved (translateY, scale) for interactivity</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>