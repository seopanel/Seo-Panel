<?php echo showSectionHead($spTextTools['Generate Keyword Reports']); ?>
<form id='search_form'>
<table width="700px" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" style='width:170px;' id="website_id" onchange="doLoad('website_id', 'keywords.php', 'keyword_area', 'sec=keywordbox&keyNull=1')">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach ($websiteList as $websiteInfo) {
    ?>
					<?php if ($websiteInfo['id'] == $websiteId) {
    ?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php 
} else {
    ?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php 
}
    ?>
				<?php 
}?>
			</select>
		</td>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td id="keyword_area" colspan='2'>
			<?php echo $this->render('keyword/keywordselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php 
                echo $this->render('searchengine/seselectbox', 'ajax');
            ?>
		</td>
		<td><a href="javascript:void(0);" onclick="scriptDoLoadPost('generate-reports.php', 'search_form', 'subcontent')" class="actionbut"><?php echo $spText['button']['Proceed']?></a></td>
		<td>&nbsp;</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
</div>