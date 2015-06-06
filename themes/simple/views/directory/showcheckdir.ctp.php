<?php echo showSectionHead($spTextDir['Check Directory Status']); ?>
<form id='search_form'>
<table width="36%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" id="stscheck">
				<option value="0"><?php echo $spText['common']['Inactive']?></option>
				<option value="1"><?php echo $spText['common']['Active']?></option>
			</select>
		</td>
		<td style="padding-left: 9px;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('directories.php', 'search_form', 'subcontent', '&sec=startdircheck')" class="actionbut">
			<?php echo $spText['button']['Proceed']?>
			</a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'><?php echo $spTextDir['clicktoproceeddirsts']?></p>
</div>