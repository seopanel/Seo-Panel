<?php echo showSectionHead($spTextPanel['Themes Manager']); ?>
<?php 
if(!empty($msg)){ 
	echo $error ? showErrorMsg($msg, false) : showSuccessMsg($msg, false); 
} 
?>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['label']['Theme']?></td>		
		<td><?php echo $spText['label']['Author']?></td>		
		<td><?php echo $spText['common']['Website']?></td>
		<td><?php echo $spText['common']['Status']?></td>		
		<td><?php echo $spText['label']['Installation']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
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
		
            $activateLink = SP_WEBPATH."/admin-panel.php?menu_selected=themes-manager&start_script=themes-manager&sec=activate&theme_id={$listInfo['id']}&pageno=$pageNo";
			if($listInfo['status']){
				$statLabel = "<font class='green'><b>".$spText['label']["Current"]."</b></font>";
			}else{
				$statLabel = '<a href="'.$activateLink.'">'.$spText['common']["Activate"].'</a>';;
			}
            
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>">
					<a href="javascript:void(0);" onclick="scriptDoLoad('themes-manager.php?sec=listinfo&pid=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'content')"><?php echo $listInfo['name']?> <?php echo $listInfo['version']?></a>
				</td>				
				<td class="td_br_right left"><?php echo $listInfo['author']?></td>
				<td class="td_br_right left"><a href="<?php echo $listInfo['website']?>" target="_blank"><?php echo $listInfo['website']?></a></td>
				<td class="td_br_right"><?php echo $statLabel; ?></td>
				<td class="td_br_right"><?php echo $listInfo['installed'] ? "<font class='green'>Success</font>" : "<font class='red'>Failed</font>"; ?></td>
				<td class="<?php echo $rightBotClass?>" width="100px">
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('themes-manager.php?pageno=<?php echo $pageNo?>', 'content', 'pid=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="upgrade"><?php echo $spText['label']['Upgrade']?></option>
						<option value="reinstall"><?php echo $spText['label']['Re-install']?></option>
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
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a href="<?php echo SP_THEMESITE?>" class="actionbut" target="_blank"><?php echo $spTextTheme['Download Seo Panel Themes']?> &gt;&gt;</a>
    	</td>
	</tr>
</table>