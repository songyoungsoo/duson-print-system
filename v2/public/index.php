<?php
declare(strict_types=1);

define('V2_ROOT', dirname(__DIR__));

require_once V2_ROOT . '/vendor/autoload.php';

$envFile = dirname(V2_ROOT) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

use App\Core\App;
use App\Core\Database;
use App\Core\View;

date_default_timezone_set('Asia/Seoul');

$dbConfig = require V2_ROOT . '/config/database.php';
Database::getInstance($dbConfig);

View::setBasePath(V2_ROOT . '/templates');

$appConfig = require V2_ROOT . '/config/app.php';
View::share('appName', $appConfig['name']);

$app = App::getInstance();

$routes = require V2_ROOT . '/config/routes.php';
$routes($app->router());

$app->run();
