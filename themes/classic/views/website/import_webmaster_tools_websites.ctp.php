<?php 
echo showSectionHead($spTextPanel['Import Websites'] . "({$spTextTools['webmaster-tools']})");

if (!empty($msg)) {
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
?>

<form id="projectform">
<input type="hidden" name="sec" value="importWebmasterTools"/>
<table id="cust_tab">
	<tr class="form_head">
		<th width="40%"><?php echo $spTextPanel['Import Websites']; ?></th>
		<th>&nbsp;</th>
	</tr>
	<?php if(!empty($isAdmin)){ ?>	
		<tr class="form_data">
			<td><?php echo $spText['common']['User']?>:</td>
			<td>
				<select name="userid" class="custom-select">
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
	<?php } else { ?>
		<tr class="form_data">
			<td><?php echo $spText['common']['User']?>:</td>
			<td><?php echo $userName?></td>
		</tr>
	<?php }?>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('websites.php', 'projectform', 'import_result_div')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
<div id="import_result_div"></div>