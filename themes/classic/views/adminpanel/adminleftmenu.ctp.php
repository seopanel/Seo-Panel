<script>
	var menuList = new Array();
	var buttonList = new Array();
	var scriptList = new Array();	
</script>
<ul id="menu">
<?php 
foreach($menuList as $i => $menuInfo){
	if($menuSelected == $menuInfo['url_section']){
		$menuClass = "fa-bars";
		$style = "";
	}else{
		$menuClass = "fa-bars";
		$style = 'none';
	}
	$button = "img".$menuInfo['id'];
	$subMenuId = "sub".$menuInfo['id'];
	?>
	<script type="text/javascript">
		menuList[<?php echo $i?>] = '<?php echo $subMenuId?>';
		buttonList[<?php echo $i?>] = '<?php echo $button?>';
	</script>
	<li class="tab" onclick="showMenu('<?php echo $button?>','<?php echo $subMenuId?>')">
		<i id="<?php echo $button?>" class="fas <?php echo $menuClass?>"></i>
		<a href='javascript:void(0);'>
			<?php echo $menuInfo['name']?>
		</a>
	</li>
	<li id="<?php echo $subMenuId?>" class="subtab" style="display:<?php echo $style?>;padding-left:0px;">
	<?php
	switch($menuInfo['url_section']){
		
		case "websites":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'websites.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content')" class="menu_active"><?php echo $spTextPanel['Website Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content', 'sec=new')"><?php echo $spTextPanel['New Website']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content', 'sec=import')"><?php echo $spTextPanel['Import Websites']?></a></li>
				<li>
					<a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content', 'sec=importWebmasterTools')">
						<?php echo $spTextPanel['Import Websites']?>(<?php echo $spTextTools['webmaster-tools']?>)
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content', 'sec=listSitemap')">
						<?php echo $spTextPanel['Sitemaps']?>(<?php echo $spTextTools['webmaster-tools']?>)
					</a>
				</li>
			</ul>
			<?php
			break;
			
		case "users":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'users.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('users.php', 'content')"><?php echo $spTextPanel['User Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('users.php', 'content', 'sec=new')"><?php echo $spTextPanel['New User']?></a></li>
				<?php if (isPluginActivated("Subscription")) {?>
					<li>
						<a href="javascript:void(0);" onclick="scriptDoLoad('users.php', 'content', 'sec=website-access-manager')">
							<?php echo $spTextPanel['Website Access Manager']?>
						</a>
					</li>
				<?php }?>
			</ul>
			<?php
			break;
			
		case "se-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'searchengine.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('searchengine.php', 'content')"><?php echo $spTextPanel['Search Engine Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('searchengine.php?sec=sync-se', 'content')"><?php echo $spTextPanel['Sync Search Engines']?></a></li>
			</ul>
			<?php
			break;
			
		case "report-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'archive.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('archive.php', 'content')"><?php echo $spTextPanel['Archived Reports']?></a></li>				
				<?php if (!SP_CUSTOM_DEV) {?>
    				<?php if (isAdmin() || SP_ALLOW_USER_SCHEDULE_REPORT) {?>
    					<li><a href="javascript:void(0);" onclick="scriptDoLoad('reports.php?sec=schedule', 'content')"><?php echo $spTextPanel['Schedule Reports']?></a></li>
    				<?php }?>
				<?php }?>
				<?php if (isAdmin()) {?>
    				<li><a href="javascript:void(0);" onclick="scriptDoLoad('cron.php', 'content')"><?php echo $spTextPanel['Report Generation Manager']?></a></li>
    				<li><a href="javascript:void(0);" onclick="scriptDoLoad('reports.php?sec=report_gen_logs', 'content')"><?php echo $spTextPanel['Report Generation Logs']?></a></li>
    				<li><a href="javascript:void(0);" onclick="scriptDoLoad('cron.php?sec=croncommand', 'content')"><?php echo $spTextPanel['Cron Command']?></a></li>					
				<?php }?>
			</ul>
			<?php
			break;
			
		case "seo-tools-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'seo-tools-manager.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('seo-tools-manager.php', 'content')"><?php echo $spTextPanel['Seo Tools Manager']?></a></li>
			</ul>
			<?php
			break;
			
		case "themes-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'themes-manager.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('themes-manager.php', 'content')"><?php echo $spTextPanel['Themes Manager']?></a></li>
			</ul>
			<?php
			break;
			
		case "seo-plugin-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'seo-plugins-manager.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('seo-plugins-manager.php', 'content')"><?php echo $spTextPanel['Seo Plugins Manager']?></a></li>
			</ul>
			<?php
			break;
			
		case "directory-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'directories.php?sec=directorymgr';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=directorymgr', 'content')"><?php echo $spTextPanel['Directory Manager']?></a></li>				
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('directories.php?sec=showcheckdir', 'content')"><?php echo $spTextPanel['Check Directory']?></a></li>
			</ul>
			<?php
			break;
			
		case "proxy-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'proxy.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php', 'content')"><?php echo $spTextPanel['Proxy Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php', 'content', 'sec=new')"><?php echo $spTextPanel['New Proxy']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php', 'content', 'sec=import')"><?php echo $spTextPanel['Import Proxy']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php', 'content', 'sec=checkAllstatus')"><?php echo $spText['button']["Check Status"]?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php', 'content', 'sec=perfomance')"><?php echo $spTextPanel['Proxy Perfomance']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'content', 'sec=proxysettings')"><?php echo $spTextPanel['Proxy Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('proxy.php?sec=croncommand', 'content')"><?php echo $spTextPanel['Cron Command']?></a></li>
			</ul>
			<?php
			break;
			
		case "log-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'log.php?sec=crawl';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('log.php?sec=crawl', 'content')"><?php echo $spTextPanel['Crawl Log Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('log.php?sec=mail', 'content')"><?php echo $spTextPanel['Mail Log Manager']?></a></li>
			</ul>
			<?php
			break;
			
		case "api-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'apimanager.php';</script>			
			<ul id='subui'>
				<?php if (isAdmin()) {?>
					<li><a href="javascript:void(0);" onclick="scriptDoLoad('apimanager.php?sec=showconnect', 'content')"><?php echo $spTextPanel['API Connection']?></a></li>
    				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=apisettings', 'content')"><?php echo $spTextPanel['API Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=seopanel_api', 'content')"><?php echo $spTextPanel['Seo Panel API Settings']?></a></li>
				<?php }?>
			</ul>
			<?php
			break;
			
		case "settings":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'settings.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php', 'content')"><?php echo $spTextPanel['System Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=seopanel_api', 'content')"><?php echo $spTextPanel['Seo Panel API Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=reportsettings', 'content')"><?php echo $spTextPanel['Global Reports Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=moz', 'content')"><?php echo $spTextPanel['MOZ Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=google', 'content')"><?php echo $spTextPanel['Google Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=dataforseo', 'content')"><?php echo $spTextPanel['DataForSEO Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?category=mail', 'content')"><?php echo $spTextPanel['Mail Settings']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=test_email', 'content')"><?php echo $spTextPanel['Test Email Settings']?></a></li>
			</ul>
			<?php
			break;
			
		case "my-profile":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'users.php?sec=my-profile';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('users.php?sec=my-profile', 'content')"><?php echo $spTextPanel['My Profile']?></a></li>
				<?php if (!SP_CUSTOM_DEV) {?>
					<li><a href="javascript:void(0);" onclick="scriptDoLoad('connections.php', 'content')"><?php echo $spTextPanel['Connections']?></a></li>
					<li><a href="javascript:void(0);" onclick="scriptDoLoad('alerts.php', 'content')"><?php echo $spTextPanel['Alerts']?></a></li>
				<?php }?>
			</ul>
			<?php
			break;
			
		case "about-us":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'settings.php?sec=aboutus';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=aboutus', 'content')"><?php echo $spTextPanel['About Us']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=aboutus&subsec=sponsors', 'content')"><?php echo $spText['label']['Sponsors']?></a></li>
				<?php if (isAdmin()) {?>
					<li><a href="javascript:void(0);" onclick="scriptDoLoad('settings.php?sec=version', 'content')"><?php echo $spText['label']['Version']?></a></li>
				<?php }?>
			</ul>
			<?php
			break;
			
		case "user-types-manager":
			?>
			<script type="text/javascript">scriptList[<?php echo $i?>] = 'user-types-manager.php';</script>			
			<ul id='subui'>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('user-types-manager.php', 'content')"><?php echo $spTextPanel['User Type Manager']?></a></li>
				<li><a href="javascript:void(0);" onclick="scriptDoLoad('user-types-manager.php', 'content', 'sec=new')"><?php echo $spTextPanel['New User Type']?></a></li>
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