<?php
/**
 * 두손기획 인쇄몰 설치 마법사
 * Duson Print Mall Installation Wizard
 *
 * Version: 1.0.0
 * Created: 2025-12-07
 */

session_start();

// 이미 설치 완료 체크
if (file_exists('../config.installed.php')) {
    $already_installed = true;
}

// 현재 단계
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$total_steps = 5;

// 단계별 제목
$step_titles = [
    1 => '시스템 요구사항 확인',
    2 => '데이터베이스 설정',
    3 => '관리자 계정 생성',
    4 => '사이트 설정',
    5 => '설치 완료'
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획 인쇄몰 설치 - <?php echo $step_titles[$step]; ?></title>
    <style>
        /* ========================================
         * 두손기획 인쇄몰 설치 마법사
         * Excel-Style Design with Pastel Colors
         * Version: 2.0.0 (Excel Theme)
         * ======================================== */

        :root {
            /* Excel 스타일 파스텔 색상 팔레트 */
            --excel-header-bg: #E8F4E8;
            --excel-header-text: #4A6741;
            --excel-row-odd: #FFF9E6;
            --excel-row-even: #FFFFFF;
            --excel-selected: #D4E8FC;
            --excel-border: #C8D6C8;
            --excel-border-dark: #B8C8B8;

            /* 상태 색상 (파스텔) */
            --pastel-green: #E8F5E9;
            --pastel-green-text: #2E7D32;
            --pastel-pink: #FFEBEE;
            --pastel-pink-text: #C62828;
            --pastel-yellow: #FFF8E1;
            --pastel-yellow-text: #F57F17;
            --pastel-blue: #E3F2FD;
            --pastel-blue-text: #1565C0;

            /* 버튼 색상 */
            --btn-primary: #81C784;
            --btn-primary-hover: #66BB6A;
            --btn-secondary: #B0BEC5;
            --btn-secondary-hover: #90A4AE;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Malgun Gothic', 'Segoe UI', Tahoma, sans-serif;
            background: #F5F5F5;
            min-height: 100vh;
            padding: 30px 20px;
        }

        /* 메인 컨테이너 - 엑셀 시트 스타일 */
        .installer-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border: 1px solid var(--excel-border);
            border-radius: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* 헤더 - 엑셀 시트 제목 스타일 */
        .installer-header {
            background: var(--excel-header-bg);
            color: var(--excel-header-text);
            padding: 20px 30px;
            text-align: left;
            border-bottom: 2px solid var(--excel-border-dark);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .installer-header::before {
            content: "📊";
            font-size: 32px;
        }

        .installer-header h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .installer-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        /* 진행바 - 엑셀 탭 스타일 */
        .progress-bar {
            display: flex;
            background: #F8F8F8;
            border-bottom: 1px solid var(--excel-border);
            padding: 0;
        }

        .progress-step {
            flex: 1;
            padding: 12px 8px;
            text-align: center;
            font-size: 11px;
            color: #888;
            position: relative;
            background: #F0F0F0;
            border-right: 1px solid var(--excel-border);
            cursor: default;
            transition: all 0.2s;
        }

        .progress-step:last-child {
            border-right: none;
        }

        .progress-step .step-num {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            font-size: 13px;
            font-weight: bold;
            background: #DDD;
            color: #666;
            border-radius: 0;
            margin-bottom: 4px;
        }

        .progress-step.active {
            background: var(--excel-selected);
            color: var(--pastel-blue-text);
            border-bottom: 3px solid var(--pastel-blue-text);
        }

        .progress-step.active .step-num {
            background: var(--pastel-blue-text);
            color: white;
        }

        .progress-step.completed {
            background: var(--pastel-green);
            color: var(--pastel-green-text);
        }

        .progress-step.completed .step-num {
            background: var(--pastel-green-text);
            color: white;
        }

        /* 콘텐츠 영역 */
        .installer-content {
            padding: 30px;
            background: white;
        }

        /* 단계 제목 - 엑셀 셀 헤더 스타일 */
        .step-title {
            font-size: 18px;
            color: var(--excel-header-text);
            margin-bottom: 25px;
            padding: 12px 15px;
            background: var(--excel-header-bg);
            border-left: 4px solid var(--pastel-green-text);
            font-weight: 600;
        }

        /* 폼 그룹 - 엑셀 행 스타일 */
        .form-group {
            margin-bottom: 0;
            display: grid;
            grid-template-columns: 180px 1fr;
            border: 1px solid var(--excel-border);
            border-top: none;
        }

        .form-group:first-of-type {
            border-top: 1px solid var(--excel-border);
        }

        .form-group label {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            font-weight: 500;
            color: var(--excel-header-text);
            background: var(--excel-header-bg);
            border-right: 1px solid var(--excel-border);
            font-size: 13px;
            margin: 0;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 0;
            font-size: 14px;
            background: white;
            transition: background 0.2s;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            background: var(--excel-selected);
        }

        .form-group small {
            grid-column: 2;
            padding: 6px 15px 10px;
            color: #888;
            font-size: 11px;
            background: var(--excel-row-odd);
            border-top: 1px dashed var(--excel-border);
        }

        /* 2열 레이아웃 */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .form-row .form-group {
            grid-template-columns: 120px 1fr;
        }

        .form-row .form-group:first-child {
            border-right: none;
        }

        /* 버튼 - 엑셀 버튼 스타일 */
        .btn {
            padding: 10px 24px;
            border: 1px solid var(--excel-border);
            border-radius: 0;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }

        .btn-primary {
            background: var(--btn-primary);
            color: white;
            border-color: var(--btn-primary);
        }

        .btn-primary:hover {
            background: var(--btn-primary-hover);
            border-color: var(--btn-primary-hover);
        }

        .btn-success {
            background: var(--pastel-green-text);
            color: white;
            border-color: var(--pastel-green-text);
        }

        .btn-success:hover {
            background: #388E3C;
            border-color: #388E3C;
        }

        .btn-secondary {
            background: var(--btn-secondary);
            color: #333;
            border-color: var(--btn-secondary);
        }

        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
            border-color: var(--btn-secondary-hover);
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--excel-border);
        }

        /* 체크 아이템 - 엑셀 표 행 스타일 */
        .check-item {
            display: grid;
            grid-template-columns: 50px 1fr auto;
            align-items: center;
            padding: 0;
            margin-bottom: 0;
            background: white;
            border: 1px solid var(--excel-border);
            border-top: none;
            border-radius: 0;
        }

        .check-item:first-child {
            border-top: 1px solid var(--excel-border);
        }

        .check-item:nth-child(odd) {
            background: var(--excel-row-odd);
        }

        .check-item .status {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0;
            font-size: 14px;
            border-right: 1px solid var(--excel-border);
            padding: 12px;
            border-radius: 0;
        }

        .check-item .status.pass {
            background: var(--pastel-green);
            color: var(--pastel-green-text);
        }

        .check-item .status.fail {
            background: var(--pastel-pink);
            color: var(--pastel-pink-text);
        }

        .check-item .status.warn {
            background: var(--pastel-yellow);
            color: var(--pastel-yellow-text);
        }

        .check-item .info {
            padding: 12px 15px;
        }

        .check-item .info strong {
            display: block;
            margin-bottom: 2px;
            font-size: 13px;
            color: #333;
        }

        .check-item .info small {
            color: #888;
            font-size: 11px;
        }

        /* 알림 메시지 - 엑셀 조건부 서식 스타일 */
        .alert {
            padding: 12px 15px;
            border-radius: 0;
            margin-bottom: 15px;
            border-left: 4px solid;
            font-size: 13px;
        }

        .alert-danger {
            background: var(--pastel-pink);
            border-color: var(--pastel-pink-text);
            color: var(--pastel-pink-text);
        }

        .alert-success {
            background: var(--pastel-green);
            border-color: var(--pastel-green-text);
            color: var(--pastel-green-text);
        }

        .alert-warning {
            background: var(--pastel-yellow);
            border-color: var(--pastel-yellow-text);
            color: var(--pastel-yellow-text);
        }

        /* 이미 설치됨 메시지 */
        .already-installed {
            text-align: center;
            padding: 50px 40px;
            background: var(--excel-row-odd);
        }

        .already-installed h2 {
            color: var(--pastel-green-text);
            margin-bottom: 15px;
            font-size: 20px;
        }

        .already-installed p {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        /* 추가 유틸리티 클래스 */
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .excel-table th {
            background: var(--excel-header-bg);
            color: var(--excel-header-text);
            padding: 10px 15px;
            text-align: left;
            font-weight: 600;
            border: 1px solid var(--excel-border);
            font-size: 13px;
        }

        .excel-table td {
            padding: 10px 15px;
            border: 1px solid var(--excel-border);
            font-size: 13px;
        }

        .excel-table tr:nth-child(odd) td {
            background: var(--excel-row-odd);
        }

        .excel-table tr:nth-child(even) td {
            background: var(--excel-row-even);
        }

        /* 반응형 */
        @media (max-width: 768px) {
            .form-group {
                grid-template-columns: 1fr;
            }

            .form-group label {
                border-right: none;
                border-bottom: 1px solid var(--excel-border);
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .progress-step {
                font-size: 10px;
                padding: 10px 5px;
            }

            .progress-step .step-num {
                width: 20px;
                height: 20px;
                line-height: 20px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <div>
                <h1>두손기획 인쇄몰</h1>
                <p>Installation Wizard v2.0.0 (Excel Style)</p>
            </div>
        </div>

        <?php if (isset($already_installed) && $step < 5): ?>
        <div class="installer-content">
            <div class="already-installed">
                <h2>이미 설치됨</h2>
                <p>이 시스템은 이미 설치되어 있습니다.<br>재설치하려면 config.installed.php 파일을 삭제하세요.</p>
                <a href="../" class="btn btn-primary">사이트로 이동</a>
                <a href="../admin/" class="btn btn-secondary">관리자로 이동</a>
            </div>
        </div>
        <?php else: ?>

        <div class="progress-bar">
            <?php for ($i = 1; $i <= $total_steps; $i++): ?>
            <div class="progress-step <?php echo $i < $step ? 'completed' : ($i == $step ? 'active' : ''); ?>">
                <span class="step-num"><?php echo $i; ?></span>
                <?php echo $step_titles[$i]; ?>
            </div>
            <?php endfor; ?>
        </div>

        <div class="installer-content">
            <?php
            // 단계별 파일 포함
            switch ($step) {
                case 1:
                    include 'step1_requirements.php';
                    break;
                case 2:
                    include 'step2_database.php';
                    break;
                case 3:
                    include 'step3_admin.php';
                    break;
                case 4:
                    include 'step4_config.php';
                    break;
                case 5:
                    include 'step5_complete.php';
                    break;
            }
            ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
