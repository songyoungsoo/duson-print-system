<html>
<head>
<title>[<?=$id?>]-내사진 원본사진</title>

<script LANGUAGE="JavaScript">

var isNav4, isIE4;
if (parseInt(navigator.appVersion.charAt(0)) >= 4) {
isNav4 = (navigator.appName == "Netscape") ? 1 : 0;
isIE4 = (navigator.appName.indexOf("Microsoft") != -1) ? 1 : 0;
}
function fitWindowSize() {
if (isNav4) {
window.innerWidth = document.layers[0].document.images[0].width;
window.innerHeight = document.layers[0].document.images[0].height;
}
if (isIE4) {
window.resizeTo(500, 500);
width = 500 - (document.body.clientWidth -  document.images[0].width);
height = 500 - (document.body.clientHeight -  document.images[0].height);
window.resizeTo(width, height);
   }
}

</script>

</head>

<body onLoad="fitWindowSize()" LEFTMARGIN=0 TOPMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0>

<div style="position:absolute; left:0px; top:0px">
<a href='#' onClick="javascript:window.close();"><img name="myimage" src="<?=$file?>" border=0></a>
</div>

</body>

</html>