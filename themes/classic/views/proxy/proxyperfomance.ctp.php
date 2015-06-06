<?php
echo showSectionHead($spTextPanel["Proxy Perfomance"]);
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<input type="hidden" name="sec" value="perfomance">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>"></td>
		<th><?php echo $spText['common']['Period']?>:</th>
    	<td width="236px">
    		<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/>
    		<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    		<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/>
    		<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    	</td>
		<th><?php echo $spText['label']['Order By']?>: </th>
		<td>
			<select name="order_by" onchange="<?php echo $searchFun?>">
				<?php
				$inactCheck = $actCheck = "";
				if ($statVal == 'success') {
				    $actCheck = "selected";
				} elseif($statVal == 'fail') {
				    $inactCheck = "selected";
				}
				?>
				<option value="success" <?php echo $actCheck?> ><?php echo $spText['label']["Success"]?></option>
				<option value="fail" <?php echo $inactCheck?> ><?php echo $spText['label']["Fail"]?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Proxy']?></td>
		<td><?php echo $spTextProxy['Request Count']?></td>
		<td><?php echo $spText['label']['Success']?></td>
		<td class="right"><?php echo $spText['label']['Fail']?></td>
	</tr>
	<?php
	$colCount = 6;
	if (count($list) > 0) {
		$catCount = count($list);
		foreach ($list as $i => $listInfo) {
			$class = ($i % 2) ? "blue_row" : "white_row";
            if ($catCount == ($i + 1)) {
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            } else {
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }

            $logLink = scriptAJAXLinkHrefDialog('proxy.php', 'content', "sec=edit&proxyId=".$listInfo['proxy_id'], $listInfo['proxy'].":".$listInfo['port']);
            $countLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=&proxy_id=".$listInfo['proxy_id'], $listInfo['count'], '', 'OnClick', 1000);
			$successLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=success&proxy_id=".$listInfo['proxy_id'], $listInfo['success'], '', 'OnClick', 1000);
			$failLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=fail&proxy_id=".$listInfo['proxy_id'], $listInfo['fail'], '', 'OnClick', 1000);
            ?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="td_br_right"><?php echo $listInfo['proxy_id']?></td>
				<td class="td_br_right left"><?php echo $logLink?></td>
				<td class="td_br_right"><?php echo $countLink?></td>
				<td class="td_br_right"><?php echo $successLink?></td>
				<td class="<?php echo $rightBotClass?>"><?php echo $failLink?></td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);
	}
	?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
</table>
</form>
