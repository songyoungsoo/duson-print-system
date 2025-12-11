<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<title>�ֹ� ���</title>
<style>
td,input,li{font-size:9pt}
</style>
</head>
<body>
<?
  include "lib_mysql.php";
  $connect = dbconn();
  $DbDir="..";
  $GGTABLE="mlangprintauto_transactionCate";
  $l[1] = "�ֹ�����";
  $l[2] = "�Ա�Ȯ��";
  $l[3] = "�۾���";
  $l[4] = "�����";
  $l[0] = "�ֹ����";

if(!$start) $start = 1;

  // �˻� �Ķ���� �ޱ� (PHP 5.2)
  $search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
  $search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
  $search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
  $search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
  $search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
  $search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

  // WHERE ���� ����
  $where_conditions = array();
  $where_conditions[] = "(zip1 like '%��%' ) or (zip2 like '%-%')";

  if($search_name != '') {
    $search_name = mysql_real_escape_string($search_name, $connect);
    $where_conditions[] = "name like '%$search_name%'";
  }

  if($search_company != '') {
    $search_company = mysql_real_escape_string($search_company, $connect);
    $where_conditions[] = "company like '%$search_company%'";
  }

  if($search_date_start != '' && $search_date_end != '') {
    $search_date_start = mysql_real_escape_string($search_date_start, $connect);
    $search_date_end = mysql_real_escape_string($search_date_end, $connect);
    $where_conditions[] = "date >= '$search_date_start' and date <= '$search_date_end'";
  } else if($search_date_start != '') {
    $search_date_start = mysql_real_escape_string($search_date_start, $connect);
    $where_conditions[] = "date >= '$search_date_start'";
  } else if($search_date_end != '') {
    $search_date_end = mysql_real_escape_string($search_date_end, $connect);
    $where_conditions[] = "date <= '$search_date_end'";
  }

  // �ֹ���ȣ ���� �˻� �߰�
  if($search_no_start != '' && $search_no_end != '') {
    $search_no_start = intval($search_no_start);
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no >= $search_no_start and no <= $search_no_end";
  } else if($search_no_start != '') {
    $search_no_start = intval($search_no_start);
    $where_conditions[] = "no >= $search_no_start";
  } else if($search_no_end != '') {
    $search_no_end = intval($search_no_end);
    $where_conditions[] = "no <= $search_no_end";
  }

  $where_sql = implode(' and ', $where_conditions);

  // ��ü ������ ���ϱ�
  $query = "select count(*) from MlangOrder_PrintAuto where $where_sql";
  $result = mysql_query($query, $connect);
  if (!$result) {
      die("Query Error: " . mysql_error($connect) . "<br>Query: " . $query);
  }
  $data = mysql_fetch_array($result);
  $total = $data[0];

  echo "<li> �� �Խù��� : $total  ";

   // ��ȭ�鿡 ǥ�õ� ��������
  $pagenum = 20;

  // ����������
  $pages = round($total / $pagenum);

  // ���ۺ���
  $s = $pagenum * ($start-1);

  // �˻� �Ķ���͸� URL�� �߰��ϱ� ���� ����
  $search_params = '';
  if($search_name != '') $search_params .= "&search_name=" . urlencode($search_name);
  if($search_company != '') $search_params .= "&search_company=" . urlencode($search_company);
  if($search_date_start != '') $search_params .= "&search_date_start=" . urlencode($search_date_start);
  if($search_date_end != '') $search_params .= "&search_date_end=" . urlencode($search_date_end);
  if($search_no_start != '') $search_params .= "&search_no_start=" . urlencode($search_no_start);
  if($search_no_end != '') $search_params .= "&search_no_end=" . urlencode($search_no_end);

  $query = "select * from MlangOrder_PrintAuto where $where_sql order by no desc";
  $query .= " limit $s, $pagenum ";
  $result = mysql_query($query, $connect);
  if (!$result) {
      die("<br>Query Error: " . mysql_error($connect) . "<br>Query: " . $query);
  }
  ?>

<!-- �˻� �� �߰� -->
<form method="get" action="<?php echo $_SERVER['PHP_SELF']?>" id="searchForm">
<table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:10px;">
  <tr>
    <td bgcolor="#CCCCCC"><b>�˻�</b></td>
    <td>
      �̸�: <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="10">
      ��ȣ: <input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="10"><br>
      ��¥: <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="10" placeholder="YYYY-MM-DD">
      ~
      <input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="10" placeholder="YYYY-MM-DD"><br>
      �ֹ���ȣ: <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="10">
      ~
      <input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="10"><br>
      <input type="submit" value="�˻�">
      <input type="button" value="�ʱ�ȭ" onclick="location.href='<?php echo $_SERVER['PHP_SELF']?>'">
      <input type="button" value="���� ���� �ٿ�ε�" onclick="exportSelectedToExcel()" style="background-color:#28a745; color:white; font-weight:bold;">
      <input type="button" value="��ü ���� �ٿ�ε�" onclick="exportAllToExcel()" style="background-color:#007bff; color:white; font-weight:bold;">
    </td>
  </tr>
</table>
</form>

<script>
function toggleAll(source) {
  var checkboxes = document.getElementsByName('selected_no[]');
  for(var i=0; i<checkboxes.length; i++) {
    checkboxes[i].checked = source.checked;
  }
}

function exportSelectedToExcel() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];
  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      selected.push(checkboxes[i].value);
    }
  }

  if(selected.length === 0) {
    alert('�ٿ�ε��� �׸��� �������ּ���.');
    return;
  }

  var form = document.createElement('form');
  form.method = 'POST';
  form.action = 'export_excel52.php';
  form.target = '_blank';

  var input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'selected_nos';
  input.value = selected.join(',');
  form.appendChild(input);

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

function exportAllToExcel() {
  var form = document.getElementById('searchForm');
  var originalAction = form.action;
  var originalMethod = form.method;
  form.action = 'export_excel52.php';
  form.method = 'get';
  form.target = '_blank';
  form.submit();
  form.action = originalAction;
  form.method = originalMethod;
  form.target = '';
}
</script>

<form id="listForm">
<table width=100% border=1>
  <tr bgcolor="#99CCFF">
    <td><input type="checkbox" onclick="toggleAll(this)"></td>
    <td> �ֹ���ȣ
    <td> ��¥
    <td> �����θ�
    <td> ������ȣ
    <td> �ּ�
    <td> ��ȭ
    <td> �ڵ���
    <td> �ڽ�����
    <td> �ù��
    <td> ���ӱ���
    <td> ǰ���
    <td> ��Ÿ
    <td> ��۸޼���

<?
  $total = 0;
  while($data = mysql_fetch_array($result)){
?>
<? if(preg_match("/16��/",$data['Type_1'])){ // 16���� 2�ڽ��ڽ����� �ڽ��簡��
		$r=2;
		$w=3000;} else
if(preg_match("/a4/",$data['Type_1'])){
	    $r=1;
		$w=4000;}else
if(preg_match("/a5/",$data['Type_1'])){
	    $r=1;
		$w=4000;} else
if(preg_match("/NameCard/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/MerchandiseBond/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/sticker/",$data['Type'])){
	    $r=1;
		$w=2500;}
if(preg_match("/��Ƽī/",$data['Type'])){
	    $r=1;
		$w=2500;} else
if(preg_match("/envelop/",$data['Type'])){
	    $r=1;
		$w=3000;
}

		?>
  <tr>
    <td><input type="checkbox" name="selected_no[]" value="<?php echo $data['no']?>"></td>
    <td><?php echo $data['no']?></td>
    <td><?php echo $data['date']?></td>
    <td><?php echo $data['name']?></td>
    <td><?php echo $data['zip']?></td>
    <td><?php echo $data['zip1']?> <?php echo $data['zip2']?></td>
    <td><?php echo $data['phone']?></td>
    <td width="120"><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo $data['Hendphone']?></a></td>
    <td align='center'><?php echo  $r; ?></td>
    <td><?php echo  $w; ?></td>
    <td>����</td>
    <td><?php echo $data['Type_1']?></td>
    <td>&nbsp;</td>
    <td><?php echo $data['Type']?></td>
  </tr>
  <?php

 } ?>
  </table>
</form>

<hr>


<?
    $a = $start - 5;
    $b = $start + 5;

    if($a<1) $a = 1;
    if($b>$pages) $b = $pages;

    $prev = $start - 10;
    $next = $start + 10;

    if($prev<=1) $prev = 1;
    if($next>=$pages) $next = $pages;
?>

<? if($prev!=1){ ?>
<a href=<?php echo $PHP_SELF?>?start=1<?php echo $search_params?>>��ó��</a>
<? } ?>

<a href=<?php echo $PHP_SELF?>?start=<?php echo $prev?><?php echo $search_params?>>[����]</a>
<?

   for($i=$a; $i<=$b; $i++){

     if($start==$i) {?>
        <b><?php echo $i?></b>
     <? }else{  ?>
       <a href=<?php echo $PHP_SELF?>?start=<?php echo $i?><?php echo $search_params?>>[<?php echo $i?>]</a>
   <? } ?>

<? } ?>

<? if($next!=$pages){ ?>
<a href=<?php echo $PHP_SELF?>?start=<?php echo $next?><?php echo $search_params?>>[����]</a>
<? } ?>

<a href=<?php echo $PHP_SELF?>?start=<?php echo $pages?><?php echo $search_params?>>�ǳ�</a>
?>
</body>
</html>
