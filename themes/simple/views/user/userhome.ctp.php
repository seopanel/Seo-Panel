<?php
$borderCollapseVal = $pdfVersion ? "border-collapse: collapse;" : "";
if(!empty($printVersion) || !empty($pdfVersion)) {
    $pdfVersion ? showPdfHeader($spTextHome['Account Summary']) : showPrintHeader($spTextHome['Account Summary']);
} else {
    ?>
	<div class="Center" style='width:100%;'>
	<div class="col" style="">
    <?php echo getRoundTabTop(); ?>
    <div id="round_content">
	<div class="SectionHeader">
	<h1 style="text-align:center;border: none;"><?php echo $spTextHome['Account Summary']?></h1>
	</div>
	<br />
	<div style="float: left;width: 300px;margin: 0px 0px 6px 10px;">
		<form name="acc_form" method="post" action="<?php echo SP_WEBPATH?>/">
    	<table width="50%" border="0" cellspacing="0" cellpadding="0" class="actionForm">
    		<tr>
				<th><?php echo $spText['common']['Name']?>: </th>
				<td>
					<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="document.acc_form.submit()">
				</td>

				<?php if(isAdmin()){ ?>
	    			<th><?php echo $spText['common']['User']?>: </th>
	    			<td>
	    				<select name="user_id" id="user_id" onchange="document.acc_form.submit()">
	    					<option value="">-- <?php echo $spText['common']['Select']?> --</option>
	    					<?php foreach($userList as $userInfo){?>
	    						<?php if($userInfo['id'] == $webUserId){?>
	    							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
	    						<?php }else{?>
	    							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
	    						<?php }?>
	    					<?php }?>
	    				</select>
	    			</td>
				<?php } ?>

    			<td>
    				<input class="button" type="submit" name="login" value="<?php echo $spText['button']['Show Records']?>"/>
				</td>
    		</tr>
    	</table>
    	</form>
	</div>
	<div style="float:right;margin-right: 10px;">
		<?php
		$argStr = "&user_id=$webUserId&search_name=".$searchInfo['search_name']
		?>
		<a href="<?php echo SP_WEBPATH?>/index.php?doc_type=pdf<?php echo $argStr?>"><img src="<?php echo SP_IMGPATH?>/icon_pdf.png"></a> &nbsp;
		<a href="<?php echo SP_WEBPATH?>/index.php?doc_type=export<?php echo $argStr?>"><img src="<?php echo SP_IMGPATH?>/icoExport.gif"></a> &nbsp;
		<a target="_blank" href="<?php echo SP_WEBPATH?>/index.php?doc_type=print<?php echo $argStr?>"><img src="<?php echo SP_IMGPATH?>/print_button.gif"></a>
	</div>
<?php }?>

<div class="Block" style="margin-top: 28px;clear: both;">
	<?php
	$colSpan = 11;
	?>
	<table width="100%" cellspacing="0" cellpadding="0" class="summary" style="<?php echo $borderCollapseVal; ?>">
		<tr><td class="topheader" colspan="<?php echo $colSpan?>"><?php echo $spTextHome['Website Statistics']?></td></tr>
		<tr>
			<td class="subheader" style="border: none;" width="5%" rowspan="2"><?php echo $spText['common']['Id']?></td>
			<td class="subheader" rowspan="2"><?php echo $spTextHome['SiteNameUrl']?></td>
			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Ranks']?></td>
			<td class="subheaderdark" colspan="3"><?php echo $spTextHome['Backlinks']?></td>
			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Pages Indexed']?></td>
			<td class="subheaderdark" colspan="2"><?php echo $spTextHome['Directory Submission']?></td>
		</tr>
		<tr>
			<td class="subheader">Google</td>
			<td class="subheader">Alexa</td>
			<td class="subheader">Google</td>
			<td class="subheader">Alexa</td>
			<td class="subheader">Bing</td>
			<td class="subheader">Google</td>
			<td class="subheader">Bing</td>
			<td class="subheader"><?php echo $spText['common']['Total']?></td>
			<td class="subheader"><?php echo $spText['common']['Active']?></td>
		</tr>
		<?php if(count($websiteList) > 0){
		    $mainLink = SP_WEBPATH."/seo-tools.php?menu_sec=";
		    ?>
			<?php foreach($websiteList as $websiteInfo){
			    $rankLink = $mainLink."rank-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
			    $backlinkLink = $mainLink."backlink-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
			    $indexedLink = $mainLink."saturation-checker&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
			    $totaldirLink = $mainLink."directory-submission&default_args=".urlencode("sec=reports&website_id=".$websiteInfo['id']);
			    $activeDirLink = $mainLink."directory-submission&default_args=".urlencode("sec=reports&active=approved&&website_id=".$websiteInfo['id']);
			    ?>
				<tr>
					<td class="content" style="border-left: none;"><?php echo $websiteInfo['id']?></td>
					<td class="content">
						<?php echo $websiteInfo['name'];?><br>
						<a href="<?php echo $websiteInfo['url'];?>" target="_blank"><?php echo $websiteInfo['url'];?></a>
					</td>
					<td class="content"><a href="<?php echo $rankLink?>"><?php echo $websiteInfo['googlerank'];?></a></td>
					<td class="content"><a href="<?php echo $rankLink?>"><?php echo $websiteInfo['alexarank'];?></a></td>
					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['google']['backlinks'];?></a></td>
					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['alexa']['backlinks'];?></a></td>
					<td class="content"><a href="<?php echo $backlinkLink?>"><?php echo $websiteInfo['msn']['backlinks'];?></a></td>
					<td class="content"><a href="<?php echo $indexedLink?>"><?php echo $websiteInfo['google']['indexed'];?></a></td>
					<td class="content"><a href="<?php echo $indexedLink?>"><?php echo $websiteInfo['msn']['indexed'];?></a></td>
					<td class="contentmid"><a href="<?php echo $totaldirLink?>"><?php echo $websiteInfo['dirsub']['total'];?></a></td>
					<td class="contentmid"><a href="<?php echo $activeDirLink?>"><?php echo $websiteInfo['dirsub']['active'];?></a></td>
				</tr>
			<?php } ?>
		<?php }else{ ?>
			<tr><td colspan="<?php echo $colSpan?>" class="norecord"><?php echo $spText['common']['nowebsites']?></td></tr>
		<?php } ?>
	</table>
</div>

<?php
if(!empty($printVersion) || !empty($pdfVersion)) {
	echo $pdfVersion ? showPdfFooter($spText) : showPrintFooter($spText);
} else {
	?>
    </div>
    <?php echo getRoundTabBot(); ?>
    </div>
    </div>
    <?php
}
?>