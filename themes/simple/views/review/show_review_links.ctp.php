<?php
echo showSectionHead($spTextTools['Review Links']);
$searchFun = "scriptDoLoadPost('$pageScriptPath', 'searchForm', 'content')";
?>
<form name="searchForm" id="searchForm" onsubmit="return false;">
<table width="100%" class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td><input type="text" name="name" value="<?php echo htmlentities($searchInfo['name'], ENT_QUOTES)?>" onblur="<?php echo $searchFun?>"></td>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $searchInfo['website_id']){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['label']['Type']?>: </th>
		<td>
			<select name="type" id="type" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<?php if($serviceName == $searchInfo['type']){?>
						<option value="<?php echo $serviceName?>" selected><?php echo $serviceInfo['label']?></option>
					<?php }else{?>
						<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td colspan="2">
			<select name="status" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$statusList = ['active' => $spText['common']["Active"], 'inactive' => $spText['common']["Inactive"]];
				foreach ($statusList as $statusVal => $statuslabel) {
				    $selectedVal = ($statusVal == $searchInfo['status']) ? "selected" : "";
				    ?>
				    <option value="<?php echo $statusVal?>" <?php echo $selectedVal?> ><?php echo $statuslabel?></option>
				    <?php				    
				}
				?>
			</select>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>

<?php echo $pagingDiv?>

<table id="cust_tab">
	<tr>
		<th><?php echo $spText['common']['Id']?></th>
		<th><?php echo $spText['common']['Name']?></th>
		<th><?php echo $spText['label']['Type']?></th>
    	<th><?php echo $spText['common']['Link']?></th>
    	<th><?php echo $spText['common']['Website']?></th>
		<th><?php echo $spText['common']['Status']?></th>
		<th><?php echo $spText['common']['Action']?></th>
	</tr>
	<?php
	if(count($list) > 0) {
		foreach($list as $i => $listInfo){
		    $projectLink = scriptAJAXLinkHref($pageScriptPath, 'content', "sec=edit&id={$listInfo['id']}", "{$listInfo['name']}");
			?>
			<tr>
				<td width="40px"><?php echo $listInfo['id']?></td>
				<td><?php echo $projectLink?></td>
				<td>
					<i class="fab fa-<?php echo $serviceList[$listInfo['type']]['icon']?>"></i>
					<?php echo $serviceList[$listInfo['type']]['label']?>
				</td>
				<td><?php echo $listInfo['url']?></td>				
				<td><?php echo $listInfo['website_name']?></td>
				<td><?php echo $listInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"];?></td>
				<td width="100px">
					<?php
					if ($listInfo['status']) {
						$statAction = "Inactivate";
						$statLabel = $spText['common']["Inactivate"];
					} else {
						$statAction = "Activate";
						$statLabel = $spText['common']["Activate"];
					} 
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" 
						onchange="doAction('<?php echo $pageScriptPath?>', 'content', 'id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="<?php echo $statAction?>"><?php echo $statLabel?></option>
                        <option value="edit"><?php echo $spText['common']['Edit']?></option>
					    <option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="7"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
		<?php
	} 
	?>
</table>
<br>
<table width="100%" class="actionSec">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('<?php echo $pageScriptPath?>', 'content', 'sec=newReviewLink')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spTextRM['New Review Link']?>
         	</a>
    	</td>
	</tr>
</table>