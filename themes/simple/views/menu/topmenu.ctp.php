<?php if ($userType == "guest") {?>
	<a href="<?php echo !empty($custSiteInfo['twitter_page_url']) ? $custSiteInfo['twitter_page_url'] : "https://twitter.com/seopanel"?>" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false" data-dnt="true">Follow @seopanel</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	<!-- facebook like button -->
	&nbsp;
	<?php $fbPage = !empty($custSiteInfo['fb_page_url']) ? $custSiteInfo['fb_page_url'] : "https://www.facebook.com/seopanel/"?>
	<iframe src="//www.facebook.com/plugins/like.php?href=<?php echo $fbPage?>&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=260885620597614" 
		scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe>
<?php }?>
<?php
$menuInfo = getCustomizerMenu("top");
if (!empty($menuInfo['item_list'])) {
	$linkStyle = !empty($menuInfo['bg_color']) ? "background-color: " . $menuInfo['bg_color'] : ""; 
	$linkStyle .= !empty($menuInfo['font_color']) ? ";color: " . $menuInfo['font_color'] : "";
	
	// loop through menu items
	foreach ($menuInfo['item_list'] as $menuItem) {
		$linkTarget = ($menuItem['window_target'] == 'new_tab') ? "_blank" : "";
		?>
		<a href="<?php echo $menuItem['url']?>" target="<?php echo $linkTarget?>" style="<?php echo $linkStyle?>">
			<?php echo $menuItem['label']?>
		</a><span class="pipe"> | </span>
		<?php
	}
	
} else {
	?> 
	<a href="<?php echo !empty($custSiteInfo['contact_url']) ? $custSiteInfo['contact_url'] : SP_CONTACT_LINK?>" target="_blank" rel="nofollow">
		<?php echo $spText['common']['contact']?>
	</a><span class="pipe"> | </span>
	<a href="<?php echo !empty($custSiteInfo['help_url']) ? $custSiteInfo['help_url'] : SP_HELP_LINK?>" target="_blank" rel="nofollow">
		<?php echo $spText['common']['help']?>
	</a><span class="pipe"> | </span>
	<a href="<?php echo !empty($custSiteInfo['support_url']) ? $custSiteInfo['support_url'] : SP_SUPPORT_LINK?>" target="_blank" rel="nofollow">
		<?php echo $spText['common']['Support']?>
	</a><span class="pipe"> | </span> 
	<?php
}
?>
<select class="form-control form-control-sm" name="lang_code" id="lang_code" onchange="doLoadUrl('lang_code', '<?php echo $redirectUrl?>')">
	<?php
	foreach ($langList as $langInfo) {
		$selected = ($langInfo['lang_code'] == $_SESSION['lang_code']) ? "selected='selected'" : "";
		?>			
		<option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_show']?></option>
		<?php
	}
	?>
</select>