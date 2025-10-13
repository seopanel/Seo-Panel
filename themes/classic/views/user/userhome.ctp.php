<div class="col-sm-12 mt-4">	
    <?php
    $showOverview = true;
    if ($showOverview) {
        $dbTabClass = "";
        $ovTabView = "";
        $mainTabClass = "";
        if (!empty($custSubMenu)) {
            $ovTabView = "active";
        } else {
            switch ($post['dashboard']) {
                case "reports":
                    $dbTabClass = "active";
                    break;
                    
                default:
                    $mainTabClass = "active";
                    break;
            }
        }
        ?>
		<ul class="nav nav-tabs">
            <li class="nav-item">
            	<a class="nav-link <?php echo $mainTabClass?>" href="<?php echo SP_WEBPATH?>/">
            		<i class="fas fa-tachometer-alt"></i> <?php echo $spText['common']['Dashboard']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $dbTabClass?>" href="<?php echo SP_WEBPATH?>/?dashboard=reports">
            		<i class="fas fa-chart-line"></i> <?php echo $spText['common']['Reports']?>
            	</a>
            </li>
            <li class="nav-item">
            	<a class="nav-link <?php echo $ovTabView?>" href="<?php echo SP_WEBPATH?>/overview.php">
            		<i class="fas fa-eye"></i> <?php echo $spText['label']['Overview']?>
            	</a>
            </li>
        </ul>
    	<?php
    }?>
    
    <?php if ($showOverview && !empty($custSubMenu)) {?>
    	<?php include(SP_VIEWPATH."/report/overview.ctp.php");?>
    <?php } else {?> 
        <div id="content">
        	<script type="text/javascript">
        		<?php if ($dbTabClass == "active") {?>
               		scriptDoLoad('archive.php', 'content', '<?php echo getRequestParamStr(); ?>');
           		<?php } else {?>
               		scriptDoLoad('dashboard.php', 'content', '<?php echo getRequestParamStr("GET"); ?>');
           		<?php }?>
        	</script>
        </div>
    <?php }?>
</div>
<?php if (!empty($_GET['source']) && $_GET['source'] == 'install') {?>
    <script type="text/javascript">
        $(document).ready(function() {
        	scriptDoLoad("<?php echo SP_WEBPATH?>/?sec=sync_all_se", "tmp");
        });
    </script>
<?php }?>