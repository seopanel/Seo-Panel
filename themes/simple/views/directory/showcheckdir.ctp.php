<?php echo showSectionHead($spTextDir['Check Directory Status']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" id="stscheck" class="custom-select">
				<option value="0"><?php echo $spText['common']['Inactive']?></option>
				<option value="1"><?php echo $spText['common']['Active']?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('directories.php', 'search_form', 'subcontent', '&sec=startdircheck')" class="btn btn-secondary">
			<?php echo $spText['button']['Proceed']?>
			</a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='alert alert-info'><?php echo $spTextDir['clicktoproceeddirsts']?></p>
</div>
