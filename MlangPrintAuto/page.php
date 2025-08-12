<?php include "./MlangPrintAutoTop.php"; ?>

<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <title>Estimate Page</title>
    <style>
        body, td, th {
            font-family: '굴림', '굴림체';
            font-size: 12px;
            color: #999999;
        }

        .style11 {
            color: #666666;
        }

        .style16 {
            color: #75F2A4;
        }

        table {
            width: 600px;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            border: 1px solid #FFFFFF;
            padding: 5px;
            text-align: center;
        }

        .bg-gray {
            background-color: #D6D6D6;
        }

        .bg-light-gray {
            background-color: #EDEDED;
        }

        .bg-yellow {
            background-color: #FFFFCC;
        }

        .bg-dark-gray {
            background-color: #F4F4F4;
        }
    </style>
    <script>
        function resizeFrame(name) {
            var oBody = document.body;
            var oFrame = parent.document.getElementById(name);
            var minHeight = 320;
            var minWidth = 465;
            var iHeight = oBody.scrollHeight + (oBody.offsetHeight - oBody.clientHeight);
            var iWidth = oBody.scrollWidth + (oBody.offsetWidth - oBody.clientWidth);

            if (iHeight < minHeight) iHeight = minHeight;
            if (iWidth < minWidth) iWidth = minWidth;

            oFrame.style.height = iHeight + 'px';
            oFrame.style.width = iWidth + 'px';

            parent.scrollTo(0, 0);
        }

        window.onload = function() {
            resizeFrame('innerFrame');
        };
    </script>
</head>

<body>
    <?php if (isset($page) && $page == "1") { ?>
        <table>
            <tr>
                <td>
                    <div align="left">
                        <p class="style11"><strong><br>
                            <span class="style16">＊</span> 150g 아트지, 스노우지</strong></p>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr class="bg-gray">
                            <th>구분</th>
                            <th>4절</th>
                            <th>국2절</th>
                            <th>2절</th>
                            <th>국전지</th>
                        </tr>
                        <tr class="bg-yellow">
                            <td class="bg-light-gray">50매</td>
                            <td class="bg-dark-gray">88,000</td>
                            <td class="bg-dark-gray">101,000</td>
                            <td class="bg-dark-gray">144,000</td>
                            <td class="bg-dark-gray">172,000</td>
                        </tr>
                        <!-- Add more rows here as needed -->
                    </table>
                </td>
            </tr>
        </table>
    <?php } ?>

    <?php if (isset($page) && $page == "2") { ?>
        <div align="left">
            <br>
            <strong><span class="style16">＊</span> <span class="style11">종 이</span></strong>
            <span class="style11"> | 200g, 250g 스노우화이트&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;규 격 | 230 * 155 mm <br>
            &nbsp;&nbsp;&nbsp;매 수 | 14~16P 가능&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;인 쇄 | 양면 4도 풀칼라 옵셋 독판인쇄 <br>
            &nbsp;&nbsp;&nbsp;봉 투 | 100g 모조 봉투와 1도 인쇄 무료</span>
            <br><br>
        </div>
        <table>
            <tr class="bg-gray">
                <th rowspan="2">구분</th>
                <th colspan="2">200 스노우 양면 원색</th>
                <th colspan="2">250 스노우 양면 원색</th>
            </tr>
            <tr class="bg-gray">
                <th class="bg-light-gray">금 액</th>
                <th class="bg-light-gray">단 가</th>
                <th class="bg-light-gray">금 액</th>
                <th class="bg-light-gray">단 가</th>
            </tr>
            <tr class="bg-yellow">
                <td class="bg-light-gray">100부</td>
                <td class="bg-dark-gray">530,000</td>
                <td class="bg-dark-gray">5,300</td>
                <td class="bg-dark-gray">540,000</td>
                <td class="bg-dark-gray">5,400</td>
            </tr>
            <!-- Add more rows here as needed -->
        </table>
    <?php } ?>

    <?php if (isset($page) && $page == "3") { ?>
        <table>
            <tr>
                <td>
                    <div align="left">
                        <p class="style11"><strong><br>
                            <span class="style16">＊</span> 300g Art 단면 칼라인쇄 단면코팅</strong></p>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr class="bg-gray">
                            <th>구분</th>
                            <th>220*305*70(포켓1개)</th>
                            <th>230*310*90(포켓1개)</th>
                            <th>220*305*70(포켓2개)</th>
                            <th>230*310*90(포켓2개)</th>
                        </tr>
                        <tr class="bg-yellow">
                            <td class="bg-light-gray">1,000부</td>
                            <td class="bg-dark-gray">338,000</td>
                            <td class="bg-dark-gray">381,000</td>
                            <td class="bg-dark-gray"></td>
                            <td class="bg-dark-gray"></td>
                        </tr>
                        <!-- Add more rows here as needed -->
                    </table>
                </td>
            </tr>
        </table>
    <?php } ?>

    <?php include "./MlangPrintAutoDown.php"; ?>
</body>

</html>
