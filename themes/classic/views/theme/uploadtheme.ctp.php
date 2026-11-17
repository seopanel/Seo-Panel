<?php
// Fallback to the English literal when these new (7.1.0) keys haven't
// been seeded into the texts table yet - matches every existing install
// until install/data/textlang.sql's seed rows for them reach it via a
// fresh install or a future core upgrade step.
$uploadThemeLabel = !empty($spTextTheme['Upload Theme']) ? $spTextTheme['Upload Theme'] : 'Upload Theme';
$themeZipFileLabel = !empty($spTextTheme['Theme Zip File']) ? $spTextTheme['Theme Zip File'] : 'Theme Zip File';
$uploadThemeHelpText = !empty($spTextTheme['Upload a theme zip to install a new theme, or upgrade an existing one of the same name.'])
	? $spTextTheme['Upload a theme zip to install a new theme, or upgrade an existing one of the same name.']
	: 'Upload a theme zip to install a new theme, or upgrade an existing one of the same name.';
?>
<?php echo showSectionHead($uploadThemeLabel); ?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $uploadThemeLabel?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $themeZipFileLabel?>:</td>
		<td class="td_right_col">
			<input type="file" id="theme_zip_file" name="theme_zip" accept=".zip">
			<p class="form-text text-muted"><?php echo $uploadThemeHelpText?></p>
			<div id="upload_result_msg"></div>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('themes-manager.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<a onclick="<?php echo SP_DEMO ? "alertDemoMsg()" : "doUploadTheme()"?>" href="javascript:void(0);" class="btn btn-primary" id="theme_upload_submit_btn">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>

<script type="text/javascript">
function doUploadTheme() {
	var fileInput = document.getElementById('theme_zip_file');
	var resultDiv = document.getElementById('upload_result_msg');

	if (!fileInput.files || !fileInput.files[0]) {
		resultDiv.innerHTML = '<p class="text-danger">Please select a .zip file first.</p>';
		return;
	}

	var fd = new FormData();
	fd.append('theme_zip', fileInput.files[0]);
	fd.append('sec', 'upload');

	var submitBtn = document.getElementById('theme_upload_submit_btn');
	submitBtn.style.pointerEvents = 'none';
	resultDiv.innerHTML = '<p class="text-muted">Uploading...</p>';

	$.ajax({
		url: 'themes-manager.php',
		type: 'post',
		data: fd,
		contentType: false,
		processData: false,
		dataType: 'json',
		success: function(response) {
			submitBtn.style.pointerEvents = '';
			if (response.status === 'success') {
				resultDiv.innerHTML = '<p class="text-success">' + response.message + '</p>';
				setTimeout(function() {
					scriptDoLoad('themes-manager.php', 'content');
				}, 1500);
			} else {
				resultDiv.innerHTML = '<p class="text-danger">' + response.message + '</p>';
			}
		},
		error: function() {
			submitBtn.style.pointerEvents = '';
			resultDiv.innerHTML = '<p class="text-danger">Upload failed - please try again.</p>';
		}
	});
}
</script>
