<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>�μձ�ȹ - ��ȹ���� �μ���� ���������� �ذ��� �帳�ϴ�.</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('/img/bg.gif');
        }
        table {
            font-size: 12px;
            margin: 0 auto;
        }
        a {
            color: #333333;
            text-decoration: none;
        }
        a:hover {
            color: #666666;
        }
        .style1 {
            color: #666666;
        }
        .style2 {
            color: #333333;
            font-weight: bold;
        }
        .style3 {
            color: #666666;
            font-weight: bold;
        }
        .main-links img {
            width: 77px;
            height: 32px;
        }
        .main-links img:last-child {
            width: 129px;
        }
        .info-table {
            width: 100%;
            background-color: #CCCCCC;
        }
        .info-table td {
            background-color: #FFFFFF;
            padding: 20px;
        }
        .note {
            background-color: #D6D6D6;
        }
        .note td {
            background-color: #ffffff;
            padding: 10px;
        }
        .content_title {
            font-weight: bold;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function preloadImages() {
                const images = [
                    '../img/main_m1a.jpg', '../img/main_m3a.jpg',
                    '../img/main_m5a.jpg', '../img/main_m8a.jpg',
                    '../img/main_m2a.jpg', '../img/main_m10a.jpg',
                    '../img/main_m11a.jpg', '../img/main_m6a.jpg',
                    '../img/main_m7a.jpg', '../img/stickerbutton.jpg',
                    '../img/stickerdayover.jpg', '../img/subjk-06.jpg'
                ];
                images.forEach(function(src) {
                    const img = new Image();
                    img.src = src;
                });
            }

            preloadImages();
        });

        function swapImage(img, src) {
            const original = img.src;
            img.src = src;
            img.addEventListener('mouseout', function() {
                img.src = original;
            }, { once: true });
        }
    </script>
</head>
<body>
    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td width="990" valign="top">
                <!-- ���� �̹��� �ΰ� ���� -->
                <!-- <?php include "$DOCUMENT_ROOT/top.htm"; ?> -->
                <!-- ���� �̹��� �ΰ� �� -->
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

    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td width="160" height="1" valign="top">
                <p>
                    <!-- ���� ��� �޴� ���� -->
                    <!-- <?php include "$DOCUMENT_ROOT/left.htm"; ?> -->
                    <!-- ���� ��� �޴� �� -->
                </p>
            </td>
            <td width="9"><img src="../img/space.gif" width="9" height="9"></td>
            <td>
                <!-- ���� ���� ���� -->
                <table width="692" border="0" cellspacing="0" cellpadding="0" class="main-links">
                    <tr>
                        <td><a href="leaflet.htm" onmouseover="swapImage(this, '../img/main_m10a.jpg')"><img src="../img/main_m10.jpg" name="Image22" border="0"></a></td>
                        <td><a href="sticker.htm" onmouseover="swapImage(this, '../img/main_m7a.jpg')"><img src="../img/main_m7.jpg" name="Image25" border="0"></a></td>
                        <td><a href="catalog.htm" onmouseover="swapImage(this, '../img/main_m2a.jpg')"><img src="../img/main_m2.jpg" name="Image20" border="0"></a></td>
                        <td><a href="brochure.htm" onmouseover="swapImage(this, '../img/main_m3a.jpg')"><img src="../img/main_m3.jpg" name="Image21" border="0"></a></td>
                        <td><a href="bookdesign.htm" onmouseover="swapImage(this, '../img/main_m8a.jpg')"><img src="../img/main_m8.jpg" name="Image26" border="0"></a></td>
                        <td><a href="poster.htm" onmouseover="swapImage(this, '../img/main_m11a.jpg')"><img src="../img/main_m11.jpg" name="Image27" border="0"></a></td>
                        <td><a href="namecard.htm" onmouseover="swapImage(this, '../img/main_m5a.jpg')"><img src="../img/main_m5.jpg" name="Image23" border="0"></a></td>
                        <td><a href="envelope.htm" onmouseover="swapImage(this, '../img/main_m6a.jpg')"><img src="../img/main_m6.jpg" name="Image24" border="0"></a></td>
                        <td><a href="seosig.htm" onmouseover="swapImage(this, '../img/main_m1a.jpg')"><img src="../img/main_m1.jpg" name="Image19" border="0"></a></td>
                    </tr>
                </table>

                <table width="692" height="873" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <img src="../img/t_info.gif" width="692" height="31">
                            <br><br>
                            <div style="margin-top:20px;"></div>
                            <div style="padding-top:10px; background:#F1F1F1; text-align:center;">
                                <div align="left" style="height:26px; padding:3px 0 0 10px;"></div>
                                <div id="boxScroll" class="scroll">
                                    <table cellspacing="0" cellpadding="6" width="100%" border="0">
                                        <tr>
                                            <th><div class="content_title">���������� �������� �� �̿����</div></th>
                                        </tr>
                                        <tr>
                                            <td style="text-align: justify;">
                                                �� �μձ�ȹ�� ȸ���Բ� �ִ������� ����ȭ�ǰ� ����ȭ�� ���񽺸� �����ϱ� ���Ͽ� ������ ���� �������� ���������� �����ϰ� �ֽ��ϴ�. <br>
                                                - ����, ���̵�, ��й�ȣ, �ֹε�Ϲ�ȣ : ȸ���� ���� �̿뿡 ���� ���� �ĺ� ������ �̿� <br>
                                                - �̸����ּ�, �̸��� ���ſ���, ��ȭ��ȣ : �������� ����, ���� �ǻ� Ȯ��, �Ҹ� ó�� �� ��Ȱ�� �ǻ���� ����� Ȯ��, ���ο� ����/�Ż�ǰ�̳� �̺�Ʈ ������ �ȳ� <br>
                                                - �ּ�, ��ȭ��ȣ : ��ǰ�� ���� ��ǰ ��ۿ� ���� ��Ȯ�� ������� Ȯ�� <br>
                                                - ��й�ȣ ��Ʈ�� ������ �亯 : ��й�ȣ�� ���� ����� �ż��� ó���� ���� ���� <br>
                                                - �� �� �����׸� : ���θ��� ���񽺸� �����ϱ� ���� �ڷ� <br>
                                                �� ��, �̿����� �⺻�� �α� ħ���� ����� �ִ� �ΰ��� ��������(���� �� ����, ��� �� ����, ����� �� ������, ��ġ�� ���� �� ���˱��, �ǰ����� �� ����Ȱ ��)�� �������� �ʽ��ϴ�.
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="6" width="100%" border="0">
                                        <tr>
                                            <th><div class="content_title">���������� ��������</div></th>
                                        </tr>
                                        <tr>
                                            <td style="text-align: justify;">
                                                �μձ�ȹ�� ������ ȸ������ ���� ���� ��κ��� �������� �����Ӱ� ������ �� �ֽ��ϴ�. �μձ�ȹ�� ȸ���� ���񽺸� �̿��Ͻð��� �� ��� ������ ������ �Է����ּž� �ϸ� �����׸��� �Է��Ͻ��� �ʾҴ� �Ͽ� ���� �̿뿡 ������ �����ϴ�. <br>
                                                1) ȸ�� ���Խ� �����ϴ� ���������� ���� <br>
                                                - �ʼ��׸� : ��� ID, ��й�ȣ, ��й�ȣ ��Ʈ�� ������ �亯, ����, �ֹε�Ϲ�ȣ, �ּ�, ��ȭ��ȣ, �̸����ּ�, �̸��� ���� ���� <br>
                                                - �����׸� : ȸ���ּ�, ȸ����ȭ��ȣ, �������, ��ȥ����, ��ȥ�����, ����, ����ռҵ�, �����з�, �ڳ��, ��������
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellspacing="0" cellpadding="6" width="100%" border="0">
                                        <tr>
                                            <th><div class="content_title">���������� �����Ⱓ �� �̿�Ⱓ </div></th>
                                        </tr>
                                        <tr>
                                            <td style="text-align: justify;">
                                                �� ������ ���������� ������ ���� ���������� �������� �Ǵ� �������� ������ �޼��Ǹ� �ı�˴ϴ�. ��, ��� �� ���ù����� ������ ���Ͽ� ������ ���� �ŷ� ���� �Ǹ� �ǹ� ������ Ȯ�� ���� ������ �����Ⱓ �����Ͽ��� �� �ʿ䰡 ���� ��쿡�� �����Ⱓ �����մϴ�. <br>
                                                - ȸ������������ ���, ȸ�������� Ż���ϰų� ȸ������ ������ ��� �� ������ ������ ��������, �Ⱓ �� �����ϴ� �������� �׸��� �����Ͽ� ���Ǹ� ���մϴ�. <br>
                                                - ��� �Ǵ� û��öȸ � ���� ��� : 5�� <br>
                                                - ��ݰ��� �� ��ȭ���� ���޿� ���� ��� : 5�� <br>
                                                - �Һ����� �Ҹ� �Ǵ� ����ó���� ���� ��� : 3�� <br>
                                                �� ������ ���Ǹ� �޾� �����ϰ� �ִ� �ŷ����� ���� ���ϲ��� ������ �䱸�ϴ� ��� �μձ�ȹ�� ��ü���� �� ����,Ȯ�� �� �� �ֵ��� ��ġ�մϴ�.
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <table class="note">
                    <tr>
                        <td>
                            <table>
                                <tbody>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><span>&nbsp;</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%"><img height="3" src="" width="3"></td>
                                        <td width="99%">
                                            <span>* ���������κ� : A2 100,000��, 4�� 100,000��, A3 80,000�� <strong>(����)</strong>�۾��� ���ݿ� ���� ���������κ�� ������ �� �ֽ��ϴ�.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%">
                                            <span>* ���������κ� ���� : ���� �������ؼ� ���Ϸ� ���� ���(<a href="javascript:go_site(103);"><strong><span style="color: #ff0000;">�۾� �� ���ǻ���</span></strong></a> �� �����ϼ���)</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%"><span>&nbsp;</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%">
                                            <span>* ���۱Ⱓ(�������� �����ñ����)�� �� 2~3��(�İ��� ���ο� ���� �۾��Ⱓ�� ����� �� �ֽ��ϴ�)</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="15"></td>
                                        <td height="15">
                                            <span>* �μ�� ���ſ��� �ణ�� �ν����� ���ɴϴ�.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="15"></td>
                                        <td height="15">
                                            <span>* <strong>��۷�(�ù��), �ΰ����� ���� </strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="15"></td>
                                        <td height="15">
                                            <span class="style3">�� �̸����̳� ���ϵ忡 �ڷḦ �ø��ô� ��� �ݵ�� ������ ����ó�� ����ñ� �ٶ��ϴ�.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="9">&nbsp;</td>
            <td width="120" valign="top">
                <!-- ������ ��� ���� -->
                <!-- <?php include "$DOCUMENT_ROOT/right.htm"; ?> -->
                <!-- ������ ��� �� -->
            </td>
        </tr>
    </table>
    <!-- �ϴܺκ� ���� -->
    <!-- <?php include "$DOCUMENT_ROOT/bottom.htm"; ?> -->
    <!-- �ϴܺκ� �� -->
</body>
</html>
