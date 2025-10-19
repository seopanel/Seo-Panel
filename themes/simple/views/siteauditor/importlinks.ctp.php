<?php echo showSectionHead($spTextSA['Import Project Links']); ?>
<form id="importlinks">
<input type="hidden" name="sec" value="importlinks"/>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextSA['Import Project Links']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<tr>
		<td><?php echo $spText['label']['Project']?>: </td>
		<td>
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" class="custom-select" style="width: 180px;">
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
	<tr>
		<td>Links:</td>
		<td>
			<textarea name="links" rows="10" class="form-control"><?php echo $post['links']?></textarea>
			<br><?php echo $errMsg['links']?>
			<p style="font-size: 12px;"><?php echo $spTextSA['Insert links separated with comma']?>.</p>
			<P><b>Eg:</b> https://www.seopanel.org/plugin/l/, https://www.seopanel.org/plugin/d/</P>
		</td>
	</tr>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('siteauditor.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('siteauditor.php', 'importlinks', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>