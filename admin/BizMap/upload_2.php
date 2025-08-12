<?php
// $upload_dir ���ε���
$MAXFSIZE="2000"; // �̹����� �뷮
if(is_uploaded_file($photofile2)) {

	$full_filename = split("\.", $photofile2_name);
	$file_extention = $full_filename[sizeof($photofile2_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\nphp / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

 	if( $MAXFSIZE * 1024 < $photofile2_size) {
		$photofile2_kfsize = intval($photofile2_size/1024);
		$msg = "���ε��Ͻ� ������ ũ�Ⱑ $photofile2_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                        history.go(-1);
                  </script>");
                exit;
	}

	if (is_file("$upload_dir/$photofile2_name")) {
		$photofile2_name = date("is")."_$photofile2_name";
	}
	if ($photofile2_size) {
	move_uploaded_file($photofile2, "$upload_dir/$photofile2_name");
	}
}

$photofile2NAME = $photofile2_name;
?>