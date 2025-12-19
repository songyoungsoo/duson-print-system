<?php
/**
 * MlangPrintAuto 업로드 테스트 페이지
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MlangPrintAuto 업로드 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .product { margin: 20px 0; padding: 20px; border: 2px solid #ddd; border-radius: 8px; background: #fafafa; }
        .product h3 { margin-top: 0; color: #007bff; }
        .buttons { margin: 15px 0; }
        button { padding: 12px 24px; margin: 5px; cursor: pointer; border: none; border-radius: 5px; font-size: 14px; font-weight: bold; }
        .open-btn { background: #28a745; color: white; }
        .open-btn:hover { background: #218838; }
        .code-btn { background: #17a2b8; color: white; }
        .code-btn:hover { background: #138496; }
        .admin-btn { background: #dc3545; color: white; }
        .admin-btn:hover { background: #c82333; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; display: none; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .checklist { background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .checklist ol { margin: 10px 0; }
        .checklist li { margin: 8px 0; }
        .summary { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #ffc107; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 MlangPrintAuto 파일 업로드 테스트</h1>

        <div class="summary">
            <h2 style="margin-top:0;">📊 로컬 코드 검증 완료</h2>
            <p><strong>✅ 전체 9개 품목</strong>의 FormData 패턴이 올바르게 수정되었습니다.</p>
            <p><strong>패턴:</strong> <code>formData.append('uploaded_files[]', fileObj.file)</code></p>
            <p><strong>⚠️ 중요:</strong> leaflet 디렉토리는 사용하지 않습니다. inserted만 사용!</p>
        </div>

        <h2>🎯 테스트 대상 품목</h2>

        <?php
        $products = [
            ['id' => 'inserted', 'name' => '전단지', 'hasUpload' => true],
            ['id' => 'sticker_new', 'name' => '스티커', 'hasUpload' => true],
            ['id' => 'envelope', 'name' => '봉투', 'hasUpload' => true],
            ['id' => 'littleprint', 'name' => '소량인쇄물', 'hasUpload' => true],
            ['id' => 'cadarok', 'name' => '카다록', 'hasUpload' => true],
            ['id' => 'merchandisebond', 'name' => '상품권', 'hasUpload' => false],
            ['id' => 'namecard', 'name' => '명함', 'hasUpload' => true],
            ['id' => 'msticker', 'name' => '자석스티커', 'hasUpload' => true],
            ['id' => 'ncrflambeau', 'name' => '양식지', 'hasUpload' => true]
        ];

        foreach ($products as $index => $product):
            $productId = $product['id'];
            $productName = $product['name'];
            $hasUpload = $product['hasUpload'];
        ?>

        <div class="product">
            <h3><?php echo ($index + 1); ?>. <?php echo $productName; ?> (<?php echo $productId; ?>)</h3>

            <?php if (!$hasUpload): ?>
                <div class="status warning" style="display:block;">
                    ⏭️ 파일 업로드 기능 없음 - 테스트 스킵
                </div>
            <?php else: ?>
                <div class="buttons">
                    <button class="open-btn" onclick="window.open('/mlangprintauto/<?php echo $productId; ?>/index.php', '_blank')">
                        🔗 페이지 열기
                    </button>
                    <button class="code-btn" onclick="checkCode('<?php echo $productId; ?>')">
                        🔍 코드 확인
                    </button>
                </div>
                <div id="status-<?php echo $productId; ?>" class="status"></div>
            <?php endif; ?>
        </div>

        <?php endforeach; ?>

        <div class="checklist">
            <h2>📝 수동 테스트 체크리스트</h2>
            <ol>
                <li><strong>페이지 열기:</strong> 각 품목의 "페이지 열기" 버튼 클릭</li>
                <li><strong>모달 열기:</strong> "파일 업로드 및 주문하기" 버튼 클릭</li>
                <li><strong>파일 선택:</strong> 이미지 파일 드래그 또는 클릭하여 선택</li>
                <li><strong>목록 확인:</strong> 업로드된 파일이 목록에 표시되는지 확인</li>
                <li><strong>옵션 선택:</strong> 수량, 사이즈 등 필수 옵션 입력</li>
                <li><strong>장바구니:</strong> "장바구니에 저장" 버튼 클릭</li>
                <li><strong>응답 확인:</strong> 성공 메시지 또는 오류 메시지 확인</li>
                <li><strong>관리자 확인:</strong> 관리자 페이지에서 파일 확인</li>
            </ol>
        </div>

        <div class="checklist" style="background: #ffe7e7;">
            <h2>🔍 확인할 사항</h2>
            <ul>
                <li><strong>폴더 생성:</strong> <code>/www/ImgFolder/_MlangPrintAuto_{품목}_index.php/2025/MMDD/IP/timestamp/</code></li>
                <li><strong>파일 존재:</strong> 위 폴더 안에 실제 업로드된 이미지 파일이 있는지</li>
                <li><strong>DB 저장:</strong> <code>shop_temp</code> 테이블에 ImgFolder, ThingCate, uploaded_files 컬럼 확인</li>
                <li><strong>다운로드:</strong> 관리자 페이지에서 파일 다운로드가 정상 작동하는지</li>
            </ul>
        </div>

        <div class="product" style="background: #fff3e0; border-color: #ff9800;">
            <h3>🔧 관리자 페이지</h3>
            <div class="buttons">
                <button class="admin-btn" onclick="window.open('/admin/mlangprintauto/admin.php', '_blank')">
                    🔗 관리자 페이지 열기
                </button>
            </div>
            <p>업로드된 주문의 파일을 확인하고 다운로드 테스트를 수행하세요.</p>
        </div>
    </div>

    <script>
        async function checkCode(productId) {
            const statusDiv = document.getElementById('status-' + productId);
            statusDiv.style.display = 'block';
            statusDiv.className = 'status info';
            statusDiv.innerHTML = '🔍 코드 검사 중...';

            try {
                const response = await fetch('/mlangprintauto/' + productId + '/index.php');
                const html = await response.text();

                // FormData 패턴 검사
                const hasCorrect = html.includes('uploaded_files[]');
                const hasWrongIndex = /uploaded_files\[\d+\]/.test(html);
                const hasWrongTemplate = /uploaded_files\[\$\{index\}\]/.test(html);
                const hasWrongConcat = /uploaded_files\["\s*\+/.test(html);

                if (hasCorrect && !hasWrongIndex && !hasWrongTemplate && !hasWrongConcat) {
                    statusDiv.className = 'status success';
                    statusDiv.innerHTML = '✅ <strong>코드 정상:</strong> uploaded_files[] 패턴 사용 중';
                } else if (hasWrongIndex || hasWrongTemplate || hasWrongConcat) {
                    statusDiv.className = 'status error';
                    let wrongPatterns = [];
                    if (hasWrongIndex) wrongPatterns.push('uploaded_files[0]');
                    if (hasWrongTemplate) wrongPatterns.push('uploaded_files[${index}]');
                    if (hasWrongConcat) wrongPatterns.push('uploaded_files[" + index + "]');

                    statusDiv.innerHTML = '❌ <strong>잘못된 패턴 발견:</strong> ' + wrongPatterns.join(', ') + '<br>📝 수정이 필요합니다!';
                } else {
                    statusDiv.className = 'status warning';
                    statusDiv.innerHTML = '⚠️ uploaded_files 패턴을 찾을 수 없습니다';
                }
            } catch (error) {
                statusDiv.className = 'status error';
                statusDiv.innerHTML = '❌ <strong>오류:</strong> ' + error.message;
            }
        }

        // 페이지 로드 시 안내
        console.log('🧪 MlangPrintAuto 테스트 페이지 로드 완료');
        console.log('각 품목의 "페이지 열기" 버튼을 클릭하여 실제 업로드를 테스트하세요.');
    </script>
</body>
</html>
