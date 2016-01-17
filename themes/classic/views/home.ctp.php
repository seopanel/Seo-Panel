<div class="Center" style='width:100%;'>
    <div class="col" style="min-height: 300px;">    
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <fieldset>
            <ul class="infobox">
            	<li>
            		<?php echo str_replace('<?'.'=SP_PLUGINSITE?>', SP_PLUGINSITE, $spTextHome['home_cont1'])?>
            	</li>
            </ul>
            </fieldset>
            
            <?php 
            echo str_replace(array('<?'.'=SP_PLUGINSITE?>', '<?'.'=SP_INSTALLED?>'), array(SP_PLUGINSITE, ""), $spTextHome['home_cont2']);
            ?>
            
            <?php 
            $search = array(
                '<?'.'=SP_DOWNLOAD_LINK?>', '<?'.'=SP_DEMO_LINK?>', '<?'.'=SP_CONTACT_LINK?>',
                '<?'.'=SP_HELP_LINK?>', '<?'.'=SP_FORUM_LINK?>', '<?'.'=SP_SUPPORT_LINK?>', '<?'.'=SP_DONATE_LINK?>'
            );
            $replace = array(SP_DOWNLOAD_LINK,SP_DEMO_LINK,SP_CONTACT_LINK,SP_HELP_LINK,SP_FORUM_LINK,SP_SUPPORT_LINK,SP_DONATE_LINK);
            echo str_replace($search, $replace, $spTextHome['home_cont3']);
            ?>
        </div>
    	<?php echo getRoundTabBot(); ?>
    </div>
</div>