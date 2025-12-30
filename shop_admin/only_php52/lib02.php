<?
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
  
 if(!$PHP_AUTH_USER or !$PHP_AUTH_PW){
     auth();
}else{
  if($id !=$PHP_AUTH_USER or $pw != $PHP_AUTH_PW) auth();
  }
  
  function dbconn(){
      $connect = mysql_connect("localhost","duson1830","du1830");
      mysql_select_db("duson1830",$connect);
      mysql_query("SET NAMES euckr");
      return $connect;
    }  
  
?>
<style>
td,input,li{font-size:9pt}
</style>
