<?php
if(is_uploaded_file($photofile_2)) {

	$full_filename = split("\.", $photofile_2_name);
	$file_extention = $full_filename[sizeof($photofile_2_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: php / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $photofile_2_size) {
		$photofile_2_kfsize = intval($photofile_2_size/1024);
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: ���ε��Ͻ� ������ ũ�Ⱑ $photofile_2_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

//---------------------------------------------------------------------------------------------------------------//
$MlangHanGulChange = "$photofile_2_name";
if(eregi ("(([^/a-zA-Z]){1,})(\.jpg|\.jpeg|\.bmp|\.png|\.gif)", $MlangHanGulChange ,$MlangHanGulregs)){
$MlangHanGulCode_date=date("YmdHis");  $MlangHanGulCode_end=1014; $MlangHanGulCode_num=rand(0,$MlangHanGulCode_end);  
$MlangHanGulCodeOK="${MlangHanGulCode_date}${MlangHanGulCode_num}";
$photofile_2_name = eregi_replace ($MlangHanGulregs[1], "$MlangHanGulCodeOK",$MlangHanGulChange);
}else{
$photofile_2_name = $photofile_2_name;
}
//---------------------------------------------------------------------------------------------------------------//

	if (is_file("$upload_dir/$photofile_2_name")) {
		$photofile_2_name = date("is")."_$photofile_2_name";
	}
	if ($photofile_2_size) {
	move_uploaded_file($photofile_2, "$upload_dir/$photofile_2_name");
	}
}

$photofile_2Name = $photofile_2_name;
?>