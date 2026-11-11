<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Seo Tools Manager']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#toolsManagerHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="toolsManagerHelpModal" tabindex="-1" role="dialog" aria-labelledby="toolsManagerHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-toolbox"></i></div>
				<h5 class="modal-title" id="toolsManagerHelpModalLabel">About Seo Tools Manager</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Turn individual SEO tools (Rank Checker, Site Auditor, Backlinks Checker, and the rest) on or off for this install, and control whether each one runs via cron and/or appears in the Tools menu.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Disabling a tool here hides it from every user's menu - it doesn't delete any data already collected.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $("table").tablesorter({
		sortList: [[2,0]]
    });
});
</script>

<table class="list tablesorter">
	<thead>
		<tr class="listHead">
			<td><?php echo $spText['common']['Name']?></td>
			<td><?php echo $spText['common']['Priority']?></td>
			<td><?php echo $spTextTools['User Access']?></td>
			<td><?php echo $spText['common']['Reports']?></td>
			<td><?php echo $spText['label']['Cron']?></td>
			<td><?php echo $spText['common']['Status']?></td>
			<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($list as $listInfo) {
			$statLabel = $listInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"];
			$btnClass = $listInfo['status'] ? "btn btn-success" : "btn btn-danger";
			$activateLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel, $btnClass) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changestatus&seotool_id={$listInfo['id']}&status={$listInfo['status']}", $statLabel, $btnClass);

			$statLabel = ($listInfo['reportgen']) ? $spText['common']["Active"] : $spText['common']["Inactive"];
			$btnClass = $listInfo['reportgen'] ? "btn btn-success" : "btn btn-danger";
			$reportgenLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel, $btnClass) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changereportgen&seotool_id={$listInfo['id']}&status={$listInfo['reportgen']}", $statLabel, $btnClass);

			$statLabel = ($listInfo['cron']) ? $spText['common']["Active"] : $spText['common']["Inactive"];
			$btnClass = $listInfo['cron'] ? "btn btn-success" : "btn btn-danger";
            $cronLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $statLabel, $btnClass) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changecron&seotool_id={$listInfo['id']}&status={$listInfo['cron']}", $statLabel, "btn btn-danger", $btnClass);

            $accessLabel = ($listInfo['user_access']) ? $spText['common']["Yes"] : $spText['common']["No"];
            $btnClass = $listInfo['user_access'] ? "btn btn-success" : "btn btn-danger";
            $accessLink = SP_DEMO ? scriptAJAXLinkHref('demo', '', "", $accessLabel, $btnClass, $btnClass) : scriptAJAXLinkHref('seo-tools-manager.php', 'content', "sec=changeaccess&seotool_id={$listInfo['id']}&user_access={$listInfo['user_access']}", $accessLabel, $btnClass);
			?>
			<tr>
				<td><?php echo $listInfo['name'];?></td>
				<td><?php echo $listInfo['priority'];?></td>
				<td class="text-center"><?php echo $accessLink;?></td>
				<td class="text-center"><?php echo $reportgenLink;?></td>
				<td class="text-center"><?php echo $cronLink;?></td>
				<td class="text-center"><?php echo $activateLink;?></td>
				<td>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
						onchange="doAction('seo-tools-manager.php', 'content', 'pid=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>
