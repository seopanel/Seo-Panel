<?php echo showSectionHead($spTextTools['Backlinks Reports']); ?>
<form id='search_form'>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" style='width:190px;' onchange="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=reports')">
				<?php foreach ($websiteList as $websiteInfo) {
    ?>
					<?php if ($websiteInfo['id'] == $websiteId) {
    ?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php 
} else {
    ?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php 
}
    ?>
				<?php 
}?>
			</select>
		</td>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/> 
			<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/> 
			<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/> 
			<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
		</td>
		<td colspan="2"><a href="javascript:void(0);" onclick="scriptDoLoadPost('backlinks.php', 'search_form', 'content', '&sec=reports')" class="actionbut"><?php echo $spText['button']['Show Records']?></a></td>
	</tr>
</table>
</form>

<?php
    if (empty($websiteId)) {
        ?>
		<p class='note error'><?php echo $spText['common']['No Records Found']?>!</p>
		<?php
        exit;
    }
?>

<div id='subcontent'>
<table width="100%" border="0" cellspacing="0" cellpadding="2px;" class="list" align='center'>
	<tr>
	<td width='33%'>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Date']?></td>
		<td>Google</td>
		<td>Alexa</td>
		<td class="right">Bing</td>
	</tr>
	<?php
    $colCount = 4;
    if (count($list) > 0) {
        $catCount = count($list);
        $i = 0;
        foreach ($list as $listInfo) {
            $class = ($i % 2) ? "blue_row" : "white_row";
            if ($catCount == ($i + 1)) {
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            } else {
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            ?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo date('Y-m-d', $listInfo['result_time']);
            ?></td>
				<td class='td_br_right' style='text-align:left;padding-left:40px;'><a href="<?php echo $directLinkList['google']?>" target="_blank"><?php echo $listInfo['google'].'</a> '. $listInfo['rank_diff_google']?></td>
				<td class='td_br_right' style='text-align:left;padding-left:40px;'><a href="<?php echo $directLinkList['alexa']?>" target="_blank"><?php echo $listInfo['alexa'].'</a> '. $listInfo['rank_diff_alexa']?></td>
				<td class='<?php echo $rightBotClass?>' style='text-align:left;padding-left:40px;'><a href="<?php echo $directLinkList['msn']?>" target="_blank"><?php echo $listInfo['msn'].'</a> '. $listInfo['rank_diff_msn']?></td>
			</tr>
			<?php
            $i++;
        }
    } else {
        echo showNoRecordsList($colCount-2);
    }
    ?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
	</table>
	</td>
	</tr>
</table>
</div>