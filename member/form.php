<?php
/**
 * 회원 정보 양식 (조회/수정)
 * - member_fild.php에서 설정된 $MlangMember_* 변수 사용
 * - admin/member/admin.php에서 include하여 사용
 */

// DB 연결 (이미 연결되어 있으면 스킵)
if (!isset($db) || !$db) {
    $db_path = isset($db_dir) ? "$db_dir/db.php" : "../db.php";
    include $db_path;
}

// 변수 기본값 설정 (member_fild.php가 설정하지 않은 경우)
$MlangMember_id = $MlangMember_id ?? '';
$MlangMember_pass1 = $MlangMember_pass1 ?? '';
$MlangMember_name = $MlangMember_name ?? '';
$MlangMember_phone1 = $MlangMember_phone1 ?? '';
$MlangMember_phone2 = $MlangMember_phone2 ?? '';
$MlangMember_phone3 = $MlangMember_phone3 ?? '';
$MlangMember_hendphone1 = $MlangMember_hendphone1 ?? '';
$MlangMember_hendphone2 = $MlangMember_hendphone2 ?? '';
$MlangMember_hendphone3 = $MlangMember_hendphone3 ?? '';
$MlangMember_email = $MlangMember_email ?? '';
$MlangMember_sample6_postcode = $MlangMember_sample6_postcode ?? '';
$MlangMember_sample6_address = $MlangMember_sample6_address ?? '';
$MlangMember_sample6_detailAddress = $MlangMember_sample6_detailAddress ?? '';
$MlangMember_sample6_extraAddress = $MlangMember_sample6_extraAddress ?? '';
$MlangMember_po1 = $MlangMember_po1 ?? '';
$MlangMember_po2 = $MlangMember_po2 ?? '';
$MlangMember_po3 = $MlangMember_po3 ?? '';
$MlangMember_po4 = $MlangMember_po4 ?? '';
$MlangMember_po5 = $MlangMember_po5 ?? '';
$MlangMember_po6 = $MlangMember_po6 ?? '';
$MlangMember_po7 = $MlangMember_po7 ?? '';
$MlangMember_date = $MlangMember_date ?? '';
$MlangMember_level = $MlangMember_level ?? '';

$action = $action ?? 'member_form_ok.php';
$MdoifyMode = $MdoifyMode ?? '';
$no = $no ?? '';

$isEditMode = ($MdoifyMode === 'view');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>회원 정보</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Noto Sans KR', sans-serif;
    background: #f5f5f5;
    font-size: 13px;
}
.container {
    max-width: 620px;
    margin: 0 auto;
    background: white;
    padding: 20px;
}
h2 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #333;
}
table.form-table {
    width: 100%;
    border-collapse: collapse;
}
table.form-table th {
    background: #f8f9fa;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    width: 120px;
    border: 1px solid #ddd;
    font-size: 12px;
    color: #333;
}
table.form-table td {
    padding: 8px 12px;
    border: 1px solid #ddd;
}
input[type="text"], input[type="password"], input[type="email"] {
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 13px;
}
input.short { width: 60px; }
input.medium { width: 150px; }
input.long { width: 100%; }
textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 13px;
    resize: vertical;
}
.phone-group input { width: 55px; text-align: center; }
.phone-group span { margin: 0 3px; color: #999; }
.btn-group {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}
.btn {
    display: inline-block;
    padding: 10px 30px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin: 0 5px;
}
.btn-primary { background: #2563eb; color: white; }
.btn-primary:hover { background: #1d4ed8; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}
.info-row { color: #666; font-size: 12px; }
.readonly-field { background: #f5f5f5; color: #666; }
.field-note { color: #888; font-size: 11px; margin-top: 4px; }

/* 사업자 정보 섹션 */
.section-header td {
    background: #eef3fb;
    padding: 10px 12px;
    font-weight: 600;
    font-size: 13px;
    color: #2563eb;
    border: 1px solid #ddd;
}
.biz-section th {
    background: #f0f4fa;
}

/* 약관 동의 섹션 */
.agreement-section {
    margin-top: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    background: #fafafa;
}
.agreement-section h3 {
    font-size: 14px;
    color: #333;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ddd;
}
.agree-all-wrap {
    margin-bottom: 12px;
    padding: 8px 10px;
    background: #eef3fb;
    border-radius: 3px;
}
.agree-all-wrap label {
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
}
.agree-box {
    margin-bottom: 12px;
}
.agree-box h4 {
    font-size: 13px;
    color: #333;
    margin-bottom: 6px;
}
.agree-box textarea.terms-content {
    width: 100%;
    height: 150px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 12px;
    line-height: 1.6;
    background: #fff;
    resize: none;
    color: #555;
}
.agree-box label {
    display: inline-block;
    margin-top: 6px;
    font-size: 13px;
    cursor: pointer;
}
.agree-box label input[type="checkbox"] {
    margin-right: 5px;
}
.required-mark { color: #e53e3e; }
</style>
<script>
function execDaumPostcode() {
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
            }
            document.getElementById('sample6_postcode').value = data.zonecode;
            document.getElementById("sample6_address").value = addr;
            document.getElementById("sample6_extraAddress").value = extraAddr;
            document.getElementById("sample6_detailAddress").focus();
        }
    }).open();
}

function checkDuplicateId() {
    var f = document.memberForm;
    var idVal = f.id.value.trim();
    if (idVal.length < 4 || idVal.length > 12) {
        alert('아이디는 4~12자로 입력해주세요.');
        f.id.focus();
        return;
    }
    if (!/^[a-zA-Z0-9]+$/.test(idVal)) {
        alert('아이디는 영문, 숫자만 사용 가능합니다.');
        f.id.focus();
        return;
    }
    window.open("id_check.php?id=" + idVal, "", "scrollbars=no,resizable=yes,width=500,height=200,top=100,left=100");
}

function toggleAgreeAll(el) {
    var checks = document.querySelectorAll('.agree-check');
    for (var i = 0; i < checks.length; i++) {
        checks[i].checked = el.checked;
    }
}

function syncAgreeAll() {
    var checks = document.querySelectorAll('.agree-check');
    var all = true;
    for (var i = 0; i < checks.length; i++) {
        if (!checks[i].checked) { all = false; break; }
    }
    var agreeAll = document.getElementById('agree_all');
    if (agreeAll) agreeAll.checked = all;
}

function copyDeliveryAddress(el) {
    var f = document.memberForm;
    if (el.checked) {
        var parts = [];
        var postcode = f.sample6_postcode.value.trim();
        var addr = f.sample6_address.value.trim();
        var detail = f.sample6_detailAddress.value.trim();
        var extra = f.sample6_extraAddress.value.trim();
        if (postcode) parts.push('[' + postcode + ']');
        if (addr) parts.push(addr);
        if (detail) parts.push(detail);
        if (extra) parts.push(extra);
        if (parts.length === 0) {
            alert('먼저 위 주소를 입력해주세요.');
            el.checked = false;
            return;
        }
        f.po6.value = parts.join(' ');
    } else {
        f.po6.value = '';
    }
}

function validateForm() {
    var f = document.memberForm;
    var isEdit = <?= $isEditMode ? 'true' : 'false' ?>;

    if (!isEdit) {
        // Join mode validations
        var idVal = f.id.value.trim();
        if (idVal === '') {
            alert('아이디를 입력해주세요.');
            f.id.focus();
            return false;
        }
        if (idVal.length < 4 || idVal.length > 12) {
            alert('아이디는 4~12자로 입력해주세요.');
            f.id.focus();
            return false;
        }
        if (!/^[a-zA-Z0-9]+$/.test(idVal)) {
            alert('아이디는 영문, 숫자만 사용 가능합니다.');
            f.id.focus();
            return false;
        }

        if (f.pass1.value.trim() === '' || f.pass1.value.length < 4) {
            alert('비밀번호를 4자 이상 입력해주세요.');
            f.pass1.focus();
            return false;
        }
        if (f.pass1.value !== f.pass2.value) {
            alert('비밀번호가 일치하지 않습니다.');
            f.pass2.focus();
            return false;
        }

        if (f.name.value.trim() === '') {
            alert('이름을 입력해주세요.');
            f.name.focus();
            return false;
        }

        if (f.email.value.trim() === '') {
            alert('이메일을 입력해주세요.');
            f.email.focus();
            return false;
        }

        if (!f.agree_terms.checked) {
            alert('이용약관에 동의해주세요.');
            return false;
        }
        if (!f.agree_privacy.checked) {
            alert('개인정보 취급방침에 동의해주세요.');
            return false;
        }
    } else {
        // Edit mode validations
        if (f.name.value.trim() === '') {
            alert('이름을 입력해주세요.');
            f.name.focus();
            return false;
        }
    }
    return true;
}
</script>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
</head>
<body>
<div class="container">
    <h2><?= $isEditMode ? '회원 정보 수정' : '두손기획인쇄 회원가입' ?></h2>

    <form name="memberForm" method="post" action="<?= htmlspecialchars($action) ?>" onsubmit="return validateForm()" autocomplete="off">
        <?php include_once __DIR__ . '/../includes/csrf.php'; csrf_field(); ?>
        <input type="hidden" name="no" value="<?= htmlspecialchars($no) ?>">
        <input type="hidden" name="mode" value="modifyok">

        <table class="form-table">
            <tr>
                <th>아이디 <?php if (!$isEditMode): ?><span class="required-mark">*</span><?php endif; ?></th>
                <td>
                    <?php if ($isEditMode): ?>
                    <input type="text" name="id" class="medium readonly-field"
                           value="<?= htmlspecialchars($MlangMember_id) ?>" readonly>
                    <?php else: ?>
                    <input type="text" name="id" class="medium" autocomplete="one-time-code"
                           value="<?= htmlspecialchars($MlangMember_id) ?>" placeholder="4~12자 영문, 숫자" maxlength="12">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="checkDuplicateId()">중복확인</button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>비밀번호 <?php if (!$isEditMode): ?><span class="required-mark">*</span><?php endif; ?></th>
                <td>
                    <input type="password" name="pass1" class="medium" autocomplete="new-password"
                           value="" placeholder="<?= $isEditMode ? '변경 시에만 입력' : '비밀번호 (4자 이상)' ?>">
                </td>
            </tr>
            <?php if (!$isEditMode): ?>
            <tr>
                <th>비밀번호 확인 <span class="required-mark">*</span></th>
                <td>
                    <input type="password" name="pass2" class="medium" autocomplete="new-password"
                           value="" placeholder="비밀번호 재입력">
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>이름/상호 <span class="required-mark">*</span></th>
                <td>
                    <input type="text" name="name" class="medium" autocomplete="one-time-code"
                           value="<?= htmlspecialchars($MlangMember_name) ?>" placeholder="이름 또는 상호명">
                </td>
            </tr>
            <tr>
                <th>전화번호</th>
                <td class="phone-group">
                    <input type="text" name="phone1" class="short" value="<?= htmlspecialchars($MlangMember_phone1) ?>" maxlength="4">
                    <span>-</span>
                    <input type="text" name="phone2" class="short" value="<?= htmlspecialchars($MlangMember_phone2) ?>" maxlength="4">
                    <span>-</span>
                    <input type="text" name="phone3" class="short" value="<?= htmlspecialchars($MlangMember_phone3) ?>" maxlength="4">
                </td>
            </tr>
            <tr>
                <th>휴대폰</th>
                <td class="phone-group">
                    <input type="text" name="hendphone1" class="short" value="<?= htmlspecialchars($MlangMember_hendphone1) ?>" maxlength="4">
                    <span>-</span>
                    <input type="text" name="hendphone2" class="short" value="<?= htmlspecialchars($MlangMember_hendphone2) ?>" maxlength="4">
                    <span>-</span>
                    <input type="text" name="hendphone3" class="short" value="<?= htmlspecialchars($MlangMember_hendphone3) ?>" maxlength="4">
                    <div class="field-note">* 주 연락처로 저장됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>이메일</th>
                <td>
                    <input type="email" name="email" class="long" autocomplete="one-time-code"
                           value="<?= htmlspecialchars($MlangMember_email) ?>" placeholder="example@email.com">
                </td>
            </tr>
            <tr>
                <th>우편번호</th>
                <td>
                    <input type="text" name="sample6_postcode" id="sample6_postcode" class="short readonly-field"
                           value="<?= htmlspecialchars($MlangMember_sample6_postcode) ?>" readonly>
                    <button type="button" class="btn btn-secondary" onclick="execDaumPostcode()" style="padding:5px 10px;font-size:12px;">우편번호 찾기</button>
                </td>
            </tr>
            <tr>
                <th>주소</th>
                <td>
                    <input type="text" name="sample6_address" id="sample6_address" class="long readonly-field"
                           value="<?= htmlspecialchars($MlangMember_sample6_address) ?>" readonly placeholder="기본주소">
                </td>
            </tr>
            <tr>
                <th>상세주소</th>
                <td>
                    <input type="text" name="sample6_detailAddress" id="sample6_detailAddress" class="long" autocomplete="one-time-code"
                           value="<?= htmlspecialchars($MlangMember_sample6_detailAddress) ?>" placeholder="상세주소">
                    <input type="hidden" name="sample6_extraAddress" id="sample6_extraAddress"
                           value="<?= htmlspecialchars($MlangMember_sample6_extraAddress) ?>">
                </td>
            </tr>

            <!-- 사업자 정보 섹션 -->
            <tr class="section-header">
                <td colspan="2">사업자 정보 (선택)</td>
            </tr>
            <tr class="biz-section">
                <th>사업자등록번호</th>
                <td><input type="text" name="po1" class="long" value="<?= htmlspecialchars($MlangMember_po1) ?>" placeholder="000-00-00000"></td>
            </tr>
            <tr class="biz-section">
                <th>상호(회사명)</th>
                <td><input type="text" name="po2" class="long" value="<?= htmlspecialchars($MlangMember_po2) ?>"></td>
            </tr>
            <tr class="biz-section">
                <th>대표자명</th>
                <td><input type="text" name="po3" class="long" value="<?= htmlspecialchars($MlangMember_po3) ?>"></td>
            </tr>
            <tr class="biz-section">
                <th>업태</th>
                <td><input type="text" name="po4" class="long" value="<?= htmlspecialchars($MlangMember_po4) ?>"></td>
            </tr>
            <tr class="biz-section">
                <th>종목</th>
                <td><input type="text" name="po5" class="long" value="<?= htmlspecialchars($MlangMember_po5) ?>"></td>
            </tr>
            <tr class="biz-section">
                <th>사업장주소</th>
                <td>
                    <label style="font-size:12px;cursor:pointer;margin-bottom:6px;display:inline-block;">
                        <input type="checkbox" id="copy_address" onclick="copyDeliveryAddress(this)"> 위 주소와 동일
                    </label>
                    <input type="text" name="po6" id="po6" class="long" value="<?= htmlspecialchars($MlangMember_po6) ?>">
                </td>
            </tr>
            <tr class="biz-section">
                <th>세금계산서 이메일</th>
                <td><input type="email" name="po7" class="long" value="<?= htmlspecialchars($MlangMember_po7) ?>" placeholder="tax@example.com"></td>
            </tr>

            <?php if ($isEditMode && $MlangMember_date): ?>
            <tr class="info-row">
                <th>가입일</th>
                <td><?= htmlspecialchars($MlangMember_date) ?></td>
            </tr>
            <tr class="info-row">
                <th>회원등급</th>
                <td>Lv.<?= htmlspecialchars($MlangMember_level) ?></td>
            </tr>
            <?php endif; ?>
        </table>

        <?php if (!$isEditMode): ?>
        <!-- 약관 동의 섹션 (가입 모드에서만 표시) -->
        <div class="agreement-section">
            <h3>약관 동의</h3>

            <div class="agree-all-wrap">
                <label><input type="checkbox" id="agree_all" onclick="toggleAgreeAll(this)"> 전체 동의</label>
            </div>

            <div class="agree-box">
                <h4>이용약관</h4>
                <textarea class="terms-content" readonly><?php
                    $terms_file = __DIR__ . '/terms.txt';
                    if (file_exists($terms_file)) {
                        echo htmlspecialchars(strip_tags(file_get_contents($terms_file)));
                    } else {
                        echo '이용약관 내용을 불러올 수 없습니다.';
                    }
                ?></textarea>
                <label><input type="checkbox" name="agree_terms" class="agree-check" onchange="syncAgreeAll()"> 이용약관에 동의합니다. <span class="required-mark">(필수)</span></label>
            </div>

            <div class="agree-box">
                <h4>개인정보 취급방침</h4>
                <textarea class="terms-content" readonly><?php
                    $privacy_file = __DIR__ . '/privacy.txt';
                    if (file_exists($privacy_file)) {
                        echo htmlspecialchars(strip_tags(file_get_contents($privacy_file)));
                    } else {
                        echo '개인정보 취급방침 내용을 불러올 수 없습니다.';
                    }
                ?></textarea>
                <label><input type="checkbox" name="agree_privacy" class="agree-check" onchange="syncAgreeAll()"> 개인정보 취급방침에 동의합니다. <span class="required-mark">(필수)</span></label>
            </div>
        </div>
        <?php endif; ?>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">
                <?= $isEditMode ? '수정하기' : '가입하기' ?>
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">닫기</button>
        </div>
    </form>
</div>
</body>
</html>
