<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<?php echo showSectionHead($spTextAIV['AI Bot Crawlers'] ?? 'AI Bot Crawlers'); ?>
<?php $submitAction = "scriptDoLoadPost('aivisibility.php?sec=botreport', 'search_form', 'content')"; ?>
<form id='search_form'>
<input type="hidden" name="sec" value="botreport">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" class="custom-select" onchange="<?php echo $submitAction?>">
				<?php foreach ($websiteList as $websiteInfo) { ?>
					<option value="<?php echo $websiteInfo['id']?>" <?php echo ($websiteInfo['id'] == $websiteId) ? 'selected' : ''?>><?php echo $websiteInfo['name']?></option>
				<?php } ?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display:inline-block;width:45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display:inline-block;width:45%;"/>
			<script type="text/javascript">
			$(function() {
				$("input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
			</script>
		</td>
		<td style="text-align:center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitAction?>" class="aiv-btn aiv-btn-primary"><?php echo $spText['button']['Show Records']?></a>
			<a href="aivisibility.php?sec=export-botreport&website_id=<?php echo intval($websiteId)?>&from_time=<?php echo urlencode($fromTime)?>&to_time=<?php echo urlencode($toTime)?>" class="aiv-btn aiv-btn-outline">
				<i class="fas fa-file-csv"></i> <?php echo $spTextAIV['Export CSV'] ?? 'Export CSV'?>
			</a>
		</td>
	</tr>
</table>
</form>

<div class="aiv-note">
	<i class="fas fa-shield-alt"></i>
	<span><?php echo $spTextAIV['botverifiednotice'] ?? '"Verified" means the crawler\'s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'?></span>
</div>

<div id='subcontent'>
	<?php if (!empty($graphContent)) { ?>
	<div class="aiv-card">
		<?php echo $graphContent; ?>
	</div>
	<?php } ?>

	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-chart-pie"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Platform breakdown'] ?? 'Platform breakdown'?></div>
		</div>
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Verified'] ?? 'Verified'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Unverified'] ?? 'Unverified'?></th>
			</tr>
			<?php if (!empty($platformTotals)) { ?>
				<?php foreach ($platformTotals as $platform => $counts) { ?>
					<tr>
						<td><?php echo htmlspecialchars($platform)?></td>
						<td class="aiv-num"><span class="aiv-badge-soft success"><?php echo intval($counts['verified'])?></span></td>
						<td class="aiv-num"><span class="aiv-badge-soft neutral"><?php echo intval($counts['unverified'])?></span></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<?php echo showNoRecordsList(0); ?>
			<?php } ?>
		</table>
	</div>

	<div class="aiv-card">
		<div class="aiv-card-header">
			<div class="aiv-card-icon"><i class="fas fa-file-alt"></i></div>
			<div class="aiv-card-title"><?php echo $spTextAIV['Top crawled pages'] ?? 'Top crawled pages'?></div>
		</div>
		<table class="aiv-table">
			<tr>
				<th><?php echo $spTextAIV['Page'] ?? 'Page'?></th>
				<th class="aiv-num"><?php echo $spTextAIV['Crawls'] ?? 'Crawls'?></th>
			</tr>
			<?php if (!empty($topPages)) { ?>
				<?php foreach ($topPages as $pageInfo) { ?>
					<tr>
						<td><?php echo htmlspecialchars($pageInfo['url_path'])?></td>
						<td class="aiv-num"><?php echo intval($pageInfo['hits'])?></td>
					</tr>
				<?php } ?>
			<?php } else { ?>
				<?php echo showNoRecordsList(0); ?>
			<?php } ?>
		</table>
	</div>
</div>
