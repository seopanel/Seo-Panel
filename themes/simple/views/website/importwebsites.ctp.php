<?php 
echo showSectionHead($spTextPanel['Import Websites']);

if (!empty($msg)) {
	?>
	<p class="dirmsg">
		<font class="success"><?php echo $msg?></font>
	</p>
	<?php 
}
	
$scriptUrl = SP_WEBPATH . "/websites.php";	

if(!empty($validationMsg)){
	?>
	<p class="dirmsg">
		<font class="error"><?php echo $validationMsg?></font>
	</p>
	<?php 
}
?>
<div id='import_website_div'>
<form id="projectform" name="projectform" target="website_import_frame" action="<?php echo $scriptUrl; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="sec" value="import"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['Import Websites']; ?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php if(!empty($isAdmin)){ ?>	
		<tr>
			<td><?php echo $spText['common']['User']?>:</td>
			<td>
				<select name="userid" class="custom-select">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $userSelected){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>						
					<?php }?>
				</select>
			</td>
		</tr>
	<?php }?>
	<tr>
		<td><?php echo $spTextWeb['Website CSV File']?>:</td>
		<td>
			<div class="custom-file">
				<input type="file" name="website_csv_file" id="customFile" class="custom-file-input">
              	<label class="custom-file-label" for="customFile">Choose file</label>
            </div>				
			<br>
			<br>
			<b>&nbsp;CSV format:</b>
			<br>
			&nbsp;name, url, meta title, meta description, meta keywords, status, analytics view id
			<br>
			<br>
			<a href="<?php echo SP_WEBPATH ?>/data/website_import_sample.csv" target="_blank">
				<?php echo $spText['common']['Sample CSV File']?>
			</a>
		</td>
	</tr>
	<tr>
		<td>Delimiter:</td>
		<td>
			<input type="text" name="delimiter" value="<?php echo $delimiter; ?>" size="1" maxlength="1" class="">
		</td>
	</tr>
	<tr>
		<td>Enclosure:</td>
		<td>
			<input type="text" name="enclosure" value='<?php echo $enclosure; ?>' size="1" maxlength="1">
		</td>
	</tr>
	<tr>
		<td>Escape:</td>
		<td>
			<input type="text" name="escape" value='<?php echo $escape; ?>' size="1" maxlength="1">
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : 'startWebsiteImport();'; ?>
         	<a id="website_import_proceed_btn" onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
</div>
<div id="website_import_status" style="display:none; margin:15px 0; color:#666;">
	<i class="fas fa-spinner fa-spin"></i> <?php echo $spTextWeb['Importing, please wait...'] ?? 'Importing, please wait...'; ?>
</div>
<div id="website_import_frame_wrap" style="display:none; border:1px solid #ddd; border-radius:4px; margin-top:10px; padding:10px;">
	<iframe style="border:none; width:100%; min-height:200px;" name="website_import_frame" id="website_import_frame"></iframe>
</div>
<script type="text/javascript">
// bug fix: this form previously gave zero progress/completion feedback -
// the "Proceed" button stayed clickable (real double-submit risk while an
// import was still running) and the response rendered into a bare,
// borderless iframe with no min-height, easy to miss entirely below the
// fold. The iframe's own load event (fires once for the initial blank
// frame, then again when the real response arrives) is what actually
// signals "done" - not a fixed timeout, since a large CSV can take a
// while.
var websiteImportFrameLoadCount = 0;
function startWebsiteImport() {
	var btn = document.getElementById('website_import_proceed_btn');
	btn.classList.add('disabled');
	btn.onclick = null;
	document.getElementById('website_import_status').style.display = '';
	document.getElementById('website_import_frame_wrap').style.display = 'none';
	document.getElementById('projectform').submit();
}
document.getElementById('website_import_frame').onload = function() {
	websiteImportFrameLoadCount++;
	// the first load is this iframe's own initial empty document, before
	// any submit has happened - ignore it, only react to a real response
	if (websiteImportFrameLoadCount < 2) { return; }
	document.getElementById('website_import_status').style.display = 'none';
	var wrap = document.getElementById('website_import_frame_wrap');
	wrap.style.display = '';
	wrap.scrollIntoView({behavior: 'smooth', block: 'nearest'});
	var btn = document.getElementById('website_import_proceed_btn');
	btn.classList.remove('disabled');
	btn.onclick = function() { startWebsiteImport(); };
};
</script>