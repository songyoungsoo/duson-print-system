var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"+SALPHA;


function TypeCheck (s, spc) {
var i;

for(i=0; i< s.length; i++) {
if (spc.indexOf(s.substring(i, i+1)) < 0) {
return false;
}
}        
return true;
}

/////////////////////////////////////////////////////////////////////////////////
function AdminKingCheckField()
{
var f=document.AdminKingInfo;


if (f.id.value == "") {
alert("관리자 ID를 입력해 주세요. ");
f.id.focus();
return false;
}
if (!TypeCheck(f.id.value, ALPHA+NUM)) {
alert("관리자 ID는 영문자 및 숫자로만 되어 있습니다.");
f.id.focus();
return false;
}
if ((f.id.value.length < 4) || (f.id.value.length > 12)) {
alert("관리자 ID는 4글자 이상, 12글자 이하이여야 합니다.");
f.id.focus();
return false;
}


if (f.pass.value == "") {
alert("비밀번호를 입력해 주세요. ");
f.pass.focus();
return false;
}
if (!TypeCheck(f.pass.value, ALPHA+NUM)) {
alert("비밀번호는 영문자 및 숫자로만 입력해 주셔야 합니다.");
f.pass.focus();
return false;
}
if ((f.pass.value.length < 4) || (f.pass.value.length > 20)) {
alert("비밀번호는 4글자 이상, 20글자 이하이여야 합니다.");
f.pass.focus();
return false;
}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function clearField(field)
{
	if (field.value == field.defaultValue) {
		field.value = "";
	}
}
function checkField(field)
{
	if (!field.value) {
		field.value = field.defaultValue;
	}
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function color(color)
{
document.FrmUserInfo.id.style.background=color;
document.FrmUserInfo.pass.style.background=color;
}