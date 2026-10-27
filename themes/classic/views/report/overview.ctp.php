<div id="content">
	<!-- Website and Period selection both now live entirely in the shared
	     filter bar above #main_dashboard_nav (userhome.ctp.php) - this tab
	     used to have its own separate Website select + raw from_time/to_time
	     date pickers + Search button; all three are gone, replaced by the
	     shared bar's website select + day/week/month/year period preset
	     (periodMode="preset" now, like every other tab - was "dates").
	     $fromTime/$toTime are still computed by the controller from
	     that preset (OverviewController::showOverView(), via
	     DashboardController::__getPeriodDateRange()) and used exactly as
	     before by $baseUrl and the two report tabs below. -->
	<?php
	$baseUrl = "overview.php?website_id=$websiteId&from_time=$fromTime&to_time=$toTime";
	?>
	<div class="mt-4">
    	<?php echo showSectionHead($spTextHome["Keyword Overview Report"]);?>
    	<div id="keyword_overview_tab">
        	<script type="text/javascript">
               	scriptDoLoad('<?php echo $baseUrl?>', 'keyword_overview_tab', 'sec=keyword-overview');
        	</script>
    	</div>
    	<br>

    	<?php echo showSectionHead($spTextHome["Page Overview Report"]);?>
    	<div id="page_overview_tab">
        	<script type="text/javascript">
               	scriptDoLoad('<?php echo $baseUrl?>', 'page_overview_tab', 'sec=page-overview');
        	</script>
    	</div>
	</div>
</div>