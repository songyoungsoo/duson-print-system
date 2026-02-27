<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

$config = [];
$r = @mysqli_query($db, "SELECT config_key, config_value, config_type, config_group, description FROM chat_config ORDER BY config_group, config_key");

if (!$r) {
    mysqli_query($db, "CREATE TABLE IF NOT EXISTS chat_config (
        config_key VARCHAR(50) PRIMARY KEY,
        config_value TEXT NOT NULL,
        config_type ENUM('boolean','number','string','time') NOT NULL DEFAULT 'string',
        config_group ENUM('widget','ai','extra') NOT NULL DEFAULT 'widget',
        description VARCHAR(200) DEFAULT '',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($db, "INSERT IGNORE INTO chat_config (config_key, config_value, config_type, config_group, description) VALUES
        ('widget_enabled','1','boolean','widget','채팅 위젯 표시 여부'),
        ('widget_position','right','string','widget','위젯 위치 (right/left)'),
        ('widget_hour_start','09:00','time','widget','위젯 표시 시작 시간'),
        ('widget_hour_end','18:30','time','widget','위젯 표시 종료 시간'),
        ('widget_button_label','상담연결','string','widget','위젯 버튼 라벨 텍스트'),
        ('widget_welcome_msg','안녕하세요!\\n더 나은 상담을 위해\\n상호명이나 성함을 알려주세요','string','widget','환영 메시지'),
        ('widget_poll_interval','2000','number','widget','메시지 폴링 간격 (ms)'),
        ('ai_enabled','1','boolean','ai','AI 자동응답 활성화'),
        ('ai_wait_seconds','60','number','ai','무응답 대기 시간 (초)'),
        ('ai_greeting_msg','안녕하세요, 긴급대응입니다. 담당자 연결 전까지 제가 도와드리겠습니다.','string','ai','AI 인사 메시지'),
        ('ai_farewell_msg','담당자가 연결되었습니다. 이어서 상담 도와드릴 거예요. 감사합니다!','string','ai','AI 퇴장 메시지'),
        ('ai_hour_start','18:30','time','ai','AI 운영 시작 시간'),
        ('ai_hour_end','09:00','time','ai','AI 운영 종료 시간'),
        ('ai_display_name','긴급대응','string','ai','AI 표시 이름'),
        ('offline_message','현재 업무시간 외입니다. 전화(02-2632-1830) 또는 이메일(dsp1830@naver.com)로 문의해 주세요.','string','extra','업무외 시간 안내 메시지'),
        ('notice_message','','string','extra','채팅창 상단 공지사항 (빈 값이면 미표시)'),
        ('upload_max_mb','10','number','extra','파일 업로드 최대 용량 (MB)'),
        ('widget_pos_x','92','number','widget','위젯 X 위치 (%)'),
        ('widget_pos_y','85','number','widget','위젯 Y 위치 (%)'),
        ('ai_pos_x','92','number','ai','AI 챗봇 X 위치 (%)'),
        ('ai_pos_y','60','number','ai','AI 챗봇 Y 위치 (%)'),
        ('ai_button_label','AI 상담','string','ai','AI 챗봇 버튼 라벨'),
        ('ai_button_color','#667eea','string','ai','AI 챗봇 버튼 색상')
    ");

    $r = mysqli_query($db, "SELECT config_key, config_value, config_type, config_group, description FROM chat_config ORDER BY config_group, config_key");
}

if ($r) { while ($row = mysqli_fetch_assoc($r)) { $config[$row['config_key']] = $row; } }

// One-time migration: 기본 시간대 업데이트 (위젯 09:00~18:30, AI 18:30~09:00)
if (isset($config['widget_hour_start']) && $config['widget_hour_start']['config_value'] === '00:00'
    && isset($config['ai_hour_start']) && $config['ai_hour_start']['config_value'] === '00:00') {
    $time_defaults = [
        'widget_hour_start' => '09:00', 'widget_hour_end' => '18:30',
        'ai_hour_start' => '18:30', 'ai_hour_end' => '09:00'
    ];
    foreach ($time_defaults as $k => $v) {
        $stmt = mysqli_prepare($db, "UPDATE chat_config SET config_value=? WHERE config_key=? AND config_value IN ('00:00','23:59')");
        if ($stmt) { mysqli_stmt_bind_param($stmt, "ss", $v, $k); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); }
    }
    $config = [];
    $r = mysqli_query($db, "SELECT config_key, config_value, config_type, config_group, description FROM chat_config ORDER BY config_group, config_key");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $config[$row['config_key']] = $row; } }
}

// One-time migration: 듀얼 위젯 신규 config 키 + chatrooms.ai_active 컬럼 추가
if (!isset($config['widget_pos_x'])) {
    mysqli_query($db, "INSERT IGNORE INTO chat_config (config_key, config_value, config_type, config_group, description) VALUES
        ('widget_pos_x','92','number','widget','위젯 X 위치 (%)'),
        ('widget_pos_y','85','number','widget','위젯 Y 위치 (%)'),
        ('ai_pos_x','92','number','ai','AI 챗봇 X 위치 (%)'),
        ('ai_pos_y','60','number','ai','AI 챗봇 Y 위치 (%)'),
        ('ai_button_label','AI 상담','string','ai','AI 챗봇 버튼 라벨'),
        ('ai_button_color','#667eea','string','ai','AI 챗봇 버튼 색상')
    ");
    // chatrooms 테이블에 ai_active 컬럼 추가
    $colCheck = mysqli_query($db, "SHOW COLUMNS FROM chatrooms LIKE 'ai_active'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        mysqli_query($db, "ALTER TABLE chatrooms ADD COLUMN ai_active TINYINT(1) NOT NULL DEFAULT 0 AFTER isactive");
    }
    // config 리로드
    $config = [];
    $r = mysqli_query($db, "SELECT config_key, config_value, config_type, config_group, description FROM chat_config ORDER BY config_group, config_key");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $config[$row['config_key']] = $row; } }
}

function cfgVal($config, $key, $default = '') {
    return htmlspecialchars($config[$key]['config_value'] ?? $default, ENT_QUOTES);
}
function cfgIs($config, $key) {
    return ($config[$key]['config_value'] ?? '0') === '1';
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
.toggle-switch { position:relative; display:inline-block; width:48px; height:26px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:26px; transition:.3s; }
.toggle-slider:before { content:""; position:absolute; height:20px; width:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.toggle-switch input:checked + .toggle-slider { background:#8b5cf6; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(22px); }
.cfg-card { background:#fff; border-radius:0.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.1); overflow:hidden; margin-bottom:1rem; }
.cfg-card-header { padding:0.75rem 1rem; border-bottom:1px solid #e5e7eb; background:#f9fafb; }
.cfg-card-body { padding:1rem; }
.cfg-row { display:flex; align-items:flex-start; gap:1rem; margin-bottom:1rem; }
.cfg-row:last-child { margin-bottom:0; }
.cfg-label { width:160px; flex-shrink:0; padding-top:0.5rem; }
.cfg-field { flex:1; min-width:0; }
.cfg-hint { font-size:0.75rem; color:#9ca3af; margin-top:2px; }
.cfg-input { width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.875rem; transition:border-color 0.2s; }
.cfg-input:focus { outline:none; border-color:#8b5cf6; box-shadow:0 0 0 2px rgba(139,92,246,0.15); }
.cfg-textarea { resize:vertical; min-height:60px; }
.cfg-time-row { display:flex; align-items:center; gap:0.5rem; }
.cfg-time-row span { color:#6b7280; font-size:0.875rem; }
.cfg-suffix { display:flex; align-items:center; gap:0.5rem; }
.cfg-suffix .cfg-input { width:120px; }
.cfg-suffix span { color:#6b7280; font-size:0.875rem; white-space:nowrap; }
.pos-preview { position:relative; aspect-ratio:16/9; background:#f3f4f6; border:2px dashed #d1d5db; border-radius:8px; cursor:crosshair; overflow:hidden; user-select:none; }
.pos-dot { position:absolute; width:32px; height:32px; border-radius:50%; transform:translate(-50%,-50%); cursor:grab; display:flex; align-items:center; justify-content:center; font-size:14px; color:#fff; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.25); transition:box-shadow 0.2s; z-index:2; }
.pos-dot:active { cursor:grabbing; }
.pos-dot.active { box-shadow:0 0 0 4px rgba(139,92,246,0.3),0 2px 8px rgba(0,0,0,0.25); }
.pos-dot-widget { background:#8b5cf6; }
.pos-dot-ai { background:#667eea; }
.pos-info { display:flex; gap:1.5rem; margin-top:0.5rem; font-size:0.8rem; color:#6b7280; }
.pos-info span { display:inline-flex; align-items:center; gap:4px; }
.pos-info .dot-sm { width:10px; height:10px; border-radius:50%; display:inline-block; }
</style>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <a href="/dashboard/chat/" class="text-gray-400 hover:text-purple-600 transition-colors text-sm">&larr; 채팅 관리</a>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">채팅 설정</h1>
                <p class="text-sm text-gray-600">채팅 위젯 및 AI 야간당번 설정 관리</p>
            </div>
            <button onclick="saveConfig()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                저장
            </button>
        </div>

        <div id="save-alert" style="display:none;" class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"></div>

        <form id="config-form">
            <!-- 위젯 위치 설정 -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <h3 class="text-sm font-semibold text-gray-900">위젯 위치 설정</h3>
                </div>
                <div class="cfg-card-body">
                    <div class="pos-preview" id="pos-preview">
                        <div class="pos-dot pos-dot-widget active" id="pos-dot-widget" style="left:<?php echo cfgVal($config, 'widget_pos_x', '92'); ?>%;top:<?php echo cfgVal($config, 'widget_pos_y', '85'); ?>%;">W</div>
                        <div class="pos-dot pos-dot-ai" id="pos-dot-ai" style="left:<?php echo cfgVal($config, 'ai_pos_x', '92'); ?>%;top:<?php echo cfgVal($config, 'ai_pos_y', '60'); ?>%;">A</div>
                    </div>
                    <div class="pos-info">
                        <span><span class="dot-sm" style="background:#8b5cf6;"></span> 채팅 위젯: X <b id="pos-wx-text"><?php echo cfgVal($config, 'widget_pos_x', '92'); ?></b>%, Y <b id="pos-wy-text"><?php echo cfgVal($config, 'widget_pos_y', '85'); ?></b>%</span>
                        <span><span class="dot-sm" style="background:#667eea;"></span> AI 챗봇: X <b id="pos-ax-text"><?php echo cfgVal($config, 'ai_pos_x', '92'); ?></b>%, Y <b id="pos-ay-text"><?php echo cfgVal($config, 'ai_pos_y', '60'); ?></b>%</span>
                        <button type="button" onclick="resetPositions()" class="text-xs text-gray-400 hover:text-purple-600 ml-auto">기본값 복원</button>
                    </div>
                    <input type="hidden" id="widget_pos_x" name="widget_pos_x" value="<?php echo cfgVal($config, 'widget_pos_x', '92'); ?>">
                    <input type="hidden" id="widget_pos_y" name="widget_pos_y" value="<?php echo cfgVal($config, 'widget_pos_y', '85'); ?>">
                    <input type="hidden" id="ai_pos_x" name="ai_pos_x" value="<?php echo cfgVal($config, 'ai_pos_x', '92'); ?>">
                    <input type="hidden" id="ai_pos_y" name="ai_pos_y" value="<?php echo cfgVal($config, 'ai_pos_y', '60'); ?>">
                    <div class="cfg-hint" style="margin-top:4px;">점을 클릭 후 드래그하거나, 빈 영역 클릭으로 선택된 점을 이동합니다</div>
                </div>
            </div>

            <!-- 채팅 위젯 설정 -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <h3 class="text-sm font-semibold text-gray-900">채팅 위젯 설정</h3>
                </div>
                <div class="cfg-card-body">
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">위젯 활성화</label></div>
                        <div class="cfg-field">
                            <label class="toggle-switch">
                                <input type="checkbox" name="widget_enabled" value="1" <?php echo cfgIs($config, 'widget_enabled') ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="cfg-hint">OFF 시 고객에게 채팅 버튼이 보이지 않습니다</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">표시 시간대</label></div>
                        <div class="cfg-field">
                            <div class="cfg-time-row">
                                <input type="time" name="widget_hour_start" value="<?php echo cfgVal($config, 'widget_hour_start', '00:00'); ?>" class="cfg-input" style="width:130px;">
                                <span>~</span>
                                <input type="time" name="widget_hour_end" value="<?php echo cfgVal($config, 'widget_hour_end', '23:59'); ?>" class="cfg-input" style="width:130px;">
                            </div>
                            <div class="cfg-hint">이 시간대에만 채팅 위젯이 표시됩니다 (00:00~23:59 = 24시간)</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">버튼 라벨</label></div>
                        <div class="cfg-field">
                            <input type="text" name="widget_button_label" value="<?php echo cfgVal($config, 'widget_button_label', '상담연결'); ?>" class="cfg-input" maxlength="20" style="width:200px;">
                            <div class="cfg-hint">채팅 버튼 위에 표시되는 텍스트</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">환영 메시지</label></div>
                        <div class="cfg-field">
                            <textarea name="widget_welcome_msg" class="cfg-input cfg-textarea" rows="3"><?php echo cfgVal($config, 'widget_welcome_msg'); ?></textarea>
                            <div class="cfg-hint">채팅 시작 시 이름 입력 모달에 표시되는 문구</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">폴링 간격</label></div>
                        <div class="cfg-field">
                            <div class="cfg-suffix">
                                <input type="number" name="widget_poll_interval" value="<?php echo cfgVal($config, 'widget_poll_interval', '2000'); ?>" class="cfg-input" min="1000" max="30000" step="500">
                                <span>ms</span>
                            </div>
                            <div class="cfg-hint">새 메시지 확인 주기 (1000=1초, 기본 2000ms)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI 야간당번 설정 -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <h3 class="text-sm font-semibold text-gray-900">AI 야간당번 설정</h3>
                </div>
                <div class="cfg-card-body">
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">AI 자동응답</label></div>
                        <div class="cfg-field">
                            <label class="toggle-switch">
                                <input type="checkbox" name="ai_enabled" value="1" <?php echo cfgIs($config, 'ai_enabled') ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="cfg-hint">OFF 시 고객 메시지에 AI가 자동 응답하지 않습니다</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">대기 시간</label></div>
                        <div class="cfg-field">
                            <div class="cfg-suffix">
                                <input type="number" name="ai_wait_seconds" value="<?php echo cfgVal($config, 'ai_wait_seconds', '60'); ?>" class="cfg-input" min="10" max="600" step="10">
                                <span>초</span>
                            </div>
                            <div class="cfg-hint">고객 메시지 후 직원 무응답 시 AI가 진입하는 시간</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">운영 시간대</label></div>
                        <div class="cfg-field">
                            <div class="cfg-time-row">
                                <input type="time" name="ai_hour_start" value="<?php echo cfgVal($config, 'ai_hour_start', '00:00'); ?>" class="cfg-input" style="width:130px;">
                                <span>~</span>
                                <input type="time" name="ai_hour_end" value="<?php echo cfgVal($config, 'ai_hour_end', '23:59'); ?>" class="cfg-input" style="width:130px;">
                            </div>
                            <div class="cfg-hint">예: 18:00~09:00 = 야간만 운영 (00:00~23:59 = 24시간)</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">표시 이름</label></div>
                        <div class="cfg-field">
                            <input type="text" name="ai_display_name" value="<?php echo cfgVal($config, 'ai_display_name', '긴급대응'); ?>" class="cfg-input" maxlength="20" style="width:200px;">
                            <div class="cfg-hint">채팅에서 AI가 사용하는 이름</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">인사 메시지</label></div>
                        <div class="cfg-field">
                            <textarea name="ai_greeting_msg" class="cfg-input cfg-textarea" rows="2"><?php echo cfgVal($config, 'ai_greeting_msg'); ?></textarea>
                            <div class="cfg-hint">AI가 채팅에 진입할 때 보내는 첫 메시지</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">퇴장 메시지</label></div>
                        <div class="cfg-field">
                            <textarea name="ai_farewell_msg" class="cfg-input cfg-textarea" rows="2"><?php echo cfgVal($config, 'ai_farewell_msg'); ?></textarea>
                            <div class="cfg-hint">직원이 응답하여 AI가 퇴장할 때 보내는 메시지</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">버튼 라벨</label></div>
                        <div class="cfg-field">
                            <input type="text" name="ai_button_label" value="<?php echo cfgVal($config, 'ai_button_label', 'AI 상담'); ?>" class="cfg-input" maxlength="20" style="width:200px;">
                            <div class="cfg-hint">AI 챗봇 버튼 위에 표시되는 텍스트</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">버튼 색상</label></div>
                        <div class="cfg-field">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="color" id="ai_color_picker" name="ai_button_color" value="<?php echo cfgVal($config, 'ai_button_color', '#667eea'); ?>" style="width:40px;height:32px;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">
                                <input type="text" id="ai_color_text" value="<?php echo cfgVal($config, 'ai_button_color', '#667eea'); ?>" class="cfg-input" style="width:100px;" maxlength="7">
                            </div>
                            <div class="cfg-hint">AI 챗봇 버튼 그라디언트 시작 색상</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 추가 설정 -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <h3 class="text-sm font-semibold text-gray-900">추가 설정</h3>
                </div>
                <div class="cfg-card-body">
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">업무외 안내</label></div>
                        <div class="cfg-field">
                            <textarea name="offline_message" class="cfg-input cfg-textarea" rows="2"><?php echo cfgVal($config, 'offline_message'); ?></textarea>
                            <div class="cfg-hint">위젯 표시 시간 외에 채팅 클릭 시 보여주는 안내 메시지</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">공지사항</label></div>
                        <div class="cfg-field">
                            <textarea name="notice_message" class="cfg-input cfg-textarea" rows="2"><?php echo cfgVal($config, 'notice_message'); ?></textarea>
                            <div class="cfg-hint">채팅창 상단에 고정 표시 (빈 값이면 미표시)</div>
                        </div>
                    </div>
                    <div class="cfg-row">
                        <div class="cfg-label"><label class="text-sm font-medium text-gray-700">파일 용량 제한</label></div>
                        <div class="cfg-field">
                            <div class="cfg-suffix">
                                <input type="number" name="upload_max_mb" value="<?php echo cfgVal($config, 'upload_max_mb', '10'); ?>" class="cfg-input" min="1" max="50">
                                <span>MB</span>
                            </div>
                            <div class="cfg-hint">채팅에서 업로드할 수 있는 파일 최대 크기</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="flex justify-end mb-8">
            <button onclick="saveConfig()" class="inline-flex items-center px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                설정 저장
            </button>
        </div>
    </div>
</main>

<script>
// 위치 피커 로직
(function() {
    var preview = document.getElementById('pos-preview');
    var dotW = document.getElementById('pos-dot-widget');
    var dotA = document.getElementById('pos-dot-ai');
    var active = 'widget';
    dotW.classList.add('active');

    function selectDot(which) {
        active = which;
        dotW.classList.toggle('active', which === 'widget');
        dotA.classList.toggle('active', which === 'ai');
    }

    function moveDot(dot, x, y) {
        x = Math.max(2, Math.min(98, x));
        y = Math.max(2, Math.min(98, y));
        dot.style.left = x + '%';
        dot.style.top = y + '%';
        var pfx = dot === dotW ? 'widget' : 'ai';
        var rx = Math.round(x), ry = Math.round(y);
        document.getElementById(pfx + '_pos_x').value = rx;
        document.getElementById(pfx + '_pos_y').value = ry;
        document.getElementById('pos-' + (pfx === 'widget' ? 'w' : 'a') + 'x-text').textContent = rx;
        document.getElementById('pos-' + (pfx === 'widget' ? 'w' : 'a') + 'y-text').textContent = ry;
    }

    function getPct(e) {
        var r = preview.getBoundingClientRect();
        return { x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 };
    }

    dotW.addEventListener('mousedown', function(e) { e.stopPropagation(); selectDot('widget'); startDrag(dotW, e); });
    dotA.addEventListener('mousedown', function(e) { e.stopPropagation(); selectDot('ai'); startDrag(dotA, e); });

    preview.addEventListener('click', function(e) {
        if (e.target.classList.contains('pos-dot')) return;
        var p = getPct(e);
        var dot = active === 'widget' ? dotW : dotA;
        moveDot(dot, p.x, p.y);
    });

    var dragDot = null;
    function startDrag(dot, e) {
        dragDot = dot;
        e.preventDefault();
    }
    document.addEventListener('mousemove', function(e) {
        if (!dragDot) return;
        var p = getPct(e);
        moveDot(dragDot, p.x, p.y);
    });
    document.addEventListener('mouseup', function() { dragDot = null; });

    // 컬러 피커 동기화
    var cp = document.getElementById('ai_color_picker');
    var ct = document.getElementById('ai_color_text');
    if (cp && ct) {
        cp.addEventListener('input', function() { ct.value = cp.value; });
        ct.addEventListener('input', function() { if (/^#[0-9a-fA-F]{6}$/.test(ct.value)) cp.value = ct.value; });
    }

    window.resetPositions = function() {
        moveDot(dotW, 92, 85);
        moveDot(dotA, 92, 60);
    };
})();

function saveConfig() {
    var form = document.getElementById('config-form');
    var data = {};

    form.querySelectorAll('input[type="text"], input[type="number"], input[type="time"], input[type="hidden"], input[type="color"], textarea').forEach(function(el) {
        if (el.name) data[el.name] = el.value;
    });

    form.querySelectorAll('input[type="radio"]:checked').forEach(function(el) {
        data[el.name] = el.value;
    });

    ['widget_enabled', 'ai_enabled'].forEach(function(key) {
        var cb = form.querySelector('input[name="' + key + '"]');
        data[key] = cb && cb.checked ? '1' : '0';
    });

    data.action = 'update_chat_config';

    fetch('/chat/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var alertEl = document.getElementById('save-alert');
        if (res.success) {
            alertEl.className = 'mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-green-50 text-green-700 border border-green-200';
            alertEl.textContent = res.data.message;
        } else {
            alertEl.className = 'mb-4 px-4 py-3 rounded-lg text-sm font-medium bg-red-50 text-red-700 border border-red-200';
            alertEl.textContent = res.message || '저장 실패';
        }
        alertEl.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(function() { alertEl.style.display = 'none'; }, 4000);
    })
    .catch(function(err) {
        alert('네트워크 오류: ' + err.message);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
