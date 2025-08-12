     <div class="modern-header">
       <a href="<?php echo isset($M123) ? $M123 : '..'; ?>/index.php" class="modern-logo">MlangWeb관리프로그램</a>
     </div>

<?php 
$menu_path = (isset($M123) ? $M123 : '.') . "/modern_menu.php";
if (file_exists($menu_path)) {
    include_once($menu_path);
} else {
    // 파일이 존재하지 않을 경우 절대 경로 시도
    include_once(dirname(__FILE__) . "/modern_menu.php");
}
?>
