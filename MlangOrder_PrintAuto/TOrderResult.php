<?php
$DbDir="..";
$GGTABLE="MlangPrintAuto_transactionCate";

// POST 또는 GET 요청에서 변수 가져오기
$MY_type = $_POST['MY_type'] ?? $_GET['MY_type'] ?? '';
$MY_Fsd = $_POST['MY_Fsd'] ?? $_GET['MY_Fsd'] ?? '';
$PN_type = $_POST['PN_type'] ?? $_GET['PN_type'] ?? '';
$POtype = $_POST['POtype'] ?? $_GET['POtype'] ?? '';
$MY_amount = $_POST['MY_amount'] ?? $_GET['MY_amount'] ?? 0;
$ordertype = $_POST['ordertype'] ?? $_GET['ordertype'] ?? '';
$PriceForm = $_POST['PriceForm'] ?? $_GET['PriceForm'] ?? 0;
$DS_PriceForm = $_POST['DS_PriceForm'] ?? $_GET['DS_PriceForm'] ?? 0;
$Order_PriceForm = $_POST['Order_PriceForm'] ?? $_GET['Order_PriceForm'] ?? 0;
$Total_PriceForm = $_POST['Total_PriceForm'] ?? $_GET['Total_PriceForm'] ?? 0;
$ImgFolder = $_POST['ImgFolder'] ?? ''; // 여기에 기본값을 설정합니다.
$page = $_POST['page'] ?? ''; // 여기에 기본값을 설정합니다.
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid;border-color:#e4e4e4">
    <tr>
        <td>
            <input type="hidden" name="ImgFolder" value="<?= htmlspecialchars($ImgFolder) ?>">
            <input type="hidden" name="Type" value="<?= htmlspecialchars($page) ?>">

            <table border="0" align="left" width="100%" cellpadding="0" cellspacing="0">
                <tr><td colspan="3" height="7"></td></tr>
                <tr><td>&nbsp;&nbsp;</td>
                    <td>

                        <table border="0" width="100%" align="center" cellpadding="1" cellspacing="0">

                            <?php if($page=="inserted"){ ?>

                            <tr>
                                <td>용지종류:
                                    <?php
                                    include "$DbDir/db.php";
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_Fsd'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_2' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>인쇄:
                                    <?php
                                    if ($POtype == "1") {
                                        echo "단면 <input type='hidden' name='Type_4' value='단면'>";
                                    } elseif ($POtype == "2") {
                                        echo "양면 <input type='hidden' name='Type_4' value='양면'>";
                                    }
                                    ?>
                                </td>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> 연 <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="sticker"){ ?>

                            <tr>
                                <td>스티커 종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> 장 <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="NameCard"){ ?>

                            <tr>
                                <td>명함종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>인쇄:
                                    <?php
                                    if ($POtype == "1") {
                                        echo "단면 <input type='hidden' name='Type_4' value='단면'>";
                                    } elseif ($POtype == "2") {
                                        echo "양면 <input type='hidden' name='Type_4' value='양면'>";
                                    }
                                    ?>
                                </td>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> 장 <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="MerchandiseBond"){ ?>

                            <tr>
                                <td>종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>인쇄:
                                    <?php
                                    if ($POtype == "1") {
                                        echo "단면 <input type='hidden' name='Type_4' value='단면'>";
                                    } elseif ($POtype == "2") {
                                        echo "양면 <input type='hidden' name='Type_4' value='양면'>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>후가공:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="envelope"){ ?>

                            <tr>
                                <td>종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>인쇄면수:
                                    <?php
                                    if ($POtype == "1") {
                                        echo "1도 <input type='hidden' name='Type_4' value='1도'>";
                                    } elseif ($POtype == "2") {
                                        echo "2도 <input type='hidden' name='Type_4' value='2도'>";
                                    } elseif ($POtype == "3") {
                                        echo "4도(칼라) <input type='hidden' name='Type_4' value='4도(칼라)'>";
                                    }
                                    ?>
                                </td>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="NcrFlambeau"){ ?>

                            <tr>
                                <td>종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_Fsd'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_2' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="cadarok"){ ?>

                            <tr>
                                <td>종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_Fsd'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_2' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="cadarokTwo"){ ?>

                            <tr>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_Fsd'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_2' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                                <td>&nbsp;</td>
                            </tr>

                            <?php } ?>

                            <?php if($page=="LittlePrint"){ ?>

                            <tr>
                                <td>종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_1' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>후가공종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$MY_Fsd'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_2' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                                <td>디자인종류:
                                    <?php
                                    $Cate_result = $db->query("SELECT * FROM $GGTABLE WHERE no='$PN_type'");
                                    $Cate_rows = $Cate_result->fetch_assoc();
                                    if ($Cate_rows) {
                                        echo "<input type='hidden' name='Type_3' value='" . htmlspecialchars($Cate_rows['title']) . "'>" . htmlspecialchars($Cate_rows['title']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>인쇄:
                                    <?php
                                    if ($POtype == "1") {
                                        echo "단면 <input type='hidden' name='Type_4' value='단면'>";
                                    } elseif ($POtype == "2") {
                                        echo "양면 <input type='hidden' name='Type_4' value='양면'>";
                                    }
                                    ?>
                                </td>
                                <td>수량: <?= htmlspecialchars($MY_amount) ?> <input type='hidden' name='Type_5' value='<?= htmlspecialchars($MY_amount) ?>'>
                                </td>
                                <td>주문형태:
                                    <?php
                                    if ($ordertype == "total") {
                                        echo "디자인+인쇄 <input type='hidden' name='Type_6' value='디자인+인쇄'>";
                                    } elseif ($ordertype == "print") {
                                        echo "인쇄만 의뢰 <input type='hidden' name='Type_6' value='인쇄만 의뢰'>";
                                    } elseif ($ordertype == "design") {
                                        echo "디자인만 의뢰 <input type='hidden' name='Type_6' value='디자인만 의뢰'>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php } ?>

                            <tr><td colspan="3" height="10"></td></tr>
                        </table>

                        <table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <input type="hidden" name="money_1" value="<?= htmlspecialchars($PriceForm) ?>">
                                    <input type="hidden" name="money_2" value="<?= htmlspecialchars($DS_PriceForm) ?>">
                                    <input type="hidden" name="money_3" value="<?= htmlspecialchars($VAT_PriceForm) ?>">
                                    <input type="hidden" name="money_4" value="<?= htmlspecialchars($Order_PriceForm) ?>">
                                    <input type="hidden" name="money_5" value="<?= htmlspecialchars($Total_PriceForm) ?>">

                                    총 인쇄비:&nbsp;<font style="font:bold; font-size:10pt;"><?= number_format((float)$PriceForm) ?></font>원&nbsp;&nbsp;    
                                </td>
                                <td>
                                    총 디자인비:&nbsp;<font style="font:bold; font-size:10pt;"><?= number_format((float)$DS_PriceForm) ?></font>원&nbsp;&nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    총 주문금액:&nbsp;<font style="font:bold; font-size:10pt; color:red;font-family:돋움;"><?= number_format((float)$Order_PriceForm) ?></font>원&nbsp;&nbsp;
                                </td>
                                <td>
                                    총 부가세 포함금액:&nbsp;<font style="font:bold; font-size:10pt; color:red;font-family:돋움;"><?= number_format((float)$Total_PriceForm) ?></font>원
                                </td>
                            </tr>
                        </table>

                    </td>
                    <td>&nbsp;&nbsp;</td>
                </tr>
                <tr><td colspan="3" height="7"></td></tr>
            </table>

        </td>
    </tr>
</table>

<!------------------- 주문수량 입력 ---------------------------------->
<table border="0" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table width="100%" border="0" cellspacing="2" cellpadding="0">
                <tr>
                    <td width="130" align="center" bgcolor="#E4E4E4"><strong>주문수량</strong></td>
                    <td align="left">
                        <input name="Gensu" type="text" size="10">
                        * 기본 주문마다 주문량을 입력 (1번이면 1)
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<!------------------- 주문수량 입력 끝 ------------------------------------>