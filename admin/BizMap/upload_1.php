<?php
// $upload_dir ���ε���
$MAXFSIZE="2000"; // �̹����� �뷮
if(is_uploaded_file($photofile1)) {

	$full_filename = split("\.", $photofile1_name);
	$file_extention = $full_filename[sizeof($photofile1_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $photofile1_size) {
		$photofile1_kfsize = intval($photofile1_size/1024);
		$msg = "���ε��Ͻ� ������ ũ�Ⱑ $photofile1_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$photofile1_name")) {
		$photofile1_name = date("is")."_$photofile1_name";
	}
	if ($photofile1_size) {
	move_uploaded_file($photofile1, "$upload_dir/$photofile1_name");
	}
}

$photofile1NAME = $photofile1_name;
?>