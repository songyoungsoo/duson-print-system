<?
////////////////// 관리자 로그인 ////////////////////
function authenticate()
{
  HEADER("WWW-authenticate: basic realm=\"MlangWeb관리프로그램-관리자 인증!.. ♥ WEBSIL.net ♥\" ");
  HEADER("HTTP/1.0 401 Unauthorized");
  echo("<html><head><script>
       <!--
        function pop()
        { alert('관리자 인증 실패');
             history.go(-1);}
       //--->
        </script>
        </head>
        <body onLoad='pop()'></body>
        </html>
       ");
exit;
}

if(!$PHP_AUTH_USER || !$PHP_AUTH_PW)
{
 authenticate();
}

else
{

$result= mysql_query("select * from member where no='1'",$db);
$row= mysql_fetch_array($result);

$adminid="$row[id]";
$adminpasswd="$row[pass]";


 if(strcmp($PHP_AUTH_USER,$adminid) || strcmp($PHP_AUTH_PW,$adminpasswd) )
 { authenticate(); }


}
?>