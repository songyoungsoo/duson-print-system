<?php
/**
 * 견적서 통합 계산기 v2.0
 *
 * 11개 품목을 단일 페이지에서 처리하는 통합 계산기
 * iframe/postMessage 대신 같은 페이지에서 동작
 *
 * @author Claude Code
 * @version 2.0
 * @date 2026-01-06
 */

session_start();
require_once __DIR__ . '/includes/CalculatorConfig.php';
require_once __DIR__ . '/../../db.php';

$products = CalculatorConfig::getAllProducts();

// URL 파라미터로 전달된 제품 (견적서에서 선택한 제품)
$selectedProduct = $_GET['product'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 계산기</title>
    <link rel="stylesheet" href="calculator_v2.css">
</head>
<body>
    <div class="calculator-container">
        <!-- Header: 제품 선택 -->
        <div class="calc-header">
            <h2>품목 선택</h2>
            <select id="productSelector" class="form-select">
                <option value="">제품을 선택하세요...</option>
                <?php foreach ($products as $code => $name): ?>
                    <option value="<?= htmlspecialchars($code) ?>"
                            <?= ($code === $selectedProduct) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Main: 동적 폼 영역 (JavaScript로 채워짐) -->
        <div id="calculatorForm" class="calc-form">
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p>위에서 제품을 선택하면 계산기가 표시됩니다</p>
            </div>
        </div>

        <!-- Footer: 가격 표시 및 버튼 -->
        <div class="calc-footer" id="calcFooter" style="display: none;">
            <div class="price-summary">
                <div class="price-row">
                    <span class="price-label">공급가:</span>
                    <span class="price-value" id="supplyPrice">0원</span>
                </div>
                <div class="price-row total">
                    <span class="price-label">합계 (VAT 포함):</span>
                    <span class="price-value" id="totalPrice">0원</span>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" id="btnReset">
                    초기화
                </button>
                <button type="button" class="btn btn-primary" id="btnAddToQuote" disabled>
                    견적서에 추가
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden data for parent window -->
    <input type="hidden" id="quotationData" value="">

    <script>
        // 전역 변수: 부모 창과 통신 여부
        window.isInModal = (window.parent !== window);
    </script>
    <script src="calculator_v2.js"></script>
</body>
</html>
