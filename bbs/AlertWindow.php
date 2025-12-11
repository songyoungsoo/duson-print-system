<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style type="text/css">
        #cache {position:absolute; top:200px; z-index:10; visibility:hidden;}
    </style>
    <script src="/admin/js/coolbar.js" type="text/javascript"></script>
    <script type="text/javascript">
    var cach = null;
    window.onload = function() {
        // 브라우저 호환성 체크
        if (document.getElementById) {
            cach = document.getElementById("cache").style;
        } else if (document.all) {
            cach = cache.style;
        } else if (document.layers) {
            cach = document.cache;
        }
        // 중앙 정렬 (IE 전용 setExpression은 최신 브라우저에서 지원 안함)
        if (cach) {
            cach.visibility = "visible";
            // 중앙정렬: 최신 브라우저에서는 left/top 계산 필요
            var cacheDiv = document.getElementById("cache");
            if (cacheDiv) {
                var left = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) / 2 - cacheDiv.offsetWidth / 2;
                var top = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) / 2 - cacheDiv.offsetHeight / 2;
                cacheDiv.style.left = left + "px";
                cacheDiv.style.top = top + "px";
            }
        }
    }
    function cacheOff() {
        if (cach) cach.visibility = "hidden";
    }
    </script>
</head>
<body onload="cacheOff()">

<!-- 로딩전에 보여주는 공치창입니다. -->
<div id="cache">
    <table border="1" align="center" width="160" height="100" cellpadding="0" cellspacing="5" class="coolBar">
        <tr>
            <td align="center">
                <span style="font-size:9pt; color:#000000;">
                    페이지를 열고 있어요..<br><br>
                    조금 기다려 주셔요..*^^*
                </span><br><br>
                ☞ <a href="javascript:location.reload();">열리지 않을때 (클릭)</a>
            </td>
        </tr>
    </table>
</div>
<!-- 로딩전에 보여주는 공치창입니다. -->

</body>
</html>