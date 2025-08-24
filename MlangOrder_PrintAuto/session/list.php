<?php
include "lib.php";
 
$isLogin = $_SESSION['isLogin'];

if (!$isLogin) {
    echo "회원만 접근 가능합니다.";
    exit; 
}

// 로그인 상태 확인
if (isset($_SESSION['isLogin']) && $_SESSION['isLogin']) {
    // 내 정보 링크 생성
    echo '<a href="myinfo.php">내 정보</a>';
    
    // 나의 주문 내역 링크 생성
    echo '<a href="myorders.php">주문 내역</a>';
    
    // 로그아웃 링크 생성
    echo '<a href="logOut.php">로그아웃</a>';
} else {
   // 로그인 페이지로 이동하는 링크 생성
   echo '<a href="login.php">로그인</a>';
   
   // 회원 가입 페이지로 이동하는 링크 생성
   echo '<a href="signup.php">회원 가입</a>';
}
?>

<a href="logOut.php">로그아웃</a> 
<h1> 회원리스트 </h1> 
<ul>
<?php for ($i = 0; $i <= 100; $i++) { ?>
    <li> 홍길동 <?= $i ?> </li>
    <li> 박문수 <?= $i ?> </li> 
<?php } ?>     
</ul>
