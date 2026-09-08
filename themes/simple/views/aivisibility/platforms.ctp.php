<?php echo showSectionHead($spTextAIV['AI Platforms'] ?? 'AI Platforms'); ?>

<div class="alert alert-info">
	<?php echo $spTextAIV['referralsourcenotice'] ?? 'Only enable this for hostnames real visitors click through from. Vendor domains used solely for bot user-agent matching (e.g. google.com for Google-Extended) must stay off, or ordinary traffic from that domain will be miscounted as an AI referral.'?>
</div>

<table class="list">
	<tr class="listHead">
		<th><?php echo $spTextAIV['Platform code'] ?? 'Platform code'?></th>
		<th><?php echo $spTextAIV['Hostname'] ?? 'Hostname'?></th>
		<th><?php echo $spTextAIV['Display name'] ?? 'Display name'?></th>
		<th><?php echo $spTextAIV['Bot UA pattern'] ?? 'Bot UA pattern'?></th>
		<th><?php echo $spTextAIV['Verify suffix'] ?? 'Verify suffix'?></th>
		<th><?php echo $spText['common']['Active'] ?? 'Active'?></th>
		<th><?php echo $spTextAIV['Referral source'] ?? 'Referral source'?></th>
		<th><?php echo $spText['common']['Action'] ?? 'Action'?></th>
	</tr>
	<?php if (!empty($platformList)) { ?>
		<?php foreach ($platformList as $platformInfo) { ?>
			<tr>
				<td><?php echo htmlspecialchars($platformInfo['platform'])?></td>
				<td><?php echo htmlspecialchars($platformInfo['hostname'])?></td>
				<td><?php echo htmlspecialchars($platformInfo['display_name'])?></td>
				<td><?php echo htmlspecialchars($platformInfo['bot_ua_pattern'] ?? '')?></td>
				<td><?php echo htmlspecialchars($platformInfo['verify_suffix'] ?? '')?></td>
				<td>
					<form id="toggle_active_<?php echo $platformInfo['id']?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="toggle-platform">
						<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
						<input type="hidden" name="field" value="is_active">
						<input type="checkbox" <?php echo $platformInfo['is_active'] ? 'checked' : ''?>
							onchange="scriptDoLoadPost('aivisibility.php', 'toggle_active_<?php echo $platformInfo['id']?>', 'content')">
					</form>
				</td>
				<td>
					<form id="toggle_ref_<?php echo $platformInfo['id']?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="toggle-platform">
						<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
						<input type="hidden" name="field" value="is_referral_source">
						<input type="checkbox" <?php echo $platformInfo['is_referral_source'] ? 'checked' : ''?>
							onchange="scriptDoLoadPost('aivisibility.php', 'toggle_ref_<?php echo $platformInfo['id']?>', 'content')">
					</form>
				</td>
				<td>
					<a href="javascript:void(0);"
						onclick="confirmSubmit('aivisibility.php', 'delete_<?php echo $platformInfo['id']?>', 'content')"
						class="actionbut"><?php echo $spText['common']['Delete']?></a>
					<form id="delete_<?php echo $platformInfo['id']?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="delete-platform">
						<input type="hidden" name="id" value="<?php echo $platformInfo['id']?>">
					</form>
				</td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<?php echo showNoRecordsList(8); ?>
	<?php } ?>
</table>

<table id="cust_tab" style="margin-top:20px;">
	<tr class="form_head">
		<th colspan="2" class="text-start"><?php echo $spTextAIV['Add Platform'] ?? 'Add Platform'?></th>
	</tr>
	<tbody>
		<form id="add_platform_form" onsubmit="return false;">
			<input type="hidden" name="sec" value="save-platform">
			<tr>
				<td><?php echo $spTextAIV['Platform code'] ?? 'Platform code'?> (e.g. chatgpt):</td>
				<td>
					<input type="text" name="platform" class="form-control" value="<?php echo htmlspecialchars($formPost['platform'] ?? '')?>">
					<?php echo $formErrMsg['platform'] ?? ''?>
				</td>
			</tr>
			<tr>
				<td><?php echo $spTextAIV['Hostname'] ?? 'Hostname'?>:</td>
				<td>
					<input type="text" name="hostname" class="form-control" value="<?php echo htmlspecialchars($formPost['hostname'] ?? '')?>">
					<?php echo $formErrMsg['hostname'] ?? ''?>
				</td>
			</tr>
			<tr>
				<td><?php echo $spTextAIV['Display name'] ?? 'Display name'?>:</td>
				<td>
					<input type="text" name="display_name" class="form-control" value="<?php echo htmlspecialchars($formPost['display_name'] ?? '')?>">
					<?php echo $formErrMsg['display_name'] ?? ''?>
				</td>
			</tr>
			<tr>
				<td><?php echo $spTextAIV['Bot UA pattern'] ?? 'Bot UA pattern'?>:</td>
				<td><input type="text" name="bot_ua_pattern" class="form-control" value="<?php echo htmlspecialchars($formPost['bot_ua_pattern'] ?? '')?>"></td>
			</tr>
			<tr>
				<td><?php echo $spTextAIV['Verify suffix'] ?? 'Verify suffix'?>:</td>
				<td><input type="text" name="verify_suffix" class="form-control" value="<?php echo htmlspecialchars($formPost['verify_suffix'] ?? '')?>"></td>
			</tr>
			<tr>
				<td><?php echo $spText['common']['Active'] ?? 'Active'?>:</td>
				<td><input type="checkbox" name="is_active" value="1" checked></td>
			</tr>
			<tr>
				<td><?php echo $spTextAIV['Referral source'] ?? 'Referral source'?>:</td>
				<td><input type="checkbox" name="is_referral_source" value="1"></td>
			</tr>
		</form>
	</tbody>
</table>
<table width="100%" class="actionSec">
	<tr>
		<td style="padding-top: 6px;text-align:right;">
			<a onclick="confirmSubmit('aivisibility.php', 'add_platform_form', 'content')" href="javascript:void(0);" class="actionbut">
				<?php echo $spText['button']['Proceed'] ?? 'Save'?>
			</a>
		</td>
	</tr>
</table>
