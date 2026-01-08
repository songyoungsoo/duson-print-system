<table border="0" align="center" width="180" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
    <tr>
        <td align="center">
            <?php
            $SwfMk_Width = "180";
            $SwfMk_Height = "135";
            $SwfMk_Url = "/swf/left_inserted.swf";
            ?>
            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="<?= $SwfMk_Width ?>" height="<?= $SwfMk_Height ?>">
                <param name="movie" value="<?= $SwfMk_Url ?>">
                <param name="quality" value="high">
                <param name="wmode" value="transparent">
                <param name="menu" value="false">
                <embed src="<?= $SwfMk_Url ?>" style="position:absolute;left:0;top:20;width:500;height:50;border:gray 1 solid;z-index:-10;" menu="false" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="<?= $SwfMk_Width ?>" height="<?= $SwfMk_Height ?>">
                </embed>
            </object>
        </td>
    </tr>
    <tr>
        <td width="180"><img src="/1235.gif" width="1" height="15"></td>
    </tr>
    <?php
    include "$LeftIncludeDir/ConDb.php";
    if ($ConDb_A) {
        $CATEGORY_LIST_script = explode(":", $ConDb_A);
        foreach ($CATEGORY_LIST_script as $k => $category) {
            $List_Ttable = $k;
            include "$LeftIncludeDir/ConDb.php";
            ?>
            <tr>
                <td width="180" height="27" valign="middle" onMouseOver="this.style.background='#F2F2F2';" onMouseOut="this.style.background='#FFFFFF';" bgcolor='#FFFFFF'>
                    &nbsp;&nbsp;&nbsp;&nbsp;<img src='/img/LeftTop12.gif' width=3 height=5 align='absmiddle'>&nbsp;
                    <a href='/mlangprintauto/<?= $View_TtableB ?>/index.php' class='LeftMenu'><?= $View_TtableC ?></a>
                </td>
            </tr>
            <tr>
                <td width="180" height="1" bgcolor='#E4E4E4'><img src='/1235.gif' width='1' height='1'></td>
            </tr>
            <?php
        }
    }
    ?>
</table>