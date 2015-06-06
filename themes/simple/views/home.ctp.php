<div class="Center" style='width:100%;'>
    <div class="col" style="min-height: 300px;">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
            <fieldset>
            <ul class="infobox">
            	<li>
            		<?php echo str_replace('<?php echo SP_PLUGINSITE?>', SP_PLUGINSITE, $spTextHome['home_cont1'])?>
            	</li>
            </ul>
            </fieldset>

            <?php
            echo str_replace( array('<?php echo SP_PLUGINSITE?>', '<?php echo SP_INSTALLED?>'), array(SP_PLUGINSITE, ""), $spTextHome['home_cont2']);
            ?>

            <?php
            $search = array('<?php echo SP_DOWNLOAD_LINK?>','<?php echo SP_DEMO_LINK?>','<?php echo SP_CONTACT_LINK?>','<?php echo SP_HELP_LINK?>','<?php echo SP_FORUM_LINK?>','<?php echo SP_SUPPORT_LINK?>','<?php echo SP_DONATE_LINK?>');
            $replace = array(SP_DOWNLOAD_LINK,SP_DEMO_LINK,SP_CONTACT_LINK,SP_HELP_LINK,SP_FORUM_LINK,SP_SUPPORT_LINK,SP_DONATE_LINK);
            echo str_replace( $search, $replace, $spTextHome['home_cont3']);
            ?>
        </div>
    	<?php echo getRoundTabBot(); ?>
    </div>
</div>