<?
// 메일 보내기 (파일 여러개 첨부 가능)
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $charset="EUC-KR", $cc="", $bcc="") {
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

    if ($file != "") {
        $boundary = uniqid("http://sir.co.kr/");

        $header .= "Content-type: MULTIPART/MIXED; BOUNDARY=\"$boundary\"\n";
        $header .= "--$boundary\n";
    }

    if ($type) {
        $header .= "Content-Type: TEXT/HTML; $charset\n";
        if ($type == 2)
            $content = nl2br($type);
    } else {
        $header .= "Content-Type: TEXT/PLAIN; $charset\n";
        $content = stripslashes($content);
    }
    $header .= "Content-Transfer-Encoding: BASE64\n\n";
    $header .= chunk_split(base64_encode($content)) . "\n";

    if ($file != "") {
        foreach ($file as $f) {
            $header .= "\n--$boundary\n";
            $header .= "Content-Type: APPLICATION/OCTET-STREAM; name=\"$f[name]\"\n";
            $header .= "Content-Transfer-Encoding: BASE64\n";
            $header .= "Content-Disposition: inline; filename=\"$f[name]\"\n";

            $header .= "\n";
            $header .= chunk_split(base64_encode($f[data]));
            $header .= "\n";
        }
        $header .= "--$boundary--\n";
    }
    @mail($to, $subject, "", $header);
}
?>
