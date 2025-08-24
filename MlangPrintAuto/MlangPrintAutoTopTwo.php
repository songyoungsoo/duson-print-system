<?php
$SoftUrl = "/MlangPrintAuto";
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <title>Estimate Page</title>
    <style>
        body {
            background-color: white;
            color: black;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        a {
            color: blue;
        }

        a:visited {
            color: purple;
        }

        a:active {
            color: red;
        }

        table {
            width: 100%;
            max-width: 1004px;
            margin: 0 auto;
            background-image: url('/images/bkline.gif');
            border-collapse: collapse;
        }

        .flash-content {
            width: 394px;
            height: 541px;
            background-image: url('/images/dot.gif');
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 768px) {
            .flash-content,
            .flash-content video {
                width: 100%;
                height: auto;
            }

            table {
                width: 100%;
                margin: 0;
            }

            .flash-content {
                height: auto;
            }
        }
    </style>
    <script>
        function restoreImgSrc(name, doc = document) {
            const img = doc.getElementById(name);
            if (img && img.dataset.altsrc) {
                img.src = img.dataset.altsrc;
                img.dataset.altsrc = '';
            }
        }

        function preloadImg(...imagePaths) {
            document.preloadlist = document.preloadlist || [];
            imagePaths.forEach((src) => {
                const img = new Image();
                img.src = src;
                document.preloadlist.push(img);
            });
        }

        function changeImgSrc(name, doc = document, newSrc) {
            const img = doc.getElementById(name);
            if (img) {
                img.dataset.altsrc = img.src;
                img.src = newSrc;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            preloadImg(
                '/images/ebt01-.gif',
                '/images/ebt02-.gif',
                '/images/ebt03-.gif',
                '/images/ebt04-.gif',
                '/images/ebt05-.gif',
                '/images/ebt06-.gif',
                '/images/ebt07-.gif'
            );
        });
    </script>
</head>

<body>
    <div align="left">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="3" height="111">
                    <p align="left"><?php include $_SERVER['DOCUMENT_ROOT'] . "/top.php"; ?></p>
                </td>
            </tr>
            <tr>
                <td class="flash-content" rowspan="2" valign="top">
                    <p>
                        <!-- Modern replacement for Flash content -->
                        <video width="394" height="541" controls>
                            <source src="/video/estimate.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </p>
                </td>
                <td width="584" height="16" valign="bottom">
                    <p align="center"><?php include $_SERVER['DOCUMENT_ROOT'] . "/menu-estimate.php"; ?></p>
                </td>
                <td width="26" height="16">
                    <p>&nbsp;</p>
                </td>
            </tr>
            <tr>
                <td width="584" height="480" valign="top">
                    <table align="center" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td height="31" valign="bottom">
                                <p align="right"><img src="/images/title14.gif" width="570" height="30" alt="Estimate Title"></p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table align="center" width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td height="10"></td>
                                    </tr>
                                    <!-- Additional content here -->
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
