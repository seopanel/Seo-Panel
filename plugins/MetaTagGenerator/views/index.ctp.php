<?php echo showSectionHead($sectionHead); ?>
<form id='search_form'>
<table width="56%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th>Website: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td align='left'>
			<a onclick="<?php echo pluginPOSTMethod('search_form', 'subcontent', 'action=show'); ?>" href="javascript:void(0);">
         		<img border="0" alt="" src="<?php echo SP_IMGPATH?>/show_records.gif"/>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'>Select a <b>Website</b> to <b>Generate</b> Meta Tags.</p>
</div>
