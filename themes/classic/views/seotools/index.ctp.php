<div class="Center" style='width:100%;'>
    <div class="col" style="">
        <div class="Block">
            <table width="100%" border="0" cellspacing="0px" cellpadding="0">
            	<tr>
            		<td class="leftmenu" valign="top">
            			<div class="selectmenu">
                            <?php echo getRoundTabTop(); ?>
                            <div id="round_content">
            				    <?php include_once(SP_VIEWPATH."/seotools/leftmenu.ctp.php");?>
            				</div>
            				<?php echo getRoundTabBot(); ?>
            			</div>
            		</td>
            		<td width="10px">&nbsp;</td>
            		<td valign="top">
                        <?php echo getRoundTabTop(); ?>
                        <div id="content">
                			<?php if(!empty($defaultScript)) {?>
                				<script type="text/javascript">
                					scriptDoLoad('<?php echo $defaultScript?>', 'content', '<?php echo $defaultArgs?>');
                				</script>
                			<?php }?>
        				</div>
        				<?php echo getRoundTabBot(); ?>
            		</td>
            	</tr>
            </table>
        </div>
    </div>
</div>
