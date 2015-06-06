<div class="Center" style='width:100%;'>
    <div class="col">
        <div class="Block">
            <table width="100%" border="0" cellspacing="0px" cellpadding="0">
            	<tr>
            		<td valign="top" class="leftmenu">
            			<div class="selectmenu">
            			    <?php echo getRoundTabTop(); ?>
                    		<div id="round_content">
            				    <?php include_once(SP_VIEWPATH."/seoplugins/seopluginleftmenu.ctp.php");?>
            				</div>
            				<?php echo getRoundTabBot(); ?>
            			</div>
            		</td>
            		<td width="10px">&nbsp;</td>
            		<td valign="top">
                        <?php echo getRoundTabTop(); ?>
                        <div id="content">
                			<script type="text/javascript">
                				<?php
                				// to pass all get arguments to the selected plugin's action function
                				$argString = "";
                				foreach ($_GET as $name => $value) {
                				    if (!in_array($name, array('sec', 'menu_selected'))) {
                				        $argString .= "&$name=$value";
                				    }
                				}
                				?>
                				scriptDoLoad('seo-plugins.php?pid=<?php echo $menuSelected?><?php echo $argString?>', 'content', '');
                			</script>
        				</div>
        				<?php echo getRoundTabBot(); ?>
            		</td>
            	</tr>
            </table>
        </div>
    </div>
</div>
