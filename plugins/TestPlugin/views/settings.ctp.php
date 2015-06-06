<?php echo showSectionHead($sectionHead); ?>
<form id='search_form'>
<table width="45%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th> Click to <a onclick="<?php echo pluginGETMethod('action=settings&graph=1', 'content'); ?>" href="javascript:void(0);">Show Settings Graph</a></th>
	</tr>
</table>
</form>
<div id='subcontent'>
	<?php if($showGraph){?>
		<img src="<?php echo PLUGIN_IMGPATH?>/graph.gif"></img>
	<?php }?>
</div>
