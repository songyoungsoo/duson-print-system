<?php
session_start();
$session_id = session_id();

include "../lib/func.php";
$connect = dbconn();

$order_id = date("Ymdhis");
$regdate = time();

if ($img_name) {
    move_uploaded_file($img, "../shop/data/" . $img_name);
}

$query = "SELECT * FROM MlangOrder_PrintAuto WHERE name='$name' AND  date='$date'"; //��������
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_array($result);

$Folder = "../shop/data/" . $img_name;
$jd = date('Y/m/d H:i:s', $data['regdate']);
$total = 0;
$type = "��Ƽī";
if ($img_name) {
    $ImgFolder = "../shop/data/" . $img_name;
}
$Type_1 = $data['Type_1'];
$OrderStyle = "2";


$body2 = "<table  align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'>
            <tr bgcolor='#CCCCFF'> 
                <td bgcolor='#CCCCFF' width='50'>NO </td>
				<td bgcolor='#CCCCFF' width='300'>�ֹ��� </td>
				<td bgcolor='#CCCCFF' width='100'>�ݾ� </td>
				<td bgcolor='#CCCCFF' width='100'>�ΰ������� </td>
				<td bgcolor='#CCCCFF' width='50'>�ֹ� </td>
				</tr> ";
$query = "SELECT * FROM MlangOrder_PrintAuto WHERE name='$name' AND  date='$date'";
$result = mysqli_query($connect, $query);
while ($data = mysqli_fetch_array($result)) {


    $body2 .= "		  
				<tr align='center' bgcolor='#FFFFFF'>
				<td bgcolor='#FFFFFF'>" . $data['no'] . "  </td>
				<td bgcolor='#FFFFFF'>" . $data['Type_1'] . "</td>
				<td bgcolor='#FFFFFF'>" . $data['money_4'] . " </td>    
				<td bgcolor='#FFFFFF'>" . $data['money_5'] . " </td>
				<td bgcolor='#FFFFFF'>" . $data['date'] . " </td>
		   </tr>
		    ";
    $total += $data['money_4'];
}
$vat = $total * 1.1;
$body3 = "<tr bgcolor='#DDECDD'> 
    <td colspan =2 bgcolor='#DDECDD'> �հ�  </td>
	 
    <td bgcolor='#FFFFFF'><strong>��$total</strong></td>
	<td > �ΰ������� </td>
    <td bgcolor='#FFFFFF'><strong>(��$vat)</strong></td>
  </tr>
  ";

$body3 .= "</table> <br>
			�μձ�ȹ�μ� ��ǥ��ȭ:1688-2384, 02-2632-1830<br>

			���� �������� ��������36��9 ��ȣ����1��<br>

			�ּ�â �˻�â�� �μձ�ȹ�μ�� �˻�<br>

			www.dsp114.com<br>   

			������ ��ƼĿ ī�޷α� �������� ���� ���� ���� �μ�<br>

			�������� 999-1688-2384  ���漱�μձ�ȹ<br>

			�������� 110-342-543507 ���漱�μձ�ȹ<br>

			����  301-2632-1830-11  ���漱�μձ�ȹ<br>";
$body = "<li> $data[name]���� ��ƼĿ���ų����Դϴ�. ";
$body .= "<table align='center' width=600 border='0' cellspacing='1' cellpadding='2' bgcolor='#000000'> 
        <tr> 
          <td bgcolor='#CCCCFF'> �̸�/��ȣ 
          <td bgcolor='#FFFFFF'> $data[name] 
		</tr>
        <tr> 
          <td  bgcolor='#CCCCFF'> ����ó 
          <td bgcolor='#FFFFFF'> $data[phone], $data[Hendphone]
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> ������ �ּ� 
          <td bgcolor='#FFFFFF'> $data[zip1]$data[zip2]
		</tr>			
		<tr> 
          <td  bgcolor='#CCCCFF'> ���޻��� 
          <td bgcolor='#FFFFFF'> $data[cont]
		</tr>			
		<tr>
          <td  bgcolor='#CCCCFF'> ���
		  <td bgcolor='#FFFFFF'>  Ư�� �۾��� <br>�ټ� �ʾ����� �ֽ��ϴ�
		</tr>			

		</table> <br>
       <li>�ֹ� ��� 
          ";
$body .= $body2 . $body3;
// $header = "From:duson<duson@dsp114.com>\n"; 
// $header .= "Content-Type:text/html\n"; 
include_once('mailer.lib.php');
$content = $body;
$to = "$email";
$subject = "$name �� ��ǰ ���Ÿ� ����帳�ϴ�.";
echo $body;
//echo $body;
//echo $subject;
//echo $content;
// mailer("������ ��� �̸�", "������ ��� �����ּ�", "�޴� ��� �����ּ�", "����", "����", "1");
mailer("$fname", "$fmail", "$to", "$subject", "$content", 1); //$fname, $fmail, $to, $subject, $content, $type=0,
//mailer("test", "dsp1830@naver.com", "dsp1830@naver.com", "�׽�Ʈ����", "�߰���", 1);
// mail($email,"$name �� ��ǰ ���Ÿ� ����帳�ϴ�.",$body, $header); 
$query  = "DELETE FROM shop_temp WHERE session_id='$session_id' ";
mysqli_query($connect, $query);
mysqli_close($connect);
?>