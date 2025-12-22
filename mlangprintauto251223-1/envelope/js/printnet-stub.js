/**
 * PrintNet stub to prevent undefined errors
 * 봉투 시스템에서 PrintNet 참조 오류 방지용 스텁
 */

// 즉시 실행하여 가능한 빨리 PrintNet을 정의
(function() {
    'use strict';

window.PrintNet = {
    // 파일 업로드 관련 스텁 함수들
    fileUpload: function(file, options) {
        console.log('PrintNet.fileUpload 스텁 호출됨:', file, options);
        return Promise.resolve({ success: true });
    },

    // 파일 크기 포맷팅
    fileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // 검증 결과 표시
    showValidationResult: function(message) {
        console.log('PrintNet.showValidationResult 스텁 호출됨:', message);
        if (message) {
            alert(message);
        }
    },

    // 기타 필요한 메서드들 스텁
    init: function() {
        console.log('PrintNet 스텁 초기화됨');
    }
};

console.log('📄 PrintNet 스텁 로드 완료');

})(); // 즉시 실행 함수 종료