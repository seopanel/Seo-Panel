<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
include(SP_VIEWPATH.'/settings/_settings_styles.ctp.php');
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Schedule Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#scheduleReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="scheduleReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="scheduleReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-calendar-alt"></i></div>
				<h5 class="modal-title" id="scheduleReportsHelpModalLabel">About Schedule Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Set up a recurring combined report (the same kind Archived Reports shows) to be emailed to you automatically on a schedule, instead of checking in manually.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>A webhook URL below sends the same notifications to a Slack/Discord channel (or any other JSON-accepting endpoint) alongside - or instead of - email: it fires for a report type exactly when its email toggle is on.</span>
				</div>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>The two alert options below are separate from the scheduled report above - they fire immediately, the moment a keyword swings or Site Auditor finds a new critical issue, instead of waiting for the next scheduled send.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
// if saved successfully
if (!empty($success)) {
    showSuccessMsg($spTextReport['sheduledsuccessfully'], false);
}
?>
<form id="schedule_form">
<input type="hidden" name="sec" value="schedule"/>
<div class="aiv-card">
	<?php if(isAdmin()){ ?>
		<div class="sp-settings-row">
			<div class="sp-settings-row-label"><?php echo $spText['common']['User']?>:</div>
			<div class="sp-settings-row-control">
				<select id="user_id" name="user_id" class="custom-select" style="width:150px;" onchange="doLoad('user_id', 'reports.php?sec=schedule', 'content')">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $repSetInfo['user_id']){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>
					<?php }?>
				</select>
			</div>
		</div>
	<?php }?>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label"><?php echo $spTextReport['Next report generation time']?>:</div>
		<div class="sp-settings-row-control"><?php echo $nextReportTime?></div>
	</div>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label"><?php echo $spTextReport['Reports generation interval']?>:</div>
		<div class="sp-settings-row-control">
			<select name="report_interval" class="custom-select">
				<?php foreach ($scheduleList as $key => $val) {
				    if ($key < SP_SYSTEM_REPORT_INTERVAL) continue;
					$selected = ($key == $reportInterval) ? "selected" : "";
					?>
					<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
					<?php
				}?>
			</select>
		</div>
	</div>
	<?php if (SP_REPORT_EMAIL_NOTIFICATION) {?>
		<div class="sp-settings-row">
			<div class="sp-settings-row-label"><?php echo $spTextReport['Email notification']?>:</div>
			<div class="sp-settings-row-control">
				<?php
				$selected = $repSetInfo['email_notification'] ? 'selected' : '';
				?>
				<select name="email_notification" class="custom-select">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $selected?>><?php echo $spText['common']['Yes']?></option>
				</select>
			</div>
		</div>
	<?php }?>
	<?php if (defined('SP_AI_INSIGHTS_EMAIL_NOTIFICATION') && SP_AI_INSIGHTS_EMAIL_NOTIFICATION) {?>
		<div class="sp-settings-row">
			<div class="sp-settings-row-label"><?php echo $spTextReport['AI Insights email notification'] ?? 'AI Insights email notification'?>:</div>
			<div class="sp-settings-row-control">
				<?php
				$aiInsightsSelected = $repSetInfo['ai_insights_email_notification'] ? 'selected' : '';
				?>
				<select name="ai_insights_email_notification" class="custom-select">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $aiInsightsSelected?>><?php echo $spText['common']['Yes']?></option>
				</select>
			</div>
		</div>
	<?php }?>
	<?php if (defined('SP_AI_VISIBILITY_EMAIL_NOTIFICATION') && SP_AI_VISIBILITY_EMAIL_NOTIFICATION) {?>
		<div class="sp-settings-row">
			<div class="sp-settings-row-label"><?php echo $spTextReport['AI Visibility email notification'] ?? 'AI Visibility email notification'?>:</div>
			<div class="sp-settings-row-control">
				<?php
				$aiVisibilitySelected = $repSetInfo['ai_visibility_email_notification'] ? 'selected' : '';
				?>
				<select name="ai_visibility_email_notification" class="custom-select">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $aiVisibilitySelected?>><?php echo $spText['common']['Yes']?></option>
				</select>
			</div>
		</div>
	<?php }?>
	<?php if (defined('SP_AI_PERCEPTION_EMAIL_NOTIFICATION') && SP_AI_PERCEPTION_EMAIL_NOTIFICATION) {?>
		<div class="sp-settings-row">
			<div class="sp-settings-row-label"><?php echo $spTextReport['AI Perception email notification'] ?? 'AI Perception email notification'?>:</div>
			<div class="sp-settings-row-control">
				<?php
				$aiPerceptionSelected = $repSetInfo['ai_perception_email_notification'] ? 'selected' : '';
				?>
				<select name="ai_perception_email_notification" class="custom-select">
					<option value="0"><?php echo $spText['common']['No']?></option>
					<option value="1" <?php echo $aiPerceptionSelected?>><?php echo $spText['common']['Yes']?></option>
				</select>
			</div>
		</div>
	<?php }?>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label">
			<?php echo $spTextReport['Webhook URL'] ?? 'Webhook URL'?>:
			<small><?php echo $spTextReport['Webhook URL note'] ?? 'Sends a short summary (and a link back here) to this URL every time an email notification above would be sent. Leave blank to disable.'?></small>
		</div>
		<div class="sp-settings-row-control">
			<input type="text" name="webhook_url" value="<?php echo htmlspecialchars($repSetInfo['webhook_url'] ?? '', ENT_QUOTES)?>" class="form-control" placeholder="https://hooks.slack.com/services/...">
		</div>
	</div>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label"><?php echo $spTextReport['Platform'] ?? 'Platform'?>:</div>
		<div class="sp-settings-row-control">
			<?php $webhookPlatform = $repSetInfo['webhook_platform'] ?? 'slack'; ?>
			<select name="webhook_platform" class="custom-select" style="width:200px;">
				<option value="slack" <?php echo $webhookPlatform == 'slack' ? 'selected' : ''?>>Slack</option>
				<option value="discord" <?php echo $webhookPlatform == 'discord' ? 'selected' : ''?>>Discord</option>
				<option value="generic" <?php echo $webhookPlatform == 'generic' ? 'selected' : ''?>>Generic / Custom (JSON)</option>
			</select>
			<div class="mt-2">
				<a href="javascript:void(0);" onclick="checkWebhookConnection('reports.php', 'show_webhook_conn_res')" class="btn btn-info"><?php echo $spTextReport['Send Test Notification'] ?? 'Send Test Notification'?> &gt;&gt;</a>
			</div>
			<div id="show_webhook_conn_res" class="mt-2"></div>
		</div>
	</div>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label">
			<?php echo $spTextReport['Alert on rank changes'] ?? 'Alert on rank changes'?>:
			<small><?php echo $spTextReport['Alert on rank changes note'] ?? 'Sends an immediate webhook alert whenever a keyword moves by at least this many positions, or enters/drops out of the ranked results - in addition to, not instead of, the scheduled report above.'?></small>
		</div>
		<div class="sp-settings-row-control">
			<?php $rankAlertSelected = !empty($repSetInfo['rank_alert_notification']) ? 'selected' : ''; ?>
			<select name="rank_alert_notification" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $rankAlertSelected?>><?php echo $spText['common']['Yes']?></option>
			</select>
			<?php echo $spTextReport['Position threshold'] ?? 'Position threshold'?>:
			<input type="number" min="1" name="rank_alert_threshold" value="<?php echo intval($repSetInfo['rank_alert_threshold'] ?? 5)?>" class="form-control" style="width:80px;display:inline-block;">
		</div>
	</div>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label">
			<?php echo $spTextReport['Alert on competitor rank changes'] ?? 'Alert on competitor rank changes'?>:
			<small><?php echo $spTextReport['Alert on competitor rank changes note'] ?? 'Sends an immediate webhook alert whenever a tracked competitor\'s rank moves by at least the position threshold above, or enters/drops out of the ranked results, for one of your keywords.'?></small>
		</div>
		<div class="sp-settings-row-control">
			<?php $competitorRankAlertSelected = !empty($repSetInfo['competitor_rank_alert_notification']) ? 'selected' : ''; ?>
			<select name="competitor_rank_alert_notification" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $competitorRankAlertSelected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</div>
	</div>
	<div class="sp-settings-row">
		<div class="sp-settings-row-label">
			<?php echo $spTextReport['Alert on new critical issues'] ?? 'Alert on new critical issues'?>:
			<small><?php echo $spTextReport['Alert on new critical issues note'] ?? 'Sends an immediate webhook alert when Site Auditor finds a page newly broken, deindexed, not mobile-friendly, not secure, or blocked by robots.'?></small>
		</div>
		<div class="sp-settings-row-control">
			<?php $siteIssueAlertSelected = !empty($repSetInfo['site_issue_alert_notification']) ? 'selected' : ''; ?>
			<select name="site_issue_alert_notification" class="custom-select">
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $siteIssueAlertSelected?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</div>
	</div>
</div>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('reports.php?sec=schedule', 'content')" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Cancel']?>
         	</a> &nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('reports.php', 'schedule_form', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
