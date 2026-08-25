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
			<a href="javascript:void(0);" onclick="<?php echo $submitAction?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<div class="alert alert-secondary">
	<i class="fas fa-shield-alt me-2"></i>
	<?php echo $spTextAIV['botverifiednotice'] ?? '"Verified" means the crawler\'s IP passed a reverse-DNS check on your own server at the moment it visited - the same method used to confirm Googlebot. It is not cryptographic proof, so treat this as advisory analytics, not forensic evidence.'?>
</div>

<div id='subcontent'>
	<?php echo $graphContent; ?>

	<h4 style="margin-top:20px;"><?php echo $spTextAIV['Platform breakdown'] ?? 'Platform breakdown'?></h4>
	<table class="list">
		<tr class="listHead">
			<th><?php echo $spTextAIV['Platform'] ?? 'Platform'?></th>
			<th><?php echo $spTextAIV['Verified'] ?? 'Verified'?></th>
			<th><?php echo $spTextAIV['Unverified'] ?? 'Unverified'?></th>
		</tr>
		<?php if (!empty($platformTotals)) { ?>
			<?php foreach ($platformTotals as $platform => $counts) { ?>
				<tr>
					<td><?php echo htmlspecialchars($platform)?></td>
					<td><span class="badge badge-success"><?php echo intval($counts['verified'])?></span></td>
					<td><span class="badge badge-secondary"><?php echo intval($counts['unverified'])?></span></td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<?php echo showNoRecordsList(0); ?>
		<?php } ?>
	</table>

	<h4 style="margin-top:20px;"><?php echo $spTextAIV['Top crawled pages'] ?? 'Top crawled pages'?></h4>
	<table class="list">
		<tr class="listHead">
			<th><?php echo $spTextAIV['Page'] ?? 'Page'?></th>
			<th><?php echo $spTextAIV['Crawls'] ?? 'Crawls'?></th>
		</tr>
		<?php if (!empty($topPages)) { ?>
			<?php foreach ($topPages as $pageInfo) { ?>
				<tr>
					<td><?php echo htmlspecialchars($pageInfo['url_path'])?></td>
					<td><?php echo intval($pageInfo['hits'])?></td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<?php echo showNoRecordsList(0); ?>
		<?php } ?>
	</table>
</div>
