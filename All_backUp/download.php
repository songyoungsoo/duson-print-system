<?php

ob_start();
 // 파일이 있는 디렉토리
 $downfiledir = "../../shop/data/"; 
  
  // 값 검증
  $downfile  = $_GET['downfile']; 

 if (!eregi($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])) Error("외부에서는 다운로드 받으실수 없습니다.");
  
  // 파일 존재 유/무 체크
 if ( file_exists($downfiledir.$downfile ) ) {
  $save_file = urlencode($save_file); // 파일명이나 경로에 한글이나 공백이 포함될 경우를 고려
   Header("Content-Type: doesn/matter");
   header("Content-Type: application/octet-stream");
   Header("Content-Disposition: attachment;; filename=$downfile ");
   header("Content-Transfer-Encoding: binary"); 
   Header("Content-Length: ".(string)(filesize($downfiledir.$downfile ))); 
   Header("Cache-Control: cache, must-revalidate"); 
   header("Pragma: no-cache"); 
   header("Expires: 0");
   $fp = fopen($downfiledir.$downfile , "rb"); //rb 읽기전용 바이러니 타입
  while ( !feof($fp) ) { 
   echo fread($fp, 100*1024); //echo는 전송을 뜻함.       
  }
   fclose ($fp);
   flush(); //출력 버퍼비우기 함수.. 
 }
 else {
 ?><script>alert("존재하지 않는 파일입니다.");history.back()</script><?
 }


?>
[출처] <PHP> 파일 다운로드 구현|작성자 하늘처럼
