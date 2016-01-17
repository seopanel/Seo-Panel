<form name="listform" id="listform">
<input type="hidden" name="sec" value="checkAllstatus">
<?php 
echo showSectionHead($spText['button']["Check Status"]);
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'subcontent', '')";
?>
<table width="70%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['label']["Proxy"]?>: </th>
		<td width="20%">
			<select name="status">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php	
                $inactCheck = $actCheck = "";
                if ($statVal == 'active') {
                    $actCheck = "selected";
                } elseif ($statVal == 'inactive') {
                    $inactCheck = "selected";
                }
                ?>
				<option value="active" <?php echo $actCheck?> ><?php echo $spText['common']["Active"]?> <?php echo $spText['label']["Proxy"]?></option>
				<option value="inactive" <?php echo $inactCheck?> ><?php echo $spText['common']["Inactive"]?> <?php echo $spText['label']["Proxy"]?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']["Check Status"]?> &gt;&gt;</a>
		</td>
	</tr>
</table>
</form>
<div id="subcontent"></div>
