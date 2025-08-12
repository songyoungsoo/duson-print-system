<?php
if(is_uploaded_file($photofile_1)) {

	$full_filename = split("\.", $photofile_1_name);
	$file_extention = $full_filename[sizeof($photofile_1_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: php / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $photofile_1_size) {
		$photofile_1_kfsize = intval($photofile_1_size/1024);
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: ���ε��Ͻ� ������ ũ�Ⱑ $photofile_1_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

//---------------------------------------------------------------------------------------------------------------//
$MlangHanGulChange = "$photofile_1_name";
if(eregi ("(([^/a-zA-Z]){1,})(\.jpg|\.jpeg|\.bmp|\.png|\.gif)", $MlangHanGulChange ,$MlangHanGulregs)){
$MlangHanGulCode_date=date("YmdHis");  $MlangHanGulCode_end=1014; $MlangHanGulCode_num=rand(0,$MlangHanGulCode_end);  
$MlangHanGulCodeOK="${MlangHanGulCode_date}${MlangHanGulCode_num}";
$photofile_1_name = eregi_replace ($MlangHanGulregs[1], "$MlangHanGulCodeOK",$MlangHanGulChange);
}else{
$photofile_1_name = $photofile_1_name;
}
//---------------------------------------------------------------------------------------------------------------//

	if (is_file("$upload_dir/$photofile_1_name")) {
		$photofile_1_name = date("is")."_$photofile_1_name";
	}
	if ($photofile_1_size) {
	move_uploaded_file($photofile_1, "$upload_dir/$photofile_1_name");
	}
}

$photofile_1Name = $photofile_1_name;
?>