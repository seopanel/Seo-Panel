<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Url']?></td>
		<td>
			<?php echo $spText['common']['Spam Score']?>
			<i class="fas fa-info-circle" data-toggle="tooltip" title="Lower is better. Green: 0-30% (Low), Yellow: 31-60% (Medium), Red: 61-100% (High)"></i>
		</td>
		<td>
			<?php echo $spText['common']['Domain Authority']?>
			<i class="fas fa-info-circle" data-toggle="tooltip" title="Higher is better. Logarithmic scale 0-100"></i>
		</td>
		<td>
			<?php echo $spText['common']['Page Authority']?>
			<i class="fas fa-info-circle" data-toggle="tooltip" title="Higher is better. Logarithmic scale 0-100"></i>
		</td>
	</tr>
	<?php
	$colCount = 5;
	if(count($list) > 0) {
		foreach($list as $i => $url) {
            $debugVar = !empty($_POST['debug']) ? "&debug=1" : "";
            $debugVar .= !empty($_POST['debug_format']) ? "&debug_format=" . $_POST['debug_format'] : ""
			?>
			<tr>
				<td><?php echo ($i+1)?></td>
				<td class="text-left"><?php echo $url?></td>
				<td width="150px" id='mozrank<?php echo $i?>' class='td_br_right rankarea'>
					<?php
				$spamScore = !empty($mozRankList[$i]['spam_score']) ? floatval($mozRankList[$i]['spam_score']) : 0;
				$spamScoreColor = getSpamScoreColor($spamScore);
				$spamScoreLabel = getSpamScoreLabel($spamScore);
				?>
				<span class="badge bg-<?php echo $spamScoreColor?>" title="<?php echo $spamScoreLabel?>">
					<?php echo $spamScore > 0 ? round($spamScore, 2) . '%' : '-'; ?>
				</span>
				</td>
				<td width="150px" class='td_br_right rankarea'>
					<?php
				$da = !empty($mozRankList[$i]['domain_authority']) ? floatval($mozRankList[$i]['domain_authority']) : 0;
				$daColor = getAuthorityColor($da);
				$daLabel = getAuthorityLabel($da);
				?>
				<span class="badge bg-<?php echo $daColor?>" title="<?php echo $daLabel?>">
					<?php echo $da > 0 ? round($da, 2) : '-'; ?>
				</span>
				</td>
				<td width="150px" class='td_br_right rankarea'>
					<?php
				$pa = !empty($mozRankList[$i]['page_authority']) ? floatval($mozRankList[$i]['page_authority']) : 0;
				$paColor = getAuthorityColor($pa);
				$paLabel = getAuthorityLabel($pa);
				?>
				<span class="badge bg-<?php echo $paColor?>" title="<?php echo $paLabel?>">
					<?php echo $pa > 0 ? round($pa, 2) : '-'; ?>
				</span>
				</td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>