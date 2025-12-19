function jsPreview() { 
prevWnd=window.open("","new","width=570,height=550,scrollbars=yes,resizable=yes,status=0");
prevWnd.document.open();	
prevWnd.document.writeln("<html><head><title>HtmlEdit-미리보기</title></head>");
prevWnd.document.writeln("<body leftmargin='25' marginwidth='0' topmargin='25' marginheight='0'>");
prevWnd.document.writeln(document.editor.tbContentElement.DOM.parentWindow.document.documentElement.outerHTML);
prevWnd.document.writeln("<p align=center><BR><BR><input type='button' value=' 창 닫기 ' onClick='javascript:window.close();'><BR><BR></p></body></html>");
return false;
}

function jsSubmit(mode){
var f = document.mailsendform;

if (f.cate.value == "0") {
alert("위치를 선택하여주세요!!");
f.cate.focus();
return false;
}
if( f.SUBJECT.value == "") {
alert("페이지 제목을 입력하여주세요!!");
f.SUBJECT.focus();
return false;
}

if ( mode == "forward" ){
;
}else{
f.CONTENT.value = document.editor.tbContentElement.DOM.parentWindow.document.documentElement.outerHTML;
}

if(confirm('저장 하려는 정보가 확실 하십니까..?\n\n확인을 누르면 저장 취소는 아시겟지요*^^*')){
f.action='./editor/submit_ok.php';
f.submit();
}
return false;
}
