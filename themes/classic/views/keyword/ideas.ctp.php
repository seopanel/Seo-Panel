<div class="aiv-page-head" style="display:flex;justify-content:space-between;align-items:center;">
	<h4 style="margin:0;"><?php echo $spTextKeyword['Keyword Research'] ?? 'Keyword Research'?></h4>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#keywordIdeasHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<div class="modal fade" id="keywordIdeasHelpModal" tabindex="-1" role="dialog" aria-labelledby="keywordIdeasHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="keywordIdeasHelpModalLabel">About Keyword Research</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<p>Enter a seed keyword and get related keyword suggestions with search volume, competition and cost-per-click, so you can discover new keywords worth tracking.</p>
				<div class="alert alert-info mb-0">
					<i class="fas fa-info-circle"></i>
					Results are shown immediately and not saved anywhere - it's a one-off lookup. Requires DataForSEO to be configured on this server.
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php if (empty($dfsEnabled)) { ?>
	<div class="alert alert-warning">
		<i class="fas fa-exclamation-triangle me-2"></i><?php echo $spTextKeyword['DataForSEO must be configured to use Keyword Research'] ?? 'DataForSEO must be configured to use Keyword Research'?>
	</div>
<?php } else { ?>
	<form id='search_form'>
	<table class="search" style="width: 60%">
		<tr>
			<th width="30%"><?php echo $spTextKeyword['Seed Keyword'] ?? 'Seed Keyword'?>: </th>
			<td>
				<input type="text" name="seed_keyword" class="form-control" style="width:300px;" />
			</td>
		</tr>
		<tr>
			<th><?php echo $spText['common']['Country']?>: </th>
			<td>
				<?php echo $this->render('country/countryselectbox', 'ajax'); ?>
			</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td>
				<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('keywords.php', 'search_form', 'subcontent', '&sec=ideas')"; ?>
				<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
			</td>
		</tr>
	</table>
	</form>
	<div id='subcontent'>
		<div class="alert alert-info">
			<i class="fas fa-info-circle me-2"></i><?php echo $spTextKeyword['Enter a seed keyword and click Proceed to get keyword suggestions'] ?? 'Enter a seed keyword and click Proceed to get keyword suggestions'?>
		</div>
	</div>
<?php } ?>
