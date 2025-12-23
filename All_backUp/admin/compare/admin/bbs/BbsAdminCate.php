<?
if(!$BbsAdminCateUrl){$BbsAdminCateUrl="../..";}

$dir_path = "$BbsAdminCateUrl/bbs/skin";
$dir_handle = opendir($dir_path);

$RRT="selected style='font-size:10pt; background-color:#429EB2; color:#FFFFFF;'";

echo("<select name='skin'><option value='0'>¢∆ SKIN º±≈√ ¢∆</option>");

while($tmp = readdir($dir_handle))
{
if(($tmp != ".") && ($tmp != "..")){
		if($BBS_ADMIN_skin=="$tmp"){
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp' $RRT>$tmp</option>");  
			}else{
			echo (is_dir($dir_path.$tmp) ? "" : "<option value='$tmp'>$tmp</option>");  
			}		  }
}

echo("</select>");

closedir($dir_handle);
?>