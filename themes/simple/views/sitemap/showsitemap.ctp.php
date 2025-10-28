<?php echo showSectionHead($spTextTools['sitemap-generator']); ?>
<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" class="custom-select">
				<?php foreach($projectList as $list) {?>
					<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextSitemap['Sitemap Type']?>: </th>
		<td>
			<select name="sm_type" class="custom-select">
				<option value="xml">XML</option>
				<option value="txt">Text</option>
				<option value="html">HTML</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextSitemap['Change frequency']?>: </th>
		<td>
			<select name="freq" class="custom-select">
				<option value="">None</option>
				<option value="always">Always</option>
				<option value="hourly">Hourly</option>
				<option value="daily">Daily</option>
				<option value="weekly">Weekly</option>
				<option value="monthly">Monthly</option>
				<option value="yearly">Yearly</option>
				<option value="never">Never</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Priority']?>: </th>
		<td>
			<select name="priority" class="custom-select">
				<option value="0.5">0.5</option>
				<option value="1">1</option>
				<option value="auto">Automatic Priority</option>
			</select>
		</td>
	</tr>
	<tr>
		<th style="vertical-align: text-top;"><?php echo $spTextSitemap['Exclude Url']?>: </th>
		<td>
			<textarea name="exclude_url" class="form-control"><?php echo $post['exclude_url']?></textarea>
			<p style="margin-top: 6px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<p><b>Note:</b> <?php echo $spTextSA['anylinkcontainexcludesitemap']?>.</p>
			<p><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</p>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('sitemap.php', 'search_form', 'subcontent')" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextSitemap['clickproceedsitemap']?>
	</div>
</div>