<?php
if ($mode == "member_login") {
    include "../db.php";

    $id = $_POST['id'];
    $pass = $_POST['pass'];

    // Check if the user wants to remember the login
    $remember_me = isset($_POST['remember_me']) ? true : false;

    $id = mysql_real_escape_string($id);
    $pass = mysql_real_escape_string($pass);

    $result = mysql_query("select * from member where id='$id' and pass='$pass'", $db);
    $rows = mysql_num_rows($result);

    if ($rows) {
        while ($rows = mysql_fetch_array($result)) {
            $Countresult = mysql_query("select * from member where id='$id'", $db);
            $Countrow = mysql_fetch_array($Countresult);
            $LogonCountOk = $Countrow[Logincount] + 1;

            $Logindate = date("Y-m-d H:i:s");
            $query = "UPDATE member SET Logincount='$LogonCountOk', EndLogin='$Logindate' WHERE id='$id'";
            mysql_query($query, $db);

            $query = "SELECT * FROM member WHERE id='$id' AND pass='$pass'";
            $result = mysql_query($query, $db);

            if (!$result) {
                die("쿼리 실행에 실패했습니다: " . mysql_error());
            }

            $data = mysql_fetch_array($result);

            if ($data) {
                $_SESSION['id_login_ok'] = true;
                setcookie("id_login_ok", stripslashes($data['id']), 0, "/");

                if ($remember_me) {
                    setcookie('id_login_ok', true, time() + (86400 * 30), "/");
                }

                ?>
                <script>
                    location.href='../shop/view.php';
                </script>
                <?php
            } else {
                echo ("<script language=javascript>
                window.alert('로그인 실패. 아이디와 비밀번호를 확인해주세요.');
                </script>");
            }

            echo ("<html>
            <script language=javascript>
            window.alert('정상적으로 ♡ $admin_name ♡ 에 로그인 되셨습니다..\\n\\n좋은 하루 되시기를  바랍니다.....*^^*');
            window.self.close();
            </script>");

            if ($selfurl) {
                $selfurl_ok = eregi_replace("@", "&", $selfurl);
                echo("<meta http-equiv='Refresh' content='0; URL=$selfurl_ok'>");
            } else {
                echo("<meta http-equiv='Refresh' content='0; URL=/'>");
            }

            echo("</html>");
        }
    } else {
        echo ("<html>
        <script language=javascript>
        window.alert('입력하신 $id (과)와 비밀번호 $pass 가 불일치 합니다..\\n\\n다시 한번 확인해 주시기 바랍니다.....*^^*');
        history.go(-1);
        </script>
        </html>");
        exit;
    }

    mysql_close($db);
} elseif ($mode == "member_logout") {
    // Logout logic
    @setcookie("bbs_login", "", 0, "/");
    @setcookie("id_login_ok", "", 0, "/");
    @session_destroy();

    echo ("<html>
    <script language=javascript>
    window.alert('정상적으로 로그아웃 처리 되었습니다........*^^*');
    </script>
    <meta http-equiv='Refresh' content='0; URL=/'>
    </html>");
    exit;
} elseif (!$mode) {
    echo ("<html>
    <script language=javascript>
    window.alert('비정상적인 접속은 허용하지 않아요....잉!!');
    </script>
    <meta http-equiv='Refresh' content='0; URL=/'>
    </html>");
    exit;
}
?>
