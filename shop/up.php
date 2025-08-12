<?php
if(isset($_POST['submit'])){
	// 파일 업로드를 처리하는 코드
	$target_dir = "data/"; // 파일이 업로드될 디렉토리
	$uploaded_files = count($_FILES['img']['name']); // 업로드할 파일 수
	for($i=0; $i < $uploaded_files; $i++){
		$target_file = $target_dir . basename($_FILES['img']['name'][$i]); // 업로드될 파일 경로
		$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION)); // 파일 확장자 추출
		if(move_uploaded_file($_FILES['img']['tmp_name'][$i], $target_file)){
			// 파일 업로드 성공
			echo "The file ". basename( $_FILES["img"]["name"][$i]). " has been uploaded.<br>";
		}else{
			// 파일 업로드 실패
			echo "Sorry, there was an error uploading your file.<br>";
		}
	}
}
?>
