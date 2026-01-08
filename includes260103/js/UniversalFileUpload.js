/**
 * 범용 파일 업로드 JavaScript 클래스
 * 모든 품목에서 재사용 가능
 */
class UniversalFileUpload {
    constructor(containerId, config = {}) {
        this.container = document.getElementById(containerId);
        this.config = {
            product_type: 'general',
            max_file_size: 10 * 1024 * 1024,
            allowed_types: ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'],
            allowed_extensions: ['jpg', 'jpeg', 'png', 'pdf'],
            upload_url: '../../includes/upload_handler.php',
            get_files_url: '../../includes/get_files_handler.php',
            delete_file_url: '../../includes/delete_file_handler.php',
            multiple: true,
            drag_drop: true,
            show_progress: true,
            auto_upload: true,
            delete_enabled: true,
            preview_enabled: true,
            ...config
        };
        
        this.uploadedFiles = [];
        this.init();
    }
    
    init() {
        this.dropZone = this.container.querySelector('.drop-zone');
        this.fileInput = this.container.querySelector('.file-input');
        this.fileList = this.container.querySelector('.file-list');
        this.uploadProgress = this.container.querySelector('.upload-progress');
        this.progressFill = this.container.querySelector('.progress-fill');
        this.progressText = this.container.querySelector('.progress-text');
        this.btnFileSelect = this.container.querySelector('.btn-file-select');
        
        this.setupEvents();
        this.loadExistingFiles();
    }
    
    setupEvents() {
        if (this.config.drag_drop && this.dropZone) {
            // 드래그 앤 드롭 이벤트
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.preventDefaults, false);
                document.body.addEventListener(eventName, this.preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.highlight.bind(this), false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                this.dropZone.addEventListener(eventName, this.unhighlight.bind(this), false);
            });
            
            this.dropZone.addEventListener('drop', this.handleDrop.bind(this), false);
            this.dropZone.addEventListener('click', () => this.fileInput.click());
        }
        
        if (this.btnFileSelect) {
            this.btnFileSelect.addEventListener('click', () => this.fileInput.click());
        }
        
        if (this.fileInput) {
            this.fileInput.addEventListener('change', this.handleFileSelect.bind(this));
        }
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    highlight() {
        if (this.dropZone) {
            this.dropZone.style.borderColor = '#007bff';
            this.dropZone.style.backgroundColor = '#f8f9ff';
            this.dropZone.style.transform = 'scale(1.02)';
        }
    }
    
    unhighlight() {
        if (this.dropZone) {
            this.dropZone.style.borderColor = '#007bff';
            this.dropZone.style.backgroundColor = 'white';
            this.dropZone.style.transform = 'scale(1)';
        }
    }
    
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    }
    
    handleFileSelect(e) {
        const files = e.target.files;
        this.handleFiles(files);
    }
    
    handleFiles(files) {
        [...files].forEach(file => this.processFile(file));
    }
    
    processFile(file) {
        if (!this.validateFile(file)) return;
        
        if (this.config.auto_upload) {
            this.uploadFile(file);
        } else {
            this.addFileToQueue(file);
        }
    }
    
    validateFile(file) {
        // 파일 크기 검증
        if (file.size > this.config.max_file_size) {
            this.showError(`파일 크기가 ${this.config.max_file_size / 1024 / 1024}MB를 초과합니다: ${file.name}`);
            return false;
        }
        
        // 파일 형식 검증
        if (!this.config.allowed_types.includes(file.type)) {
            this.showError(`지원하지 않는 파일 형식입니다: ${file.name}\n지원 형식: ${this.config.allowed_extensions.join(', ').toUpperCase()}`);
            return false;
        }
        
        return true;
    }
    
    uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('product_type', this.config.product_type);
        formData.append('session_id', this.getSessionId());
        
        // 파일 목록에 추가 (업로드 중 상태)
        const fileItem = this.addFileToList(file, 'uploading');
        
        // 진행률 표시
        if (this.config.show_progress) {
            this.showProgress();
        }
        
        fetch(this.config.upload_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateFileStatus(fileItem, 'success', data.file_info);
                this.uploadedFiles.push(data.file_info);
                this.onFileUploaded(data.file_info);
            } else {
                this.updateFileStatus(fileItem, 'error', null, data.message);
                this.onUploadError(data.message);
            }
        })
        .catch(error => {
            console.error('업로드 오류:', error);
            this.updateFileStatus(fileItem, 'error', null, '업로드 중 오류가 발생했습니다.');
            this.onUploadError(error.message);
        })
        .finally(() => {
            if (this.config.show_progress) {
                this.hideProgress();
            }
        });
    }
    
    addFileToList(file, status) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 0.9rem;
        `;
        
        const fileName = file.name.length > 30 ? file.name.substring(0, 30) + '...' : file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2) + 'MB';
        
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-name" style="font-weight: 600;">${fileName}</span>
                <span class="file-size" style="color: #6c757d; margin-left: 0.5rem;">(${fileSize})</span>
            </div>
            <div class="file-status">
                ${this.getStatusIcon(status)}
            </div>
        `;
        
        this.fileList.appendChild(fileItem);
        return fileItem;
    }
    
    updateFileStatus(fileItem, status, fileInfo = null, errorMessage = null) {
        const statusElement = fileItem.querySelector('.file-status');
        statusElement.innerHTML = this.getStatusIcon(status, fileInfo);
        
        if (status === 'error' && errorMessage) {
            fileItem.style.borderColor = '#dc3545';
            fileItem.title = errorMessage;
        } else if (status === 'success') {
            fileItem.style.borderColor = '#28a745';
        }
    }
    
    getStatusIcon(status, fileInfo = null) {
        switch (status) {
            case 'uploading':
                return '<span style="color: #007bff;">⏳ 업로드 중...</span>';
            case 'success':
                let successHtml = '<span style="color: #28a745;">✅ 완료</span>';
                if (this.config.delete_enabled && fileInfo) {
                    successHtml += `
                        <button onclick="window.deleteUploadedFile(${fileInfo.id}, this)" 
                                style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8rem; margin-left: 0.5rem;">
                            🗑️ 삭제
                        </button>
                    `;
                }
                return successHtml;
            case 'error':
                return '<span style="color: #dc3545;">❌ 실패</span>';
            default:
                return '';
        }
    }
    
    showProgress() {
        if (this.uploadProgress) {
            this.uploadProgress.style.display = 'block';
            this.progressText.textContent = '파일 업로드 중...';
            
            // 가짜 진행률 애니메이션
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                this.progressFill.style.width = progress + '%';
            }, 200);
            
            this.progressInterval = interval;
        }
    }
    
    hideProgress() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }
        
        if (this.progressFill && this.progressText && this.uploadProgress) {
            this.progressFill.style.width = '100%';
            this.progressText.textContent = '업로드 완료!';
            
            setTimeout(() => {
                this.uploadProgress.style.display = 'none';
                this.progressFill.style.width = '0%';
            }, 1000);
        }
    }
    
    loadExistingFiles() {
        fetch(`${this.config.get_files_url}?product_type=${this.config.product_type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                data.files.forEach(file => {
                    const fileItem = this.createExistingFileItem(file);
                    this.fileList.appendChild(fileItem);
                    this.uploadedFiles.push(file);
                });
            }
        })
        .catch(error => {
            console.error('기존 파일 로드 오류:', error);
        });
    }
    
    createExistingFileItem(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.style.cssText = `
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
            border: 1px solid #28a745;
            border-radius: 5px;
            font-size: 0.9rem;
        `;
        
        const fileName = file.original_name.length > 30 ? 
            file.original_name.substring(0, 30) + '...' : file.original_name;
        const fileSize = (file.file_size / 1024 / 1024).toFixed(2) + 'MB';
        
        fileItem.innerHTML = `
            <div class="file-info">
                <span class="file-name" style="font-weight: 600;">${fileName}</span>
                <span class="file-size" style="color: #6c757d; margin-left: 0.5rem;">(${fileSize})</span>
                <span class="upload-date" style="color: #868e96; margin-left: 0.5rem; font-size: 0.8rem;">
                    ${new Date(file.upload_date).toLocaleString()}
                </span>
            </div>
            <div class="file-actions">
                <span style="color: #28a745; margin-right: 0.5rem;">✅ 업로드 완료</span>
                ${this.config.delete_enabled ? `
                    <button onclick="window.deleteUploadedFile(${file.id}, this)" 
                            style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 0.8rem;">
                        🗑️ 삭제
                    </button>
                ` : ''}
            </div>
        `;
        
        return fileItem;
    }
    
    getSessionId() {
        // PHP 세션 ID를 가져오는 방법 (서버에서 전달받아야 함)
        return document.querySelector('meta[name="session-id"]')?.content || '';
    }
    
    showError(message) {
        alert(message);
    }
    
    // 이벤트 콜백 함수들
    onFileUploaded(fileInfo) {
        // 파일 업로드 완료 시 호출
        console.log('파일 업로드 완료:', fileInfo);
    }
    
    onUploadError(error) {
        // 업로드 오류 시 호출
        console.error('업로드 오류:', error);
    }
    
    onFileDeleted(fileId) {
        // 파일 삭제 완료 시 호출
        console.log('파일 삭제 완료:', fileId);
    }
    
    // 공개 메서드들
    getUploadedFiles() {
        return this.uploadedFiles;
    }
    
    clearAllFiles() {
        this.fileList.innerHTML = '';
        this.uploadedFiles = [];
    }
}

// 전역 파일 삭제 함수
window.deleteUploadedFile = function(fileId, buttonElement) {
    if (!confirm('이 파일을 삭제하시겠습니까?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('file_id', fileId);
    
    fetch('../../includes/delete_file_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 파일 아이템 제거
            const fileItem = buttonElement.closest('.file-item');
            fileItem.remove();
            
            alert('파일이 삭제되었습니다.');
        } else {
            alert('파일 삭제에 실패했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('파일 삭제 오류:', error);
        alert('파일 삭제 중 오류가 발생했습니다.');
    });
};