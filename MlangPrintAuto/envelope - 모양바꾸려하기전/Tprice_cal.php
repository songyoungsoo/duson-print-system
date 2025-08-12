<?php 
function ERROR(){
    echo "<script>alert('정상적으로 선택해 주시기 바랍니다.');</script>";
}

if (isset($TRYCobe) && $TRYCobe == "ok") {
    if (empty($TypeOne) || $TypeOne == "========") {
        ERROR();
    } else if (empty($TypeTwo) || $TypeTwo == "========") {
        ERROR();
    } else {
        $Ttable = "envelope";
        include "../../db.php";
        
        // Initialize database connection using mysqli
        $db = mysqli_connect($host, $user, $password, $dataname);

        if (!$db) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        // Build the SQL query dynamically, validating the table name
        $query = "SELECT * FROM MlangPrintAuto_" . mysqli_real_escape_string($db, $Ttable) . " WHERE style=? AND Section=? AND POtype=? ORDER BY quantity ASC";
        
        // Prepare and execute the query
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $TypeOne, $TypeTwo, $TypeTree);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            $rows = mysqli_num_rows($result);
            ?>

            <script>
                var obj = parent.document.forms["choiceForm"].MY_amount;
                var i;

                // Clear existing options
                for (i = parent.document.forms["choiceForm"].MY_amount.options.length - 1; i >= 0; i--) {
                    parent.document.forms["choiceForm"].MY_amount.options[i] = null; 
                }

                <?php
                if ($rows) {
                    $g = 0;
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                obj.options[<?= $g ?>] = new Option('<?= $row['quantity'] ?>매', '<?= $row['quantity'] ?>');
                <?php
                        $g++;
                    }
                }
                ?>
            </script>

            <?php
            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            // Handle the error if the statement couldn't be prepared
            echo "<script>alert('Query preparation failed.');</script>";
        }

        // Close the database connection
        mysqli_close($db);
    }
}
?>