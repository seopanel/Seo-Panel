<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Cron Command']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#proxyCronHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="proxyCronHelpModal" tabindex="-1" role="dialog" aria-labelledby="proxyCronHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-terminal"></i></div>
				<h5 class="modal-title" id="proxyCronHelpModalLabel">About Cron Command</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>The shell command to add to your server's crontab so <code>proxycheckercron.php</code> runs automatically, keeping every proxy's Active/Inactive status up to date without visiting this page manually.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<table width="600px" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th style="text-align: left;font-size: 14px;padding-left: 10px;"><?php echo $spTextPanel['Add following command to your cron tab']?></th>		
	</tr>
	<tr>
		<td valign="middle">
			<p class="note" style="padding-top: 6px;font-size: 15px;width: 600px;">
			<?php
			$command = "0 0,6 * * * php ".SP_ABSPATH."/proxycheckercron.php";
			highlight_string($command); 
			?>
			</p>
		</td>
	</tr>
</table>