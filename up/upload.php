<!DOCTYPE html>
<html>
<style>
a {
text-decoration:none; 
color:black;'
}
</style>
<body>
<?php 
header("Content-Type: text/html; charset=euckr");
session_start();
include "../lib/func.php"; 
$conn = dbconn(); 
//	$conn = new mysqli("localhost","duson1830","du1830","duson1830");
mysqli_query($conn,'SET NAMES euckr');
$target_dir="../shop/data/";
$total = count($_FILES["file"]["name"]);
for($i=0; $i<$total; $i++) {
$target_file = $target_dir.basename($_FILES["file"]["name"][$i]);
$ext = pathinfo($target_file,PATHINFO_EXTENSION);
$filename = basename($target_file,".$ext");
$num = 1;
if (file_exists($target_file)) {
            while(file_exists($target_file)) {
                $filename2 = $filename."($num)";
                $target_file = $target_dir.$filename2.".$ext";
                $num++;
            }
        }
 if(move_uploaded_file($_FILES["file"]["tmp_name"][$i], $target_file)) {
$sql = "INSERT INTO upload(nickname,starttime,realname,changename)
VALUES ('".$_POST['nickname']."','".$_POST['time']."','".$filename.".$ext"."','$target_file')";
            $res = $conn->query($sql);
        } else {
            echo "<script>parent.alert('업로드를 성공하지 못했습니다.');</script>";
            exit();
        }
}
	$sql2 = "SELECT *FROM upload WHERE nickname='".$_POST['nickname']."' AND starttime='".$_POST['time']."'";
	$res2 = mysqli_query($conn, $sql2);
	while($row=mysqli_fetch_array($res2)) {
	echo "<div><a href='download.php?filepath=".$row['changename']."&filename=".$row['realname']."'>".$row['realname']."</a><a style='float:right' href='delete.php?filename=".$row['changename']."&time=".$row['starttime']."'>삭제</a></div><br>";
	}
?>
</body>
</html>
?>