<?php echo showSectionHead($spTextDir['Check Directory Submission Status']); ?>
<form id='search_form'>
<table width="45%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td align='left'>
			<a onclick="<?php echo $onClick?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
         </td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'><?php echo $spTextDir['selectwebsiteschecksub']?></p>
</div>