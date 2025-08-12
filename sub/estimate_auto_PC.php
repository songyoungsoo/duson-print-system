<?php
include("./dbConn.inc");


// ������ ���������� Ȯ���̸�
if($no && $HTTP_COOKIE_VARS["adminVar"]){
	$sql = "update orderDB set viewCheck='Y' where no=$no";
	$result = mysql_query($sql,$connection);
		if(!$result){
			echo("<script>
			alert('Ȯ�� üũ�� �ȵǾ����ϴ�.');
			history.go(-1);
			</script>");
			exit;
		}
		echo("<script>
			opener.location.reload();
			self.close();
			</script>");
}
else if($del != "" && $HTTP_COOKIE_VARS["adminVar"]){
	$sql = "delete from orderDB where no=$del";
	$result = mysql_query($sql,$connection);
		if(!$result){
			error("QUERY_ERROR");
			exit;
		}
		echo("<script>
			opener.location.reload();
			self.close();
			</script>");
}


else {
//================ �ֹ��� �ۼ� ����� �Է¹޾� ó���ϴ� ������ =================================

// �ֹ����� �Էµ� ���� �޾ƿ´�.
	$tel = $tel1."-".$tel2."-".$tel3;
	$fax = $fax1."-".$fax2."-".$fax3;
	$cel = $cel1."-".$cel2."-".$cel3;
	$cnt = $cntchk;	//����
	//�ڹٽ�ũ��Ʈ�󿡼� �ε����� ����ϰ� ���� ���.
	if ($sizechk){	$size = $sizechk;	}	
	if ($coatingchk){ $coating = $coatingchk; }
	if ($gubunchk){$gubun=$gubunchk; }


// �����ڷḦ ���� ���ε��Ѵ�.
	$upDir = $DOCUMENT_ROOT."/upOrder/$kind/";		//������ ���ε��� ���� : �������� ���ε��
	if (!is_dir($upDir)){
		mkdir($upDir,0707);
	}
	chmod("$upDir",0755);

// ���� ���ε带 ���� �Լ�
	function fileup($atch,$atchName,$atchSize,$upDir,$i){

		//���� Ȯ���� �˻�
		$file_ext = substr(strrchr($atchName,"."),1);	
		if($file_ext==php3 || $file_ext==php || $file_ext==html || $file_ext==htm || $file_ext==phtml || $file_ext==inc || $file_ext==js){
			echo("<script>
				alert('Ȯ���ڰ� '+'$file_ext'+'�� ���� ������ ÷���� �� �����ϴ�.');
				history.go(-1);
				</script>");
				exit;
		}
		
		//�ѱ����϶����� �̸��� �����Ѵ�.
		srand((double)microtime()*1000000);
		$atchName = date(Ymd).time().rand(1,1500000).".".$file_ext;


		if (file_exists("$upDir.$atchName")){
			echo("<script>
				alert('�̹� $atchName�� �����մϴ�. Ȯ���ϰ� �ٽ� �ø�����.');
				history.go(-1);
				</script>");
				exit;
		}
		if (!$atchSize){
			echo("<script>
				alert('������ ������ ���ų� ���� ũ�Ⱑ 0KB�Դϴ�.');
				history.go(-1);
				</script>");
				exit;
		}
		else if ($atchSize > 10240000){
			echo("<script>
				alert('���� ũ�Ⱑ 10MB�� �Ѿ����ϴ�. ���ϵ�� �÷��ּ���.');
				history.go(-1);
				</script>");
				exit;
		}

		if(is_uploaded_file($atch)) {
		  if(@move_uploaded_file($atch,"$upDir$atchName"))
			{
			//echo "���� ���� �Ϸ�";
			global $file;
			$file = "$atchName";	//DB�� �����ϱ� ���� ���Ϲ�ȣ���� ���� �̸��� ���
			return $file;
			}
		  else{
			  unlink($atchName);
			  echo("<script>
				alert('���� ������ �����߽��ϴ�.  �˼������� �ٽ� �÷��ּ���.');
				history.go(-1);
				</script> ");
				exit;
		  }
		}
	}
	
//-- ���� ���� ���� : ���ε��ִ� ��ŭ�� �ݺ��Ѵ�.
	for ($i=1; $i<=5; $i++){
		$fileN = "atch".$i;		//���ε� ����?
		$fileName = "atch".$i."_name";		//���ε� ���� �̸�
		$filesize = "atch".$i."_size";		//���ε� ���� ũ��
		if ($$fileN){
			fileup($$fileN,$$fileName,$$filesize,$upDir,$i);
			$$fileN = $file;
		}
		else{
			break;
		}
	}


//--- DB�� �ڷᰪ�� �Է��Ѵ�.
	$wdate = date("Y-m-d");
	$sql="insert into orderDB (`kind`,`paper`,`size`,`cnt`,`coating`,`price`,`num`,`reorder`,`printer`,`edit`,`design`,`total`,`name`,`company`,`tel`,`fax`,`cell`,`email`,`memo`,`file1`,`file2`,`file3`,`file4`,`file5`,`ordDate`,`gubun`) values('$kind','$paper','$size','$cnt','$coating','$price','$num','$reorder','$printer','$edit','$design','$total','$ordername','$company','$tel','$fax','$cel','$email','$memo','$atch1','$atch2','$atch3','$atch4','$atch5','$wdate','$gubun')";
//echo $sql; exit;
	$result = mysql_query($sql,$connection); 

	if($result){
		echo("
			<script language='javascript'>  
				alert('���������� �ԷµǾ����ϴ�.');
				location.href='estimate_auto.htm?pg=$kind';
			</script>
		");
		exit;
	}else {
		echo("
			<script language='javascript'>  
				alert('���������� �Էµ��� �ʾҽ��ϴ�. �ٽ� �Է��Ͽ��ּ���.');
				history.go(-1);
			</script>
		");
		exit;
	}

}
?>