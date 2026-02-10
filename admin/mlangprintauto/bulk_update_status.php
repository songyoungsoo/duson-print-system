<?php
/**
 * 일괄 진행상태 변경 도구
 * 날짜 범위의 주문을 특정 상태로 일괄 변경
 */

// 관리자 인증 체크
session_start();
include "../../includes/db_constants.php";
include "../../db.php";

// 간단한 인증 (admin 세션 체크)
if (!isset($_SESSION['admin_logged_in'])) {
    // 임시 비밀번호 체크 (실제 사용 시 제거하거나 강화 필요)
    if ($_POST['admin_password'] ?? '' !== 'duson2026!') {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>일괄 상태 변경 - 관리자 인증</title>
            <style>
                body { font-family: 'Noto Sans KR', sans-serif; background: #f5f5f5; padding: 50px; }
                .auth-box { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h2 { color: #333; margin-bottom: 20px; }
                input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; }
                button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                button:hover { background: #2980b9; }
            </style>
        </head>
        <body>
            <div class="auth-box">
                <h2>🔐 관리자 인증</h2>
                <form method="post">
                    <input type="password" name="admin_password" placeholder="관리자 비밀번호" required autofocus>
                    <button type="submit">로그인</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

$action = $_POST['action'] ?? 'preview';
$target_date = $_POST['target_date'] ?? '2026-01-31';
$target_status = $_POST['target_status'] ?? '8'; // 8 = 작업완료

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>일괄 진행상태 변경 도구</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning strong { color: #856404; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #555; }
        input[type="date"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; margin-right: 10px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        .result-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin-top: 20px; }
        .result-box h3 { margin-top: 0; color: #2c3e50; }
        .stat { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e9ecef; }
        .stat:last-child { border-bottom: none; }
        .stat-label { font-weight: 600; color: #555; }
        .stat-value { color: #2c3e50; font-size: 18px; font-weight: 700; }
        .progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 4px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #3498db, #2ecc71); transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .success-message { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .success-message strong { color: #155724; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
        table th { background: #f8f9fa; font-weight: 600; color: #555; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 일괄 진행상태 변경 도구</h1>
        <p style="color: #777; margin-bottom: 30px;">특정 날짜 이전의 주문을 일괄적으로 원하는 상태로 변경합니다.</p>

        <?php if ($action === 'preview'): ?>
            <!-- 미리보기 모드 -->
            <div class="warning">
                <strong>⚠️ 주의사항</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>이 작업은 <strong>되돌릴 수 없습니다</strong>.</li>
                    <li>변경 전 반드시 데이터베이스 백업을 권장합니다.</li>
                    <li>먼저 "미리보기"로 영향받을 주문을 확인하세요.</li>
                </ul>
            </div>

            <form method="post">
                <input type="hidden" name="admin_password" value="duson2026!">
                
                <div class="form-group">
                    <label for="target_date">📅 기준 날짜 (이 날짜 이전의 주문 변경)</label>
                    <input type="date" id="target_date" name="target_date" value="<?php echo htmlspecialchars($target_date); ?>" required>
                </div>

                <div class="form-group">
                    <label for="target_status">🎯 변경할 진행상태</label>
                    <select id="target_status" name="target_status" required>
                        <option value="1" <?php echo $target_status == '1' ? 'selected' : ''; ?>>견적접수</option>
                        <option value="2" <?php echo $target_status == '2' ? 'selected' : ''; ?>>주문접수</option>
                        <option value="3" <?php echo $target_status == '3' ? 'selected' : ''; ?>>접수완료</option>
                        <option value="4" <?php echo $target_status == '4' ? 'selected' : ''; ?>>입금대기</option>
                        <option value="5" <?php echo $target_status == '5' ? 'selected' : ''; ?>>시안제작중</option>
                        <option value="6" <?php echo $target_status == '6' ? 'selected' : ''; ?>>시안</option>
                        <option value="7" <?php echo $target_status == '7' ? 'selected' : ''; ?>>교정</option>
                        <option value="8" <?php echo $target_status == '8' ? 'selected' : ''; ?>>작업완료</option>
                        <option value="9" <?php echo $target_status == '9' ? 'selected' : ''; ?>>작업중</option>
                        <option value="10" <?php echo $target_status == '10' ? 'selected' : ''; ?>>교정작업중</option>
                        <option value="11" <?php echo $target_status == '11' ? 'selected' : ''; ?>>카드결제</option>
                    </select>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" name="action" value="preview" class="btn btn-primary">🔍 미리보기</button>
                    <button type="button" onclick="window.close()" class="btn btn-secondary">❌ 취소</button>
                </div>
            </form>

            <?php
            // 미리보기 쿼리 실행
            $preview_date = $target_date . ' 23:59:59';
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM mlangorder_printauto WHERE date <= ?");
            $stmt->bind_param("s", $preview_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_count = $result->fetch_assoc()['total'];
            $stmt->close();

            // 상태별 집계
            $stmt = $db->prepare("SELECT OrderStyle, COUNT(*) as count FROM mlangorder_printauto WHERE date <= ? GROUP BY OrderStyle ORDER BY OrderStyle");
            $stmt->bind_param("s", $preview_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $status_counts = [];
            while ($row = $result->fetch_assoc()) {
                $status_counts[$row['OrderStyle']] = $row['count'];
            }
            $stmt->close();

            $orderStyleNames = [
                '1' => '견적접수', '2' => '주문접수', '3' => '접수완료', '4' => '입금대기',
                '5' => '시안제작중', '6' => '시안', '7' => '교정', '8' => '작업완료',
                '9' => '작업중', '10' => '교정작업중', '11' => '카드결제'
            ];
            $target_status_name = $orderStyleNames[$target_status] ?? $target_status;
            ?>

            <div class="result-box">
                <h3>📋 미리보기 결과</h3>
                <div class="stat">
                    <span class="stat-label">기준 날짜</span>
                    <span class="stat-value"><?php echo htmlspecialchars($target_date); ?> 23:59:59 이전</span>
                </div>
                <div class="stat">
                    <span class="stat-label">변경할 상태</span>
                    <span class="stat-value"><?php echo $target_status_name; ?></span>
                </div>
                <div class="stat">
                    <span class="stat-label">영향받을 주문 수</span>
                    <span class="stat-value" style="color: #e74c3c;"><?php echo number_format($total_count); ?>건</span>
                </div>

                <?php if ($total_count > 0): ?>
                    <h4 style="margin-top: 20px; color: #555;">현재 상태별 분포</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>현재 상태</th>
                                <th>주문 수</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($status_counts as $status => $count): ?>
                                <tr>
                                    <td><?php echo $orderStyleNames[$status] ?? "알 수 없음 ($status)"; ?></td>
                                    <td><strong><?php echo number_format($count); ?>건</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <form method="post" onsubmit="return confirm('⚠️ 정말로 <?php echo number_format($total_count); ?>건의 주문을 「<?php echo $target_status_name; ?>」로 변경하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다!');">
                        <input type="hidden" name="admin_password" value="duson2026!">
                        <input type="hidden" name="target_date" value="<?php echo htmlspecialchars($target_date); ?>">
                        <input type="hidden" name="target_status" value="<?php echo htmlspecialchars($target_status); ?>">
                        <div style="margin-top: 30px;">
                            <button type="submit" name="action" value="execute" class="btn btn-danger">🚀 실행하기 (<?php echo number_format($total_count); ?>건 변경)</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p style="color: #777; margin-top: 20px;">해당 날짜 이전의 주문이 없습니다.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'execute'): ?>
            <!-- 실행 모드 -->
            <?php
            $execute_date = $target_date . ' 23:59:59';
            
            // 트랜잭션 시작
            $db->begin_transaction();
            
            try {
                // UPDATE 실행
                $stmt = $db->prepare("UPDATE mlangorder_printauto SET OrderStyle = ? WHERE date <= ?");
                $stmt->bind_param("ss", $target_status, $execute_date);
                $stmt->execute();
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                
                // 커밋
                $db->commit();
                
                $orderStyleNames = [
                    '1' => '견적접수', '2' => '주문접수', '3' => '접수완료', '4' => '입금대기',
                    '5' => '시안제작중', '6' => '시안', '7' => '교정', '8' => '작업완료',
                    '9' => '작업중', '10' => '교정작업중', '11' => '카드결제'
                ];
                $target_status_name = $orderStyleNames[$target_status] ?? $target_status;
                ?>
                
                <div class="success-message">
                    <strong>✅ 일괄 변경 완료!</strong>
                </div>

                <div class="result-box">
                    <h3>📊 실행 결과</h3>
                    <div class="stat">
                        <span class="stat-label">기준 날짜</span>
                        <span class="stat-value"><?php echo htmlspecialchars($target_date); ?> 23:59:59 이전</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">변경된 상태</span>
                        <span class="stat-value"><?php echo $target_status_name; ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">변경된 주문 수</span>
                        <span class="stat-value" style="color: #27ae60;"><?php echo number_format($affected_rows); ?>건</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">실행 시각</span>
                        <span class="stat-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button onclick="window.location.href='orderlist.php'" class="btn btn-primary">📋 주문 목록으로</button>
                    <button onclick="window.location.reload()" class="btn btn-secondary">🔄 다시 실행</button>
                </div>

                <?php
            } catch (Exception $e) {
                // 롤백
                $db->rollback();
                ?>
                <div class="warning">
                    <strong>❌ 오류 발생</strong>
                    <p style="margin: 10px 0 0 0;">일괄 변경 중 오류가 발생했습니다: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                </div>
                <div style="margin-top: 20px;">
                    <button onclick="history.back()" class="btn btn-secondary">← 돌아가기</button>
                </div>
                <?php
            }
            ?>

        <?php endif; ?>
    </div>
</body>
</html>
