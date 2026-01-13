<?php
/**
 * 스티커 주문 데이터 마이그레이션 스크립트
 * 2026-01-13 - 기존 데이터의 표준 필드 및 Type_1 JSON 업데이트
 *
 * 사용법: ?action=check (확인만) 또는 ?action=migrate (실제 업데이트)
 */

include "../../db.php";
include "../../includes/DataAdapter.php";

header('Content-Type: text/html; charset=utf-8');

$action = $_GET['action'] ?? 'check';
$limit = intval($_GET['limit'] ?? 100);

echo "<h2>스티커 주문 데이터 마이그레이션</h2>";
echo "<p>Action: <strong>{$action}</strong> | Limit: {$limit}</p>";

if ($action === 'check') {
    echo "<p><a href='?action=migrate&limit={$limit}' style='color:red;font-weight:bold;'>⚠️ 실제 마이그레이션 실행하기</a></p>";
}

// 표준 필드 컬럼 확인 및 생성
echo "<h3>1. 표준 필드 컬럼 확인</h3>";
$columns_to_add = [
    'spec_type' => 'VARCHAR(100)',
    'spec_material' => 'VARCHAR(200)',
    'spec_size' => 'VARCHAR(100)',
    'spec_sides' => 'VARCHAR(100)',
    'spec_design' => 'VARCHAR(100)',
    'quantity_value' => 'INT(11) DEFAULT 0',
    'quantity_unit' => 'VARCHAR(20)',
    'quantity_sheets' => 'INT(11) DEFAULT 0',
    'quantity_display' => 'VARCHAR(100)',
    'price_supply' => 'INT(11) DEFAULT 0',
    'price_vat' => 'INT(11) DEFAULT 0',
    'price_vat_amount' => 'INT(11) DEFAULT 0',
    'data_version' => 'INT(11) DEFAULT 1'
];

foreach ($columns_to_add as $col => $def) {
    $result = mysqli_query($db, "SHOW COLUMNS FROM mlangorder_printauto LIKE '{$col}'");
    if (mysqli_num_rows($result) == 0) {
        echo "<p>🔧 컬럼 추가: {$col}... ";
        if ($action === 'migrate') {
            $alter = mysqli_query($db, "ALTER TABLE mlangorder_printauto ADD COLUMN {$col} {$def}");
            echo $alter ? "✅ 성공" : "❌ 실패: " . mysqli_error($db);
        } else {
            echo "(check 모드 - 실제 실행 안 함)";
        }
        echo "</p>";
    } else {
        echo "<p>✅ {$col} 이미 존재</p>";
    }
}

// 마이그레이션 대상 스티커 주문 조회
echo "<h3>2. 마이그레이션 대상 조회</h3>";
$query = "SELECT no, Type_1, money_4, money_5, data_version, spec_type, spec_material, spec_size, quantity_display
          FROM mlangorder_printauto
          WHERE product_type = 'sticker'
          AND (spec_type IS NULL OR spec_type = '' OR data_version IS NULL OR data_version < 2)
          ORDER BY no DESC
          LIMIT {$limit}";
$result = mysqli_query($db, $query);
$count = mysqli_num_rows($result);

echo "<p>마이그레이션 대상: <strong>{$count}건</strong></p>";

if ($count == 0) {
    echo "<p>✅ 마이그레이션 대상이 없습니다. 모든 데이터가 최신 상태입니다.</p>";
    exit;
}

// 마이그레이션 실행
echo "<h3>3. 마이그레이션 " . ($action === 'migrate' ? '실행' : '미리보기') . "</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>주문번호</th><th>기존 Type_1</th><th>추출 데이터</th><th>상태</th></tr>";

$success = 0;
$failed = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $type1_raw = $row['Type_1'];
    $type1_data = json_decode($type1_raw, true);

    echo "<tr><td>{$no}</td>";
    echo "<td><small>" . htmlspecialchars(substr($type1_raw, 0, 100)) . "...</small></td>";

    // Type_1 JSON에서 레거시 필드 추출 시도
    $legacy_data = [];

    if ($type1_data && is_array($type1_data)) {
        // 이미 표준 필드가 있으면 사용
        if (!empty($type1_data['spec_type'])) {
            $legacy_data = $type1_data;
        }
        // 레거시 필드가 있으면 변환
        elseif (!empty($type1_data['jong']) || !empty($type1_data['garo'])) {
            $legacy_data = [
                'jong' => $type1_data['jong'] ?? '',
                'garo' => $type1_data['garo'] ?? '',
                'sero' => $type1_data['sero'] ?? '',
                'domusong' => $type1_data['domusong'] ?? '',
                'mesu' => $type1_data['mesu'] ?? '',
                'ordertype' => $type1_data['ordertype'] ?? 'print',
                'price' => intval($row['money_4'] ?? 0),
                'price_vat' => intval($row['money_5'] ?? 0)
            ];
            $legacy_data = DataAdapter::legacyToStandard($legacy_data, 'sticker');
        }
    }

    // 레거시 필드를 찾지 못하면 기존 데이터에서 추출 시도
    if (empty($legacy_data) && !empty($type1_raw)) {
        // 텍스트 형식 파싱 시도
        $parts = preg_split('/[\|\/]/', strip_tags($type1_raw));
        $legacy_data = [
            'spec_type' => '사각',
            'spec_material' => trim($parts[0] ?? ''),
            'spec_size' => trim($parts[1] ?? ''),
            'spec_design' => '인쇄만',
            'quantity_value' => 1000,
            'quantity_unit' => '매',
            'quantity_display' => '1,000매',
            'data_version' => 2
        ];
    }

    echo "<td><small>" . htmlspecialchars(json_encode($legacy_data, JSON_UNESCAPED_UNICODE)) . "</small></td>";

    if ($action === 'migrate' && !empty($legacy_data)) {
        // 레거시 필드도 Type_1에 포함
        $new_type1 = array_merge($legacy_data, [
            'jong' => $type1_data['jong'] ?? $legacy_data['spec_material'] ?? '',
            'garo' => $type1_data['garo'] ?? '',
            'sero' => $type1_data['sero'] ?? '',
            'domusong' => $type1_data['domusong'] ?? $legacy_data['spec_type'] ?? '',
            'mesu' => $type1_data['mesu'] ?? $legacy_data['quantity_value'] ?? 0,
            'ordertype' => $type1_data['ordertype'] ?? 'print',
            'data_version' => 2
        ]);
        $new_type1_json = json_encode($new_type1, JSON_UNESCAPED_UNICODE);

        $update_query = "UPDATE mlangorder_printauto SET
            spec_type = ?,
            spec_material = ?,
            spec_size = ?,
            spec_sides = ?,
            spec_design = ?,
            quantity_value = ?,
            quantity_unit = ?,
            quantity_sheets = ?,
            quantity_display = ?,
            data_version = 2,
            Type_1 = ?
            WHERE no = ?";

        $stmt = $db->prepare($update_query);
        $stmt->bind_param('sssssdsissi',
            $legacy_data['spec_type'],
            $legacy_data['spec_material'],
            $legacy_data['spec_size'],
            $legacy_data['spec_sides'] ?? '',
            $legacy_data['spec_design'],
            $legacy_data['quantity_value'],
            $legacy_data['quantity_unit'],
            $legacy_data['quantity_sheets'] ?? $legacy_data['quantity_value'],
            $legacy_data['quantity_display'],
            $new_type1_json,
            $no
        );

        if ($stmt->execute()) {
            echo "<td style='color:green'>✅ 성공</td>";
            $success++;
        } else {
            echo "<td style='color:red'>❌ 실패: " . $stmt->error . "</td>";
            $failed++;
        }
    } else {
        echo "<td>(미리보기)</td>";
    }

    echo "</tr>";
}

echo "</table>";

echo "<h3>결과 요약</h3>";
echo "<p>성공: {$success}건 | 실패: {$failed}건</p>";

if ($action === 'check') {
    echo "<p><a href='?action=migrate&limit={$limit}' style='color:red;font-weight:bold;'>⚠️ 실제 마이그레이션 실행하기</a></p>";
}
?>
