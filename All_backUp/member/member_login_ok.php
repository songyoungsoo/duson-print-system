<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 로그인 폼에서 전송된 데이터를 받아온다.
    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    // $mode가 올바른지 확인하고, 그에 따라 적절한 동작을 수행한다.
if ($mode == "member_login") {
    include "../db.php";
    $id = isset($_POST['id']) ? mysqli_real_escape_string($db, $_POST['id']) : '';
    $pass = isset($_POST['pass']) ? mysqli_real_escape_string($db, $_POST['pass']) : '';

    $result_id = mysqli_query($db, "SELECT * FROM member WHERE id='$id'");
    $rows_id = mysqli_num_rows($result_id);

    if ($rows_id) {
        $result = mysqli_query($db, "SELECT * FROM member WHERE id='$id' AND pass='$pass'");
        $rows = mysqli_num_rows($result);
        var_dump($rows);

        if ($rows) {
            $Countresult = mysqli_query($db, "SELECT * FROM member WHERE id='$id'");
            $Countrow = mysqli_fetch_array($Countresult);
            $LogonCountOk = $Countrow['Logincount'] + 1;

            $Logindate = date("Y-m-d H:i:s");
            $query = "UPDATE member SET Logincount='$LogonCountOk', EndLogin='$Logindate' WHERE id='$id'";
            mysqli_query($db, $query);

            // include "../db.php";
            $query = "SELECT * FROM member WHERE id='$id' AND pass='$pass'";
            $result = mysqli_query($db, $query);

            if (!$result) {
                die("쿼리 실행에 실패했습니다: " . mysqli_error($db));
            }

            $data = mysqli_fetch_array($result);

            if ($data) {
                $_SESSION['id_login_ok'] = array(
                    'id' => $data['id'],
                    'pass' => $data['pass']
                );

                setcookie("id_login_ok", stripslashes($data['id']), 0, "/");
                ?>
                <html>
                    <script language="javascript">
                        window.alert('정상적으로 로그인 되셨습니다..\\n\\n좋은 하루 되시기를  바랍니다.....*^^*');
                        location.href = '../shop/view.php';
                        exit; // Ensure that the script exits after sending the JavaScript code
                    </script>
                </html>
                <?php
            } else {
                ?>
                <html>
                    <script language="javascript">
                        window.alert('로그인 실패. 아이디와 비밀번호를 확인해주세요.');
                    </script>
                </html>
                <?php
            }
        } else {
            ?>
            <html>
                <script language="javascript">
                    window.alert('입력하신 <?php echo $id; ?>와 비밀번호 <?php echo $pass; ?>가 일치하지 않습니다..\\n\\n다시 한번 확인해 주시기 바랍니다.....*^^*');
                    history.go(-1);
                </script>
            </html>
            <?php
            exit;
        }
    } else {
        ?>
        <html>
            <script language="javascript">
                window.alert('입력하신 <?php echo $id; ?>로는 회원 가입이 되어있지 않습니다.\\n\\n다시 한번 확인해 주시기 바랍니다.....*^^*');
                history.go(-1);
            </script>
        </html>
        <?php
        exit;
    }
} elseif ($mode == "member_logout") {
    @setcookie("bbs_login", "", 0, "/");
    @setcookie("id_login_ok", "", 0, "/");
    @session_destroy();
    ?>
    <html>
        <script language="javascript">
            window.alert('정상적으로 로그아웃 처리 되었습니다........*^^*');
            location.href = '/';
        </script>
    </html>
    <?php
} elseif (!$mode) {
    ?>
    <html>
        <script language="javascript">
            window.alert('비정상적인 접속은 허용하지 않아요....잉!!');
            location.href = '/';
        </script>
    </html>
    <?php
}
mysqli_close($db);
}
?>
