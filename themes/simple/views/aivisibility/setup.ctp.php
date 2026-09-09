<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
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

<div class="aiv-note">
	<i class="fas fa-shield-alt"></i>
	<span><?php echo $spTextAIV['Privacy note'] ?? 'No cookies, no localStorage, no visitor identifiers are ever stored - only that a visit arrived from a given AI platform to a given page. Data stays on your own server.'?></span>
</div>

<?php $advancedNeedsAttention = !empty($robotsWriteError) || !empty($htaccessWriteError) || !empty($llmsWriteError); ?>
<div class="aiv-tabs" id="aivTabs">
	<button type="button" class="aiv-tab-btn" data-tab="setup">
		<i class="fas fa-rocket"></i> <?php echo $spTextAIV['Setup'] ?? 'Setup'?>
	</button>
	<button type="button" class="aiv-tab-btn" data-tab="advanced">
		<i class="fas fa-sliders-h"></i> <?php echo $spTextAIV['Advanced'] ?? 'Advanced'?>
		<?php if ($advancedNeedsAttention) { ?><span class="aiv-tab-btn-badge"></span><?php } ?>
	</button>
</div>

<div class="aiv-tab-panel" data-tab="setup" hidden>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-code"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Install snippet'] ?? 'Install snippet'?></div>
			<div class="aiv-card-subtitle"><?php echo $spTextAIV['snippetinstructions'] ?? 'Paste this snippet just before the closing </body> tag on every page of your site.'?></div>
		</div>
	</div>

	<div class="aiv-code-box">
		<code id="aivSnippet">&lt;script defer data-token="<?php echo htmlspecialchars($siteInfo['token'])?>" src="<?php echo htmlspecialchars($snippetUrl)?>"&gt;&lt;/script&gt;</code>
		<button type="button" id="aivCopyBtn" class="aiv-btn aiv-btn-outline aiv-code-copy">
			<i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?>
		</button>
	</div>

	<div id="aivInstallStatus" class="aiv-status-line">
		<i class="fas fa-spinner fa-spin"></i>
		<span id="aivStatusText"><?php echo $spTextAIV['Waiting for first hit'] ?? 'Waiting for first hit...'?></span>
	</div>

	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['floornotice'] ?? 'Some AI clients strip or omit the referrer, and native mobile apps often send nothing - treat these counts as a floor, not a complete measure.'?></span>
	</div>

	<p style="font-size:13px;color:#565a72;margin:0;">
		<strong><?php echo $spTextAIV['WordPress note'] ?? 'WordPress:'?></strong>
		<?php echo $spTextAIV['wordpressinstructions'] ?? 'Paste the snippet using a header/footer plugin (e.g. Insert Headers and Footers), or your theme\'s footer.php.'?>
	</p>
</div>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-robot"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['AI Bot Crawler Tracking'] ?? 'AI Bot Crawler Tracking'?></div>
			<div class="aiv-card-subtitle"><?php echo $spTextAIV['botcollectordesc'] ?? 'AI crawlers (GPTBot, ClaudeBot, PerplexityBot, and others) never execute JavaScript, so the referral snippet above cannot see them. Download this collector script and include it on your server to track real crawler visits.'?></div>
		</div>
	</div>

	<a href="<?php echo htmlspecialchars($botCollectorUrl)?>" class="aiv-btn aiv-btn-outline">
		<i class="fas fa-download"></i> <?php echo $spTextAIV['Download collector script'] ?? 'Download collector script'?>
	</a>

	<?php if (!empty($wpInstallError)) { ?>
		<div class="aiv-note aiv-note-danger"><i class="fas fa-times-circle"></i> <span><?php echo htmlspecialchars($wpInstallError)?></span></div>
	<?php } ?>

	<?php if (!empty($wpCollectorInstalled)) { ?>
		<span class="aiv-badge-soft success" style="margin-left:10px;"><i class="fas fa-check-circle"></i> <?php echo $spTextAIV['Installed automatically'] ?? 'Installed automatically'?></span>
	<?php } elseif (!empty($wpDetected)) { ?>
		<div class="aiv-note">
			<i class="fas fa-magic"></i>
			<span>
				<?php echo $spTextAIV['wpdetectednotice'] ?? 'WordPress detected at your configured Document Root. Skip the manual download/paste step - install the collector directly as a must-use plugin.'?>
				<form id="wp_install_form" onsubmit="return false;">
					<input type="hidden" name="sec" value="install-wp-collector">
					<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
				</form>
				<br><br>
				<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'wp_install_form', 'content')" class="aiv-btn aiv-btn-primary">
					<?php echo $spTextAIV['Install Automatically'] ?? 'Install Automatically'?>
				</a>
			</span>
		</div>
	<?php } ?>

	<div id="aivBotInstallStatus" class="aiv-status-line">
		<i class="fas fa-spinner fa-spin"></i>
		<span id="aivBotStatusText"><?php echo $spTextAIV['Waiting for first bot visit'] ?? 'Waiting for first bot visit...'?></span>
	</div>

	<p style="font-size:13px;color:#565a72;margin-top:14px;">
		<?php echo $spTextAIV['botinstallinstructions'] ?? 'Generic PHP: include this file at the very top of your site\'s bootstrap (e.g. the first line of index.php or wp-config.php).'?>
	</p>
	<p style="font-size:13px;color:#565a72;margin:0;">
		<strong><?php echo $spTextAIV['WordPress note'] ?? 'WordPress:'?></strong>
		<?php echo $spTextAIV['botwordpressinstructions'] ?? 'WordPress: save it into wp-content/mu-plugins/ so it loads automatically on every request.'?>
	</p>

	<div class="aiv-note">
		<i class="fas fa-shield-alt"></i>
		<span><?php echo $spTextAIV['botverifiednotice'] ?? '"Verified" means the crawler\'s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'?></span>
	</div>
</div>

<div class="aiv-note">
	<i class="fas fa-sliders-h"></i>
	<span>
		<?php echo $spTextAIV['advancedlinknotice'] ?? 'Need document root access, custom crawler rules, or AI-bot response headers?'?>
		<a href="javascript:void(0);" onclick="aivActivateTab('advanced')" style="font-weight:600;">
			<?php echo $spTextAIV['Go to Advanced settings'] ?? 'Go to Advanced settings'?> <i class="fas fa-arrow-right"></i>
		</a>
	</span>
</div>

</div>

<div class="aiv-tab-panel" data-tab="advanced" hidden>

<div class="aiv-note">
	<i class="fas fa-info-circle"></i>
	<span><?php echo $spTextAIV['advancedsectionnotice'] ?? 'Document root access, robots.txt/llms.txt crawler rules, and .htaccess AI-bot headers - optional, for sites hosted on this same server.'?></span>
</div>

<?php if (isAdmin()) { ?>
<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-server"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Server Access Configuration'] ?? 'Server Access Configuration'?></div>
		</div>
	</div>
	<div class="aiv-note aiv-note-warn">
		<i class="fas fa-exclamation-triangle"></i>
		<span><?php echo $spTextAIV['serveraccessnotice'] ?? 'Admin-only. Only set these if SEO Panel and this website are on the same server. Grants SEO Panel real filesystem read/write to this path with the web server\'s own permissions.'?></span>
	</div>
	<form id="access_config_form" onsubmit="return false;">
		<input type="hidden" name="sec" value="save-site-access">
		<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">

		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Document Root Path'] ?? 'Document Root Path'?></label>
			<input type="text" name="docroot_path" placeholder="/var/www/html/example.com" value="<?php echo htmlspecialchars($accessInfo['docroot_path'] ?? '')?>">
			<?php if ($docrootStatus) { ?>
				<?php if (!empty($docrootStatus['writable'])) { ?>
					<div class="aiv-field-status ok"><i class="fas fa-check-circle"></i> <?php echo $spText['common']['Writable'] ?? 'Writable'?></div>
				<?php } elseif (!empty($docrootStatus['ok'])) { ?>
					<div class="aiv-field-status warn"><i class="fas fa-exclamation-triangle"></i> <?php echo $spTextAIV['Path not writable - view only'] ?? 'Path not writable - view only'?></div>
				<?php } else { ?>
					<div class="aiv-field-status err"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($docrootStatus['error'])?></div>
				<?php } ?>
			<?php } ?>
		</div>

		<div class="aiv-field">
			<label class="aiv-field-label"><?php echo $spTextAIV['Access Log Path'] ?? 'Access Log Path'?></label>
			<input type="text" name="access_log_path" placeholder="/var/log/apache2/example.com-access.log" value="<?php echo htmlspecialchars($accessInfo['access_log_path'] ?? '')?>">
			<div class="aiv-field-hint"><?php echo $spTextAIV['combinedlogformatnotice'] ?? 'Expects standard Apache/Nginx Combined Log Format. Custom log_format configs may not parse.'?></div>
			<?php if ($accessLogStatus) { ?>
				<?php if (!empty($accessLogStatus['readable'])) { ?>
					<div class="aiv-field-status ok"><i class="fas fa-check-circle"></i> <?php echo $spText['common']['Readable'] ?? 'Readable'?></div>
				<?php } else { ?>
					<div class="aiv-field-status err"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($accessLogStatus['error'])?></div>
				<?php } ?>
			<?php } ?>
		</div>

		<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'access_config_form', 'content')" class="aiv-btn aiv-btn-primary">
			<?php echo $spText['button']['Proceed'] ?? 'Save'?>
		</a>
	</form>
</div>
<?php } ?>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-ban"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['AI Crawler Rules'] ?? 'AI Crawler Rules'?></div>
		</div>
	</div>
	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['robotswritenotice'] ?? 'Toggling a platform below adds or removes a Disallow rule for it inside a clearly marked block in this site\'s robots.txt - everything else in the file is left untouched.'?></span>
	</div>

	<?php if (!empty($robotsWriteError)) { ?>
		<div class="aiv-note aiv-note-danger"><i class="fas fa-times-circle"></i> <span><?php echo htmlspecialchars($robotsWriteError)?></span></div>
	<?php } ?>

	<label class="aiv-field-label"><?php echo $spTextAIV['Live robots.txt'] ?? 'Live robots.txt'?></label>
	<?php if ($robotsPreview !== null) { ?>
		<div class="aiv-code-box aiv-code-multiline"><pre><?php echo htmlspecialchars($robotsPreview)?></pre></div>
	<?php } else { ?>
		<p class="text-muted"><?php echo $_SESSION['text']['common']['No Records Found'] ?? 'No Records Found'?></p>
	<?php } ?>

	<table class="aiv-table">
		<tr>
			<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
			<th style="width:180px;white-space:nowrap;"><?php echo $spTextAIV['Block this platform'] ?? 'Block this platform'?></th>
		</tr>
		<?php foreach ($robotsPlatformList as $platformInfo) { $isBlocked = !empty($robotsRuleMap[$platformInfo['platform']]); ?>
			<tr>
				<td><?php echo htmlspecialchars($platformInfo['display_name'])?></td>
				<td>
					<form id="toggle_robots_<?php echo htmlspecialchars($platformInfo['platform'])?>" onsubmit="return false;">
						<input type="hidden" name="sec" value="toggle-robots-rule">
						<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
						<input type="hidden" name="platform" value="<?php echo htmlspecialchars($platformInfo['platform'])?>">
						<label class="aiv-switch">
							<input type="checkbox" <?php echo $isBlocked ? 'checked' : ''?> <?php echo empty($docrootStatus['writable']) ? 'disabled' : ''?>
								onchange="scriptDoLoadPost('aivisibility.php', 'toggle_robots_<?php echo htmlspecialchars($platformInfo['platform'])?>', 'content')">
							<span class="aiv-switch-track"></span>
						</label>
					</form>
				</td>
			</tr>
		<?php } ?>
	</table>

	<div class="aiv-note">
		<i class="fas fa-shield-alt"></i>
		<span>
			<?php echo $spTextAIV['auditlognotice'] ?? 'A timestamped record of every AI crawler rule change made through SEO Panel for this website - exportable as proof of policy enforcement for legal/compliance review.'?>
			<a href="aivisibility.php?sec=export-robots-audit&website_id=<?php echo intval($websiteId)?>" class="aiv-btn aiv-btn-outline" style="margin-left:10px;padding:5px 12px;">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export Audit Trail'] ?? 'Export Audit Trail'?>
			</a>
		</span>
	</div>

	<hr class="aiv-divider">

	<?php if (!empty($llmsWriteError)) { ?>
		<div class="aiv-note aiv-note-danger"><i class="fas fa-times-circle"></i> <span><?php echo htmlspecialchars($llmsWriteError)?></span></div>
	<?php } ?>

	<?php if (!empty($llmsConfirmNeeded)) { ?>
		<div class="aiv-note aiv-note-warn">
			<i class="fas fa-exclamation-triangle"></i>
			<span>
				<?php echo $spTextAIV['llmsoverwritenotice'] ?? 'llms.txt already exists and wasn\'t generated by SEO Panel. Regenerating will overwrite it.'?>
				<form id="llms_confirm_form" onsubmit="return false;">
					<input type="hidden" name="sec" value="regenerate-llms">
					<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
					<input type="hidden" name="confirm_overwrite" value="1">
				</form>
				<br><br>
				<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'llms_confirm_form', 'content')" class="aiv-btn aiv-btn-danger">
					<?php echo $spTextAIV['Yes, overwrite'] ?? 'Yes, overwrite'?>
				</a>
			</span>
		</div>
	<?php } elseif (!empty($docrootStatus['writable'])) { ?>
		<form id="llms_form" onsubmit="return false;">
			<input type="hidden" name="sec" value="regenerate-llms">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
		</form>
		<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'llms_form', 'content')" class="aiv-btn aiv-btn-primary">
			<?php echo $spTextAIV['Regenerate llms.txt'] ?? 'Regenerate llms.txt'?>
		</a>
	<?php } else { ?>
		<button class="aiv-btn aiv-btn-outline disabled" disabled><?php echo $spTextAIV['Regenerate llms.txt'] ?? 'Regenerate llms.txt'?></button>
	<?php } ?>

	<?php if (!empty($websiteInfo['url'])) { $urlParts = parse_url($websiteInfo['url']); if (!empty($urlParts['host'])) { ?>
		<a href="<?php echo (!empty($urlParts['scheme']) ? $urlParts['scheme'] : 'http')?>://<?php echo htmlspecialchars($urlParts['host'])?>/llms.txt" target="_blank" style="margin-left:12px;font-size:13px;">
			<?php echo $spTextAIV['View live llms.txt'] ?? 'View live llms.txt'?>
		</a>
	<?php } } ?>

	<?php if (!empty($localAiAvailable) && empty($websiteInfo['description'])) { ?>
		<div class="aiv-note" style="margin-top:14px;">
			<i class="fas fa-magic"></i>
			<span>
				<?php echo $spTextAIV['llmsdescriptionhint'] ?? 'Paste this into your website\'s Description field (Website Manager) so it appears in llms.txt.'?>
				<br><br>
				<a href="javascript:void(0);" id="aivLlmsSuggestBtn" onclick="aivSuggestLlmsDescription()" class="aiv-btn aiv-btn-outline">
					<i class="fas fa-magic"></i> <?php echo $spTextAIV['Suggest description with Local AI'] ?? 'Suggest description with Local AI'?>
				</a>
				<div id="aivLlmsSuggestResult" hidden style="margin-top:12px;">
					<div class="aiv-code-box">
						<code id="aivLlmsSuggestText"></code>
						<button type="button" id="aivLlmsCopyBtn" class="aiv-btn aiv-btn-outline aiv-code-copy"><i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'?></button>
					</div>
				</div>
			</span>
		</div>
	<?php } ?>
</div>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-user-shield"></i></div>
		<div>
			<div class="aiv-card-title"><?php echo $spTextAIV['AI-Bot Response Headers'] ?? 'AI-Bot Response Headers'?></div>
		</div>
	</div>
	<div class="aiv-note">
		<i class="fas fa-info-circle"></i>
		<span><?php echo $spTextAIV['htaccessnotice'] ?? 'Adds an X-Robots-Tag header for the selected file types, inside a clearly marked block in this site\'s .htaccess - everything else in the file is left untouched. Every save is verified live against your site before it is kept; if the new rules make your site unreachable, they are automatically reverted.'?></span>
	</div>

	<?php if (!empty($htaccessWriteError)) { ?>
		<div class="aiv-note aiv-note-danger"><i class="fas fa-times-circle"></i> <span><?php echo htmlspecialchars($htaccessWriteError)?></span></div>
	<?php } ?>

	<?php if (empty($docrootStatus['writable'])) { ?>
		<p class="text-muted"><?php echo $spTextAIV['Path not writable - view only'] ?? 'Path not writable - view only'?></p>
	<?php } else { ?>
		<form id="htaccess_form" onsubmit="return false;">
			<input type="hidden" name="sec" value="save-htaccess-config">
			<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">

			<div class="aiv-switch-row" style="margin-bottom:14px;">
				<label class="aiv-switch">
					<input type="checkbox" name="htaccess_ai_headers_enabled" value="1" <?php echo !empty($htaccessEnabled) ? 'checked' : ''?>>
					<span class="aiv-switch-track"></span>
				</label>
				<span class="aiv-field-label" style="margin:0;"><?php echo $spText['common']['Active'] ?? 'Active'?></span>
			</div>

			<div class="aiv-chip-group">
				<?php foreach (['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'] as $ext) { ?>
					<label class="aiv-chip">
						<input type="checkbox" name="htaccess_extensions[]" value="<?php echo $ext?>" <?php echo in_array($ext, $htaccessExtensions ?? []) ? 'checked' : ''?>>
						.<?php echo $ext?>
					</label>
				<?php } ?>
			</div>
		</form>
		<a href="javascript:void(0);" onclick="scriptDoLoadPost('aivisibility.php', 'htaccess_form', 'content')" class="aiv-btn aiv-btn-primary">
			<?php echo $spTextAIV['Save & Apply'] ?? 'Save & Apply'?>
		</a>
	<?php } ?>

	<?php if (!empty($htaccessLastWrittenAt)) { ?>
		<p style="font-size:12px;color:#8a8ea3;margin-top:14px;"><?php echo $spTextAIV['Last applied'] ?? 'Last applied'?>: <?php echo htmlspecialchars($htaccessLastWrittenAt)?></p>
	<?php } ?>
	<?php if (!empty($htaccessLastError)) { ?>
		<p style="font-size:12px;color:#c0392b;"><?php echo $spTextAIV['Last error'] ?? 'Last error'?>: <?php echo htmlspecialchars($htaccessLastError)?></p>
	<?php } ?>
</div>

</div>

<script>
(function() {
	// Every action on this page (toggling a rule, saving a form) reloads
	// this whole view via scriptDoLoadPost(), which re-renders from
	// scratch - so the active tab is persisted in localStorage and
	// restored here, rather than always snapping back to "Setup".
	var STORAGE_KEY = 'aivSetupActiveTab';
	var tabs = document.querySelectorAll('#aivTabs .aiv-tab-btn');
	var panels = document.querySelectorAll('.aiv-tab-panel');

	function activateTab(name) {
		tabs.forEach(function(btn) { btn.classList.toggle('active', btn.dataset.tab === name); });
		panels.forEach(function(panel) { panel.hidden = (panel.dataset.tab !== name); });
	}

	// exposed so the "Go to Advanced settings" link inside the Setup tab
	// (for users who'd otherwise never notice the Advanced tab exists)
	// can switch tabs the same way clicking the tab button itself does
	window.aivActivateTab = function(name) {
		activateTab(name);
		try { localStorage.setItem(STORAGE_KEY, name); } catch (e) {}
	};

	tabs.forEach(function(btn) {
		btn.addEventListener('click', function() { window.aivActivateTab(btn.dataset.tab); });
	});

	// a write error/pending confirmation living in the Advanced tab always
	// wins, even over a remembered "Setup" preference - otherwise a user
	// could save a form, get bounced to Setup, and never see why it failed
	var initialTab = <?php echo $advancedNeedsAttention ? "'advanced'" : 'null'?>;
	if (!initialTab) {
		try { initialTab = localStorage.getItem(STORAGE_KEY); } catch (e) {}
	}
	activateTab(initialTab === 'advanced' ? 'advanced' : 'setup');
})();

function aivCopyToClipboard(text, btn) {
	var originalHtml = btn.innerHTML;
	var textarea = document.createElement('textarea');
	textarea.value = text;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	textarea.select();
	document.execCommand('copy');
	document.body.removeChild(textarea);
	btn.innerHTML = '<i class="fas fa-check"></i> <?php echo $spText['common']['Copied'] ?? 'Copied'?>!';
	setTimeout(function() { btn.innerHTML = originalHtml; }, 2000);
}

document.getElementById('aivCopyBtn').addEventListener('click', function() {
	aivCopyToClipboard(document.getElementById('aivSnippet').textContent, this);
});

var aivLlmsCopyBtn = document.getElementById('aivLlmsCopyBtn');
if (aivLlmsCopyBtn) {
	aivLlmsCopyBtn.addEventListener('click', function() {
		aivCopyToClipboard(document.getElementById('aivLlmsSuggestText').textContent, this);
	});
}

function aivSuggestLlmsDescription() {
	var btn = document.getElementById('aivLlmsSuggestBtn');
	var original = btn.innerHTML;
	btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $spTextAIV['Generating...'] ?? 'Generating...'?>';
	fetch('aivisibility.php?sec=suggest-llms-description&website_id=<?php echo intval($websiteId)?>', { credentials: 'same-origin' })
		.then(function(res) { return res.json(); })
		.then(function(data) {
			btn.innerHTML = original;
			if (data.ok && data.suggestion) {
				document.getElementById('aivLlmsSuggestText').textContent = data.suggestion;
				document.getElementById('aivLlmsSuggestResult').hidden = false;
			} else {
				alert(data.error || 'Could not generate a suggestion.');
			}
		})
		.catch(function() {
			btn.innerHTML = original;
			alert('Could not generate a suggestion.');
		});
}

(function pollInstallStatus() {
	fetch('aivisibility.php?sec=installstatus&website_id=<?php echo intval($websiteId)?>', { credentials: 'same-origin' })
		.then(function(res) { return res.json(); })
		.then(function(data) {
			var wrap = document.getElementById('aivInstallStatus');
			if (data.status === 'receiving') {
				wrap.classList.add('is-ready');
				wrap.innerHTML = '<i class="fas fa-check-circle"></i> <span><?php echo $spTextAIV['Receiving data'] ?? 'Receiving data'?></span>';
			}

			var botWrap = document.getElementById('aivBotInstallStatus');
			if (botWrap && data.bot_status === 'receiving') {
				botWrap.classList.add('is-ready');
				botWrap.innerHTML = '<i class="fas fa-check-circle"></i> <span><?php echo $spTextAIV['Receiving data'] ?? 'Receiving data'?></span>';
			}

			if (data.status !== 'receiving' || data.bot_status !== 'receiving') {
				setTimeout(pollInstallStatus, 5000);
			}
		})
		.catch(function() { setTimeout(pollInstallStatus, 10000); });
})();
</script>

<?php } ?>
