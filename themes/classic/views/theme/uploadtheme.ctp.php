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
$dropHereLabel = !empty($spTextTheme['Drag & drop a .zip file here, or click to browse'])
	? $spTextTheme['Drag & drop a .zip file here, or click to browse']
	: 'Drag & drop a .zip file here, or click to browse';
?>
<?php echo showSectionHead($uploadThemeLabel); ?>
<div class="card shadow-sm sp-upload-card" style="max-width:640px;margin:0 auto;">
	<div class="card-header card-header-gradient-blue d-flex align-items-center">
		<i class="fas fa-palette"></i>
		<h4><?php echo $uploadThemeLabel?></h4>
	</div>
	<div class="card-body" style="padding:24px;">
		<div id="theme_drop_zone" class="sp-upload-dropzone" onclick="document.getElementById('theme_zip_file').click();">
			<input type="file" id="theme_zip_file" name="theme_zip" accept=".zip" style="display:none;">
			<div id="theme_drop_zone_default">
				<i class="fas fa-cloud-upload-alt sp-upload-icon"></i>
				<p class="sp-upload-title"><?php echo $themeZipFileLabel?></p>
				<p class="sp-upload-sub"><?php echo $dropHereLabel?></p>
			</div>
			<div id="theme_selected_file" class="sp-upload-selected" style="display:none;">
				<i class="fas fa-file-archive"></i>
				<span id="theme_selected_file_name"></span>
				<a href="javascript:void(0);" onclick="event.stopPropagation(); clearThemeUpload();" class="sp-upload-remove"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<p class="form-text text-muted mt-2"><?php echo $uploadThemeHelpText?></p>
		<div class="sp-upload-progress" id="theme_upload_progress" style="display:none;">
			<div class="sp-upload-progress-bar" id="theme_upload_progress_bar"></div>
		</div>
		<div id="upload_result_msg" class="mt-2"></div>
	</div>
</div>
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
(function() {
	var dropZone = document.getElementById('theme_drop_zone');
	var fileInput = document.getElementById('theme_zip_file');

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
			showSelectedThemeFile();
		}
	});
	fileInput.addEventListener('change', showSelectedThemeFile);
})();

function showSelectedThemeFile() {
	var fileInput = document.getElementById('theme_zip_file');
	if (fileInput.files && fileInput.files[0]) {
		document.getElementById('theme_selected_file_name').textContent = fileInput.files[0].name;
		document.getElementById('theme_drop_zone_default').style.display = 'none';
		document.getElementById('theme_selected_file').style.display = 'flex';
	}
}

function clearThemeUpload() {
	var fileInput = document.getElementById('theme_zip_file');
	fileInput.value = '';
	document.getElementById('theme_drop_zone_default').style.display = '';
	document.getElementById('theme_selected_file').style.display = 'none';
	document.getElementById('upload_result_msg').innerHTML = '';
}

function doUploadTheme() {
	var fileInput = document.getElementById('theme_zip_file');
	var resultDiv = document.getElementById('upload_result_msg');

	if (!fileInput.files || !fileInput.files[0]) {
		resultDiv.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Please select a .zip file first.</p>';
		return;
	}

	var fd = new FormData();
	fd.append('theme_zip', fileInput.files[0]);
	fd.append('sec', 'upload');

	var submitBtn = document.getElementById('theme_upload_submit_btn');
	var progressWrap = document.getElementById('theme_upload_progress');
	var progressBar = document.getElementById('theme_upload_progress_bar');
	submitBtn.style.pointerEvents = 'none';
	resultDiv.innerHTML = '';
	progressWrap.style.display = 'block';
	progressBar.style.width = '0%';

	$.ajax({
		url: 'themes-manager.php',
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
					scriptDoLoad('themes-manager.php', 'content');
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
