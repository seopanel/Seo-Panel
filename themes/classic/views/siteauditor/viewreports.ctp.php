<?php echo showSectionHead($spTextTools['Auditor Reports']); ?>
<?php
if(empty($projectId)) {
	showErrorMsg($spTextSA['No active projects found'].'!');
}
$submitJsFunc = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport')";
?>
<!-- Report Type Tabs -->
<style>
.nav-tabs-sm .nav-link {
	padding: 10px 20px !important;
	font-size: 14px !important;
}
</style>

<form id='search_form' onsubmit="<?php echo $submitJsFunc; ?>; return false;">
<input type="hidden" name="report_type" value="<?php echo $repType?>">
<ul class="nav nav-tabs nav-tabs-sm" role="tablist" style="margin-bottom: 15px;">
	<?php foreach($reportTypes as $type => $label){?>
		<li role="presentation" class="nav-item">
			<a href="javascript:void(0);" data-value="<?php echo $type?>"
			   class="nav-link <?php echo ($repType == $type) ? 'active' : ''?>">
				<?php echo $label?>
			</a>
		</li>
	<?php }?>
</ul>

<table class="search" style="width: 100%;">
	<tr>
		<th><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<?php foreach($projectList as $list) {?>
					<?php if($list['id'] == $projectId) {?>
						<option value="<?php echo $list['id']?>" selected="selected"><?php echo $list['name']?></option>
					<?php } else {?>
						<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextSA['Crawled']?>: </th>
		<td>
			<select name="crawled" id="crawled" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" selected><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4 search_box" style="display: <?php echo ($repType != 'rp_summary') ? '' : 'none'; ?>"><?php echo $spTextSA['Page Link']?>: </th>
		<td class="search_box" style="display: <?php echo ($repType != 'rp_summary') ? '' : 'none'; ?>">
			<input type="text" name="page_url" value="" onblur="<?php echo $submitJsFunc?>" class="form-control">
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $submitJsFunc?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th>Robots Blocked: </th>
		<td>
			<select name="blocked_by_robots" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">AI Bot Allowed: </th>
		<td>
			<select name="ai_robot_allowed" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Mobile Friendly: </th>
		<td>
			<select name="mobile_friendly" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th>HTTPS Secure: </th>
		<td>
			<select name="https_secure" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Has OG Tags: </th>
		<td>
			<select name="has_og_tags" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Has Twitter Cards: </th>
		<td>
			<select name="has_twitter_cards" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1"><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th><?php echo $spTextSA['Discovered Via']?>: </th>
		<td>
			<select name="discovered_via" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="crawl">Crawl</option>
				<option value="sitemap">Sitemap</option>
				<option value="robots">Robots.txt</option>
				<option value="canonical">Canonical</option>
				<option value="import">Import</option>
			</select>
		</td>
		<td colspan="4"></td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<script><?php echo $submitJsFunc?></script>
</div>

<script type="text/javascript">
// Handle nav tabs clicks
$(document).on('click', '.nav-tabs .nav-link', function () {
	$('.nav-tabs .nav-link').removeClass('active');
	$(this).addClass('active');
	var rpType = $(this).data('value');
	$('input[name=report_type]').val(rpType);
	
	if(rpType == 'rp_links') {
		$('.filter-row-rp_links').show();
	} else { 
		$('.filter-row-rp_links').hide();
	}
	
	if(rpType == 'rp_summary') {
		$('.search_box').hide();
	} else {
		$('.search_box').show();
	}
	
	<?php echo $submitJsFunc?>
});
</script>
