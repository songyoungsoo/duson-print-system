<?php
// $Gourl 변수가 URL 파라미터로 전달될 경우 이를 가져옵니다.
$Gourl = isset($_GET['Gourl']) ? $_GET['Gourl'] : '';

// 팝업 여부 확인: $Gourl 변수가 "pop"인지 여부를 확인합니다.
$isPopup = ($Gourl === "pop");

// 기본 로그인 리다이렉트 URL 설정
$redirectUrl = "/member/login.php";

function check_login($isPopup, $redirectUrl) {
    if (isset($_COOKIE['id_login_ok'])) {
        // 로그인 상태 확인 완료
        return true;
    } else {
        // 로그인 필요
        $message = "현 페이지는 로그인을 하여야 이용하실 수 있습니다.\\n\\n로그인할 아이디가 없으시면 회원가입 후 이용하시기 바랍니다.";
        echo "<script>alert('$message');</script>";

        if ($isPopup) {
            // 팝업일 경우
            echo "<script>
                    opener.parent.location='$redirectUrl'; 
                    window.self.close();
                  </script>";
        } else {
            // 일반 페이지일 경우
            $selfUrl = str_replace("&", "@", $_SERVER['REQUEST_URI']);
            echo "<meta http-equiv='Refresh' content='0; URL=$redirectUrl?LoginChickBoxUrl=$selfUrl'>";
        }
        exit;
    }
}

// 로그인 상태를 확인하고 필요시 리다이렉트
check_login($isPopup, $redirectUrl);
?>
