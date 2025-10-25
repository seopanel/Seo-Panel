<?php echo showSectionHead($spTextTools['Generate Keyword Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox&keyNull=0')" class="custom-select">
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
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php
				echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('generate-reports.php', 'search_form', 'subcontent')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
	<div class="alert alert-warning">
		<i class="fas fa-exclamation-triangle me-2"></i><?php echo $spTextTools['note_report_generation']?>
	</div>
</div>
