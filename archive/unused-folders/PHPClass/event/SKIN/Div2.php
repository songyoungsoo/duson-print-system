<body>
<script language="JavaScript1.2">
var freq=2

var random_num=Math.floor(Math.random()*freq)
if (random_num==0)
window.onload=initbox
var ie=document.all
var dom=document.getElementById
var ns4=document.layers

var bouncelimit=32 //8의 배수
var direction="up"

function initbox(){
if (!dom&&!ie&&!ns4)
return
crossobj=(dom)?document.getElementById("dropin").style : ie? document.all.dropin : document.dropin
scroll_top=(ie)? document.body.scrollTop : window.pageYOffset
crossobj.top=scroll_top-250
crossobj.visibility=(dom||ie)? "visible" : "show"
dropstart=setInterval("dropin()",50)
}

function dropin(){
scroll_top=(ie)? document.body.scrollTop : window.pageYOffset
if (parseInt(crossobj.top)<195+scroll_top)
crossobj.top=parseInt(crossobj.top)+40
else{
clearInterval(dropstart)
bouncestart=setInterval("bouncein()",50)
}
}

function bouncein(){
crossobj.top=parseInt(crossobj.top)-bouncelimit
if (bouncelimit<0)
bouncelimit+=8
bouncelimit=bouncelimit*-1
if (bouncelimit==0){
clearInterval(bouncestart)
}
}

function dismissbox(){
if (window.bouncestart) clearInterval(bouncestart)
crossobj.visibility="hidden"
}


</script>


<div id="dropin" style="<?php echo $DicTTCode?> position:absolute;visibility:hidden">
<?php include "$EventDir/index.php";?>
<div align="center"><a href="javascript:dismissbox()">(창닫기)</a></div>
</div>

</body>
?>