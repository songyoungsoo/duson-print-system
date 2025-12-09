<?php
/**
 * 인쇄 규격 API
 *
 * 사용법:
 * - GET /api/get_print_sizes.php - 전체 규격 목록
 * - GET /api/get_print_sizes.php?series=A - A 시리즈만
 * - GET /api/get_print_sizes.php?find=1&width=250&height=420 - 가장 가까운 규격 찾기
 *
 * @date 2025-12-03
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

$response = ['success' => false, 'data' => null, 'error' => null];

try {
    // 규격 찾기 모드
    if (isset($_GET['find']) && $_GET['find'] == '1') {
        $width = intval($_GET['width'] ?? 0);
        $height = intval($_GET['height'] ?? 0);

        if ($width <= 0 || $height <= 0) {
            throw new Exception('가로, 세로 값을 입력해주세요.');
        }

        // 정규화 (작은값=가로, 큰값=세로)
        $w = min($width, $height);
        $h = max($width, $height);

        // 활성 규격 조회
        $result = mysqli_query($db, "SELECT * FROM print_sizes WHERE is_active = 1");
        if (!$result) {
            throw new Exception('데이터 조회 실패: ' . mysqli_error($db));
        }

        $bestMatch = null;
        $minDiff = PHP_INT_MAX;

        while ($row = mysqli_fetch_assoc($result)) {
            $diffW = abs($row['width'] - $w);
            $diffH = abs($row['height'] - $h);
            $totalDiff = sqrt($diffW * $diffW + $diffH * $diffH);

            if ($totalDiff < $minDiff) {
                $minDiff = $totalDiff;
                $bestMatch = [
                    'id' => intval($row['id']),
                    'name' => $row['name'],
                    'width' => intval($row['width']),
                    'height' => intval($row['height']),
                    'jeolsu' => intval($row['jeolsu']),
                    'series' => $row['series'],
                    'sheets_per_yeon' => intval($row['sheets_per_yeon']),
                    'description' => $row['description'],
                    'diff_width' => $diffW,
                    'diff_height' => $diffH,
                    'total_diff' => round($totalDiff, 1)
                ];
            }
        }

        if (!$bestMatch) {
            throw new Exception('일치하는 규격을 찾을 수 없습니다.');
        }

        // 재단 가능 여부
        $canCut = ($w <= $bestMatch['width'] && $h <= $bestMatch['height']);

        // 메시지 생성
        $message = "{$bestMatch['name']} ({$bestMatch['width']}×{$bestMatch['height']}mm)";
        if ($canCut) {
            $message .= " - 재단 가능";
            if ($bestMatch['diff_width'] > 0 || $bestMatch['diff_height'] > 0) {
                $message .= " (여백: 가로 {$bestMatch['diff_width']}mm, 세로 {$bestMatch['diff_height']}mm)";
            }
        } else {
            $message .= " - ⚠️ 입력 크기가 규격보다 큼";
        }
        $message .= " | {$bestMatch['jeolsu']}절 = 1연당 " . number_format($bestMatch['sheets_per_yeon']) . "장";

        $response = [
            'success' => true,
            'data' => [
                'input' => ['width' => $w, 'height' => $h],
                'match' => $bestMatch,
                'can_cut' => $canCut,
                'message' => $message
            ]
        ];

    } else {
        // 규격 목록 조회
        $sql = "SELECT id, name, width, height, jeolsu, series, sheets_per_yeon, description
                FROM print_sizes
                WHERE is_active = 1
                ORDER BY series ASC, sort_order ASC, jeolsu ASC";

        // 시리즈 필터
        if (!empty($_GET['series'])) {
            $series = mysqli_real_escape_string($db, strtoupper($_GET['series']));
            $sql = "SELECT id, name, width, height, jeolsu, series, sheets_per_yeon, description
                    FROM print_sizes
                    WHERE is_active = 1 AND series = '$series'
                    ORDER BY sort_order ASC, jeolsu ASC";
        }

        $result = mysqli_query($db, $sql);
        if (!$result) {
            throw new Exception('데이터 조회 실패: ' . mysqli_error($db));
        }

        $sizes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sizes[] = [
                'id' => intval($row['id']),
                'name' => $row['name'],
                'width' => intval($row['width']),
                'height' => intval($row['height']),
                'jeolsu' => intval($row['jeolsu']),
                'series' => $row['series'],
                'sheets_per_yeon' => intval($row['sheets_per_yeon']),
                'description' => $row['description'],
                'label' => "{$row['name']} ({$row['width']}×{$row['height']}mm) - {$row['jeolsu']}절"
            ];
        }

        $response = [
            'success' => true,
            'data' => [
                'sizes' => $sizes,
                'total' => count($sizes),
                'sheets_per_yeon_base' => 500
            ]
        ];
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'data' => null,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
