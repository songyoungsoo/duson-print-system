<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="euc-kr">
    <meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
    <title>�� �μձ�ȹ - ��ȹ���� �μ���� ���������� �ذ��� �帳�ϴ�.</title>
    <style>
        table {
            font-size: 12px;
        }
        a {
            color: #333;
            text-decoration: none;
        }
        a:hover,
        a:visited {
            color: #666;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const imagesToPreload = [
                '../img/main_m1a.jpg', '../img/main_m2a.jpg', '../img/main_m3a.jpg', '../img/main_m5a.jpg',
                '../img/main_m6a.jpg', '../img/main_m7a.jpg', '../img/main_m8a.jpg', '../img/main_m10a.jpg',
                '../img/main_m11a.jpg'
            ];
            imagesToPreload.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        });

        function swapImage(imageName, newSrc) {
            const image = document.querySelector(`img[name="${imageName}"]`);
            if (image) {
                image.src = newSrc;
            }
        }
    </script>
</head>
<body style="background: url('/img/bg.gif') no-repeat; margin: 0;" onload="MM_preloadImages('../img/main_m1a.jpg','../img/main_m2a.jpg','../img/main_m3a.jpg','../img/main_m5a.jpg','../img/main_m6a.jpg','../img/main_m7a.jpg','../img/main_m10a.jpg','../img/main_m11a.jpg','../img/main_m8a.jpg')">
    <table width="990" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr style="background: url('/img/bg.gif') no-repeat;">
            <td width="990" valign="top">
                <!-- ���� �̹��� �ΰ� ���� -->
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
            <td width="9"><img src="../img/space.gif" width="9" height="9" alt=""></td>
            <td valign="top">
                <!-- ���� ���� ���� -->
                <table width="692" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <a href="seosig.htm" onmouseout="swapImage('Image19', '../img/main_m1.jpg')" onmouseover="swapImage('Image19', '../img/main_m1a.jpg')"><img src="../img/main_m1.jpg" name="Image19" width="77" height="32" border="0"></a>
                            <a href="catalog.htm" onmouseout="swapImage('Image20', '../img/main_m2.jpg')" onmouseover="swapImage('Image20', '../img/main_m2a.jpg')"><img src="../img/main_m2.jpg" name="Image20" width="77" height="32" border="0"></a>
                            <a href="brochure.htm" onmouseout="swapImage('Image21', '../img/main_m3.jpg')" onmouseover="swapImage('Image21', '../img/main_m3a.jpg')"><img src="../img/main_m3.jpg" name="Image21" width="77" height="32" border="0"></a>
                            <a href="leaflet.htm" onmouseout="swapImage('Image211', '../img/main_m10.jpg')" onmouseover="swapImage('Image211', '../img/main_m10a.jpg')"><img src="../img/main_m10.jpg" name="Image211" width="77" height="32" border="0" id="Image211"></a>
                            <a href="poster.htm" onmouseout="swapImage('Image231', '../img/main_m11.jpg')" onmouseover="swapImage('Image231', '../img/main_m11a.jpg')"><img src="../img/main_m11.jpg" name="Image231" width="76" height="32" border="0" id="Image231"></a>
                            <a href="namecard.htm" onmouseout="swapImage('Image23', '../img/main_m5.jpg')" onmouseover="swapImage('Image23', '../img/main_m5a.jpg')"><img src="../img/main_m5.jpg" name="Image23" width="77" height="32" border="0"></a>
                            <a href="envelope.htm" onmouseout="swapImage('Image24', '../img/main_m6.jpg')" onmouseover="swapImage('Image24', '../img/main_m6a.jpg')"><img src="../img/main_m6.jpg" name="Image24" width="77" height="32" border="0"></a>
                            <a href="sticker.htm" onmouseout="swapImage('Image25', '../img/main_m7.jpg')" onmouseover="swapImage('Image25', '../img/main_m7a.jpg')"><img src="../img/main_m7.jpg" name="Image25" width="77" height="32" border="0"></a>
                            <a href="bookdesign.htm" onmouseout="swapImage('Image26', '../img/main_m8.jpg')" onmouseover="swapImage('Image26', '../img/main_m8a.jpg')"><img src="../img/main_m8.jpg" name="Image26" width="77" height="32" border="0"></a>
                        </td>
                    </tr>
                </table>
                <table width="692" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top">&nbsp;</td>
                    </tr>
                    <tr>
                        <td height="323" valign="top"><img src="../img/t_expense.gif" width="692" height="31" alt="">
                            <br><br>
                            <table width="614" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="20" valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td width="594"><strong><span style="color: #FF0000;">�ɹ�/�ΰ�</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="141" height="24" bgcolor="#F9F5E3"><div align="center">����Ͻ�</div></td>
                                                <td width="304" bgcolor="#FFFFFF"><div align="center">200,000��~500,000��</div></td>
                                                <td width="141" rowspan="2" bgcolor="#FFFFFF"><div align="center">3,4���þ� ����</div></td>
                                            </tr>
                                            <tr>
                                                <td height="24" bgcolor="#F9F5E3"><div align="center">�����</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">1,000,000��</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">ī�ٷα�</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="142" height="24" bgcolor="#EBF3F0"><div align="center">�����κ��</div></td>
                                                <td width="162" bgcolor="#FFFFFF"><div align="center">50,000��~ (��������)</div></td>
                                                <td width="142" bgcolor="#EBF3F0"><div align="center">���(6��)</div></td>
                                                <td width="139" bgcolor="#FFFFFF"><div align="center">240,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">������</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td height="16" valign="top">&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td height="24" bgcolor="#FAEDFA"><div align="center">A4/16�� �ܸ������</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">30,000��~</div></td>
                                                <td bgcolor="#FAEDFA"><div align="center">A4/16�� ��������</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">60,000��~</div></td>
                                            </tr>
                                            <tr>
                                                <td height="24" bgcolor="#FAEDFA"><div align="center">A4/16�� 2�ܵ�����</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">40,000��/P��~</div></td>
                                                <td bgcolor="#FAEDFA"><div align="center">A4/16�� 3�ܵ�����</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">50,000��/P��~</div></td>
                                            </tr>
                                            <tr>
                                                <td width="142" height="24" bgcolor="#FAEDFA"><div align="center">A3/8�� �ܸ������</div></td>
                                                <td width="163" bgcolor="#FFFFFF"><div align="center">60,000��~</div></td>
                                                <td width="140" bgcolor="#FAEDFA"><div align="center">A3/8�� ��������</div></td>
                                                <td width="140" bgcolor="#FFFFFF"><div align="center">100,000��~</div></td>
                                            </tr>
                                            <tr>
                                                <td height="24" bgcolor="#FAEDFA"><div align="center">A2/4�� �ܸ������</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">120,000��~</div></td>
                                                <td bgcolor="#FAEDFA"><div align="center">A2/4�� ��������</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">200,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td><span style="color: #666666;">�� �Ϲ��۾� ���� ��� �߰� ����� �߻��� �� �ֽ��ϴ�. �þ� �߰� /������ ����/�����۾�/���伥�̹����۾�</span></td>
                                </tr>
                                <tr>
                                    <td><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">������</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="143" rowspan="2" bgcolor="#F3E7D6"><div align="center">�����κ��</div></td>
                                                <td height="24" bgcolor="#FFFFFF"><div align="center">A2 150,000��~ </div></td>
                                            </tr>
                                            <tr>
                                                <td height="24" bgcolor="#FFFFFF"><div align="center">4�� 100,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">����</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>
                                        <table width="583" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="142" height="24" bgcolor="#EEEEFF"><div align="center">�ܸ������</div></td>
                                                <td width="165" bgcolor="#FFFFFF"><div align="center">8,000��~</div></td>
                                                <td width="140" bgcolor="#EEEEFF"><div align="center">��������</div></td>
                                                <td width="131" bgcolor="#FFFFFF"><div align="center">10,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td><span style="color: #666666;">�� ������ �۾� ���� ��� �߰� ����� �߻��� �� �ֽ��ϴ�. �þ� �߰� /������ ����/�Ϲ����� �ð��� ��</span></td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">����</span></strong></td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top">&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="143" height="24" bgcolor="#F2EAED"><div align="center">�����κ��</div></td>
                                                <td bgcolor="#FFFFFF"><div align="center">�ܻ�1�� 5,000��~, Į����� 50,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20" valign="top">&nbsp;</td>
                                    <td><span style="color: #666666;">�� ������ �۾� ���� ��� �߰� ����� �߻��� �� �ֽ��ϴ�. �þ� �߰� /������ ����/�Ϲ����� �ð��� ��</span></td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">��Ƽī</span></strong></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="142" height="24" bgcolor="#F1F1E4"><div align="center">�����κ��</div></td>
                                                <td width="445" bgcolor="#FFFFFF"><div align="center">10,000��~</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20">&nbsp;</td>
                                    <td><span style="color: #666666;">�� �Ϲ��۾� ���� ��� �߰� ����� �߻��� �� �ֽ��ϴ�. �þ� �߰� /������ ����/�����۾�/���伥�̹����۾�</span></td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">�ϵ�����</span></strong></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="143" height="24" bgcolor="#E8F0EE"><div align="center">�����κ��</div></td>
                                                <td width="207" bgcolor="#FFFFFF"><div align="center">100,000�� ~ 200,000��</div></td>
                                                <td width="236" bgcolor="#FFFFFF"><div align="center">ǥ1, ǥ4</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td valign="top"><img src="../img/icon_cross_green.gif" width="10" height="10" alt=""></td>
                                    <td><strong><span style="color: #FF0000;">Į��ڽ�������</span></strong></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>
                                        <table width="590" border="0" cellpadding="0" cellspacing="1" bgcolor="#E8E8E8">
                                            <tr>
                                                <td width="144" height="24" bgcolor="#E6E6F2"><div align="center">�����κ��</div></td>
                                                <td width="206" bgcolor="#FFFFFF"><div align="center">150,000�� ~ 300,000��</div></td>
                                                <td width="236" bgcolor="#FFFFFF"><div align="center">����, ����, ���</div></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td height="20">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </table>
                            <table width="626" border="0" align="center" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><strong><span style="color: #FF0000;">* �ɺ�/�ΰ� �۾��ܿ��� ���������� �������� �ʽ��ϴ�.</span></strong></td>
                                    </tr>
                                    <tr>
                                        <td width="1%"><img height="3" src="" width="3" alt=""></td>
                                        <td width="99%"><span style="color: #333;">* ���뿡 ���� �۾��ð��� ����� �� �ֽ��ϴ�.</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><span style="color: #333;">* 100% �Ա� Ȯ�� �� �۾��� ����˴ϴ�.</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><span style="color: #333;">* ī�ٷα�, ��ν���� ����, �ϼ����� ���� �ٸ��� �����Ƿ� ���� ���� ���� �ٶ��ϴ�.</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><span style="color: #333;">* ���� �������� �ֹ� �� 1�Ⱓ ������ ��Ģ���� �ϸ�, ������ ���������� �ֹ��� �����κ���� �����Դϴ�.</span></td>
                                    </tr>
                                    <tr>
                                        <td height="15"></td>
                                        <td height="15"><span style="color: #333;">* ���ֹ��� ������ �����κ���� �����̰� ��������� <span style="color: #FF0000;">10,000��</span>���� ���뿡 ���� �����Ͽ� �����մϴ�</span></td>
                                    </tr>
                                    <tr>
                                        <td width="1%" height="15"></td>
                                        <td width="99%" height="15"><span style="color: #333;">* ���� Ư���� <span style="color: #FF0000;">������ �۾������ ���Ա� Ȯ�� ��</span> ������ �۾��� ���ϴ�.</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
                <p> 
                    <!-- ���� ���� �� -->
                </p>
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
