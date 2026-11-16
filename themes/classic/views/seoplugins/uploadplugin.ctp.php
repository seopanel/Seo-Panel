<?php
// Fallback to the English literal when these new (7.1.0) keys haven't
// been seeded into the texts table yet - matches every existing install
// until install/data/textlang.sql's seed rows for them reach it via a
// fresh install or a future core upgrade step.
$uploadPluginLabel = !empty($spTextPlugin['Upload Plugin']) ? $spTextPlugin['Upload Plugin'] : 'Upload Plugin';
$pluginZipFileLabel = !empty($spTextPlugin['Plugin Zip File']) ? $spTextPlugin['Plugin Zip File'] : 'Plugin Zip File';
$uploadPluginHelpText = !empty($spTextPlugin['Upload a plugin zip to install a new plugin, or upgrade an existing one of the same name.'])
	? $spTextPlugin['Upload a plugin zip to install a new plugin, or upgrade an existing one of the same name.']
	: 'Upload a plugin zip to install a new plugin, or upgrade an existing one of the same name.';
?>
<?php echo showSectionHead($uploadPluginLabel); ?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $uploadPluginLabel?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $pluginZipFileLabel?>:</td>
		<td class="td_right_col">
			<input type="file" id="plugin_zip_file" name="plugin_zip" accept=".zip">
			<p class="form-text text-muted"><?php echo $uploadPluginHelpText?></p>
			<div id="upload_result_msg"></div>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('seo-plugins-manager.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<a onclick="<?php echo SP_DEMO ? "alertDemoMsg()" : "doUploadPlugin()"?>" href="javascript:void(0);" class="btn btn-primary" id="plugin_upload_submit_btn">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>

<script type="text/javascript">
function doUploadPlugin() {
	var fileInput = document.getElementById('plugin_zip_file');
	var resultDiv = document.getElementById('upload_result_msg');

	if (!fileInput.files || !fileInput.files[0]) {
		resultDiv.innerHTML = '<p class="text-danger">Please select a .zip file first.</p>';
		return;
	}

	var fd = new FormData();
	fd.append('plugin_zip', fileInput.files[0]);
	fd.append('sec', 'upload');

	var submitBtn = document.getElementById('plugin_upload_submit_btn');
	submitBtn.style.pointerEvents = 'none';
	resultDiv.innerHTML = '<p class="text-muted">Uploading...</p>';

	$.ajax({
		url: 'seo-plugins-manager.php',
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
					scriptDoLoad('seo-plugins-manager.php', 'content');
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
