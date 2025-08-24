<?php
<?php
if($FileDelete=="ok"){
unlink("../../ImgFolder/$Turi/$Ty/$Tmd/$Tip/$Ttime/$FileName"); 

echo("
<html>
<script>
window.self.close();
</script>
</html>
");
}
?>
