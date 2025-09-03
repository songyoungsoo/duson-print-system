<?php
/**
 * SuperClaude Framework Management Dashboard
 * Web interface for managing SuperClaude operations
 */

require_once 'includes/superclaud_framework.php';

// Initialize framework
if (!isset($GLOBALS['superclaud'])) {
    include 'db.php';
    if ($db) {
        $GLOBALS['superclaud'] = new SuperClaudeFramework($db);
    }
}

$framework = $GLOBALS['superclaud'];
$stats = $framework->getStats();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperClaude Dashboard - 두손기획인쇄</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .dashboard-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .framework-status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #00C9FF, #92FE9D);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .dashboard-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .panel-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .panel-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .panel-content {
            padding: 25px;
        }
        
        .command-section {
            margin-bottom: 30px;
        }
        
        .command-input {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .command-field {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .command-field:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        .agent-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .agent-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .agent-card:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .agent-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .agent-status {
            font-size: 0.8rem;
            color: #666;
        }
        
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .log-entry {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }
        
        .log-timestamp {
            color: #666;
            font-size: 0.8rem;
        }
        
        .command-result {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 0.9rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .mode-selector {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .mode-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mode-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .quick-btn {
            padding: 15px 10px;
            background: #f8f9fa;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .quick-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .dashboard-title {
                font-size: 2rem;
            }
        }
        
        .loading {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-robot"></i> SuperClaude Framework
            </h1>
            <p class="dashboard-subtitle">두손기획인쇄 통합 지능형 관리 시스템</p>
            <div class="framework-status">
                <i class="fas fa-check-circle"></i>
                <span>운영 중 - v<?php echo $stats['version']; ?></span>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #667eea;">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="stat-value"><?php echo $stats['agents_count']; ?></div>
                <div class="stat-label">Active Agents</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #764ba2;">
                    <i class="fas fa-terminal"></i>
                </div>
                <div class="stat-value"><?php echo $stats['commands_count']; ?></div>
                <div class="stat-label">Commands</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #00C9FF;">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-value"><?php echo $stats['current_mode']; ?></div>
                <div class="stat-label">Current Mode</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #92FE9D;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['uptime']; ?></div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Command Interface -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <i class="fas fa-terminal"></i>
                    <span class="panel-title">Command Interface</span>
                </div>
                <div class="panel-content">
                    <!-- Mode Selector -->
                    <div class="mode-selector">
                        <button class="mode-btn active" data-mode="production">Production</button>
                        <button class="mode-btn" data-mode="order_management">Orders</button>
                        <button class="mode-btn" data-mode="analysis">Analysis</button>
                        <button class="mode-btn" data-mode="optimization">Optimize</button>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <button class="quick-btn" data-command="/sc:system-health">System Health</button>
                        <button class="quick-btn" data-command="/sc:inventory-status">Inventory</button>
                        <button class="quick-btn" data-command="/sc:production-status">Production</button>
                        <button class="quick-btn" data-command="/sc:report-daily">Daily Report</button>
                    </div>
                    
                    <!-- Command Input -->
                    <div class="command-section">
                        <div class="command-input">
                            <input type="text" class="command-field" id="commandInput" 
                                   placeholder="/sc:command-name [parameters]" 
                                   value="/sc:system-health">
                            <button class="btn btn-primary" onclick="executeCommand()">
                                <i class="fas fa-play"></i> Execute
                            </button>
                        </div>
                        
                        <div id="commandResult" class="command-result" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Agent Status -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <i class="fas fa-robot"></i>
                    <span class="panel-title">Agent Status</span>
                </div>
                <div class="panel-content">
                    <div class="agent-list" id="agentList">
                        <!-- Agents will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="dashboard-panel">
            <div class="panel-header">
                <i class="fas fa-list"></i>
                <span class="panel-title">Recent Activity</span>
                <div style="margin-left: auto;">
                    <button class="btn btn-secondary" onclick="refreshActivity()">
                        <i class="fas fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="panel-content">
                <div class="activity-log" id="activityLog">
                    <!-- Activity will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dashboard JavaScript
        let currentMode = 'production';
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadAgents();
            loadActivity();
            setupEventListeners();
        });
        
        // Setup event listeners
        function setupEventListeners() {
            // Mode buttons
            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    setMode(this.dataset.mode);
                });
            });
            
            // Quick action buttons
            document.querySelectorAll('.quick-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('commandInput').value = this.dataset.command;
                    executeCommand();
                });
            });
            
            // Enter key for command input
            document.getElementById('commandInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    executeCommand();
                }
            });
        }
        
        // Set framework mode
        function setMode(mode) {
            currentMode = mode;
            
            // Update UI
            document.querySelectorAll('.mode-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-mode="${mode}"]`).classList.add('active');
            
            // Send mode change to backend
            fetch('/api/superclaud_api.php/mode', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({mode: mode})
            });
            
            showNotification(`Mode changed to: ${mode}`, 'success');
        }
        
        // Execute command
        async function executeCommand() {
            const commandInput = document.getElementById('commandInput');
            const resultDiv = document.getElementById('commandResult');
            const command = commandInput.value.trim();
            
            if (!command) {
                showNotification('Please enter a command', 'error');
                return;
            }
            
            // Show loading
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<i class="fas fa-spinner loading"></i> Executing command...';
            
            try {
                const response = await fetch('/api/superclaud_api.php/commands', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        command: command,
                        params: {}
                    })
                });
                
                const result = await response.json();
                
                // Display result
                resultDiv.innerHTML = `<pre>${JSON.stringify(result, null, 2)}</pre>`;
                
                // Show notification
                if (result.success) {
                    showNotification('Command executed successfully', 'success');
                } else {
                    showNotification('Command failed: ' + (result.error || 'Unknown error'), 'error');
                }
                
                // Refresh activity log
                setTimeout(loadActivity, 1000);
                
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">Error: ${error.message}</div>`;
                showNotification('Network error occurred', 'error');
            }
        }
        
        // Load agents
        async function loadAgents() {
            try {
                const response = await fetch('/api/superclaud_api.php/agents');
                const data = await response.json();
                
                const agentList = document.getElementById('agentList');
                agentList.innerHTML = '';
                
                Object.entries(data.agents).forEach(([name, description]) => {
                    const agentCard = document.createElement('div');
                    agentCard.className = 'agent-card';
                    agentCard.innerHTML = `
                        <div class="agent-name">${name}</div>
                        <div class="agent-status">Active</div>
                    `;
                    agentList.appendChild(agentCard);
                });
                
            } catch (error) {
                console.error('Failed to load agents:', error);
            }
        }
        
        // Load activity log
        async function loadActivity() {
            try {
                const response = await fetch('/api/superclaud_api.php/tasks?limit=20');
                const data = await response.json();
                
                const activityLog = document.getElementById('activityLog');
                activityLog.innerHTML = '';
                
                data.tasks.forEach(task => {
                    const logEntry = document.createElement('div');
                    logEntry.className = 'log-entry';
                    logEntry.innerHTML = `
                        <div><strong>${task.task_name}</strong> - ${task.status}</div>
                        <div class="log-timestamp">${task.created_at}</div>
                    `;
                    activityLog.appendChild(logEntry);
                });
                
            } catch (error) {
                console.error('Failed to load activity:', error);
            }
        }
        
        // Refresh activity
        function refreshActivity() {
            loadActivity();
            showNotification('Activity log refreshed', 'success');
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                transition: all 0.3s ease;
            `;
            
            // Set color based on type
            switch (type) {
                case 'success':
                    notification.style.background = '#28a745';
                    break;
                case 'error':
                    notification.style.background = '#dc3545';
                    break;
                case 'warning':
                    notification.style.background = '#ffc107';
                    break;
                default:
                    notification.style.background = '#17a2b8';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Auto-refresh every 30 seconds
        setInterval(() => {
            loadActivity();
        }, 30000);
    </script>
</body>
</html>