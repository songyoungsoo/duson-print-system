<?php
require_once __DIR__ . '/base.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        jsonResponse(true, 'Members API skeleton ready', []);
        break;
    default:
        jsonResponse(false, 'Invalid action');
}
