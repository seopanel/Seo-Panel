<?php echo showSectionHead($spTextSA['Import Project Links']); ?>
<form id="importlinks">
<input type="hidden" name="sec" value="importlinks"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextSA['Import Project Links']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr class="blue_row">				
		<td class="td_left_col"><?php echo $spText['label']['Project']?>: </td>
		<td class="td_right_col">
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" style="width: 180px;">
				<?php foreach($projectList as $list) {?>
					<?php if($list['id'] == $projectId) {?>
						<option value="<?php echo $list['id']?>" selected="selected"><?php echo $list['name']?></option>
					<?php } else {?>
						<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
	</tr>	
	<tr class="white_row">
		<td class="td_left_col">Links:</td>
		<td class="td_right_col">
			<textarea name="links" rows="10"><?php echo $post['links']?></textarea>
			<br><?php echo $errMsg['links']?>
			<p style="font-size: 12px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<P><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</P>
		</td>
	</tr>		
	<tr class="blue_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('siteauditor.php', 'importlinks', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>