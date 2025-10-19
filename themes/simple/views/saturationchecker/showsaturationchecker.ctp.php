<?php echo showSectionHead($spTextSat['Quick Search Engine Saturation Checker']); ?>
<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['common']['Website']?>: </th>
		<td>
			<textarea name="website_urls" rows="8" class="form-control"></textarea>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('saturationchecker.php', 'search_form', 'subcontent')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextSat['clickproceedsaturation']?>
	</div>
</div>