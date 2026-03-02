<?php
/**
 * 프리미엄 전단지 생성 서비스 — AI 배경 + mPDF 벡터 텍스트 아키텍처
 *
 * Pipeline: Collector → Copywriter → Background Image → PDF Assembly
 *   Stage 1 (collect):                고객 폼 데이터 → 구조화된 브리프 JSON
 *   Stage 2 (writeCopy):              Gemini Text → 앞/뒷면 카피 JSON
 *   Stage 3 (generateBackgroundImage): Gemini Image → 1~2장 배경 이미지 (텍스트 없음)
 *   Final  (buildPdf):                mPDF로 배경 이미지 위에 벡터 텍스트 오버레이
 *
 * 핵심 원칙:
 *   - AI 이미지는 배경/분위기 전용 (텍스트 절대 포함 안 함)
 *   - 모든 텍스트는 mPDF가 벡터로 렌더링 → 어떤 DPI에서도 선명
 *   - mPDF CSS 제약 준수: flexbox/grid/CSS변수/calc() 사용 불가
 *
 * 모델: gemini-3-pro-preview (텍스트), gemini-3-pro-image-preview (이미지)
 * 의존: mPDF 8.x (PDF 생성), PHP GD (폴백 배경)
 *
 * @since 2026-03-02
 */

class FlyerPremiumService
{
    /** @var string Gemini API key */
    private $apiKey;

    /** @var string 텍스트 생성 모델 */
    private $textModel = 'gemini-3-pro-preview';

    /** @var string 이미지 생성 모델 */
    private $imageModel = 'gemini-3-pro-image-preview';

    /** @var string Gemini API base URL */
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /** @var string 이미지 업로드 루트 */
    private $uploadDir;

    /** @var callable|null 상태 보고 콜백 */
    private $statusCallback;

    /** @var int 배경 이미지 너비 (px) — imageConfig 2K + 3:4 = 1792px */
    const IMAGE_WIDTH = 1792;

    /** @var int 배경 이미지 높이 (px) — imageConfig 2K + 3:4 = 2400px */
    const IMAGE_HEIGHT = 2400;

    /** @var string 이미지 프롬프트 접미사 — 텍스트 금지 강제 */
    const PROMPT_SUFFIX = 'No text, no letters, no words, no numbers, no characters, no logos. Photorealistic, professional print quality, soft focus background suitable for text overlay.';

    /**
     * @param string|null $apiKey Gemini API 키 (null이면 .env에서 로드)
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey ? $apiKey : $this->loadApiKey();
        $this->uploadDir = __DIR__ . '/../uploads/ai/';

        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }

    // ════════════════════════════════════════════════════════════
    //  Public API
    // ════════════════════════════════════════════════════════════

    /**
     * 프리미엄 전단지 전체 파이프라인 실행
     *
     * @param array    $formData  폼 입력 데이터
     * @param callable $onStatus  상태 콜백 fn(string $stage, string $message, int $progress)
     * @return array 성공: ['success'=>true, 'pdf_url'=>'...', 'preview_images'=>[...]] / 실패: ['error'=>'...']
     */
    public function generate(array $formData, callable $onStatus)
    {
        $this->statusCallback = $onStatus;
        $sessionId = $this->generateSessionId();
        $sessionDir = $this->uploadDir . $sessionId . '/';

        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0755, true);
        }

        $doubleSided = !empty($formData['double_sided']);

        try {
            // ── Stage 1: Collector (no LLM) ──
            $this->reportStatus('collect', '📋 고객 정보를 정리하고 있습니다...', 0);
            $brief = $this->collect($formData);
            $brief['double_sided'] = $doubleSided;

            // ── Stage 2: Copywriter (Gemini text) ──
            $this->reportStatus('copywrite', '✍️ AI가 전단지 문구를 작성하고 있습니다...', 10);
            $copy = $this->writeCopy($brief);

            if (isset($copy['error'])) {
                return ['error' => '카피라이팅 실패: ' . $copy['error']];
            }

            // ── Stage 3: Background Image Generation ──
            $this->reportStatus('generate_image', '🖼️ 배경 이미지를 생성하고 있습니다 (앞면)...', 30);
            $frontBgPath = $this->generateBackgroundImage($brief, 'front');

            if ($frontBgPath === null) {
                $frontBgPath = $sessionDir . 'bg_front_fallback.jpg';
                $this->createFallbackBackground($frontBgPath, $brief['color_preset']);
            } else {
                $dest = $sessionDir . 'bg_front.jpg';
                @copy($frontBgPath, $dest);
                @unlink($frontBgPath);
                $frontBgPath = $dest;
            }

            $backBgPath = null;
            if ($doubleSided) {
                $this->reportStatus('generate_image', '🖼️ 배경 이미지를 생성하고 있습니다 (뒷면)...', 60);
                $backBgPath = $this->generateBackgroundImage($brief, 'back');

                if ($backBgPath === null) {
                    $backBgPath = $sessionDir . 'bg_back_fallback.jpg';
                    $this->createFallbackBackground($backBgPath, $brief['color_preset']);
                } else {
                    $dest = $sessionDir . 'bg_back.jpg';
                    @copy($backBgPath, $dest);
                    @unlink($backBgPath);
                    $backBgPath = $dest;
                }
            }

            // ── Final: PDF Assembly (mPDF) ──
            $this->reportStatus('assemble', '📄 전단지를 조립하고 있습니다...', 80);
            $pdfPath = $this->buildPdf($brief, $copy, $frontBgPath, $backBgPath, $sessionDir);

            $this->reportStatus('complete', '✅ 완성! 다운로드 준비 중...', 100);

            // 웹 접근용 상대 경로
            $relativeDir = 'uploads/ai/' . $sessionId . '/';

            $previewImages = [];
            if ($frontBgPath && file_exists($frontBgPath)) {
                $previewImages[] = $relativeDir . basename($frontBgPath);
            }
            if ($backBgPath && file_exists($backBgPath)) {
                $previewImages[] = $relativeDir . basename($backBgPath);
            }

            return [
                'success'        => true,
                'pdf_url'        => $relativeDir . basename($pdfPath),
                'preview_images' => $previewImages,
                'data'           => [
                    'session_id' => $sessionId,
                    'brief'      => $brief,
                    'copy'       => $copy,
                ],
            ];
        } catch (\Exception $e) {
            error_log('[FlyerPremiumService] Pipeline exception: ' . $e->getMessage());
            return ['error' => '파이프라인 오류: ' . $e->getMessage()];
        }
    }

    /**
     * API 키가 설정되어 있는지 확인
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->apiKey);
    }

    // ════════════════════════════════════════════════════════════
    //  Stage 1 — Collector (no LLM)
    // ════════════════════════════════════════════════════════════

    /**
     * 고객 폼 데이터를 구조화된 브리프로 변환
     *
     * @param array $formData 폼 입력
     * @return array 구조화된 브리프
     */
    private function collect(array $formData)
    {
        $industry = trim(isset($formData['industry']) ? $formData['industry'] : 'general');
        $colorPreset = $this->getColorPreset($industry);

        return [
            'business_name'    => trim(isset($formData['business_name']) ? $formData['business_name'] : ''),
            'industry'         => $industry,
            'industry_name'    => $colorPreset['name'],
            'phone'            => trim(isset($formData['phone']) ? $formData['phone'] : ''),
            'address'          => trim(isset($formData['address']) ? $formData['address'] : ''),
            'menu_items'       => isset($formData['menu_items']) ? $formData['menu_items'] : '',
            'promotion'        => trim(isset($formData['promotion']) ? $formData['promotion'] : ''),
            'features'         => trim(isset($formData['features']) ? $formData['features'] : ''),
            'hours'            => trim(isset($formData['hours']) ? $formData['hours'] : ''),
            'style_preference' => trim(isset($formData['style_preference']) ? $formData['style_preference'] : 'modern'),
            'color_preset'     => $colorPreset,
            'double_sided'     => !empty($formData['double_sided']),
        ];
    }

    // ════════════════════════════════════════════════════════════
    //  Stage 2 — Copywriter (Gemini Text)
    // ════════════════════════════════════════════════════════════

    /**
     * Gemini로 전단지 카피 생성 — 앞면/뒷면 통합 JSON
     *
     * @param array $brief Stage 1 브리프
     * @return array 앞면/뒷면 카피 데이터 또는 ['error'=>'...']
     */
    private function writeCopy(array $brief)
    {
        $businessName = $brief['business_name'];
        $industryName = $brief['industry_name'];
        $menuItems    = is_array($brief['menu_items']) ? implode(', ', $brief['menu_items']) : $brief['menu_items'];
        $promotion    = $brief['promotion'];
        $features     = $brief['features'];
        $hours        = $brief['hours'];
        $phone        = $brief['phone'];
        $address      = $brief['address'];
        $doubleSided  = $brief['double_sided'];

        $systemPrompt = '당신은 대한민국 최고의 전단지 카피라이터입니다.' . "\n"
            . '동네 소상공인의 A4 전단지를 만들어 주세요.' . "\n"
            . '고객이 읽자마자 "여기 가봐야겠다" 느끼게 만드세요.' . "\n\n"
            . '작성 규칙:' . "\n"
            . '- 모든 텍스트는 한국어만 사용' . "\n"
            . '- 따뜻하고 친근한 톤' . "\n"
            . '- 헤드라인은 15자 이내' . "\n"
            . '- 과장 없이 신뢰감 있게' . "\n"
            . '- 이모지/특수문자 사용 금지' . "\n"
            . '- 가격은 2025-2026년 대한민국 실제 시세 반영';

        $backSection = '';
        if ($doubleSided) {
            $backSection = "\n\n"
                . '## 뒷면 (back)' . "\n"
                . '- features: 특장점 3개 (각각 title + description)' . "\n"
                . '- hours: 영업시간 (이 업종의 실제 표준 영업시간)' . "\n"
                . '- additional_info: 부가 정보 (배달 가능, 주차 안내 등)' . "\n\n"
                . '## 뒷면 응답 형식' . "\n"
                . '"back":{"features":[{"title":"20자이내","description":"40자이내"},...],"hours":"영업시간","additional_info":"부가정보"}';
        }

        $userPrompt = '## 의뢰 정보' . "\n"
            . '- 상호명: ' . $businessName . "\n"
            . '- 업종: ' . $industryName . "\n"
            . '- 전화: ' . $phone . "\n"
            . '- 주소: ' . $address . "\n"
            . '- 메뉴/서비스: ' . $menuItems . "\n"
            . '- 프로모션: ' . $promotion . "\n"
            . '- 특장점: ' . $features . "\n"
            . '- 영업시간: ' . $hours . "\n\n"
            . '## 앞면 (front)' . "\n"
            . '- headline: 상호명 (그대로 사용)' . "\n"
            . '- tagline: 캐치프레이즈 (15자 이내, 운율감 있게)' . "\n"
            . '- menu_items: 대표 메뉴/서비스 7~10개 [{name, price}]' . "\n"
            . '  가격은 "8,000원" 형식. 이 업종의 실제 시세 반영' . "\n"
            . '- promotion: 프로모션 문구 1줄 (30자 이내, 현실적 할인)' . "\n"
            . '- contact: {phone, address} — 의뢰 정보 그대로 사용' . "\n"
            . '- cta: 행동 유도 문구 (15자 이내)'
            . $backSection . "\n\n"
            . '## 응답 형식 (반드시 아래 JSON만 출력)' . "\n"
            . '{"front":{"headline":"상호명","tagline":"캐치프레이즈","menu_items":[{"name":"메뉴명","price":"가격"}],"promotion":"프로모션","contact":{"phone":"전화","address":"주소"},"cta":"행동유도"}'
            . ($doubleSided ? ',"back":{"features":[{"title":"제목","description":"설명"}],"hours":"영업시간","additional_info":"부가정보"}' : '')
            . '}';

        $responseText = $this->callGeminiText($systemPrompt, $userPrompt);

        if ($responseText === null) {
            return ['error' => 'Gemini 텍스트 API 호출 실패'];
        }

        $parsed = $this->parseJsonResponse($responseText);

        if ($parsed === null || !isset($parsed['front'])) {
            return ['error' => 'AI 응답을 JSON으로 파싱할 수 없습니다.'];
        }

        // front.contact 폴백: AI가 연락처를 변조할 수 있으므로 원본 우선
        if (!isset($parsed['front']['contact'])) {
            $parsed['front']['contact'] = [];
        }
        if (!empty($phone)) {
            $parsed['front']['contact']['phone'] = $phone;
        }
        if (!empty($address)) {
            $parsed['front']['contact']['address'] = $address;
        }

        // headline 폴백
        if (empty($parsed['front']['headline'])) {
            $parsed['front']['headline'] = $businessName;
        }

        // 뒷면 폴백
        if ($doubleSided && !isset($parsed['back'])) {
            $parsed['back'] = [
                'features' => [],
                'hours' => $hours ? $hours : '매일 10:00 - 22:00',
                'additional_info' => '',
            ];
        }

        return $parsed;
    }

    // ════════════════════════════════════════════════════════════
    //  Stage 3 — Background Image Generator
    // ════════════════════════════════════════════════════════════

    /**
     * 배경 이미지 생성 (텍스트 없는 분위기/무드 사진만)
     *
     * @param array  $brief 브리프 데이터
     * @param string $page  'front' 또는 'back'
     * @return string|null 성공 시 파일 경로, 실패 시 null
     */
    private function generateBackgroundImage(array $brief, $page)
    {
        $industryName = $brief['industry_name'];
        $industry = $brief['industry'];
        $style = $brief['style_preference'];
        $colorPreset = $brief['color_preset'];

        $sceneDesc = $this->getSceneDescription($industry);

        if ($page === 'front') {
            $prompt = 'Create a high-quality atmospheric background photograph for a Korean '
                . $industryName . ' business promotional flyer. '
                . 'Scene: ' . $sceneDesc . ' '
                . 'Style: ' . $style . ', warm inviting atmosphere, shallow depth of field. '
                . 'Color palette hints: primary ' . $colorPreset['primary']
                . ', secondary ' . $colorPreset['secondary'] . '. '
                . 'The image will have dark text overlaid on top, so ensure good contrast areas. '
                . 'Portrait A4 aspect ratio. '
                . self::PROMPT_SUFFIX;
        } else {
            $prompt = 'Create a soft, lighter background photograph for the back page of a Korean '
                . $industryName . ' business flyer. '
                . 'Scene: A complementary, softer version — clean workspace, detail shot, or abstract texture related to ' . $sceneDesc . ' '
                . 'Style: ' . $style . ', subtle and elegant, lots of light areas for text readability. '
                . 'Color palette hints: primary ' . $colorPreset['primary']
                . ', secondary ' . $colorPreset['secondary'] . '. '
                . 'Lighter overall tone than the front — text will be dark colored. '
                . 'Portrait A4 aspect ratio. '
                . self::PROMPT_SUFFIX;
        }

        return $this->callGeminiImage($prompt);
    }

    // ════════════════════════════════════════════════════════════
    //  PDF Assembly (mPDF — 벡터 텍스트 오버레이)
    // ════════════════════════════════════════════════════════════

    /**
     * mPDF로 배경 이미지 + 벡터 텍스트 오버레이 PDF 생성
     *
     * @param array       $brief       브리프 데이터
     * @param array       $copy        카피 데이터 (front/back)
     * @param string|null $frontBgPath 앞면 배경 이미지 경로
     * @param string|null $backBgPath  뒷면 배경 이미지 경로
     * @param string      $sessionDir  세션 디렉토리
     * @return string PDF 파일 경로
     */
    private function buildPdf(array $brief, array $copy, $frontBgPath, $backBgPath, $sessionDir)
    {
        // mPDF autoload
        $autoloadPath = '';
        if (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
            $autoloadPath = $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
        }
        if (empty($autoloadPath) || !file_exists($autoloadPath)) {
            $autoloadPath = realpath(__DIR__ . '/../../../../vendor/autoload.php');
        }

        require_once $autoloadPath;

        $pdfPath = $sessionDir . 'flyer_premium_' . date('Ymd_His') . '.pdf';

        // mPDF 임시 디렉토리
        $mpdfTempDir = sys_get_temp_dir() . '/mpdf';
        if (!is_dir($mpdfTempDir)) {
            @mkdir($mpdfTempDir, 0755, true);
        }

        // 한글 폰트 설정 (generate.php 패턴 준수)
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // A4: 210mm x 297mm, full-bleed (마진 0)
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'default_font'  => 'nanumgothic',
            'tempDir'       => $mpdfTempDir,
            'fontDir'       => $fontDirs,
            'fontdata'      => $fontData + [
                'nanumgothic' => [
                    'R' => 'NanumGothic.ttf',
                    'B' => 'NanumGothicBold.ttf',
                ],
            ],
        ]);

        $mpdf->SetDisplayMode('fullpage');

        $colors = $brief['color_preset'];

        // ── Page 1: Front ──
        // Place background image at page origin using Image() — covers full A4
        if ($frontBgPath && file_exists($frontBgPath)) {
            $mpdf->Image($frontBgPath, 0, 0, 210, 297, 'jpg', '', true, false);
        }
        $frontHtml = $this->buildFrontPageHtml($brief, $copy['front'], $colors);
        $mpdf->WriteHTML($frontHtml);

        // ── Page 2: Back (optional) ──
        if ($brief['double_sided'] && $backBgPath && isset($copy['back'])) {
            $mpdf->AddPage();
            if (file_exists($backBgPath)) {
                $mpdf->Image($backBgPath, 0, 0, 210, 297, 'jpg', '', true, false);
            }
            $backHtml = $this->buildBackPageHtml($brief, $copy['back'], $colors);
            $mpdf->WriteHTML($backHtml);
        }

        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);

        return $pdfPath;
    }

    /**
     * 앞면 HTML 생성 — mPDF 호환 (normal flow, no absolute positioning)
     * Background image is set via mPDF's SetDefaultBodyCSS in buildPdf().
     * Dark overlay is achieved via semi-transparent background on content divs.
     *
     * @param array  $brief   브리프
     * @param array  $front   앞면 카피 데이터
     * @param array  $colors  컬러 프리셋
     * @return string HTML 문자열
     */
    private function buildFrontPageHtml(array $brief, array $front, array $colors)
    {
        $businessName = htmlspecialchars($brief['business_name']);
        $tagline = htmlspecialchars(isset($front['tagline']) ? $front['tagline'] : '');
        $promotion = htmlspecialchars(isset($front['promotion']) ? $front['promotion'] : '');
        $cta = htmlspecialchars(isset($front['cta']) ? $front['cta'] : '');
        $phone = htmlspecialchars(isset($front['contact']['phone']) ? $front['contact']['phone'] : $brief['phone']);
        $address = htmlspecialchars(isset($front['contact']['address']) ? $front['contact']['address'] : $brief['address']);

        $primary = htmlspecialchars($colors['primary']);
        $secondary = htmlspecialchars($colors['secondary']);
        $accent = isset($colors['accent']) ? htmlspecialchars($colors['accent']) : $secondary;

        // 메뉴 아이템 HTML — cap at 6 to prevent overflow
        $menuItems = isset($front['menu_items']) ? $front['menu_items'] : [];
        if (count($menuItems) > 6) {
            $menuItems = array_slice($menuItems, 0, 6);
        }
        $menuHtml = $this->buildMenuTableHtml($menuItems, $secondary);

        // === Normal-flow HTML (no absolute positioning) ===
        // Background image is rendered by mPDF natively.
        // Dark overlay achieved via semi-transparent div backgrounds.
        $html = '';

        // Dark overlay — full-page semi-transparent background
        $html .= '<div style="background:rgba(0,0,0,0.40); margin:0; padding:12mm 15mm;">';

        // Top accent bar
        $html .= '<div style="background:' . $primary . '; height:2mm; width:100%; margin-bottom:6mm;"></div>';

        // Business name
        $html .= '<div style="text-align:center; margin-bottom:4mm;">';
        $html .= '<div style="font-size:28pt; font-weight:bold; color:#FFFFFF; letter-spacing:2px; line-height:1.3;">'
            . $businessName . '</div>';

        if ($tagline !== '') {
            $html .= '<div style="font-size:13pt; color:' . $secondary . '; margin-top:3mm; font-weight:bold;">'
                . $tagline . '</div>';
        }
        $html .= '</div>';

        // Promotion banner
        if ($promotion !== '') {
            $html .= '<div style="background:' . $accent . '; color:#FFFFFF; font-size:11pt; font-weight:bold; '
                . 'text-align:center; padding:2mm 5mm; margin:3mm 0;">'
                . $promotion . '</div>';
        }

        // Menu section
        if (!empty($menuItems)) {
            $html .= '<div style="border-top:0.5mm solid rgba(255,255,255,0.3); margin:4mm 0 3mm 0;"></div>';
            $html .= '<div style="font-size:12pt; font-weight:bold; color:' . $secondary . '; text-align:center; margin-bottom:3mm;">MENU</div>';
            $html .= $menuHtml;
        }

        // Spacer to push contact info toward bottom
        $html .= '<br />';

        // Contact section
        $html .= '<div style="border-top:0.5mm solid rgba(255,255,255,0.3); padding-top:4mm; margin-top:8mm;">';
        if ($phone !== '') {
            $html .= '<div style="font-size:16pt; color:#FFFFFF; font-weight:bold; margin-bottom:1mm;">'
                . 'TEL ' . $phone . '</div>';
        }
        if ($address !== '') {
            $html .= '<div style="font-size:10pt; color:rgba(255,255,255,0.85); margin-bottom:2mm;">'
                . $address . '</div>';
        }
        if ($cta !== '') {
            $html .= '<div style="font-size:12pt; color:' . $secondary . '; font-weight:bold; margin-top:2mm;">'
                . $cta . '</div>';
        }
        $html .= '</div>';

        // Bottom accent bar
        $html .= '<div style="background:' . $primary . '; height:2mm; width:100%; margin-top:4mm;"></div>';

        $html .= '</div>'; // close dark overlay div

        return $html;
    }

    /**
     * 뒷면 HTML 생성 — mPDF 호환 (normal flow, no absolute positioning)
     * Background image is set via Image() in buildPdf().
     *
     * @param array  $brief   브리프
     * @param array  $back    뒷면 카피 데이터
     * @param array  $colors  컬러 프리셋
     * @return string HTML 문자열
     */
    private function buildBackPageHtml(array $brief, array $back, array $colors)
    {
        $businessName = htmlspecialchars($brief['business_name']);
        $phone = htmlspecialchars($brief['phone']);
        $address = htmlspecialchars($brief['address']);

        $primary = htmlspecialchars($colors['primary']);
        $secondary = htmlspecialchars($colors['secondary']);

        $features = isset($back['features']) ? $back['features'] : [];
        $hours = htmlspecialchars(isset($back['hours']) ? $back['hours'] : '');
        $additionalInfo = htmlspecialchars(isset($back['additional_info']) ? $back['additional_info'] : '');

        $featuresHtml = $this->buildFeaturesHtml($features, $primary, $secondary);

        // Normal-flow HTML — background image placed via Image() in buildPdf()
        $html = '<div style="background:rgba(255,255,255,0.75); padding:15mm;">';

        // Top accent bar
        $html .= '<div style="background:' . $primary . '; height:2mm; width:100%; margin-bottom:10mm;"></div>';

        // Business name
        $html .= '<div style="text-align:center; margin-bottom:10mm;">'
            . '<div style="font-size:24pt; font-weight:bold; color:' . $primary . ';">'
            . $businessName . '</div></div>';

        // Features
        if (!empty($features)) {
            $html .= '<div style="font-size:16pt; font-weight:bold; color:' . $primary . '; margin-bottom:6mm; '
                . 'border-left:3mm solid ' . $secondary . '; padding-left:4mm;">이런 점이 다릅니다</div>'
                . $featuresHtml
                . '<div style="margin-bottom:8mm;"></div>';
        }

        // Hours
        if ($hours !== '') {
            $html .= '<div style="font-size:16pt; font-weight:bold; color:' . $primary . '; margin-bottom:4mm; '
                . 'border-left:3mm solid ' . $secondary . '; padding-left:4mm;">영업시간</div>'
                . '<div style="font-size:13pt; color:#333333; margin-bottom:8mm; padding-left:7mm;">'
                . $hours . '</div>';
        }

        // Additional info
        if ($additionalInfo !== '') {
            $html .= '<div style="background:rgba(0,0,0,0.05); padding:4mm 6mm; font-size:11pt; color:#555555; margin-bottom:8mm;">'
                . $additionalInfo . '</div>';
        }

        // Contact section
        $html .= '<div style="border-top:0.5mm solid ' . $primary . '; padding-top:5mm; text-align:center; margin-top:10mm;">';
        if ($phone !== '') {
            $html .= '<div style="font-size:16pt; font-weight:bold; color:' . $primary . '; margin-bottom:2mm;">'
                . 'TEL ' . $phone . '</div>';
        }
        if ($address !== '') {
            $html .= '<div style="font-size:11pt; color:#555555;">' . $address . '</div>';
        }
        $html .= '</div>';

        // Bottom accent bar
        $html .= '<div style="background:' . $primary . '; height:2mm; width:100%; margin-top:5mm;"></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * 메뉴 아이템 테이블 HTML 생성 (자동 1/2컬럼 전환)
     *
     * @param array  $menuItems [{name, price}, ...]
     * @param string $primary   프라이머리 색상
     * @return string HTML 테이블
     */
    private function buildMenuTableHtml(array $menuItems, $primary)
    {
        if (empty($menuItems)) {
            return '';
        }

        $total = count($menuItems);
        $useTwoColumns = ($total > 5);

        if ($useTwoColumns) {
            $half = (int)ceil($total / 2);
            $leftItems = array_slice($menuItems, 0, $half);
            $rightItems = array_slice($menuItems, $half);

            $html = '<table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0"><tr>'
                . '<td width="48%" valign="top">' . $this->buildSingleMenuColumn($leftItems, $primary) . '</td>'
                . '<td width="4%"></td>'
                . '<td width="48%" valign="top">' . $this->buildSingleMenuColumn($rightItems, $primary) . '</td>'
                . '</tr></table>';

            return $html;
        }

        return $this->buildSingleMenuColumn($menuItems, $primary);
    }

    /**
     * 단일 메뉴 칼럼 HTML
     *
     * @param array  $items   [{name, price}, ...]
     * @param string $primary 색상
     * @return string HTML
     */
    private function buildSingleMenuColumn(array $items, $primary)
    {
        $html = '<table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">';

        foreach ($items as $i => $item) {
            $name = htmlspecialchars(isset($item['name']) ? $item['name'] : '');
            $price = htmlspecialchars(isset($item['price']) ? $item['price'] : '');
            $bgColor = ($i % 2 === 0) ? 'rgba(255,255,255,0.12)' : 'rgba(255,255,255,0.06)';

            $html .= '<tr style="background:' . $bgColor . ';">'
                . '<td style="padding:1.5mm 3mm; font-size:9pt; color:#FFFFFF;">' . $name . '</td>'
                . '<td style="padding:1.5mm 3mm; font-size:9pt; font-weight:bold; color:'
                . htmlspecialchars($primary) . '; text-align:right; white-space:nowrap;">' . $price . '</td>'
                . '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * 특장점 HTML 생성 (뒷면용 — 번호 배지 + 제목/설명)
     *
     * @param array  $features  [{title, description}, ...]
     * @param string $primary   프라이머리 색상
     * @param string $secondary 세컨더리 색상
     * @return string HTML
     */
    private function buildFeaturesHtml(array $features, $primary, $secondary)
    {
        if (empty($features)) {
            return '';
        }

        $html = '<table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">';

        foreach ($features as $i => $feat) {
            $title = htmlspecialchars(isset($feat['title']) ? $feat['title'] : '');
            $desc = htmlspecialchars(isset($feat['description']) ? $feat['description'] : '');
            $num = $i + 1;

            $html .= '<tr>'
                . '<td width="12mm" valign="top" style="padding:3mm 2mm;">'
                . '<div style="background:' . htmlspecialchars($primary) . '; color:#FFFFFF; font-size:12pt; font-weight:bold; '
                . 'text-align:center; width:10mm; height:10mm; line-height:10mm;">' . $num . '</div>'
                . '</td>'
                . '<td valign="top" style="padding:3mm 2mm;">'
                . '<div style="font-size:13pt; font-weight:bold; color:#222222;">' . $title . '</div>'
                . '<div style="font-size:10pt; color:#555555; margin-top:1mm;">' . $desc . '</div>'
                . '</td></tr>';

            if ($i < count($features) - 1) {
                $html .= '<tr><td colspan="2" style="padding:0;">'
                    . '<div style="border-top:0.3mm solid #DDDDDD; margin:2mm 0;"></div></td></tr>';
            }
        }

        $html .= '</table>';
        return $html;
    }

    // ════════════════════════════════════════════════════════════
    //  Gemini API Helpers
    // ════════════════════════════════════════════════════════════

    /**
     * Gemini Text API 호출 (JSON 응답 모드)
     *
     * @param string $systemPrompt 시스템 프롬프트
     * @param string $userPrompt   사용자 프롬프트
     * @return string|null 성공 시 텍스트, 실패 시 null
     */
    private function callGeminiText($systemPrompt, $userPrompt)
    {
        $url = $this->baseUrl . $this->textModel . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $userPrompt]],
                ],
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig' => [
                'temperature'      => 0.7,
                'maxOutputTokens'  => 8192,
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = $this->curlPost($url, $payload, 120);

        if ($response === null || isset($response['error'])) {
            $msg = isset($response['error']['message']) ? $response['error']['message'] : 'Unknown error';
            error_log('[FlyerPremiumService] Gemini text error: ' . $msg);
            return null;
        }

        $text = isset($response['candidates'][0]['content']['parts'][0]['text'])
            ? $response['candidates'][0]['content']['parts'][0]['text']
            : null;

        return $text;
    }

    /**
     * Gemini Image API 호출 — imageConfig로 2K 3:4 (1792x2400px) 생성
     *
     * @param string $prompt 이미지 프롬프트
     * @return string|null 성공 시 저장된 파일 경로, 실패 시 null
     */
    private function callGeminiImage($prompt)
    {
        $url = $this->baseUrl . $this->imageModel . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
                'imageConfig' => [
                    'aspectRatio' => '3:4',
                    'imageSize'   => '2K',
                ],
            ],
        ];

        $response = $this->curlPost($url, $payload, 180);

        if ($response === null || isset($response['error'])) {
            $msg = isset($response['error']['message']) ? $response['error']['message'] : 'Unknown error';
            error_log('[FlyerPremiumService] Gemini image error: ' . $msg);
            return null;
        }

        // 이미지 데이터 추출
        $parts = isset($response['candidates'][0]['content']['parts'])
            ? $response['candidates'][0]['content']['parts']
            : [];

        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $mimeType   = isset($part['inlineData']['mimeType']) ? $part['inlineData']['mimeType'] : 'image/jpeg';
                $base64Data = isset($part['inlineData']['data']) ? $part['inlineData']['data'] : '';

                if (empty($base64Data)) {
                    continue;
                }

                $imageData = base64_decode($base64Data);
                if ($imageData === false) {
                    continue;
                }

                // 실제 형식 감지 (Gemini가 mimeType을 잘못 보고할 수 있음)
                $ext = 'jpg';
                if (strlen($imageData) >= 8 && substr($imageData, 0, 4) === "\x89PNG") {
                    $ext = 'png';
                }

                $filename = 'premium_bg_' . uniqid() . '.' . $ext;
                $filePath = $this->uploadDir . $filename;

                if (file_put_contents($filePath, $imageData) !== false) {
                    return $filePath;
                }
            }
        }

        return null;
    }

    /**
     * cURL POST 공통 래퍼
     *
     * @param string $url     요청 URL
     * @param array  $payload JSON 페이로드
     * @param int    $timeout 타임아웃 (초)
     * @return array|null 파싱된 JSON 응답
     */
    private function curlPost($url, array $payload, $timeout = 120)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[FlyerPremiumService] cURL error: ' . $curlError);
            return null;
        }

        if ($httpCode !== 200) {
            error_log('[FlyerPremiumService] HTTP ' . $httpCode . ': ' . substr($response, 0, 500));
            $decoded = json_decode($response, true);
            return $decoded ? $decoded : null;
        }

        $result = json_decode($response, true);
        return $result ? $result : null;
    }

    // ════════════════════════════════════════════════════════════
    //  Fallback & Helpers
    // ════════════════════════════════════════════════════════════

    /**
     * 이미지 생성 실패 시 그래디언트 폴백 배경 생성 (GD)
     *
     * @param string $outputPath 출력 경로
     * @param array  $colorPreset ['primary'=>'#hex', 'secondary'=>'#hex']
     * @return string 출력 파일 경로
     */
    private function createFallbackBackground($outputPath, array $colorPreset)
    {
        $width = self::IMAGE_WIDTH;
        $height = self::IMAGE_HEIGHT;

        $img = imagecreatetruecolor($width, $height);

        if ($img === false) {
            // 최소 폴백
            $img = imagecreatetruecolor(100, 100);
            $gray = imagecolorallocate($img, 80, 80, 80);
            imagefill($img, 0, 0, $gray);
            imagejpeg($img, $outputPath, 90);
            imagedestroy($img);
            return $outputPath;
        }

        $topColor = $this->hexToRgb(isset($colorPreset['primary']) ? $colorPreset['primary'] : '#37474F');
        $botColor = $this->hexToRgb(isset($colorPreset['secondary']) ? $colorPreset['secondary'] : '#78909C');

        // 세로 그래디언트
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = intval($topColor['r'] + ($botColor['r'] - $topColor['r']) * $ratio);
            $g = intval($topColor['g'] + ($botColor['g'] - $topColor['g']) * $ratio);
            $b = intval($topColor['b'] + ($botColor['b'] - $topColor['b']) * $ratio);

            $color = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width - 1, $y, $color);
        }

        imagejpeg($img, $outputPath, 90);
        imagedestroy($img);

        return $outputPath;
    }

    /**
     * HEX 색상 → RGB 배열 변환
     *
     * @param string $hex '#RRGGBB' 형식
     * @return array ['r'=>int, 'g'=>int, 'b'=>int]
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * JSON 응답 텍스트 파싱 (마크다운 래핑 제거 포함)
     *
     * @param string $text Gemini 응답 텍스트
     * @return array|null 파싱된 JSON 또는 null
     */
    private function parseJsonResponse($text)
    {
        $text = trim($text);

        // ```json ... ``` 래핑 제거
        $text = preg_replace('/^```json\s*/s', '', $text);
        $text = preg_replace('/\s*```$/s', '', $text);

        $parsed = json_decode($text, true);
        if ($parsed !== null) {
            return $parsed;
        }

        // JSON 범위 추출 시도
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonStr = substr($text, $start, $end - $start + 1);
            $jsonStr = preg_replace('/,\s*([}\]])/', '$1', $jsonStr);

            $parsed = json_decode($jsonStr, true);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * 상태 보고
     *
     * @param string $stage    단계명
     * @param string $message  한글 메시지
     * @param int    $progress 진행률 (0-100)
     */
    private function reportStatus($stage, $message, $progress)
    {
        if ($this->statusCallback !== null && is_callable($this->statusCallback)) {
            call_user_func($this->statusCallback, $stage, $message, $progress);
        }
    }

    /**
     * 세션 ID 생성 (타임스탬프 기반)
     * @return string
     */
    private function generateSessionId()
    {
        return date('Ymd_His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
    }

    /**
     * .env에서 GEMINI_API_KEY 로드
     * @return string
     */
    private function loadApiKey()
    {
        $key = isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : getenv('GEMINI_API_KEY');
        if ($key) {
            return $key;
        }

        $envFile = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/.env' : '';
        if (empty($envFile) || !file_exists($envFile)) {
            $envFile = realpath(__DIR__ . '/../../../../.env');
        }

        if ($envFile && file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#') {
                        continue;
                    }
                    if (strpos($line, 'GEMINI_API_KEY=') === 0) {
                        return trim(substr($line, strlen('GEMINI_API_KEY=')));
                    }
                }
            }
        }

        return '';
    }

    /**
     * 업종별 이미지 장면 설명
     * @param string $industry
     * @return string
     */
    private function getSceneDescription($industry)
    {
        $scenes = [
            'korean'   => 'Warm Korean restaurant interior with steaming dishes, wooden tables, traditional decor, golden warm lighting',
            'japanese' => 'Clean elegant Japanese restaurant with sushi counter, minimalist design, soft ambient light',
            'chinese'  => 'Vibrant Chinese restaurant scene with rich red accents, steaming wok dishes, ornate decorations',
            'western'  => 'Sophisticated Western cafe or bistro, ambient lighting, modern interior with coffee and pastries',
            'chicken'  => 'Casual Korean pub atmosphere, crispy golden fried chicken, beer glasses, warm casual lighting',
            'academy'  => 'Bright modern classroom or study space, clean desks, educational atmosphere, natural light',
            'fitness'  => 'Modern gym interior with professional equipment, energetic atmosphere, clean well-lit space',
            'beauty'   => 'Elegant beauty salon or spa interior, soft lighting, luxurious treatment chairs, professional tools',
            'general'  => 'Professional, welcoming business interior, clean modern design, warm lighting',
        ];

        return isset($scenes[$industry]) ? $scenes[$industry] : $scenes['general'];
    }

    // ════════════════════════════════════════════════════════════
    //  Industry Color Presets
    // ════════════════════════════════════════════════════════════

    /**
     * 업종별 컬러 프리셋 반환
     *
     * @param string $industry 업종 키
     * @return array ['primary'=>'#hex', 'secondary'=>'#hex', 'accent'=>'#hex', 'name'=>'한글명']
     */
    private function getColorPreset($industry)
    {
        $presets = [
            'korean'   => ['primary' => '#D32F2F', 'secondary' => '#FF8A65', 'accent' => '#FF5722', 'name' => '한식'],
            'japanese' => ['primary' => '#1A237E', 'secondary' => '#90CAF9', 'accent' => '#3F51B5', 'name' => '일식'],
            'chinese'  => ['primary' => '#E65100', 'secondary' => '#FFD54F', 'accent' => '#FF9800', 'name' => '중식'],
            'western'  => ['primary' => '#4E342E', 'secondary' => '#BCAAA4', 'accent' => '#795548', 'name' => '양식/카페'],
            'chicken'  => ['primary' => '#F57F17', 'secondary' => '#D32F2F', 'accent' => '#FF6F00', 'name' => '치킨'],
            'academy'  => ['primary' => '#1565C0', 'secondary' => '#42A5F5', 'accent' => '#2196F3', 'name' => '학원'],
            'fitness'  => ['primary' => '#2E7D32', 'secondary' => '#66BB6A', 'accent' => '#4CAF50', 'name' => '피트니스'],
            'beauty'   => ['primary' => '#AD1457', 'secondary' => '#F48FB1', 'accent' => '#E91E63', 'name' => '뷰티'],
            'general'  => ['primary' => '#37474F', 'secondary' => '#78909C', 'accent' => '#546E7A', 'name' => '일반'],
        ];

        return isset($presets[$industry]) ? $presets[$industry] : $presets['general'];
    }
}
