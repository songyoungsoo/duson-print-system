<!-- jQuery UI CSS 추가 -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
<!-- jQuery 및 jQuery UI 추가 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
$(document).ready(function() {
    $("#search_company").autocomplete({
        source: "search_company.php", // 검색 API
        minLength: 2, // 최소 2글자 입력 시 검색 시작
        select: function(event, ui) {
            $("#search_company").val(ui.item.label); // 선택된 값 입력
            $("#company_name").val(ui.item.name);
            $("#email").val(ui.item.email);
            $("#phone1").val(ui.item.phone1);
            $("#phone2").val(ui.item.phone2);
            $("#phone3").val(ui.item.phone3);
            $("#hendphone1").val(ui.item.hendphone1);
            $("#hendphone2").val(ui.item.hendphone2);
            $("#hendphone3").val(ui.item.hendphone3);
            $("#sample6_postcode").val(ui.item.postcode);
            $("#sample6_address").val(ui.item.address);
            $("#sample6_detailAddress").val(ui.item.detailAddress);
            $("#sample6_extraAddress").val(ui.item.extraAddress);
            $("#po1").val(ui.item.po1);
            $("#po2").val(ui.item.po2);
            $("#po3").val(ui.item.po3);
            $("#po4").val(ui.item.po4);
            $("#po5").val(ui.item.po5);
            $("#po6").val(ui.item.po6);
            return false;
        }
    });
});
</script>

<table>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 검색(상호/이름) </td>
        <td bgcolor="#FFFFFF">
            <input type="text" id="search_company"  size="42" placeholder="상호/이름 검색">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 상호/이름 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
            <input type="text" id="company_name" name="name" size="42">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 담당 이메일 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
            <input type="text" id="email" name="email" size="42" placeholder="주문내역이 이메일로 발송됩니다">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 전화번호 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
            <input name="phone1" id="phone1" type="text" size="6" maxlength="3"> -
            <input name="phone2" id="phone2" type="text" size="10" maxlength="4"> -
            <input name="phone3" id="phone3" type="text" size="10" maxlength="4">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 휴대폰 </td>
        <td bgcolor="#FFFFFF">
            <input name="hendphone1" id="hendphone1" type="text" size="6" maxlength="3"> -
            <input name="hendphone2" id="hendphone2" type="text" size="10" maxlength="4"> -
            <input name="hendphone3" id="hendphone3" type="text" size="10" maxlength="4">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 주소(택배) </td>
        <td bgcolor="#FFFFFF">
            <input type="text" id="sample6_postcode" name="sample6_postcode" placeholder="우편번호">
            <input type="button" onclick="sample6_execDaumPostcode()" value="우편번호 찾기"><br>
            <input type="text" id="sample6_address" name="sample6_address" size="42" placeholder="주소"><br>
            <input type="text" id="sample6_detailAddress" name="sample6_detailAddress" placeholder="상세주소">
            <input type="text" id="sample6_extraAddress" name="sample6_extraAddress" size="16" placeholder="참고항목">
        </td>
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 사업자번호 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po1" name="po1" size="42">
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 상호 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po2" name="po2" size="42">
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 대표자 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po3" name="po3" size="42">
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 사업장주소 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po6" name="po6" size="42">
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 업태 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po5" name="po5" size="42">
    </tr>
    <tr>
        <td align="center" bgcolor="#E1E1FF"> 종목 <span class="style1">*</span> </td>
        <td bgcolor="#FFFFFF">
        <input type="text" id="po4" name="po4" size="42">
    </tr>
</table>
<script>
$(document).ready(function() {
    $("#search_company").autocomplete({
        source: "search_company.php", // 검색 API
        minLength: 2, // 최소 2글자 입력 시 검색 시작
        select: function(event, ui) {
            $("#search_company").val(ui.item.label); // 선택된 값 입력
            $("#company_name").val(ui.item.name);
            $("#email").val(ui.item.email);
            $("#phone1").val(ui.item.phone1);
            $("#phone2").val(ui.item.phone2);
            $("#phone3").val(ui.item.phone3);
            $("#hendphone1").val(ui.item.hendphone1);
            $("#hendphone2").val(ui.item.hendphone2);
            $("#hendphone3").val(ui.item.hendphone3);
            $("#sample6_postcode").val(ui.item.postcode);
            $("#sample6_address").val(ui.item.address);
            $("#sample6_detailAddress").val(ui.item.detailAddress);
            $("#sample6_extraAddress").val(ui.item.extraAddress);
            $("#po1").val(ui.item.po1);
            $("#po2").val(ui.item.po2);
            $("#po3").val(ui.item.po3);
            $("#po4").val(ui.item.po4);
            $("#po5").val(ui.item.po5);
            $("#po6").val(ui.item.po6);
            return false;
        }
    });
});
</script>
<style>
/* 자동완성 목록 스타일 */
.ui-autocomplete {
    position: absolute;
    z-index: 1000;
    display: block;
    background: white;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
    width: 250px;
}

.ui-menu-item {
    padding: 5px;
    cursor: pointer;
}

.ui-menu-item:hover {
    background: #f0f0f0;
}
</style>