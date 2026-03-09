<?php
/**
 * result.php — Result Display Page for Poster Factory
 * 
 * GET /poster/result.php?job_id=xxx
 * Displays SVG preview, metadata, and download links.
 */

$jobId = trim($_GET['job_id'] ?? '');

if ($jobId === '') {
    header('Location: index.php');
    exit;
}

// Sanitize
$jobId = basename($jobId);

$outputBase = realpath(__DIR__ . '/../_poster_factory/output');
$jobDir     = $outputBase ? $outputBase . '/' . $jobId : '';
$jobExists  = $jobDir && is_dir($jobDir);

// Load data
$brief    = null;
$metadata = null;
$svgPath  = '';
$svgUrl   = '';
$previewUrl = '';
$hasPreview = false;

if ($jobExists) {
    // Brief
    $briefPath = $jobDir . '/brief.json';
    if (file_exists($briefPath)) {
        $brief = json_decode(file_get_contents($briefPath), true);
    }

    // Metadata
    $metaPath = $jobDir . '/metadata.json';
    if (file_exists($metaPath)) {
        $metadata = json_decode(file_get_contents($metaPath), true);
    }

    // SVG
    $svgFile = $jobDir . '/poster.svg';
    if (file_exists($svgFile)) {
        $svgUrl = '/_poster_factory/output/' . $jobId . '/poster.svg';
    }

    // Preview HTML
    $previewFile = $jobDir . '/preview.html';
    if (file_exists($previewFile)) {
        $hasPreview = true;
        $previewUrl = '/_poster_factory/output/' . $jobId . '/preview.html';
    }

    // Layout spec
    $layoutSpec = null;
    $layoutPath = $jobDir . '/layout_spec.json';
    if (file_exists($layoutPath)) {
        $layoutSpec = json_decode(file_get_contents($layoutPath), true);
    }

    // Count images
    $imageFiles = glob($jobDir . '/images/item_*.png');
    $imageCount = is_array($imageFiles) ? count($imageFiles) : 0;
    $heroExists = file_exists($jobDir . '/images/hero.png');
}

$bizName = $brief['business_name'] ?? $jobId;
$industry = $brief['industry'] ?? '';
$layoutId = $layoutSpec['layout_id'] ?? ($metadata['layout'] ?? 'auto');
$elapsed  = $metadata['elapsed_seconds'] ?? 0;
$cost     = $metadata['estimated_cost_usd'] ?? 0;

// Format elapsed
$elapsedStr = '';
if ($elapsed > 0) {
    $mins = floor($elapsed / 60);
    $secs = $elapsed % 60;
    $elapsedStr = $mins > 0 ? "{$mins}분 {$secs}초" : "{$secs}초";
}

// Format cost
$costStr = $cost > 0 ? '$' . number_format($cost, 2) : '';

// Layout label map
$layoutLabels = [
    'classic_grid'   => '클래식 그리드',
    'hero_dominant'  => '히어로 강조',
    'magazine_split' => '매거진 분할',
    'bold_typo'      => '볼드 타이포',
    'side_by_side'   => '사이드 바이',
    'auto'           => '자동',
];
$layoutLabel = $layoutLabels[$layoutId] ?? $layoutId;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>포스터 완성 — <?= htmlspecialchars($bizName) ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --brand: #2D3436;
  --accent: #D4A373;
  --accent-light: #E8C9A0;
  --bg: #FAFAF8;
  --bg-warm: #F5EDE3;
  --bg-card: #FFFFFF;
  --text: #2D3436;
  --text-muted: #636E72;
  --text-light: #B2BEC3;
  --border: #DFE6E9;
  --success: #00B894;
  --radius: 12px;
  --radius-sm: 8px;
  --shadow-sm: 0 1px 3px rgba(45,52,54,0.06);
  --shadow-md: 0 4px 16px rgba(45,52,54,0.08);
  --shadow-lg: 0 8px 32px rgba(45,52,54,0.12);
  --transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

html {
  font-size: 15px;
  -webkit-font-smoothing: antialiased;
}

body {
  font-family: 'Pretendard', 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--bg);
  color: var(--text);
  line-height: 1.6;
  min-height: 100vh;
}

/* ── Success Banner ── */
.success-banner {
  background: linear-gradient(135deg, var(--brand) 0%, #3D4A4D 100%);
  padding: 2.5rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.success-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 40% 50%, rgba(0,184,148,0.08) 0%, transparent 50%);
}

.success-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: rgba(0,184,148,0.15);
  border: 2px solid rgba(0,184,148,0.3);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 0.75rem;
  position: relative;
  animation: scaleIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
}

@keyframes scaleIn {
  from { transform: scale(0); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.success-banner h1 {
  color: #FFFFFF;
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  position: relative;
  animation: fadeUp 0.5s ease-out 0.15s both;
}

.success-banner h1 span {
  color: var(--accent-light);
}

.success-banner p {
  color: rgba(255,255,255,0.5);
  font-size: 0.85rem;
  margin-top: 0.35rem;
  position: relative;
  animation: fadeUp 0.5s ease-out 0.25s both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Container ── */
.container {
  max-width: 840px;
  margin: 0 auto;
  padding: 2rem 1.25rem 4rem;
}

/* ── Metadata Row ── */
.meta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  animation: fadeUp 0.5s ease-out 0.3s both;
}

.meta-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.85rem;
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--text-muted);
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 100px;
}

.meta-tag .meta-icon {
  font-size: 0.85rem;
}

.meta-tag strong {
  font-weight: 600;
  color: var(--text);
}

/* ── SVG Preview ── */
.preview-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  margin-bottom: 1.5rem;
  animation: fadeUp 0.5s ease-out 0.35s both;
}

.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.25rem;
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}

.preview-header-title {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.preview-header-size {
  font-size: 0.75rem;
  color: var(--text-light);
}

.preview-body {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 1.5rem;
  background: #E8E8E8;
  min-height: 300px;
}

.preview-body object {
  width: 100%;
  max-width: 710px;
  border-radius: 4px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.12);
}

.no-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
  text-align: center;
}

.no-preview .no-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
  opacity: 0.5;
}

/* ── Action Cards ── */
.actions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  animation: fadeUp 0.5s ease-out 0.4s both;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  text-decoration: none;
  color: var(--text);
  transition: all var(--transition);
  box-shadow: var(--shadow-sm);
}

.action-card:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--accent);
  transform: translateY(-1px);
}

.action-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.action-icon.dl { background: rgba(212,163,115,0.12); }
.action-icon.web { background: rgba(9,132,227,0.08); }
.action-icon.new { background: rgba(0,184,148,0.08); }
.action-icon.folder { background: rgba(108,92,231,0.08); }

.action-label {
  font-size: 0.88rem;
  font-weight: 600;
}

.action-sublabel {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 400;
}

/* ── Files List ── */
.files-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  box-shadow: var(--shadow-sm);
  animation: fadeUp 0.5s ease-out 0.45s both;
}

.files-title {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--brand);
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.file-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0;
  border-bottom: 1px solid rgba(223,230,233,0.5);
  font-size: 0.82rem;
}

.file-row:last-child { border-bottom: none; }

.file-icon {
  font-size: 0.85rem;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.file-name {
  color: var(--text);
  font-weight: 500;
  font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
  font-size: 0.78rem;
}

.file-desc {
  color: var(--text-light);
  font-size: 0.75rem;
  margin-left: auto;
}

/* ── Back Button ── */
.back-wrap {
  text-align: center;
  margin-top: 2rem;
  animation: fadeUp 0.5s ease-out 0.5s both;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 2rem;
  font-size: 0.9rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--brand);
  background: var(--bg-card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  cursor: pointer;
  text-decoration: none;
  transition: all var(--transition);
  box-shadow: var(--shadow-sm);
}

.btn-back:hover {
  border-color: var(--accent);
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

/* ── Footer ── */
.footer {
  text-align: center;
  padding: 2rem 1rem;
  font-size: 0.78rem;
  color: var(--text-light);
}

.footer a {
  color: var(--accent);
  text-decoration: none;
}

/* ── Responsive ── */
@media (max-width: 600px) {
  .success-banner { padding: 2rem 1rem; }
  .success-banner h1 { font-size: 1.3rem; }
  .container { padding: 1.25rem 1rem 3rem; }
  .actions-grid { grid-template-columns: 1fr; }
  .preview-body { padding: 1rem; }
}
</style>
</head>
<body>

<?php if (!$jobExists || !$svgUrl): ?>
<!-- ── Error State ── -->
<div class="success-banner" style="background: linear-gradient(135deg, #636E72, #2D3436);">
  <div class="success-icon" style="background: rgba(214,48,49,0.15); border-color: rgba(214,48,49,0.3);">⚠️</div>
  <h1>포스터를 찾을 수 없습니다</h1>
  <p>작업 ID: <?= htmlspecialchars($jobId) ?></p>
</div>
<div class="container">
  <div class="no-preview">
    <div class="no-icon">📭</div>
    <p>요청하신 포스터가 아직 생성 중이거나 존재하지 않습니다.</p>
    <p style="margin-top: 0.5rem; font-size: 0.82rem;">잠시 후 다시 시도하거나 새 포스터를 만들어 주세요.</p>
  </div>
  <div class="back-wrap">
    <a href="index.php" class="btn-back">← 새 포스터 만들기</a>
  </div>
</div>

<?php else: ?>
<!-- ── Success State ── -->
<div class="success-banner">
  <div class="success-icon">✅</div>
  <h1><span><?= htmlspecialchars($bizName) ?></span> 포스터 완성!</h1>
  <p>AI가 자동으로 생성한 인쇄용 포스터입니다</p>
</div>

<div class="container">

  <!-- Metadata -->
  <div class="meta-row">
    <div class="meta-tag">
      <span class="meta-icon">🏪</span>
      <strong><?= htmlspecialchars($industry) ?></strong>
    </div>
    <div class="meta-tag">
      <span class="meta-icon">🎨</span>
      레이아웃: <strong><?= htmlspecialchars($layoutLabel) ?></strong>
    </div>
    <?php if ($elapsedStr): ?>
    <div class="meta-tag">
      <span class="meta-icon">⏱</span>
      소요: <strong><?= htmlspecialchars($elapsedStr) ?></strong>
    </div>
    <?php endif; ?>
    <?php if ($costStr): ?>
    <div class="meta-tag">
      <span class="meta-icon">💰</span>
      비용: <strong><?= htmlspecialchars($costStr) ?></strong>
    </div>
    <?php endif; ?>
    <div class="meta-tag">
      <span class="meta-icon">🖼</span>
      이미지: <strong><?= ($heroExists ? 1 : 0) + $imageCount ?>장</strong>
    </div>
  </div>

  <!-- SVG Preview -->
  <div class="preview-card">
    <div class="preview-header">
      <span class="preview-header-title">📐 포스터 미리보기</span>
      <span class="preview-header-size">2130 × 3000px</span>
    </div>
    <div class="preview-body">
      <object data="<?= htmlspecialchars($svgUrl) ?>" type="image/svg+xml" style="aspect-ratio: 2130/3000;">
        포스터를 표시할 수 없습니다. <a href="<?= htmlspecialchars($svgUrl) ?>">SVG 파일 다운로드</a>
      </object>
    </div>
  </div>

  <!-- Action Cards -->
  <div class="actions-grid">
    <a href="<?= htmlspecialchars($svgUrl) ?>" download class="action-card">
      <div class="action-icon dl">📥</div>
      <div>
        <div class="action-label">SVG 다운로드</div>
        <div class="action-sublabel">일러스트레이터에서 편집 가능</div>
      </div>
    </a>

    <?php if ($hasPreview): ?>
    <a href="<?= htmlspecialchars($previewUrl) ?>" target="_blank" class="action-card">
      <div class="action-icon web">🌐</div>
      <div>
        <div class="action-label">웹 미리보기</div>
        <div class="action-sublabel">브라우저에서 전체 화면 보기</div>
      </div>
    </a>
    <?php else: ?>
    <a href="<?= htmlspecialchars($svgUrl) ?>" target="_blank" class="action-card">
      <div class="action-icon web">🌐</div>
      <div>
        <div class="action-label">새 탭에서 보기</div>
        <div class="action-sublabel">SVG 원본 보기</div>
      </div>
    </a>
    <?php endif; ?>
  </div>

  <!-- Generated Files -->
  <div class="files-card">
    <div class="files-title">📁 생성된 파일</div>

    <div class="file-row">
      <span class="file-icon">📐</span>
      <span class="file-name">poster.svg</span>
      <span class="file-desc">최종 포스터 (편집 가능)</span>
    </div>

    <?php if ($hasPreview): ?>
    <div class="file-row">
      <span class="file-icon">🌐</span>
      <span class="file-name">preview.html</span>
      <span class="file-desc">웹 미리보기</span>
    </div>
    <?php endif; ?>

    <?php if ($heroExists): ?>
    <div class="file-row">
      <span class="file-icon">🖼</span>
      <span class="file-name">images/hero.png</span>
      <span class="file-desc">메인 히어로 이미지</span>
    </div>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $imageCount; $i++): ?>
    <div class="file-row">
      <span class="file-icon">🖼</span>
      <span class="file-name">images/item_<?= sprintf('%02d', $i) ?>.png</span>
      <span class="file-desc">품목 <?= $i ?> 이미지</span>
    </div>
    <?php endfor; ?>

    <div class="file-row">
      <span class="file-icon">📋</span>
      <span class="file-name">brief.json</span>
      <span class="file-desc">입력 데이터</span>
    </div>

    <?php if (file_exists($jobDir . '/copy.json')): ?>
    <div class="file-row">
      <span class="file-icon">✍️</span>
      <span class="file-name">copy.json</span>
      <span class="file-desc">AI 생성 카피</span>
    </div>
    <?php endif; ?>

    <?php if (file_exists($jobDir . '/design.json')): ?>
    <div class="file-row">
      <span class="file-icon">🎨</span>
      <span class="file-name">design.json</span>
      <span class="file-desc">이미지 프롬프트</span>
    </div>
    <?php endif; ?>

    <?php if (file_exists($jobDir . '/layout_spec.json')): ?>
    <div class="file-row">
      <span class="file-icon">📏</span>
      <span class="file-name">layout_spec.json</span>
      <span class="file-desc">아트디렉팅 사양</span>
    </div>
    <?php endif; ?>

    <?php if (file_exists($jobDir . '/metadata.json')): ?>
    <div class="file-row">
      <span class="file-icon">ℹ️</span>
      <span class="file-name">metadata.json</span>
      <span class="file-desc">생성 메타데이터</span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Back -->
  <div class="back-wrap">
    <a href="index.php" class="btn-back">🚀 새 포스터 만들기</a>
  </div>

</div>
<?php endif; ?>

<footer class="footer">
  <a href="/">두손기획인쇄</a> &middot; 포스터 팩토리 &middot; Powered by Gemini AI
</footer>

</body>
</html>
