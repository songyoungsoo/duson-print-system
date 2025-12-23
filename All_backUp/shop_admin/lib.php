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
  
 $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'] ?? '';
 $PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'] ?? '';

 if(!$PHP_AUTH_USER or !$PHP_AUTH_PW){
     auth();
}else{
  if($id !=$PHP_AUTH_USER or $pw != $PHP_AUTH_PW) auth();
  }
  
  function dbconn(){
      $connect = mysqli_connect("localhost","duson1830","du1830");
      mysqli_select_db($connect, "duson1830");
      mysqli_query($connect, "SET NAMES utf8");
      return $connect;
    }  
  
?>
<style>
td,input,li{font-size:9pt}
</style>