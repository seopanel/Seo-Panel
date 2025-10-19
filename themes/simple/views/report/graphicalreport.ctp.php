<?php echo showSectionHead($spTextKeyword['Graphical Keyword Position Reports']); ?>
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
			<?php
				echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('graphical-reports.php', 'search_form', 'content')" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
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
	<?php echo $graphContent; ?>
</div>
