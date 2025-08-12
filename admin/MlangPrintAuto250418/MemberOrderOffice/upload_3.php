<?php
if(is_uploaded_file($photofile_3)) {

	$full_filename = split("\.", $photofile_3_name);
	$file_extention = $full_filename[sizeof($photofile_3_name)];

	if (eregi($file_extention, "html|php3|phtml|inc|asp")) {
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: php / asp ���������� ���� ���ε� �Ͻ� �� �����ϴ�.\\n\\n������ Ȯ���ڸ� �����Ͽ� �÷� �ּ���.\\n";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

 	if( $MAXFSIZE * 1024 < $photofile_3_size) {
		$photofile_3_kfsize = intval($photofile_3_size/1024);
		$msg = "\\n�����ڷḦ ���ε��߿� ������ �߻��Ͽ����ϴ�.\\n\\nȸ�������������� �����ڷḦ ���Է��Ͻø� �˴ϴ�.\\n\\n��������: ���ε��Ͻ� ������ ũ�Ⱑ $photofile_3_kfsize KB�Դϴ�. �����ڰ� ������ �뷮�� $MAXFSIZE KB�Դϴ�.";
		echo ("<script language=javascript>
                     window.alert('$msg');
                  </script>");
	}

//---------------------------------------------------------------------------------------------------------------//
$MlangHanGulChange = "$photofile_3_name";
if(eregi ("(([^/a-zA-Z]){1,})(\.jpg|\.jpeg|\.bmp|\.png|\.gif)", $MlangHanGulChange ,$MlangHanGulregs)){
$MlangHanGulCode_date=date("YmdHis");  $MlangHanGulCode_end=1014; $MlangHanGulCode_num=rand(0,$MlangHanGulCode_end);  
$MlangHanGulCodeOK="${MlangHanGulCode_date}${MlangHanGulCode_num}";
$photofile_3_name = eregi_replace ($MlangHanGulregs[1], "$MlangHanGulCodeOK",$MlangHanGulChange);
}else{
$photofile_3_name = $photofile_3_name;
}
//---------------------------------------------------------------------------------------------------------------//

	if (is_file("$upload_dir/$photofile_3_name")) {
		$photofile_3_name = date("is")."_$photofile_3_name";
	}
	if ($photofile_3_size) {
	move_uploaded_file($photofile_3, "$upload_dir/$photofile_3_name");
	}
}

$photofile_3Name = $photofile_3_name;
?>