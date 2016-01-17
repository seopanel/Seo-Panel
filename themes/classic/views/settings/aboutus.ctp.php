<?php echo showSectionHead($spTextPanel['About Us']); ?>
<table width="80%" cellspacing="0" cellpadding="0" class="summary">
	<tr><td class="topheader" colspan="2"><?php echo $spText['label']['Developers']?></td></tr>
	<tr>
		<td class="content" style="border-left: none;width: 30%">PHP, MYSQL, AJAX, HTML</td>					
		<td class="contentmid" style="text-align: left;padding-left: 10px">Geo Varghese</td>
	</tr>
	<tr>
		<td class="content" style="border-left: none;width: 30%">PHP, MYSQL, JQUERY</td>					
		<td class="contentmid" style="text-align: left;padding-left: 10px">Deepthy Rao</td>
	</tr>
</table>

<br><br>
<table width="80%" cellspacing="0" cellpadding="0" class="summary">
	<tr><td class="topheader" colspan="2"><?php echo $spText['label']['Sponsors']?></td></tr>
	<?php echo $sponsors?>
	<tr>
		<td class="contentmid" colspan="2" style="border-left: none;font-size: 13px;text-align: left">
			<a target="_blank" href="<?php echo SP_DONATE_LINK?>">
			    <?php echo str_replace('$100', '$250', $spTextSettings['Click here to become a sponsor for Seo Panel']); ?>
			    <?php echo $spTextSettings['getallpluginfree']; ?>
		   </a>
		</td>
	</tr>
</table>

<br><br>

<table width="80%" cellspacing="0" cellpadding="0" class="summary">
	<tr><td class="topheader" colspan="2"><?php echo $spText['label']['Translators']?></td></tr>
	<?php foreach ($transList as $transInfo) {
    ?>
		<tr>
			<td class="content" style="border-left: none;width: 30%"><?php echo $transInfo['lang_name']?></td>					
			<td class="contentmid" style="text-align: left;padding-left: 10px"><?php echo $transInfo['trans_name']?>, <a href="<?php echo $transInfo['trans_website']?>" target="_blank"><?php echo $transInfo['trans_company']?></a></td>
		</tr>
	<?php 
}?>
</table>
