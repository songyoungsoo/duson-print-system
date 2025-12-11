<?php
/**
 * 공통 파일 업로드 모달
 * 모든 MlangPrintAuto 품목에서 공통으로 사용
 * 
 * @version 1.0
 * @date 2025-01-08
 */

// 제품별 커스터마이징을 위한 기본값 설정
$modal_title = isset($modalTitle) ? $modalTitle : "파일첨부방법 선택";
$modal_product_icon = isset($modalProductIcon) ? $modalProductIcon : "📎";
$modal_product_name = isset($modalProductName) ? $modalProductName : "";

// 완전한 제목 조합
$full_modal_title = $modal_product_icon . " " . $modal_product_name . " " . $modal_title;
?>

<!-- 공통 파일 업로드 모달 -->
<div id="uploadModal" class="upload-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeUploadModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo htmlspecialchars($full_modal_title); ?></h3>
            <button type="button" class="modal-close" onclick="closeUploadModal()">✕</button>
        </div>
        
        <div class="modal-body">
            <div class="upload-container">
                <div class="upload-left">
                    <label class="upload-label" for="modalFileInput">파일첨부</label>
                    <div class="upload-buttons">
                        <button type="button" class="btn-upload-method active" onclick="selectUploadMethod('upload')">
                            파일업로드
                        </button>
                        <button type="button" class="btn-upload-method" onclick="selectUploadMethod('manual')" disabled>
                            디자인 의뢰 (별도 문의)
                        </button>
                    </div>
                    <div class="upload-area" id="modalUploadArea">
                        <div class="upload-dropzone" id="modalUploadDropzone">
                            <span class="upload-icon">📁</span>
                            <span class="upload-text">파일을 여기에 드래그하거나 클릭하세요</span>
                            <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.psd,.zip" multiple hidden>
                        </div>
                        <div class="upload-info">
                            파일첨부 시 특수문자(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생할 수 있습니다.<br>
                            되도록 짧고 간단한 파일명으로 작성해 주세요!
                        </div>
                    </div>
                </div>
                
                <div class="upload-right">
                    <label class="upload-label">작업메모</label>
                    <textarea id="modalWorkMemo" class="memo-textarea" placeholder="특별한 요청사항이 있으시면 입력해주세요...&#10;&#10;예: 색상 조정, 크기 변경, 레이아웃 수정 등"></textarea>
                    
                    <div class="upload-notice">
                        <div class="notice-item">🖨️ 인쇄 품질 향상을 위해 고해상도 파일을 권장합니다</div>
                        <div class="notice-item">📐 재단선이 있는 경우 3mm 여백을 추가해 주세요</div>
                    </div>
                </div>
            </div>
            
            <div class="uploaded-files" id="modalUploadedFiles" style="display: none;">
                <h5>📂 업로드된 파일</h5>
                <div class="file-list" id="modalFileList"></div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="modal-btn btn-cart" onclick="addToBasketFromModal()">
                🛒 장바구니에 저장
            </button>
        </div>
    </div>
</div>