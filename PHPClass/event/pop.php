<? 
if(!$EventDir){$EventDir=".";} 
include "$EventDir/config.php";
?>

<?php if($pop=="1"){?>

<script>
popup=window.open("<?php echo $EventDir?>/index.php","WebtingEvent","width=<?php echo $popWidth?>,height=<?php echo $popheight?>,left=<?php echo $TopLeft?>,top=<?php echo $TopTop?>")
popup.focus();
</script>
<?php }else if($pop=="2"){?>


<?php $ip_event="$REMOTE_ADDR";?>
<head>
<SCRIPT LANGUAGE="JavaScript">
var expDays = 1; // 쿠키 만료기간(일수)

var page = "<?php echo $EventDir?>/index.php"; // 팝업창의 위치
var windowprops = "width=<?php echo $popWidth?>,height=<?php echo $popheight?>,left=<?php echo $TopLeft?>,top=<?php echo $TopTop?>"; // 팝업창 옵션

function GetCookie (name) {  
var arg = name + "=";  
var alen = arg.length;  
var clen = document.cookie.length;  
var i = 0;  
while (i < clen) {    
var j = i + alen;    
if (document.cookie.substring(i, j) == arg)      
return getCookieVal (j);    
i = document.cookie.indexOf(" ", i) + 1;    
if (i == 0) break;   
}  
return null;
}
function SetCookie (name, value) {  
var argv = SetCookie.arguments;  
var argc = SetCookie.arguments.length;  
var expires = (argc > 2) ? argv[2] : null;  
var path = (argc > 3) ? argv[3] : null;  
var domain = (argc > 4) ? argv[4] : null;  
var secure = (argc > 5) ? argv[5] : false;  
document.cookie = name + "=" + escape (value) + 
((expires == null) ? "" : ("; expires=" + expires.toGMTString())) + 
((path == null) ? "" : ("; path=" + path)) +  
((domain == null) ? "" : ("; domain=" + domain)) +    
((secure == true) ? "; secure" : "");
}
function DeleteCookie (name) {  
var exp = new Date();  
exp.setTime (exp.getTime() - 1);  
var cval = GetCookie (name);  
document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
}
var exp = new Date(); 
exp.setTime(exp.getTime() + (expDays*24*60*60*1000));
function amt(){
var count = GetCookie('<?php echo $ip_event?>')
if(count == null) {
SetCookie('<?php echo $ip_event?>','1')
return 1
}
else {
var newcount = parseInt(count) + 1;
DeleteCookie('<?php echo $ip_event?>')
SetCookie('<?php echo $ip_event?>',newcount,exp)
return count
   }
}
function getCookieVal(offset) {
var endstr = document.cookie.indexOf (";", offset);
if (endstr == -1)
endstr = document.cookie.length;
return unescape(document.cookie.substring(offset, endstr));
}

function checkCount() {
var count = GetCookie('<?php echo $ip_event?>');
if (count == null) {
count=1;
SetCookie('<?php echo $ip_event?>', count, exp);

window.open(page, "", windowprops);

}
else {
count++;
SetCookie('<?php echo $ip_event?>', count, exp);
   }
}
</script>
<BODY OnLoad="checkCount()">

<?php }else if($pop=="Div1"){ // Skin Div1 - DHTML창 스무스하게 ?> 

<?php include "$EventDir/SKIN/${pop}.php";?>

<?php }else if($pop=="Div2"){ // Skin Div1 - DHTML창 떨어지면서 ㅋㅋ ?> 

<?php include "$EventDir/SKIN/${pop}.php";?>

<?php }else{}?>
