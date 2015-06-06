<?php echo showSectionHead($spTextTools['Generate Backlinks Reports']); ?>
<form id='search_form'>
<table width="400px" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" style='width:170px;' id="website_id">
				<option value="">-- Select --</option>
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td><a href="javascript:void(0);" onclick="scriptDoLoadPost('backlinks.php', 'search_form', 'subcontent', '&sec=generate')" class="actionbut"><?php echo $spText['button']['Proceed']?></a></td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
</div>