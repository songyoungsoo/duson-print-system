<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="euc-kr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>�� �μձ�ȹ - ��ȹ���� �μ���� ���������� �ذ��� �帳�ϴ�.</title>
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
        .style1 {color: #666666}
        .style2 {
            color: #333333;
            font-weight: bold;
        }
        .style3 {color: #666666; font-weight: bold; }
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
    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td width="990" valign="top">
                <!--���� �̹��� �ΰ� ���� -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/top.htm'; ?>
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
            <td width="160" valign="top">
                <!-- ���� ��� �޴� ���� -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/left.htm'; ?>
                <!-- ���� ��� �޴� �� -->
            </td>
            <td width="9"><img src="../img/space.gif" width="9" height="9"></td>
            <td valign="top">
                <!-- ���� ���� ���� -->
                <table width="692" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td><a href="leaflet.htm" onmouseout="restoreImage('Image22')" onmouseover="swapImage('Image22', '../img/main_m10a.jpg')"><img src="../img/main_m10.jpg" id="Image22" width="77" height="32" border="0"></a></td>
                        <td><a href="sticker.htm" onmouseout="restoreImage('Image25')" onmouseover="swapImage('Image25', '../img/main_m7a.jpg')"><img src="../img/main_m7.jpg" id="Image25" width="77" height="32" border="0"></a></td>
                        <td><a href="catalog.htm" onmouseout="restoreImage('Image20')" onmouseover="swapImage('Image20', '../img/main_m2a.jpg')"><img src="../img/main_m2.jpg" id="Image20" width="77" height="32" border="0"></a></td>
                        <td><a href="brochure.htm" onmouseout="restoreImage('Image21')" onmouseover="swapImage('Image21', '../img/main_m3a.jpg')"><img src="../img/main_m3.jpg" id="Image21" width="77" height="32" border="0"></a></td>
                        <td><a href="bookdesign.htm" onmouseout="restoreImage('Image26')" onmouseover="swapImage('Image26', '../img/main_m8a.jpg')"><img src="../img/main_m8.jpg" id="Image26" width="77" height="32" border="0"></a></td>
                        <td><a href="poster.htm" onmouseout="restoreImage('Image27')" onmouseover="swapImage('Image27', '../img/main_m11a.jpg')"><img src="../img/main_m11.jpg" id="Image27" width="76" height="32" border="0"></a></td>
                        <td><a href="namecard.htm" onmouseout="restoreImage('Image23')" onmouseover="swapImage('Image23', '../img/main_m5a.jpg')"><img src="../img/main_m5.jpg" id="Image23" width="77" height="32" border="0"></a></td>
                        <td><a href="envelope.htm" onmouseout="restoreImage('Image24')" onmouseover="swapImage('Image24', '../img/main_m6a.jpg')"><img src="../img/main_m6.jpg" id="Image24" width="77" height="32" border="0"></a></td>
                        <td><a href="seosig.htm" onmouseout="restoreImage('Image19')" onmouseover="swapImage('Image19', '../img/main_m1a.jpg')"><img src="../img/main_m1.jpg" id="Image19" width="77" height="32" border="0"></a></td>
                    </tr>
                </table>
                <table width="692" height="873" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td valign="top"><img src="../img/t_info.gif" width="692" height="31"><br><br>
                            <table width="620" height="777" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td height="344" valign="top">
                                        <p class="style2">[�������� ��޹�ħ]</p>
                                        <p><font color="#666666">�μձ�ȹ�� ������ ����������ȣ�� �ſ� �߿���մϴ�.<br> ������ ���� �̿�� �¶��λ� ������ ������ ��ȣ ���� �� �ֵ��� �ּ��� �� �մϴ�.</font></p>
                                        <p><font color="#666666">�������� ��޹���� �����̳� �����ÿ��� Ȩ�������� ���� �Խ��ϰ�, ������ ������ �̿��ڵ��� ���� �� �� �ֵ��� �������ڸ� �ο��մϴ�. ����Ʈ �湮�� ���÷� Ȯ���Ͽ� �ֽñ� �ٶ��ϴ�.<br><br></font><span class="style1">�μձ�ȹ(���� '�μ�'�̶� ��)�� ���ѹα� ����� ����� '���Ȱ�� ��а� ���� �� ����� ��� ������ ���� ���� ���� �� ���Ȱ�� ���� ������ �����ϰ� �ֽ��ϴ�. </span></p>
                                        <p class="style1">�׷��� �Ϻ� �ε����� ������� �ҹ������� ���� �߻������� �𸣴� ���Ȱ ħ�ظ� ����, ȸ���� ���������� ���������� ��ȣ�ϸ�, ���ÿ� ȸ������ ���� ������ ���񽺸� �����ϱ� ���� �μ��� ������ ���� �������� ��ȣ ��å�� �����ϰ� �ֽ��ϴ�.</p>
                                        <p class="style1">�� �������� ��ȣ��å�� ������ ���ù��� �� ��ħ�� ����� �μ��� ��å ��ȭ�� ���� ����� �� �����Ƿ� �� ����Ʈ�� �湮 �Ͻ� �� ���÷� Ȯ���Ͽ� �ֽñ⸦ ��ε帳�ϴ�.</p>
                                        <p class="style1"><strong>1. ����������?</strong><br> �����ϴ� ���ο� ���� �����μ� ���� ������ ���ԵǾ� �ִ� ����, �ֹε�Ϲ�ȣ ���� ���׿� ���Ͽ� ���� ������ �ĺ��� �� �ִ� ����(���� ���������δ� Ư�� ������ �ĺ��� �� ������ �ٸ� ������ �����ϰ� �����Ͽ� �ĺ��� �� �ִ� ���� �����մϴ�)�� ���մϴ�.</p>
                                        <p class="style1"><strong>2. ���������� �������� �� �̿�</strong><br> �μ��� ȸ����ϰ� �α���(Log on) �ڷ� ���� ���� �Լ��� ȸ�������� ��ü���� ����ڷ�� �̿�Ǹ�, �̸� ������� ������ �Ǵ� ���»�� �����Ͽ� ȸ���鿡�� ������ ���񽺸� �����ϴ� �� Ȱ��˴ϴ�.<br> �μ��� Ȩ�������� ����Ǵ� ������ �⺻������ ȸ������ ���� ǳ���� ���񽺸� ����� �����ϱ� ���� ������, �� �� ȸ������ ���ȭ�� ���������� ȸ���鿡�� ���� �����ϰ� ������ ������ ��ġ�ϱ� ���� �ڷ�� �̿�˴ϴ�. ���� ȸ�� �������� ��ȣ�� ���� �´� ���񽺸� �����ϱ� ���Ͽ�, ȸ������ ���������� ���� ������� ���ɵ�, �ൿ���� ���� �м��Ͽ� ���� ���, ����̳� ��ü, ���� ��� �����ϴ� �� Ȱ���ϰ� �˴ϴ�.<br> - ȸ������ �� �̿� ID �߱�<br> - ����� ����<br> - ����ȸ���� ���� ���� ���㼭��<br> - �α�������� �м� (�̿����� ���ɺ�, ����, ������ ���м�)<br> - ȸ���� ���� �̿뿡 ���� ��踦 �����ϰ�, �̸� ���� ��å�� �ݿ� (���� ���� �� Ȯ��)<br> - ��ǰ ���<br> - ��� �� ��� ����<br> - ���ο� ����, �̺�Ʈ �����ȳ�</p>
                                        <p class="style1"><strong>3. ���������� �����׸�</strong><br> �μ��� ���� ȸ������ �����Ͻ� �� �ʼ����� ���������� ��� �ֽ��ϴ�. �� �� �����Ͻô� ���������δ� ����, �ּ�, e-mail �ּҰ� ������ ��ǰ�̳� ���ǰ ���� �߼۰� ������ ���� ���� ���� �������� ����, ��ȭ��ȣ, ���ɺо�, ������� ���� ���������� ������ �� �ֵ��� �ϰ� �ֽ��ϴ�. ����, ���Ŀ� ȸ���� ��ǰ���� ������ ���� �μ��� �ε����ϰ� ȸ���� �ּ�, ������ȣ, ��ȭ��ȣ ���� ���������� ��û�ϰ� �Ǹ�, �� ��쿡�� �μտ����� �ش� ������ �����̳� ������ ȸ������ ���� ���� �̿��� � �ٸ� �������ε� ȸ���� ���������� ������� �ʽ��ϴ�.</p>
                                        <p class="style1">[�ʼ�����]<br> - ����<br> - �޴��� ��ȣ<br> - ��� ID<br> - �ּ�(������ȣ)<br> - ��ȭ��ȣ</p>
                                        <p class="style1"><strong>4. ��Ű(cookie)�� �ǹ̿� �̿�</strong><br> ����ڿ��� ����ȭ�� ������ ���񽺸� �����ϱ� ���� ������� ���Ͽ� ���� ������ �����ϰ� "�μ�" ���ӽ� �̿�˴ϴ�. "��Ű"�� ����Ʈ���� ����� ���������� ������ �����ͷμ� ������ ��ǻ�� �ϵ� ��ũ�� ����˴ϴ�. ���ϰ� ������ ��� ����Ʈ �Ǵ� ����ȭ�� ����Ʈ�� �̿��ϱ� ���ؼ��� ���ϰ� ��Ű�� ����Ͽ��� �մϴ�. �μ��� ��Ű�� ���� �α� ������ ������� �Ͽ� ȸ���� ���ɻ�� �ൿ���, �α�������� ���� ���� �м��� �� �ֽ��ϴ�. �̷��� �м��� ���������� �μհ� �� ���¾�ü�� ȸ������ ���� ���� ���񽺸� �����ϱ� ���Ͽ� ���˴ϴ�. �̷��� ������ ������ ������ �Ǵ� ���»�� ������ �� �ֽ��ϴ�.<br> - ���� ���� ���� �̿�<br> - ���� ���� ���� ����<br> - ���Ἥ�� �̿� �� �̿�Ⱓ �ȳ�<br> - �Խ��� �� ���</p>
                                        <p class="style1">��Ÿ�� ��� ������Ż���� ���� ������ɿ� ���Ͽ� ��������� �䱸�ϴ� ��츦 ������ ��쿡�� ���νŻ� ������ ������ �³� ���� Ÿ�ο��� ����, �������� �ʽ��ϴ�.</p>
                                        <p class="style3">- ��Ű�� ��ġ/� �� �ź�</p>
                                        <ul>
                                            <li class="style1">�̿��ڴ� ��Ű ��ġ�� ���� ���ñ��� ������ �ֽ��ϴ�. ���� �̿��ڴ� ������������ �ɼ��� ���������ν� ��� ��Ű�� ����ϰų�, ��Ű�� ����� ������ Ȯ���� ��ġ�ų�, �ƴϸ� ��� ��Ű�� ������ �ź��� ���� �ֽ��ϴ�.</li>
                                            <li class="style1">�ٸ�, ��Ű�� ������ �ź��� ��쿡�� �α����� �ʿ��� �μ��� �Ϻ� ���񽺴� �̿뿡 ������� ���� �� �ֽ��ϴ�.</li>
                                            <li class="style1">��Ű ��ġ ��� ���θ� �����ϴ� ���(Internet Explorer�� ���)�� ������ �����ϴ�.
                                                <ul>
                                                    <li class="style1">�� [����] �޴����� [���ͳ� �ɼ�]�� �����մϴ�.</li>
                                                    <li class="style1">�� [�������� ��]�� Ŭ���մϴ�.</li>
                                                    <li class="style1">�� [����������� ����]�� �����Ͻø� �˴ϴ�.</li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <p class="style1"><strong>5. �������� ����, ����, ����</strong><br> �μ��� ȸ���� �Է��� ���������� 'ȸ����ø'�� 'ȸ����������'������ �������� ����, ����, ������ �� �ֵ��� �ϰ� ������, ���� ���� ������ �ݴ��ϴ� ȸ������ �������� ������ ���� ���Ǹ� öȸ�� �� �ְ� �ϰ� �ֽ��ϴ�.<br> ȸ��Ż��, �� ���̵�(ID)�� ������ ���Ͻø� �̸���(dsp1830@naver.com)�� ��û �Ǵ� ������ ������ ���� ó���Ͻø� �˴ϴ�.</p>
                                        <p class="style1"><strong>6. ���������� ���� �� ����</strong><br> �μ��� ������ ���� ���� �������� �Ż� ������ �ٸ� �����̳� ���, �Ⱓ�� �������� �ʴ� ���� ��Ģ���� �ϰ� �ֽ��ϴ�. �ٸ�, ȸ���� ����� ��쳪 ��ŷ� ���� ������ ���� �ʿ��� ���, �Ǵ� �̿� ����� ������ ȸ������ ������ ���縦 �ֱ� ���� ��쿡�� ������ ������ �� �ֽ��ϴ�. ������, �̷��� ���� �����̹��ÿ� ���� ����� ���並 ��ģ �Ŀ� �ϰ� �˴ϴ�.</p>
                                        <p class="style1"><strong>7. ���������� ���� �� ���</strong><br> �μ�ȸ�����μ� �μ��� �����ϴ� ���񽺸� �޴� ���� ȸ���� ���������� �μտ��� ��� �����ϸ� ���� ������ ���� �̿��ϰ� �˴ϴ�. �ٸ�, <br> - ȸ������ ������ ���, ȸ�������� Ż���ϰų� ȸ������ ������ ���, ID(���̵�) ������ �̷���� ��� <br> - ���������� ���������� �޼��� ���, ���ù����� ������ ���� �����ǹ��� ���� �� �μ��� �¶��� �Ǵ� �������λ� ������ ��� ���������� �������Ǿ� ����� �� ������ �ϰ� �ֽ��ϴ�.<br> (��, ��� �� ������ ������ ���Ͽ� ������ �ʿ伺�� �ִ� ��쿡�� ���ܷ� �մϴ�.)</p>
                                        <p class="style1"><strong>8. �������� ��ȣ</strong><br> �μ��� ȸ�� ������ ������ ����� �����ϰ� ��ų �� �ֵ��� �׻� ��� ����� ���ܰ� ����� ���ϰ� �ֽ��ϴ�. �׷��� ȸ���� ���̵�(ID) �� ��й�ȣ�� ������ �⺻������ ȸ�� �������� å���Ͽ� �ֽ��ϴ�. �μտ��� ���������� ������ �� �ִ� ����� ���� ȸ���� ���̵�(ID) �� ��й�ȣ�� ���� �α��� (Log in)�� ���� ����̸�, �μ��� e-mail�̳� ��ȭ, �� ��� ����� ���ؼ��� ȸ���� ��й�ȣ�� ���� ���� �����Ƿ�(ID/��й�ȣ �нǷ� ���� ȸ������ ��û�� ����), ȸ�� ������ ������ ���� ��й�ȣ�� ���� �ٲپ��ֽñ� �ٶ��ϴ�. �μ��� �̿��Ͻ� �Ŀ��� �ݵ�� �α׾ƿ�(Log out)�� �� �ֽð�, ��ǻ�͸� �����ϰų� ������ҿ��� ��ǻ�͸� ����ϴ� ��쿡�� �̿� �� �ݵ�� �� �������� â�� �ݾ��ִ� �� �������� ������ ���� ���� ������ ����� ��￩�ֽñ� �ٶ��ϴ�.</p>
                                        <p class="style1"><strong>9. �Ƶ��� ���� ��ȣ</strong><br> �� 14�� �̸� (13�� ����)�� �Ƶ��� �μ��� ȸ������ �����ϱ� ���ؼ��� ���� �θ���̳� ��ȣ�ڿ��� ����� ���� �� �����Ͽ��� �մϴ�.<br> �μ��� ������ɿ��� ���Ǿ� ���� ���� �� 14�� �̸� (13�� ����) �Ƶ��� ���� ���������� �Ǹ��ϰų� �ٸ� ������� �������� �ƴ��մϴ�.</p>
                                        <p class="style1"><strong>10. �������� �ҹ������ ����</strong><br> �μ��� ���ο� ���� ���Ը����� �߼��� ����� �ִ� ��3�ڿ��� ���������� �Ǹſ� ������ ���� �ʽ��ϴ�. �μ��� ���񽺸� �̿��ϴ� ��� ������ ������ �������� �ϴ� ��� Ȱ���̳� ��Ÿ �ҹ� �������� Ÿ���� ���� ������ ����� �� �����ϴ�. <br> ���� �̷� ���Ͽ� Ÿ���� ������ �ջ��Ű�ų� Ÿ�ο��� �������� �߻��� ��� �μ� �̿� ����� ������ ������ �����Ͽ� ȸ�� ������ ���� �Ǵ� �Ͻ� ������ �� �ֽ��ϴ�. ���� �ҹ������� ���� ��� å���� ������ ���ο��� ������ ȸ��� ��ü�� å���� ���� �ƴ��մϴ�. ��, ���� ���ɿ� ���� ���� ����� ��û �� ���� ������ �� �� �ֽ��ϴ�.<br></p>
                                        <ul>
                                            <li>������������å���� ���� : �ۿ���</li>
                                            <li>��ȭ��ȣ : 02-2671-1830</li>
                                            <li>�̸��� : dsp1830@naver.com</li>
                                        </ul>
                                        <ul>
                                            <li>���ϲ����� ȸ���� ���񽺸� �̿��Ͻø� �߻��ϴ� ��� ����������ȣ ���� �ο��� ������������å���� Ȥ�� ���μ��� �Ű��Ͻ� �� �ֽ��ϴ�.</li>
                                            <li>ȸ��� �̿��ڵ��� �Ű����׿� ���� �ż��ϰ� ����� �亯�� �帱 ���Դϴ�.</li>
                                            <li>��Ÿ ��������ħ�ؿ� ���� �Ű��� ����� �ʿ��Ͻ� ��쿡�� �Ʒ� ����� �����Ͻñ� �ٶ��ϴ�.
                                                <ol>
                                                    <li>���κ�����������ȸ (www.1336.or.kr/1336)</li>
                                                    <li>������ȣ��ũ��������ȸ (www.eprivacy.or.kr/02-580-0533~4)</li>
                                                    <li>�����û ���ͳݹ��˼��缾�� (http://icic.sppo.go.kr/02-3480-3600)</li>
                                                    <li>����û ���̹��׷��������� (www.ctrc.go.kr/02-392-0330)</li>
                                                </ol>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p>&nbsp;</p>
            </td>
            <td width="9">&nbsp;</td>
            <td width="120" valign="top">
                <!-- ������ ��� ���� -->
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/right.htm'; ?>
                <!-- ������ ��� �� -->
            </td>
        </tr>
    </table>
    <!-- �ϴܺκ� ���� -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/bottom.htm'; ?>
    <!-- �ϴܺκ� �� -->
</body>
</html>
