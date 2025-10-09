<?php echo showSectionHead($spTextPanel['Directory Manager']); ?>
<form id='search_form'>
<table class="search">
	<?php $submitLink = "scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=directorymgr')";?>
	<tr>
		<th><?php echo $spText['common']['Directory']?>: </th>
		<td><input type="text" name="dir_name" value="<?php echo htmlentities($info['dir_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" onchange="<?php echo $submitLink?>" class="custom-select">
				<?php foreach($statusList as $key => $val){?>
					<?php if($info['stscheck'] == $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextDir['Captcha']?>: </th>
		<td>
			<select name="capcheck" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="">-- All --</option>
				<?php foreach($captchaList as $key => $val){?>
					<?php if($info['capcheck'] == $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Pagerank']?>: </th>
		<td>
			<select name="pagerank" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				for ($i=0; $i<=10; $i++) {
					$selected = (($info['pagerank'] != '') && ($i == $info['pagerank'])) ? "selected" : "";
					?>
					<option value="<?php echo $i?>" <?php echo $selected?>>PR <?php echo $i?></option>
					<?php
				}
				?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['common']['lang']?>: </th>
		<td>
			<select name="lang_code" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($langList as $langInfo) {
					$selected = ($langInfo['lang_code'] == $info['lang_code']) ? "selected" : "";
					?>
					<option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
					<?php
				}
				?>
			</select>
		</td>
		<td colspan="2" style="text-align: center;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=directorymgr')" class="btn btn-secondary">
				<?php echo $spText['button']['Show Records']?>
			</a>
		</td>
	</tr>
</table>
</form>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Website']?></td>
		<td>PR</td>
		<td><?php echo $spText['common']['Domain Authority']?></td>
		<td><?php echo $spText['common']['Page Authority']?></td>
		<td><?php echo $spTextDir['Captcha']?></td>
		<td><?php echo $spText['common']['lang']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 9;
	if(count($list) > 0){
		foreach($list as $i => $listInfo){

            $statusLink = $ctrler->getStatusLink($listInfo['id'], $listInfo['working']);
            $checkLink = scriptAJAXLinkHref('directories.php', "status_{$listInfo['id']}", "sec=checkdir&dir_id={$listInfo['id']}&nodebug=1&checkpr=1", $spText['button']["Check Status"]);
			?>
			<tr>
				<td><?php echo $listInfo['id']?></td>

				<td><a target="_blank" href="<?php echo $listInfo['submit_url']?>"><?php echo str_replace('http://', '', $listInfo['domain']); ?></a></td>
				<td id="pr_<?php echo $listInfo['id']?>"><?php echo $listInfo['pagerank']?></td>
				<td id="da_<?php echo $listInfo['id']?>"><?php echo $listInfo['domain_authority']?></td>
				<td id="pa_<?php echo $listInfo['id']?>"><?php echo $listInfo['page_authority']?></td>
				<td class="text-center" id="captcha_<?php echo $listInfo['id']?>">
					<?php echo $listInfo['is_captcha'] ?
						"<span class='badge badge-warning py-2 px-3 text-light'>{$spText['common']['Yes']}</span>" :
						"<span class='badge badge-success py-2 px-3 text-light'>{$spText['common']['No']}</span>";
					?>
				</td>
				<td><?php echo $listInfo['lang_name']?></td>
				<td id="status_<?php echo $listInfo['id']?>"><?php echo $statusLink; ?></td>
				<td><?php echo $checkLink; ?></td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
