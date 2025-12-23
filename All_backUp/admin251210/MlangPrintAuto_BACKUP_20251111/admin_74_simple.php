<?php
// CSP 헤더 설정 - JavaScript eval 허용
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-eval' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");

// 현재 환경 데이터베이스 연결
include "../../db.php";
include "../config.php";

// 변수 초기화 (현재 환경 방식)
$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';

// 주문정보 보기 모드
if($mode == "OrderView" && !empty($no)) {
    include "../title.php";

    // 주문 정보 조회 (현재 환경 방식)
    $query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
    if ($stmt = mysqli_prepare($db, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $no);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // 주문 상태 업데이트 (OrderStyle이 "2"일 경우)
        if ($row && $row['OrderStyle'] == "2") {
            $update_query = "UPDATE mlangorder_printauto SET OrderStyle = '3' WHERE no = ?";
            if ($update_stmt = mysqli_prepare($db, $update_query)) {
                mysqli_stmt_bind_param($update_stmt, "i", $no);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                echo "<script>opener.parent.location.reload();</script>";
            }
        }
    }
    ?>

    <style>
        body {
            font-family: 'Noto Sans KR', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 15px;
            min-height: 100vh;
            font-size: 14px;
        }

        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .admin-content {
            padding: 25px;
            background: #f8f9fa;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            border: 1px solid #e9ecef;
        }

        .form-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: right;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
    </style>

    <div class="admin-container">
        <div class="admin-header">
            <h1>📋 주문정보 보기</h1>
            <p>주문번호: <?= htmlspecialchars($no) ?></p>
        </div>

        <div class="admin-content">
            <?php if ($row): ?>
                <div class="info-card">
                    <h3>👤 주문자 정보</h3>
                    <div class="form-row">
                        <div class="form-label">주문번호:</div>
                        <div><?= htmlspecialchars($row['no'] ?? '') ?></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label">주문자명:</div>
                        <div><?= htmlspecialchars($row['name'] ?? '') ?></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label">주문일:</div>
                        <div><?= htmlspecialchars($row['date'] ?? '') ?></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label">품목:</div>
                        <div><?= htmlspecialchars($row['Type'] ?? '') ?></div>
                    </div>
                    <div class="form-row">
                        <div class="form-label">상태:</div>
                        <div>
                            <?php
                            $status_names = [
                                '1' => '견적접수',
                                '2' => '주문접수',
                                '3' => '접수완료',
                                '4' => '입금대기',
                                '5' => '시안제작중',
                                '6' => '시안',
                                '7' => '교정',
                                '8' => '작업완료',
                                '9' => '작업중',
                                '10' => '교정작업중'
                            ];
                            $order_style = $row['OrderStyle'] ?? '';
                            echo htmlspecialchars($status_names[$order_style] ?? $order_style);
                            ?>
                        </div>
                    </div>
                    <?php if (!empty($row['email'])): ?>
                    <div class="form-row">
                        <div class="form-label">이메일:</div>
                        <div><?= htmlspecialchars($row['email']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['phone'])): ?>
                    <div class="form-row">
                        <div class="form-label">연락처:</div>
                        <div><?= htmlspecialchars($row['phone']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h3>📎 첨부 파일</h3>
                    <?php if (!empty($row['ThingCate'])): ?>
                        <p>📄 <a href='download.php?downfile=<?= urlencode($row['ThingCate']) ?>' style="color: #007bff; text-decoration: none;">
                            <?= htmlspecialchars($row['ThingCate']) ?>
                        </a></p>
                    <?php else: ?>
                        <p style="color: #6c757d;">📂 업로드된 파일이 없습니다.</p>
                    <?php endif; ?>
                </div>

                <!-- 추가 옵션 표시 (admin_7.4 특징) -->
                <div class="info-card">
                    <h3>⚙️ 추가 옵션</h3>
                    <?php
                    // 추가 옵션 시스템이 있다면 표시
                    if (class_exists('AdditionalOptionsDisplay')) {
                        $optionsDisplay = new AdditionalOptionsDisplay();
                        $optionData = [
                            'coating_enabled' => !empty($row['coating_type']),
                            'coating_type' => $row['coating_type'] ?? '',
                            'coating_price' => $row['coating_price'] ?? 0,
                            'folding_enabled' => !empty($row['folding_type']),
                            'folding_type' => $row['folding_type'] ?? '',
                            'folding_price' => $row['folding_price'] ?? 0,
                            'creasing_enabled' => !empty($row['creasing_lines']),
                            'creasing_lines' => $row['creasing_lines'] ?? '',
                            'creasing_price' => $row['creasing_price'] ?? 0
                        ];

                        $summary = $optionsDisplay->getCartSummary($optionData);
                        if ($summary === '옵션 없음') {
                            echo "<p style='color: #6c757d;'>옵션이 선택되지 않았습니다.</p>";
                        } else {
                            echo "<p style='color: #28a745; font-weight: bold;'>$summary</p>";
                        }
                    } else {
                        echo "<p style='color: #6c757d;'>추가 옵션 시스템이 연결되지 않았습니다.</p>";
                    }
                    ?>
                </div>

            <?php else: ?>
                <div class="info-card">
                    <p style="color: #dc3545; text-align: center;">❌ 주문 정보를 찾을 수 없습니다.</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 30px;">
                <button onclick="window.close()" class="btn btn-secondary">창닫기</button>
                <?php if ($row): ?>
                    <a href="admin.php?mode=SinForm&no=<?= $no ?>" target="_blank" class="btn btn-primary">교정/시안 등록</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
}

// 기본 응답 (모드가 없거나 다른 경우)
else {
    echo "<h1>Admin Panel Test</h1>";
    echo "<p>모드: " . htmlspecialchars($mode) . "</p>";
    echo "<p>번호: " . htmlspecialchars($no) . "</p>";

    if (empty($mode)) {
        echo "<p>사용 가능한 테스트:</p>";
        echo "<ul>";
        echo "<li><a href='?mode=OrderView&no=1'>주문정보 보기 (no=1)</a></li>";
        echo "</ul>";
    }
}

// 데이터베이스 연결 종료
if (isset($db)) {
    mysqli_close($db);
}
?>