<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Perception Tracking'] ?? 'AI Perception Tracking'); ?>

<?php if (!empty($noWebsites)) { ?>
	<?php echo showNoRecordsList(0); ?>
<?php } else { ?>

<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select class="custom-select" onchange="scriptDoLoad('ai-perception.php?sec=tracking', 'content', '&website_id='+this.value)">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo htmlspecialchars($websiteInfo['name'])?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>

<div class="aiv-note">
	<i class="fas fa-info-circle"></i>
	<span>
		<?php echo $spTextAIV['You can track up to'] ?? 'You can track up to'?> <?php echo intval($promptCap)?>
		<?php echo $spTextAIV['prompts per website, checked at most once every'] ?? 'prompts per website, checked at most once every'?> <?php echo intval($trackingIntervalDays)?>
		<?php echo $spTextAIV['days, against every AI provider you have configured a key for.'] ?? 'days, against every AI provider you have configured a key for.'?>
	</span>
</div>

<?php if (!empty($shareOfVoice) || $shareOfVoice === 0) { ?>
	<div class="aiv-card" style="text-align:center;padding:22px;max-width:260px;">
		<div style="font-size:30px;font-weight:800;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);-webkit-background-clip:text;background-clip:text;color:transparent;"><?php echo intval($shareOfVoice)?>%</div>
		<div style="font-size:13px;color:#8a8ea3;font-weight:600;margin-top:4px;"><?php echo $spTextAIV['Share of Voice (latest checks)'] ?? 'Share of Voice (latest checks)'?></div>
	</div>
<?php } ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-list"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Tracked Prompts'] ?? 'Tracked Prompts'?></div>
	</div>

	<?php if (empty($prompts)) { ?>
		<?php echo showNoRecordsList(0); ?>
	<?php } else { ?>
		<table class="aiv-table">
			<tr>
				<th><?php echo $spText['common']['Prompt'] ?? 'Prompt'?></th>
				<th><?php echo $spText['common']['Provider'] ?? 'Provider'?></th>
				<th><?php echo $spText['common']['Status'] ?? 'Status'?></th>
				<th><?php echo $spText['common']['Date'] ?? 'Last Checked'?></th>
				<th style="width:10%"><?php echo $spText['common']['Action'] ?? 'Action'?></th>
			</tr>
			<?php foreach ($prompts as $prompt) { ?>
				<?php $rowspan = max(1, count($prompt['results'])); ?>
				<?php if (empty($prompt['results'])) { ?>
					<tr>
						<td><?php echo htmlspecialchars($prompt['prompt_text'])?></td>
						<td colspan="3"><em style="color:#adb5bd;"><?php echo $spTextAIV['Not checked yet'] ?? 'Not checked yet'?></em></td>
						<td>
							<form id="aip_remove_prompt_form_<?php echo $prompt['id']?>" onsubmit="return false;">
								<input type="hidden" name="sec" value="remove-prompt">
								<input type="hidden" name="prompt_id" value="<?php echo $prompt['id']?>">
							</form>
							<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_prompt_form_<?php echo $prompt['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger btn-sm">
								<?php echo $spText['button']['Delete'] ?? 'Delete'?>
							</a>
						</td>
					</tr>
				<?php } else { ?>
					<?php foreach ($prompt['results'] as $i => $result) { ?>
						<tr>
							<?php if ($i === 0) { ?>
								<td rowspan="<?php echo $rowspan?>"><?php echo htmlspecialchars($prompt['prompt_text'])?></td>
							<?php } ?>
							<td><?php echo htmlspecialchars($result['provider'])?></td>
							<td>
								<?php if (!empty($result['mentioned'])) { ?>
									<span class="badge badge-success py-1 px-2 text-light"><?php echo $spTextAIV['Mentioned'] ?? 'Mentioned'?></span>
								<?php } else { ?>
									<span class="badge badge-secondary py-1 px-2 text-light"><?php echo $spTextAIV['Not mentioned'] ?? 'Not mentioned'?></span>
								<?php } ?>
							</td>
							<td><?php echo htmlspecialchars($result['checked_date'])?></td>
							<?php if ($i === 0) { ?>
								<td rowspan="<?php echo $rowspan?>">
									<form id="aip_remove_prompt_form_<?php echo $prompt['id']?>" onsubmit="return false;">
										<input type="hidden" name="sec" value="remove-prompt">
										<input type="hidden" name="prompt_id" value="<?php echo $prompt['id']?>">
									</form>
									<a onclick="confirmSubmit('ai-perception.php', 'aip_remove_prompt_form_<?php echo $prompt['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger btn-sm">
										<?php echo $spText['button']['Delete'] ?? 'Delete'?>
									</a>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</table>
	<?php } ?>

	<?php if (count($prompts) < $promptCap) { ?>
		<form id="aip_add_prompt_form" onsubmit="return false;" style="margin-top:15px;">
			<input type="hidden" name="sec" value="add-prompt">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
			<input type="text" name="prompt_text" class="form-control" style="max-width:420px;display:inline-block;" maxlength="500" placeholder="<?php echo $spTextAIV['Add a prompt to track weekly (e.g. "best CRM for small business")'] ?? 'Add a prompt to track weekly (e.g. "best CRM for small business")'?>">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('ai-perception.php', 'aip_add_prompt_form', 'content')" class="btn btn-primary">
				<?php echo $spTextAIV['Add Prompt'] ?? 'Add Prompt'?>
			</a>
		</form>
	<?php } ?>
</div>

<?php if (empty($providers)) { ?>
	<div class="aiv-note aiv-note-warn">
		<i class="fas fa-exclamation-triangle"></i>
		<span>
			<?php echo $spTextAIV['No AI providers configured yet.'] ?? 'No AI providers configured yet.'?>
			<a href="javascript:void(0);" onclick="scriptDoLoad('ai-perception.php', 'content')"><?php echo $spTextAIV['Add an API key'] ?? 'Add an API key'?></a>
		</span>
	</div>
<?php } ?>

<?php } ?>
