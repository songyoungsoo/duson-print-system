<?php
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $files=array(), $charset="UTF-8", $cc="", $bcc="") {
    // type : text=0, html=1, text+html=2

    $fname   = "=?$charset?B?" . base64_encode($fname) . "?=";
    $subject = "=?$charset?B?" . base64_encode($subject) . "?=";
    $charset = ($charset != "") ? "charset=$charset" : "";

    $header  = "Return-Path: <$fmail>\n";
    $header .= "From: $fname <$fmail>\n";
    $header .= "Reply-To: <$fmail>\n";
    if ($cc)  $header .= "Cc: $cc\n";
    if ($bcc) $header .= "Bcc: $bcc\n";
    $header .= "MIME-Version: 1.0\n";
    $header .= "X-Mailer: SiR Mailer 1.0\n";

    if (!empty($files)) {
        $boundary = uniqid("boundary_");

        $header .= "Content-Type: MULTIPART/MIXED; BOUNDARY=\"$boundary\"\n\n";
        $header .= "--$boundary\n";
    }

    if ($type) {
        $header .= "Content-Type: TEXT/HTML; $charset\n";
        if ($type == 2)
            $content = nl2br($content);
    } else {
        $header .= "Content-Type: TEXT/PLAIN; $charset\n";
        $content = stripslashes($content);
    }
    $header .= "Content-Transfer-Encoding: BASE64\n\n";
    $header .= chunk_split(base64_encode($content)) . "\n";

    if (!empty($files)) {
        foreach ($files as $file) {
            $header .= "--$boundary\n";
            $header .= "Content-Type: APPLICATION/OCTET-STREAM; name=\"" . $file['name'] . "\"\n";
            $header .= "Content-Transfer-Encoding: BASE64\n";
            $header .= "Content-Disposition: attachment; filename=\"" . $file['name'] . "\"\n\n";

            $header .= chunk_split(base64_encode($file['data'])) . "\n";
        }
        $header .= "--$boundary--\n";
    }

    @mail($to, $subject, "", $header);
}
?>
