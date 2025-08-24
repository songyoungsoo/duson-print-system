<?php
include("$DOCUMENT_ROOT/sub/dbConn.inc");
include("$DOCUMENT_ROOT/admin/func.php");

if (!$offset) {  $number = $total; }

// total record

	$sql = "select no from design";
	if($select){
		$sql = "select * from design";
			if($select=="����") $sql = $sql." where title like '%$search%'";
			if($select=="ȸ��") $sql = $sql." where company like '%$search%'";
	}

	$numresults=mysql_query($sql,$connection);
	$numrows=mysql_num_rows($numresults);
	
	//$limit=10;    // ���������� ���̴� ���ڵ� �� : ��Ŭ��� �ϱ��� ���� ���������� �޾ƿ�
	$num=10;		 //	������ ��ũ ���� ��

	if($offset=="" || $offset=="1"){	
		$offset=(int)1;
		$paging=0;
	}
	else {
		$paging=($offset-1)*$limit;
	}
?>

<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}
//-->
</script>
<script language="javascript">
<!--
function winpop1(kind,no){
	url = "/sub/check.htm?no="+no;
	window.open(url,'pop1','width=980,height=720,top=0,left=0,scrollbars=no,resizable=no');
	}

function chk(){
	f= document.form1;
	if (f.select.selectedIndex > 0 && f.search.value=="")
	{
		alert("�˻� ������ �Է��ϼ���.");
		f.search.focus();
		return false;
	}
}
//-->
</script>
</head>

<body>
<table width="692" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td valign="top"> 
      <!--���� ���� -->
      <table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td colspan="6" height="26" valign="top"><img src="../img/checkboard_top.jpg" width="692" height="26"></td>
        </tr>
        <!-- ����Ʈ -->
        <?php
					if (!$numrows){
						echo ("<tr><td colspan='6' valign='top' align='center'>������ �����ϴ�.</td></tr>");
					}
					else{
						if($select){
							$sql = "select * from design";
								if($select=="����") $sql = $sql." where title like '%$search%'";
								if($select=="�ֹ���") $sql = $sql." where ordername like '%$search%'";
								if($select=="ȸ��") $sql = $sql." where company like '%$search%'";
								$sql = $sql. " order by no desc limit $paging,$limit";
						}
						else{
							$sql = "select * from design order by no desc limit $paging,$limit";
						}
						$result = mysql_query($sql,$connection);
							while ($row=mysql_fetch_array($result))
							{
								$day=explode(" ",$row[wdate]);
								switch($row[kind]){
									case(1):	$kind="����"; break;
									case(2):	$kind="��ƼĿ"; break;
									case(3):	$kind="����"; break;
									case(4):	$kind="������"; break;
									case(5):	$kind="����"; break;
									case(6):	$kind="ī�ٷα�";	 break;									
								}
								if ($row[company]==""){
									$order=($row[ordername]);
								}
								else {
									$order=$row[company];
								}

								echo("
								  <tr bgcolor='$bgcolor'> 
									  <td align=center width=46 height=25>$row[no]</td>
									  <td align=center width=90>$kind</td>
									  <td width=294>&nbsp;<a href=javascript:winpop1('$row[kind]','$row[no]')>$row[title]</a></td>
									  <td align=center width=104>$order</td>
									  <td align=center width=82>$day[0]</td>
									  <td align=center width=76><a href=javascript:winpop1('$row[kind]','$row[no]')><img src='../img/bu_see.gif' width='54' height='20' border=0></a></td>
								  <tr> 
									<td height='1' colspan='6' bgcolor='#E7E7E7'></td>
								  </tr>");
							}	//while
					} //if
					?>
        <!-- ����Ʈ �� -->
      </table></td>
  </tr>
  <tr>
    <td>   <? if($numrows>$limit || $select){ ?>
		<table width="692" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="478" height="30"><font color="#999999">
            <? 	
			  paging($PHP_SELF,'design',$offset,$num,$limit,$numrows,$sdate,$edate,$kind,$ordername,$company,$mode,$flug);	?>
            </font></td>
          <td width="220"> <form name="form1" method="post" action="/sub/checkboard.htm" onSubmit="return chk()">
              <table width="220" border="0" align="right" cellpadding="0" cellspacing="0">
                <tr> 
                  <td ><select name="select">
                      <option selected>��ü</option>
                      <option value="����">����</option>
                      <option value="ȸ��">ȸ��</option>
	  				  <option value="�ֹ���">�ֹ���</option>
                    </select> <input name="search" type="text" size="12"></td>
                  <td width="42"><a href="#"><input type="image" src="../img/bu_search.gif" width="36" height="21" border="0"></a></td>
                </tr>
              </table>
            </form></td>
        </tr>
      </table><? }  ?>
      <!-- ���� �� -->
    </td>
  </tr>
</table>
</body>
</html>
