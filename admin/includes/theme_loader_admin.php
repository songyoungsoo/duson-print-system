<?php
/**
 * 관리자 페이지용 테마 로더
 * 레거시 관리자 페이지 구조에 맞춰 테마를 적용합니다.
 *
 * 사용법:
 * 1. 관리자 PHP 파일 상단에: require_once __DIR__ . '/includes/theme_loader_admin.php';
 * 2. <head> 태그 내부에: <?php AdminThemeLoader::renderCSS(); ?>
 * 3. <body> 태그에: <body <?php AdminThemeLoader::renderBodyAttributes(); ?>>
 * 4. </body> 직전에: <?php AdminThemeLoader::renderSwitcher(); ?>
 */

class AdminThemeLoader {
    const THEME_DEFAULT = 'default';
    const THEME_EXCEL = 'excel';
    const COOKIE_NAME = 'dsp_theme';
    const SESSION_KEY = 'site_theme';

    private static $initialized = false;

    /**
     * 테마 시스템 초기화
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // POST로 테마 변경 요청이 온 경우
        if (isset($_POST['change_theme'])) {
            self::setTheme($_POST['change_theme']);
        }

        // GET으로 테마 변경 요청이 온 경우
        if (isset($_GET['theme'])) {
            self::setTheme($_GET['theme']);
        }

        self::$initialized = true;
    }

    /**
     * 현재 테마 가져오기
     */
    public static function getCurrentTheme() {
        self::init();

        // 세션 우선
        if (isset($_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY];
        }

        // 쿠키 확인
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            return $_COOKIE[self::COOKIE_NAME];
        }

        return self::THEME_DEFAULT;
    }

    /**
     * 테마 설정
     */
    public static function setTheme($theme) {
        self::init();

        // 유효한 테마인지 확인
        $validThemes = [self::THEME_DEFAULT, self::THEME_EXCEL];
        if (!in_array($theme, $validThemes)) {
            $theme = self::THEME_DEFAULT;
        }

        // 세션에 저장
        $_SESSION[self::SESSION_KEY] = $theme;

        // 쿠키에도 저장 (30일)
        setcookie(self::COOKIE_NAME, $theme, time() + (30 * 24 * 60 * 60), '/');
    }

    /**
     * 엑셀 테마가 활성화되어 있는지 확인
     */
    public static function isExcelTheme() {
        return self::getCurrentTheme() === self::THEME_EXCEL;
    }

    /**
     * CSS 링크 렌더링 (head 태그 내부에 삽입)
     */
    public static function renderCSS() {
        self::init();

        if (self::isExcelTheme()) {
            // 관리자 페이지용 엑셀 테마 CSS
            echo '<link rel="stylesheet" href="/admin/css/admin-theme-excel.css">' . "\n";
        }
    }

    /**
     * body 태그 속성 렌더링
     */
    public static function renderBodyAttributes() {
        self::init();

        if (self::isExcelTheme()) {
            echo 'data-theme="excel"';
        }
    }

    /**
     * 인라인 스타일 렌더링 (레거시 페이지용)
     * head 태그 내부에 삽입하면 기존 스타일을 오버라이드
     */
    public static function renderInlineStyles() {
        self::init();

        if (!self::isExcelTheme()) {
            return;
        }

        echo '<style>
/* 엑셀 스타일 테마 - 관리자 페이지 */
[data-theme="excel"] {
    --excel-header-bg: #E8F4E8;
    --excel-row-odd: #FFF9E6;
    --excel-row-even: #FFFFFF;
    --excel-selected: #D4E8FC;
    --excel-border: #C8D6C8;
    --excel-header-text: #4A6741;
    --pastel-green: #E8F5E9;
    --pastel-blue: #E3F2FD;
    --pastel-yellow: #FFF8E1;
}

[data-theme="excel"] body,
[data-theme="excel"] body.coolBar {
    background: #F5F5F5 !important;
    font-family: "Malgun Gothic", "맑은 고딕", sans-serif !important;
}

/* 테이블 스타일링 */
[data-theme="excel"] table {
    border-collapse: collapse !important;
    border: 1px solid var(--excel-border) !important;
    background: white !important;
}

[data-theme="excel"] th,
[data-theme="excel"] td {
    border: 1px solid var(--excel-border) !important;
    padding: 8px 12px !important;
}

[data-theme="excel"] th {
    background: var(--excel-header-bg) !important;
    color: var(--excel-header-text) !important;
    font-weight: 600 !important;
}

[data-theme="excel"] tr:nth-child(odd) td {
    background: var(--excel-row-odd) !important;
}

[data-theme="excel"] tr:nth-child(even) td {
    background: var(--excel-row-even) !important;
}

[data-theme="excel"] tr:hover td {
    background: var(--excel-selected) !important;
}

/* 입력 필드 스타일링 */
[data-theme="excel"] input[type="text"],
[data-theme="excel"] input[type="password"],
[data-theme="excel"] input[type="email"],
[data-theme="excel"] input[type="number"],
[data-theme="excel"] select,
[data-theme="excel"] textarea {
    border: 1px solid var(--excel-border) !important;
    border-radius: 0 !important;
    padding: 6px 10px !important;
    background: #FFFFF8 !important;
}

[data-theme="excel"] input:focus,
[data-theme="excel"] select:focus,
[data-theme="excel"] textarea:focus {
    outline: 2px solid #4A90D9 !important;
    background: var(--excel-selected) !important;
}

/* 버튼 스타일링 */
[data-theme="excel"] input[type="submit"],
[data-theme="excel"] input[type="button"],
[data-theme="excel"] button,
[data-theme="excel"] .btn {
    background: var(--excel-header-bg) !important;
    color: var(--excel-header-text) !important;
    border: 1px solid var(--excel-border) !important;
    border-radius: 0 !important;
    padding: 8px 16px !important;
    cursor: pointer !important;
    font-weight: 600 !important;
}

[data-theme="excel"] input[type="submit"]:hover,
[data-theme="excel"] input[type="button"]:hover,
[data-theme="excel"] button:hover,
[data-theme="excel"] .btn:hover {
    background: #D4E8D4 !important;
}

/* 링크 스타일링 */
[data-theme="excel"] a {
    color: #1565C0 !important;
}

[data-theme="excel"] a:hover {
    color: #0D47A1 !important;
    text-decoration: underline !important;
}

/* 컨테이너/박스 스타일링 */
[data-theme="excel"] .container,
[data-theme="excel"] .box,
[data-theme="excel"] .panel,
[data-theme="excel"] fieldset {
    border: 1px solid var(--excel-border) !important;
    border-radius: 0 !important;
    background: white !important;
}

/* 헤더/타이틀 스타일링 */
[data-theme="excel"] h1,
[data-theme="excel"] h2,
[data-theme="excel"] h3,
[data-theme="excel"] .title {
    color: var(--excel-header-text) !important;
    border-bottom: 2px solid var(--excel-border) !important;
    padding-bottom: 8px !important;
}

/* 알림/메시지 스타일링 */
[data-theme="excel"] .alert,
[data-theme="excel"] .message,
[data-theme="excel"] .notice {
    border: 1px solid var(--excel-border) !important;
    border-radius: 0 !important;
    padding: 12px !important;
}

[data-theme="excel"] .alert-success,
[data-theme="excel"] .success {
    background: var(--pastel-green) !important;
    border-left: 4px solid #4CAF50 !important;
}

[data-theme="excel"] .alert-warning,
[data-theme="excel"] .warning {
    background: var(--pastel-yellow) !important;
    border-left: 4px solid #FFC107 !important;
}

[data-theme="excel"] .alert-danger,
[data-theme="excel"] .error {
    background: #FFE4E1 !important;
    border-left: 4px solid #F44336 !important;
}

/* 페이지네이션 스타일링 */
[data-theme="excel"] .pagination a,
[data-theme="excel"] .paging a {
    border: 1px solid var(--excel-border) !important;
    padding: 4px 10px !important;
    margin: 0 2px !important;
    background: white !important;
}

[data-theme="excel"] .pagination a:hover,
[data-theme="excel"] .paging a:hover,
[data-theme="excel"] .pagination .active,
[data-theme="excel"] .paging .current {
    background: var(--excel-selected) !important;
}
</style>' . "\n";
    }

    /**
     * 테마 스위처 UI 렌더링 (body 끝부분에 삽입)
     */
    public static function renderSwitcher() {
        self::init();

        $currentTheme = self::getCurrentTheme();
        $isExcel = $currentTheme === self::THEME_EXCEL;

        echo '
<!-- 관리자 테마 스위처 -->
<div id="admin-theme-switcher" style="
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 99999;
    display: flex;
    gap: 5px;
    background: white;
    padding: 8px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    border: 1px solid #ddd;
">
    <button onclick="changeAdminTheme(\'default\')" style="
        padding: 6px 12px;
        border: 1px solid #ccc;
        background: ' . ($isExcel ? '#fff' : '#4CAF50') . ';
        color: ' . ($isExcel ? '#333' : '#fff') . ';
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
    ">기본</button>
    <button onclick="changeAdminTheme(\'excel\')" style="
        padding: 6px 12px;
        border: 1px solid #ccc;
        background: ' . ($isExcel ? '#4CAF50' : '#fff') . ';
        color: ' . ($isExcel ? '#fff' : '#333') . ';
        cursor: pointer;
        border-radius: 4px;
        font-size: 12px;
    ">엑셀</button>
</div>

<script>
function changeAdminTheme(theme) {
    // 현재 URL에 theme 파라미터 추가하여 리다이렉트
    var url = new URL(window.location.href);
    url.searchParams.set("theme", theme);
    window.location.href = url.toString();
}
</script>
';
    }
}

// 자동 초기화
AdminThemeLoader::init();
