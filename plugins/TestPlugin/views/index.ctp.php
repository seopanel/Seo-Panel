<?php echo showSectionHead($sectionHead); ?>
<form id='search_form'>
<table width="45%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th>Test Plugin Type: </th>
		<td>
			<select name="tp_type">
				<option value="1">active</option>
				<option value="0">inactive</option>
			</select>
		</td>
		<td align='left'>
			<a onclick="testPlugin();<?php echo pluginPOSTMethod('search_form', 'subcontent', 'action=show'); ?>" href="javascript:void(0);">
         		<img border="0" alt="" src="<?php echo SP_IMGPATH?>/show_records.gif"/>
         	</a>
         </td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'>Click Show Records to <b class="testplugin">Show</b> Test Plugin Records.</p>
</div>
