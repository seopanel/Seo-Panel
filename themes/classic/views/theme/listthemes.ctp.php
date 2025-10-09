<?php echo showSectionHead($spTextPanel['Themes Manager']); ?>
<?php
if(!empty($msg)){
	echo $error ? showErrorMsg($msg, false) : showSuccessMsg($msg, false);
}
?>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['label']['Theme']?></td>
		<td><?php echo $spText['label']['Author']?></td>
		<td><?php echo $spText['common']['Website']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td><?php echo $spText['label']['Installation']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 6;
	if(count($list) > 0){
		foreach($list as $i => $listInfo){

            $activateLink = SP_WEBPATH."/admin-panel.php?menu_selected=themes-manager&start_script=themes-manager&sec=activate&theme_id={$listInfo['id']}&pageno=$pageNo";
			if($listInfo['status']){
				$statLabel = "<span class='badge badge-success py-2 px-3 text-light'>Current</span>";
			}else{
				$statLabel = '<a href="'.$activateLink.'" class="btn btn-success">'.$spText['common']["Activate"].'</a>';
			}

			?>
			<tr>
				<td>
					<a href="javascript:void(0);" onclick="scriptDoLoad('themes-manager.php?sec=listinfo&pid=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'content')"><?php echo $listInfo['name']?> <?php echo $listInfo['version']?></a>
				</td>
				<td><?php echo $listInfo['author']?></td>
				<td><a href="<?php echo $listInfo['website']?>" target="_blank"><?php echo $listInfo['website']?></a></td>
				<td class="text-center"><?php echo $statLabel; ?></td>
				<td class="text-center">
					<?php echo $listInfo['installed'] ? "<span class='badge badge-success py-2 px-3 text-light'>Success</span>" : "<span class='badge badge-danger py-2 px-3 text-light'>Failed</span>"; ?>
				</td>
				<td>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
						onchange="doAction('themes-manager.php?pageno=<?php echo $pageNo?>', 'content', 'pid=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
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
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a href="<?php echo SP_THEMESITE?>" class="btn btn-info" target="_blank"><?php echo $spTextTheme['Download Seo Panel Themes']?> &gt;&gt;</a>
    	</td>
	</tr>
</table>
