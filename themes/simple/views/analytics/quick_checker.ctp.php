<?php 
echo showSectionHead($spTextTools['Quick Checker']);
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('analytics.php', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<form id='search_form' onsubmit="<?php echo $actFun; ?>;return false;">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id">
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th width="100px;"><?php echo $spText['common']['Period']?>:</th>
    	<td>
    		<input type="text" value="<?php echo $fromTime?>" name="from_time"/> 
    		<input type="text" value="<?php echo $toTime?>" name="to_time"/>
			<script type="text/javascript">
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    	</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="actionbut"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
</div>