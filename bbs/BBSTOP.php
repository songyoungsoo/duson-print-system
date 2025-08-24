<?php
$BbsTopH_result = mysqli_query($db, "select * from BBS_TOP order by no desc limit 0, $BBS_ADMIN_NoticeStyleSu");
$BbsTopH_rows = mysqli_num_rows($BbsTopH_result);
if ($BbsTopH_rows) {

    while ($BbsTopH_row = mysqli_fetch_array($BbsTopH_result)) {

        // 올바른 배열 인덱싱 및 쿼리 문자열 조립
        $bbs_table = $BbsTopH_row['BBS_Table'];
        $bbs_no = $BbsTopH_row['BBS_No'];
        $BbsTopOk_result = mysqli_query($db, "select * from Mlang_{$bbs_table}_bbs where Mlang_bbs_no='$bbs_no'");
        $BbsTopOk_row = mysqli_fetch_array($BbsTopOk_result);

        if ($BbsTopOk_row) {

            $BbsListTitle_1_ok = str_cutting($BbsTopOk_row['Mlang_bbs_title'], $x);
            $BbsListTitle_1 = htmlspecialchars($BbsListTitle_1_ok);

            echo "<tr bgcolor='$BBS_ADMIN_td_color2'>
<td>&nbsp;</td>";

            $BbsListTitle_91_ok = str_cutting($BbsTopOk_row['Mlang_bbs_title'], $x);
            $BbsListTitle_91 = htmlspecialchars($BbsListTitle_91_ok);

            if ($bbs_table == "notice") {
                $BbsTopLink = "/mepa114/sub/06_01.php";
            } else {
                $BbsTopLink = $PHP_SELF;
            }

            echo "<td><a href='{$BbsTopLink}?mode=view&table={$bbs_table}&no={$BbsTopOk_row['Mlang_bbs_no']}&page=$page&PCode=$PCode' class='bbs'>{$BbsListTitle_91}</a></td>";

            if ($BBS_ADMIN_cate) {
                if ($BbsTopOk_row['CATEGORY']) {
                    echo "<td align=center nowrap><font style='color:#408080;'>[ {$BbsTopOk_row['CATEGORY']} ]</font>&nbsp;</td>";
                }
            }

            if ($BBS_ADMIN_name_select == "yes") {
                echo "<td align=center nowrap>{$BbsTopOk_row['Mlang_bbs_member']}</td>";
            }

            if ($BBS_ADMIN_count_select == "yes") {
                echo "<td align=center nowrap>{$BbsTopOk_row['Mlang_bbs_count']}</td>";
            }

            if ($BBS_ADMIN_recommendation_select == "yes") {
                echo "<td align=center nowrap>{$BbsTopOk_row['Mlang_bbs_rec']}</td>";
            }

            if ($BBS_ADMIN_date_select == "yes") {
                $date_111 = substr($BbsTopOk_row['Mlang_date'], 0, 10);
                echo "<td align=center nowrap>$date_111</td>";
            }

            /////////////////////////// 관리자 모드 호출 START //////////////////
            $AdminChickTYyj = mysqli_query($db, "select * from member where no='1'");
            $row_AdminChickTYyj = mysqli_fetch_array($AdminChickTYyj);
            $BBSAdminloginKK = $row_AdminChickTYyj['id'];
            if (isset($_COOKIE['id_login_ok']) && $_COOKIE['id_login_ok'] == $BBSAdminloginKK) {
                echo "<td align=center nowrap>";
                ?>
                <input type='button' onClick="javascript:popup=window.open('/mepa114/admin/int/delete.php?AdminCode21=form&no=<?php echo $BbsTopOk_row['Mlang_bbs_no']?>&table=<?php echo 'Mlang_' . $bbs_table . '_bbs'; ?>', 'BBSTopCount','scrollbars=no,resizable=yes,width=450,height=150,top=120,left=20'); popup.focus();" value='카운터' style='font-size:8pt; width:45; height:17;'>
                <input type='button' onClick="javascript:popup=window.open('/mepa114/admin/int/delete.php?no=<?php echo $BbsTopOk_row['Mlang_bbs_no']?>&bbs=del&table=<?php echo 'Mlang_' . $bbs_table . '_bbs'; ?>&BBS_Table=<?php echo $bbs_table; ?>', 'BBSTopDel','scrollbars=no,resizable=yes,width=400,height=50,top=2000,left=2000'); popup.focus();" value='삭제' style='font-size:8pt; width:30; height:17;'>
                <?php
                echo "</td>";
            }
            /////////////////////////// 관리자 모드 호출 END    //////////////////

            echo "</tr>";

        }
    }

} else {
    // 아무것도 없음
}
?>