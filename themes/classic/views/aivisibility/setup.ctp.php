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
