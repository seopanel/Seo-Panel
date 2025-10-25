<div class="col-sm-12">

	<ul class="nav nav-tabs nav-tabs-sm" role="tablist">
    	<?php
    	$i = 0;
    	foreach ($seList as $seInfo) {
    	    $linkClass = "";
    	    if ($i == 0) {
    	        $linkClass = "active";
    	        $defaultPageOVUrl = $pageOVUrl . "&se_id=" . $seInfo['id'];
    	    }
            ?>
            <li role="presentation" class="nav-item">
            	<a class="nav-link <?php echo $linkClass?>" href="#" data-href="<?php echo $pageOVUrl?>&se_id=<?php echo $seInfo['id']?>" 
            	id="link-<?php echo $i;?>" aria-controls="section-<?php echo $i;?>" role="tab" data-target="#section-<?php echo $i;?>" data-toggle="tab"><?php echo $seInfo['domain']?></a>
            </li>
            <?php
            $i++;
    	}
    	?>
	</ul>

    <!-- Tab panes -->
  	<div class="tab-content">
    	<?php
    	$i = 0;
    	foreach ($seList as $seInfo) {
    	    $linkClass = $i ? "" : "active"; 
            ?>
        	<div role="tabpanel" class="tab-pane <?php echo $linkClass?>" id="section-<?php echo $i;?>"></div>
            <?php
            $i++;
    	}
    	?>
	</div>
  
</div>

<script type="text/javascript">
$(document).ready(function() {
	scriptDoLoad('<?php echo $defaultPageOVUrl?>', 'section-0', '');
});

$('[data-toggle="tab"]').on('click', function(){
    var $this = $(this),
    source = $this.attr('data-href'),
    pane = $this.attr('data-target');
    area = $this.attr('aria-controls');
  
    if($(pane).is(':empty')) {

      scriptDoLoad(source, area, '');

      $(this).tab('show');
      return false;
    }
});
</script>