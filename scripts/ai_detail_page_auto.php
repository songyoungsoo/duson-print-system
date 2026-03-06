<?php
/**
 * AI Detail Page Generator - Full Auto
 * 이미지 배경 + HTML 텍스트 오버레이 (수정 가능)
 * 
 * 사용법:
 *   php ai_detail_page_auto.php generate [제품]  # 카피+HTML
 *   php ai_detail_page_auto.php status
 *   php ai_detail_page_auto.php swap [제품]
 *   php ai_detail_page_auto.php preview [제품]
 */

declare(strict_types=1);

define('API_KEY', 'AIzaSyAfv-I9Vpbq8JZzWkeI6DmYrM91bFw2iZQ');
define('OUTPUT_BASE', '/var/www/html/ImgFolder/detail_page');
define('STAGING_BASE', '/var/www/html/ImgFolder/detail_page_staging');
define('GEMINI_MODEL', 'gemini-2.5-flash');
define('IMAGE_MODEL', 'nano-banana-pro-preview');

$PRODUCTS = [
    'sticker_new' => '스티커', 'namecard' => '명함', 'inserted' => '전단지',
    'envelope' => '봉투', 'littleprint' => '포스터', 'merchandisebond' => '상품권',
    'cadarok' => '카다록', 'ncrflambeau' => 'NCR양식지', 'msticker' => '자석스티커'
];

// 섹션별 이미지 프롬프트
$IMAGE_PROMPTS = [
    'sticker_new' => [1=>'premium custom sticker sheets',2=>'low quality stickers peeling off',3=>'high quality sticker strong adhesive',4=>'macro printed sticker surface',5=>'strong adhesive sticker applied',6=>'water drops waterproof sticker',7=>'premium vinyl material texture',8=>'stickers on laptop packaging',9=>'various sticker shapes die-cut',10=>'printing machine stickers',11=>'quality inspection sticker sheets',12=>'happy business owner stickers',13=>'beautiful sticker sheets CTA'],
    'namecard' => [1=>'premium business cards',2=>'cheap business cards comparison',3=>'high quality business cards',4=>'macro business card printing',5=>'embossed business cards',6=>'water resistant business card',7=>'premium cardstock texture',8=>'business cards meeting',9=>'various business card sizes',10=>'printing press business cards',11=>'quality inspection business cards',12=>'happy professional business cards',13=>'beautiful business card CTA'],
    'inserted' => [1=>'premium flyer leaflets',2=>'cheap flyers comparison',3=>'high quality flyers',4=>'macro flyer print detail',5=>'flyer paper quality',6=>'water resistant flyer',7=>'premium paper texture',8=>'flyers event promotion',9=>'various flyer sizes',10=>'printing machine flyers',11=>'quality inspection flyers',12=>'happy customers flyers',13=>'beautiful flyer design CTA'],
    'envelope' => [1=>'premium envelopes',2=>'cheap envelopes comparison',3=>'high quality envelopes',4=>'macro envelope printing',5=>'envelope adhesive seal',6=>'water resistant envelope',7=>'premium envelope texture',8=>'envelopes business correspondence',9=>'various envelope sizes',10=>'envelope manufacturing',11=>'quality inspection envelopes',12=>'happy business owner envelopes',13=>'beautiful envelope CTA'],
    'littleprint' => [1=>'premium poster',2=>'cheap poster comparison',3=>'high quality poster',4=>'macro poster print',5=>'poster durability',6=>'water resistant poster',7=>'premium poster texture',8=>'poster on wall office',9=>'various poster sizes',10=>'large format printing',11=>'quality inspection posters',12=>'happy customer poster',13=>'beautiful poster CTA'],
    'merchandisebond' => [1=>'premium gift certificate',2=>'cheap gift certificate',3=>'high quality gift certificate',4=>'macro gift certificate',5=>'gift certificate security',6=>'water resistant gift certificate',7=>'premium certificate texture',8=>'gift certificate retail',9=>'various certificate designs',10=>'certificate printing machine',11=>'quality inspection certificate',12=>'happy customer certificate',13=>'beautiful certificate CTA'],
    'cadarok' => [1=>'premium catalog',2=>'cheap catalog comparison',3=>'high quality catalog',4=>'macro catalog print',5=>'catalog binding',6=>'water resistant catalog',7=>'premium catalog texture',8=>'catalog product showcase',9=>'various catalog sizes',10=>'catalog printing binding',11=>'quality inspection catalog',12=>'happy customer catalog',13=>'beautiful catalog CTA'],
    'ncrflambeau' => [1=>'premium NCR forms',2=>'cheap carbon paper',3=>'high quality NCR',4=>'macro NCR form',5=>'NCR carbon copy',6=>'water resistant NCR',7=>'premium NCR texture',8=>'NCR forms office',9=>'various NCR sizes',10=>'NCR printing machine',11=>'quality inspection NCR',12=>'happy office worker NCR',13=>'beautiful NCR CTA'],
    'msticker' => [1=>'premium magnet sticker',2=>'cheap magnet comparison',3=>'high quality magnet sticker',4=>'macro magnet sticker',5=>'magnet strong adhesion',6=>'water resistant magnet',7=>'premium magnet texture',8=>'magnet on refrigerator',9=>'various magnet sizes',10=>'magnet printing machine',11=>'quality inspection magnet',12=>'happy customer magnet',13=>'beautiful magnet CTA']
];

$SECTION_GUIDES = [
    1=>['topic'=>'제품 대표','guide'=>'최고의 제품 이미지. 프리미엄.'],
    2=>['topic'=>'문제 제기','guide'=>'일반 제품 문제점.'],
    3=>['topic'=>'해결 제시','guide'=>'우리 제품이 문제를 해결.'],
    4=>['topic'=>'인쇄 품질','guide'=>'고해상도 인쇄.'],
    5=>['topic'=>'접착력','guide'=>'강력한 접착력.'],
    6=>['topic'=>'방수','guide'=>'방수 코팅.'],
    7=>['topic'=>'재질','guide'=>'프리미엄 재질.'],
    8=>['topic'=>'활용','guide'=>'다양한 활용처.'],
    9=>['topic'=>'맞춤 제작','guide'=>'자유로운 맞춤.'],
    10=>['topic'=>'제작 공정','guide'=>'전문 제작 공정.'],
    11=>['topic'=>'품질 관리','guide'=>'철저한 품질 관리.'],
    12=>['topic'=>'후기','guide'=>'고객 후기.'],
    13=>['topic'=>'CTA','guide'=>'지금 주문하세요!']
];

function getImgPrompt($pk, $s): string {
    global $IMAGE_PROMPTS;
    return ($IMAGE_PROMPTS[$pk][$s] ?? $IMAGE_PROMPTS['sticker_new'][$s]) . ', ultra realistic product photography, commercial advertising, studio lighting, clean white background, premium quality';
}

function genImage($pk, $s, $dir): array {
    $prompt = getImgPrompt($pk, $s);
    $file = $dir . '/section_' . str_pad((string)$s, 2, '0', STR_PAD_LEFT) . '.png';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . IMAGE_MODEL . ':generateContent?key=' . API_KEY;
    $data = ['contents'=>[['parts'=>[['text'=>$prompt]]], 'generationConfig'=>['temperature'=>0.9,'maxOutputTokens'=>32768]];
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($data),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>180]);
    $r = curl_exec($ch); $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($hc !== 200) return ['success'=>false,'error'=>"HTTP $hc"];
    $res = json_decode($r, true);
    if (isset($res['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
        file_put_contents($file, base64_decode($res['candidates'][0]['content']['parts'][0]['inlineData']['data']));
        return ['success'=>true,'file'=>$file,'size'=>filesize($file)];
    }
    return ['success'=>false,'error'=>'No image'];
}

/**
 * 카피 생성 - 네이버 광고 검수 가이드라인 적용
 */
function genCopy($pk, $pn, $s, $imgPrompt): array {
    global $SECTION_GUIDES;
    $ctx = $SECTION_GUIDES[$s] ?? $SECTION_GUIDES[1];
    
    // [중요] 네이버 광고 검수 가이드라인
    $prompt = <<<'PROMPT'
당신은 마케팅 카피라이터입니다.

[중요] 아래 네이버 광고 검수 가이드라인을 반드시 준수하세요:

❌ 금지 표현:
- 과대광고: "세계제일", "최고급", "최우수", "업계 최초", "무조건", "반드시"
- 타업체 비교/ 폄하: "타사 대비", "경쟁사보다", "기타 제품보다", "기존"
- 허위과장: "100%", "절대", "무한"

✅ 허용 표현:
- 구체적 사실: "10년 경험", "고객 만족 99%"
- 제품 특성 설명: "고품질", "친환경", "사용자 편의"

[제품] {$pn}
[섹션] {$ctx['topic']}
[이미지] {$imgPrompt}
[가이드] {$ctx['guide']}

요청: 헤드카피(10-20자), 서브카피(20-30자), 상세설명(50-80자)
JSON으로만 출력: {"headline":"","subheadline":"","description":""}
PROMPT;
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . API_KEY;
    $data = ['contents'=>[['parts'=>[['text'=>$prompt]]]], 'generationConfig'=>['temperature'=>0.7,'maxOutputTokens'=>512]];
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($data),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_TIMEOUT=>60]);
    $r = curl_exec($ch); $hc = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($hc !== 200) return ['headline'=>"Section $s",'subheadline'=>$ctx['topic'],'description'=>$ctx['guide']];
    $res = json_decode($r, true);
    try {
        $c = trim(preg_replace('/^```json\s*/', '', preg_replace('/\s*```$/', '', $res['candidates'][0]['content']['parts'][0]['text'] ?? '')));
        $j = json_decode($c, true);
        if ($j) return ['headline'=>$j['headline']??'','subheadline'=>$j['subheadline']??'','description'=>$j['description']??''];
    } catch (Exception $e) {}
    return ['headline'=>"Section $s",'subheadline'=>$ctx['topic'],'description'=>$ctx['guide']];
}

/**
 * HTML 생성 - Pretendard 폰트 사용
 */
function genHTML($pn, $dir, $copies): string {
    $html = '';
    for ($i = 1; $i <= 13; $i++) {
        $c = $copies[$i] ?? ['headline'=>"Section $i",'subheadline'=>'','description'=>''];
        $img = 'section_' . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . '.png';
        $html .= "<section class=\"detail-section\" id=\"section-$i\"><div class=\"img\"><img src=\"$img\" alt=\"{$c['headline']}\"></div><div class=\"txt\"><h2>{$c['headline']}</h2><h3>{$c['subheadline']}</h3><p>{$c['description']}</p></div></section>\n";
    }
    // Pretendard 폰트 CDN 추가
    return "<!DOCTYPE html><html lang=\"ko\"><head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"><title>$pn - 상세페이지</title><link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css\"><style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Pretendard',-apple-system,BlinkMacSystemFont,sans-serif;background:#fff;color:#333}.detail-section{display:flex;flex-wrap:wrap;max-width:1200px;margin:0 auto 60px;padding:20px;align-items:center}.detail-section:nth-child(even){flex-direction:row-reverse}.img{flex:1;min-width:300px;padding:20px}.img img{width:100%;height:auto;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.1)}.txt{flex:1;min-width:300px;padding:40px}.txt h2{font-size:2.5rem;margin-bottom:16px;color:#1a1a2e;font-weight:700}.txt h3{font-size:1.5rem;margin-bottom:20px;color:#4a4a6a;font-weight:500}.txt p{font-size:1.1rem;line-height:1.8;color:#666}#section-1{text-align:center;padding:80px 20px;background:linear-gradient(135deg,#f8f9fa,#e9ecef)}#section-1 .txt h2{font-size:3.5rem}#section-13{text-align:center;padding:100px 20px;background:linear-gradient(135deg,#1a1a2e,#16213e);color:#fff}#section-13 .txt h2{color:#fff}#section-13 .txt h3{color:#adb5bd}@media(max-width:768px){.detail-section{flex-direction:column!important;text-align:center}.txt h2{font-size:2rem}}</style></head><main>$html</main></body></html>";
}

function showStatus($p=null) {
    global $PRODUCTS;
    echo "\n╔═══════════════════════════════════════════════════════╗\n";
    echo "║  🎨 AI 상세페이지 - 이미지+카피 연동              ║\n";
    echo "╠═══════════════════════════════════════════════════════╣\n";
    foreach ($p ? [$p=>$PRODUCTS[$p]] : $PRODUCTS as $k=>$n) {
        $d = STAGING_BASE."/$k";
        $imgs = is_dir($d) ? count(glob("$d/section_*.png")) : 0;
        $hasC = file_exists("$d/copies.json");
        $hasH = file_exists("$d/detail.html");
        echo "║ 📦 $n ($k)\n";
        echo "║    🖼️ 이미지: $imgs/13 " . ($imgs>=13?"✅":"") . "\n";
        echo "║    ✍️ 카피: " . ($hasC?"✅":"❌") . "\n";
        echo "║    🌐 HTML: " . ($hasH?"✅":"❌") . "\n║\n";
    }
    echo "╚═══════════════════════════════════════════════════════╝\n";
}

function generateAll($product) {
    global $PRODUCTS;
    if (!isset($PRODUCTS[$product])) { echo "❌ 잘못된 제품\n"; exit(1); }
    $dir = STAGING_BASE . "/$product";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $existing = count(glob("$dir/section_*.png"));
    $hasImgs = $existing >= 13;
    
    echo "=== {$PRODUCTS[$product]} 생성 ";
    if ($hasImgs) echo "(카피+HTML만 - 이미지 {$existing}개 존재)\n\n";
    else echo "(이미지+카피+HTML)\n\n";
    
    // 1. Images
    if (!$hasImgs) {
        echo "📷 [1/3] 이미지 생성...\n";
        for ($i = 1; $i <= 13; $i++) { echo "   섹션 $i... "; $r = genImage($product, $i, $dir); echo $r['success']?"✅\n":"❌ {$r['error']}\n"; }
    } else { echo "📷 [1/3] 이미지: 기존 {$existing}개 사용 ✅\n"; }
    
    // 2. Copy
    echo "\n✍️ [2/3] 카피 생성 (네이버 광고 검수 적용)...\n";
    $copies = [];
    for ($i = 1; $i <= 13; $i++) {
        echo "   섹션 $i... ";
        $copies[$i] = genCopy($product, $PRODUCTS[$product], $i, getImgPrompt($product, $i));
        echo "✅ ({$copies[$i]['headline']})\n";
    }
    file_put_contents("$dir/copies.json", json_encode($copies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // 3. HTML
    echo "\n🌐 [3/3] HTML 생성 (Pretendard 폰트)...\n";
    file_put_contents("$dir/detail.html", genHTML($PRODUCTS[$product], $dir, $copies));
    echo "   ✅ 완료\n\n";
    echo "🎉 생성 완료!\n미리보기: php ai_detail_page_auto.php preview $product\n";
}

function swap($product) {
    global $PRODUCTS;
    if (!isset($PRODUCTS[$product])) { echo "❌ 잘못된 제품\n"; exit(1); }
    $live = OUTPUT_BASE . "/$product";
    $stage = STAGING_BASE . "/$product";
    if (!is_dir($stage)) { echo "❌ 대기폴더 없음\n"; exit(1); }
    
    $backup = OUTPUT_BASE . '/backup_' . date('Y-m-d_His');
    if (!is_dir($backup)) mkdir($backup, 0755, true);
    
    echo "=== {$PRODUCTS[$product]} 교체 ===\n\n";
    foreach (glob("$stage/section_*.png") as $f) {
        $b = basename($f);
        if (file_exists("$live/$b")) copy("$live/$b", "$backup/$b");
        copy($f, "$live/$b"); echo "  ✅ $b\n";
    }
    if (file_exists("$stage/detail.html")) { copy("$stage/detail.html", "$live/detail.html"); echo "  ✅ detail.html\n"; }
    if (file_exists("$stage/copies.json")) { copy("$stage/copies.json", "$live/copies.json"); echo "  ✅ copies.json\n"; }
    echo "\n🎉 교체 완료!\n";
}

function preview($product) {
    global $PRODUCTS;
    $dir = STAGING_BASE . "/$product";
    $f = "$dir/copies.json";
    if (!file_exists($f)) { echo "❌ 카피 없음\n"; exit(1); }
    $copies = json_decode(file_get_contents($f), true);
    echo "=== {$PRODUCTS[$product]} 카피 미리보기 ===\n\n";
    for ($i = 1; $i <= 13; $i++) {
        $c = $copies[$i] ?? ['headline'=>'','subheadline'=>'','description'=>''];
        $has = file_exists("$dir/section_".str_pad((string)$i,2,'0',STR_PAD_LEFT).".png");
        echo "[$i] 🖼️ ".($has?"✅":"❌")." {$c['headline']}\n    ➡️ {$c['subheadline']}\n    📄 {$c['description']}\n\n";
    }
}

$cmd = $argv[1] ?? 'status';
$prod = $argv[2] ?? null;

switch ($cmd) {
    case 'generate': generateAll($prod ?? 'sticker_new'); break;
    case 'swap': swap($prod ?? 'sticker_new'); break;
    case 'preview': preview($prod ?? 'sticker_new'); break;
    default: showStatus($prod);
}
