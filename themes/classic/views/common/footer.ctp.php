<?php if (!empty($custSiteInfo['footer_copyright'])) {?>
	<div><?php echo str_replace('[year]', date('Y'), convertMarkdownToHtml($custSiteInfo['footer_copyright']))?></div>
<?php } else {
    $urlInfo = parse_url(SP_WEBPATH);
    $relNofollow = in_array($_SERVER['REQUEST_URI'], [$urlInfo['path'], $urlInfo['path']."/"]) ? "" : 'rel="nofollow"';
    ?>
	<div><?php echo str_replace('[year]', date('Y'), $spText['common']['copyright'])?></div>
    <span class="footer-sep">|</span>
    <div id="powered">
    	<?php echo $spText['common']['Powered by']; ?> <a href="<?php echo SP_MAIN_SITE?>" target="_blank" title="Seo Control Panel" <?php echo $relNofollow?>>Seo Panel</a>
    	<?php echo $translatorInfo?>
    </div>
<?php }?>

