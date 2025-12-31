<?php
/**
 * 현대적이고 안전한 회원가입 양식
 *
 * 개선사항:
 * - users 테이블 전용
 * - 비밀번호 강도 향상 (특수문자 허용, 8-20자)
 * - 실시간 입력 검증
 * - AJAX 아이디 중복 체크
 * - 세금계산서 이메일 필드 추가
 * - 반응형 디자인
 * - XSS 방지
 */

include "../db.php";

$action = "register_process.php";
$ModifyMode = $_GET['mode'] ?? '';

// 수정 모드일 경우 기존 데이터 로드 (users 테이블)
$userData = null;
if ($ModifyMode === 'view' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>회원가입 - 두손기획인쇄</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans KR', sans-serif;
    background: #f5f5f5;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 40px;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.required-notice {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 12px 16px;
    margin-bottom: 30px;
    border-radius: 4px;
    font-size: 14px;
    color: #856404;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-group label .required {
    color: #dc3545;
    margin-right: 4px;
}

.form-group input[type="text"],
.form-group input[type="password"],
.form-group input[type="email"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #4CAF50;
}

.form-group .input-hint {
    margin-top: 6px;
    font-size: 12px;
    color: #666;
}

.input-with-button {
    display: flex;
    gap: 8px;
}

.input-with-button input {
    flex: 1;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-outline {
    background: white;
    border: 1px solid #4CAF50;
    color: #4CAF50;
}

.btn-outline:hover {
    background: #4CAF50;
    color: white;
}

.phone-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.phone-inputs input {
    flex: 1;
    text-align: center;
}

.phone-inputs span {
    color: #666;
}

.business-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    margin-top: 8px;
}

.business-section h4 {
    margin-bottom: 16px;
    color: #495057;
    font-size: 14px;
}

.privacy-agreement {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.privacy-agreement label {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-bottom: 10px;
}

.privacy-agreement input[type="radio"] {
    margin-right: 8px;
}

.submit-area {
    margin-top: 40px;
    text-align: center;
    display: flex;
    gap: 16px;
    justify-content: center;
}

.submit-area button {
    min-width: 150px;
}

.validation-message {
    margin-top: 6px;
    font-size: 12px;
    display: none;
}

.validation-message.error {
    color: #dc3545;
    display: block;
}

.validation-message.success {
    color: #28a745;
    display: block;
}

.collapsible-section {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-bottom: 16px;
}

.collapsible-header {
    padding: 12px 16px;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}

.collapsible-header:hover {
    background: #e9ecef;
}

.collapsible-content {
    padding: 16px;
    display: none;
}

.collapsible-content.show {
    display: block;
}

/* ===== 사업자 정보 가로 배치 레이아웃 ===== */
.business-info-horizontal {
    margin-bottom: 1rem;
}

.business-info-horizontal .info-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 0.8rem;
}

.business-info-horizontal .info-row-single {
    margin-bottom: 0.8rem;
}

.business-info-horizontal .info-field {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 5px;
    align-items: center;
}

/* 두 번째 필드 (대표자명, 종목) label 너비 조정 */
.business-info-horizontal .info-row .info-field:nth-child(2) {
    grid-template-columns: 70px 1fr;
}

.business-info-horizontal .info-field-full {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 5px;
    align-items: start;
}

.business-info-horizontal .info-field label,
.business-info-horizontal .info-field-full label {
    white-space: nowrap;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    text-align: left;
}

.business-info-horizontal .info-field input,
.business-info-horizontal .info-field-full input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    .phone-inputs {
        flex-direction: column;
    }

    .phone-inputs span {
        display: none;
    }
}
</style>
</head>
<body>

<div class="container">
    <h2>🖨️ 두손기획인쇄 회원가입</h2>
    <p class="subtitle">안전하고 빠른 주문을 위해 회원가입을 해주세요</p>

    <div class="required-notice">
        ⚠️ <span class="required">*</span> 표시된 항목은 필수 입력사항입니다.
    </div>

    <form name="registerForm" method="post" action="<?= htmlspecialchars($action) ?>" onsubmit="return validateForm()">
        <?php if ($ModifyMode === 'view' && $userData): ?>
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($userData['id']) ?>">
        <?php endif; ?>

        <!-- 로그인 정보 -->
        <div class="form-group">
            <label>
                <span class="required">*</span> 아이디
            </label>
            <div class="input-with-button">
                <input
                    type="text"
                    name="id"
                    id="userId"
                    maxlength="20"
                    placeholder="영문자와 숫자 4-20자"
                    value="<?= $userData ? htmlspecialchars($userData['username']) : '' ?>"
                    <?= $ModifyMode === 'view' ? 'readonly' : '' ?>
                    required
                >
                <?php if ($ModifyMode !== 'view'): ?>
                <button type="button" class="btn btn-outline" onclick="checkIdDuplicate()">중복확인</button>
                <?php endif; ?>
            </div>
            <div id="idValidation" class="validation-message"></div>
            <div class="input-hint">4-20자의 영문자와 숫자 조합</div>
        </div>

        <div class="form-group">
            <label>
                <span class="required">*</span> 비밀번호
            </label>
            <input
                type="password"
                name="pass1"
                id="password"
                maxlength="20"
                placeholder="8-20자, 영문+숫자+특수문자 조합 권장"
                onkeyup="checkPasswordStrength()"
                required
            >
            <div id="passwordStrength" class="validation-message"></div>
            <div class="input-hint">8-20자, 영문자, 숫자, 특수문자 포함 권장</div>
        </div>

        <div class="form-group">
            <label>
                <span class="required">*</span> 비밀번호 확인
            </label>
            <input
                type="password"
                name="pass2"
                id="password2"
                maxlength="20"
                placeholder="비밀번호를 다시 입력하세요"
                onkeyup="checkPasswordMatch()"
                required
            >
            <div id="passwordMatch" class="validation-message"></div>
        </div>

        <!-- 기본 정보 -->
        <div class="form-group">
            <label>
                <span class="required">*</span> 업체명/성명
            </label>
            <input
                type="text"
                name="name"
                maxlength="100"
                placeholder="업체명 또는 성명 입력"
                value="<?= $userData ? htmlspecialchars($userData['name']) : '' ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>
                <span class="required">*</span> 전화번호
            </label>
            <input
                type="text"
                name="phone"
                maxlength="20"
                placeholder="02-1234-5678"
                value="<?= $userData ? htmlspecialchars($userData['phone']) : '' ?>"
            >
        </div>

        <div class="form-group">
            <label>
                <span class="required">*</span> 휴대폰
            </label>
            <input
                type="text"
                name="hendphone"
                maxlength="20"
                placeholder="010-1234-5678"
                required
            >
        </div>

        <div class="form-group">
            <label>
                <span class="required">*</span> 이메일
            </label>
            <input
                type="email"
                name="email"
                maxlength="200"
                placeholder="example@dsp1830.shop"
                value="<?= $userData ? htmlspecialchars($userData['email']) : '' ?>"
                required
            >
            <div class="input-hint">주문 내역을 이메일로 발송합니다</div>
        </div>

        <!-- 주소 -->
        <div class="form-group">
            <label>
                <span class="required">*</span> 주소
            </label>
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                <input
                    type="text"
                    id="postcode"
                    placeholder="우편번호"
                    readonly
                    required
                    style="width: 140px; padding: 12px; border: 1px solid #ddd; border-radius: 4px;"
                >
                <button type="button" class="btn btn-secondary" onclick="execDaumPostcode()">우편번호 찾기</button>
            </div>
            <input
                type="text"
                id="address"
                placeholder="주소"
                readonly
                required
                style="width: 100%; margin-bottom: 0.5rem; padding: 12px; border: 1px solid #ddd; border-radius: 4px;"
            >
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                <input
                    type="text"
                    id="detailAddress"
                    placeholder="상세주소"
                    required
                    style="padding: 12px; border: 1px solid #ddd; border-radius: 4px;"
                >
                <input
                    type="text"
                    id="extraAddress"
                    placeholder="참고항목"
                    readonly
                    style="padding: 12px; border: 1px solid #ddd; border-radius: 4px;"
                >
            </div>
            <!-- Hidden fields for form submission -->
            <input type="hidden" name="sample6_postcode" id="hidden_postcode" value="<?= $userData ? htmlspecialchars($userData['postcode']) : '' ?>">
            <input type="hidden" name="sample6_address" id="hidden_address" value="<?= $userData ? htmlspecialchars($userData['address']) : '' ?>">
            <input type="hidden" name="sample6_detailAddress" id="hidden_detailAddress" value="<?= $userData ? htmlspecialchars($userData['detail_address']) : '' ?>">
            <input type="hidden" name="sample6_extraAddress" id="hidden_extraAddress" value="<?= $userData ? htmlspecialchars($userData['extra_address']) : '' ?>">
        </div>

        <!-- 사업자 정보 (선택) -->
        <div class="collapsible-section">
            <div class="collapsible-header" onclick="toggleSection('businessSection')">
                <span>💼 사업자 정보 (선택사항)</span>
                <span>▼</span>
            </div>
            <div id="businessSection" class="collapsible-content">
                <div class="business-section">
                    <div class="business-info-horizontal">
                        <!-- 1줄: 사업자등록번호 + 대표자명 -->
                        <div class="info-row">
                            <div class="info-field">
                                <label>사업자등록번호</label>
                                <input type="text" name="po1" maxlength="12" placeholder="000-00-00000" value="<?= $userData ? htmlspecialchars($userData['business_number']) : '' ?>">
                            </div>
                            <div class="info-field">
                                <label>대표자명</label>
                                <input type="text" name="po3" maxlength="100" placeholder="대표자 성명" value="<?= $userData ? htmlspecialchars($userData['business_owner']) : '' ?>">
                            </div>
                        </div>

                        <!-- 2줄: 사업장 주소 -->
                        <div class="info-row-single">
                            <div style="display: grid; grid-template-columns: 110px 1fr; gap: 5px; align-items: start;">
                                <label style="white-space: nowrap; font-weight: 600; color: #2c3e50; margin: 0; padding-top: 8px;">사업장 주소</label>
                                <div>
                                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <input type="text" id="business_postcode" placeholder="우편번호" readonly style="width: 140px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                        <button type="button" onclick="execBusinessDaumPostcode()" style="background: #3498db; color: white; border: none; padding: 8px 16px; cursor: pointer; border-radius: 3px; white-space: nowrap;">
                                            우편번호 찾기
                                        </button>
                                    </div>
                                    <input type="text" id="business_address_display" placeholder="주소" readonly style="width: 100%; margin-bottom: 0.5rem; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <input type="text" id="business_detailAddress" placeholder="상세주소" style="padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                        <input type="text" id="business_extraAddress" placeholder="참고항목" style="padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>
                                    <input type="hidden" name="po6" id="business_address_hidden" value="<?= $userData ? htmlspecialchars($userData['business_address']) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 3줄: 업태 + 종목 -->
                        <div class="info-row">
                            <div class="info-field">
                                <label>업태</label>
                                <input type="text" name="po4" maxlength="100" placeholder="제조업, 서비스업" value="<?= $userData ? htmlspecialchars($userData['business_type']) : '' ?>">
                            </div>
                            <div class="info-field">
                                <label>종목</label>
                                <input type="text" name="po5" maxlength="100" placeholder="인쇄업, 광고업" value="<?= $userData ? htmlspecialchars($userData['business_item']) : '' ?>">
                            </div>
                        </div>

                        <!-- 4줄: 세금용 메일 -->
                        <div class="info-row-single">
                            <div class="info-field-full">
                                <label>세금용 메일</label>
                                <input type="email" name="po7" maxlength="200" placeholder="세금계산서를 받을 이메일 주소를 입력하세요" value="<?= $userData ? htmlspecialchars($userData['tax_invoice_email']) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <div style="background: #e8f4fd; padding: 0.6rem; border-radius: 4px; margin-top: 0.8rem;">
                        <p style="margin: 0; font-size: 12px; color: #2c3e50;"><strong>안내:</strong></p>
                        <p style="margin: 0.2rem 0 0 0; font-size: 12px; color: #666;">• 세금계산서 발행을 원하시면 정확한 사업자 정보를 입력해주세요</p>
                        <p style="margin: 0.2rem 0 0 0; font-size: 12px; color: #666;">• 사업자등록번호는 하이픈(-) 포함하여 입력해주세요</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 개인정보 동의 -->
        <div class="form-group">
            <div class="privacy-agreement">
                <label>
                    <span class="required">*</span> 개인정보 수집 및 이용 동의
                </label>
                <label>
                    <input type="radio" name="priv" value="1" checked required>
                    동의합니다
                </label>
                <label>
                    <input type="radio" name="priv" value="0">
                    거부합니다
                </label>
                <div style="margin-top: 12px;">
                    <a href="http://www.dsp1830.shop/members/modal2.html" target="_blank" style="color: #4CAF50; text-decoration: none;">
                        📄 개인정보처리방침 및 이용약관 확인하기
                    </a>
                </div>
            </div>
        </div>

        <!-- 제출 버튼 -->
        <div class="submit-area">
            <?php if ($ModifyMode === 'view'): ?>
                <button type="submit" class="btn btn-primary">정보 수정</button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">회원가입</button>
                <button type="reset" class="btn btn-secondary">다시 작성</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Daum 우편번호 API -->
<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script>
// 아이디 중복 체크
let idChecked = false;

function checkIdDuplicate() {
    const userId = document.getElementById('userId').value.trim();
    const validation = document.getElementById('idValidation');

    if (!userId) {
        validation.className = 'validation-message error';
        validation.textContent = '아이디를 입력해주세요.';
        return;
    }

    if (userId.length < 4 || userId.length > 20) {
        validation.className = 'validation-message error';
        validation.textContent = '아이디는 4-20자여야 합니다.';
        return;
    }

    if (!/^[a-zA-Z0-9]+$/.test(userId)) {
        validation.className = 'validation-message error';
        validation.textContent = '아이디는 영문자와 숫자만 사용할 수 있습니다.';
        return;
    }

    // AJAX로 중복 체크
    fetch('id_check_ajax.php?id=' + encodeURIComponent(userId))
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                validation.className = 'validation-message success';
                validation.textContent = '✓ 사용 가능한 아이디입니다.';
                idChecked = true;
            } else {
                validation.className = 'validation-message error';
                validation.textContent = '✗ 이미 사용 중인 아이디입니다.';
                idChecked = false;
            }
        })
        .catch(error => {
            validation.className = 'validation-message error';
            validation.textContent = '중복 확인 중 오류가 발생했습니다.';
            idChecked = false;
        });
}

// 비밀번호 강도 체크
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const validation = document.getElementById('passwordStrength');

    if (password.length === 0) {
        validation.className = 'validation-message';
        validation.textContent = '';
        return;
    }

    if (password.length < 8) {
        validation.className = 'validation-message error';
        validation.textContent = '비밀번호는 최소 8자 이상이어야 합니다.';
        return;
    }

    let strength = 0;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (strength >= 3) {
        validation.className = 'validation-message success';
        validation.textContent = '✓ 강력한 비밀번호입니다.';
    } else if (strength >= 2) {
        validation.className = 'validation-message';
        validation.style.color = '#ffc107';
        validation.style.display = 'block';
        validation.textContent = '⚠ 보통 수준의 비밀번호입니다.';
    } else {
        validation.className = 'validation-message error';
        validation.textContent = '✗ 약한 비밀번호입니다. 영문+숫자+특수문자 조합을 권장합니다.';
    }

    checkPasswordMatch();
}

// 비밀번호 일치 확인
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const password2 = document.getElementById('password2').value;
    const validation = document.getElementById('passwordMatch');

    if (password2.length === 0) {
        validation.className = 'validation-message';
        validation.textContent = '';
        return;
    }

    if (password === password2) {
        validation.className = 'validation-message success';
        validation.textContent = '✓ 비밀번호가 일치합니다.';
    } else {
        validation.className = 'validation-message error';
        validation.textContent = '✗ 비밀번호가 일치하지 않습니다.';
    }
}

// 일반 배송 주소 검색
function execDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            let addr = data.userSelectedType === 'R' ? data.roadAddress : data.jibunAddress;
            let extraAddr = '';

            if (data.userSelectedType === 'R') {
                if (data.bname && /[동|로|가]$/g.test(data.bname)) {
                    extraAddr += data.bname;
                }
                if (data.buildingName && data.apartment === 'Y') {
                    extraAddr += (extraAddr ? ', ' + data.buildingName : data.buildingName);
                }
                if (extraAddr) {
                    extraAddr = ' (' + extraAddr + ')';
                }
            }

            document.getElementById('postcode').value = data.zonecode;
            document.getElementById('address').value = addr;
            document.getElementById('extraAddress').value = extraAddr;
            document.getElementById('detailAddress').focus();

            // Hidden 필드 업데이트
            document.getElementById('hidden_postcode').value = data.zonecode;
            document.getElementById('hidden_address').value = addr;
            document.getElementById('hidden_extraAddress').value = extraAddr;
        }
    }).open();
}

// 상세주소 입력 시 hidden 필드 업데이트
document.addEventListener('DOMContentLoaded', function() {
    // 기존 주소 데이터를 표시 필드에 로드
    const hiddenPostcode = document.getElementById('hidden_postcode');
    const hiddenAddress = document.getElementById('hidden_address');
    const hiddenDetailAddress = document.getElementById('hidden_detailAddress');
    const hiddenExtraAddress = document.getElementById('hidden_extraAddress');

    if (hiddenPostcode && hiddenPostcode.value) {
        document.getElementById('postcode').value = hiddenPostcode.value;
    }
    if (hiddenAddress && hiddenAddress.value) {
        document.getElementById('address').value = hiddenAddress.value;
    }
    if (hiddenDetailAddress && hiddenDetailAddress.value) {
        document.getElementById('detailAddress').value = hiddenDetailAddress.value;
    }
    if (hiddenExtraAddress && hiddenExtraAddress.value) {
        document.getElementById('extraAddress').value = hiddenExtraAddress.value;
    }

    // 상세주소 입력 이벤트
    const detailInput = document.getElementById('detailAddress');
    if (detailInput) {
        detailInput.addEventListener('input', function() {
            document.getElementById('hidden_detailAddress').value = this.value;
        });
    }
});

// 사업장 주소 검색
function execBusinessDaumPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            var addr = '';
            var extraAddr = '';

            if (data.userSelectedType === 'R') {
                addr = data.roadAddress;
            } else {
                addr = data.jibunAddress;
            }

            if(data.userSelectedType === 'R'){
                if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                    extraAddr += data.bname;
                }
                if(data.buildingName !== '' && data.apartment === 'Y'){
                    extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                }
                if(extraAddr !== ''){
                    extraAddr = ' (' + extraAddr + ')';
                }
                document.getElementById("business_extraAddress").value = extraAddr;
            } else {
                document.getElementById("business_extraAddress").value = '';
            }

            document.getElementById('business_postcode').value = data.zonecode;
            document.getElementById('business_address_display').value = addr;
            document.getElementById("business_detailAddress").focus();

            // hidden 필드 업데이트
            updateBusinessAddress();
        }
    }).open();
}

// 사업장 주소 필드 변경 시 hidden 필드 업데이트
function updateBusinessAddress() {
    const postcode = document.getElementById('business_postcode')?.value || '';
    const address = document.getElementById('business_address_display')?.value || '';
    const detailAddress = document.getElementById('business_detailAddress')?.value || '';
    const extraAddress = document.getElementById('business_extraAddress')?.value || '';

    let fullAddress = '';
    if (postcode) fullAddress += '[' + postcode + '] ';
    if (address) fullAddress += address;
    if (detailAddress) fullAddress += ' ' + detailAddress;
    if (extraAddress) fullAddress += ' ' + extraAddress;

    const hiddenInput = document.getElementById('business_address_hidden');
    if (hiddenInput) {
        hiddenInput.value = fullAddress.trim();
    }
}

// 페이지 로드 시 기존 사업장 주소 분리
window.addEventListener('DOMContentLoaded', function() {
    const businessAddress = document.getElementById('business_address_hidden')?.value;
    if (businessAddress) {
        // [우편번호] 주소 상세주소 (참고항목) 형식 파싱
        const postcodeMatch = businessAddress.match(/\[(\d{5})\]/);
        if (postcodeMatch) {
            document.getElementById('business_postcode').value = postcodeMatch[1];

            // 우편번호 제거한 나머지 주소
            let remaining = businessAddress.replace(/\[\d{5}\]\s*/, '');

            // 참고항목 추출 (괄호로 감싸진 부분)
            const extraMatch = remaining.match(/\(([^)]+)\)\s*$/);
            if (extraMatch) {
                document.getElementById('business_extraAddress').value = '(' + extraMatch[1] + ')';
                remaining = remaining.replace(/\s*\([^)]+\)\s*$/, '');
            }

            // 남은 주소를 display에 표시
            document.getElementById('business_address_display').value = remaining.trim();
        }
    }

    // 상세주소/참고항목 입력 시 hidden 필드 업데이트
    const detailAddr = document.getElementById('business_detailAddress');
    const extraAddr = document.getElementById('business_extraAddress');
    if (detailAddr) detailAddr.addEventListener('input', updateBusinessAddress);
    if (extraAddr) extraAddr.addEventListener('input', updateBusinessAddress);
});

// 섹션 토글
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    section.classList.toggle('show');
}

// 폼 검증
function validateForm() {
    const form = document.registerForm;

    // 아이디 검증
    if (!form.id.value.trim()) {
        alert('아이디를 입력해주세요.');
        form.id.focus();
        return false;
    }

    if (form.id.value.length < 4 || form.id.value.length > 20) {
        alert('아이디는 4-20자여야 합니다.');
        form.id.focus();
        return false;
    }

    if (!/^[a-zA-Z0-9]+$/.test(form.id.value)) {
        alert('아이디는 영문자와 숫자만 사용할 수 있습니다.');
        form.id.focus();
        return false;
    }

    <?php if ($ModifyMode !== 'view'): ?>
    if (!idChecked) {
        alert('아이디 중복확인을 해주세요.');
        return false;
    }
    <?php endif; ?>

    // 비밀번호 검증
    if (!form.pass1.value) {
        alert('비밀번호를 입력해주세요.');
        form.pass1.focus();
        return false;
    }

    if (form.pass1.value.length < 8 || form.pass1.value.length > 20) {
        alert('비밀번호는 8-20자여야 합니다.');
        form.pass1.focus();
        return false;
    }

    if (form.pass1.value !== form.pass2.value) {
        alert('비밀번호가 일치하지 않습니다.');
        form.pass2.focus();
        return false;
    }

    // 필수 항목 검증
    if (!form.name.value.trim()) {
        alert('업체명/성명을 입력해주세요.');
        form.name.focus();
        return false;
    }

    if (!form.hendphone.value.trim()) {
        alert('휴대폰 번호를 입력해주세요.');
        form.hendphone.focus();
        return false;
    }

    if (!form.email.value.trim()) {
        alert('이메일을 입력해주세요.');
        form.email.focus();
        return false;
    }

    if (!form.sample6_postcode.value || !form.sample6_address.value || !form.sample6_detailAddress.value) {
        alert('주소를 입력해주세요.');
        return false;
    }

    if (form.priv.value !== '1') {
        alert('개인정보 수집 및 이용에 동의해야 회원가입이 가능합니다.');
        return false;
    }

    return true;
}

// 아이디 입력 시 중복 체크 상태 초기화
document.getElementById('userId')?.addEventListener('input', function() {
    idChecked = false;
    document.getElementById('idValidation').className = 'validation-message';
    document.getElementById('idValidation').textContent = '';
});
</script>

</body>
</html>
