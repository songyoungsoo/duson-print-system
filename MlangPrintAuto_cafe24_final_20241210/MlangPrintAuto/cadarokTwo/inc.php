<?php
$DesignMoney = "";
$SectionOne = "1111111111111111111111. -기본 작업내용 및 기본 정보를 입력하여 주시면 최단 시간내에 담당자가 연락드려서 보다 정확한 견적가를 산출해 드리겠습니다.";
$SectionTwo = "2222";
$SectionTree = "111111112awdawea asdada adadadas";
$SectionFour = "3333";
$SectionFive = "444444";
$ImgOne = "";
$ImgTwo = "";
$ImgTree = "";
$ImgFour = "";
$ImgFive = "";

// Example of how to safely output these variables in HTML
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <h1>Sections</h1>
    <p><?= htmlspecialchars($SectionOne, ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= htmlspecialchars($SectionTwo, ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= nl2br(htmlspecialchars($SectionTree, ENT_QUOTES, 'UTF-8')) ?></p>
    <p><?= htmlspecialchars($SectionFour, ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= htmlspecialchars($SectionFive, ENT_QUOTES, 'UTF-8') ?></p>

    <h1>Images</h1>
    <p><img src="<?= htmlspecialchars($ImgOne, ENT_QUOTES, 'UTF-8') ?>" alt="Image One"></p>
    <p><img src="<?= htmlspecialchars($ImgTwo, ENT_QUOTES, 'UTF-8') ?>" alt="Image Two"></p>
    <p><img src="<?= htmlspecialchars($ImgTree, ENT_QUOTES, 'UTF-8') ?>" alt="Image Three"></p>
    <p><img src="<?= htmlspecialchars($ImgFour, ENT_QUOTES, 'UTF-8') ?>" alt="Image Four"></p>
    <p><img src="<?= htmlspecialchars($ImgFive, ENT_QUOTES, 'UTF-8') ?>" alt="Image Five"></p>
</body>
</html>
