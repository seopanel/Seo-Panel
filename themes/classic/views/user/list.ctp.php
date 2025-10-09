<?php $submitLink = "scriptDoLoadPost('users.php', 'listform', 'content')";?>
<form name="listform" id="listform" onsubmit="<?php echo $submitLink?>;return false;">
<?php echo showSectionHead($spTextPanel['User Manager']); ?>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td>
			<input type="text" name="user_name" value="<?php echo htmlentities($info['user_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
		</td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($statusList as $key => $val){?>
					<?php if(isset($info['stscheck']) && $info['stscheck'] === $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['login']['User Type']?>: </th>
		<td>
			<select name="user_type_id" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($userTypeList as $key => $val){?>
					<?php if(isset($info["user_type_id"]) && ($info["user_type_id"] == $key)){?>
						<option value="<?php echo $key?>" selected><?php echo $val['user_type']?></option>
					<?php }else{?>
						<option value="<?php echo $key?>"><?php echo $val['user_type']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitLink; ?>" class="btn btn-secondary">
				<?php echo $spText['button']['Search']?>
			</a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['login']['Username']?></td>
		<td><?php echo $spText['login']['User Type']?></td>
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spText['login']['Email']?></td>
		<td><?php echo $spTextUser['Expiry Date']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 9; 
	if(count($userList) > 0){
		foreach($userList as $i => $userInfo){
            $usernameLink = scriptAJAXLinkHref('users.php', 'content', "sec=edit&userId={$userInfo['id']}", "{$userInfo['username']}");
			?>
			<tr>				
				<td><input type="checkbox" name="ids[]" value="<?php echo $userInfo['id']?>"></td>
				<td><?php echo $usernameLink?></td>
				<td><?php echo $userTypeList[$userInfo['utype_id']]['user_type']?></td>
				<td><?php echo $userInfo['first_name']." ".$userInfo['last_name']?></td>
				<td><?php echo $userInfo['email']?></td>
				<td><?php echo formatDate($userInfo['expiry_date']); ?></td>
				<td><?php echo $userInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"];	?></td>
				<td>
					<?php
						if($userInfo['status']){
							$statVal = "Inactivate";
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statVal = "Activate";
							$statLabel = $spText['common']["Activate"];
						} 
					?>
					<select name="action" id="action<?php echo $userInfo['id']?>" class="custom-select" 
						onchange="doAction('users.php', 'content', 'userId=<?php echo $userInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $userInfo['id']?>')" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="<?php echo $statVal?>"><?php echo $statLabel?></option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
						<?php if ($isSubscriptionActive && ($userTypeList[$userInfo['utype_id']]['access_type'] == 'read')) {?>
							<option value="website-access-manager"><?php echo $spTextPanel['Website Access Manager']?></option>
						<?php }?>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
<?php
if (SP_DEMO) {
    $actFun = $inactFun = $delFun = "alertDemoMsg()";
} else {
    $actFun = "confirmSubmit('users.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('users.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('users.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}   
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('users.php', 'content', 'sec=new')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['New User']?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-success">
         		<?php echo $spText['common']["Activate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>