<?php
// 구문 오류 테스트 파일
echo "<h2>🔍 구문 오류 테스트</h2>";

try {
    // generate_quote_pdf.php 파일 포함 테스트
    include_once 'generate_quote_pdf.php';
    echo "<p>✅ generate_quote_pdf.php 구문 오류 없음</p>";
} catch (ParseError $e) {
    echo "<p style='color: red;'>❌ generate_quote_pdf.php 구문 오류: " . $e->getMessage() . "</p>";
    echo "<p>오류 위치: 라인 " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: orange;'>⚠️ generate_quote_pdf.php 실행 오류: " . $e->getMessage() . "</p>";
}

try {
    // generate_quote_tcpdf.php 파일 포함 테스트
    include_once 'generate_quote_tcpdf.php';
    echo "<p>✅ generate_quote_tcpdf.php 구문 오류 없음</p>";
} catch (ParseError $e) {
    echo "<p style='color: red;'>❌ generate_quote_tcpdf.php 구문 오류: " . $e->getMessage() . "</p>";
    echo "<p>오류 위치: 라인 " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: orange;'>⚠️ generate_quote_tcpdf.php 실행 오류: " . $e->getMessage() . "</p>";
}

try {
    // company_info.php 파일 포함 테스트
    include_once '../includes/company_info.php';
    echo "<p>✅ company_info.php 구문 오류 없음</p>";
    
    // 함수 테스트
    echo "<h3>함수 테스트</h3>";
    echo "<p>회사명: " . COMPANY_NAME . "</p>";
    echo "<p>결제 정보 HTML:</p>";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo getPaymentInfoHTML('quote');
    echo "</div>";
    
} catch (ParseError $e) {
    echo "<p style='color: red;'>❌ company_info.php 구문 오류: " . $e->getMessage() . "</p>";
    echo "<p>오류 위치: 라인 " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: orange;'>⚠️ company_info.php 실행 오류: " . $e->getMessage() . "</p>";
}

echo "<h3>테스트 완료</h3>";
echo "<p><a href='cart.php'>🛒 장바구니로 돌아가기</a></p>";
echo "<p><a href='preview_payment_info.php'>💳 결제 정보 미리보기</a></p>";
?>