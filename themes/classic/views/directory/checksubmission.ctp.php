<?php echo showSectionHead($spTextDir['Check Directory Submission Status']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td>
			<a onclick="<?php echo $onClick?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='alert alert-info'><?php echo $spTextDir['selectwebsiteschecksub']?></p>
</div>
