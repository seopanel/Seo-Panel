<?php echo showSectionHead($spTextKeyword['Quick Keyword Position Checker']); ?>
<form id='search_form'>
<table class="search" style="width: 60%">
	<tr>
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php echo $this->render('searchengine/seselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['lang']?>: </th>
		<td>
			<?php echo $this->render('language/languageselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Country']?>: </th>
		<td>
			<?php echo $this->render('country/countryselectbox', 'ajax'); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<input type="text" value="" name="url" class="form-control"/>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td>
			<input type="text" value="" name="name" class="form-control"/>
		</td>
	</tr>
	<tr>
		<th><?php echo $spTextKeyword['Show All results']?>: </th>
		<td>
			<div class="form-check">
				<input type="checkbox" value="1" name="show_all" class="form-check-input" id="show_all"/>
				<label class="form-check-label" for="show_all"></label>
			</div>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('reports.php', 'search_form', 'subcontent', '&sec=kwchecker')"; ?>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<div class="alert alert-info">
		<i class="fas fa-info-circle me-2"></i><?php echo $spTextTools['clickgeneratereports']?>
	</div>
</div>
