<?php
$MAXFSIZE="200"; // �̹����� �뷮
if(is_uploaded_file($PhotoFileSo)) {

	$full_filename = split("\.", $PhotoFileSo_name);
	$file_extention = $full_filename[sizeof($PhotoFileSo_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: php / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $PhotoFileSo_size) {
		$PhotoFileSo_kfsize = intval($PhotoFileSo_size/1024);
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: ���ε��Ͻ� ������ ũ�Ⱑ $PhotoFileSo_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

	if (is_file("$upload_dir/$PhotoFileSo_name")) {
		$PhotoFileSo_name = date("is")."_$PhotoFileSo_name";
	}
	if ($PhotoFileSo_size) {
	move_uploaded_file($PhotoFileSo, "$upload_dir/$PhotoFileSo_name");
	}
}

$PhotoFileSoName = $PhotoFileSo_name;
?>