<?php echo showSectionHead($spTextTools['Auditor Reports']); ?>
<?php
if(empty($projectId)) {
	showErrorMsg($spTextSA['No active projects found'].'!');
}
$submitJsFunc = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport')";

// Get filter values from URL parameters
$paType = isset($_GET['pa_type']) ? $_GET['pa_type'] : '';
$backlinksFilter = isset($_GET['backlinks_filter']) ? $_GET['backlinks_filter'] : '';
$indexedFilter = isset($_GET['indexed_filter']) ? $_GET['indexed_filter'] : '';
$brockenFilter = isset($_GET['brocken']) ? $_GET['brocken'] : '-1';
$mobileFriendly = isset($_GET['mobile_friendly']) ? $_GET['mobile_friendly'] : '-1';
$httpsSecure = isset($_GET['https_secure']) ? $_GET['https_secure'] : '-1';
$aiRobotAllowed = isset($_GET['ai_robot_allowed']) ? $_GET['ai_robot_allowed'] : '-1';
$hasOgTags = isset($_GET['has_og_tags']) ? $_GET['has_og_tags'] : '-1';
$hasTwitterCards = isset($_GET['has_twitter_cards']) ? $_GET['has_twitter_cards'] : '-1';
$blockedByRobots = isset($_GET['blocked_by_robots']) ? $_GET['blocked_by_robots'] : '-1';
$discoveredVia = isset($_GET['discovered_via']) ? $_GET['discovered_via'] : '';
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
				<option value="-1" <?php echo $blockedByRobots == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $blockedByRobots === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $blockedByRobots === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">AI Bot Allowed: </th>
		<td>
			<select name="ai_robot_allowed" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $aiRobotAllowed == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $aiRobotAllowed === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $aiRobotAllowed === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Mobile Friendly: </th>
		<td>
			<select name="mobile_friendly" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $mobileFriendly == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $mobileFriendly === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $mobileFriendly === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th>HTTPS Secure: </th>
		<td>
			<select name="https_secure" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $httpsSecure == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $httpsSecure === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $httpsSecure === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Has OG Tags: </th>
		<td>
			<select name="has_og_tags" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $hasOgTags == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $hasOgTags === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $hasOgTags === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4">Has Twitter Cards: </th>
		<td>
			<select name="has_twitter_cards" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $hasTwitterCards == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0" <?php echo $hasTwitterCards === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
				<option value="1" <?php echo $hasTwitterCards === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th><?php echo $spTextSA['Discovered Via']?>: </th>
		<td>
			<select name="discovered_via" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="" <?php echo $discoveredVia == '' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="crawl" <?php echo $discoveredVia == 'crawl' ? 'selected' : ''?>>Crawl</option>
				<option value="sitemap" <?php echo $discoveredVia == 'sitemap' ? 'selected' : ''?>>Sitemap</option>
				<option value="robots" <?php echo $discoveredVia == 'robots' ? 'selected' : ''?>>Robots.txt</option>
				<option value="canonical" <?php echo $discoveredVia == 'canonical' ? 'selected' : ''?>>Canonical</option>
				<option value="import" <?php echo $discoveredVia == 'import' ? 'selected' : ''?>>Import</option>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextSA['Page Authority'] ?? 'Page Authority'?>: </th>
		<td>
			<select name="pa_type" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="" <?php echo $paType == '' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="excellent" <?php echo $paType == 'excellent' ? 'selected' : ''?>><?php echo $spTextSA['Excellent Page Authority'] ?? 'Excellent Page Authority'?> (&ge;75)</option>
				<option value="good" <?php echo $paType == 'good' ? 'selected' : ''?>><?php echo $spTextSA['Good Page Authority'] ?? 'Good Page Authority'?> (40-74)</option>
				<option value="low" <?php echo $paType == 'low' ? 'selected' : ''?>><?php echo $spTextSA['Low Page Authority'] ?? 'Low Page Authority'?> (1-39)</option>
				<option value="none" <?php echo $paType == 'none' ? 'selected' : ''?>><?php echo $spTextSA['No Page Authority'] ?? 'No Page Authority'?> (0)</option>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextSA['Has Backlinks'] ?? 'Has Backlinks'?>: </th>
		<td>
			<select name="backlinks_filter" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="" <?php echo $backlinksFilter == '' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="has" <?php echo $backlinksFilter == 'has' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
				<option value="no" <?php echo $backlinksFilter == 'no' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
			</select>
		</td>
	</tr>
	<tr class="filter-row-rp_links" style="display: <?php echo ($repType == 'rp_links') ? 'table-row' : 'none'; ?>">
		<th><?php echo $spTextHome['Indexed'] ?? 'Indexed'?>: </th>
		<td>
			<select name="indexed_filter" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="" <?php echo $indexedFilter == '' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="google_yes" <?php echo $indexedFilter == 'google_yes' ? 'selected' : ''?>><?php echo $spTextHome['Indexed'] ?? 'Indexed'?></option>
				<option value="google_no" <?php echo $indexedFilter == 'google_no' ? 'selected' : ''?>><?php echo $spTextSA['Not Indexed'] ?? 'Not Indexed'?></option>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['label']['Brocken'] ?? 'Broken'?>: </th>
		<td>
			<select name="brocken" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1" <?php echo $brockenFilter == '-1' ? 'selected' : ''?>>-- <?php echo $spText['common']['Select']?> --</option>
				<option value="1" <?php echo $brockenFilter === '1' ? 'selected' : ''?>><?php echo $spText['common']['Yes']?></option>
				<option value="0" <?php echo $brockenFilter === '0' ? 'selected' : ''?>><?php echo $spText['common']['No']?></option>
			</select>
		</td>
		<td colspan="2"></td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<script><?php echo $submitJsFunc?></script>
</div>

<script type="text/javascript">
// Handle nav tabs clicks - use .off() to prevent multiple bindings
$(document).off('click', '.nav-tabs .nav-link').on('click', '.nav-tabs .nav-link', function (e) {
	e.preventDefault();
	e.stopPropagation();

	// Prevent double clicks
	if ($(this).hasClass('active')) {
		return false;
	}

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

	<?php echo $submitJsFunc?>;
	return false;
});
</script>
