<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextDir['Check Directory Status']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#checkDirStatusHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="checkDirStatusHelpModal" tabindex="-1" role="dialog" aria-labelledby="checkDirStatusHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-tasks"></i></div>
				<h5 class="modal-title" id="checkDirStatusHelpModalLabel">About Check Directory Status</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>This bulk re-checks every directory in the global Directory Manager list, optionally filtered to just the Active or Inactive ones, and updates each one's status in place.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This checks the directory sites themselves, not a specific website's submissions. For that, see Websites &gt; Check Submission Status.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" id="stscheck" class="custom-select">
				<option value="0"><?php echo $spText['common']['Inactive']?></option>
				<option value="1"><?php echo $spText['common']['Active']?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('directories.php', 'search_form', 'subcontent', '&sec=startdircheck')" class="btn btn-secondary">
			<?php echo $spText['button']['Proceed']?>
			</a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='alert alert-info'><?php echo $spTextDir['clicktoproceeddirsts']?></p>
</div>
