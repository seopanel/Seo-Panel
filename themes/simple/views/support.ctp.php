<div class="col" id="support_screen">
	<?php
	if (!empty($blogContent['blog_content'])) {
    	echo $blogContent['blog_content'];
	} else {
	    
	    // add no follow option to SP links
	    $spTextSupport['support_cont1'] = str_replace('<a ', '<a rel="nofollow" ', $spTextSupport['support_cont1']);
	    $spTextSupport['support_cont2'] = str_replace('<a ', '<a rel="nofollow" ', $spTextSupport['support_cont2']);
	    $spTextSupport['support_cont3'] = str_replace('<a ', '<a rel="nofollow" ', $spTextSupport['support_cont3']);
	    	    
		$search = array(
        	'<?'.'=SP_PLUGINSITE?>', '<?'.'=SP_DOWNLOAD_LINK?>', '<?'.'=SP_DEMO_LINK?>',
            '<?'.'=SP_CONTACT_LINK?>', '<?'.'=SP_HELP_LINK?>', '<?'.'=SP_FORUM_LINK?>',
            '<?'.'=SP_SUPPORT_LINK?>', '<?'.'=SP_DONATE_LINK?>', '$160', 'size="14"'
		);
		$replace = array(
            SP_PLUGINSITE,SP_DOWNLOAD_LINK,SP_DEMO_LINK,SP_CONTACT_LINK,SP_HELP_LINK,
		    SP_FORUM_LINK,SP_SUPPORT_LINK,SP_DONATE_LINK, '$150', 'size="6"'
		);
        echo str_replace( $search, $replace, $spTextSupport['support_cont1']);
        echo str_replace( $search, $replace, $spTextSupport['support_cont2']);
        echo str_replace( $search, $replace, $spTextSupport['support_cont3']);
	}
	?>
</div>
