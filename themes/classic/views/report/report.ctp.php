<?php echo showSectionHead($spTextKeyword['Detailed Keyword Position Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox')" class="custom-select">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area">
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
		<td>
			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			  $( function() {
			    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
			  } );
		  	</script>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php echo $this->render('searchengine/seselectbox', 'ajax'); ?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('reports.php', 'search_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php
	if(empty($keywordId)){
		?>
		<div class="alert alert-danger">
			<i class="fas fa-exclamation-circle me-2"></i><?php echo $spText['common']['No Keywords Found']?>!
		</div>
		<?php
		exit;
	}
?>

<div id='subcontent'>
<table width="100%" class="list">
	<tr class="listHead">
		<td width="10%"><?php echo $spText['common']['Date']?></td>
		<td><?php echo $seInfo['domain']?> <?php echo $spText['common']['Results']?></td>
		<td><?php echo $spText['common']['Rank']?></td>
	</tr>
	<?php
	$colCount = 3; 
	if(count($list) > 0) {
		foreach($list as $listInfo) {
            $scriptLink = "sec=show-info&keyId={$listInfo['keyword_id']}&time={$listInfo['time']}&seId=$seId";
            $dateLink = scriptAJAXLinkHref('reports.php', 'subcontent', $scriptLink, date('Y-m-d', $listInfo['time']) );
			?>
			<tr class="<?php echo $class?>">
				<td><?php echo $dateLink; ?></td>
				<td id='seresult'>
					<a href='<?php echo $listInfo['url']?>' target='_blank'><?php echo stripslashes($listInfo['title']);?></a>
					<p><?php echo stripslashes($listInfo['description']);?><p>
					<label><?php echo $listInfo['url']?></label>
				</td>
				<td class="fw-bold"><?php echo $listInfo['rank'].'</b> '. $listInfo['rank_diff']?></td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
</div>