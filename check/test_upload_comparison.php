<?php
/**
 * 봉투 vs 전단지 파일 업로드 비교 테스트
 */

header('Content-Type: text/html; charset=utf-8');
require_once 'db.php';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>파일 업로드 비교 테스트</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .product { border: 2px solid #ddd; padding: 15px; border-radius: 5px; }
        .product.envelope { border-color: #4CAF50; }
        .product.inserted { border-color: #2196F3; }
        h2 { margin-top: 0; }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        .info { color: #2196F3; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .file-list { list-style: none; padding: 0; }
        .file-list li { padding: 5px; margin: 5px 0; background: #f9f9f9; border-left: 3px solid #2196F3; padding-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 봉투 vs 전단지 파일 업로드 비교 테스트</h1>
        
        <?php
        // 최근 주문 조회
        $envelope_query = "SELECT no, session_id, ImgFolder, uploaded_files, created_at 
                          FROM shop_temp 
                          WHERE product_type = 'envelope' 
                          ORDER BY no DESC 
                          LIMIT 1";
        
        $inserted_query = "SELECT no, session_id, ImgFolder, uploaded_files, created_at 
                          FROM shop_temp 
                          WHERE product_type IN ('leaflet', 'inserted')
                          ORDER BY no DESC 
                          LIMIT 1";
        
        $envelope_result = mysqli_query($db, $envelope_query);
        $inserted_result = mysqli_query($db, $inserted_query);
        
        $envelope_data = mysqli_fetch_assoc($envelope_result);
        $inserted_data = mysqli_fetch_assoc($inserted_result);
        ?>
        
        <div class="test-section">
            <h2>🔍 최근 주문 비교</h2>
            <div class="comparison">
                <!-- 봉투 -->
                <div class="product envelope">
                    <h3>📦 봉투 (Envelope)</h3>
                    <?php if ($envelope_data): ?>
                        <table>
                            <tr><th>주문 번호</th><td><?= $envelope_data['no'] ?></td></tr>
                            <tr><th>세션 ID</th><td><?= substr($envelope_data['session_id'], 0, 20) ?>...</td></tr>
                            <tr><th>ImgFolder</th><td class="<?= empty($envelope_data['ImgFolder']) ? 'error' : 'success' ?>"><?= $envelope_data['ImgFolder'] ?: '❌ 비어있음' ?></td></tr>
                            <tr><th>uploaded_files</th><td class="<?= empty($envelope_data['uploaded_files']) ? 'error' : 'success' ?>"><?= empty($envelope_data['uploaded_files']) ? '❌ 비어있음' : '✅ 있음' ?></td></tr>
                            <tr><th>생성일</th><td><?= $envelope_data['created_at'] ?></td></tr>
                        </table>
                        
                        <?php if (!empty($envelope_data['ImgFolder'])): 
                            $envelope_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $envelope_data['ImgFolder'];
                        ?>
                            <h4>📁 폴더 상태</h4>
                            <ul>
                                <li>경로: <code><?= $envelope_path ?></code></li>
                                <li>존재: <?= is_dir($envelope_path) ? '<span class="success">✅ Yes</span>' : '<span class="error">❌ No</span>' ?></li>
                                <?php if (is_dir($envelope_path)): 
                                    $files = array_diff(scandir($envelope_path), ['.', '..']);
                                ?>
                                    <li>파일 개수: <span class="<?= count($files) > 0 ? 'success' : 'warning' ?>"><?= count($files) ?>개</span></li>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if (is_dir($envelope_path) && count($files) > 0): ?>
                                <h4>📄 업로드된 파일</h4>
                                <ul class="file-list">
                                    <?php foreach ($files as $file): 
                                        $file_path = $envelope_path . '/' . $file;
                                        $file_size = filesize($file_path);
                                    ?>
                                        <li><?= $file ?> (<?= number_format($file_size) ?> bytes)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <?php if (!empty($envelope_data['uploaded_files'])): 
                                $files_json = json_decode($envelope_data['uploaded_files'], true);
                            ?>
                                <h4>💾 DB 저장된 파일 정보</h4>
                                <pre><?= json_encode($files_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="warning">⚠️ 최근 봉투 주문이 없습니다.</p>
                    <?php endif; ?>
                </div>
                
                <!-- 전단지 -->
                <div class="product inserted">
                    <h3>📄 전단지 (Inserted)</h3>
                    <?php if ($inserted_data): ?>
                        <table>
                            <tr><th>주문 번호</th><td><?= $inserted_data['no'] ?></td></tr>
                            <tr><th>세션 ID</th><td><?= substr($inserted_data['session_id'], 0, 20) ?>...</td></tr>
                            <tr><th>ImgFolder</th><td class="<?= empty($inserted_data['ImgFolder']) ? 'error' : 'success' ?>"><?= $inserted_data['ImgFolder'] ?: '❌ 비어있음' ?></td></tr>
                            <tr><th>uploaded_files</th><td class="<?= empty($inserted_data['uploaded_files']) ? 'error' : 'success' ?>"><?= empty($inserted_data['uploaded_files']) ? '❌ 비어있음' : '✅ 있음' ?></td></tr>
                            <tr><th>생성일</th><td><?= $inserted_data['created_at'] ?></td></tr>
                        </table>
                        
                        <?php if (!empty($inserted_data['ImgFolder'])): 
                            $inserted_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $inserted_data['ImgFolder'];
                        ?>
                            <h4>📁 폴더 상태</h4>
                            <ul>
                                <li>경로: <code><?= $inserted_path ?></code></li>
                                <li>존재: <?= is_dir($inserted_path) ? '<span class="success">✅ Yes</span>' : '<span class="error">❌ No</span>' ?></li>
                                <?php if (is_dir($inserted_path)): 
                                    $files = array_diff(scandir($inserted_path), ['.', '..']);
                                ?>
                                    <li>파일 개수: <span class="<?= count($files) > 0 ? 'success' : 'warning' ?>"><?= count($files) ?>개</span></li>
                                <?php endif; ?>
                            </ul>
                            
                            <?php if (is_dir($inserted_path) && count($files) > 0): ?>
                                <h4>📄 업로드된 파일</h4>
                                <ul class="file-list">
                                    <?php foreach ($files as $file): 
                                        $file_path = $inserted_path . '/' . $file;
                                        $file_size = filesize($file_path);
                                    ?>
                                        <li><?= $file ?> (<?= number_format($file_size) ?> bytes)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <?php if (!empty($inserted_data['uploaded_files'])): 
                                $files_json = json_decode($inserted_data['uploaded_files'], true);
                            ?>
                                <h4>💾 DB 저장된 파일 정보</h4>
                                <pre><?= json_encode($files_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="warning">⚠️ 최근 전단지 주문이 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🔬 차이점 분석</h2>
            <?php
            $differences = [];
            
            if ($envelope_data && $inserted_data) {
                // ImgFolder 비교
                $envelope_has_folder = !empty($envelope_data['ImgFolder']);
                $inserted_has_folder = !empty($inserted_data['ImgFolder']);
                
                if ($envelope_has_folder != $inserted_has_folder) {
                    $differences[] = [
                        'type' => 'ImgFolder',
                        'envelope' => $envelope_has_folder ? '✅ 있음' : '❌ 없음',
                        'inserted' => $inserted_has_folder ? '✅ 있음' : '❌ 없음'
                    ];
                }
                
                // uploaded_files 비교
                $envelope_has_files = !empty($envelope_data['uploaded_files']);
                $inserted_has_files = !empty($inserted_data['uploaded_files']);
                
                if ($envelope_has_files != $inserted_has_files) {
                    $differences[] = [
                        'type' => 'uploaded_files (DB)',
                        'envelope' => $envelope_has_files ? '✅ 있음' : '❌ 없음',
                        'inserted' => $inserted_has_files ? '✅ 있음' : '❌ 없음'
                    ];
                }
                
                // 실제 파일 존재 비교
                if ($envelope_has_folder && $inserted_has_folder) {
                    $envelope_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $envelope_data['ImgFolder'];
                    $inserted_path = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $inserted_data['ImgFolder'];
                    
                    $envelope_file_count = is_dir($envelope_path) ? count(array_diff(scandir($envelope_path), ['.', '..'])) : 0;
                    $inserted_file_count = is_dir($inserted_path) ? count(array_diff(scandir($inserted_path), ['.', '..'])) : 0;
                    
                    if ($envelope_file_count != $inserted_file_count) {
                        $differences[] = [
                            'type' => '실제 파일 개수',
                            'envelope' => $envelope_file_count . '개',
                            'inserted' => $inserted_file_count . '개'
                        ];
                    }
                }
            }
            
            if (count($differences) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th>봉투</th>
                            <th>전단지</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($differences as $diff): ?>
                            <tr>
                                <td><?= $diff['type'] ?></td>
                                <td><?= $diff['envelope'] ?></td>
                                <td><?= $diff['inserted'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="success">✅ 두 제품의 파일 업로드 방식이 동일합니다!</p>
            <?php endif; ?>
        </div>
        
        <div class="test-section">
            <h2>📝 테스트 가이드</h2>
            <ol>
                <li><strong>봉투 테스트</strong>: <a href="/mlangprintauto/envelope/" target="_blank">봉투 페이지</a>에서 파일 업로드 후 장바구니 추가</li>
                <li><strong>전단지 테스트</strong>: <a href="/mlangprintauto/inserted/" target="_blank">전단지 페이지</a>에서 파일 업로드 후 장바구니 추가</li>
                <li><strong>결과 확인</strong>: 이 페이지를 새로고침하여 비교</li>
            </ol>
            
            <h3>✅ 성공 조건</h3>
            <ul>
                <li>ImgFolder: 경로가 저장되어야 함</li>
                <li>uploaded_files (DB): JSON 데이터가 저장되어야 함</li>
                <li>실제 파일: 폴더에 파일이 존재해야 함</li>
            </ul>
        </div>
        
        <div class="test-section">
            <button onclick="location.reload()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">🔄 새로고침</button>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($db);
?>
