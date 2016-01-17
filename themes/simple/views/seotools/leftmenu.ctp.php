<script>
	var menuList = new Array();
	var buttonList = new Array();
	var scriptList = new Array();	
</script>
<ul id="menu">
<?php 
foreach ($menuList as $i => $menuInfo) {
    if ($menuSelected == $menuInfo['url_section']) {
        $imgSrc = "hide";
        $style = "";
    } else {
        $imgSrc = "more";
        $style = 'none';
    }
    $button = "img".$menuInfo['id'];
    $subMenuId = "sub".$menuInfo['id'];
    ?>
	<script type="text/javascript">
		menuList[<?php echo $i?>] = '<?php echo $subMenuId?>';
		buttonList[<?php echo $i?>] = '<?php echo $button?>';
	</script>
	<li class="tab">
		<a href='javascript:void(0);' onclick="showMenu('<?php echo $button?>','<?php echo $subMenuId?>')"><img id="<?php echo $button?>" src="<?php echo SP_IMGPATH."/".$imgSrc?>.gif"> <?php echo $spTextTools[$menuInfo['url_section']]?></a>
	</li>
	<li id="<?php echo $subMenuId?>" class="subtab" style="display:<?php echo $style?>;padding-left:0px;">
	<?php
    switch ($menuInfo['url_section']) {
        case "keyword-position-checker":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'reports.php?sec=reportsum';</script>
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('reports.php?sec=reportsum', 'content')"><?php echo $spTextTools['Keyword Position Summary']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('reports.php', 'content')"><?php echo $spTextTools['Detailed Position Reports']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('graphical-reports.php', 'content')"><?php echo $spTextTools['Graphical Position Reports']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('reports.php?sec=kwchecker', 'content')"><?php echo $spTextTools['Quick Position Checker']?></a></li>				
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content')"><?php echo $spTextTools['Keywords Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=new')"><?php echo $spTextKeyword['New Keyword']?></a></li>
	         	<li><a href="javascript:void(0);" onclick="scriptDoLoad('keywords.php', 'content', 'sec=import')"><?php echo $spTextKeyword['Import Keywords']?></a></li>
			    <?php if (SP_USER_GEN_REPORT || isAdmin()) {
    ?>
					<?php if (SP_DEMO) {
    ?>
		         		<li><a href="javascript:void(0);" onclick="alertDemoMsg();"><?php echo $spTextTools['Generate Keyword Reports']?></a></li>
		         	<?php 
} else {
    ?>
						<li><a href="javascript:void(0);" onclick="scriptDoLoad('generate-reports.php', 'content')"><?php echo $spTextTools['Generate Keyword Reports']?></a></li>
		         	<?php 
}
    ?>
	         	<?php 
}?>
			</ul>
			<?php
            break;
            
        case "site-auditor":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'siteauditor.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php', 'content')"><?php echo $spTextTools['Auditor Projects']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php?sec=viewreports', 'content')"><?php echo $spTextTools['Auditor Reports']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php?sec=importlinks', 'content')"><?php echo $spTextTools['Import Project Links']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('sitemap.php', 'content')"><?php echo $spTextTools['sitemap-generator']?></a></li>
			    <?php if (isAdmin()) {
    ?>
			    	<li><a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php?sec=croncommand', 'content')"><?php echo $spTextPanel['Cron Command']?></a></li>
					<li><a href="javascript:void(0);" onclick="scriptDoLoad('siteauditor.php?sec=showsettings', 'content')"><?php echo $spTextTools['Auditor Settings']?></a></li>
				<?php 
}?>
			</ul>
			<?php
            break;
            
        case "sitemap-generator":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'sitemap.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('sitemap.php', 'content')"><?php echo $spTextTools['Google Sitemap Generator']?></a></li>
			</ul>
			<?php
            break;
            
        case "rank-checker":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'rank.php?sec=google';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('rank.php?sec=quickrank', 'content')"><?php echo $spTextTools['Quick Rank Checker']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('rank.php?sec=reports', 'content')"><?php echo $spTextTools['Rank Reports']?></a></li>
				<?php if (SP_USER_GEN_REPORT || isAdmin()) {
    ?>
					<?php if (SP_DEMO) {
    ?>
		         		<li><a href="javascript:void(0);" onclick="alertDemoMsg();"><?php echo $spTextTools['Generate Rank Reports']?></a></li>
		         	<?php 
} else {
    ?>
						<li><a href="javascript:void(0);" onclick="scriptDoLoad('rank.php?sec=generate', 'content')"><?php echo $spTextTools['Generate Rank Reports']?></a></li>
		         	<?php 
}
    ?>
	         	<?php 
}?>
			</ul>
			<?php
            break;
            
        case "backlink-checker":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'backlinks.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('backlinks.php', 'content')"><?php echo $spTextTools['Quick Backlinks Checker']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('backlinks.php?sec=reports', 'content')"><?php echo $spTextTools['Backlinks Reports']?></a></li>
				<?php if (SP_USER_GEN_REPORT || isAdmin()) {
    ?>
					<?php if (SP_DEMO) {
    ?>
		         		<li><a href="javascript:void(0);" onclick="alertDemoMsg();"><?php echo $spTextTools['Generate Backlinks Reports']?></a></li>
		         	<?php 
} else {
    ?>
						<li><a href="javascript:void(0);" onclick="scriptDoLoad('backlinks.php?sec=generate', 'content')"><?php echo $spTextTools['Generate Backlinks Reports']?></a></li>
		         	<?php 
}
    ?>
		  		<?php 
}?>
			</ul>
			<?php
            break;
            
        case "directory-submission":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'directories.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php', 'content')"><?php echo $spTextTools['Automatic Submission']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=featured', 'content')"><?php echo $spTextTools['Featured Submission']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=skipped', 'content')"><?php echo $spTextTools['Skipped Directories']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=reports', 'content')"><?php echo $spTextTools['Submission Reports']?></a></li>
				<?php if (SP_USER_GEN_REPORT || isAdmin()) {
    ?>
					<?php if (SP_DEMO) {
    ?>
		         		<li><a href="javascript:void(0);" onclick="alertDemoMsg();"><?php echo $spTextTools['Check Submission Status']?></a></li>
		         	<?php 
} else {
    ?>	         		
						<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=checksub', 'content')"><?php echo $spTextTools['Check Submission Status']?></a></li>
		         	<?php 
}
    ?>
		     	<?php 
}?>
			</ul>
			<?php
            break;
            
        case "saturation-checker":
            ?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'saturationchecker.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('saturationchecker.php', 'content')"><?php echo $spTextTools['Quick Saturation Checker']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('saturationchecker.php?sec=reports', 'content')"><?php echo $spTextTools['Saturation Reports']?></a></li>
				<?php if (SP_USER_GEN_REPORT || isAdmin()) {
    ?>				
					<?php if (SP_DEMO) {
    ?>
		         		<li><a href="javascript:void(0);" onclick="alertDemoMsg();"><?php echo $spTextTools['Generate Saturation Reports']?></a></li>
		         	<?php 
} else {
    ?>
		         		<li><a href="javascript:void(0);" onclick="scriptDoLoad('saturationchecker.php?sec=generate', 'content')"><?php echo $spTextTools['Generate Saturation Reports']?></a></li>
		         	<?php 
}
    ?>
		      	<?php 
}?>
			</ul>
			<?php
            break;
    }
    ?>
	</li>
	<?php

}
?>
</ul>