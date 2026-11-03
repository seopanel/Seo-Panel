<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Website Manager']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#webManagerHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="webManagerHelpModal" tabindex="-1" role="dialog" aria-labelledby="webManagerHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-globe"></i></div>
				<h5 class="modal-title" id="webManagerHelpModalLabel">About Website Manager</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>The master list of every website tracked in this install. Every other tool - Rank Checker, Site Auditor, AI Visibility, and the rest - works against the websites added here.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Edit, activate/deactivate or delete a website from here. Deleting one also removes its keywords and saved history across every tool.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form name="listform" id="listform">
<?php $submitLink = "scriptDoLoadPost('websites.php', 'listform', 'content')";?>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td>
			<input type="text" name="search_name" value="<?php echo htmlentities($info['search_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
		</td>
		<?php if(!empty($isAdmin)){ ?>
			<th class="pl-4"><?php echo $spText['common']['User']?>: </th>
			<td>
				<select name="userid" id="userid" onchange="<?php echo $submitLink?>" class="custom-select">
					<option value="">-- <?php echo $spText['common']['Select']?> --</option>
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] === $userId){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
		<?php }?>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($statusList as $key => $val){?>
					<?php if(isset($info['stscheck']) && $info['stscheck'] === $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td class="pl-4">
			<a href="javascript:void(0);" onclick="<?php echo $submitLink; ?>" class="btn btn-secondary">
				<?php echo $spText['button']['Search']?>
			</a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Website']?></td>
		<?php if(!empty($isAdmin)){ ?>		
			<td><?php echo $spText['common']['User']?></td>
		<?php } ?>
		<td><?php echo $spText['common']['Url']?></td>
		<td><?php echo $spTextWeb['Google Analytics Property']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 15%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = empty($isAdmin) ? 6 : 7; 
	if(count($list) > 0){
		foreach($list as $listInfo) {
            $websiteLink = scriptAJAXLinkHref('websites.php', 'content', "sec=edit&websiteId={$listInfo['id']}", "{$listInfo['name']}");
			?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="text-left"><?php echo $websiteLink?></td>
				<?php if(!empty($isAdmin)){ ?>
					<td><?php echo $listInfo['username']?></td>
				<?php } ?>
				<td class="text-left"><?php echo wordwrap(htmlspecialchars($listInfo['url']), 70, "<br>", true); ?></td>
				<td>
					<?php echo !empty($listInfo['analytics_view_id']) ? $propertyList[$listInfo['analytics_view_id']] : ""?>
				</td>
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
						onchange="doAction('websites.php', 'content', 'websiteId=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>&userid=<?php echo $userId?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="addToWebmasterTools"><?php echo $spTextWeb['Add to Webmaster Tools']?></option>
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
    $actFun = "confirmSubmit('websites.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('websites.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('websites.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}   
?>
<table class="actionSec mt-3">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('websites.php', 'content', 'sec=new')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['New Website']?>
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