<?php
/**
 * site_audit.php
 * XAMPP htdocs 구조를 스캔해 문제점과 "권장 규칙"을 생성합니다.
 * 실행:  php site_audit.php   (CLI)  또는  브라우저로 /site_audit.php
 */
ini_set('display_errors', 0);
mb_internal_encoding('UTF-8');

$root = isset($_GET['root']) ? $_GET['root'] : (PHP_SAPI==='cli' ? getcwd() : realpath(__DIR__));
$outDir = $root . DIRECTORY_SEPARATOR . 'audit_out';
@mkdir($outDir, 0777, true);

$ignoreDirs = ['.git','node_modules','vendor','storage','cache','tmp','temp','upload','uploads','.idea','.vscode','playwright-report'];
$extMap = ['php','html','htm','js','css'];

$files = [];
$stats = ['total'=>0,'by_ext'=>array_fill_keys($extMap,0)];
$inline = ['style'=>[],'script'=>[]];
$includes = [];
$assets = ['css_links'=>[],'js_links'=>[],'img_src'=>[]];
$candidates = ['header'=>[],'nav'=>[],'footer'=>[],'login'=>[],'template'=>[]];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $fi) {
  if (!$fi->isFile()) continue;
  $path = $fi->getPathname();
  $rel  = str_replace($root, '', $path);
  $parts = explode(DIRECTORY_SEPARATOR, trim(dirname($rel), DIRECTORY_SEPARATOR));
  if (count(array_intersect($parts, $ignoreDirs))>0) continue;

  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  if (!in_array($ext, $extMap)) continue;

  $stats['total']++;
  $stats['by_ext'][$ext]++;

  $size = $fi->getSize();
  $mtime = $fi->getMTime();
  $content = '';
  if ($ext==='php' || $ext==='html' || $ext==='htm') {
    $content = @file_get_contents($path);

    // 인라인 style/script
    if (preg_match_all('/<style\b[^>]*>/i', $content, $m)) {
      $inline['style'][] = $rel;
    }
    if (preg_match_all('/<script\b(?![^>]*\bsrc=)[^>]*>/i', $content, $m)) {
      $inline['script'][] = $rel;
    }

    // include/require
    if (preg_match_all('/\b(include|require)(_once)?\s*\(?\s*[\'"]([^\'"]+)[\'"]\s*\)?/i', $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $mm) {
        $includes[] = ['file'=>$rel, 'type'=>strtolower($mm[1].($mm[2]?:'')), 'target'=>$mm[3]];
      }
    }

    // link/script/img 자산
    if (preg_match_all('/<link\b[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $m)) {
      foreach ($m[1] as $href) $assets['css_links'][] = ['file'=>$rel,'href'=>$href];
    }
    if (preg_match_all('/<script\b[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $m)) {
      foreach ($m[1] as $src) $assets['js_links'][] = ['file'=>$rel,'src'=>$src];
    }
    if (preg_match_all('/<img\b[^>]*src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $m)) {
      foreach ($m[1] as $src) $assets['img_src'][] = ['file'=>$rel,'src'=>$src];
    }

    // 공통 파츠 후보 감지
    $fname = strtolower(basename($path));
    if (preg_match('/header/i',$fname)) $candidates['header'][] = $rel;
    if (preg_match('/nav|menu|gnb|lnb/i',$fname)) $candidates['nav'][] = $rel;
    if (preg_match('/footer/i',$fname)) $candidates['footer'][] = $rel;
    if (preg_match('/login/i',$fname)) $candidates['login'][] = $rel;
    if (preg_match('/layout|template/i',$fname)) $candidates['template'][] = $rel;
  }

  $files[] = ['path'=>$rel,'ext'=>$ext,'size'=>$size,'mtime'=>$mtime];
}

// 중복 CSS/JS 링크 상위 10개
function topRefs($list,$key){
  $count=[]; foreach($list as $x){ $k=$x[$key]; $count[$k]=($count[$k]??0)+1; }
  arsort($count); return array_slice($count,0,10,true);
}
$topCss = topRefs($assets['css_links'],'href');
$topJs  = topRefs($assets['js_links'],'src');

// 규칙 제안
$rules = [];
$rules[] = "## 공통 폴더 구조\n- CSS: `/assets/css/*`\n- JS: `/assets/js/*`\n- 공통 파츠: `/includes/*` (header.php, nav.php, login.php, footer.php)\n- 레이아웃: `/templates/layout.php` (좌: 갤러리 / 우: 계산기)\n- 슬라이드: `/slide/*`";
if (count($inline['style']) + count($inline['script']) > 0) {
  $rules[] = "## 인라인 금지\n- 발견된 인라인 `<style>`: ".count($inline['style'])."개 파일\n- 발견된 인라인 `<script>`: ".count($inline['script'])."개 파일\n- **모두 assets로 이전**, 페이지에는 include/link/script만 유지";
}
$rules[] = "## include 규칙\n- `require_once __DIR__.'/파일.php';` 형식으로 **절대경로화**\n- 페이지 파일(index 등)은 **include만** 남기고 로직 금지";
$rules[] = "## 자산 로드 규칙\n- `includes/config.php`에 `asset('/assets/...')` 헬퍼로 `?v=버전` 캐시버스트\n- 중복 사용 상위 CSS/JS:\n  - CSS: ".json_encode($topCss, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n  - JS : ".json_encode($topJs , JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
$rules[] = "## 캐러셀 규격(고정)\n- Center-mode Peek: 가운데 2/3·W, 좌/우 각 1/6·W, **항상 우→좌(Forward-Only)**\n- 전환 800ms, 자동 2000ms, 호버/포커스 일시정지, prev 비활성";
$rules[] = "## 접근성/성능\n- `aria-roledescription=\"carousel\"`, 도트 `aria-current`, lazy, CLS 방지, reduced-motion 지원";
$rules[] = "## PHP 호환/보안\n- PHP 7.4+ 호환, `mysqli + prepared statements`\n- 공통 `functions.php`: `e()`, `money_kr()`, CSRF 토큰, 페이징";

// 출력 저장
$report = [
  'root'=>$root,
  'stats'=>$stats,
  'inline'=>$inline,
  'includes'=>$includes,
  'assets'=>$assets,
  'candidates'=>$candidates,
  'top'=>['css'=>$topCss,'js'=>$topJs],
  'generated_at'=>date('c')
];

file_put_contents($outDir.'/audit_report.json', json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
file_put_contents($outDir.'/audit_rules.md', "# 권장 규칙(자동 생성)\n\n".implode("\n\n",$rules)."\n");

header('Content-Type: text/plain; charset=utf-8');
echo "[OK] 스캔 완료\n";
echo "root: {$root}\n";
echo "files: {$stats['total']} (php {$stats['by_ext']['php']}, html {$stats['by_ext']['html']}, js {$stats['by_ext']['js']}, css {$stats['by_ext']['css']})\n";
echo "inline <style> files: ".count($inline['style'])."\n";
echo "inline <script> files: ".count($inline['script'])."\n";
echo "candidates: header(".count($candidates['header'])."), nav(".count($candidates['nav'])."), footer(".count($candidates['footer'])."), login(".count($candidates['login']).")\n";
echo "output: {$outDir}\\audit_report.json, {$outDir}\\audit_rules.md\n";
