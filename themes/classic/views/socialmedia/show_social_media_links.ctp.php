<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$searchFun = "scriptDoLoadPost('$pageScriptPath', 'searchForm', 'content')";
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Social Media Links']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#smLinksHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="smLinksHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('smLinksHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-share-alt"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Social Media Links</div>
		<div class="aiv-intro-desc">Register your websites' social profile links here - this is the setup step before any check or report has data to show. <a href="javascript:void(0);" data-toggle="modal" data-target="#smLinksHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="smLinksHelpModal" tabindex="-1" role="dialog" aria-labelledby="smLinksHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-share-alt"></i></div>
				<h5 class="modal-title" id="smLinksHelpModalLabel">About Social Media Links</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Lists the social media profile links you've registered per website (Facebook, Twitter, Instagram, LinkedIn, Pinterest, YouTube, Reddit). Add, edit, delete or activate/deactivate from here.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>A link must be created here before the Reports or Quick Checker pages have anything to show for it. Link counts are capped by your account's plan.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<form name="searchForm" id="searchForm" onsubmit="return false;">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td><input type="text" name="name" value="<?php echo htmlentities($searchInfo['name'], ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $searchInfo['website_id']){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th><?php echo $spText['label']['Type']?>: </th>
		<td>
			<select name="type" id="type" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<?php if($serviceName == $searchInfo['type']){?>
						<option value="<?php echo $serviceName?>" selected><?php echo $serviceInfo['label']?></option>
					<?php }else{?>
						<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$statusList = ['active' => $spText['common']["Active"], 'inactive' => $spText['common']["Inactive"]];
				foreach ($statusList as $statusVal => $statuslabel) {
				    $selectedVal = ($statusVal == $searchInfo['status']) ? "selected" : "";
				    ?>
				    <option value="<?php echo $statusVal?>" <?php echo $selectedVal?> ><?php echo $statuslabel?></option>
				    <?php
				}
				?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php echo $pagingDiv?>

<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Name']?></th>
		<th><?php echo $spText['label']['Type']?></th>
    	<th><?php echo $spText['common']['Link']?></th>
    	<th><?php echo $spText['common']['Website']?></th>
		<th><?php echo $spText['common']['Status']?></th>
		<th><?php echo $spText['common']['Action']?></th>
	</tr>
	<?php
	if(count($list) > 0) {
		foreach($list as $i => $listInfo){
		    $projectLink = scriptAJAXLinkHref($pageScriptPath, 'content', "sec=edit&id={$listInfo['id']}", htmlspecialchars($listInfo['name']));
			?>
			<tr>
				<td><?php echo $projectLink?></td>
				<td>
					<i class="fab fa-<?php echo strtolower($serviceList[$listInfo['type']]['label'])?>"></i>
					<?php echo $serviceList[$listInfo['type']]['label']?>
				</td>
				<td>
					<?php
					if ($listInfo['type'] == "linkedin") {
					    echo htmlspecialchars($serviceList[$listInfo['type']]['show_url'] . "/" . $listInfo['url']);
					} else {
					   echo htmlspecialchars($listInfo['url']);
					}
					?>
				</td>
				<td><?php echo htmlspecialchars($listInfo['website_name'])?></td>
				<td><?php echo showStatusBadge($listInfo['status']);?></td>
				<td>
					<?php
					if ($listInfo['status']) {
						$statAction = "Inactivate";
						$statLabel = $spText['common']["Inactivate"];
					} else {
						$statAction = "Activate";
						$statLabel = $spText['common']["Activate"];
					}
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
						onchange="doAction('<?php echo $pageScriptPath?>', 'content', 'id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="<?php echo $statAction?>"><?php echo $statLabel?></option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="6"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
		<?php
	} 
	?>
</table>

<table class="actionSec mt-2">
	<tr>
		<td>
			<a onclick="scriptDoLoad('<?php echo $pageScriptPath?>', 'content', 'sec=newSocialMediaLink')" href="javascript:void(0);" class="btn btn-primary">
				<?php echo $spTextSMC['New Social Media Link']?>
			</a>
		</td>
	</tr>
</table>