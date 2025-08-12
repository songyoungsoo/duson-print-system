<?php
include "top.php";
include "../db.php";

// 데이터베이스 연결
$db = new mysqli($host, $user, $password, $dataname);

// 연결 확인
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// UTF-8 설정
$db->set_charset("utf8");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 페이지</title>
    <style>
        .content {
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-spacing: 50px;
        }
        .info-table {
            border: 0;
            background-color: teal;
            cellspacing: 1;
            cellpadding: 5;
        }
        .info-table caption {
            text-align: top;
            font-weight: bold;
        }
        .info-table td {
            background-color: #F5F5F5;
        }
        .info-table td.value {
            background-color: white;
        }
    </style>
    <script>
        function verNumIE() {
            var brVer = navigator.userAgent;
            var brVerId = brVer.indexOf('MSIE');
            return brVer.substr(brVerId, 8);
        }

        function verNumOt() {
            var brVer = navigator.userAgent;
            var reg = new RegExp('/');
            var brVerId = brVer.search(reg);
            return brVer.substring(brVerId + 1);
        }

        function getBrowserInfo() {
            var navName = navigator.appName;
            var brNum = (navigator.appName === 'Microsoft Internet Explorer') ? verNumIE() : verNumOt();
            var platform = navigator.platform;
            var javaEnabled = navigator.javaEnabled() ? "Yes" : "No";

            return {
                navName: navName,
                brNum: brNum,
                platform: platform,
                javaEnabled: javaEnabled
            };
        }

        function displayBrowserInfo() {
            var info = getBrowserInfo();
            document.write("<table class='info-table'>");
            document.write("<caption>관계자님의 브라우저 정보</caption>");
            document.write("<tr><td><b>브라우저 이름 : </b></td><td class='value'>" + info.navName + "</td></tr>");
            document.write("<tr><td><b>플랫폼의 이름 : </b></td><td class='value'>" + info.platform + "</td></tr>");
            document.write("<tr><td><b>브라우저의 버전 : </b></td><td class='value'>" + info.brNum + "</td></tr>");
            document.write("<tr><td><b>자바 실행 가능여부 : </b></td><td class='value'>" + info.javaEnabled + "</td></tr>");
            document.write("</table>");
        }
    </script>
</head>
<body>
    <div class="content">
        <br><br>
        관리자의 페이지에 진입하셨음을 진심으로 환영합니다.
        <br><br>
        사업 무궁한 발전이 있기를 바랍니다..
        <br><br>
        오늘 하루 좋은일 많으시기도 바래요 *^^*
        <br><br>
        <script>displayBrowserInfo();</script>
        <br><br>
        현페이지는 많은 입력폼과 출력관계로 인해 <u>Microsoft Internet Explorer 6.X, 해상도: 1152X864</u>에 최적화 되어져 있습니다.<br>
        Microsoft Internet Explorer 버젼이 낮을 시 프로그램 에러가 생길 수 있으므로 꼭 업데이트 후 작업을 해주시기 바랍니다.
        <br><br>
        <a href='http://download.naver.com/pds_leaf.asp?pg_code=860&pv_code=17' target='_blank'>익스플로어 6.X 한글판 풀버젼 다운 받는곳(클릭하세요!!)</a>
        <br><br>
    </div>
</body>
</html>

<?php
include "./down.php";
?>
