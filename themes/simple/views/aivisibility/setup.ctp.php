<?php echo showSectionHead($spTextTools['AI Visibility'] ?? 'AI Visibility'); ?>

<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="scriptDoLoad('aivisibility.php', 'content', '&website_id='+this.value)">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo $websiteInfo['name']?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>
</form>

<?php if (!empty($siteInfo)) { ?>

<div class="alert alert-secondary">
	<i class="fas fa-shield-alt me-2"></i>
	<?php echo $spTextAIV['Privacy note'] ?? 'No cookies, no localStorage, no visitor identifiers are ever stored - only that a visit arrived from a given AI platform to a given page. Data stays on your own server.'?>
</div>

<div class="cron-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-bottom:20px;">
	<div class="cron-card-title" style="font-weight:600;margin-bottom:10px;">
		<?php echo $spTextAIV['Install snippet'] ?? 'Install snippet'?>
	</div>
	<p><?php echo $spTextAIV['snippetinstructions'] ?? 'Paste this snippet just before the closing </body> tag on every page of your site.'?></p>

	<div class="cron-command-box" style="background:#1e1e1e;border-radius:8px;padding:15px;position:relative;">
		<code id="aivSnippet" style="color:#9cdcfe;white-space:pre-wrap;word-break:break-all;">&lt;script defer data-token="<?php echo htmlspecialchars($siteInfo['token'])?>" src="<?php echo htmlspecialchars($snippetUrl)?>"&gt;&lt;/script&gt;</code>
		<button type="button" id="aivCopyBtn" class="btn btn-sm btn-secondary" style="position:absolute;top:10px;right:10px;">
			<i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?>
		</button>
	</div>

	<div id="aivInstallStatus" style="margin-top:15px;">
		<i class="fas fa-spinner fa-spin"></i>
		<span id="aivStatusText"><?php echo $spTextAIV['Waiting for first hit'] ?? 'Waiting for first hit...'?></span>
	</div>

	<div class="alert alert-info" style="margin-top:15px;">
		<?php echo $spTextAIV['floornotice'] ?? 'Some AI clients strip or omit the referrer, and native mobile apps often send nothing - treat these counts as a floor, not a complete measure.'?>
	</div>

	<p style="margin-top:15px;">
		<strong><?php echo $spTextAIV['WordPress note'] ?? 'WordPress:'?></strong>
		<?php echo $spTextAIV['wordpressinstructions'] ?? 'Paste the snippet using a header/footer plugin (e.g. Insert Headers and Footers), or your theme\'s footer.php.'?>
	</p>
</div>

<div class="cron-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-bottom:20px;">
	<div class="cron-card-title" style="font-weight:600;margin-bottom:10px;">
		<i class="fas fa-robot me-2"></i>
		<?php echo $spTextAIV['AI Bot Crawler Tracking'] ?? 'AI Bot Crawler Tracking'?>
	</div>
	<p><?php echo $spTextAIV['botcollectordesc'] ?? 'AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and others) never execute JavaScript, so the referral snippet above cannot see them. Download this collector script and include it on your server to track real crawler visits.'?></p>

	<a href="<?php echo htmlspecialchars($botCollectorUrl)?>" class="btn btn-sm btn-secondary">
		<i class="fas fa-download"></i> <?php echo $spTextAIV['Download collector script'] ?? 'Download collector script'?>
	</a>

	<div id="aivBotInstallStatus" style="margin-top:15px;">
		<i class="fas fa-spinner fa-spin"></i>
		<span id="aivBotStatusText"><?php echo $spTextAIV['Waiting for first bot visit'] ?? 'Waiting for first bot visit...'?></span>
	</div>

	<p style="margin-top:15px;">
		<?php echo $spTextAIV['botinstallinstructions'] ?? 'Generic PHP: include this file at the very top of your site\'s bootstrap (e.g. the first line of index.php or wp-config.php).'?>
	</p>
	<p>
		<strong><?php echo $spTextAIV['WordPress note'] ?? 'WordPress:'?></strong>
		<?php echo $spTextAIV['botwordpressinstructions'] ?? 'WordPress: save it into wp-content/mu-plugins/ so it loads automatically on every request.'?>
	</p>

	<div class="alert alert-secondary" style="margin-top:15px;">
		<i class="fas fa-shield-alt me-2"></i>
		<?php echo $spTextAIV['botverifiednotice'] ?? '"Verified" means the crawler\'s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'?>
	</div>
</div>

<?php if (isAdmin()) { ?>
<div class="cron-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-bottom:20px;">
	<div class="cron-card-title" style="font-weight:600;margin-bottom:10px;">
		<i class="fas fa-server me-2"></i>
		<?php echo $spTextAIV['Server Access Configuration'] ?? 'Server Access Configuration'?>
	</div>
	<div class="alert alert-warning">
		<?php echo $spTextAIV['serveraccessnotice'] ?? 'Admin-only. Only set these if SEO Panel and this website are on the same server. Grants SEO Panel real filesystem read/write to this path with the web server\'s own permissions.'?>
	</div>
	<form id="access_config_form" onsubmit="return false;">
		<input type="hidden" name="sec" value="save-site-access">
		<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
		<table class="search" style="width:100%;">
			<tr>
				<th style="white-space:nowrap;"><?php echo $spTextAIV['Document Root Path'] ?? 'Document Root Path'?>:</th>
				<td>
					<input type="text" name="docroot_path" class="form-control" placeholder="/var/www/html/example.com" value="<?php echo htmlspecialchars($accessInfo['docroot_path'] ?? '')?>">
					<?php if ($docrootStatus) { ?>
						<?php if (!empty($docrootStatus['writable'])) { ?>
							<small class="text-success"><i class="fas fa-check-circle"></i> <?php echo $spText['common']['Writable'] ?? 'Writable'?></small>
						<?php } elseif (!empty($docrootStatus['ok'])) { ?>
							<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> <?php echo $spTextAIV['Path not writable - view only'] ?? 'Path not writable - view only'?></small>
						<?php } else { ?>
							<small class="text-danger"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($docrootStatus['error'])?></small>
						<?php } ?>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th style="white-space:nowrap;"><?php echo $spTextAIV['Access Log Path'] ?? 'Access Log Path'?>:</th>
				<td>
					<input type="text" name="access_log_path" class="form-control" placeholder="/var/log/apache2/example.com-access.log" value="<?php echo htmlspecialchars($accessInfo['access_log_path'] ?? '')?>">
					<br><small class="text-muted"><?php echo $spTextAIV['combinedlogformatnotice'] ?? 'Expects standard Apache/Nginx Combined Log Format. Custom log_format configs may not parse.'?></small>
					<?php if ($accessLogStatus) { ?>
						<?php if (!empty($accessLogStatus['readable'])) { ?>
							<br><small class="text-success"><i class="fas fa-check-circle"></i> <?php echo $spText['common']['Readable'] ?? 'Readable'?></small>
						<?php } else { ?>
							<br><small class="text-danger"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($accessLogStatus['error'])?></small>
						<?php } ?>
					<?php } ?>
				</td>
			</tr>
		</table>
		<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'access_config_form', 'content')" class="btn btn-sm btn-secondary">
			<?php echo $spText['button']['Proceed'] ?? 'Save'?>
		</a>
	</form>
</div>
<?php } ?>

<div class="cron-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:20px;margin-bottom:20px;">
	<div class="cron-card-title" style="font-weight:600;margin-bottom:10px;">
		<i class="fas fa-robot me-2"></i>
		<?php echo $spTextAIV['AI Crawler Rules'] ?? 'AI Crawler Rules'?>
	</div>
	<div class="alert alert-info">
		<?php echo $spTextAIV['robotswritenotice'] ?? 'Toggling a platform below adds or removes a Disallow rule for it inside a clearly marked block in this site\'s robots.txt - everything else in the file is left untouched.'?>
	</div>

	<?php if (!empty($robotsWriteError)) { ?>
		<div class="alert alert-danger"><?php echo htmlspecialchars($robotsWriteError)?></div>
	<?php } ?>

	<h6><?php echo $spTextAIV['Live robots.txt'] ?? 'Live robots.txt'?></h6>
	<?php if ($robotsPreview !== null) { ?>
		<pre style="background:#1e1e1e;color:#9cdcfe;padding:15px;border-radius:8px;max-height:200px;overflow:auto;white-space:pre-wrap;"><?php echo htmlspecialchars($robotsPreview)?></pre>
	<?php } else { ?>
		<p class="text-muted"><?php echo $_SESSION['text']['common']['No Records Found'] ?? 'No Records Found'?></p>
	<?php } ?>

	<table class="list">
		<tr class="listHead">
			<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
			<th><?php echo $spTextAIV['Block this platform'] ?? 'Block this platform'?></th>
		</tr>
		<?php foreach ($robotsPlatformList as $platformInfo) { $isBlocked = !empty($robotsRuleMap[$platformInfo['platform']]); ?>
			<tr>
				<td><?php echo htmlspecialchars($platformInfo['display_name'])?></td>
				<td>
					<form id="toggle_robots_<?php echo htmlspecialchars($platformInfo['platform'])?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="toggle-robots-rule">
						<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
						<input type="hidden" name="platform" value="<?php echo htmlspecialchars($platformInfo['platform'])?>">
						<input type="checkbox" <?php echo $isBlocked ? 'checked' : ''?> <?php echo empty($docrootStatus['writable']) ? 'disabled' : ''?>
							onchange="scriptDoLoadPost('aivisibility.php', 'toggle_robots_<?php echo htmlspecialchars($platformInfo['platform'])?>', 'content')">
					</form>
				</td>
			</tr>
		<?php } ?>
	</table>

	<hr>

	<?php if (!empty($llmsWriteError)) { ?>
		<div class="alert alert-danger"><?php echo htmlspecialchars($llmsWriteError)?></div>
	<?php } ?>

	<?php if (!empty($llmsConfirmNeeded)) { ?>
		<div class="alert alert-warning">
			<?php echo $spTextAIV['llmsoverwritenotice'] ?? 'llms.txt already exists and wasn\'t generated by SEO Panel. Regenerating will overwrite it.'?>
			<form id="llms_confirm_form" onsubmit="return false;">
				<input type="hidden" name="sec" value="regenerate-llms">
				<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
				<input type="hidden" name="confirm_overwrite" value="1">
			</form>
			<br><br>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'llms_confirm_form', 'content')" class="btn btn-sm btn-danger">
				<?php echo $spTextAIV['Yes, overwrite'] ?? 'Yes, overwrite'?>
			</a>
		</div>
	<?php } elseif (!empty($docrootStatus['writable'])) { ?>
		<form id="llms_form" onsubmit="return false;">
			<input type="hidden" name="sec" value="regenerate-llms">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
		</form>
		<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'llms_form', 'content')" class="btn btn-sm btn-secondary">
			<?php echo $spTextAIV['Regenerate llms.txt'] ?? 'Regenerate llms.txt'?>
		</a>
	<?php } else { ?>
		<button class="btn btn-sm btn-secondary" disabled><?php echo $spTextAIV['Regenerate llms.txt'] ?? 'Regenerate llms.txt'?></button>
	<?php } ?>

	<?php if (!empty($websiteInfo['url'])) { $urlParts = parse_url($websiteInfo['url']); if (!empty($urlParts['host'])) { ?>
		<a href="<?php echo (!empty($urlParts['scheme']) ? $urlParts['scheme'] : 'http')?>://<?php echo htmlspecialchars($urlParts['host'])?>/llms.txt" target="_blank" style="margin-left:10px;">
			<?php echo $spTextAIV['View live llms.txt'] ?? 'View live llms.txt'?>
		</a>
	<?php } } ?>
</div>

<script>
document.getElementById('aivCopyBtn').addEventListener('click', function() {
	var btn = this;
	var textarea = document.createElement('textarea');
	textarea.value = document.getElementById('aivSnippet').textContent;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	textarea.select();
	document.execCommand('copy');
	document.body.removeChild(textarea);
	btn.innerHTML = '<i class="fas fa-check"></i> <?php echo $spText['common']['Copied'] ?? 'Copied'?>!';
	setTimeout(function() {
		btn.innerHTML = '<i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?>';
	}, 2000);
});

(function pollInstallStatus() {
	fetch('aivisibility.php?sec=installstatus&website_id=<?php echo intval($websiteId)?>', { credentials: 'same-origin' })
		.then(function(res) { return res.json(); })
		.then(function(data) {
			var wrap = document.getElementById('aivInstallStatus');
			if (data.status === 'receiving') {
				wrap.innerHTML = '<i class="fas fa-check-circle text-success"></i> <span><?php echo $spTextAIV['Receiving data'] ?? 'Receiving data'?></span>';
			}

			var botWrap = document.getElementById('aivBotInstallStatus');
			if (botWrap && data.bot_status === 'receiving') {
				botWrap.innerHTML = '<i class="fas fa-check-circle text-success"></i> <span><?php echo $spTextAIV['Receiving data'] ?? 'Receiving data'?></span>';
			}

			if (data.status !== 'receiving' || data.bot_status !== 'receiving') {
				setTimeout(pollInstallStatus, 5000);
			}
		})
		.catch(function() { setTimeout(pollInstallStatus, 10000); });
})();
</script>

<?php } ?>
