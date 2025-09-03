<?php
// $upload_dir ���ε���
$MAXFSIZE="2000"; // �̹����� �뷮
if(is_uploaded_file($photofile3)) {

	$full_filename = split("\.", $photofile3_name);
	$file_extention = $full_filename[sizeof($photofile3_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $photofile3_size) {
		$photofile3_kfsize = intval($photofile3_size/1024);
		$msg = "���ε��Ͻ� ������ ũ�Ⱑ $photofile3_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$photofile3_name")) {
		$photofile3_name = date("is")."_$photofile3_name";
	}
	if ($photofile3_size) {
	move_uploaded_file($photofile3, "$upload_dir/$photofile3_name");
	}
}

$photofile3NAME = $photofile3_name;
?>