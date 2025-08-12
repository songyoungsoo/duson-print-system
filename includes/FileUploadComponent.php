<?php
/**
 * 파일 업로드 컴포넌트
 * 모든 품목에서 재사용 가능한 업로드 시스템
 */

class FileUploadComponent {
    private $config;
    private $session_id;
    
    public function __construct($config = []) {
        $this->session_id = session_id();
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }
    
    /**
     * 기본 설정값
     */
    private function getDefaultConfig() {
        return [
            'product_type' => 'general',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
            'upload_dir' => 'uploads/',
            'multiple' => true,
            'drag_drop' => true,
            'show_progress' => true,
            'auto_upload' => true,
            'delete_enabled' => true,
            'preview_enabled' => true,
            'custom_messages' => []
        ];
    }
    
    /**
     * 업로드 HTML 컴포넌트 렌더링
     */
    public function render() {
        $uniqueId = 'upload_' . uniqid();
        $messages = $this->getMessages();
        
        ob_start();
        ?>
        <div class="file-upload-component" id="<?php echo $uniqueId; ?>">
            <!-- 업로드 섹션 -->
            <div class="upload-section" style="margin: 2rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6;">
                <h4 style="margin-bottom: 1rem; color: #495057;">
                    📁 <?php echo $messages['title']; ?>
                </h4>
                
                <?php if ($this->config['drag_drop']): ?>
                <!-- 드래그 앤 드롭 영역 -->
                <div class="drop-zone" style="
                    border: 2px dashed #007bff; 
                    border-radius: 8px; 
                    padding: 2rem; 
                    text-align: center; 
                    background: white;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    margin-bottom: 1rem;
                ">
                    <div class="drop-zone-content">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📤</div>
                        <p style="margin: 0; font-size: 1.1rem; color: #6c757d;">
                            <strong><?php echo $messages['drop_text']; ?></strong>
                        </p>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #868e96;">
                            <?php echo $messages['format_text']; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 파일 선택 버튼 -->
                <input type="file" class="file-input" 
                       <?php echo $this->config['multiple'] ? 'multiple' : ''; ?>
                       accept="<?php echo implode(',', $this->getAcceptTypes()); ?>" 
                       style="<?php echo $this->config['drag_drop'] ? 'display: none;' : ''; ?>">
                
                <?php if (!$this->config['drag_drop']): ?>
                <button type="button" class="btn-file-select" style="
                    padding: 0.75rem 1.5rem;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-bottom: 1rem;
                ">
                    📁 파일 선택
                </button>
                <?php endif; ?>
                
                <!-- 업로드된 파일 목록 -->
                <div class="file-list" style="margin-top: 1rem;"></div>
                
                <?php if ($this->config['show_progress']): ?>
                <!-- 업로드 진행률 -->
                <div class="upload-progress" style="display: none; margin-top: 1rem;">
                    <div class="progress-bar" style="
                        width: 100%; 
                        height: 20px; 
                        background: #e9ecef; 
                        border-radius: 10px; 
                        overflow: hidden;
                    ">
                        <div class="progress-fill" style="
                            height: 100%; 
                            background: linear-gradient(90deg, #28a745, #20c997); 
                            width: 0%; 
                            transition: width 0.3s ease;
                        "></div>
                    </div>
                    <p class="progress-text" style="margin: 0.5rem 0 0 0; text-align: center; font-size: 0.9rem;"></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        (function() {
            // 컴포넌트별 고유 인스턴스 생성
            const uploadComponent = new UniversalFileUpload('<?php echo $uniqueId; ?>', <?php echo json_encode($this->config); ?>);
        })();
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Accept 타입 문자열 생성
     */
    private function getAcceptTypes() {
        $extensions = array_map(function($ext) {
            return '.' . $ext;
        }, $this->config['allowed_extensions']);
        
        return array_merge($extensions, $this->config['allowed_types']);
    }
    
    /**
     * 메시지 설정
     */
    private function getMessages() {
        $defaults = [
            'title' => '디자인 파일 업로드',
            'drop_text' => '파일을 여기로 드래그하거나 클릭하여 선택하세요',
            'format_text' => '지원 형식: ' . strtoupper(implode(', ', $this->config['allowed_extensions'])) . 
                           ' (최대 ' . ($this->config['max_file_size'] / 1024 / 1024) . 'MB)'
        ];
        
        return array_merge($defaults, $this->config['custom_messages']);
    }
    
    /**
     * JavaScript 라이브러리 포함
     */
    public static function includeAssets() {
        return '<script src="/includes/js/UniversalFileUpload.js"></script>';
    }
}
?>