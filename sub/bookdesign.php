<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>▒ 두손기획 - 기획에서 인쇄까지 원스톱으로 해결해 드립니다.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: url('/img/bg.gif') no-repeat;
            margin: 0;
            padding: 0;
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

        table {
            width: 990px;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .content-table {
            width: 692px;
            margin: 10px 0;
        }

        .content-table img {
            border: none;
        }

        .info-table {
            width: 692px;
            margin: 20px auto;
            border-collapse: collapse;
            border: 1px solid #ccc;
        }

        .info-table th,
        .info-table td {
            padding: 10px;
            text-align: left;
        }

        .info-table th {
            background-color: #8AC4FD;
        }

        .price-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .price-table th,
        .price-table td {
            padding: 10px;
            text-align: center;
        }

        .price-table th {
            background-color: #BFEBFF;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const preloadImages = [
                '../img/main_m1a.jpg', '../img/main_m2a.jpg', '../img/main_m3a.jpg',
                '../img/main_m5a.jpg', '../img/main_m6a.jpg', '../img/main_m7a.jpg',
                '../img/main_m8a.jpg', '../img/main_m10a.jpg', '../img/main_m11a.jpg'
            ];

            preloadImages.forEach(src => {
                const img = new Image();
                img.src = src;
            });

            const swapImage = (element, src) => {
                element.dataset.originalSrc = element.dataset.originalSrc || element.src;
                element.src = src;
            };

            const restoreImage = () => {
                document.querySelectorAll("[data-original-src]").forEach(img => {
                    img.src = img.dataset.originalSrc;
                });
            };

            document.querySelectorAll("a[data-swap]").forEach(link => {
                link.addEventListener("mouseover", () => swapImage(link.querySelector("img"), link.dataset.src));
                link.addEventListener("mouseout", restoreImage);
            });
        });
    </script>
</head>

<body>
    <table>
        <tr>
            <td><?php include $_SERVER['DOCUMENT_ROOT'] . "/top7.htm"; ?></td>
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

    <table>
        <tr>
            <td width="160" valign="top">
                <p><?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm"; ?></p>
            </td>
            <td width="9"><img src="../img/space.gif" alt="spacer" width="9" height="9"></td>
            <td valign="top">
                <!--본문 내용 시작-->
                <div class="content-table">
                    <table>
                        <tr>
                            <td><a href="leaflet.php" data-swap="Image22" data-src="../img/main_m10a.jpg"><img src="../img/main_m10.jpg" name="Image22" alt="Leaflet" width="77" height="32"></a></td>
                            <td><a href="sticker.php" data-swap="Image25" data-src="../img/main_m7a.jpg"><img src="../img/main_m7.jpg" name="Image25" alt="Sticker" width="77" height="32"></a></td>
                            <td><a href="catalog.php" data-swap="Image20" data-src="../img/main_m2a.jpg"><img src="../img/main_m2.jpg" name="Image20" alt="Catalog" width="77" height="32"></a></td>
                            <td><a href="brochure.php" data-swap="Image21" data-src="../img/main_m3a.jpg"><img src="../img/main_m3.jpg" name="Image21" alt="Brochure" width="77" height="32"></a></td>
                            <td><a href="bookdesign.php" data-swap="Image26" data-src="../img/main_m8a.jpg"><img src="../img/main_m8.jpg" name="Image26" alt="Book Design" width="77" height="32"></a></td>
                            <td><a href="poster.php" data-swap="Image27" data-src="../img/main_m11a.jpg"><img src="../img/main_m11.jpg" name="Image27" alt="Poster" width="76" height="32"></a></td>
                            <td><a href="namecard.php" data-swap="Image23" data-src="../img/main_m5a.jpg"><img src="../img/main_m5.jpg" name="Image23" alt="Namecard" width="77" height="32"></a></td>
                            <td><a href="envelope.php" data-swap="Image24" data-src="../img/main_m6a.jpg"><img src="../img/main_m6.jpg" name="Image24" alt="Envelope" width="77" height="32"></a></td>
                            <td><a href="seosig.php" data-swap="Image19" data-src="../img/main_m1a.jpg"><img src="../img/main_m1.jpg" name="Image19" alt="Symbol" width="77" height="32"></a></td>
                        </tr>
                    </table>
                </div>

                <div class="content-table">
                    <img src="../img/t_bookdesign.gif" alt="Book Design" width="692" height="59">
                    <img src="../img/t_portfolio.gif" alt="Portfolio" width="600" height="22">
                    <table class="info-table">
                        <tr>
                            <td>
                                <?php
                                $CATEGORY = "북디자인";
                                $BBS_CODE = "portfolio";
                                $BbsDir = "../bbs/";
                                $DbDir = "..";
                                $table = "$BBS_CODE";
                                include "$BbsDir/bbs.php";
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="content-table">
                    <img src="../img/t_price.gif" alt="Price" width="600" height="22">
                    <table class="price-table">
                        <tr>
                            <th colspan="7">대봉투</th>
                        </tr>
                        <tr>
                            <th>구분</th>
                            <th colspan="2">크라프트</th>
                            <th colspan="2">모조120</th>
                            <th colspan="2">줄레쟈크 120, 체크레쟈크 120</th>
                        </tr>
                        <tr>
                            <th>수량</th>
                            <th>1도</th>
                            <th>2도</th>
                            <th>1도</th>
                            <th>2도</th>
                            <th>1도</th>
                            <th>2도</th>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>40,000</td>
                            <td>50,000</td>
                            <td>50,000</td>
                            <td>60,000</td>
                            <td>60,000</td>
                            <td>70,000</td>
                        </tr>
                        <tr>
                            <td>1,000</td>
                            <td>70,000</td>
                            <td>90,000</td>
                            <td>90,000</td>
                            <td>100,000</td>
                            <td>100,000</td>
                            <td>110,000</td>
                        </tr>
                        <tr>
                            <td>2,000</td>
                            <td>140,000</td>
                            <td>150,000</td>
                            <td>170,000</td>
                            <td>180,000</td>
                            <td>180,000</td>
                            <td>190,000</td>
                        </tr>
                    </table>
                    <table class="price-table">
                        <tr>
                            <th>구분</th>
                            <th>모조120</th>
                            <th>줄레쟈크 120, 체크레쟈크 120</th>
                        </tr>
                        <tr>
                            <th>수량</th>
                            <th>4도(올칼라)</th>
                            <th>4도(올칼라)</th>
                        </tr>
                        <tr>
                            <td>1,000</td>
                            <td>130,000</td>
                            <td>130,000</td>
                        </tr>
                        <tr>
                            <td>2,000</td>
                            <td>240,000</td>
                            <td>240,000</td>
                        </tr>
                        <tr>
                            <td>3,000</td>
                            <td>310,000</td>
                            <td>310,000</td>
                        </tr>
                    </table>
                </div>

                <div class="content-table">
                    <table class="price-table">
                        <tr>
                            <th colspan="6">소봉투</th>
                        </tr>
                        <tr>
                            <th>구분</th>
                            <th colspan="2">모조100</th>
                            <th>모조120</th>
                            <th colspan="2">줄레쟈크 120, 체크레쟈크 120</th>
                        </tr>
                        <tr>
                            <th>수량</th>
                            <th>1도</th>
                            <th>4도</th>
                            <th>1도</th>
                            <th>1도</th>
                            <th>4도</th>
                        </tr>
                        <tr>
                            <td>1,000</td>
                            <td>30,000</td>
                            <td>80,000</td>
                            <td>45,000</td>
                            <td>50,000</td>
                            <td>80,000</td>
                        </tr>
                        <tr>
                            <td>2,000</td>
                            <td>60,000</td>
                            <td>150,000</td>
                            <td>90,000</td>
                            <td>90,000</td>
                            <td>150,000</td>
                        </tr>
                        <tr>
                            <td>3,000</td>
                            <td>80,000</td>
                            <td>210,000</td>
                            <td>120,000</td>
                            <td>130,000</td>
                            <td>210,000</td>
                        </tr>
                    </table>
                </div>

                <div class="content-table">
                    <ul>
                        <li>옵셋인쇄(고급인쇄) : 4도 인쇄시만 뚜껑 인쇄 가능.</li>
                        <li>마스터인쇄(경인쇄) : 뚜껑 인쇄 불가능.</li>
                        <li>편집비 : 대.소봉투 1도 5,000원 / 칼라 15,000원 / 로고도안 별도</li>
                        <li>편집비 무료 : 직접 디자인해서 파일로 보낼 경우 (<a href="javascript:go_site(103);"><b>작업 시 유의사항</b></a> 꼭 참고하세요)</li>
                        <li>제작기간(고객께서 받으시기까지)은 약 4~5 일(후가공 여부에 따라 작업기간은 변경될 수 있습니다)</li>
                        <li>인쇄시 정매에서 약간의 로스분이 나옵니다.</li>
                        <li>배송료(택배비 착불), 부가세는 별도</li>
                    </ul>
                </div>
                <!--본문 내용 끝-->
            </td>
            <td width="9">&nbsp;</td>
            <td width="120" valign="top">
                <?php include $_SERVER['DOCUMENT_ROOT'] . "/right.htm"; ?>
            </td>
        </tr>
    </table>

    <?php include $_SERVER['DOCUMENT_ROOT'] . "/bottom.htm"; ?>
</body>

</html>
