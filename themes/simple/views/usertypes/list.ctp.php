<form name="listform" id="listform">
<?php echo showSectionHead($spTextPanel['User Type Manager']); ?>
<?php
if ($isPluginSubsActive) {
	$currencySymbol = $currencyList[SP_PAYMENT_CURRENCY]['symbol'];
} else {
	?>
	<div id="topnewsbox">
		<a class="btn btn-info" href="<?php echo SP_MAIN_SITE?>/plugin/l/65/membership-subscription/" target="_blank">
			<?php echo $spTextSubscription['click-activate-pay-plugin']; ?> &gt;&gt;
		</a>
	</div>
	<?php
}
?>
<?php echo $pagingDiv?>

<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['User Type']?></td>
		<td><?php echo $spText['label']['Description']?></td>
		<td><?php echo $spText['common']['Keywords Count']?></td>
		<td><?php echo $spText['common']['Websites Count']?></td>
		<td><?php echo $spTextSubscription['Social Media Link Count']?></td>
		<td><?php echo $spTextSubscription['Review Link Count']?></td>
		<td><?php echo $spTextSubscription['Directory Submit Limit']?></td>
		<?php if ($isPluginSubsActive) {?>
			<td><?php echo $spText['common']['Price']?></td>
			<td><?php echo $spTextSubscription['Access Type']?></td>
		<?php }?>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = $isPluginSubsActive ? 13 : 11;
	if(count($list) > 0){
		foreach($list as $i => $listInfo){
            $userTypeLink = scriptAJAXLinkHref('user-types-manager.php', 'content', "sec=edit&userTypeId={$listInfo['id']}", "{$listInfo['user_type']}")
			?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $listInfo['id']?></td>
				<td><?php echo $userTypeLink?></td>
				<td><?php echo $listInfo['description']?></td>
				<td><?php echo $listInfo['keywordcount']?></td>
				<td><?php echo $listInfo['websitecount']?></td>
				<td><?php echo $listInfo['social_media_link_count']?></td>
				<td><?php echo $listInfo['review_link_count']?></td>
				<td><?php echo $listInfo['directory_submit_limit']?></td>
				<?php if ($isPluginSubsActive) {?>
					<td>
						<?php echo !empty($listInfo['price']) ? $currencySymbol . $listInfo['price'] : ""; ?>
					</td>
					<td><?php echo $accessTypeList[$listInfo['access_type']]; ?></td>
				<?php }?>
				<td class="text-center">
					<?php echo showStatusBadge($listInfo['status']);?>
				</td>
				<td>
					<?php
						if($listInfo['status']){
							$statVal = "Inactivate";
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statVal = "Activate";
							$statLabel = $spText['common']["Activate"];
						}
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
						onchange="doAction('user-types-manager.php', 'content', 'userTypeId=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="<?php echo $statVal?>"><?php echo $statLabel?></option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}

	} else {
	    echo showNoRecordsList($colCount-2);
	}
	?>
</table>
<?php
if (SP_DEMO) {
	$actFun = $inactFun = $delFun = "alertDemoMsg()";
} else {
	$actFun = "confirmSubmit('user-types-manager.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
	$inactFun = "confirmSubmit('user-types-manager.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
	$delFun = "confirmSubmit('user-types-manager.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('user-types-manager.php', 'content', 'sec=new')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['New User Type']?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-success">
         		<?php echo $spText['common']["Activate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
