<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="euc-kr">
    <title>▒ 두손기획 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</title>
    <style>
        body {
            margin: 0;
            background: url('/img/bg.gif') no-repeat;
        }

        table {
            font-size: 12px;
        }

        a {
            color: #333333;
            text-decoration: none;
        }

        a:hover {
            color: #666666;
        }

        a:visited {
            color: #666666;
        }
    </style>
    <script>
        function preloadImages() {
            const images = [
                '../img/main_m1a.jpg',
                '../img/main_m2a.jpg',
                '../img/main_m3a.jpg',
                '../img/main_m5a.jpg',
                '../img/main_m6a.jpg',
                '../img/main_m7a.jpg',
                '../img/main_m8a.jpg',
                '../img/main_m10a.jpg',
                '../img/main_m11a.jpg'
            ];
            images.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        }

        function swapImage(id, newSrc) {
            const img = document.getElementById(id);
            if (img) {
                img.dataset.origSrc = img.src;
                img.src = newSrc;
            }
        }

        function restoreImage(id) {
            const img = document.getElementById(id);
            if (img && img.dataset.origSrc) {
                img.src = img.dataset.origSrc;
            }
        }

        document.addEventListener('DOMContentLoaded', preloadImages);
    </script>
</head>

<body>
    <table width="990" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td width="990" valign="top">
                <!-- 메인 이미지 로고 시작 -->
                <?php require $_SERVER['DOCUMENT_ROOT'] . '/top.htm'; ?>
                <!-- 메인 이미지 로고 끝 -->
            </td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
    </table>

    <map name="Map2">
        <area shape="rect" coords="4,7,162,127" href="#">
        <area shape="rect" coords="165,7,323,127" href="#">
        <area shape="rect" coords="4,133,162,253" href="#">
        <area shape="rect" coords="165,133,323,253" href="#">
        <area shape="rect" coords="326,7,484,127" href="#">
        <area shape="rect" coords="325,132,484,253" href="#">
        <area shape="rect" coords="487,7,645,127" href="#">
        <area shape="rect" coords="487,133,645,253" href="#">
    </map>

    <table width="990" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td width="160" valign="top">
                <!-- 왼쪽 배너 메뉴 시작 -->
                <?php require $_SERVER['DOCUMENT_ROOT'] . '/left.htm'; ?>
                <!-- 왼쪽 배너 메뉴 끝 -->
            </td>
            <td width="9"><img src="../img/space.gif" width="9" height="9"></td>
            <td valign="top">
                <!-- 본문 내용 시작 -->
                <table width="692" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td><a href="seosig.htm" onMouseOut="restoreImage('Image19')" onMouseOver="swapImage('Image19', '../img/main_m1a.jpg')"><img src="../img/main_m1.jpg" id="Image19" width="77" height="32" border="0"></a></td>
                        <td><a href="catalog.htm" onMouseOut="restoreImage('Image20')" onMouseOver="swapImage('Image20', '../img/main_m2a.jpg')"><img src="../img/main_m2.jpg" id="Image20" width="77" height="32" border="0"></a></td>
                        <td><a href="brochure.htm" onMouseOut="restoreImage('Image21')" onMouseOver="swapImage('Image21', '../img/main_m3a.jpg')"><img src="../img/main_m3.jpg" id="Image21" width="77" height="32" border="0"></a></td>
                        <td><a href="leaflet.htm" onMouseOut="restoreImage('Image211')" onMouseOver="swapImage('Image211', '../img/main_m10a.jpg')"><img src="../img/main_m10.jpg" id="Image211" width="77" height="32" border="0"></a></td>
                        <td><a href="poster.htm" onMouseOut="restoreImage('Image231')" onMouseOver="swapImage('Image231', '../img/main_m11a.jpg')"><img src="../img/main_m11.jpg" id="Image231" width="76" height="32" border="0"></a></td>
                        <td><a href="namecard.htm" onMouseOut="restoreImage('Image23')" onMouseOver="swapImage('Image23', '../img/main_m5a.jpg')"><img src="../img/main_m5.jpg" id="Image23" width="77" height="32" border="0"></a></td>
                        <td><a href="envelope.htm" onMouseOut="restoreImage('Image24')" onMouseOver="swapImage('Image24', '../img/main_m6a.jpg')"><img src="../img/main_m6.jpg" id="Image24" width="77" height="32" border="0"></a></td>
                        <td><a href="sticker.htm" onMouseOut="restoreImage('Image25')" onMouseOver="swapImage('Image25', '../img/main_m7a.jpg')"><img src="../img/main_m7.jpg" id="Image25" width="77" height="32" border="0"></a></td>
                        <td><a href="bookdesign.htm" onMouseOut="restoreImage('Image26')" onMouseOver="swapImage('Image26', '../img/main_m8a.jpg')"><img src="../img/main_m8.jpg" id="Image26" width="77" height="32" border="0"></a></td>
                    </tr>
                </table>
                <table width="692" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td height="323" valign="top"><img src="../img/t_open.jpg" width="692" height="59"><br><br>
                            <table width="620" border="0" align="center" cellpadding="0" cellspacing="0" style="background: url('../img/open_bg.gif')">
                                <tr>
                                    <td colspan="2"><img src="../img/open_top.gif" width="620" height="8"></td>
                                </tr>
                                <tr>
                                    <td width="106">
                                        <div align="center"><img src="../img/open_img1.gif" width="70" height="70"></div>
                                    </td>
                                    <td width="514"><img src="../img/open_t1.gif" width="482" height="23"><br><br>
                                        <font color="#996600">업무의 효율을 극대화할 수 있는 업무 공간 인테리어에서 부터 로고/심볼을 비롯한 명함, <br> 명패, 각종 서식류 등 회사를 창업하는데 필요한 모든 업무를 지원합니다.</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><img src="../img/open_bottom.gif" width="620" height="8"></td>
                                </tr>
                            </table><br>
                            <table width="620" border="0" align="center" cellpadding="0" cellspacing="0" style="background: url('../img/open_bg.gif')">
                                <tr>
                                    <td colspan="2"><img src="../img/open_top.gif" width="620" height="8"></td>
                                </tr>
                                <tr>
                                    <td width="106">
                                        <div align="center"><img src="../img/open_img2.gif" width="70" height="70"></div>
                                    </td>
                                    <td width="514"><img src="../img/open_t2.gif" width="482" height="23"><br><br>
                                        <font color="#996600">친근함, 신선함, 신뢰감이 공존하는 공간을 제공합니다.</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><img src="../img/open_bottom.gif" width="620" height="8"></td>
                                </tr>
                            </table><br>
                            <table width="620" border="0" align="center" cellpadding="0" cellspacing="0" style="background: url('../img/open_bg.gif')">
                                <tr>
                                    <td colspan="2"><img src="../img/open_top.gif" width="620" height="8"></td>
                                </tr>
                                <tr>
                                    <td width="106">
                                        <div align="center"><img src="../img/open_img3.gif" width="70" height="70"></div>
                                    </td>
                                    <td width="514"><img src="../img/open_t3.gif" width="482" height="23"><br><br>
                                        <font color="#996600">업종의 특성을 정확히 파악하여 소비자에게 어필할 수 있는 디자인 서비스를 제공합니다. <br> 두손과 함께 업계 최고가 되십시오.</font>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"><img src="../img/open_bottom.gif" width="620" height="8"></td>
                                </tr>
                            </table>
                            <p>&nbsp;</p>
                        </td>
                    </tr>
                </table>
                <!-- 본문 내용 끝 -->
            </td>
            <td width="9">&nbsp;</td>
            <td width="120" valign="top">
                <!-- 오른쪽 배너 시작 -->
                <?php require $_SERVER['DOCUMENT_ROOT'] . '/right.htm'; ?>
                <!-- 오른쪽 배너 끝 -->
            </td>
        </tr>
    </table>
    <!-- 하단부분 시작 -->
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/bottom.htm'; ?>
    <!-- 하단부분 끝 -->
</body>
</html>
