<?php
$MAXFSIZE="500"; // �̹����� �뷮
if(is_uploaded_file($PhotoFileBig)) {

	$full_filename = split("\.", $PhotoFileBig_name);
	$file_extention = $full_filename[sizeof($PhotoFileBig_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: php / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $PhotoFileBig_size) {
		$PhotoFileBig_kfsize = intval($PhotoFileBig_size/1024);
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: ���ε��Ͻ� ������ ũ�Ⱑ $PhotoFileBig_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

	if (is_file("$upload_dir/$PhotoFileBig_name")) {
		$PhotoFileBig_name = date("is")."_$PhotoFileBig_name";
	}
	if ($PhotoFileBig_size) {
	move_uploaded_file($PhotoFileBig, "$upload_dir/$PhotoFileBig_name");
	}
}

$PhotoFileBigName = $PhotoFileBig_name;
?>