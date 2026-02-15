<?php
/**
 * 공통 파일 업로드 모달
 * 모든 MlangPrintAuto 품목에서 공통으로 사용
 * 
 * 두 가지 모드:
 *  1. 완성파일 업로드 (upload) - 인쇄용 완성 파일
 *  2. 디자인 의뢰 (design) - 원고/참고자료 + 디자인 요청
 * 
 * @version 2.1
 * @date 2026-02-15
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
            <h2 class="modal-brand">두손기획인쇄</h2>
            <h3 class="modal-title"><?php echo htmlspecialchars($full_modal_title); ?></h3>
            <button type="button" class="modal-close" onclick="closeUploadModal()">✕</button>
        </div>
        
        <div class="modal-body">
            <!-- 모드 선택 탭 (항상 표시) -->
            <div class="upload-mode-selector">
                <div class="upload-buttons">
                    <button type="button" class="btn-upload-method active" onclick="selectUploadMethod('upload')">
                        완성파일 업로드
                    </button>
                    <button type="button" class="btn-upload-method" onclick="selectUploadMethod('design')">
                        디자인 의뢰
                    </button>
                </div>
            </div>

            <!-- 디자인 의뢰 안내 (Step 1: 읽기 단계) -->
            <div class="design-intro-panel" id="designIntroPanel" style="display:none;">
                <div class="design-intro-header">📋 디자인 의뢰 안내</div>
                
                <div class="design-intro-section">
                    <div class="design-intro-item">📋 원고파일을 업로드해주세요</div>
                    <div class="design-intro-item design-intro-sub">한글(HWP), 엑셀, PPT, 워드, 이미지, PDF, AI, PSD, 메모장, ZIP 등</div>
                    <div class="design-intro-item design-intro-must">⚠️ 회사 로고·마크·약도는 <b>반드시</b> 업로드해주세요</div>
                    <div class="design-intro-item">🎨 참고 디자인이 있으면 이미지나 URL을 함께 첨부해주세요</div>
                    <div class="design-intro-tip">
                        💡 원고파일 외에 <b>샘플 이미지</b>나 <b>원하는 참고 이미지</b>를 함께 넣어주시면<br>
                        편집 디자인 시 효과적인 결과물을 빠른 시간에 받으실 수 있습니다!
                    </div>
                </div>

                <div class="design-intro-memo">
                    <label class="design-intro-memo-label">✏️ 디자인 요청사항을 자세히 적어주세요 — 체계적으로 자세히 적을수록 디자인에 반영됩니다</label>
                    <div class="design-intro-memo-sub">대충 적으면 대충 나옵니다. <span class="design-intro-memo-red">그럴 바엔 샘플 이미지를 첨부해주세요.</span></div>
                    <textarea id="designIntroMemo" class="design-intro-memo-textarea" placeholder="예) 전단지 앞면에 신메뉴 3개 넣어주세요. 로고는 첨부파일에 있습니다.&#10;색상은 빨간색 계열로 해주세요."></textarea>
                </div>
                
                <div class="design-intro-warning">
                    <div class="design-intro-warning-title">📌 디자인 의뢰 시 꼭 확인해주세요</div>
                    <div class="design-intro-warning-item">✅ <b>완성된 원고</b>(텍스트, 이미지 등)를 넘겨주세요</div>
                    <div class="design-intro-warning-item">👔 사내 결재 구조(대리→과장→부장 등)가 있는 경우,<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>최종 승인권자의 승인을 받은 원고</b>로 접수해주세요</div>
                    <div class="design-intro-warning-item">💰 접수 이후 <b>내용 수정</b>은 <b>단계별 수정비용</b>이 청구됩니다</div>
                    <div class="design-intro-warning-item design-intro-highlight">
                        🔄 수정 요청은 <b>2회까지 무료</b>입니다. 3회차부터 <b>추가 요금</b>이 발생합니다<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;교정본을 <b>전체 확인</b> 후 수정사항을 <b>한 번에</b> 요청해주세요<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="design-intro-example">예) 한 글자 수정 후 또 수정 요청 → 각각 1회로 카운트</span>
                    </div>
                    <div class="design-intro-warning-item">💡 디자인비는 작업 난이도에 따라 별도 안내드립니다</div>
                </div>
                
                <button type="button" class="btn-design-proceed" onclick="proceedToDesignUpload()">
                    확인했습니다. 파일 첨부하기 →
                </button>
            </div>

            <!-- 업로드 컨테이너 (완성파일 모드 / 디자인 의뢰 Step 2) -->
            <div class="upload-container" id="uploadContainer">
                <div class="upload-left">
                    <label class="upload-label" id="uploadLeftLabel">파일첨부</label>

                    <!-- 완성파일 모드 안내 -->
                    <div class="upload-mode-guide" id="guideUpload">
                        <div class="guide-item">🖨️ 인쇄용 완성 파일을 업로드해주세요</div>
                        <div class="guide-item">📐 재단선이 있는 경우 3mm 여백을 포함해주세요</div>
                    </div>

                    <!-- 디자인 의뢰 모드 안내 (Step 2 간략 표시) -->
                    <div class="upload-mode-guide" id="guideDesign" style="display:none;">
                        <div class="guide-item">📋 원고파일을 업로드해주세요</div>
                        <div class="guide-item guide-sub">한글(HWP), 엑셀, PPT, 워드, 이미지, PDF, AI, PSD, 메모장, ZIP 등</div>
                        <div class="guide-item guide-must">⚠️ 회사 로고·마크·약도는 <b>반드시</b> 업로드해주세요</div>
                        <div class="guide-item">🎨 참고 디자인이 있으면 이미지나 URL을 함께 첨부해주세요</div>
                    </div>

                    <div class="upload-area" id="modalUploadArea">
                        <div class="upload-dropzone" id="modalUploadDropzone">
                            <span class="upload-icon" id="dropzoneIcon">📁</span>
                            <span class="upload-text" id="dropzoneText">파일을 여기에 드래그하거나 클릭하세요</span>
                            <span class="upload-formats" id="dropzoneFormats">JPG, PNG, PDF, AI, EPS, PSD, ZIP (15MB 이하)</span>
                            <input type="file" id="modalFileInput" accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.psd,.zip" multiple hidden>
                        </div>
                        <div class="upload-info-design" id="uploadInfoDesign" style="display:none;">
                            💡 원고파일 외에 <b>샘플 이미지</b>나 <b>원하는 참고 이미지</b>를 함께 넣어주시면<br>
                            편집 디자인 시 효과적인 결과물을 빠른 시간에 받으실 수 있습니다!
                        </div>
                        <div class="upload-info" id="uploadInfoText">
                            파일첨부 시 특수문자(#,&,'&',*,%, 등) 사용은 불가능하며 파일명이 길면 오류가 발생할 수 있습니다.<br>
                            되도록 짧고 간단한 파일명으로 작성해 주세요!
                        </div>
                    </div>
                </div>
                
                <div class="upload-right">
                    <label class="upload-label" id="uploadRightLabel">작업메모</label>
                    <textarea id="modalWorkMemo" class="memo-textarea" placeholder="특별한 요청사항이 있으시면 입력해주세요...&#10;&#10;예: 색상 조정, 크기 변경, 레이아웃 수정 등"></textarea>
                    
                    <!-- 완성파일 모드 하단 안내 -->
                    <div class="upload-notice" id="noticeUpload">
                        <div class="notice-item">🖨️ 고해상도 파일을 권장합니다 (300dpi 이상)</div>
                        <div class="notice-item">📐 재단선 3mm 여백 포함 필수</div>
                    </div>

                    <!-- 디자인 의뢰 모드 하단 경고 -->
                    <div class="upload-notice upload-notice-warning" id="noticeDesign" style="display:none;">
                        <div class="notice-warning-box">
                            <div class="notice-warning-title">📌 디자인 의뢰 시 꼭 확인해주세요</div>
                            <div class="notice-warning-item">✅ <b>완성된 원고</b>(텍스트, 이미지 등)를 넘겨주세요</div>
                            <div class="notice-warning-item">👔 사내 결재 구조(대리→과장→부장 등)가 있는 경우,<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>최종 승인권자의 승인을 받은 원고</b>로 접수해주세요</div>
                            <div class="notice-warning-item">💰 접수 이후 <b>내용 수정</b>은 <b>단계별 수정비용</b>이 청구됩니다</div>
                            <div class="notice-warning-item notice-warning-highlight">🔄 수정 요청은 <b>2회까지 무료</b>입니다. 3회차부터 <b>추가 요금</b>이 발생합니다<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;교정본을 <b>전체 확인</b> 후 수정사항을 <b>한 번에</b> 요청해주세요<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="notice-warning-example">예) 한 글자 수정 후 또 수정 요청 → 각각 1회로 카운트</span></div>
                            <div class="notice-warning-item">💡 디자인비는 작업 난이도에 따라 별도 안내드립니다</div>
                        </div>
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