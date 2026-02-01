<?php if($pp=="form"){ 
// 변수 초기화
$tt = isset($_GET['tt']) ? $_GET['tt'] : (isset($_POST['tt']) ? $_POST['tt'] : '');
$table = isset($table) ? $table : '';
$page = isset($page) ? $page : '';
$offset = isset($offset) ? $offset : '';
$no = isset($no) ? $no : '';
$WebtingMemberLogin_id = isset($WebtingMemberLogin_id) ? $WebtingMemberLogin_id : '';
$BBS_ADMIN_write_select = isset($BBS_ADMIN_write_select) ? $BBS_ADMIN_write_select : 'guest';
$BBS_ADMIN_cate = isset($BBS_ADMIN_cate) ? $BBS_ADMIN_cate : '';
$BBS_ADMIN_secret_select = isset($BBS_ADMIN_secret_select) ? $BBS_ADMIN_secret_select : 'yes';
$BBS_ADMIN_file_select = isset($BBS_ADMIN_file_select) ? $BBS_ADMIN_file_select : 'yes';
$BBS_ADMIN_link_select = isset($BBS_ADMIN_link_select) ? $BBS_ADMIN_link_select : 'yes';

// 수정 시 기존 데이터 변수들 초기화
$BbsViewMlang_bbs_member = isset($BbsViewMlang_bbs_member) ? $BbsViewMlang_bbs_member : '';
$BbsViewMlang_bbs_title = isset($BbsViewMlang_bbs_title) ? $BbsViewMlang_bbs_title : '';
$BbsViewMlang_bbs_connent = isset($BbsViewMlang_bbs_connent) ? $BbsViewMlang_bbs_connent : '';
$BbsViewMlang_bbs_file = isset($BbsViewMlang_bbs_file) ? $BbsViewMlang_bbs_file : '';
$BbsViewMlang_bbs_link = isset($BbsViewMlang_bbs_link) ? $BbsViewMlang_bbs_link : '';
$BbsViewMlang_bbs_secret = isset($BbsViewMlang_bbs_secret) ? $BbsViewMlang_bbs_secret : 'yes';

$Write_Style1="style='padding:8px; border:1px solid #ddd; border-radius:4px; font-size:14px; width:100%; box-sizing:border-box;'";	

  $end=2547;
  $num=rand(0,$end);
?>

<!DOCTYPE html>
<html>
<head>
<title>포트폴리오 글쓰기 - <?php echo $BBS_ADMIN_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
body {
    font-family: 'Noto Sans KR', Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 20px;
}

.write-container {
    max-width: 800px;
    margin: 0 auto;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.write-title {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
    font-size: 24px;
    font-weight: bold;
    border-bottom: 2px solid #4a6da7;
    padding-bottom: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
    font-size: 14px;
}

.form-control {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #4a6da7;
    box-shadow: 0 0 0 2px rgba(74, 109, 167, 0.2);
}

.form-control-file {
    padding: 8px;
    border: 2px dashed #ddd;
    border-radius: 4px;
    background-color: #fafafa;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-control-file:hover {
    border-color: #4a6da7;
    background-color: #f0f4f8;
}

.drag-drop-area {
    min-height: 120px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    background-color: #fafafa;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.drag-drop-area:hover {
    border-color: #4a6da7;
    background-color: #f0f4f8;
}

.drag-drop-area.dragover {
    border-color: #4a6da7;
    background-color: #e3f2fd;
    transform: scale(1.02);
}

.drag-drop-area .upload-icon {
    font-size: 24px;
    color: #666;
    margin-bottom: 8px;
}

.drag-drop-area .upload-text {
    color: #666;
    font-size: 14px;
    margin-bottom: 4px;
}

.drag-drop-area .upload-hint {
    color: #999;
    font-size: 12px;
}

.drag-drop-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.image-preview-container {
    margin-top: 15px;
    text-align: center;
}

.image-preview-container img {
    max-width: 300px;
    max-height: 200px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.file-info {
    margin-top: 10px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
}

.remove-image-btn {
    margin-top: 10px;
    padding: 5px 10px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.remove-image-btn:hover {
    background-color: #c82333;
}

.form-help {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.radio-group {
    display: flex;
    gap: 20px;
    align-items: center;
}

.radio-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.category-select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: white;
    cursor: pointer;
}

.captcha-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.captcha-display {
    font-size: 20px;
    font-weight: bold;
    color: #4a6da7;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    filter: blur(0.5px);
    user-select: none;
}

.button-group {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    margin: 0 5px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #4a6da7;
    color: white;
}

.btn-primary:hover {
    background-color: #3a5a8c;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-outline {
    background-color: white;
    color: #6c757d;
    border: 1px solid #6c757d;
}

.btn-outline:hover {
    background-color: #6c757d;
    color: white;
}

.notice-box {
    background-color: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 4px;
    padding: 15px;
    margin-top: 20px;
    color: #1976d2;
    font-size: 13px;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .write-container {
        padding: 20px;
        margin: 10px;
    }
    
    .radio-group {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
// 폼 유효성 검사 함수
function board_writeCheckField() {
    var f = document.board_write;
    
    // 이름 확인
    if (!f.name.value.trim()) {
        alert("작성자 이름을 입력해주세요.");
        f.name.focus();
        return false;
    }
    
    <?php if($BBS_ADMIN_write_select != "member"): ?>
    // 비밀번호 확인 (비회원인 경우)
    if (!f.pass.value.trim()) {
        <?php if($tt=="modify"): ?>
        alert("글을 수정하시려면 작성시의 비밀번호를 입력하셔야 합니다.");
        <?php else: ?>
        alert("차후 수정을 위해 비밀번호를 입력해주세요.");
        <?php endif; ?>
        f.pass.focus();
        return false;
    }
    
    if (f.pass.value.length < 4) {
        alert("비밀번호는 4자 이상 입력해주세요.");
        f.pass.focus();
        return false;
    }
    <?php endif; ?>
    
    // 제목 확인
    if (!f.title.value.trim()) {
        alert("제목을 입력해주세요.");
        f.title.focus();
        return false;
    }
    
    if (f.title.value.length > 100) {
        alert("제목은 100자 이내로 입력해주세요.");
        f.title.focus();
        return false;
    }
    
    <?php if($BBS_ADMIN_cate): ?>
    // 카테고리 확인
    if (f.TX_cate.value == "0" || !f.TX_cate.value) {
        alert("카테고리를 선택해주세요.");
        f.TX_cate.focus();
        return false;
    }
    <?php endif; ?>
    
    // 포트폴리오 이미지 확인 (수정시에는 선택사항)
    <?php if($tt !== "modify"): ?>
    if (!f.upfile.value && f.upfile.files && f.upfile.files.length === 0) {
        alert("포트폴리오에 표시될 이미지를 선택해주세요.");
        f.upfile.focus();
        return false;
    }
    <?php endif; ?>
    
    // 파일 형식 확인
    if (f.upfile.value) {
        var allowedTypes = /(\.jpg|\.jpeg|\.png|\.gif|\.bmp)$/i;
        if (!allowedTypes.exec(f.upfile.value)) {
            alert("이미지 파일만 업로드 가능합니다. (jpg, jpeg, png, gif, bmp)");
            f.upfile.focus();
            return false;
        }
    }
    
    // 보안 코드 확인
    if (!f.check_num.value.trim()) {
        alert("보안 코드를 입력해주세요.");
        f.check_num.focus();
        return false;
    }
    
    if (f.check_num.value != f.num.value) {
        alert("보안 코드가 올바르지 않습니다.");
        f.check_num.focus();
        return false;
    }
    
    return true;
}

// 드래그 앤 드롭 이벤트 처리
function initializeDragDrop() {
    var dragArea = document.getElementById('drag-drop-area');
    var fileInput = document.getElementById('upfile');
    
    if (!dragArea || !fileInput) return;
    
    // 드래그 오버 이벤트
    dragArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragArea.classList.add('dragover');
    });
    
    // 드래그 나가기 이벤트
    dragArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragArea.classList.remove('dragover');
    });
    
    // 드롭 이벤트
    dragArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dragArea.classList.remove('dragover');
        
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            var file = files[0];
            
            // 이미지 파일인지 확인
            if (file.type.startsWith('image/')) {
                // DataTransfer 객체 생성하여 파일 입력 필드에 설정
                var dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                
                // 파일 처리
                handleFileSelection(file, fileInput);
            } else {
                alert('이미지 파일만 업로드 가능합니다.');
            }
        }
    });
    
    // 클릭 이벤트
    dragArea.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });
}

// 파일 선택 처리 함수
function handleFileSelection(file, input) {
    // 파일 크기 확인
    var maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        alert("파일 크기가 너무 큽니다. 5MB 이하의 파일을 선택해주세요.");
        input.value = '';
        clearImagePreview();
        return false;
    }
    
    // 파일 형식 확인
    var allowedTypes = /^image\/(jpeg|jpg|png|gif|bmp)$/i;
    if (!allowedTypes.test(file.type)) {
        alert("이미지 파일만 업로드 가능합니다. (jpg, jpeg, png, gif, bmp)");
        input.value = '';
        clearImagePreview();
        return false;
    }
    
    // 미리보기 생성
    createImagePreview(file);
    
    // 파일명을 제목에 자동 입력 (확장자 제거)
    autoFillTitle(file.name);
    
    return true;
}

// 파일명으로 제목 자동 입력
function autoFillTitle(filename) {
    var titleInput = document.querySelector('input[name="title"]');
    if (titleInput && !titleInput.value.trim()) {
        // 확장자 제거
        var nameWithoutExt = filename.replace(/\.[^/.]+$/, "");
        // 특수문자를 공백으로 변경
        var cleanName = nameWithoutExt.replace(/[_\-]/g, ' ');
        
        // 제목 입력에 시각적 효과 추가
        titleInput.style.transition = 'all 0.3s ease';
        titleInput.style.backgroundColor = '#e8f5e8';
        titleInput.style.borderColor = '#28a745';
        titleInput.value = cleanName;
        
        // 자동 입력 알림
        showAutoFillNotification(cleanName);
        
        // 3초 후 원래 스타일로 복원
        setTimeout(function() {
            titleInput.style.backgroundColor = '';
            titleInput.style.borderColor = '';
        }, 3000);
    }
}

// 자동 입력 알림 표시
function showAutoFillNotification(title) {
    // 기존 알림 제거
    var existingNotification = document.getElementById('auto-fill-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // 새 알림 생성
    var notification = document.createElement('div');
    notification.id = 'auto-fill-notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        padding: 12px 16px;
        font-size: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    notification.innerHTML = `
        <strong>✅ 제목 자동 입력됨</strong><br>
        "${title}"<br>
        <small>필요시 직접 수정 가능합니다</small>
    `;
    
    document.body.appendChild(notification);
    
    // 5초 후 자동 제거
    setTimeout(function() {
        if (notification && notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(function() {
                if (notification && notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// CSS 애니메이션 추가
if (!document.getElementById('auto-fill-styles')) {
    var style = document.createElement('style');
    style.id = 'auto-fill-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// 이미지 미리보기 생성
function createImagePreview(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var previewContainer = document.getElementById('image-preview-container');
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.id = 'image-preview-container';
            previewContainer.className = 'image-preview-container';
            
            var dragArea = document.getElementById('drag-drop-area');
            dragArea.parentNode.insertBefore(previewContainer, dragArea.nextSibling);
        }
        
        var fileInfo = formatFileSize(file.size);
        
        previewContainer.innerHTML = 
            '<img src="' + e.target.result + '" alt="미리보기">' +
            '<div class="file-info">' +
                '<strong>파일명:</strong> ' + file.name + '<br>' +
                '<strong>크기:</strong> ' + fileInfo + '<br>' +
                '<strong>형식:</strong> ' + file.type +
            '</div>' +
            '<button type="button" class="remove-image-btn" onclick="removeImage()">이미지 제거</button>';
        
        // 드래그 영역 숨기기
        document.getElementById('drag-drop-area').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// 파일 크기 포맷팅
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    var k = 1024;
    var sizes = ['Bytes', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 이미지 제거
function removeImage() {
    var fileInput = document.getElementById('upfile');
    var previewContainer = document.getElementById('image-preview-container');
    var dragArea = document.getElementById('drag-drop-area');
    
    fileInput.value = '';
    if (previewContainer) {
        previewContainer.remove();
    }
    if (dragArea) {
        dragArea.style.display = 'flex';
    }
}

// 미리보기 초기화
function clearImagePreview() {
    var previewContainer = document.getElementById('image-preview-container');
    var dragArea = document.getElementById('drag-drop-area');
    
    if (previewContainer) {
        previewContainer.remove();
    }
    if (dragArea) {
        dragArea.style.display = 'flex';
    }
}

// 기존 함수들 (호환성 유지)
function previewImage(input) {
    if (input.files && input.files[0]) {
        handleFileSelection(input.files[0], input);
    }
}

function checkFileSize(input) {
    if (input.files && input.files[0]) {
        return handleFileSelection(input.files[0], input);
    }
    return true;
}

// 페이지 로드시 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop();
});
</script>
</head>

<body>
<div class="write-container">
    <h1 class="write-title">
        <?php 
        if($tt=="modify") {
            echo "포트폴리오 수정";
        } else {
            echo "포트폴리오 등록";
        }
        ?>
    </h1>
    
    <form name='board_write' method='post' enctype='multipart/form-data' onsubmit='return board_writeCheckField()' action='<?php echo $BbsDir ?>/bbs.php'>
        <input type='hidden' name='table' value='<?php echo $table?>'>
        <input type='hidden' name='page' value='<?php echo $page?>'>
        <input type='hidden' name='offset' value='<?php echo $offset?>'>

        <?php
        // 현재 시간을 timestamp로 가져오기
        $GGHtime = time();
        ?>
        <input type='hidden' name='WriteTime' value='<?php echo $GGHtime+20?>'>

        <?php if($tt=="reply"): ?>
        <input type='hidden' name='reply' value='<?php echo $no?>'>
        <input type='hidden' name='mode' value='write_ok'>
        <?php elseif($tt=="modify"): ?>
        <input type='hidden' name='mode' value='modify_ok'>
        <input type='hidden' name='no' value='<?php echo $no?>'>
        <?php else: ?>
        <input type='hidden' name='reply' value='0'>
        <input type='hidden' name='mode' value='write_ok'>
        <?php endif; ?>

        <!-- 작성자 필드 -->
        <div class="form-group">
            <label class="form-label" for="name">작성자</label>
            <?php if($BBS_ADMIN_write_select=="member" && $WebtingMemberLogin_id): ?>
                <input type='hidden' name='name' value='<?php echo $WebtingMemberLogin_id?>'>
                <input type="text" class="form-control" value="<?php echo $WebtingMemberLogin_id?>" readonly>
            <?php else: ?>
                <input type='text' name='name' class="form-control" maxlength='20' 
                       value='<?php if($tt=="modify"){echo htmlspecialchars($BbsViewMlang_bbs_member);}else if($WebtingMemberLogin_id){echo htmlspecialchars($WebtingMemberLogin_id);}?>' 
                       placeholder="작성자 이름을 입력하세요">
            <?php endif; ?>
        </div>

        <?php if($BBS_ADMIN_write_select != "member"): ?>
        <!-- 비밀번호 필드 -->
        <div class="form-group">
            <label class="form-label" for="pass">비밀번호</label>
            <input type='password' name='pass' class="form-control" maxlength='20' 
                   placeholder="4자 이상의 비밀번호를 입력하세요">
            <div class="form-help">
                <?php if($tt=="modify"): ?>
                    글을 등록할 당시의 비밀번호를 입력해주세요.
                <?php else: ?>
                    글 수정시 필요한 비밀번호입니다.
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 제목 필드 -->
        <div class="form-group">
            <label class="form-label" for="title">제목 *</label>
            <input type='text' name='title' class="form-control" maxlength='100' 
                   value='<?php if($tt=="modify"){echo htmlspecialchars($BbsViewMlang_bbs_title);}?>' 
                   placeholder="포트폴리오 제목을 입력하세요">
        </div>

        <?php if($BBS_ADMIN_cate): ?>
        <!-- 카테고리 필드 -->
        <div class="form-group">
            <label class="form-label" for="TX_cate">카테고리 *</label>
            <select name="TX_cate" class="form-control category-select">
                <option value="0">카테고리를 선택하세요</option>
                <option value="inserted">전단지</option>
                <option value="leaflet">리플렛</option>
                <option value="namecard">명함</option>
                <option value="sticker">스티커</option>
                <option value="msticker">자석스티커</option>
                <option value="envelope">봉투</option>
                <option value="form">양식지</option>
                <option value="catalog">카달로그</option>
                <option value="brochure">브로슈어</option>
                <option value="bookdesign">북디자인</option>
                <option value="poster">포스터</option>
                <option value="coupon">쿠폰</option>
                <option value="logo">심볼/로고</option>
            </select>
        </div>
        <?php endif; ?>

        <?php if($BBS_ADMIN_secret_select=="yes"): ?>
        <!-- 공개 여부 필드 -->
        <div class="form-group">
            <label class="form-label">공개 여부</label>
            <div class="radio-group">
                <div class="radio-item">
                    <input type='radio' name='secret' value='yes' id="secret_yes" 
                           <?php if($tt=="modify") { if($BbsViewMlang_bbs_secret=="yes") echo "checked"; } else echo "checked"; ?>>
                    <label for="secret_yes">공개</label>
                </div>
                <div class="radio-item">
                    <input type='radio' name='secret' value='no' id="secret_no" 
                           <?php if($tt=="modify" && $BbsViewMlang_bbs_secret=="no") echo "checked"; ?>>
                    <label for="secret_no">비공개</label>
                </div>
            </div>
            <div class="form-help">비공개로 설정하면 관리자만 볼 수 있습니다.</div>
        </div>
        <?php endif; ?>

        <?php if($BBS_ADMIN_file_select=="yes"): ?>
        <!-- 포트폴리오 이미지 (필수) -->
        <div class="form-group">
            <label class="form-label" for="upfile">포트폴리오 이미지 *</label>
            <?php if($tt=="modify"): ?>
                <div class="form-help" style="margin-bottom: 10px;">
                    <input type='checkbox' name='uploadModify' value='yes' id="change_upfile"> 
                    <label for="change_upfile">파일을 변경하려면 체크해주세요</label>
                    <br><strong>현재 파일:</strong> <?php echo htmlspecialchars($BbsViewMlang_bbs_file); ?>
                </div>
            <?php endif; ?>
            
            <!-- 드래그 앤 드롭 영역 -->
            <div id="drag-drop-area" class="drag-drop-area">
                <div class="upload-icon">📁</div>
                <div class="upload-text">이미지를 드래그해서 놓거나 클릭하여 선택하세요</div>
                <div class="upload-hint">JPG, PNG, GIF, BMP 파일 지원 (최대 5MB)</div>
                <input type='file' name='upfile' id='upfile' 
                       accept="image/*" onchange="previewImage(this);">
            </div>
            
            <div class="form-help">
                📌 <strong>자동 기능:</strong><br>
                • 이미지를 선택하면 파일명이 제목에 자동으로 입력됩니다<br>
                • 제목은 직접 수정할 수 있습니다<br>
                • 썸네일은 자동으로 생성됩니다
            </div>
        </div>
        <?php endif; ?>

        <?php if($BBS_ADMIN_link_select=="yes"): ?>
        <!-- 외부 링크 -->
        <div class="form-group">
            <label class="form-label" for="link">외부 링크 (선택사항)</label>
            <input type='text' name='link' class="form-control" 
                   value='<?php if($tt=="modify"){echo htmlspecialchars($BbsViewMlang_bbs_link);}?>' 
                   placeholder="http://example.com">
            <div class="form-help">관련 웹사이트나 추가 정보 링크를 입력하세요.</div>
        </div>
        <?php endif; ?>

        <!-- 보안 코드 -->
        <div class="form-group">
            <label class="form-label" for="check_num">보안 코드 *</label>
            <div class="captcha-section">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input name="check_num" type="text" class="form-control" style="width: 150px;" 
                           placeholder="오른쪽 숫자 입력">
                    <div class="captcha-display"><?php echo $num ?></div>
                    <input name="num" value='<?php echo $num ?>' type="hidden">
                </div>
                <div class="form-help">스팸 방지를 위한 보안 코드를 입력해주세요.</div>
            </div>
        </div>

        <!-- 버튼 그룹 -->
        <div class="button-group">
            <?php if($tt=="modify"): ?>
                <input type='submit' value='수정하기' class="btn btn-primary">
            <?php else: ?>
                <input type='submit' value='등록하기' class="btn btn-primary">
            <?php endif; ?>
            <input type='reset' value='다시작성' class="btn btn-secondary">
            <input type='button' value='목록으로' class="btn btn-outline" 
                   onclick="window.location.href='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>?mode=list&table=<?php echo $table?>&page=<?php echo $page?>';">
        </div>

        <div class="notice-box">
            <strong>📌 안내사항</strong><br>
            • 불법 스팸 등록을 방지하기 위해 최소 10초 이상 입력 시간이 필요합니다.<br>
            • 포트폴리오 작품은 저작권을 준수하여 등록해주세요.<br>
            • 부적절한 내용의 게시물은 관리자에 의해 삭제될 수 있습니다.
        </div>

    </form>
</div>
</body>
</html>


<?php } ?>

<?php if($pp=="modify_ok"){  // 글을 수정 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////

// POST 변수 초기화
$name = isset($_POST['name']) ? $_POST['name'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$link = isset($_POST['link']) ? $_POST['link'] : '';
$secret = isset($_POST['secret']) ? $_POST['secret'] : 'yes';
$TX_cate = isset($_POST['TX_cate']) ? $_POST['TX_cate'] : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$no = isset($_POST['no']) ? $_POST['no'] : '';
$table = isset($_POST['table']) ? $_POST['table'] : '';
$page = isset($_POST['page']) ? $_POST['page'] : '';

// 기본 유효성 검사
if (empty($name) || empty($title)) {
    echo "<script>
        alert('작성자와 제목을 모두 입력해주세요.');
        history.go(-1);
    </script>";
    exit;
}

// 디렉토리 설정
if(!$DbDir) {$DbDir="..";}
if(!$BbsDir) {$BbsDir=".";}
include "$DbDir/db.php";

// 기존 게시글 정보 조회
$result_pass = mysqli_query($db, "SELECT * FROM Mlang_{$table}_bbs WHERE Mlang_bbs_no='$no'");
if (!$result_pass || mysqli_num_rows($result_pass) == 0) {
    echo "<script>
        alert('수정할 게시글을 찾을 수 없습니다.');
        window.location.href = '" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table';
    </script>";
    exit;
}

$row_pass = mysqli_fetch_array($result_pass);

// 권한 검증
$can_modify = false;

// 관리자 정보 조회 (users 테이블 사용)
$admin_query = mysqli_query($db, "SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1");
$admin_info = mysqli_fetch_array($admin_query);
$admin_id = $admin_info['id'] ?? '';

if ($BBS_ADMIN_write_select == "member") {
    // 회원 모드: 작성자 본인 또는 관리자
    if ($row_pass['Mlang_bbs_member'] == $WebtingMemberLogin_id) {
        $can_modify = true; // 작성자 본인
    } else if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $admin_id) {
        $can_modify = true; // 관리자
    }
} else {
    // 비회원 모드: 비밀번호 검증
    if ($row_pass['Mlang_bbs_pass'] == $pass) {
        $can_modify = true; // 비밀번호 일치
    } else if ($BBS_ADMIN_pass == $pass) {
        $can_modify = true; // 게시판 관리자 비밀번호
    }
}

if (!$can_modify) {
    echo "<script>
        alert('수정 권한이 없습니다.');
        history.go(-1);
    </script>";
    exit;
}

// 기존 파일 정보 저장
$old_content_file = $row_pass['Mlang_bbs_connent'];
$old_detail_file = $row_pass['Mlang_bbs_file'];
$new_content_file = $old_content_file;
$new_detail_file = $old_detail_file;

// 포트폴리오 이미지 변경 처리 (단일 업로드)
if (isset($_POST['uploadModify']) && $_POST['uploadModify'] == "yes" && isset($_FILES['upfile'])) {
    include "$BbsDir/upload_secure.php";
    
    if (!empty($UPFILENAME)) {
        // 기존 상세 이미지 파일 삭제
        if ($old_detail_file && file_exists("$BbsDir/upload/$table/$old_detail_file")) {
            unlink("$BbsDir/upload/$table/$old_detail_file");
        }
        
        // 기존 썸네일 파일 삭제
        if ($old_content_file && file_exists("$BbsDir/upload/$table/$old_content_file")) {
            unlink("$BbsDir/upload/$table/$old_content_file");
        }
        
        // 새 파일 설정
        $new_detail_file = $UPFILENAME;
        
        // 썸네일 자동 생성
        $new_content_file = createThumbnail($UPFILENAME, $table, $BbsDir);
        if (empty($new_content_file)) {
            $new_content_file = $UPFILENAME; // 썸네일 생성 실패시 원본 사용
        }
    }
}

// 데이터 정제
$name = mysqli_real_escape_string($db, trim($name));
$title = mysqli_real_escape_string($db, trim($title));
$link = mysqli_real_escape_string($db, trim($link ?? ''));
$secret = mysqli_real_escape_string($db, $secret ?? 'yes');
$TX_cate = mysqli_real_escape_string($db, $TX_cate ?? '');

// 게시글 업데이트
$query = "UPDATE Mlang_{$table}_bbs SET 
    Mlang_bbs_member='$name',
    Mlang_bbs_title='$title',
    Mlang_bbs_link='$link',
    Mlang_bbs_secret='$secret',
    CATEGORY='$TX_cate',
    Mlang_bbs_connent='$new_content_file',
    Mlang_bbs_file='$new_detail_file'
    WHERE Mlang_bbs_no='$no'";

$result = mysqli_query($db, $query);

if (!$result) {
    echo "<script>
        alert('수정 중 오류가 발생했습니다: " . addslashes(mysqli_error($db)) . "');
        history.go(-1);
    </script>";
    exit;
}

// 성공 메시지 및 리다이렉션
echo "<script>
    alert('포트폴리오가 성공적으로 수정되었습니다!');
    window.location.href = '" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table&page=$page';
</script>";
exit;

} ?>


<?php if ($pp == "form_ok") {  // 글을 입력 처리한다.. /////////////////////////////////////////////////////////////////////////////////////////

// POST 변수 초기화
$name = isset($_POST['name']) ? $_POST['name'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$link = isset($_POST['link']) ? $_POST['link'] : '';
$secret = isset($_POST['secret']) ? $_POST['secret'] : 'yes';
$TX_cate = isset($_POST['TX_cate']) ? $_POST['TX_cate'] : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$table = isset($_POST['table']) ? $_POST['table'] : '';
$page = isset($_POST['page']) ? $_POST['page'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$check_num = isset($_POST['check_num']) ? $_POST['check_num'] : '';

// 보안 코드 검증
if ($num != $check_num) {
    echo "<script>
        alert('보안 코드가 올바르지 않습니다.');
        history.go(-1);
    </script>";
    exit;
}

// 기본 유효성 검사
if (empty($name) || empty($title)) {
    echo "<script>
        alert('작성자와 제목을 모두 입력해주세요.');
        history.go(-1);
    </script>";
    exit;
}

// 디렉토리 설정
if (!$DbDir) {
    $DbDir = "..";
}
if (!$BbsDir) {
    $BbsDir = ".";
}

include "$DbDir/db.php";

// 보안이 강화된 업로드 처리
include "$BbsDir/upload_secure.php";

// 상세 이미지는 필수
if (empty($UPFILENAME)) {
    echo "<script>
        alert('포트폴리오 이미지를 업로드해주세요.');
        history.go(-1);
    </script>";
    exit;
}

// 썸네일 자동 생성
$CONTENTNAME = '';
if (!empty($UPFILENAME)) {
    $CONTENTNAME = createThumbnail($UPFILENAME, $table, $BbsDir);
}

// 다음 게시글 번호 조회
$result = mysqli_query($db, "SELECT MAX(Mlang_bbs_no) FROM Mlang_{$table}_bbs");
if (!$result) {
    echo "<script>
        alert('데이터베이스 오류가 발생했습니다.');
        history.go(-1);
    </script>";
    exit;
}

$row = mysqli_fetch_row($result);
$new_no = $row[0] ? $row[0] + 1 : 1;

// 데이터 정제
$name = mysqli_real_escape_string($db, trim($name));
$title = mysqli_real_escape_string($db, trim($title));
$link = mysqli_real_escape_string($db, trim($link ?? ''));
$secret = mysqli_real_escape_string($db, $secret ?? 'yes');
$TX_cate = mysqli_real_escape_string($db, $TX_cate ?? '');
$pass = mysqli_real_escape_string($db, $pass ?? '');

// 현재 시간
$date = date("Y-m-d H:i:s");

// 게시글 삽입 (실제 테이블 구조에 맞게 수정)
$query = "INSERT INTO Mlang_{$table}_bbs (
    Mlang_bbs_no,
    Mlang_bbs_member,
    Mlang_bbs_title,
    Mlang_bbs_style,
    Mlang_bbs_connent,
    Mlang_bbs_link,
    Mlang_bbs_file,
    Mlang_bbs_pass,
    Mlang_bbs_count,
    Mlang_bbs_rec,
    Mlang_bbs_secret,
    Mlang_bbs_reply,
    Mlang_date,
    CATEGORY,
    NoticeSelect
) VALUES (
    '$new_no',
    '$name',
    '$title',
    'br',
    '$CONTENTNAME',
    '$link',
    '$UPFILENAME',
    '$pass',
    '0',
    '0',
    '$secret',
    '0',
    '$date',
    '$TX_cate',
    'no'
)";

$result_insert = mysqli_query($db, $query);

if (!$result_insert) {
    // 업로드된 파일들 삭제
    if ($CONTENTNAME) {
        unlink("$BbsDir/upload/$table/$CONTENTNAME");
    }
    if ($UPFILENAME) {
        unlink("$BbsDir/upload/$table/$UPFILENAME");
    }
    
    echo "<script>
        alert('게시글 등록 중 오류가 발생했습니다: " . addslashes(mysqli_error($db)) . "');
        history.go(-1);
    </script>";
    exit;
}

// 포트폴리오는 포인트 적립 제외 (무료 서비스)
// $Point_TT_mode = "BoardPointWrite";
// if (file_exists("$BbsDir/PointChick.php")) {
//     include "$BbsDir/PointChick.php";
// }

// 성공 메시지 및 리다이렉션
echo "<script>
    alert('포트폴리오가 성공적으로 등록되었습니다!');
    window.location.href = '" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=list&table=$table';
</script>";
exit;

} ?>