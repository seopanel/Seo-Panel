<?php echo showSectionHead($sectionHead); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th>Website: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td>
			<a onclick="<?php echo pluginPOSTMethod('search_form', 'subcontent', 'action=show'); ?>" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Show Records']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'>Select a <b>Website</b> to <b>Generate</b> Meta Tags.</p>
</div>
