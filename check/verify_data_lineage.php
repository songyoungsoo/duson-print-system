<?php
/**
 * Data Lineage Cross-Check 검증 스크립트
 *
 * Grand Design 아키텍처의 데이터 일관성 검증
 *
 * @package DusonPrint
 * @since 2026-01-13
 */

// 관리자 인증 체크
require_once __DIR__ . '/../includes/auth.php';

// Core 모듈 로드
require_once __DIR__ . '/../lib/core_print_logic.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: text/html; charset=utf-8');

// 결과 저장
$results = [];
$passed = 0;
$failed = 0;

/**
 * 테스트 결과 기록
 */
function recordTest($name, $expected, $actual, $description = '') {
    global $results, $passed, $failed;

    $success = ($expected === $actual);
    if ($success) {
        $passed++;
    } else {
        $failed++;
    }

    $results[] = [
        'name' => $name,
        'expected' => $expected,
        'actual' => $actual,
        'success' => $success,
        'description' => $description
    ];

    return $success;
}

// ============================================================
// 테스트 1: QuantityFormatter SSOT 검증
// ============================================================

// TC-01: 전단지 0.5연 포맷
$result = QuantityFormatter::format(0.5, 'R', 2000);
recordTest(
    'TC-01: 전단지 0.5연 포맷',
    '0.5연 (2,000매)',
    $result,
    'QuantityFormatter::format(0.5, R, 2000)'
);

// TC-02: 명함 1000매 포맷
$result = QuantityFormatter::format(1000, 'S');
recordTest(
    'TC-02: 명함 1000매 포맷',
    '1,000매',
    $result,
    'QuantityFormatter::format(1000, S)'
);

// TC-03: 카다록 10부 포맷
$result = QuantityFormatter::format(10, 'B');
recordTest(
    'TC-03: 카다록 10부 포맷',
    '10부',
    $result,
    'QuantityFormatter::format(10, B)'
);

// TC-04: NCR양식지 5권 포맷
$result = QuantityFormatter::format(5, 'V');
recordTest(
    'TC-04: NCR양식지 5권 포맷',
    '5권',
    $result,
    'QuantityFormatter::format(5, V)'
);

// ============================================================
// 테스트 2: PrintCore 파사드 검증
// ============================================================

// TC-05: PrintCore 포맷팅
$result = PrintCore::formatQuantity(0.5, 'R', 2000);
recordTest(
    'TC-05: PrintCore 포맷팅',
    '0.5연 (2,000매)',
    $result,
    'PrintCore::formatQuantity(0.5, R, 2000)'
);

// TC-06: 제품별 단위 조회 - 전단지
$result = PrintCore::getUnitName('inserted');
recordTest(
    'TC-06: 전단지 단위',
    '연',
    $result,
    'PrintCore::getUnitName(inserted)'
);

// TC-07: 제품별 단위 조회 - 스티커
$result = PrintCore::getUnitName('sticker_new');
recordTest(
    'TC-07: 스티커 단위',
    '매',
    $result,
    'PrintCore::getUnitName(sticker_new)'
);

// ============================================================
// 테스트 3: DB 샛밥 조회 검증
// ============================================================

// TC-08: 전단지 0.5연 매수 조회
$result = PrintCore::lookupInsertedSheets(0.5);
recordTest(
    'TC-08: 0.5연 매수 DB조회',
    2000,
    $result,
    'PrintCore::lookupInsertedSheets(0.5) from mlangprintauto_inserted'
);

// TC-09: 전단지 1연 매수 조회
$result = PrintCore::lookupInsertedSheets(1);
recordTest(
    'TC-09: 1연 매수 DB조회',
    4000,
    $result,
    'PrintCore::lookupInsertedSheets(1) from mlangprintauto_inserted'
);

// TC-10: 존재하지 않는 연수 조회 (0 반환 확인 - 계산 금지)
$result = PrintCore::lookupInsertedSheets(999);
recordTest(
    'TC-10: 없는 연수 조회 → 0',
    0,
    $result,
    'PrintCore::lookupInsertedSheets(999) should return 0, not calculate'
);

// ============================================================
// 테스트 4: 레거시 데이터 추출 검증
// ============================================================

// TC-11: 전단지 레거시 추출
$legacyData = ['MY_amount' => 0.5, 'mesu' => 2000, 'product_type' => 'inserted'];
$result = QuantityFormatter::extractFromLegacy($legacyData, 'inserted');
recordTest(
    'TC-11: 전단지 레거시 추출 - qty_value',
    0.5,
    $result['qty_value'],
    'extractFromLegacy(inserted) qty_value'
);

recordTest(
    'TC-11b: 전단지 레거시 추출 - unit_code',
    'R',
    $result['qty_unit_code'],
    'extractFromLegacy(inserted) qty_unit_code'
);

recordTest(
    'TC-11c: 전단지 레거시 추출 - qty_sheets',
    2000,
    $result['qty_sheets'],
    'extractFromLegacy(inserted) qty_sheets'
);

// TC-12: 명함 천단위 변환 (MY_amount < 10)
$legacyData = ['MY_amount' => 1];
$result = QuantityFormatter::extractFromLegacy($legacyData, 'namecard');
recordTest(
    'TC-12: 명함 천단위 변환',
    1000,
    $result['qty_value'],
    'extractFromLegacy(namecard) MY_amount=1 → 1000'
);

// TC-13: 명함 mesu 우선
$legacyData = ['MY_amount' => 1, 'mesu' => 500];
$result = QuantityFormatter::extractFromLegacy($legacyData, 'namecard');
recordTest(
    'TC-13: 명함 mesu 우선',
    500,
    $result['qty_value'],
    'extractFromLegacy(namecard) mesu=500 takes priority'
);

// ============================================================
// 테스트 5: ProductSpecFormatter 검증
// ============================================================

// TC-14: ProductSpecFormatter 인스턴스
$formatter = PrintCore::getSpecFormatter();
recordTest(
    'TC-14: ProductSpecFormatter 로드',
    true,
    ($formatter instanceof ProductSpecFormatter),
    'PrintCore::getSpecFormatter() returns ProductSpecFormatter'
);

// ============================================================
// 테스트 6: 헬퍼 함수 검증
// ============================================================

// TC-15: duson_format_qty 헬퍼
$result = duson_format_qty(500, 'S');
recordTest(
    'TC-15: duson_format_qty 헬퍼',
    '500매',
    $result,
    'duson_format_qty(500, S)'
);

// TC-16: duson_get_unit 헬퍼
$result = duson_get_unit('cadarok');
recordTest(
    'TC-16: duson_get_unit 헬퍼',
    '부',
    $result,
    'duson_get_unit(cadarok)'
);

// ============================================================
// 테스트 7: 컴포넌트 로드 상태
// ============================================================

$version = PrintCore::version();
recordTest(
    'TC-17: QuantityFormatter 로드',
    true,
    $version['components']['QuantityFormatter'],
    'class_exists(QuantityFormatter)'
);

recordTest(
    'TC-18: ProductSpecFormatter 로드',
    true,
    $version['components']['ProductSpecFormatter'],
    'class_exists(ProductSpecFormatter)'
);

recordTest(
    'TC-19: SpecDisplayService 로드',
    true,
    $version['components']['SpecDisplayService'],
    'class_exists(SpecDisplayService)'
);

recordTest(
    'TC-20: DataAdapter 로드',
    true,
    $version['components']['DataAdapter'],
    'class_exists(DataAdapter)'
);

// ============================================================
// 결과 출력
// ============================================================

$total = $passed + $failed;
$passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Lineage Cross-Check 검증</title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .summary-stats {
            display: flex;
            gap: 20px;
        }
        .stat {
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-size: 1.2em;
        }
        .stat-passed { background: #4CAF50; }
        .stat-failed { background: #f44336; }
        .stat-total { background: #2196F3; }
        .stat-rate { background: #9C27B0; }
        .test-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-table th {
            background: #333;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .test-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        .test-table tr:hover {
            background: #f9f9f9;
        }
        .status-pass {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-fail {
            color: #f44336;
            font-weight: bold;
        }
        .expected, .actual {
            font-family: monospace;
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .timestamp {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>🔍 Data Lineage Cross-Check 검증</h1>

    <div class="summary">
        <h2>검증 결과 요약</h2>
        <div class="summary-stats">
            <div class="stat stat-total">총 테스트: <?= $total ?></div>
            <div class="stat stat-passed">✅ 통과: <?= $passed ?></div>
            <div class="stat stat-failed">❌ 실패: <?= $failed ?></div>
            <div class="stat stat-rate">통과율: <?= $passRate ?>%</div>
        </div>
        <p class="timestamp">검증 시각: <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <table class="test-table">
        <thead>
            <tr>
                <th>상태</th>
                <th>테스트명</th>
                <th>기대값</th>
                <th>실제값</th>
                <th>설명</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
                <td class="<?= $r['success'] ? 'status-pass' : 'status-fail' ?>">
                    <?= $r['success'] ? '✅ PASS' : '❌ FAIL' ?>
                </td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><span class="expected"><?= htmlspecialchars(var_export($r['expected'], true)) ?></span></td>
                <td><span class="actual"><?= htmlspecialchars(var_export($r['actual'], true)) ?></span></td>
                <td><?= htmlspecialchars($r['description']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary" style="margin-top: 20px;">
        <h3>📋 테스트 범위</h3>
        <ul>
            <li>✅ QuantityFormatter SSOT 검증 (TC-01 ~ TC-04)</li>
            <li>✅ PrintCore 파사드 검증 (TC-05 ~ TC-07)</li>
            <li>✅ DB 샛밥 조회 검증 (TC-08 ~ TC-10)</li>
            <li>✅ 레거시 데이터 추출 검증 (TC-11 ~ TC-13)</li>
            <li>✅ ProductSpecFormatter 검증 (TC-14)</li>
            <li>✅ 헬퍼 함수 검증 (TC-15 ~ TC-16)</li>
            <li>✅ 컴포넌트 로드 상태 (TC-17 ~ TC-20)</li>
        </ul>
    </div>
</body>
</html>
