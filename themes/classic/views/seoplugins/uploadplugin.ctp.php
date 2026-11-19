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
$dropHereLabel = !empty($spTextPlugin['Drag & drop a .zip file here, or click to browse'])
	? $spTextPlugin['Drag & drop a .zip file here, or click to browse']
	: 'Drag & drop a .zip file here, or click to browse';
?>
<?php echo showSectionHead($uploadPluginLabel); ?>
<div class="card shadow-sm sp-upload-card" style="max-width:640px;margin:0 auto;">
	<div class="card-header card-header-gradient-blue d-flex align-items-center">
		<i class="fas fa-puzzle-piece"></i>
		<h4><?php echo $uploadPluginLabel?></h4>
	</div>
	<div class="card-body" style="padding:24px;">
		<div id="plugin_drop_zone" class="sp-upload-dropzone" onclick="document.getElementById('plugin_zip_file').click();">
			<input type="file" id="plugin_zip_file" name="plugin_zip" accept=".zip" style="display:none;">
			<div id="plugin_drop_zone_default">
				<i class="fas fa-cloud-upload-alt sp-upload-icon"></i>
				<p class="sp-upload-title"><?php echo $pluginZipFileLabel?></p>
				<p class="sp-upload-sub"><?php echo $dropHereLabel?></p>
			</div>
			<div id="plugin_selected_file" class="sp-upload-selected" style="display:none;">
				<i class="fas fa-file-archive"></i>
				<span id="plugin_selected_file_name"></span>
				<a href="javascript:void(0);" onclick="event.stopPropagation(); clearPluginUpload();" class="sp-upload-remove"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<p class="form-text text-muted mt-2"><?php echo $uploadPluginHelpText?></p>
		<div class="sp-upload-progress" id="plugin_upload_progress" style="display:none;">
			<div class="sp-upload-progress-bar" id="plugin_upload_progress_bar"></div>
		</div>
		<div id="upload_result_msg" class="mt-2"></div>
	</div>
</div>
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
(function() {
	var dropZone = document.getElementById('plugin_drop_zone');
	var fileInput = document.getElementById('plugin_zip_file');

	dropZone.addEventListener('dragover', function(e) {
		e.preventDefault();
		dropZone.classList.add('dragover');
	});
	dropZone.addEventListener('dragleave', function() {
		dropZone.classList.remove('dragover');
	});
	dropZone.addEventListener('drop', function(e) {
		e.preventDefault();
		dropZone.classList.remove('dragover');
		if (e.dataTransfer.files && e.dataTransfer.files[0]) {
			fileInput.files = e.dataTransfer.files;
			showSelectedPluginFile();
		}
	});
	fileInput.addEventListener('change', showSelectedPluginFile);
})();

function showSelectedPluginFile() {
	var fileInput = document.getElementById('plugin_zip_file');
	if (fileInput.files && fileInput.files[0]) {
		document.getElementById('plugin_selected_file_name').textContent = fileInput.files[0].name;
		document.getElementById('plugin_drop_zone_default').style.display = 'none';
		document.getElementById('plugin_selected_file').style.display = 'flex';
	}
}

function clearPluginUpload() {
	var fileInput = document.getElementById('plugin_zip_file');
	fileInput.value = '';
	document.getElementById('plugin_drop_zone_default').style.display = '';
	document.getElementById('plugin_selected_file').style.display = 'none';
	document.getElementById('upload_result_msg').innerHTML = '';
}

function doUploadPlugin() {
	var fileInput = document.getElementById('plugin_zip_file');
	var resultDiv = document.getElementById('upload_result_msg');

	if (!fileInput.files || !fileInput.files[0]) {
		resultDiv.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Please select a .zip file first.</p>';
		return;
	}

	var fd = new FormData();
	fd.append('plugin_zip', fileInput.files[0]);
	fd.append('sec', 'upload');

	var submitBtn = document.getElementById('plugin_upload_submit_btn');
	var progressWrap = document.getElementById('plugin_upload_progress');
	var progressBar = document.getElementById('plugin_upload_progress_bar');
	submitBtn.style.pointerEvents = 'none';
	resultDiv.innerHTML = '';
	progressWrap.style.display = 'block';
	progressBar.style.width = '0%';

	$.ajax({
		url: 'seo-plugins-manager.php',
		type: 'post',
		data: fd,
		contentType: false,
		processData: false,
		dataType: 'json',
		xhr: function() {
			var xhr = $.ajaxSettings.xhr();
			if (xhr.upload) {
				xhr.upload.addEventListener('progress', function(e) {
					if (e.lengthComputable) {
						progressBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
					}
				});
			}
			return xhr;
		},
		success: function(response) {
			submitBtn.style.pointerEvents = '';
			progressWrap.style.display = 'none';
			if (response.status === 'success') {
				resultDiv.innerHTML = '<p class="text-success"><i class="fas fa-check-circle"></i> ' + response.message + '</p>';
				setTimeout(function() {
					scriptDoLoad('seo-plugins-manager.php', 'content');
				}, 1500);
			} else {
				resultDiv.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + response.message + '</p>';
			}
		},
		error: function() {
			submitBtn.style.pointerEvents = '';
			progressWrap.style.display = 'none';
			resultDiv.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Upload failed - please try again.</p>';
		}
	});
}
</script>
