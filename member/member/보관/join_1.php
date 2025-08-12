<?php
$Color1="1466BA";
$Color2="4C90D6";
$Color3="BBD5F0";
$PageCode="member";
include"../db.php";
include"../top.php";
?>
  <!-- <link rel="stylesheet" type="text/css" href="css/style.css">
  <style>
     /*�α��� �� ��Ÿ�� */
      form {
          width: 400px;
          border: 1px solid gray;
          padding: 20px;
          border-radius: 5px;
          box-shadow: 1px 1px 10px gray;
          text-align: center;
          position: absolute;
          top: 50%;
          left: 50%;
          margin-top:-250px ;
         margin-left:-150px ;
      }

      label{
        display:block; 
        margin-bottom :10px; 
      }

      input[type="text"], input[type="password"]{
        width :100%; 
        padding :5px; 
        margin-bottom :10px; 
      }

      button[type="submit"]{
         padding :5px   ; 
         background-color :skyblue ;  
         color:white ;  
         border:none ;  
         border-radius :3px ;
         cursor:pointer ;   
       }
       button[type="submit"]:hover{background-color:dodgerblue;}
      
   </style>  -->
<?php
if(!$DbDir){$DbDir="..";}
if(!$MemberDir){$MemberDir=".";}

include"$DbDir/db.php";
$query = "select * from member where id='$id'";
$result = mysql_query($query,$db);
$rows=mysql_num_rows($result);
if($rows){
echo("
<script language=javascript>
window.alert('\\n $id �� �̵̹�ϵǾ��ִ�\\n\\n�̸��̹Ƿ� ��û�ϽǼ� �����ϴ�.\\n');
history.go(-1);
</script>	
");
exit;

}

$login_dir="$MemberDir";
$db_dir="$MemberDir";

$action="$MemberDir/member_form_ok.php";
include"$MemberDir/form.php";

include"../down.php";
?>