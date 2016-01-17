<?php if ($plain) {
    ?>
    <tr class="white_row">
    	<td class="td_left_border td_br_right" style="border-right: 0px;">&nbsp;</td>
    	<td class="td_br_right" style="border-right: 0px;" colspan="<?php echo $colspan?>"><?php echo $msg?></td>
    	<td class="td_br_right">&nbsp;</td>
    </tr>
<?php 
} else {
    ?>	
    <tr class="white_row">
    	<td class="tab_left_bot_noborder">&nbsp;</td>
    	<td class="td_bottom_border" colspan="<?php echo $colspan?>"><?php echo $msg?></td>
    	<td class="tab_right_bot">&nbsp;</td>
    </tr>
<?php 
}?>
