<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script>
        function openWindow() {
            // 새 윈도우를 엽니다.
            var passwordWindow = window.open("about:blank", "passwordWindow", "width=300,height=200");

            // 윈도우에 HTML 폼을 생성합니다.
            var passwordForm = "<html><head><title>Password</title></head><body>" +
                "<form name='passwordForm'>" +
                "Password: <input type='password' name='password'>" +
                "<input type='button' value='Submit' onclick='submitPassword()'>" +
                "</form></body></html>";
            passwordWindow.document.write(passwordForm);
        }

        function submitPassword() {
            // 입력한 비밀번호를 가져옵니다.
            var passwordInput = window.opener.document.forms["myForm"]["password"].value;

            // PHP 스크립트로 비밀번호를 전송합니다.
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // PHP 스크립트에서 반환된 회원 번호를 가져옵니다.
                    var member_id = this.responseText;

                    if (member_id == "") {
                        // 회원 번호가 없으면 제자리로 돌아갑니다.
                        window.close();
                        alert("Invalid password.");
                    } else {
                        // 회원 번호가 있으면 페이지를 표시합니다.
						
                        window.close();
                        // 이 부분에서는 페이지를 표시하는 코드를 작성합니다.
						echo "../mlangorder_printauto/WindowSian.php?mode=OrderView&no=$_POST["$no"]";
                    }
                }
            };
            xmlhttp.open("GET", "pw_check1.php?password=" + passwordInput, true);
            xmlhttp.send();
        }
    </script>
</head>
<body>
    <form name="myForm">
        <input type="button" value="Login" onclick="openWindow()">
    </form>
</body>
</html>
