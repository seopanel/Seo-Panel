<?php 
$headText = ($editAction == 'update') ? $spTextPanel['New Website'] : $spTextWeb['Edit Website'];
echo showSectionHead($headText);
if(!empty($msg)){
	?>
	<p class="dirmsg">
		<font class="success"><?php echo $msg?></font>
	</p>
	<?php 
}

if(!empty($validationMsg)){
	?>
	<p class="dirmsg">
		<font class="error"><?php echo $validationMsg?></font>
	</p>
	<?php 
}

$post['url'] = empty($post['url']) ? "https://" : $post['url'];
?>
<form id="edit_website">
<input type="hidden" name="sec" value="<?php echo $editAction?>"/>
<?php if ($editAction == 'update') {?>	
	<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<?php }?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New Website']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php if(!empty($isAdmin)){ ?>	
		<tr class="blue_row">
			<td class="td_left_col"><?php echo $spText['common']['User']?>:</td>
			<td class="td_right_col">
				<select name="userid" class="form-control">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $userSelected){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>						
					<?php }?>
				</select>
			</td>
		</tr>
	<?php }?>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['common']['Name']?>:</td>
		<td class="td_right_col">
			<input type="text" name="name" value="<?php echo $post['name']?>" class="form-control"><?php echo $errMsg['name']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['common']['Url']?>:</td>
		<td class="td_right_col">
			<div class="row">
				<div class="col-sm-9">
					<input type="text" id='weburl' name="url" value="<?php echo $post['url']?>" class="form-control">
				</div>
				<div class="col-sm-3">
    				<a  class="btn btn-primary" href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta', 'crawlstats')">
    					<?php echo $spText['common']['Crawl Meta Data']?>
    				</a>
				</div>
			</div>							
			<?php echo $errMsg['url']?>
			<div id="crawlstats" style="padding-right:40px;" class="mt-2 float-right mt-2"></div>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Title']?>:</td>
		<td class="td_right_col"><input type="text" id="webtitle" name="title" value="<?php echo $post['title']?>" class="form-control"></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><?php echo $spText['label']['Description']?>:</td>
		<td class="td_right_col"><textarea name="description" id="webdescription" class="form-control"><?php echo $post['description']?></textarea><?php echo $errMsg['description']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spText['label']['Keywords']?>:</td>
		<td class="td_right_col"><textarea name="keywords" id="webkeywords" class="form-control"><?php echo $post['keywords']?></textarea><?php echo $errMsg['keywords']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><?php echo $spTextWeb['Google Analytics Property']?>:</td>
		<td class="td_right_col">
			<div class="row">
            	<div class="col-sm-8">    					
					<?php echo createSelectBoxFromList($propertyList, "analytics_view_id", $post['analytics_view_id'], $spText['common']['Select']);?>
    	        </div>
    	        <div class="col-sm-4">
    	        	<a href="javascript:void(0);" class="btn btn-primary" id="connection_refresh">
    	        		<?php echo $spTextWeb['Sync Google Analytics Properties']?>  
	        		</a>
    	        </div>
	        </div>
	        <div id="connection_refresh_loading" class="float-right mt-2" style="display: none; width: 50%;">
				<div id="loading_longthin"></div>
	        </div>
        	<div id="connection_refresh_content" style="margin: 16px 6px;display: none;" class="fw-bold float-right"></div>
		</td>
	</tr>
</table>
<table class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('websites.php', 'edit_website', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>

<script type="text/javascript">
$(function() {
    $("#connection_refresh").click(function() {
    	$("#connection_refresh_content").show();
        $.ajax({
            url: '<?php echo SP_WEBPATH?>/websites.php?sec=fetchgoogleanalytics',
            type: "GET",
  			dataType: "json",
            success: function(response) {
            	$("#connection_refresh_content").show();
                if(response.status) {
                	var connectionList = response.data;
                	$('#analytics_view_id').empty();
					$('#analytics_view_id').append($('<option>', {
                        value: '',
                        text: '-- <?php echo $spText['common']['Select']?> --',
                    }));
                	$.each(connectionList, function(propertyId, propertyName) {
                      $('#analytics_view_id').append($('<option>', {
                        value: propertyId,
                        text: propertyName,
                      }));
                    });
                	
                	$("#connection_refresh_content").html('<span class="text-success form-success"><i class="ri-checkbox-circle-line"></i>Google Analytics Properties Synced.</span>');
                } else {
                	$("#connection_refresh_content").html('<span class="text-danger form-error"><i class="ri-error-warning-line"></i>'+ response.msg +'</span>');
                }
            },
            beforeSend: function() {
				$('#connection_refresh_loading').show();
				$("#connection_refresh_content").html('');
			},
            error: function(jqXHR, textStatus, errorThrown) {
            	$("#connection_refresh_content").show();
                var errMsg = "API Error: " + errorThrown; 
				$("#connection_refresh_content").html('<span class="text-danger form-error"><i class="ri-error-warning-line"></i>' + errMsg + '</span>');
            },
            complete: function() {
				$('#connection_refresh_loading').hide();
			}
        });
    });
});
</script>