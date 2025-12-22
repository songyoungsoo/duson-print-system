<?php 
   $id = "duson1830";
   $pw = "du1830";
   
   function auth(){
      header("WWW-authenticate:basic realm=\"관리자모드\"");
      header("HTTP/1.0 401 unauthorized");
      echo "
         <script>
            alert('관리자만 접근 가능합니다.');
            history.back(1);
        </script>
        ";
        exit;
  }
  
 // PHP 5.3+ 호환성: $_SERVER 사용
 $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'] ?? null;
 $PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'] ?? null;

 if(!$PHP_AUTH_USER or !$PHP_AUTH_PW){
     auth();
}else{
  if($id !=$PHP_AUTH_USER or $pw != $PHP_AUTH_PW) auth();
  }
  
  function dbconn(){
      $connect = mysqli_connect("localhost","dsp1830","ds701018", "dsp1830");
      if (!$connect) {
          $error = mysqli_connect_error();
          $errno = mysqli_connect_errno();
          die("Connection failed (Code: $errno): $error<br>Host: localhost<br>User: dsp1830<br>DB: dsp1830");
      }
      mysqli_query($connect, "SET NAMES utf8");
      return $connect;
    }
?>