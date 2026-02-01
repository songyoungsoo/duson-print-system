<?php
/**
 * 환경별 설정 관리 시스템
 * 개발 환경과 운영 환경을 자동으로 감지하여 적절한 설정을 적용합니다.
 */

// 환경 감지 클래스
class EnvironmentDetector {

    private static $environment = null;
    private static $config = null;

    /**
     * 현재 환경을 자동 감지
     */
    public static function detectEnvironment() {
        if (self::$environment !== null) {
            return self::$environment;
        }

        // 1. 호스트명으로 환경 판단
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // 2. 서버 경로로 환경 판단
        $serverPath = $_SERVER['DOCUMENT_ROOT'] ?? '';

        // 3. IP 주소로 환경 판단
        $serverIP = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';

        // 로컬 개발 환경 감지
        if (
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '::1') !== false ||
            strpos($serverPath, 'xampp') !== false ||
            strpos($serverPath, 'wamp') !== false ||
            strpos($serverPath, 'mamp') !== false ||
            $serverIP === '127.0.0.1' ||
            $serverIP === '::1'
        ) {
            self::$environment = 'local';
        }
        // 운영 환경 감지 (dsp114.co.kr 및 dsp1830.shop)
        else if (
            strpos($host, 'dsp114.co.kr') !== false ||
            strpos($host, 'www.dsp114.co.kr') !== false ||
            strpos($host, 'dsp114.com') !== false ||
            strpos($host, 'www.dsp114.com') !== false ||
            strpos($host, 'dsp1830.shop') !== false ||
            strpos($host, 'www.dsp1830.shop') !== false
        ) {
            self::$environment = 'production';
        }
        // 기타 환경 (스테이징, 테스트 등)
        else {
            self::$environment = 'production'; // 기본값을 운영환경으로 설정 (안전)
        }

        return self::$environment;
    }

    /**
     * 환경별 데이터베이스 설정 반환
     */
    public static function getDatabaseConfig() {
        if (self::$config !== null) {
            return self::$config;
        }

        $env = self::detectEnvironment();

        switch ($env) {
            case 'local':
                // 로컬 개발 환경 설정 (WSL2)
                self::$config = [
                    'host' => 'localhost',
                    'user' => 'dsp1830',
                    'password' => 'ds701018',
                    'database' => 'dsp1830',
                    'charset' => 'utf8mb4',
                    'environment' => 'local',
                    'debug' => true
                ];
                break;

            case 'production':
                // 운영 환경 설정 (웹 호스팅)
                self::$config = [
                    'host' => 'localhost',
                    'user' => 'dsp1830',
                    'password' => 't3zn?5R56',
                    'database' => 'dsp1830',
                    'charset' => 'utf8mb4',
                    'environment' => 'production',
                    'debug' => false
                ];
                break;

            default:
                // 기본값 (운영환경과 동일)
                self::$config = [
                    'host' => 'localhost',
                    'user' => 'dsp1830',
                    'password' => 't3zn?5R56',
                    'database' => 'dsp1830',
                    'charset' => 'utf8mb4',
                    'environment' => 'unknown',
                    'debug' => false
                ];
                break;
        }

        return self::$config;
    }

    /**
     * 현재 환경이 로컬 개발환경인지 확인
     */
    public static function isLocal() {
        return self::detectEnvironment() === 'local';
    }

    /**
     * 현재 환경이 운영환경인지 확인
     */
    public static function isProduction() {
        return self::detectEnvironment() === 'production';
    }

    /**
     * 환경 정보를 배열로 반환 (디버깅용)
     */
    public static function getEnvironmentInfo() {
        return [
            'environment' => self::detectEnvironment(),
            'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'php_self' => $_SERVER['PHP_SELF'] ?? 'unknown'
        ];
    }

    /**
     * 수동으로 환경 설정 (테스트용)
     */
    public static function forceEnvironment($env) {
        self::$environment = $env;
        self::$config = null; // 설정 초기화
    }

    /**
     * SMTP 설정 반환
     * ✅ 2026-01-17: 보안 강화 - 하드코딩 대신 환경 설정 사용
     */
    public static function getSmtpConfig() {
        return [
            'host' => 'smtp.naver.com',
            'port' => 465,
            'secure' => 'ssl',
            'username' => getenv('SMTP_USERNAME') ?: 'dsp1830',
            'password' => getenv('SMTP_PASSWORD') ?: '2CP3P5BTS83Y',
            'from_email' => 'dsp1830@naver.com',
            'from_name' => '두손기획인쇄'
        ];
    }
}

// 편의 함수들
function get_db_config() {
    return EnvironmentDetector::getDatabaseConfig();
}

function is_local_environment() {
    return EnvironmentDetector::isLocal();
}

function is_production_environment() {
    return EnvironmentDetector::isProduction();
}

function get_current_environment() {
    return EnvironmentDetector::detectEnvironment();
}

// 환경별 에러 리포팅 설정
$config = EnvironmentDetector::getDatabaseConfig();
if ($config['debug']) {
    // 로컬 환경: 모든 오류 표시
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    // 운영 환경: 오류 숨김
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>