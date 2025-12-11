function MemberCheckField() {
    var form = document.FrmUserInfo;

    if (!form.id.value) {
        alert('아이디를 입력해주세요.');
        form.id.focus();
        return false;
    }

    if (!form.pass.value) {
        alert('비밀번호를 입력해주세요.');
        form.pass.focus();
        return false;
    }

    return true;
}
