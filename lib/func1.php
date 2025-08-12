<?php
function dbconn(){
    $c = mysql_connect("localhost","duson1830","du1830");
	mysql_select_db("duson1830",$c);
	mysql_query("SET NAMES euckr");
	return $c;
	}
	
	function passwd($a){
	   global $connect;
	   $query = "select password('$a')";
	   $result =mysql_query($query, $connect);
	   $temp = mysql_fetch_array($result);
	   return $temp[0];
	  }
	  
	  function member(){
	     global $connect,$sing_member;
		 $temp = explode("//",$sing_member);
		 $user_id = $temp[0];
		 $pw = $temp[1];
		 
		 $query = "select * from member where user_id='user_id' ";
		 $result =mysql_query($query, $connect);
     $data = mysql_fetch_array($result);
		 
		 if(passwd('$data[pw]')==$pw) return $data;
		}
		
		?>
		<style>
td,input,li,a{font-size:9pt}
border{border-color:#CCC}
</style>
