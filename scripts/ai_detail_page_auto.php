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

define('API_KEY', getenv('GEMINI_API_KEY') ?: 'AIzaSyAEBMlGYm0cvBsBHMqaCmJRObFKEXN8jXs');
define('OUTPUT_BASE', '/var/www/html/ImgFolder/detail_page');
define('STAGING_BASE', '/var/www/html/ImgFolder/detail_page_staging');
define('GEMINI_MODEL', 'gemini-3-pro-preview');
define('IMAGE_MODEL', 'gemini-3-pro-image-preview');

$PRODUCTS = [
    'sticker_new' => '스티커', 'namecard' => '명함', 'inserted' => '전단지',
    'envelope' => '봉투', 'littleprint' => '포스터', 'merchandisebond' => '상품권',
    'cadarok' => '카다록', 'ncrflambeau' => 'NCR양식지', 'msticker' => '자석스티커'
];

// 제품별 상세 정보 (카피 참조용 - 실제 설명 페이지 기반 2026-03-06)
$PRODUCT_INFO = [
    'sticker_new' => [
        'desc' => '사각형 맞춤 컬러 스티커 인쇄. 10mm~600mm 자유 규격, 11종 용지로 나만의 스티커 제작',
        'features' => '코팅/비코팅/무광/강접/초강접 선택, 원터치(배경없음)/투터치(배경있음), 유광/무광/심플레인보우 코팅, 당일 출고(아트지 유광/강접/비코팅), 97% 당일 출고',
        'materials' => '아트지유광 90g, 아트지비코팅 90g, 아트지무광 90g, 강접코팅 90g, 초강접유광 90g, 초강접비코팅 90g, 은데드롱 25g(메탈릭), 투명데드롱 25g(투명라벨), 유포지 80g(방수), 모조지 80g, 크라프트지 57g(친환경)',
        'sizes' => '10mm~600mm 사각형 맞춤, 원터치/투터치(사방 3mm 여분)',
        'delivery' => '아트지 유광/강접/비코팅: 매일 당일출고 / 무광·은데드롱·기타: 요일별 출고',
        'usecases' => '상품 라벨, 홍보 스티커, 봉인 스티커, 냉장 식품 라벨(유포지), 화장품 라벨(무광), 친환경 브랜드(크라프트)',
    ],
    'namecard' => [
        'desc' => '일반지부터 최고급 수입지까지 30가지 용지로 제작하는 명함 인쇄. 비코팅/코팅/고품격코팅 선택',
        'features' => '비코팅(부드러운질감/은은한광택), 코팅216g/250g(구겨짐적음/찢어지지않음), 고품격코팅300g/400g(탄탄한두께), 당일판(11시마감→18시출고), 옵셋/디지털/당일판인쇄, 97% 다음날 출고',
        'materials' => '스노우화이트 비코팅 250g, 코팅 216g/250g, 고품격코팅 300g/400g, 유광코팅 300g, 린넨(천질감), 펄(은은한반짝임), 크라프트(갈색자연), 수입특수지',
        'sizes' => '90x50mm(표준), 86x52mm, 맞춤 50~500mm',
        'delivery' => '매일 출고, 당일판 오전 11시 마감 → 18시 전후 출고(97% 예상)',
        'usecases' => '개인 명함, 기업 명함, 고급 수입지 명함, 당일 급행 명함',
    ],
    'inserted' => [
        'desc' => '합판전단지(저렴/빠름)와 독판전단지(고급/다양한후가공) 선택. 대량 배포에 최적화',
        'features' => '합판: A2/A3/A4/4절/8절/16절, 당일판(아트지 11시마감→18시출고), 독판: A1~16절/2절/4절 다양한사이즈, 오시/미싱/코팅/도무송/박/형압/타공/접지/접착/귀도리/넘버링 후가공',
        'materials' => '아트지 90g(광택/선명), 모조지 80g(무광/경제적), 스노우화이트(무광고급, 독판만)',
        'sizes' => '합판: A2·A3·A4·4절·8절·16절 / 독판: A1~16절, 맞춤',
        'delivery' => '합판 아트지: 당일 출고(11시마감) / 모조지/독판: 익일 출고',
        'usecases' => '길거리 배포 전단지(합판), 고급 브로슈어(독판), 이벤트 안내지, 식당/가게 홍보물',
    ],
    'envelope' => [
        'desc' => '30가지 용지로 제작하는 옵셋 봉투. 각대/소/창봉투 등 9가지 규격 선택',
        'features' => '각대봉투/자켓소봉투/일반소봉투/티켓봉투/6절/8절/9절봉투/A3봉투/창봉투, 양면테이프, 로고인쇄, 레쟈크/크라프트/탄트 등 특수지 선택',
        'materials' => '레쟈크체크백색 110g, 레쟈크줄백색 100g, 모조지 120g/150g, 화일지 120g, 스타펄 120g, 크라프트 98g, 레이드지 120g, 탄트지 120g, 밍크지 120g, 창봉투 모조지 100g',
        'sizes' => '각대봉투 510x387mm, 소봉투 238x262mm, 티켓봉투 225x193mm, 창봉투, A3봉투 등',
        'delivery' => '접수 후 2~3일',
        'usecases' => '기업 서류봉투, 청첩장봉투, 급여봉투, 상품권봉투, 로고인쇄봉투',
    ],
    'littleprint' => [
        'desc' => '벽면/게시판 부착용 포스터 인쇄. 국2절/4x6 2절/4x6 4절, 단독 독판인쇄로 고품질',
        'features' => '단독 독판인쇄(색상정밀), 오시/미싱/코팅(유광/무광)/도무송/박(금박/은박)/형압(엠보싱/디보싱)/타공/접지/접착/귀도리 후가공, 매일 출고',
        'materials' => '아트지(광택/화사, 사진중심), 스노우지무광(차분/고급, 전시/브랜드), 모조지(필기가능/경제적)',
        'sizes' => '국전2절(420x594mm), 4x6 2절, 4x6 4절, 작업사이즈 사방1.5mm여분',
        'delivery' => '매일 출고',
        'usecases' => '행사홍보포스터, 매장홍보물, 전시회포스터(스노우지), 공지게시물(모조지), 금박로고포스터',
    ],
    'merchandisebond' => [
        'desc' => '비코팅/코팅/고품격코팅 상품권 및 티켓 인쇄. 당일판 가능, 넘버링 옵션',
        'features' => '비코팅(필기가능/은은한광택), 코팅216g/250g(내구성), 고품격코팅300g/400g(탄탄), 당일판(11시30분마감→17시출고), 넘버링, 옵셋/디지털/당일판인쇄',
        'materials' => '스노우화이트 비코팅 250g, 코팅 216g/250g, 고품격코팅 300g/400g, 유광코팅 300g',
        'sizes' => '158x72mm, 168x72mm, 172x72mm, 148x68mm(신권), 160x73mm(구권), 맞춤 50~500mm',
        'delivery' => '매일 출고, 당일판 11시30분 마감 → 17시 전후 출고',
        'usecases' => '상품권, 이벤트티켓, 입장권, 쿠폰, 식권, 기프트카드',
    ],
    'cadarok' => [
        'desc' => '리플렛/팜플렛/카다록 인쇄. 중철제본·무선제본, 다양한 용지와 후가공',
        'features' => '중철제본(중간철심), 무선제본(풀붙임), 오시/미싱/코팅/도무송/박/형압/타공/접지 후가공, 접지형 리플렛(2단/3단/4단접지)',
        'materials' => '아트지(광택/선명), 스노우화이트(무광/고급), 모조지(필기가능/경제적), 컬러지',
        'sizes' => 'A4, A5, 3단접지, 맞춤규격',
        'delivery' => '접수 후 2~3일',
        'usecases' => '기업 카탈로그, 제품설명서, 행사 팜플렛, 학원·병원 리플렛, 브랜드 룩북',
    ],
    'ncrflambeau' => [
        'desc' => '무카본 NCR 복사지 및 마스터 양식지 인쇄. 2~4매 세트, 세금계산서/전표/영수증',
        'features' => 'NCR 복사지(압력으로 복사, 무탄소), 마스터 양식지(70~80g 모조지 1~2도인쇄), 백색/황색/적색/청색/녹색 선택, 2~4매 1세트, A4~64절 다양한 사이즈',
        'materials' => 'NCR용지(무카본복사지), 모조지 70g/80g',
        'sizes' => '64절 95x130mm, 48절 85x190mm, 32절 130x190mm, 16절 190x260mm, A5, A4(1/4), A4(1/3), A4',
        'delivery' => '접수 후 2~3일',
        'usecases' => '세금계산서, 거래명세서, 간이영수증, 오더지, 메모지, 입금전표',
    ],
    'msticker' => [
        'desc' => '냉장고·차량 등에 부착하는 종이자석스티커 인쇄. 강력 자력, 맞춤 디자인',
        'features' => '자석지(냉장고/화이트보드/금속부착), 인쇄 후 자석접착, 맞춤규격, 단면인쇄',
        'materials' => '종이자석지(자력있는 고무자석 코팅), 인쇄면 유광/무광',
        'sizes' => '맞춤 규격',
        'delivery' => '접수 후 2~3일',
        'usecases' => '냉장고 광고스티커, 차량 자석스티커, 화이트보드 마킹, 이름표 자석, 업체 홍보용 자석',
    ],
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
    $data = ['contents'=>[['parts'=>[['text'=>$prompt]]]], 'generationConfig'=>['temperature'=>0.9,'maxOutputTokens'=>32768]];
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
 * 카피 생성 - 네이버 광고 검수 가이드라인 적용 + 제품 정보 참조
 */
function genCopy($pk, $pn, $s, $imgPrompt): array {
    global $SECTION_GUIDES, $PRODUCT_INFO;
    $ctx = $SECTION_GUIDES[$s] ?? $SECTION_GUIDES[1];
    $prod = $PRODUCT_INFO[$pk] ?? $PRODUCT_INFO['sticker_new'];
    
    // 제품별 특징
    $prodDesc = $prod['desc'] ?? '';
    $prodFeatures = $prod['features'] ?? '';
    $prodMaterials = $prod['materials'] ?? '';
    $prodSizes = $prod['sizes'] ?? '';
    $prodDelivery = $prod['delivery'] ?? '';
    $prodUsecases = $prod['usecases'] ?? '';

    // 네이버 광고 검수 가이드라인
    $prompt = <<<PROMPT
당신은 두손기획인쇄의 마케팅 카피라이터입니다.
두손기획인쇄는 서울 영등포구 문래동에 위치한 인쇄 전문 업체입니다.

[중요] 아래 네이버 광고 검수 가이드라인을 반드시 준수하세요:

❌ 금지 표현:
- 과대광고: "세계제일", "최고급", "최우수", "업계 최초", "무조건", "반드시"
- 타업체 비교/폄하: "타사 대비", "경쟁사보다", "기타 제품보다"
- 허위과장: "100%", "절대", "무한"

✅ 허용 표현:
- 구체적 사실: "30가지 용지", "97% 당일 출고", "11종 재질"
- 제품 특성: "당일 출고", "방수 재질", "무카본 복사"

[제품명] {$pn}
[제품설명] {$prodDesc}
[주요특징] {$prodFeatures}
[재질옵션] {$prodMaterials}
[제작사이즈] {$prodSizes}
[출고안내] {$prodDelivery}
[활용용도] {$prodUsecases}
[섹션] {$ctx['topic']}
[이미지설명] {$imgPrompt}
[섹션가이드] {$ctx['guide']}

위 제품 정보를 바탕으로 해당 섹션에 맞는 카피를 작성하세요.
헤드카피는 핵심 메시지, 서브카피는 구체적 특징, 상세설명은 고객 혜택 중심으로 작성하세요.

요청: 헤드카피(10-20자), 서브카피(20-30자), 상세설명(50-80자)
JSON으로만 출력: {"headline":"","subheadline":"","description":""}
PROMPT;
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . API_KEY;
    $data = ['contents'=>[['parts'=>[['text'=>$prompt]]]], 'generationConfig'=>['temperature'=>0.7,'maxOutputTokens'=>2048,'responseMimeType'=>'application/json']];
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
    echo "\n✍️ [2/3] 카피 생성 (네이버 광고 검수 + 제품정보 적용)...\n";
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
