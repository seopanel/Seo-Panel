<?php echo showSectionHead($spTextPanel['Seo Tools Manager']); ?>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spTextTools['User Access']?></td>
		<td><?php echo $spText['common']['Reports']?></td>
		<td><?php echo $spText['label']['Cron']?></td>
		<td class="right"><?php echo $spText['common']['Status']?></td>
	</tr>
	<?php
	$colCount = 6; 
	if(count($list) > 0){
		$catCount = count($list);
		foreach($list as $i => $listInfo){
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
		
			if($listInfo['status']){
				$statLabel = $spText['common']["Active"];
			}else{
				$statLabel = $spText['common']["Inactive"];
			}
            $activateLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changestatus&seotool_id={$listInfo['id']}&status={$listInfo['status']}", $statLabel);
            
            $statLabel = ($listInfo['reportgen']) ? $spText['common']["Active"] : $spText['common']["Inactive"]; 
            $reportgenLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changereportgen&seotool_id={$listInfo['id']}&status={$listInfo['reportgen']}", $statLabel);
            
            $statLabel = ($listInfo['cron']) ? $spText['common']["Active"] : $spText['common']["Inactive"]; 
            $cronLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changecron&seotool_id={$listInfo['id']}&status={$listInfo['cron']}", $statLabel);
            
            $accessLabel = ($listInfo['user_access']) ? $spText['common']["Yes"] : $spText['common']["No"]; 
            $accessLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $accessLabel) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changeaccess&seotool_id={$listInfo['id']}&user_access={$listInfo['user_access']}", $accessLabel);
            
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo $listInfo['id'];?></td>
				<td class="td_br_right left"><?php echo $listInfo['name'];?></td>
				<td class="td_br_right"><?php echo $accessLink;?></td>				
				<td class="td_br_right"><?php echo $reportgenLink;?></td>
				<td class="td_br_right"><?php echo $cronLink;?></td>
				<td class="<?php echo $rightBotClass?>" width="100px"><?php echo $activateLink;?></td>
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