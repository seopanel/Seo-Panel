<?php echo showSectionHead($sectionHead); ?>
<form id='preview_search_form'>
<table class="search">
	<tr>
		<th>Website: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td>
			<a onclick="<?php echo pluginPOSTMethod('preview_search_form', 'subcontent', 'action=showPreview'); ?>" href="javascript:void(0);" class="btn btn-secondary">
         		<?php echo $spText['button']['Show Records']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'>Select a <b>Website</b> to preview how it looks in Google search results and when shared on social media.</p>
</div>
