<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Platforms'] ?? 'AI Platforms'); ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-layer-group"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['AI Platforms'] ?? 'AI Platforms'?></div>
		</div>
	</div>
	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['referralsourcenotice'] ?? 'Only enable this for hostnames real visitors click through from. Vendor domains used solely for bot user-agent matching (e.g. google.com for Google-Extended) must stay off, or ordinary traffic from that domain will be miscounted as an AI referral.'?></span>
	</div>

	<div style="overflow-x:auto;">
	<table class="aiv-table">
		<tr>
			<th><?php echo $spTextAIV['Platform code'] ?? 'Platform code'?></th>
			<th><?php echo $spTextAIV['Hostname'] ?? 'Hostname'?></th>
			<th><?php echo $spTextAIV['Display name'] ?? 'Display name'?></th>
			<th><?php echo $spTextAIV['Bot UA pattern'] ?? 'Bot UA pattern'?></th>
			<th><?php echo $spTextAIV['Robots.txt User-agent token'] ?? 'Robots.txt User-agent token'?></th>
			<th><?php echo $spTextAIV['Verify suffix'] ?? 'Verify suffix'?></th>
			<th style="white-space:nowrap;"><?php echo $spText['common']['Active'] ?? 'Active'?></th>
			<th style="white-space:nowrap;"><?php echo $spTextAIV['Referral source'] ?? 'Referral source'?></th>
			<th><?php echo $spText['common']['Action'] ?? 'Action'?></th>
		</tr>
		<?php if (!empty($platformList)) { ?>
			<?php foreach ($platformList as $platformInfo) { ?>
				<tr>
					<td><?php echo htmlspecialchars($platformInfo['platform'])?></td>
					<td><?php echo htmlspecialchars($platformInfo['hostname'])?></td>
					<td><?php echo htmlspecialchars($platformInfo['display_name'])?></td>
					<td><?php echo htmlspecialchars($platformInfo['bot_ua_pattern'] ?? '')?></td>
					<td><?php echo htmlspecialchars($platformInfo['robots_user_agent_token'] ?? '')?></td>
					<td><?php echo htmlspecialchars($platformInfo['verify_suffix'] ?? '')?></td>
					<td>
						<form id="toggle_active_<?php echo $platformInfo['id']?>" onsubmit="return false;">
							<input type="hidden" name="sec" value="toggle-platform">
							<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
							<input type="hidden" name="field" value="is_active">
							<label class="aiv-switch">
								<input type="checkbox" <?php echo $platformInfo['is_active'] ? 'checked' : ''?>
									onchange="scriptDoLoadPost('aivisibility.php', 'toggle_active_<?php echo $platformInfo['id']?>', 'content')">
								<span class="aiv-switch-track"></span>
							</label>
						</form>
					</td>
					<td>
						<form id="toggle_ref_<?php echo $platformInfo['id']?>" onsubmit="return false;">
							<input type="hidden" name="sec" value="toggle-platform">
							<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
							<input type="hidden" name="field" value="is_referral_source">
							<label class="aiv-switch">
								<input type="checkbox" <?php echo $platformInfo['is_referral_source'] ? 'checked' : ''?>
									onchange="scriptDoLoadPost('aivisibility.php', 'toggle_ref_<?php echo $platformInfo['id']?>', 'content')">
								<span class="aiv-switch-track"></span>
							</label>
						</form>
					</td>
					<td>
						<a href="javascript:void(0);"
							onclick="confirmSubmit('aivisibility.php', 'delete_<?php echo $platformInfo['id']?>', 'content')"
							class="aiv-btn aiv-btn-danger" style="padding:5px 12px;"><?php echo $spText['common']['Delete']?></a>
						<form id="delete_<?php echo $platformInfo['id']?>" onsubmit="return false;">
							<input type="hidden" name="sec" value="delete-platform">
							<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
						</form>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<?php echo showNoRecordsList(9); ?>
		<?php } ?>
	</table>
	</div>
</div>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-plus"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Add Platform'] ?? 'Add Platform'?></div>
		</div>
	</div>
	<form id="add_platform_form" onsubmit="return false;">
		<input type="hidden" name="sec" value="save-platform">

		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Platform code'] ?? 'Platform code'?> (e.g. chatgpt)</label>
			<input type="text" name="platform" value="<?php echo htmlspecialchars($formPost['platform'] ?? '')?>">
			<?php echo $formErrMsg['platform'] ?? ''?>
		</div>
		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Hostname'] ?? 'Hostname'?></label>
			<input type="text" name="hostname" value="<?php echo htmlspecialchars($formPost['hostname'] ?? '')?>">
			<?php echo $formErrMsg['hostname'] ?? ''?>
		</div>
		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Display name'] ?? 'Display name'?></label>
			<input type="text" name="display_name" value="<?php echo htmlspecialchars($formPost['display_name'] ?? '')?>">
			<?php echo $formErrMsg['display_name'] ?? ''?>
		</div>
		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Bot UA pattern'] ?? 'Bot UA pattern'?></label>
			<input type="text" name="bot_ua_pattern" value="<?php echo htmlspecialchars($formPost['bot_ua_pattern'] ?? '')?>">
		</div>
		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Robots.txt User-agent token'] ?? 'Robots.txt User-agent token'?></label>
			<input type="text" name="robots_user_agent_token" value="<?php echo htmlspecialchars($formPost['robots_user_agent_token'] ?? '')?>">
			<div class="aiv-field-hint"><?php echo $spTextAIV['robotsuseragenttokenhint'] ?? 'Optional. The exact token this crawler documents for its own robots.txt User-agent line (e.g. Google-Extended). Leave blank to reuse the UA match pattern above.'?></div>
		</div>
		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Verify suffix'] ?? 'Verify suffix'?></label>
			<input type="text" name="verify_suffix" value="<?php echo htmlspecialchars($formPost['verify_suffix'] ?? '')?>">
		</div>
		<div class="aiv-field">
			<div class="aiv-switch-row">
				<label class="aiv-switch">
					<input type="checkbox" name="is_active" value="1" checked>
					<span class="aiv-switch-track"></span>
				</label>
				<span class="aiv-field-label" style="margin:0;"><?php echo $spText['common']['Active'] ?? 'Active'?></span>
			</div>
		</div>
		<div class="aiv-field">
			<div class="aiv-switch-row">
				<label class="aiv-switch">
					<input type="checkbox" name="is_referral_source" value="1">
					<span class="aiv-switch-track"></span>
				</label>
				<span class="aiv-field-label" style="margin:0;"><?php echo $spTextAIV['Referral source'] ?? 'Referral source'?></span>
			</div>
		</div>

		<a onclick="confirmSubmit('aivisibility.php', 'add_platform_form', 'content')" href="javascript:void(0);" class="aiv-btn aiv-btn-primary">
			<?php echo $spText['button']['Proceed'] ?? 'Save'?>
		</a>
	</form>
</div>
