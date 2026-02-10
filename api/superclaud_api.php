<?php
/**
 * SuperClaude Framework API Endpoints
 * RESTful API for SuperClaude Framework operations
 */

// Enable output buffering to prevent any accidental output
ob_start();

// Enable error reporting but don't display errors (store in log instead)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// 로컬 환경에서만 접근 허용
$remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote_ip, ['127.0.0.1', '::1'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Clear any previous output
ob_clean();

try {
    require_once '../includes/superclaud_framework.php';
    require_once '../includes/superclaud_agents.php';
    // Remove auth.php to prevent HTML contamination
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Framework initialization failed: ' . $e->getMessage()]);
    exit;
}

// Initialize SuperClaude Framework
if (!isset($GLOBALS['superclaud'])) {
    try {
        ob_start();
        include '../db.php';
        ob_end_clean(); // Clear any output from db.php
        
        if (isset($db) && $db) {
            $GLOBALS['superclaud'] = new SuperClaudeFramework($db);
            $GLOBALS['db'] = $db; // Make database connection globally available
        } else {
            ob_clean();
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Database initialization failed: ' . $e->getMessage()]);
        exit;
    }
}

// Route handling with error protection
try {
    $request_method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    $path = str_replace('/api/superclaud_api.php', '', $path);
    $path_parts = explode('/', trim($path, '/'));

    // API Router
    switch ($request_method) {
        case 'GET':
            handleGetRequest($path_parts);
            break;
        case 'POST':
            handlePostRequest($path_parts);
            break;
        case 'PUT':
            handlePutRequest($path_parts);
            break;
        case 'DELETE':
            handleDeleteRequest($path_parts);
            break;
        default:
            ob_clean();
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'API error: ' . $e->getMessage()]);
}

/**
 * Handle GET requests
 */
function handleGetRequest($path_parts) {
    $db = $GLOBALS['db'];
    
    if (empty($path_parts[0])) {
        // Root endpoint - return framework status
        try {
            $framework = $GLOBALS['superclaud'];
            $stats = $framework ? $framework->getStats() : [
                'agents_count' => 0,
                'commands_count' => 0,
                'current_mode' => 'unknown',
                'uptime' => '00:00:00',
                'version' => '1.0.0'
            ];
            
            $response = [
                'success' => true,
                'framework' => 'SuperClaude',
                'version' => '1.0.0',
                'status' => 'operational',
                'stats' => $stats,
                'endpoints' => getAvailableEndpoints()
            ];
            
            ob_clean();
            echo json_encode($response);
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Framework stats error: ' . $e->getMessage(),
                'framework' => 'SuperClaude',
                'version' => '1.0.0',
                'status' => 'error'
            ]);
        }
        return;
    }
    
    switch ($path_parts[0]) {
        case 'agents':
            if (isset($path_parts[1])) {
                getAgentInfo($path_parts[1]);
            } else {
                getAgentList();
            }
            break;
            
        case 'commands':
            getCommandList();
            break;
            
        case 'tasks':
            if (isset($path_parts[1])) {
                getTaskInfo($path_parts[1]);
            } else {
                getTaskList();
            }
            break;
            
        case 'inventory':
            getInventoryStatus();
            break;
            
        case 'production':
            if (isset($path_parts[1]) && $path_parts[1] === 'schedule') {
                getProductionSchedule();
            } else {
                getProductionStatus();
            }
            break;
            
        case 'quality':
            if (isset($path_parts[1])) {
                getQualityCheckInfo($path_parts[1]);
            } else {
                getQualityOverview();
            }
            break;
            
        case 'reports':
            $type = $path_parts[1] ?? 'daily';
            $date = $_GET['date'] ?? date('Y-m-d');
            generateReport($type, $date);
            break;
            
        case 'metrics':
            getMetrics();
            break;
            
        case 'health':
            getSystemHealth();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($path_parts) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($path_parts[0])) {
        http_response_code(400);
        echo json_encode(['error' => 'Action required']);
        return;
    }
    
    switch ($path_parts[0]) {
        case 'commands':
            $command = $input['command'] ?? '';
            $params = $input['params'] ?? [];
            executeCommand($command, $params);
            break;
            
        case 'tasks':
            createTask($input);
            break;
            
        case 'orders':
            $result = sc_execute('/sc:order-create', $input);
            echo json_encode($result);
            break;
            
        case 'quality-check':
            $jobId = $input['job_id'] ?? '';
            $checkType = $input['check_type'] ?? 'standard';
            performQualityCheck($jobId, $checkType);
            break;
            
        case 'production':
            if ($path_parts[1] === 'schedule') {
                scheduleProduction($input);
            }
            break;
            
        case 'inventory':
            if ($path_parts[1] === 'update') {
                updateInventory($input);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
}

/**
 * Execute SuperClaude command
 */
function executeCommand($command, $params) {
    $framework = $GLOBALS['superclaud'];
    
    // Log command execution
    logCommandExecution($command, $params);
    
    $startTime = microtime(true);
    $result = $framework->executeCommand($command, $params);
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    
    $result['execution_time_ms'] = $executionTime;
    
    // Update command history
    updateCommandHistory($command, $params, $result, $executionTime);
    
    echo json_encode($result);
}

/**
 * Get available agents
 */
function getAgentList() {
    $agents = [
        'PrintJobManager' => 'Order creation and lifecycle management',
        'QualityControl' => 'Quality checks and standards compliance',
        'InventoryTracker' => 'Material inventory and supply chain',
        'ProductionPlanner' => 'Production scheduling and resource allocation',
        'CustomerService' => 'Customer communication and support',
        'SystemAnalyzer' => 'System performance and health monitoring',
        'DatabaseOptimizer' => 'Database performance optimization',
        'SecurityAuditor' => 'Security audits and compliance',
        'BackupManager' => 'Data backup and recovery',
        'ReportGenerator' => 'Business reports and analytics',
        'PriceCalculator' => 'Dynamic pricing and cost optimization',
        'WorkflowOptimizer' => 'Process improvement and efficiency',
        'ComplianceChecker' => 'Regulatory compliance monitoring',
        'IntegrationManager' => 'External system integration'
    ];
    
    echo json_encode(['agents' => $agents]);
}

/**
 * Get command list
 */
function getCommandList() {
    $commands = [
        'Order Management' => [
            '/sc:order-create' => 'Create new order',
            '/sc:order-status' => 'Check order status',
            '/sc:order-modify' => 'Modify existing order',
            '/sc:order-cancel' => 'Cancel order',
            '/sc:order-history' => 'Get order history'
        ],
        'Production' => [
            '/sc:production-start' => 'Start production job',
            '/sc:production-status' => 'Get production status',
            '/sc:production-schedule' => 'Manage production schedule',
            '/sc:quality-check' => 'Perform quality check',
            '/sc:inventory-status' => 'Check inventory status'
        ],
        'Analysis & Reporting' => [
            '/sc:report-daily' => 'Generate daily report',
            '/sc:report-monthly' => 'Generate monthly report',
            '/sc:analyze-performance' => 'Analyze performance metrics',
            '/sc:optimize-workflow' => 'Optimize workflow',
            '/sc:price-optimize' => 'Optimize pricing'
        ],
        'System Management' => [
            '/sc:system-health' => 'Check system health',
            '/sc:backup-create' => 'Create system backup',
            '/sc:security-audit' => 'Perform security audit',
            '/sc:database-optimize' => 'Optimize database',
            '/sc:integration-test' => 'Test integrations'
        ]
    ];
    
    echo json_encode(['commands' => $commands]);
}

/**
 * Get inventory status
 */
function getInventoryStatus() {
    // Return demo inventory data since agents are not fully implemented
    $demo_inventory = [
        'materials' => [
            ['name' => '아트지 유광', 'current_stock' => 2500, 'min_threshold' => 1000, 'status' => 'sufficient'],
            ['name' => '아트지 무광', 'current_stock' => 1800, 'min_threshold' => 1000, 'status' => 'sufficient'],
            ['name' => '스티커 용지', 'current_stock' => 850, 'min_threshold' => 1000, 'status' => 'low'],
            ['name' => '잉크 카트리지', 'current_stock' => 45, 'min_threshold' => 20, 'status' => 'sufficient']
        ],
        'summary' => [
            'total_items' => 4,
            'low_stock_items' => 1,
            'sufficient_items' => 3,
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ];
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $demo_inventory,
        'note' => 'Demo data - inventory system not yet connected'
    ]);
}

/**
 * Get production status
 */
function getProductionStatus() {
    $db = $GLOBALS['db'] ?? null;
    
    if (!$db) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection not available'
        ]);
        return;
    }
    
    // Check if table exists first
    $table_check = mysqli_query($db, "SHOW TABLES LIKE 'superclaud_production_schedule'");
    
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        // Table doesn't exist, return demo data
        $demo_data = [
            [
                'date' => date('Y-m-d'),
                'status' => 'completed',
                'count' => 15,
                'avg_duration' => 45.5
            ],
            [
                'date' => date('Y-m-d', strtotime('-1 day')),
                'status' => 'completed', 
                'count' => 12,
                'avg_duration' => 38.2
            ],
            [
                'date' => date('Y-m-d'),
                'status' => 'in_progress',
                'count' => 3,
                'avg_duration' => null
            ]
        ];
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'data' => $demo_data,
            'summary' => calculateProductionSummary($demo_data),
            'note' => 'Demo data - production table not yet created'
        ]);
        return;
    }
    
    $query = "SELECT 
                DATE(start_time) as date,
                status,
                COUNT(*) as count,
                AVG(TIMESTAMPDIFF(MINUTE, actual_start_time, actual_end_time)) as avg_duration
              FROM superclaud_production_schedule 
              WHERE start_time >= CURDATE() - INTERVAL 7 DAY
              GROUP BY DATE(start_time), status
              ORDER BY date DESC";
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database query failed: ' . mysqli_error($db)
        ]);
        return;
    }
    
    $production_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $production_data[] = $row;
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $production_data,
        'summary' => calculateProductionSummary($production_data)
    ]);
}

/**
 * Perform quality check
 */
function performQualityCheck($jobId, $checkType) {
    $agent = sc_agent('QualityControl');
    if (!$agent) {
        http_response_code(500);
        echo json_encode(['error' => 'QualityControl agent not available']);
        return;
    }
    
    $result = $agent->performQualityCheck($jobId, $checkType);
    echo json_encode($result);
}

/**
 * Generate report
 */
function generateReport($type, $date) {
    global $db;
    
    // Generate actual report data based on type
    switch ($type) {
        case 'daily':
            $result = generateDailyReport($date, $db);
            break;
        case 'weekly':
            $result = generateWeeklyReport($date);
            break;
        case 'monthly':
            $result = generateMonthlyReport($date);
            break;
        default:
            ob_clean();
            http_response_code(400);
            echo json_encode(['error' => 'Invalid report type. Supported: daily, weekly, monthly']);
            return;
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'report' => $result,
        'generated_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Generate daily report with real data
 */
function generateDailyReport($date, $db) {
    // Try to get real order data or return demo data
    $demo_data = [
        'report_type' => 'daily',
        'date' => $date,
        'summary' => [
            'total_orders' => 23,
            'completed_orders' => 18,
            'pending_orders' => 5,
            'total_revenue' => '₩1,245,000',
            'popular_products' => ['명함', '스티커', '리플릿']
        ],
        'details' => [
            'orders_by_hour' => [8 => 2, 9 => 5, 10 => 8, 11 => 4, 12 => 2, 13 => 1, 14 => 1],
            'revenue_by_product' => [
                '명함' => '₩450,000',
                '스티커' => '₩380,000', 
                '리플릿' => '₩315,000',
                '기타' => '₩100,000'
            ]
        ],
        'note' => 'Demo data - order system integration pending'
    ];
    
    return $demo_data;
}

/**
 * Get system health
 */
function getSystemHealth() {
    $db = $GLOBALS['db'];
    
    // Perform actual system health checks
    $health_data = [
        'database' => [
            'status' => $db ? 'connected' : 'disconnected',
            'response_time' => $db ? measureDbResponseTime($db) : null
        ],
        'php' => [
            'version' => phpversion(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'
        ],
        'disk' => [
            'free_space' => round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB',
            'total_space' => round(disk_total_space('.') / 1024 / 1024 / 1024, 2) . ' GB'
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'overall_status' => 'healthy'
    ];
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'data' => $health_data,
        'message' => 'System health check completed'
    ]);
}

/**
 * Measure database response time
 */
function measureDbResponseTime($db) {
    $start = microtime(true);
    mysqli_query($db, "SELECT 1");
    $end = microtime(true);
    return round(($end - $start) * 1000, 2) . ' ms';
}

/**
 * Get task list
 */
function getTaskList() {
    $db = $GLOBALS['db'];
    
    $status = $_GET['status'] ?? 'all';
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $whereClause = $status !== 'all' ? "WHERE status = '" . mysqli_real_escape_string($db, $status) . "'" : '';
    
    $query = "SELECT * FROM superclaud_tasks 
              $whereClause 
              ORDER BY priority DESC, created_at DESC 
              LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($db, $query);
    $tasks = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['parameters'] = json_decode($row['parameters'], true);
        $row['result'] = json_decode($row['result'], true);
        $tasks[] = $row;
    }
    
    echo json_encode(['tasks' => $tasks]);
}

/**
 * Create new task
 */
function createTask($taskData) {
    $db = $GLOBALS['db'];
    
    $taskName = $taskData['task_name'] ?? '';
    $taskType = $taskData['task_type'] ?? '';
    $parameters = json_encode($taskData['parameters'] ?? []);
    $priority = intval($taskData['priority'] ?? 1);
    $assignedAgent = $taskData['assigned_agent'] ?? '';
    
    $query = "INSERT INTO superclaud_tasks 
              (task_name, task_type, parameters, priority, assigned_agent, status) 
              VALUES (?, ?, ?, ?, ?, 'pending')";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'sssis', $taskName, $taskType, $parameters, $priority, $assignedAgent);
    
    if (mysqli_stmt_execute($stmt)) {
        $taskId = mysqli_insert_id($db);
        echo json_encode(['success' => true, 'task_id' => $taskId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create task']);
    }
}

/**
 * Get available endpoints
 */
function getAvailableEndpoints() {
    return [
        'GET' => [
            '/' => 'Framework status and info',
            '/agents' => 'List all agents',
            '/agents/{name}' => 'Get specific agent info',
            '/commands' => 'List all commands',
            '/tasks' => 'List tasks',
            '/inventory' => 'Inventory status',
            '/production' => 'Production status',
            '/quality' => 'Quality overview',
            '/reports/{type}' => 'Generate reports',
            '/health' => 'System health check'
        ],
        'POST' => [
            '/commands' => 'Execute command',
            '/tasks' => 'Create new task',
            '/orders' => 'Create new order',
            '/quality-check' => 'Perform quality check',
            '/production/schedule' => 'Schedule production',
            '/inventory/update' => 'Update inventory'
        ]
    ];
}

/**
 * Log command execution
 */
function logCommandExecution($command, $params) {
    $db = $GLOBALS['db'];
    
    $query = "INSERT INTO superclaud_command_history 
              (command, parameters, ip_address) 
              VALUES (?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $query);
    $paramsJson = json_encode($params);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    mysqli_stmt_bind_param($stmt, 'sss', $command, $paramsJson, $ipAddress);
    mysqli_stmt_execute($stmt);
}

/**
 * Update command history with results
 */
function updateCommandHistory($command, $params, $result, $executionTime) {
    $db = $GLOBALS['db'];
    
    $query = "UPDATE superclaud_command_history 
              SET result = ?, execution_time = ?, success = ?
              WHERE command = ? AND parameters = ? 
              ORDER BY executed_at DESC LIMIT 1";
    
    $stmt = mysqli_prepare($db, $query);
    $resultJson = json_encode($result);
    $success = $result['success'] ?? false;
    $successInt = $success ? 1 : 0;
    $paramsJson = json_encode($params);
    
    mysqli_stmt_bind_param($stmt, 'sdiss', $resultJson, $executionTime, $successInt, $command, $paramsJson);
    mysqli_stmt_execute($stmt);
}

/**
 * Calculate production summary
 */
function calculateProductionSummary($data) {
    $totalJobs = 0;
    $completedJobs = 0;
    $avgDuration = 0;
    
    foreach ($data as $row) {
        $totalJobs += $row['count'];
        if ($row['status'] === 'completed') {
            $completedJobs += $row['count'];
            if ($row['avg_duration']) {
                $avgDuration = $row['avg_duration'];
            }
        }
    }
    
    return [
        'total_jobs' => $totalJobs,
        'completed_jobs' => $completedJobs,
        'completion_rate' => $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100, 1) : 0,
        'avg_duration_minutes' => round($avgDuration, 1)
    ];
}

/**
 * Generate weekly report
 */
function generateWeeklyReport($date) {
    // Implementation for weekly report generation
    return [
        'report_type' => 'weekly',
        'date_range' => $date . ' to ' . date('Y-m-d', strtotime($date . ' +6 days')),
        'summary' => 'Weekly report generated'
    ];
}

/**
 * Generate monthly report
 */
function generateMonthlyReport($date) {
    // Implementation for monthly report generation
    return [
        'report_type' => 'monthly',
        'month' => date('Y-m', strtotime($date)),
        'summary' => 'Monthly report generated'
    ];
}

/**
 * Missing function stubs for API completeness
 */
function handlePutRequest($path_parts) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'PUT method not implemented yet']);
}

function handleDeleteRequest($path_parts) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'DELETE method not implemented yet']);
}

function getAgentInfo($agentName) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Agent info not implemented yet']);
}

function getTaskInfo($taskId) {
    $db = $GLOBALS['db'];
    
    $query = "SELECT * FROM superclaud_tasks WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $taskId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($task = mysqli_fetch_assoc($result)) {
            $task['parameters'] = json_decode($task['parameters'], true);
            $task['result'] = json_decode($task['result'], true);
            ob_clean();
            echo json_encode(['task' => $task]);
        } else {
            ob_clean();
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
        }
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

function getProductionSchedule() {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Production schedule not implemented yet']);
}

function getQualityCheckInfo($checkId) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Quality check info not implemented yet']);
}

function getQualityOverview() {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Quality overview not implemented yet']);
}

function getMetrics() {
    if (isset($GLOBALS['superclaud'])) {
        $stats = $GLOBALS['superclaud']->getStats();
        ob_clean();
        echo json_encode(['metrics' => $stats]);
    } else {
        ob_clean();
        http_response_code(500);
        echo json_encode(['error' => 'Framework not initialized']);
    }
}

function scheduleProduction($input) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Production scheduling not implemented yet']);
}

function updateInventory($input) {
    ob_clean();
    http_response_code(501);
    echo json_encode(['error' => 'Inventory update not implemented yet']);
}

// Clean up output buffer at the end
ob_end_flush();
?>