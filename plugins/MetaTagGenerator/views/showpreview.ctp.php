<?php
$pvTitle = htmlspecialchars(stripslashes($websiteInfo['title']));
$pvDescription = htmlspecialchars(stripslashes($websiteInfo['description']));
$pvUrl = htmlspecialchars(stripslashes($websiteInfo['url']));
$pvDomain = htmlspecialchars(parse_url($websiteInfo['url'], PHP_URL_HOST) ?: $websiteInfo['url']);
?>
<table id="cust_tab">
	<tr class="form_head">
		<th width='30%'>Preview Content</th>
		<th>&nbsp;</th>
	</tr>
	<tr class="form_data">
		<td>Title:</td>
		<td>
			<input type="text" id="pvTitleInput" value="<?php echo $pvTitle?>" class="form-control" oninput="mtgUpdatePreview()">
			<p><span id="pvTitleCount">0</span>/60 characters (Google typically truncates titles beyond this)</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Description:</td>
		<td>
			<textarea id="pvDescriptionInput" class="form-control" oninput="mtgUpdatePreview()"><?php echo $pvDescription?></textarea>
			<p><span id="pvDescCount">0</span>/160 characters (Google typically truncates descriptions beyond this)</p>
		</td>
	</tr>
	<tr class="form_data">
		<td>Share Image URL:</td>
		<td>
			<input type="text" id="pvImageInput" value="" class="form-control" placeholder="https://yoursite.com/share-image.jpg" oninput="mtgUpdatePreview()">
			<p>Optional - shown on the social share card below only.</p>
		</td>
	</tr>
</table>

<h4 style="margin-top:25px;"><i class="fab fa-google"></i> Google Search Preview</h4>
<div style="max-width:600px; font-family:arial,sans-serif; padding:15px; border:1px solid #dfe1e5; border-radius:8px; background:#fff;">
	<div style="color:#202124; font-size:14px;"><?php echo $pvDomain?></div>
	<div id="serpTitle" style="color:#1a0dab; font-size:20px; line-height:1.3; margin:2px 0;"></div>
	<div style="color:#4d5156; font-size:14px;"><?php echo $pvUrl?></div>
	<div id="serpDesc" style="color:#4d5156; font-size:14px; line-height:1.4; margin-top:2px;"></div>
</div>

<h4 style="margin-top:25px;"><i class="fas fa-share-alt"></i> Social Share Preview</h4>
<div style="max-width:500px; border:1px solid #dadde1; border-radius:8px; overflow:hidden; font-family:Helvetica,Arial,sans-serif;">
	<img id="ogImagePreview" style="width:100%; display:none; max-height:260px; object-fit:cover;">
	<div style="padding:10px 12px; background:#f2f3f5;">
		<div style="text-transform:uppercase; font-size:12px; color:#65676b;"><?php echo $pvDomain?></div>
		<div id="ogTitlePreview" style="font-weight:600; color:#050505; font-size:16px; margin:2px 0;"></div>
		<div id="ogDescPreview" style="font-size:14px; color:#65676b;"></div>
	</div>
</div>

<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="<?php echo pluginGETMethod('action=preview')?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
    	</td>
	</tr>
</table>

<script type="text/javascript">
function mtgUpdatePreview() {
	var title = document.getElementById('pvTitleInput').value;
	var desc = document.getElementById('pvDescriptionInput').value;
	var image = document.getElementById('pvImageInput').value;

	document.getElementById('pvTitleCount').innerText = title.length;
	document.getElementById('pvTitleCount').style.color = title.length > 60 ? '#d93025' : '#3c4043';
	document.getElementById('pvDescCount').innerText = desc.length;
	document.getElementById('pvDescCount').style.color = desc.length > 160 ? '#d93025' : '#3c4043';

	document.getElementById('serpTitle').innerText = title.substring(0, 70) || '(no title)';
	document.getElementById('serpDesc').innerText = desc.substring(0, 165) || '(no description)';

	document.getElementById('ogTitlePreview').innerText = title || '(no title)';
	document.getElementById('ogDescPreview').innerText = desc.substring(0, 200) || '(no description)';

	var img = document.getElementById('ogImagePreview');
	if (image) {
		img.src = image;
		img.style.display = 'block';
	} else {
		img.style.display = 'none';
	}
}
mtgUpdatePreview();
</script>
