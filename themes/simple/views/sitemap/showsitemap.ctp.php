<?php echo showSectionHead($spTextTools['sitemap-generator']); ?>
<form id='search_form'>
<table width="98%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>				
		<th><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" style="width: 150px;">
				<?php foreach($projectList as $list) {?>
					<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
				<?php }?>
			</select>
		</td>
	</tr>	
	<tr>
		<th><?php echo $spTextSitemap['Sitemap Type']?>: </th>
		<td>
			<select name="sm_type" style="width:150px;">
				<option value="xml">XML</option>
				<option value="txt">Text</option>
				<option value="html">HTML</option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextSitemap['Change frequency']?>: </th>
		<td>
			<select name="freq" style="width:150px;">
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
			<select name="priority" style="width:150px;">
				<option value="0.5">0.5</option>
				<option value="1">1</option>
				<option value="auto">Automatic Priority</option>
			</select>
		</td>
	</tr>		
	<tr>
		<th style="vertical-align: text-top;padding-top: 10px;"><?php echo $spTextSitemap['Exclude Url']?>: </th>
		<td>
			<textarea name="exclude_url"><?php echo $post['exclude_url']?></textarea>
			<p style="margin-top: 6px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<p><b>Note:</b> <?php echo $spTextSA['anylinkcontainexcludesitemap']?>.</p>
			<p><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</p>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td style="padding-left: 9px;padding-top: 10px;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('sitemap.php', 'search_form', 'subcontent')" class="actionbut"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<br>
<div id='subcontent'>
	<p class='note' id='proceed'><?php echo $spTextSitemap['clickproceedsitemap']?>.</p>
</div>