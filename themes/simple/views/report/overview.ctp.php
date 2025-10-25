<div id="content">
    <form id='ov_search_form' method="post">
        <table class="search">	
        	<tr>
        	    <th><?php echo $spText['common']['Website']?>: </th>
        		<td>
        			<select name="website_id" id="website_id" onchange="$('#ov_search_form').submit()" class="custom-select">
        				<?php foreach($siteList as $websiteInfo){?>
        					<?php if($websiteInfo['id'] == $websiteId){?>
        						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
        					<?php }else{?>
        						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
        					<?php }?>
        				<?php }?>
        			</select>
        		</td>
        		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
        		<td>
        			<input type="text" value="<?php echo $fromTime?>" name="from_time" id="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
        			<input type="text" value="<?php echo $toTime?>" name="to_time" id="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
        			<script>
        			  $( function() {
        			    $( "#from_time, #to_time").datepicker({dateFormat: "yy-mm-dd"});
        			  } );
        		  	</script>
        		</td>
        		<td>
            		<a href="javascript:void(0);" onclick="$('#ov_search_form').submit()" class="btn btn-secondary">
            			<?php echo $spText['button']['Search']?>
            		</a>
        		</td>
        	</tr>
        </table>
    </form>
	<?php
	$baseUrl = "overview.php?website_id=$websiteId&from_time=$fromTime&to_time=$toTime";
	?>
	<div class="mt-4">	
    	<?php echo showSectionHead($spTextHome["Page Overview Report"]);?>
    	<div id="page_overview_tab">
        	<script type="text/javascript">
               	scriptDoLoad('<?php echo $baseUrl?>', 'page_overview_tab', 'sec=page-overview');
        	</script>
    	</div>
    	<br>
    	
    	<?php echo showSectionHead($spTextHome["Keyword Overview Report"]);?>
    	<div id="keyword_overview_tab">
        	<script type="text/javascript">
               	scriptDoLoad('<?php echo $baseUrl?>', 'keyword_overview_tab', 'sec=keyword-overview');
        	</script>
    	</div>
	</div>
</div>