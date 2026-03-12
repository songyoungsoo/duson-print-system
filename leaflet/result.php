<?php
/**
 * result.php — V2 Leaflet Factory Result Viewer
 * 
 * 완성된 SVG 전단지와 메타데이터를 보여줍니다.
 */

$jobId = trim($_GET['job_id'] ?? '');

if ($jobId === '') {
    header('Location: index.php');
    exit;
}

$jobId = basename($jobId);
$outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
$jobDir = $outputBase ? $outputBase . '/' . $jobId : '';
$jobExists = $jobDir && is_dir($jobDir);

// 관련 정보 세팅
$brief = [];
$svgUrl = '';
$hasError = false;
$errorMsg = '';

if ($jobExists) {
    if (file_exists($jobDir . '/brief.json')) {
        $brief = json_decode(file_get_contents($jobDir . '/brief.json'), true);
    }
    
    // 완성된 파일이 있는지 확인
    if (file_exists($jobDir . '/leaflet.svg')) {
        $svgUrl = '/_leaflet_factory/output/' . $jobId . '/leaflet.svg';
    } else {
        // 아직 없으면 에러 혹은 생성 중단된 상태
        $hasError = true;
        $errorMsg = '전단지 파일이 정상적으로 생성되지 않았습니다.';
        
        // status 확인
        if (file_exists($jobDir . '/status.json')) {
            $status = json_decode(file_get_contents($jobDir . '/status.json'), true);
            if ($status['status'] === 'error') {
                $errorMsg = $status['error_message'] ?? $errorMsg;
            }
        }
    }
}

$bizName = $brief['business_name'] ?? $jobId;
$imageUsageMode = $brief['direction']['image_usage'] ?? 'ai_generate';

$modeLabels = [
    'ai_generate' => 'AI 전체 자동 생성',
    'reference_only' => '참조 이미지 기반 AI 생성',
    'use_original' => '사용자 업로드 이미지 유지'
];
$modeLabel = $modeLabels[$imageUsageMode] ?? $imageUsageMode;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>제작 결과 — <?= htmlspecialchars($bizName) ?></title>
<style>
/* ── Reset & Foundation ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

@font-face {
  font-family: 'Pretendard';
  src: url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.min.css');
}

:root {
  --brand-color: #5b8fb9; 
  --accent-color: #8db596; 
  --bg-color: #f7f9fc;
  --panel-bg: #ffffff;
  --text-main: #333333;
  --text-muted: #666666;
  --border-color: #d1d9e6;
  --header-bg: #e3ebf3; 
  --radius: 4px;
}

body {
  font-family: 'Pretendard', 'Noto Sans KR', sans-serif;
  background-color: var(--bg-color);
  color: var(--text-main);
  font-size: 14px;
  line-height: 1.5;
  padding: 20px;
}

.container {
  max-width: 960px;
  margin: 0 auto;
}

.result-card {
  background: var(--panel-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-bottom: 20px;
  overflow: hidden;
}

.card-header {
  background-color: var(--header-bg);
  padding: 15px 20px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h2 {
  font-size: 16px;
  color: var(--brand-color);
  margin: 0;
}

.card-body {
  padding: 20px;
}

/* 요약 테이블 */
.summary-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}
.summary-table th, .summary-table td {
  border: 1px solid var(--border-color);
  padding: 8px 12px;
  text-align: left;
}
.summary-table th {
  background-color: #f0f4f8;
  font-weight: 500;
  width: 150px;
  color: var(--text-muted);
}

.svg-preview {
  display: flex;
  justify-content: center;
  background: #e8e8e8;
  padding: 30px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  min-height: 400px;
}

.svg-preview object {
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  max-width: 100%;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  justify-content: center;
}

.btn {
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  border-radius: var(--radius);
  cursor: pointer;
  text-decoration: none;
  border: 1px solid var(--border-color);
  background: #fff;
  color: var(--text-main);
  transition: all 0.2s;
}

.btn-primary {
  background-color: var(--brand-color);
  color: #fff;
  border: none;
}
.btn-primary:hover {
  background-color: #4a7a9e;
}

/* 에러 박스 */
.error-box {
  background-color: #fee;
  border: 1px solid #e57373;
  padding: 20px;
  color: #c62828;
  border-radius: var(--radius);
  margin-bottom: 20px;
}

</style>
</head>
<body>

<div class="container">
  <?php if (!$jobExists || $hasError): ?>
    <div class="error-box">
      <h3 style="margin-top:0;">⚠ 처리 중 오류가 발생했습니다.</h3>
      <p><?= htmlspecialchars($errorMsg) ?></p>
      <div style="margin-top: 15px;">
        <a href="index.php" class="btn">돌아가기</a>
      </div>
    </div>
  <?php else: ?>
  
    <div class="result-card">
      <div class="card-header">
        <h2>전단지 생성 완료 보고서</h2>
        <span style="font-size: 12px; color: var(--text-muted);">Job ID: <?= htmlspecialchars($jobId) ?></span>
      </div>
      
      <div class="card-body">
        <table class="summary-table">
          <tr>
            <th>가게명</th>
            <td><?= htmlspecialchars($bizName) ?></td>
            <th>제작 모드</th>
            <td><?= htmlspecialchars($modeLabel) ?></td>
          </tr>
          <tr>
            <th>업종</th>
            <td colspan="3"><?= htmlspecialchars($brief['category'] ?? '') ?></td>
          </tr>
        </table>
        
        <div class="svg-preview">
          <!-- 실제 렌더링 영역 (비율은 A4 세로 비율 등 스크립트 결과에 맞춰 조정됨) -->
          <object data="<?= htmlspecialchars($svgUrl) ?>" type="image/svg+xml" width="500"></object>
        </div>
        
        <div class="actions">
          <a href="index.php" class="btn">새 작업 시작</a>
          <a href="<?= htmlspecialchars($svgUrl) ?>" download="leaflet_<?= htmlspecialchars($bizName) ?>.svg" class="btn btn-primary">⬇ SVG 다운로드 (편집용)</a>
        </div>
      </div>
    </div>
    
  <?php endif; ?>
</div>

</body>
</html>
