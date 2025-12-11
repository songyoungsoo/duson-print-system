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

 // PHP 5.2 호환성
 $PHP_AUTH_USER = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
 $PHP_AUTH_PW = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;

 if(!$PHP_AUTH_USER or !$PHP_AUTH_PW){
     auth();
}else{
  if($id !=$PHP_AUTH_USER or $pw != $PHP_AUTH_PW) auth();
  }

  function dbconn(){
      // PHP 5.2는 mysql_* 함수 사용
      $connect = mysql_connect("localhost","duson1830","du1830");
      mysql_select_db("duson1830", $connect);
      mysql_query("SET NAMES euckr", $connect);
      mysql_query("SET CHARACTER SET euckr", $connect);
      return $connect;
    }
?>