<?
session_cache_limiter("no-cache, must-revalidate"); 
Header ("Expires: 0");
Header ("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 2001 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Cache-control: private"); // <= it's magical!!
?>

<?
include"../top.php"; 
?>

     <table border=0 align=center cellpadding=0 cellspacing=0>
       <tr>
         <td><img src='./img/HelpImg_1.gif' width=693 height=147></td>
       </tr>
	         
			  <tr>
         <td><img src='./img/HelpImg_2.gif' width=153 height=22></td>
       </tr>
  <tr>
    <td height="2" background="../images/dot.gif"> </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	          <tr>
         <td><img src='./img/HelpImg_3.gif' width=644 height=188></td>
       </tr>
	     <tr>
    <td height=40>&nbsp;</td>
  </tr>

			  <tr>
         <td>&nbsp;<img src='./img/HelpImg_4.gif' width=90 height=20></td>
       </tr>
  <tr>
    <td height="2" background="../images/dot.gif"> </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	          <tr>
         <td><img src='./img/HelpImg_5.gif' width=632 height=203></td>
       </tr>
	     <tr>
    <td height=40>&nbsp;</td>
  </tr>

  			  <tr>
         <td>&nbsp;<img src='./img/HelpImg_6.gif' width=356 height=21></td>
       </tr>
  <tr>
    <td height="2" background="../images/dot.gif"> </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	          <tr>
         <td><img src='./img/HelpImg_7.gif' width=618 height=414></td>
       </tr>
	     <tr>
    <td height=40>&nbsp;</td>
  </tr>

    			  <tr>
         <td>&nbsp;<img src='./img/HelpImg_8.gif' width=216 height=23></td>
       </tr>
  <tr>
    <td height="2" background="../images/dot.gif"> </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	          <tr>
         <td><img src='./img/HelpImg_9.gif' width=566 height=401></td>
       </tr>
	     <tr>
    <td height=40>&nbsp;</td>
  </tr>

      			  <tr>
         <td>&nbsp;<img src='./img/HelpImg_10.gif' width=105 height=22></td>
       </tr>
  <tr>
    <td height="2" background="../images/dot.gif"> </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	          <tr>
         <td><img src='./img/HelpImg_11.gif' width=489 height=349></td>
       </tr>
	     <tr>
    <td height=40>&nbsp;</td>
  </tr>

     </table>

<BR><BR>
<?
include"../down.php"; 
?>