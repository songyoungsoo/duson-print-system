<?
include"top.php";
?>


<table border=0 align=center width=100% cellpadding='50' cellspacing='50'>
<tr><td>
<font style='font-size:15pt;'>
<BR><BR>
관리자의 페이지에 진입하셨음을 진심으로 환영합니다.
<BR><BR>
사업 무궁한 발전히 있기를 바랍니다..
<BR><BR>
오늘 하루 좋은일 많으시기도 바래요 *^^*
</font>
</b>
<BR><BR>
<SCRIPT LANGUAGE="JavaScript">
var navName = navigator.appName ;
var brVer = navigator.userAgent; var brNum; var reg = new RegExp('/');
function verNumIE() {
   var brVerId = brVer.indexOf('MSIE');
   brNum = brVer.substr(brVerId,8);
}
function verNumOt() {
   var brVerId = brVer.search(reg);
   brNum = brVer.substring(brVerId+1);
}
</script>

</HEAD>

<SCRIPT LANGUAGE="JavaScript">
if (navigator.appName == 'Microsoft Internet Explorer') {
  verNumIE() ;
} else {
  verNumOt() ;
}
document.write("<TABLE BORDER=0 bgColor=teal cellspacing=1 cellpadding=5>");
document.write("<CAPTION Align=Top><b>관계자님의 브라우저 정보</b></CAPTION>");
document.write("<Tr>");
document.write("<td bgcolor=#F5F5F5><b>브라우저 이름 : </b></td>");
document.write("<td bgColor=white>",navName,"</td>");
document.write("</Tr>");
document.write("<Tr>");
document.write("<td bgcolor=#F5F5F5><b>플랫폼의 이름 : </b></td>");
document.write("<td bgColor=white>",navigator.platform,"</td>");
document.write("</Tr>");
document.write("<Tr>");
document.write("<td bgcolor=#F5F5F5><b>브라우저의 버전 : </b></td>");
document.write("<td bgColor=white>",brNum,"</td>");
document.write("</Tr>");
document.write("<Tr>");
document.write("<td bgcolor=#F5F5F5><b>자바 실행 가능여부 : </b></td>");
if ( !(navigator.javaEnabled()) ) {
  java="No" ;
} else {
  java="Yes" ;
}
document.write("<td bgColor=white>",java,"</td>");
document.write("</Tr>");
document.write("</TABLE>");
</script>

</BODY>
</HTML>
<BR><BR>
현페이지는 많은 입력폼과 출력관계로 인해 <u>Microsoft Internet Explorer 6.X, 해상도: 1152X864</u> 에 최적화 되어져 있습니다.<BR>
Microsoft Internet Explorer 버젼이 낮을시 프로그램 에러가 생길수 있음으로 꼭 업데이트후 작업을 해주시기 바랍니다..
<BR><BR>
<a href='http://download.naver.com/pds_leaf.asp?pg_code=860&pv_code=17' target='_blank'>익스플로어 6.X 한글판 풀버젼 다운 받는곳(클릭하세요!!)</a>
</td></tr>
</table>

<?
include"down.php";
?>