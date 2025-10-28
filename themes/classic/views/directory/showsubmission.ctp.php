<?php echo showSectionHead($spTextDir['Semi Automatic Directory Submission Tool']); ?>
<form id='search_form'>
<table class="search" style="width: 80%">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td width="20px" style="text-align: right;">
			<input <?php echo empty($_SESSION['no_captcha']) ? "" : "checked"; ?> type="checkbox" name="no_captcha" id="no_captcha" onclick="checkDirectoryFilter('no_captcha', 'directories.php?sec=checkcaptcha', 'tmp')">
		</td>
		<th style="text-align: left;" nowrap="nowrap"><?php echo $spTextDir['Directories with out captcha']?></th>
		<td width="20px" style="text-align: right;">
			<input <?php echo empty($_SESSION['no_reciprocal']) ? "" : "checked"; ?> type="checkbox" name="no_reciprocal" id="no_reciprocal" onclick="checkDirectoryFilter('no_reciprocal', 'directories.php?sec=checkreciprocal', 'tmp')">
		</td>
		<th style="text-align: left;" nowrap="nowrap"><?php echo $spTextDir['Directories with out Reciprocal Link']?></th>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Pagerank']?>: </th>
		<td>
			<select name="pagerank" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				for ($i=0; $i<=10; $i++) {
					?>
					<option value="<?php echo $i?>" <?php echo $selected?>>PR <?php echo $i?></option>
					<?php
				}
				?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['lang']?>:</th>
		<td>
			<select name="lang_code" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($langList as $langInfo) {
				    $selected = ($_SESSION['dirsub_lang'] == $langInfo['lang_code']) ? "selected" : "";
					?>
					<option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
					<?php
				}
				?>
			</select>
		</td>
		<td align='left' colspan="2">
			<a onclick="scriptDoLoadPost('directories.php', 'search_form', 'subcontent')" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Show Details']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note left'><?php echo $spTextDir['selectwebsiteproceed']?>!</p>
</div>