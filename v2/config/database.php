<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config.env.php';
$v1Config = get_db_config();

return [
    'host' => $v1Config['host'],
    'database' => $v1Config['database'],
    'username' => $v1Config['user'],
    'password' => $v1Config['password'],
    'charset' => $v1Config['charset'] ?? 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
];
