<?php
/**
 * 테마 로더 - Excel 스타일 테마 통합 시스템
 *
 * 사용법:
 * 1. 페이지 상단에서 include: <?php include_once __DIR__ . '/includes/theme_loader.php'; ?>
 * 2. <head> 내에서 CSS 로드: <?php ThemeLoader::renderCSS(); ?>
 * 3. <body> 태그에 속성 추가: <body <?php ThemeLoader::renderBodyAttributes(); ?>>
 * 4. 테마 스위처 표시: <?php ThemeLoader::renderSwitcher(); ?>
 *
 * 테마 설정:
 * - URL 파라미터: ?theme=excel 또는 ?theme=default
 * - 세션 저장: 한번 선택하면 세션 동안 유지
 * - 쿠키 저장: 브라우저 닫아도 30일간 유지
 */

class ThemeLoader {
    const THEME_DEFAULT = 'default';
    const THEME_EXCEL = 'excel';
    const COOKIE_NAME = 'dsp_theme';
    const COOKIE_DAYS = 30;

    private static $initialized = false;
    private static $currentTheme = self::THEME_DEFAULT;

    /**
     * 테마 초기화 (자동 호출됨)
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        // 세션 시작 (아직 안 됐으면)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. URL 파라미터 확인 (최우선)
        if (isset($_GET['theme'])) {
            $theme = $_GET['theme'];
            if (in_array($theme, [self::THEME_DEFAULT, self::THEME_EXCEL])) {
                self::setTheme($theme);
            }
        }

        // 2. 세션에서 테마 로드
        if (isset($_SESSION['theme'])) {
            self::$currentTheme = $_SESSION['theme'];
        }
        // 3. 쿠키에서 테마 로드
        elseif (isset($_COOKIE[self::COOKIE_NAME])) {
            $theme = $_COOKIE[self::COOKIE_NAME];
            if (in_array($theme, [self::THEME_DEFAULT, self::THEME_EXCEL])) {
                self::$currentTheme = $theme;
                $_SESSION['theme'] = $theme;
            }
        }

        self::$initialized = true;
    }

    /**
     * 테마 설정
     */
    public static function setTheme($theme) {
        if (!in_array($theme, [self::THEME_DEFAULT, self::THEME_EXCEL])) {
            return false;
        }

        self::$currentTheme = $theme;
        $_SESSION['theme'] = $theme;

        // 쿠키 설정 (30일)
        $expires = time() + (self::COOKIE_DAYS * 24 * 60 * 60);
        setcookie(self::COOKIE_NAME, $theme, $expires, '/', '', false, true);

        return true;
    }

    /**
     * 현재 테마 반환
     */
    public static function getTheme() {
        self::init();
        return self::$currentTheme;
    }

    /**
     * Excel 테마인지 확인
     */
    public static function isExcelTheme() {
        return self::getTheme() === self::THEME_EXCEL;
    }

    /**
     * CSS 링크 렌더링 (head 내에 사용)
     */
    public static function renderCSS() {
        self::init();

        // 기본 CSS는 항상 로드됨 (각 페이지에서 직접 로드)

        // Excel 테마 CSS 추가 로드
        if (self::$currentTheme === self::THEME_EXCEL) {
            echo '<link rel="stylesheet" href="/css/theme-excel.css">' . "\n";
        }
    }

    /**
     * Body 태그 속성 렌더링
     */
    public static function renderBodyAttributes() {
        self::init();

        $attrs = [];

        if (self::$currentTheme === self::THEME_EXCEL) {
            $attrs[] = 'data-theme="excel"';
        }

        echo implode(' ', $attrs);
    }

    /**
     * 테마 스위처 UI 렌더링
     */
    public static function renderSwitcher($position = 'bottom-right') {
        self::init();

        $isExcel = self::$currentTheme === self::THEME_EXCEL;
        $currentUrl = $_SERVER['REQUEST_URI'];
        $baseUrl = strtok($currentUrl, '?');
        $queryParams = $_GET;

        // 테마 전환 URL 생성
        $queryParams['theme'] = $isExcel ? self::THEME_DEFAULT : self::THEME_EXCEL;
        $switchUrl = $baseUrl . '?' . http_build_query($queryParams);

        // 위치 클래스
        $positionClass = 'theme-switcher-' . $position;

        ?>
        <div class="theme-switcher <?php echo $positionClass; ?>">
            <a href="<?php echo htmlspecialchars($switchUrl); ?>"
               class="theme-switcher-btn"
               title="<?php echo $isExcel ? '기본 테마로 전환' : 'Excel 테마로 전환'; ?>">
                <?php if ($isExcel): ?>
                    <span class="theme-icon">🎨</span>
                    <span class="theme-label">기본</span>
                <?php else: ?>
                    <span class="theme-icon">📊</span>
                    <span class="theme-label">Excel</span>
                <?php endif; ?>
            </a>
        </div>
        <style>
            .theme-switcher {
                position: fixed;
                z-index: 9999;
                font-family: 'Malgun Gothic', sans-serif;
            }
            .theme-switcher-bottom-right {
                bottom: 20px;
                right: 20px;
            }
            .theme-switcher-bottom-left {
                bottom: 20px;
                left: 20px;
            }
            .theme-switcher-top-right {
                top: 20px;
                right: 20px;
            }
            .theme-switcher-top-left {
                top: 20px;
                left: 20px;
            }
            .theme-switcher-btn {
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 10px 16px;
                background: <?php echo $isExcel ? '#E8F4E8' : '#fff'; ?>;
                border: 2px solid <?php echo $isExcel ? '#C8D6C8' : '#ddd'; ?>;
                border-radius: <?php echo $isExcel ? '0' : '25px'; ?>;
                color: <?php echo $isExcel ? '#4A6741' : '#333'; ?>;
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
            }
            .theme-switcher-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                background: <?php echo $isExcel ? '#D4E8D4' : '#f5f5f5'; ?>;
            }
            .theme-icon {
                font-size: 18px;
            }
            .theme-label {
                font-weight: 600;
            }

            /* 모바일에서 라벨 숨김 */
            @media (max-width: 480px) {
                .theme-label {
                    display: none;
                }
                .theme-switcher-btn {
                    padding: 10px 12px;
                }
            }
        </style>
        <?php
    }

    /**
     * 테마 스위처 JavaScript (AJAX 전환용)
     */
    public static function renderSwitcherJS() {
        ?>
        <script>
        const ThemeSwitcher = {
            setTheme: function(theme) {
                // URL 파라미터로 페이지 새로고침
                const url = new URL(window.location.href);
                url.searchParams.set('theme', theme);
                window.location.href = url.toString();
            },

            toggle: function() {
                const current = document.body.getAttribute('data-theme');
                const newTheme = current === 'excel' ? 'default' : 'excel';
                this.setTheme(newTheme);
            }
        };
        </script>
        <?php
    }

    /**
     * 관리자 페이지용 테마 설정 패널 렌더링
     */
    public static function renderAdminPanel() {
        self::init();
        $isExcel = self::$currentTheme === self::THEME_EXCEL;
        ?>
        <div class="theme-admin-panel">
            <h4>🎨 사이트 테마</h4>
            <div class="theme-options">
                <label class="theme-option <?php echo !$isExcel ? 'active' : ''; ?>">
                    <input type="radio" name="site_theme" value="default"
                           <?php echo !$isExcel ? 'checked' : ''; ?>
                           onchange="ThemeSwitcher.setTheme('default')">
                    <span class="theme-preview default-preview">
                        <span class="preview-header"></span>
                        <span class="preview-content"></span>
                    </span>
                    <span class="theme-name">기본 테마</span>
                </label>
                <label class="theme-option <?php echo $isExcel ? 'active' : ''; ?>">
                    <input type="radio" name="site_theme" value="excel"
                           <?php echo $isExcel ? 'checked' : ''; ?>
                           onchange="ThemeSwitcher.setTheme('excel')">
                    <span class="theme-preview excel-preview">
                        <span class="preview-header"></span>
                        <span class="preview-content"></span>
                    </span>
                    <span class="theme-name">Excel 테마</span>
                </label>
            </div>
        </div>
        <style>
            .theme-admin-panel {
                padding: 20px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                margin-bottom: 20px;
            }
            .theme-admin-panel h4 {
                margin: 0 0 15px 0;
                font-size: 14px;
                color: #333;
            }
            .theme-options {
                display: flex;
                gap: 20px;
            }
            .theme-option {
                cursor: pointer;
                text-align: center;
            }
            .theme-option input {
                display: none;
            }
            .theme-preview {
                display: block;
                width: 100px;
                height: 70px;
                border: 2px solid #ddd;
                margin-bottom: 8px;
                position: relative;
                overflow: hidden;
            }
            .theme-option.active .theme-preview {
                border-color: #4CAF50;
                box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.3);
            }
            .preview-header {
                display: block;
                height: 20px;
                background: #2c3e50;
            }
            .preview-content {
                display: block;
                height: 50px;
                background: #fff;
            }
            .excel-preview .preview-header {
                background: #E8F4E8;
            }
            .excel-preview .preview-content {
                background: linear-gradient(to bottom, #FFF9E6 50%, #fff 50%);
            }
            .excel-preview {
                border-radius: 0;
            }
            .default-preview {
                border-radius: 8px;
            }
            .theme-name {
                display: block;
                font-size: 12px;
                color: #666;
            }
            .theme-option.active .theme-name {
                color: #4CAF50;
                font-weight: bold;
            }
        </style>
        <?php
    }
}

// 자동 초기화
ThemeLoader::init();
