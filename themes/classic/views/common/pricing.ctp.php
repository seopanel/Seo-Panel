<div class="Left">
	<div class="col">
        <?php echo getRoundTabTop(); ?>
        <div id="round_content">
    		<div class="Block">
    			<?php echo showSectionHead($spTextSubscription['Plans and Pricing']); ?>
    			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
					<tr class="plainHead">
						<td class="left" style="width: 30%;"><?php echo $spText['common']['User Type']; ?></td>
						<td><?php echo $spText['common']['Keywords Count']?></td>
						<td><?php echo $spText['common']['Websites Count']?></td>
						<td><?php echo $spText['common']['Price']?></td>
						<td class="right"></td>
					</tr>
					<?php
                    $colCount = 7;
                    if (count($list) > 0) {
                        $catCount = count($list);
                        foreach ($list as $i => $listInfo) {
                            $class = ($i % 2) ? "blue_row" : "white_row";
                            
                            if (!$i || ($catCount != ($i + 1))) {
                                $leftBotClass = "td_left_border td_br_right";
                                $rightBotClass = "td_br_right";
                            }
                            
                            $orderLink = SP_WEBPATH . '/register.php?utype_id=' . $listInfo['id'];
                            ?>
							<tr class="<?php echo $class?>">
								<td class="<?php echo $leftBotClass?> left">
									<a href="<?php echo $orderLink;
                            ?>"><?php echo $listInfo['description'];
                            ?></a>
								</td>
								<td class="td_br_right"><?php echo $listInfo['keywordcount']?></td>
								<td class="td_br_right"><?php echo $listInfo['websitecount']?></td>
								<td class="td_br_right" style="font-weight: bold;"><?php echo $currencyList[SP_PAYMENT_CURRENCY]['symbol'] . $listInfo['price']?></td>
								<td class="td_br_right">
									<a class="bold_link" href="<?php echo $orderLink;
                            ?>"><?php echo $spTextSubscription['Subscribe'];
                            ?> &gt;&gt;</a>
								</td>
							</tr>
							<?php

                        }
                    } else {
                        echo showNoRecordsList($colCount - 2, '', true);
                    }
                    ?>
					<tr class="listBot">
						<td class="left" colspan="<?php echo($colCount-1)?>"></td>
						<td class="right"></td>
					</tr>
				</table>
				
    		</div>
		</div>
		<?php echo getRoundTabBot(); ?>
	</div>
</div>