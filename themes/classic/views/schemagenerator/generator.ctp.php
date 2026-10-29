<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Schema Markup Generator'] ?? 'AI Schema Markup Generator'); ?>

<style>
/* Page-scoped - not shared with other AI Visibility pages via _styles.ctp.php,
   since this is the only page with a long multi-field, multi-schema-type
   form (raw Bootstrap .form-group/.form-control/<label> previously, same
   plain look the rest of AI Visibility moved away from already). */
.sg-type-pill {
	display: inline-flex; align-items: center; gap: 7px;
	padding: 8px 16px; border-radius: 9px; margin-right: 8px; margin-bottom: 8px;
	font-size: 13.5px; font-weight: 600; text-decoration: none;
	border: 1px solid #d8dee8; background: #fff; color: #4a4e69;
	transition: all 0.15s ease;
}
.sg-type-pill:hover { border-color: #4a8fc7; color: #4a8fc7; text-decoration: none; }
.sg-type-pill.sg-type-active {
	background: linear-gradient(135deg, #4a8fc7 0%, #6faee0 100%);
	border-color: #4a8fc7; color: #fff;
	box-shadow: 0 2px 6px rgba(74, 143, 199, 0.35);
}
.sg-form .form-group { margin-bottom: 14px; }
.sg-form label {
	display: block; font-size: 11.5px; font-weight: 700; text-transform: uppercase;
	letter-spacing: 0.03em; color: #5a6372; margin-bottom: 4px;
}
.sg-form .form-control {
	border: 1px solid #d8dee8; border-radius: 7px; padding: 8px 11px;
	font-size: 13.5px; background: #fafbfd;
	transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.sg-form .form-control:focus {
	border-color: #4a8fc7; background: #fff;
	box-shadow: 0 0 0 3px rgba(74, 143, 199, 0.12); outline: none;
}
.sg-faq-row { border: 1px solid #eef0f5; border-radius: 10px; padding: 14px; margin-bottom: 10px; background: #fafbfd; }
.sg-markup-wrap { position: relative; margin-top: 10px; }
.sg-copy-btn {
	position: absolute; top: 10px; right: 10px;
	background: #4a8fc7; color: #fff; border: none;
	padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;
}
.sg-copy-btn:hover { background: #3d7fb5; }
</style>

<?php if (!empty($noWebsites)) { ?>
	<?php echo showNoRecordsList(0); ?>
<?php } else { ?>

<div class="aiv-note">
	<i class="fas fa-info-circle"></i>
	<span>
		<?php echo $spTextAIV['Structured data (JSON-LD) is the fact layer AI answer engines and Google AI Overview read directly. Fill in a type below, paste the generated script into your page\'s'] ?? 'Structured data (JSON-LD) is the fact layer AI answer engines and Google AI Overview read directly. Fill in a type below, paste the generated script into your page\'s'?> <code>&lt;head&gt;</code>. <?php echo $spTextAIV['Nothing here is sent anywhere - it only runs on this server.'] ?? 'Nothing here is sent anywhere - it only runs on this server.'?>
	</span>
</div>

<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select class="custom-select" onchange="scriptDoLoad('schema-generator.php', 'content', '&website_id='+this.value+'&schema_type=<?php echo urlencode($schemaType)?>')">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo htmlspecialchars($websiteInfo['name'])?></option>
				<?php } ?>
			</select>
		</td>
	</tr>
</table>

<div class="aiv-card">
	<div class="aiv-card-header">
		<div class="aiv-card-icon"><i class="fas fa-sitemap"></i></div>
		<div class="aiv-card-title"><?php echo $spTextAIV['Schema Type'] ?? 'Schema Type'?></div>
	</div>
	<?php $sgTypeIcons = ['Organization' => 'fa-building', 'LocalBusiness' => 'fa-store', 'Article' => 'fa-newspaper', 'FAQPage' => 'fa-question-circle']; ?>
	<div style="margin-bottom:18px;">
		<?php foreach ($schemaTypes as $type) { ?>
			<a href="javascript:void(0);" onclick="scriptDoLoad('schema-generator.php', 'content', '&website_id=<?php echo intval($websiteId)?>&schema_type=<?php echo urlencode($type)?>')"
				class="sg-type-pill <?php echo ($type === $schemaType) ? 'sg-type-active' : ''?>">
				<i class="fas <?php echo $sgTypeIcons[$type] ?? 'fa-code'?>"></i>
				<?php echo htmlspecialchars($type)?>
				<?php if (in_array($type, $savedTypes, true)) { ?><i class="fas fa-check-circle" style="color:<?php echo ($type === $schemaType) ? '#fff' : '#1f9d55'?>;"></i><?php } ?>
			</a>
		<?php } ?>
	</div>

	<form id="sg_form" class="sg-form" onsubmit="return false;">
		<input type="hidden" name="sec" value="generate">
		<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
		<input type="hidden" name="schema_type" value="<?php echo htmlspecialchars($schemaType)?>">

		<?php if ($schemaType === 'Organization') { ?>
			<div class="form-group"><label><?php echo $spText['common']['Name'] ?? 'Name'?></label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($fieldData['name'] ?? '')?>"></div>
			<div class="form-group"><label>URL</label><input type="text" name="url" class="form-control" value="<?php echo htmlspecialchars($fieldData['url'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spText['common']['Description'] ?? 'Description'?></label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($fieldData['description'] ?? '')?></textarea></div>
			<div class="form-group"><label><?php echo $spTextAIV['Logo URL'] ?? 'Logo URL'?></label><input type="text" name="logo" class="form-control" value="<?php echo htmlspecialchars($fieldData['logo'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spTextAIV['Social profile URLs (comma-separated)'] ?? 'Social profile URLs (comma-separated)'?></label><input type="text" name="sameAs" class="form-control" value="<?php echo htmlspecialchars($fieldData['sameAs'] ?? '')?>" placeholder="https://twitter.com/you, https://linkedin.com/company/you"></div>

		<?php } else if ($schemaType === 'LocalBusiness') { ?>
			<div class="form-group"><label><?php echo $spText['common']['Name'] ?? 'Name'?></label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($fieldData['name'] ?? '')?>"></div>
			<div class="form-group"><label>URL</label><input type="text" name="url" class="form-control" value="<?php echo htmlspecialchars($fieldData['url'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spText['common']['Description'] ?? 'Description'?></label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($fieldData['description'] ?? '')?></textarea></div>
			<div class="form-group"><label><?php echo $spTextAIV['Image URL'] ?? 'Image URL'?></label><input type="text" name="image" class="form-control" value="<?php echo htmlspecialchars($fieldData['image'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spTextAIV['Street Address'] ?? 'Street Address'?></label><input type="text" name="street_address" class="form-control" value="<?php echo htmlspecialchars($fieldData['street_address'] ?? '')?>"></div>
			<div class="form-row" style="display:flex; gap:10px;">
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['City'] ?? 'City'?></label><input type="text" name="locality" class="form-control" value="<?php echo htmlspecialchars($fieldData['locality'] ?? '')?>"></div>
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['State/Region'] ?? 'State/Region'?></label><input type="text" name="region" class="form-control" value="<?php echo htmlspecialchars($fieldData['region'] ?? '')?>"></div>
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['Postal Code'] ?? 'Postal Code'?></label><input type="text" name="postal_code" class="form-control" value="<?php echo htmlspecialchars($fieldData['postal_code'] ?? '')?>"></div>
			</div>
			<div class="form-group"><label><?php echo $spTextAIV['Country'] ?? 'Country'?></label><input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($fieldData['country'] ?? '')?>"></div>
			<div class="form-row" style="display:flex; gap:10px;">
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['Telephone'] ?? 'Telephone'?></label><input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($fieldData['telephone'] ?? '')?>"></div>
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['Price Range (e.g. $$)'] ?? 'Price Range (e.g. $$)'?></label><input type="text" name="price_range" class="form-control" value="<?php echo htmlspecialchars($fieldData['price_range'] ?? '')?>"></div>
			</div>

		<?php } else if ($schemaType === 'Article') { ?>
			<div class="form-group"><label><?php echo $spTextAIV['Headline'] ?? 'Headline'?></label><input type="text" name="headline" class="form-control" value="<?php echo htmlspecialchars($fieldData['headline'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spText['common']['Description'] ?? 'Description'?></label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($fieldData['description'] ?? '')?></textarea></div>
			<div class="form-group"><label><?php echo $spTextAIV['Image URL'] ?? 'Image URL'?></label><input type="text" name="image" class="form-control" value="<?php echo htmlspecialchars($fieldData['image'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spTextAIV['Author Name'] ?? 'Author Name'?></label><input type="text" name="author_name" class="form-control" value="<?php echo htmlspecialchars($fieldData['author_name'] ?? '')?>"></div>
			<div class="form-row" style="display:flex; gap:10px;">
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['Date Published'] ?? 'Date Published'?></label><input type="date" name="date_published" class="form-control" value="<?php echo htmlspecialchars($fieldData['date_published'] ?? '')?>"></div>
				<div class="form-group" style="flex:1;"><label><?php echo $spTextAIV['Date Modified'] ?? 'Date Modified'?></label><input type="date" name="date_modified" class="form-control" value="<?php echo htmlspecialchars($fieldData['date_modified'] ?? '')?>"></div>
			</div>
			<div class="form-group"><label><?php echo $spTextAIV['Publisher Name'] ?? 'Publisher Name'?></label><input type="text" name="publisher_name" class="form-control" value="<?php echo htmlspecialchars($fieldData['publisher_name'] ?? '')?>"></div>
			<div class="form-group"><label><?php echo $spTextAIV['Publisher Logo URL'] ?? 'Publisher Logo URL'?></label><input type="text" name="publisher_logo" class="form-control" value="<?php echo htmlspecialchars($fieldData['publisher_logo'] ?? '')?>"></div>

		<?php } else if ($schemaType === 'FAQPage') { ?>
			<div id="sg_faq_rows">
				<?php $pairs = !empty($fieldData['pairs']) ? $fieldData['pairs'] : [['question' => '', 'answer' => '']]; ?>
				<?php foreach ($pairs as $pair) { ?>
					<div class="sg-faq-row">
						<div class="form-group"><label><?php echo $spTextAIV['Question'] ?? 'Question'?></label><input type="text" name="question[]" class="form-control" value="<?php echo htmlspecialchars($pair['question'] ?? '')?>"></div>
						<div class="form-group" style="margin-bottom:0;"><label><?php echo $spTextAIV['Answer'] ?? 'Answer'?></label><textarea name="answer[]" class="form-control" rows="2"><?php echo htmlspecialchars($pair['answer'] ?? '')?></textarea></div>
					</div>
				<?php } ?>
			</div>
			<a href="javascript:void(0);" onclick="sgAddFaqRow()" class="btn btn-outline-secondary btn-sm" style="margin-bottom:15px;">
				<i class="fas fa-plus"></i> <?php echo $spTextAIV['Add Question'] ?? 'Add Question'?>
			</a>
		<?php } ?>

		<div>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('schema-generator.php', 'sg_form', 'content')" class="btn btn-primary">
				<i class="fas fa-magic"></i> <?php echo $spTextAIV['Generate & Save'] ?? 'Generate & Save'?>
			</a>
			<?php if (in_array($schemaType, $savedTypes, true)) { ?>
				<form id="sg_remove_form" onsubmit="return false;" style="display:inline;">
					<input type="hidden" name="sec" value="remove">
					<input type="hidden" name="website_id" value="<?php echo intval($websiteId)?>">
					<input type="hidden" name="schema_type" value="<?php echo htmlspecialchars($schemaType)?>">
				</form>
				<a onclick="confirmSubmit('schema-generator.php', 'sg_remove_form', 'content')" href="javascript:void(0);" class="btn btn-danger">
					<?php echo $spText['button']['Delete'] ?? 'Delete'?>
				</a>
			<?php } ?>
		</div>
	</form>
</div>

<?php if (!empty($jsonLdOutput)) { ?>
	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-code"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Generated Markup'] ?? 'Generated Markup'?></div>
		</div>
		<div class="sg-markup-wrap">
			<button class="sg-copy-btn" id="sgCopyBtn" type="button"><i class="fas fa-copy"></i> <?php echo $spText['button']['Copy'] ?? 'Copy'; ?></button>
			<pre id="sgJsonLdOutput" style="background:#282c34; color:#abb2bf; padding:15px; border-radius:8px; overflow-x:auto; font-size:12px; margin:0;">&lt;script type="application/ld+json"&gt;
<?php echo htmlspecialchars($jsonLdOutput); ?>
&lt;/script&gt;</pre>
		</div>
	</div>
<?php } ?>

<?php } ?>

<script>
function sgAddFaqRow() {
	var container = document.getElementById('sg_faq_rows');
	if (!container) return;
	var row = document.createElement('div');
	row.className = 'sg-faq-row';
	row.innerHTML = '<div class="form-group"><label>Question</label><input type="text" name="question[]" class="form-control"></div>'
		+ '<div class="form-group" style="margin-bottom:0;"><label>Answer</label><textarea name="answer[]" class="form-control" rows="2"></textarea></div>';
	container.appendChild(row);
}

var sgCopyBtn = document.getElementById('sgCopyBtn');
if (sgCopyBtn) {
	sgCopyBtn.addEventListener('click', function() {
		var btn = this;
		var text = document.getElementById('sgJsonLdOutput').textContent;
		var textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.opacity = '0';
		document.body.appendChild(textarea);
		textarea.select();
		document.execCommand('copy');
		document.body.removeChild(textarea);
		var original = btn.innerHTML;
		btn.innerHTML = '<i class="fas fa-check"></i> Copied';
		setTimeout(function() { btn.innerHTML = original; }, 1500);
	});
}
</script>
