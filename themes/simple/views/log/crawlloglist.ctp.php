<?php 
echo showSectionHead($spTextPanel["Crawl Log Manager"]);
$searchFun = "scriptDoLoadPost('log.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>"></td>
		<th><?php echo $spText['label']['Report Type']?>: </th>
		<td>
			<select name="crawl_type" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php				
				foreach ($crawlTypeList as $cInfo) {
					$selectType = ($cInfo['crawl_type'] == $crawlType) ? "selected" : "";
					?>
					<option value="<?php echo $cInfo['crawl_type']; ?>" <?php echo $selectType; ?> ><?php echo $cInfo['crawl_type']; ?></option>
					<?php
				}	
				?>
			</select>
		</td>
	</tr>
	<tr>
		<th width="100px;"><?php echo $spText['common']['Period']?>:</th>
    	<td width="236px">
    		<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $fromTime?>" name="from_time"/> 
    		<img align="bottom" onclick="displayDatePicker('from_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/> 
    		<input type="text" style="width: 80px;margin-right:0px;" value="<?php echo $toTime?>" name="to_time"/> 
    		<img align="bottom" onclick="displayDatePicker('to_time', false, 'ymd', '-');" src="<?php echo SP_IMGPATH?>/cal.gif"/>
    	</td>	
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php
			$this->data['onChange'] = $searchFun;
			$this->data['seNull'] = true;
			echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
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
		<th><?php echo $spText['label']['Proxy']; ?>: </th>
		<td>
			<select name="proxy_id" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php				
				foreach ($proxyList as $proxyInfo) {
					$selectType = ($proxyInfo['proxy_id'] == $proxyId) ? "selected" : "";
					?>
					<option value="<?php echo $proxyInfo['proxy_id']; ?>" <?php echo $selectType; ?> ><?php echo $proxyInfo['proxy'].":".$proxyInfo['port']; ?></option>
					<?php
				}	
				?>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>

<br><br>
<b>Current Time:</b> <?php echo date("Y-m-d H:i:s <b>T(P)</b>"); ?>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>		
		<td width="80px"><?php echo $spText['label']['Report Type']?></td>		
		<td><?php echo $spText['label']['Reference']?></td>	
		<td><?php echo $spText['label']['Subject']?></td>
		<td><?php echo $spText['common']['Details']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td><?php echo $spText['label']['Updated']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 9; 
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
            
            // if from popup
			if ($fromPopUp) {
            	$logLink = scriptAJAXLinkHref('log.php', 'content', "sec=crawl_log_details&id=".$listInfo['id'], $listInfo['id']);
            } else {
				$logLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log_details&id=".$listInfo['id'], $listInfo['id']);
			}
            
            // crawl log is for keyword
            if ($listInfo['crawl_type'] == 'keyword') {
				
				// if ref is is integer find keyword name
				if (!empty($listInfo['keyword'])) {
					$listInfo['ref_id'] = $listInfo['keyword'];
				}
				
				// find search engine info
				if (preg_match("/^\d+$/", $listInfo['subject'])) {
					$seCtrler = new SearchEngineController();
					$seInfo = $seCtrler->__getsearchEngineInfo($listInfo['subject']);
					$listInfo['subject'] = $seInfo['domain'];
				}

			}
            
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="td_br_right"><?php echo $logLink?></td>
				<td class="td_br_right left"><?php echo $listInfo['crawl_type']?></td>
				<td class="td_br_right left"><?php echo $listInfo['ref_id']?></td>
				<td class="td_br_right left"><?php echo $listInfo['subject']?></td>
				<td class="td_br_right left"><?php echo $listInfo['log_message']?></td>
				<td class="td_br_right">
					<?php 
					if ($listInfo['crawl_status']) {
						echo "<b class='success'>{$spText['label']['Success']}</b>";
					} else {
						echo "<b class='error'>{$spText['label']['Fail']}</b>";
					}
					?>
				</td>
				<td class="td_br_right"><?php echo $listInfo['crawl_time']?></td>
				<td class="<?php echo $rightBotClass?>" width="100px">
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('log.php', 'content', 'id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?><?php echo $urlParams?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="delete_crawl_log"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
</table>
<?php
if (SP_DEMO) {
    $clearAllFun = $delFun = "alertDemoMsg()";
} else {
    $delFun = "confirmSubmit('log.php', 'listform', 'content', '&sec=delete_all_crawl_log&pageno=$pageNo')";
    $clearAllFun = "confirmLoad('log.php', 'content', '&sec=clear_all_log')";
}   
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;" class="left">
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']['Delete']?>
         	</a>&nbsp;&nbsp;
         	<?php if (empty($fromPopUp)) {?>
	         	<a onclick="<?php echo $clearAllFun?>" href="javascript:void(0);" class="actionbut">
	         		<?php echo $spTextLog['Clear All Logs']?>
	         	</a>
	         <?php }?>
    	</td>
	</tr>
</table>
</form>
