<?php include $_SERVER['DOCUMENT_ROOT'] . "/top5.php"; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm"; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/right.htm"; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/bottom.htm"; ?>

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

        .style1 {
            color: #FF0000;
        }

        .style2 {
            color: #FFFFFF;
            font-weight: bold;
        }

        .style3 {
            color: #FFFFFF;
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
            width: 614px;
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

        .download-table {
            width: 614px;
            margin: 20px auto;
            border-collapse: collapse;
            border: 1px solid #ccc;
        }

        .download-table th,
        .download-table td {
            padding: 10px;
            text-align: center;
        }

        .download-table th {
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
            <td><?php include $_SERVER['DOCUMENT_ROOT'] . "/top5.php"; ?></td>
        </tr>
        <tr>
            <td height="10"></td>
        </tr>
    </table>

    <table>
        <tr>
            <td width="160" valign="top">
                <p><?php include $_SERVER['DOCUMENT_ROOT'] . "/left.htm"; ?></p>
            </td>
            <td width="9"><img src="../img/space.gif" alt="spacer" width="9" height="9"></td>
            <td valign="top">
                <div class="content-table">
                    <table>
                        <tr>
                            <td><a href="seosig.htm" data-swap="Image19" data-src="../img/main_m1a.jpg"><img src="../img/main_m1.jpg" name="Image19" alt="Symbol" width="77" height="32"></a></td>
                            <td><a href="catalog.htm" data-swap="Image20" data-src="../img/main_m2a.jpg"><img src="../img/main_m2.jpg" name="Image20" alt="Catalog" width="77" height="32"></a></td>
                            <td><a href="brochure.htm" data-swap="Image21" data-src="../img/main_m3a.jpg"><img src="../img/main_m3.jpg" name="Image21" alt="Brochure" width="77" height="32"></a></td>
                            <td><a href="leaflet.htm" data-swap="Image211" data-src="../img/main_m10a.jpg"><img src="../img/main_m10.jpg" name="Image211" alt="Leaflet" width="77" height="32"></a></td>
                            <td><a href="poster.htm" data-swap="Image231" data-src="../img/main_m11a.jpg"><img src="../img/main_m11.jpg" name="Image231" alt="Poster" width="76" height="32"></a></td>
                            <td><a href="namecard.htm" data-swap="Image23" data-src="../img/main_m5a.jpg"><img src="../img/main_m5.jpg" name="Image23" alt="Namecard" width="77" height="32"></a></td>
                            <td><a href="envelope.htm" data-swap="Image24" data-src="../img/main_m6a.jpg"><img src="../img/main_m6.jpg" name="Image24" alt="Envelope" width="77" height="32"></a></td>
                            <td><a href="sticker.htm" data-swap="Image25" data-src="../img/main_m7a.jpg"><img src="../img/main_m7.jpg" name="Image25" alt="Sticker" width="77" height="32"></a></td>
                            <td><a href="bookdesign.htm" data-swap="Image26" data-src="../img/main_m8a.jpg"><img src="../img/main_m8.jpg" name="Image26" alt="Book Design" width="77" height="32"></a></td>
                        </tr>
                    </table>
                </div>

                <div class="content-table">
                    <table>
                        <tr>
                            <td valign="top">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <img src="../img/t_attention.gif" alt="Attention" width="692" height="31">
                                <br><br>
                                <table class="info-table">
                                    <tr>
                                        <td width="20"><img src="../img/icon_cross_green.gif" alt="icon" width="10" height="10"></td>
                                        <td>반드시 <strong><span style="color:#FF0000;">완성된 원고</span></strong>를 접수하며 <strong><span style="color:#FF0000;">2회의 교정,수정</span></strong>을 기본으로 합니다. (추가 수정은 협의하여 추가비용을 결정)</td>
                                    </tr>
                                    <tr>
                                        <td width="20"><img src="../img/icon_cross_green.gif" alt="icon" width="10" height="10"></td>
                                        <td>원고 접수시 원하시는 디자인 컨셉이나 샘플을 보내주시면 작업에 참고하여 더욱 원활한 의사소통이 가능할 것입니다. <strong><span style="color:#FF0000;">단</span></strong> 시안 완성 후 다른 시안을 요구하실 때에는 추가비용이 소요됩니다.</td>
                                    </tr>
                                    <!-- More items can be added here following the same pattern -->
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div align="center">
                        <img src="./images/size_ex.jpg" alt="Size Examples" width="615" height="831">
                    </div>

                    <div class="content-table">
                        <table class="download-table">
                            <tr>
                                <th colspan="6"><strong>작업사이즈 다운로드</strong><br><span class="style1">※ 각 절수를 클릭하시면 실사이즈 작업용 ai파일을<strong> 다운</strong>받으실 수 있습니다.</span></th>
                            </tr>
                            <tr>
                                <th>절수</th>
                                <th>작업사이즈</th>
                                <th>재단사이즈</th>
                                <th>절수</th>
                                <th>작업사이즈</th>
                                <th>재단사이즈</th>
                            </tr>
                            <tr>
                                <td class="style2"><a href="./images/32size.zip" class="style3">32절</a></td>
                                <td>130mm x 185mm</td>
                                <td>127mm x 182mm</td>
                                <td class="style2"><a href="./images/A5size.zip" class="style3">A5</a></td>
                                <td>150mm x 213mm</td>
                                <td>147mm x 210mm</td>
                            </tr>
                            <!-- More rows can be added here following the same pattern -->
                        </table>
                    </div>

                    <div class="content-table">
                        <table class="info-table">
                            <tr>
                                <td colspan="2"><strong>포토샵 디자인 작업시 주사항 (Adobe Photoshop CS3 이하)</strong></td>
                            </tr>
                            <tr>
                                <td width="20"><img src="../img/icon_cross_green.gif" alt="icon" width="10" height="10"></td>
                                <td>작업모드 CMYK 색상으로 작업합니다. RGB는 화면상의 색상이므로 출력시 정확한 색상이 나오지 않습니다.</td>
                            </tr>
                            <!-- More items can be added here following the same pattern -->
                        </table>
                    </div>
                </div>
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
