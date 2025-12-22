<?php
// session_start();
ini_set('display_errors', '0');

$HomeDir = "..";
$PageCode = "PrintAuto";
include "$HomeDir/db.php";
// include $_SERVER['DOCUMENT_ROOT'] . "/mlangprintauto/mlangprintautotop.php"; // 상단 이미지 제거

// GET 및 POST 변수 가져오기
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$page = $_POST['page'] ?? $_GET['page'] ?? '';

// POST 데이터 가져오기
$textarea = $_POST['cont'] ?? ''; // 여기에서 textarea 변수를 설정

if ($mode == "SubmitOk") {
    include "../db.php";

    // 데이터베이스에서 최대 no 값을 가져오기
    $stmt = $db->prepare("SELECT MAX(no) FROM mlangorder_printauto");
    $stmt->execute();
    $stmt->bind_result($row);
    $stmt->fetch();
    $stmt->close();

    $new_no = $row ? $row + 1 : 1;

    // 자료를 업로드할 폴더를 생성 시켜준다.
    $dir = "upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
        chmod($dir, 0777);
    }

    // POST 데이터 가져오기
    $zip = $_POST['sample6_postcode'] ?? '';
    $zip1 = $_POST['sample6_address'] ?? '';
    $zip2 = $_POST['sample6_detailAddress'] ?? '';
    $address3 = $_POST['sample6_extraAddress'] ?? '';
    $name = $_POST['username'] ?? ''; //name에 중복되는 것이 있어서 username으로

    // 디비에 관련 자료 저장
    $PageSSOk = ($page == "OrderOne") ? "2" : "1";

    $date = date("Y-m-d H:i:s");
    $dbinsert = $db->prepare("INSERT INTO mlangorder_printauto 
        (no, Type, ImgFolder, Type_1, Type_2, Type_3, Type_4, Type_5, Type_6, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, delivery, bizname, bank, bankname, cont, date, PageSSOk, pass, Gensu) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $dbinsert->bind_param(
        "issssssssssssssssssssssssssss",
        $new_no,
        $_POST['Type'] ?? '',
        $_POST['ImgFolder'] ?? '',
        $_POST['Type_1'] ?? '',
        $_POST['Type_2'] ?? '',
        $_POST['Type_3'] ?? '',
        $_POST['Type_4'] ?? '',
        $_POST['Type_5'] ?? '',
        $_POST['Type_6'] ?? '',
        $_POST['money_1'] ?? '',
        $_POST['money_2'] ?? '',
        $_POST['money_3'] ?? '',
        $_POST['money_4'] ?? '',
        $_POST['money_5'] ?? '',
        $name,
        $_POST['email'] ?? '',
        $zip,
        $zip1,
        $zip2,
        $_POST['phone'] ?? '',
        $_POST['Hendphone'] ?? '',
        $_POST['delivery'] ?? '',
        $_POST['bizname'] ?? '',
        $_POST['bank'] ?? '',
        $_POST['bankname'] ?? '',
        $_POST['cont'] ?? '',
        $date,
        $PageSSOk,
        $_POST['pass'] ?? '',
        $_POST['Gensu'] ?? ''
    );

    if ($dbinsert->execute()) {
        echo "<html>
        <meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle={$_POST['OrderSytle']}&no=$new_no&username=$name&Type_1={$_POST['Type_1']}&Type_2={$_POST['Type_2']}&Type_3={$_POST['Type_3']}&Type_4={$_POST['Type_4']}&Type_5={$_POST['Type_5']}&Type_6={$_POST['Type_6']}&money4={$_POST['money_4']}&money5={$_POST['money_5']}&phone={$_POST['phone']}&Hendphone={$_POST['Hendphone']}&zip1=$zip1&zip2=$zip2&email={$_POST['email']}&date=$date&cont={$_POST['cont']}&standard={$_POST['standard']}&page={$_POST['page']}&PageSS={$_POST['PageSS']}'>
        </html>";
        exit;
    } else {
        echo "<html>
        <meta http-equiv='Refresh' content='0; URL=OrderResult.php?OrderSytle={$_POST['OrderSytle']}&no=$new_no&username=$name&Type_1={$_POST['Type_1']}&Type_2={$_POST['Type_2']}&Type_3={$_POST['Type_3']}&Type_4={$_POST['Type_4']}&Type_5={$_POST['Type_5']}&Type_6={$_POST['Type_6']}&money4={$_POST['money_4']}&money5={$_POST['money_5']}&phone={$_POST['phone']}&Hendphone={$_POST['Hendphone']}&zip1=$zip1&zip2=$zip2&email={$_POST['email']}&date=$date&cont={$_POST['cont']}&standard={$_POST['standard']}&page={$_POST['page']}&PageSS={$_POST['PageSS']}'>
        </html>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>주문 정보 입력</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style250801.css">
    <style>
        .input { font-size:10pt; background-color:#FFFFFF; color:#336699; line-height:130%; }
        .inputOk { font-size:10pt; background-color:#FFFFFF; color:#429EB2; border-style:solid; height:22px; border:0; solid #FFFFFF; font:bold; }
        .Td1 { font-size:9pt; background-color:#EBEBEB; color:#336699; }
        .Td2 { font-size:9pt; color:#232323; }
        .style3 { color: #33CCFF }
        .style4 { color: #FF0000 }
        
        /* 주문 폼 전용 스타일 */
        .order-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .order-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .order-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .order-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .order-content {
            padding: 2rem;
        }
    </style>
    <script type="text/javascript" src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script language="javascript">
        function JoinCheckField() {
            var f = document.JoinInfo;
            if (f.username.value == "") {
                alert("신청자 이름을 입력해 주세요");
                f.username.focus();
                return false;
            }
            if (f.email.value == "") {
                alert("이메일을 입력해 주세요");
                f.email.focus();
                return false;
            }
            if (f.phone.value == "") {
                alert("전화번호를 입력해 주세요.");
                f.phone.focus();
                return false;
            }
            if (f.bankname.value == "") {
                alert("입금자명을 입력해 주세요.");
                f.bankname.focus();
                return false;
            }
            return true;
        }

        function sample6_execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function(data) {
                    var addr = ''; // 주소 변수
                    var extraAddr = ''; // 참고항목 변수

                    if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                        addr = data.roadAddress;
                    } else { // 사용자가 지번 주소를 선택했을 경우(J)
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
                        document.getElementById("sample6_extraAddress").value = extraAddr;
                    } else {
                        document.getElementById("sample6_extraAddress").value = '';
                    }

                    document.getElementById('sample6_postcode').value = data.zonecode;
                    document.getElementById("sample6_address").value = addr;
                    document.getElementById("sample6_detailAddress").focus();
                }
            }).open();
        }
    </script>
</head>
<body>
    <div class="order-container">
        <div class="order-card">
            <div class="order-header">
                <h2>🖨️ 주문 정보 입력</h2>
                <p>정확한 정보를 입력해 주세요</p>
            </div>
            <div class="order-content">
                <form name='JoinInfo' method='post' enctype='multipart/form-data' OnSubmit='return JoinCheckField()' action='OnlineOrder.php'>
                    <input type="hidden" name='PageSS' value='OrderOne'>
                    <input type="hidden" name='SubmitMode' value='OrderOne'>
                    <input type="hidden" name='mode' value='SubmitOk'>
                    
                    <!-- 주문 요약 정보 -->
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px;">
                        <?php include "TOrderResult.php"; ?>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="color: #2c3e50; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px;">
                            📝 신청자 정보
                        </h3>
                        <p style="color: #6c757d; font-size: 0.95rem;">* 신청자 정보를 정확히 입력 바랍니다.</p>
                    </div>
                    
                    <div>
                    <?php
                    $id_login_ok = isset($_SESSION['id_login_ok']) ? $_SESSION['id_login_ok'] : false;
                    include "../db.php";
                    if (!$id_login_ok) {
                    ?>
                    <div class="form-grid" style="display: grid; gap: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">👤 성명/상호</label>
                                <input name="username" type="text" class="form-control-modern" placeholder="성명 또는 상호명을 입력하세요">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📧 이메일</label>
                                <input name="email" type="email" class="form-control-modern" placeholder="이메일 주소를 입력하세요">
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">🏠 주소 (우편물 수령지)</label>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" class="form-control-modern" style="width: 150px;">
                                <button type="button" onclick="sample6_execDaumPostcode()" style="padding: 10px 20px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">우편번호 찾기</button>
                            </div>
                            <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" class="form-control-modern" style="margin-bottom: 10px;">
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소" class="form-control-modern" style="flex: 1;">
                                <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목" class="form-control-modern" style="flex: 1;">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📞 전화번호</label>
                                <input name="phone" type="tel" class="form-control-modern" placeholder="전화번호를 입력하세요">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📱 핸드폰</label>
                                <input name="Hendphone" type="tel" class="form-control-modern" placeholder="핸드폰 번호를 입력하세요">
                            </div>
                        </div>
                    </div>
                    <?php } else {
                        $userid = $_SESSION['id_login_ok']['id'];
                        if ($userid) {
                            $query = "SELECT * FROM member WHERE id='" . $db->real_escape_string($userid) . "'";
                            $result = $db->query($query);
                            $row = $result->fetch_assoc();

                            if ($row) {
                                $View_No = htmlspecialchars($row['no']);
                                $View_name = htmlspecialchars($row['name']);
                                $View_email = htmlspecialchars($row['email']);
                                $View_zip = htmlspecialchars($row['sample6_postcode']);
                                $View_zip1 = htmlspecialchars($row['sample6_address']);
                                $View_zip2 = htmlspecialchars($row['sample6_detailAddress']);
                                $View_phone = htmlspecialchars($row['phone1']) . '-' . htmlspecialchars($row['phone2']) . '-' . htmlspecialchars($row['phone3']);
                                $View_Hendphone = htmlspecialchars($row['hendphone1']) . '-' . htmlspecialchars($row['hendphone2']) . '-' . htmlspecialchars($row['hendphone3']);
                            } else {
                                echo ("<script language=javascript>
                                    window.alert('Database 오류입니다.');
                                    window.self.close();
                                </script>");
                                exit;
                            }
                        }
                    ?>
                    <div class="form-grid" style="display: grid; gap: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">👤 성명/상호</label>
                                <input name="username" type="text" class="form-control-modern" value='<?=$View_name?>'>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📧 이메일</label>
                                <input name="email" type="email" class="form-control-modern" value='<?=$View_email?>'>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">🏠 주소 (우편물 수령지)</label>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호" class="form-control-modern" style="width: 150px;" value='<?=$View_zip?>'>
                                <button type="button" onclick="sample6_execDaumPostcode()" style="padding: 10px 20px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">우편번호 찾기</button>
                            </div>
                            <input type="text" id="sample6_address" name="sample6_address" placeholder="주소" class="form-control-modern" style="margin-bottom: 10px;" value='<?=$View_zip1?>'>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소" class="form-control-modern" style="flex: 1;" value='<?=$View_zip2?>'>
                                <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" placeholder="참고항목" class="form-control-modern" style="flex: 1;" value='<?=$View_zip3?>'>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📞 전화번호</label>
                                <input name="phone" type="tel" class="form-control-modern" value='<?=$View_phone?>'>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">📱 핸드폰</label>
                                <input name="Hendphone" type="tel" class="form-control-modern" value='<?=$View_Hendphone?>'>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">🏢 상호명</label>
                            <input type="text" name="bizname" class="form-control-modern" value='<?=$View_bizname?>'>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <!-- 입금 정보 섹션 -->
                    <div style="margin: 2rem 0;">
                        <h3 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                            💳 입금 정보
                        </h3>
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: start;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">🏦 입금은행</label>
                                <input name="bank" type="text" class="form-control-modern" placeholder="입금할 은행명을 입력하세요">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">👤 입금자명</label>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <input name="bankname" type="text" class="form-control-modern" placeholder="입금자명" style="width: 200px;">
                                    <div style="color: #6c757d; font-size: 0.9rem; line-height: 1.4;">
                                        <strong>입금자 핸드폰번호 필수</strong><br>
                                        <span style="color: #e74c3c;">무통장 입금</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 기타 요청사항 섹션 -->
                    <div style="margin: 2rem 0;">
                        <h3 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                            📝 기타 요청사항
                        </h3>
                        <textarea name="cont" class="form-control-modern" rows="6" placeholder="추가 요청사항이나 특별한 지시사항이 있으시면 입력해 주세요..." style="resize: vertical;"><?=$textarea?></textarea>
                    </div>
                    
                    <!-- 개인정보처리방침 동의 -->
                    <div style="margin: 2rem 0; padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px; border-left: 4px solid #3498db;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="min-width: 150px;">
                                <strong style="color: #2c3e50;">
                                    <a href="http://www.dsp1830.shop/sub/pri_info.html" target="_blank" style="color: #3498db; text-decoration: none;">
                                        🔒 개인정보처리방침
                                    </a>
                                </strong>
                            </div>
                            <div style="flex: 1;">
                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; cursor: pointer;">
                                    <input type="radio" name="priv" value="1" checked="checked" style="transform: scale(1.2);">
                                    <span style="color: #27ae60; font-weight: 600;">✅ 동의합니다</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="radio" name="priv" value="" style="transform: scale(1.2);">
                                    <span style="color: #e74c3c; font-weight: 600;">❌ 동의하지 않습니다</span>
                                </label>
                                <p style="color: #6c757d; font-size: 0.85rem; margin-top: 8px; margin-bottom: 0;">
                                    * 주문 처리를 위해 개인정보 수집·이용에 동의가 필요합니다.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 주문 버튼 -->
                    <div style="text-align: center; margin: 2rem 0;">
                        <button type="submit" class="btn-calculate" style="font-size: 1.2rem; padding: 18px 40px;">
                            🛒 주문하기
                        </button>
                    </div>
                </form>
                
                <!-- 문의 정보 -->
                <div style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: linear-gradient(135deg, #e8f4fd 0%, #d6eaf8 100%); border-radius: 15px;">
                    <p style="color: #2c3e50; font-size: 1.1rem; margin: 0;">
                        📞 주문 관련 문의는 
                        <strong style="color: #3498db;">이메일</strong> 
                        <a href="mailto:dsp1830@naver.com" style="color: #e74c3c; text-decoration: none; font-weight: 600;">dsp1830@naver.com</a> 
                        또는 
                        <strong style="color: #3498db;">전화</strong> 
                        <a href="tel:02-2632-1829" style="color: #e74c3c; text-decoration: none; font-weight: 600;">02-2632-1829</a>로 
                        문의 바랍니다.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>