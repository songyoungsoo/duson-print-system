<?php
// Function to authenticate users
function authenticate()
{
    header("WWW-authenticate: basic realm=\"회원 전용!\" ");
    header("HTTP/1.0 401 Unauthorized");
    echo("<html><head><script>
          function pop() {
              alert('인증이 필요합니다');
              history.go(-1);
          }
          </script></head>
          <body onLoad='pop()'></body></html>");
    exit;
}

// Check if credentials are provided
if (!$PHP_AUTH_USER || !$PHP_AUTH_PW) {
    authenticate();
} else {
    // Include database connection and fetch admin credentials
    include "../../db.php";
    $result = mysql_query("select * from $admin_table where no='1'", $db);
    $row = mysql_fetch_array($result);

    $adminid = $row["id"];
    $adminpasswd = $row["pass"];

    // Check if provided credentials match admin credentials
    if (strcmp($PHP_AUTH_USER, $adminid) || strcmp($PHP_AUTH_PW, $adminpasswd)) {
        authenticate();
    }
}

// Handle different modes (form, form_ok, delete)
if ($mode == "form") {
    // Form for member registration
    include "../title.php";
    $Bgcolor1 = "408080";
    ?>
    <html>
    <head>
        <style>
            .Left1 {
                font-size: 10pt;
                color: #FFFFFF;
                font: bold;
            }
        </style>
        <script language="javascript">
            // JavaScript form validation
            function MemberXCheckField() {
                var f = document.FrmUserXInfo;

                if (f.sex.value == "0") {
                    alert("성별을 선택하세요!!");
                    f.sex.focus();
                    return false;
                }
                // Add similar checks for other fields

                return true;
            }
        </script>
    </head>
    <body LEFTMARGIN='0' TOPMARGIN='0' MARGINWIDTH='0' MARGINHEIGHT='0' class='coolBar'>
    <b>&nbsp;&nbsp;회원 등록 입력</b><BR>
    <table border=0 align=center width=100% cellpadding=0 cellspacing=5>
    <form name='FrmUserXInfo' method='post' OnSubmit='javascript:return MemberXCheckField()' action='<?php echo $PHP_SELF?>'>
        <input type="hidden" name='mode' value='form_ok'>
        <tr>
            <td bgcolor='#<?php echo $Bgcolor1?>' width=100 class='Left1' align=right>성별&nbsp;&nbsp;</td>
            <td>
                <select name='sex'>
                    <option value='0'>성별을 선택하세요</option>
                    <option value='1'>남성</option>
                    <option value='2'>여성</option>
                </select>
            </td>
        </tr>
        <!-- Add similar fields here -->
        <tr>
            <td>&nbsp;&nbsp;</td>
            <td><input type='submit' value=' 등록 '></td>
        </tr>
    </form>
    </table>
    </body>
    </html>
    <?php
} elseif ($mode == "form_ok") {
    // Form submission handling (insert into database)
    include "../../db.php";

    // Sanitize inputs before using in SQL query
    $id = mysql_real_escape_string($id, $db);
    $year = intval($year); // Ensure integer
    $map = mysql_real_escape_string($map, $db);
    $job = mysql_real_escape_string($job, $db);
    $school = intval($school); // Ensure integer
    $sex = intval($sex); // Ensure integer

    // Insert into database
    $dbinsert = "INSERT INTO member_X VALUES(NULL, '$id', '$year', '$map', '$job', '$school', '$sex')";
    $result_insert = mysql_query($dbinsert, $db);

    if ($result_insert) {
        echo "<script>alert('회원 등록이 완료되었습니다.'); window.self.close();</script>";
    } else {
        echo "<script>alert('회원 등록 중 오류가 발생했습니다.'); history.go(-1);</script>";
    }
} elseif ($mode == "delete") {
    // Delete member from database
    include "../../db.php";

    $result = mysql_query("DELETE FROM member_X WHERE no='$no'", $db);

    if ($result) {
        echo "<script>alert('회원 삭제가 완료되었습니다.'); window.self.close();</script>";
    } else {
        echo "<script>alert('회원 삭제 중 오류가 발생했습니다.'); history.go(-1);</script>";
    }
}

mysql_close($db);
?>
