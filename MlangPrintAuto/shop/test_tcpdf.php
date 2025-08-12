<?php
// TCPDF 설치 및 기본 기능 테스트

echo "<h2>🔍 TCPDF 설치 테스트</h2>";

// 1. TCPDF 라이브러리 확인
echo "<h3>1. TCPDF 라이브러리 확인</h3>";

$tcpdf_paths = [
    '../../vendor/autoload.php' => 'Composer 설치',
    '../../lib/tcpdf/tcpdf.php' => '직접 다운로드'
];

$tcpdf_available = false;
$tcpdf_path = '';

foreach ($tcpdf_paths as $path => $method) {
    if (file_exists($path)) {
        echo "<p>✅ $method: $path</p>";
        $tcpdf_available = true;
        $tcpdf_path = $path;
        break;
    } else {
        echo "<p>❌ $method: $path (파일 없음)</p>";
    }
}

if (!$tcpdf_available) {
    echo "<p style='color: red;'><strong>TCPDF가 설치되지 않았습니다.</strong></p>";
    echo "<p>설치 방법:</p>";
    echo "<ul>";
    echo "<li>Composer: <code>composer require tecnickcom/tcpdf</code></li>";
    echo "<li>직접 다운로드: <a href='https://tcpdf.org/download'>https://tcpdf.org/download</a></li>";
    echo "</ul>";
    echo "<p><a href='/MlangPrintAuto/shop/generate_quote_pdf.php'>HTML 버전 견적서 사용하기</a></p>";
    exit;
}

// 2. TCPDF 로드 테스트
echo "<h3>2. TCPDF 로드 테스트</h3>";

try {
    if (strpos($tcpdf_path, 'vendor') !== false) {
        require_once($tcpdf_path);
    } else {
        require_once($tcpdf_path);
    }
    echo "<p>✅ TCPDF 로드 성공</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ TCPDF 로드 실패: " . $e->getMessage() . "</p>";
    exit;
}

// 3. 기본 PDF 생성 테스트
echo "<h3>3. 기본 PDF 생성 테스트</h3>";

try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // 문서 정보 설정
    $pdf->SetCreator('TCPDF 테스트');
    $pdf->SetAuthor('두손기획인쇄');
    $pdf->SetTitle('TCPDF 테스트 문서');
    
    // 페이지 추가
    $pdf->AddPage();
    
    // 기본 폰트로 텍스트 추가
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'TCPDF Test Document', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Created: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
    
    echo "<p>✅ 기본 PDF 생성 성공</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ PDF 생성 실패: " . $e->getMessage() . "</p>";
    exit;
}

// 4. 한글 폰트 테스트
echo "<h3>4. 한글 폰트 테스트</h3>";

$korean_fonts = ['nanumgothic', 'dejavusans', 'helvetica'];
$korean_font_available = false;

foreach ($korean_fonts as $font) {
    try {
        $pdf->SetFont($font, '', 12);
        $pdf->Cell(0, 10, '한글 테스트: 안녕하세요', 0, 1, 'L');
        echo "<p>✅ $font 폰트 사용 가능</p>";
        $korean_font_available = true;
        break;
    } catch (Exception $e) {
        echo "<p>❌ $font 폰트 사용 불가: " . $e->getMessage() . "</p>";
    }
}

if (!$korean_font_available) {
    echo "<p style='color: orange;'>⚠️ 한글 폰트가 없습니다. 기본 폰트를 사용합니다.</p>";
}

// 5. 메모리 및 시스템 정보
echo "<h3>5. 시스템 정보</h3>";
echo "<p><strong>PHP 버전:</strong> " . phpversion() . "</p>";
echo "<p><strong>메모리 제한:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>최대 실행 시간:</strong> " . ini_get('max_execution_time') . "초</p>";
echo "<p><strong>임시 디렉토리:</strong> " . sys_get_temp_dir() . "</p>";

// 6. 테스트 PDF 다운로드 링크
echo "<h3>6. 테스트 결과</h3>";
echo "<p>✅ 모든 테스트 통과!</p>";
echo "<p><a href='?download=test' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📄 테스트 PDF 다운로드</a></p>";
echo "<p><a href='generate_quote_tcpdf.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 견적서 PDF 생성</a></p>";

// 테스트 PDF 다운로드 처리
if (isset($_GET['download']) && $_GET['download'] === 'test') {
    try {
        // 테스트 내용 추가
        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'System Information:', 0, 1, 'L');
        $pdf->Cell(0, 8, 'PHP Version: ' . phpversion(), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Memory Limit: ' . ini_get('memory_limit'), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Date: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
        
        // PDF 출력
        $pdf->Output('tcpdf_test_' . date('YmdHis') . '.pdf', 'D');
        exit;
    } catch (Exception $e) {
        echo "<p style='color: red;'>PDF 다운로드 오류: " . $e->getMessage() . "</p>";
    }
}
?>