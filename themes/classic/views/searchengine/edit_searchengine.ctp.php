<?php
$headText = ($editAction == 'updateSearchEngine') ? 'Edit Search Engine' : 'New Search Engine';
echo showSectionHead($headText);
?>
<form id="edit_form" onsubmit="return false;">
    <input type="hidden" name="sec" value="<?php echo $editAction?>"/>
    <?php if ($editAction == 'updateSearchEngine') {?>
    	<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
    <?php }?>
	<table id="cust_tab">
    	<tr class="form_head">
    		<th colspan="2" class="text-start"><?php echo $headText?></th>
    	</tr>
    	<tbody>
        	<tr>
        		<td>Maximum Results:</td>
        		<td>
        			<input type="number" name="max_results" class="form-control" value="<?php echo $post['max_results']?>">
        			<?php echo $errMsg['max_results']?>
    			</td>
        	</tr>
    	</tbody>
    </table>
    <table width="100%" class="actionSec">
    	<tr>
        	<td style="padding-top: 6px;text-align:right;">
            	<a onclick="scriptDoLoad('searchengine.php', 'content')" href="javascript:void(0);" class="actionbut">
             		<?php echo $spText['button']['Cancel']?>
             	</a>&nbsp;
             	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('searchengine.php', 'edit_form', 'content')"; ?>
             	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
             		<?php echo $spText['button']['Proceed']?>
             	</a>
     		</td>
     	</tr>
 	</table>
</form>